<?php

namespace App\Http\Controllers\Concerns\Attendance;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use App\Services\AttendanceCalendar;
use App\Services\PdfDocument as Pdf;
use App\Services\TenantScope;
use App\Services\WorkforceAccess;
use App\Services\WorkScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

trait AttendanceReports
{
    private function personalReport(Request $request, User $employee)
    {
        TenantScope::companyFilter($request->integer('company_id') ?: null);
        $filters = $request->validate([
            'period' => 'nullable|in:day,week,month',
            'date' => 'nullable|date_format:Y-m-d',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'user_id' => 'nullable|integer',
            'format' => 'nullable|in:csv',
        ]);
        abort_if(isset($filters['user_id']) && (int) $filters['user_id'] !== (int) $employee->id, 403, 'You can only view your own attendance.');

        $today = now()->startOfDay();
        $period = $filters['period'] ?? 'month';
        $date = match (true) {
            isset($filters['date']) => Carbon::parse($filters['date'])->startOfDay(),
            isset($filters['month']) || isset($filters['year']) => Carbon::create(
                $filters['year'] ?? $today->year, $filters['month'] ?? $today->month, 1
            )->startOfDay(),
            default => $today,
        };

        [$start, $end, $previous, $next] = match ($period) {
            'day' => [$date, $date, $date->copy()->subDay(), $date->copy()->addDay()],
            'week' => [$date->copy()->startOfWeek(Carbon::MONDAY), $date->copy()->endOfWeek(Carbon::SUNDAY), $date->copy()->subWeek(), $date->copy()->addWeek()],
            'month' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth(), $date->copy()->subMonthNoOverflow(), $date->copy()->addMonthNoOverflow()],
        };

        $days = app(AttendanceCalendar::class)->daysForUser($employee, $start, $end);
        $rows = $days->map(function (array $day) {
            $sessions = $day['daily']->filter(fn (Attendance $session) => $session->clock_in || $session->clock_out);

            return [
                'date' => $day['date'],
                'status' => $day['status'],
                'wfh' => $day['wfh'],
                'clock_in' => $sessions->map(fn (Attendance $session) => $session->clock_in_datetime?->format('H:i'))->values(),
                'clock_out' => $sessions->map(fn (Attendance $session) => $session->clock_out_datetime?->format('H:i')
                    ?? ($session->clock_in ? 'Open' : null))->values(),
                'seconds' => $day['seconds'],
            ];
        });
        $totalSeconds = $rows->sum('seconds');
        $daysWorked = $days->filter(fn (array $day) => in_array($day['status'], ['present', 'late', 'half_day', 'wfh'], true)
            && $day['daily']->isNotEmpty())->count();
        $statuses = [
            'present' => 'Present', 'late' => 'Late', 'half_day' => 'Half day',
            'wfh' => 'Work from home', 'absent' => 'Absent', 'leave' => 'On leave',
            'unpaid_leave' => 'Unpaid leave', 'holiday' => 'Holiday',
            'weekend' => 'Weekend', 'scheduled_off' => 'Scheduled off', 'day_off' => 'Day off',
            'not_joined' => 'Before joining', 'not_marked' => 'Not marked',
        ];
        $hours = fn (int $seconds) => $this->secondsToHhmm($seconds);

