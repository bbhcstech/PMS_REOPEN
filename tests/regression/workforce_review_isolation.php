<?php

// Isolated SQLite fixtures; no live records are changed.
require __DIR__.'/authority_admin_delivery.php';

use App\Models\{Leave, User};
use App\Services\{LeaveService, WorkforceAccess, SystemNotificationService};
use Illuminate\Support\Facades\{Auth, DB, Notification};
use Symfony\Component\HttpKernel\Exception\HttpException;

function denyReview(callable $action): void {
    try { $action(); } catch (HttpException $e) {
        if ($e->getStatusCode() === 403) return;
        throw $e;
    }
    throw new RuntimeException('Forbidden record access was allowed.');
}

$admin = User::find(1); $hr = User::find(5); $employee = User::find(8);
Auth::guard('web')->setUser($admin);
DB::table('leaves')->insert(['id' => 8, 'user_id' => 8, 'company_id' => 1, 'staff_category' => 'employee', 'status' => 'pending']);
$employeeLeave = Leave::findOrFail(8);
$review = new ReflectionMethod(LeaveService::class, 'authorizeReviewer');
$service = new LeaveService;
foreach ([4, 5, 6, 7] as $id) {
    $owner = User::find($id);
    $leave = Leave::findOrFail($id);
    $assert(!WorkforceAccess::canAccessRecord($owner, $owner, 'authority'), 'Higher staff can access their own historical record');
    $assert(!WorkforceAccess::canAccessRecord($hr, $owner, 'authority'), 'HR can access an authority record');
    $review->invoke($service, $leave, $admin);
    denyReview(fn () => $service->approve($leave, $owner));
    denyReview(fn () => $service->reject($leave, $hr));
    denyReview(fn () => $service->markPending($leave, User::find(6)));
    Notification::fake();
    SystemNotificationService::notifyLeaveReviewers($owner, 'New Leave Request', 'Authority request');
    $assert(Notification::sent($admin, App\Notifications\SystemNotification::class)->isNotEmpty(), 'Authority request did not reach Admin');
    $assert(Notification::sent($hr, App\Notifications\SystemNotification::class)->isEmpty(), 'Authority request leaked to HR');
}
$review->invoke($service, $employeeLeave, $hr);
denyReview(fn () => $service->approve($employeeLeave, $employee));
denyReview(fn () => $service->approve($employeeLeave, User::find(3)));
$assert(WorkforceAccess::canAccessRecord($hr, $employee), 'HR cannot access employee details');
$assert(!WorkforceAccess::canAccessRecord($hr, User::find(6), 'employee'), 'Old employee snapshot bypassed authority protection');
Auth::guard('web')->setUser($employee);
Notification::fake();
SystemNotificationService::notifyLeaveReviewers($employee, 'New Leave Request', 'Employee request');
$assert(Notification::sent($hr, App\Notifications\SystemNotification::class)->isNotEmpty(), 'Employee request did not reach HR');
$assert(Notification::sent(User::find(6), App\Notifications\SystemNotification::class)->isEmpty(), 'Employee request leaked to Manager');
Auth::guard('web')->setUser($hr);
$assert(Leave::find(8) !== null, 'HR company query cannot see employee leave');
$assert(Leave::find(6) === null, 'HR query exposed Manager leave');
$controller = app(App\Http\Controllers\LeaveController::class);
$authorize = new ReflectionMethod($controller, 'authorizeLeaveAccess');
$authorize->invoke($controller, $employeeLeave);
Auth::guard('web')->setUser($admin);
$authority = Leave::findOrFail(5);
Auth::guard('web')->setUser($hr);
denyReview(fn () => $authorize->invoke($controller, $authority));
$scope = new ReflectionMethod($controller, 'scopeRecordCategory');
app()->instance('request', Illuminate\Http\Request::create('/leaves'));
$assert($scope->invoke($controller, Leave::query())->pluck('id')->all() === [8], 'HR queue includes own or authority records');
foreach (['index', 'create', 'show', 'apology-letters/index'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path("views/admin/leaves/{$view}.blade.php"))), TOKEN_PARSE);
}
echo "PASS: HR employee queue and notifications, admin-only authority records, self-review denial, direct record access, old snapshots and company isolation.\n";

