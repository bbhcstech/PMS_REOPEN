<?php

require __DIR__.'/attendance_calendar.php';

use App\Http\Controllers\WorkforceFileController;
use App\Http\Middleware\EnsureModuleAccess;
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
$foreignRecord = Attendance::where('user_id', 3)->firstOrFail();
$hr = User::findOrFail(40);
Auth::guard('web')->setUser($hr);
checkCalendar(Attendance::query()->distinct()->pluck('user_id')->all() === [40], 'An unfiltered HR attendance query returned another employee.');
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
checkCalendar(str_contains($html, 'Read only') && str_contains($html, 'Export CSV') && str_contains($html, '/hr/attendance') && ! str_contains($html, 'name="user_id"') && ! str_contains($html, 'attendance/edit'), 'The HR report links are incorrect or employee/override controls are exposed.');

foreach (['hrAttendance', 'index', 'employeeIndex', 'attendanceReport', 'authorityIndex'] as $action) {
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
Schema::create('modules', function ($table) {
    $table->id();
    $table->string('slug');
    $table->string('route_name')->nullable();
    $table->string('route_prefix')->nullable();
});
DB::table('modules')->insert(['slug' => 'attendance', 'route_name' => 'attendance.index']);
$guarded = function ($path, $method = 'GET', $params = [], $runAction = false) {
    $request = Request::create($path, $method, $params);
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => Auth::user());
    app()->instance('request', $request);

    return (new ProtectWorkforceRecords)->handle($request, fn () => $runAction ? $route->run() : response('Allowed'));
};
checkCalendar(app(EnsureModuleAccess::class)->moduleForRoute('attendance.hr')->slug === 'attendance', 'The HR report did not inherit Attendance permissions.');
foreach (['/hr/attendance', '/attendance', '/attendance/report', '/my-attendance', '/admin/authority-attendance'] as $path) {
    $params = ['view' => 'team', 'staff_category' => 'authority', 'period' => 'week', 'date' => '2026-10-01'];
    $view = $guarded($path, 'GET', $params, true);
    $data = $view->getData();
    checkCalendar($view->name() === 'admin.attendance.personal' && $data['employee']->id === 40 && $data['totalSeconds'] === 39600, "$path did not open the personal HR report.");
    try {
        $guarded($path, 'GET', $params + ['user_id' => 3, 'format' => 'csv'], true);
        throw new RuntimeException("$path exposed employee CSV data.");
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        checkCalendar($e->getStatusCode() === 403, 'Wrong rejection for an employee CSV request.');
    }
}
$csv = $guarded('/hr/attendance', 'GET', ['period' => 'day', 'date' => '2026-10-01', 'format' => 'csv'], true);
checkCalendar(str_contains($csv->headers->get('Content-Disposition'), 'my-attendance-day-2026-10-01.csv'), 'The HR route does not export a personal CSV.');
try {
    $guarded('/hr/attendance', 'GET', ['company_id' => 2], true);
    throw new RuntimeException('HR report accepted a different company.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Wrong rejection for a foreign company.');
}
foreach ([
    ['/attendance/'.$record->id, 'PUT'],
    ['/attendance/store', 'POST'],
    ['/attendance/mark', 'POST'],
    ['/attendance/month/archive', 'POST'],
    ['/attendance/'.$record->id.'/restore', 'POST'],
    ['/attendance/export/excel', 'GET'],
    ['/attendance/filter', 'GET'],
    ['/attendance/by-member', 'GET'],
    ['/attendance/details', 'GET'],
] as [$path, $method]) {
    try {
        $guarded($path, $method, ['user_id' => $path === '/attendance/details' ? 3 : 40]);
        throw new RuntimeException("HR reached $method $path.");
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        checkCalendar($e->getStatusCode() === 403, "Wrong denial for $path.");
    }
}
$details = $controller->showAttendanceDetails($personalRequest(['user_id' => 40, 'attendance_id' => $foreignRecord->id]));
checkCalendar($details->getStatusCode() === 404, 'A different record ID bypassed HR attendance detail ownership.');
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
checkCalendar($admin['employees']->pluck('id')->sort()->values()->all() === [33, 40], 'HR Attendance must fetch only HR accounts and exclude all other employees.');
DB::table('users')->insert(['id' => 41, 'company_id' => 1, 'name' => 'Manager', 'email' => 'manager@local.test', 'role' => 'manager']);
DB::table('attendances')->insert(['user_id' => 41, 'date' => '2026-10-01', 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '17:00:00']);
Auth::guard('web')->setUser(User::findOrFail(1));
$hrTeam = $controller->authorityIndex(Request::create('/admin/authority-attendance', 'GET', ['view' => 'team', 'month' => 10, 'year' => 2026]))->getData();
checkCalendar($hrTeam['teamRows']->pluck('employee.id')->sort()->values()->all() === [33, 40], 'HR Attendance team view leaked non-HR employees.');
DB::table('users')->insert(['id' => 42, 'company_id' => 1, 'name' => 'HR Two', 'email' => 'hr2@local.test', 'role' => 'hr']);
Auth::guard('web')->setUser(User::findOrFail(40));
try {
    $controller->store(Request::create('/attendance/store', 'POST', ['user_id' => [42], 'overwrite_attendance' => 'yes']));
    throw new RuntimeException('HR could override another HR attendance.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'HR override of another HR returned wrong status.');
}
Auth::guard('web')->setUser(User::findOrFail(41));
try {
    $guarded('/hr/attendance', 'GET', [], true);
    throw new RuntimeException('The HR report route accepted a non-HR viewer.');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    checkCalendar($e->getStatusCode() === 403, 'Wrong rejection for a non-HR viewer.');
}
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
echo "PASS: HR model and report route isolation, day/week/month totals, personal CSV, detail and photo ownership, self-override protection, and admin access.\n";
