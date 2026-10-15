<?php

// Run with: php tests/regression/hr_own_leave_requests.php  (isolated SQLite fixtures; no live data)
// HR applies for leave -> the company admin reviews it -> HR can follow it on "My Leave Requests".
require __DIR__.'/authority_admin_delivery.php';

use App\Models\{Leave, User};
use App\Services\{LeaveService, WorkforceAccess};
use Illuminate\Support\Facades\{Auth, DB};
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
// Realistic queue: HR's own request (stored by the app as a higher-level record), an employee's and a manager's.
DB::table('leaves')->insert([
    ['id' => 21, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'pending'],   // HR: Casual leave, 2 days
    ['id' => 22, 'user_id' => 5, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'approved'],  // HR: earlier sick leave
    ['id' => 23, 'user_id' => 8, 'company_id' => 1, 'staff_category' => 'employee', 'status' => 'pending'],    // Employee request
]);

echo "Leave owner and category\n";
$check(WorkforceAccess::isAuthority($hr), 'HR is higher-level staff, so their leave goes to the company admin');

echo "HR side\n";
Auth::guard('web')->setUser($hr);
$mine = Leave::query()->where('user_id', $hr->id)->whereNull('archived_at')->orderBy('id')->pluck('id')->all();
$check($mine === [5, 21, 22], 'My Leave Requests query returns all of HR\'s own requests (' . implode(',', $mine) . ')');
$controller = app(App\Http\Controllers\LeaveController::class);
$scope = new ReflectionMethod($controller, 'scopeRecordCategory');
app()->instance('request', Illuminate\Http\Request::create('/leaves'));
$queue = $scope->invoke($controller, Leave::query())->pluck('id')->all();
$check(! array_intersect($queue, [5, 21, 22]) && in_array(23, $queue, true), 'HR "My Leaves" (employee queue) is unchanged: employee requests only');

DB::connection('central')->statement('CREATE TABLE companies (id integer primary key, name text, db_name text, deleted_at text)');
DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Test company', 'db_name' => ':memory:']);
foreach (['is_active', 'login_allowed'] as $column) DB::statement("ALTER TABLE users ADD COLUMN {$column} integer DEFAULT 1");
DB::statement('ALTER TABLE users ADD COLUMN archived_at text');
$middleware = new App\Http\Middleware\ProtectWorkforceRecords;
$hit = function (int $actorId, string $action, ?int $leaveId = null) use ($middleware) {
    Auth::guard('web')->setUser(User::findOrFail($actorId));
    $request = Illuminate\Http\Request::create($leaveId ? '/leaves/'.$leaveId : '/leaves/my-requests', 'GET');
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
$check($hit(5, 'show', 23) === 'allowed', 'HR can still open employee requests in their queue');

// The leave page shows Approve / Reject only when LeaveService::canReview() allows it (checked below).

echo "Review rules\n";
$service = new LeaveService;
$check(! $service->canReview(Leave::findOrFail(21), $hr), 'HR cannot review their own request (no Approve / Reject on their leave page)');
$check($denied(fn () => $service->approve(Leave::findOrFail(21), $hr)), 'HR approving their own request is refused');
Auth::guard('web')->setUser($admin);
$check($service->canReview(Leave::findOrFail(21), $admin), 'Company admin can review HR\'s request');
Auth::guard('web')->setUser($hr);
$check($service->canReview(Leave::findOrFail(23), $hr), 'HR can still review employee requests');
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
    $check($hrNotified === ($ownerId === 8), "Owner {$ownerId}: HR notified only for ordinary employee requests");
    foreach ([3, 6] as $excludedId) {
        $check(Illuminate\Support\Facades\Notification::sent(User::findOrFail($excludedId), App\Notifications\SystemNotification::class)->isEmpty(), "Owner {$ownerId}: unrelated admin/manager {$excludedId} not notified");
    }
}

echo "Pages compile\n";
foreach (['index', 'show'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path("views/admin/leaves/{$view}.blade.php"))), TOKEN_PARSE);
}
$check(true, 'leave index and show views compile');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: HR's own requests reach the admin, appear on My Leave Requests, open read-only, stay unreviewable by HR; HR's employee queue and other staff isolation unchanged.\n";
exit($failures ? 1 : 0);
