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

        // Core authentication & profile routes are always accessible to avoid redirect loops
        if (in_array($routeName, ['home', 'login', 'logout', 'profile.edit', 'profile.update', 'dashboard'], true)) {
            return $next($request);
        }

        $module = $this->moduleForRoute($routeName);
        $moduleSlug = $module ? $module->slug : null;

        if ($moduleSlug) {
            $company = app(\App\Services\CompanyContext::class)->current();
            if (! $company && $user->company_id) {
                try {
                    $company = \App\Models\Company::find($user->company_id)
                        ?? \App\Models\Central\Company::on('central')->find($user->company_id);
                } catch (\Throwable $e) {}
            }

            // 1. Company subscription / Super Admin feature check: MUST BE CHECKED FOR ALL USERS
            if ($company && method_exists($company, 'hasFeature') && ! $company->hasFeature($moduleSlug)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => "Feature '{$moduleSlug}' is disabled for your company by the platform administrator.",
                    ], 403);
                }

                return redirect()->route('dashboard')
                    ->with('error', "Access Denied: The feature '{$moduleSlug}' is disabled for your organization.");
            }

            // 2. Platform Admin / Tenant Admin has unrestricted role-based permissions on all ENABLED features
            if (in_array(strtolower((string) $user->role), ['admin', 'administrator', 'superadmin'], true)) {
                return $next($request);
            }

            // 3. Granular Role-based permissions for other roles (HR, Manager, Employee, etc.)
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

        $aliases = [
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
        ];

        foreach ($aliases as $prefix => $slug) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix . '.')) {
                $foundModule = Module::where('slug', $slug)->where('is_active', true)->first();
                if ($foundModule) {
                    return $foundModule;
                }
                return new Module(['slug' => $slug, 'name' => \Illuminate\Support\Str::headline($slug)]);
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
