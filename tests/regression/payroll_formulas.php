<?php

// Uses actual SQLite salary assignments, attendance, appraisals and formula records.
// No demo writes occur in the user's company databases.
require __DIR__.'/payroll_self_service.php';

use App\Models\{Appraisal, PayrollFormula, PayrollPolicy, PayrollAuditLog, User};
use App\Services\{PayrollExpression, PayrollFormulaCatalog, PayrollFormulaRules};
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$as(1);
Carbon::setTestNow(Carbon::parse('2026-09-30 18:00:00', 'Asia/Kolkata'));
$engine = app(PayrollExpression::class);
foreach (app(PayrollFormulaCatalog::class)->examples() as $example) check(abs(round($engine->evaluate($example['formula'], app(PayrollFormulaCatalog::class)->samples()), 2) - $example['expected']) < .01, 'Example result incorrect');
check($engine->evaluate('X > 0 ? 5 : 1 / 0', ['X' => 1]) === 5.0, 'Conditional branch was not lazy');
check($engine->evaluate('IF(X > 0, 1, 1 / 0)', ['X' => 2]) === 1.0, 'IF was not lazy');
check($engine->evaluate('MAX(0, MIN(2000, BASIC * 0.1))', ['basic' => 30000]) === 2000.0, 'Safe functions or case-insensitive variables failed');
check($engine->evaluate('A == 1 ? 400 : A == 2 ? 200 : 0', ['A' => 2]) === 200.0, 'Legacy nested conditional failed');
foreach (['system(1)', 'BASIC + MISSING', '1 / 0', '1E309', '1; 2', 'ROUND()', '1 + )', str_repeat('(', 70).'1'.str_repeat(')', 70)] as $invalid) {
    try { $engine->evaluate($invalid, ['BASIC' => 30000]); throw new RuntimeException('Invalid expression accepted: '.$invalid); }
    catch (InvalidArgumentException $e) {}
}
DB::table('users')->insert(['id' => 101, 'name' => 'Formula Test Employee', 'role' => 'employee', 'company_id' => 3, 'email' => 'formula-test@example.test']);
DB::table('employee_details')->insert(['user_id' => 101, 'employee_id' => 'TEST-101', 'designation_id' => 1, 'department_id' => 1, 'joining_date' => '2026-01-01', 'employment_type' => 'full_time']);
$assignment = Request::create('/payroll/employee-salary', 'POST', ['user_id' => 101, 'actual_basic_salary' => 30000,
    'hra_type' => 'percentage', 'hra_value' => 40, 'special_allowance_type' => 'percentage', 'special_allowance_value' => 0,
    'effective_from' => '2026-01-01', 'status' => 'active']);
$controller->storeEmployeeSalary($assignment);
$day = Carbon::parse('2026-09-01'); $present = 0;
while ($day->month === 9) {
    if (!$day->isWeekend()) {
        DB::table('attendances')->insert(['user_id' => 101, 'company_id' => 3, 'date' => $day->toDateString(), 'status' => $present < 20 ? 'present' : 'absent',
            'clock_in' => $present < 20 ? '09:00:00' : null, 'clock_out' => $present < 20 ? '17:00:00' : null]);
        $present++;
    }
    $day->addDay();
}
foreach (['2026_08_15_000006_create_appraisals_table.php', '2026_10_08_000004_add_deleted_at_to_appraisals_table.php', '2026_10_14_000002_add_appraisal_policy_snapshot.php'] as $migration) (require database_path('migrations/tenant/'.$migration))->up();
Appraisal::create(['company_id' => 3, 'employee_id' => 101, 'appraisal_period' => '2026 Q2', 'overall_score' => 85, 'attendance_score' => 90,
    'project_score' => 80, 'behaviour_score' => 85, 'recommended_increment_percent' => 7, 'status' => 'approved']);
Appraisal::create(['company_id' => 3, 'employee_id' => 101, 'appraisal_period' => '2026 Q4', 'overall_score' => 100, 'recommended_increment_percent' => 99, 'status' => 'approved']);
Appraisal::create(['company_id' => 3, 'employee_id' => 101, 'appraisal_period' => '2026 Q3', 'overall_score' => 100, 'recommended_increment_percent' => 99, 'status' => 'submitted']);
PayrollPolicy::where('company_id', 3)->update(['status' => 'draft']);
PayrollPolicy::create(['company_id' => 3, 'name' => 'Formula-only integration fixture', 'code' => 'FORMULA-TEST', 'status' => 'published',
    'version' => 99, 'effective_from' => '2026-01-01', 'deductions_rules' => ['pf_enabled' => false, 'esi_enabled' => false, 'pt_enabled' => false]]);
