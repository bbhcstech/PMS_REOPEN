<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use SoftDeletes, \App\Models\Concerns\HasCompanyAiAgent, \App\Models\Concerns\ResolvesCompanyFeatures;

    protected $connection = 'central';
    protected $table = 'companies';

    protected $fillable = [
        'company_code', 'name', 'short_name', 'email', 'password', 'phone', 'website',
        'logo', 'favicon', 'domain', 'subdomain', 'db_name',
        'address', 'gst_number', 'pan_number', 'registration_number',
        'employee_id_prefix', 'leave_prefix', 'payroll_prefix', 'payslip_prefix',
        'greeting_message', 'theme', 'settings', 'status', 'trial_ends_at',
        'max_users', 'max_projects', 'max_clients', 'max_storage_mb',
        'letterhead_file', 'letterhead_original_name',
        'letterhead_file_type', 'letterhead_uploaded_at',
        'manually_suspended',
    ];

    protected static function booted(): void
    {
        static::creating(function ($company) {
            if (empty($company->db_name)) {
                $base = $company->company_code ?: ($company->subdomain ?: $company->name);
                $slug = \Illuminate\Support\Str::slug((string) $base, '_');
                if (empty($slug)) {
                    $slug = 'tenant_' . strtolower(\Illuminate\Support\Str::random(6));
                }
                $prefix = env('TENANT_DB_PREFIX', 'pms_');
                $candidate = str_starts_with($slug, $prefix) ? $slug : $prefix . $slug;

                $count = 1;
                $original = $candidate;
                while (static::where('db_name', $candidate)->exists()) {
                    $candidate = $original . '_' . $count++;
                }
                $company->db_name = $candidate;
            }
        });
    }

    protected $casts = [
        'theme'                  => 'array',
        'settings'               => 'array',
        'trial_ends_at'          => 'datetime',
        'letterhead_uploaded_at' => 'datetime',
        'manually_suspended'     => 'boolean',
    ];

    /**
     * True when the company has been deleted by the Super Admin (soft-deleted in the central DB).
     * Fails open (false) when the central DB cannot be reached so an outage never locks everyone out.
     */
    public static function isDeletedById($companyId): bool
    {
        if (empty($companyId)) {
            return false;
        }

        try {
            $company = static::on('central')->withTrashed()->find($companyId);

            return (bool) $company?->trashed();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->short_name ?: 'Workspace');
    }

    public function getBrandNameAttribute(): string
    {
        return $this->name ?: 'Workspace';
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset($this->logo) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon ? asset($this->favicon) : null;
    }

    public function hasLetterhead(): bool
    {
        return !empty($this->letterhead_file);
    }

    public function letterheadUrl(): ?string
    {
        return $this->letterhead_file ? asset($this->letterhead_file) : null;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function companyModules()
    {
        return $this->belongsToMany(\App\Models\Module::class, 'company_modules', 'company_id', 'module_id')
            ->withPivot('is_enabled', 'settings')
            ->withTimestamps();
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->whereDate('ends_at', '>=', now()->toDateString())
            ->latest();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(SuperAdminActivityLog::class);
    }

    public function isActive(): bool
    {
        return strtolower((string)$this->status) === 'active' && !$this->isSuspended() && !$this->isExpired();
    }

    public function isOnTrial(): bool
    {
        try {
            if (app()->bound(\App\Services\SubscriptionService::class)) {
                return app(\App\Services\SubscriptionService::class)->isTrialActive($this);
            }
        } catch (\Throwable $e) {}

        if (strtolower((string) ($this->status ?? '')) === 'trial') {
            if (! $this->trial_ends_at) {
                return true;
            }
            $endsAt = is_string($this->trial_ends_at) ? \Carbon\Carbon::parse($this->trial_ends_at) : $this->trial_ends_at;
            return $endsAt->isFuture();
        }

        return false;
    }

    public function isSuspended(): bool
    {
        if (strtolower((string)$this->status) === 'suspended' || !empty($this->manually_suspended)) {
            return true;
        }

        try {
            if (app()->bound(\App\Services\SubscriptionService::class)) {
                return app(\App\Services\SubscriptionService::class)->isSuspended($this);
            }
        } catch (\Throwable $e) {}

        return false;
    }

    public function isExpired(): bool
    {
        if (strtolower((string)$this->status) === 'expired') {
            return true;
        }

        try {
            if (app()->bound(\App\Services\SubscriptionService::class)) {
                return app(\App\Services\SubscriptionService::class)->isExpired($this);
            }
        } catch (\Throwable $e) {}

        return false;
    }

    public function remainingDays(): int
    {
        try {
            return app(\App\Services\SubscriptionService::class)->getRemainingDays($this);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
