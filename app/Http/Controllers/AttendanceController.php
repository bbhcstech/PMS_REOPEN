<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\Attendance\AttendanceLocationViews;
use App\Http\Controllers\Concerns\Attendance\AttendancePolicySettings;
use App\Http\Controllers\Concerns\Attendance\AttendanceRecords;
use App\Http\Controllers\Concerns\Attendance\AttendanceReports;
use App\Models\Attendance;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use App\Services\SystemNotificationService;
use App\Services\WorkforceAccess;
use App\Services\WorkScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceController extends Controller
{
    use AttendanceReports;
    use AttendanceLocationViews;
    use AttendanceRecords;
    use AttendancePolicySettings;

    private function notifyAttendanceActivity(string $title, string $message, ?string $url, array $data, ?int $companyId): void
    {
        $employee = User::where('company_id', $companyId)->find($data['employee_id'] ?? null);
        $record = Attendance::find($data['record_id'] ?? null);
        if ($employee && (\App\Services\WorkforceAccess::isAuthority($employee) || $record?->staff_category === 'authority')) {
            $url = route('admin.authority-attendance', ['view' => 'team', 'user_id' => $employee->id]);
            SystemNotificationService::notifyAdmins($title, $message, $url, $data);
        } else {
            SystemNotificationService::notifyAllRoles($title, $message, $url, $data, $companyId);
        }
    }

    /**
     * Role Helper Methods (Case-insensitive & SuperAdmin aware)
     */
    private function isAdminUser($user): bool
    {
        if (!$user) return false;
        $role = strtolower((string) ($user->role ?? ''));
        return \App\Services\WorkforceAccess::isAdmin($user);
    }

    private function isManagerOrHrUser($user): bool
    {
        if (!$user) return false;
        $role = strtolower((string) ($user->role ?? ''));
        return in_array($role, ['manager', 'hr'], true);
    }

    private function canManageAttendance($user): bool
    {
        if (!$user) return false;
        $role = strtolower((string) ($user->role ?? ''));
        return \App\Services\WorkforceAccess::isAdmin($user);
    }

    private function isEmployeeUser($user): bool
    {
        if (!$user) return false;
        return !$this->isAdminUser($user) && !$this->isManagerOrHrUser($user);
    }

    /**
     * Main Attendance Index - ROLE BASED
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (WorkforceAccess::isHr($user)) {
            return $this->personalReport($request, $user);
        }

        return $this->calendar($request);
    }

    public function hrAttendance(Request $request)
    {
        $user = Auth::user();
        abort_unless(WorkforceAccess::isHr($user), 403);

        return $this->personalReport($request, $user);
    }

    public function authorityIndex(Request $request)
    {
        $user = Auth::user();
        if (WorkforceAccess::isHr($user)) {
            return $this->personalReport($request, $user);
        }

        abort_unless($this->isAdminUser($user), 403);
        $request->merge(['staff_category' => 'authority']);
        return $this->calendar($request);
    }

    private function calendar(Request $request)
    {
        \App\Services\CompanyStaffSchema::ensure();
        $actor = Auth::user();
        abort_unless($actor, 401);
        abort_unless($actor->company_id, 403, 'No company assigned to this account.');
        // Clock-in boundary: whoever missed the clock-in window on a working day shows as absent.
        try {
            \App\Services\AttendanceClockPolicy::markMissedClockIns((int) $actor->company_id);
        } catch (\Throwable $e) {
            report($e);
        }
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'user_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $canManage = $this->canManageAttendance($actor);
        $staffCategory = $canManage ? ($request->input('staff_category') === 'authority' ? 'authority' : 'employee') : null;
        $query = User::with(['employeeDetail.designation', 'companyStaffRole'])->where('company_id', $actor->company_id)
            ->whereRaw('LOWER(COALESCE(role, ?)) <> ?', ['', 'client'])->whereNull('archived_at');
        if ($canManage) \App\Services\WorkforceAccess::usersWithHistory($query, $staffCategory, 'attendances');
        else $query->whereKey($actor->id);
        $employees = $query->orderBy('name')->get();
        $teamView = $request->query('view') === 'team';
        $visibleEmployees = $employees;
        if ($teamView && $request->filled('search')) {
            $search = mb_strtolower(trim($request->query('search')));
            $visibleEmployees = $employees->filter(fn ($employee) => str_contains(mb_strtolower($employee->name . ' ' . $employee->email . ' ' . ($employee->employeeDetail?->employee_id ?? '')), $search))->values();
        }
        $selectedEmployee = $request->filled('user_id')
            ? $employees->firstWhere('id', $request->integer('user_id'))
            : ($employees->firstWhere('id', $actor->id) ?? $employees->first());
        abort_if($request->filled('user_id') && ! $selectedEmployee, 403, 'You cannot view this employee.');
        $recordEmployeeIds = $teamView ? $visibleEmployees->pluck('id') : collect($selectedEmployee ? [$selectedEmployee->id] : []);
        $recordsQuery = Attendance::whereIn('user_id', $recordEmployeeIds)
            ->whereNull('archived_at')->whereBetween('date', [$start->toDateString(), $end->toDateString()])->orderBy('id');
        if ($canManage) \App\Services\WorkforceAccess::records($recordsQuery, $staffCategory);
        $records = $recordEmployeeIds->isNotEmpty() ? $recordsQuery->get() : collect();
        $holidayQuery = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        if (Schema::connection('tenant')->hasColumn('holidays', 'archived_at')) $holidayQuery->whereNull('archived_at');
        if (Schema::connection('tenant')->hasColumn('holidays', 'company_id')) {
            // Older holiday rows belong to the current tenant database and have no company_id.
            $holidayQuery->where(fn ($query) => $query->where('company_id', $actor->company_id)->orWhereNull('company_id'));
        }
        $holidays = $holidayQuery->get();
        $leaves = collect();
        if ($recordEmployeeIds->isNotEmpty()) {
            $leaveQuery = Leave::whereIn('user_id', $recordEmployeeIds)->where('status', 'approved');
            if ($canManage) \App\Services\WorkforceAccess::records($leaveQuery, $staffCategory);
            if (Schema::connection('tenant')->hasColumn('leaves', 'archived_at')) $leaveQuery->whereNull('archived_at');
            $leaveQuery->where(function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
                if (Schema::connection('tenant')->hasColumn('leaves', 'start_date')) {
                    $query->orWhere(function ($range) use ($start, $end) {
                        $range->whereDate('start_date', '<=', $end)->whereDate('end_date', '>=', $start);
                    });
                }
            });
            $leaves = $leaveQuery->get();
        }
        $schedule = WorkScheduleService::getSettings();
        if ($teamView) {
            $builder = app(\App\Services\AttendanceCalendar::class);
            $workingDays = \App\Models\AppSetting::getCompanyWorkingDays();
            $recordsByEmployee = $records->groupBy('user_id');
            $leavesByEmployee = $leaves->groupBy('user_id');
            $rows = $visibleEmployees->map(fn ($employee) => [
                'employee' => $employee,
                'calendar' => $builder->build($start, $recordsByEmployee->get($employee->id, collect()), $holidays, $leavesByEmployee->get($employee->id, collect()), $employee->employeeDetail, $workingDays, $employee->id, $schedule),
            ]);
            if ($request->query('format') === 'csv') {
                return response()->streamDownload(function () use ($rows) {
                    $output = fopen('php://output', 'w');
                    fputcsv($output, ['Employee', 'Employee ID', 'Email', 'Date', 'Status', 'Work from home', 'Hours', 'Clock in', 'Clock out', 'Clock-in address', 'Clock-out address']);
                    $safe = static fn ($value) => preg_match('/^[=+@\-\t\r\n]/u', (string) $value) ? "'" . $value : $value;
                    foreach ($rows as $row) {
                        foreach ($row['calendar']['days'] as $day) {
                            $sessions = $day['daily']->isEmpty() ? collect([null]) : $day['daily'];
                            foreach ($sessions as $session) {
                                fputcsv($output, array_map($safe, [$row['employee']->name, $row['employee']->employeeDetail?->employee_id, $row['employee']->email, $day['key'], $day['status'], $day['wfh'] ? 'Yes' : 'No', $this->secondsToHhmm($session ? (int) $session->total_seconds : 0), $session?->clock_in, $session?->clock_out, $session?->clock_in_address, $session?->clock_out_address]));
                            }
                        }
                    }
                    fclose($output);
                }, sprintf('attendance-%04d-%02d.csv', $year, $month), ['Content-Type' => 'text/csv; charset=UTF-8']);
            }
            $summary = ['employees' => $rows->count(), 'present' => 0, 'absent' => 0, 'seconds' => 0];
            foreach ($rows as $row) {
                $summary['present'] += $row['calendar']['totals']['present'] + $row['calendar']['totals']['late'] + $row['calendar']['totals']['wfh_present'];
                $summary['absent'] += $row['calendar']['totals']['absent'];
                $summary['seconds'] += $row['calendar']['totals']['seconds'];
            }
            $page = $request->integer('page') ?: 1;
            $teamRows = new \Illuminate\Pagination\LengthAwarePaginator($rows->forPage($page, 25)->values(), $rows->count(), 25, $page, ['path' => $request->url(), 'query' => $request->except('page', 'format')]);
            return view('admin.attendance.team', compact('teamRows', 'summary', 'month', 'year', 'start', 'canManage'));
        }
        $calendar = app(\App\Services\AttendanceCalendar::class)->build(
            $start, $records, $holidays, $leaves, $selectedEmployee?->employeeDetail,
            \App\Models\AppSetting::getCompanyWorkingDays(), $selectedEmployee?->id, $schedule
        );
        return view('admin.attendance.calendar', compact('employees', 'selectedEmployee', 'month', 'year', 'start', 'calendar', 'canManage'));
    }

    /**
     * Filter Attendance - ROLE BASED
     */
    public function filter(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        abort_if(WorkforceAccess::isHr($user), 403, 'Use My Attendance to view your own report.');
        // Clock-in boundary: whoever missed the clock-in window on a working day shows as absent.
        try {
            \App\Services\AttendanceClockPolicy::markMissedClockIns((int) $user->company_id);
        } catch (\Throwable $e) {
            report($e);
        }

        $month = (int) ($request->filled('month') ? $request->input('month') : now()->month);
        $year  = (int) ($request->filled('year') ? $request->input('year') : now()->year);
        $userId = $request->input('user_id');
        $department_id = $request->input('department_id');
        $designation_id = $request->input('designation_id');

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // load holidays in month
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($holiday) {
                return [Carbon::parse($holiday->date)->format('Y-m-d') => $holiday->occassion ?? $holiday->title ?? 'Holiday'];
            })->toArray();

        $isAdmin = $this->isAdminUser($user);
        $canManage = $this->canManageAttendance($user);
        $isManagerOrHr = $this->isManagerOrHrUser($user);

        if ($isAdmin || $canManage) {
            $usersQuery = User::with('employeeDetail')->where(function ($q) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(role, ""))'), ['client']);
            });

            if ($user->company_id && strtolower((string)$user->role) === 'hr') {
                $usersQuery->where('company_id', $user->company_id);
            }

            if ($userId) {
                $usersQuery->where('id', (int) $userId);
            }

            if ($department_id) {
                $usersQuery->whereHas('employeeDetail', function ($q) use ($department_id) {
                    $q->where('department_id', $department_id);
                });
            }

            if ($designation_id) {
                $usersQuery->whereHas('employeeDetail', function ($q) use ($designation_id) {
                    $q->where('designation_id', $designation_id);
                });
            }
        } elseif ($isManagerOrHr) {
            $visibleIds = method_exists($user, 'visibleEmployeeIds') ? $user->visibleEmployeeIds() : collect();
            $usersQuery = User::with('employeeDetail')
                ->whereIn('id', $visibleIds);

            if ($userId) {
                $usersQuery->where('id', (int) $userId);
            }

            if ($department_id) {
                $usersQuery->whereHas('employeeDetail', function ($q) use ($department_id) {
                    $q->where('department_id', $department_id);
                });
            }

            if ($designation_id) {
                $usersQuery->whereHas('employeeDetail', function ($q) use ($designation_id) {
                    $q->where('designation_id', $designation_id);
                });
            }
        } else {
            // Employee can only see their own data
            $usersQuery = User::with('employeeDetail')->where('id', $user->id);
        }

        $users = $usersQuery->orderBy('name')->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // if no users, still return empty table
        if ($users->isEmpty()) {
            $attendanceMap = [];
            $designations = ($isAdmin || $canManage) ? Designation::all() : collect();

            $html = view('admin.attendance.table', compact(
                'users',
                'designations',
                'attendanceMap',
                'daysInMonth',
                'month',
                'year'
            ))->render();

            return response()->json([
                'html' => $html,
                'meta' => [
                    'totalEmployees' => $users->count(),
                    'monthName' => Carbon::createFromDate($year, $month, 1)->format('F'),
                    'year' => $year,
                    'daysInMonth' => $daysInMonth,
                ],
            ]);
        }

        $userIds = $users->pluck('id')->toArray();

        $attendances = Attendance::whereIn('user_id', $userIds)
            ->whereNull('archived_at')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        // map attendance rows
        $attendanceMap = [];
        foreach ($attendances as $att) {
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');
            $attendanceMap[$att->user_id][$dateKey] = $att;
        }

        // merge approved leaves
        $leaves = Leave::whereIn('user_id', $userIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->get();

        foreach ($leaves as $leave) {
            $dateKey = Carbon::parse($leave->date)->format('Y-m-d');
            $attendanceMap[$leave->user_id][$dateKey] = (object)[
                'status' => 'leave',
                'leave_type' => $leave->type ?? null,
                'duration' => $leave->duration ?? null,
                'reason' => $leave->reason ?? null,
            ];
        }

        // initialize empty dates and apply holidays
        foreach ($users as $u) {
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $formattedDate = $d->format('Y-m-d');

                if (! isset($attendanceMap[$u->id][$formattedDate])) {
                    // holiday?
                    if (array_key_exists($formattedDate, $holidays)) {
                        $attendanceMap[$u->id][$formattedDate] = (object)[
                            'status' => 'holiday',
                            'occassion' => $holidays[$formattedDate]
                        ];
                    } else {
                        $attendanceMap[$u->id][$formattedDate] = (object)[ 'status' => 'absent' ];
                    }
                }
            }
        }

        $designations = ($isAdmin || $canManage) ? Designation::all() : collect();

        // calculate period totals and pass them to the view used by AJAX
        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);

        $html = view('admin.attendance.table', compact(
            'users',
            'designations',
            'attendanceMap',
            'daysInMonth',
            'month',
            'year',
            'periodTotals'
        ))->render();

        return response()->json([
            'html' => $html,
            'meta' => [
                'totalEmployees' => $users->count(),
                'monthName' => Carbon::createFromDate($year, $month, 1)->format('F'),
                'year' => $year,
                'daysInMonth' => $daysInMonth,
            ],
        ]);
    }

    /**
     * Employee Index (for employee-only view) - EMPLOYEE ONLY
     */
    public function employeeIndex(Request $request)
    {
        $user = Auth::user();

        if (WorkforceAccess::isHr($user)) {
            return $this->personalReport($request, $user);
        }

        // Only employees can access this view
        if ($user->role !== 'employee') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Get days in selected month
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // Get attendance records for logged in employee
        $attendanceRecords = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summary
        $summary = [
            'present' => $attendanceRecords->where('status', 'present')->count(),
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
            'late' => $attendanceRecords->where('status', 'late')->count(),
            'leave' => $attendanceRecords->where('status', 'leave')->count(),
        ];

        return view('attendance.employee.index', compact(
            'user',
            'attendanceRecords',
            'daysInMonth',
            'month',
            'year',
            'summary'
        ));
    }
}
