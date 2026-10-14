<?php

require __DIR__.'/company_provided_modules.php';

use App\Models\{Company, Module, RolePermission, User, WorkspaceModule};
use App\Services\{AdminWorkspaceModules, CompanyContext};
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};

$service = app(AdminWorkspaceModules::class);
$migration = require database_path('migrations/2026_10_12_000003_register_sidebar_submodules.php');
$migration->up();
$registryCount = Module::count();
$migration->up();
$assert(Module::count() === $registryCount, 'Migration created duplicate modules');
$assert(! Module::where('slug', 'notifications')->first()->is_active, 'Migration reactivated a platform module');

Schema::connection('central')->create('company_modules', function ($t) {
    $t->id(); $t->integer('company_id'); $t->integer('module_id'); $t->boolean('is_enabled');
});
Schema::connection('central')->create('plan_modules', function ($t) {
    $t->id(); $t->integer('plan_id'); $t->integer('module_id');
});
$reports = Module::where('slug', 'reports')->firstOrFail();
foreach (config('workspace_modules') as $slug => $item) {
    if ($item['route']) $assert(Illuminate\Support\Facades\Route::has($item['route']), 'Sidebar module points to missing route: '.$slug);
}
$sidebar = file_get_contents(resource_path('views/admin/layout/manu.blade.php'));
$sidebar = substr($sidebar, strpos($sidebar, '@if($canSeeModule(\'dashboard\'))'));
$sidebar = substr($sidebar, 0, strpos($sidebar, '<!-- ================== SETTINGS SECTION'));
preg_match_all('/href="\{\{ route\(\'([^\']+)\'/', $sidebar, $links);
$configuredRoutes = array_column(config('workspace_modules'), 'route');
foreach (config('workspace_modules') as $item) {
    $configuredRoutes = array_merge($configuredRoutes, $item['route_names'] ?? []);
}
foreach (array_unique($links[1]) as $route) $assert(in_array($route, $configuredRoutes, true), 'Current sidebar entry missing from module catalog: '.$route);
foreach (['admin.settings.parent-departments.index' => 'department-parent', 'admin.settings.departments.index' => 'department-sub', 'admin.settings.designations.index' => 'designations'] as $route => $slug) {
    $assert($service->featuresForRoute($route) === [$slug], 'Settings shortcut has a different module identity: '.$route);
    $assert($service->subdivisionForRoute($route) === $slug, 'Settings shortcut has a different permission identity: '.$route);
}
$assert($service->categoryFor('reports-attendance') === 'REPORTS & ANALYTICS', 'Report was categorized as HR');
$assert($service->categoryFor('attendance-settings') === 'ADMINISTRATION & SETTINGS', 'Settings page was categorized as HR');
DB::connection('central')->table('plan_modules')->insert(['plan_id' => 99, 'module_id' => $reports->id]);
$company = new Company;
$company->setConnection('central');
$company->forceFill(['id' => 1, 'status' => 'active']);
$company->setRelation('activeSubscription', (object) ['plan_id' => 99, 'plan' => (object) ['slug' => 'gold']]);
$secondCompany = clone $company;
$secondCompany->id = 2;
DB::connection('central')->table('company_modules')->insert(['company_id' => 2, 'module_id' => $reports->id, 'is_enabled' => false]);

$modules = $service->companyModules($company);
$reportSlugs = array_keys(array_filter(config('workspace_modules'), fn ($item) => $item['parent'] === 'reports'));
$positions = $modules->pluck('slug')->all();
$reportPosition = array_search('reports', $positions, true);
$assert(array_slice($positions, $reportPosition + 1, count($reportSlugs)) === $reportSlugs, 'Reports are not grouped in sidebar order');
foreach ($reportSlugs as $slug) {
    $module = $modules->firstWhere('slug', $slug);
    $assert($module && $module->parent->slug === 'reports' && $module->sidebar_depth === 1, 'Report hierarchy missing: '.$slug);
    $assert(WorkspaceModule::find($module->id)->parent_id === $module->parent->id, 'Parent ID was not company-local');
    $assert($company->hasFeature($slug), 'Existing Reports plan did not include its child: '.$slug);
    $assert(! $secondCompany->hasFeature($slug), 'Second company inherited first company entitlement');
}
$assert(! $service->companyModules($secondCompany)->contains(fn ($module) => str_starts_with($module->slug, 'reports-')), 'Revoked reports remained in company list');
$finance = $modules->firstWhere('slug', 'reports-finance');
DB::connection('central')->table('company_modules')->insert(['company_id' => 1, 'module_id' => Module::where('slug', 'reports-finance')->value('id'), 'is_enabled' => false]);
app(App\Services\FeatureEntitlements::class)->forget(1);
$assert(! $company->hasFeature('reports-finance') && $company->hasFeature('reports-sales'), 'Individual platform revocation affected sibling reports');
DB::connection('central')->table('company_modules')->where('company_id', 1)->delete();
app(App\Services\FeatureEntitlements::class)->forget(1);

