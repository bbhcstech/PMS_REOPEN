<?php

namespace App\Http\Controllers\Concerns\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Services\WorkScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait AttendancePolicySettings
{
    private function applyOrganizationAttendanceRules(Attendance $attendance): Attendance
    {
        return WorkScheduleService::applyOrganizationAttendanceRules($attendance, $this->attendancePolicy());
    }

    private function attendancePolicy(): AttendanceSetting
    {
        $setting = AttendanceSetting::firstOrCreate([], [
            'office_start_time' => '09:30:00',
            'late_time' => '09:30:00',
            'half_day_threshold_minutes' => 510,
            'day_off_threshold_minutes' => 270,
        ]);

        $updates = [];
        if (! $setting->office_start_time) {
            $updates['office_start_time'] = '09:30:00';
        }
        if (! $setting->late_time) {
            $updates['late_time'] = '09:30:00';
        }
        if (! $setting->half_day_threshold_minutes) {
            $updates['half_day_threshold_minutes'] = 510;
        }
        if (! $setting->day_off_threshold_minutes) {
            $updates['day_off_threshold_minutes'] = 270;
        }

        if ($updates) {
            $setting->forceFill($updates)->save();
            $setting->refresh();
        }

        return $setting;
    }

    public function syncAttendancePolicyRules(): int
    {
        $updated = 0;

        Attendance::whereNull('archived_at')
            ->whereNotNull('clock_in')
            ->chunkById(100, function ($attendances) use (&$updated) {
                foreach ($attendances as $attendance) {
                    $this->applyOrganizationAttendanceRules($attendance);
                    $updated++;
                }
            });

        return $updated;
    }

    /**
     * Settings - ADMIN ONLY
     */
    public function settings()
    {
        $user = Auth::user();

        // Only admin can access settings
        if (! $this->isAdminUser($user)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access settings.');
        }

        \App\Services\AttendanceClockPolicy::ensureSchema();
        $setting = $this->attendancePolicy();
        return view('attendance.settings', compact('setting'));
    }

    /**
     * Update Settings - ADMIN ONLY
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        // Only admin can update settings
        if ($user->role !== 'admin') {
            return back()->with('error', 'You do not have permission to update settings.');
        }

        $data = $request->validate([
            'office_start_time' => 'required|date_format:H:i',
            'late_time' => 'required|date_format:H:i',
            'half_day_threshold_minutes' => 'nullable|integer|min:1|max:1440',
            'day_off_threshold_minutes' => 'nullable|integer|min:1|max:1440',
            'clock_window_enabled' => 'nullable|boolean',
            'clock_in_opens_at' => 'nullable|required_if:clock_window_enabled,1|date_format:H:i',
            'clock_in_closes_at' => 'nullable|required_if:clock_window_enabled,1|date_format:H:i',
            'half_day_after_time' => 'nullable|date_format:H:i',
            'office_end_time' => 'nullable|date_format:H:i',
            'clock_out_closes_at' => 'nullable|date_format:H:i',
            'missed_clock_out_status' => 'nullable|in:half_day,absent,keep',
        ]);

        // Clock-in / clock-out boundary: the times must follow each other through the day.
        $enabled = $request->boolean('clock_window_enabled');
        if ($enabled) {
            $order = array_filter([
                'clock_in_opens_at' => $data['clock_in_opens_at'] ?? null,
                'late_time' => $data['late_time'],
                'half_day_after_time' => $data['half_day_after_time'] ?? null,
                'clock_in_closes_at' => $data['clock_in_closes_at'] ?? null,
            ]);
            $labels = ['clock_in_opens_at' => 'Clock-in opens', 'late_time' => 'Late cutoff', 'half_day_after_time' => 'Half day after', 'clock_in_closes_at' => 'Clock-in closes'];
            $previous = null;
            foreach ($order as $field => $time) {
                if ($previous && $time < $order[$previous]) {
                    return back()->withErrors([$field => $labels[$field] . ' must not be earlier than ' . $labels[$previous] . '.'])->withInput();
                }
                $previous = $field;
            }
            if (! empty($data['clock_out_closes_at']) && ! empty($data['office_end_time']) && $data['clock_out_closes_at'] < $data['office_end_time']) {
                return back()->withErrors(['clock_out_closes_at' => 'Auto clock-out must not be earlier than the office end time.'])->withInput();
            }
        }
        \App\Services\AttendanceClockPolicy::ensureSchema();
        $current = $this->attendancePolicy();
        $data['clock_window_enabled'] = $enabled;
        // Missed clock-ins are only marked absent from the day the boundary was switched on.
        $data['clock_window_enabled_at'] = $enabled
            ? ($current->clock_window_enabled && $current->clock_window_enabled_at ? $current->clock_window_enabled_at : now()->toDateString())
            : null;
        $data['missed_clock_out_status'] = $data['missed_clock_out_status'] ?? 'half_day';

        $data['half_day_threshold_minutes'] = $data['half_day_threshold_minutes'] ?? 510;
        $data['day_off_threshold_minutes'] = $data['day_off_threshold_minutes'] ?? 270;

        if ($data['day_off_threshold_minutes'] >= $data['half_day_threshold_minutes']) {
            return back()->withErrors([
                'day_off_threshold_minutes' => 'Day off threshold must be lower than half day threshold.',
            ])->withInput();
        }

        AttendanceSetting::updateOrCreate([], $data);
        \App\Services\AttendanceClockPolicy::forget();
        $updated = $this->syncAttendancePolicyRules();

        return back()->with('success', 'Settings updated and ' . $updated . ' attendance record(s) recalculated.');
    }
}
