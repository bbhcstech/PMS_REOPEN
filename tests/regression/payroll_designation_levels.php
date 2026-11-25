<?php

require __DIR__.'/designation_company_levels.php';

use App\Models\User;
use App\Services\PayrollDesignations;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\ValidationException;

$actor = new User;
$actor->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'hr']);
Auth::guard('web')->setUser($actor);
DB::table('designations')->delete();
DB::table('designations')->insert([
    ['id' => 101, 'company_id' => 1, 'name' => 'Company Director', 'level' => 0, 'archived_at' => null],
    ['id' => 102, 'company_id' => 1, 'name' => 'Company Engineer', 'level' => 6, 'archived_at' => null],
    ['id' => 103, 'company_id' => 1, 'name' => 'Archived Role', 'level' => 2, 'archived_at' => '2026-10-12'],
    ['id' => 104, 'company_id' => 2, 'name' => 'Other Company Role', 'level' => 3, 'archived_at' => null],
    ['id' => 105, 'company_id' => 1, 'name' => 'Developer', 'level' => 7, 'archived_at' => null],
]);
$service = new PayrollDesignations;
checkLevels($service->available()->pluck('id')->all() === [101, 102, 105], 'Payroll designation list is not company scoped and limited to actual designation records.');
checkLevels($service->resolve(101, 'L0')->name === 'Company Director', 'L0 designation was lost.');
checkLevels($service->resolve(102, 'L6')->name === 'Company Engineer', 'L6 designation was lost.');
foreach ([[101, 'L6'], [101, 'D1'], [103, 'L2'], [104, 'L3']] as [$id, $grade]) {
    try {
        $service->resolve($id, $grade);
        throw new RuntimeException('Invalid designation/level was accepted.');
    } catch (ValidationException $e) {}
}
checkLevels($service->resolve(105, 'L7')->name === 'Developer', 'Real L7 designation was excluded.');
checkLevels(\App\Services\DesignationLevels::legend()->get(7) === 'Developer', 'Legend did not use real L7 name.');
checkLevels(!\App\Services\DesignationLevels::legend()->has(2), 'Archived designation appears in legend.');
$developer = $service->resolve(105, 'L7');
DB::statement('CREATE TABLE employee_salary_assignments (id integer primary key, user_id integer, status text, effective_from text, effective_to text, deleted_at text, actual_basic_salary real, grade text, salary_structure_id integer, hra_type text, hra_value real, special_allowance_type text, special_allowance_value real)');
DB::table('employee_salary_assignments')->insert(['id' => 1, 'user_id' => 1, 'status' => 'active', 'actual_basic_salary' => 10000, 'grade' => 'D1', 'hra_type' => 'fixed', 'hra_value' => 2000, 'special_allowance_type' => 'fixed', 'special_allowance_value' => 1000]);
foreach ([101 => 'L0', 105 => 'L7'] as $id => $expectedLevel) {
    $detail = new \App\Models\EmployeeDetail;
    $detail->setRelation('designation', $service->resolve($id, $expectedLevel));
    $salaryUser = new \App\Models\User;
    $salaryUser->forceFill(['id' => 1, 'company_id' => 1]);
    $salaryUser->setRelation('employeeDetail', $detail);
    $salary = (new \App\Services\PayrollCalculationService)->getSalaryStructure($salaryUser);
    checkLevels($salary['grade'] === $expectedLevel, 'Payroll used obsolete assignment grade instead of real designation level.');
    checkLevels($salary['basic'] === 10000.0 && $salary['gross'] === 13000.0, 'Designation fix changed salary amounts.');
}
$details = \App\Services\PayslipDesignation::details(['grade' => 'D1'], $developer);
checkLevels($details === ['name' => 'Developer', 'level' => 'L7'], 'Legacy payslip displayed a static grade.');
$details = \App\Services\PayslipDesignation::details(['designation' => 'Developer', 'designation_level' => 0], $developer);
checkLevels($details['level'] === 'L0', 'Saved level zero was lost or overwritten.');
$details = \App\Services\PayslipDesignation::details(['designation' => 'Historical Developer', 'designation_level' => 7], null);
checkLevels($details['name'] === 'Historical Developer' && $details['level'] === 'L7', 'Payslip history changed after designation removal.');
DB::table('designations')->where('id', 101)->update(['level' => 1]);
checkLevels($service->resolve(101, 'L1')->level == 1, 'Designation changes did not reach Payroll.');
DB::table('designations')->delete();
checkLevels($service->available()->isEmpty(), 'Empty designation section returned dummy options.');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/payroll/salary-structures.blade.php'))), TOKEN_PARSE);
echo "PASS: real company designations, L0/L6/L7 values, live designation changes, mismatched/foreign/archived rejection and real legend/payslip levels, empty data without dummy options and Blade syntax.\n";
