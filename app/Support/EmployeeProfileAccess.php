<?php

namespace App\Support;

use App\Models\User;
use App\Services\TenantScope;

class EmployeeProfileAccess
{
    public static function canShowEditLinks(?User $actor): bool
    {
        return self::canUseEditor($actor) && $actor->hasModulePermission('employees', 'edit');
    }

    public static function canEditTarget(?User $actor, User $employee): bool
    {
        if (!self::canUseEditor($actor)) {
            return false;
        }

        $companyId = (int) $actor->company_id;
        $sameCompany = $companyId > 0 && $companyId === (int) $employee->company_id;
        if ($employee->company_staff_role_id) {
            return $sameCompany && $actor->normalizedRole() === 'admin';
        }

        $platformAdmin = $actor->normalizedRole() === 'superadmin' && TenantScope::isPlatformAdmin();

        return ($sameCompany || $platformAdmin)
            && (self::canAssignRoles($actor) || $employee->role === 'employee');
    }

    public static function canAssignRoles(?User $actor): bool
    {
        return in_array($actor?->normalizedRole(), ['admin', 'superadmin'], true);
    }

    private static function canUseEditor(?User $actor): bool
    {
        return in_array($actor?->normalizedRole(), ['admin', 'superadmin', 'hr', 'manager'], true);
    }
}
