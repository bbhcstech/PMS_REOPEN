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
echo "PASS: company role discovery, default access, saved custom permissions, and cross-company isolation.\n";
