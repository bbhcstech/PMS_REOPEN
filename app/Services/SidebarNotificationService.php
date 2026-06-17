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

        $items['notifications'] = self::item($user->unreadNotifications()->count(), 'unread');

        if ($isReviewer) {
            $pendingLeaves = Leave::where('status', 'pending')->whereNull('archived_at')->count();
            $pendingTimers = TaskTimer::where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'pending');
            })->whereNotNull('end_time')->count();
            $openTickets = Ticket::whereIn('status', ['open', 'pending'])->count();
            $overdueProjects = Project::whereNotNull('deadline')
                ->whereDate('deadline', '<', Carbon::today())
                ->whereNotIn('status', ['completed'])
                ->count();
            $overdueTasks = Task::whereNotNull('due_date')
                ->whereDate('due_date', '<', Carbon::today())
                ->whereNotIn('status', ['Completed', 'completed'])
                ->count();
            $inactiveEmployees = User::where('role', 'employee')
                ->where(function ($query) {
                    $query->where('is_active', false)
                        ->orWhere('login_allowed', false)
                        ->orWhereNotNull('archived_at');
                })
                ->count();

            $items['employees'] = self::item($inactiveEmployees, 'warning');
            $items['attendance'] = self::item(self::missingAttendanceCount(), 'warning');
            $items['leaves'] = self::item($pendingLeaves, 'pending', true);
            $items['projects'] = self::item($overdueProjects, 'issue', true);
            $items['tasks'] = self::item($overdueTasks, 'issue', true);
            $items['timelogs'] = self::item($pendingTimers, 'pending', true);
            $items['tickets'] = self::item($openTickets, 'pending', true);
            $items['holidays'] = self::item(self::upcomingHolidayCount(), 'new');
        } else {
            $assignedTasks = Task::where(function ($query) use ($user) {
                    $query->whereHas('assignees', function ($assignees) use ($user) {
                        $assignees->where('users.id', $user->id);
                    })->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$user->id]);
                })
                ->whereNotIn('status', ['Completed', 'completed'])
                ->count();

            $activeTickets = Ticket::where(function ($query) use ($user) {
                    $query->where('agent_id', $user->id)->orWhere('requester_id', $user->id);
                })
                ->whereIn('status', ['open', 'pending'])
                ->count();

            $myPendingLeaves = Leave::where('user_id', $user->id)
                ->where('status', 'pending')
                ->whereNull('archived_at')
                ->count();

            $myRejectedLeaves = Leave::where('user_id', $user->id)
                ->where('status', 'rejected')
                ->whereNull('archived_at')
                ->count();

            $myOpenTimers = TaskTimer::where('user_id', $user->id)
                ->whereNull('end_time')
                ->count();

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

            $items['projects'] = self::item($assignedProjects, 'new');
            $items['tasks'] = self::item($assignedTasks, 'pending', true);
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
