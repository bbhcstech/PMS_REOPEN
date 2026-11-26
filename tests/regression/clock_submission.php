<?php

require __DIR__.'/employee_daily_clock.php';

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB, Schema};

// Exercise the route's real permission/workforce middleware, not just the controller.
foreach (['tenant', 'central'] as $connection) {
    Schema::connection($connection)->create('modules', function ($t) {
        $t->id(); $t->string('slug'); $t->string('name'); $t->string('route_name')->nullable();
        $t->string('route_prefix')->nullable(); $t->boolean('is_active')->default(true); $t->unsignedBigInteger('parent_id')->nullable();
    });
    DB::connection($connection)->table('modules')->insert(['id' => 1, 'slug' => 'dashboard', 'name' => 'Dashboard', 'route_name' => 'dashboard', 'route_prefix' => 'dashboard']);
}
Schema::connection('tenant')->create('role_permissions', function ($t) {
    $t->id(); $t->string('role'); $t->unsignedBigInteger('module_id');
    foreach (['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'] as $action) $t->boolean('can_'.$action)->default(false);
});
DB::connection('tenant')->table('role_permissions')->insert(['role' => 'employee', 'module_id' => 1, 'can_view' => true, 'can_create' => false]);
$performClock = function (string $phase, $r) use ($dashboard) {
    $route = app('router')->getRoutes()->getByName('dashboard.clock'.$phase);
    $r->setRouteResolver(fn () => $route->bind($r));
    $r->setUserResolver(fn () => Auth::user());
    return (new App\Http\Middleware\EnforceRolePermissionToggles)->handle($r,
        fn ($r) => (new App\Http\Middleware\ProtectWorkforceRecords)->handle($r,
            fn ($r) => (new App\Http\Middleware\EnsureModuleAccess)->handle($r,
                fn ($r) => $phase === 'in' ? $dashboard->clockIn($r) : $dashboard->clockOut($r))));
};

$jsonRequest = function (array $data) use ($request) {
    $r = $request($data);
    $r->headers->set('Accept', 'application/json');
    return $r;
};
Carbon::setTestNow(Carbon::parse('2026-10-16 09:15:00', 'Asia/Kolkata'));
$inRequest = fn () => $jsonRequest(['clock_in_selfie' => $image, 'clock_in_timezone' => 'Asia/Kolkata', 'user_id' => 1, 'company_id' => 99]);
$response = $performClock('in', $inRequest());
checkSave($response->getStatusCode() === 200 && $response->getData()->success === true, 'JSON clock-in was not confirmed.');
checkSave($response->getData()->redirect_url === route('dashboard', [], false), 'Confirmed clock-in did not return dashboard location.');
$attendance = Attendance::where('user_id', 2)->whereDate('date', '2026-10-16')->firstOrFail();
$inPhoto = $attendance->getRawOriginal('clock_in_photo');
checkSave($attendance->clock_in === '09:15:00' && !$attendance->clock_out && is_file(public_path($inPhoto)), 'Confirmed clock-in did not save start time/photo.');
checkSave((int) $attendance->company_id === 1 && (int) $attendance->user_id === 2, 'Clock-in trusted forged employee/company inputs.');
$duplicate = $performClock('in', $inRequest());
checkSave($duplicate->getStatusCode() === 422 && !$duplicate->getData()->success, 'Repeated JSON clock-in was reported as success.');
checkSave(Attendance::where('user_id', 2)->whereDate('date', '2026-10-16')->count() === 1, 'JSON retry duplicated attendance.');
Carbon::setTestNow(Carbon::parse('2026-10-16 17:45:00', 'Asia/Kolkata'));
$out = $performClock('out', $jsonRequest(['clock_out_selfie' => $image]));
checkSave($out->getStatusCode() === 200 && $out->getData()->success, 'JSON clock-out was not confirmed.');
$attendance->refresh();
checkSave($attendance->clock_out === '17:45:00' && $attendance->total_duration === '08:30:00', 'Confirmed clock-out did not preserve exact work duration.');
checkSave($performClock('out', $jsonRequest([]))->getStatusCode() === 422, 'Repeated JSON clock-out did not report policy error.');
foreach ([$inPhoto, $attendance->getRawOriginal('clock_out_photo')] as $path) if ($path && is_file(public_path($path))) unlink(public_path($path));
Carbon::setTestNow();
echo "PASS: JSON save confirmation, stored start/photo, dashboard redirect, exact worked time, duplicate clock-in/out rejection and legacy HTML compatibility.\n";
