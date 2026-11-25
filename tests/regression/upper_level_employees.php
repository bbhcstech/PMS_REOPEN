<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\UpperLevelEmployeeController;
use App\Models\{CompanyStaffRole, EmployeeDetail, User};
use App\Services\{CompanyContext, CompanyStaffSchema, CompanyStaffLogin};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Hash, Schema};
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
    $r = Request::create('/admin/upper-level-employees', 'POST', $data);
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
        $t->unsignedBigInteger('reporting_to')->nullable(); $t->unsignedBigInteger('department_id')->nullable();
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
    $controller = new UpperLevelEmployeeController;
    $controller->storeRole(req(['name' => 'Project Operations Lead', 'access_role' => 'manager', 'company_id' => 99]));
    staffCheck(CompanyStaffRole::first()->company_id === $companyId, 'Role trusted submitted company ID.');
    staffCheck(CompanyStaffRole::count() === 1, 'Company roles were prepopulated or copied.');
}
tenant('alpha-test'); Auth::guard('web')->setUser(User::findOrFail(1));
$controller = new UpperLevelEmployeeController;
$controller->storeRole(req(['name' => 'People Partner', 'access_role' => 'hr']));
invalid(fn () => $controller->storeRole(req(['name' => 'Dangerous', 'access_role' => 'admin'])), 'access_role');
invalid(fn () => $controller->storeRole(req(['name' => 'People Partner', 'access_role' => 'hr'])), 'name');
$foreignRole = CompanyStaffRole::create(['company_id' => 99, 'name' => 'Foreign role', 'access_role' => 'manager']);
$data = ['name' => 'Lead', 'email' => 'lead@test.com', 'password' => 'Exact-password-123', 'password_confirmation' => 'Exact-password-123',
    'company_staff_role_id' => 1, 'designation_id' => 1, 'joining_date' => '2026-10-09', 'login_allowed' => 1, 'company_id' => 99, 'level' => 99, 'role' => 'admin'];
