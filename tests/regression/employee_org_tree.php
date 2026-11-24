<?php

// Run with: php tests/regression/employee_org_tree.php  (in-memory models; no live data)
// Employees "Org Tree": placement by reporting manager, then closest superior by designation
// level (same department first), cycle safety, and direct/total counts.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EmployeeController;
use App\Models\{Department, Designation, EmployeeDetail, User};

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
    $employee(1, 'Ceo', 'eng', $designation(0, 'CEO'), null, now()->subYears(3)->toDateString()),
    $employee(2, 'EngLead', 'eng', $designation(2, 'Engineering Lead')),
    $employee(3, 'Dev', 'eng', $designation(4, 'Developer')),               // auto: closest superior in Engineering = EngLead
    $employee(4, 'Tester', 'qa', $designation(4, 'Tester')),                // auto: no QA superior -> parent department (Eng, level 2)
    $employee(5, 'SalesRep', 'sales', $designation(4, 'Sales Rep'), 1),     // explicit reporting manager wins
    $employee(6, 'NewHire', 'eng', null),                                   // no level -> lowest; closest = Dev (level 4)
    $employee(7, 'LoopA', 'sales', $designation(1, 'VP'), 8),
    $employee(8, 'LoopB', 'sales', $designation(1, 'VP'), 7),               // reporting cycle must not hang or drop nodes
]);

$method = new ReflectionMethod(EmployeeController::class, 'orgTreeNodes');
$nodes = $method->invoke(app(EmployeeController::class), $people);

$check(count($nodes) === 8, 'every visible employee becomes a node');
$check($nodes[1]['parent'] === null, 'top designation level without a manager is a root');
$check($nodes[2]['parent'] === 1 && $nodes[2]['placement'] === 'level', 'lead is placed under the closest senior level automatically');
$check($nodes[3]['parent'] === 2, 'developer lands under the lead of the same department');
$check($nodes[4]['parent'] === 2, 'department without a superior falls back to the parent department');
$check($nodes[5]['parent'] === 1 && $nodes[5]['placement'] === 'reporting', 'saved reporting manager takes priority over level placement');
$check($nodes[6]['parent'] === 3, 'employee without a designation level sits under the closest leveled colleague');

$walk = function (int $id) use ($nodes) {
    $seen = [];
    while ($id !== null) { if (isset($seen[$id])) return false; $seen[$id] = true; $id = $nodes[$id]['parent']; }
    return true;
};
$check($walk(7) && $walk(8), 'reporting cycles are broken so the chart always terminates');
$check($nodes[1]['directs'] === 2 && $nodes[2]['directs'] === 2, 'direct report counts are correct');
$check($nodes[1]['total'] >= 5 && $nodes[2]['total'] === 3, 'total staff counts include the whole branch');
$check(str_contains((string) $nodes[1]['tenure'], 'yr'), 'tenure is derived from the joining date');
$check(str_ends_with($nodes[3]['show_url'], '/employees/3'), 'node links to the existing employee profile');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: org tree placement, cycle safety and counts.\n";
exit($failures ? 1 : 0);
