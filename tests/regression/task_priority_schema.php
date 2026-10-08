<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\TaskPrioritySchema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
$schema = Schema::connection('tenant');
$db = DB::connection('tenant');
TaskPrioritySchema::ensure();
$schema->create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->unsignedBigInteger('project_id');
    $table->string('status');
    $table->date('due_date');
});
$db->table('tasks')->insert([
    'title' => 'pirates of the carebbian', 'project_id' => 3,
    'status' => 'To Do', 'due_date' => '2026-10-14',
]);
TaskPrioritySchema::ensure();
TaskPrioritySchema::ensure();
$query = $db->table('tasks')->where('project_id', 3)
    ->where('title', 'like', '%pirates of the carebbian%')->where('status', 'To Do')
    ->whereBetween('due_date', ['2026-10-07', '2026-10-21']);
if ((clone $query)->where('priority', 'medium')->count() !== 1
    || (clone $query)->where('priority', 'high')->count() !== 0) {
    throw new RuntimeException('Priority filter failed after repairing legacy schema.');
}
$db->table('tasks')->update(['priority' => 'high']);
foreach (['', 'tenant/'] as $directory) {
    $migration = require dirname(__DIR__, 2) . '/database/migrations/' . $directory . '2026_10_09_000001_ensure_tasks_priority_column.php';
    $migration->up();
    $migration->up();
    $migration->down();
}
if ($db->table('tasks')->value('priority') !== 'high'
    || $db->table('tasks')->value('title') !== 'pirates of the carebbian') {
    throw new RuntimeException('Schema repair changed existing task data.');
}
echo "Task priority schema and filter checks passed.\n";
