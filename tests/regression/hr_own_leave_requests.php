<?php

// Run with: php tests/regression/hr_own_leave_requests.php  (isolated SQLite fixtures; no live data)
// HR applies for leave -> the company admin reviews it -> HR can follow it on "My Leave Requests".
require __DIR__.'/authority_admin_delivery.php';

use App\Http\Middleware\{EnsureModuleAccess, ProtectWorkforceRecords};
use App\Models\{Leave, LeaveType, User};
use App\Notifications\SystemNotification;
use App\Services\CompanyContext;
use App\Services\{LeaveService, WorkforceAccess};
use Carbon\Carbon;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, Blade, DB, Notification, Schema, Storage};
use Illuminate\Validation\ValidationException;
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
    $view = $controller->myLeaves($request(['period' => $period]));
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
foreach (['myLeaves', 'leaveReport'] as $action) {
    $view = $controller->$action($request(['period' => 'week', 'status' => 'unpaid']));
    $check($view->getData()['leaves']->pluck('id')->all() === [22], "$action uses the personal request and status filters");
}
$check($denied(fn () => $controller->export($request(['company_id' => 2]))), 'Forged company_id is refused on export');
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
$check(Leave::find(6) !== null, 'HR can load non-HR Manager leave for Employee Leaves management');
Auth::guard('web')->setUser($admin);
$employeeLeave = Leave::findOrFail(23);
$check($hit(5, 'show', $employeeLeave) === 'allowed', 'HR can open an employee request in Employee Leaves');
foreach (['update', 'updateStatus', 'updatePaidStatus', 'destroy', 'archiveLeave', 'archiveAll', 'bulkAction', 'restore'] as $action) {
    $check($denied(fn () => $hit(5, $action, 21)), "HR cannot $action their own HR leave records");
}
$route = app('router')->getRoutes()->match(Illuminate\Http\Request::create('/leaves/export'));
$check($route->getName() === 'leaves.export', 'The export URL resolves to export before generic leave details');
$files = new App\Http\Controllers\WorkforceFileController;
$ownLeave = Leave::findOrFail(22);
Storage::fake('local')->put($ownLeave->getRawOriginal('attachment'), 'fixture');
$check($files->attachment($ownLeave)->headers->get('Cache-Control') === 'no-store, private', 'HR can open their own private attachment');
DB::table('leaves')->where('id', $employeeLeave->id)->update(['attachment' => 'authority-leave/1/emp-23.pdf']);
$employeeLeave->refresh();
Storage::disk('local')->put('authority-leave/1/emp-23.pdf', 'fixture-emp');
$check($files->attachment($employeeLeave)->headers->get('Cache-Control') === 'no-store, private', 'HR can open non-HR employee leave attachment');
Storage::disk('local')->delete([$ownLeave->getRawOriginal('attachment'), 'authority-leave/1/emp-23.pdf']);
DB::table('leaves')->where('id', $employeeLeave->id)->update(['attachment' => null]);
$employeeLeave->refresh();

// The leave page shows Approve / Reject only when LeaveService::canReview() allows it (checked below).

echo "Review rules\n";
$service = new LeaveService;
$check(! $service->canReview(Leave::findOrFail(21), $hr), 'HR cannot review their own request (no Approve / Reject on their leave page)');
$check($denied(fn () => $service->approve(Leave::findOrFail(21), $hr)), 'HR approving their own request is refused');
Auth::guard('web')->setUser($admin);
$check($service->canReview(Leave::findOrFail(21), $admin), 'Company admin can review HR\'s request');
Auth::guard('web')->setUser($hr);
$check($service->canReview($employeeLeave, $hr), 'HR can review non-HR employee requests');
Auth::guard('web')->setUser($admin);
$managerLeave = Leave::findOrFail(6);
Auth::guard('web')->setUser($hr);
$check($service->canReview($managerLeave, $hr), 'HR can review non-HR Manager requests in Employee Leaves');

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

echo "Embedded HR leave application\n";
Schema::create('modules', function ($table) {
    $table->id();
    $table->string('slug');
    $table->string('name');
    $table->boolean('is_active')->default(true);
});
Schema::create('role_permissions', function ($table) {
    $table->id();
    $table->string('role');
    $table->unsignedBigInteger('module_id');
    $table->boolean('can_view');
    $table->boolean('can_create');
});
DB::table('modules')->insert([
    ['id' => 1, 'slug' => 'leaves', 'name' => 'My Leave'],
    ['id' => 2, 'slug' => 'leaves-apply', 'name' => 'Apply Leave'],
]);
DB::table('role_permissions')->insert([
    ['role' => 'hr', 'module_id' => 1, 'can_view' => true, 'can_create' => false],
    ['role' => 'hr', 'module_id' => 2, 'can_view' => true, 'can_create' => true],
]);
app()->instance(CompanyContext::class, new class extends CompanyContext {
    public function __construct() {}
    public function current() { return new class { public function hasFeature($slug) { return true; } }; }
});
Schema::table('users', function ($table) {
    $table->timestamps();
    $table->integer('annual_leave_balance')->nullable();
    $table->integer('remaining_leaves')->nullable();
    $table->integer('leaves_taken_this_year')->nullable();
    $table->date('last_leave_reset')->nullable();
});
Schema::table('leaves', function ($table) {
    $table->timestamps();
    $table->string('duration')->nullable();
});
Schema::create('employee_details', function ($table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
});
Schema::create('holidays', function ($table) {
    $table->id();
    $table->date('date');
    $table->timestamp('archived_at')->nullable();
});
foreach ([
    '2026_02_09_130245_create_leave_policies_table.php',
    '2026_02_09_130311_create_leave_balances_table.php',
    '2026_06_17_010000_upgrade_leave_management_system.php',
    '2026_06_17_030000_add_paid_unpaid_days_to_leaves_table.php',
    '2026_06_18_130000_create_leave_apology_letters_table.php',
    '2026_06_18_140000_add_archived_at_to_leave_apology_letters_table.php',
] as $migration) (require database_path('migrations/tenant/'.$migration))->up();

