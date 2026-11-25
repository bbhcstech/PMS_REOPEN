<?php

require __DIR__.'/sidebar_module_hierarchy.php';

use App\Models\{Module, RolePermission, User, WorkspaceModule};
use App\Services\AdminWorkspaceModules;
use Illuminate\Support\Facades\{Auth, Blade, DB, Route};

$service = app(AdminWorkspaceModules::class);
$migration = require database_path('migrations/2026_10_12_000004_register_payroll_submodules.php');
$migration->up();
$count = Module::count();
$migration->up();
$assert(Module::count() === $count, 'Payroll registration was not idempotent');
$payroll = Module::where('slug', 'payroll')->firstOrFail();
DB::connection('central')->table('plan_modules')->insert(['plan_id' => 99, 'module_id' => $payroll->id]);
app(App\Services\PlanFeatureEntitlements::class)->forget(99);
$modules = $service->companyModules($company);
$children = ['salary-structures', 'employee-salary', 'payroll-processing', 'payroll-history', 'payslips', 'formula-builder'];
$positions = $modules->pluck('slug')->all();
$assert(array_slice($positions, array_search('payroll', $positions, true) + 1, 6) === $children, 'Payroll child order is incorrect');
$payrollLocal = $modules->firstWhere('slug', 'payroll');
RolePermission::updateOrCreate(['role' => 'hr', 'module_id' => $payrollLocal->id], ['can_view' => true, 'can_create' => true]);
$hr = new User(['company_id' => 1, 'role' => 'hr']);
$matrix = $service->permissionMatrixModules()->pluck('slug');
foreach ($children as $slug) {
    $module = $modules->firstWhere('slug', $slug);
    $assert($module && $module->parent->slug === 'payroll' && $module->sidebar_depth === 1, 'Missing Payroll child: '.$slug);
    $assert($module->category === 'FINANCE & PAYROLL' && $matrix->contains($slug), 'Payroll child missing from permissions/category: '.$slug);
    $assert($company->hasFeature($slug) && $hr->canViewModule($slug), 'Legacy Payroll access did not inherit: '.$slug);
    $config = config('workspace_modules.'.$slug);
    $routes = $config['route_names'] ?? [];
    foreach ($config['route_prefixes'] ?? [] as $prefix) {
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with((string) $route->getName(), $prefix.'.')) $routes[] = $route->getName();
        }
    }
    RolePermission::updateOrCreate(['role' => 'hr', 'module_id' => $module->id], ['can_view' => false, 'can_create' => false]);
    foreach ($routes as $name) {
        $assert(Route::has($name) && $service->subdivisionForRoute($name) === $slug, 'Wrong route identity: '.$name);
        $assert($run($hr, 'GET', $name)->getStatusCode() === 403, 'Explicit denial bypassed: '.$name);
    }
    WorkspaceModule::where('id', $module->id)->update(['is_active' => false]);
    $assert(! (new User(['company_id' => 1, 'role' => 'admin']))->canViewModule($slug), 'Admin bypassed disabled child: '.$slug);
    $assert($hr->canViewModule('payroll'), 'Child disable affected parent');
    WorkspaceModule::where('id', $module->id)->update(['is_active' => true]);
    RolePermission::where('role', 'hr')->where('module_id', $module->id)->delete();
}
Auth::guard('web')->setUser($hr);
$denied = $modules->firstWhere('slug', 'payslips');
RolePermission::create(['role' => 'hr', 'module_id' => $denied->id, 'can_view' => false]);
$html = Blade::render(file_get_contents(resource_path('views/admin/payroll/partials/tabs.blade.php')));
$assert(!str_contains($html, 'href="'.route('payroll.payslips.index').'"'), 'Denied payslips tab is visible');
foreach (['Salary Structure', 'Employee Salary', 'Processing', 'History', 'Formulas & Rules'] as $label) {
    $assert(str_contains($html, $label), 'Sibling tab disappeared: '.$label);
}
$request = Illuminate\Http\Request::create('/payroll/payslips');
$request->setRouteResolver(fn () => (new Illuminate\Routing\Route('GET', 'payroll/payslips', []))->name('payroll.payslips.index'));
$app->instance('request', $request);
$authorize = new ReflectionMethod(App\Http\Controllers\PayrollController::class, 'authorizePayroll');
try {
    $authorize->invoke(new App\Http\Controllers\PayrollController, 'payslips');
    throw new RuntimeException('Payroll controller bypassed explicit Payslips denial');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $assert($e->getStatusCode() === 403, 'Wrong controller denial status');
}
echo "PASS: six Payroll children, order, catalog registration, local hierarchy, permission editor, inherited access, explicit denials for all tab routes/actions, disabled children and tab visibility.\n";
