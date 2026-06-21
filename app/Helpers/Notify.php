<?php

namespace App\Helpers;

use App\Models\User;
use App\Services\SystemNotificationService;

class Notify
{
    /**
     * Notify when EMPLOYEE clocks in (notify all admins)
     */
    public static function employeeClockedIn($attendance)
    {
        $employee = $attendance->user;
        SystemNotificationService::notifyAllRoles(
            'Clock In Recorded',
            ($employee?->name ?? 'Employee') . ' clocked in.',
            route('attendance.index'),
            [
                'type' => 'clock_in',
                'record_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'employee_id' => $attendance->user_id,
                'icon' => 'fa-clock',
                'color' => 'success',
            ],
            $employee?->company_id
        );

        return true;
    }

    /**
     * Notify when ADMIN assigns task (notify employee)
     */
    public static function taskAssigned($task, $admin, $employeeId)
    {
        $employee = User::find($employeeId);
        if (!$employee) return false;

        SystemNotificationService::notifyAllRoles(
            'Task Assigned',
            ($admin?->name ?? 'Someone') . ' assigned "' . $task->title . '" to ' . $employee->name . '.',
            route('tasks.show', $task->id),
            [
                'type' => 'task_assigned',
                'task_id' => $task->id,
                'project_id' => $task->project_id ?? null,
                'employee_id' => $employee->id,
                'icon' => 'fa-tasks',
                'color' => 'primary',
            ],
            $employee->company_id ?? $admin?->company_id
        );

        return true;
    }

    /**
     * Notify when EMPLOYEE creates ticket (notify all admins)
     */
    public static function ticketCreated($ticket, $employee)
    {
        SystemNotificationService::notifyAllRoles(
            'Ticket Raised',
            ($employee?->name ?? 'Someone') . ' raised ticket #' . $ticket->id . ': ' . ($ticket->subject ?? 'Ticket'),
            route('tickets.show', $ticket->id),
            [
                'type' => 'ticket_created',
                'ticket_id' => $ticket->id,
                'project_id' => $ticket->project_id ?? null,
                'employee_id' => $employee?->id,
                'icon' => 'fa-ticket',
                'color' => 'info',
            ],
            $employee?->company_id
        );

        return true;
    }

    /**
     * Notify when ADMIN updates employee info
     */
    public static function employeeUpdated($employee, $admin, $changes = [])
    {
        $notificationData = [
            'type' => 'employee_updated',
            'title' => 'Profile Updated',
            'message' => 'Your profile has been updated by admin',
            'changes' => $changes,
            'updated_by' => $admin->name,
            'url' => url('/employee/profile'),
            'icon' => 'fa-user-edit',
            'color' => 'warning',
        ];

        SystemNotificationService::notifyAllRoles(
            $notificationData['title'],
            $notificationData['message'],
            $notificationData['url'],
            $notificationData,
            $employee?->company_id ?? $admin?->company_id
        );

        return true;
    }

    /**
     * Create a custom notification
     */
    public static function custom($user, $data)
    {
        $defaults = [
            'type' => 'custom',
            'title' => 'Notification',
            'message' => '',
            'url' => '#',
            'icon' => 'fa-bell',
            'color' => 'primary',
        ];

        $payload = array_merge($defaults, $data);
        SystemNotificationService::notifyAllRoles(
            $payload['title'],
            $payload['message'],
            $payload['url'],
            $payload,
            $user?->company_id
        );

        return true;
    }

    /**
     * Notify multiple users
     */
    public static function broadcast($users, $data)
    {
        foreach ($users as $user) {
            self::custom($user, $data);
        }

        return true;
    }
}
