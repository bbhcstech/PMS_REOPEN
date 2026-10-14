<?php

require __DIR__.'/attendance_calendar.php';

use App\Models\{Attendance, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

DB::table('users')->insert(['id' => 40, 'company_id' => 1, 'name' => 'HR', 'email' => 'hr@local.test', 'role' => 'hr']);
DB::table('attendances')->insert(['user_id' => 40, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '17:00:00']);
$hr = User::findOrFail(40);
Auth::guard('web')->setUser($hr);
$request = Request::create('/attendance', 'GET', ['view' => 'personal', 'month' => 10, 'year' => 2026]);
$data = $controller->index($request)->getData();
checkCalendar($data['employees']->pluck('id')->all() === [40] && $data['selectedEmployee']->id === 40, 'HR personal view selected another employee');
checkCalendar(!$data['canManage'] && $data['calendar']['totals']['seconds'] === 28800, 'HR personal calendar is editable or lacks own recorded hours');
try {
    $controller->index(Request::create('/attendance', 'GET', ['view' => 'personal', 'user_id' => 3]));
    throw new RuntimeException('Personal view accepted another user');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Wrong personal view rejection');
}
$record = Attendance::where('user_id', 40)->firstOrFail();
foreach ([40, [40], [3, 40]] as $ids) {
    try {
        $controller->store(Request::create('/attendance/store', 'POST', ['user_id' => $ids, 'overwrite_attendance' => 'yes']));
        throw new RuntimeException('HR manual self attendance accepted');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        checkCalendar($e->getStatusCode() === 403, 'Manual self write wrong rejection');
    }
}
try {
    $controller->update(Request::create('/attendance/'.$record->id, 'PUT', ['clock_in' => '01:00', 'status' => 'present']), $record);
    throw new RuntimeException('HR self edit accepted');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Self edit wrong rejection');
}
checkCalendar($record->fresh()->clock_in === '09:00:00', 'Rejected HR edit changed saved time');
Auth::guard('web')->setUser(User::findOrFail(1));
$admin = $controller->authorityIndex(Request::create('/admin/authority-attendance', 'GET', ['user_id' => 40, 'month' => 10, 'year' => 2026]))->getData();
checkCalendar($admin['selectedEmployee']->id === 40 && $admin['canManage'], 'Admin lost access to HR attendance');
echo "PASS: HR own monthly hours, read-only personal view, forged employee rejection, single/bulk self-overwrite denial, unchanged saved times and admin access.\n";
