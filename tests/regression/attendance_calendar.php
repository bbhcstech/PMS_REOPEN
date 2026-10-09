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
foreach (['calendar', 'create', 'team'] as $view) {
    $compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/attendance/' . $view . '.blade.php')));
    token_get_all($compiled, TOKEN_PARSE);
}
Carbon::setTestNow();
// Exercise the actual calendar controller with isolated in-memory company data.
config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant', 'session.driver' => 'array']);
\Illuminate\Support\Facades\DB::purge('tenant');
config(['database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
\Illuminate\Support\Facades\DB::purge('central');
\Illuminate\Support\Facades\Schema::create('users', function ($table) {
    $table->id(); $table->unsignedBigInteger('company_id'); $table->string('name'); $table->string('email'); $table->string('role'); $table->timestamp('archived_at')->nullable();
});
\Illuminate\Support\Facades\Schema::create('employee_details', function ($table) { $table->id(); $table->unsignedBigInteger('user_id'); $table->string('employee_id')->nullable(); });
\Illuminate\Support\Facades\Schema::create('attendances', function ($table) { $table->id(); $table->unsignedBigInteger('user_id'); $table->date('date'); $table->string('status')->nullable(); $table->string('clock_in')->nullable(); $table->string('clock_out')->nullable(); $table->timestamp('archived_at')->nullable(); });
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
$legacy = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['view' => 'grid']));
checkCalendar($legacy->name() === 'admin.attendance.calendar', 'Legacy grid URL must show the calendar.');

\Illuminate\Support\Facades\DB::table('attendances')->insert([
    ['user_id' => 3, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '12:00:00', 'archived_at' => null],
    ['user_id' => 3, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '13:00:00', 'clock_out' => '17:00:00', 'archived_at' => null],
    ['user_id' => 3, 'date' => '2026-10-02', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'archived_at' => '2026-10-03 00:00:00'],
    ['user_id' => 2, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '00:00:00', 'clock_out' => '23:00:00', 'archived_at' => null],
]);
$teamRequest = fn ($extra = []) => \Illuminate\Http\Request::create('/attendance', 'GET', array_merge(['view' => 'team', 'month' => 10, 'year' => 2026], $extra));
$employeeTeam = $controller->index($teamRequest())->getData();
checkCalendar($employeeTeam['teamRows']->total() === 1 && $employeeTeam['teamRows'][0]['employee']->id === 3, 'Employee team view leaked other employees.');
\Illuminate\Support\Facades\Auth::guard('web')->setUser(\App\Models\User::findOrFail(1));
$team = $controller->index($teamRequest())->getData();
checkCalendar($team['summary']['employees'] === 2 && $team['teamRows']->pluck('employee.id')->sort()->values()->all() === [1, 3], 'Team view leaked another company.');
checkCalendar($team['summary']['seconds'] === 25200, 'Team hours lost sessions or included archived/foreign records.');
$teamEmployee = collect($team['teamRows']->items())->first(fn ($row) => $row['employee']->id === 3);
$individual = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026, 'user_id' => 3]))->getData();
checkCalendar($teamEmployee['calendar']['totals'] === $individual['calendar']['totals'], 'Table totals differ from individual calendar.');
$template = preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/attendance/team.blade.php')));
$html = \Illuminate\Support\Facades\Blade::render($template, $team + ['errors' => new \Illuminate\Support\ViewErrorBag]);
checkCalendar(str_contains($html, 'employee@local.test') && str_contains($html, 'Session 2') && str_contains($html, '7h 00m') && ! str_contains($html, 'foreign@local.test'), 'Rendered table lost attendance details or leaked data.');
$searched = $controller->index($teamRequest(['search' => 'LOCAL EMPLOYEE']))->getData();
checkCalendar($searched['teamRows']->total() === 1 && $searched['teamRows'][0]['employee']->id === 3, 'Team search failed.');
$empty = $controller->index($teamRequest(['search' => 'Foreign']))->getData();
checkCalendar($empty['teamRows']->total() === 0 && $empty['summary']['seconds'] === 0, 'Search exposed foreign data.');
$csvResponse = $controller->index($teamRequest(['format' => 'csv']));
ob_start();
$csvResponse->sendContent();
$csv = ob_get_clean();
checkCalendar(substr_count($csv, '2026-10-01,present') === 2 && ! str_contains($csv, 'foreign@local.test'), 'CSV lost sessions or leaked foreign data.');
for ($id = 4; $id <= 29; $id++) {
    \Illuminate\Support\Facades\DB::table('users')->insert(['id' => $id, 'company_id' => 1, 'name' => 'Employee ' . $id, 'email' => 'employee' . $id . '@local.test', 'role' => 'employee']);
}
$secondPage = $controller->index($teamRequest(['page' => 2]))->getData();
checkCalendar($secondPage['teamRows']->total() === 28 && $secondPage['teamRows']->count() === 3 && $secondPage['summary']['employees'] === 28, 'Team pagination or full filtered totals failed.');
echo "Attendance calendar and template checks passed.\n";
