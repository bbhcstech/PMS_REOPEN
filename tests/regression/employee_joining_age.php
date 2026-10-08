<?php

// Run with: php tests/regression/employee_joining_age.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$method = new ReflectionMethod(App\Http\Controllers\EmployeeController::class, 'validateJoiningAge');
$controller = new App\Http\Controllers\EmployeeController;
$cases = [
    ['2030-02-01', '2026-10-08', false],
    ['2008-10-09', '2026-10-08', false],
    ['2008-10-08', '2026-10-08', true],
    ['2008-10-07', '2026-10-08', true],
    ['2000-02-29', '2018-02-27', false],
    ['2000-02-29', '2018-02-28', true],
    ['invalid', '2026-10-08', false],
    ['2000-01-01', 'invalid', false],
    ['', '2026-10-08', false],
];
foreach ($cases as [$dob, $joiningDate, $expected]) {
    $accepted = true;
    try {
        $method->invoke($controller, Illuminate\Http\Request::create('/employees', 'POST', [
            'dob' => $dob, 'joining_date' => $joiningDate,
        ]));
    } catch (Illuminate\Validation\ValidationException $exception) {
        $accepted = false;
    }
    if ($accepted !== $expected) {
        throw new RuntimeException("Unexpected age validation result: {$dob} / {$joiningDate}");
    }
}
echo "Employee joining age checks passed.\n";
