<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentRequirement extends TenantModel
{
    protected $table = 'recruitment_requirements';

    protected $fillable = [
        'company_id',
        'title',
        'department_id',
        'department_name',
        'positions',
        'employment_type',
        'experience_required',
        'salary_range',
        'location',
        'description',
        'requirements_summary',
        'status',
        'created_by',
    ];

    protected $casts = [
        'positions' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-label-success',
            'in_progress' => 'bg-label-warning',
            'closed' => 'bg-label-secondary',
            'cancelled' => 'bg-label-danger',
            default => 'bg-label-info',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }
}
