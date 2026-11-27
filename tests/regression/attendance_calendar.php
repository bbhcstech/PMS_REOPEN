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
checkCalendar($days['2026-10-03']['status'] === 'scheduled_off', 'Configured non-Sunday rest day was marked absent or counted as a short shift.');
checkCalendar($days['2026-10-04']['status'] === 'weekend', 'Sunday was not distinguished as Weekend.');
checkCalendar($days['2026-10-06']['status'] === 'holiday', 'Holiday was not applied.');
checkCalendar($days['2026-10-07']['status'] === 'half_day' && $days['2026-10-07']['holiday'] === null, 'Half-day leave or holiday targeting failed.');
checkCalendar($days['2026-10-09']['status'] === 'not_marked', 'Future day was marked absent.');
checkCalendar($result['totals']['seconds'] === 70200 && $result['totals']['late'] === 1 && $result['totals']['wfh'] === 1, 'Monthly totals are incorrect.');
$leap = (new AttendanceCalendar)->build(Carbon::parse('2024-02-01'), collect(), collect(), collect(), (object) ['joining_date' => '2024-02-05'], $workingDays);
checkCalendar(count($leap['days']) === 29 && $leap['days'][0]['status'] === 'not_joined', 'Leap year or joining date handling failed.');
foreach (['calendar', 'create', 'team'] as $view) {
    $source = file_get_contents(resource_path('views/admin/attendance/' . $view . '.blade.php'));
    checkCalendar(str_contains($source, 'data-live-records-off'), "Attendance {$view} view must disable 5-second live-records DOM polling.");
    $compiled = app('blade.compiler')->compileString($source);
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
    ['id' => 1, 'company_id' => 1, 'name' => 'Local Admin', 'email' => 'admin@local.test', 'role' => 'admin'],
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
checkCalendar($data['employees']->pluck('id')->sort()->values()->all() === [3], 'Calendar employee dropdown leaked another company.');
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
checkCalendar($self['selectedEmployee']->id === 3, 'My attendance did not default to the logged-in employee.');
try {
    $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['user_id' => 1]));
    throw new RuntimeException('Employee could select another company member.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Unexpected self-attendance access error.');
}
$calendarSource = file_get_contents(resource_path('views/admin/attendance/calendar.blade.php'));
preg_match('/<form class="ac-panel ac-toolbar"[\s\S]*?<\/form>/', $calendarSource, $toolbarMatch);
$toolbarData = ['attendanceRoute' => 'attendance.index', 'navigation' => fn ($date) => '/attendance?month='.$date->month, 'previous' => $self['start']->copy()->subMonth(), 'next' => $self['start']->copy()->addMonth()] + $self;
$toolbar = \Illuminate\Support\Facades\Blade::render($toolbarMatch[0], $toolbarData);
checkCalendar(!str_contains($toolbar, 'name="user_id"') && str_contains($toolbar, 'Local employee'), 'My attendance still offers employee selection.');
$toolbarData['canManage'] = true;
$toolbar = \Illuminate\Support\Facades\Blade::render($toolbarMatch[0], $toolbarData);
checkCalendar(str_contains($toolbar, 'name="user_id"'), 'Administrator employee selection was removed.');
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
checkCalendar($team['summary']['employees'] === 1 && $team['teamRows']->pluck('employee.id')->sort()->values()->all() === [3], 'Team view leaked another company.');
checkCalendar($team['summary']['seconds'] === 25200, 'Team hours lost sessions or included archived/foreign records.');
$teamEmployee = collect($team['teamRows']->items())->first(fn ($row) => $row['employee']->id === 3);
$individual = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026, 'user_id' => 3]))->getData();
checkCalendar($teamEmployee['calendar']['totals'] === $individual['calendar']['totals'], 'Table totals differ from individual calendar.');
$template = preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/attendance/team.blade.php')));
$html = \Illuminate\Support\Facades\Blade::render($template, $team + ['errors' => new \Illuminate\Support\ViewErrorBag]);
checkCalendar(str_contains($html, 'employee@local.test') && str_contains($html, 'Session 2') && str_contains($html, '7h 00m') && str_contains($html, 'at-day-trigger') && str_contains($html, 'id="at-day-modal"') && ! str_contains($html, 'foreign@local.test'), 'Rendered table lost attendance details or leaked data.');
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
checkCalendar($secondPage['teamRows']->total() === 27 && $secondPage['teamRows']->count() === 2 && $secondPage['summary']['employees'] === 27, 'Team pagination or full filtered totals failed.');

