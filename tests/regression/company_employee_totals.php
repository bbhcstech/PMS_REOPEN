<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{EmployeeDetail, User};
use App\Services\CompanyEmployeeDirectory;
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};

foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::create('users', function ($table) {
    $table->id(); $table->integer('company_id')->nullable(); $table->string('name'); $table->string('role');
    $table->integer('hr_id')->nullable(); $table->integer('manager_id')->nullable();
    $table->integer('company_staff_role_id')->nullable();
    $table->timestamp('archived_at')->nullable(); $table->boolean('is_active')->default(true);
    $table->timestamps();
});
Schema::create('employee_details', function ($table) {
    $table->id(); $table->integer('user_id'); $table->string('status')->nullable(); $table->timestamps();
});
$check = function ($condition, $message) { if (!$condition) throw new RuntimeException($message); };
$actors = [];
foreach (['admin', 'hr', 'manager', 'employee'] as $role) {
    $actors[$role] = User::create(['company_id' => 1, 'name' => $role, 'role' => $role]);
}
foreach (['hr', 'manager', 'employee'] as $i => $role) {
    $actors['custom_'.$role] = User::create(['company_id' => 1, 'name' => 'Custom '.$role,
        'role' => $role, 'company_staff_role_id' => $i + 1]);
}
$directory = new CompanyEmployeeDirectory;
foreach (['admin', 'hr', 'manager'] as $creator) {
    Auth::guard('web')->setUser($actors[$creator]);
    $created = User::create(['company_id' => 1, 'name' => 'Added by '.$creator, 'role' => 'employee',
        'hr_id' => $creator === 'hr' ? $actors[$creator]->id : null,
        'manager_id' => $creator === 'manager' ? $actors[$creator]->id : null]);
    EmployeeDetail::create(['user_id' => $created->id, 'status' => 'Active']);
    $check($created->getConnectionName() === 'tenant' && $created->employeeDetail->getConnectionName() === 'tenant', 'Employee records went to a role-specific connection');
}
// A duplicate profile must never inflate the employee account count.
EmployeeDetail::create(['user_id' => $created->id, 'status' => 'Active']);
User::create(['company_id' => 2, 'name' => 'Foreign', 'role' => 'employee']);
User::create(['company_id' => null, 'name' => 'Unassigned', 'role' => 'employee']);
User::create(['company_id' => 1, 'name' => 'Client', 'role' => 'client']);
User::create(['company_id' => 1, 'name' => 'Archived', 'role' => 'employee', 'archived_at' => now()]);
foreach ($actors as $role => $actor) {
    Auth::guard('web')->setUser($actor);
    $check($directory->count() === 9, $role.' sees a different company employee count');
    $html = Blade::render(file_get_contents(resource_path('views/partials/company-employee-total.blade.php')));
    $check(str_contains($html, '>9</strong>'), $role.' dashboard total did not render dynamically');
    try { $directory->count(2); throw new RuntimeException('Cross-company count allowed'); }
    catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $check($e->getStatusCode() === 403, 'Wrong company rejection'); }
}
Auth::guard('web')->setUser($actors['admin']);
$created->forceFill(['archived_at' => now()])->save();
$check($directory->count() === 8, 'Archive did not immediately reduce total');
$created->forceFill(['archived_at' => null])->save();
$check($directory->count() === 9, 'Restore did not immediately update total');
$created->delete();
$check($directory->count() === 8, 'Deletion did not immediately update total');
$alphaPdo = DB::connection('tenant')->getPdo();
DB::connection('tenant')->setPdo(new PDO('sqlite::memory:'));
DB::statement('CREATE TABLE users (id integer primary key, company_id integer, role text, archived_at text)');
DB::table('users')->insert(['id' => 1, 'company_id' => 2, 'role' => 'employee']);
Auth::guard('web')->setUser((new User)->forceFill(['id' => 1, 'company_id' => 2, 'role' => 'admin']));
$check($directory->count() === 1, 'Switching company database retained another company total');
DB::connection('tenant')->setPdo($alphaPdo);
Auth::guard('web')->setUser($actors['admin']);
$check($directory->count() === 8, 'Returning to first company changed its total');
echo "PASS: company-wide counts shared by all role actors, shared tenant writes from Admin/HR/Manager, duplicate profile protection, archives/restores/deletion and foreign/unassigned account isolation.\n";
