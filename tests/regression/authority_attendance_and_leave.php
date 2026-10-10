<?php

// Uses the isolated two-company fixtures and secure-login checks, never live data.
require __DIR__ . '/upper_level_employees.php';

use App\Http\Controllers\{AttendanceController, DashboardController, LeaveController};
use App\Http\Middleware\ProtectWorkforceRecords;
use App\Models\{Attendance, Leave, LeaveType, User};
use App\Services\{LeaveService, WorkforceAccess, WorkforceRecordSchema};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Support\Facades\{Blade, Storage};

tenant('alpha-test');
Auth::guard('web')->setUser(User::findOrFail(1));
config(['app.timezone' => 'Asia/Kolkata']);
Carbon::setTestNow(Carbon::parse('2026-10-12 09:00:00', 'Asia/Kolkata'));
foreach ([
    '2025_07_02_052755_create_attendances_table.php',
    '2025_07_02_115048_create_attendance_settings_table.php',
    '2025_07_02_144219_create_leaves_table.php',
    '2025_07_02_155853_create_holidays_table.php',
    '2026_02_09_130159_add_leave_fields_to_users_table.php',
    '2026_02_09_130245_create_leave_policies_table.php',
    '2026_02_09_130311_create_leave_balances_table.php',
    '2026_06_16_000004_add_archived_at_to_attendances_table.php',
    '2026_06_16_000006_add_clock_in_photo_to_attendances_table.php',
    '2026_06_17_010000_upgrade_leave_management_system.php',
    '2026_06_17_020000_add_archived_at_to_leaves_table.php',
    '2026_06_17_030000_add_paid_unpaid_days_to_leaves_table.php',
    '2026_06_17_040000_add_policy_thresholds_to_attendance_settings_table.php',
    '2026_10_07_000003_add_location_and_missing_columns_to_attendances_table.php',
] as $file) (require base_path('database/migrations/tenant/' . $file))->up();
require base_path('database/migrations/tenant/2026_02_06_153453_add_location_columns_to_attendances_table.php');
(new AddLocationColumnsToAttendancesTable)->up();
Schema::table('attendances', fn ($t) => $t->unsignedBigInteger('company_id')->nullable());
Schema::table('leaves', function ($t) { $t->unsignedBigInteger('company_id')->nullable(); $t->string('duration')->nullable(); $t->date('date')->nullable(); });
Schema::create('app_settings', function ($t) { $t->id(); $t->string('key'); $t->text('value'); $t->timestamps(); });
WorkforceRecordSchema::ensure();
WorkforceRecordSchema::ensure();

