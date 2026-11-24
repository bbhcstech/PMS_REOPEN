<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\RolePermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    private array $roles = ['manager', 'hr', 'employee'];
    private array $permissions = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $this->ensureTenantModulesSynced();

        $role = strtolower($request->query('role', 'hr'));
        if (! in_array($role, $this->roles, true)) {
            $role = 'hr';
        }

        // Exclude role-specific management modules per role ("for a particular role that role do not come up under the role")
        $excludedSlugsByRole = [
            'hr' => [
                'hr-management', 'manager-management', 'role-management',
                'permission-management', 'role-permissions-settings', 'module-management',
            ],
            'manager' => [
                'manager-management', 'hr-management', 'role-management',
                'permission-management', 'role-permissions-settings', 'module-management',
            ],
            'employee' => [
                'hr-management', 'manager-management', 'role-management',
                'permission-management', 'user-management', 'role-permissions-settings',
                'module-management', 'settings-dashboard', 'settings',
                'payroll-settings', 'security-settings', 'localization-settings',
                'terms-policy-settings', 'activity-logs', 'system-logs',
            ],
            'admin' => [],
        ];

        $excludedSlugs = $excludedSlugsByRole[$role] ?? [];
        $modules = Module::with('parent')
            ->where('is_active', true)
            ->whereNotIn('slug', $excludedSlugs)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $savedPermissions = RolePermission::where('role', $role)->get()->keyBy('module_id');
        $hasCustomSaved = $savedPermissions->contains(function ($item) {
            return $item->can_view || $item->can_create || $item->can_edit || $item->can_delete || $item->can_approve || $item->can_export || $item->can_assign;
        });

        // Compute effective permissions for the view so previously granted or default permissions are pre-checked
        $effectivePermissions = [];
        foreach ($modules as $module) {
            $saved = $savedPermissions->get($module->id);
            $defaultPerms = $this->getDefaultPermissionsForRole($role, $module->slug);

            foreach ($this->permissions as $perm) {
                if ($role === 'admin') {
                    $effectivePermissions[$module->id][$perm] = true;
                } elseif ($hasCustomSaved && $saved) {
                    $effectivePermissions[$module->id][$perm] = (bool) $saved->{'can_' . $perm};
                } else {
                    $effectivePermissions[$module->id][$perm] = in_array($perm, $defaultPerms, true);
                }
            }
        }

        $staffUsers = \App\Models\User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'user'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.settings.role-permissions.index', [
            'roles' => $this->roles,
            'role' => $role,
            'permissions' => $this->permissions,
            'modules' => $modules,
            'savedPermissions' => $savedPermissions,
            'effectivePermissions' => $effectivePermissions,
            'staffUsers' => $staffUsers,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $this->ensureTenantModulesSynced();

        $role = strtolower($request->input('role', ''));
        abort_unless(in_array($role, $this->roles, true), 422);

        $submitted = $request->input('permissions', []);

        // Excluded modules for this role should not have permissions granted
        $excludedSlugsByRole = [
            'hr' => [
                'hr-management', 'manager-management', 'role-management',
                'permission-management', 'role-permissions-settings', 'module-management',
            ],
            'manager' => [
                'manager-management', 'hr-management', 'role-management',
                'permission-management', 'role-permissions-settings', 'module-management',
            ],
            'employee' => [
                'hr-management', 'manager-management', 'role-management',
                'permission-management', 'user-management', 'role-permissions-settings',
                'module-management', 'settings-dashboard', 'settings',
                'payroll-settings', 'security-settings', 'localization-settings',
                'terms-policy-settings', 'activity-logs', 'system-logs',
            ],
            'admin' => [],
        ];
        $excludedSlugs = $excludedSlugsByRole[$role] ?? [];
        $excludedModuleIds = Module::whereIn('slug', $excludedSlugs)->pluck('id')->all();

        // Query the valid module IDs currently present in the tenant modules table
        $tenantModuleIds = \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->pluck('id')->all();

        foreach ($tenantModuleIds as $moduleId) {
            if (in_array($moduleId, $excludedModuleIds, true)) {
                $modulePermissions = [];
            } else {
                $modulePermissions = $submitted[$moduleId] ?? [];
            }

            RolePermission::updateOrCreate(
                ['role' => $role, 'module_id' => $moduleId],
                collect($this->permissions)
                    ->mapWithKeys(fn ($permission) => ['can_' . $permission => in_array($permission, $modulePermissions, true)])
                    ->all()
            );
        }

        return redirect()->route('admin.role-permissions.index', ['role' => $role])
            ->with('success', 'Role permissions saved successfully for ' . ucfirst($role) . '.');
    }

    public function getDefaultPermissionsForRole(string $role, string $slug): array
    {
        if ($role === 'admin') {
            return $this->permissions;
        }

        $hrFullModules = [
            'dashboard', 'notifications', 'my-documents', 'organization', 'employees',
            'hr-employees', 'departments', 'designations', 'attendance', 'leaves',
            'leave-management', 'holidays', 'recognition', 'awards', 'recruitment',
            'appraisal', 'work', 'projects', 'tasks', 'timesheets', 'timelogs',
            'payroll', 'payroll-architectures', 'payslips', 'salary-structures',
            'payroll-cycles', 'payroll-policies', 'formula-builder', 'deduction-rules',
            'bonus-rules', 'tax-rules', 'overtime-rules', 'payroll-reports', 'expenses',
            'billing', 'reports', 'analytics', 'advanced-reports', 'events', 'community',
            'collaborating-companies', 'clients', 'leads-contacts', 'tickets',
        ];

        $managerFullModules = [
            'dashboard', 'notifications', 'my-documents', 'organization', 'teams',
            'work', 'projects', 'tasks', 'timesheets', 'timelogs', 'attendance',
            'leaves', 'leave-management', 'reports', 'analytics', 'recruitment',
            'appraisal', 'recognition', 'awards', 'events', 'community',
            'collaborating-companies', 'clients', 'leads-contacts', 'tickets',
        ];

        $employeeModules = [
            'dashboard', 'notifications', 'my-documents', 'attendance', 'leaves',
            'leave-management', 'holidays', 'recognition', 'awards', 'work',
            'projects', 'tasks', 'timesheets', 'timelogs', 'payslips', 'events',
            'community',
        ];

        if ($role === 'hr') {
            if (in_array($slug, $hrFullModules, true)) {
                return ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
            }
            return ['view'];
        }

        if ($role === 'manager') {
            if (in_array($slug, $managerFullModules, true)) {
                return ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
            }
            if (in_array($slug, ['employees', 'hr-employees'], true)) {
                return ['view', 'export'];
            }
            return ['view'];
        }

        if ($role === 'employee') {
            if (in_array($slug, ['my-documents', 'timesheets', 'timelogs', 'leaves', 'leave-management', 'attendance', 'tasks'], true)) {
                return ['view', 'create', 'edit'];
            }
            if (in_array($slug, $employeeModules, true)) {
                return ['view'];
            }
            return [];
        }

        return [];
    }

    /**
     * Ensure all central modules are present in the tenant modules table
     * with matching IDs to prevent foreign key constraint violations on role_permissions.
     */
    private function ensureTenantModulesSynced(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('modules')) {
                return;
            }

            $centralModules = \Illuminate\Support\Facades\DB::connection('central')->table('modules')->get();
            $tenantModules = \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->get();
            $tenantModuleIds = $tenantModules->pluck('id')->all();
            $tenantModuleSlugs = $tenantModules->pluck('id', 'slug')->all();

            $missingModules = $centralModules->whereNotIn('id', $tenantModuleIds);

            if ($missingModules->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 0;');
                foreach ($missingModules as $cMod) {
                    if (isset($tenantModuleSlugs[$cMod->slug])) {
                        $oldId = $tenantModuleSlugs[$cMod->slug];
                        \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->where('id', $oldId)->update(['id' => $cMod->id]);
                        \Illuminate\Support\Facades\DB::connection('tenant')->table('role_permissions')->where('module_id', $oldId)->update(['module_id' => $cMod->id]);
                        \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->where('parent_id', $oldId)->update(['parent_id' => $cMod->id]);
                    } else {
                        \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->insert([
                            'id'           => $cMod->id,
                            'name'         => $cMod->name,
                            'slug'         => $cMod->slug,
                            'icon'         => $cMod->icon,
                            'description'  => $cMod->description,
                            'route_prefix' => $cMod->route_prefix,
                            'route_name'   => $cMod->route_name,
                            'parent_id'    => $cMod->parent_id,
                            'is_core'      => $cMod->is_core,
                            'is_active'    => $cMod->is_active,
                            'sort_order'   => $cMod->sort_order,
                            'created_at'   => $cMod->created_at ?: now(),
                            'updated_at'   => $cMod->updated_at ?: now(),
                        ]);
                    }
                }
                \Illuminate\Support\Facades\DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 1;');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Module sync warning in RolePermissionController: ' . $e->getMessage());
        }
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check() || ($user && in_array($user->normalizedRole(), ['admin', 'superadmin'], true));
        abort_unless($isAdmin, 403);
    }
}
