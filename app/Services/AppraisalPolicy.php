<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AppraisalPolicy
{
    public function settings(): array
    {
        $values = Schema::connection('tenant')->hasTable('app_settings')
            ? AppSetting::where('key', 'like', 'perf_%')->pluck('value', 'key')->all() : [];
        $defaults = [
            'appraisal_cycle' => 'Quarterly', 'work_weight' => 40, 'attendance_weight' => 30,
            'behaviour_weight' => 30, 'admin_approval_required' => '1',
            'increment_enabled' => '1', 'increment_a_plus' => 10, 'increment_a' => 7,
            'increment_b' => 3, 'increment_c' => 0, 'increment_d' => 0,
        ];
        foreach ($defaults as $key => $value) $defaults[$key] = $values['perf_'.$key] ?? $value;
        return $defaults;
    }

    public function canManage(User $actor): bool
    {
        return WorkforceAccess::isAdmin($actor) || $actor->normalizedRole() === 'hr';
    }

    public function canEvaluate(User $actor, User $employee): bool
    {
        return $this->canManage($actor) && (int) $actor->company_id === (int) $employee->company_id
            && (WorkforceAccess::isAdmin($actor) || (int) $actor->id !== (int) $employee->id)
            && in_array($employee->normalizedRole(), ['employee', 'manager', 'hr'], true);
    }

    public function periodDates(string $period): array
    {
        if (preg_match('/^(\d{4}) Q([1-4])$/', $period, $m)) {
            $start = Carbon::create((int) $m[1], ((int) $m[2] - 1) * 3 + 1, 1)->startOfDay();
            return [$start, $start->copy()->addMonths(3)->subDay()->endOfDay()];
        }
        if (preg_match('/^Annual (\d{4})$/', $period, $m)) {
            $start = Carbon::create((int) $m[1], 1, 1)->startOfDay();
            return [$start, $start->copy()->endOfYear()];
        }
        if (preg_match('/^(\d{4}) H([12])$/', $period, $m)) {
            $start = Carbon::create((int) $m[1], $m[2] === '1' ? 1 : 7, 1)->startOfDay();
            return [$start, $start->copy()->addMonths(6)->subDay()->endOfDay()];
        }
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $period, $m)) {
            $start = Carbon::create((int) $m[1], (int) $m[2], 1)->startOfDay();
            return [$start, $start->copy()->endOfMonth()];
        }
        throw ValidationException::withMessages(['appraisal_period' => 'Choose a valid appraisal period.']);
    }

    public function currentPeriod(): string
    {
        $now = now();
        return match ($this->settings()['appraisal_cycle']) {
            'Bi-Annually' => $now->year.' H'.($now->month <= 6 ? 1 : 2),
            'Annually', 'Annual' => 'Annual '.$now->year,
            'Quarterly' => $now->year.' Q'.$now->quarter,
            default => $now->format('Y-m'),
        };
    }

    public function periods(): array
    {
        $periods = [$this->currentPeriod()];
        $cycle = $this->settings()['appraisal_cycle'];
        for ($year = now()->year; $year >= now()->year - 2; $year--) {
            switch ($cycle) {
                case 'Annually':
                case 'Annual':
                    $periods[] = 'Annual '.$year;
                    break;
                case 'Bi-Annually':
                    foreach ([2, 1] as $half) $periods[] = $year.' H'.$half;
                    break;
                case 'Quarterly':
                    foreach (range(4, 1) as $quarter) $periods[] = $year.' Q'.$quarter;
                    break;
                default:
                    foreach (range(12, 1) as $month) $periods[] = sprintf('%d-%02d', $year, $month);
            }
        }
        return array_values(array_unique($periods));
    }

    public function score(float $work, float $attendance, float $behaviour, ?array $settings = null): float
    {
        $settings ??= $this->settings();
        return round(($work * $settings['work_weight'] + $attendance * $settings['attendance_weight'] + $behaviour * $settings['behaviour_weight']) / 100, 2);
    }

    public function increment(string $grade, ?array $settings = null): float
    {
        $settings ??= $this->settings();
        if ($settings['increment_enabled'] !== '1') return 0;
        $key = match ($grade) { 'A+' => 'a_plus', 'A' => 'a', 'B' => 'b', 'C' => 'c', default => 'd' };
        return (float) $settings['increment_'.$key];
    }
}
