<?php

require __DIR__ . '/company_custom_role_permissions.php';

use App\Http\Controllers\Admin\RolePermissionController;
use App\Models\{RolePermission, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

config(['database.connections.central' => config('database.connections.tenant')]);
DB::purge('central');
foreach (['tenant', 'central'] as $connection) {
    Schema::connection($connection)->create('modules', function ($t) {
        $t->id(); $t->string('slug'); $t->string('name'); $t->boolean('is_active')->default(true); $t->string('route_name')->nullable(); $t->string('route_prefix')->nullable(); $t->unsignedBigInteger('parent_id')->nullable(); $t->integer('sort_order')->default(0);
    });
    for ($id = 1; $id <= 170; $id++) {
        DB::connection($connection)->table('modules')->insert(['id' => $id, 'slug' => $id === 170 ? 'role-management' : 'module-' . $id, 'name' => 'Module ' . $id, 'route_name' => 'projects.index']);
    }
}
Schema::table('role_permissions', function ($t) {
    $t->unsignedBigInteger('module_id')->nullable();
    foreach (['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'] as $permission) $t->boolean('can_' . $permission)->default(false);
    $t->timestamps();
});
DB::table('role_permissions')->delete();
Auth::guard('web')->setUser(new User(['company_id' => 1, 'role' => 'admin']));
$controller = new RolePermissionController;
$actions = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
$send = function ($role, $permissions) use ($controller) {
    $request = Request::create('/settings/role-permissions', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode(compact('role', 'permissions')));
    return $controller->update($request);
};
foreach (['hr', 'manager', 'employee', $first->permissionKey()] as $role) {
    $response = $send($role, array_fill(1, 170, $actions));
    if ($response->getStatusCode() !== 200 || !$response->getData()->success || $response->headers->has('Location')) throw new RuntimeException('Save redirected or failed: ' . $role);
    if (RolePermission::where('role', $role)->where('can_assign', true)->count() !== 169) throw new RuntimeException('Large matrix truncated or excluded module granted: ' . $role);
}
$send('hr', [1 => ['view'], 169 => ['export']]);
if (!RolePermission::where('role', 'hr')->where('module_id', 169)->first()->can_export || RolePermission::where('role', 'hr')->where('module_id', 2)->first()->can_view) throw new RuntimeException('HR changes did not persist or revocation failed.');
if (!RolePermission::where('role', 'manager')->where('module_id', 2)->first()->can_view) throw new RuntimeException('HR save affected manager.');
try {
    $send('hr', [1 => ['invalid']]);
    throw new RuntimeException('Invalid permission accepted.');
} catch (Illuminate\Validation\ValidationException $e) {}
Auth::guard('web')->setUser(new User(['company_id' => 1, 'role' => 'hr']));
try {
    $send('hr', []);
    throw new RuntimeException('HR actor changed administrator settings.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() !== 403) throw $e;
}
echo "PASS: JSON saves above 1,000 permissions, HR persistence and revocation, other/custom role isolation, exclusions, validation and admin authorization.\n";
