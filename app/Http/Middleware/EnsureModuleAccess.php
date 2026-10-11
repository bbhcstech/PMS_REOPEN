<?php

namespace App\Http\Middleware;

use App\Models\WorkspaceModule as Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        if (
            \Illuminate\Support\Facades\Auth::guard('super_admin')->check() ||
            $request->is('super-admin*') ||
            $request->is('superadmin*') ||
            (auth()->check() && in_array(strtolower((string)(auth()->user()->role ?? '')), ['superadmin', 'super-admin', 'super_admin'], true))
        ) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();

        // Administrators must always be able to repair their company's access settings.
        if (in_array($user->normalizedRole(), ['admin', 'superadmin'], true)
            && (str_starts_with($routeName, 'admin.modules.') || str_starts_with($routeName, 'admin.role-permissions.'))) {
            return $next($request);
        }

        // Core authentication & profile routes are always accessible to avoid redirect loops
        if (in_array($routeName, ['home', 'login', 'logout', 'profile.edit', 'profile.update', 'dashboard'], true)) {
            return $next($request);
        }

        $module = $this->moduleForRoute($routeName);
        $moduleSlug = $module ? $module->slug : null;

        if ($moduleSlug) {
            if ($module->exists && ! $module->is_active) {
                abort(403, 'This module is disabled for your company.');
            }
            $inheritedFeature = config('workspace_modules.'.$moduleSlug.'.inherits');
            if ($inheritedFeature && Module::where('slug', $inheritedFeature)->where('is_active', false)->exists()) {
                abort(403, 'The parent module is disabled for your company.');
            }
            $company = app(\App\Services\CompanyContext::class)->current();
            if (! $company && $user->company_id) {
                try {
                    $company = \App\Models\Company::find($user->company_id)
                        ?? \App\Models\Central\Company::on('central')->find($user->company_id);
                } catch (\Throwable $e) {}
            }

            // 1. Company subscription / Super Admin feature check: MUST BE CHECKED FOR ALL USERS
            if ($user->company_id && ! $company) {
                abort(503, 'Company access configuration is temporarily unavailable.');
            }
            if ($company && method_exists($company, 'hasFeature') && ! $company->hasFeature($moduleSlug)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => "Feature '{$moduleSlug}' is disabled for your company by the platform administrator.",
                    ], 403);
                }

                return redirect()->route('dashboard')
                    ->with('error', "Access Denied: The feature '{$moduleSlug}' is disabled for your organization.");
            }

            // Only company administrators retain authority to manage access.
            if (in_array(strtolower((string) $user->role), ['admin', 'administrator', 'superadmin'], true)) {
                return $next($request);
            }

            if ($permission === 'view' && ! $request->isMethodSafe()) {
                $action = strtolower((string) strrchr($routeName, '.'));
                $permission = match (true) {
                    $request->isMethod('DELETE'), str_contains($action, 'destroy'), str_contains($action, 'delete') => 'delete',
                    str_contains($action, 'approve'), str_contains($action, 'reject') => 'approve',
                    str_contains($action, 'export'), str_contains($action, 'download') => 'export',
                    str_contains($action, 'assign') => 'assign',
                    $request->isMethod('PUT'), $request->isMethod('PATCH'), str_contains($action, 'update'), str_contains($action, 'toggle') => 'edit',
                    default => 'create',
                };
            }
            if ($permission === 'view' && preg_match('/\.(create|edit|export|download)(?:-|\.|$)/', $routeName, $match)) {
                $permission = $match[1] === 'download' ? 'export' : $match[1];
            }

            // Apply saved permissions to HR, managers, employees and custom roles.
            if ($module && ! $user->hasModulePermission($module->slug, $permission)) {
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

        // Exact sidebar subdivisions take precedence over broad legacy aliases
        // such as attendance.report -> attendance and admin.leave.* -> leaves.
        foreach (config('workspace_modules', []) as $slug => $item) {
            if ($item['inherits'] && $item['route'] === $routeName) {
                $submodule = Module::where('slug', $slug)->first();
                if ($submodule) return $submodule;
            }
        }

        $aliases = [
            'admin.authority-attendance' => 'attendance',
            'admin.authority-leaves' => 'leaves',
            'users.tasks' => 'tasks',
            'projects.tasks' => 'tasks',
            'projects.timelogs' => 'timelogs',
            'task-timer' => 'tasks',
            'sticky_notes' => 'dashboard',
            'dashboard-timers' => 'timelogs',
            'admin.settings.organization-details' => 'organization-details-settings',
            'admin.settings.business-address' => 'business-address-settings',
            'admin.settings.work-schedule' => 'work-schedule-settings',
            'admin.settings.leave' => 'leave-settings',
            'attendance.settings' => 'attendance-settings',
            'payroll.settings' => 'payroll-settings',
            'admin.settings.recruitment' => 'recruitment-settings',
            'admin.settings.performance' => 'performance-settings',
            'admin.settings.notification' => 'notification-settings',
            'admin.settings.email' => 'email-settings',
            'admin.settings.document' => 'document-settings',
            'admin.settings.security' => 'security-settings',
            'admin.settings.change-password' => 'change-password-settings',
            'admin.role-permissions' => 'role-permissions-settings',
            'admin.settings.localization' => 'localization-settings',
            'admin.settings.terms-policy' => 'terms-policy-settings',
            'settings.company' => 'company-profile-settings',
            'admin.settings.index' => 'settings-dashboard',
            'admin.leave' => 'leaves',
            'admin.leaves' => 'leaves',
            'leaves' => 'leaves',
            'admin.tickets' => 'tickets',
            'tickets' => 'tickets',
            'admin.employees' => 'employees',
            'employees' => 'employees',
        ];

        foreach ($aliases as $prefix => $slug) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix . '.')) {
                $foundModule = Module::where('slug', $slug)->first();
                if ($foundModule) {
                    return $foundModule;
                }
                return new Module(['slug' => $slug, 'name' => \Illuminate\Support\Str::headline($slug)]);
            }
        }

        // Match literal prefixes, choosing the most specific route. This also avoids
        // treating wildcard characters in a configured prefix as SQL patterns.
        return Module::query()->get()
            ->filter(fn ($module) => $module->route_name === $routeName
                || ($module->route_prefix && ($routeName === $module->route_prefix
                    || str_starts_with($routeName, $module->route_prefix . '.'))))
            ->sortByDesc(fn ($module) => $module->route_name === $routeName
                ? PHP_INT_MAX : strlen($module->route_prefix))
            ->first();
    }
}
