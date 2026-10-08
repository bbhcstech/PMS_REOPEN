<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class WorkScheduleService
{
    /**
     * Retrieve all work schedule configuration settings.
     */
    public static function getSettings(): array
    {
        return [
            'working_days' => json_decode(AppSetting::valueFor('work_working_days', '["Monday","Tuesday","Wednesday","Thursday","Friday"]'), true) ?? ["Monday","Tuesday","Wednesday","Thursday","Friday"],
            'work_start_time' => AppSetting::valueFor('work_start_time', '09:00'),
            'work_end_time' => AppSetting::valueFor('work_end_time', '18:00'),
            'break_duration' => AppSetting::valueFor('work_break_duration', '60'),
            'shift_enabled' => AppSetting::valueFor('work_shift_enabled', '0'),
            'special_days' => json_decode(AppSetting::valueFor('work_special_days', '[]'), true) ?? [],
            'employee_modes' => json_decode(AppSetting::valueFor('work_employee_modes', '{}'), true) ?? [],
            'employee_wfh_dates' => json_decode(AppSetting::valueFor('work_employee_wfh_dates', '[]'), true) ?? [],
        ];
    }

    /**
     * Get combined holidays and weekly off-days for a given date range.
     * Combines all 3 sources:
     * 1. Weekly non-working days from Work Schedule (e.g. Sat/Sun)
     * 2. Special Holidays declared in Work Schedule (work_special_days)
     * 3. Holidays from the holidays table
     *
     * @return array [ 'YYYY-MM-DD' => [ 'status' => 'holiday', 'occassion' => '...', 'is_weekly_off' => bool ] ]
     */
    public static function getHolidaysAndOffDays(Carbon $startDate, Carbon $endDate, ?int $companyId = null): array
    {
        $settings = self::getSettings();
        $workingDays = $settings['working_days'] ?? ["Monday","Tuesday","Wednesday","Thursday","Friday"];
        $specialDays = $settings['special_days'] ?? [];

        $holidaysMap = [];

        // 1. Company Holidays table
        $dbHolidaysQuery = Holiday::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        if ($companyId) {
            $dbHolidaysQuery->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }
        foreach ($dbHolidaysQuery->get() as $holiday) {
            $dateKey = Carbon::parse($holiday->date)->format('Y-m-d');
            $occassion = trim($holiday->occassion ?? $holiday->title ?? 'Holiday');
            $holidaysMap[$dateKey] = [
                'status' => 'holiday',
                'occassion' => $occassion,
                'is_weekly_off' => false,
            ];
        }

        // 2. Special Days from Work Schedule (type = holiday)
        foreach ($specialDays as $sd) {
            if (($sd['type'] ?? '') === 'holiday' && !empty($sd['date'])) {
                $dateKey = Carbon::parse($sd['date'])->format('Y-m-d');
                if ($dateKey >= $startDate->format('Y-m-d') && $dateKey <= $endDate->format('Y-m-d')) {
                    $holidaysMap[$dateKey] = [
                        'status' => 'holiday',
                        'occassion' => trim($sd['title'] ?? 'Special Holiday'),
                        'is_weekly_off' => false,
                    ];
                }
            }
        }

        // 3. Weekly Off Days based on operating working days
        for ($curr = $startDate->copy(); $curr->lte($endDate); $curr->addDay()) {
            $dateKey = $curr->format('Y-m-d');
            $dayName = $curr->format('l');

            if (!in_array($dayName, $workingDays, true)) {
                if (!isset($holidaysMap[$dateKey])) {
                    $holidaysMap[$dateKey] = [
                        'status' => 'holiday',
                        'occassion' => "{$dayName} (Weekly Off)",
                        'is_weekly_off' => true,
                    ];
                }
            }
        }

        return $holidaysMap;
    }

    /**
     * Check if a specific employee is assigned Work From Home on a particular date.
     * Evaluates:
     * 1. Date-specific WFH assignment for this user (work_employee_wfh_dates)
     * 2. Global Special Day WFH override (work_special_days where type=wfh)
     * 3. Ongoing standard employee work mode (work_employee_modes[user_id] == 'wfh')
     *
     * @return array|null [ 'is_wfh' => true, 'type' => 'specific_date'|'global_special'|'standard_mode', 'reason' => '...' ]
     */
    public static function isUserWfhOnDate(int|string|null $userId, string|Carbon|null $date): ?array
    {
        if (empty($userId) || empty($date)) {
            return null;
        }

        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        $settings = self::getSettings();

        // 1. Check Date-Specific Assignment for this employee
        $wfhDates = $settings['employee_wfh_dates'] ?? [];
        foreach ($wfhDates as $entry) {
            if ((int)($entry['user_id'] ?? 0) === (int)$userId && ($entry['date'] ?? '') === $dateStr) {
                return [
                    'is_wfh' => true,
                    'type' => 'specific_date',
                    'reason' => $entry['reason'] ?? 'Admin Assigned Work From Home',
                ];
            }
        }

        // 2. Check Global Special Day WFH override
        $specialDays = $settings['special_days'] ?? [];
        foreach ($specialDays as $sd) {
            if (($sd['type'] ?? '') === 'wfh' && ($sd['date'] ?? '') === $dateStr) {
                return [
                    'is_wfh' => true,
                    'type' => 'global_special',
                    'reason' => $sd['title'] ?? 'Company-Wide Work From Home (WFH)',
                ];
            }
        }

        // 3. Check Employee Standard Mode
        $employeeModes = $settings['employee_modes'] ?? [];
        if (($employeeModes[$userId] ?? 'office') === 'wfh') {
            return [
                'is_wfh' => true,
                'type' => 'standard_mode',
                'reason' => 'Standard Remote Mode (WFH)',
            ];
        }

        return null;
    }

    /**
     * Assign a specific date as Work From Home for a particular employee.
     */
    public static function assignEmployeeWfhDate(int $userId, string $date, string $reason = ''): array
    {
        $settings = self::getSettings();
        $wfhDates = $settings['employee_wfh_dates'] ?? [];
        $newId = uniqid('ewfh_');

        $newEntry = [
            'id' => $newId,
            'user_id' => $userId,
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'reason' => trim($reason) ?: 'Work From Home Approved',
            'created_at' => now()->toDateTimeString(),
        ];

        // Replace if employee already has an entry on this date, else append
        $existingIndex = -1;
        foreach ($wfhDates as $idx => $entry) {
            if ((int)($entry['user_id'] ?? 0) === $userId && ($entry['date'] ?? '') === $newEntry['date']) {
                $existingIndex = $idx;
                break;
            }
        }

        if ($existingIndex >= 0) {
            $wfhDates[$existingIndex] = $newEntry;
        } else {
            $wfhDates[] = $newEntry;
        }

        // Sort by date ascending
        usort($wfhDates, fn($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));

        AppSetting::updateOrCreate(['key' => 'work_employee_wfh_dates'], [
            'label' => 'Employee WFH Date Assignments',
            'value' => json_encode(array_values($wfhDates)),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        // Send notifications to Employee & HR
        $targetUser = User::find($userId);
        if ($targetUser) {
            $formattedDate = Carbon::parse($date)->format('D, M d, Y');
            try {
                SystemNotificationService::notifyUser(
                    $targetUser,
                    "Assigned Work From Home (WFH)",
                    "Admin has designated {$formattedDate} as your Work From Home day ({$newEntry['reason']}).",
                    route('dashboard'),
                    ['date' => $newEntry['date'], 'reason' => $newEntry['reason']]
                );

                $hrUsers = SystemNotificationService::adminsAndHr();
                SystemNotificationService::notifyUser(
                    $hrUsers,
                    "Employee WFH Scheduled: {$targetUser->name}",
                    "Admin assigned {$targetUser->name} to Work From Home on {$formattedDate}.",
                    route('admin.settings.work-schedule'),
                    ['target_user_id' => $targetUser->id, 'date' => $newEntry['date']]
                );
            } catch (\Throwable $e) {
                Log::error('Assign WFH notification error: ' . $e->getMessage());
            }
        }

        return $newEntry;
    }

    /**
     * Delete an assigned employee WFH date by ID.
     */
    public static function deleteEmployeeWfhDate(string $id): bool
    {
        $settings = self::getSettings();
        $wfhDates = $settings['employee_wfh_dates'] ?? [];

        $filtered = array_values(array_filter($wfhDates, fn($item) => ($item['id'] ?? '') !== $id));

        AppSetting::updateOrCreate(['key' => 'work_employee_wfh_dates'], [
            'label' => 'Employee WFH Date Assignments',
            'value' => json_encode($filtered),
            'page' => 'work-schedule',
            'section' => 'Work Schedule',
            'type' => 'json'
        ]);

        return true;
    }

    /**
     * Apply organization attendance calculation rules.
     * 
     * Key Business Rules:
     * 1. If assigned to Work From Home (by date, global special day, or standard employee mode)
     *    or work_from_type is 'wfh' or status is 'wfh':
     *    -> Status MUST BE 'wfh' and work_from_type MUST BE 'wfh'.
     *    -> NEVER overwrite to half_day, day_off, or present!
     * 2. If regular shift:
     *    -> Completed shift with working time < day_off_threshold_minutes: marked as 'day_off'.
     *    -> Completed shift with working time < half_day_threshold_minutes: marked as 'half_day'.
     *    -> Completed shift with working time >= half_day_threshold_minutes: marked as Full Day ('present' or 'late').
     *    -> Active/Open clock-in: marked as 'present' (or 'late').
     */
    public static function applyOrganizationAttendanceRules(Attendance $attendance, ?AttendanceSetting $setting = null): Attendance
    {
        $setting = $setting ?? self::attendancePolicy();
        $attendance->append(['total_seconds', 'clock_in_datetime', 'clock_out_datetime']);

        $dateStr = $attendance->date instanceof Carbon ? $attendance->date->format('Y-m-d') : Carbon::parse($attendance->date)->format('Y-m-d');
        $wfhInfo = self::isUserWfhOnDate($attendance->user_id, $dateStr);

        // ==========================================
        // RULE 1: WORK FROM HOME (WFH)
        // If assigned WFH, mark as WFH, NOT half day or full day!
        // ==========================================
        if ($wfhInfo !== null || $attendance->work_from_type === 'wfh' || $attendance->status === 'wfh') {
            $attendance->status = 'wfh';
            $attendance->work_from_type = 'wfh';
            if ($attendance->hasTableColumn('late')) {
                $attendance->late = 'no';
            }
            if ($attendance->hasTableColumn('half_day')) {
                $attendance->half_day = 'no';
            }
            $attendance->save();
            return $attendance;
        }

        // ==========================================
        // RULE 2: STANDARD SHIFT CALCULATION
        // ==========================================
        $clockIn = $attendance->clock_in_datetime;
        $seconds = (int) ($attendance->total_seconds ?? 0);

        // If clock_in and clock_out are present, re-verify seconds accurately
        if (!empty($attendance->clock_in) && !empty($attendance->clock_out)) {
            try {
                $cIn = Carbon::parse($dateStr . ' ' . $attendance->clock_in);
                $cOut = Carbon::parse($dateStr . ' ' . $attendance->clock_out);
                if ($cOut->lt($cIn)) {
                    $cOut->addDay();
                }
                $seconds = abs($cOut->diffInSeconds($cIn));
            } catch (\Throwable $_) {}
        }

        $lateTimeStr = $setting->late_time ?: '09:30:00';
        $lateTime = Carbon::parse($dateStr . ' ' . $lateTimeStr);
        $isLate = $clockIn && $clockIn->gt($lateTime);
        $hasCompletedShift = !empty($attendance->clock_in) && !empty($attendance->clock_out);

        $dayOffSeconds = (int) ($setting->day_off_threshold_minutes ?? 270) * 60;
        $halfDaySeconds = (int) ($setting->half_day_threshold_minutes ?? 510) * 60;

        if ($hasCompletedShift && $seconds < $dayOffSeconds) {
            $attendance->status = 'day_off';
            if ($attendance->hasTableColumn('late')) {
                $attendance->late = $isLate ? 'yes' : 'no';
            }
            if ($attendance->hasTableColumn('half_day')) {
                $attendance->half_day = 'no';
            }
        } elseif ($hasCompletedShift && $seconds < $halfDaySeconds) {
            $attendance->status = 'half_day';
            if ($attendance->hasTableColumn('late')) {
                $attendance->late = $isLate ? 'yes' : 'no';
            }
            if ($attendance->hasTableColumn('half_day')) {
                $attendance->half_day = 'yes';
            }
        } elseif ($isLate) {
            $attendance->status = 'late';
            if ($attendance->hasTableColumn('late')) {
                $attendance->late = 'yes';
            }
            if ($attendance->hasTableColumn('half_day')) {
                $attendance->half_day = 'no';
            }
        } elseif ($attendance->clock_in) {
            $attendance->status = 'present';
            if ($attendance->hasTableColumn('late')) {
                $attendance->late = 'no';
            }
            if ($attendance->hasTableColumn('half_day')) {
                $attendance->half_day = 'no';
            }
        }

        $attendance->save();

        return $attendance;
    }

    /**
     * Fetch or initialize AttendanceSetting.
     */
    public static function attendancePolicy(): AttendanceSetting
    {
        $setting = AttendanceSetting::firstOrCreate([], [
            'office_start_time' => '09:30:00',
            'late_time' => '09:30:00',
            'half_day_threshold_minutes' => 510,
            'day_off_threshold_minutes' => 270,
        ]);

        $updates = [];
        if (!$setting->office_start_time) {
            $updates['office_start_time'] = '09:30:00';
        }
        if (!$setting->late_time) {
            $updates['late_time'] = '09:30:00';
        }
        if (!$setting->half_day_threshold_minutes) {
            $updates['half_day_threshold_minutes'] = 510;
        }
        if (!$setting->day_off_threshold_minutes) {
            $updates['day_off_threshold_minutes'] = 270;
        }

        if ($updates) {
            $setting->forceFill($updates)->save();
            $setting->refresh();
        }

        return $setting;
    }
}
