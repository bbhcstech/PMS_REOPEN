<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloperAccess
{
    /**
     * Handle an incoming request for Developer Portal routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Allow Super Admin to access Developer Portal for previewing/inspecting
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // Developer role check
        $isDevRole = method_exists($user, 'isDeveloper') ? $user->isDeveloper() : (
            in_array(strtolower($user->role ?? ''), ['developer', 'dev'], true)
            || str_contains(strtolower($user->designation ?? ''), 'developer')
            || str_contains(strtolower($user->designation ?? ''), 'engineer')
        );

        if ($isDevRole && $user->login_allowed && empty($user->archived_at)) {
            // Force temporary password change requirement if set
            if ($user->must_change_password && !in_array($request->route()?->getName(), ['developer.settings', 'developer.settings.password', 'logout', 'superadmin.logout'], true)) {
                return redirect()->route('developer.settings')->with('warning', 'Security Action Required: Please change your temporary password before continuing.');
            }
            return $next($request);
        }

        abort(403, 'Unauthorized access. Developer Portal access required.');
    }
}
