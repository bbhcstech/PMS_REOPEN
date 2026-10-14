<?php

require __DIR__.'/attendance_clock_out_photo.php';

use App\Models\{Attendance, User};
use App\Services\WorkforceAccess;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, Blade};

\Illuminate\Support\Facades\Schema::create('employee_details', function ($table) {
    $table->id(); $table->unsignedBigInteger('user_id');
});
\Illuminate\Support\Facades\Schema::table('attendances', function ($table) {
    $table->decimal('clock_in_latitude', 10, 7)->nullable();
    $table->decimal('clock_in_longitude', 10, 7)->nullable();
    $table->string('clock_in_photo')->nullable();
});
Auth::guard('web')->setUser(User::findOrFail(2));
Carbon::setTestNow(Carbon::parse('2026-10-14 09:00:00', 'Asia/Kolkata'));
$clockRequest = fn () => $request(['clock_in_selfie' => $image, 'clock_in_timezone' => 'Asia/Kolkata']);
$dashboard->clockIn($clockRequest());
$line = Attendance::where('user_id', 2)->whereDate('date', '2026-10-14')->firstOrFail();
$photo = $line->getRawOriginal('clock_in_photo');
checkSave($line->clock_in === '09:00:00' && !$line->clock_out, 'Initial daily clock-in missing');
Carbon::setTestNow(Carbon::parse('2026-10-14 10:00:00', 'Asia/Kolkata'));
$dashboard->clockIn($clockRequest());
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-14')->count() === 1 && $line->fresh()->clock_in === '09:00:00', 'Repeated active clock-in changed session');
Carbon::setTestNow(Carbon::parse('2026-10-14 18:00:00', 'Asia/Kolkata'));
$dashboard->clockOut($request(['clock_out_selfie' => $image]));
$outPhoto = $line->fresh()->getRawOriginal('clock_out_photo');
$dashboard->clockIn($clockRequest());
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-14')->count() === 1, 'Completed shift allowed another clock-in');
checkSave($line->fresh()->clock_out === '18:00:00' && $line->fresh()->total_duration === '09:00:00', 'Completed daily duration changed');
$dashboard->clockOut($request([]));
checkSave($line->fresh()->clock_out === '18:00:00', 'Repeated clock-out replaced original time');

$source = file_get_contents(resource_path('views/employee-dashboard.blade.php'));
$begin = strpos($source, '@if ($attendance && $attendance->clock_in && !$attendance->clock_out)');
$end = strpos($source, '@if($attendance && $attendance->clock_in)', $begin);
$fragment = substr($source, $begin, $end - $begin);
$closedHtml = Blade::render($fragment, ['attendance' => $line->fresh()]);
checkSave(str_contains($closedHtml, 'Shift Completed') && !str_contains($closedHtml, 'employeeClockInForm') && !str_contains($closedHtml, 'employeeClockOutForm'), 'Completed dashboard displays a clock action');
$open = clone $line;
$open->clock_out = null;
$openHtml = Blade::render($fragment, ['attendance' => $open]);
checkSave(str_contains($openHtml, 'employeeClockOutForm') && !str_contains($openHtml, 'employeeClockInForm'), 'Active dashboard does not show only Clock Out');
Carbon::setTestNow(Carbon::parse('2026-10-15 09:00:00', 'Asia/Kolkata'));
checkSave(WorkforceAccess::currentAttendance(Auth::user()) === null, 'Yesterday completed shift blocked new day view');
$dashboard->clockIn($clockRequest());
$next = Attendance::where('user_id', 2)->whereDate('date', '2026-10-15')->firstOrFail();
checkSave($next->clock_in === '09:00:00', 'Next business day clock-in was blocked');
foreach ([$photo, $outPhoto, $next->getRawOriginal('clock_in_photo')] as $path) {
    if ($path && is_file(public_path($path))) unlink(public_path($path));
}
Carbon::setTestNow();
echo "PASS: one employee session per day, active/completed dashboard buttons, stored clock-out and duration, repeat protection and next-day clock-in.\n";
