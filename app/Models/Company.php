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

    public function isSuspended(): bool
    {
        return strtolower($this->status ?? '') === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function hasFeature(string $featureSlug): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        // Always allow dashboard and basic user routes
        if (in_array($featureSlug, ['dashboard', 'home', 'profile'], true)) {
            return true;
        }

        // Check if parent feature is turned off by Super Admin
        if ($this->isParentFeatureRevoked($featureSlug)) {
            return false;
        }

        // Check if Super Admin has set an explicit feature override for this company
        try {
            // Map exact feature slug to central module slugs without cross-module contamination
            $slugs = match ($featureSlug) {
                'crm', 'deals', 'crm-deals' => ['crm-deals', 'crm', 'deals'],
                'leads', 'leads-contacts' => ['leads-contacts', 'leads'],
                'leaves', 'leave-management' => ['leave-management', 'leaves'],
                'employees', 'user-management' => ['employees', 'user-management'],
                'designations' => ['designations'],
                'departments' => ['departments'],
                'attendance' => ['attendance'],
                'holidays' => ['holidays'],
                'awards', 'recognition' => ['recognition', 'awards'],
                'hr' => ['hr'],
                'projects' => ['projects'],
                'tasks' => ['tasks'],
                'timelogs', 'timesheets' => ['timesheets', 'timelogs'],
                'work' => ['work'],
                'payroll', 'payslips', 'salary-structures', 'payroll-architectures' => ['payroll', 'payslips', 'salary-structures', 'payroll-architectures'],
                'expenses' => ['expenses'],
                'billing' => ['billing'],
                'clients', 'client' => ['clients', 'client', 'leads-contacts'],
                'collaborating-companies', 'collaborating_companies' => ['collaborating-companies', 'collaborating_companies'],
                'reports', 'standard-reports', 'analytics', 'advanced-reports' => ['reports', 'standard-reports', 'analytics', 'advanced-reports'],
                'organization', 'teams' => ['organization', 'teams'],
                'tickets' => ['tickets'],
                'contracts' => ['contracts'],
                'notifications' => ['notifications'],
                default => [$featureSlug],
            };

            $override = \Illuminate\Support\Facades\DB::connection('central')
                ->table('company_modules')
                ->join('modules', 'modules.id', '=', 'company_modules.module_id')
                ->where('company_modules.company_id', $this->id)
                ->whereIn('modules.slug', $slugs)
                ->select('company_modules.is_enabled')
                ->orderBy('company_modules.updated_at', 'desc')
                ->first();

            if ($override !== null) {
                return (bool) $override->is_enabled;
            }
        } catch (\Throwable $e) {}

        if ($this->isOnTrial()) {
            return true;
        }

        $sub = $this->activeSubscription;
        if ($sub && method_exists($sub, 'hasFeature')) {
            return (bool) $sub->hasFeature($featureSlug);
        }

        if ($this->isActive()) {
            return true;
        }

        return false;
    }

    private function isParentFeatureRevoked(string $featureSlug): bool
    {
        $parentKey = match ($featureSlug) {
            'designations', 'departments', 'attendance', 'leaves', 'leave-management', 'holidays', 'awards', 'recognition' => 'hr',
            'projects', 'tasks', 'timelogs', 'timesheets' => 'work',
            'deals', 'crm', 'crm-deals', 'leads-contacts' => 'leads',
            'expenses', 'billing', 'payslips', 'salary-structures', 'payroll-architectures' => 'payroll',
            default => null,
        };

        if (! $parentKey) {
            return false;
        }

        try {
            $parentSlugs = match ($parentKey) {
                'hr' => ['hr', 'hr-management'],
                'work' => ['work'],
                'leads' => ['leads', 'leads-contacts', 'crm'],
                'payroll' => ['payroll'],
                default => [$parentKey],
            };

            $parentOverride = \Illuminate\Support\Facades\DB::connection('central')
                ->table('company_modules')
                ->join('modules', 'modules.id', '=', 'company_modules.module_id')
                ->where('company_modules.company_id', $this->id)
                ->whereIn('modules.slug', $parentSlugs)
                ->select('company_modules.is_enabled')
                ->orderBy('company_modules.updated_at', 'desc')
                ->first();

            if ($parentOverride !== null && ! (bool) $parentOverride->is_enabled) {
                return true;
            }
        } catch (\Throwable $e) {}

        return false;
    }
}
