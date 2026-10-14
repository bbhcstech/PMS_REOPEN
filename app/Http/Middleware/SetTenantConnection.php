<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantConnection
{
    /**
     * Set the active tenant database connection dynamically for the incoming request.
     *
     * SESSION is the primary source of truth — it is written once at login and
     * is always correct. Using user->company_id as the primary resolver caused a
     * chicken-and-egg problem: Auth::user() reloads the user from whatever 'tenant'
     * DB is active BEFORE this middleware switches it, so the user could be from
     * a different company's DB, giving the wrong company_id back.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultDb = DB::connection('session_db')->getDatabaseName();
        // Preserve credentials parsed from DB_URL, while preventing its database
        // component from overriding the company database chosen below.
        $mysqlConfiguration = DB::connection('mysql')->getConfig();
        $mysqlConfiguration['url'] = null;
        config(['database.connections.mysql' => $mysqlConfiguration]);
        app(\App\Services\CompanyContext::class)->reset();

        // A company deleted by the Super Admin must not keep working: end every session bound to it.
        $sessionCompanyId = $request->session()->get('current_company_id');
        $registeredCompany = null;
        if ($sessionCompanyId) {
            try {
                $registeredCompany = \App\Models\Central\Company::on('central')->withTrashed()->find($sessionCompanyId);
            } catch (\Throwable $e) {
                abort(503, 'Company registry is temporarily unavailable.');
            }
        }
        if ($registeredCompany?->trashed()) {
            return $this->endDeletedCompanySession($request, $next, $defaultDb);
        }

        // Restore the login database before any guard retrieves its user.
        $sessionDb = $request->session()->get('current_company_db');
        // A database name is never an authority: bind it to the central company registry
        // before the tenant guard can load a user with a colliding numeric ID.
        if ($sessionCompanyId || $sessionDb) {
            abort_unless($registeredCompany && $registeredCompany->db_name
                && (!$sessionDb || $sessionDb === $registeredCompany->db_name), 403, 'Invalid company workspace. Please sign in again.');
            $sessionDb = $registeredCompany->db_name;
            $request->session()->put('current_company_db', $sessionDb);
        } else {
            // Clear database state left by a previous request in a persistent worker.
            if (config('database.connections.tenant.database') !== $defaultDb || config('database.connections.mysql.database') !== $defaultDb) {
                $this->useDatabase($defaultDb);
            }
        }
        if ($sessionDb) {
            try {
                if (config("database.connections.tenant.database") !== $sessionDb || config("database.connections.mysql.database") !== $sessionDb) {
                    config([
                        "database.connections.tenant.database" => $sessionDb,
                        "database.connections.mysql.database"  => $sessionDb,
                    ]);
                    DB::purge('tenant');
                    DB::purge('mysql');
                }
                DB::connection('tenant')->getPdo();
                DB::connection('mysql')->getPdo();
            } catch (\Throwable $e) {
                DB::purge('tenant');
                DB::purge('mysql');
                abort(503, 'Your company workspace is temporarily unavailable.');
            }
        }

        $boundUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if ($boundUser?->company_id && !$sessionCompanyId && !\App\Services\TenantScope::isPlatformAdmin()) {
            abort(403, 'Company session is missing. Please sign in again.');
        }
        if ($sessionCompanyId && $boundUser && !\App\Services\TenantScope::isPlatformAdmin()) {
            abort_unless((int) $boundUser->company_id === (int) $sessionCompanyId, 403, 'Account does not belong to this company workspace.');
        }
        app(\App\Services\CompanyContext::class)->reset($registeredCompany);

        // 1. Bypass tenant DB switching for SuperAdmin & Developer routes
        if (
            $request->is('super-admin*') ||
            $request->is('superadmin*') ||
            $request->is('developer*')
        ) {
            return $this->privateResponse($next($request));
        }
        $isSuperAdmin = \App\Services\TenantScope::isPlatformAdmin();

        $user = \Illuminate\Support\Facades\Auth::guard('web')->user() ?? auth()->user();
        $targetDb = null;

        if ($isSuperAdmin) {
            // Super Admin can switch / impersonate via session
            $targetDb = session('current_company_db') ?: $defaultDb;
        } elseif ($user) {
            $isDev = (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
                in_array(strtolower((string)($user->role ?? '')), ['developer', 'dev'], true) ||
                str_contains(strtolower((string)($user->role ?? '')), 'developer') ||
                str_contains(strtolower((string)($user->designation ?? '')), 'developer') ||
                str_contains(strtolower((string)($user->designation ?? '')), 'engineer');

            if (! $isDev) {
                $company = null;

                // PRIMARY: Trust the session set at login time — it is always correct.
                // user->company_id cannot be trusted here because Laravel reloads the
                // user from whatever 'tenant' DB is active at middleware boot time
                // (before we've had a chance to switch it), causing a stale-user
                // chicken-and-egg problem that redirects to the wrong company.
                if (session('current_company_id')) {
                    $company = $registeredCompany;
                }

                // SECONDARY: No session yet (e.g. first request after seeding or a "remember me" re-login).
                // Fall back to company_id on the user — safe only when no session exists.
                if (! session('current_company_id') && \App\Models\Central\Company::isDeletedById($user->company_id ?? null)) {
                    return $this->endDeletedCompanySession($request, $next, $defaultDb);
                }

                if (!$company && !empty($user->company_id)) {
                    try {
                        $company = \App\Models\Central\Company::on('central')->find($user->company_id);
                    } catch (\Throwable $e) {}
                }

                if ($company && !empty($company->db_name)) {
                    abort_unless((int) $company->id === (int) $user->company_id, 403, 'Account does not belong to this company workspace.');
                    $targetDb = $company->db_name;
                    // Keep session consistent
                    session([
                        'current_company_db'   => $targetDb,
                        'current_company_id'   => $company->id,
                        'current_company_name' => $company->name,
                    ]);
                    // Real "Last Activity" shown to the Super Admin (throttled to one write a minute).
                    \App\Services\CompanyActivity::touch((int) $company->id);
                } else {
                    abort(403, 'No valid company workspace is assigned to this account.');
                }
            } else {
                $targetDb = session('current_company_db') ?: $defaultDb;
            }
        } else {
            // Unauthenticated guest request
            $targetDb = session('current_company_db') ?: $defaultDb;
        }

        if (!$targetDb) {
            $targetDb = $defaultDb;
        }

        // Apply target connection if different from current
        $currentTenantDb = config('database.connections.tenant.database');
        $currentMysqlDb  = config('database.connections.mysql.database');
        if ($targetDb !== $currentTenantDb || $targetDb !== $currentMysqlDb) {
            try {
                config([
                    'database.connections.tenant.database' => $targetDb,
                    'database.connections.mysql.database'  => $targetDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
                // Test PDO connection
                DB::connection('tenant')->getPdo();
                DB::connection('mysql')->getPdo();
            } catch (\Throwable $e) {
                DB::purge('tenant');
                DB::purge('mysql');
                abort(503, 'Your company workspace is temporarily unavailable.');
            }
        }

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset($registeredCompany ?? $company ?? null);
        }

        if ($user?->company_id && ! $isSuperAdmin) {
            app(\App\Services\AttendanceAutoClockOut::class)->closeDue((int) $user->company_id, (int) $user->id);
        }
        return $this->privateResponse($next($request));
    }

    private function privateResponse(Response $response): Response
    {
        if (\Illuminate\Support\Facades\Auth::guard('web')->check() || \Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->setVary('Cookie', false);
        }
        return $response;
    }

    /**
     * The session's company was deleted by the Super Admin.
     * Company users are logged out (remember-me cookie included) and sent to login;
     * a Super Admin who was viewing that company only loses the company context.
     */
    private function endDeletedCompanySession(Request $request, Closure $next, string $defaultDb): Response
    {
        // Only the central super_admin guard is trusted here: a web-guard user id from the deleted
        // company's DB could resolve to an unrelated user once the connection falls back to default.
        $isSuperAdmin = false;
        try {
            $isSuperAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check();
        } catch (\Throwable $e) {}

        if ($isSuperAdmin) {
            $this->useDatabase($defaultDb);
            $request->session()->forget(['current_company_id', 'current_company_db', 'current_company_name']);

            if (app()->bound(\App\Services\CompanyContext::class)) {
                app(\App\Services\CompanyContext::class)->reset();
            }

            return $next($request);
        }

        // Do not reload the deleted tenant's numeric user ID in the primary DB
        // merely to revoke a session: it could belong to an unrelated account.
        $guard = \Illuminate\Support\Facades\Auth::guard('web');
        $guard->forgetUser();
        \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget($guard->getRecallerName()));
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->useDatabase($defaultDb);

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset();
        }

        $message = 'Your company account has been deleted. You have been signed out and can no longer access this workspace.';

        if ($request->expectsJson() || $request->ajax()) {
            $request->session()->flash('error', $message);
            return response()->json([
                'message'  => $message,
                'redirect' => route('login'),
            ], 401)->header('X-Company-Deleted', '1');
        }

        return redirect()->route('login')->with('error', $message);
    }

    private function useDatabase(string $database): void
    {
        config([
            'database.connections.tenant.database' => $database,
            'database.connections.mysql.database'  => $database,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');
    }
}
