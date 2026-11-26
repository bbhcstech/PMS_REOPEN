<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\TicketController;
use App\Http\Middleware\EnsureModuleAccess;
use App\Models\{RolePermission, Ticket, User, WorkspaceModule};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Schema};

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('tenant')->getPdo()->sqliteCreateFunction('FIND_IN_SET', fn ($needle, $haystack) => $haystack ? (in_array((string) $needle, explode(',', (string) $haystack), true) ? 1 : 0) : 0, 2);
config(['database.default' => 'tenant', 'cache.default' => 'array', 'session.driver' => 'array']);

DB::statement('CREATE TABLE users (id integer primary key, company_id integer, name text, email text, role text, designation text, profile_image text, company_staff_role_id integer, archived_at text, created_at text, updated_at text)');
DB::statement('CREATE TABLE notifications (id text primary key, type text, notifiable_type text, notifiable_id integer, data text, read_at text, created_at text, updated_at text)');
Schema::create('modules', function (Blueprint $t) {
    $t->id();
    $t->string('slug');
    $t->string('name');
    $t->boolean('is_active')->default(true);
    $t->string('route_name')->nullable();
    $t->string('route_prefix')->nullable();
    $t->unsignedBigInteger('parent_id')->nullable();
    $t->integer('sort_order')->default(0);
    $t->timestamps();
});
Schema::create('role_permissions', function (Blueprint $t) {
    $t->id();
    $t->string('role');
    $t->unsignedBigInteger('module_id');
    foreach (['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'] as $perm) {
        $t->boolean('can_' . $perm)->default(false);
    }
    $t->timestamps();
});
Schema::create('company_settings', function (Blueprint $t) {
    $t->id();
    $t->string('company_name')->nullable();
    $t->string('company_logo')->nullable();
});
Schema::create('projects', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('company_id')->nullable();
    $t->string('name');
    $t->string('status')->nullable();
    $t->string('deleted_at')->nullable();
    $t->timestamps();
});
Schema::create('tasks', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('company_id')->nullable();
    $t->unsignedBigInteger('project_id')->nullable();
    $t->string('title');
    $t->string('assigned_to')->nullable();
    $t->string('status')->nullable();
    $t->timestamps();
});
Schema::create('assigned_task_user', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('task_id');
    $t->unsignedBigInteger('user_id');
});
Schema::create('project_user', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('project_id');
    $t->unsignedBigInteger('user_id');
});
Schema::create('task_timers', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->unsignedBigInteger('task_id')->nullable();
    $t->string('start_time')->nullable();
    $t->string('end_time')->nullable();
    $t->timestamps();
});
Schema::create('sticky_notes', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->text('note_text')->nullable();
    $t->string('colour')->nullable();
    $t->string('completed_at')->nullable();
    $t->timestamps();
});
Schema::create('tickets', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('company_id')->nullable();
    $t->unsignedBigInteger('requester_id');
    $t->unsignedBigInteger('agent_id')->nullable();
    $t->unsignedBigInteger('project_id')->nullable();
    $t->unsignedBigInteger('group_id')->nullable();
    foreach (['requester_type', 'requester_name', 'affected_module', 'type_id', 'subject', 'description', 'attachment', 'priority', 'deadline', 'channel', 'tags', 'status'] as $column) {
        $t->text($column)->nullable();
    }
    $t->timestamps();
});
Schema::create('ticket_activities', function (Blueprint $t) {
    $t->id();
    foreach (['ticket_id', 'project_id', 'user_id', 'assigned_to', 'channel_id', 'group_id', 'type_id'] as $column) {
        $t->unsignedBigInteger($column)->nullable();
    }
    foreach (['status', 'priority', 'type', 'content'] as $column) {
        $t->text($column)->nullable();
    }
    $t->timestamps();
});
Schema::create('ticket_groups', function (Blueprint $t) {
    $t->id();
    $t->string('group_name');
    $t->timestamps();
});
Schema::create('replies', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('ticket_id');
    $t->unsignedBigInteger('user_id');
    $t->text('message');
    $t->string('attachment')->nullable();
    $t->timestamps();
});
Schema::create('time_logs', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('user_id')->nullable();
    $t->unsignedBigInteger('project_id')->nullable();
    $t->text('memo')->nullable();
    $t->float('total_hours')->nullable();
    $t->string('start_date')->nullable();
    $t->timestamps();
});
Schema::create('holidays', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('company_id')->nullable();
    $t->string('date')->nullable();
    $t->string('archived_at')->nullable();
});
Schema::create('employee_details', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->unsignedBigInteger('designation_id')->nullable();
    $t->unsignedBigInteger('department_id')->nullable();
});
Schema::create('designations', function (Blueprint $t) {
    $t->id();
    $t->string('name')->nullable();
});
Schema::create('departments', function (Blueprint $t) {
    $t->id();
    $t->string('dpt_name')->nullable();
});
Schema::create('company_staff_roles', function (Blueprint $t) {
    $t->id();
    $t->unsignedBigInteger('company_id')->nullable();
    $t->string('name')->nullable();
    $t->string('access_role')->nullable();
});

