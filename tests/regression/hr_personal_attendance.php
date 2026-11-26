<?php

require __DIR__.'/attendance_calendar.php';

use App\Http\Controllers\WorkforceFileController;
use App\Http\Middleware\ProtectWorkforceRecords;
use App\Models\{Attendance, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema, Storage};

Carbon::setTestNow(Carbon::parse('2026-10-08 12:00:00'));
DB::table('users')->insert(['id' => 40, 'company_id' => 1, 'name' => 'HR', 'email' => 'hr@local.test', 'role' => 'hr']);
DB::table('attendances')->insert([
    ['user_id' => 40, 'date' => '2026-09-30', 'status' => 'present', 'clock_in' => '08:00:00', 'clock_out' => '09:00:00', 'archived_at' => null],
    ['user_id' => 40, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '12:15:00', 'archived_at' => null],
    ['user_id' => 40, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '13:00:00', 'clock_out' => '17:45:00', 'archived_at' => null],
    ['user_id' => 40, 'date' => '2026-10-03', 'status' => 'present', 'clock_in' => '23:00:00', 'clock_out' => '01:00:00', 'archived_at' => null],
    ['user_id' => 40, 'date' => '2026-10-06', 'status' => 'present', 'clock_in' => '07:00:00', 'clock_out' => '08:30:00', 'archived_at' => null],
    ['user_id' => 40, 'date' => '2026-10-02', 'status' => 'present', 'clock_in' => '03:00:00', 'clock_out' => '13:00:00', 'archived_at' => '2026-10-03 00:00:00'],
]);
$hr = User::findOrFail(40);
Auth::guard('web')->setUser($hr);
$personalRequest = fn ($extra = []) => Request::create('/attendance', 'GET', $extra + ['date' => '2026-10-01']);
foreach (['day' => [1, '08:00'], 'week' => [7, '11:00'], 'month' => [31, '11:30']] as $period => [$count, $hours]) {
    $params = ['period' => $period, 'view' => 'team'];
    $view = $controller->index($personalRequest($params));
    $data = $view->getData();
    checkCalendar($view->name() === 'admin.attendance.personal' && $data['employee']->id === 40, 'An HR attendance entry point exposed the team view.');
    checkCalendar($data['rows']->count() === $count && $data['hours']($data['totalSeconds']) === $hours, "Incorrect $period attendance totals.");
    if ($period === 'week') {
        checkCalendar($data['start']->toDateString() === '2026-09-28' && $data['end']->toDateString() === '2026-10-04', 'Weekly attendance lost dates across month boundaries.');
    }
    $csv = $controller->index($personalRequest($params + ['format' => 'csv']));
    ob_start();
    $csv->sendContent();
    $csvContents = ob_get_clean();
    checkCalendar(str_contains($csv->headers->get('Content-Disposition'), "my-attendance-$period-"), 'The CSV has the wrong filename.');
    checkCalendar(substr_count(trim($csvContents), "\n") === $count + 1 && str_contains($csvContents, "\"Total hours\",,,,,$hours"), "The $period CSV differs from the report.");
    checkCalendar(str_contains($csvContents, '09:00 | 13:00') && ! str_contains($csvContents, '00:00,23:00') && ! str_contains($csvContents, '03:00,13:00'), 'CSV lost split shifts or included foreign/archived shifts.');
}
$html = Blade::render(
    preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/attendance/personal.blade.php'))),
    $data
);
checkCalendar(str_contains($html, 'Read only') && str_contains($html, 'Export CSV') && ! str_contains($html, 'name="user_id"') && ! str_contains($html, 'attendance/edit'), 'The personal report is missing export or renders employee/override controls.');

foreach (['index', 'employeeIndex', 'attendanceReport'] as $action) {
    checkCalendar($controller->$action($personalRequest())->getData()['employee']->id === 40, "$action lost the personal report.");
    foreach ([3, 2] as $otherUser) {
        try {
            $controller->$action($personalRequest(['user_id' => $otherUser, 'format' => 'csv']));
            throw new RuntimeException("$action accepted someone else's attendance export.");
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            checkCalendar($e->getStatusCode() === 403, 'Wrong personal view rejection');
        }
    }
}

$record = Attendance::where('user_id', 40)->whereDate('date', '2026-10-01')->firstOrFail();
Schema::connection('central')->create('companies', function ($table) {
    $table->id();
    $table->string('db_name');
    $table->softDeletes();
});
DB::connection('central')->table('companies')->insert(['id' => 1, 'db_name' => ':memory:']);
$guarded = function ($path, $method = 'GET', $params = []) use ($hr) {
    $request = Request::create($path, $method, $params);
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => $hr);

    return (new ProtectWorkforceRecords)->handle($request, fn () => response('Allowed'));
};
checkCalendar($guarded('/attendance/report')->getStatusCode() === 200, 'HR report is blocked by middleware.');
foreach ([
    ['/attendance/'.$record->id, 'PUT'],
    ['/attendance/store', 'POST'],
    ['/attendance/mark', 'POST'],
    ['/attendance/month/archive', 'POST'],
    ['/attendance/'.$record->id.'/restore', 'POST'],
    ['/attendance/export/excel', 'GET'],
    ['/attendance/details', 'GET'],
] as [$path, $method]) {
    try {
        $guarded($path, $method, ['user_id' => $path === '/attendance/details' ? 3 : 40]);
        throw new RuntimeException("HR reached $method $path.");
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        checkCalendar($e->getStatusCode() === 403, "Wrong denial for $path.");
    }
}
$foreignRecord = Attendance::where('user_id', 3)->firstOrFail();
$details = $controller->showAttendanceDetails($personalRequest(['user_id' => 40, 'attendance_id' => $foreignRecord->id]));
checkCalendar($details->getStatusCode() === 403, 'A different record ID bypassed HR attendance detail ownership.');
$photoPath = 'authority-attendance/1/hr-report-test.jpg';
Storage::fake('local')->put($photoPath, 'fixture');
$record->clock_in_photo = $photoPath;
$record->syncOriginal();
$files = new WorkforceFileController;
checkCalendar($files->photo($record)->headers->get('Cache-Control') === 'no-store, private', 'HR cannot read their own private attendance photo.');
try {
    $files->photo($foreignRecord);
    throw new RuntimeException('HR reached another employee’s private attendance photo.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Wrong denial for another attendance photo.');
}
Storage::disk('local')->delete($photoPath);
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
DB::table('users')->insert(['id' => 41, 'company_id' => 1, 'name' => 'Manager', 'email' => 'manager@local.test', 'role' => 'manager']);
DB::table('attendances')->insert(['user_id' => 41, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '17:00:00']);
Auth::guard('web')->setUser(User::findOrFail(41));
$managerData = $controller->index(Request::create('/attendance', 'GET', ['view' => 'personal', 'month' => 10, 'year' => 2026]))->getData();
checkCalendar($managerData['employees']->pluck('id')->all() === [41] && !$managerData['canManage'], 'Manager personal attendance is not isolated/read-only');
checkCalendar($managerData['calendar']['totals']['seconds'] === 28800, 'Manager personal working hours missing');
try {
    $controller->index(Request::create('/attendance', 'GET', ['user_id' => 40]));
    throw new RuntimeException('Manager viewed HR attendance');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Wrong manager attendance isolation rejection');
}
Carbon::setTestNow();
echo "PASS: HR day/week/month attendance and CSV, isolated report and details, read-only markup, self-override protection and admin access.\n";
