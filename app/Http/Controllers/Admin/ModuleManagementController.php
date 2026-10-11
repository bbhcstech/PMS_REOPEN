<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkspaceModule as Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleManagementController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        $company = app(\App\Services\CompanyContext::class)->current();
        abort_unless($company, 503, 'Company module configuration is unavailable.');
        $modules = app(\App\Services\AdminWorkspaceModules::class)->companyModules($company);

        return view('admin.settings.modules.index', [
            'modules' => $modules,
            'parentModules' => $modules->sortBy('name')->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        Module::create($data);

        return back()->with('success', 'Module created successfully.');
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validated($request, $module->id);
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $module->update($data);

        return back()->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($module->is_core) {
            return back()->withErrors(['module' => 'Core modules cannot be deleted. Deactivate them instead.']);
        }

        $module->delete();

        return back()->with('success', 'Module deleted successfully.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name')))]);
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenant.modules,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'icon' => ['nullable', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'route_prefix' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:tenant.modules,id', function ($attribute, $value, $fail) use ($ignoreId) {
                $seen = $ignoreId ? [$ignoreId] : [];
                while ($value) {
                    if (in_array((int) $value, $seen, true)) {
                        $fail('A module cannot belong to itself or one of its descendants.');
                        return;
                    }
                    $seen[] = (int) $value;
                    $value = Module::find($value)?->parent_id;
                }
            }],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check() || ($user && in_array($user->normalizedRole(), ['admin', 'superadmin'], true));
        abort_unless($isAdmin, 403);
    }
}