$admin = User::findOrFail(1);
$staff = User::where('email', 'lead@test.com')->firstOrFail();
$employee = User::create(['company_id' => 1, 'name' => 'Normal employee', 'email' => 'normal@test.com', 'password' => 'unused', 'role' => 'employee', 'is_active' => true, 'login_allowed' => true]);
$dashboard = new DashboardController;
Auth::guard('web')->setUser($staff);
$clockRequest = req(['clock_in_timezone' => 'Asia/Kolkata', 'user_id' => $employee->id, 'company_id' => 99, 'clock_in' => '01:00:00']);
try {
    $dashboard->clockIn($clockRequest);
    throw new RuntimeException('Clock-in without a photo was accepted.');
} catch (\Illuminate\Validation\ValidationException $e) {
    staffCheck(isset($e->errors()['clock_in_selfie']), 'Missing photo validation error.');
}
staffCheck(Attendance::count() === 0, 'Rejected clock-in wrote an attendance record.');
\Illuminate\Support\Facades\Storage::fake('local');
$clockRequest->merge(['clock_in_selfie' => 'data:image/png;base64,'.base64_encode('invalid image')]);
try {
    $dashboard->clockIn($clockRequest);
    throw new RuntimeException('Clock-in with an invalid image was accepted.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    staffCheck($e->getStatusCode() === 422, 'Invalid photo returned the wrong status.');
}
staffCheck(Attendance::count() === 0, 'Invalid image wrote an attendance record.');
$clockRequest->merge(['clock_in_selfie' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII=']);
$dashboard->clockIn($clockRequest);
$record = Attendance::firstOrFail();
staffCheck($record->user_id === $staff->id && $record->company_id === 1 && $record->clock_in === '09:00:00', 'Clock-in trusted forged user, company or time.');
staffCheck($record->staff_category === 'authority' && $record->staff_role_name === 'Project Operations Lead' && $record->staff_designation_level === 2, 'Role and hierarchy snapshots missing.');
$dashboard->clockIn($clockRequest);
staffCheck(Attendance::count() === 1, 'Duplicate clock-in created a second row.');
denied(fn () => (new AttendanceController)->update(req(['status' => 'present', 'clock_in' => '01:00']), $record));
denied(fn () => (new AttendanceController)->authorityIndex(req([])));
Carbon::setTestNow(Carbon::parse('2026-10-12 18:00:00', 'Asia/Kolkata'));
$dashboard->clockOut(req(['clock_out_timezone' => 'Asia/Kolkata', 'clock_out' => '10:00']));
staffCheck($record->fresh()->clock_out === '18:00:00', 'Clock-out accepted a forged time.');
$dashboard->clockOut(req(['clock_out_timezone' => 'Asia/Kolkata']));
staffCheck($record->fresh()->clock_out === '18:00:00', 'Repeated clock-out overwrote attendance.');

$service = new LeaveService;
$service->ensureDefaultTypes();
$service->policy()->update(['auto_approve_casual_leave' => true, 'hr_approval_required' => false]);
$type = LeaveType::where('code', 'CL')->firstOrFail();
$leave = $service->createLeave($staff, $type, ['start_date' => '2026-11-02', 'end_date' => '2026-11-02', 'reason' => 'Family appointment', 'status' => 'approved'], $staff);
staffCheck($leave->status === 'pending' && $leave->staff_category === 'authority' && $leave->staff_designation_level === 2, 'Higher-level leave bypassed manual admin approval.');
denied(fn () => $service->approve($leave, $staff));
denied(fn () => $service->reject($leave, $staff, 'Self reject'));
denied(fn () => (new LeaveController($service))->updateStatus(req(['status' => 'approved']), $leave));
denied(fn () => (new LeaveController($service))->authorityIndex(req([])));

Auth::guard('web')->setUser($admin);
$service->approve($leave, $admin, 'Approved by company admin');
staffCheck($leave->fresh()->status === 'approved' && $leave->fresh()->approved_by === $admin->id, 'Company admin approval failed.');
$normal = $service->createLeave($employee, $type, ['start_date' => '2026-11-03', 'end_date' => '2026-11-03', 'reason' => 'Personal day', 'status' => 'pending'], $employee);
staffCheck($normal->status === 'approved' && $normal->staff_category === 'employee', 'Normal employee auto-approval changed.');
$authorityRequest = req([]);
$data = (new LeaveController($service))->authorityIndex($authorityRequest)->getData();
staffCheck($data['leaves']->pluck('user_id')->unique()->all() === [$staff->id], 'Authority leaves mixed with normal employees.');
$normalRequest = req([]);
$data = (new LeaveController($service))->index($normalRequest)->getData();
staffCheck($data['leaves']->pluck('user_id')->unique()->all() === [$employee->id], 'Employee leaves mixed with authorities.');

$attendanceController = new AttendanceController;
$r = Request::create('/attendance', 'GET', ['view' => 'team']); app()->instance('request', $r);
$higher = $attendanceController->authorityIndex($r)->getData();
staffCheck($higher['teamRows']->pluck('employee.id')->all() === [$staff->id], 'Higher-level attendance leaked normal or foreign staff.');
$r = Request::create('/attendance', 'GET', ['view' => 'team']); app()->instance('request', $r);
$regular = $attendanceController->index($r)->getData();
staffCheck($regular['teamRows']->pluck('employee.id')->all() === [$employee->id], 'Normal attendance mixed with higher-level staff.');

// Role changes must not relabel past records or move them into employee history.
DB::table('users')->where('id', $staff->id)->update(['role' => 'employee', 'company_staff_role_id' => null]);
$r = Request::create('/attendance', 'GET', ['view' => 'team', 'month' => 10, 'year' => 2026]); app()->instance('request', $r);
$history = $attendanceController->authorityIndex($r)->getData();
$old = collect($history['teamRows']->items())->first(fn ($row) => $row['employee']->id === $staff->id);
staffCheck($old && $old['calendar']['totals']['seconds'] === 32400, 'Past authority attendance moved when a role changed.');
$r = Request::create('/attendance', 'GET', ['view' => 'team', 'month' => 10, 'year' => 2026]); app()->instance('request', $r);
$history = $attendanceController->index($r)->getData();
staffCheck($history['summary']['seconds'] === 0, 'Authority history appeared in normal employee attendance.');
DB::table('users')->where('id', $staff->id)->update(['role' => 'manager', 'company_staff_role_id' => 1]);

Auth::guard('web')->setUser($staff);
$clockTemplate = file_get_contents(resource_path('views/admin/partials/staff-clock.blade.php'));
$html = Blade::render($clockTemplate, ['attendance' => null]);
staffCheck(str_contains($html, 'id="employeeClockInForm"') && ! str_contains($html, 'attendance.update'), 'Staff dashboard lacks clock-in or exposes attendance editing.');
$open = clone $record; $open->clock_out = null;
$html = Blade::render($clockTemplate, ['attendance' => $open]);
staffCheck(str_contains($html, 'id="employeeClockOutForm"') && ! str_contains($html, 'id="employeeClockInForm"'), 'Active shift shows incorrect controls.');
$dashboardData = (new App\Services\CompanyRoleDashboard)->data($staff);
staffCheck($dashboardData['attendance']->user_id === $staff->id && $dashboardData['role']->name === 'Project Operations Lead', 'Custom-role dashboard lost personal attendance.');

Storage::fake('local');
$photoPath = 'authority-attendance/1/test-photo.jpg';
Storage::disk('local')->put($photoPath, 'test private photo');
$record->update(['clock_in_photo' => $photoPath]);
$files = new App\Http\Controllers\WorkforceFileController;
$download = $files->photo($record->fresh());
staffCheck($download->headers->get('Cache-Control') === 'no-store, private' && str_contains($record->fresh()->clock_in_photo, '/photo'), 'Private attendance photo is not protected from shared caches.');
Auth::guard('web')->setUser($employee);
denied(fn () => $files->photo($record));
Auth::guard('web')->setUser($admin);
$files->photo($record);
Storage::disk('local')->delete($photoPath);
$uploadRequest = req([]);
$uploadRequest->files->set('attachment', \Illuminate\Http\UploadedFile::fake()->create('leave.pdf', 1, 'application/pdf'));
$upload = new ReflectionMethod(LeaveController::class, 'storePrivateLeaveAttachment');
$attachmentPath = $upload->invoke(new LeaveController($service), $uploadRequest, $staff);
$leave->update(['attachment' => $attachmentPath]);
staffCheck(str_starts_with($leave->getRawOriginal('attachment'), 'authority-leave/1/'), 'Authority leave attachment was stored publicly.');
$files->attachment($leave);
Auth::guard('web')->setUser($employee);
denied(fn () => $files->attachment($leave));
Auth::guard('web')->setUser($staff);
$files->attachment($leave);
Storage::disk('local')->delete($attachmentPath);

// Corrupt ownership labels must never expose a row through a local user's ID.
DB::table('attendances')->insert(['user_id' => $staff->id, 'company_id' => 99, 'date' => '2026-10-13', 'clock_in' => '01:00:00', 'status' => 'present']);
staffCheck(Attendance::where('company_id', 99)->count() === 0, 'Cross-company attendance was visible.');
Auth::guard('web')->setUser($employee);
staffCheck(! Leave::whereKey($leave->id)->exists() && ! Attendance::where('user_id', $staff->id)->exists(), 'Employee could read higher-level records.');
Auth::guard('web')->setUser($staff);
staffCheck(! Leave::whereKey($normal->id)->exists(), 'Authority could read normal employee leave.');
session(['current_company_id' => 2]);
denied(fn () => WorkforceAccess::authorizeActor());
session()->forget('current_company_id');

foreach (['attendance.update', 'attendance.mark', 'attendance.store', 'attendance.settings.update', 'attendance.month.archive', 'leaves.updateStatus'] as $name) {
    $route = app('router')->getRoutes()->getByName($name);
    staffCheck((bool) $route, 'Missing route: ' . $name);
    $r = Request::create('/test', 'POST'); $r->setRouteResolver(fn () => $route); $r->setUserResolver(fn () => $staff);
    if ($name !== 'leaves.updateStatus') denied(fn () => (new ProtectWorkforceRecords)->handle($r, fn () => 'UNAUTHORIZED'));
}
foreach (['admin/partials/staff-clock', 'admin/partials/staff-clock-camera', 'admin/company-role-dashboard', 'admin/attendance/calendar', 'admin/attendance/team', 'admin/attendance/by-hour', 'admin/attendance/archive', 'admin/attendance/create', 'admin/leaves/index', 'admin/leaves/create', 'admin/leaves/calendar', 'admin/leaves/archive', 'admin/layout/manu'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/' . $view . '.blade.php'))), TOKEN_PARSE);
}
tenant('beta-test');
Auth::guard('web')->setUser(User::findOrFail(1));
Schema::create('attendances', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('user_id'); $t->date('date'); $t->string('clock_in'); });
DB::table('attendances')->insert(['id' => $record->id, 'user_id' => $staff->id, 'company_id' => 2, 'date' => '2026-10-12', 'clock_in' => '10:00:00']);
staffCheck(Attendance::findOrFail($record->id)->clock_in === '10:00:00', 'Colliding record IDs read another tenant database.');
denied(fn () => $service->approve($leave, User::findOrFail(1)));
tenant('alpha-test');
Auth::guard('web')->setUser(User::findOrFail(1));
staffCheck(Attendance::findOrFail($record->id)->clock_in === '09:00:00', 'Switching tenants changed the original attendance.');
Carbon::setTestNow();
echo "PASS: authority clock-in/out, tamper denial, admin-only leave approval, unchanged employee auto-approval, separate views and company isolation.\n";
