<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Attendance, Leave, User};
use App\Services\{SystemNotificationService, WorkforceAccess};
use Illuminate\Support\Facades\{Auth, DB, Notification};

foreach (['tenant', 'central', 'mysql'] as $connection) {
    config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
DB::statement('CREATE TABLE users (id integer primary key, company_id integer, name text, role text, company_staff_role_id integer)');
DB::statement('CREATE TABLE notifications (id text primary key)');
DB::statement('CREATE TABLE leaves (id integer primary key, user_id integer, company_id integer, staff_category text, status text)');
DB::statement('CREATE TABLE attendances (id integer primary key, user_id integer, company_id integer, staff_category text, date text)');
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin', 'company_staff_role_id' => null],
    ['id' => 2, 'company_id' => 1, 'name' => 'Admin Alias', 'role' => 'administrator', 'company_staff_role_id' => null],
    ['id' => 3, 'company_id' => 2, 'name' => 'Other Admin', 'role' => 'admin', 'company_staff_role_id' => null],
    ['id' => 4, 'company_id' => 1, 'name' => 'Custom Role', 'role' => 'employee', 'company_staff_role_id' => 10],
    ['id' => 5, 'company_id' => 1, 'name' => 'HR', 'role' => 'HR', 'company_staff_role_id' => null],
    ['id' => 6, 'company_id' => 1, 'name' => 'Manager', 'role' => 'manager', 'company_staff_role_id' => null],
    ['id' => 7, 'company_id' => 1, 'name' => 'Developer', 'role' => 'developer', 'company_staff_role_id' => null],
    ['id' => 8, 'company_id' => 1, 'name' => 'Employee', 'role' => 'employee', 'company_staff_role_id' => null],
]);
$assert = fn ($condition, $message) => $condition ?: throw new RuntimeException($message);
foreach ([4, 5, 6, 7] as $id) {
    $staff = User::findOrFail($id);
    Auth::guard('web')->setUser($staff);
    $assert(WorkforceAccess::isAuthority($staff), 'Upper-level role was not recognized');
    DB::table('leaves')->insert(['id' => $id, 'user_id' => $id, 'company_id' => 1, 'staff_category' => 'authority', 'status' => 'pending']);
    DB::table('attendances')->insert(['id' => $id, 'user_id' => $id, 'company_id' => 1, 'staff_category' => 'authority', 'date' => '2026-10-12']);
    Notification::fake();
    SystemNotificationService::notifyAdmins('New Leave Request', $staff->name.' requested leave', route('leaves.show', ['leave' => $id, 'staff_category' => 'authority']));
    foreach ([1, 2] as $adminId) $assert(Notification::sent(User::find($adminId), App\Notifications\SystemNotification::class)->isNotEmpty(), 'Admin did not receive leave notification');
    $assert(Notification::sent(User::find(3), App\Notifications\SystemNotification::class)->isEmpty(), 'Notification crossed companies');
    $method = new ReflectionMethod(App\Http\Controllers\DashboardController::class, 'notifyAuthorityAttendance');
    foreach (['Clocked In', 'Clocked Out'] as $action) {
        Notification::fake();
        DB::connection('tenant')->transaction(fn () => $method->invoke(new App\Http\Controllers\DashboardController, Attendance::find($id), $action));
        foreach ([1, 2] as $adminId) $assert(Notification::sent(User::find($adminId), App\Notifications\SystemNotification::class,
            fn ($notification) => str_contains($notification->toDatabase(User::find($adminId))['url'], '/admin/authority-attendance'))->isNotEmpty(), 'Attendance notification missing or routed incorrectly');
        $assert(Notification::sent(User::find(3), App\Notifications\SystemNotification::class)->isEmpty(), 'Notification crossed companies');
    }
    Notification::fake();
    DB::connection('tenant')->beginTransaction();
    $method->invoke(new App\Http\Controllers\DashboardController, Attendance::find($id), 'Clocked In');
    DB::connection('tenant')->rollBack();
    $assert(Notification::sentNotifications() === [], 'Rolled-back attendance sent notification');
}
Auth::guard('web')->setUser(User::find(1));
$assert(WorkforceAccess::records(Leave::query(), 'authority')->count() === 4, 'Admin cannot see every upper-level leave');
$assert(WorkforceAccess::records(Attendance::query(), 'authority')->count() === 4, 'Admin cannot see every upper-level attendance');
$assert(WorkforceAccess::users(User::query(), 'authority')->pluck('id')->all() === [4, 5, 6, 7], 'Legacy HR/manager/custom role filtering failed');
Auth::guard('web')->setUser(User::find(3));
$assert(Leave::count() === 0 && Attendance::count() === 0, 'Another company can see workforce records');
echo "PASS: HR, manager, developer and custom role submissions notify company admins and administrator aliases; clock notifications link to upper-level attendance, rollbacks send nothing, records stay visible to Admin and isolated from other companies.\n";
