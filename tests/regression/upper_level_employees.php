<?php

// Company-role ("upper level") accounts after the "Add Upper Level Employee" page was removed.
// Existing company-role accounts keep working: login, tenant isolation, admin-only management
// through the Employees section, and HR/managers stay unable to manage them.
// Also the shared fixture for authentication_session.php, authority_attendance_and_leave.php and company_ai.php.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{CompanyStaffRole, EmployeeDetail, User};
use App\Services\{CompanyContext, CompanyStaffSchema, CompanyStaffLogin};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Route, Schema};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

// Separate persistent in-memory PDOs emulate independently provisioned tenants,
// including overlapping IDs. No production databases or email services are used.
$pdos = [];
app()->instance('db.connector.sqlite', new class($pdos) implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $pdos = [];
    public function __construct($unused) {}
    public function connect(array $config) {
        return $this->pdos[$config['database']] ??= new PDO('sqlite::memory:');
    }
});
foreach (['central' => 'central-test', 'tenant' => 'alpha-test'] as $name => $database) {
    config(["database.connections.$name" => ['driver' => 'sqlite', 'database' => $database, 'prefix' => '']]);
    DB::purge($name);
}
config(['database.default' => 'tenant', 'session.driver' => 'array', 'cache.default' => 'array']);
Schema::connection('central')->create('companies', function ($t) {
    $t->id(); foreach (['name', 'email', 'db_name', 'employee_id_prefix'] as $column) $t->string($column);
    $t->string('short_name')->nullable(); $t->string('status')->default('active');
    $t->integer('max_users')->default(100); $t->text('settings')->nullable(); $t->softDeletes(); $t->timestamps();
});
DB::connection('central')->table('companies')->insert([
    ['id' => 1, 'name' => 'Alpha', 'email' => 'alpha-admin@test.com', 'db_name' => 'alpha-test', 'employee_id_prefix' => 'ALPHA-EMP', 'settings' => '{"designation_max_level":6}'],
    ['id' => 2, 'name' => 'Beta', 'email' => 'beta-admin@test.com', 'db_name' => 'beta-test', 'employee_id_prefix' => 'BETA-EMP', 'settings' => '{"designation_max_level":10}'],
]);
function staffCheck($ok, $message) { if (! $ok) throw new RuntimeException($message); }
function tenant($name) {
    config(['database.connections.tenant.database' => $name]); DB::purge('tenant');
    app(CompanyContext::class)->reset(); session()->forget(['current_company_id', 'current_company_db']);
}
function req($data) {
    $r = Request::create('/employees', 'POST', $data);
    $r->setLaravelSession(app('session')->driver()); app()->instance('request', $r); return $r;
}
function denied(callable $call, int $status = 403) {
    try { $call(); throw new RuntimeException('Unauthorized action succeeded.'); }
    catch (HttpException $e) { staffCheck($e->getStatusCode() === $status, 'Unexpected denial status.'); }
    catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { staffCheck($status === 404, 'Unexpected missing record.'); }
}
function invalid(callable $call, $field) {
    try { $call(); throw new RuntimeException('Invalid input accepted: ' . $field); }
    catch (ValidationException $e) { staffCheck(isset($e->errors()[$field]), 'Missing validation error: ' . $field); }
}
/** Creates a company-role account the way the removed page used to (fixture only). */
function staffAccount(int $companyId, string $prefix, CompanyStaffRole $role, array $data): User {
    $account = new User;
    $account->forceFill([
        'name' => $data['name'], 'email' => $data['email'], 'company_id' => $companyId, 'password' => Hash::make($data['password']),
        'role' => $role->access_role, 'company_staff_role_id' => $role->id, 'login_allowed' => true, 'is_active' => true,
    ])->save();
    EmployeeDetail::create(['user_id' => $account->id, 'company_id' => $companyId, 'designation_id' => $data['designation_id'],
        'employee_id' => $prefix . '-0001', 'joining_date' => '2026-10-09', 'login_allowed' => true, 'business_address' => '', 'status' => 'Active']);
    return $account->fresh();
}
foreach (['alpha-test' => 1, 'beta-test' => 2] as $name => $companyId) {
    tenant($name);
    Schema::connection('tenant')->create('users', function ($t) {
        $t->id(); $t->unsignedBigInteger('company_id'); $t->string('name'); $t->string('email')->unique();
        $t->string('password'); $t->string('raw_password')->nullable(); $t->string('role');
        $t->boolean('is_active')->default(true); $t->boolean('login_allowed')->default(true);
        $t->timestamp('archived_at')->nullable(); $t->timestamps();
    });
    Schema::connection('tenant')->create('designations', function ($t) {
        $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->unsignedBigInteger('added_by')->nullable();
        $t->string('name'); $t->integer('level'); $t->timestamps();
    });
    Schema::connection('tenant')->create('employee_details', function ($t) {
        $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('company_id');
        $t->unsignedBigInteger('designation_id'); $t->string('employee_id')->unique(); $t->date('joining_date');
        $t->unsignedBigInteger('reporting_to')->nullable(); $t->unsignedBigInteger('department_id')->nullable(); $t->date('exit_date')->nullable();
        $t->boolean('login_allowed'); $t->text('business_address'); $t->string('status')->default('Active'); $t->timestamps();
    });
    Schema::connection('tenant')->create('departments', function ($t) { $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->timestamps(); });
    DB::connection('tenant')->table('users')->insert(['id' => 1, 'company_id' => $companyId, 'name' => 'Admin', 'email' => $name . '@test.com', 'password' => Hash::make('Admin-pass-123'), 'role' => 'admin']);
    DB::connection('tenant')->table('designations')->insert([
        ['id' => 1, 'company_id' => $companyId, 'name' => 'Project Lead', 'level' => 2],
        ['id' => 2, 'company_id' => 99, 'name' => 'Foreign designation', 'level' => 1],
        ['id' => 3, 'company_id' => $companyId, 'name' => 'Level 9', 'level' => 9],
    ]);
    Auth::guard('web')->setUser(User::findOrFail(1)); CompanyStaffSchema::ensure(); CompanyStaffSchema::ensure();
    CompanyStaffRole::create(['company_id' => $companyId, 'name' => 'Project Operations Lead', 'access_role' => 'manager']);
}

