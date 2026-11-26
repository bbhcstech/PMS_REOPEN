<?php

// Run with: php tests/regression/task_complete_status_update.php  (isolated SQLite; no live data)
// "Save Work Update" with status Completed: sub-task completion is read from sub_tasks.status
// (there is no is_completed column), so completing works, and pending sub-tasks give a clear message.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\{SubTaskController, TaskController};
use App\Models\{SubTask, Task, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->string('email')->nullable(); $t->string('password')->nullable(); $t->string('role')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); });
Schema::create('tasks', function ($t) {
    $t->id(); $t->string('title'); $t->unsignedBigInteger('project_id')->nullable(); $t->string('assigned_to')->nullable(); $t->string('status')->nullable();
    $t->integer('board_column_id')->nullable(); $t->boolean('is_completed')->default(false); $t->dateTime('completed_on')->nullable();
    $t->integer('progress')->default(0); $t->text('remarks')->nullable(); $t->timestamps();
});
// Same shape as the real table: completion is an "incomplete"/"complete" status, no is_completed column.
Schema::create('sub_tasks', function ($t) {
    $t->id(); $t->unsignedBigInteger('task_id'); $t->text('title'); $t->dateTime('due_date')->nullable(); $t->date('start_date')->nullable();
    $t->string('status')->default('incomplete'); $t->unsignedBigInteger('assigned_to')->nullable(); $t->unsignedBigInteger('added_by')->nullable();
    $t->unsignedBigInteger('last_updated_by')->nullable(); $t->text('description')->nullable(); $t->string('files')->nullable(); $t->timestamps();
});
Schema::create('assigned_task_user', function ($t) { $t->id(); $t->unsignedBigInteger('task_id'); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('assigned_by')->nullable(); $t->timestamp('assigned_at')->nullable(); $t->timestamps(); });
Schema::create('task_updates', function ($t) { $t->id(); $t->unsignedBigInteger('task_id'); $t->unsignedBigInteger('user_id')->nullable(); $t->string('status')->nullable(); $t->integer('progress')->nullable(); $t->text('remarks')->nullable(); $t->timestamps(); });
Schema::create('notifications', function ($t) { $t->uuid('id')->primary(); $t->string('type'); $t->string('notifiable_type'); $t->unsignedBigInteger('notifiable_id'); $t->text('data'); $t->timestamp('read_at')->nullable(); $t->timestamps(); });
DB::table('users')->insert([['id' => 1, 'company_id' => 1, 'name' => 'Saraswata', 'role' => 'employee'], ['id' => 2, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin']]);
DB::table('tasks')->insert([
    ['id' => 1, 'title' => 'Website Structure', 'assigned_to' => '1', 'status' => 'Doing', 'progress' => 30],
    ['id' => 2, 'title' => 'Has sub-tasks', 'assigned_to' => '1', 'status' => 'Doing', 'progress' => 30],
]);
DB::table('assigned_task_user')->insert([['task_id' => 1, 'user_id' => 1], ['task_id' => 2, 'user_id' => 1]]);
DB::table('sub_tasks')->insert([
    ['task_id' => 2, 'title' => 'Header', 'status' => 'complete'],
    ['task_id' => 2, 'title' => 'Footer', 'status' => 'Incomplete'],
]);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
Auth::guard('web')->setUser(User::findOrFail(1));
session(['current_company_id' => 1]);
$save = function (int $taskId, array $data) {
    $request = Request::create('/tasks/' . $taskId . '/update-status', 'POST', $data);
    $request->headers->set('Accept', 'application/json');
    app()->instance('request', $request);
    return (new TaskController)->updateStatus($request, Task::findOrFail($taskId));
};

// 1. The reported case: no sub-tasks, status Completed, progress 100
$response = $save(1, ['status' => 'Completed', 'progress' => 100, 'remarks' => 'Do it']);
$task = Task::findOrFail(1);
$check($response->getStatusCode() === 200 && $response->getData()->success === true, 'Save Work Update with Completed succeeds (no database error)');
$check($task->status === 'Completed' && (int) $task->progress === 100 && (bool) $task->is_completed && $task->completed_on && $task->remarks === 'Do it', 'task is stored as Completed at 100% with remarks and completion date');
$check(DB::table('task_updates')->where('task_id', 1)->where('status', 'Completed')->exists(), 'work update history is recorded');

// 2. Pending sub-task: clear refusal instead of a server error, task unchanged
$response = $save(2, ['status' => 'Completed', 'progress' => 100]);
$check($response->getStatusCode() === 422 && str_contains($response->getData()->message, '1 sub-task is still pending'), 'pending sub-task blocks completion with a clear message');
$check(Task::findOrFail(2)->status === 'Doing', 'task stays unchanged while a sub-task is pending');

// 3. Mark the sub-task complete (new status field on the sub-task edit form), then complete the task
$sub = SubTask::where('title', 'Footer')->firstOrFail();
$request = Request::create('/subtasks/' . $sub->id, 'PUT', ['title' => 'Footer', 'assigned_to' => 1, 'status' => 'complete']);
app()->instance('request', $request);
(new SubTaskController)->update($request, $sub);
$check(strtolower(SubTask::findOrFail($sub->id)->status) === 'complete', 'a sub-task can be marked complete from its edit form');
$response = $save(2, ['status' => 'Completed', 'progress' => 100]);
$check($response->getStatusCode() === 200 && Task::findOrFail(2)->status === 'Completed', 'once all sub-tasks are complete the task can be Completed');

// 4. Other statuses unaffected
$response = $save(1, ['status' => 'Doing', 'progress' => 50]);
$check($response->getStatusCode() === 200 && Task::findOrFail(1)->status === 'Doing' && ! Task::findOrFail(1)->is_completed, 'other statuses still save normally');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: Completed work updates save; pending sub-tasks give a clear message.\n";
exit($failures ? 1 : 0);
