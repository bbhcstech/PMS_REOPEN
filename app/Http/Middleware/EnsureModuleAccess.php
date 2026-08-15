<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();

        // Core routes are always accessible to avoid redirect loops
        if (in_array($routeName, ['dashboard', 'home', 'login', 'logout'], true) || str_starts_with($routeName, 'dashboard.')) {
            return $next($request);
        }

        $module = $this->moduleForRoute($routeName);

        if ($module && $module->slug !== 'dashboard') {
            // Check company feature access for all tenant users (including Company Admins)
            $company = app(\App\Services\CompanyContext::class)->current();
            if (! $company && $user->company_id) {
                $company = \App\Models\Company::find($user->company_id);
            }

            if ($company && method_exists($company, 'hasFeature') && ! $company->hasFeature($module->slug)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => "Feature '{$module->name}' is not enabled for your company subscription plan.",
                    ], 403);
                }

                return redirect()->route('dashboard')
                    ->with('error', "Access Denied: The '{$module->name}' module is not included in your subscription plan.");
            }

            if (! $user->hasModulePermission($module->slug, $permission)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to access this module.'], 403);
                }

                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access this module.');
            }
        }

        return $next($request);
    }

    private function moduleForRoute(string $routeName): ?Module
    {
        if ($routeName === '') {
            return null;
        }

        $aliases = [
            'users.tasks' => 'tasks',
            'projects.tasks' => 'tasks',
            'projects.timelogs' => 'timelogs',
            'task-timer' => 'tasks',
            'sticky_notes' => 'dashboard',
            'dashboard-timers' => 'timelogs',
        ];

        foreach ($aliases as $prefix => $slug) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix . '.')) {
                return Module::where('slug', $slug)->where('is_active', true)->first();
            }
        }

        return Module::query()
            ->where('is_active', true)
            ->where(function ($query) use ($routeName) {
                $query->where('route_name', $routeName)
                    ->orWhere(function ($prefixQuery) use ($routeName) {
                        $prefixQuery->whereNotNull('route_prefix')
                            ->where(function ($nested) use ($routeName) {
                                $nested->where('route_prefix', strtok($routeName, '.'))
                                    ->orWhereRaw('? LIKE CONCAT(route_prefix, ".%")', [$routeName]);
                            });
                    });
            })
            ->orderByDesc('route_name')
            ->first();
    }
}