$app->instance(App\Services\CompanyContext::class, new class extends App\Services\CompanyContext {
    public function __construct() {}
    public function current()
    {
        return new class {
            public int $id = 1;
            public string $name = 'Bengal IT Hub';
            public string $brand_name = 'Bengal IT Hub';
            public ?string $logo = null;
            public ?string $favicon = null;
            public ?object $subscriptions = null;
            public ?string $trial_ends_at = null;
            public function hasFeature($slug) { return true; }
            public function isSuspended() { return false; }
            public function isOnTrial() { return false; }
        };
    }
});

DB::table('modules')->insert([
    ['id' => 1, 'slug' => 'tickets', 'name' => 'Internal Tickets', 'is_active' => 1, 'route_name' => 'tickets.index', 'route_prefix' => 'tickets'],
    ['id' => 2, 'slug' => 'projects', 'name' => 'Projects', 'is_active' => 1, 'route_name' => 'projects.index', 'route_prefix' => 'projects'],
    ['id' => 3, 'slug' => 'tasks', 'name' => 'Tasks', 'is_active' => 1, 'route_name' => 'tasks.index', 'route_prefix' => 'tasks'],
]);

DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin', 'designation' => 'Admin'],
    ['id' => 2, 'company_id' => 1, 'name' => 'Saraswata Chatarjee', 'email' => 'dev@example.com', 'role' => 'employee', 'designation' => 'Developer'],
    ['id' => 3, 'company_id' => 1, 'name' => 'Other Dev', 'email' => 'other@example.com', 'role' => 'employee', 'designation' => 'Developer'],
]);

$employee = User::findOrFail(2);
Auth::guard('web')->setUser($employee);
session(['current_company_id' => 1]);
Notification::fake();

DB::table('tickets')->insert([
    ['id' => 1, 'company_id' => 1, 'requester_id' => 1, 'agent_id' => 2, 'subject' => 'Assigned Ticket', 'description' => 'Work on it', 'status' => 'open', 'priority' => 'high', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 2, 'company_id' => 1, 'requester_id' => 2, 'agent_id' => 3, 'subject' => 'Requested Ticket', 'description' => 'Raised by me', 'status' => 'open', 'priority' => 'medium', 'created_at' => now(), 'updated_at' => now()],
]);

$assert = fn (bool $ok, string $msg) => $ok ?: throw new RuntimeException($msg);
view()->share('errors', new \Illuminate\Support\ViewErrorBag());
$middleware = new EnsureModuleAccess;
$controller = new TicketController;

// 1. Employee with view + edit on tickets: Start Progress sets in_progress (not pending), End sets resolved, Details works;
//    and requester-only ticket (#2) must NOT render Start Progress / End buttons.
$ticketPerm = RolePermission::create(['role' => 'employee', 'module_id' => 1, 'can_view' => true, 'can_edit' => true]);
RolePermission::create(['role' => 'employee', 'module_id' => 2, 'can_view' => true]);
RolePermission::create(['role' => 'employee', 'module_id' => 3, 'can_view' => true]);

$html = view('admin.layout.manu')->render();
$assert(str_contains($html, '#1 Assigned Ticket') && str_contains($html, '#2 Requested Ticket'), 'Expected assigned & requested tickets in dropdown.');
$assert(str_contains($html, 'value="in_progress"'), 'Start Progress button must submit status=in_progress.');
$assert(! str_contains($html, 'value="pending"'), 'Start Progress button must not submit status=pending.');

