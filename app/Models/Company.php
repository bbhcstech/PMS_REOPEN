<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    protected $connection = 'central';
    use SoftDeletes;

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
        return $this->status === 'active';
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
        if (strtolower($this->status ?? '') === 'suspended') {
            return true;
        }

        try {
            if (app()->bound(\App\Services\SubscriptionService::class)) {
                return app(\App\Services\SubscriptionService::class)->isSuspended($this);
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

    public function hasFeature(string $featureSlug): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        if (in_array($featureSlug, ['home', 'profile', 'dashboard'], true)) {
            return true;
        }

        $centralCompanyId = $this->getCentralCompanyId();

        try {
            $slugs = match ($featureSlug) {
                'crm', 'deals', 'crm-deals' => ['crm-deals', 'crm', 'deals', 'leads-contacts'],
                'leads', 'leads-contacts' => ['leads-contacts', 'leads', 'crm'],
                'leaves', 'leave-management', 'leave-settings' => ['leave-management', 'leaves', 'leave-settings'],
                'employees', 'user-management' => ['employees', 'user-management'],
                'designations' => ['designations'],
                'departments', 'parent-departments' => ['departments', 'parent-departments'],
                'attendance', 'attendance-settings' => ['attendance', 'attendance-settings'],
                'holidays', 'holiday-settings' => ['holidays', 'holiday-settings'],
                'awards', 'recognition', 'letterhead' => ['recognition', 'awards', 'letterhead'],
                'recruitment', 'recruitment-settings' => ['recruitment', 'recruitment-settings'],
                'appraisal', 'performance-settings' => ['appraisal', 'performance-settings'],
                'hr', 'hr-management' => ['hr', 'hr-management', 'hr-employees'],
                'projects' => ['projects'],
                'tasks' => ['tasks'],
                'timelogs', 'timesheets' => ['timesheets', 'timelogs'],
                'work' => ['work'],
                'payroll', 'payslips', 'salary-structures', 'payroll-architectures', 'payroll-settings', 'payroll-policies', 'payroll-cycles', 'payroll-reports', 'bonus-rules', 'deduction-rules', 'overtime-rules', 'tax-rules', 'formula-builder' => ['payroll', 'payslips', 'salary-structures', 'payroll-architectures', 'payroll-settings', 'payroll-policies', 'payroll-cycles', 'payroll-reports', 'bonus-rules', 'deduction-rules', 'overtime-rules', 'tax-rules', 'formula-builder'],
                'expenses' => ['expenses'],
                'billing' => ['billing', 'expenses'],
                'clients', 'client' => ['clients', 'client', 'leads-contacts'],
                'products' => ['products'],
                'orders' => ['orders'],
                'events' => ['events'],
                'my-documents', 'documents' => ['my-documents', 'documents'],
                'community' => ['community'],
                'collaborating-companies', 'collaborating_companies' => ['collaborating-companies', 'collaborating_companies'],
                'reports', 'standard-reports', 'analytics', 'advanced-reports', 'company-complaints', 'activity-logs', 'system-logs' => ['reports', 'standard-reports', 'analytics', 'advanced-reports', 'company-complaints', 'activity-logs', 'system-logs'],
                'organization', 'teams' => ['organization', 'teams'],
                'tickets' => ['tickets'],
                'contracts' => ['contracts'],
                'notifications', 'notification-settings' => ['notifications', 'notification-settings'],
                'settings' => ['settings'],
                'settings-dashboard' => ['settings-dashboard', 'settings'],
                'company-profile-settings' => ['company-profile-settings', 'settings'],
                'organization-details-settings' => ['organization-details-settings', 'settings'],
                'business-address-settings' => ['business-address-settings', 'settings'],
                'work-schedule-settings' => ['work-schedule-settings', 'settings'],
                'security-settings' => ['security-settings', 'settings'],
                'change-password-settings' => ['change-password-settings', 'settings'],
                'role-permissions-settings' => ['role-permissions-settings', 'role-management', 'settings'],
                'localization-settings' => ['localization-settings', 'settings'],
                'terms-policy-settings' => ['terms-policy-settings', 'settings'],
                default => [$featureSlug],
            };

            // 1. Explicit Company Module Overrides set by Super Admin (takes top priority)
            if ($centralCompanyId) {
                // Check direct feature override first
                $directOverrides = \Illuminate\Support\Facades\DB::connection('central')
                    ->table('company_modules')
                    ->join('modules', 'modules.id', '=', 'company_modules.module_id')
                    ->where('company_modules.company_id', $centralCompanyId)
                    ->whereIn('modules.slug', $slugs)
                    ->select('company_modules.is_enabled')
                    ->orderBy('company_modules.is_enabled', 'asc') // disabled (0) comes first
                    ->get();

                if ($directOverrides->isNotEmpty()) {
                    if ($directOverrides->contains('is_enabled', 0)) {
                        return false;
                    }
                    if ($directOverrides->contains('is_enabled', 1)) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 2. Check if parent category feature is disabled by Super Admin
        if ($this->isParentFeatureRevoked($featureSlug)) {
            return false;
        }

        // 3. Check Active Subscription Plan Module Entitlements
        try {
            $centralComp = $this->getConnectionName() === 'central'
                ? $this
                : (\App\Models\Central\Company::on('central')->find($centralCompanyId) ?? $this);

            $sub = $centralComp?->activeSubscription ?? $this->activeSubscription;
            if ($sub && $sub->plan_id) {
                $plan = $sub->plan ?? \App\Models\Central\Plan::on('central')->find($sub->plan_id);
                $planSlug = strtolower($plan?->slug ?? '');

                $planModules = \Illuminate\Support\Facades\DB::connection('central')
                    ->table('plan_modules')
                    ->join('modules', 'modules.id', '=', 'plan_modules.module_id')
                    ->where('plan_modules.plan_id', $sub->plan_id)
                    ->pluck('modules.slug')
                    ->toArray();

                if (! empty($planModules)) {
                    foreach ($slugs as $slug) {
                        if (in_array($slug, $planModules, true)) {
                            return true;
                        }
                    }
                    return false;
                }

                // Standard paid plans (gold, platinum, diamond) include all base platform features by default if plan_modules not explicitly populated
                if (in_array($planSlug, ['gold', 'platinum', 'diamond'], true)) {
                    return true;
                }

                // Free tier default allowed features
                if (in_array($planSlug, ['free', 'trial', 'starter'], true)) {
                    $freeAllowed = ['dashboard', 'notifications', 'organization', 'hr', 'employees', 'attendance', 'leave-management', 'leaves', 'holidays', 'events', 'my-documents', 'community', 'tickets', 'settings'];
                    foreach ($slugs as $slug) {
                        if (in_array($slug, $freeAllowed, true)) {
                            return true;
                        }
                    }
                    return false;
                }
            }
        } catch (\Throwable $e) {}

        // 4. Default for active or trial accounts without explicit restrictions
        if ($this->isOnTrial() || $this->isActive()) {
            return true;
        }

        return false;
    }

    private function isParentFeatureRevoked(string $featureSlug): bool
    {
        $parentKey = match ($featureSlug) {
            'designations', 'departments', 'parent-departments', 'attendance', 'leaves', 'leave-management', 'holidays', 'awards', 'recognition', 'recruitment', 'appraisal', 'employees', 'user-management' => 'hr',
            'projects', 'tasks', 'timelogs', 'timesheets' => 'work',
            'deals', 'crm', 'crm-deals', 'leads-contacts' => 'leads',
            'expenses', 'billing', 'payslips', 'salary-structures', 'payroll-architectures' => 'payroll',
            'teams' => 'organization',
            'settings-dashboard', 'company-profile-settings', 'organization-details-settings', 'business-address-settings', 'work-schedule-settings', 'leave-settings', 'holiday-settings', 'attendance-settings', 'payroll-settings', 'recruitment-settings', 'performance-settings', 'notification-settings', 'email-settings', 'document-settings', 'security-settings', 'change-password-settings', 'role-permissions-settings', 'localization-settings', 'terms-policy-settings' => 'settings',
            default => null,
        };

        if (! $parentKey) {
            return false;
        }

        try {
            $centralCompanyId = $this->getCentralCompanyId();
            if (! $centralCompanyId) {
                return false;
            }

            $parentSlugs = match ($parentKey) {
                'hr' => ['hr', 'hr-management', 'hr-employees'],
                'work' => ['work'],
                'leads' => ['leads', 'leads-contacts', 'crm'],
                'payroll' => ['payroll'],
                'organization' => ['organization'],
                'settings' => ['settings', 'module-management'],
                default => [$parentKey],
            };

            $parentOverride = \Illuminate\Support\Facades\DB::connection('central')
                ->table('company_modules')
                ->join('modules', 'modules.id', '=', 'company_modules.module_id')
                ->where('company_modules.company_id', $centralCompanyId)
                ->whereIn('modules.slug', $parentSlugs)
                ->select('company_modules.is_enabled')
                ->orderBy('company_modules.is_enabled', 'asc')
                ->first();

            if ($parentOverride !== null && (int) $parentOverride->is_enabled === 0) {
                return true;
            }
        } catch (\Throwable $e) {}

        return false;
    }
}
