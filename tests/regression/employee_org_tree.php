<?php

// Run with: php tests/regression/employee_org_tree.php  (in-memory models; no live data)
// Employees "Org Tree": valid reporting manager, then closest higher designation level,
// with same-department priority, tenant isolation, and direct/total team counts.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EmployeeController;
use App\Models\{Department, Designation, EmployeeDetail, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};

$designations = [];
$designation = function (int $level, string $name) use (&$designations) {
    return $designations[$name] ??= (new Designation)->forceFill(['id' => count($designations) + 1, 'name' => $name, 'level' => $level]);
};
$departments = [
    'eng' => (new Department)->forceFill(['id' => 1, 'dpt_name' => 'Engineering', 'parent_dpt_id' => 10]),
    'qa' => (new Department)->forceFill(['id' => 2, 'dpt_name' => 'Quality', 'parent_dpt_id' => 10]),
    'sales' => (new Department)->forceFill(['id' => 3, 'dpt_name' => 'Sales', 'parent_dpt_id' => 20]),
];
foreach ($departments as $department) $department->setRelation('parent', null);
$employee = function (int $id, string $name, ?string $dept, ?Designation $role, ?int $reportsTo = null, ?string $joined = null) use ($departments) {
    $user = (new User)->forceFill(['id' => $id, 'name' => $name, 'email' => strtolower($name) . '@example.test', 'role' => 'employee']);
    $detail = (new EmployeeDetail)->forceFill(['user_id' => $id, 'employee_id' => 'EMP-' . $id, 'status' => 'Active', 'reporting_to' => $reportsTo, 'joining_date' => $joined]);
    $detail->setRelation('designation', $role);
    $detail->setRelation('department', $dept ? $departments[$dept] : null);
    $detail->setRelation('reportingTo', null);
    $user->setRelation('employeeDetail', $detail);
    $user->setRelation('company', null);
    return $user;
};

$people = collect([
    $employee(1, 'Ceo', 'eng', $designation(6, 'CEO'), null, now()->subYears(3)->toDateString()),
    $employee(2, 'EngLead', 'eng', $designation(4, 'Engineering Lead')),
    $employee(3, 'Dev', 'eng', $designation(2, 'Developer')),               // auto: closest superior in Engineering = EngLead
    $employee(4, 'Tester', 'qa', $designation(2, 'Tester')),                // auto: no QA superior -> parent department (Eng lead, L4)
    $employee(5, 'SalesRep', 'sales', $designation(2, 'Sales Rep'), 1),     // explicit reporting manager wins
    $employee(6, 'NewHire', 'eng', null),                                   // no level -> below every level; closest = Intern (L0)
    $employee(9, 'Intern', 'eng', $designation(0, 'Intern')),                // L0 = most junior -> under Dev (L2), never above anyone
    $employee(7, 'LoopA', 'sales', $designation(5, 'VP'), 8),
    $employee(8, 'LoopB', 'sales', $designation(5, 'VP'), 7),               // reporting cycle must not hang or drop nodes
]);

$method = new ReflectionMethod(EmployeeController::class, 'orgTreeNodes');
$nodes = $method->invoke(app(EmployeeController::class), $people);

$check(count($nodes) === 9, 'every visible employee becomes a node');
$check($nodes[1]['parent'] === null, 'top designation level without a manager is a root');
$check($nodes[2]['parent'] === 1 && $nodes[2]['placement'] === 'level', 'lead is placed under the closest senior level automatically');
$check($nodes[3]['parent'] === 2, 'developer lands under the lead of the same department');
$check($nodes[4]['parent'] === 2, 'department without a superior falls back to the parent department');
$check($nodes[5]['parent'] === 1 && $nodes[5]['placement'] === 'reporting', 'saved reporting manager takes priority over level placement');
$check($nodes[9]['parent'] === 3, 'L0 is the most junior level and sits under the nearest higher level');
$check($nodes[6]['parent'] === 9, 'employee without a designation level sits under the closest leveled colleague');

$walk = function (int $id) use ($nodes) {
    $seen = [];
    while ($id !== null) { if (isset($seen[$id])) return false; $seen[$id] = true; $id = $nodes[$id]['parent']; }
    return true;
};
$check($walk(7) && $walk(8), 'reporting cycles are broken so the chart always terminates');
$check($nodes[7]['parent'] === 1 && $nodes[8]['parent'] === 1, 'same-level reporting links fall back to a higher level');
$check($nodes[1]['directs'] === 4 && $nodes[2]['directs'] === 2, 'direct team counts are correct');
$check($nodes[1]['total'] === 8 && $nodes[2]['total'] === 4, 'total staff counts include the whole branch');
$check(str_contains((string) $nodes[1]['tenure'], 'yr'), 'tenure is derived from the joining date');
$check(str_ends_with($nodes[3]['show_url'], '/employees/3'), 'node links to the existing employee profile');