$post = fn (array $data) => Request::create('/payroll/formulas', 'POST', $data);
$create = function ($code, $category, $expression, $extra = []) use ($controller, $post) {
    $controller->storeFormula($post(array_merge(['name' => $code.' test', 'code' => $code, 'category' => $category, 'formula' => $expression,
        'effective_from' => '2026-09-01', 'is_active' => '1'], $extra)));
    return PayrollFormula::where('code', strtoupper($code))->firstOrFail();
};
$base = $create('CURRENT_BASIC', 'earnings', 'WORKING_DAYS > 0 ? BASIC * PAYABLE_DAYS / WORKING_DAYS : 0');
$recovery = $create('EXAMPLE_RECOVERY', 'deduction', 'ROUND(GROSS_SALARY * 0.02, 2)');
$appraisal = $create('APPRAISAL_BONUS', 'bonus', 'IF(APPRAISAL_SCORE >= 80, BASIC * APPRAISAL_INCREMENT_PERCENT / 100, 0)');
$employer = $create('EMPLOYER_WELFARE', 'custom', 'BASIC * 0.01');
$create('FUTURE_RULE', 'deduction', '999', ['effective_from' => '2026-10-01']);
$create('EXPIRED_RULE', 'deduction', '999', ['effective_from' => '2026-08-01', 'effective_to' => '2026-08-31']);
PayrollFormula::create(['company_id' => 9, 'name' => 'Other company rule', 'code' => 'FOREIGN_RULE', 'category' => 'deduction', 'formula' => '99999', 'is_active' => true]);
$employee = User::findOrFail(101);
$line = $service->calculateEmployeePayrollLine($employee, 2026, 9, 22);
foreach (['basic' => 30000, 'payable_days' => 20, 'current_basic' => 27272.73, 'current_gross' => 38181.82, 'other_deductions' => 763.64,
    'total_deductions' => 763.64, 'net_pay' => 37418.18, 'total_additional_earnings' => 2100, 'total_in_hand' => 39518.18, 'other_employer_contribution' => 300, 'ctc' => 40581.82] as $field => $expected) {
    check(abs((float) $line[$field] - $expected) < .01, $field.' expected '.$expected.' but got '.($line[$field] ?? 'missing'));
}
check($line['pt'] === 0.0 && $line['pf'] === 0.0 && $line['esi'] === 0.0, 'Disabled statutory policy was ignored');
check(($line['custom_formula_amounts']['APPRAISAL_BONUS'] ?? null) === 2100.0 && count($line['custom_formula_amounts']) === 3, 'Foreign/future/expired formula or unapproved appraisal affected payroll');
check(collect($line['deduction_reasons'])->contains(fn ($reason) => str_contains($reason, 'EXAMPLE_RECOVERY')), 'Deduction explanation missing');
check(PayrollAuditLog::where('action', 'created_payroll_formula')->count() === 6, 'Formula mutations are not audited');
$testResponse = $controller->testFormulaLive($post(['formula' => $recovery->formula, 'variables' => ['GROSS_SALARY' => 38181.82]]));
check($testResponse->getData()->result === 763.64, 'Live tester differs from payroll');
$badTest = $controller->testFormulaLive($post(['formula' => 'BASIC / ZERO', 'variables' => ['BASIC' => 30000, 'ZERO' => 0]]));
check($badTest->getStatusCode() === 422 && !$badTest->getData()->success, 'Invalid live test accepted');
$bad = function ($callback) {
    try { $callback(); throw new RuntimeException('Invalid formula was saved'); } catch (ValidationException $e) {}
};
$bad(fn () => $create('WRONG_STAGE', 'deduction', 'NET_PAY * 0.1'));
$bad(fn () => $create('current_basic', 'earnings', '1'));
$bad(fn () => $create('REVERSED_DATES', 'bonus', '1', ['effective_to' => '2026-08-01']));
$bad(fn () => $create('BROKEN_INPUTS', 'bonus', 'BASIC * 0.1', ['test_inputs' => '{broken}']));
$controller->toggleFormulaActive($post([]), $recovery);
check(!$recovery->fresh()->is_active, 'Disable did not persist');
$disabledVersion = $recovery->fresh()->version;
$controller->toggleFormulaActive($post(['desired_active' => '0']), $recovery->fresh());
check(!$recovery->fresh()->is_active && $recovery->fresh()->version === $disabledVersion, 'Repeated disable toggled the rule back on');
$withoutRecovery = $service->calculateEmployeePayrollLine($employee, 2026, 9, 22);
check($withoutRecovery['other_deductions'] === 0.0, 'Disabled custom deduction still applied');
$controller->toggleFormulaActive($post([]), $recovery->fresh());
$recovery = $recovery->fresh();
$update = ['name' => 'Changed recovery', 'category' => 'deduction', 'formula' => 'GROSS_SALARY * 0.01', 'version' => $recovery->version,
    'effective_from' => '2026-09-01', 'is_active' => '1'];
