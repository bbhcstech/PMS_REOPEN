<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminWorkspaceModules
{
    /** Called by deployment/platform catalog setup, never by a company read. */
    public function syncSidebarRegistry(): void
    {
        foreach (config('workspace_modules', []) as $slug => $item) {
            Module::firstOrCreate(['slug' => $slug], [
                'name' => $item['name'], 'route_name' => $item['route'],
                'route_prefix' => $slug === 'reports' ? 'reports' : null,
                'parent_id' => $item['parent'] ? Module::where('slug', $item['parent'])->value('id') : null,
                'is_active' => true, 'is_core' => true, 'sort_order' => $item['order'],
            ]);
        }
    }

    /** Keep sidebar labels separate from persisted platform identity. */
    private function sidebarDetails(Module $module): Module
    {
        $item = config('workspace_modules.'.$module->slug);
        $module->sidebar_name = $item['name'] ?? $module->name;
        $module->sidebar_parent_slug = $item ? $item['parent'] : $module->parent?->slug;
        $module->sidebar_order = $item['order'] ?? 1000 + (int) $module->sort_order;
        return $module;
    }

    /** Depth-first order keeps each sidebar's children immediately below it. */
    public function sidebarOrder(Collection $modules): Collection
    {
        $result = collect();
        $visited = [];
        $visit = function ($module, $depth = 0) use (&$visit, &$visited, $modules, $result) {
            if (isset($visited[$module->slug])) return;
            $visited[$module->slug] = true;
            $module->sidebar_depth = $depth;
            $result->push($module);
            foreach ($modules->where('sidebar_parent_slug', $module->slug)->sortBy('sidebar_order') as $child) $visit($child, $depth + 1);
        };
        foreach ($modules->sortBy('sidebar_order') as $module) {
            if (! $module->sidebar_parent_slug || ! $modules->contains('slug', $module->sidebar_parent_slug)) $visit($module);
        }
        // Malformed historical hierarchies cannot make modules disappear.
        foreach ($modules->sortBy('sidebar_order') as $module) $visit($module);
        return $result->values();
    }
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
        $catalog = $this->all(false)->keyBy('slug');
        $provided = $catalog->filter(function ($module) use ($catalog, $company) {
            $inherited = config('workspace_modules.'.$module->slug.'.inherits');
            return $module->is_active && (!$inherited || ! $catalog->has($inherited) || $catalog->get($inherited)->is_active)
                && $company->hasFeature($module->slug);
        });
        $tenant = \Illuminate\Support\Facades\DB::connection('tenant');
        return $tenant->transaction(function () use ($provided, $tenant) {
            $inserted = [];
            foreach ($provided as $platformModule) {
                if ($tenant->table('modules')->where('slug', $platformModule->slug)->exists()) continue;
                $attributes = $platformModule->getAttributes();
                unset($attributes['id'], $attributes['category'], $attributes['sidebar_name'], $attributes['sidebar_parent_slug'], $attributes['sidebar_order']);
                $attributes['parent_id'] = null;
                $tenant->table('modules')->insert($attributes);
                $inserted[] = $platformModule->slug;
            }
            $local = \App\Models\WorkspaceModule::with('parent')->whereIn('slug', $provided->pluck('slug'))->get()->keyBy('slug');
            $modules = $provided->map(function ($platformModule) use ($local, $inserted, $tenant) {
                $module = $local->get($platformModule->slug);
                // The platform defines module identity; the company keeps its local enabled state.
                foreach (['name', 'icon', 'description', 'route_name', 'route_prefix', 'is_core', 'sort_order'] as $attribute) {
                    $module->setAttribute($attribute, $platformModule->{$attribute});
                }
                $module->category = $this->categoryFor($module->slug);
                foreach (['sidebar_name', 'sidebar_parent_slug', 'sidebar_order'] as $attribute) {
                    $module->setAttribute($attribute, $platformModule->{$attribute});
                }
                $parent = $local->get($platformModule->sidebar_parent_slug);
                if (in_array($module->slug, $inserted, true) && $parent) {
                    $tenant->table('modules')->where('id', $module->id)->update(['parent_id' => $parent->id]);
                }
                $module->parent_id = $parent?->id;
                $module->setRelation('parent', $parent);
                return $module;
            });
            return $this->sidebarOrder($modules);
        });
    }

    public function all(bool $ensureRegistry = true): Collection
    {
        if ($ensureRegistry) $this->syncSidebarRegistry();
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

        return Module::with('parent')->orderBy('sort_order')->orderBy('name')->get()
            ->filter(function (Module $module) use ($slugs, $routes) {
                if ($slugs->contains($module->slug) || config('workspace_modules.'.$module->slug)) return true;
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
                return $this->sidebarDetails($module);
            })->values();
    }
}
