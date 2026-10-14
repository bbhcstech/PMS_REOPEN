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
