<?php

// Isolated database and save controller fixture; no live company records are changed.
require __DIR__.'/attendance_save.php';

use App\Models\{Attendance, User};
use App\Services\AttendanceCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

Auth::guard('web')->setUser(User::findOrFail(1));
Carbon::setTestNow(Carbon::parse('2026-10-15 18:00:00'));
saveAttendance(['date' => '2026-10-12', 'clock_in' => '09:00', 'clock_out' => '13:29']);
saveAttendance(['date' => '2026-10-13', 'clock_in' => '09:00', 'clock_out' => '13:30']);
saveAttendance(['date' => '2026-10-14', 'clock_in' => '09:00', 'clock_out' => null]);
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-12')->first()->status === 'day_off', 'Completed office shift under 4:30 must save as Day off.');
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-13')->first()->status === 'half_day', 'Exactly 4:30 must not save as Day off.');
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-14')->first()->status === 'present', 'Open shift must not be prematurely marked Day off.');
$records = Attendance::where('user_id', 2)->whereDate('date', '>=', '2026-10-12')->whereDate('date', '<=', '2026-10-14')->get();
$calendar = (new AttendanceCalendar)->build(Carbon::parse('2026-10-01'), $records, collect(), collect(), null, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
$days = collect($calendar['days'])->keyBy('key');
checkSave($calendar['totals']['weekend'] === 4 && $calendar['totals']['day_off'] === 1, 'Weekend and short shift totals were combined.');
checkSave($days['2026-10-11']['status'] === 'weekend' && $days['2026-10-12']['status'] === 'day_off', 'Calendar badges do not distinguish Sunday and Day off.');
checkSave($calendar['totals']['scheduled_days'] === 13, 'Short shifts must remain in the attendance denominator; Sundays must be excluded.');
checkSave($calendar['totals']['seconds'] === $records->sum('total_seconds'), 'Recorded working hours were changed.');
Carbon::setTestNow();
echo "PASS: Sunday Weekend, short completed office shifts, exact 4:30 boundary, open shifts, separate totals and attendance denominator.\n";
