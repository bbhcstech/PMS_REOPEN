<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;

class EmployeeInvitation extends TenantModel
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_SENT = 'Sent';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_EXPIRED = 'Expired';
    public const STATUS_REVOKED = 'Revoked';

    public const METHOD_EMAIL = 'email';
    public const METHOD_LINK = 'link';

    protected $table = 'employee_invitations';

    protected $fillable = [
        'company_id',
        'email',
        'token_hash',
        'encrypted_token',
        'invitation_method',
        'personal_message',
        'status',
        'invited_by',
        'single_use',
        'resend_count',
        'last_sent_at',
        'email_verified_at',
        'email_verification_code_hash',
        'email_verification_expires_at',
        'email_verification_attempts',
        'expires_at',
        'submitted_at',
        'revoked_at',
        'revoked_by',
        'delivery_error',
    ];

    protected $hidden = [
        'token_hash',
        'encrypted_token',
        'email_verification_code_hash',
    ];

    protected $casts = [
        'single_use' => 'boolean',
        'last_sent_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function revoker()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function application()
    {
        return $this->hasOne(EmployeeOnboardingApplication::class, 'invitation_id');
    }

    public function audits()
    {
        return $this->hasMany(EmployeeOnboardingAuditLog::class, 'invitation_id')->latest('id');
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function effectiveStatus(): string
    {
        if (in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_REVOKED], true)) {
            return $this->status;
        }
        if ($this->isExpired()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status ?: self::STATUS_PENDING;
    }

    public function isUsable(): bool
    {
        $status = $this->effectiveStatus();
        if (! in_array($status, [self::STATUS_PENDING, self::STATUS_SENT], true)) {
            return false;
        }
        if ($this->single_use && $this->submitted_at !== null) {
            return false;
        }

        return $this->revoked_at === null && ! $this->isExpired();
    }

    public function decryptPlainToken(): ?string
    {
        if (empty($this->encrypted_token)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->encrypted_token);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function onboardingUrl(?string $plainToken = null): ?string
    {
        $token = $plainToken ?: $this->decryptPlainToken();
        if (! $token) {
            return null;
        }

        return route('employee-onboarding.show', ['token' => $token]);
    }
}
