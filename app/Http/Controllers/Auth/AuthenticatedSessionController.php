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
        // LoginRequest handles all authentication including exit date checks
        $request->authenticate();

        $request->session()->regenerate();

        // Multi-Tenant: Preserve and set active tenant company database name and company ID in session
        $user = Auth::user();
        $isDeveloper = $user && (
            (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
            in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
            str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
        );

        if ($user && ! $isDeveloper) {
            $company = null;
            if (!empty($user->company_id)) {
                $company = \App\Models\Central\Company::on('central')->where('id', $user->company_id)->first();
            }
            if (!$company && !empty($user->email)) {
                $company = \App\Models\Central\Company::on('central')->where('email', $user->email)->first();
            }
            if (!$company && session('current_company_db')) {
                $company = \App\Models\Central\Company::on('central')->where('db_name', session('current_company_db'))->first();
            }

            $dbName = $company?->db_name ?: (session('current_company_db') ?: config('database.connections.tenant.database'));
            $companyId = $company?->id ?: session('current_company_id');
            $companyName = $company?->name ?: session('current_company_name');

            $request->session()->put('current_company_db', $dbName);
            if ($companyId) {
                $request->session()->put('current_company_id', $companyId);
            }
            if ($companyName) {
                $request->session()->put('current_company_name', $companyName);
            }

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

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
