<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appraisal extends TenantModel
{
    use SoftDeletes;

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
        'policy_snapshot',
        'recommended_increment_percent',
    ];

    protected $casts = [
        'policy_snapshot' => 'array',
        'recommended_increment_percent' => 'float',
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
            'Pending' => 'badge bg-label-secondary',
            default => 'badge-grade-good',
        };
    }

    /**
     * Use the evaluation's saved policy; legacy evaluations retain their original 40/30/30 weights.
     */
    public function computeOverallScore(): float
    {
        return app(\App\Services\AppraisalPolicy::class)->score($this->project_score ?? 0, $this->attendance_score ?? 0,
            $this->behaviour_score ?? 0, $this->policy_snapshot ?: ['work_weight' => 40, 'attendance_weight' => 30, 'behaviour_weight' => 30]);
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
