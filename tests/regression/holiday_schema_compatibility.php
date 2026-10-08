<?php

// Run with: php tests/regression/holiday_schema_compatibility.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Holiday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Holiday::clearTableColumnsCache();
Schema::connection('tenant')->create('holidays', function ($table) {
    $table->id(); $table->date('date'); $table->string('title'); $table->timestamps();
});
$holiday = Holiday::create([
    'date' => '2026-10-08', 'title' => 'Chill', 'occassion' => 'Chill',
    'group_id' => 'test-group', 'type' => 'holiday', 'recurring_day' => null,
    'department_id_json' => '["1","2"]', 'designation_id_json' => '["1"]', 'employment_type_json' => '["full_time"]',
]);
$holiday->refresh();
if ($holiday->title !== 'Chill' || $holiday->occassion !== 'Chill') throw new RuntimeException('Legacy schema creation failed.');
$holiday->update(['occassion' => 'Updated holiday']);
if ($holiday->fresh()->title !== 'Updated holiday') throw new RuntimeException('Legacy schema editing failed.');
Schema::connection('tenant')->table('holidays', function ($table) { $table->string('occassion')->nullable(); });
Holiday::clearTableColumnsCache();
$modern = Holiday::create(['date' => '2026-10-09', 'title' => 'Modern holiday', 'occassion' => 'Modern holiday']);
if ($modern->fresh()->getRawOriginal('occassion') !== 'Modern holiday') throw new RuntimeException('Modern schema creation failed.');
$modern->update(['title' => 'Modern updated', 'occassion' => 'Modern updated']);
if ($modern->fresh()->occassion !== 'Modern updated') throw new RuntimeException('Modern schema editing failed.');
echo "Holiday schema compatibility checks passed.\n";
