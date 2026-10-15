<?php

// Run with: php tests/regression/parent_department_delete.php  (isolated SQLite; no live data)
// Parent Department "Delete Department": permanent delete, refused while sub-departments or employees are linked.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ParentDepartmentController;
use App\Models\{ParentDepartment, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant']);
Schema::create('parent_departments', function ($t) {
    $t->id(); $t->string('dpt_name'); $t->string('dpt_code')->nullable(); $t->unsignedBigInteger('company_id')->nullable();
    $t->unsignedBigInteger('added_by')->nullable(); $t->unsignedBigInteger('last_updated_by')->nullable(); $t->timestamp('archived_at')->nullable(); $t->timestamps();
});
Schema::create('departments', function ($t) { $t->id(); $t->string('dpt_name'); $t->unsignedBigInteger('parent_dpt_id')->nullable(); $t->timestamps(); });
Schema::create('employee_details', function ($t) { $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->unsignedBigInteger('parent_dpt_id')->nullable(); $t->timestamps(); });
DB::table('parent_departments')->insert([
    ['id' => 1, 'dpt_name' => 'Laravel Developer', 'dpt_code' => 'DEP-0002'],
    ['id' => 2, 'dpt_name' => 'Mern Stack', 'dpt_code' => 'DEP-0001'],
    ['id' => 3, 'dpt_name' => 'Design', 'dpt_code' => 'DEP-0003'],
]);
DB::table('departments')->insert(['dpt_name' => 'Backend', 'parent_dpt_id' => 1]);
DB::table('employee_details')->insert(['user_id' => 9, 'parent_dpt_id' => 2]);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$controller = app(ParentDepartmentController::class);
$delete = function (int $id) use ($controller) {
    $request = Request::create('/admin/settings/parent-departments/' . $id . '/permanent', 'DELETE');
    $request->headers->set('Accept', 'application/json');
    app()->instance('request', $request);
    return $controller->deletePermanently($request, $id);
};

// A user whose module permission allows deletes (company admin).
$admin = new class extends User { public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return true; } };
$admin->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($admin);

$response = $delete(1);
$check($response->getStatusCode() === 422 && str_contains($response->getData()->message, 'sub-departments'), 'department with sub-departments is not deleted (clear message)');
$check(ParentDepartment::find(1) !== null, 'department with sub-departments still exists');
$response = $delete(2);
$check($response->getStatusCode() === 422 && str_contains($response->getData()->message, 'employees'), 'department tagged with employees is not deleted (clear message)');
$check(ParentDepartment::find(2) !== null, 'department tagged with employees still exists');
$response = $delete(3);
$check($response->getStatusCode() === 200 && $response->getData()->status === 'success', 'unlinked department is deleted');
$check(DB::table('parent_departments')->where('id', 3)->doesntExist(), 'deleted department is permanently removed (not archived)');

// No delete permission -> refused.
$viewer = new class extends User { public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return $permission !== 'delete'; } };
$viewer->forceFill(['id' => 2, 'company_id' => 1, 'role' => 'hr']);
Auth::guard('web')->setUser($viewer);
$denied = false;
try { $delete(1); } catch (HttpException $e) { $denied = $e->getStatusCode() === 403; }
$check($denied, 'user without delete permission cannot delete');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: permanent delete with linked sub-department/employee protection and delete permission.\n";
exit($failures ? 1 : 0);
