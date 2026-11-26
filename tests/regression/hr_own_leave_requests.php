<?php

// Run with: php tests/regression/hr_own_leave_requests.php  (isolated SQLite fixtures; no live data)
// HR applies for leave -> the company admin reviews it -> HR can follow it on "My Leave Requests".
require __DIR__.'/authority_admin_delivery.php';

use App\Models\{Leave, User};
use App\Services\{LeaveService, WorkforceAccess};
use Illuminate\Support\Facades\{Auth, Blade, DB, Storage};
use Symfony\Component\HttpKernel\Exception\HttpException;

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$denied = function (callable $action): bool {
    try { $action(); } catch (HttpException $e) { return $e->getStatusCode() === 403; }
    return false;
};

$admin = User::find(1); $hr = User::find(5); $manager = User::find(6); $employee = User::find(8);
DB::statement('ALTER TABLE leaves ADD COLUMN archived_at text');
foreach (['type', 'start_date', 'end_date', 'date', 'total_days', 'reason', 'is_unpaid', 'attachment'] as $column) {
    DB::statement("ALTER TABLE leaves ADD COLUMN {$column} text");
}
DB::statement('ALTER TABLE leaves ADD COLUMN leave_type_id integer');
// Realistic queue: HR's own request (stored by the app as a higher-level record), an employee's and a manager's.
DB::table('leaves')->insert([
    ['id' => 21, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'pending'],   // HR: Casual leave, 2 days
    ['id' => 22, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'approved'],  // HR: earlier sick leave
    ['id' => 23, 'user_id' => 8, 'company_id' => 1, 'staff_category' => 'employee', 'status' => 'pending'],    // Employee request
    ['id' => 24, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'rejected'],
    ['id' => 25, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'approved'],
    ['id' => 26, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'approved'],
]);
foreach ([
    5 => ['start_date' => '2026-10-26', 'end_date' => '2026-10-26', 'reason' => 'Later October'],
    21 => ['start_date' => '2026-09-30', 'end_date' => '2026-10-02', 'reason' => 'Crosses month'],
    22 => ['date' => '2026-10-01', 'reason' => ' =1+1', 'is_unpaid' => '1', 'attachment' => 'authority-leave/1/hr-test.pdf'],
    23 => ['start_date' => '2026-10-01', 'reason' => 'Employee private reason'],
    24 => ['start_date' => '2026-10-04', 'reason' => 'Weekly request'],
    25 => ['start_date' => '2026-10-01', 'reason' => 'Archived request', 'archived_at' => '2026-10-03'],
    26 => ['start_date' => '2026-09-28', 'reason' => 'September request'],
] as $id => $fields) {
    DB::table('leaves')->where('id', $id)->update($fields + ['type' => 'casual', 'total_days' => '1']);
}

echo "Leave owner and category\n";
$check(WorkforceAccess::isAuthority($hr), 'HR is higher-level staff, so their leave goes to the company admin');

echo "HR side\n";
Auth::guard('web')->setUser($hr);
$mine = Leave::query()->where('user_id', $hr->id)->whereNull('archived_at')->orderBy('id')->pluck('id')->all();
$check($mine === [5, 21, 22, 24, 26], 'My Leave Requests query returns all of HR\'s own active requests');
$controller = app(App\Http\Controllers\LeaveController::class);
$request = fn ($query = []) => Illuminate\Http\Request::create('/leaves', 'GET', $query + ['date' => '2026-10-01']);
foreach (['day' => [22, 21], 'week' => [24, 22, 21, 26], 'month' => [5, 24, 22, 21]] as $period => $ids) {
    $view = $controller->index($request(['period' => $period]));
    $report = $view->getData();
    $check($view->name() === 'admin.leaves.personal' && $report['leaves']->pluck('id')->all() === $ids, "HR $period report shows only their overlapping requests");
    $check($report['counts']->sum() === count($ids), "HR $period summary matches its date range");
    $csv = $controller->myLeaves($request(['period' => $period, 'format' => 'csv']));
    ob_start();
    $csv->sendContent();
    $export = ob_get_clean();
    $check(str_contains($csv->headers->get('Content-Disposition'), "my-leave-$period-") && substr_count(trim($export), "\n") === count($ids), "HR $period export matches the report");
    $check(str_contains($export, "' =1+1") && !str_contains($export, 'Employee private reason') && !str_contains($export, 'Archived request'), 'CSV escapes spreadsheet formulas and excludes unrelated records');
}
foreach (['index', 'myLeaves', 'leaveReport'] as $action) {
    $view = $controller->$action($request(['period' => 'week', 'status' => 'unpaid']));
    $check($view->getData()['leaves']->pluck('id')->all() === [22], "$action uses the personal request and status filters");
}
foreach (['employee' => 8, 'user_id' => 6, 'company_id' => 2] as $key => $value) {
    $check($denied(fn () => $controller->export($request([$key => $value]))), "Forged $key is refused on export");
}
$html = Blade::render(preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/leaves/personal.blade.php'))),
    $report + ['errors' => new \Illuminate\Support\ViewErrorBag]);
$check(str_contains($html, 'Export CSV') && str_contains($html, 'View details') && !str_contains($html, 'name="user_id"') && !str_contains($html, '/edit'), 'Report renders export and details without employee or update controls');

