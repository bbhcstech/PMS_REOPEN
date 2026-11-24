<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\Dashboard\DashboardAttendance;
use App\Http\Controllers\Concerns\Dashboard\DashboardReports;
use App\Http\Controllers\Concerns\Dashboard\DashboardSearch;
use App\Http\Controllers\Concerns\Dashboard\DashboardStickyNotes;
use App\Http\Controllers\Concerns\Dashboard\DashboardTimers;
use App\Models\Attendance;
use App\Models\Award;
use App\Models\Client;
use App\Models\EmployeeDetail;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use DashboardAttendance;
    use DashboardTimers;
    use DashboardStickyNotes;
    use DashboardSearch;
    use DashboardReports;

    private const OFFICE_LATITUDE = 22.49682;

    private const OFFICE_LONGITUDE = 88.39462;

    private const OFFICE_RADIUS_METERS = 10;

    private const OFFICE_ADDRESS = '11 Hospital Link Road, Satavisha Building, Kolkata, West Bengal 700075';

    public function index()
    {
        $user = auth()->user();
        if ($user?->company_id) {
            // Clock-in boundary: today's missed clock-ins count as absent on every dashboard.
            try {
                \App\Services\AttendanceClockPolicy::markMissedClockIns((int) $user->company_id);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        if ($user?->company_staff_role_id) {
            return view('admin.company-role-dashboard', app(\App\Services\CompanyRoleDashboard::class)->data($user));
        }
        // An employee's first workspace visit shows the welcome on the employee dashboard (recorded below).
        $employeeFirstVisit = $user && strtolower((string) $user->role) === 'employee' && ! $user->employee_welcome_seen_at
            && ! request()->hasHeader('X-PMS-Live-Records');
        if ($user && ! $employeeFirstVisit && (
            (method_exists($user, 'isDeveloper') && $user->isDeveloper()) ||
            in_array(strtolower((string) ($user->role ?? '')), ['developer', 'dev'], true) ||
            str_contains(strtolower((string) ($user->role ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'developer') ||
            str_contains(strtolower((string) ($user->designation ?? '')), 'engineer')
        )) {
            return redirect()->route('developer.dashboard');
        }

        $userId = Auth::id();
        $userRole = strtolower((string) (auth()->user()?->role ?? ''));

        if (in_array($userRole, ['admin', 'administrator', 'superadmin'], true)) {
            $companyId = app(\App\Services\CompanyContext::class)->id() ?? auth()->user()?->company_id;

            try {
                $totalEmployees = app(\App\Services\CompanyEmployeeDirectory::class)->count((int) $companyId);
            } catch (\Throwable $e) { $totalEmployees = 0; }
            $today = now()->toDateString();

            try {
                $presentCount = \App\Models\Attendance::where('date', $today)
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('attendances', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                    ->whereIn('status', ['present', 'late'])
                    ->count();
            } catch (\Throwable $e) { $presentCount = 0; }

            try {
                $lateCount = \App\Models\Attendance::where('date', $today)
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('attendances', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                    ->where('status', 'late')
                    ->count();
            } catch (\Throwable $e) { $lateCount = 0; }

            $absentCount = max(0, $totalEmployees - $presentCount);

            try {
                $totalClient = \App\Models\Client::when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('clients', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))->count();
            } catch (\Throwable $e) { $totalClient = 0; }

            try {
                $totalProject = \App\Models\Project::when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('projects', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))->count();
            } catch (\Throwable $e) { $totalProject = 0; }

            try {
                $pendingTask = \App\Models\Task::where('status', '!=', 'Completed')
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tasks', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                    ->count();
            } catch (\Throwable $e) { $pendingTask = 0; }

            try {
                $unresolvedTicketQuery = \App\Models\Ticket::query()
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')));

                if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'status')) {
                    $unresolvedTicket = $unresolvedTicketQuery->where('status', '!=', 'closed')->count();
                } else {
                    $unresolvedTicket = $unresolvedTicketQuery->count();
                }
            } catch (\Throwable $e) { $unresolvedTicket = 0; }

            try {
                $pendingLeaves = Leave::with('user')
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('leaves', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                    ->where('status', 'pending')
                    ->latest()
                    ->take(5)
                    ->get();
            } catch (\Throwable $e) { $pendingLeaves = collect(); }

            try {
                $openTicketsQuery = \App\Models\Ticket::with(['project', 'agent'])
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')));

                if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'status')) {
                    $openTickets = $openTicketsQuery->where('status', 'open')->latest()->take(5)->get();
                } else {
                    $openTickets = $openTicketsQuery->latest()->take(5)->get();
                }
            } catch (\Throwable $e) { $openTickets = collect(); }

            try {
                $pendingTasksTotal = \App\Models\Task::where('status', '!=', 'Completed')
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tasks', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                    ->with('project')
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get();
            } catch (\Throwable $e) { $pendingTasksTotal = collect(); }

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('project_activity')) {
                    $activitiesQuery = DB::table('project_activity')
                        ->join('projects', 'projects.id', '=', 'project_activity.project_id')
                        ->select(
                            'project_activity.activity',
                            'project_activity.created_at',
                            'projects.name as project_name'
                        );

                    if ($companyId && \Illuminate\Support\Facades\Schema::hasColumn('projects', 'company_id')) {
                        $activitiesQuery->where(fn($sub) => $sub->where('projects.company_id', $companyId)->orWhereNull('projects.company_id'));
                    }

                    $activities = $activitiesQuery->orderByDesc('project_activity.created_at')->limit(15)->get();
                } else {
                    $activities = collect();
                }
            } catch (\Throwable $e) { $activities = collect(); }

            try {
                $useractivities = UserActivity::with('user')
                    ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('user_activities', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Throwable $e) { $useractivities = collect(); }

            try {
                $projects = Project::when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('projects', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))->get();
            } catch (\Throwable $e) { $projects = collect(); }

            try {
                $tasks = Task::when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tasks', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))->get();
            } catch (\Throwable $e) { $tasks = collect(); }

            return view('dashboard', compact(
                'totalEmployees',
                'presentCount',
                'lateCount',
                'absentCount',
                'totalClient',
                'totalProject',
                'unresolvedTicket',
                'pendingTask',
                'pendingLeaves',
                'openTickets',
                'pendingTasksTotal',
                'activities',
                'useractivities',
                'projects', 'tasks'
            ));
        }

        // ✅ Client logic
        if ($userRole === 'client') {
            return view('client-dashboard');
        }

        if ($userRole === 'manager') {
            return $this->project(request());
        }

        if ($userRole === 'hr') {
            return $this->hrindex(request());
        }

        // ✅ Employee logic
        if ($userRole === 'employee') {
            $user = Auth::user()->loadMissing(['employeeDetail.designation', 'employeeDetail.department']);
            $employeeTimezone = $this->resolveEmployeeTimezone(session('attendance_timezone.' . $user->id));
            $today = Carbon::now($employeeTimezone)->toDateString();

            // The welcome appears on the employee's first workspace visit only. It is recorded here, the
            // moment it is shown, so a reload, a quick navigation or a lost browser request never shows it
            // again. Background live-refresh requests (X-PMS-Live-Records) never trigger or consume it.
            $isLiveRefresh = request()->hasHeader('X-PMS-Live-Records');
            $showEmployeeWelcome = ! $user->employee_welcome_seen_at && ! $isLiveRefresh;
            if ($showEmployeeWelcome) {
                try {
                    $user->forceFill(['employee_welcome_seen_at' => now()])->save();
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            $attendance = \App\Services\WorkforceAccess::currentAttendance($user);
            $attendancePolicy = $this->attendancePolicy();


            // Fetch week data
            $startOfWeek = Carbon::now($employeeTimezone)->startOfWeek(); // Monday
            $endOfWeek = Carbon::now($employeeTimezone)->endOfWeek();     // Sunday

            // Same source and rules as My Attendance, so dashboard charts never disagree with it.
            try {
                \App\Services\AttendanceClockPolicy::markMissedClockIns((int) $user->company_id);
            } catch (\Throwable $e) {
                report($e);
            }
            $weekDays = app(\App\Services\AttendanceCalendar::class)->daysForUser($user, $startOfWeek, $endOfWeek);
            $weekAttendance = $this->summarizeEmployeeWeek($weekDays);

            $openTasksCount = Task::where('status', '!=', 'completed')->count();
            $projectsCount = Project::count();
           $openTicketsCount = Ticket::where('status', 'open')->count();
           $userId = auth()->id();

            $projects = \DB::table('projects')
            ->join('project_user', 'projects.id', '=', 'project_user.project_id')
            ->where('project_user.user_id', $userId)
            ->where(function ($query) {
                $query->where('projects.status', 'in progress')
                      ->where(function ($sub) {
                          $sub->whereNull('projects.deadline') // allow no deadline
                               ->orWhere('projects.deadline', '>=', Carbon::today()); // not overdue
                      });
            })
            ->orWhere(function ($query) {
                $query->where('projects.status', 'in progress')
                      ->whereNotNull('projects.deadline')
                      ->where('projects.deadline', '<', Carbon::today()); // overdue
            })
            ->select('projects.*')
            ->distinct()
            ->get();

            $inProgress = \DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->where(function ($q) {
                    $q->whereNull('projects.deadline')
                      ->orWhere('projects.deadline', '>=', Carbon::today());
                })
                ->select('projects.*')
                ->get();

                // Overdue Projects

                $overdue = \DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->whereNotNull('projects.deadline')
                ->where('projects.deadline', '<', Carbon::today())
                ->select('projects.*')
                ->get();

            // In-Progress Projects

            // In-Progress Projects Count (assigned to user)
            $inProgressCount = DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->where(function ($q) {
                    $q->whereNull('projects.deadline')
                      ->orWhere('projects.deadline', '>=', Carbon::today());
                })
                ->count();

            // Overdue Projects Count (assigned to user)
            $overdueCount = DB::table('projects')
                ->join('project_user', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.user_id', $userId)
                ->where('projects.status', 'in progress')
                ->whereNotNull('projects.deadline')
                ->where('projects.deadline', '<', Carbon::today())
                ->count();
                $totalProjects = $inProgress->count() + $overdue->count();



            // Count Pending Tasks (assigned_to includes current user AND status is Incomplete or Doing)
            $pendingTasksCount = Task::whereIn('status', ['Incomplete', 'Doing'])
                ->whereRaw("FIND_IN_SET(?, assigned_to)", [auth()->id()])
                ->count();

            // Count Overdue Tasks (same filter + due date is past)
            $overdueTasksCount = Task::whereIn('status', ['Incomplete', 'Doing'])
                ->whereRaw("FIND_IN_SET(?, assigned_to)", [auth()->id()])
                ->whereDate('due_date', '<', Carbon::today())
                ->count();

        //   Birthdays Today
            $birthdaysToday = EmployeeDetail::whereRaw('DATE_FORMAT(dob, "%m-%d") = ?', [date('m-d')])->get();
            //  Employee Appreciations

            $appreciations = Award::whereDate('award_date', now())->get();

        // On Leave Today
         $onLeaveToday = Leave::where('status', 'approved')
        ->whereDate('start_date', '<=', now())
        ->whereDate('end_date', '>=', now())
        ->with('user')
        ->get();

       // Today’s Joinings & Work Anniversary

       $todaysJoinings = EmployeeDetail::whereDate('joining_date', now())->get();

       $workAnniversaries = EmployeeDetail::whereRaw('DATE_FORMAT(joining_date, "%m-%d") = ?', [date('m-d')])->get();

        $myTasks = Task::where(function ($q) use ($userId) {
            $q->whereHas('assignees', fn ($assignees) => $assignees->where('users.id', $userId))
              ->orWhereRaw("FIND_IN_SET(?, assigned_to)", [$userId]);
        })
        ->with('project')
        ->orderBy('due_date', 'asc')
        ->limit(10)
        ->get();

        $myTickets = Ticket::where(function ($q) use ($userId) {
                $q->where('agent_id', $userId)
                  ->orWhere('requester_id', $userId);
            })
            ->with(['project', 'requester', 'agent'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $myProjects = Project::whereNull('deleted_at')
            ->whereHas('users', fn ($user) => $user->where('users.id', $userId))
            ->with([
                'client',
                'tasks' => function ($q) use ($userId) {
                    $q->where(function ($taskScope) use ($userId) {
                        $taskScope->whereHas('assignees', fn ($assignees) => $assignees->where('users.id', $userId))
                            ->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId]);
                    })->orderBy('title');
                }
            ])
            ->orderByDesc('created_at')
            ->get();

        $projects = $myProjects;
        $tasks = $myTasks;

        $dashboardStatusCharts = app(\App\Services\EmployeeDashboardCharts::class)->forUser($user);
        return view('employee-dashboard', compact(
            'dashboardStatusCharts',
            'employeeTimezone',
            'user',
            'projects',
            'tasks',
            'myProjects',
            'attendance',
            'attendancePolicy',
            'weekDays',
            'weekAttendance',
            'openTasksCount',
            'projectsCount',
            'openTicketsCount',
            'appreciations',
            'birthdaysToday',
            'todaysJoinings',
            'workAnniversaries',
            'onLeaveToday',
            'pendingTasksCount',
            'overdueTasksCount',
            'totalProjects',
            'inProgressCount',
            'overdueCount',
            'myTasks',
            'myTickets',
            'showEmployeeWelcome'
        ));
    }

        abort(403);
    }

    /**
     * Weekly chart data for the employee dashboard from My Attendance calendar days.
     * Each day lands in exactly one pie slice; holidays, days off and upcoming days are not counted.
     */
    private function summarizeEmployeeWeek($weekDays): array
    {
        $summary = ['Present' => 0, 'Absent' => 0, 'On Leave' => 0, 'Work From Home' => 0, 'Half Day' => 0];
        $labels = [];
        $hours = [];
        $scheduled = 0;
        $attended = 0.0;
        foreach ($weekDays as $day) {
            $labels[] = $day['date']->format('D');
            $hours[] = round($day['seconds'] / 3600, 2);
            $slice = match ($day['status']) {
                'present', 'late' => $day['wfh'] ? 'Work From Home' : 'Present',
                'wfh' => 'Work From Home',
                'half_day' => 'Half Day',
                'leave', 'unpaid_leave' => 'On Leave',
                'absent' => 'Absent',
                default => null,
            };
            if ($slice) $summary[$slice]++;
            if (! $day['date']->isFuture() && ! in_array($day['status'], ['holiday', 'day_off', 'not_joined'], true)) {
                $scheduled++;
                $attended += in_array($day['status'], ['present', 'late', 'wfh'], true) ? 1 : ($day['status'] === 'half_day' ? 0.5 : 0);
            }
        }

        return [
            'labels' => $labels,
            'hours' => $hours,
            'summary' => $summary,
            'totalHours' => round(array_sum($hours), 1),
            'percent' => $scheduled > 0 ? (int) round(100 * $attended / $scheduled) : 0,
        ];
    }

   public function markEmployeeWelcomeSeen(Request $request)
{
    $user = Auth::user();

    if (! $user || $user->role !== 'employee') {
        return response()->json(['success' => false], 403);
    }

    if (! $user->employee_welcome_seen_at) {
        $user->forceFill(['employee_welcome_seen_at' => now()])->save();
    }

    return response()->json(['success' => true]);
}
}
