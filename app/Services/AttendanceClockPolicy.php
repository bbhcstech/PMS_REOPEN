<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Clock-in / clock-out boundary configured in Settings > Attendance Settings.
 *
 * When the boundary is switched on, staff (everyone except admin/superadmin) can only clock in
 * between "clock-in opens" and "clock-in closes"; a late arrival after "half day after" counts as a
 * half day; whoever has not clocked in by the closing time on a working day is marked absent; and a
 * forgotten clock-out is closed at "clock-out closes" and counted as configured (half day / absent).
 * When it is switched off, attendance behaves exactly as before.
 */
class AttendanceClockPolicy
{
    public const EXEMPT_ROLES = ['admin', 'superadmin', 'administrator', 'client'];
    public const MISSED_CLOCK_IN = 'missed_clock_in';
    public const MISSED_CLOCK_OUT = 'missed_clock_out';

    private static ?AttendanceSetting $cached = null;
    private static bool $schemaReady = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }
        (require database_path('migrations/tenant/2026_10_12_000004_add_clock_window_to_attendance_settings.php'))->up();
        self::$schemaReady = true;
    }

    public static function setting(): ?AttendanceSetting
    {
        if (self::$cached) {
            return self::$cached;
        }
        try {
            self::ensureSchema();
            return self::$cached = WorkScheduleService::attendancePolicy();
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /** Call after switching company database (scheduled jobs) or saving the settings. */
    public static function forget(): void
    {
        self::$cached = null;
        self::$schemaReady = false;
    }

    public static function enabled(?AttendanceSetting $setting = null): bool
    {
        $setting ??= self::setting();
        return (bool) ($setting?->clock_window_enabled);
    }

    public static function appliesTo(?User $user): bool
    {
        return $user && ! in_array(strtolower((string) $user->role), self::EXEMPT_ROLES, true);
    }

    /** Settings time (H:i:s) on the given local day, or null when not configured. */
    public static function at(?string $time, Carbon $day): ?Carbon
    {
        if (! $time) {
            return null;
        }
        return $day->copy()->setTimeFromTimeString(substr($time, 0, 8));
    }

    /**
     * Why this user may not clock in at $now (local time of the employee), or null when allowed.
     */
    public static function clockInBlockedReason(User $user, Carbon $now): ?string
    {
        $setting = self::setting();
        if (! self::enabled($setting) || ! self::appliesTo($user)) {
            return null;
        }

        $opens = self::at($setting->clock_in_opens_at, $now);
        $closes = self::at($setting->clock_in_closes_at, $now);
        if ($opens && $now->lt($opens)) {
            return 'Clock-in opens at ' . $opens->format('h:i A') . '. Please clock in between '
                . $opens->format('h:i A') . ($closes ? ' and ' . $closes->format('h:i A') : '') . '.';
        }
        if ($closes && $now->gt($closes)) {
            $marked = Attendance::where('user_id', $user->id)->whereDate('date', $now->toDateString())
                ->where('system_status_reason', self::MISSED_CLOCK_IN)->exists();
            return 'Clock-in closed at ' . $closes->format('h:i A') . '. '
                . ($marked ? 'Today is marked absent. ' : 'Today will be marked absent. ')
                . 'Please contact your admin or HR if you need a correction.';
        }

        return null;
    }

    /** Text for the clock widgets, e.g. "Clock in 08:30 AM – 11:00 AM · Clock out by 06:30 PM". */
    public static function windowSummary(?User $user = null): ?string
    {
        $setting = self::setting();
        if (! self::enabled($setting) || ($user && ! self::appliesTo($user))) {
            return null;
        }
        $fmt = fn ($t) => $t ? Carbon::parse($t)->format('h:i A') : null;
        $parts = [];
        if ($setting->clock_in_opens_at || $setting->clock_in_closes_at) {
            $parts[] = 'Clock in ' . ($fmt($setting->clock_in_opens_at) ?? 'any time') . ' – ' . ($fmt($setting->clock_in_closes_at) ?? 'any time');
        }
        if ($setting->late_time) {
            $parts[] = 'late after ' . $fmt($setting->late_time);
        }
        if ($setting->half_day_after_time) {
            $parts[] = 'half day after ' . $fmt($setting->half_day_after_time);
        }
        if ($setting->office_end_time) {
            $parts[] = 'clock out from ' . $fmt($setting->office_end_time);
        }
        return $parts ? implode(' · ', $parts) : null;
    }

    /** Latest moment an open session may run before it is closed automatically. */
    public static function clockOutCutoff(Carbon $clockIn): ?Carbon
    {
        $setting = self::setting();
        if (! self::enabled($setting) || ! $setting->clock_out_closes_at) {
            return null;
        }
        $cutoff = self::at($setting->clock_out_closes_at, $clockIn);
        return $cutoff->lt($clockIn) ? null : $cutoff;
    }

    /**
     * Status adjustments of the boundary, applied after the regular late/half-day/day-off rules.
     */
    public static function applyToAttendance(Attendance $attendance, AttendanceSetting $setting): void
    {
        $hasColumn = fn (string $column) => $attendance->hasTableColumn($column);
        if (! $attendance->clock_in) {
            return;
        }
        // The regular rules have just recalculated the status, so an earlier boundary outcome no longer holds.
        if ($hasColumn('system_status_reason') && $attendance->system_status_reason === self::MISSED_CLOCK_OUT) {
            $attendance->system_status_reason = null;
        }
        if (! self::enabled($setting) || ! self::appliesTo($attendance->user()->first())) {
            return;
        }
        // Days before the boundary was switched on keep the rules that applied then.
        $day = ($attendance->date instanceof Carbon ? $attendance->date : Carbon::parse($attendance->date))->toDateString();
        if ($setting->clock_window_enabled_at && $day < Carbon::parse($setting->clock_window_enabled_at)->toDateString()) {
            return;
        }

        $markHalfDay = function () use ($attendance, $hasColumn) {
            $attendance->status = 'half_day';
            if ($hasColumn('half_day')) {
                $attendance->half_day = 'yes';
            }
        };

        // Forgotten clock-out: closed automatically by the system.
        if ($attendance->clock_out && $hasColumn('auto_clocked_out') && $attendance->auto_clocked_out) {
            $outcome = $setting->missed_clock_out_status ?: 'half_day';
            if ($outcome === 'absent') {
                $attendance->status = 'absent';
            } elseif ($outcome === 'half_day' && ! in_array($attendance->status, ['day_off', 'absent'], true)) {
                $markHalfDay();
            }
            if ($outcome !== 'keep' && $hasColumn('system_status_reason')) {
                $attendance->system_status_reason = self::MISSED_CLOCK_OUT;
            }
            return;
        }

        // Clocked in after the half-day cut-off.
        $date = $attendance->date instanceof Carbon ? $attendance->date : Carbon::parse($attendance->date);
        $halfDayAfter = self::at($setting->half_day_after_time, $date->copy()->startOfDay());
        $clockIn = Carbon::parse($date->toDateString() . ' ' . $attendance->clock_in);
        if ($halfDayAfter && $clockIn->gt($halfDayAfter) && in_array($attendance->status, ['present', 'late'], true)) {
            $markHalfDay();
            if ($hasColumn('late')) {
                $attendance->late = 'yes';
            }
        }
    }

    /**
     * Record "absent" for staff who did not clock in on a working day once clock-in has closed.
     * Covers today and the previous few days (in case nobody opened the app), never before the
     * boundary was switched on. Safe to call repeatedly.
     */
    public static function markMissedClockIns(?int $companyId, ?Carbon $now = null, bool $throttle = true): int
    {
        $setting = self::setting();
        if (! self::enabled($setting) || ! $setting->clock_in_closes_at) {
            return 0;
        }
        $now ??= Carbon::now(config('app.timezone') ?: 'Asia/Kolkata');
        if ($throttle && ! Cache::add('attendance-missed-clock-in:' . ($companyId ?: 0) . ':' . $now->format('YmdHi'), 1, 120)) {
            return 0;
        }

        $schema = Schema::connection((new Attendance)->getConnectionName() ?: config('database.default'));
        $enabledFrom = $setting->clock_window_enabled_at ? Carbon::parse($setting->clock_window_enabled_at)->startOfDay() : $now->copy()->startOfDay();
        $first = $now->copy()->subDays(6)->startOfDay()->max($enabledFrom);
        $last = $now->copy()->startOfDay();
        if ($now->lte(self::at($setting->clock_in_closes_at, $now))) {
            $last->subDay(); // today's window is still open
        }
        if ($last->lt($first)) {
            return 0;
        }

        $offDays = WorkScheduleService::getHolidaysAndOffDays($first, $last, $companyId);
        $users = self::staffQuery($companyId)->get(['id', 'role', 'created_at']);
        if ($users->isEmpty()) {
            return 0;
        }
        $userIds = $users->pluck('id');

        $existing = Attendance::withoutGlobalScopes()->whereIn('user_id', $userIds)
            ->whereDate('date', '>=', $first->toDateString())
            ->whereDate('date', '<=', $last->toDateString())
            ->get(['user_id', 'date'])
            ->map(fn ($row) => $row->user_id . '|' . Carbon::parse($row->date)->toDateString())
            ->flip();
        $leaves = Leave::withoutGlobalScope('workforce_company')->whereIn('user_id', $userIds)
            ->whereIn('status', ['approved', 'Approved', 'active'])
            ->where(fn ($q) => $q->whereBetween('date', [$first->toDateString(), $last->toDateString()])
                ->orWhere(fn ($r) => $r->whereDate('start_date', '<=', $last)->whereDate('end_date', '>=', $first)))
            ->get(['user_id', 'date', 'start_date', 'end_date']);
        $joining = $schema->hasTable('employee_details') && $schema->hasColumn('employee_details', 'joining_date')
            ? \App\Models\EmployeeDetail::whereIn('user_id', $userIds)->pluck('joining_date', 'user_id')
            : collect();

        $hasCompany = $schema->hasColumn('attendances', 'company_id');
        $marked = 0;
        for ($day = $first->copy(); $day->lte($last); $day->addDay()) {
            $date = $day->toDateString();
            if (isset($offDays[$date])) {
                continue;
            }
            foreach ($users as $user) {
                if (isset($existing[$user->id . '|' . $date])) {
                    continue;
                }
                $startedOn = $joining[$user->id] ?? $user->created_at;
                if ($startedOn && Carbon::parse($startedOn)->toDateString() > $date) {
                    continue;
                }
                $onLeave = $leaves->contains(function ($leave) use ($user, $date) {
                    if ((int) $leave->user_id !== (int) $user->id) {
                        return false;
                    }
                    $from = Carbon::parse($leave->start_date ?: $leave->date)->toDateString();
                    $to = Carbon::parse($leave->end_date ?: $leave->date ?: $leave->start_date)->toDateString();
                    return $date >= $from && $date <= $to;
                });
                if ($onLeave) {
                    continue;
                }

                $row = ['user_id' => $user->id, 'date' => $date, 'status' => 'absent', 'system_status_reason' => self::MISSED_CLOCK_IN];
                if ($hasCompany) {
                    $row['company_id'] = $companyId;
                }
                if ($schema->hasColumn('attendances', 'late')) {
                    $row['late'] = 'no';
                }
                if ($schema->hasColumn('attendances', 'half_day')) {
                    $row['half_day'] = 'no';
                }
                Attendance::create($row);
                $marked++;
            }
        }

        return $marked;
    }

    private static function staffQuery(?int $companyId)
    {
        $users = User::query()->where(fn ($q) => $q->whereNull('role')->orWhereNotIn('role', self::EXEMPT_ROLES));
        $schema = Schema::connection((new User)->getConnectionName() ?: config('database.default'));
        if ($companyId) {
            $users->where('company_id', $companyId);
        }
        foreach (['is_active' => true, 'login_allowed' => true] as $column => $value) {
            if ($schema->hasColumn('users', $column)) {
                $users->where(fn ($q) => $q->where($column, $value)->orWhereNull($column));
            }
        }
        if ($schema->hasColumn('users', 'archived_at')) {
            $users->whereNull('archived_at');
        }
        return $users;
    }
}
