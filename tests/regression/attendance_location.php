<?php

// Run with: php tests/regression/attendance_location.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::connection('tenant')->create('attendances', function (Blueprint $t) {
    $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('location_id')->nullable();
    $t->text('clock_in_address')->nullable(); $t->timestamps();
});
$attendance = Attendance::create(['user_id' => 1, 'location_id' => null, 'clock_in_address' => 'Client office, Delhi']);
$attendance->refresh();
if ($attendance->clock_in_address !== 'Client office, Delhi') throw new RuntimeException('Custom location was not saved.');
$attendance->update(['location_id' => 2, 'clock_in_address' => 'Updated office address']);
$attendance->refresh();
if ($attendance->clock_in_address !== 'Updated office address' || (int) $attendance->location_id !== 2) throw new RuntimeException('Location edit was not saved.');
$attendance->update(['location_id' => null, 'clock_in_address' => null]);
$attendance->refresh();
if ($attendance->location_id !== null || $attendance->clock_in_address !== null) throw new RuntimeException('Location could not be cleared.');
foreach (['create', 'edit_form', 'archive'] as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/attendance/' . $view . '.blade.php')));
    token_get_all($compiled, TOKEN_PARSE);
}
echo "Attendance location persistence and template checks passed.\n";
