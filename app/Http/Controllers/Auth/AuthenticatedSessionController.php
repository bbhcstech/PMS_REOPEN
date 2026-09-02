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
    public function create(Request $request): Response
    {
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
        if ($user) {
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

            $dbName = $company?->db_name ?: (session('current_company_db') ?: config('database.connections.tenant.database', env('DB_DATABASE', 'pms_last')));
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

        $role = strtolower(Auth::user()?->role ?? '');
        $designation = strtolower(Auth::user()?->designation ?? '');

        if ($role === 'superadmin') {
            // If superadmin logged in via company credentials (tenant company session active), redirect to company dashboard
            if (session('current_company_id') && session('current_company_id') != 1) {
                return redirect()->route('dashboard');
            }
            return redirect()->route('superadmin.dashboard');
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
        Session::forget('auth_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
