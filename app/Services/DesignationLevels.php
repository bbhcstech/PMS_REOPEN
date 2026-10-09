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

    public static function expandAfterAdminCreation(int $level): void
    {
        if (! in_array(strtolower((string) auth()->user()?->role), ['admin', 'administrator'], true)) {
            return;
        }

        \Illuminate\Support\Facades\DB::connection('central')->transaction(function () use ($level) {
            $company = Company::whereKey(self::companyId())->lockForUpdate()->firstOrFail();
            $settings = $company->settings ?? [];
            $maximum = max(6, (int) ($settings['designation_max_level'] ?? 6));
            if ($level >= $maximum && $level < 2147483647) {
                $settings['designation_max_level'] = $level + 1;
                $company->settings = $settings;
                $company->save();
            }
        });
    }
}
