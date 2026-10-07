<?php

// Run with: php tests/regression/payroll_processing_filters.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\PayrollController;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

foreach (['tenant', 'mysql', 'central', 'session_db'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array', 'cache.default' => 'array']);
foreach ([
    'users' => 'id integer primary key, company_id integer, name text, role text',
    'employee_details' => 'id integer primary key, user_id integer, company_id integer, department_id integer, designation_id integer, business_address text, employment_type text',
    'departments' => 'id integer primary key, company_id integer, dpt_name text',
    'designations' => 'id integer primary key',
    'employee_salary_assignments' => 'id integer primary key, user_id integer, salary_structure_id integer, effective_from text, deleted_at text',
    'business_addresses' => 'id integer primary key, branch_name text',
    'payrolls' => 'id integer primary key, company_id integer, period_start text, period_end text, metadata text, status text, created_at text, deleted_at text',
    'payroll_histories' => 'id integer primary key, payroll_id integer, user_id integer, snapshot text',
] as $table => $columns) {
    DB::statement("CREATE TABLE {$table} ({$columns})");
}
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin'],
    ['id' => 2, 'company_id' => 1, 'name' => 'A', 'role' => 'employee'],
    ['id' => 3, 'company_id' => 1, 'name' => 'B', 'role' => 'employee'],
    ['id' => 4, 'company_id' => 2, 'name' => 'Foreign', 'role' => 'employee'],
]);
foreach ([2 => [10, 'HQ', 'full_time'], 3 => [20, 'Kolkata', 'part_time'], 4 => [10, 'HQ', 'full_time']] as $id => $detail) {
    DB::table('employee_details')->insert(['id' => $id, 'user_id' => $id, 'company_id' => $id === 4 ? 2 : 1,
        'department_id' => $detail[0], 'business_address' => $detail[1], 'employment_type' => $detail[2]]);
}
$actor = new class extends User {
    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return true; }
};
$actor->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($actor);
app(CompanyContext::class)->reset((object) ['id' => 1]);
DB::table('payrolls')->insert(['id' => 1, 'company_id' => 1, 'period_start' => '2026-10-01',
    'period_end' => '2026-10-31', 'metadata' => '{}', 'status' => 'draft', 'created_at' => '2026-10-08']);
foreach ([2, 3, 4] as $id) {
    DB::table('payroll_histories')->insert(['id' => $id, 'payroll_id' => 1, 'user_id' => $id,
        'snapshot' => json_encode(['gross_salary' => $id * 100, 'net_pay' => $id * 90])]);
}
$controller = new PayrollController;
$service = new PayrollCalculationService;
foreach ([
    [[], [2, 3], 500],
    [['department_id' => 10], [2], 200],
    [['department_id' => 20], [3], 300],
    [['office' => 'HQ'], [2], 200],
    [['employee_type' => 'part_time'], [3], 300],
    [['department_id' => 10, 'office' => 'Kolkata'], [], 0],
] as [$filters, $expectedIds, $expectedGross]) {
    $data = $controller->processing(Request::create('/payroll/processing', 'GET',
        $filters + ['year' => 2026, 'month' => 10]), $service)->getData();
    if ($data['payrollItems']->pluck('user_id')->all() !== $expectedIds
        || $data['summary']['total_gross'] != $expectedGross
        || $data['summary']['total_employees'] !== count($expectedIds)) {
        throw new RuntimeException('Payroll filter or summary failed: ' . json_encode($filters));
    }
}
if (DB::table('payroll_histories')->count() !== 3) {
    throw new RuntimeException('Viewing filtered payroll changed saved rows');
}
echo "PASS: saved payroll filters, company isolation, empty results, filtered totals, and read-only behavior\n";
