<?php

namespace App\Services;

use App\Models\Company;

class DesignationLevels
{
    public static function companyId(): int
    {
        $id = (int) auth()->user()?->company_id;
        abort_unless($id > 0, 403, 'A company must be assigned to manage designations.');
        return $id;
    }

    public static function maximum(): int
    {
        $company = Company::findOrFail(self::companyId());
        return max(6, (int) (($company->settings ?? [])['designation_max_level'] ?? 6));
    }
}