Auth::guard('web')->setUser($hr);
$legacy = $controller->create();
$applicationUrl = fn () => route('leaves.mine', ['apply' => 1]).'#apply-leave';
$check($legacy->getTargetUrl() === $applicationUrl(), 'The standalone Apply Leave URL opens the embedded form');
$open = $controller->myLeaves($request(['apply' => 1]))->getData();
$applicationHtml = Blade::render(preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/leaves/personal.blade.php'))),
    $open + ['errors' => new \Illuminate\Support\ViewErrorBag]);
$check($open['showLeaveApplication'] && str_contains($applicationHtml, 'id="hr-leave-application"') && str_contains($applicationHtml, 'Send to admin')
    && !str_contains($applicationHtml, 'name="user_id"') && !str_contains($applicationHtml, 'name="admin_note"'), 'The embedded form has no employee picker or management fields');

Carbon::setTestNow(Carbon::parse('2026-11-27 12:00:00'));
Notification::fake();
Storage::fake('local');
$sick = LeaveType::where('code', 'SL')->firstOrFail();
$submit = function (array $input, array $files = []) {
    $request = Request::create('/leaves/store', 'POST', $input, [], $files);
    $request->setLaravelSession(app('session')->driver());
    $request->setUserResolver(fn () => Auth::user());
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);
    app()->instance('request', $request);

    return (new ProtectWorkforceRecords)->handle($request, fn () => app(EnsureModuleAccess::class)->handle($request, fn () => $route->run()));
};
$input = [
    'leave_type_id' => $sick->id,
    'start_date' => '2026-12-08',
    'end_date' => '2026-12-08',
    'reason' => 'HR test request',
    'half_day_flag' => '1',
    'user_id' => 8,
    'status' => 'approved',
    'admin_note' => 'Forged reviewer note',
];
try {
    $submit(array_replace($input, ['end_date' => '2026-12-09']));
    $check(false, 'A multi-day half-day request is rejected');
} catch (ValidationException $e) {
    $check(isset($e->errors()['end_date']) && $e->redirectTo === $applicationUrl(), 'Invalid half-day dates reopen the embedded form');
}
$response = $submit($input, ['attachment' => UploadedFile::fake()->create('support.pdf', 1, 'application/pdf')]);
$newLeave = Leave::where('reason', 'HR test request')->firstOrFail();
$check($newLeave->user_id === 5 && $newLeave->status === 'pending' && (float) $newLeave->total_days === 0.5,
    'The form saved only HR’s own half-day request with pending status');
$check($response->getTargetUrl() === route('leaves.mine', ['period' => 'day', 'date' => '2026-12-08']),
    'Successful submission opens the day report containing the new request');
$afterSubmit = $controller->myLeaves($request(['period' => 'day', 'date' => '2026-12-08']))->getData();
$check($afterSubmit['leaves']->pluck('id')->all() === [$newLeave->id], 'The new request appears in HR’s own daily report');
$check($newLeave->approvals()->firstOrFail()->note === null, 'HR could not submit a reviewer note');
$check(Storage::disk('local')->exists($newLeave->getRawOriginal('attachment')), 'The application saved the supporting document in private storage');
foreach ([1, 2] as $adminId) {
    $reviewer = User::findOrFail($adminId);
    $check(Notification::sent($reviewer, SystemNotification::class,
        fn ($notification) => str_contains($notification->toDatabase($reviewer)['url'], '/leaves/'.$newLeave->id))->isNotEmpty(),
        "Company admin {$adminId} receives the request link");
}
$check(Notification::sent($hr, SystemNotification::class)->isEmpty()
    && Notification::sent(User::findOrFail(3), SystemNotification::class)->isEmpty(), 'Review notification stays with company admins');
$duplicate = $submit($input);
$check($duplicate->getTargetUrl() === $applicationUrl() && Leave::where('reason', 'HR test request')->count() === 1,
    'An overlapping request returns to the same form with no duplicate record');