if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'company_staff_role_id')) {
    \Illuminate\Support\Facades\Schema::table('users', function ($table) {
        $table->unsignedBigInteger('company_staff_role_id')->nullable();
    });
}
if (! \Illuminate\Support\Facades\Schema::hasColumn('attendances', 'staff_category')) {
    \Illuminate\Support\Facades\Schema::table('attendances', function ($table) {
        $table->string('staff_category')->nullable();
    });
}
\Illuminate\Support\Facades\DB::table('users')->insert([
    ['id' => 30, 'company_id' => 1, 'name' => 'Manager One', 'email' => 'manager@local.test', 'role' => 'manager', 'company_staff_role_id' => null],
    ['id' => 31, 'company_id' => 1, 'name' => 'Dev One', 'email' => 'dev@local.test', 'role' => 'developer', 'company_staff_role_id' => null],
    ['id' => 32, 'company_id' => 1, 'name' => 'Custom Lead', 'email' => 'custom@local.test', 'role' => 'employee', 'company_staff_role_id' => 5],
    ['id' => 33, 'company_id' => 1, 'name' => 'HR User', 'email' => 'hr@local.test', 'role' => 'HR', 'company_staff_role_id' => null],
    ['id' => 34, 'company_id' => 1, 'name' => 'Client User', 'email' => 'client@local.test', 'role' => 'client', 'company_staff_role_id' => null],
]);
\Illuminate\Support\Facades\DB::table('attendances')->insert([
    ['user_id' => 30, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '12:00:00', 'staff_category' => 'authority', 'archived_at' => null],
    ['user_id' => 31, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '11:00:00', 'staff_category' => 'authority', 'archived_at' => null],
    ['user_id' => 32, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '10:00:00', 'staff_category' => 'authority', 'archived_at' => null],
    ['user_id' => 33, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '17:00:00', 'staff_category' => 'authority', 'archived_at' => null],
]);
$allNonHr = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026, 'user_id' => 30]))->getData();
$nonHrIds = $allNonHr['employees']->pluck('id')->all();
checkCalendar(in_array(30, $nonHrIds, true) && in_array(31, $nonHrIds, true) && in_array(32, $nonHrIds, true), 'Non-HR manager/developer/custom-role employees missing from attendance.');
checkCalendar(! in_array(33, $nonHrIds, true) && ! in_array(34, $nonHrIds, true) && ! in_array(1, $nonHrIds, true), 'HR, client or admin appeared in attendance.');
checkCalendar($allNonHr['calendar']['totals']['seconds'] === 10800, 'Attendance hours for authority-marked non-HR employee were not fetched.');
try {
    $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['user_id' => 33]));
    throw new RuntimeException('HR attendance selection should be rejected.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Unexpected HR denial status.');
}
\Illuminate\Support\Facades\Auth::guard('web')->setUser(\App\Models\User::findOrFail(33));
$hrView = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026]));
checkCalendar($hrView->name() === 'admin.attendance.personal' && $hrView->getData()['employee']->id === 33, 'HR attendance entry point must remain HR personal attendance.');
\Illuminate\Support\Facades\Auth::guard('web')->setUser(\App\Models\User::findOrFail(1));
Carbon::setTestNow(Carbon::parse('2026-10-01 15:00:00'));
$todayCsvResponse = $controller->index($teamRequest(['format' => 'csv', 'export_scope' => 'today']));
ob_start();
$todayCsvResponse->sendContent();
$todayCsv = ob_get_clean();
checkCalendar(str_contains($todayCsvResponse->headers->get('Content-Disposition'), 'attendance-2026-10-01.csv'), 'Today CSV filename mismatch.');
checkCalendar(substr_count($todayCsv, ',2026-10-01,') >= 2 && ! str_contains($todayCsv, ',2026-10-02,'), 'Today CSV included other days.');

