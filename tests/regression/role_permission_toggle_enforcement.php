<?php

// Run with: php tests/regression/role_permission_toggle_enforcement.php
// Admin > Roles & Permissions toggles must hide/block modules for company staff roles
// (e.g. "Associate Manager") and the built-in roles, on routes outside "module.access" too.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Middleware\EnforceRolePermissionToggles;
use App\Models\{CompanyStaffRole, RolePermission, User, WorkspaceModule};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'cache.default' => 'array', 'session.driver' => 'array']);
foreach ([
    'users' => 'id integer primary key, company_id integer, name text, email text, role text, company_staff_role_id integer, created_at text, updated_at text',
    'company_staff_roles' => 'id integer primary key, company_id integer, name text, access_role text, created_at text, updated_at text',
    'modules' => 'id integer primary key, slug text, name text, is_active integer default 1, route_name text, route_prefix text, parent_id integer, sort_order integer default 0',
    'role_permissions' => 'id integer primary key, role text, module_id integer, can_view integer default 0, can_create integer default 0, can_edit integer default 0, can_delete integer default 0, can_approve integer default 0, can_export integer default 0, can_assign integer default 0, created_at text, updated_at text',
] as $table => $columns) {
    DB::statement("CREATE TABLE {$table} ({$columns})");
}
$app->instance(App\Services\CompanyContext::class, new class extends App\Services\CompanyContext {
    public function __construct() {}
    public function current() { return new class { public function hasFeature($slug) { return true; } }; }
});
function check(bool $ok, string $message): void { if (! $ok) throw new RuntimeException($message); }

$slugs = ['events' => 'events.index', 'company-assistant' => 'company.ai.index', 'leads-contacts' => 'leads.contacts.index', 'community' => 'community.index', 'tasks' => 'tasks.index'];
foreach (array_keys($slugs) as $i => $slug) {
    DB::table('modules')->insert(['id' => 40 + $i, 'slug' => $slug, 'name' => $slug, 'route_name' => $slugs[$slug]]);
}
$role = CompanyStaffRole::create(['company_id' => 3, 'name' => 'Associate Manager', 'access_role' => 'manager']);
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 3, 'name' => 'Admin', 'role' => 'admin', 'company_staff_role_id' => null],
    ['id' => 2, 'company_id' => 3, 'name' => 'Assoc', 'role' => 'manager', 'company_staff_role_id' => $role->id],
    ['id' => 3, 'company_id' => 3, 'name' => 'Plain manager', 'role' => 'manager', 'company_staff_role_id' => null],
]);
$save = function (string $roleKey, array $on) {
    // Same shape as RolePermissionController::update: one row per module, OFF = all false.
    foreach (WorkspaceModule::all() as $module) {
        RolePermission::updateOrCreate(['role' => $roleKey, 'module_id' => $module->id], [
            'can_view' => in_array($module->slug, $on, true), 'can_create' => in_array($module->slug, $on, true),
        ]);
    }
};
$fresh = fn (int $id) => User::find($id); // new instance = new request
$hit = function (User $user, string $routeName, string $method = 'GET', bool $navigate = true) {
    Auth::guard('web')->setUser($user);
    $route = app('router')->getRoutes()->getByName($routeName);
    check((bool) $route, "Route {$routeName} missing");
    $request = Request::create('/' . $route->uri(), $method, [], [], [], $navigate ? ['HTTP_SEC_FETCH_MODE' => 'navigate'] : ['HTTP_ACCEPT' => 'application/json']);
    $request->setRouteResolver(fn () => $route->bind($request));
    $request->setUserResolver(fn () => $user);
    return (new EnforceRolePermissionToggles)->handle($request, fn () => response('ok'));
};
$allowed = fn ($response) => $response->getContent() === 'ok';

// Never configured: unchanged behaviour.
check($fresh(2)->moduleToggleAllows('events'), 'Unconfigured module hidden.');
check($allowed($hit($fresh(2), 'events.index')), 'Unconfigured module blocked.');

// Admin switches Events, Company Assistant and Lead Contact OFF for Associate Manager.
$save($role->permissionKey(), ['tasks', 'community']);
$assoc = $fresh(2);
foreach (['events', 'company-assistant', 'leads-contacts'] as $slug) {
    check(! $assoc->moduleToggleAllows($slug), "Sidebar still shows {$slug} for Associate Manager.");
}
check($assoc->moduleToggleAllows('tasks') && $assoc->moduleToggleAllows('community'), 'Enabled module hidden.');
check(! $allowed($response = $hit($fresh(2), 'events.index')) && $response->isRedirect(route('dashboard')), 'Events page reachable when OFF.');
check($hit($fresh(2), 'company.ai.ask', 'POST', false)->getStatusCode() === 403, 'Company Assistant API reachable when OFF.');
check($hit($fresh(2), 'leads.contacts.store', 'POST', false)->getStatusCode() === 403, 'Lead contact API reachable when OFF.');
check($allowed($hit($fresh(2), 'community.index')), 'Enabled module blocked.');
check($allowed($hit($fresh(1), 'events.index')), 'Admin blocked.');
check($allowed($hit($fresh(3), 'events.index')), 'Associate Manager settings leaked to plain managers.');

// View-only: page opens, creating is refused.
RolePermission::where('role', $role->permissionKey())->where('module_id', WorkspaceModule::where('slug', 'events')->value('id'))->update(['can_view' => 1, 'can_create' => 0]);
check($allowed($hit($fresh(2), 'events.index')), 'View permission ignored.');
check($hit($fresh(2), 'events.store', 'POST', false)->getStatusCode() === 403, 'Create allowed without permission.');

// Turned back ON: visible and reachable at once.
$save($role->permissionKey(), ['tasks', 'community', 'events', 'company-assistant', 'leads-contacts']);
check($fresh(2)->moduleToggleAllows('events') && $allowed($hit($fresh(2), 'company.ai.ask', 'POST', false)), 'Re-enabled module still blocked.');

// Same for the built-in roles.
$save('manager', ['tasks']);
check(! $fresh(3)->moduleToggleAllows('community') && $hit($fresh(3), 'community.index', 'GET', false)->getStatusCode() === 403, 'Manager toggle not enforced.');

foreach (['admin.layout.manu', 'admin.company-role-dashboard', 'admin.partials.staff-clock'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(view()->getFinder()->find($view))), TOKEN_PARSE);
}
echo "PASS: Associate Manager / role toggles hide modules and block page + API routes outside module.access; view vs create; re-enable; admin and other roles unaffected; unconfigured modules unchanged.\n";
