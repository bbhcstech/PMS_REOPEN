<?php

namespace App\Http\Middleware;

use App\Services\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the Super Admin portal to the platform Super Admin.
 * Company admins, HR, managers, employees and clients are tenant users and must
 * never see or manage other companies, so they are turned away here.
 */
class EnsurePlatformSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (TenantScope::isPlatformAdmin()) {
            return $next($request);
        }

        $user = $request->user();
        $isDeveloper = $user && (
            (method_exists($user, 'isDeveloper') && $user->isDeveloper())
            || in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true)
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Only the platform Super Admin can access this area.'], 403);
        }

        if ($isDeveloper) {
            return redirect()->route('developer.dashboard');
        }

        return redirect()->route('dashboard')->with('error', 'Only the platform Super Admin can access this area.');
    }
}
