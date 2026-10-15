<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies Admin > Roles & Permissions to every signed-in web route, including the route groups
 * that are not wrapped in "module.access" (events, community, recruitment, appraisal, lead
 * contacts, documents, settings pages, company assistant, ...).
 *
 * A request is refused only when the admin has saved the route's module as OFF for the user's
 * role (company staff roles like "Associate Manager" included). Admins and modules that were
 * never configured for the role keep their current behaviour.
 */
class EnforceRolePermissionToggles
{
    private const ALWAYS_ALLOWED = ['home', 'login', 'logout', 'logout.get', 'dashboard', 'profile.edit', 'profile.update'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $route = $request->route();
        if (! $user || ! $route || ! method_exists($user, 'moduleToggleAllows')
            || \Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return $next($request);
        }

        $routeName = (string) $route->getName();
        if ($routeName === '' || in_array($routeName, self::ALWAYS_ALLOWED, true)) {
            return $next($request);
        }

        $isAdmin = in_array($user->normalizedRole(), ['admin', 'superadmin'], true);

        // 1. Super Admin "Manage Features" applies to every company user, admins included, on every
        //    route of the feature (not only its sidebar page).
        if (! $this->featureAvailable($user, $routeName, $isAdmin)) {
            return $this->deny($request, 'This feature is not available for your company.');
        }

        // 2. Role permission toggles (company admins manage them, so they are not restricted here).
        // Routes guarded by module.access already apply the full permission check.
        if ($isAdmin || in_array('module.access', $route->gatherMiddleware(), true)) {
            return $next($request);
        }

        try {
            $module = app(EnsureModuleAccess::class)->moduleForRoute($routeName);
            if (! $module || ! $module->slug) {
                return $next($request);
            }
            $permission = EnsureModuleAccess::permissionForRequest($request, $routeName);
            $allowed = $user->moduleToggleAllows($module->slug, $permission);
        } catch (\Throwable $e) {
            report($e);
            return $next($request);
        }

        if ($allowed) {
            return $next($request);
        }

        return $this->deny($request, 'You do not have permission to access this module.');
    }

    /** False only when every registry feature the route belongs to is switched off for the company. */
    private function featureAvailable($user, string $routeName, bool $isAdmin): bool
    {
        // Administrators must always be able to repair their company's access settings.
        if ($isAdmin && (str_starts_with($routeName, 'admin.modules.') || str_starts_with($routeName, 'admin.role-permissions.'))) {
            return true;
        }
        try {
            $features = array_filter(
                app(\App\Services\AdminWorkspaceModules::class)->featuresForRoute($routeName),
                // Landing pages (e.g. dashboard) stay reachable so a denied request always has somewhere to go.
                fn ($slug) => ! in_array(config('workspace_modules.'.$slug.'.route'), self::ALWAYS_ALLOWED, true)
            );
            if (! $features) {
                return true;
            }
            foreach ($features as $slug) {
                if ($user->featureEnabled($slug)) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    private function deny(Request $request, string $message): Response
    {
        // Page navigations go back to the dashboard; background/API calls get a plain 403 (no flash message).
        $isNavigation = $request->header('Sec-Fetch-Mode') === 'navigate'
            || (! $request->hasHeader('Sec-Fetch-Mode') && $request->isMethod('GET') && ! $request->ajax() && ! $request->expectsJson());
        if (! $isNavigation) {
            return response()->json(['error' => $message], 403);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }
}
