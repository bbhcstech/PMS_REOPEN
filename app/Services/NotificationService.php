<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    /**
     * Notify ALL Admins when employee does something
     */
    public static function notifyAdmins($title, $message, $url = null, $ticketId = null, $employee = null)
    {
        if (!$employee) {
            $employee = auth()->user();
        }

        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'ticket_id' => $ticketId,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'type' => 'employee_to_admin',
            'icon' => 'fa-user',
            'color' => 'info',
        ];

        SystemNotificationService::notifyAllRoles($title, $message, $url, $data, $employee?->company_id);

        // Create action log for employee
        if ($employee) {
            $employee->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'employee_action',
                'notifiable_type' => User::class,
                'notifiable_id' => $employee->id,
                'data' => array_merge($data, [
                    'action' => 'sent_notification_to_admins',
                    'sent_at' => now()
                ]),
                'read_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Notify ALL Employees when admin does something
     */
    public static function notifyEmployees($title, $message, $url = null, $ticketId = null, $admin = null)
    {
        if (!$admin) {
            $admin = auth()->user();
        }

        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'ticket_id' => $ticketId,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'type' => 'admin_to_employee',
            'icon' => 'fa-shield',
            'color' => 'warning',
        ];

        SystemNotificationService::notifyAllRoles($title, $message, $url, $data, $admin?->company_id);

        // Create action log for admin
        $admin->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'admin_action',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => array_merge($data, [
                'action' => 'sent_notification_to_employees',
                'sent_at' => now()
            ]),
            'read_at' => now(),
        ]);

        return true;
    }

    /**
     * Notify specific user(s)
     */
    public static function notifyUser($userIds, $title, $message, $url = null, $ticketId = null)
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $sender = auth()->user();

        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'ticket_id' => $ticketId,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_type' => $sender->is_admin ? 'admin' : 'employee',
            'type' => 'personal',
            'icon' => 'fa-envelope',
            'color' => 'primary',
        ];

        SystemNotificationService::notifyAllRoles($title, $message, $url, $data, $sender?->company_id);

        return true;
    }

    /**
     * Send general action notification
     */
    public static function sendActionNotification($title, $message, $url = null, $type = 'info')
    {
        $user = auth()->user();

        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'type' => $type,
            'icon' => 'fa-bell',
            'color' => 'secondary',
        ];

        SystemNotificationService::notifyAllRoles($title, $message, $url, $data, $user?->company_id);

        return true;
    }
}
