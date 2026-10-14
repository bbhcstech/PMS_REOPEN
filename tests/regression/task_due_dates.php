<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\TaskController;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
auth()->guard()->setUser(new User(['role' => 'admin']));
class TaskDateValidationReached extends RuntimeException {}
$queries = 0;
DB::connection('tenant')->beforeExecuting(function () use (&$queries) {
    $queries++;
    throw new TaskDateValidationReached;
});
$controller = new TaskController;
foreach (['store', 'update'] as $method) {
    $invoke = function ($request) use ($controller, $method) {
        return $method === 'store' ? $controller->store($request) : $controller->update($request, new Task);
    };
    foreach ([null, '0', 0, false, 'false'] as $unchecked) {
        $data = ['start_date' => '2026-10-09', 'due_date' => '2026-10-06'];
        if ($unchecked !== null) $data['without_due_date'] = $unchecked;
        $before = $queries;
        try {
            $invoke(Request::create('/tasks', 'POST', $data));
            throw new RuntimeException('Earlier due date accepted.');
        } catch (ValidationException $e) {
            if (! isset($e->errors()['due_date'])) throw new RuntimeException('Due date error missing.');
            if ($queries !== $before) throw new RuntimeException('Invalid dates reached database work.');
        }
    }
    foreach ([
        ['start_date' => '2026-10-09', 'due_date' => '2026-10-09'],
        ['start_date' => '2026-10-09', 'due_date' => '2026-10-10'],
        ['start_date' => '2026-10-09', 'due_date' => '2026-10-06', 'without_due_date' => 'on'],
        ['start_date' => '2026-10-09', 'due_date' => null],
        ['due_date' => '2026-10-09'],
    ] as $data) {
        $request = Request::create('/tasks', 'POST', $data);
        try {
            $invoke($request);
            throw new RuntimeException('Expected next controller step missing.');
        } catch (TaskDateValidationReached $e) {
            if ($request->boolean('without_due_date') && $request->input('due_date') !== null) {
                throw new RuntimeException('Without Due Date retained a stale date.');
            }
        }
    }
}
echo "PASS: task create/edit reject earlier due dates including false checkbox values; equal/later dates and Without Due Date remain supported.\n";
