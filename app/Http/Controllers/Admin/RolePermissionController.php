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

    private function companyId(): int
    {
        $companyId = \App\Services\TenantScope::companyId();
        abort_unless($companyId, 403, 'Select a valid company workspace before managing permissions.');
        return $companyId;
    }

    private function companyRoles(): array
    {
        $roles = array_combine($this->roles, array_map('ucfirst', $this->roles));
        $companyId = $this->companyId();
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
        $companyId = $this->companyId();
        abort_unless(preg_match('/^staff:([1-9][0-9]*):([1-9][0-9]*)$/', $role, $matches)
            && (int) $matches[1] === $companyId, 403, 'Role does not belong to this company.');
        return \App\Models\CompanyStaffRole::where('company_id', $companyId)
            ->findOrFail((int) $matches[2])->access_role;
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();


        $roleLabels = $this->companyRoles();
        $role = strtolower($request->query('role', 'hr'));
        if (str_starts_with($role, 'staff:')) {
            abort_unless(array_key_exists($role, $roleLabels), 403, 'Role does not belong to this company.');
        }
        if (! array_key_exists($role, $roleLabels)) {
            $role = 'hr';
        }

        $accessRole = $this->accessRole($role);
        $modules = $this->companyModules();

        $savedPermissions = RolePermission::where('role', $role)->get()->keyBy('module_id');
        $hasCustomSaved = $savedPermissions->isNotEmpty();
        $payrollModule = Module::where('slug', 'payroll')->where('is_active', true)->first();

        // Compute effective permissions for the view so previously granted or default permissions are pre-checked
        $effectivePermissions = [];
        foreach ($modules as $module) {
            $saved = $savedPermissions->get($module->id);
            // Display legacy parent grants as checked; otherwise saving the editor
            // silently turns inherited access into an explicit child denial.
            $inherited = null;
            $inheritedSlug = config('workspace_modules.'.$module->slug.'.inherits');
            if (!$saved && $inheritedSlug) {
                $parentModule = Module::where('slug', $inheritedSlug)->where('is_active', true)->first();
                $inherited = $parentModule ? $savedPermissions->get($parentModule->id) : null;
            }
            if (!$saved && $payrollModule && (str_starts_with($module->slug, 'payroll-')
                || in_array($module->slug, ['payslips', 'salary-structures', 'employee-salary', 'formula-builder',
                    'deduction-rules', 'bonus-rules', 'tax-rules', 'overtime-rules'], true))) {
                $inherited = $savedPermissions->get($payrollModule->id);
            }
            $defaultPerms = $this->getDefaultPermissionsForRole($accessRole, $module->slug);

            foreach ($this->permissions as $perm) {
                if ($role === 'admin') {
                    $effectivePermissions[$module->id][$perm] = true;
                } elseif ($hasCustomSaved) {
                    $effectivePermissions[$module->id][$perm] = $saved ? (bool) $saved->{'can_' . $perm}
                        : ($inherited ? (bool) $inherited->{'can_' . $perm} : false);
                } else {
                    $effectivePermissions[$module->id][$perm] = in_array($perm, $defaultPerms, true);
                }
            }
        }

        $staffUsers = \App\Models\User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'user'])
            ->where('company_id', $this->companyId())
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


        $role = strtolower($request->input('role', ''));
        abort_unless(array_key_exists($role, $this->companyRoles()), 422);

        $validated = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string', 'in:' . implode(',', $this->permissions)],
        ]);
        $submitted = $validated['permissions'] ?? [];

        // Save only the same company-provided modules shown in Module Management.
        $visibleModuleIds = $this->companyModules()->pluck('id')->all();

        DB::connection('tenant')->transaction(function () use ($visibleModuleIds, $submitted, $role) {
            foreach ($visibleModuleIds as $moduleId) {
                $modulePermissions = $submitted[$moduleId] ?? [];

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
    private function companyModules(): \Illuminate\Support\Collection
    {
        $company = app(\App\Services\CompanyContext::class)->current();
        abort_unless($company, 503, 'Company module configuration is unavailable.');
        return app(\App\Services\AdminWorkspaceModules::class)->companyModules($company);
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check() || ($user && in_array($user->normalizedRole(), ['admin', 'superadmin'], true));
        abort_unless($isAdmin, 403);
        $this->companyId();
    }
}
