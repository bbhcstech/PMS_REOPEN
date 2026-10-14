<?php

// Run with: php tests/regression/employee_exit_date_and_role.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EmployeeController;
use App\Models\EmployeeDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Setup in-memory sqlite database for testing
foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);

Schema::connection('central')->create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('status')->default('active');
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('status')->default('active');
    $table->timestamps();
});

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('name')->default('Test User');
    $table->string('email')->unique();
    $table->string('mobile')->nullable();
    $table->string('role')->default('employee');
    $table->string('password')->default('secret');
    $table->boolean('login_allowed')->default(true);
    $table->boolean('email_notifications')->default(true);
    $table->timestamps();
});

Schema::create('employee_details', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('employee_id')->unique();
    $table->unsignedBigInteger('user_role')->nullable(); // INTEGER column that originally caused the error
    $table->string('status')->default('Active');
    $table->unsignedBigInteger('department_id')->nullable();
    $table->unsignedBigInteger('parent_dpt_id')->nullable();
    $table->unsignedBigInteger('designation_id')->nullable();
    $table->date('joining_date')->nullable();
    $table->date('dob')->nullable();
    $table->date('exit_date')->nullable();
    $table->string('mobile')->nullable();
    $table->text('business_address')->nullable();
    $table->boolean('login_allowed')->default(true);
    $table->timestamps();
});

// Seed sample company and admin
DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Demo Corp', 'status' => 'active']);
DB::table('companies')->insert(['id' => 1, 'name' => 'Demo Corp', 'status' => 'active']);

$adminUser = User::create([
    'name' => 'Admin Boss',
    'email' => 'admin@example.com',
    'mobile' => '+919876543210',
    'role' => 'admin',
    'company_id' => 1,
]);

Auth::guard('web')->setUser($adminUser);

// Test 1: EmployeeDetail mutator prevents string from being set on integer user_role column
$detail = new EmployeeDetail();
$detail->user_role = 'employee'; // string from form
if ($detail->user_role !== null) {
    throw new RuntimeException("Test 1 Failed: Non-numeric user_role should be converted to null, got: " . var_export($detail->user_role, true));
}

$detail->user_role = 12; // valid numeric id
if ($detail->user_role !== 12) {
    throw new RuntimeException("Test 1 Failed: Numeric user_role should be preserved, got: " . var_export($detail->user_role, true));
}

// Test 2: EmployeeDetail exit_date mutator
$detail->exit_date = '';
if ($detail->exit_date !== null) {
    throw new RuntimeException("Test 2 Failed: Empty exit_date string should become null, got: " . var_export($detail->exit_date, true));
}

$detail->exit_date = '2026-10-28';
if ($detail->exit_date->format('Y-m-d') !== '2026-10-28') {
    throw new RuntimeException("Test 2 Failed: exit_date should be parsed and formatted, got: " . var_export($detail->exit_date, true));
}

// Create a target employee
$targetUser = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'mobile' => '+919123456789',
    'role' => 'employee',
    'company_id' => 1,
]);

$targetDetail = EmployeeDetail::create([
    'user_id' => $targetUser->id,
    'company_id' => 1,
    'employee_id' => 'EMP001',
    'status' => 'Active',
    'joining_date' => '2020-01-01',
    'dob' => '1995-05-15',
    'exit_date' => null,
    'business_address' => 'HQ',
]);

$controller = new EmployeeController();

// Test 3: Update employee to Inactive with exit_date and user_role = 'employee'
// This was the exact failing query:
// update employee_details set status = Inactive, user_role = employee, exit_date = 2026-10-28 where id = 8
$request = Request::create("/employees/{$targetUser->id}", 'POST', [
    '_method' => 'PUT',
    'name' => 'John Doe Updated',
    'email' => 'john@example.com',
    'company_id' => 1,
    'mobile_country_code' => '+91',
    'mobile' => '9123456789',
    'employee_id' => 'EMP001',
    'dob' => '1995-05-15',
    'joining_date' => '2020-01-01',
    'status' => 'Inactive',
    'exit_date' => '2026-10-28',
    'user_role' => 'employee',
    'login_allowed' => '1',
]);

$response = $controller->update($request, $targetUser->id);

$targetUser->refresh();
$targetDetail->refresh();

