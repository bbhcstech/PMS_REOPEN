<?php

// Inherits only the isolated in-memory attendance fixture, never live data.
require __DIR__ . '/attendance_save.php';

use App\Models\Attendance;
use App\Services\AttendanceAutoClockOut;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Schema};

(require base_path('database/migrations/tenant/2026_10_11_000001_add_attendance_session_timezone.php'))->up();
Schema::table('attendances', function ($t) { $t->unsignedBigInteger('company_id')->nullable(); $t->timestamp('archived_at')->nullable(); $t->decimal('total_hours', 8, 2)->nullable(); });
DB::table('users')->insert([
    ['id' => 3, 'company_id' => 1, 'name' => 'HR', 'role' => 'hr', 'is_active' => true],
    ['id' => 4, 'company_id' => 1, 'name' => 'Manager', 'role' => 'manager', 'is_active' => true],
    ['id' => 5, 'company_id' => 2, 'name' => 'Other company', 'role' => 'employee', 'is_active' => true],
]);
$makeSession = function (int $user, string $date, string $in, string $timezone = 'Asia/Kolkata', ?string $out = null, ?string $archived = null) {
    $id = DB::table('attendances')->insertGetId(['user_id' => $user, 'company_id' => $user === 5 ? 2 : 1, 'date' => $date, 'clock_in' => $in, 'clock_out' => $out, 'status' => 'present', 'clock_in_timezone' => $timezone, 'archived_at' => $archived]);
    return Attendance::withoutGlobalScopes()->findOrFail($id);
};
$employee = $makeSession(2, '2026-10-11', '09:30:00');
$hr = $makeSession(3, '2026-10-11', '10:30:00');
$manager = $makeSession(4, '2026-10-11', '23:30:00');
$foreign = $makeSession(5, '2026-10-11', '09:00:00');
$manual = $makeSession(2, '2026-10-10', '09:00:00', 'Asia/Kolkata', '18:00:00');
$archived = $makeSession(3, '2026-10-10', '09:00:00', 'Asia/Kolkata', null, '2026-10-11');
$service = new AttendanceAutoClockOut;
Carbon::setTestNow(Carbon::parse('2026-10-11 23:57:59', 'Asia/Kolkata'));
checkSave($service->closeDue(1) === 0, 'Session closed before 23:58.');
Carbon::setTestNow(Carbon::parse('2026-10-11 23:58:00', 'Asia/Kolkata'));
checkSave($service->closeDue(1, 2) === 1, 'Employee catch-up did not close own session.');
checkSave($hr->fresh()->clock_out === null, 'Employee catch-up changed another user.');
checkSave($service->closeDue(1) === 2, 'HR and manager sessions were not closed.');
$employee->refresh(); $hr->refresh(); $manager->refresh();
checkSave($employee->clock_out === '23:58:00' && $employee->auto_clocked_out, 'Clock-out timestamp or automatic marker incorrect.');
checkSave($employee->total_duration === '14:28:00' && $employee->total_seconds === 52080, 'Employee work time incorrect.');
checkSave($hr->total_duration === '13:28:00', 'HR work time incorrect.');
checkSave($manager->total_duration === '00:28:00', 'Manager short shift time incorrect.');
checkSave((float) $employee->total_hours === 14.47, 'Stored total hours incorrect.');
checkSave($foreign->fresh()->clock_out === null && $manual->fresh()->clock_out === '18:00:00' && $archived->fresh()->clock_out === null, 'Another company, manual or archived attendance was modified.');
checkSave($service->closeDue(1) === 0, 'Repeated automatic clock-out changed a completed session.');
$missed = $makeSession(2, '2026-10-09', '09:00:00');
checkSave($missed->total_duration === '14:58:00', 'Old open shift still returns fixed hours or counts overnight.');
checkSave($service->closeDue(1) === 1 && $missed->fresh()->clock_out === '23:58:00', 'Missed scheduler run was not caught up.');
$dubai = $makeSession(3, '2026-10-11', '09:00:00', 'Asia/Dubai');
checkSave($service->closeDue(1) === 0, 'Dubai session closed using India timezone.');
Carbon::setTestNow(Carbon::parse('2026-10-11 23:58:00', 'Asia/Dubai'));
checkSave($service->closeDue(1) === 1 && $dubai->fresh()->total_duration === '14:58:00', 'Session timezone cutoff incorrect.');
$lateLegacy = $makeSession(4, '2026-10-10', '23:59:00');
checkSave($service->closeDue(1) === 1 && $lateLegacy->fresh()->total_seconds === 0, 'After-cutoff legacy record became an overnight shift.');
Carbon::setTestNow();
echo "Automatic clock-out checks passed: employee/HR/manager isolation, 23:58 cutoff, exact durations, timezones, missed runs, manual/archived preservation and idempotency.\n";
