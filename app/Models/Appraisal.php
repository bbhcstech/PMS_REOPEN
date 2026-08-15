<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appraisal extends TenantModel
{
    protected $table = 'appraisals';

    protected $fillable = [
        'company_id',
        'employee_id',
        'appraisal_period',
        'projects_count',
        'completed_tasks',
        'project_score',
        'project_remarks',
        'present_days',
        'total_working_days',
        'attendance_percentage',
        'attendance_score',
        'attendance_remarks',
        'teamwork_score',
        'communication_score',
        'punctuality_score',
        'behaviour_score',
        'behaviour_remarks',
        'overall_score',
        'overall_grade',
        'recommendation',
        'status',
        'evaluated_by',
    ];

    protected $casts = [
        'projects_count' => 'integer',
        'completed_tasks' => 'integer',
        'present_days' => 'integer',
        'total_working_days' => 'integer',
        'project_score' => 'float',
        'attendance_percentage' => 'float',
        'attendance_score' => 'float',
        'teamwork_score' => 'float',
        'communication_score' => 'float',
        'punctuality_score' => 'float',
        'behaviour_score' => 'float',
        'overall_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getGradeBadgeAttribute(): string
    {
        return match ($this->overall_grade) {
            'A+' => 'badge-grade-excellent',
            'A' => 'badge-grade-excellent',
            'B' => 'badge-grade-good',
            'C' => 'badge-grade-satisfactory',
            'D' => 'badge-grade-danger',
            default => 'badge-grade-good',
        };
    }

    /**
     * Compute weighted overall score: 40% Project + 30% Attendance + 30% Behaviour
     */
    public function computeOverallScore(): float
    {
        $projPart = ($this->project_score ?? 0) * 0.40;
        $attPart = ($this->attendance_score ?? 0) * 0.30;
        $behPart = ($this->behaviour_score ?? 0) * 0.30;

        return round($projPart + $attPart + $behPart, 2);
    }

    /**
     * Compute grade based on overall score
     */
    public static function computeGrade(float $overallScore): string
    {
        if ($overallScore >= 90) return 'A+';
        if ($overallScore >= 80) return 'A';
        if ($overallScore >= 70) return 'B';
        if ($overallScore >= 60) return 'C';
        return 'D';
    }
}
