<?php
namespace App\Services;

use Illuminate\Support\Facades\Schema;

class CompanySuspensionSchema
{
    public static function ensure(): void
    {
        $schema = Schema::connection('central');
        foreach ([
            'suspended_at' => '2026_08_26_000001_add_subscription_lifecycle_fields_to_companies_table.php',
            'manually_suspended' => '2026_10_01_000001_add_manually_suspended_to_companies_table.php',
        ] as $column => $file) {
            if (!$schema->hasColumn('companies', $column)) {
                (require database_path('migrations/central/' . $file))->up();
            }
        }
        foreach (['suspension_category', 'suspension_reason', 'suspended_by'] as $column) {
            if (!$schema->hasColumn('companies', $column)) {
                (require database_path('migrations/central/2026_10_09_190000_add_company_suspension_reasons.php'))->up();
                break;
            }
        }
    }
}
