<?php

// Run with: php tests/regression/employee_delete_after_inactive.php  (isolated SQLite; no live data)
// Employees "Delete Employee": an employee can be deleted permanently only after being marked inactive,
// with no direct reports, inside the admin's company, with delete permission and a typed confirmation.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EmployeeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant']);
DB::connection('tenant')->statement('PRAGMA foreign_keys = ON');
Schema::create('users', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->string('email')->nullable(); $t->string('password')->nullable();
    $t->string('role')->nullable(); $t->unsignedBigInteger('company_staff_role_id')->nullable(); $t->boolean('is_active')->default(true);
    $t->boolean('login_allowed')->default(true); $t->timestamp('archived_at')->nullable(); $t->timestamps();
});
Schema::create('employee_details', function ($t) {
    $t->id(); $t->unsignedBigInteger('user_id'); $t->string('employee_id')->nullable(); $t->string('status')->nullable();
    $t->date('exit_date')->nullable(); $t->unsignedBigInteger('reporting_to')->nullable(); $t->timestamps();
});
// A record that blocks deletion (like a project created by the employee).
Schema::create('projects', function ($t) { $t->id(); $t->foreignId('created_by')->constrained('users'); $t->string('name'); });

DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin'],
    ['id' => 2, 'company_id' => 1, 'name' => 'Active Person', 'role' => 'employee'],
    ['id' => 3, 'company_id' => 1, 'name' => 'Inactive Person', 'role' => 'employee'],
    ['id' => 4, 'company_id' => 1, 'name' => 'Team Lead', 'role' => 'employee'],
    ['id' => 5, 'company_id' => 1, 'name' => 'Reports To Lead', 'role' => 'employee'],
    ['id' => 6, 'company_id' => 2, 'name' => 'Other Company', 'role' => 'employee'],
    ['id' => 7, 'company_id' => 1, 'name' => 'Linked Person', 'role' => 'employee'],
]);
DB::table('employee_details')->insert([
    ['user_id' => 2, 'employee_id' => 'EMP-002', 'status' => 'Active', 'reporting_to' => null],
    ['user_id' => 3, 'employee_id' => 'EMP-003', 'status' => 'Inactive', 'reporting_to' => null],
    ['user_id' => 4, 'employee_id' => 'EMP-004', 'status' => 'Inactive', 'reporting_to' => null],
    ['user_id' => 5, 'employee_id' => 'EMP-005', 'status' => 'Active', 'reporting_to' => 4],
    ['user_id' => 6, 'employee_id' => 'EMP-006', 'status' => 'Inactive', 'reporting_to' => null],
    ['user_id' => 7, 'employee_id' => 'EMP-007', 'status' => 'Inactive', 'reporting_to' => null],
]);
DB::table('projects')->insert(['created_by' => 7, 'name' => 'Linked project']);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$admin = new class extends User {
    public bool $canDelete = true;
    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return $permission !== 'delete' || $this->canDelete; }
};
$admin->setConnection('tenant');
$admin->forceFill(['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin'])->syncOriginal();
$admin->exists = true;
Auth::guard('web')->setUser($admin);
$controller = app(EmployeeController::class);
$delete = function (int $id, ?string $code) use ($controller) {
    $request = Request::create('/employees/' . $id . '/permanent', 'DELETE', ['confirm_code' => $code]);
    $request->headers->set('Accept', 'application/json');
    app()->instance('request', $request);
    $request->setUserResolver(fn () => Auth::user());
    return $controller->deletePermanently($request, $id);
};
$status = fn ($response) => $response->getStatusCode();
$message = fn ($response) => (string) ($response->getData()->message ?? '');

// 1. Active employee: refused, must be marked inactive first
$response = $delete(2, 'EMP-002');
$check($status($response) === 422 && str_contains($message($response), 'inactive first'), 'active employee cannot be deleted (asked to mark inactive first)');
$check(User::find(2) !== null, 'active employee still exists');

// 2. Wrong confirmation text
$response = $delete(3, 'EMP-999');
$check($status($response) === 422 && str_contains($message($response), 'EMP-003'), 'wrong typed confirmation is refused');
$check(User::find(3) !== null, 'employee kept when confirmation is wrong');

// 3. Inactive + correct confirmation: permanently deleted with profile
$response = $delete(3, 'emp-003');
$check($status($response) === 200 && str_contains($message($response), 'deleted permanently'), 'inactive employee is deleted after typing the employee ID');
$check(User::find(3) === null && DB::table('employee_details')->where('user_id', 3)->doesntExist(), 'user and employee profile are removed permanently');

// 4. Inactive but has direct reports
$response = $delete(4, 'EMP-004');
$check($status($response) === 422 && str_contains($message($response), 'report to them'), 'employee with direct reports cannot be deleted');

// 5. Other company
$denied = false;
try { $delete(6, 'EMP-006'); } catch (HttpException $e) { $denied = $e->getStatusCode() === 403; }
$check($denied && User::find(6) !== null, 'employee of another company cannot be deleted');

// 6. Records still reference the employee: clean refusal, nothing removed
$response = $delete(7, 'EMP-007');
$check($status($response) === 409 && str_contains($message($response), 'Archive the employee instead'), 'linked records give a clear message instead of an error page');
$check(User::find(7) !== null && DB::table('employee_details')->where('user_id', 7)->exists(), 'failed delete is rolled back completely');

// 7. Cannot delete yourself / without permission
$response = $delete(1, 'Admin');
$check($status($response) === 422, 'an admin cannot delete their own account');
$admin->canDelete = false;
$denied = false;
try { $delete(4, 'EMP-004'); } catch (HttpException $e) { $denied = $e->getStatusCode() === 403; }
$check($denied, 'users without delete permission are refused');
$admin->canDelete = true;

// 8. Mark Inactive (step 1) then delete
$request = Request::create('/employees/2/inactive', 'POST', ['exit_date' => now()->addDay()->toDateString()]);
app()->instance('request', $request);
$invalid = false;
try { $controller->markInactive($request, 2); } catch (Illuminate\Validation\ValidationException $e) { $invalid = isset($e->errors()['exit_date']); }
$check($invalid, 'a future exit date is rejected when marking inactive now');
$request = Request::create('/employees/2/inactive', 'POST', ['exit_date' => now()->toDateString()]);
app()->instance('request', $request);
$response = $controller->markInactive($request, 2);
$detail = DB::table('employee_details')->where('user_id', 2)->first();
$check($response->isRedirect() && $detail->status === 'Inactive' && $detail->exit_date === now()->toDateString(), 'Mark Inactive sets status and exit date');
$response = $delete(2, 'EMP-002');
$check($status($response) === 200 && User::find(2) === null, 'after marking inactive the employee can be deleted');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: employee delete only after inactive (confirmation, team, company, linked records, permission).\n";
exit($failures ? 1 : 0);
