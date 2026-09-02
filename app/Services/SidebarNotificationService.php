<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTimer;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class SidebarNotificationService
{
    public static function forUser(User $user): array
    {
        $role = strtolower((string) $user->role);
        $isReviewer = in_array($role, ['admin', 'hr', 'manager'], true);
        $items = [];

        try {
            $items['notifications'] = self::item($user->unreadNotifications()->count(), 'unread');
        } catch (\Throwable $e) {
            $items['notifications'] = self::item(0, 'unread');
        }

        if ($isReviewer) {
            try { $pendingLeaves = Leave::where('status', 'pending')->whereNull('archived_at')->count(); } catch (\Throwable $e) { $pendingLeaves = 0; }
            try {
                $pendingTimers = TaskTimer::where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'pending');
                })->whereNotNull('end_time')->count();
            } catch (\Throwable $e) { $pendingTimers = 0; }
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'status')) {
                    $openTickets = Ticket::whereIn('status', ['open', 'pending'])->count();
                } else {
                    $openTickets = Ticket::count();
                }
            } catch (\Throwable $e) { $openTickets = 0; }
            try {
                $overdueProjects = Project::whereNotNull('deadline')
                    ->whereDate('deadline', '<', Carbon::today())
                    ->whereNotIn('status', ['completed'])
                    ->count();
            } catch (\Throwable $e) { $overdueProjects = 0; }
            try {
                $overdueTasks = Task::whereNotNull('due_date')
                    ->whereDate('due_date', '<', Carbon::today())
                    ->whereNotIn('status', ['Completed', 'completed'])
                    ->count();
            } catch (\Throwable $e) { $overdueTasks = 0; }
            try {
                $inactiveEmployees = User::where('role', 'employee')
                    ->where(function ($query) {
                        $query->where('is_active', false)
                            ->orWhere('login_allowed', false)
                            ->orWhereNotNull('archived_at');
                    })
                    ->count();
            } catch (\Throwable $e) { $inactiveEmployees = 0; }

            $items['employees'] = self::item($inactiveEmployees, 'warning');
            $items['attendance'] = self::item(self::missingAttendanceCount(), 'warning');
            $items['leaves'] = self::item($pendingLeaves, 'pending', true);
            $items['projects'] = self::item($overdueProjects, 'issue', true);
            $items['tasks'] = self::item($overdueTasks, 'issue', true);
            $items['timelogs'] = self::item($pendingTimers, 'pending', true);
            $items['tickets'] = self::item($openTickets, 'pending', true);
            $items['holidays'] = self::item(self::upcomingHolidayCount(), 'new');
        } else {
            $today = Carbon::today();
            try {
                $assignedTasks = Task::where(function ($query) use ($user) {
                        $query->whereHas('assignees', function ($assignees) use ($user) {
                            $assignees->where('users.id', $user->id);
                        })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$user->id]);
                    })
                    ->whereNotIn('status', ['Completed', 'completed'])
                    ->count();
            } catch (\Throwable $e) { $assignedTasks = 0; }

            try {
                $overdueTasks = Task::where(function ($query) use ($user) {
                        $query->whereHas('assignees', function ($assignees) use ($user) {
                            $assignees->where('users.id', $user->id);
                        })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$user->id]);
                    })
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', $today)
                    ->whereNotIn('status', ['Completed', 'completed'])
                    ->count();
            } catch (\Throwable $e) { $overdueTasks = 0; }

            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'status')) {
                    $activeTickets = Ticket::where(function ($query) use ($user) {
                            $query->where('agent_id', $user->id)->orWhere('requester_id', $user->id);
                        })
                        ->whereIn('status', ['open', 'pending'])
                        ->count();
                } else {
                    $activeTickets = Ticket::where('agent_id', $user->id)->orWhere('requester_id', $user->id)->count();
                }
            } catch (\Throwable $e) { $activeTickets = 0; }

            try {
                $myPendingLeaves = Leave::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->whereNull('archived_at')
                    ->count();
            } catch (\Throwable $e) { $myPendingLeaves = 0; }

            try {
                $myRejectedLeaves = Leave::where('user_id', $user->id)
                    ->where('status', 'rejected')
                    ->whereNull('archived_at')
                    ->count();
            } catch (\Throwable $e) { $myRejectedLeaves = 0; }

            try {
                $myOpenTimers = TaskTimer::where('user_id', $user->id)
                    ->whereNull('end_time')
                    ->count();
            } catch (\Throwable $e) { $myOpenTimers = 0; }

            try {
                $todayAttendanceCount = Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->whereNotNull('clock_in')
                    ->count();
            } catch (\Throwable $e) { $todayAttendanceCount = 0; }

            try {
                $assignedProjects = Project::where(function ($query) use ($user) {
                        $query->whereHas('users', function ($members) use ($user) {
                            $members->where('users.id', $user->id);
                        })->orWhereHas('tasks', function ($taskQuery) use ($user) {
                            $taskQuery->whereHas('assignees', function ($assignees) use ($user) {
                                $assignees->where('users.id', $user->id);
                            })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$user->id]);
                        });
                    })
                    ->whereNotIn('status', ['completed'])
                    ->count();
            } catch (\Throwable $e) { $assignedProjects = 0; }

            $items['attendance'] = self::item($todayAttendanceCount > 0 ? 0 : 1, 'warning', $todayAttendanceCount === 0);
            $items['projects'] = self::item($assignedProjects, 'new');
            $items['tasks'] = self::item($assignedTasks, $overdueTasks > 0 ? 'issue' : 'pending', $overdueTasks > 0);
            $items['tickets'] = self::item($activeTickets, 'pending', true);
            $items['leaves'] = self::item($myPendingLeaves + $myRejectedLeaves, $myRejectedLeaves > 0 ? 'issue' : 'pending', $myRejectedLeaves > 0);
            $items['timelogs'] = self::item($myOpenTimers, 'warning', true);
            $items['holidays'] = self::item(self::upcomingHolidayCount(), 'new');
        }

        $items['hr'] = self::aggregate($items, ['employees', 'attendance', 'leaves', 'holidays']);
        $items['work'] = self::aggregate($items, ['projects', 'tasks', 'timelogs']);
        $items['reports'] = self::aggregate($items, ['attendance', 'leaves', 'timelogs']);

        return $items;
    }

    private static function item(int $count, string $type = 'new', bool $important = false): array
    {
        return [
            'count' => max(0, $count),
            'type' => $type,
            'important' => $important && $count > 0,
        ];
    }

    private static function aggregate(array $items, array $keys): array
    {
        $count = 0;
        $important = false;
        $type = 'new';

        foreach ($keys as $key) {
            $item = $items[$key] ?? self::item(0);
            $count += (int) $item['count'];
            $important = $important || (bool) $item['important'];
            if (($item['count'] ?? 0) > 0 && in_array($item['type'], ['issue', 'warning', 'pending'], true)) {
                $type = $item['type'];
            }
        }

        return self::item($count, $type, $important);
    }

    private static function missingAttendanceCount(): int
    {
        $employeeCount = User::where('role', 'employee')->count();
        $attendanceCount = Attendance::whereDate('date', Carbon::today())->distinct('user_id')->count('user_id');

        return max(0, $employeeCount - $attendanceCount);
    }

    private static function upcomingHolidayCount(): int
    {
        return Holiday::whereNull('archived_at')
            ->whereBetween('date', [Carbon::today()->toDateString(), Carbon::today()->addDays(14)->toDateString()])
            ->count();
    }
}
