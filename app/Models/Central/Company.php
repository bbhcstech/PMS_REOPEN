<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'companies';

    protected $fillable = [
        'company_code', 'name', 'short_name', 'email', 'phone', 'website',
        'logo', 'favicon', 'domain', 'subdomain', 'db_name',
        'address', 'gst_number', 'pan_number', 'registration_number',
        'employee_id_prefix', 'leave_prefix', 'payroll_prefix', 'payslip_prefix',
        'greeting_message', 'theme', 'settings', 'status', 'trial_ends_at',
        'max_users', 'max_projects', 'max_clients', 'max_storage_mb',
        'letterhead_file', 'letterhead_original_name',
        'letterhead_file_type', 'letterhead_uploaded_at',
    ];

    protected $casts = [
        'theme'                  => 'array',
        'settings'               => 'array',
        'trial_ends_at'          => 'datetime',
        'letterhead_uploaded_at' => 'datetime',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
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
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    /**
     * Check if company has access to a specific feature.
     * Trial companies get access to all features by default.
     * Active companies use active subscription plan's feature list.
     */
    public function hasFeature(string $featureSlug): bool
    {
        // Active trial grants full feature access
        if ($this->isOnTrial()) {
            return true;
        }

        // Check active subscription feature set
        $sub = $this->activeSubscription;
        if ($sub && $sub->hasFeature($featureSlug)) {
            return true;
        }

        // Default fallback: if no specific subscription record is set yet, allow active companies access
        if (! $sub && $this->isActive()) {
            return true;
        }

        return false;
    }
}