// The removed page and its endpoints are gone.
staffCheck(! Route::has('admin.upper-level-employees.index') && ! Route::has('admin.upper-level-employees.store')
    && ! Route::has('admin.upper-level-employees.update') && ! Route::has('admin.upper-level-employees.roles.store'), 'Upper level employee routes still registered.');
staffCheck(config('workspace_modules.upper-level-employees') === null, 'Upper level employee module still in the sidebar registry.');
staffCheck(! class_exists(App\Http\Controllers\Admin\UpperLevelEmployeeController::class), 'Upper level employee controller still present.');
staffCheck(! str_contains(file_get_contents(resource_path('views/admin/layout/manu.blade.php')), 'Add Upper Level Employee'), 'Sidebar still links to Add Upper Level Employee.');

tenant('alpha-test'); Auth::guard('web')->setUser(User::findOrFail(1));
$data = ['name' => 'Lead', 'email' => 'lead@test.com', 'password' => 'Exact-password-123', 'designation_id' => 1];
$staff = staffAccount(1, 'ALPHA-EMP', CompanyStaffRole::firstOrFail(), $data);
staffCheck($staff->company_id === 1 && $staff->role === 'manager' && (int) $staff->company_staff_role_id === 1, 'Account ownership/access role incorrect.');

// Existing company-role accounts still appear in the company employee list, never across companies.
$employeeList = (new App\Http\Controllers\EmployeeController)->index(req([]))->getData();
staffCheck($employeeList['employees']->pluck('id')->all() === [$staff->id], 'Manager did not appear in company employee list or a foreign user leaked.');
denied(fn () => (new App\Http\Controllers\EmployeeController)->index(req(['company_id' => 99])));

