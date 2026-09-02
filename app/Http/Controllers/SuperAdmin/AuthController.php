<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the Super Admin login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('super_admin')->check()) {
            return redirect()->route('superadmin.dashboard');
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $isDev = method_exists($user, 'isDeveloper') ? $user->isDeveloper() : in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true);
            if ($isDev) {
                return redirect()->route('developer.dashboard');
            }
            if (in_array(strtolower((string) ($user->role ?? '')), ['superadmin', 'admin'], true)) {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }

    /**
     * Handle Super Admin authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('super_admin')->attempt($credentials, $remember)) {
            $user = Auth::guard('super_admin')->user();

            if (! $user->is_active) {
                Auth::guard('super_admin')->logout();
                return back()->withErrors([
                    'email' => 'Your account is deactivated. Please contact administrator.',
                ]);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $request->session()->regenerate();

            return redirect()->intended(route('superadmin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Destroy Super Admin authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('super_admin')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
