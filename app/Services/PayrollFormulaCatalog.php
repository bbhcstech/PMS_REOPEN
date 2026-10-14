<?php

namespace App\Services;

class PayrollFormulaCatalog
{
    public function categories(): array
    {
        return ['earnings' => 'Employee earnings', 'deduction' => 'Employee deductions', 'tax' => 'Employee tax',
            'bonus' => 'Bonus & appraisal payments', 'custom' => 'Employer contributions', 'statutory' => 'Employer statutory costs'];
    }

    public function definitions(): array
    {
        return [
            'CURRENT_BASIC' => ['earnings', ['BASIC', 'PAYABLE_DAYS', 'WORKING_DAYS', 'APPRAISAL_SCORE', 'APPRAISAL_INCREMENT_PERCENT', 'APPRAISAL_ATTENDANCE_SCORE', 'APPRAISAL_WORK_SCORE', 'APPRAISAL_BEHAVIOUR_SCORE']],
            'GROSS_SALARY' => ['earnings', ['CURRENT_BASIC', 'HRA', 'SPECIAL_ALLOWANCE', 'OTHER_ALLOWANCES']],
            'PF_EMPLOYEE' => ['deduction', ['CURRENT_BASIC', 'GROSS_SALARY']],
            'ESI_EMPLOYEE' => ['deduction', ['CURRENT_BASIC', 'GROSS_SALARY']],
            'PROFESSIONAL_TAX' => ['tax', ['CURRENT_BASIC', 'GROSS_SALARY']],
            'TOTAL_DEDUCTIONS' => ['deduction', ['PF', 'ESI', 'PT', 'TDS', 'OTHER_DEDUCTIONS']],
            'NET_PAY' => ['earnings', ['GROSS_SALARY', 'TOTAL_DEDUCTIONS']],
            'ATTENDANCE_BONUS' => ['bonus', ['WORKING_HOURS']], 'BEST_EMPLOYEE_BONUS' => ['bonus', ['BE_RANK']],
            'TOTAL_ADDITIONAL_EARNINGS' => ['bonus', ['ATTENDANCE_BONUS', 'BEST_EMPLOYEE_BONUS', 'TA', 'OVERTIME', 'COMMISSION', 'ADJUSTMENTS', 'CUSTOM_BONUSES']],
            'TOTAL_IN_HAND' => ['earnings', ['NET_PAY', 'TOTAL_ADDITIONAL_EARNINGS']],
            'PF_EMPLOYER' => ['custom', ['CURRENT_BASIC', 'GROSS_SALARY']], 'ESI_EMPLOYER' => ['custom', ['CURRENT_BASIC', 'GROSS_SALARY']],
            'EDLI_EMPLOYER' => ['custom', ['CURRENT_BASIC']],
            'TOTAL_EMPLOYER_CONTRIBUTIONS' => ['custom', ['EMPLOYER_PF', 'EMPLOYER_ESI', 'EDLI', 'OTHER_EMPLOYER']],
            'CTC' => ['custom', ['GROSS_SALARY', 'TOTAL_ADDITIONAL_EARNINGS', 'TOTAL_EMPLOYER_CONTRIBUTION', 'TOTAL_EMPLOYER_CONTRIBUTIONS']],
        ];
    }

    public function allowedVariables(string $code, string $category): array
    {
        if (isset($this->definitions()[$code])) return $this->definitions()[$code][1];
        $base = ['BASIC', 'CURRENT_BASIC', 'HRA', 'SPECIAL_ALLOWANCE', 'PAYABLE_DAYS', 'WORKING_DAYS', 'WORKING_HOURS',
            'APPRAISAL_SCORE', 'APPRAISAL_INCREMENT_PERCENT', 'APPRAISAL_ATTENDANCE_SCORE', 'APPRAISAL_WORK_SCORE', 'APPRAISAL_BEHAVIOUR_SCORE'];
        if ($category !== 'earnings') $base[] = 'GROSS_SALARY';
        if (in_array($category, ['bonus', 'custom', 'statutory'], true)) $base = array_merge($base, ['NET_PAY', 'OVERTIME_HOURS', 'OVERTIME_RATE', 'BE_RANK']);
        return $base;
    }

    public function samples(): array
    {
        return ['BASIC' => 30000, 'PAYABLE_DAYS' => 20, 'WORKING_DAYS' => 22, 'CURRENT_BASIC' => 27272.73,
            'HRA' => 10909.09, 'SPECIAL_ALLOWANCE' => 0, 'OTHER_ALLOWANCES' => 0, 'GROSS_SALARY' => 38181.82,
            'PF' => 1800, 'ESI' => 0, 'PT' => 0, 'TDS' => 0, 'OTHER_DEDUCTIONS' => 763.64, 'TOTAL_DEDUCTIONS' => 2563.64,
            'NET_PAY' => 35618.18, 'WORKING_HOURS' => 160, 'BE_RANK' => 0, 'OVERTIME_HOURS' => 0, 'OVERTIME_RATE' => 150,
            'TA' => 0, 'OVERTIME' => 0, 'COMMISSION' => 0, 'ADJUSTMENTS' => 0, 'ATTENDANCE_BONUS' => 0,
            'BEST_EMPLOYEE_BONUS' => 0, 'CUSTOM_BONUSES' => 2100, 'TOTAL_ADDITIONAL_EARNINGS' => 2100,
            'EMPLOYER_PF' => 1800, 'EMPLOYER_ESI' => 0, 'EDLI' => 0, 'OTHER_EMPLOYER' => 0, 'TOTAL_EMPLOYER_CONTRIBUTIONS' => 1800, 'TOTAL_EMPLOYER_CONTRIBUTION' => 1800,
            'APPRAISAL_SCORE' => 85, 'APPRAISAL_INCREMENT_PERCENT' => 7,
            'APPRAISAL_ATTENDANCE_SCORE' => 90, 'APPRAISAL_WORK_SCORE' => 80, 'APPRAISAL_BEHAVIOUR_SCORE' => 85];
    }

    public function examples(): array
    {
        return [
            ['name' => 'Attendance-based basic', 'code' => 'CURRENT_BASIC', 'category' => 'earnings', 'formula' => 'WORKING_DAYS > 0 ? BASIC * PAYABLE_DAYS / WORKING_DAYS : 0', 'expected' => 27272.73],
            ['name' => 'Example recovery deduction', 'code' => 'EXAMPLE_RECOVERY', 'category' => 'deduction', 'formula' => 'ROUND(GROSS_SALARY * 0.02, 2)', 'expected' => 763.64],
            ['name' => 'Approved appraisal payment', 'code' => 'APPRAISAL_BONUS', 'category' => 'bonus', 'formula' => 'IF(APPRAISAL_SCORE >= 80, BASIC * APPRAISAL_INCREMENT_PERCENT / 100, 0)', 'expected' => 2100],
        ];
    }
}
