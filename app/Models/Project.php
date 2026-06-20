<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
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
        'client_id', 'created_by', 'name', 'project_code', 'category_id', 'department_id', 'team_id',
        'description', 'start_date', 'deadline', 'without_deadline', 'status', 'priority', 'notes', 'remarks',
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
}
