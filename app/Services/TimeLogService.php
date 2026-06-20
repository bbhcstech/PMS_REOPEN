<?php

namespace App\Services;

use App\Models\DailyTimesheet;
use App\Models\TaskTimer;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeLogService
{
    public function createManualLog(array $data, int $actorId): TaskTimer
    {
        return DB::transaction(function () use ($data, $actorId) {
            $log = TaskTimer::create($this->payload($data));
            $this->syncDailyTimesheet((int) $log->user_id, Carbon::parse($log->start_date)->toDateString());
            $this->recordActivity($actorId, 'Created time log #' . $log->id);

            return $log;
        });
    }

    public function updateManualLog(TaskTimer $log, array $data, int $actorId): TaskTimer
    {
        return DB::transaction(function () use ($log, $data, $actorId) {
            $oldUserId = (int) $log->user_id;
            $oldDate = Carbon::parse($log->start_date)->toDateString();

            $log->update($this->payload($data, $log));

            $this->syncDailyTimesheet($oldUserId, $oldDate);
            $this->syncDailyTimesheet((int) $log->user_id, Carbon::parse($log->start_date)->toDateString());
            $this->recordActivity($actorId, 'Updated time log #' . $log->id);

            return $log;
        });
    }

    public function completeTimer(TaskTimer $timer, array $data, int $actorId): TaskTimer
    {
        return DB::transaction(function () use ($timer, $data, $actorId) {
            $start = Carbon::parse($timer->start_time);
            $end = Carbon::now();
            $hours = $start->lt($end) ? round($end->diffInSeconds($start) / 3600, 2) : 0;

            $timer->update([
                'end_time' => $end,
                'memo' => $data['memo'] ?? $timer->memo,
                'remarks' => $data['remarks'] ?? $timer->remarks,
                'project_id' => $data['project_id'] ?? $timer->project_id ?? $timer->task?->project_id,
                'start_date' => $data['start_date'] ?? $start->toDateString(),
                'end_date' => $data['end_date'] ?? $end->toDateString(),
                'total_hours' => $hours,
                'status' => $timer->status ?: 'pending',
            ]);

            $this->syncDailyTimesheet((int) $timer->user_id, Carbon::parse($timer->start_date)->toDateString());
            $this->recordActivity($actorId, 'Stopped timer and logged ' . $hours . ' hour(s)');

            return $timer;
        });
    }

    public function deleteLog(TaskTimer $log, int $actorId): void
    {
        DB::transaction(function () use ($log, $actorId) {
            $userId = (int) $log->user_id;
            $date = Carbon::parse($log->start_date)->toDateString();
            $id = $log->id;

            $log->delete();
            $this->syncDailyTimesheet($userId, $date);
            $this->recordActivity($actorId, 'Deleted time log #' . $id);
        });
    }

    public function syncDailyTimesheet(int $userId, string $date): void
    {
        $summary = TaskTimer::where('user_id', $userId)
            ->whereDate('start_date', $date)
            ->whereNotNull('end_time')
            ->selectRaw('COALESCE(SUM(total_hours), 0) as total_hours, COUNT(*) as log_count')
            ->first();

        DailyTimesheet::updateOrCreate(
            ['user_id' => $userId, 'work_date' => $date],
            [
                'total_hours' => round((float) ($summary->total_hours ?? 0), 2),
                'log_count' => (int) ($summary->log_count ?? 0),
            ]
        );
    }

    private function payload(array $data, ?TaskTimer $existing = null): array
    {
        $start = Carbon::parse($data['start_date'] . ' ' . $data['start_time']);
        $end = Carbon::parse($data['end_date'] . ' ' . $data['end_time']);

        if ($end->lessThanOrEqualTo($start)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'end_time' => 'End time must be after start time.',
            ]);
        }

        return [
            'user_id' => (int) ($data['employee_id'] ?? $existing?->user_id ?? auth()->id()),
            'project_id' => (int) $data['project_id'],
            'task_id' => (int) $data['task_id'],
            'start_date' => $data['start_date'],
            'start_time' => $start,
            'end_date' => $data['end_date'],
            'end_time' => $end,
            'memo' => $data['memo'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'total_hours' => round($end->diffInSeconds($start) / 3600, 2),
            'status' => $existing?->status ?? 'pending',
        ];
    }

    private function recordActivity(int $actorId, string $activity): void
    {
        UserActivity::create([
            'company_id' => auth()->user()->company_id ?? null,
            'user_id' => $actorId,
            'activity' => $activity,
        ]);
    }
}
