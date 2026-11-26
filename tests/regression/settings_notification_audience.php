<?php

// Run with: php tests/regression/settings_notification_audience.php  (isolated SQLite; no live data)
// 1. System/settings updates ("Performance Appraisal Settings Updated", operating-days changes) are never
//    sent and older ones are hidden from every Notification Center (list, unread count, bell).
// 2. Role & Permission changes notify only the users that role applies to, with what changed.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\RolePermissionController;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Support\Str;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::create('users', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->string('email')->nullable(); $t->string('password')->nullable();
    $t->string('role')->nullable(); $t->unsignedBigInteger('company_staff_role_id')->nullable(); $t->boolean('is_active')->default(true);
    $t->timestamp('archived_at')->nullable(); $t->timestamps();
});
Schema::create('notifications', function ($t) {
    $t->uuid('id')->primary(); $t->string('type'); $t->string('notifiable_type'); $t->unsignedBigInteger('notifiable_id');
    $t->text('data'); $t->timestamp('read_at')->nullable(); $t->timestamps();
});
Schema::create('company_staff_roles', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); $t->string('name'); $t->string('access_role'); $t->timestamps(); });
Schema::create('role_permissions', function ($t) {
    $t->id(); $t->string('role'); $t->unsignedBigInteger('module_id');
    foreach (['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'] as $p) $t->boolean('can_' . $p)->default(false);
    $t->timestamps();
});
DB::table('company_staff_roles')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Associate Manager', 'access_role' => 'hr']);
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 2, 'company_id' => 1, 'name' => 'HR One', 'role' => 'hr', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 3, 'company_id' => 1, 'name' => 'HR Two', 'role' => 'hr', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 4, 'company_id' => 1, 'name' => 'Manager', 'role' => 'manager', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 5, 'company_id' => 1, 'name' => 'Developer', 'role' => 'employee', 'company_staff_role_id' => null, 'archived_at' => null],
    ['id' => 6, 'company_id' => 1, 'name' => 'Associate Manager', 'role' => 'hr', 'company_staff_role_id' => 1, 'archived_at' => null],
    ['id' => 7, 'company_id' => 1, 'name' => 'Former HR', 'role' => 'hr', 'company_staff_role_id' => null, 'archived_at' => now()],
]);
DB::table('users')->where('id', 7)->update(['is_active' => false]); // archiving deactivates the account
// The company role "Associate Manager" has its own saved permissions, so HR changes do not apply to it.
DB::table('role_permissions')->insert(['role' => 'staff:1:1', 'module_id' => 10, 'can_view' => true]);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$addNotice = function (int $userId, array $data) {
    DB::table('notifications')->insert(['id' => (string) Str::uuid(), 'type' => 'App\\Notifications\\SystemNotification', 'notifiable_type' => User::class,
        'notifiable_id' => $userId, 'data' => json_encode(['company_id' => 1] + $data), 'created_at' => now(), 'updated_at' => now()]);
};
$titles = fn (int $id) => User::findOrFail($id)->notifications()->get()->map(fn ($n) => $n->data['title'] ?? '')->sort()->values()->all();
Auth::guard('web')->setUser(User::findOrFail(1));
session(['current_company_id' => 1]);

// ---- 1. Old system updates already stored are hidden for everyone ----
foreach ([1, 5] as $userId) {
    $addNotice($userId, ['type' => 'setting_update', 'setting_module' => 'performance', 'title' => 'Performance Appraisal Settings Updated', 'url' => route('admin.settings.performance')]);
    $addNotice($userId, ['type' => 'erp_activity', 'title' => 'Company Operating Days Schedule Updated', 'working_days' => ['Monday', 'Tuesday'], 'url' => route('admin.settings.work-schedule')]);
    $addNotice($userId, ['type' => 'erp_activity', 'title' => 'Announcement: Holiday on Fri, Nov 27, 2026', 'special_day_date' => '2026-11-27', 'url' => route('admin.settings.work-schedule')]);
    $addNotice($userId, ['type' => 'erp_activity', 'title' => 'New Task Assigned', 'url' => route('tasks.index')]);
    $addNotice($userId, ['title' => 'Ticket Raised']);
}
$expected = ['Announcement: Holiday on Fri, Nov 27, 2026', 'New Task Assigned', 'Ticket Raised'];
$check($titles(5) === $expected, 'employee no longer sees old "Settings Updated" / operating-days system updates');
$check($titles(1) === $expected, 'admins do not see old system updates either');
$check(User::findOrFail(5)->unreadNotifications()->count() === 3, 'unread count (bell badge) ignores hidden system updates');
$check(DB::table('notifications')->count() === 10, 'nothing is deleted from the database');

