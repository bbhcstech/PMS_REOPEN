<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
     protected $fillable = [
        'task_short_code',
        'title',
        'project_id',
        'start_date',
        'due_date',
        'assigned_to',
        'description',
        'task_labels',
        'milestone_id',
        'board_column_id',
        'is_private',
        'billable',
        'estimate_hours',
        'estimate_minutes',
        'repeat',
        'repeat_complete',
        'repeat_count',
        'repeat_type',
        'repeat_cycles',
        'dependent_task_id',
        'image_url',
        'priority',
        'category_id',
        'parent_id',
        'is_completed',
        'is_pinned',
        'status',
        'progress',
        'remarks',
        'created_by',
        'completed_on'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_pinned' => 'boolean',
        'completed_on' => 'datetime',
    ];

   // Relationships

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    // public function subTasks()
    // {
    //     return $this->hasMany(Task::class, 'parent_id');
    // }

    public function dependentTask()
    {
        return $this->belongsTo(Task::class, 'dependent_task_id');
    }

    public function boardColumn()
    {
        return $this->belongsTo(BoardColumn::class, 'board_column_id');
    }

    // Optional: Convert task_labels (comma-separated label IDs) to array
    public function getTaskLabelsArrayAttribute()
    {
        return $this->task_labels ? explode(',', $this->task_labels) : [];
    }
   
   
    public function assignees()
        {
            return $this->belongsToMany(User::class, 'assigned_task_user', 'task_id', 'user_id')
                ->withPivot(['assigned_by', 'assigned_at'])
                ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updates()
    {
        return $this->hasMany(TaskUpdate::class);
    }

    public function latestUpdate()
    {
        return $this->hasOne(TaskUpdate::class)->latestOfMany();
    }

  public function activeTimer()
   {
    return $this->hasOne(TaskTimer::class)->where('user_id', auth()->id())->whereNull('end_time');
  }
  
  //
  public function timers()
{
    return $this->hasMany(TaskTimer::class);
}

// Optional: helper to get total logged time
public function getTotalLoggedSecondsAttribute()
{
    return $this->timers()
        ->whereNotNull('end_time')
        ->get()
        ->sum(function ($timer) {
            return \Carbon\Carbon::parse($timer->start_time)
                ->diffInSeconds($timer->end_time);
        });
}

public function getTotalLoggedFormattedAttribute()
{
    $totalSeconds = $this->total_logged_seconds;

    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;

    return sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);
}
public function subTasks()
{
    return $this->hasMany(SubTask::class);
}

public function comments()
{
    return $this->hasMany(TaskComment::class);
}

public function timeLogs()
{
    return $this->hasMany(TimeLog::class);
}
public function tasktimeLogs()
{
    return $this->hasMany(TaskTimer::class);
}



public function notes()
{
    return $this->hasMany(TaskNote::class);

}

public function activityLogs()
{
    return $this->hasMany(TaskHistory::class, 'task_id');
}


public function task_labels()
{
    return $this->belongsToMany(TaskLabel::class, 'task_label_task');
}


}
