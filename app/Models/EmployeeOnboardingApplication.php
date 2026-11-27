<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;

class EmployeeOnboardingApplication extends TenantModel
{
    public const STATUS_PENDING_REVIEW = 'Pending Review';
    public const STATUS_CORRECTION_REQUESTED = 'Correction Requested';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';

    protected $table = 'employee_onboarding_applications';

    protected $fillable = [
        'company_id',
        'invitation_id',
        'name',
        'email',
        'mobile_country_code',
        'mobile',
        'gender',
        'dob',
        'country',
        'marital_status',
        'language',
        'address',
        'about',
        'skills',
        'directory_about',
        'linkedin_url',
        'portfolio_url',
        'facebook_url',
        'instagram_url',
        'x_url',
        'profile_picture_path',
        'profile_picture_name',
        'cv_file_path',
        'cv_file_name',
        'government_id_card_path',
        'government_id_card_name',
        'admin_configuration',
        'status',
        'correction_token_hash',
        'encrypted_correction_token',
        'correction_token_expires_at',
        'correction_reason',
        'rejection_reason',
        'email_verified_at',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approved_user_id',
        'approved_employee_id',
        'activation_email_sent_at',
        'activation_email_error',
    ];

    protected $hidden = [
        'correction_token_hash',
        'encrypted_correction_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'admin_configuration' => 'array',
        'correction_token_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'activation_email_sent_at' => 'datetime',
    ];

    public function invitation()
    {
        return $this->belongsTo(EmployeeInvitation::class, 'invitation_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function approvedUser()
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }

    public function audits()
    {
        return $this->hasMany(EmployeeOnboardingAuditLog::class, 'application_id')->latest('id');
    }

    public function canApprove(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_REVIEW,
            self::STATUS_CORRECTION_REQUESTED,
        ], true) && empty($this->approved_user_id);
    }

    public function canRequestCorrection(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_REVIEW,
            self::STATUS_CORRECTION_REQUESTED,
        ], true);
    }

    public function canReject(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_REVIEW,
            self::STATUS_CORRECTION_REQUESTED,
        ], true);
    }

    public function correctionIsValid(): bool
    {
        return $this->status === self::STATUS_CORRECTION_REQUESTED
            && ! empty($this->correction_token_hash)
            && ($this->correction_token_expires_at === null || $this->correction_token_expires_at->isFuture());
    }

    public function decryptCorrectionToken(): ?string
    {
        if (empty($this->encrypted_correction_token)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->encrypted_correction_token);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function correctionUrl(?string $plainToken = null): ?string
    {
        $token = $plainToken ?: $this->decryptCorrectionToken();
        if (! $token) {
            return null;
        }

        return route('employee-onboarding.correction.show', ['token' => $token]);
    }
}
