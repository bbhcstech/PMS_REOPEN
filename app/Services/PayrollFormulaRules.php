<?php

namespace App\Services;

use App\Models\{Appraisal, PayrollFormula, User};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PayrollFormulaRules
{
    public function effective(int $companyId, Carbon $period): Collection
    {
        return PayrollFormula::where('company_id', $companyId)->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $period->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $period->toDateString()))
            ->orderBy('id')->get()->keyBy('code');
    }

    public function evaluate(PayrollFormula $formula, array $variables): float
    {
        try {
            if (!$formula->is_valid) throw new \InvalidArgumentException('This active formula needs validation. Edit and save it before running payroll.');
            $result = app(PayrollExpression::class)->evaluate($formula->formula, $variables);
            if ($result < 0) throw new \InvalidArgumentException('A payroll component cannot be negative.');
            return round($result, 2);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['formula' => 'Payroll stopped: '.$formula->code.' — '.$e->getMessage()]);
        }
    }

    public function custom(Collection $formulas, array $categories, array $variables): array
    {
        $builtIn = app(PayrollFormulaCatalog::class)->definitions(); $amounts = [];
        foreach ($formulas as $formula) {
            if (isset($builtIn[$formula->code]) || !in_array($formula->category, $categories, true)) continue;
            $amounts[$formula->code] = $this->evaluate($formula, $variables);
        }
        return $amounts;
    }

    public function appraisalVariables(User $employee, Carbon $period): array
    {
        $values = array_fill_keys(['APPRAISAL_SCORE', 'APPRAISAL_INCREMENT_PERCENT', 'APPRAISAL_ATTENDANCE_SCORE', 'APPRAISAL_WORK_SCORE', 'APPRAISAL_BEHAVIOUR_SCORE'], 0.0);
        if (!Schema::connection('tenant')->hasTable('appraisals')) return $values;
        $records = Appraisal::where('company_id', $employee->company_id)->where('employee_id', $employee->id)->where('status', 'approved')
            ->where('updated_at', '<=', $period->copy()->endOfMonth())->latest('updated_at')->get();
        foreach ($records as $record) {
            try { [, $end] = app(AppraisalPolicy::class)->periodDates($record->appraisal_period); }
            catch (ValidationException $e) { continue; }
            if ($end->gt($period->copy()->endOfMonth())) continue;
            return ['APPRAISAL_SCORE' => (float) $record->overall_score, 'APPRAISAL_INCREMENT_PERCENT' => (float) $record->recommended_increment_percent,
                'APPRAISAL_ATTENDANCE_SCORE' => (float) $record->attendance_score, 'APPRAISAL_WORK_SCORE' => (float) $record->project_score,
                'APPRAISAL_BEHAVIOUR_SCORE' => (float) $record->behaviour_score];
        }
        return $values;
    }
}
