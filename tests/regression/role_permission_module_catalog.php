<?php

require __DIR__.'/payroll_module_management.php';

use App\Http\Controllers\Admin\{ModuleManagementController, RolePermissionController};
use App\Models\{RolePermission, User, WorkspaceModule};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, Schema};

if (!Schema::hasTable('users')) Schema::create('users', function ($table) {
    $table->id(); $table->integer('company_id'); $table->string('name');
    $table->string('email'); $table->string('role'); $table->softDeletes();
});
Auth::guard('web')->setUser(new User(['role' => 'admin', 'company_id' => 1]));
$expected = (new ModuleManagementController)->index()->getData()['modules'];
$controller = new RolePermissionController;
$hidden = WorkspaceModule::firstOrCreate(['slug' => 'legacy-unlisted'], ['name' => 'Legacy Unlisted', 'is_active' => true]);
$labels = $expected->mapWithKeys(fn ($module) => [$module->id => $module->sidebar_name])->all();
foreach (['hr', 'manager', 'employee', $staffRole->permissionKey()] as $role) {
    $data = $controller->index(Request::create('/settings/roles', 'GET', ['role' => $role]))->getData();
    $assert($data['modules']->pluck('id')->all() === $expected->pluck('id')->all(), 'Module lists differ for '.$role);
    $assert($data['modules']->mapWithKeys(fn ($module) => [$module->id => $module->sidebar_name])->all() === $labels, 'Module labels differ for '.$role);
    $assert(!$data['modules']->contains('id', $hidden->id), 'Extra legacy module shown for '.$role);
}
$disabled = $expected->firstWhere('slug', 'reports-finance');
$assert(!$disabled->is_active && $data['modules']->contains('id', $disabled->id), 'Disabled Module Management entry disappeared from permissions');
// The editor must preserve legacy Work/Projects grants exactly as authorization resolves them.
$orders = $expected->firstWhere('slug', 'orders');
$work = WorkspaceModule::firstOrCreate(['slug' => 'work'], ['name' => 'Work', 'is_active' => true]);
$projects = WorkspaceModule::firstOrCreate(['slug' => 'projects'], ['name' => 'Projects', 'is_active' => true]);
RolePermission::where('role', 'manager')->delete();
RolePermission::create(['role' => 'manager', 'module_id' => $work->id, 'can_view' => true]);
RolePermission::create(['role' => 'manager', 'module_id' => $projects->id, 'can_export' => true]);
$managerData = $controller->index(Request::create('/permissions', 'GET', ['role' => 'manager']))->getData();
$assert($managerData['effectivePermissions'][$orders->id]['view'] && $managerData['effectivePermissions'][$orders->id]['export'], 'Editor lost legacy Work/Projects actions');
RolePermission::create(['role' => 'manager', 'module_id' => $orders->id, 'can_view' => false]);
$managerData = $controller->index(Request::create('/permissions', 'GET', ['role' => 'manager']))->getData();
$assert(!$managerData['effectivePermissions'][$orders->id]['view'] && !$managerData['effectivePermissions'][$orders->id]['export'], 'Editor ignored an explicit child denial');
// Fresh registry rows lack route prefixes; their resource actions must still be protected.
$middleware = new App\Http\Middleware\EnsureModuleAccess;
foreach (['products.store' => 'products', 'orders.destroy' => 'orders', 'projects.update' => 'projects', 'admin.settings.email' => 'email-settings'] as $route => $slug) {
    $assert($middleware->moduleForRoute($route)?->slug === $slug, 'Module action not protected: '.$route);
}
WorkspaceModule::where('id', $orders->id)->update(['route_prefix' => null]);
$decision = RolePermission::where('role', 'manager')->where('module_id', $orders->id)->firstOrFail();
foreach ([['GET', 'orders.index', 'view'], ['POST', 'orders.store', 'create'], ['GET', 'orders.edit', 'edit'], ['DELETE', 'orders.destroy', 'delete'], ['POST', 'orders.approve', 'approve'], ['GET', 'orders.export', 'export'], ['POST', 'orders.assign', 'assign']] as [$method, $route, $action]) {
    $actor = new User(['role' => 'manager', 'company_id' => 1]);
    $assert($run($actor, $method, $route)->getStatusCode() === 403, 'Unchecked action was allowed: '.$action);
    $decision->update(['can_'.$action => true]);
    $assert($run($actor, $method, $route)->getStatusCode() === 200, 'Checked action was denied: '.$action);
    $decision->update(['can_'.$action => false]);
}
Auth::guard('web')->setUser(new User(['role' => 'admin', 'company_id' => 1]));
RolePermission::updateOrCreate(['role' => 'hr', 'module_id' => $hidden->id], ['can_view' => true]);
$shown = $expected->first();
$request = Request::create('/settings/roles', 'POST', [
    'role' => 'hr', 'permissions' => [$shown->id => ['view', 'edit'], $hidden->id => ['delete']],
]);
$request->headers->set('Accept', 'application/json');
$assert($controller->update($request)->getStatusCode() === 200, 'Permission save failed');
$saved = RolePermission::where('role', 'hr')->where('module_id', $shown->id)->firstOrFail();
$assert($saved->can_view && $saved->can_edit, 'Visible module actions did not save');
$savedHidden = RolePermission::where('role', 'hr')->where('module_id', $hidden->id)->firstOrFail();
$assert($savedHidden->can_view && !$savedHidden->can_delete, 'Unlisted module permissions changed');
$source = file_get_contents(resource_path('views/admin/settings/role-permissions/index.blade.php'));
token_get_all(app('blade.compiler')->compileString($source), TOKEN_PARSE);
$assert(str_contains($source, '$module->sidebar_name ?? $module->name'), 'Permission names do not use Module Management labels');
$renderSource = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', $source);
$html = Blade::render($renderSource."\n@yield('content')", $data);
foreach (['permissionCategoryFilter', 'permissionSaveStatus', 'Disabled in Module Management', 'Finance Report', 'Salary Structure'] as $label) {
    $assert(str_contains($html, $label), 'Rendered role editor missing '.$label);
}
$assert(substr_count($html, 'class="perm-checkbox action-checkbox"') === $data['modules']->count() * 7, 'A module is missing action checkboxes');
echo "PASS: Role & Permission exactly matches Module Management for built-in/custom roles, names and IDs, includes disabled entries, excludes legacy extras, and saves only listed modules.\n";
