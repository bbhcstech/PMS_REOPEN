<?php

// Run with: php tests/regression/attendance_save.php. Uses only an in-memory database.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\AttendanceController;
use App\Models\{Attendance, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Schema};
use Illuminate\Validation\ValidationException;

config(['database.default' => 'tenant', 'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'session.driver' => 'array']);
DB::purge('tenant');
Schema::create('users', function (Blueprint $t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->string('name'); $t->string('role'); $t->boolean('is_active');
});
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin', 'is_active' => true],
    ['id' => 2, 'company_id' => 1, 'name' => 'Employee', 'role' => 'employee', 'is_active' => true],
]);
foreach (['2025_07_02_052755_create_attendances_table.php', '2025_07_02_115048_create_attendance_settings_table.php', '2026_06_17_040000_add_policy_thresholds_to_attendance_settings_table.php', '2026_10_07_000003_add_location_and_missing_columns_to_attendances_table.php'] as $file) {
    (require base_path('database/migrations/tenant/' . $file))->up();
}
Schema::table('attendances', function (Blueprint $t) { $t->text('clock_in_address')->nullable(); $t->string('work_from_type')->nullable(); });
Schema::create('app_settings', function (Blueprint $t) { $t->id(); $t->string('key'); $t->text('value'); });
Auth::guard('web')->setUser(User::findOrFail(1));
Notification::fake();
$controller = new AttendanceController;
function saveAttendance(array $values) {
    global $controller;
    $request = Request::create('/attendance', 'POST', $values + ['user_id' => [2], 'clock_in' => '10:30', 'clock_out' => '19:30', 'status' => 'present', 'work_from_type' => 'office']);
    $request->headers->set('Accept', 'application/json');
    return $controller->store($request);
}
function checkSave(bool $condition, string $message) { if (! $condition) throw new RuntimeException($message); }
foreach ([['mark_attendance_by' => 'month', 'year' => 2026], ['mark_attendance_by' => 'date', 'date_range' => 'invalid'], ['mark_attendance_by' => 'date', 'date_range' => '02/30/2026 - 03/01/2026'], ['mark_attendance_by' => 'date', 'date_range' => '03/02/2026 - 03/01/2026'], []] as $invalid) {
    try { saveAttendance($invalid); throw new RuntimeException('Invalid dates were accepted.'); }
    catch (ValidationException $e) {}
}
checkSave(Attendance::count() === 0, 'Validation failure wrote attendance.');
saveAttendance(['mark_attendance_by' => 'month', 'year' => 2024, 'month' => 2]);
checkSave(Attendance::count() === 29, 'Leap month did not save all dates.');
checkSave(Attendance::whereDate('date', '2024-02-29')->first()->clock_in === '10:30:00', 'Times were not saved.');
saveAttendance(['mark_attendance_by' => 'month', 'year' => 2024, 'month' => 2, 'clock_in' => '11:00']);
checkSave(Attendance::first()->clock_in === '10:30:00', 'Existing attendance was overwritten without permission.');
saveAttendance(['mark_attendance_by' => 'month', 'year' => 2024, 'month' => 2, 'clock_in' => '11:00', 'overwrite_attendance' => 'yes']);
checkSave(Attendance::count() === 29 && Attendance::first()->clock_in === '11:00:00', 'Overwrite failed or duplicated rows.');
saveAttendance(['mark_attendance_by' => 'date', 'date_range' => '03/01/2024 - 03/02/2024']);
checkSave(Attendance::count() === 31, 'Date range did not save both dates.');
$response = saveAttendance(['date' => '2024-03-03']);
checkSave($response->getStatusCode() === 200 && Attendance::count() === 32, 'Single date save failed.');
echo "Attendance save, validation, leap month, date range, notification and overwrite checks passed.\n";

foreach (['10/15/2026-10/16/2026', ' 10/15/2026  -  10/16/2026 ', "10/15/2026\t-\t10/16/2026", '10/15/2026 - 10/15/2026'] as $range) {
    saveAttendance(['mark_attendance_by' => 'date', 'date_range' => $range]);
    checkSave(Attendance::where('user_id', 2)->whereDate('date', '>=', '2026-10-15')->whereDate('date', '<=', '2026-10-16')->count() === 2, 'Valid typed range failed or duplicated dates.');
}
$beforeInvalid = Attendance::count();
foreach (['02/29/2025-03/01/2025', '10/16/2026-10/15/2026', '10/15/2026-', '10/15/2026-10/16/2026-extra', '2026-10-15-2026-10-16'] as $range) {
    try { saveAttendance(['mark_attendance_by' => 'date', 'date_range' => $range]); throw new RuntimeException('Malformed or impossible range accepted.'); }
    catch (ValidationException $e) {}
}
checkSave(Attendance::count() === $beforeInvalid, 'Rejected ranges wrote attendance.');
DB::table('users')->insert([
    ['id' => 3, 'company_id' => 1, 'name' => 'HR', 'role' => 'hr', 'is_active' => true],
    ['id' => 4, 'company_id' => 2, 'name' => 'Other company employee', 'role' => 'employee', 'is_active' => true],
]);
saveAttendance(['user_id' => 3, 'mark_attendance_by' => 'date', 'date_range' => '10/15/2026-10/16/2026']);
checkSave(Attendance::where('user_id', 3)->count() === 2, 'Company admin could not manage HR attendance.');
$beforeRejected = Attendance::count();
try {
    saveAttendance(['user_id' => [2, 4], 'date' => '2026-10-17']);
    throw new RuntimeException('Mixed-company bulk request accepted.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkSave($e->getStatusCode() === 403, 'Wrong company boundary rejection.');
}
checkSave(Attendance::count() === $beforeRejected, 'Mixed-company request partially wrote attendance.');
Auth::guard('web')->setUser(User::findOrFail(3));
foreach ([3, [3], [2, 3]] as $ids) {
    try {
        saveAttendance(['user_id' => $ids, 'date' => '2026-10-17', 'overwrite_attendance' => 'yes']);
        throw new RuntimeException('HR own manual attendance accepted.');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        checkSave($e->getStatusCode() === 403, 'Wrong HR self-write rejection.');
    }
}
checkSave(Attendance::count() === $beforeRejected, 'Rejected HR request partially wrote attendance.');
echo "PASS: compact/spaced date ranges, impossible/reversed dates, admin HR access, HR single/bulk self-write denial and company isolation.\n";