DB::table('role_permissions')->where('module_id', 2)->update(['can_create' => false]);
$check(!$controller->myLeaves($request(['apply' => 1]))->getData()['showLeaveApplication'], 'HR with view-only access cannot open the form');
$check($submit($input)->getTargetUrl() === route('dashboard'), 'Create permissions are enforced on form submission');
Storage::disk('local')->delete($newLeave->getRawOriginal('attachment'));
Carbon::setTestNow();

echo "Pages compile\n";
foreach (['index', 'show', 'personal', 'partials/personal-application'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path("views/admin/leaves/{$view}.blade.php"))), TOKEN_PARSE);
}
$check(true, 'leave index and show views compile');

Schema::create('app_settings', function ($table) {
    $table->id();
    $table->string('key');
    $table->text('value')->nullable();
});
Auth::guard('web')->setUser($hr);
$empLeaveRequest = Request::create('/leaves', 'GET');
app()->instance('request', $empLeaveRequest);
$hrEmpView = $controller->index($empLeaveRequest);
$hrEmpData = $hrEmpView->getData();
$check($hrEmpView->name() === 'admin.leaves.index'
    && ! $hrEmpData['employees']->pluck('id')->contains($hr->id)
    && $hrEmpData['employees']->pluck('id')->contains(6)
    && $hrEmpData['employees']->pluck('id')->contains(8), 'HR Employee Leaves lists all company employees except HR');
$check(! $hrEmpData['leaves']->getCollection()->pluck('user_id')->contains($hr->id)
    && $hrEmpData['leaves']->getCollection()->pluck('id')->contains(23), 'HR Employee Leaves excludes HR requests and includes employee requests');
$empLeaveHtml = Blade::render(preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/leaves/index.blade.php'))),
    $hrEmpData + ['errors' => new \Illuminate\Support\ViewErrorBag]);
$check(! str_contains($empLeaveHtml, 'Higher-level Leaves') && ! str_contains($empLeaveHtml, 'Archive All') && str_contains($empLeaveHtml, 'Employee Leave Management') && str_contains($empLeaveHtml, '<strong class="crumb-current">Employee Leave</strong>'), 'Employee Leaves shows Employee Leave breadcrumb/heading and hides Higher-level Leaves and Archive All buttons');
$check($controller->show($employeeLeave)->name() === 'admin.leaves.show', 'Leave details allow HR to view employee requests');

Auth::guard('web')->setUser($admin);
$hrLeaveRequest = Request::create('/admin/authority-leaves', 'GET');
app()->instance('request', $hrLeaveRequest);
$hrLeaveData = $controller->authorityIndex($hrLeaveRequest)->getData();
$hrLeaveHtml = Blade::render(preg_replace('/^@(extends|section|endsection).*$/m', '', file_get_contents(resource_path('views/admin/leaves/index.blade.php'))),
    $hrLeaveData + ['errors' => new \Illuminate\Support\ViewErrorBag]);
$check($hrLeaveData['employees']->pluck('id')->all() === [5], 'HR Leave employee dropdown only contains HR accounts');
$check($hrLeaveData['leaves']->getCollection()->pluck('user_id')->unique()->all() === [5], 'HR Leave list only contains HR leaves and excludes manager/employee leaves');
$check(!str_contains($hrLeaveHtml, '> Employee Leaves') && !str_contains($hrLeaveHtml, 'Archive All') && str_contains($hrLeaveHtml, 'HR Leave Management') && str_contains($hrLeaveHtml, '<strong class="crumb-current">HR Leave</strong>'), 'HR Leave shows HR Leave breadcrumb/heading and hides Employee Leaves and Archive All buttons');
$check($service->canReview($newLeave->fresh(), $admin) && ! $service->canReview($newLeave->fresh(), $hr) && ! $service->canReview($newLeave->fresh(), User::findOrFail(6)), 'Only company admin can review HR leave');
try {
    $service->approve($newLeave->fresh(), User::findOrFail(6));
    $check(false, 'Non-admin manager could approve HR leave');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $check($e->getStatusCode() === 403, 'Non-admin approval of HR leave is rejected with 403');
}
Schema::table('attendances', function ($table) {
    foreach (['status', 'clock_in_address', 'staff_role_name', 'staff_designation', 'staff_designation_level', 'updated_at', 'created_at'] as $col) {
        if (! Schema::hasColumn('attendances', $col)) $table->string($col)->nullable();
    }
});
$approvedByAdmin = $service->approve($newLeave->fresh(), $admin, 'Approved by admin');
$check($approvedByAdmin->status === 'approved' && (int) $approvedByAdmin->approved_by === (int) $admin->id, 'Admin can approve HR leave');
$approvedByHr = $service->approve($employeeLeave->fresh(), $hr, 'Approved by HR');
$rejectedByHr = $service->reject($managerLeave->fresh(), $hr, 'Rejected by HR');
$check($approvedByHr->status === 'approved' && (int) $approvedByHr->approved_by === (int) $hr->id, 'HR can approve non-HR employee leave');
$check($rejectedByHr->status === 'rejected' && (int) $rejectedByHr->rejected_by === (int) $hr->id, 'HR can reject non-HR manager leave in Employee Leaves');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: HR personal reports, CSV, private details, embedded applications and admin review.\n";
exit($failures ? 1 : 0);
