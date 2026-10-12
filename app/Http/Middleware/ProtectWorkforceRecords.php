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
            $isAdmin = WorkforceAccess::isAdmin($actor);
            $isHr = WorkforceAccess::isHr($actor);
            $action = $route->getActionMethod();
            if (($attendance || $leave) && ! $isAdmin) {
                abort_if($request->input('staff_category') === 'authority', 403, 'Higher-level staff records are managed only by the company admin.');
                if (WorkforceAccess::isAuthority($actor) && ! $isHr) {
                    $allowed = $leave ? ['create', 'store'] : ['clockIn'];
                    abort_unless(in_array($action, $allowed, true), 403, 'Your leave and attendance records are managed only by the company admin.');
                }
                if ($leave && $isHr) {
                    abort_if(in_array($action, ['updatePolicy', 'storeLeaveType', 'resetEmployeeLeaves'], true), 403, 'Only the company admin may change leave policy or balances.');
                    $ids = [];
                    foreach (['leave', 'id'] as $parameter) {
                        $value = $route->parameter($parameter);
                        if ($value instanceof \App\Models\Leave) $ids[] = $value->id;
                        elseif (is_numeric($value)) $ids[] = (int) $value;
                    }
                    if ($action === 'updatePaidStatus') $ids[] = $request->integer('leave_id');
                    if (in_array($action, ['bulkArchive', 'bulkAction', 'bulkDelete'], true)) {
                        $ids = array_merge($ids, (array) $request->input('ids', []));
                    }
                    if (! str_contains(strtolower($action), 'apology')) {
                        foreach (\App\Models\Leave::with('user')->whereIn('id', $ids)->get() as $record) {
                            abort_unless($record->user && WorkforceAccess::canAccessRecord($actor, $record->user, $record->staff_category), 403, 'Only the company admin may access higher-level staff leave.');
                        }
                    }
                }
            }
            if ($request->routeIs('admin.authority-attendance', 'admin.authority-leaves')) {
                abort_unless(WorkforceAccess::isAdmin($actor), 403);
            }
            if ($attendance && ! WorkforceAccess::isAdmin($actor)) {
                $action = $route->getActionMethod();
                abort_unless(in_array($action, ['index', 'employeeIndex', 'clockIn', 'showAttendanceDetails', 'photo'], true), 403, 'Only your company admin may manage attendance.');
                if ($action === 'showAttendanceDetails') {
                    $owner = \App\Models\User::findOrFail($request->integer('user_id'));
                    abort_unless(WorkforceAccess::canAccessRecord($actor, $owner), 403);
                }
            }
            if ($clock || ($attendance && $route->getActionMethod() === 'clockIn')) {
                abort_unless(WorkforceAccess::isAuthority($actor) || strtolower((string) $actor->role) === 'employee' || WorkforceAccess::isAdmin($actor), 403);
            }
        }
        return $next($request);
    }
}
