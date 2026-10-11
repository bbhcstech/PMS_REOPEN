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
    private const VERSION = '2026-10-12';

    /** Migration file => table whose absence means the migration has not run (null = safe to re-run). */
    private const MIGRATIONS = [
        '2026_06_20_000006_create_enterprise_payroll_tables.php' => null,
        '2026_08_17_000001_add_salary_structure_id_to_employee_details_table.php' => null,
        '2026_08_18_000001_create_payroll_policies_tables.php' => null,
        '2026_09_14_000001_create_payroll_formulas_table.php' => 'payroll_formulas',
        '2026_10_12_000001_add_payroll_company_scope.php' => null,
        '2026_10_10_000001_enhance_payroll_and_salary_structure_tables.php' => null,
        '2026_10_11_000001_add_effective_dates_to_payroll_formulas.php' => null,
        '2026_10_11_000002_add_company_id_to_payslips_table.php' => null,
    ];

    private static array $checked = [];

    private static function ready(): bool
    {
        $schema = Schema::connection('tenant');
        $tables = array_column($schema->getTables(), 'name');
        foreach ([
            'payroll_architectures', 'payroll_architecture_versions', 'salary_structures',
            'salary_structure_versions', 'salary_components', 'deduction_components', 'bonus_rules',
            'tax_rules', 'overtime_rules', 'payroll_cycles', 'payrolls', 'payroll_histories',
            'payroll_approvals', 'payslip_templates', 'payslips', 'payroll_reports', 'payroll_archives',
            'payroll_import_logs', 'payroll_export_logs', 'payroll_audit_logs', 'payroll_policies', 'payroll_policy_histories',
        ] as $table) {
            if (! in_array(DB::connection('tenant')->getTablePrefix().$table, $tables, true)) return false;
        }
        foreach ([
            'salary_structures' => ['company_id', 'designation_id', 'effective_from'],
            'employee_salary_assignments' => ['effective_from', 'deleted_at'],
            'payroll_formulas' => ['effective_from', 'effective_to', 'deleted_at'],
            'payrolls' => ['company_id', 'total_in_hand'],
            'payroll_histories' => ['company_id', 'total_in_hand'],
            'payslips' => ['company_id', 'deleted_at'],
        ] as $table => $columns) {
            if (! $schema->hasColumns($table, $columns)) return false;
        }
        return true;
    }

    public static function ensure(): void
    {
        $connection = DB::connection('tenant');
        $database = $connection->getDatabaseName();
        $identity = hash('sha256', json_encode([
            $connection->getDriverName(), $connection->getConfig('host'),
            $connection->getConfig('port'), $database,
        ]));
        $cacheKey = 'payroll-schema:' . self::VERSION . ':' . $identity;
        $requestKey = $identity . ':' . spl_object_id($connection->getPdo());
        if ((isset(self::$checked[$requestKey]) || Cache::get($cacheKey)) && self::ready()) {
            return;
        }
        Cache::lock($cacheKey . ':repair', 120)->block(15, function () use ($database, $cacheKey, $requestKey) {
            // Another request may have repaired this company's schema while we waited.
            if (Cache::get($cacheKey) && self::ready()) {
                self::$checked[$requestKey] = true;
                return;
            }
            unset(self::$checked[$requestKey]);
            Cache::forget($cacheKey);
            $originalConnection = DB::getDefaultConnection();
            DB::setDefaultConnection('tenant');
            try {
                foreach (self::MIGRATIONS as $file => $table) {
                    if ($table !== null && Schema::hasTable($table)) continue;
                    try {
                        (require database_path('migrations/tenant/' . $file))->up();
                    } catch (\Throwable $e) {
                        Log::error('Payroll schema repair failed: ' . $file, ['database' => $database, 'error' => $e->getMessage()]);
                        throw $e;
                    }
                }
                if (! self::ready()) throw new \RuntimeException('Company payroll schema is incomplete after repair.');
                Cache::forever($cacheKey, true);
                self::$checked[$requestKey] = true;
            } finally {
                DB::setDefaultConnection($originalConnection);
            }
        });
    }
}