        if (($filters['format'] ?? null) === 'csv') {
            return response()->streamDownload(function () use ($rows, $totalSeconds, $statuses, $hours) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Date', 'Status', 'Work from home', 'Clock in', 'Clock out', 'Hours'], ',', '"', '');
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row['date']->toDateString(),
                        $statuses[$row['status']] ?? 'Not marked',
                        $row['wfh'] ? 'Yes' : 'No',
                        $row['clock_in']->implode(' | '),
                        $row['clock_out']->implode(' | '),
                        $hours($row['seconds']),
                    ], ',', '"', '');
                }
                fputcsv($output, ['Total hours', '', '', '', '', $hours($totalSeconds)], ',', '"', '');
                fclose($output);
            }, "my-attendance-{$period}-{$start->toDateString()}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        return view('admin.attendance.personal', compact(
            'employee', 'today', 'period', 'date', 'start', 'end', 'previous', 'next',
            'rows', 'totalSeconds', 'daysWorked', 'statuses', 'hours',
        ));
    }

    /**
     * Convert seconds to HH:MM or HH:MM:SS
     */
    private function secondsToHhmm(int $seconds, bool $showSeconds = false): string
    {
        $seconds = max(0, $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($showSeconds) {
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * Build a Carbon datetime from a separate date (Y-m-d) and a time value (H:i or H:i:s or Carbon)
     * Returns null on failure.
     */
    private function buildDateTimeFromDateAndTime($date, $timeValue): ?\Carbon\Carbon
    {
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }
        if (empty($date) || $timeValue === null || $timeValue === '') {
            return null;
        }

        // Normalize time to H:i:s
        if (is_object($timeValue) && method_exists($timeValue, 'format')) {
            $timeStr = $timeValue->format('H:i:s');
        } else {
            $timeStr = trim((string)$timeValue);
            if (preg_match('/^\d{1,2}:\d{2}$/', $timeStr)) {
                $timeStr .= ':00';
            }
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $timeStr);
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($date . ' ' . $timeStr);
            } catch (\Throwable $_) {
                return null;
            }
        }
    }

    /**
     * Calculate period totals (seconds, HH:MM, HH:MM:SS, decimal hours) for given users
     */
    private function calculatePeriodTotals($users, array $attendanceMap, Carbon $startDate, Carbon $endDate): array
    {
        $result = [];

        $userIds = [];
        if ($users instanceof \Illuminate\Support\Collection) {
            $userIds = $users->pluck('id')->toArray();
        } elseif (is_array($users)) {
            if (! empty($users) && is_object($users[0]) && property_exists($users[0], 'id')) {
                $userIds = array_map(fn($u) => $u->id, $users);
            } else {
                $userIds = $users;
            }
        } elseif (is_object($users) && property_exists($users, 'id')) {
            $userIds = [$users->id];
        }

        foreach ($userIds as $uid) {
            $periodSeconds = 0;

            $d = $startDate->copy();
            for (; $d->lte($endDate); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');

                $cell = ($attendanceMap[$uid][$dateKey] ?? null);

                if ($cell instanceof \App\Models\Attendance) {
                    $periodSeconds += (int) ($cell->total_seconds ?? 0);
                    continue;
                }

                if (is_array($cell) || $cell instanceof \Illuminate\Support\Collection) {
                    foreach ($cell as $c) {
                        if ($c instanceof \App\Models\Attendance) {
                            $periodSeconds += (int) ($c->total_seconds ?? 0);
                        }
                    }
                }
            }

            $secs = max(0, (int) $periodSeconds);
            $hhmm = $this->secondsToHhmm($secs, false);
            $hhmmss = $this->secondsToHhmm($secs, true);
            $decimal = round($secs / 3600, 2);

            $result[$uid] = [
                'seconds' => $secs,
                'hhmm'    => $hhmm,
                'hhmmss'  => $hhmmss,
                'decimal' => $decimal,
            ];
        }

        return $result;
    }

    /**
     * Export Multi PDF - ADMIN ONLY
     */
    public function exportMultiPdf(Request $request)
    {
        $user = Auth::user();

        // Only admin / authorized roles can export
        $userRole = strtolower((string) ($user->role ?? ''));
        if (! in_array($userRole, ['admin', 'superadmin', 'hr', 'manager', 'developer'], true)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to export data.');
        }

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year ?? now()->year);

        $userQuery = User::query();

        // Apply all filters
        if ($request->has('user_ids') && is_array($request->user_ids) && count($request->user_ids) > 0) {
            $userQuery->whereIn('id', $request->user_ids);
        }

        // Apply department filter
        if ($request->has('department_id') && $request->department_id) {
            $userQuery->whereHas('employeeDetail', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Apply designation filter
        if ($request->has('designation_id') && $request->designation_id) {
            $userQuery->whereHas('employeeDetail', function($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        $users = $userQuery->orderBy('name')->get();

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $startDate = Carbon::create($year, $month, 1)->toDateString();
        $endDate   = Carbon::create($year, $month, $daysInMonth)->toDateString();

        // Build attendance query with same filters
        $attendanceQuery = Attendance::whereBetween('date', [$startDate, $endDate]);

        if ($request->has('user_ids') && is_array($request->user_ids) && count($request->user_ids)) {
            $attendanceQuery->whereIn('user_id', $request->user_ids);
        }

        if ($request->has('department_id') && $request->department_id) {
            $attendanceQuery->where('department_id', $request->department_id);
        }

        $attendanceRows = $attendanceQuery->get();

        // ensure model accessors are available in the view
        $attendanceRows->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        // build map $attendanceMap[$userId][$Y-m-d] = record
        $attendanceMap = [];
        foreach ($attendanceRows as $r) {
            $dateKey = Carbon::parse($r->date)->format('Y-m-d');
            $attendanceMap[$r->user_id][$dateKey] = $r;
        }

        // Build filter info for title
        $filterInfo = '';
        if ($request->department_id) {
            $dept = Department::find($request->department_id);
            $filterInfo .= $dept ? $dept->name : 'Department ' . $request->department_id;
        }
        if ($request->designation_id) {
            $desig = Designation::find($request->designation_id);
            $filterInfo .= $desig ? ' - ' . $desig->name : ' - Designation ' . $request->designation_id;
        }

        $data = [
            'users' => $users,
            'attendanceMap' => $attendanceMap,
            'daysInMonth' => $daysInMonth,
            'month' => $month,
            'year' => $year,
            'generated_at' => now()->format('d-M-Y H:i'),
            'chunkDays' => 15,
            'filterInfo' => $filterInfo
        ];

        $pdf = Pdf::loadView('admin.attendance.report_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        $fileName = sprintf('Attendance_Multi_%02d_%04d%s.pdf',
            $month,
            $year,
            $filterInfo ? '_' . Str::slug($filterInfo) : ''
        );

        return $pdf->download($fileName);
    }

    /**
     * By Hour View - ADMIN ONLY
     */
    public function byHour(Request $request)
    {
        $user = Auth::user();

        // Only admin can access by hour view
        if ($user->role !== 'admin') {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $monthRaw = $request->input('month', now()->month);
        $yearRaw  = $request->input('year', now()->year);
        $userId   = $request->input('user_id', null);

        // Normalize month -> integer 1..12
        try {
            if (is_numeric($monthRaw)) {
                $month = (int) $monthRaw;
            } else {
                $parsed = Carbon::parse($monthRaw);
                $month = (int) $parsed->month;
            }
        } catch (\Throwable $e) {
            $month = (int) now()->month;
        }

        $year = is_numeric($yearRaw) ? (int)$yearRaw : (int) now()->year;

        // pick users list (employees, hr, managers)
        $category = $request->input('staff_category') === 'authority' ? 'authority' : 'employee';
        $userQuery = \App\Services\WorkforceAccess::usersWithHistory(User::where('company_id', $user->company_id)->whereNull('archived_at'), $category, 'attendances');
        $employeeOptions = (clone $userQuery)->orderBy('name')->get();
        abort_if($userId && ! $employeeOptions->contains('id', (int) $userId), 403);
        $users = $userQuery
            ->when($userId, function ($query) use ($userId) {
                return $query->where('id', (int) $userId);
            })
            ->orderBy('name')
            ->get();

        // Build a query that will find attendances matching either:
        // - month/year of clock_in (preferred) OR
        // - month/year of date (fallback)
        $attendanceQuery = Attendance::query();
        \App\Services\WorkforceAccess::records($attendanceQuery, $category);

        // if user filter provided
        if ($userId) {
            $attendanceQuery->where('user_id', (int)$userId);
        }

        $attendanceQuery->where(function($q) use ($month, $year) {
            $q->where(function($q2) use ($month, $year) {
                // clock_in is TIME column: build datetime via CONCAT(date,' ',COALESCE(clock_in,'00:00:00'))
                $q2->whereRaw("MONTH(CONCAT(`date`, ' ', COALESCE(`clock_in`, '00:00:00'))) = ?", [$month])
                   ->whereRaw("YEAR(CONCAT(`date`, ' ', COALESCE(`clock_in`, '00:00:00'))) = ?", [$year]);
            })->orWhere(function($q3) use ($month, $year) {
                // fallback: attendance->date column
                $q3->whereMonth('date', $month)
                   ->whereYear('date', $year);
            });
        });

        // eager load user relation for display
        $attendances = $attendanceQuery->with('user')->get();

        // append accessors expected by views
        $attendances->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

        // build attendanceMap: userId -> date -> collection(attendance)
        $attendanceMap = [];
        foreach ($attendances as $att) {
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');
            if (! isset($attendanceMap[$att->user_id])) $attendanceMap[$att->user_id] = [];
            if (! isset($attendanceMap[$att->user_id][$dateKey])) $attendanceMap[$att->user_id][$dateKey] = collect();
            $attendanceMap[$att->user_id][$dateKey]->push($att);
        }

        // compute dayTotals: [user_id][Y-m-d] => seconds
        $dayTotals = [];
        foreach ($attendances as $att) {
            $uid = $att->user_id;
            $dateKey = Carbon::parse($att->date)->format('Y-m-d');

            // use model accessor total_seconds (handles overnight internally)
            $secs = (int) ($att->total_seconds ?? 0);

            if (! isset($dayTotals[$uid])) $dayTotals[$uid] = [];
            if (! isset($dayTotals[$uid][$dateKey])) $dayTotals[$uid][$dateKey] = 0;
            $dayTotals[$uid][$dateKey] += $secs;
        }

        // prepare days count for selected month/year
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // compute periodTotals
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);
        $archivedCount = Attendance::whereNotNull('archived_at')->count();

        return view('admin.attendance.by-hour', compact('attendances', 'users', 'employeeOptions', 'month', 'year', 'attendanceMap', 'daysInMonth', 'periodTotals', 'dayTotals', 'archivedCount'));
    }

    /**
     * Attendance Report - ADMIN ONLY
     */
    public function attendanceReport(Request $request)
    {
        $user = Auth::user();

        if (WorkforceAccess::isHr($user)) {
            return $this->personalReport($request, $user);
        }

        // Only authorized admin / management roles can access reports
        $userRole = strtolower((string) ($user->role ?? ''));
        if (! in_array($userRole, ['admin', 'superadmin', 'manager', 'developer'], true)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access reports.');
        }

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year ?? now()->year);
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        if ($userRole === 'manager' && method_exists($user, 'visibleEmployeeIds')) {
            $visibleIds = $user->visibleEmployeeIds();
            $users = User::where('role', 'employee')->whereIn('id', $visibleIds)->get();
        } else {
            $users = User::where('role', 'employee')->get();
        }

        $input = $request->user_id;

        if (is_array($input)) {
            $selectedIds = array_map('intval', $input);
        } elseif (!empty($input)) {
            $selectedIds = [ (int) $input ];
        } else {
            $selectedIds = [];
        }

        $selectedUser = count($selectedIds) === 1
            ? User::find($selectedIds[0])
            : null;

        $startDate = Carbon::create($year, $month, 1)->toDateString();
        $endDate   = Carbon::create($year, $month, $daysInMonth)->toDateString();

        $attRecords = !empty($selectedIds)
            ? Attendance::whereIn('user_id', $selectedIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
            : collect();

        if ($attRecords instanceof \Illuminate\Support\Collection && $attRecords->isNotEmpty()) {
            $attRecords->each->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);
        }

        $grouped = $attRecords->groupBy(function ($a) {
            $day = Carbon::parse($a->date)->day;
            return "{$a->user_id}|{$day}";
        });

        $attendances = [];
        $targetUserIds = !empty($selectedIds)
            ? $selectedIds
            : $users->pluck('id')->toArray();

        for ($i = 0; $i < count($targetUserIds); $i++) {
            $uid = $targetUserIds[$i];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $key = "{$uid}|{$d}";
                $attendances[$uid][$d] = $grouped[$key][0] ?? null;
            }
        }

        $summary = null;

        if ($selectedUser) {
            $summary = [
                'present' => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
                'late'    => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'late')->count(),
                'absent'  => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
                'leave'   => Attendance::where('user_id', $selectedUser->id)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'leave')->count(),
            ];
        } elseif (count($selectedIds) > 1) {
            $summary = [];

            foreach ($selectedIds as $uid) {
                $summary[$uid] = [
                    'present' => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count(),
                    'late'    => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'late')->count(),
                    'absent'  => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count(),
                    'leave'   => Attendance::where('user_id', $uid)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'leave')->count(),
                ];
            }
        }

        // Build attendanceMap for totals (userId -> date -> attendance model or null)
        $attendanceMap = [];
        foreach ($attRecords as $r) {
            $dateKey = Carbon::parse($r->date)->format('Y-m-d');
            if (! isset($attendanceMap[$r->user_id])) $attendanceMap[$r->user_id] = [];
            // if multiple records for same day, keep as collection
            if (! isset($attendanceMap[$r->user_id][$dateKey])) {
                $attendanceMap[$r->user_id][$dateKey] = [];
            }
            $attendanceMap[$r->user_id][$dateKey][] = $r;
        }

        // ensure we have full date keys for each user (fill nulls)
        foreach ($targetUserIds as $uid) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateKey = Carbon::create($year, $month, $d)->format('Y-m-d');
                if (! isset($attendanceMap[$uid][$dateKey])) {
                    $attendanceMap[$uid][$dateKey] = null;
                }
            }
        }

        $periodTotals = $this->calculatePeriodTotals($targetUserIds, $attendanceMap, Carbon::create($year,$month,1), Carbon::create($year,$month,1)->endOfMonth());

        return view('admin.attendance.report', compact(
            'users',
            'attendances',
            'month',
            'year',
            'daysInMonth',
            'selectedUser',
            'summary',
            'selectedIds',
            'periodTotals'
        ));
    }

    /**
     * Export Excel - ADMIN ONLY
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        // Only authorized admin / management roles can export
        $userRole = strtolower((string) ($user->role ?? ''));
        if (! in_array($userRole, ['admin', 'superadmin', 'hr', 'manager', 'developer'], true)) {
            abort(403, 'Unauthorized access');
        }

        // Fixed: Use Excel::download() instead of calling non-existent method
        $filters = $request->all();
        $fileName = 'attendance-report-' . date('Y-m-d') . '.xlsx';

        return Excel::download(
            new \App\Exports\AttendanceExport($filters),
            $fileName
        );
    }

    /**
     * Export PDF - ADMIN ONLY
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // Only authorized admin / management roles can export
        $userRole = strtolower((string) ($user->role ?? ''));
        if (! in_array($userRole, ['admin', 'superadmin', 'hr', 'manager', 'developer'], true)) {
            abort(403, 'Unauthorized access');
        }

        // Fixed: Use the existing exportMultiPdf method instead
        return $this->exportMultiPdf($request);
    }

    /**
     * By Member View - ADMIN ONLY
     */
    public function byMember(Request $request)
    {
        $user = Auth::user();

        // Only authorized admin / management roles can access by member view
        $userRole = strtolower((string) ($user->role ?? ''));
        if (! in_array($userRole, ['admin', 'superadmin', 'hr', 'manager', 'developer'], true)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $userId = $request->input('user_id');

        $users = User::whereIn('role', ['employee', 'hr', 'manager'])
            ->with('employeeDetail.designation')
            ->when($userId, function ($q) use ($userId) {
                return $q->where('id', (int) $userId);
            })
            ->orderBy('name')
            ->get();

        $attendances = Attendance::query()
            ->whereNull('archived_at')
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', (int) $userId);
            })
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('user')
            ->get();

        $attendances->each(function ($attendance) {
            $this->applyOrganizationAttendanceRules($attendance);
            $attendance->append(['total_seconds', 'total_duration', 'clock_in_datetime', 'clock_out_datetime']);
        });

        $attendanceMap = [];
        foreach ($attendances as $attendance) {
            $dateKey = Carbon::parse($attendance->date)->format('Y-m-d');
            if (! isset($attendanceMap[$attendance->user_id])) $attendanceMap[$attendance->user_id] = [];
            if (! isset($attendanceMap[$attendance->user_id][$dateKey])) $attendanceMap[$attendance->user_id][$dateKey] = null;
            $attendanceMap[$attendance->user_id][$dateKey] = $attendance;
        }

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // ensure each date key exists for each user
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = WorkScheduleService::getHolidaysAndOffDays($startDate, $endDate);

        $leaves = Leave::whereIn('user_id', $users->pluck('id'))
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

        $todayStr = now()->format('Y-m-d');
        foreach ($users as $u) {
            for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');
                if (! isset($attendanceMap[$u->id][$dateKey]) || $attendanceMap[$u->id][$dateKey] === null) {
                    if (array_key_exists($dateKey, $holidays)) {
                        $attendanceMap[$u->id][$dateKey] = (object)[
                            'status' => 'holiday',
                            'occassion' => $holidays[$dateKey]['occassion'] ?? 'Holiday'
                        ];
                    } else {
                        $wfhAssignment = WorkScheduleService::isUserWfhOnDate($u->id, $dateKey);
                        if ($wfhAssignment !== null) {
                            $attendanceMap[$u->id][$dateKey] = (object)[
                                'status' => 'wfh',
                                'work_from_type' => 'wfh',
                                'reason' => $wfhAssignment['reason'] ?? 'Work From Home',
                            ];
                        } else {
                            $attendanceMap[$u->id][$dateKey] = $dateKey <= $todayStr
                                ? (object)['status' => 'absent']
                                : null;
                        }
                    }
                }
            }
        }

        $periodTotals = $this->calculatePeriodTotals($users, $attendanceMap, $startDate, $endDate);
        $archivedCount = Attendance::whereNotNull('archived_at')->count();

        return view('admin.attendance.by_member', compact(
            'month', 'year', 'userId', 'users', 'attendanceMap', 'daysInMonth', 'periodTotals', 'archivedCount'
        ));
    }
}
