<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && (
                (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
                in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
                str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
                str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
                str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
            )) {
                return redirect()->route('developer.dashboard');
            }
        }

        $request->session()->regenerateToken();

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // LoginRequest::authenticate() resolves the correct company and writes
        // current_company_id / current_company_db / current_company_name to session.
        $request->authenticate();

        // regenerate() changes session ID but preserves all session data (including
        // the company session keys written inside authenticate() above).
        $request->session()->regenerate();

        // Multi-Tenant: Re-affirm the company session and lock the DB connection.
        // We deliberately do NOT read user->company_id here because Auth::user() may
        // have been loaded from the wrong DB connection before SetTenantConnection
        // had a chance to switch it. The session is the single source of truth.
        $user = Auth::user();
        $isDeveloper = $user && (
            (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
            in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
            str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
        );

        if ($user && ! $isDeveloper) {
            // Resolve company exclusively from session (set correctly by authenticate())
            $company = null;
            if (session('current_company_id')) {
                try {
                    $company = \App\Models\Central\Company::on('central')->find(session('current_company_id'));
                } catch (\Throwable $e) {}
            }

            $dbName     = $company?->db_name     ?: (session('current_company_db')   ?: config('database.connections.tenant.database'));
            $companyId   = $company?->id          ?: session('current_company_id');
            $companyName = $company?->name        ?: session('current_company_name');

            // Write final, authoritative company context to session
            $request->session()->put('current_company_db',   $dbName);
            $request->session()->put('current_company_id',   $companyId);
            $request->session()->put('current_company_name', $companyName);

            // Point both connections at the correct company DB
            config([
                'database.connections.tenant.database' => $dbName,
                'database.connections.mysql.database'  => $dbName,
            ]);
            \Illuminate\Support\Facades\DB::purge('tenant');
            \Illuminate\Support\Facades\DB::purge('mysql');

            if (app()->bound(\App\Services\CompanyContext::class)) {
                app(\App\Services\CompanyContext::class)->reset();
            }
        }

        $intendedUrl = session('url.intended');
        if ($intendedUrl && (str_contains($intendedUrl, '/login') || str_contains($intendedUrl, '/register'))) {
            session()->forget('url.intended');
        }

        $user = Auth::user();
        $role = strtolower((string) ($user?->role ?? ''));
        $designation = strtolower((string) ($user?->designation ?? ''));

        // 1. Developer redirection (unconditional for any developer account)
        if (
            ($user && method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
            in_array($role, ['developer', 'dev'], true) ||
            str_contains($role, 'developer') ||
            str_contains($designation, 'developer') ||
            str_contains($designation, 'engineer')
        ) {
            return redirect()->route('developer.dashboard');
        }

        if ($role === 'superadmin') {
            // If superadmin logged in via company credentials (tenant company session active), redirect to company dashboard
            if (session('current_company_id') && session('current_company_id') != 1) {
                return redirect()->route('dashboard');
            }
            return redirect()->route('superadmin.dashboard');
        }

        if ($role === 'employee') {
            return redirect()->route('dashboard');
        }

        if (in_array($role, ['developer', 'dev'], true) || str_contains($designation, 'developer') || str_contains($designation, 'engineer')) {
            return redirect()->route('developer.dashboard');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        if (Auth::guard('super_admin')->check()) {
            Auth::guard('super_admin')->logout();
        }
        Auth::logout();

        $request->session()->forget(['current_company_id', 'current_company_db', 'current_company_name']);
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $defaultDb = env('DB_DATABASE', 'pms_last');
        config([
            'database.connections.tenant.database' => $defaultDb,
            'database.connections.mysql.database'  => $defaultDb,
        ]);
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\DB::purge('mysql');

        if (app()->bound(\App\Services\CompanyContext::class)) {
            app(\App\Services\CompanyContext::class)->reset();
        }

        return redirect()->route('login');
    }
}
