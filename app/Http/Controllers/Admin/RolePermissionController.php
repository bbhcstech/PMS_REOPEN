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

        $role = strtolower($request->query('role', 'manager'));
        if (! in_array($role, $this->roles, true)) {
            $role = 'manager';
        }

        $staffUsers = \App\Models\User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'user'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.settings.role-permissions.index', [
            'roles' => $this->roles,
            'role' => $role,
            'permissions' => $this->permissions,
            'modules' => Module::with('parent')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'savedPermissions' => RolePermission::where('role', $role)->get()->keyBy('module_id'),
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
        
        // Query the valid module IDs currently present in the tenant modules table
        $tenantModuleIds = \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->pluck('id')->all();

        foreach ($tenantModuleIds as $moduleId) {
            $modulePermissions = $submitted[$moduleId] ?? [];
            RolePermission::updateOrCreate(
                ['role' => $role, 'module_id' => $moduleId],
                collect($this->permissions)
                    ->mapWithKeys(fn ($permission) => ['can_' . $permission => in_array($permission, $modulePermissions, true)])
                    ->all()
            );
        }

        return redirect()->route('admin.role-permissions.index', ['role' => $role])
            ->with('success', 'Role permissions saved successfully.');
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