$rangeCsvResponse = $controller->index($teamRequest(['format' => 'csv', 'export_scope' => 'range', 'start_date' => '2026-09-29', 'end_date' => '2026-10-02']));
ob_start();
$rangeCsvResponse->sendContent();
$rangeCsv = ob_get_clean();
checkCalendar(str_contains($rangeCsvResponse->headers->get('Content-Disposition'), 'attendance-2026-09-29-to-2026-10-02.csv'), 'Range CSV filename mismatch.');
checkCalendar(str_contains($rangeCsv, ',2026-09-30,') && str_contains($rangeCsv, ',2026-10-02,') && ! str_contains($rangeCsv, ',2026-10-03,'), 'Range CSV boundaries failed.');

$calendarTodayCsv = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['user_id' => 3, 'month' => 10, 'year' => 2026, 'format' => 'csv', 'export_scope' => 'today']));
ob_start();
$calendarTodayCsv->sendContent();
$calendarTodayContent = ob_get_clean();
checkCalendar(str_contains($calendarTodayContent, 'employee@local.test,2026-10-01,') && ! str_contains($calendarTodayContent, 'manager@local.test'), 'Calendar view CSV did not scope to selected employee.');
try {
    $controller->index($teamRequest(['format' => 'csv', 'export_scope' => 'range', 'start_date' => '2026-10-05', 'end_date' => '2026-10-01']));
    throw new RuntimeException('Reversed export date range was accepted.');
} catch (\Illuminate\Validation\ValidationException $e) {
    checkCalendar(isset($e->errors()['end_date']), 'Missing end_date validation error.');
}
$calendarAllCsv = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026, 'format' => 'csv', 'export_scope' => 'today']));
ob_start();
$calendarAllCsv->sendContent();
$calendarAllContent = ob_get_clean();
checkCalendar(str_contains($calendarAllContent, 'employee@local.test,2026-10-01,') && str_contains($calendarAllContent, 'manager@local.test,2026-10-01,') && ! str_contains($calendarAllContent, 'hr@local.test'), 'Calendar CSV without selected employees should export all non-HR employees.');

$calendarSelectedCsv = $controller->index(\Illuminate\Http\Request::create('/attendance', 'GET', ['month' => 10, 'year' => 2026, 'format' => 'csv', 'export_scope' => 'range', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02', 'user_ids' => [30, 31]]));
ob_start();
$calendarSelectedCsv->sendContent();
$calendarSelectedContent = ob_get_clean();
checkCalendar(str_contains($calendarSelectedContent, 'manager@local.test,2026-10-01,') && str_contains($calendarSelectedContent, 'dev@local.test,2026-10-02,') && ! str_contains($calendarSelectedContent, 'employee@local.test') && ! str_contains($calendarSelectedContent, 'hr@local.test'), 'Calendar CSV with selected employees leaked unselected or HR users.');

$selectedCsvResponse = $controller->index($teamRequest(['format' => 'csv', 'export_scope' => 'today', 'user_ids' => [30, 33, 2]]));
ob_start();
$selectedCsvResponse->sendContent();
$selectedCsv = ob_get_clean();
checkCalendar(str_contains($selectedCsv, 'manager@local.test,2026-10-01,') && ! str_contains($selectedCsv, 'employee@local.test') && ! str_contains($selectedCsv, 'hr@local.test') && ! str_contains($selectedCsv, 'foreign@local.test'), 'Selected employee CSV export leaked unselected, HR or foreign users.');

$allDefaultCsvResponse = $controller->index($teamRequest(['format' => 'csv', 'export_scope' => 'today']));
ob_start();
$allDefaultCsvResponse->sendContent();
$allDefaultCsv = ob_get_clean();
checkCalendar(str_contains($allDefaultCsv, 'employee@local.test,2026-10-01,') && str_contains($allDefaultCsv, 'manager@local.test,2026-10-01,') && ! str_contains($allDefaultCsv, 'hr@local.test'), 'Team CSV without selected employees should export all non-HR employees.');

Carbon::setTestNow();

echo "Attendance calendar and template checks passed.\n";
