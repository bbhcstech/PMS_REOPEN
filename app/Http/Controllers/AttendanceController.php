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
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'format' => 'nullable|in:csv',
            'export_scope' => 'nullable|in:month,today,range',
            'start_date' => 'nullable|required_if:export_scope,range|date_format:Y-m-d',
            'end_date' => [
                'nullable',
                'required_if:export_scope,range',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->query('export_scope') === 'range' && $request->filled('start_date') && $value) {
                        $from = Carbon::createFromFormat('Y-m-d', (string) $request->query('start_date'));
                        $to = Carbon::createFromFormat('Y-m-d', (string) $value);
                        if ($from->diffInDays($to) > 366) {
                            $fail('Select a date range of up to 366 days.');
                        }
                    }
                },
            ],
        ]);
        $month = $request->integer('month') ?: now()->month;
        $year = $request->integer('year') ?: now()->year;
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $isCsvExport = $request->query('format') === 'csv';
        $exportScope = $request->query('export_scope', 'month');
        if ($isCsvExport && $exportScope === 'today') {
            $exportStart = now()->startOfDay();
            $exportEnd = $exportStart->copy();
            $exportFilename = sprintf('attendance-%s.csv', $exportStart->toDateString());
        } elseif ($isCsvExport && $exportScope === 'range') {
            $exportStart = Carbon::createFromFormat('Y-m-d', (string) $request->query('start_date'))->startOfDay();
            $exportEnd = Carbon::createFromFormat('Y-m-d', (string) $request->query('end_date'))->startOfDay();
            $exportFilename = sprintf('attendance-%s-to-%s.csv', $exportStart->toDateString(), $exportEnd->toDateString());
        } else {
            $exportStart = $start->copy();
            $exportEnd = $end->copy()->startOfDay();
            $exportFilename = sprintf('attendance-%04d-%02d.csv', $year, $month);
        }
        $rangeStart = $isCsvExport ? $exportStart->copy()->startOfMonth() : $start;
        $rangeEnd = $isCsvExport ? $exportEnd->copy()->endOfMonth() : $end;
        $canManage = $this->canManageAttendance($actor);
        $authorityView = $canManage && $request->input('staff_category') === 'authority';
        $query = User::with(['employeeDetail.designation', 'companyStaffRole'])->where('company_id', $actor->company_id)
            ->whereRaw('LOWER(COALESCE(role, ?)) <> ?', ['', 'client'])->whereNull('archived_at');
        if ($authorityView) {
            \App\Services\WorkforceAccess::hrUsers($query);
        } elseif ($canManage) {
            \App\Services\WorkforceAccess::attendanceEmployees($query);
        } else {
            $query->whereKey($actor->id);
        }
        $employees = $query->orderBy('name')->get();
        $teamView = $request->query('view') === 'team';
        $visibleEmployees = $employees;
        if ($teamView && $request->filled('search')) {
            $search = mb_strtolower(trim($request->query('search')));
            $visibleEmployees = $employees->filter(fn ($employee) => str_contains(mb_strtolower($employee->name . ' ' . $employee->email . ' ' . ($employee->employeeDetail?->employee_id ?? '')), $search))->values();
        }
        $selectedUserIds = collect($request->input('user_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $selectedEmployee = $request->filled('user_id')
            ? $employees->firstWhere('id', $request->integer('user_id'))
            : ($employees->firstWhere('id', $actor->id) ?? $employees->first());
        abort_if($request->filled('user_id') && ! $selectedEmployee, 403, 'You cannot view this employee.');
        if ($isCsvExport) {
            if ($teamView) {
                $exportEmployees = $selectedUserIds->isNotEmpty()
                    ? $visibleEmployees->whereIn('id', $selectedUserIds->all())->values()
                    : $visibleEmployees;
            } elseif ($selectedUserIds->isNotEmpty()) {
                $exportEmployees = $employees->whereIn('id', $selectedUserIds->all())->values();
            } elseif ($request->filled('user_id') && $selectedEmployee) {
                $exportEmployees = collect([$selectedEmployee]);
            } else {
                $exportEmployees = $employees;
            }
            $recordEmployeeIds = $exportEmployees->pluck('id');
        } else {
            $recordEmployeeIds = $teamView ? $visibleEmployees->pluck('id') : collect($selectedEmployee ? [$selectedEmployee->id] : []);
        }
        $recordsQuery = Attendance::whereIn('user_id', $recordEmployeeIds)
            ->whereNull('archived_at')->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])->orderBy('id');
        $records = $recordEmployeeIds->isNotEmpty() ? $recordsQuery->get() : collect();
        $holidayQuery = Holiday::whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
        if (Schema::connection('tenant')->hasColumn('holidays', 'archived_at')) $holidayQuery->whereNull('archived_at');
        if (Schema::connection('tenant')->hasColumn('holidays', 'company_id')) {
            // Older holiday rows belong to the current tenant database and have no company_id.
            $holidayQuery->where(fn ($query) => $query->where('company_id', $actor->company_id)->orWhereNull('company_id'));
        }
        $holidays = $holidayQuery->get();
        $leaves = collect();
        if ($recordEmployeeIds->isNotEmpty()) {
            $leaveQuery = Leave::whereIn('user_id', $recordEmployeeIds)->where('status', 'approved');
            if (Schema::connection('tenant')->hasColumn('leaves', 'archived_at')) $leaveQuery->whereNull('archived_at');
            $leaveQuery->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
                if (Schema::connection('tenant')->hasColumn('leaves', 'start_date')) {
                    $query->orWhere(function ($range) use ($rangeStart, $rangeEnd) {
                        $range->whereDate('start_date', '<=', $rangeEnd)->whereDate('end_date', '>=', $rangeStart);
                    });
                }
            });
            $leaves = $leaveQuery->get();
        }
        $schedule = WorkScheduleService::getSettings();
        if ($isCsvExport) {
            return $this->streamAttendanceCsv($exportEmployees, $exportStart, $exportEnd, $exportFilename, $records, $holidays, $leaves, $schedule);
        }
        if ($teamView) {
            $builder = app(\App\Services\AttendanceCalendar::class);
            $workingDays = \App\Models\AppSetting::getCompanyWorkingDays();
            $recordsByEmployee = $records->groupBy('user_id');
            $leavesByEmployee = $leaves->groupBy('user_id');
            $rows = $visibleEmployees->map(fn ($employee) => [
                'employee' => $employee,
                'calendar' => $builder->build($start, $recordsByEmployee->get($employee->id, collect()), $holidays, $leavesByEmployee->get($employee->id, collect()), $employee->employeeDetail, $workingDays, $employee->id, $schedule),
            ]);
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
     * Stream attendance CSV rows for a selected month, today, or custom date range.
     */
    private function streamAttendanceCsv($employees, Carbon $exportStart, Carbon $exportEnd, string $filename, $records, $holidays, $leaves, array $schedule)
    {
        $builder = app(\App\Services\AttendanceCalendar::class);
        $workingDays = \App\Models\AppSetting::getCompanyWorkingDays();
        $recordsByEmployee = $records->groupBy('user_id');
        $leavesByEmployee = $leaves->groupBy('user_id');
        $singleMonth = $exportStart->isSameDay($exportStart->copy()->startOfMonth())
            && $exportEnd->isSameDay($exportStart->copy()->endOfMonth()->startOfDay());

        $rows = $employees->map(function ($employee) use ($builder, $exportStart, $exportEnd, $singleMonth, $recordsByEmployee, $holidays, $leavesByEmployee, $workingDays, $schedule) {
            $empRecords = $recordsByEmployee->get($employee->id, collect());
            $empLeaves = $leavesByEmployee->get($employee->id, collect());
            if ($singleMonth) {
                $days = collect($builder->build($exportStart->copy(), $empRecords, $holidays, $empLeaves, $employee->employeeDetail, $workingDays, $employee->id, $schedule)['days']);
            } else {
                $days = collect();
                for ($cursor = $exportStart->copy()->startOfMonth(); $cursor->lte($exportEnd); $cursor->addMonthNoOverflow()->startOfMonth()) {
                    $monthCalendar = $builder->build($cursor->copy(), $empRecords, $holidays, $empLeaves, $employee->employeeDetail, $workingDays, $employee->id, $schedule);
                    $days = $days->merge(array_filter($monthCalendar['days'], fn ($day) => $day['date']->betweenIncluded($exportStart, $exportEnd)));
                }
            }

            return ['employee' => $employee, 'days' => $days->values()];
        });

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Employee', 'Employee ID', 'Email', 'Date', 'Status', 'Work from home', 'Hours', 'Clock in', 'Clock out', 'Clock-in address', 'Clock-out address']);
            $safe = static fn ($value) => preg_match('/^[=+@\-\t\r\n]/u', (string) $value) ? "'" . $value : $value;
            foreach ($rows as $row) {
                foreach ($row['days'] as $day) {
                    $sessions = $day['daily']->isEmpty() ? collect([null]) : $day['daily'];
                    foreach ($sessions as $session) {
                        fputcsv($output, array_map($safe, [$row['employee']->name, $row['employee']->employeeDetail?->employee_id, $row['employee']->email, $day['key'], $day['status'], $day['wfh'] ? 'Yes' : 'No', $this->secondsToHhmm($session ? (int) $session->total_seconds : 0), $session?->clock_in, $session?->clock_out, $session?->clock_in_address, $session?->clock_out_address]));
                    }
                }
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
            $usersQuery = \App\Services\WorkforceAccess::attendanceEmployees(
                User::with('employeeDetail')->whereNull('archived_at')
            );

            if ($user->company_id) {
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
