<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'manual_timelog' => 'boolean',
        'allow_client_notification' => 'boolean',
        'enable_miroboard' => 'boolean',
        'public_gantt_chart' => 'boolean',
        'public_taskboard' => 'boolean',
        'client_access' => 'boolean',
        'need_approval_by_admin' => 'boolean',
        'public' => 'boolean',
        'without_deadline' => 'boolean',
    ];

    protected $fillable = [
        'client_id', 'project_type', 'created_by', 'name', 'project_code', 'category_id', 'department_id', 'team_id',
        'description', 'start_date', 'deadline', 'without_deadline', 'status', 'payment_status', 'priority', 'notes', 'remarks',
        'public_gantt_chart', 'public_taskboard', 'client_access', 'need_approval_by_admin',
        'public', 'allow_client_notification', 'completion_percent',
        'calculate_task_progress', 'project_budget', 'hours_allocated', 'currency_id',
        'miro_board_id', 'enable_miroboard', 'manual_timelog'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function users()
{
    return $this->belongsToMany(\App\Models\User::class, 'project_user', 'project_id', 'user_id')
                ->withPivot('hourly_rate', 'role', 'assigned_by', 'assigned_at')
                ->withTimestamps();
}

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_project', 'project_id', 'department_id')
            ->withTimestamps();
    }

    
    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function activities()
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function updates()
    {
        return $this->hasMany(ProjectUpdate::class);
    }

    public function latestUpdate()
    {
        return $this->hasOne(ProjectUpdate::class)->latestOfMany();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'project_id');
    }

    public function notes()
    {
        return $this->hasMany(ProjectNote::class, 'project_id');
    }

    public function projectNotes()
    {
        return $this->hasMany(ProjectNote::class, 'project_id');
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class, 'project_id');
    }

    public function timelogs()
    {
        return $this->hasMany(TimeLog::class, 'project_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'project_id');
    }

    /**
     * Automatically calculate and update project progress (average of tasks)
     * and project status (majority of task statuses).
     */
    public function recalculateProgressAndStatus(): void
    {
        $tasks = $this->tasks()->get();

        if ($tasks->isEmpty()) {
            $this->completion_percent = 0;
            $this->status = 'not started';
            $this->saveQuietly();
            return;
        }

        // 1. Average of progress of each task
        $avgProgress = (int) round($tasks->avg('progress') ?? 0);
        $this->completion_percent = max(0, min(100, $avgProgress));

        // 2. Majority of status of all tasks
        $statusCounts = [
            'completed' => 0,
            'in progress' => 0,
            'not started' => 0,
            'pending' => 0,
            'on hold' => 0,
        ];

        foreach ($tasks as $task) {
            $rawStatus = strtolower(trim((string) $task->status));
            $mappedStatus = match ($rawStatus) {
                'completed', 'done' => 'completed',
                'doing', 'in progress', 'incomplete' => 'in progress',
                'waiting for approval', 'waiting', 'pending' => 'pending',
                'on hold', 'on-hold', 'hold' => 'on hold',
                'to do', 'todo', 'not started' => 'not started',
                default => 'not started',
            };
            $statusCounts[$mappedStatus]++;
        }

        // If all tasks are completed, status is strictly completed
        if ($statusCounts['completed'] === $tasks->count()) {
            $majorityStatus = 'completed';
        } else {
            // Determine majority (highest count)
            // Priority order for tie-breaking: in progress > pending > not started > completed > on hold
            $priority = ['in progress', 'pending', 'not started', 'completed', 'on hold'];
            $maxCount = 0;
            $majorityStatus = 'not started';

            foreach ($priority as $statusKey) {
                if ($statusCounts[$statusKey] > $maxCount) {
                    $maxCount = $statusCounts[$statusKey];
                    $majorityStatus = $statusKey;
                }
            }

            if ($this->completion_percent > 0 && $majorityStatus === 'not started') {
                $majorityStatus = 'in progress';
            }
        }

        $this->status = $majorityStatus;
        $this->saveQuietly();
    }
}

