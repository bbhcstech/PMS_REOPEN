<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

/**
 * Company databases that have not run the latest tenant migrations still get the recruitment
 * branding tables/columns before Recruitment reads or saves them (instead of a 500 error).
 * The migrations themselves are idempotent; this only runs them when something is missing.
 */
class RecruitmentBrandingSchema
{
    private static array $checked = [];

    public static function ensure(): void
    {
        $database = (string) config('database.connections.tenant.database');
        if (isset(self::$checked[$database])) return;

        $schema = Schema::connection('tenant');
        if (! $schema->hasTable('recruitment_requirements')) return;

        $missing = ! $schema->hasTable('recruitment_branding_assets');
        foreach (['pdf_header_image', 'pdf_footer_image', 'pdf_header_asset_id', 'pdf_footer_asset_id'] as $column) {
            $missing = $missing || ! $schema->hasColumn('recruitment_requirements', $column);
        }

        if ($missing) {
            (require database_path('migrations/tenant/2026_10_10_120000_add_pdf_header_footer_to_recruitment_requirements.php'))->up();
            (require database_path('migrations/tenant/2026_11_26_000002_create_recruitment_branding_assets.php'))->up();
        }

        self::$checked[$database] = true;
    }
}
