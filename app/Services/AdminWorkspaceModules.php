<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminWorkspaceModules
{
    public function all(): Collection
    {
        $sidebar = file_get_contents(resource_path('views/admin/layout/manu.blade.php'));
        preg_match_all('/\$canSeeModule\([\'"]([^\'"]+)[\'"]\)|data-sidebar-key=[\'"]([^\'"]+)[\'"]/', $sidebar, $matches);
        $slugs = collect(array_merge($matches[1], $matches[2]))->filter()->unique()->values();
        $slugs->push('dashboard');
        preg_match_all('/route\([\'"]([^\'"]+)[\'"]/', $sidebar, $routeMatches);
        $routes = collect($routeMatches[1])->filter(fn ($name) => Route::has($name))->unique();

        foreach ($slugs as $order => $slug) {
            Module::firstOrCreate(['slug' => $slug], [
                'name' => Str::headline($slug),
                'icon' => 'bx-cube',
                'description' => 'Admin workspace feature.',
                'is_active' => true,
                'sort_order' => $order + 1,
            ]);
        }

        return Module::orderBy('sort_order')->orderBy('name')->get()
            ->filter(function (Module $module) use ($slugs, $routes) {
                if ($slugs->contains($module->slug)) return true;
                // Custom modules must point to an implemented workspace page.
                if ($module->route_name && $routes->contains($module->route_name)) return true;
                if (! $module->route_prefix) return false;
                return $routes->contains(function ($name) use ($module) {
                    $route = Route::getRoutes()->getByName($name);
                    $prefix = trim($module->route_prefix, '/');
                    return $name === $prefix || str_starts_with($name, $prefix . '.')
                        || $route->uri() === $prefix || str_starts_with($route->uri(), $prefix . '/');
                });
            })->map(function (Module $module) {
                // Category is display metadata; the modules table does not store it.
                $module->category = match (true) {
                    (bool) preg_match('/employee|hr|department|designation|attendance|leave|holiday|award|recognition|recruitment|appraisal|letterhead/', $module->slug) => 'HR & PEOPLE',
                    (bool) preg_match('/project|task|timelog|timesheet|work$|client|contract|product|order|collaborating/', $module->slug) => 'WORK MANAGEMENT',
                    (bool) preg_match('/payroll|salary|payslip|expense|billing/', $module->slug) => 'FINANCE & PAYROLL',
                    (bool) preg_match('/setting|role|permission|activity|log/', $module->slug) => 'ADMINISTRATION & SETTINGS',
                    default => 'CORE PLATFORM',
                };
                return $module;
            })->values();
    }
}
