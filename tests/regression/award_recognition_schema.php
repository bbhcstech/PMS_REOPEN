<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Award;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::connection('tenant')->create('awards', function ($table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('title');
    $table->date('award_date');
    $table->timestamps();
});
$legacy = Award::create(['user_id' => 13, 'title' => 'Existing award', 'award_date' => '2026-10-08']);
$migration = require dirname(__DIR__, 2) . '/database/migrations/tenant/2026_10_08_120000_add_recognition_fields_to_awards_table.php';
$migration->up();
$migration->up(); // Safe for partially upgraded tenant schemas.
$award = Award::create(['user_id' => 13, 'title' => 'Recognition', 'appreciation_id' => 1, 'award_date' => '2026-10-09', 'photo' => 'admin/uploads/awards/test.png']);
$award->refresh();
if ($award->appreciation_id != 1 || $award->photo !== 'admin/uploads/awards/test.png' || $award->status !== 'active') {
    throw new RuntimeException('Recognition fields were not persisted.');
}
$award->update(['appreciation_id' => 2, 'title' => 'Updated recognition']);
if ($award->fresh()->appreciation_id != 2 || $legacy->fresh()->title !== 'Existing award') {
    throw new RuntimeException('Recognition update or legacy preservation failed.');
}
echo "Award recognition schema checks passed.\n";
