<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }
    
   public function handle(Request $request, Closure $next, $role = 'admin')
    {
        $allowedRoles = array_filter(array_map('trim', explode(',', (string) $role)));
        if (empty($allowedRoles)) {
            $allowedRoles = ['admin'];
        }

        $userRole = strtolower((string) auth()->user()?->role);

        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check() 
            || (auth()->check() && (in_array($userRole, $allowedRoles, true) || in_array($userRole, ['superadmin', 'admin', 'administrator'], true)))) {
            return $next($request);
        }

        // If the role doesn't match, redirect to home or error page
        return redirect('home')->with('error', 'You do not have the required role.');
    }

}
