<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Single source of truth for tenant isolation.
 *
 * Only the platform Super Admin may see or act on more than one company.
 * Every other role (company admin, HR, manager, employee, client) is locked to the
 * company resolved at login, and any attempt to reach another company is refused.
 */
class TenantScope
{
    public static function isPlatformAdmin(): bool
    {
        try {
            if (Auth::guard('super_admin')->check()) {
                return true;
            }
        } catch (\Throwable $e) {}

        $user = Auth::guard('web')->user();
        $role = strtolower((string) ($user?->role ?? ''));

        return !$user?->company_id && in_array($role, ['superadmin', 'super-admin', 'super_admin'], true);
    }

    /**
     * The company the signed-in user belongs to. The session value is written at login
     * from the central registry, so it is preferred over the user row.
     */
    public static function companyId(): ?int
    {
        $sessionCompanyId = (int) session('current_company_id');
        $userCompanyId = (int) (Auth::guard('web')->user()?->company_id ?? 0);
        if ($sessionCompanyId > 0 && !static::isPlatformAdmin()) {
            abort_unless($userCompanyId === $sessionCompanyId, 403, 'Account and company workspace do not match.');
        }
        if ($sessionCompanyId > 0) {
            return $sessionCompanyId;
        }

        return $userCompanyId > 0 ? $userCompanyId : null;
    }

    /**
     * Company filter to apply to a query for the current user.
     * Platform admins may pick any company (or none for "all"); everyone else always
     * gets their own company and is refused when they ask for a different one.
     */
    public static function companyFilter(?int $requestedCompanyId = null): ?int
    {
        if (static::isPlatformAdmin()) {
            return $requestedCompanyId ?: null;
        }

        $ownCompanyId = static::companyId();
        abort_unless($ownCompanyId, 403, 'No company is assigned to this account.');

        if ($requestedCompanyId && $requestedCompanyId !== $ownCompanyId) {
            abort(403, 'You can only access data that belongs to your own company.');
        }

        return $ownCompanyId;
    }

    /**
     * Companies the current user is allowed to see: all active companies for the
     * platform admin, only their own company for everyone else.
     */
    public static function visibleCompanies(): Collection
    {
        if (static::isPlatformAdmin()) {
            return Company::where('status', 'active')->orderBy('name')->get();
        }

        $ownCompanyId = static::companyId();

        return $ownCompanyId
            ? Company::whereKey($ownCompanyId)->get()
            : collect();
    }

    /**
     * Abort unless the given company is the current user's company (platform admin passes).
     */
    public static function authorizeCompany($companyId): void
    {
        if (static::isPlatformAdmin()) {
            return;
        }

        $ownCompanyId = static::companyId();
        abort_unless($ownCompanyId && (int) $companyId === $ownCompanyId, 403, 'You can only access data that belongs to your own company.');
    }
}
