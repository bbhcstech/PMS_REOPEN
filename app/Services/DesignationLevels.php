<?php

namespace App\Services;

use App\Models\Company;

class DesignationLevels
{
    public static function legend(): \Illuminate\Support\Collection
    {
        return \App\Models\Designation::whereNull('archived_at')->whereNotNull('level')->where('level', '>=', 0)
            ->orderBy('level')->orderBy('name')->get()->groupBy('level')
            ->map(fn ($designations) => $designations->pluck('name')->unique()->implode(', '));
    }

    public static function companyId(): int
    {
        $id = (int) auth()->user()?->company_id;
        abort_unless($id > 0, 403, 'A company must be assigned to manage designations.');
        return $id;
    }

    public static function maximum(): int
    {
        return self::count() - 1;
    }

    public static function count(): int
    {
        $company = Company::findOrFail(self::companyId());
        return max(7, (int) (($company->settings ?? [])['designation_max_level'] ?? 7));
    }

    public static function expandAfterAdminCreation(int $level): void
    {
        if (! in_array(strtolower((string) auth()->user()?->role), ['admin', 'administrator'], true)) {
            return;
        }

        \Illuminate\Support\Facades\DB::connection('central')->transaction(function () use ($level) {
            $company = Company::whereKey(self::companyId())->lockForUpdate()->firstOrFail();
            $settings = $company->settings ?? [];
            $maximum = max(7, (int) ($settings['designation_max_level'] ?? 7)) - 1;
            if ($level >= $maximum && $level < 2147483646) {
                $settings['designation_max_level'] = $level + 2;
                $company->settings = $settings;
                $company->save();
            }
        });
    }
}
