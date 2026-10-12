<?php

// Run with: php tests/regression/attendance_clock_window.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
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
foreach ([
    'users' => 'id integer primary key, company_id integer, name text, role text, is_active integer, created_at text, updated_at text',
    'employee_details' => 'id integer primary key, user_id integer, joining_date text',
    'attendances' => 'id integer primary key, user_id integer, company_id integer, date text, status text, clock_in text, clock_out text, late text, half_day text, work_from_type text, auto_clocked_out integer, total_hours real, archived_at text, created_at text, updated_at text',
    'attendance_settings' => 'id integer primary key, office_start_time text, late_time text, half_day_threshold_minutes integer, day_off_threshold_minutes integer, created_at text, updated_at text',
    'leaves' => 'id integer primary key, user_id integer, date text, start_date text, end_date text, status text',
    'holidays' => 'id integer primary key, company_id integer, date text, occassion text, title text',
    'app_settings' => 'id integer primary key, key text, value text, created_at text, updated_at text',
] as $table => $columns) {
    DB::statement("CREATE TABLE {$table} ({$columns})");
}
function check(bool $ok, string $message): void { if (! $ok) throw new RuntimeException($message); }

DB::table('users')->insert([
    ['id' => 1, 'company_id' => 7, 'name' => 'Admin', 'role' => 'admin', 'is_active' => 1, 'created_at' => '2026-01-01'],
    ['id' => 2, 'company_id' => 7, 'name' => 'Employee', 'role' => 'employee', 'is_active' => 1, 'created_at' => '2026-01-01'],
    ['id' => 3, 'company_id' => 7, 'name' => 'HR', 'role' => 'hr', 'is_active' => 1, 'created_at' => '2026-01-01'],
    ['id' => 4, 'company_id' => 7, 'name' => 'Manager on leave', 'role' => 'manager', 'is_active' => 1, 'created_at' => '2026-01-01'],
    ['id' => 5, 'company_id' => 7, 'name' => 'Joins later', 'role' => 'employee', 'is_active' => 1, 'created_at' => '2026-01-01'],
    ['id' => 6, 'company_id' => 8, 'name' => 'Other company', 'role' => 'employee', 'is_active' => 1, 'created_at' => '2026-01-01'],
]);
DB::table('employee_details')->insert(['user_id' => 5, 'joining_date' => '2026-10-20']);
DB::table('leaves')->insert(['user_id' => 4, 'date' => '2026-10-13', 'start_date' => '2026-10-13', 'end_date' => '2026-10-13', 'status' => 'approved']);

$employee = User::find(2);
$admin = User::find(1);
$day = fn (string $time) => Carbon::parse('2026-10-13 ' . $time, 'Asia/Kolkata'); // a Tuesday

// Switched off: nothing changes.
AttendanceClockPolicy::ensureSchema();
WorkScheduleService::attendancePolicy();
check(AttendanceClockPolicy::clockInBlockedReason($employee, $day('06:00')) === null, 'Disabled boundary blocked clock-in.');
check(AttendanceClockPolicy::markMissedClockIns(7, $day('23:00'), false) === 0, 'Disabled boundary marked absences.');

AttendanceSetting::query()->update([
    'late_time' => '09:30:00', 'clock_window_enabled' => 1, 'clock_window_enabled_at' => '2026-10-13',
    'clock_in_opens_at' => '08:30:00', 'clock_in_closes_at' => '11:00:00', 'half_day_after_time' => '10:30:00',
    'office_end_time' => '18:30:00', 'clock_out_closes_at' => '20:00:00', 'missed_clock_out_status' => 'half_day',
]);
AttendanceClockPolicy::forget();

// Window.
check(str_contains((string) AttendanceClockPolicy::clockInBlockedReason($employee, $day('08:00')), 'opens at 08:30 AM'), 'Early clock-in allowed.');
check(AttendanceClockPolicy::clockInBlockedReason($employee, $day('09:00')) === null, 'Clock-in inside the window blocked.');
check(str_contains((string) AttendanceClockPolicy::clockInBlockedReason($employee, $day('11:01')), 'closed at 11:00 AM'), 'Late clock-in allowed after closing.');
check(AttendanceClockPolicy::clockInBlockedReason($admin, $day('06:00')) === null, 'Admin was restricted.');
check(str_contains((string) AttendanceClockPolicy::windowSummary($employee), '08:30 AM – 11:00 AM'), 'Window summary missing.');

// Statuses.
$make = fn (int $user, string $date, string $in, ?string $out = null, bool $auto = false) => WorkScheduleService::applyOrganizationAttendanceRules(
    Attendance::create(['user_id' => $user, 'company_id' => 7, 'date' => $date, 'status' => 'present', 'clock_in' => $in, 'clock_out' => $out, 'auto_clocked_out' => $auto]));
check($make(2, '2026-10-14', '09:10:00')->status === 'present', 'On-time clock-in not present.');
check($make(3, '2026-10-14', '09:45:00')->status === 'late', 'Clock-in after late cutoff not late.');
$half = $make(4, '2026-10-14', '10:45:00');
check($half->status === 'half_day' && $half->half_day === 'yes' && $half->late === 'yes', 'Clock-in after half-day time not half day.');
$missed = $make(2, '2026-10-15', '09:00:00', '20:00:00', true);
check($missed->status === 'half_day' && $missed->system_status_reason === AttendanceClockPolicy::MISSED_CLOCK_OUT, 'Missed clock-out not half day.');
$before = $make(2, '2026-10-12', '10:45:00');
check($before->status === 'late', 'Boundary rewrote a day before it was switched on.');
$open = Attendance::create(['user_id' => 2, 'company_id' => 7, 'date' => '2026-10-16', 'status' => 'present', 'clock_in' => '09:00:00']);
check($open->auto_clock_out_at->format('H:i') === '20:00', 'Auto clock-out time not taken from settings.');

// Missed clock-ins on 13 Oct (Tue): employee + HR absent; admin exempt; manager on leave; user 5 not joined; other company untouched.
DB::table('attendances')->whereDate('date', '>=', '2026-10-14')->delete();
check(AttendanceClockPolicy::markMissedClockIns(7, $day('10:00'), false) === 0, 'Absences marked while the window was open.');
check(AttendanceClockPolicy::markMissedClockIns(7, $day('11:30'), false) === 2, 'Wrong number of missed clock-ins marked.');
check(Attendance::whereDate('date', '2026-10-13')->where('status', 'absent')->where('system_status_reason', AttendanceClockPolicy::MISSED_CLOCK_IN)->pluck('user_id')->sort()->values()->all() === [2, 3], 'Wrong employees marked absent.');
check(AttendanceClockPolicy::markMissedClockIns(7, $day('11:45'), false) === 0, 'Absences marked twice.');
check(str_contains((string) AttendanceClockPolicy::clockInBlockedReason($employee, $day('11:50')), 'Today is marked absent'), 'Blocked message does not mention the absence.');
// Saturday 17 Oct is a weekly off.
check(AttendanceClockPolicy::markMissedClockIns(7, Carbon::parse('2026-10-17 12:00', 'Asia/Kolkata'), false) === 9, 'Weekday absences for 14-16 Oct (3 staff x 3 days) expected, weekend skipped.');

foreach (['admin.attendance.settings', 'admin.partials.clock-window-note'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(view()->getFinder()->find($view))), TOKEN_PARSE);
}
echo "PASS: clock-in window, admin exemption, late/half-day/missed clock-out statuses, auto clock-out time, missed clock-ins marked absent (leave, joining date, weekly off, company isolation), disabled = unchanged.\n";
