<?php
namespace App\Services;

use Illuminate\Support\Facades\{Cache, DB, Log, Schema};

/**
 * Makes sure the current company database has every payroll table and column the Payroll pages use.
 * Company databases that were not migrated after a deploy (`php artisan tenant:migrate` skipped)
 * otherwise turn every Payroll page into a 500 error.
 */
class PayrollSchema
{
    private const VERSION = '2026-10-10';

    /** Migration file => table whose absence means the migration has not run (null = safe to re-run). */
    private const MIGRATIONS = [
        '2026_06_20_000006_create_enterprise_payroll_tables.php' => 'payrolls',
        '2026_08_17_000001_add_salary_structure_id_to_employee_details_table.php' => null,
        '2026_08_18_000001_create_payroll_policies_tables.php' => 'payroll_policies',
        '2026_09_14_000001_create_payroll_formulas_table.php' => 'payroll_formulas',
        '2026_10_10_000001_enhance_payroll_and_salary_structure_tables.php' => null,
    ];

    private static array $checked = [];

    public static function ensure(): void
    {
        try {
            $database = DB::connection()->getDatabaseName();
        } catch (\Throwable $e) {
            return;
        }
        $cacheKey = 'payroll-schema:' . self::VERSION . ':' . $database;
        if (isset(self::$checked[$database]) || Cache::get($cacheKey)) {
            return;
        }
        self::$checked[$database] = true;

        $complete = true;
        foreach (self::MIGRATIONS as $file => $table) {
            if ($table !== null && Schema::hasTable($table)) {
                continue;
            }
            try {
                (require database_path('migrations/tenant/' . $file))->up();
            } catch (\Throwable $e) {
                $complete = false;
                Log::error('Payroll schema repair failed: ' . $file, ['database' => $database, 'error' => $e->getMessage()]);
            }
        }
        if ($complete) {
            Cache::forever($cacheKey, true);
        }
    }
}
