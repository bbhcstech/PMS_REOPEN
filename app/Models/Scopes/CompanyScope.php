<?php

namespace App\Models\Scopes;

use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Bypass scope for Super Admin unless actively impersonating a company
        $isSuperAdmin = Auth::guard('super_admin')->check() ||
            (Auth::check() && in_array(strtolower((string)(Auth::user()->role ?? '')), ['superadmin', 'super-admin', 'super_admin'], true));

        $companyId = app(CompanyContext::class)->id();

        if ($isSuperAdmin && !session('current_company_id')) {
            return;
        }

        // 2. Only apply when a tenant company is resolved
        if ($companyId) {
            $builder->where(function ($q) use ($model, $companyId) {
                $q->where($model->qualifyColumn('company_id'), $companyId)
                  ->orWhereNull($model->qualifyColumn('company_id'));
            });
        }
    }
}
