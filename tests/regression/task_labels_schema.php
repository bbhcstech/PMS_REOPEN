<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\TaskLabelsSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
$schema = Schema::connection('tenant');
$db = DB::connection('tenant');
TaskLabelsSchema::ensure();
$schema->create('tasks', function ($table) {
    $table->id();
    $table->string('title');
});
$db->table('tasks')->insert(['title' => 'Existing task']);
TaskLabelsSchema::ensure();
TaskLabelsSchema::ensure();
if ($db->table('tasks')->value('title') !== 'Existing task'
    || $db->table('tasks')->value('task_labels') !== null) {
    throw new RuntimeException('Task labels repair changed legacy task data.');
}
$id = $db->table('tasks')->insertGetId(['title' => 'Labeled task', 'task_labels' => '2,5']);
$db->table('tasks')->insert(['title' => 'Task without labels', 'task_labels' => null]);
foreach (['', 'tenant/'] as $directory) {
    $migration = require dirname(__DIR__, 2) . '/database/migrations/' . $directory . '2026_10_09_000002_ensure_tasks_labels_column.php';
    $migration->up();
    $migration->down();
}
if ($db->table('tasks')->where('id', $id)->value('task_labels') !== '2,5') {
    throw new RuntimeException('Repair failed to preserve existing task labels.');
}
echo "Task labels schema and insert checks passed.\n";
