<?php

// Run with: php tests/regression/holiday_import_group_id_test.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Imports\HolidaysImport;
use App\Models\Holiday;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Setup in-memory sqlite database for testing
foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);

// Create initial legacy holidays table WITHOUT group_id and without occassion
Schema::create('holidays', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->date('date');
    $table->string('type')->default('holiday');
    $table->string('recurring_day')->nullable();
    $table->timestamp('archived_at')->nullable();
    $table->timestamps();
});

echo "Running Holiday Import & group_id Resilience Tests...\n";

// Clear cache before test
Holiday::clearTableColumnsCache();

// =========================================================================
// TEST 1: Import on Legacy Table (NO group_id column in database)
// =========================================================================
assert(! Schema::hasColumn('holidays', 'group_id'), "Table must not have group_id yet");
assert(! Schema::hasColumn('holidays', 'occassion'), "Table must not have occassion yet");

$importRows = new Collection([
    [
        'date' => '2026-01-01',
        'occassion' => 'New Year Day',
        'type' => 'holiday',
        'override_existing' => 'yes',
        'department_ids' => '1,2',
        'designation_ids' => '3',
        'employment_types' => 'full_time',
    ],
    [
        'date' => '2026-01-26',
        'occassion' => 'Republic Day',
        'type' => 'holiday',
        'override_existing' => 'yes',
    ],
]);

$import = new HolidaysImport();
$import->collection($importRows);

assert($import->created === 2, "Expected 2 holidays created on legacy table, got {$import->created}");
assert(empty($import->errors), "Expected no errors on legacy table, got: " . json_encode($import->errors));

$newYear = Holiday::whereDate('date', '2026-01-01')->first();
assert($newYear !== null, "New Year holiday should exist in DB");
assert($newYear->title === 'New Year Day', "Title should be 'New Year Day'");
assert($newYear->occassion === 'New Year Day', "Occasion accessor should return 'New Year Day'");
assert(!empty($newYear->group_id), "group_id accessor should return non-empty value");

// Verify relationship doesn't crash on legacy schema
assert($newYear->group !== null, "group relation should resolve without crashing");
assert($newYear->holidays->count() >= 1, "holidays relation should resolve without crashing");
echo "[PASS] Test 1: Holiday import succeeded on legacy schema without group_id column\n";

// =========================================================================
// TEST 2: Run Migration to add group_id and extended columns
// =========================================================================
$migration = require dirname(__DIR__, 2) . '/database/migrations/tenant/2026_10_08_000003_add_group_id_and_details_to_holidays_table.php';
$migration->up();

Holiday::clearTableColumnsCache();

assert(Schema::hasColumn('holidays', 'group_id'), "Table must now have group_id column");
assert(Schema::hasColumn('holidays', 'occassion'), "Table must now have occassion column");
assert(Schema::hasColumn('holidays', 'department_id_json'), "Table must now have department_id_json column");
assert(Schema::hasColumn('holidays', 'designation_id_json'), "Table must now have designation_id_json column");
assert(Schema::hasColumn('holidays', 'employment_type_json'), "Table must now have employment_type_json column");
assert(Schema::hasColumn('holidays', 'notification_sent'), "Table must now have notification_sent column");
echo "[PASS] Test 2: Migration added all missing columns to holidays table successfully\n";

// =========================================================================
// TEST 3: Import with Migrated Schema (Stores group_id physically)
// =========================================================================
$importRowsPostMigration = new Collection([
    [
        'date' => '2026-08-15',
        'occassion' => 'Independence Day',
        'type' => 'holiday',
        'override_existing' => 'yes',
        'department_ids' => '1,4',
        'designation_ids' => '2,5',
        'employment_types' => 'full_time,part_time',
    ],
]);

$import2 = new HolidaysImport();
$import2->collection($importRowsPostMigration);

assert($import2->created === 1, "Expected 1 holiday created on migrated table, got {$import2->created}");

$indDay = Holiday::whereDate('date', '2026-08-15')->first();
assert($indDay !== null, "Independence Day holiday must exist");
assert(!empty($indDay->getRawOriginal('group_id')), "group_id must be stored physically in DB");
assert($indDay->getRawOriginal('occassion') === 'Independence Day', "occassion must be stored physically in DB");
assert($indDay->department_id_json === json_encode(['1', '4']), "department_id_json must be stored physically");
echo "[PASS] Test 3: Holiday import succeeded on migrated schema with physical group_id and metadata\n";

echo "\nAll Holiday Import & group_id tests passed successfully!\n";
