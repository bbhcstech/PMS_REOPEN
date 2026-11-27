<?php

namespace App\Models;

class EmployeeOnboardingAuditLog extends TenantModel
{
    protected $table = 'employee_onboarding_audit_logs';

    protected $fillable = [
        'company_id',
        'invitation_id',
        'application_id',
        'event',
        'actor_id',
        'actor_type',
        'actor_label',
        'notes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function invitation()
    {
        return $this->belongsTo(EmployeeInvitation::class, 'invitation_id');
    }

    public function application()
    {
        return $this->belongsTo(EmployeeOnboardingApplication::class, 'application_id');
    }
}
