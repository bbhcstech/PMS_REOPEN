<?php

require __DIR__.'/role_permission_toggle_enforcement.php';

try {
    $routeHit = function ($user, string $name) {
        Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
        $route = app('router')->getRoutes()->getByName($name);
        $request = Illuminate\Http\Request::create('/'.$route->uri(), 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setRouteResolver(fn () => $route->bind($request));
        $request->setUserResolver(fn () => $user);
        return (new App\Http\Middleware\EnforceRolePermissionToggles)->handle($request,
            fn ($r) => (new App\Http\Middleware\EnsureModuleAccess)->handle($r, fn () => response('ok')));
    };
    DB::table('modules')->insert([
        ['id' => 100, 'slug' => 'dashboard', 'name' => 'Dashboard', 'route_name' => 'dashboard', 'route_prefix' => 'dashboard'],
        ['id' => 101, 'slug' => 'attendance', 'name' => 'Attendance', 'route_name' => 'attendance.index', 'route_prefix' => 'attendance'],
    ]);
    DB::table('users')->insert([
        ['id' => 4, 'company_id' => 3, 'name' => 'Employee', 'role' => 'employee'],
        ['id' => 5, 'company_id' => 3, 'name' => 'HR', 'role' => 'hr'],
    ]);
    foreach (['employee', 'hr', 'manager', $role->permissionKey()] as $key) {
        App\Models\RolePermission::updateOrCreate(['role' => $key, 'module_id' => 100], ['can_view' => true, 'can_create' => false, 'can_edit' => false]);
        App\Models\RolePermission::updateOrCreate(['role' => $key, 'module_id' => 101], ['can_view' => false, 'can_create' => false, 'can_edit' => false]);
    }
    foreach ([4, 5, 3, 2] as $id) {
        foreach (['dashboard.clockin', 'dashboard.clockout'] as $name) {
            $response = $routeHit($fresh($id), $name);
            check($allowed($response), "Own clock action {$name} blocked for account {$id}: ".$response->getContent());
        }
        check(!$fresh($id)->moduleToggleAllows('attendance', 'create') && !$fresh($id)->moduleToggleAllows('attendance', 'edit'), 'Self clock fix granted attendance management privileges.');
    }
    check($routeHit($fresh(4), 'events.store')->getStatusCode() === 403, 'Unrelated module create permission was bypassed.');
    echo "PASS: employee/HR/manager/custom-role own clock actions work with view-only Dashboard and no attendance management permission; other CRUD permissions remain denied.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage()."\n"); exit(1);
}
