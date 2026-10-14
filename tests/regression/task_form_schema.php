<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Task;
use App\Services\TaskFormSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

foreach (['tenant', 'central'] as $name) {
    config(["database.connections.$name" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($name);
}
config(['database.default' => 'central']);
Schema::connection('tenant')->create('tasks', function ($t) {
    $t->id(); $t->string('title'); $t->unsignedBigInteger('project_id')->nullable();
    $t->string('repeat_type')->default('week'); $t->timestamps();
});
$db = DB::connection('tenant');
$oldId = $db->table('tasks')->insertGetId(['title' => 'Existing task', 'repeat_type' => 'month']);
$migration = require dirname(__DIR__, 2) . '/database/migrations/tenant/2026_10_09_130000_ensure_task_form_columns.php';
$migration->up(); TaskFormSchema::ensure(); $migration->up();
if (Schema::connection('central')->hasTable('tasks')) throw new RuntimeException('Repair used the central database.');
if ($db->table('tasks')->where('id', $oldId)->value('repeat_type') !== 'month') throw new RuntimeException('Existing task was changed.');
$data = ['title' => 'New task', 'milestone_id' => 11, 'board_column_id' => 2, 'dependent_task_id' => 3,
    'category_id' => 4, 'start_date' => '2026-10-09', 'due_date' => '2026-10-09', 'is_private' => 0,
    'billable' => 0, 'estimate_hours' => 0, 'estimate_minutes' => 0, 'repeat' => 0,
    'repeat_complete' => 0, 'repeat_count' => 1, 'repeat_type' => 'day', 'repeat_cycles' => 1, 'image_url' => null];
$task = Task::withoutEvents(fn () => Task::create($data));
$task->updateQuietly(['milestone_id' => 12, 'due_date' => '2026-10-10']);
if ($task->fresh()->milestone_id != 12) throw new RuntimeException('Milestone was not persisted.');
if ($db->table('tasks')->count() !== 2) throw new RuntimeException('Existing records were lost.');
echo "PASS: legacy tenant task schema repaired repeatedly; task insert/update persist form fields and preserve existing data.\n";
