<?php

require __DIR__.'/employee_daily_clock.php';

use App\Models\Attendance;
use Carbon\Carbon;

$jsonRequest = function (array $data) use ($request) {
    $r = $request($data);
    $r->headers->set('Accept', 'application/json');
    return $r;
};
Carbon::setTestNow(Carbon::parse('2026-10-16 09:15:00', 'Asia/Kolkata'));
$inRequest = fn () => $jsonRequest(['clock_in_selfie' => $image, 'clock_in_timezone' => 'Asia/Kolkata']);
$response = $dashboard->clockIn($inRequest());
checkSave($response->getStatusCode() === 200 && $response->getData()->success === true, 'JSON clock-in was not confirmed.');
checkSave($response->getData()->redirect_url === route('dashboard', [], false), 'Confirmed clock-in did not return dashboard location.');
$attendance = Attendance::where('user_id', 2)->whereDate('date', '2026-10-16')->firstOrFail();
$inPhoto = $attendance->getRawOriginal('clock_in_photo');
checkSave($attendance->clock_in === '09:15:00' && !$attendance->clock_out && is_file(public_path($inPhoto)), 'Confirmed clock-in did not save start time/photo.');
$duplicate = $dashboard->clockIn($inRequest());
checkSave($duplicate->getStatusCode() === 422 && !$duplicate->getData()->success, 'Repeated JSON clock-in was reported as success.');
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-16')->count() === 1, 'JSON retry duplicated attendance.');
Carbon::setTestNow(Carbon::parse('2026-10-16 17:45:00', 'Asia/Kolkata'));
$out = $dashboard->clockOut($jsonRequest(['clock_out_selfie' => $image]));
checkSave($out->getStatusCode() === 200 && $out->getData()->success, 'JSON clock-out was not confirmed.');
$attendance->refresh();
checkSave($attendance->clock_out === '17:45:00' && $attendance->total_duration === '08:30:00', 'Confirmed clock-out did not preserve exact work duration.');
checkSave($dashboard->clockOut($jsonRequest([]))->getStatusCode() === 422, 'Repeated JSON clock-out did not report policy error.');
foreach ([$inPhoto, $attendance->getRawOriginal('clock_out_photo')] as $path) if ($path && is_file(public_path($path))) unlink(public_path($path));
Carbon::setTestNow();
echo "PASS: JSON save confirmation, stored start/photo, dashboard redirect, exact worked time, duplicate clock-in/out rejection and legacy HTML compatibility.\n";
