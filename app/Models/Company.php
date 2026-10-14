<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    protected $connection = 'central';
    use SoftDeletes, \App\Models\Concerns\HasCompanyAiAgent, \App\Models\Concerns\ResolvesCompanyFeatures;

    protected $fillable = [
        'company_code',
        'name',
        'short_name',
        'email',
        'password',
        'phone',
        'website',
        'logo',
        'favicon',
        'domain',
        'subdomain',
        'db_name',
        'address',
        'gst_number',
        'pan_number',
        'registration_number',
        'employee_id_prefix',
        'leave_prefix',
        'payroll_prefix',
        'payslip_prefix',
        'greeting_message',
        'theme',
        'status',
        'trial_ends_at',
        'max_users',
        'max_projects',
        'max_clients',
        'max_storage_mb',
        'settings',
        'letterhead_file',
        'letterhead_original_name',
        'letterhead_file_type',
        'letterhead_uploaded_at',
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
        'settings' => 'array',
        'theme' => 'array',
        'trial_ends_at' => 'datetime',
        'letterhead_uploaded_at' => 'datetime',
    ];

    public function getCompanyNameAttribute(): string
    {
        return (string) $this->name;
    }

    public function setCompanyNameAttribute(?string $value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'ERP';
    }

    public function getBrandNameAttribute(): string
    {
        return trim($this->display_name . ' ERP');
    }

    public function logoUrl(): ?string
    {
        if ($this->logo && file_exists(public_path($this->logo))) {
            return asset($this->logo);
        }
        if (file_exists(public_path('logo.png'))) {
            return asset('logo.png');
        }
        return null;
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest();
    }

    /**
     * Most recent non-cancelled subscription (active, trial, pending or expired), used for display
     * when the company has no currently running paid subscription.
     */
    public function latestSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->ofMany(['id' => 'max'], fn ($query) => $query->where('status', '!=', 'cancelled'));
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'company_modules')
            ->withPivot('is_enabled', 'settings')
            ->withTimestamps();
    }

    public function enabledModules()
    {
        return $this->modules()->wherePivot('is_enabled', true);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
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

    public function getCentralCompanyId(): ?int
    {
        if ($this->getConnectionName() === 'central' || $this instanceof \App\Models\Central\Company) {
            return (int) $this->id;
        }

        if (session('current_company_id')) {
            return (int) session('current_company_id');
        }

        if (auth()->check() && auth()->user()->company_id) {
            return (int) auth()->user()->company_id;
        }

        try {
            $currentDb = config('database.connections.tenant.database') ?: env('DB_DATABASE');
            $centralComp = \Illuminate\Support\Facades\DB::connection('central')
                ->table('companies')
                ->where(function ($q) use ($currentDb) {
                    if (!empty($currentDb)) {
                        $q->orWhere('db_name', $currentDb);
                    }
                    if (!empty($this->email)) {
                        $q->orWhere('email', $this->email);
                    }
                    if (!empty($this->company_code)) {
                        $q->orWhere('company_code', $this->company_code);
                    }
                    if (!empty($this->name)) {
                        $q->orWhere('name', $this->name);
                    }
                })
                ->select('id')
                ->first();

            if ($centralComp) {
                return (int) $centralComp->id;
            }
        } catch (\Throwable $e) {}

        return (int) $this->id;
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
