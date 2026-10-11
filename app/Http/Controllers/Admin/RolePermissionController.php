<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkspaceModule as Module;
use App\Models\RolePermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    private array $roles = ['manager', 'hr', 'employee'];
    private array $permissions = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];

    private function companyRoles(): array
    {
        $roles = array_combine($this->roles, array_map('ucfirst', $this->roles));
        $companyId = auth()->user()?->company_id;
        if ($companyId && \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('company_staff_roles')) {
            foreach (\App\Models\CompanyStaffRole::where('company_id', $companyId)->orderBy('name')->get() as $staffRole) {
                $roles[$staffRole->permissionKey()] = $staffRole->name;
            }
        }
        return $roles;
    }

    private function accessRole(string $role): string
    {
        if (in_array($role, $this->roles, true)) return $role;
        return \App\Models\CompanyStaffRole::where('company_id', auth()->user()->company_id)
            ->findOrFail((int) substr($role, strrpos($role, ':') + 1))->access_role;
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $this->ensureTenantModulesSynced();

        $roleLabels = $this->companyRoles();
        $role = strtolower($request->query('role', 'hr'));
        if (! array_key_exists($role, $roleLabels)) {
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

        $accessRole = $this->accessRole($role);
        $excludedSlugs = $excludedSlugsByRole[$accessRole] ?? [];
        $modules = app(\App\Services\AdminWorkspaceModules::class)->permissionMatrixModules()
            ->reject(fn ($module) => in_array($module->slug, $excludedSlugs, true))->values();

        $savedPermissions = RolePermission::where('role', $role)->get()->keyBy('module_id');
        $hasCustomSaved = $savedPermissions->isNotEmpty();

        // Compute effective permissions for the view so previously granted or default permissions are pre-checked
        $effectivePermissions = [];
        foreach ($modules as $module) {
            $saved = $savedPermissions->get($module->id);
            $defaultPerms = $this->getDefaultPermissionsForRole($accessRole, $module->slug);

            foreach ($this->permissions as $perm) {
                if ($role === 'admin') {
                    $effectivePermissions[$module->id][$perm] = true;
                } elseif ($hasCustomSaved) {
                    $effectivePermissions[$module->id][$perm] = $saved ? (bool) $saved->{'can_' . $perm} : false;
                } else {
                    $effectivePermissions[$module->id][$perm] = in_array($perm, $defaultPerms, true);
                }
            }
        }

        $staffUsers = \App\Models\User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'user'])
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.settings.role-permissions.index', [
            'roles' => array_keys($roleLabels),
            'roleLabels' => $roleLabels,
            'role' => $role,
            'permissions' => $this->permissions,
            'modules' => $modules,
            'savedPermissions' => $savedPermissions,
            'effectivePermissions' => $effectivePermissions,
            'staffUsers' => $staffUsers,
            'defaultPermissions' => $modules->mapWithKeys(fn ($module) => [$module->id => $this->getDefaultPermissionsForRole($accessRole, $module->slug)])->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeAdmin();

        $this->ensureTenantModulesSynced();

        $role = strtolower($request->input('role', ''));
        abort_unless(array_key_exists($role, $this->companyRoles()), 422);

        $validated = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string', 'in:' . implode(',', $this->permissions)],
        ]);
        $submitted = $validated['permissions'] ?? [];

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
        $excludedSlugs = $excludedSlugsByRole[$this->accessRole($role)] ?? [];
        $excludedModuleIds = Module::whereIn('slug', $excludedSlugs)->pluck('id')->all();

        // Query the valid module IDs currently present in the tenant modules table
        $tenantModuleIds = \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->pluck('id')->all();
        $visibleModuleIds = app(\App\Services\AdminWorkspaceModules::class)->permissionMatrixModules()->pluck('id')->all();

        DB::connection('tenant')->transaction(function () use ($tenantModuleIds, $excludedModuleIds, $visibleModuleIds, $submitted, $role) {
            foreach ($tenantModuleIds as $moduleId) {
                // A hidden registry row must not be revoked just because it is absent from the form.
                if (!in_array($moduleId, $visibleModuleIds, true) && !in_array($moduleId, $excludedModuleIds, true)) continue;
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
        });

        $message = 'Role permissions saved successfully for ' . $this->companyRoles()[$role] . '.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'role' => $role]);
        }

        return redirect()->route('admin.role-permissions.index', ['role' => $role])
            ->with('success', $message);
    }

    public function getDefaultPermissionsForRole(string $role, string $slug): array
    {
        return app(\App\Services\RolePermissionDefaults::class)->getDefaultPermissionsForRole($role, $slug);
    }
    /** Import new platform modules without changing company configuration or IDs. */
    private function ensureTenantModulesSynced(): void
    {
        $tenant = DB::connection('tenant');
        if (! \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('modules')) {
            abort(503, 'Company module configuration is unavailable.');
        }
        $centralModules = DB::connection('central')->table('modules')->get();
        $tenant->transaction(function () use ($tenant, $centralModules) {
            $inserted = [];
            foreach ($centralModules as $module) {
                if ($tenant->table('modules')->where('slug', $module->slug)->exists()) continue;
                $data = (array) $module;
                unset($data['id']);
                $data['parent_id'] = null;
                $tenant->table('modules')->insert($data);
                $inserted[] = $module->slug;
            }
            $ids = $tenant->table('modules')->pluck('id', 'slug');
            $centralById = $centralModules->keyBy('id');
            foreach ($centralModules as $module) {
                if (!in_array($module->slug, $inserted, true) || !$module->parent_id) continue;
                $parent = $centralById->get($module->parent_id);
                $tenant->table('modules')->where('slug', $module->slug)
                    ->update(['parent_id' => $parent ? $ids->get($parent->slug) : null]);
            }
        });
    }
    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check() || ($user && in_array($user->normalizedRole(), ['admin', 'superadmin'], true));
        abort_unless($isAdmin, 403);
    }
}
