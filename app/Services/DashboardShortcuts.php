<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\User;
use App\Models\WorkspaceModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class DashboardShortcuts
{
    public const SETTING_KEY = 'dashboard_feature_shortcuts_enabled';

    public function enabled(): bool
    {
        return AppSetting::valueFor(self::SETTING_KEY, '0') === '1';
    }

    public function save(bool $enabled): void
    {
        AppSetting::updateOrCreate(['key' => self::SETTING_KEY], [
            'label' => 'Dashboard Feature Shortcuts',
            'value' => $enabled ? '1' : '0',
            'type' => 'checkbox', 'page' => 'dashboard',
            'section' => 'Dashboard Preferences', 'sort_order' => 0,
        ]);
    }

    public function groups(User $user): Collection
    {
        if (! $this->enabled() || ! in_array($user->normalizedRole(), ['admin', 'superadmin'], true)) {
            return collect();
        }

        $registry = collect(config('workspace_modules', []));
        // Include company modules added after deployment as well as the built-in catalog.
        foreach (WorkspaceModule::where('is_active', true)->orderBy('sort_order')->get() as $module) {
            if (! $registry->has($module->slug)) {
                $registry->put($module->slug, ['name' => $module->name, 'route' => $module->route_name]);
            }
        }

        $catalog = app(AdminWorkspaceModules::class);
        return $registry->map(function ($item, $slug) use ($user, $catalog) {
            $name = $item['route'] ?? null;
            $route = $name ? Route::getRoutes()->getByName($name) : null;
            if ($slug === 'dashboard' || ! $route || ! in_array('GET', $route->methods(), true)
                || ! $user->canViewModule($slug)
                || ($slug === 'tickets' && \App\Support\TicketAccess::hiddenForCurrentUser())) {
                return null;
            }
            // Shortcuts must open directly, without needing a record ID.
            foreach ($route->parameterNames() as $parameter) {
                if (! array_key_exists($parameter, $route->defaults)
                    && ! str_contains($route->uri(), '{'.$parameter.'?}')) {
                    return null;
                }
            }
            return [
                'label' => $item['name'], 'url' => route($name),
                'group' => $catalog->categoryFor($slug),
            ];
        })->filter()->unique('url')->groupBy('group');
    }
}
