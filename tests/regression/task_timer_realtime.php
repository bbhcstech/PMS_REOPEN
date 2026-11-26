<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\TaskTimerController;
use App\Models\{Task, TaskTimer, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant', 'session.driver' => 'array', 'app.timezone' => 'Asia/Kolkata']);
DB::purge('tenant');
Schema::create('task_timers', function ($t) {
    $t->id(); $t->unsignedBigInteger('task_id'); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('project_id')->nullable();
    $t->date('start_date')->nullable(); $t->date('end_date')->nullable(); $t->dateTime('start_time');
    $t->dateTime('end_time')->nullable(); $t->dateTime('pause_time')->nullable(); $t->text('memo')->nullable();
    $t->decimal('total_hours', 8, 2)->default(0); $t->timestamps();
});
Auth::guard('web')->setUser((new User)->forceFill(['id' => 1, 'role' => 'admin']));
$app['session']->start();
$task = (new Task)->forceFill(['id' => 9, 'project_id' => null]);
$controller = new TaskTimerController;
try {
    Carbon::setTestNow(Carbon::parse('2026-10-14 23:59:30', 'Asia/Kolkata'));
    $controller->start($task);
    $timer = TaskTimer::firstOrFail();
    if ($timer->start_time->format('Y-m-d H:i:s') !== '2026-10-14 23:59:30') throw new RuntimeException('Start timestamp is not actual start time');
    Carbon::setTestNow(Carbon::parse('2026-10-15 00:01:30', 'Asia/Kolkata'));
    $request = Request::create('/tasks/9/timer/stop', 'POST', [
        'timer_id' => $timer->id, 'memo' => 'Completed work', 'start_date' => '1990-01-01', 'end_date' => '1990-01-01',
    ]);
    $controller->stop($request, $task);
    $timer->refresh();
    if ($timer->start_time->format('H:i:s') !== '23:59:30' || $timer->end_time->format('Y-m-d H:i:s') !== '2026-10-15 00:01:30') throw new RuntimeException('Start or stop timestamp incorrect');
    if ($timer->start_date->toDateString() !== '2026-10-14' || $timer->end_date->toDateString() !== '2026-10-15') throw new RuntimeException('Dates came from stale browser values');
    if ((float) $timer->total_hours !== 0.03) throw new RuntimeException('Elapsed time is negative or incorrect');
    if ($timer->memo !== 'Completed work') throw new RuntimeException('Memo saving changed');
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/tasks/show.blade.php'))), TOKEN_PARSE);
    echo "PASS: actual timer start/stop timestamps, midnight rollover, positive elapsed hours, memo and template.\n";
} finally {
    Carbon::setTestNow();
}