if ($targetDetail->status !== 'Inactive') {
    $errs = session()->has('errors') ? session()->get('errors')->all() : [];
    throw new RuntimeException("Test 3 Failed: Status was not set to Inactive. Errors: " . json_encode($errs));
}
if ($targetDetail->exit_date->format('Y-m-d') !== '2026-10-28') {
    throw new RuntimeException("Test 3 Failed: Exit date was not set to 2026-10-28.");
}
if ($targetDetail->user_role !== null) {
    throw new RuntimeException("Test 3 Failed: employee_details.user_role should be null (not integer error), got: " . var_export($targetDetail->user_role, true));
}
if ($targetUser->role !== 'employee') {
    throw new RuntimeException("Test 3 Failed: users.role should be employee, got: " . var_export($targetUser->role, true));
}

// Test 4: Updating user_role to 'admin' properly updates users.role without corrupting employee_details
$requestAdmin = Request::create("/employees/{$targetUser->id}", 'POST', [
    '_method' => 'PUT',
    'name' => 'John Doe Admin',
    'email' => 'john@example.com',
    'company_id' => 1,
    'mobile_country_code' => '+91',
    'mobile' => '9123456789',
    'employee_id' => 'EMP001',
    'dob' => '1995-05-15',
    'joining_date' => '2020-01-01',
    'status' => 'Inactive',
    'exit_date' => '2026-10-28',
    'user_role' => 'admin',
    'login_allowed' => '1',
]);

$controller->update($requestAdmin, $targetUser->id);
$targetUser->refresh();
$targetDetail->refresh();

if ($targetUser->role !== 'admin') {
    throw new RuntimeException("Test 4 Failed: users.role was not updated to admin, got: " . var_export($targetUser->role, true));
}
if ($targetDetail->user_role !== null) {
    throw new RuntimeException("Test 4 Failed: employee_details.user_role should remain null, got: " . var_export($targetDetail->user_role, true));
}

// Test 5: Reactivating employee to 'Active' clears exit_date to null
$requestActive = Request::create("/employees/{$targetUser->id}", 'POST', [
    '_method' => 'PUT',
    'name' => 'John Doe Reactivated',
    'email' => 'john@example.com',
    'company_id' => 1,
    'mobile_country_code' => '+91',
    'mobile' => '9123456789',
    'employee_id' => 'EMP001',
    'dob' => '1995-05-15',
    'joining_date' => '2020-01-01',
    'status' => 'Active',
    'exit_date' => '2026-10-28', // Stale exit date submitted
    'user_role' => 'employee',
    'login_allowed' => '1',
]);

$controller->update($requestActive, $targetUser->id);
$targetDetail->refresh();

if ($targetDetail->status !== 'Active') {
    throw new RuntimeException("Test 5 Failed: Status was not set to Active.");
}
if ($targetDetail->exit_date !== null) {
    throw new RuntimeException("Test 5 Failed: Exit date must be cleared to null when status is Active, got: " . var_export($targetDetail->exit_date, true));
}

// Test 6: Inactive without exit_date returns validation error
$requestNoExit = Request::create("/employees/{$targetUser->id}", 'POST', [
    '_method' => 'PUT',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'company_id' => 1,
    'mobile_country_code' => '+91',
    'mobile' => '9123456789',
    'employee_id' => 'EMP001',
    'dob' => '1995-05-15',
    'joining_date' => '2020-01-01',
    'status' => 'Inactive',
    'exit_date' => '',
    'user_role' => 'employee',
    'login_allowed' => '1',
]);

$redirectResponse = $controller->update($requestNoExit, $targetUser->id);
if (!$redirectResponse->isRedirection() || !session()->has('errors')) {
    throw new RuntimeException("Test 6 Failed: Inactive status without exit_date must redirect back with errors.");
}

// Test 7: Exit date <= joining date returns validation error
$requestInvalidExit = Request::create("/employees/{$targetUser->id}", 'POST', [
    '_method' => 'PUT',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'company_id' => 1,
    'mobile_country_code' => '+91',
    'mobile' => '9123456789',
    'employee_id' => 'EMP001',
    'dob' => '1995-05-15',
    'joining_date' => '2020-01-01',
    'status' => 'Inactive',
    'exit_date' => '2019-12-31', // Before joining date
    'user_role' => 'employee',
    'login_allowed' => '1',
]);

$redirectResponseInvalid = $controller->update($requestInvalidExit, $targetUser->id);
if (!$redirectResponseInvalid->isRedirection() || !session()->has('errors')) {
    throw new RuntimeException("Test 7 Failed: Exit date before joining date must redirect back with errors.");
}

echo "All employee exit date and user_role tests passed successfully!\n";
