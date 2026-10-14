<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{User, RolePermission, WorkspaceModule};
use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

config(['database.default' => 'tenant']);
foreach (['tenant', 'central'] as $connection) {
    config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
    Schema::connection($connection)->create('modules', function ($t) {
        $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->boolean('is_active')->default(true);
        $t->boolean('is_core')->default(false); $t->string('route_name')->nullable(); $t->string('route_prefix')->nullable();
        $t->integer('parent_id')->nullable(); $t->integer('sort_order')->default(0); $t->timestamps();
    });
    DB::connection($connection)->table('modules')->insert([
        ['id' => 1, 'name' => 'Tasks', 'slug' => 'tasks', 'route_prefix' => 'tasks'],
        ['id' => 2, 'name' => 'Work', 'slug' => 'work', 'route_prefix' => 'work'],
        ['id' => 3, 'name' => 'Orders', 'slug' => 'orders', 'route_prefix' => 'orders'],
    ]);
}
Schema::connection('tenant')->create('role_permissions', function ($t) {
    $t->id(); $t->string('role'); $t->integer('module_id');
    foreach (['view','create','edit','delete','approve','export','assign'] as $action) $t->boolean('can_'.$action)->default(false);
    $t->timestamps(); $t->unique(['role', 'module_id']);
});
$assert = function ($condition, $message) { if (!$condition) throw new RuntimeException($message); };
$middleware = new EnsureModuleAccess;
$run = function ($user, $method, $name) use ($middleware) {
    Auth::guard('web')->setUser($user);
    $request = Request::create('/tasks', $method, [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $route = (new Route([$method], '/tasks', []))->name($name);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => $user);
    return $middleware->handle($request, fn () => response('allowed'));
};
foreach (['hr', 'manager', 'employee'] as $role) {
    $user = new User(['role' => $role]);
    $permission = RolePermission::create(['role' => $role, 'module_id' => 1, 'can_view' => true]);
    $assert($run($user, 'GET', 'tasks.index')->getStatusCode() === 200, "$role lost view access");
    $assert($run($user, 'POST', 'tasks.store')->getStatusCode() === 403, "$role bypassed create denial");
    $assert($run($user, 'GET', 'tasks.edit')->getStatusCode() === 403, "$role bypassed edit denial");
    foreach ([['DELETE', 'tasks.destroy'], ['POST', 'tasks.approve'], ['GET', 'tasks.export'], ['POST', 'tasks.assign']] as [$method, $route]) {
        $assert($run($user, $method, $route)->getStatusCode() === 403, "$role bypassed $route denial");
    }
    $permission->update(['can_create' => true]);
    $assert($run($user, 'POST', 'tasks.store')->getStatusCode() === 200, "$role grant did not take effect");
    $permission->update(['can_view' => false]);
    $assert($run($user, 'GET', 'tasks.index')->getStatusCode() === 403, "$role revocation did not take effect");
}
Auth::guard('web')->setUser(new User(['role' => 'manager']));
RolePermission::create(['role' => 'manager', 'module_id' => 2, 'can_view' => true]);
RolePermission::create(['role' => 'manager', 'module_id' => 3, 'can_view' => false]);
$assert(!Auth::user()->canViewModule('orders'), 'Parent fallback overrode explicit denial');

Auth::guard('web')->setUser(new User(['role' => 'admin']));
$controller = new ModuleManagementController;
$request = Request::create('/admin/settings/modules/1', 'PUT', ['name' => 'Company Tasks', 'slug' => 'tasks', 'is_active' => '1']);
$controller->update($request, WorkspaceModule::findOrFail(1));
$assert(DB::connection('tenant')->table('modules')->where('id', 1)->value('name') === 'Company Tasks', 'Company edit was not saved');
$assert(DB::connection('central')->table('modules')->where('id', 1)->value('name') === 'Tasks', 'Company edit changed platform registry');
$assert((new WorkspaceModule)->resolveRouteBinding(1)->name === 'Company Tasks', 'Route binding used platform database');
WorkspaceModule::find(2)->update(['parent_id' => 1]);
try {
    $controller->update(Request::create('/modules/1', 'PUT', ['name' => 'Company Tasks', 'slug' => 'tasks', 'parent_id' => 2]), WorkspaceModule::find(1));
    throw new RuntimeException('Cyclic hierarchy was accepted');
} catch (Illuminate\Validation\ValidationException $e) {}

// Reuse the same user and module IDs in another company's database.
$firstPdo = DB::connection('tenant')->getPdo();
$secondPdo = new PDO('sqlite::memory:');
foreach ($firstPdo->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name IN ('modules', 'role_permissions')")->fetchAll(PDO::FETCH_COLUMN) as $sql) {
    $secondPdo->exec($sql);
}
$secondPdo->exec("INSERT INTO modules (id, name, slug, is_active) VALUES (1, 'Second Company Tasks', 'tasks', 1)");
$secondPdo->exec("INSERT INTO role_permissions (role, module_id, can_view) VALUES ('hr', 1, 1)");
DB::connection('tenant')->setPdo($secondPdo)->setReadPdo($secondPdo);
Auth::guard('web')->setUser(new User(['role' => 'hr']));
$assert(Auth::user()->canViewModule('tasks'), 'Second company inherited first company revocation');
$assert(WorkspaceModule::find(1)->name === 'Second Company Tasks', 'Second company inherited first company configuration');
DB::connection('tenant')->setPdo($firstPdo)->setReadPdo($firstPdo);
$assert(!Auth::user()->canViewModule('tasks'), 'First company inherited second company grant');
$assert(WorkspaceModule::find(1)->name === 'Company Tasks', 'First company configuration was overwritten');
WorkspaceModule::find(1)->update(['is_active' => false]);
try {
    $run(new User(['role' => 'admin']), 'GET', 'tasks.index');
    throw new RuntimeException('Disabled module remained accessible');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $assert($e->getStatusCode() === 403, 'Wrong disabled-module status');
}
$assert($run(new User(['role' => 'admin']), 'GET', 'admin.modules.index')->getStatusCode() === 200, 'Administrator lost settings recovery access');
DB::table('role_permissions')->where('role', 'employee')->delete();
WorkspaceModule::find(1)->update(['is_active' => true]);
Auth::guard('web')->setUser(new User(['role' => 'employee']));
$assert(Auth::user()->hasModulePermission('tasks', 'edit'), 'Employee default edit access was lost');
$assert(!Auth::user()->hasModulePermission('tasks', 'delete'), 'Employee defaults granted unintended delete access');

Schema::create('company_staff_roles', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('access_role'); $t->timestamps();
});
$entitlement = new class {
    public bool $enabled = true;
    public function hasFeature($slug) { return $this->enabled; }
};
$context = new class($entitlement) extends App\Services\CompanyContext {
    public function __construct(private $entitlement) {}
    public function current() { return $this->entitlement; }
};
$app->instance(App\Services\CompanyContext::class, $context);
$staffRole = App\Models\CompanyStaffRole::create(['company_id' => 1, 'name' => 'Team Lead', 'access_role' => 'manager']);
RolePermission::create(['role' => $staffRole->permissionKey(), 'module_id' => 1, 'can_view' => true]);
$customUser = new User(['company_id' => 1, 'role' => 'manager', 'company_staff_role_id' => $staffRole->id]);
$assert($run($customUser, 'GET', 'tasks.index')->getStatusCode() === 200, 'Custom role view grant was ignored');
$assert($run($customUser, 'PATCH', 'tasks.update')->getStatusCode() === 403, 'Custom role edit denial was ignored');
$foreignUser = new User(['company_id' => 2, 'role' => 'manager', 'company_staff_role_id' => $staffRole->id]);
$assert($foreignUser->permissionRole() === 'manager', 'Foreign company custom role was selected');
$entitlement->enabled = false;
foreach ([$customUser, new User(['company_id' => 1, 'role' => 'admin'])] as $user) {
    $assert($run($user, 'GET', 'tasks.index')->getStatusCode() === 403, 'Company permission bypassed platform entitlement');
}
$entitlement->enabled = true;
WorkspaceModule::create(['id' => 4, 'name' => 'Payroll', 'slug' => 'payroll', 'is_active' => true]);
WorkspaceModule::create(['id' => 5, 'name' => 'Payslips', 'slug' => 'payslips', 'is_active' => true, 'route_name' => 'payroll.payslips.index']);
RolePermission::create(['role' => 'hr', 'module_id' => 4, 'can_view' => true]);
$hr = new User(['company_id' => 1, 'role' => 'hr']);
Auth::guard('web')->setUser($hr);
$assert($hr->canViewModule('payslips'), 'Legacy Payroll grant did not include Payslips');
$assert(!$hr->hasModulePermission('payslips', 'export'), 'Payroll view grant incorrectly enabled export');
$assert(app(App\Services\AdminWorkspaceModules::class)->permissionMatrixModules()->pluck('slug')->contains('payslips'), 'Payslips cannot be managed in permission editor');
$payslipPermission = RolePermission::create(['role' => 'hr', 'module_id' => 5, 'can_view' => false]);
$assert(!$hr->canViewModule('payslips'), 'Payroll grant bypassed explicit Payslips revocation');
$payslipPermission->update(['can_view' => true]);
$assert($hr->canViewModule('payslips'), 'Explicit Payslips grant did not take effect');
$payslipPermission->delete();
WorkspaceModule::find(4)->update(['is_active' => false]);
$assert(!$hr->canViewModule('payslips'), 'Inactive Payroll supplied inherited Payslips access');
WorkspaceModule::find(4)->update(['is_active' => true]);
$administrator = new User(['company_id' => 1, 'role' => 'administrator']);
Auth::guard('web')->setUser($administrator);
$assert($administrator->normalizedRole() === 'admin' && $administrator->canViewModule('payslips'), 'Administrator alias lost company admin authority');
$entitlement->enabled = false;
$assert(!$administrator->canViewModule('payslips'), 'Administrator bypassed platform Payslips restriction');
echo "PASS: all seven actions, built-in/custom roles, immediate grants and revocations, explicit denial, company database isolation, tenant module edits/binding, hierarchy validation, disabled modules, admin recovery, shared defaults, platform entitlement ceiling.\n";
echo "PASS: Payroll-to-Payslips inheritance, action restrictions, editable Payslips permission, explicit revocation/grant, inactive parent, administrator alias, platform restrictions.\n";
