<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\SystemNotificationService;
use App\Services\WorkScheduleService;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $settings = WorkScheduleService::getSettings();

        $employees = User::whereIn('role', ['employee', 'hr', 'manager'])
            ->where('company_id', auth()->user()?->company_id)
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderBy('name', 'asc')
            ->get();
        $isAdmin = \App\Services\WorkforceAccess::isAdmin(auth()->user());

        return view('admin.settings.work-schedule', compact('settings', 'employees', 'isAdmin'));
    }

    public function update(Request $request)
    {
        abort_unless(\App\Services\WorkforceAccess::isAdmin(auth()->user()) && auth()->user()?->company_id, 403);
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weekly = null;
        if ($request->has('weekly_modes')) {
            $rules = ['weekly_modes' => ['required', 'array:'.implode(',', $weekdays)]];
            foreach ($weekdays as $day) $rules['weekly_modes.'.$day] = ['required', 'in:office,wfh,weekend'];
            $weekly = $request->validate($rules)['weekly_modes'];
            $request->merge(['working_days' => array_keys(array_filter($weekly, fn ($mode) => $mode !== 'weekend'))]);
        }
        $request->validate([
            'working_days' => 'required|array|min:1|max:7',
            'working_days.*' => ['required', 'distinct', \Illuminate\Validation\Rule::in($weekdays)],
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i',
            'break_duration' => 'required|integer|between:0,240',
        ]);

        \Illuminate\Support\Facades\DB::connection('tenant')->transaction(function () use ($request, $weekly) {
        if (isset($weekly)) {
            AppSetting::updateOrCreate(['key' => 'work_weekly_wfh_days'], [
                'label' => 'Recurring Weekly WFH Days',
                'value' => json_encode(array_keys(array_filter($weekly, fn ($mode) => $mode === 'wfh'))),
                'page' => 'work-schedule', 'section' => 'Work Schedule', 'type' => 'json',
            ]);
        } else {
            // Legacy working-day forms must not leave WFH assigned to a day off.
            $wfh = array_values(array_intersect(WorkScheduleService::getSettings()['weekly_wfh_days'], $request->working_days));
            AppSetting::updateOrCreate(['key' => 'work_weekly_wfh_days'], [
                'label' => 'Recurring Weekly WFH Days', 'value' => json_encode($wfh),
                'page' => 'work-schedule', 'section' => 'Work Schedule', 'type' => 'json',
            ]);
        }
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
        });

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

    public function addEmployeeWfhDate(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only Administrators can assign WFH dates.'], 403);
            }
            return back()->with('error', 'Unauthorized. Only Administrators can assign WFH dates.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $entry = WorkScheduleService::assignEmployeeWfhDate((int)$request->user_id, $request->date, $request->reason ?? '');

        $targetUser = User::find($request->user_id);
        $name = $targetUser ? $targetUser->name : 'Employee';
        $formattedDate = date('M d, Y', strtotime($request->date));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Assigned Work From Home to {$name} on {$formattedDate}. Notifications sent.",
                'entry' => $entry
            ]);
        }

        return back()->with('success', "Assigned Work From Home to {$name} on {$formattedDate}. Notifications sent to employee and HR.");
    }

    public function deleteEmployeeWfhDate($id)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized. Only Administrators can remove WFH date assignments.');
        }

        WorkScheduleService::deleteEmployeeWfhDate($id);

        return back()->with('success', 'Employee Work From Home date assignment removed successfully.');
    }
}
