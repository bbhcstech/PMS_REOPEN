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
            $hrReport = $isHr && $attendance
                && in_array($action, ['hrAttendance', 'index', 'employeeIndex', 'attendanceReport', 'authorityIndex'], true);
            if (($attendance || $leave) && ! $isAdmin) {
                abort_if(! $hrReport && $request->input('staff_category') === 'authority', 403, 'Higher-level staff records are managed only by the company admin.');
                if (WorkforceAccess::isAuthority($actor) && ! $isHr) {
                    $allowed = $leave ? ['create', 'store', 'index', 'myLeaves', 'show'] : ['index', 'employeeIndex', 'clockIn', 'showAttendanceDetails', 'photo'];
                    abort_unless(in_array($action, $allowed, true), 403, 'Your leave and attendance records are managed only by the company admin.');
                }
                if ($leave && $isHr) {
                    abort_unless(in_array($action, [
                        'index', 'myLeaves', 'leaveReport', 'export', 'calendar', 'calendarData',
                        'create', 'store', 'show', 'apologyLetters', 'showApologyLetter',
                        'createApologyLetter', 'storeApologyLetter', 'downloadApologySample',
                    ], true), 403, 'Only the company admin may manage leave records.');
                    if ($action === 'show') {
                        $record = $route->parameter('leave');
                        $owner = $record instanceof \App\Models\Leave ? $record->user_id : \App\Models\Leave::findOrFail($record)->user_id;
                        abort_unless((int) $owner === (int) $actor->id, 403);
                    }
                }
            }
            if ($request->routeIs('admin.authority-leaves') || ($request->routeIs('admin.authority-attendance') && ! $hrReport)) {
                abort_unless($isAdmin, 403);
            }
            if ($attendance && ! $isAdmin) {
                $allowed = ['index', 'employeeIndex', 'clockIn', 'showAttendanceDetails', 'photo'];
                abort_unless($hrReport || in_array($action, $allowed, true), 403, 'Only your company admin may manage attendance.');
                if ($action === 'showAttendanceDetails') {
                    $ownerId = $request->integer('user_id');
                    if ($isHr) {
                        abort_unless($ownerId === (int) $actor->id, 403);
                    } else {
                        $owner = \App\Models\User::findOrFail($ownerId);
                        abort_unless((int) $owner->id === (int) $actor->id || WorkforceAccess::canAccessRecord($actor, $owner), 403);
                    }
                }
            }
            if ($clock || ($attendance && $route->getActionMethod() === 'clockIn')) {
                abort_unless(WorkforceAccess::isAuthority($actor) || strtolower((string) $actor->role) === 'employee' || WorkforceAccess::isAdmin($actor), 403);
            }
        }
        return $next($request);
    }
}
