<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceCalendar
{
    /**
     * Calendar days for one employee between two dates, using the same records, holidays,
     * approved leaves and working days as the My Attendance calendar.
     */
    public function daysForUser(\App\Models\User $user, Carbon $from, Carbon $to): Collection
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();
        $first = $from->copy()->startOfMonth();
        $last = $to->copy()->endOfMonth();
        $tenant = \Illuminate\Support\Facades\Schema::connection('tenant');

        $records = \App\Models\Attendance::where('user_id', $user->id)->whereNull('archived_at')
            ->whereBetween('date', [$first->toDateString(), $last->toDateString()])->orderBy('id')->get();

        $holidayQuery = \App\Models\Holiday::whereBetween('date', [$first->toDateString(), $last->toDateString()]);
        if ($tenant->hasColumn('holidays', 'archived_at')) $holidayQuery->whereNull('archived_at');
        if ($tenant->hasColumn('holidays', 'company_id')) {
            $holidayQuery->where(fn ($query) => $query->where('company_id', $user->company_id)->orWhereNull('company_id'));
        }
        $holidays = $holidayQuery->get();

        $leaveQuery = \App\Models\Leave::where('user_id', $user->id)->where('status', 'approved');
        if ($tenant->hasColumn('leaves', 'archived_at')) $leaveQuery->whereNull('archived_at');
        $leaveQuery->where(function ($query) use ($first, $last, $tenant) {
            $query->whereBetween('date', [$first->toDateString(), $last->toDateString()]);
            if ($tenant->hasColumn('leaves', 'start_date')) {
                $query->orWhere(fn ($range) => $range->whereDate('start_date', '<=', $last)->whereDate('end_date', '>=', $first));
            }
        });
        $leaves = $leaveQuery->get();

        $workingDays = \App\Models\AppSetting::getCompanyWorkingDays();
        $schedule = WorkScheduleService::getSettings();
        $days = collect();
        for ($month = $first->copy(); $month->lte($to); $month->addMonthNoOverflow()->startOfMonth()) {
            $calendar = $this->build($month->copy(), $records, $holidays, $leaves, $user->employeeDetail, $workingDays, $user->id, $schedule);
            $days = $days->merge(array_filter($calendar['days'], fn ($day) => $day['date']->betweenIncluded($from, $to)));
        }

        return $days->values();
    }

    public function build(Carbon $month, Collection $records, Collection $holidays, Collection $leaves, $employeeDetail, array $workingDays, ?int $userId = null, array $schedule = []): array
    {
        $sessions = $records->groupBy(fn ($record) => Carbon::parse($record->date)->toDateString());
        $days = [];
        $totals = array_fill_keys(['present', 'absent', 'late', 'half_day', 'holiday', 'leave', 'unpaid_leave', 'day_off', 'wfh', 'wfh_present', 'seconds', 'scheduled_days'], 0);
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
            if (!$holiday) {
                foreach ($schedule['special_days'] ?? [] as $special) {
                    if (($special['date'] ?? '') === $key && ($special['type'] ?? '') === 'holiday') {
                        $holiday = (object) ['date' => $key, 'title' => $special['title'] ?? 'Company holiday'];
                        break;
                    }
                }
            }
            $status = 'not_marked';
            if (!$record && $joiningDate && $date->lt($joiningDate)) {
                $status = 'not_joined';
            } elseif ($record) {
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
                return strtolower((string) ($session->status ?? '')) === 'wfh'
                    || in_array(strtolower((string) ($session->work_from_type ?? $session->working_from ?? '')), ['home', 'wfh', 'work_from_home', 'work from home'], true);
            });
            $scheduledWfh = !in_array($status, ['holiday', 'day_off', 'not_joined', 'leave', 'unpaid_leave'], true)
                && $userId && $schedule && WorkScheduleService::isUserWfhOnDate($userId, $key, $schedule) !== null;
            $wfh = $wfh || $scheduledWfh;
            $seconds = (int) $daily->sum(fn ($session) => max(0, (int) ($session->total_seconds ?? 0)));
            if ($status !== 'wfh' && array_key_exists($status, $totals)) $totals[$status]++;
            if ($wfh) $totals['wfh']++;
            if ($status === 'wfh' && $record) $totals['wfh_present']++;
            $totals['seconds'] += $seconds;
            if (! $date->isFuture() && (! $joiningDate || $date->gte($joiningDate)) && ! in_array($status, ['holiday', 'day_off', 'not_joined'], true)) $totals['scheduled_days']++;
            $days[] = compact('date', 'key', 'status', 'daily', 'holiday', 'leave', 'seconds', 'wfh', 'scheduledWfh');
        }
        $totals['attendance_rate'] = $totals['scheduled_days'] > 0
            ? round(100 * ($totals['present'] + $totals['late'] + $totals['wfh_present'] + $totals['half_day'] * 0.5) / $totals['scheduled_days'], 1) : 0;
        return ['days' => $days, 'totals' => $totals, 'offset' => $month->copy()->startOfMonth()->dayOfWeek];
    }
}