$companyRole = fn (int $company, int $level, string $name) => $designation($level, $name)->forceFill(['company_id' => $company]);
$tallTree = collect([
    $employee(20, 'Director', 'eng', $companyRole(1, 900, 'Director'), 21)->forceFill(['company_id' => 1]),
    $employee(21, 'Principal', 'eng', $companyRole(1, 18, 'Principal'))->forceFill(['company_id' => 1]),
    $employee(22, 'Analyst', 'eng', $companyRole(1, 0, 'Analyst'), 30)->forceFill(['company_id' => 1]),
    $employee(23, 'Engineer', 'eng', $companyRole(1, 0, 'Engineer'), 20)->forceFill(['company_id' => 1]),
    $employee(24, 'Designer', 'eng', $companyRole(1, 0, 'Designer'))->forceFill(['company_id' => 1]),
    $employee(25, 'Writer', 'eng', $companyRole(1, 0, 'Writer'))->forceFill(['company_id' => 1]),
    $employee(30, 'AnotherDirector', 'eng', $companyRole(2, 901, 'Other Director'))->forceFill(['company_id' => 2]),
]);
$tallNodes = $method->invoke(app(EmployeeController::class), $tallTree);
$check($tallNodes[20]['parent'] === null && $tallNodes[20]['reporting_to_id'] === 21, 'a reversed reporting link leaves the record intact and places the senior employee above it');
$check($tallNodes[21]['parent'] === 20 && $tallNodes[22]['parent'] === 21 && $tallNodes[23]['parent'] === 20, 'levels above L6 and skipped levels retain valid recorded and inferred connections');
$check($tallNodes[22]['level'] === 0 && $tallNodes[21]['directs'] === 3 && $tallNodes[20]['total'] === 5, 'L0 stays numeric and managers can have more than two direct reports');
$check($tallNodes[30]['total'] === 0 && $tallNodes[20]['level'] === 900, 'different companies never share a reporting branch');

foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
DB::connection('central')->statement('CREATE TABLE companies (id integer primary key, name text, deleted_at text)');
DB::connection('central')->statement("INSERT INTO companies (id, name) VALUES (1, 'Alpha'), (2, 'Beta')");
DB::statement('CREATE TABLE users (id integer primary key, name text, email text, company_id integer, role text, archived_at text)');
DB::statement('CREATE TABLE designations (id integer primary key, name text, level integer, company_id integer, added_by integer)');
DB::statement('CREATE TABLE employee_details (id integer primary key, user_id integer, designation_id integer, employee_id text, status text, reporting_to integer)');
DB::statement("INSERT INTO users (id, name, email, company_id, role) VALUES
    (101, 'Director', 'director@example.test', 1, 'admin'), (102, 'Staff', 'staff@example.test', 1, 'employee'),
    (201, 'Other manager', 'manager@example.test', 2, 'manager')");
DB::statement("INSERT INTO designations VALUES (1, 'Director', 18, null, 101), (2, 'Staff', 0, 1, 101), (3, 'Manager', 32, 2, 201)");
DB::statement("INSERT INTO employee_details VALUES (1, 101, 1, 'EMP-101', 'Active', null), (2, 102, 2, 'EMP-102', 'Active', 101), (3, 201, 3, 'EMP-201', 'Active', null)");
$controller = app(EmployeeController::class);
$requestAs = function (User $viewer, array $query = []) {
    Auth::guard('web')->setUser($viewer);
    $request = Request::create('/employees/org-tree', 'GET', $query);
    $request->setUserResolver(fn () => $viewer);

    return $request;
};
$admin = User::findOrFail(101);
$payload = $controller->orgTree($requestAs($admin))->getData(true);
$check(array_column($payload['nodes'], 'level') === [18, 0] && $payload['company']['name'] === 'Alpha', 'endpoint includes company leadership and legacy designation levels');
$check($payload['nodes'][1]['parent'] === 101 && $payload['nodes'][1]['reporting_to_name'] === 'Director', 'endpoint returns reporting links and employee details');
$staffPayload = $controller->orgTree($requestAs(User::findOrFail(102)))->getData(true);
$check(array_column($staffPayload['nodes'], 'id') === [102], 'employee access retains the existing roster visibility filter');
try {
    $controller->orgTree($requestAs($admin, ['company_id' => 2]));
    $check(false, 'company administrators cannot request another company');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $check($e->getStatusCode() === 403, 'company administrators cannot request another company');
}
$platform = (new User)->forceFill(['id' => 9001, 'role' => 'superadmin']);
$platformPayload = $controller->orgTree($requestAs($platform, ['company_id' => 2]))->getData(true);
$check(array_column($platformPayload['nodes'], 'level') === [32] && $platformPayload['company']['name'] === 'Beta', 'platform view loads the selected company designations');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: org tree placement, cycle safety and counts.\n";
exit($failures ? 1 : 0);