DB::connection('central')->statement('CREATE TABLE companies (id integer primary key, name text, db_name text, deleted_at text)');
DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Test company', 'db_name' => ':memory:']);
foreach (['is_active', 'login_allowed'] as $column) DB::statement("ALTER TABLE users ADD COLUMN {$column} integer DEFAULT 1");
DB::statement('ALTER TABLE users ADD COLUMN archived_at text');
App\Services\CompanyStaffSchema::ensure();
DB::table('company_staff_roles')->insert(['id' => 10, 'company_id' => 1, 'name' => 'Associate Manager', 'access_role' => 'employee']);
function guardedRequest(int $actorId, string $controller, string $action, string $name, array $input = [], ?int $id = null) {
    Auth::guard('web')->setUser(User::findOrFail($actorId));
    $request = Illuminate\Http\Request::create($id ? '/test/'.$id : '/test', 'GET', $input);
    $route = new Illuminate\Routing\Route(['GET'], $id ? 'test/{leave}' : 'test', [$controller, $action]);
    $route->name($name)->bind($request);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => Auth::user());
    return (new App\Http\Middleware\ProtectWorkforceRecords)->handle($request, fn () => 'allowed');
}
$leaves = App\Http\Controllers\LeaveController::class;
$attendance = App\Http\Controllers\AttendanceController::class;
$assert(guardedRequest(5, $leaves, 'index', 'leaves.index') === 'allowed', 'HR employee inbox blocked');
denyReview(fn () => guardedRequest(5, $leaves, 'index', 'leaves.index', ['staff_category' => 'authority']));
denyReview(fn () => guardedRequest(5, $leaves, 'show', 'leaves.show', [], 5));
denyReview(fn () => guardedRequest(5, $leaves, 'bulkAction', 'leaves.bulkAction', ['ids' => [8, 5], 'action' => 'delete']));
foreach ([4, 6, 7] as $id) {
    denyReview(fn () => guardedRequest($id, $leaves, 'index', 'leaves.index'));
    denyReview(fn () => guardedRequest($id, $attendance, 'index', 'attendance.index'));
    denyReview(fn () => guardedRequest($id, $attendance, 'store', 'attendance.store'));
    $assert(guardedRequest($id, $leaves, 'create', 'leaves.create') === 'allowed', 'Authority cannot submit leave');
}
$assert(guardedRequest(1, $leaves, 'authorityIndex', 'admin.authority-leaves') === 'allowed', 'Admin authority inbox blocked');
$assert(guardedRequest(8, $attendance, 'index', 'attendance.index') === 'allowed', 'Employee own attendance blocked');
echo "PASS: HTTP guards reject forged authority filters, self-detail URLs, mixed bulk IDs and authority history/edit routes; submissions and employee attendance remain accessible.\n";

foreach (['index', 'create', 'store', 'calendar', 'calendarData', 'leaveReport', 'export', 'createApologyLetter', 'storeApologyLetter'] as $action) {
    $assert(guardedRequest(8, $leaves, $action, 'leaves.'.$action) === 'allowed', 'Employee leave action blocked: '.$action);
}
DB::table('users')->where('id', 8)->update(['is_active' => null, 'login_allowed' => null]);
$assert(guardedRequest(8, $leaves, 'index', 'leaves.index') === 'allowed', 'Legacy employee with unset flags was forbidden');
foreach (['is_active', 'login_allowed'] as $flag) {
    DB::table('users')->where('id', 8)->update(['is_active' => 1, 'login_allowed' => 1, $flag => 0]);
    denyReview(fn () => guardedRequest(8, $leaves, 'index', 'leaves.index'));
}
DB::table('users')->where('id', 8)->update(['is_active' => 1, 'login_allowed' => 1, 'archived_at' => '2026-10-12']);
denyReview(fn () => guardedRequest(8, $leaves, 'index', 'leaves.index'));
DB::table('users')->where('id', 8)->update(['archived_at' => null]);
Auth::guard('web')->setUser(User::find(8));
$assert($scope->invoke($controller, Leave::query())->pluck('id')->all() === [8], 'Employee queue exposes other employees or authority requests');
$authorize->invoke($controller, $employeeLeave);
denyReview(fn () => $authorize->invoke($controller, $authority));
echo "PASS: employee leave pages and submissions, own-record access, legacy unset account flags, explicit disabled/archived denial and authority isolation.\n";

DB::statement('CREATE TABLE modules (id integer primary key, slug text, name text, is_active integer, route_name text, route_prefix text, parent_id integer, sort_order integer)');
DB::statement('CREATE TABLE role_permissions (id integer primary key, role text, module_id integer, can_view integer, can_create integer, can_edit integer, can_delete integer, can_approve integer, can_export integer, can_assign integer)');
DB::table('modules')->insert([
    ['id' => 1, 'slug' => 'leaves', 'name' => 'My Leaves', 'is_active' => 1, 'route_name' => 'leaves.index'],
    ['id' => 2, 'slug' => 'leaves-apply', 'name' => 'Apply Leave', 'is_active' => 1, 'route_name' => 'leaves.create'],
]);
DB::table('role_permissions')->insert([
    ['role' => 'employee', 'module_id' => 1, 'can_view' => 1, 'can_create' => 0],
    ['role' => 'employee', 'module_id' => 2, 'can_view' => 1, 'can_create' => 1],
]);
app()->instance(App\Services\CompanyContext::class, new class extends App\Services\CompanyContext {
    public function __construct() {}
    public function current() { return new class { public function hasFeature($slug) { return true; } }; }
});
$moduleAccess = app(App\Http\Middleware\EnsureModuleAccess::class);
foreach (['leaves.create' => 'GET', 'leaves.store' => 'POST'] as $name => $method) {
    $assert($moduleAccess->moduleForRoute($name)->slug === 'leaves-apply', 'Form and submission have different permission modules');
    $request = Illuminate\Http\Request::create('/leave-test', $method);
    $route = new Illuminate\Routing\Route([$method], 'leave-test', [$leaves, $method === 'GET' ? 'create' : 'store']);
    $route->name($name)->bind($request);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => Auth::user());
    $response = $moduleAccess->handle($request, fn () => response('allowed'));
    $assert($response->getContent() === 'allowed', 'Employee Apply Leave grant cannot open or submit the form');
}
DB::table('role_permissions')->where('module_id', 2)->update(['can_create' => 0]);
$request->headers->set('Accept', 'application/json');
$response = $moduleAccess->handle($request, fn () => response('forbidden bypass'));
$assert($response->getStatusCode() === 403, 'Explicit admin permission denial was bypassed');
echo "PASS: Apply Leave form/submission share permissions, parent view-only grants work, explicit admin denials remain enforced.\n";