// ---- 2. New system updates are not sent at all; announcements still are ----
DB::table('notifications')->delete();
SystemNotificationService::notifyAllRoles('Performance Appraisal Settings Updated', 'Updated by Admin.', route('admin.settings.performance'), ['type' => 'setting_update', 'setting_module' => 'performance'], 1);
SystemNotificationService::notifyAllRoles('Company Operating Days Schedule Updated', 'Working days changed.', route('admin.settings.work-schedule'), ['working_days' => ['Monday'], 'type' => 'setting_update', 'setting_module' => 'work-schedule'], 1);
$check(DB::table('notifications')->count() === 0, 'settings and operating-days updates send no notifications');
SystemNotificationService::notifyAllRoles('Announcement: Holiday on Fri, Nov 27, 2026', 'Holiday.', route('admin.settings.work-schedule'), ['special_day_date' => '2026-11-27'], 1);
$check(DB::table('notifications')->count() === 6, 'special-day announcements still reach all active staff');

// ---- 3. Role & Permission changes reach only that role ----
DB::table('notifications')->delete();
$controller = new RolePermissionController;
$notify = new ReflectionMethod($controller, 'notifyRoleOfChanges');
$modules = collect([
    (object) ['id' => 10, 'slug' => 'payroll', 'name' => 'Payroll', 'sidebar_name' => 'Payroll'],
    (object) ['id' => 11, 'slug' => 'reports', 'name' => 'Reports', 'sidebar_name' => 'Reports'],
]);
$count = $notify->invoke($controller, 'hr', $modules, [10 => [], 11 => ['view', 'export']], [10 => ['view'], 11 => ['view']]);
$recipients = DB::table('notifications')->pluck('notifiable_id')->sort()->values()->all();
$check($count === 2 && $recipients === [2, 3], 'HR permission change notifies only active HR users (not admin, manager, employee, other company roles or archived users)');
$data = json_decode(DB::table('notifications')->first()->data, true);
$check(($data['title'] ?? '') === 'Your role permissions were updated'
    && str_contains($data['message'], 'Access added: Payroll (view).') && str_contains($data['message'], 'Access removed: Reports (export).')
    && ($data['type'] ?? '') === 'permission_update', 'notice explains exactly what was added and removed');
$check($titles(2) === ['Your role permissions were updated'], 'the permission notice is visible in the HR Notification Center');
$check($titles(4) === [] && $titles(5) === [], 'manager and employee receive nothing for an HR change');

DB::table('notifications')->delete();
$count = $notify->invoke($controller, 'hr', $modules, [10 => ['view']], [10 => ['view']]);
$check($count === 0 && DB::table('notifications')->count() === 0, 'saving without changes sends nothing');

$count = $notify->invoke($controller, 'staff:1:1', $modules, [10 => ['view']], [10 => ['view', 'create']]);
$check($count === 1 && DB::table('notifications')->pluck('notifiable_id')->all() === [6], 'a company role change notifies only that company role\'s users');

DB::table('notifications')->delete();
$count = $notify->invoke($controller, 'employee', $modules, [], [11 => ['view']]);
$check($count === 1 && DB::table('notifications')->pluck('notifiable_id')->all() === [5], 'employee role change notifies only employees');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: no system-update notifications; permission changes notify only the affected role.\n";
exit($failures ? 1 : 0);
