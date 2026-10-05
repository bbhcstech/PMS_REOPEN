<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    /**
     * Boot the BelongsToCompany trait for Eloquent models.
     */
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function ($model) {
            $isSuperAdmin = Auth::guard('super_admin')->check() ||
                (Auth::check() && in_array(strtolower((string)(Auth::user()->role ?? '')), ['superadmin', 'super-admin', 'super_admin'], true));

            $companyId = app(CompanyContext::class)->id() ?? auth()->user()?->company_id;

            // Enforce company_id server-side for tenant users; do not trust frontend input
            if (!$isSuperAdmin && $companyId) {
                $model->company_id = $companyId;
            } elseif (empty($model->company_id) && $companyId) {
                $model->company_id = $companyId;
            }
        });
    }

    /**
     * Explicit scope helper for company filtering if needed.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where($this->qualifyColumn('company_id'), $companyId);
    }
}
