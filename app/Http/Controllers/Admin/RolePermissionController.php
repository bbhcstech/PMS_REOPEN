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
    private array $roles = ['admin', 'manager', 'hr', 'employee'];
    private array $permissions = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $role = strtolower($request->query('role', 'manager'));
        if (! in_array($role, $this->roles, true)) {
            $role = 'manager';
        }

        return view('admin.settings.role-permissions.index', [
            'roles' => $this->roles,
            'role' => $role,
            'permissions' => $this->permissions,
            'modules' => Module::with('parent')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'savedPermissions' => RolePermission::where('role', $role)->get()->keyBy('module_id'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $role = strtolower($request->input('role', ''));
        abort_unless(in_array($role, $this->roles, true), 422);

        $submitted = $request->input('permissions', []);
        foreach (Module::pluck('id') as $moduleId) {
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

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->normalizedRole() === 'admin', 403);
    }
}
