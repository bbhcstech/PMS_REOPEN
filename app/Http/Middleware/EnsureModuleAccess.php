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
        $user = $request->user();
        if (! $user || $user->normalizedRole() === 'admin') {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();
        $module = $this->moduleForRoute($routeName);

        if ($module && ! $user->hasModulePermission($module->slug, $permission)) {
            abort(403, 'You do not have permission to access this module.');
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
