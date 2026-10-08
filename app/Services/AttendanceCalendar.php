<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceCalendar
{
    public function build(Carbon $month, Collection $records, Collection $holidays, Collection $leaves, $employeeDetail, array $workingDays): array
    {
        $sessions = $records->groupBy(fn ($record) => Carbon::parse($record->date)->toDateString());
        $days = [];
        $totals = array_fill_keys(['present', 'absent', 'late', 'half_day', 'holiday', 'leave', 'unpaid_leave', 'day_off', 'wfh', 'seconds', 'scheduled_days'], 0);
        $joiningDate = ! empty($employeeDetail->joining_date) ? Carbon::parse($employeeDetail->joining_date)->startOfDay() : null;
        for ($day = 1; $day <= $month->daysInMonth; $day++) {
            $date = $month->copy()->day($day)->startOfDay();
            $key = $date->toDateString();
            $daily = $sessions->get($key, collect());
            $holiday = $holidays->first(function ($holiday) use ($key, $employeeDetail) {
                if (Carbon::parse($holiday->date)->toDateString() !== $key) return false;
                foreach (['department_id_json' => 'department_id', 'designation_id_json' => 'designation_id', 'employment_type_json' => 'employment_type'] as $field => $attribute) {
                    $values = $holiday->$field ?? [];
                    if (is_string($values)) $values = json_decode($values, true) ?: [];
                    if ($values && ! in_array((string) ($employeeDetail->$attribute ?? ''), array_map('strval', (array) $values), true)) return false;
                }
                return true;
            });
            $leave = $leaves->first(function ($leave) use ($date) {
                $from = $leave->start_date ?? $leave->date;
                $to = $leave->end_date ?? $from;
                return $from && $date->betweenIncluded(Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay());
            });
            $record = $daily->last();
            $status = 'not_marked';
            if ($record) {
                $status = strtolower((string) $record->status);
                if (($record->half_day ?? 'no') === 'yes') $status = 'half_day';
                elseif (($record->late ?? 'no') === 'yes' && $status === 'present') $status = 'late';
            } elseif ($holiday) {
                $status = 'holiday';
            } elseif ($leave) {
                $status = ($leave->half_day_flag ?? false) || ($leave->duration ?? '') === 'half_day' ? 'half_day' : (($leave->is_unpaid ?? false) ? 'unpaid_leave' : 'leave');
            } elseif ($joiningDate && $date->lt($joiningDate)) {
                $status = 'not_joined';
            } elseif (! in_array($date->format('l'), $workingDays, true)) {
                $status = 'day_off';
            } elseif ($date->isPast() && ! $date->isToday()) {
                $status = 'absent';
            }
            $wfh = $daily->contains(function ($session) {
                return in_array(strtolower((string) ($session->work_from_type ?? $session->working_from ?? '')), ['home', 'wfh', 'work_from_home', 'work from home'], true);
            });
            $seconds = (int) $daily->sum(fn ($session) => max(0, (int) ($session->total_seconds ?? 0)));
            if (array_key_exists($status, $totals)) $totals[$status]++;
            if ($wfh) $totals['wfh']++;
            $totals['seconds'] += $seconds;
            if (! $date->isFuture() && (! $joiningDate || $date->gte($joiningDate)) && ! in_array($status, ['holiday', 'day_off', 'not_joined'], true)) $totals['scheduled_days']++;
            $days[] = compact('date', 'key', 'status', 'daily', 'holiday', 'leave', 'seconds', 'wfh');
        }
        $totals['attendance_rate'] = $totals['scheduled_days'] > 0
            ? round(100 * ($totals['present'] + $totals['late'] + $totals['half_day'] * 0.5) / $totals['scheduled_days'], 1) : 0;
        return ['days' => $days, 'totals' => $totals, 'offset' => $month->copy()->startOfMonth()->dayOfWeek];
    }
}