invalid(fn () => $controller->store(req(array_replace($data, ['company_staff_role_id' => $foreignRole->id]))), 'company_staff_role_id');
invalid(fn () => $controller->store(req(array_replace($data, ['designation_id' => 2]))), 'designation_id');
invalid(fn () => $controller->store(req(array_replace($data, ['designation_id' => 3]))), 'designation_id');
invalid(fn () => $controller->store(req(array_replace($data, ['email' => 'hr@company.com']))), 'email');
$controller->store(req($data));
$staff = User::where('email', 'lead@test.com')->firstOrFail();
staffCheck($staff->company_id === 1 && $staff->role === 'manager' && $staff->company_staff_role_id === 1, 'Account ownership/access role incorrect.');
staffCheck(Hash::check($data['password'], $staff->password) && $staff->raw_password === null, 'Password was not stored securely.');
staffCheck($staff->employeeDetail->designation_id === 1 && $staff->employeeDetail->company_id === 1 && $staff->employeeDetail->employee_id === 'ALPHA-EMP-0001', 'Employee profile or designation missing.');
invalid(fn () => $controller->store(req($data)), 'email');
DB::connection('central')->table('companies')->where('id', 1)->update(['max_users' => 2]);
invalid(fn () => $controller->store(req(array_replace($data, ['email' => 'over-limit@test.com']))), 'name');
DB::connection('central')->table('companies')->where('id', 1)->update(['max_users' => 100]);
DB::connection('tenant')->statement("CREATE TRIGGER reject_test_detail BEFORE INSERT ON employee_details BEGIN SELECT RAISE(ABORT, 'test rollback'); END");
try {
    $controller->store(req(array_replace($data, ['email' => 'rollback@test.com'])));
    throw new RuntimeException('Expected profile persistence failure.');
} catch (\Illuminate\Database\QueryException $e) {}
DB::connection('tenant')->statement('DROP TRIGGER reject_test_detail');
staffCheck(! User::where('email', 'rollback@test.com')->exists(), 'Profile failure left a partial login account.');
$foreignUser = User::create(['company_id' => 99, 'name' => 'Other company HR', 'email' => 'foreign@test.com', 'password' => 'unused', 'role' => 'hr']);
denied(fn () => $controller->update(req($data), $foreignUser->id), 404);
$view = $controller->index(req([]))->getData();
staffCheck($view['accounts']->pluck('id')->all() === [$staff->id], 'Foreign account leaked into list.');
staffCheck($view['roles']->count() === 2 && $view['designations']->count() === 1, 'Foreign or over-limit choices leaked.');
$employeeList = (new App\Http\Controllers\EmployeeController)->index(req([]))->getData();
staffCheck($employeeList['employees']->pluck('id')->all() === [$staff->id], 'Manager did not appear in company employee list or a foreign user leaked.');
denied(fn () => (new App\Http\Controllers\EmployeeController)->index(req(['company_id' => 99])));
$template = file_get_contents(dirname(__DIR__, 2) . '/resources/views/admin/employees/upper-level.blade.php');
$template = preg_replace('/@extends\([^\n]*\)|@section\([^\n]*\)|@endsection/', '', $template);
$html = Blade::render($template, $view + ['errors' => new \Illuminate\Support\ViewErrorBag]);
staffCheck(str_contains($html, 'Project Operations Lead') && str_contains($html, 'Level 2') && ! str_contains($html, 'Exact-password-123'), 'Account view missing data or leaking password.');
foreach (['hr', 'manager', 'employee', 'superadmin'] as $role) {
    $actor = new User(['company_id' => 1, 'role' => $role]); Auth::guard('web')->setUser($actor);
    denied(fn () => $controller->index(req([])));
    denied(fn () => $controller->store(req($data)));
    denied(fn () => $controller->storeRole(req(['name' => 'Not allowed', 'access_role' => 'hr'])));
    if (in_array($role, ['hr', 'manager'], true)) {
        $legacy = new App\Http\Controllers\EmployeeController;
        denied(fn () => $legacy->bulkUpdateStatus(req(['employee_ids' => [$staff->id], 'status' => 'Active'])));
        denied(fn () => $legacy->bulkDelete(req(['employee_ids' => [$staff->id]])));
        denied(fn () => $legacy->edit($staff->id));
    }
}
Auth::guard('web')->setUser(User::findOrFail(1));
denied(fn () => (new App\Http\Controllers\Admin\RoleAccountController)->resetPassword(req(['password' => 'Bypass-password-123']), 'manager', $staff));
denied(fn () => (new App\Http\Controllers\EmployeeController)->acceptInviteSubmit(req(['user_id' => $staff->id, 'name' => 'Bypass', 'password' => 'Bypass-password-123', 'password_confirmation' => 'Bypass-password-123'])));
$controller->update(req(array_replace($data, ['password' => '', 'password_confirmation' => '', 'login_allowed' => 0])), $staff->id);
$staff->refresh(); staffCheck(Hash::check('Exact-password-123', $staff->password) && ! $staff->login_allowed, 'Optional password or disabled login changed incorrectly.');
$login = new CompanyStaffLogin;
invalid(fn () => $login->authenticate($staff, 'Exact-password-123'), 'email');
staffCheck(! $staff->fresh()->login_allowed, 'Login re-enabled disabled staff.');
$controller->update(req(array_replace($data, ['password' => 'New-password-123', 'password_confirmation' => 'New-password-123'])), $staff->id);
$staff->refresh();
invalid(fn () => $login->authenticate($staff, 'Exact-password-123'), 'email');
invalid(fn () => $login->authenticate($staff, ' New-password-123 '), 'email');
$login->authenticate($staff, 'New-password-123');
staffCheck(Auth::id() === $staff->id && session('current_company_id') === 1, 'Login chose wrong company.');
tenant('beta-test'); Auth::guard('web')->setUser(User::findOrFail(1));
staffCheck(CompanyStaffRole::count() === 1 && User::count() === 1 && EmployeeDetail::count() === 0, 'Alpha data leaked into fresh Beta tenant.');
invalid(fn () => $login->authenticate($staff, 'New-password-123'), 'email');
$beta = array_replace($data, ['designation_id' => 3]); $controller->store(req($beta));
staffCheck(User::where('email', 'lead@test.com')->first()->company_id === 2, 'Beta account adopted Alpha ownership.');
staffCheck(EmployeeDetail::first()->employee_id === 'BETA-EMP-0001', 'Employee IDs did not restart for company.');
// Exercise the full login request discovery flow, with the same email in two
// tenants and different passwords, rather than only the staff login service.
config(['database.connections.mysql' => ['driver' => 'sqlite', 'database' => 'alpha-test', 'prefix' => '']]);
DB::purge('mysql'); Auth::guard('web')->forgetUser();
$loginRequest = App\Http\Requests\Auth\LoginRequest::create('/login', 'POST', ['email' => 'lead@test.com', 'password' => 'Exact-password-123']);
$loginRequest->setContainer(app()); $loginRequest->setLaravelSession(app('session')->driver());
$loginRequest->authenticate();
staffCheck(Auth::user()->company_id === 2 && session('current_company_id') === 2 && config('database.connections.tenant.database') === 'beta-test', 'Login request chose another company with the same email.');
Auth::guard('web')->setUser(User::findOrFail(1));
config(['database.connections.tenant.database' => 'wrong-db']);
denied(fn () => $controller->index(req([])));
echo "PASS: company-specific roles, admin-only management, secure passwords/login, designation limits, view rendering and isolation across tenants with overlapping IDs.\n";
