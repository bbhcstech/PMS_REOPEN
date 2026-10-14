<?php

// Isolated in-memory database; never modifies application payroll records.
require __DIR__.'/payroll_processing_filters.php';

use App\Models\PayrollHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$assert = function ($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
};
DB::statement('ALTER TABLE payrolls ADD gross_total numeric');
DB::statement('ALTER TABLE payroll_histories ADD payroll_status text');
DB::table('payrolls')->where('id', 1)->update(['gross_total' => 999999]);
DB::table('payroll_histories')->where('id', 2)->update([
    'payroll_status' => 'approved',
    'snapshot' => json_encode(['gross_salary' => 1000, 'total_deductions' => 100, 'net_pay' => 900,
        'total_in_hand' => 950, 'total_employer_contribution' => 80, 'ctc' => 1130]),
]);
$dashboard = fn ($month = 10) => $controller->index(Request::create('/payroll', 'GET', ['year' => 2026, 'month' => $month]))->getData();
$data = $dashboard();
$assert($data['kpis']['total_employees'] === 2 && $data['kpis']['payroll_processed'] === 2, 'Employee scope differs from payroll eligibility');
$assert($data['kpis']['total_gross'] === 1300.0 && $data['kpis']['total_net_pay'] === 1170.0, 'Dashboard used stale aggregate or foreign/ineligible lines');
$assert($data['kpis']['total_employer_contribution'] === 80.0 && $data['statusBreakdown']['approved'] === 1, 'Snapshot contributions/status missing');
$empty = $dashboard(11)['kpis'];
$assert($empty['payroll_processed'] === 0 && $empty['payroll_pending'] === 2 && $empty['total_gross'] === 0.0, 'Empty month generated estimated amounts');
DB::table('payrolls')->insert([
    ['id' => 2, 'company_id' => 1, 'period_start' => '2026-10-01', 'period_end' => '2026-10-31', 'status' => 'draft', 'created_at' => '2026-10-10'],
    ['id' => 3, 'company_id' => 2, 'period_start' => '2026-10-01', 'period_end' => '2026-10-31', 'status' => 'draft', 'created_at' => '2026-10-11'],
]);
DB::table('payroll_histories')->insert([
    ['id' => 10, 'payroll_id' => 2, 'user_id' => 2, 'payroll_status' => 'calculated', 'snapshot' => json_encode(['gross_salary' => 2000, 'net_pay' => 1800])],
    ['id' => 11, 'payroll_id' => 3, 'user_id' => 2, 'payroll_status' => 'finalized', 'snapshot' => json_encode(['gross_salary' => 90000])],
]);
$before = PayrollHistory::count();
$updated = $dashboard();
$assert($updated['kpis']['total_gross'] === 2300.0 && $updated['kpis']['payroll_processed'] === 2, 'Rerun double-counted employee or omitted earlier department run');
$assert($updated['statusBreakdown']['calculated'] === 1 && $updated['statusBreakdown']['approved'] === 0, 'Rerun retained obsolete employee status');
DB::table('payroll_histories')->where('id', 10)->delete();
$assert($dashboard()['kpis']['total_gross'] === 1300.0, 'Dashboard did not reflect removed saved line');
$assert(PayrollHistory::count() === $before - 1, 'Viewing dashboard changed payroll records');
try {
    $dashboard(13);
    throw new RuntimeException('Invalid month accepted');
} catch (\Illuminate\Validation\ValidationException $e) {
    $assert(isset($e->errors()['month']), 'Month validation missing');
}
echo "PASS: dashboard uses saved monthly employee lines, zero empty-month amounts, live changes, latest reruns, multi-run totals, statuses, company isolation and read-only requests.\n";
