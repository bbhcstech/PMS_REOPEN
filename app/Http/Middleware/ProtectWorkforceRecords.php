<?php

namespace App\Http\Middleware;

use App\Services\{WorkforceAccess, WorkforceRecordSchema};
use Closure;
use Illuminate\Http\Request;

class ProtectWorkforceRecords
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $controller = $route?->getActionName() ?? '';
        $attendance = str_contains($controller, 'AttendanceController@') || $request->routeIs('attendance.*');
        $leave = str_contains($controller, 'LeaveController@');
        $clock = $request->routeIs('dashboard.clockin', 'dashboard.clockout');
        if (($attendance || $leave || $clock) && $request->user()) {
            $actor = WorkforceAccess::authorizeActor();
            WorkforceRecordSchema::ensure();
            if ($request->routeIs('admin.authority-attendance', 'admin.authority-leaves')) {
                abort_unless(WorkforceAccess::isAdmin($actor), 403);
            }
            if ($attendance && ! WorkforceAccess::isAdmin($actor)) {
                $action = $route->getActionMethod();
                abort_unless(in_array($action, ['index', 'employeeIndex', 'clockIn', 'showAttendanceDetails', 'photo'], true), 403, 'Only your company admin may manage attendance.');
                if ($action === 'showAttendanceDetails') abort_if($request->integer('user_id') !== (int) $actor->id, 403);
            }
            if ($clock || ($attendance && $route->getActionMethod() === 'clockIn')) {
                abort_unless(WorkforceAccess::isAuthority($actor) || strtolower((string) $actor->role) === 'employee' || WorkforceAccess::isAdmin($actor), 403);
            }
        }
        return $next($request);
    }
}
