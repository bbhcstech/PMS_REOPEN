<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Models\TaskTimer;
use App\Models\Project;
use App\Models\UserActivity;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskTimerController extends Controller
{
   // Start Timer
public function start(Task $task)
{
    TaskTimer::where('user_id', auth()->id())->whereNull('end_time')->update(['end_time' => now()]);

    TaskTimer::create([
        'task_id' => $task->id,
        'user_id' => auth()->id(),
        'start_time' => now()
    ]);

    return back()->with('success', 'Timer started.');
}

// Pause Timer
public function pause(Task $task)
{
    TaskTimer::where('task_id', $task->id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->update(['pause_time' => now()]);

    return back()->with('success', 'Timer paused.');
}

// Resume Timer
public function resume(Task $task)
{
    $timer = TaskTimer::where('task_id', $task->id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->whereNotNull('pause_time')
        ->first();

    if ($timer) {
        $timer->update(['pause_time' => null]);
        return back()->with('success', 'Timer resumed.');
    }

    return back()->with('error', 'No paused timer found.');
}


// Stop Timer
public function stop(Request $request, Task $task)
{
    
    $request->validate([
        'memo' => 'required|string|max:500',
        'project_status' => 'nullable|in:not started,in progress,on hold,completed',
    ]);
    $timer = TaskTimer::where('id', $request->timer_id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->first();

    if ($timer) {
        $start = Carbon::parse($timer->start_time);
        $end = Carbon::now();

        if ($start->lt($end)) {
            $seconds = $end->diffInSeconds($start);
            $total_hours_decimal = round($seconds / 3600, 2);
        } else {
            $seconds = 0;
            $total_hours_decimal = 0;
        }

        $timer->end_time = $end;
        $timer->memo = $request->memo;
        $timer->project_id = $request->project_id ?? $timer->project_id ?? $task->project_id;
        $timer->start_date = $request->start_date ?? $start->toDateString();
        $timer->end_date = $request->end_date ?? $end->toDateString();
        $timer->total_hours = $total_hours_decimal;
        $timer->save();

        $this->updateProjectStatusForTimer($timer->project, $request->project_status);

        if (strtolower((string) auth()->user()?->role) === 'employee') {
            SystemNotificationService::notifyAdmins(
                'Timer Logged',
                auth()->user()->name . ' logged ' . $total_hours_decimal . ' hour(s) for ' . ($timer->project?->name ?? 'a project'),
                route('timelogs.show', $timer->id),
                ['project_id' => $timer->project_id, 'task_id' => $timer->task_id, 'type' => 'timer_stopped', 'icon' => 'fa-clock']
            );
        }
    }

    return back()->with('success', 'Timer stopped and logged.');
}


public function globalstop(Request $request, Task $task)
{
    $request->validate([
        'timer_id'   => 'required|exists:task_timers,id',
        'memo'       => 'required|string|max:500',
        'project_id' => 'nullable|exists:projects,id',
        'start_date' => 'nullable|date',
        'end_date'   => 'nullable|date',
        'project_status' => 'nullable|in:not started,in progress,on hold,completed',
    ]);

    $timer = TaskTimer::where('id', $request->timer_id)
        ->where('user_id', auth()->id())
        ->whereNull('end_time')
        ->first();

    if ($timer) {
        $start = Carbon::parse($timer->start_time);
        $end   = Carbon::now();

        $seconds = $start->lt($end) ? $end->diffInSeconds($start) : 0;
        $total_hours_decimal = round($seconds / 3600, 2);

        $timer->update([
            'end_time'    => $end,
            'memo'        => $request->memo,
            'project_id'  => $request->project_id ?? $timer->project_id,
            'start_date'  => $request->start_date ?? $start->toDateString(),
            'end_date'    => $request->end_date ?? $end->toDateString(),
            'total_hours' => $total_hours_decimal,
        ]);

        $this->updateProjectStatusForTimer($timer->project, $request->project_status);

        if (strtolower((string) auth()->user()?->role) === 'employee') {
            SystemNotificationService::notifyAdmins(
                'Timer Logged',
                auth()->user()->name . ' logged ' . $total_hours_decimal . ' hour(s) for ' . ($timer->project?->name ?? 'a project'),
                route('timelogs.show', $timer->id),
                ['project_id' => $timer->project_id, 'task_id' => $timer->task_id, 'type' => 'timer_stopped', 'icon' => 'fa-clock']
            );
        }
    }

    return back()->with('success', 'Timer stopped and logged.');
}

private function updateProjectStatusForTimer(?Project $project, ?string $status): void
{
    if (! $project || ! $status) {
        return;
    }

    if (! in_array($status, ['not started', 'in progress', 'on hold', 'completed'], true)) {
        return;
    }

    if ($project->status === $status) {
        return;
    }

    $project->forceFill(['status' => $status])->save();

    UserActivity::create([
        'company_id' => auth()->user()->company_id ?? null,
        'user_id' => auth()->id(),
        'activity' => 'Changed project status from timer: ' . $project->name . ' -> ' . $status,
    ]);
}


}
