<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => [
    'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
], 'database.default' => 'tenant']);
DB::purge('tenant');

$migration = require __DIR__.'/../../database/migrations/tenant/2026_10_10_000001_enhance_payroll_and_salary_structure_tables.php';
$migration->up();
DB::table('employee_salary_assignments')->insert([
    'company_id' => 7, 'user_id' => 42, 'actual_basic_salary' => 25000,
]);
$migration->up();
$assignment = DB::table('employee_salary_assignments')->first();
if (!Schema::hasColumn('employee_salary_assignments', 'effective_from')
    || !Schema::hasColumn('employee_salary_assignments', 'deleted_at')
    || DB::table('employee_salary_assignments')->count() !== 1
    || (float) $assignment->actual_basic_salary !== 25000.0
    || (int) $assignment->company_id !== 7) {
    throw new RuntimeException('Tenant payroll migration failed or changed existing salary data.');
}
echo "PASS: missing tenant salary table repaired; repeated migration preserves salary data.\n";
