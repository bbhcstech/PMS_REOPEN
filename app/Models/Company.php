<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
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
    ];

    protected $casts = [
        'settings' => 'array',
        'theme' => 'array',
        'trial_ends_at' => 'datetime',
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
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }
}
