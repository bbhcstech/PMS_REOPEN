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
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user && (
                (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
                in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
                str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
                str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
                str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
            )) {
                return redirect()->route('developer.dashboard');
            }
            if ($user && strtolower((string) ($user->role ?? '')) === 'superadmin') {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        if (Auth::guard('super_admin')->check()) {
            return redirect()->route('superadmin.dashboard');
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

        $user = Auth::user();

        // If a standard company user is logged in (manager, hr, employee, admin, etc.), clear any stale super_admin guard
        if ($user && strtolower((string) ($user->role ?? '')) !== 'superadmin') {
            if (Auth::guard('super_admin')->check()) {
                Auth::guard('super_admin')->logout();
            }
        }
        $isDeveloper = $user && (
            (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
            in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
            str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
        );

        if ($user && $user->company_id && !\App\Services\SecureCompanyLogin::isPlatformDeveloper($user)) {
            $company = \App\Models\Central\Company::find(session('current_company_id'));
            if (!$company || (int) $company->id !== (int) $user->company_id
                || $company->db_name !== config('database.connections.tenant.database')) {
                $request->session()->forget([Auth::guard('web')->getName(), 'current_company_id', 'current_company_db', 'current_company_name']);
                Auth::guard('web')->forgetUser();
                throw \Illuminate\Validation\ValidationException::withMessages(['email' => trans('auth.failed')]);
            }
        }

        $intendedUrl = session('url.intended');
        if ($intendedUrl && (str_contains($intendedUrl, '/login') || str_contains($intendedUrl, '/register'))) {
            session()->forget('url.intended');
        }

        $user = Auth::user();
        $role = strtolower((string) ($user?->role ?? ''));
        $designation = strtolower((string) ($user?->designation ?? ''));
        if ($user?->company_staff_role_id) return redirect()->route('dashboard');
        // A newly added employee's first login opens the employee dashboard once for the welcome; every
        // later login goes straight to their usual workspace (e.g. the developer portal for engineers).
        if ($role === 'employee' && $user && ! $user->employee_welcome_seen_at) return redirect()->route('dashboard');

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

        if ($role === 'superadmin' || (! $user && Auth::guard('super_admin')->check())) {
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
        return app(\App\Services\AuthenticationSession::class)->logout($request);
    }
}
