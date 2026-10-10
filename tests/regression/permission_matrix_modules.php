<?php

require __DIR__ . '/role_permissions_save.php';

use App\Models\{RolePermission, User};
use App\Services\AdminWorkspaceModules;
use Illuminate\Support\Facades\{Auth, DB};

Auth::guard('web')->setUser(new User(['company_id' => 1, 'role' => 'admin']));
$rows = [
    ['id' => 171, 'slug' => 'bye', 'name' => 'bye', 'route_name' => 'projects.index', 'parent_id' => null],
    ['id' => 172, 'slug' => 'kjm', 'name' => 'kjm', 'route_name' => null, 'parent_id' => 173],
    ['id' => 173, 'slug' => 'activity-logs', 'name' => 'Activity Logs', 'route_name' => null, 'parent_id' => null],
    ['id' => 174, 'slug' => 'random-test-module', 'name' => 'Random test module', 'route_name' => 'nonexistent.index', 'parent_id' => null],
    ['id' => 175, 'slug' => 'documents', 'name' => 'Documents', 'route_name' => null, 'parent_id' => null],
    ['id' => 176, 'slug' => 'my-documents', 'name' => 'My Documents', 'route_name' => null, 'parent_id' => null],
    ['id' => 177, 'slug' => 'community', 'name' => 'Community', 'route_name' => null, 'parent_id' => null],
    ['id' => 178, 'slug' => 'dashboard', 'name' => 'Dashboard', 'route_name' => null, 'parent_id' => null],
    ['id' => 179, 'slug' => 'leave-management', 'name' => 'Leave Management', 'route_name' => 'leaves.index', 'parent_id' => null],
];
foreach (['central', 'tenant'] as $connection) DB::connection($connection)->table('modules')->insert($rows);
$ids = app(AdminWorkspaceModules::class)->permissionMatrixModules()->pluck('id')->all();
foreach ([171, 172, 173, 174] as $id) if (in_array($id, $ids, true)) throw new RuntimeException('Invalid or non-menu module visible.');
foreach ([175, 176, 177, 178, 179] as $id) if (!in_array($id, $ids, true)) throw new RuntimeException('Real feature hidden: ' . $id);
DB::table('role_permissions')->insert(['role' => 'hr', 'module_id' => 171, 'can_view' => true]);
$send('hr', [175 => ['view']]);
if (!RolePermission::where('role', 'hr')->where('module_id', 171)->first()->can_view) throw new RuntimeException('Hidden permissions were changed.');
if (DB::connection('central')->table('modules')->whereIn('id', [171,172,174])->count() !== 3) throw new RuntimeException('Display filter deleted registry records.');
echo "PASS: dummy/unimplemented modules hidden, real Admin features retained and hidden registry/permission records preserved.\n";
