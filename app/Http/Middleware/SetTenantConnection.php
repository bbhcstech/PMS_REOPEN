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
        $defaultDb = config('database.connections.mysql.database') ?: env('DB_DATABASE', 'pms_last');

        // Restore the login database before any guard retrieves its user.
        $sessionDb = $request->session()->get('current_company_db');
        if ($sessionDb) {
            try {
                if (config("database.connections.tenant.database") !== $sessionDb || config("database.connections.mysql.database") !== $sessionDb) {
                    config([
                        "database.connections.tenant.database" => $sessionDb,
                        "database.connections.mysql.database"  => $sessionDb,
                    ]);
                    DB::purge('tenant');
                    DB::purge('mysql');
                    DB::connection('tenant')->getPdo();
                }
            } catch (\Throwable $e) {
                // If session DB is invalid/inaccessible (e.g. pms_last on production server), revert to default
                config([
                    "database.connections.tenant.database" => $defaultDb,
                    "database.connections.mysql.database"  => $defaultDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
                $request->session()->put('current_company_db', $defaultDb);
            }
        }

        // 1. Bypass tenant DB switching for SuperAdmin & Developer routes
        if (
            $request->is('super-admin*') ||
            $request->is('superadmin*') ||
            $request->is('developer*')
        ) {
            return $next($request);
        }
        $isSuperAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check() ||
            (auth()->check() && in_array(strtolower((string)(auth()->user()->role ?? '')), ['superadmin', 'super-admin', 'super_admin'], true));

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
                    try {
                        $company = \App\Models\Central\Company::on('central')->find(session('current_company_id'));
                    } catch (\Throwable $e) {}
                }

                // SECONDARY: No session yet (e.g. first request after seeding).
                // Fall back to company_id on the user — safe only when no session exists.
                if (!$company && !empty($user->company_id)) {
                    try {
                        $company = \App\Models\Central\Company::on('central')->find($user->company_id);
                    } catch (\Throwable $e) {}
                }

                if ($company && !empty($company->db_name)) {
                    $targetDb = $company->db_name;
                    // Keep session consistent
                    session([
                        'current_company_db'   => $targetDb,
                        'current_company_id'   => $company->id,
                        'current_company_name' => $company->name,
                    ]);
                } else {
                    $targetDb = session('current_company_db') ?: $defaultDb;
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
                // If target DB cannot be connected to, fall back safely to default database
                config([
                    'database.connections.tenant.database' => $defaultDb,
                    'database.connections.mysql.database'  => $defaultDb,
                ]);
                DB::purge('tenant');
                DB::purge('mysql');
                $request->session()->put('current_company_db', $defaultDb);
            }
        }

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset();
        }

        return $next($request);
    }
}