DB::connection('central')->statement('CREATE TABLE companies (id integer primary key, name text, db_name text, deleted_at text)');
DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Test company', 'db_name' => ':memory:']);
foreach (['is_active', 'login_allowed'] as $column) DB::statement("ALTER TABLE users ADD COLUMN {$column} integer DEFAULT 1");
DB::statement('ALTER TABLE users ADD COLUMN archived_at text');
$middleware = new App\Http\Middleware\ProtectWorkforceRecords;
$hit = function (int $actorId, string $action, $leaveId = null) use ($middleware) {
    Auth::guard('web')->setUser(User::findOrFail($actorId));
    $request = Illuminate\Http\Request::create($leaveId ? '/leaves/'.($leaveId instanceof Leave ? $leaveId->id : $leaveId) : '/leaves/my-requests', 'GET');
    $route = new Illuminate\Routing\Route(['GET'], $leaveId ? 'leaves/{leave}' : 'leaves/my-requests', [App\Http\Controllers\LeaveController::class, $action]);
    $route->name($leaveId ? 'leaves.'.$action : 'leaves.mine')->bind($request);
    if ($leaveId) $route->setParameter('leave', $leaveId);
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => Auth::user());
    return $middleware->handle($request, fn () => 'allowed');
};
$check($hit(5, 'myLeaves') === 'allowed', 'HR can open My Leave Requests');
$check($hit(5, 'show', 21) === 'allowed', 'HR can open their own pending request');
$check($hit(5, 'show', 22) === 'allowed', 'HR can open their own approved request');
$check($denied(fn () => $hit(5, 'edit', 21)), 'HR cannot edit their own request (admin manages it)');
Auth::guard('web')->setUser($hr);
$check(Leave::find(6) === null, 'HR still cannot load the Manager\'s leave (hidden by the company scope, so the page is not found)');
Auth::guard('web')->setUser($admin);
$employeeLeave = Leave::findOrFail(23);
$check($denied(fn () => $hit(5, 'show', $employeeLeave)), 'HR cannot open an employee request through a direct details URL');
$check($denied(fn () => $controller->show($employeeLeave)), 'Leave details independently enforce HR ownership');
foreach (['update', 'updateStatus', 'updatePaidStatus', 'destroy', 'archiveLeave', 'archiveAll', 'bulkAction', 'restore'] as $action) {
    $check($denied(fn () => $hit(5, $action, 21)), "HR cannot $action leave records");
}
$route = app('router')->getRoutes()->match(Illuminate\Http\Request::create('/leaves/export'));
$check($route->getName() === 'leaves.export', 'The export URL resolves to export before generic leave details');
$files = new App\Http\Controllers\WorkforceFileController;
$ownLeave = Leave::findOrFail(22);
Storage::fake('local')->put($ownLeave->getRawOriginal('attachment'), 'fixture');
$check($files->attachment($ownLeave)->headers->get('Cache-Control') === 'no-store, private', 'HR can open their own private attachment');
$check($denied(fn () => $files->attachment($employeeLeave)), 'HR cannot open another employee’s attachment');
Storage::disk('local')->delete($ownLeave->getRawOriginal('attachment'));

// The leave page shows Approve / Reject only when LeaveService::canReview() allows it (checked below).

echo "Review rules\n";
$service = new LeaveService;
$check(! $service->canReview(Leave::findOrFail(21), $hr), 'HR cannot review their own request (no Approve / Reject on their leave page)');
$check($denied(fn () => $service->approve(Leave::findOrFail(21), $hr)), 'HR approving their own request is refused');
Auth::guard('web')->setUser($admin);
$check($service->canReview(Leave::findOrFail(21), $admin), 'Company admin can review HR\'s request');
Auth::guard('web')->setUser($hr);
$check(! $service->canReview($employeeLeave, $hr), 'HR cannot review employee requests');
Auth::guard('web')->setUser($admin);
$managerLeave = Leave::findOrFail(6);
Auth::guard('web')->setUser($hr);
$check(! $service->canReview($managerLeave, $hr), 'HR cannot review the Manager\'s request');

echo "Manager personal access\n";
$check($hit(6, 'myLeaves') === 'allowed', 'Manager can open My Leave Requests');
$check($hit(6, 'index') === 'allowed', 'Manager can open their own leave status list');
$check($hit(6, 'show', 6) === 'allowed', 'Manager can read their own leave status');
$check($denied(fn () => $hit(6, 'edit', 6)), 'Manager cannot edit their own request');
$check($denied(fn () => $hit(6, 'updateStatus', 6)), 'Manager cannot approve their own request');
$check(! $service->canReview($managerLeave, $manager), 'Manager has no review authority over their own leave');

echo "Actual reviewer notification routing\n";
foreach ([5, 6, 8] as $ownerId) {
    $owner = User::findOrFail($ownerId);
    Auth::guard('web')->setUser($owner);
    Illuminate\Support\Facades\Notification::fake();
    App\Services\SystemNotificationService::notifyLeaveReviewers($owner, 'New Leave Request', 'Review requested');
    foreach ([1, 2] as $adminId) {
        $check(Illuminate\Support\Facades\Notification::sent(User::findOrFail($adminId), App\Notifications\SystemNotification::class)->isNotEmpty(), "Owner {$ownerId}: company admin {$adminId} notified");
    }
    $hrNotified = Illuminate\Support\Facades\Notification::sent(User::findOrFail(5), App\Notifications\SystemNotification::class)->isNotEmpty();
    $check(! $hrNotified, "Owner {$ownerId}: review notification is private to company admins");
    foreach ([3, 6] as $excludedId) {
        $check(Illuminate\Support\Facades\Notification::sent(User::findOrFail($excludedId), App\Notifications\SystemNotification::class)->isEmpty(), "Owner {$ownerId}: unrelated admin/manager {$excludedId} not notified");
    }
}

echo "Pages compile\n";
foreach (['index', 'show', 'personal'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path("views/admin/leaves/{$view}.blade.php"))), TOKEN_PARSE);
}
$check(true, 'leave index and show views compile');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: HR personal leave reports, period CSV exports, private details and attachments, write denial, and company admin review.\n";
exit($failures ? 1 : 0);
