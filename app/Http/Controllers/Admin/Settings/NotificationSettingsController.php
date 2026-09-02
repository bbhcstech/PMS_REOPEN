<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class NotificationSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'email_notifications' => AppSetting::valueFor('notif_email_enabled', '1'),
            'system_notifications' => AppSetting::valueFor('notif_system_enabled', '1'),
            'task_assignment_alert' => AppSetting::valueFor('notif_task_assignment', '1'),
            'leave_request_alert' => AppSetting::valueFor('notif_leave_request', '1'),
            'attendance_reminder' => AppSetting::valueFor('notif_attendance_reminder', '1'),
            'daily_summary_digest' => AppSetting::valueFor('notif_daily_summary', '0'),
        ];

        return view('admin.settings.notification', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can update notification settings.');
        }

        $data = [
            'email_enabled' => $request->has('email_notifications') ? '1' : '0',
            'system_enabled' => $request->has('system_notifications') ? '1' : '0',
            'task_assignment' => $request->has('task_assignment_alert') ? '1' : '0',
            'leave_request' => $request->has('leave_request_alert') ? '1' : '0',
            'attendance_reminder' => $request->has('attendance_reminder') ? '1' : '0',
            'daily_summary' => $request->has('daily_summary_digest') ? '1' : '0',
        ];

        foreach ($data as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'notif_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $val,
                    'page' => 'notification-settings',
                    'section' => 'Notification',
                    'type' => 'checkbox'
                ]
            );
        }

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Notification Preferences Updated',
            'System alert preferences and notification rules have been updated by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('admin.settings.notification'),
            [
                'type' => 'setting_update',
                'setting_module' => 'notification-settings',
                'icon' => 'fa-bell',
                'color' => 'warning',
            ]
        );

        return back()->with('success', 'Notification settings updated successfully!');
    }
}
