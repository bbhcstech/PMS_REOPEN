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
        $isDevRole = in_array(strtolower($user->role ?? ''), ['developer', 'dev'], true)
            || str_contains(strtolower($user->designation ?? ''), 'developer')
            || str_contains(strtolower($user->designation ?? ''), 'engineer');

        if ($isDevRole && $user->login_allowed && empty($user->archived_at)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Developer Portal access required.');
    }
}
