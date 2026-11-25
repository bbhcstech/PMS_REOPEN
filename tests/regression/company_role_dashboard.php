<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Schema, Auth};
use App\Models\{CompanyStaffRole, User};
use App\Services\CompanyRoleDashboard;
use App\Http\Controllers\DashboardController;
app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) { return $this->databases[$config['database']] ??= new PDO('sqlite::memory:'); }
});
foreach (['central' => 'registry', 'tenant' => 'alpha'] as $name => $db) {
    config(["database.connections.$name" => ['driver' => 'sqlite', 'database' => $db, 'prefix' => '']]); DB::purge($name);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::connection('central')->create('companies', function ($t) { $t->id(); $t->string('name'); $t->string('db_name'); $t->softDeletes(); $t->timestamps(); });
DB::connection('central')->table('companies')->insert([['id' => 1, 'name' => 'Alpha', 'db_name' => 'alpha'], ['id' => 2, 'name' => 'Beta', 'db_name' => 'beta']]);
foreach (['alpha' => 1, 'beta' => 2] as $db => $companyId) {
    config(['database.connections.tenant.database' => $db]); DB::purge('tenant');
    Schema::create('company_staff_roles', function ($t) { $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('access_role'); $t->timestamps(); });
    Schema::create('users', function ($t) { $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('role'); $t->integer('company_staff_role_id'); $t->boolean('is_active')->default(true); $t->boolean('login_allowed')->default(true); $t->string('designation')->nullable(); $t->timestamps(); });
    Schema::create('tasks', function ($t) { $t->id(); $t->integer('company_id'); $t->string('title'); $t->string('assigned_to'); $t->integer('project_id'); $t->string('status'); });
    Schema::create('projects', function ($t) { $t->id(); $t->integer('company_id'); $t->string('name'); $t->integer('created_by')->nullable(); $t->string('status'); });
    foreach (['project_user', 'assigned_task_user'] as $table) Schema::create($table, function ($t) use ($table) { $t->integer($table === 'project_user' ? 'project_id' : 'task_id'); $t->integer('user_id'); });
    foreach (['attendances', 'leaves'] as $table) Schema::create($table, function ($t) { $t->id(); $t->integer('company_id'); $t->integer('user_id'); $t->date('date'); $t->string('status'); });
    foreach (['hr', 'manager', 'employee'] as $i => $base) {
        CompanyStaffRole::create(['company_id' => $companyId, 'name' => "$db custom $base", 'access_role' => $base]);
        User::create(['company_id' => $companyId, 'name' => "$db $base", 'role' => $base, 'company_staff_role_id' => $i + 1, 'is_active' => true, 'login_allowed' => true, 'designation' => 'Software Engineer']);
    }
    DB::table('projects')->insert([['id' => 1, 'company_id' => $companyId, 'name' => "$db assigned", 'created_by' => 2, 'status' => 'active'], ['id' => 2, 'company_id' => 99, 'name' => 'Foreign row', 'created_by' => 2, 'status' => 'active']]);
    DB::table('project_user')->insert([['project_id' => 1, 'user_id' => 2], ['project_id' => 2, 'user_id' => 2]]);
    DB::table('tasks')->insert([
        ['id' => 1, 'company_id' => $companyId, 'title' => "$db personal", 'assigned_to' => '2,3', 'project_id' => 1, 'status' => 'open'],
        ['id' => 2, 'company_id' => $companyId, 'title' => "$db team task", 'assigned_to' => '12', 'project_id' => 1, 'status' => 'open'],
        ['id' => 3, 'company_id' => 99, 'title' => 'Foreign task', 'assigned_to' => '2', 'project_id' => 2, 'status' => 'open'],
    ]);
    DB::table('attendances')->insert([['company_id' => $companyId, 'user_id' => 2, 'date' => now()->toDateString(), 'status' => 'present'], ['company_id' => 99, 'user_id' => 2, 'date' => now()->toDateString(), 'status' => 'present']]);
    DB::table('leaves')->insert([['company_id' => $companyId, 'user_id' => 3, 'date' => now()->toDateString(), 'status' => 'pending'], ['company_id' => 99, 'user_id' => 2, 'date' => now()->toDateString(), 'status' => 'pending']]);
}
function roleCheck($ok, $reason) { if (!$ok) throw new RuntimeException($reason); }
$service = new CompanyRoleDashboard();
foreach (['alpha' => 1, 'beta' => 2] as $db => $id) {
    config(['database.connections.tenant.database' => $db]); DB::purge('tenant'); session()->put('current_company_id', $id);
    $manager = User::find(2); Auth::guard('web')->setUser($manager);
    $data = $service->data($manager);
    roleCheck($data['role']->name === "$db custom manager" && $data['taskCount'] === 1 && $data['projectCount'] === 1 && $data['attendanceDays'] === 1, 'Role/assignment/company filtering failed.');
    roleCheck($data['tasks']->first()->title === "$db personal" && $data['hr'] === null && $data['manager']['project_tasks'] === 2, 'Manager saw another role/company data.');
    $view = (new DashboardController())->index();
    roleCheck($view->name() === 'admin.company-role-dashboard', 'Custom engineering role redirected to another dashboard.');
    $loginRequest = new class extends App\Http\Requests\Auth\LoginRequest { public function authenticate(): void {} };
    $loginRequest->setLaravelSession(app('session')->driver());
    roleCheck((new App\Http\Controllers\Auth\AuthenticatedSessionController())->store($loginRequest)->getTargetUrl() === route('dashboard'), 'Login routed the custom role to another dashboard.');
    roleCheck((new DashboardController())->hrindex(Illuminate\Http\Request::create('/hr-dashboard'))->name() === 'admin.company-role-dashboard', 'HR shortcut opened another dashboard.');
    $employee = User::find(3); Auth::guard('web')->setUser($employee); $data = $service->data($employee);
    roleCheck($data['hr'] === null && $data['manager'] === null && $data['projectCount'] === 0 && $data['taskCount'] === 1, 'Employee acquired HR/manager oversight.');
    $hr = User::find(1); Auth::guard('web')->setUser($hr); $data = $service->data($hr);
    roleCheck($data['hr']['staff'] === 3 && $data['hr']['pending_leaves'] === 1 && $data['manager'] === null, 'HR data leaked or was not scoped.');
    $hr->role = 'manager';
    try { $service->data($hr); throw new RuntimeException('Mismatched role accepted.'); } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { roleCheck($e->getStatusCode() === 403, 'Wrong role denial.'); }
}
echo "PASS: automatic custom dashboards, engineering-title routing, HR/manager/employee data separation, own assignments, complete IDs and overlapping tenant isolation.\n";