// HR and managers still cannot manage company-role accounts; employees and others cannot either.
foreach (['hr', 'manager'] as $role) {
    $actor = new User(['company_id' => 1, 'role' => $role]); Auth::guard('web')->setUser($actor);
    $legacy = new App\Http\Controllers\EmployeeController;
    denied(fn () => $legacy->bulkUpdateStatus(req(['employee_ids' => [$staff->id], 'status' => 'Active'])));
    denied(fn () => $legacy->bulkDelete(req(['employee_ids' => [$staff->id]])));
    denied(fn () => $legacy->edit($staff->id));
}
// A company admin now manages these accounts from the Employees section (no redirect to the removed page).
Auth::guard('web')->setUser(User::findOrFail(1));
$response = (new App\Http\Controllers\EmployeeController)->bulkUpdateStatus(req(['employee_ids' => [$staff->id], 'status' => 'Active']));
staffCheck($response->getStatusCode() === 200, 'Company admin can no longer update a company-role account status.');
// Role Accounts still refuses them (their access is tied to the company role).
denied(fn () => (new App\Http\Controllers\Admin\RoleAccountController)->resetPassword(req(['password' => 'Bypass-password-123']), 'manager', $staff));

// Login stays secure and tied to the company role.
$login = new CompanyStaffLogin;
$staff->forceFill(['login_allowed' => false])->save();
invalid(fn () => $login->authenticate($staff->fresh(), 'Exact-password-123'), 'email');
staffCheck(! $staff->fresh()->login_allowed, 'Login re-enabled disabled staff.');
$staff->forceFill(['login_allowed' => true, 'password' => Hash::make('New-password-123')])->save();
$staff->refresh();
invalid(fn () => $login->authenticate($staff, 'Exact-password-123'), 'email');
invalid(fn () => $login->authenticate($staff, ' New-password-123 '), 'email');
$login->authenticate($staff, 'New-password-123');
staffCheck(Auth::id() === $staff->id && session('current_company_id') === 1, 'Login chose wrong company.');

// Tenant isolation with overlapping IDs.
tenant('beta-test'); Auth::guard('web')->setUser(User::findOrFail(1));
staffCheck(CompanyStaffRole::count() === 1 && User::count() === 1 && EmployeeDetail::count() === 0, 'Alpha data leaked into fresh Beta tenant.');
invalid(fn () => $login->authenticate($staff, 'New-password-123'), 'email');
staffAccount(2, 'BETA-EMP', CompanyStaffRole::firstOrFail(), array_replace($data, ['designation_id' => 3]));
staffCheck(User::where('email', 'lead@test.com')->first()->company_id === 2, 'Beta account adopted Alpha ownership.');
staffCheck(EmployeeDetail::first()->employee_id === 'BETA-EMP-0001', 'Employee IDs did not restart for company.');
// Full login request discovery flow, with the same email in two tenants and different passwords.
config(['database.connections.mysql' => ['driver' => 'sqlite', 'database' => 'alpha-test', 'prefix' => '']]);
DB::purge('mysql'); Auth::guard('web')->forgetUser();
$loginRequest = App\Http\Requests\Auth\LoginRequest::create('/login', 'POST', ['email' => 'lead@test.com', 'password' => 'Exact-password-123']);
$loginRequest->setContainer(app()); $loginRequest->setLaravelSession(app('session')->driver());
$loginRequest->authenticate();
staffCheck(Auth::user()->company_id === 2 && session('current_company_id') === 2 && config('database.connections.tenant.database') === 'beta-test', 'Login request chose another company with the same email.');
Auth::guard('web')->setUser(User::findOrFail(1));
echo "PASS: Add Upper Level Employee removed; existing company-role accounts keep secure login, isolation and admin-only management.\n";