$app->instance(CompanyContext::class, new class($company) extends CompanyContext {
    public function __construct(private $company) {}
    public function current() { return $this->company; }
});
$reportModule = $modules->firstWhere('slug', 'reports');
RolePermission::updateOrCreate(['role' => 'hr', 'module_id' => $reportModule->id], ['can_view' => true, 'can_export' => false]);
$hr = new User(['company_id' => 1, 'role' => 'hr']);
$assert($hr->canViewModule('reports-finance') && ! $hr->hasModulePermission('reports-finance', 'export'), 'Legacy report permission/action inheritance changed');
RolePermission::create(['role' => 'hr', 'module_id' => $finance->id, 'can_view' => false]);
$assert(! $hr->canViewModule('reports-finance') && $hr->canViewModule('reports-sales'), 'Explicit child denial changed sibling permissions');
$assert($run($hr, 'GET', 'reports.finance')->getStatusCode() === 403, 'Denied report route remained accessible');
$attendance = $modules->firstWhere('slug', 'reports-attendance');
RolePermission::create(['role' => 'hr', 'module_id' => $attendance->id, 'can_view' => false]);
$assert($run($hr, 'GET', 'attendance.report')->getStatusCode() === 403, 'Broad attendance alias bypassed report permission');
foreach (['manager', 'employee', $staffRole->permissionKey()] as $role) {
    RolePermission::updateOrCreate(['role' => $role, 'module_id' => $reportModule->id], ['can_view' => true]);
    $roleUser = new User(['company_id' => 1, 'role' => $role === $staffRole->permissionKey() ? 'manager' : $role]);
    if ($role === $staffRole->permissionKey()) $roleUser->company_staff_role_id = $staffRole->id;
    $assert($roleUser->canViewModule('reports-sales'), 'Report inheritance failed for '.$role);
    $salesModule = $modules->firstWhere('slug', 'reports-sales');
    RolePermission::create(['role' => $role, 'module_id' => $salesModule->id, 'can_view' => false]);
    $assert(! $roleUser->canViewModule('reports-sales') && $roleUser->canViewModule('reports-deal'), 'Child permission was not isolated for '.$role);
}
RolePermission::where('role', 'hr')->where('module_id', $attendance->id)->update(['can_view' => true]);
WorkspaceModule::where('id', $reportModule->id)->update(['is_active' => false]);
$assert(! $hr->canViewModule('reports-attendance'), 'Explicit child grant bypassed disabled parent');
try {
    $run(new User(['company_id' => 1, 'role' => 'admin']), 'GET', 'attendance.report');
    throw new RuntimeException('Administrator bypassed disabled report parent');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $assert($e->getStatusCode() === 403, 'Wrong disabled report parent status');
}
WorkspaceModule::where('id', $reportModule->id)->update(['is_active' => true]);
$matrixSlugs = $service->permissionMatrixModules()->pluck('slug');
foreach ($reportSlugs as $slug) $assert($matrixSlugs->contains($slug), 'Report missing from permission editor: '.$slug);
$before = Module::count();
WorkspaceModule::where('id', $finance->id)->update(['is_active' => false]);
$assert(! $service->companyModules($company)->firstWhere('slug', 'reports-finance')->is_active, 'Company-local disabled state was reset');
$assert(Module::count() === $before && Module::where('slug', 'reports-finance')->first()->is_active, 'Company list changed central registry');

Auth::guard('web')->setUser(new User(['company_id' => 1, 'role' => 'admin']));
$view = (new App\Http\Controllers\Admin\ModuleManagementController)->index();
$data = $view->getData();
$assert($data['parentModuleCount'] < $data['modules']->count(), 'Parent metric counts all modules');
Schema::connection('tenant')->create('users', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('email'); $t->string('role');
});
$permissions = (new App\Http\Controllers\Admin\RolePermissionController)->index(Illuminate\Http\Request::create('/permissions', 'GET', ['role' => 'hr']))->getData();
$salesId = $modules->firstWhere('slug', 'reports-sales')->id;
$assert($permissions['effectivePermissions'][$salesId]['view'], 'Permission editor did not check inherited Reports access');
$assert(! $permissions['effectivePermissions'][$attendance->id]['export'], 'Permission editor granted unintended inherited export');
$source = file_get_contents(resource_path('views/admin/settings/modules/index.blade.php'));
$source = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', $source);
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
$html = Blade::render($source."\n@yield('content')", $data);
foreach (['Finance Report', 'Sales Report', 'Deal Report', 'Submodule'] as $label) $assert(str_contains($html, $label), 'Module UI is missing '.$label);
foreach (['admin/layout/manu', 'admin/settings/modules/index'] as $viewName) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/'.$viewName.'.blade.php'))), TOKEN_PARSE);
}
echo "PASS: sidebar report hierarchy and labels, migration idempotency, company entitlements/isolation, inherited and explicit permissions, exact report route authorization, local status, metrics and rendered module UI.\n";
