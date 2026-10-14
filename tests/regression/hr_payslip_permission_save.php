<?php

// Exercise the real permission-save and PayrollController authorization paths.
class PayslipSchemaDouble { public static function ensure(): void {} }
class_alias(PayslipSchemaDouble::class, 'App\Services\PayrollSchema');
require __DIR__.'/role_permissions_save.php';

use App\Models\{User, CompanyStaffRole, RolePermission};
use App\Http\Controllers\{PayrollController, Admin\RolePermissionController};
use Illuminate\Support\Facades\{Auth, DB};

$entitlement = new class {
    public bool $enabled = true;
    public function hasFeature($slug) { return $this->enabled; }
};
$app->instance(App\Services\CompanyContext::class, new class($entitlement) extends App\Services\CompanyContext {
    public function __construct(private $company) {}
    public function current() { return $this->company; }
});
foreach (['central' => [181, 182], 'tenant' => [9001, 9002]] as $connection => [$payrollId, $payslipId]) {
    DB::connection($connection)->table('modules')->insert([
        ['id' => $payrollId, 'name' => 'Payroll', 'slug' => 'payroll', 'route_name' => 'payroll.index'],
        ['id' => $payslipId, 'name' => 'Payslips', 'slug' => 'payslips', 'route_name' => 'payroll.payslips.index'],
    ]);
}
$authorize = new ReflectionMethod(PayrollController::class, 'authorizePayroll');
$payrollController = new PayrollController;
$permissionsController = new RolePermissionController;
$save = function ($role, $permissions) use ($permissionsController) {
    Auth::guard('web')->setUser(new User(['company_id' => 1, 'role' => 'admin']));
    $request = Illuminate\Http\Request::create('/settings/role-permissions', 'POST', [], [], [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode(compact('role', 'permissions')));
    if ($permissionsController->update($request)->getStatusCode() !== 200) throw new RuntimeException('Permission save failed');
};
$check = function ($user, $expected, $action = 'view') use ($authorize, $payrollController) {
    Auth::guard('web')->setUser($user);
    $allowed = true;
    try { $authorize->invoke($payrollController, 'payslips', $action); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
        if ($e->getStatusCode() !== 403) throw $e;
        $allowed = false;
    }
    if ($allowed !== $expected) throw new RuntimeException('Saved HR permission differs from Payslips authorization');
};
$hr = new User(['company_id' => 1, 'role' => 'hr']);
$save('hr', [9001 => ['view'], 9002 => ['view']]);
$check($hr, true);
$check($hr, false, 'export');
$save('hr', [9001 => ['view'], 9002 => []]);
$check($hr, false);
$save('hr', [9001 => ['view'], 9002 => ['view', 'export']]);
$check($hr, true);
$check($hr, true, 'export');
Illuminate\Support\Facades\Schema::connection('tenant')->create('users', function ($table) {
    $table->id(); $table->integer('company_id'); $table->string('name'); $table->string('email'); $table->string('role');
});
RolePermission::where('role', 'hr')->where('module_id', 9002)->delete();
Auth::guard('web')->setUser(new User(['company_id' => 1, 'role' => 'admin']));
$editor = $permissionsController->index(Illuminate\Http\Request::create('/settings/role-permissions', 'GET', ['role' => 'hr']))->getData();
if (!$editor['effectivePermissions'][9002]['view']) throw new RuntimeException('Editor hid inherited Payslips view grant');
$payload = [];
foreach ($editor['effectivePermissions'] as $moduleId => $actions) {
    $payload[$moduleId] = array_keys(array_filter($actions));
}
$save('hr', $payload);
$check($hr, true);
$role = CompanyStaffRole::create(['company_id' => 1, 'name' => 'HR', 'access_role' => 'hr']);
$customHr = new User(['company_id' => 1, 'role' => 'hr', 'company_staff_role_id' => $role->id]);
$save($role->permissionKey(), [9002 => ['view']]);
$check($customHr, true);
$save($role->permissionKey(), [9002 => []]);
$check($customHr, false);
$check($hr, true);
$save($role->permissionKey(), [9002 => ['view']]);
$check($customHr, true);
$entitlement->enabled = false;
$check($hr, false);
$check($customHr, false);
echo "PASS: actual admin save -> HR Payslips authorization, mismatched platform/tenant IDs, grant/revoke/regrant, export, independent custom HR permissions, platform restriction.\n";
