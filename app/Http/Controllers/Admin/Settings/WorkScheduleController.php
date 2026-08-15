<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\SystemNotificationService;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $settings = [
            'working_days' => json_decode(AppSetting::valueFor('work_working_days', '["Monday","Tuesday","Wednesday","Thursday","Friday"]'), true) ?? ["Monday","Tuesday","Wednesday","Thursday","Friday"],
            'work_start_time' => AppSetting::valueFor('work_start_time', '09:00'),
            'work_end_time' => AppSetting::valueFor('work_end_time', '18:00'),
            'break_duration' => AppSetting::valueFor('work_break_duration', '60'),
            'shift_enabled' => AppSetting::valueFor('work_shift_enabled', '0'),
            'special_days' => json_decode(AppSetting::valueFor('work_special_days', '[]'), true) ?? [],
            'employee_modes' => json_decode(AppSetting::valueFor('work_employee_modes', '{}'), true) ?? [],
        ];

        $employees = User::whereIn('role', ['employee', 'hr', 'manager'])
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderBy('name', 'asc')
            ->get();
        $isAdmin = auth()->user()?->role === 'admin';

        return view('admin.settings.work-schedule', compact('settings', 'employees', 'isAdmin'));
    }

    public function update(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized. Only Administrators can update the Work Schedule.');
        }
        $request->validate([
            'working_days' => 'required|array',
            'work_start_time' => 'required',
            'work_end_time' => 'required',
            'break_duration' => 'required|numeric',
        ]);

        AppSetting::updateOrCreate(['key' => 'work_working_days'], [
            'label' => 'Working Days',
            'value' => json_encode($request->working_days),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        AppSetting::updateOrCreate(['key' => 'work_start_time'], [
            'label' => 'Work Start Time',
            'value' => $request->work_start_time,
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'text'
        ]);

        AppSetting::updateOrCreate(['key' => 'work_end_time'], [
            'label' => 'Work End Time',
            'value' => $request->work_end_time,
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'text'
        ]);

        AppSetting::updateOrCreate(['key' => 'work_break_duration'], [
            'label' => 'Break Duration (Mins)',
            'value' => $request->break_duration,
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'number'
        ]);

        AppSetting::updateOrCreate(['key' => 'work_shift_enabled'], [
            'label' => 'Enable Shift Management',
            'value' => $request->has('shift_enabled') ? '1' : '0',
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'checkbox'
        ]);

        // Broadcast notification about company working days schedule update
        try {
            $daysStr = implode(', ', $request->working_days);
            SystemNotificationService::notifyAllRoles(
                "Company Operating Days Schedule Updated",
                "Admin has set the official company normal operating working days to: {$daysStr}.",
                route('admin.settings.work-schedule'),
                ['working_days' => $request->working_days]
            );
        } catch (\Throwable $e) {
            \Log::error('Work schedule update notification error: ' . $e->getMessage());
        }

        return back()->with('success', 'Company operating working days and shift settings updated successfully! Notification sent to all staff.');
    }

    public function addSpecialDay(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized. Only Administrators can declare special days.');
        }

        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:holiday,wfh',
            'title' => 'required|string|max:255',
        ]);

        $specialDays = json_decode(AppSetting::valueFor('work_special_days', '[]'), true) ?? [];

        $newId = uniqid('sd_');
        $typeLabel = $request->type === 'holiday' ? 'Special Holiday' : 'Global Work From Home (WFH)';
        $formattedDate = date('M d, Y', strtotime($request->date));

        $newSpecialDay = [
            'id' => $newId,
            'date' => $request->date,
            'type' => $request->type,
            'title' => trim($request->title),
            'created_at' => now()->toDateTimeString(),
        ];

        // Replace if date already exists or push new
        $existingIndex = -1;
        foreach ($specialDays as $idx => $day) {
            if (($day['date'] ?? '') === $request->date) {
                $existingIndex = $idx;
                break;
            }
        }

        if ($existingIndex >= 0) {
            $specialDays[$existingIndex] = $newSpecialDay;
        } else {
            $specialDays[] = $newSpecialDay;
        }

        // Sort by date ascending
        usort($specialDays, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        AppSetting::updateOrCreate(['key' => 'work_special_days'], [
            'label' => 'Special Day Overrides',
            'value' => json_encode(array_values($specialDays)),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        // Dispatch Notification to Each Employee, HR, and Manager
        try {
            $notificationTitle = "Announcement: {$typeLabel} on {$formattedDate}";
            $notificationMsg = "Admin has scheduled {$formattedDate} as a {$typeLabel} ({$request->title}) for all employees, HR, and managers.";

            SystemNotificationService::notifyAllRoles(
                $notificationTitle,
                $notificationMsg,
                route('admin.settings.work-schedule'),
                [
                    'special_day_date' => $request->date,
                    'special_day_type' => $request->type,
                    'special_day_title' => $request->title,
                ]
            );
        } catch (\Throwable $e) {
            \Log::error('Work schedule special day notification error: ' . $e->getMessage());
        }

        return back()->with('success', "Special Day ({$typeLabel}) announced for {$formattedDate}! Notification sent to all employees, HR, and managers.");
    }

    public function deleteSpecialDay($id)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized. Only Administrators can remove special day overrides.');
        }

        $specialDays = json_decode(AppSetting::valueFor('work_special_days', '[]'), true) ?? [];
        $filtered = array_values(array_filter($specialDays, function ($item) use ($id) {
            return ($item['id'] ?? '') !== $id;
        }));

        AppSetting::updateOrCreate(['key' => 'work_special_days'], [
            'label' => 'Special Day Overrides',
            'value' => json_encode($filtered),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        return back()->with('success', 'Special Day override removed successfully.');
    }

    public function updateEmployeeMode(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only Administrators can update work location settings.'], 403);
            }
            return back()->with('error', 'Unauthorized. Only Administrators can update work location settings.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mode' => 'required|in:office,wfh',
        ]);

        $employeeModes = json_decode(AppSetting::valueFor('work_employee_modes', '{}'), true) ?? [];
        $employeeModes[$request->user_id] = $request->mode;

        AppSetting::updateOrCreate(['key' => 'work_employee_modes'], [
            'label' => 'Employee Work Modes',
            'value' => json_encode($employeeModes),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        $targetUser = User::find($request->user_id);
        $modeLabel = $request->mode === 'wfh' ? 'Work From Home (WFH)' : 'From Office (WFO)';

        if ($targetUser) {
            // Notify target employee and HR
            try {
                // 1. Notify Particular Employee
                SystemNotificationService::notifyUser(
                    $targetUser,
                    "Work Schedule Mode Updated",
                    "Admin has set your standard work location mode to: {$modeLabel}.",
                    route('dashboard'),
                    ['mode' => $request->mode, 'user_id' => $targetUser->id]
                );

                // 2. Notify HR & Admins
                $hrUsers = SystemNotificationService::adminsAndHr();
                SystemNotificationService::notifyUser(
                    $hrUsers,
                    "Employee Work Location Updated: {$targetUser->name}",
                    "Admin updated work location for {$targetUser->name} ({$targetUser->email}) to {$modeLabel}.",
                    route('admin.settings.work-schedule'),
                    ['mode' => $request->mode, 'target_user_id' => $targetUser->id]
                );
            } catch (\Throwable $e) {
                \Log::error('Employee work mode notification error: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Work location for {$targetUser->name} set to {$modeLabel}. Notification sent to HR and {$targetUser->name}.",
                'mode' => $request->mode,
                'mode_label' => $modeLabel,
            ]);
        }

        return back()->with('success', "Work location for {$targetUser->name} set to {$modeLabel}. Notification sent to HR and {$targetUser->name}.");
    }

    public function bulkUpdateEmployeeModes(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized. Only Administrators can bulk update work location settings.');
        }

        $request->validate([
            'modes' => 'required|array',
            'modes.*' => 'in:office,wfh',
        ]);

        $existingModes = json_decode(AppSetting::valueFor('work_employee_modes', '{}'), true) ?? [];
        $updatedCount = 0;

        foreach ($request->modes as $userId => $newMode) {
            $oldMode = $existingModes[$userId] ?? 'office';
            if ($oldMode !== $newMode) {
                $existingModes[$userId] = $newMode;
                $updatedCount++;

                $targetUser = User::find($userId);
                if ($targetUser) {
                    $modeLabel = $newMode === 'wfh' ? 'Work From Home (WFH)' : 'From Office (WFO)';
                    try {
                        // Notify Employee
                        SystemNotificationService::notifyUser(
                            $targetUser,
                            "Work Schedule Mode Updated",
                            "Admin has updated your standard work location mode to: {$modeLabel}.",
                            route('dashboard')
                        );
                        // Notify HR
                        $hrUsers = SystemNotificationService::adminsAndHr();
                        SystemNotificationService::notifyUser(
                            $hrUsers,
                            "Employee Work Location Updated: {$targetUser->name}",
                            "Admin updated work location for {$targetUser->name} to {$modeLabel}.",
                            route('admin.settings.work-schedule')
                        );
                    } catch (\Throwable $e) {
                        \Log::error('Bulk employee work mode notification error: ' . $e->getMessage());
                    }
                }
            }
        }

        AppSetting::updateOrCreate(['key' => 'work_employee_modes'], [
            'label' => 'Employee Work Modes',
            'value' => json_encode($existingModes),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        return back()->with('success', "Employee work location settings updated successfully. Notifications sent for {$updatedCount} updated employee schedule(s).");
    }
}
