<?php

namespace App\Http\Controllers\Concerns\Attendance;

use App\Http\Controllers\DashboardController;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\CompanyAddress;
use App\Models\Department;
use App\Models\User;
use App\Notifications\ClockInNotification;
use App\Services\SystemNotificationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait AttendanceRecords
{
    private function parseManualDateRange($value): ?array
    {
        if (!is_string($value) || !preg_match('/\A\s*(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}\/\d{2}\/\d{4})\s*\z/', $value, $parts)) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('!m/d/Y', $parts[1]);
            $end = Carbon::createFromFormat('!m/d/Y', $parts[2]);
            if ($start->format('m/d/Y') !== $parts[1] || $end->format('m/d/Y') !== $parts[2] || $end->lt($start)) {
                return null;
            }
            return [$start, $end];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Mark Attendance - ADMIN ONLY
     */
    public function markAttendance(Request $request)
    {
        $user = Auth::user();

        // Only authorized management can mark attendance
        if (!$this->canManageAttendance($user) && !$this->isAdminUser($user)) {
            return back()->with('error', 'You do not have permission to mark attendance.');
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,holiday,late,half_day,leave,day_off,unpaid_leave,wfh',
            'clock_in' => 'nullable|date_format:H:i',
        ]);

        $attendanceEmployee = User::find($data['user_id']);
        abort_if(
            $attendanceEmployee && \App\Services\WorkforceAccess::isHr($attendanceEmployee) && ! \App\Services\WorkforceAccess::isAdmin($user),
            403,
            'Only your company admin may override HR attendance.'
        );
        $attendancePayload = [
            'status' => $data['status'],
            'clock_in' => $data['clock_in'] ? Carbon::createFromFormat('H:i', $data['clock_in'])->format('H:i:s') : null,
            'work_from_type' => $data['status'] === 'wfh' ? 'wfh' : 'office',
        ];
        if (Schema::hasColumn('attendances', 'company_id')) {
            $attendancePayload['company_id'] = $attendanceEmployee?->company_id;
        }

        $record = Attendance::updateOrCreate(
            ['user_id' => $data['user_id'], 'date' => $data['date']],
            $attendancePayload
        );
        $this->applyOrganizationAttendanceRules($record);

        return back()->with('success', 'Attendance updated');
    }

    /**
     * Create Attendance - ADMIN ONLY
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Only authorized management can access bulk create page
        if (!$this->canManageAttendance($user) && !$this->isAdminUser($user)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to add attendance.');
        }

        $departments = Department::get();
        $usersQuery = \App\Models\User::where('company_id', $user->company_id)
            ->whereNull('archived_at')
            ->orderBy('name');
        if ($request->input('staff_category') === 'authority') {
            \App\Services\WorkforceAccess::hrUsers($usersQuery);
        } else {
            \App\Services\WorkforceAccess::attendanceEmployees($usersQuery);
        }
        $users = $usersQuery->get();
        $request->validate(['date' => 'nullable|date_format:Y-m-d', 'user_id' => 'nullable|integer']);
        abort_if($request->filled('user_id') && ! $users->contains('id', $request->integer('user_id')), 403);
        $year = now()->format('Y');
        $month = now()->format('m');
        $location = CompanyAddress::all();

        return view('admin.attendance.create', compact('users','departments','year','month','location'));
    }

    /**
     * Store Attendance
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Normalize user_id to array (accept both single id and array)
        $userIds = $request->input('user_id', []);
        if (!is_array($userIds)) $userIds = [$userIds];
        $request->merge(['user_id' => $userIds]);

        $targetUsers = User::whereIn('id', $userIds)->get();
        abort_if(
            ! \App\Services\WorkforceAccess::isAdmin($user)
            && $targetUsers->contains(fn ($target) => \App\Services\WorkforceAccess::isHr($target)),
            403,
            'Only your company admin may add or overwrite HR attendance. Use dashboard clock-in and clock-out for daily attendance.'
        );

        $canManageAll = $this->canManageAttendance($user) || $this->isAdminUser($user);
        $isSelf = count($userIds) === 1 && (int) reset($userIds) === (int) $user->id;
        $isManagerForEmployees = false;
        if ($this->isManagerOrHrUser($user) && method_exists($user, 'visibleEmployeeIds')) {
            $visibleIds = $user->visibleEmployeeIds();
            if ($visibleIds instanceof \Illuminate\Support\Collection) {
                $isManagerForEmployees = collect($userIds)->every(fn($id) => $visibleIds->contains($id));
            }
        }

        if (!$canManageAll && !$isSelf && !$isManagerForEmployees) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to add attendance.'
                ], 403);
            }
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to add attendance.');
        }

        $parsedDateRange = null;
        $request->validate([
            'user_id'   => 'required|array|min:1',
            'mark_attendance_by' => 'nullable|in:month,date',
            'year' => 'required_if:mark_attendance_by,month|nullable|integer|min:2000|max:2100',
            'month' => 'required_if:mark_attendance_by,month|nullable|integer|min:1|max:12',
            'date' => 'required_without:mark_attendance_by|nullable|date_format:Y-m-d',
            'date_range' => ['required_if:mark_attendance_by,date', 'nullable', function ($attribute, $value, $fail) use (&$parsedDateRange) {
                $parsedDateRange = $this->parseManualDateRange($value);
                if ($parsedDateRange === null) {
                    $fail('Select a valid date range (MM/DD/YYYY - MM/DD/YYYY), with the end on or after the start.');
                }
            }],
            'location_id' => 'nullable|integer|exists:tenant.company_addresses,id',
            'clock_in_address' => 'nullable|string|max:1000',
            'user_id.*' => 'required|integer|exists:users,id',
            'clock_in'  => ['nullable', function ($attribute, $value, $fail) {
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
                    $fail('Clock In must be in HH:MM format (00–23 hours, 00–59 minutes).');
                }
            }],
            'clock_out' => ['nullable', function ($attribute, $value, $fail) use ($request) {
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
                    $fail('Clock Out must be in HH:MM format (00–23 hours, 00–59 minutes).');
                }
            }],
        ]);

        // Normalize user_id to array (accept both single id and array)
        $userIds = $request->input('user_id', []);
        if (!is_array($userIds)) $userIds = [$userIds];

        // Check every target before writing any date, including mixed-company bulk requests.
        foreach (User::whereIn('id', $userIds)->get() as $employee) {
            \App\Services\TenantScope::authorizeCompany($employee->company_id);
        }

        // If user selected mark by date-range (bulk)
        if (in_array($request->mark_attendance_by, ['month', 'date'], true)) {
            if ($request->mark_attendance_by === 'month') {
                $startDate = Carbon::create($request->integer('year'), $request->integer('month'), 1)->startOfDay();
                $endDate = $startDate->copy()->endOfMonth();
            } else {
                [$startDate, $endDate] = $parsedDateRange;
            }

            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($userIds as $userId) {
                foreach ($period as $date) {
                    $existing = Attendance::where('user_id', $userId)->whereDate('date', $date)->first();
                    if ($existing && ! $request->has('overwrite_attendance')) {
                        continue;
                    }

                    // store time-only strings for DB TIME columns (H:i:s)
                    $clockInVal = $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in)->format('H:i:s') : null;
                    $clockOutVal = $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out)->format('H:i:s') : null;

                    $employeeForAttendance = User::find($userId);
                    $attendancePayload = [
                        'department_id'  => $request->department_id,
                        'status'         => $request->status,
                        'working_from'   => $request->working_from,
                        'location_id'    => $request->location_id,
                        'clock_in_address' => $request->input('clock_in_address'),
                        'work_from_type' => $request->work_from_type,
                        'late'           => ($request->late == 'yes') ? 'yes' : 'no',
                        'half_day'       => ($request->half_day == 'yes') ? 'yes' : 'no',
                        'half_day_type'  => ($request->half_day == 'yes') ? $request->half_day_duration : null,
                        'clock_in'       => $clockInVal,
                        'clock_out'      => $clockOutVal,
                    ];
                    if (Schema::hasColumn('attendances', 'company_id')) {
                        $attendancePayload['company_id'] = $employeeForAttendance?->company_id;
                    }

                    $record = $existing ?? new Attendance(['user_id' => $userId, 'date' => $date->toDateString()]);
                    $record->fill($attendancePayload)->save();
                    $record = $this->applyOrganizationAttendanceRules($record);

                    // prepare a Carbon instance for the notification (combine date + time)
                    $record->clocked_at = $this->buildDateTimeFromDateAndTime($record->date, $record->clock_in) ?? Carbon::now();

                    // notify the user (database channel)
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        try {
                            $user->notify(new ClockInNotification($record));
                        } catch (\Throwable $e) {
                            Log::error('ClockInNotification failed (bulk)', ['user' => $userId, 'error' => $e->getMessage()]);
                        }

                        $this->notifyAttendanceActivity(
                            'Attendance Updated',
                            $user->name . ' attendance was updated for ' . Carbon::parse($record->date)->format('M d, Y') . '.',
                            route('attendance.index'),
                            ['type' => 'attendance_updated', 'record_id' => $record->id, 'employee_id' => $user->id, 'icon' => 'fa-clock', 'color' => 'success'],
                            $user->company_id
                        );
                    }
                }
            }

        } else {
            // Single-date branch
            foreach ($userIds as $userId) {
                $existing = Attendance::where('user_id', $userId)
                    ->whereDate('date', $request->date)
                    ->first();

                if ($existing && !$request->has('overwrite_attendance')) {
                    continue;
                }

                $clockInVal = $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in)->format('H:i:s') : null;
                $clockOutVal = $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out)->format('H:i:s') : null;

                $employeeForAttendance = User::find($userId);
                $attendancePayload = [
                    'department_id'  => $request->department_id,
                    'status'         => $request->status,
                    'working_from'   => $request->working_from,
                    'location_id'    => $request->location_id,
                    'clock_in_address' => $request->input('clock_in_address'),
                    'work_from_type' => $request->work_from_type,
                    'late'           => ($request->late == 'yes') ? 'yes' : 'no',
                    'half_day'       => ($request->half_day == 'yes') ? 'yes' : 'no',
                    'half_day_type'  => ($request->half_day == 'yes') ? $request->half_day_duration : null,
                    'clock_in'       => $clockInVal,
                    'clock_out'      => $clockOutVal,
                ];
                if (Schema::hasColumn('attendances', 'company_id')) {
                    $attendancePayload['company_id'] = $employeeForAttendance?->company_id;
                }

                $record = $existing ?? new Attendance(['user_id' => $userId, 'date' => $request->date]);
                $record->fill($attendancePayload)->save();
                $record = $this->applyOrganizationAttendanceRules($record);

                // prepare a Carbon instance for the notification
                $record->clocked_at = $this->buildDateTimeFromDateAndTime($record->date, $record->clock_in) ?? Carbon::now();

                // notify the user
                $user = \App\Models\User::find($userId);
                if ($user) {
                    try {
                        $user->notify(new ClockInNotification($record));
                    } catch (\Throwable $e) {
                        Log::error('ClockInNotification failed (single)', ['user' => $userId, 'error' => $e->getMessage()]);
                    }

                    $this->notifyAttendanceActivity(
                        'Attendance Updated',
                        $user->name . ' attendance was updated for ' . Carbon::parse($record->date)->format('M d, Y') . '.',
                        route('attendance.index'),
                        ['type' => 'attendance_updated', 'record_id' => $record->id, 'employee_id' => $user->id, 'icon' => 'fa-clock', 'color' => 'success'],
                        $user->company_id
                    );
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Attendance saved successfully.']);
        }

        return redirect()->route($request->input('staff_category') === 'authority' ? 'admin.authority-attendance' : 'attendance.index')->with('success', 'Attendance saved successfully.');
    }

    /**
     * Show Attendance Details - ROLE BASED
     */
    public function showAttendanceDetails(Request $request)
    {
        try {
            $user = Auth::user();

            Log::info('showAttendanceDetails() called', [
                'attendance_id' => $request->attendance_id,
                'user_id'       => $request->user_id,
                'date'          => $request->date,
            ]);

            $attendanceId = $request->attendance_id;
            $requestedUserId = $request->user_id;
            $date = $request->date;

            // Check permission: Employee can only view their own details
            if ($user->role === 'employee' && $requestedUserId != $user->id) {
                return response('<div class="alert alert-danger">You can only view your own attendance details.</div>', 403);
            }

            $attendance = Attendance::with('user', 'user.employeeDetail')->find($attendanceId);

            if (!$attendance) {
                Log::warning('Attendance record not found', ['attendance_id' => $attendanceId]);
                return response('<div class="alert alert-danger">Attendance record not found.</div>', 404);
            }
            if (! $attendance->user || ((int) $attendance->user_id !== (int) $user->id && ! \App\Services\WorkforceAccess::canAccessRecord($user, $attendance->user, $attendance->staff_category))) {
                return response('<div class="alert alert-danger">You cannot access this attendance record.</div>', 403);
            }

            if (($user->role === 'employee' || \App\Services\WorkforceAccess::isHr($user)) && (int) $attendance->user_id !== (int) $user->id) {
                return response('<div class="alert alert-danger">You can only view your own attendance details.</div>', 403);
            }

            $defaultTz = (config('app.timezone') && config('app.timezone') !== 'UTC') ? config('app.timezone') : 'Asia/Kolkata';
            $companyTimezone = $defaultTz;
            $startTime = $attendance->clock_in_datetime;
            $endTime = $attendance->clock_out_datetime;
            $firstClockIn = $startTime ? $startTime->format('h:i A') : 'N/A';
            $lastClockOut = $endTime ? $endTime->format('h:i A') : 'N/A';
            $notClockedOut = empty($attendance->clock_out);
            $totalTimeSeconds = $attendance->total_seconds;
            $totalTimeFormatted = $attendance->total_duration;
            $attendanceDate = Carbon::parse($attendance->date, $defaultTz);
            $attendanceSettings = AttendanceSetting::first();
            $normalizedActivities = [];

            return view('admin.attendance.attendance_details', [
                'attendance' => $attendance,
                'attendanceActivity' => $normalizedActivities,
                'totalTimeFormatted' => $totalTimeFormatted,
                'firstClockIn' => $firstClockIn,
                'lastClockOut' => $lastClockOut,
                'notClockedOut' => $notClockedOut,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'attendanceDate' => $attendanceDate,
                'attendanceSettings' => $attendanceSettings,
                'totalTimeSeconds' => $totalTimeSeconds,
                'companyTimezone' => $companyTimezone,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in showAttendanceDetails()', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('<div class="alert alert-danger">Something went wrong: ' . e($e->getMessage()) . '</div>', 500);
        }
    }

    /**
     * Edit Attendance
     */
    public function edit(Request $request)
    {
        abort_unless($this->isAdminUser(Auth::user()), 403, 'Only your company admin may edit attendance.');
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson() && !$request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }
            return response('<div class="alert alert-danger m-3">Unauthenticated.</div>', 401);
        }

        $attendanceId = $request->attendance_id;
        $userId       = $request->user_id;
        $date         = $request->date;

        if (!$userId || !$date) {
            if ($request->expectsJson() && !$request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Missing required parameters.'], 400);
            }
            return response('<div class="alert alert-danger m-3">Missing required parameters (employee or date).</div>', 400);
        }

        $canManageAll = $this->canManageAttendance($user) || $this->isAdminUser($user);
        $isSelf = (int) $userId === (int) $user->id;
        $isManagerForEmployee = false;
        if ($this->isManagerOrHrUser($user) && method_exists($user, 'visibleEmployeeIds')) {
            $visibleIds = $user->visibleEmployeeIds();
            if ($visibleIds instanceof \Illuminate\Support\Collection && $visibleIds->contains($userId)) {
                $isManagerForEmployee = true;
            }
        }

        if (!$canManageAll && !$isSelf && !$isManagerForEmployee) {
            if ($request->expectsJson() && !$request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to edit attendance.'
                ], 403);
            }
            return response('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-1"></i> You do not have permission to edit attendance.</div>', 403);
        }

        $attendance = $attendanceId ? Attendance::find($attendanceId) : null;

        if ($attendanceId && !$attendance) {
            if ($request->expectsJson() && !$request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance record not found.'
                ], 404);
            }
            return response('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-1"></i> Attendance record not found.</div>', 404);
        }

        if ($attendance && !$canManageAll && !$isManagerForEmployee && (int) $attendance->user_id !== (int) $user->id) {
            if ($request->expectsJson() && !$request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to edit this attendance.'
                ], 403);
            }
            return response('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-1"></i> You do not have permission to edit this attendance.</div>', 403);
        }

        $employee    = User::with(['employeeDetail.department', 'employeeDetail.designation'])->find($userId);
        if (!$employee) {
            if ($request->expectsJson() && !$request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee not found.'
                ], 404);
            }
            return response('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-1"></i> Employee not found.</div>', 404);
        }

        $departments = Department::all();
        $location    = CompanyAddress::all();

        return view('admin.attendance.edit_form', compact(
            'attendance',
            'employee',
            'departments',
            'location',
            'userId',
            'date'
        ));
    }

    /**
     * Update Attendance
     */
    public function update(Request $request, Attendance $attendance)
    {
        abort_unless($this->isAdminUser(Auth::user()), 403, 'Only your company admin may edit attendance.');
        \App\Services\TenantScope::authorizeCompany($attendance->user?->company_id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $canManageAll = $this->canManageAttendance($user) || $this->isAdminUser($user);
        $isSelf = (int) $attendance->user_id === (int) $user->id;
        $isManagerForEmployee = false;
        if ($this->isManagerOrHrUser($user) && method_exists($user, 'visibleEmployeeIds')) {
            $visibleIds = $user->visibleEmployeeIds();
            if ($visibleIds instanceof \Illuminate\Support\Collection && $visibleIds->contains($attendance->user_id)) {
                $isManagerForEmployee = true;
            }
        }

        if (!$canManageAll && !$isSelf && !$isManagerForEmployee) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update attendance.'
            ], 403);
        }

        $request->validate([
            'clock_in'  => 'nullable|date_format:H:i',
            'location_id' => 'nullable|integer|exists:tenant.company_addresses,id',
            'clock_in_address' => 'nullable|string|max:1000',
            'clock_out' => 'nullable|date_format:H:i',
            'status'    => 'required|string'
        ]);

        $updateData = [
            'clock_in'  => $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in)->format('H:i:s') : null,
            'clock_out' => $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out)->format('H:i:s') : null,
            'status'    => $request->status,
        ];

        if ($request->has('clock_in_address')) {
            $updateData['clock_in_address'] = $request->input('clock_in_address');
        }
        if ($request->has('location_id')) {
            $updateData['location_id'] = $request->location_id;
        }
        if ($request->filled('work_from_type')) {
            $updateData['work_from_type'] = $request->work_from_type;
        }
        if ($request->filled('working_from')) {
            $updateData['working_from'] = $request->working_from;
        }
        if ($request->filled('late')) {
            $updateData['late'] = ($request->late == 'yes') ? 'yes' : 'no';
        }
        if ($request->filled('half_day')) {
            $updateData['half_day'] = ($request->half_day == 'yes') ? 'yes' : 'no';
        }
        if ($request->has('overwrite_attendance')) {
            $updateData['overwrite_attendance'] = ($request->overwrite_attendance == 'yes') ? 'yes' : 'no';
        }

        $attendance->update($updateData);
        $attendance = $this->applyOrganizationAttendanceRules($attendance);

        if ($attendance->user && (int) $attendance->user_id !== (int) Auth::id()) {
            SystemNotificationService::notifyUser(
                $attendance->user,
                'Attendance Updated',
                'Your attendance for ' . Carbon::parse($attendance->date)->format('d M Y') . ' was updated by ' . Auth::user()->name . '.',
                route('attendance.index'),
                ['employee_id' => $attendance->user_id, 'type' => 'attendance_updated', 'icon' => 'fa-calendar-check']
            );
        }

        return response()->json(['success' => true, 'message' => 'Attendance updated successfully.']);
    }

    /**
     * Archive one employee's attendance records for a selected month
     */
    public function archiveMonth(Request $request)
    {
        $user = Auth::user();

        if (!$this->canManageAttendance($user) && !$this->isAdminUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to archive attendance.'
            ], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $archived = Attendance::where('user_id', $data['user_id'])
            ->whereNull('archived_at')
            ->whereMonth('date', $data['month'])
            ->whereYear('date', $data['year'])
            ->update(['archived_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $archived > 0
                ? 'Monthly attendance records archived successfully.'
                : 'No active attendance records were found for this employee and month.',
            'archived' => $archived,
        ]);
    }

    /**
     * Archived attendance records
     */
    public function archive(Request $request)
    {
        $user = Auth::user();

        if (!$this->canManageAttendance($user) && !$this->isAdminUser($user)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to view archived attendance.');
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $query = Attendance::with('user.employeeDetail.designation')
            ->whereNotNull('archived_at');
        if ($request->input('staff_category') === 'authority') {
            $query->whereHas('user', fn ($userQuery) => \App\Services\WorkforceAccess::hrUsers($userQuery));
        } else {
            $query->whereHas('user', fn ($userQuery) => \App\Services\WorkforceAccess::attendanceEmployees($userQuery));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', '%' . $search . '%')
                    ->orWhereDate('date', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $attendances = $query->orderByDesc('archived_at')->paginate($perPage)->withQueryString();

        return view('admin.attendance.archive', compact('attendances'));
    }

    /**
     * Restore archived attendance
     */
    public function restore($id)
    {
        $user = Auth::user();

        if (!$this->canManageAttendance($user) && !$this->isAdminUser($user)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to restore attendance.');
        }

        $attendance = Attendance::whereNotNull('archived_at')->findOrFail($id);

        $attendance->forceFill([
            'archived_at' => null,
        ])->save();

        return redirect()->route('attendance.archive')
            ->with('success', 'Attendance record restored successfully.');
    }

    /**
     * Clock In - AVAILABLE FOR BOTH ADMIN AND EMPLOYEE
     */
    public function clockIn(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        // Clock-in needs a live photo and must respect the clock-in window, exactly like the dashboard clock-in.
        if (! $request->filled('clock_in_selfie')) {
            $message = 'Capture your photo to clock in.';
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => $message], 422)
                : redirect()->back()->with('error', $message);
        }
        if (! $request->expectsJson()) {
            return app(DashboardController::class)->clockIn($request);
        }
        $blocked = \App\Services\AttendanceClockPolicy::clockInBlockedReason($user, Carbon::now($this->resolveEmployeeTimezone($request->input('clock_in_timezone') ?? $request->input('timezone'))));
        if ($blocked) {
            return response()->json(['status' => false, 'message' => $blocked], 422);
        }
        $response = app(DashboardController::class)->clockIn($request);
        $error = session()->pull('error');
        $latest = Attendance::where('user_id', $user->id)->latest('date')->latest('id')->first();
        return response()->json(['status' => ! $error, 'message' => $error ?: (session()->pull('success') ?: 'Clock-in recorded'), 'attendance' => $latest], $error ? 422 : 200);
    }

    /**
     * Use the deployment timezone for all new attendance sessions.
     */
    protected function resolveEmployeeTimezone(?string $clientTz = null, ?float $lat = null, ?float $lng = null): string
    {
        // One business timezone; device location must not change attendance dates.
        return config('app.timezone', 'Asia/Kolkata');
    }
}
