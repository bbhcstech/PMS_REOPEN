<?php

// Run with: php tests/regression/attendance_late_policy.php
// Late arrivals must follow Settings > Attendance Settings "late" cutoff on the employee's own clock,
// whatever timezone their device reports, and keep the existing half-day / day-off / WFH outcomes.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Services\AttendanceClockPolicy;
use App\Services\WorkScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'cache.default' => 'array', 'app.timezone' => 'Asia/Kolkata']);
date_default_timezone_set('Asia/Kolkata');
foreach ([
    'users' => 'id integer primary key, company_id integer, name text, role text, is_active integer, created_at text, updated_at text',
    'employee_details' => 'id integer primary key, user_id integer, joining_date text',
    'attendances' => 'id integer primary key, user_id integer, company_id integer, date text, status text, clock_in text, clock_in_timezone text, clock_out text, late text, half_day text, work_from_type text, auto_clocked_out integer, total_hours real, archived_at text, created_at text, updated_at text',
    'attendance_settings' => 'id integer primary key, office_start_time text, late_time text, half_day_threshold_minutes integer, day_off_threshold_minutes integer, created_at text, updated_at text',
    'leaves' => 'id integer primary key, user_id integer, date text, start_date text, end_date text, status text',
    'holidays' => 'id integer primary key, company_id integer, date text, occassion text, title text',
    'app_settings' => 'id integer primary key, key text, value text, created_at text, updated_at text',
] as $table => $columns) {
    DB::statement("CREATE TABLE {$table} ({$columns})");
}

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};

DB::table('users')->insert(['id' => 2, 'company_id' => 7, 'name' => 'Employee', 'role' => 'employee', 'is_active' => 1, 'created_at' => '2026-01-01']);
AttendanceClockPolicy::ensureSchema();
WorkScheduleService::attendancePolicy();
AttendanceSetting::query()->update(['late_time' => '09:30:00', 'half_day_threshold_minutes' => 510, 'day_off_threshold_minutes' => 270]);
AttendanceClockPolicy::forget();

$record = function (string $clockIn, ?string $clockOut = null, string $timezone = 'Asia/Kolkata', string $date = '2026-10-13') {
    DB::table('attendances')->delete();
    $attendance = Attendance::create([
        'user_id' => 2, 'company_id' => 7, 'date' => $date, 'status' => 'present',
        'clock_in' => $clockIn, 'clock_in_timezone' => $timezone, 'clock_out' => $clockOut, 'work_from_type' => 'office',
    ]);
    return WorkScheduleService::applyOrganizationAttendanceRules($attendance, WorkScheduleService::attendancePolicy())->fresh();
};

echo "Late cutoff (09:30) on the employee's clock\n";
foreach (['Asia/Kolkata', 'Asia/Dhaka', 'Asia/Dubai', 'Europe/London', 'America/New_York', 'UTC', 'Australia/Sydney'] as $tz) {
    $late = $record('10:05:00', null, $tz);
    $check($late->status === 'late' && $late->late === 'yes', "10:05 clock-in is late ($tz)");
    $onTime = $record('09:10:00', null, $tz);
    $check($onTime->status === 'present' && $onTime->late === 'no', "09:10 clock-in is present ($tz)");
}
$boundary = $record('09:30:00');
$check($boundary->status === 'present', 'clock-in exactly at the cutoff is not late');
$justAfter = $record('09:30:01');
$check($justAfter->status === 'late', 'one second after the cutoff is late');

AttendanceSetting::query()->update(['late_time' => '10:00:00']);
$check($record('09:45:00')->status === 'present', 'admin-changed cutoff 10:00: 09:45 is present');
$check($record('10:15:00')->status === 'late', 'admin-changed cutoff 10:00: 10:15 is late');
AttendanceSetting::query()->update(['late_time' => '09:30:00']);

echo "Existing outcomes kept\n";
$full = $record('10:05:00', '19:00:00');
$check($full->status === 'late', 'late arrival with a full shift stays late');
$half = $record('10:05:00', '16:00:00');
$check($half->status === 'half_day' && $half->late === 'yes', 'short shift (< half-day threshold) is half day and flagged late');
$dayOff = $record('09:00:00', '11:00:00');
$check($dayOff->status === 'day_off', 'very short shift (< day-off threshold) is day off');
$present = $record('09:00:00', '18:00:00');
$check($present->status === 'present' && $present->late === 'no', 'on-time full shift is present');
$wfh = $record('10:30:00');
$wfh->forceFill(['work_from_type' => 'wfh'])->save();
$wfh = WorkScheduleService::applyOrganizationAttendanceRules($wfh->fresh(), WorkScheduleService::attendancePolicy())->fresh();
$check($wfh->status === 'wfh' && $wfh->late === 'no', 'work from home is never marked late');

echo "Calendar shows the late day\n";
$record('10:05:00', '19:00:00');
$calendar = app(\App\Services\AttendanceCalendar::class)->build(
    Carbon::parse('2026-10-01'), Attendance::all(), collect(), collect(), null, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
);
$day = collect($calendar['days'])->firstWhere('key', '2026-10-13');
$check(($day['status'] ?? null) === 'late', 'calendar day status is late');
$check($calendar['totals']['late'] === 1 && $calendar['totals']['present'] === 0, 'calendar totals count it as Late, not Present');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: late cutoff on the employee's clock for every timezone, admin cutoff changes, half day / day off / WFH outcomes and calendar totals.\n";
exit($failures ? 1 : 0);
