<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleManagementController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.settings.modules.index', [
            'modules' => Module::with('parent')->orderBy('sort_order')->orderBy('name')->get(),
            'parentModules' => Module::orderBy('name')->get(),
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
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:modules,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'icon' => ['nullable', 'string', 'max:255'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'route_prefix' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:modules,id'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->normalizedRole() === 'admin', 403);
    }
}
