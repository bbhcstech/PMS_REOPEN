<?php

namespace App\Services;

use App\Models\{AppSetting, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Schema};

class AppraisalMetrics
{
    public function calculate(User $employee, string $period): array
    {
        [$start, $end] = app(AppraisalPolicy::class)->periodDates($period);
        $end = $end->min(now())->endOfDay();
        $schema = Schema::connection('tenant');
        $db = DB::connection('tenant');
        $joined = $employee->joining_date;
        if (!$joined && $schema->hasTable('employee_details')) {
            $joined = $db->table('employee_details')->where('user_id', $employee->id)->value('joining_date');
        }
        if ($joined) $start = $start->max(Carbon::parse($joined)->startOfDay());
        $daysOff = $schema->hasTable('holidays') && $schema->hasTable('app_settings') && $start->lte($end)
            ? WorkScheduleService::getHolidaysAndOffDays($start, $end, (int) $employee->company_id) : [];
        $workingDays = $schema->hasTable('app_settings') ? AppSetting::getCompanyWorkingDays() : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $eligibleDates = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if (in_array($day->format('l'), $workingDays, true) && !isset($daysOff[$day->toDateString()])) $eligibleDates[] = $day->toDateString();
        }
        $expectedCredits = array_fill_keys($eligibleDates, 1.0);
        if ($schema->hasTable('leaves')) {
            $leaves = $db->table('leaves')->where('user_id', $employee->id)->where('company_id', $employee->company_id)
                ->whereIn(DB::raw('LOWER(status)'), ['approved', 'active']);
            if ($schema->hasColumn('leaves', 'archived_at')) $leaves->whereNull('archived_at');
            foreach ($leaves->get() as $leave) {
                $from = substr($leave->start_date ?: $leave->date, 0, 10);
                $to = substr($leave->end_date ?: $leave->date ?: $leave->start_date, 0, 10);
                $half = !empty($leave->half_day_flag) || str_contains(strtolower((string) ($leave->duration ?? '')), 'half');
                foreach ($expectedCredits as $date => $credit) {
                    if ($from && $to && $date >= $from && $date <= $to) $expectedCredits[$date] = min($credit, $half ? 0.5 : 0);
                }
            }
            $expectedCredits = array_filter($expectedCredits, fn ($credit) => $credit > 0);
            $eligibleDates = array_keys($expectedCredits);
        }
        $attendance = $db->table('attendances')->where('user_id', $employee->id)->where('company_id', $employee->company_id)->whereIn('date', $eligibleDates);
        if ($schema->hasColumn('attendances', 'archived_at')) $attendance->whereNull('archived_at');
        $credits = $attendance->get()->groupBy(fn ($row) => substr($row->date, 0, 10))->map(function ($rows) {
            return $rows->max(function ($row) {
                $status = strtolower((string) $row->status);
                if (in_array($status, ['half_day', 'half-day', 'half day'], true)) return 0.5;
                return in_array($status, ['present', 'late'], true) ? 1 : 0;
            });
        });
        $credited = $credits->map(fn ($credit, $date) => min($credit, $expectedCredits[$date]))->sum();
        $attendanceScore = array_sum($expectedCredits) ? round($credited / array_sum($expectedCredits) * 100, 2) : 0;

        $assignedIds = $schema->hasTable('assigned_task_user')
            ? $db->table('assigned_task_user')->where('user_id', $employee->id)->pluck('task_id')->all() : [];
        $tasks = $db->table('tasks')->where('company_id', $employee->company_id)
            ->whereBetween(DB::raw('COALESCE(due_date, start_date, created_at)'), [$start->toDateString(), $end->toDateString().' 23:59:59'])->get()
            ->filter(function ($task) use ($employee, $assignedIds) {
                $directIds = preg_split('/[,\s]+/', trim((string) $task->assigned_to));
                return in_array((string) $employee->id, $directIds, true) || in_array($task->id, $assignedIds);
            });
        $completed = $tasks->filter(fn ($task) => ((bool) $task->is_completed || in_array(strtolower((string) $task->status), ['completed', 'finished', 'done'], true))
            && (empty($task->completed_on) || Carbon::parse($task->completed_on)->lte($end)))->count();
        return [
            'projects_count' => $tasks->pluck('project_id')->filter()->unique()->count(),
            'completed_tasks' => $completed, 'project_score' => $tasks->count() ? round($completed / $tasks->count() * 100, 2) : 0,
            'project_remarks' => $completed.' of '.$tasks->count().' assigned tasks completed in this review period.',
            'present_days' => $credits->filter(fn ($credit) => $credit > 0)->count(), 'total_working_days' => count($eligibleDates),
            'attendance_percentage' => $attendanceScore, 'attendance_score' => $attendanceScore,
            'attendance_remarks' => 'Recorded attendance on scheduled working days; half days count as 0.5. Future days, holidays and approved leave time are excluded.',
        ];
    }
}
