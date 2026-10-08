<?php

// Run with: php tests/regression/attendance_calendar.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\AttendanceCalendar;
use Carbon\Carbon;

Carbon::setTestNow(Carbon::parse('2026-10-08 14:00:00'));
$records = collect([
    (object) ['date' => '2026-10-01', 'status' => 'present', 'total_seconds' => 14400, 'work_from_type' => 'home'],
    (object) ['date' => '2026-10-01', 'status' => 'present', 'total_seconds' => 14400, 'work_from_type' => 'home'],
    (object) ['date' => '2026-10-02', 'status' => 'late', 'total_seconds' => 27000],
    (object) ['date' => '2026-10-05', 'status' => 'present', 'half_day' => 'yes', 'total_seconds' => 14400],
]);
$holidays = collect([
    (object) ['date' => '2026-10-06', 'title' => 'Company holiday'],
    (object) ['date' => '2026-10-07', 'title' => 'Other department holiday', 'department_id_json' => '[99]'],
]);
$leaves = collect([(object) ['start_date' => '2026-09-30', 'end_date' => '2026-10-01', 'duration' => 'full_day'], (object) ['date' => '2026-10-07', 'duration' => 'half_day']]);
$workingDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$result = (new AttendanceCalendar)->build(Carbon::parse('2026-10-01'), $records, $holidays, $leaves, (object) ['department_id' => 1], $workingDays);
$days = collect($result['days'])->keyBy('key');
function checkCalendar($condition, $message) { if (!$condition) throw new RuntimeException($message); }
checkCalendar(count($result['days']) === 31 && $result['offset'] === 4, 'October calendar alignment is incorrect.');
checkCalendar($days['2026-10-01']['seconds'] === 28800 && $days['2026-10-01']['wfh'], 'Multiple sessions or WFH were lost.');
checkCalendar($days['2026-10-01']['status'] === 'present' && $days['2026-10-01']['leave'] !== null, 'Leave overwrote recorded hours.');
checkCalendar($days['2026-10-03']['status'] === 'day_off', 'Configured day off was marked absent.');
checkCalendar($days['2026-10-06']['status'] === 'holiday', 'Holiday was not applied.');
checkCalendar($days['2026-10-07']['status'] === 'half_day' && $days['2026-10-07']['holiday'] === null, 'Half-day leave or holiday targeting failed.');
checkCalendar($days['2026-10-09']['status'] === 'not_marked', 'Future day was marked absent.');
checkCalendar($result['totals']['seconds'] === 70200 && $result['totals']['late'] === 1 && $result['totals']['wfh'] === 1, 'Monthly totals are incorrect.');
$leap = (new AttendanceCalendar)->build(Carbon::parse('2024-02-01'), collect(), collect(), collect(), (object) ['joining_date' => '2024-02-05'], $workingDays);
checkCalendar(count($leap['days']) === 29 && $leap['days'][0]['status'] === 'not_joined', 'Leap year or joining date handling failed.');
foreach (['calendar', 'create', 'index'] as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/attendance/' . $view . '.blade.php')));
    token_get_all($compiled, TOKEN_PARSE);
}
Carbon::setTestNow();
// Exercise the actual calendar controller with isolated in-memory company data.
config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant', 'session.driver' => 'array']);
\Illuminate\Support\Facades\DB::purge('tenant');
\Illuminate\Support\Facades\Schema::create('users', function ($table) {
    $table->id(); $table->unsignedBigInteger('company_id'); $table->string('name'); $table->string('email'); $table->string('role'); $table->timestamp('archived_at')->nullable();
});
\Illuminate\Support\Facades\Schema::create('employee_details', function ($table) { $table->id(); $table->unsignedBigInteger('user_id'); $table->string('employee_id')->nullable(); });
\Illuminate\Support\Facades\Schema::create('attendances', function ($table) { $table->id(); $table->unsignedBigInteger('user_id'); $table->date('date'); $table->timestamp('archived_at')->nullable(); });
\Illuminate\Support\Facades\Schema::create('holidays', function ($table) { $table->id(); $table->unsignedBigInteger('company_id'); $table->date('date'); $table->string('title'); $table->timestamp('archived_at')->nullable(); });
\Illuminate\Support\Facades\Schema::create('leaves', function ($table) { $table->id(); $table->unsignedBigInteger('user_id'); $table->date('date')->nullable(); $table->date('start_date')->nullable(); $table->date('end_date')->nullable(); $table->string('status'); $table->timestamp('archived_at')->nullable(); });
\Illuminate\Support\Facades\Schema::create('app_settings', function ($table) { $table->id(); $table->string('key'); $table->text('value'); });
\Illuminate\Support\Facades\DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Local HR', 'email' => 'hr@local.test', 'role' => 'hr'],
    ['id' => 2, 'company_id' => 2, 'name' => 'Foreign', 'email' => 'foreign@local.test', 'role' => 'employee'],
    ['id' => 3, 'company_id' => 1, 'name' => 'Local employee', 'email' => 'employee@local.test', 'role' => 'employee'],
]);
\Illuminate\Support\Facades\DB::table('holidays')->insert([
    ['company_id' => 1, 'date' => '2026-10-06', 'title' => 'Local holiday'],
    ['company_id' => 2, 'date' => '2026-10-07', 'title' => 'Foreign holiday'],
]);
\Illuminate\Support\Facades\Auth::guard('web')->setUser(\App\Models\User::findOrFail(1));
$controller = new \App\Http\Controllers\AttendanceController;
$data = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026, 'user_id' => 3]))->getData();
checkCalendar($data['employees']->pluck('id')->sort()->values()->all() === [1, 3], 'Calendar employee dropdown leaked another company.');
checkCalendar($data['selectedEmployee']->id === 3, 'Calendar employee selection failed.');
$scopedDays = collect($data['calendar']['days'])->keyBy('key');
checkCalendar($scopedDays['2026-10-06']['holiday'] !== null && $scopedDays['2026-10-07']['holiday'] === null, 'Calendar holiday company isolation failed.');
try {
    $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['user_id' => 2]));
    throw new RuntimeException('Foreign employee calendar was allowed.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { checkCalendar($e->getStatusCode() === 403, 'Unexpected access error.'); }
\Illuminate\Support\Facades\Auth::guard('web')->setUser(\App\Models\User::findOrFail(3));
$self = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET'))->getData();
checkCalendar($self['employees']->pluck('id')->all() === [3], 'Employee can see other employee options.');
echo "Attendance calendar and template checks passed.\n";
