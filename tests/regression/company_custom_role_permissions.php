<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{CompanyStaffRole, User};
use App\Http\Controllers\Admin\RolePermissionController;
use Illuminate\Support\Facades\{DB, Schema, Auth};
use Illuminate\Database\Schema\Blueprint;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant']);
DB::purge('tenant');
Schema::create('company_staff_roles', function (Blueprint $table) {
    $table->id(); $table->integer('company_id'); $table->string('name'); $table->string('access_role'); $table->timestamps();
});
Schema::create('role_permissions', function (Blueprint $table) {
    $table->id(); $table->string('role');
});
$first = CompanyStaffRole::create(['company_id' => 1, 'name' => 'Team Lead', 'access_role' => 'manager']);
$second = CompanyStaffRole::create(['company_id' => 2, 'name' => 'Team Lead', 'access_role' => 'manager']);
$actor = new User(['company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($actor);
$controller = new RolePermissionController;
$method = new ReflectionMethod($controller, 'companyRoles');
$roles = $method->invoke($controller);
if (($roles[$first->permissionKey()] ?? null) !== 'Team Lead' || isset($roles[$second->permissionKey()]) || count($roles) !== 4) {
    throw new RuntimeException('Role selector leaked another company or lost its default roles.');
}
$staff = new User(['company_id' => 1, 'role' => 'manager', 'company_staff_role_id' => $first->id]);
if ($staff->permissionRole() !== 'manager') throw new RuntimeException('Unconfigured role lost its existing access.');
DB::table('role_permissions')->insert(['role' => $first->permissionKey()]);
if ($staff->permissionRole() !== $first->permissionKey()) throw new RuntimeException('Custom permissions were not selected.');
$foreign = new User(['company_id' => 2, 'role' => 'manager', 'company_staff_role_id' => $first->id]);
if ($foreign->permissionRole() !== 'manager') throw new RuntimeException('Foreign role permissions leaked.');
Auth::guard('web')->setUser(new User(['company_id' => 2, 'role' => 'admin']));
$roles = $method->invoke($controller);
if (isset($roles[$first->permissionKey()]) || !isset($roles[$second->permissionKey()])) throw new RuntimeException('Company switch retained foreign roles.');
Auth::guard('web')->setUser($actor);
$accessRole = new ReflectionMethod($controller, 'accessRole');
foreach ([$second->permissionKey(), 'staff:1:'.$second->id] as $foreignKey) {
    try {
        $accessRole->invoke($controller, $foreignKey);
        throw new RuntimeException('Foreign or forged custom role key accepted.');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
        if ($e->getStatusCode() !== 403) throw $e;
    } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {}
}
session(['current_company_id' => 2]);
try {
    $method->invoke($controller);
    throw new RuntimeException('Mismatched company session listed roles.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() !== 403) throw $e;
}
try {
    $controller->update(Illuminate\Http\Request::create('/settings/role-permissions', 'POST', ['role' => 'hr']));
    throw new RuntimeException('Mismatched company session attempted a permission save.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() !== 403) throw $e;
}
session()->forget('current_company_id');
Auth::guard('web')->setUser(new User(['role' => 'admin']));
try {
    $method->invoke($controller);
    throw new RuntimeException('Missing company workspace listed roles.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() !== 403) throw $e;
}
Auth::guard('web')->setUser($actor);
echo "PASS: company-scoped roles, saved custom permissions, foreign/forged keys rejected, mismatched sessions blocked before saves, missing workspace rejected.\n";