// Verify #1 has Start Progress + End + Details, while #2 only has Details (no 403 change-status forms).
$parts = explode('#2 Requested Ticket', $html);
$assert(count($parts) === 2, 'Could not locate ticket #2 block.');
$ticket2Block = explode('</li>', $parts[1])[0];
$assert(! str_contains($ticket2Block, 'tickets/change-status'), 'Requester-only ticket #2 must not render change-status forms.');
$assert(str_contains($ticket2Block, route('tickets.show', 2)), 'Requester-only ticket #2 must still render Details link.');

// Verify Start Progress & End & Details actually succeed through EnsureModuleAccess + TicketController for #1.
$routeShow = app('router')->getRoutes()->getByName('tickets.show');
$reqShow = Request::create('/tickets/1', 'GET');
$reqShow->setRouteResolver(fn () => $routeShow->bind($reqShow));
$reqShow->setUserResolver(fn () => $employee);
$resShow = $middleware->handle($reqShow, fn () => response($controller->show(1)));
$assert($resShow->getStatusCode() === 200, 'Assigned ticket Details failed.');

$reqShowRequested = Request::create('/tickets/2', 'GET');
$reqShowRequested->setRouteResolver(fn () => $routeShow->bind($reqShowRequested));
$reqShowRequested->setUserResolver(fn () => $employee);
$resShowRequested = $middleware->handle($reqShowRequested, fn () => response($controller->show(2)));
$assert($resShowRequested->getStatusCode() === 200, 'Requester-only ticket Details failed.');

$routeStatus = app('router')->getRoutes()->getByName('tickets.change-status');
$reqProgress = Request::create('/tickets/change-status', 'POST', ['ticketId' => 1, 'status' => 'in_progress'], [], [], ['HTTP_ACCEPT' => 'application/json']);
$reqProgress->setRouteResolver(fn () => $routeStatus->bind($reqProgress));
$reqProgress->setUserResolver(fn () => $employee);
$resProgress = $middleware->handle($reqProgress, fn ($r) => $controller->changeStatus($r));
$assert($resProgress->getStatusCode() === 200 && Ticket::findOrFail(1)->status === 'in_progress', 'Start Progress failed to set in_progress.');

$reqEnd = Request::create('/tickets/change-status', 'POST', ['ticketId' => 1, 'status' => 'resolved'], [], [], ['HTTP_ACCEPT' => 'application/json']);
$reqEnd->setRouteResolver(fn () => $routeStatus->bind($reqEnd));
$reqEnd->setUserResolver(fn () => $employee);
$resEnd = $middleware->handle($reqEnd, fn ($r) => $controller->changeStatus($r));
$assert($resEnd->getStatusCode() === 200 && Ticket::findOrFail(1)->status === 'resolved', 'End failed to set resolved.');

// Reset ticket #1 to open, then test view-only permission (can_edit = false): Details works, Start Progress & End hidden.
Ticket::whereKey(1)->update(['status' => 'open']);
$ticketPerm->update(['can_edit' => false]);
$employeeViewOnly = User::findOrFail(2);
Auth::guard('web')->setUser($employeeViewOnly);
$htmlViewOnly = view('admin.layout.manu')->render();
$assert(! str_contains($htmlViewOnly, 'tickets/change-status'), 'View-only employee must not see Start Progress / End buttons.');
$assert(str_contains($htmlViewOnly, route('tickets.show', 1)), 'View-only employee must still see Details link.');

// Test tickets view permission disabled (can_view = false): ticket items hidden from Assigned Work.
$ticketPerm->update(['can_view' => false]);
Auth::guard('web')->setUser(User::findOrFail(2));
$htmlNoView = view('admin.layout.manu')->render();
$assert(! str_contains($htmlNoView, '#1 Assigned Ticket') && ! str_contains($htmlNoView, route('tickets.show', 1)), 'Ticket links shown when tickets module view is disabled.');

echo "PASS: Assigned Work ticket Start Progress (in_progress), End (resolved), Details, requester-only protection, and module permission guards verified.\n";
