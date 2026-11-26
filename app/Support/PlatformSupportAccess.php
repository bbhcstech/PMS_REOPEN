<?php

namespace App\Support;

class PlatformSupportAccess
{
    public static function companyAdmin($user): bool
    {
        return $user && $user->company_id && \App\Services\WorkforceAccess::isAdmin($user);
    }

    public static function authorizeCompanyAdmin($user, ?int $companyId = null): void
    {
        abort_unless(self::companyAdmin($user) && (!$companyId || (int) $user->company_id === $companyId), 403,
            'Platform Support is for company administrators and Super Admin only. Please use Internal Tickets.');
    }
}
