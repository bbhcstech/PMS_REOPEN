<?php

namespace App\Http\Controllers\Concerns\Dashboard;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimer;
use App\Models\UserActivity;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait DashboardTimers
{
 public function timersstore(Request $request)
{
    // Check if creating a new task
    $createNewTask = $request->has('create_task');

    // Validation
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'task_id'    => $createNewTask ? 'nullable' : 'required|exists:tasks,id',
        'new_task_name' => $createNewTask ? 'required|string|max:255' : 'nullable',
        'memo'       => 'required|string',
        'project_status' => 'nullable|in:not started,in progress,on hold,completed',
    ]);

    $project = Project::findOrFail($request->project_id);
    abort_unless($this->canUseTimerProject($project), 403);

    // If creating a new task, save it first
    if ($createNewTask) {
        abort_unless($this->canCreateWorkItems(), 403);

        $task = new Task();
        $task->company_id = Auth::user()->company_id ?? null;
        $task->created_by = Auth::id();
        $task->assigned_to = (string) Auth::id();
        $task->title = $request->new_task_name;
        $task->project_id = $request->project_id;
        $task->start_date = now()->toDateString();
        $task->status = 'In Progress';
        $task->task_short_code = 'TSK-' . strtoupper(substr(uniqid(), -4));
        $task->save();
        $taskId = $task->id;
    } else {
        $task = Task::findOrFail($request->task_id);
        abort_unless((int) $task->project_id === (int) $project->id && $this->canUseTimerTask($task), 403);

        $taskId = $request->task_id;
    }

    // Save the timer
    $timer = new TaskTimer();
    $timer->project_id = $request->project_id;
    $timer->task_id = $taskId;
    $timer->user_id  = Auth::id();
    $timer->start_date = now()->toDateString();
    $timer->start_time = now(); // current timestamp
    $timer->memo = $request->memo;
    $timer->save();

    $this->updateProjectStatusForTimer($project, $request->project_status);

    if (strtolower((string) auth()->user()?->role) === 'employee') {
        SystemNotificationService::notifyAdmins(
            'Timer Started',
            auth()->user()->name . ' started timer for ' . $project->name,
            route('timelogs.index'),
            ['project_id' => $project->id, 'task_id' => $taskId, 'type' => 'timer_started', 'icon' => 'fa-clock']
        );
    }

    return redirect()->back()->with('success', 'Timer started successfully!');
}

private function canCreateWorkItems(): bool
{
    return in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager', 'superadmin', 'super_admin', 'super-admin'], true);
}

private function canUseTimerProject(Project $project): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ($this->canCreateWorkItems()) {
        return true;
    }

    if (strtolower((string) $user->role) !== 'employee') {
        return false;
    }

    return $project->users()->where('users.id', $user->id)->exists()
        || Task::where('project_id', $project->id)
            ->where(function ($query) use ($user) {
                $query->whereHas('assignees', function ($assignees) use ($user) {
                    $assignees->where('users.id', $user->id);
                })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$user->id]);
            })
            ->exists();
}

private function canUseTimerTask(Task $task): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ($this->canCreateWorkItems()) {
        return true;
    }

    if (strtolower((string) $user->role) !== 'employee') {
        return false;
    }

    return $task->assignees()->where('users.id', $user->id)->exists()
        || collect(explode(',', (string) $task->assigned_to))
            ->map(fn ($id) => (int) trim($id))
            ->contains((int) $user->id);
}

private function updateProjectStatusForTimer(Project $project, ?string $status): void
{
    if (! $status) {
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