$controller->updateFormula($post($update), $recovery);
$bad(fn () => $controller->updateFormula($post($update), $recovery->fresh()));
check($recovery->fresh()->version === $recovery->version + 1, 'Version was not incremented');
$controller->destroyFormula($post([]), $recovery->fresh());
check($recovery->fresh()->trashed(), 'Archive did not soft-delete the formula');
$archivedPage = $controller->formulas(Request::create('/payroll/formulas', 'GET', ['status' => 'archived']))->getData();
check($archivedPage['formulas']->contains('id', $recovery->id), 'Archived rule is not recoverable from the UI');
check(str_contains($render('admin.payroll.formulas', $archivedPage), 'Restore as disabled'), 'Archived restore UI missing');
$controller->restoreFormula($post([]), PayrollFormula::withTrashed()->findOrFail($recovery->id));
check(!$recovery->fresh()->trashed() && !$recovery->fresh()->is_active, 'Restored formula was reactivated unexpectedly');
$as(2);
try { $controller->storeFormula($post(['name' => 'Unauthorized'])); throw new RuntimeException('HR changed payroll formulas'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { check($e->getStatusCode() === 403, 'Wrong HR denial'); }
$as(1);
$foreign = PayrollFormula::where('company_id', 9)->firstOrFail();
try { $controller->toggleFormulaActive($post([]), $foreign); throw new RuntimeException('Foreign formula changed'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { check($e->getStatusCode() === 404, 'Wrong company denial'); }
$viewData = $controller->formulas(Request::create('/payroll/formulas', 'GET'))->getData();
check(!$viewData['formulas']->contains('company_id', 9), 'Foreign formulas visible');
$html = $render('admin.payroll.formulas', $viewData);
foreach (['kpi-grid', 'Try sample payroll data', 'Appraisal', 'formulaEditorModal', 'formulaTesterModal'] as $text) check(str_contains($html, $text), 'Formula UI missing '.$text);
PayrollFormula::whereKey($base->id)->update(['formula' => 'BASIC / WORKING_DAYS']);
$bad(fn () => $service->calculateEmployeePayrollLine($employee, 2026, 9, 0));
PayrollFormula::whereKey($base->id)->update(['formula' => 'BASIC * (1 + APPRAISAL_INCREMENT_PERCENT / 100) * PAYABLE_DAYS / WORKING_DAYS']);
$adjusted = $service->calculateEmployeePayrollLine($employee, 2026, 9, 22);
check($adjusted['current_basic'] === 29181.82, 'Approved appraisal increase did not adjust calculated basic');
check((float) App\Models\EmployeeSalaryAssignment::where('user_id', 101)->value('actual_basic_salary') === 30000.0, 'Formula permanently changed the salary assignment');
Carbon::setTestNow();
echo 'PASS: real salary ₹30,000, 20/22 payable days, gross ₹38,181.82, deduction ₹763.64, approved appraisal payment ₹2,100, employee in-hand ₹39,518.18, employer cost ₹300, CTC ₹40,581.82.', PHP_EOL;
echo 'PASS: safe expression parser, actual payroll integration, company/effective-date isolation, admin-only CRUD, audit, stale versions, tester equivalence, zero-denominator failures and rendered responsive UI.', PHP_EOL;
