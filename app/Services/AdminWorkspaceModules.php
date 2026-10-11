<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminWorkspaceModules
{
    public function categoryFor(string $slug): string
    {
        return match (true) {
            (bool) preg_match('/payroll|salary|payslip|expense|billing|formula|deduction|bonus|tax-rule|overtime/', $slug) => 'FINANCE & PAYROLL',
            (bool) preg_match('/setting|role|permission|activity|system-log/', $slug) => 'ADMINISTRATION & SETTINGS',
            (bool) preg_match('/employee|hr|department|designation|attendance|leave|holiday|award|recognition|recruitment|appraisal/', $slug) => 'HR & PEOPLE',
            (bool) preg_match('/project|task|timelog|timesheet|work|client|contract|product|order/', $slug) => 'WORK MANAGEMENT',
            default => 'CORE PLATFORM',
        };
    }
    /** Read-only list of registry entries backed by the implemented Admin menu. */
    public function permissionMatrixModules(): Collection
    {
        $sidebar = file_get_contents(resource_path('views/admin/layout/manu.blade.php'));
        preg_match_all('/\$canSeeModule\([\'\"]([^\'\"]+)[\'\"]\)|data-sidebar-key=[\'\"]([^\'\"]+)[\'\"]/', $sidebar, $matches);
        $slugs = collect(array_merge($matches[1], $matches[2]))->filter()->push('dashboard')->unique();
        preg_match_all('/\$canAnyModule\(\[([^\]]+)\]/', $sidebar, $groups);
        foreach ($groups[1] as $group) {
            preg_match_all('/[\'\"]([^\'\"]+)[\'\"]/', $group, $items);
            $slugs = $slugs->merge($items[1]);
        }
        preg_match_all('/route\([\'\"]([^\'\"]+)[\'\"]/', $sidebar, $routeMatches);
        $routes = collect($routeMatches[1])->filter(fn ($name) => Route::has($name))->unique();

        return \App\Models\WorkspaceModule::with('parent')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()
            ->filter(function (Module $module) use ($slugs, $routes) {
                if (in_array(strtolower(trim($module->slug)), ['bye', 'kjm'], true)
                    || in_array(strtolower(trim($module->name)), ['bye', 'kjm'], true)) return false;
                if ($slugs->contains($module->slug)) return true;
                // Payroll navigation opens a workspace whose tabs have their own permissions.
                if ($module->slug === 'payslips' && Route::has('payroll.payslips.index')) return true;
                if ($module->route_name && str_starts_with($module->route_name, 'payroll.')
                    && Route::has($module->route_name)) return true;
                if ($module->route_name && $routes->contains($module->route_name)) return true;
                if (!$module->route_prefix) return false;
                $prefix = trim($module->route_prefix, '/');
                return $routes->contains(function ($name) use ($prefix) {
                    $route = Route::getRoutes()->getByName($name);
                    return $name === $prefix || str_starts_with($name, $prefix . '.')
                        || $route->uri() === $prefix || str_starts_with($route->uri(), $prefix . '/');
                });
            })->map(function ($module) {
                $module->category = $this->categoryFor($module->slug);
                return $module;
            })->values();
    }

    /** Company list uses the platform workspace catalog and its effective entitlements. */
    public function companyModules($company): Collection
    {
        $provided = $this->all(false)->filter(fn ($module) => $module->is_active && $company->hasFeature($module->slug));
        $tenant = \Illuminate\Support\Facades\DB::connection('tenant');
        return $tenant->transaction(function () use ($provided, $tenant) {
            foreach ($provided as $platformModule) {
                if ($tenant->table('modules')->where('slug', $platformModule->slug)->exists()) continue;
                $attributes = $platformModule->getAttributes();
                unset($attributes['id'], $attributes['category']);
                $attributes['parent_id'] = null;
                $tenant->table('modules')->insert($attributes);
            }
            $local = \App\Models\WorkspaceModule::with('parent')->whereIn('slug', $provided->pluck('slug'))->get()->keyBy('slug');
            return $provided->map(function ($platformModule) use ($local) {
                $module = $local->get($platformModule->slug);
                // The platform defines module identity; the company keeps its local enabled state.
                foreach (['name', 'icon', 'description', 'route_name', 'route_prefix', 'is_core', 'sort_order'] as $attribute) {
                    $module->setAttribute($attribute, $platformModule->{$attribute});
                }
                $module->category = $this->categoryFor($module->slug);
                $parent = $platformModule->parent ? $local->get($platformModule->parent->slug) : null;
                $module->parent_id = $parent?->id;
                $module->setRelation('parent', $parent);
                return $module;
            })->sortBy('category')->values();
        });
    }

    public function all(bool $ensureRegistry = true): Collection
    {
        $sidebar = file_get_contents(resource_path('views/admin/layout/manu.blade.php'));
        preg_match_all('/\$canSeeModule\([\'"]([^\'"]+)[\'"]\)|data-sidebar-key=[\'"]([^\'"]+)[\'"]/', $sidebar, $matches);
        $slugs = collect(array_merge($matches[1], $matches[2]))->filter()->unique()->values();
        $slugs->push('dashboard');
        preg_match_all('/route\([\'"]([^\'"]+)[\'"]/', $sidebar, $routeMatches);
        $routes = collect($routeMatches[1])->filter(fn ($name) => Route::has($name))->unique();

        foreach ($ensureRegistry ? $slugs : [] as $order => $slug) {
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
