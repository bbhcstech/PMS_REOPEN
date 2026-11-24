<?php

namespace App\Http\Controllers\Concerns\Dashboard;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\User;
use App\Services\SystemNotificationService;
use App\Services\WorkScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DashboardAttendance
{
    private function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2)
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) * sin($lonDelta / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function storeClockInPhoto(string $selfieData, int $userId, string $today, string $phase = 'in'): ?string
    {
        if (! preg_match('/^data:image\/(png|jpe?g);base64,/', $selfieData)) {
            abort(422, 'Capture a valid attendance photo before continuing.');
        }

        $imageData = preg_replace('/^data:image\/(png|jpe?g);base64,/', '', $selfieData);
        $decoded = base64_decode($imageData, true);

        if ($decoded === false) {
            abort(422, 'Capture a valid attendance photo before continuing.');
        }
        abort_if(strlen($decoded) > 5 * 1024 * 1024 || ! @getimagesizefromstring($decoded), 422, 'Invalid attendance photo.');
        if (\App\Services\WorkforceAccess::isAuthority(auth()->user())) {
            $path = 'authority-attendance/' . auth()->user()->company_id . '/' . \Illuminate\Support\Str::uuid() . '.jpg';
            abort_unless(\Illuminate\Support\Facades\Storage::disk('local')->put($path, $decoded), 500, 'Could not save your attendance photo.');
            return $path;
        }

        $directory = public_path('admin/uploads/attendance-selfies');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = 'clock-' . $phase . '-' . $userId . '-' . $today . '-' . \Illuminate\Support\Str::uuid() . '.jpg';
        $absolutePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        return file_put_contents($absolutePath, $decoded) !== false
            ? 'admin/uploads/attendance-selfies/' . $fileName
            : null;
    }

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

    /**
     * Use the deployment timezone for all new attendance sessions.
     */
    protected function resolveEmployeeTimezone(?string $clientTz = null, ?float $lat = null, ?float $lng = null): string
    {
        // One business timezone; device location must not change attendance dates.
        return config('app.timezone', 'Asia/Kolkata');
    }

   public function clockIn(Request $request)
{
    $actor = \App\Services\WorkforceAccess::authorizeActor();
    \App\Services\WorkforceRecordSchema::ensure();
    $validated = $request->validate([
        'clock_in_latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'clock_in_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'clock_in_accuracy' => ['nullable', 'numeric', 'min:0'],
        'clock_in_address' => ['nullable', 'string', 'max:255'],
        'clock_in_selfie' => ['required', 'string', 'max:7000000'],
        'clock_in_timezone' => ['nullable', 'string', 'max:50'],
    ]);

    $timezone = $this->resolveEmployeeTimezone(
        $request->input('clock_in_timezone'),
        isset($validated['clock_in_latitude']) ? (float) $validated['clock_in_latitude'] : null,
        isset($validated['clock_in_longitude']) ? (float) $validated['clock_in_longitude'] : null
    );
    $request->session()->put('attendance_timezone.' . auth()->id(), $timezone);

    $now = Carbon::now($timezone);
    app(\App\Services\AttendanceAutoClockOut::class)->closeDue((int) $actor->company_id, (int) $actor->id, $now);
    if ($now->gte($now->copy()->setTime(23, 58, 0))) {
        return $this->clockActionResponse($request, 'Clock-in closes at 11:58 PM. Please clock in after midnight for the next day.', false);
    }
    if ($blocked = \App\Services\AttendanceClockPolicy::clockInBlockedReason($actor, $now)) {
        return $this->clockActionResponse($request, $blocked, false);
    }
    $today = $now->toDateString();
    $userId = auth()->id();

    $lat = isset($validated['clock_in_latitude']) && $validated['clock_in_latitude'] !== ''
        ? (float) $validated['clock_in_latitude']
        : self::OFFICE_LATITUDE;

    $lng = isset($validated['clock_in_longitude']) && $validated['clock_in_longitude'] !== ''
        ? (float) $validated['clock_in_longitude']
        : self::OFFICE_LONGITUDE;

    $distance = $this->distanceInMeters(
        self::OFFICE_LATITUDE,
        self::OFFICE_LONGITUDE,
        $lat,
        $lng
    );

    return DB::connection('tenant')->transaction(function () use ($request, $validated, $now, $today, $userId, $lat, $lng, $distance, $timezone) {
        User::whereKey($userId)->lockForUpdate()->firstOrFail();
        // Serialize against concurrent clock-ins using the user's row lock above.
        // A completed employee session still consumes that business day's clock-in.
        if (strtolower((string) auth()->user()->role) === 'employee'
            && Attendance::where('user_id', $userId)->whereDate('date', $today)
                ->whereNotNull('clock_in')->exists()) {
            return $this->clockActionResponse($request, 'You have already clocked in today. You can clock in again on the next day.', false);
        }
        if (Attendance::where('user_id', $userId)->whereNotNull('clock_in')->whereNull('clock_out')->exists()) {
            return $this->clockActionResponse($request, 'Please clock out of your active shift first.', false);
        }

        $photoPath = !empty($validated['clock_in_selfie'])
            ? $this->storeClockInPhoto($validated['clock_in_selfie'], $userId, $today)
            : null;
        abort_unless($photoPath, 500, 'Could not save your clock-in photo. Please try again.');

        $clockInTime = $now->format('H:i:s');
        $currentLocationLabel = trim((string) ($validated['clock_in_address'] ?? ''));

        if ($currentLocationLabel === '') {
            $currentLocationLabel = 'Current location: '
                . $lat
                . ', '
                . $lng
                . ' (' . round($distance, 1) . 'm from office)';
        }

        $currentLocationLabel = mb_substr($currentLocationLabel, 0, 255);

        $isWfhAssigned = WorkScheduleService::isUserWfhOnDate($userId, $today);
        $workFromType = $isWfhAssigned ? 'wfh' : ($distance <= self::OFFICE_RADIUS_METERS ? 'office' : 'field');
        $initialStatus = $isWfhAssigned ? 'wfh' : 'present';

        $attendanceData = [
            'user_id'            => $userId,
            'date'               => $today,
            'clock_in'           => $clockInTime,
            'clock_in_timezone'  => $timezone,
            'status'             => $initialStatus,
            'clock_in_latitude'  => $lat,
            'clock_in_longitude' => $lng,
            'clock_in_address'   => $currentLocationLabel,
            'clock_in_photo'     => $photoPath,
            'work_from_type'     => $workFromType,
            // Older company databases require this field even when work_from_type exists.
            'working_from'       => $workFromType,
        ];

        if (Schema::hasColumn('attendances', 'location')) {
            $attendanceData['location'] = $currentLocationLabel;
        }
        if (Schema::hasColumn('attendances', 'latitude')) {
            $attendanceData['latitude'] = $lat;
        }
        if (Schema::hasColumn('attendances', 'longitude')) {
            $attendanceData['longitude'] = $lng;
        }
        if (Schema::hasColumn('attendances', 'company_id')) {
            $attendanceData['company_id'] = auth()->user()?->company_id;
        }

        // A day the system marked absent (clock-in window had closed) becomes this clock-in, e.g. after the admin widened the window.
        $markedAbsent = Schema::hasColumn('attendances', 'system_status_reason')
            ? Attendance::where('user_id', $userId)->whereDate('date', $today)->whereNull('clock_in')
                ->where('system_status_reason', \App\Services\AttendanceClockPolicy::MISSED_CLOCK_IN)->first()
            : null;
        if ($markedAbsent) {
            $markedAbsent->fill($attendanceData + ['system_status_reason' => null])->save();
            $attendance = $markedAbsent;
        } else {
            $attendance = Attendance::create($attendanceData);
        }
        $this->applyOrganizationAttendanceRules($attendance);

        $this->notifyAuthorityAttendance($attendance, 'Clocked In');
        $wfhMsg = $isWfhAssigned ? ' (Assigned Work From Home)' : '';
        return $this->clockActionResponse($request, 'Clocked in at ' . $now->format('h:i A') . $wfhMsg . '. Current location saved.');
    }, 5);
}

   public function clockOut(Request $request)
{
    $actor = \App\Services\WorkforceAccess::authorizeActor();
    \App\Services\WorkforceRecordSchema::ensure();
    $timezone = $this->resolveEmployeeTimezone($request->input('clock_out_timezone') ?? $request->input('timezone') ?? session('attendance_timezone.' . auth()->id()));
    $now = Carbon::now($timezone);
    $today = $now->toDateString();
    $userId = auth()->id();

    app(\App\Services\AttendanceAutoClockOut::class)->closeDue((int) $actor->company_id, (int) $actor->id, $now);

    return DB::connection('tenant')->transaction(function () use ($request, $now, $today, $userId) {
        User::whereKey($userId)->lockForUpdate()->firstOrFail();
        $attendance = Attendance::where('user_id', $userId)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->latest('date')
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (!$attendance) {
            $hasClosedToday = Attendance::where('user_id', $userId)
                ->where('date', $today)
                ->whereNotNull('clock_out')
                ->exists();

            if ($hasClosedToday) {
                return $this->clockActionResponse($request, 'You have already clocked out of your shift.', false);
            }

            return $this->clockActionResponse($request, 'You need to clock in first.', false);
        }

        $sessionNow = Carbon::now($attendance->sessionTimezone());
        $cutoff = $attendance->auto_clock_out_at;
        $out = $sessionNow->copy()->min($cutoff);
        $attendance->clock_out = $out->format('H:i:s');
        $attendance->auto_clocked_out = $sessionNow->gte($cutoff);
        if (!$attendance->auto_clocked_out) {
            $validated = $request->validate(['clock_out_selfie' => ['required', 'string', 'max:7500000']]);
            $attendance->clock_out_photo = $this->storeClockInPhoto($validated['clock_out_selfie'], $userId, $attendance->date->toDateString(), 'out');
            abort_unless($attendance->clock_out_photo, 500, 'Could not save your clock-out photo. Please try again.');
        }
        if ($attendance->hasTableColumn('total_hours')) $attendance->total_hours = round($attendance->total_seconds / 3600, 2);
        $attendance->save();
        $this->applyOrganizationAttendanceRules($attendance->fresh());

        $this->notifyAuthorityAttendance($attendance, 'Clocked Out');
        return $this->clockActionResponse($request, 'Clocked out at ' . $out->format('h:i A'));
    }, 5);
}

private function clockActionResponse(Request $request, string $message, bool $success = true)
{
    if (!$request->expectsJson()) return back()->with($success ? 'success' : 'error', $message);
    if ($success) $request->session()->flash('success', $message);
    return response()->json([
        'success' => $success,
        'message' => $message,
        'redirect_url' => route('dashboard', [], false),
    ], $success ? 200 : 422);
}

private function notifyAuthorityAttendance(Attendance $attendance, string $action): void
{
    $user = auth()->user();
    if (! $user || ! \App\Services\WorkforceAccess::isAuthority($user)) return;
    DB::connection('tenant')->afterCommit(function () use ($attendance, $user, $action) {
        SystemNotificationService::notifyAdmins(
            $action,
            $user->name . ' ' . strtolower($action) . ' for ' . $attendance->date->format('d M Y') . '.',
            route('admin.authority-attendance', ['view' => 'team', 'user_id' => $user->id]),
            ['employee_id' => $user->id, 'entity_type' => Attendance::class, 'entity_id' => $attendance->id,
                'type' => $action === 'Clocked In' ? 'attendance_clock_in' : 'attendance_clock_out', 'icon' => 'fa-user-clock']
        );
    });
}
}
