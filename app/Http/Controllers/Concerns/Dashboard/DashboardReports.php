<?php

namespace App\Http\Controllers\Concerns\Dashboard;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\Leave;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\TaskTimer;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DashboardReports
{
public function project(Request $request)
{
    // Default to today if no filter
    $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfDay();
    $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

    // Apply filters to all queries where applicable
    $totalProjects = Project::whereBetween('created_at', [$startDate, $endDate])->count();

    $overdueProjects = Project::whereBetween('created_at', [$startDate, $endDate])
        ->where('deadline', '<', now())
        ->where('status', '!=', 'Completed')
        ->count();

    $pendingMilestones = ProjectMilestone::whereHas('project', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->where('status', 'Pending')
        ->with('project')
        ->get();

    $statusWiseCounts = Project::whereBetween('created_at', [$startDate, $endDate])
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status')
        ->toArray();

    return view('dasboard-project', compact(
        'totalProjects',
        'overdueProjects',
        'pendingMilestones',
        'statusWiseCounts',
        'startDate',
        'endDate'
    ));
}

public function clientDashboard(Request $request)
{
    return redirect()->route('dashboard');

    $startDate = $request->filled('start_date')
        ? Carbon::parse($request->start_date)->startOfDay()
        : now()->startOfDay();

    $endDate = $request->filled('end_date')
        ? Carbon::parse($request->end_date)->endOfDay()
        : now()->endOfDay();

    // Filter clients created between start and end dates
    $totalClients = Client::whereBetween('created_at', [$startDate, $endDate])->count();

    // Example placeholders for charts
    $timelogChartData = [];
    $earningsData = [];

    return view('dashboard-client', compact(
        'totalClients',
        'startDate',
        'endDate'
    ));
}

// public function ticketDashboard()
// {
//     return view('dashboard-ticket', [
//         'unresolved' => Ticket::where('status', '!=', 'resolved')->count(),
//         'resolved' => Ticket::where('status', 'resolved')->count(),
//         'unassigned' => Ticket::whereNull('agent_id')->count(),
//         'typeWiseData' => Ticket::selectRaw('type_id, COUNT(*) as count')->groupBy('type_id')->pluck('count', 'type_id')->toArray(),
//         'statusWiseData' => Ticket::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(),
//         'channelWiseData' => Ticket::selectRaw('channel, COUNT(*) as count')->groupBy('channel')->pluck('count', 'channel')->toArray(),
//         'openTickets' => Ticket::where('status', 'open')->orderBy('created_at', 'desc')->limit(5)->get()
//     ]);
// }

public function ticketDashboard(Request $request)
{
    return redirect()->route('dashboard');

    $startDate = $request->filled('start_date')
        ? Carbon::parse($request->start_date)->startOfDay()
        : now()->startOfDay();

    $endDate = $request->filled('end_date')
        ? Carbon::parse($request->end_date)->endOfDay()
        : now()->endOfDay();

    // Base query for date filtering
    $ticketQuery = Ticket::whereBetween('created_at', [$startDate, $endDate]);

    return view('dashboard-ticket', [
        'unresolved' => (clone $ticketQuery)->where('status', '!=', 'resolved')->count(),
        'resolved' => (clone $ticketQuery)->where('status', 'resolved')->count(),
        'unassigned' => (clone $ticketQuery)->whereNull('agent_id')->count(),
        'typeWiseData' => (clone $ticketQuery)
                            ->selectRaw('type_id, COUNT(*) as count')
                            ->groupBy('type_id')
                            ->pluck('count', 'type_id')
                            ->toArray(),
        'statusWiseData' => (clone $ticketQuery)
                            ->selectRaw('status, COUNT(*) as count')
                            ->groupBy('status')
                            ->pluck('count', 'status')
                            ->toArray(),
        'channelWiseData' => (clone $ticketQuery)
                            ->selectRaw('channel, COUNT(*) as count')
                            ->groupBy('channel')
                            ->pluck('count', 'channel')
                            ->toArray(),
        'openTickets' => (clone $ticketQuery)
                            ->where('status', 'open')
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get(),
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]);
}

public function hrindex(Request $request)
{
    if (auth()->user()?->company_staff_role_id) {
        return view('admin.company-role-dashboard', app(\App\Services\CompanyRoleDashboard::class)->data(auth()->user()));
    }
        $today = Carbon::today();
        // Parse date range
       $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
       $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();


        $totalEmployees = app(\App\Services\CompanyEmployeeDirectory::class)->count();
        $activeEmployees = EmployeeDetail::where('status', 'Active')->count();
        $departmentsCount = Department::count();
        $designationsCount = Designation::count();
        $myLeaves = Leave::where('user_id', auth()->id())->whereNull('archived_at');
        $onLeaveToday = (clone $myLeaves)->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->where('status', 'approved')
                            ->count();
        $pendingLeaves = (clone $myLeaves)->where('status', 'pending')->count();

        $newEmployees = EmployeeDetail::whereBetween('joining_date', [$startDate, $endDate])->count();
          $exits = EmployeeDetail::whereBetween('exit_date', [$startDate, $endDate])->count();
         $approvedLeaves = (clone $myLeaves)->where('status', 'approved')
                        ->whereDate('start_date', '<=', $endDate)->whereDate('end_date', '>=', $startDate)
                        ->count();

        $pendingTasks = Task::where('status', 'Pending Task')->count();

        $monthlyHeadcount = EmployeeDetail::selectRaw('MONTH(joining_date) as month, COUNT(*) as count')
            ->groupBy('month')->pluck('count', 'month');

        $monthlyJoinings = EmployeeDetail::whereBetween('joining_date', [$startDate, $endDate])
                        ->selectRaw('MONTH(joining_date) as month, COUNT(*) as joinings')
                        ->groupBy('month')
                        ->pluck('joinings', 'month');

        $monthlyAttrition = EmployeeDetail::whereBetween('exit_date', [$startDate, $endDate])
                        ->selectRaw('MONTH(exit_date) as month, COUNT(*) as attritions')
                        ->groupBy('month')
                        ->pluck('attritions', 'month');

        $hasLateFlag = Schema::connection('tenant')->hasColumn('attendances', 'late');
        $lateDates = Attendance::where('user_id', Auth::id())->whereNull('archived_at')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($query) use ($hasLateFlag) {
                $query->whereRaw('LOWER(status) = ?', ['late']);
                if ($hasLateFlag) {
                    $query->orWhere('late', 'yes');
                }
            })
            ->orderByDesc('date')->distinct()->pluck('date');

              $leavesTaken = (clone $myLeaves)->with(['user.employeeDetail.designation'])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endDate)->whereDate('end_date', '>=', $startDate)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->get();



        $departmentWise = EmployeeDetail::leftJoin('departments', 'employee_details.department_id', '=', 'departments.id')
            ->select('departments.dpt_name as department_name', DB::raw('COUNT(*) as total'))
            ->groupBy('departments.dpt_name')
            ->get();

            // echo'<pre>';print_r($departmentWise);die;

        // Designation-wise employee count
        $designationWise = EmployeeDetail::with('designation')
            ->selectRaw('designation_id, COUNT(*) as total')
            ->groupBy('designation_id')
            ->get();

        $genderCounts = EmployeeDetail::select('gender')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('gender')
        ->pluck('total', 'gender');

        $roleCounts = User::select('role')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalProjects = Project::whereNull('deleted_at')->count();
        $activeProjects = Project::whereNull('deleted_at')->whereNotIn('status', ['completed', 'canceled'])->count();
        $pendingTasksTotal = Task::where('status', '!=', 'Completed')
            ->with('project')
            ->orderByDesc('start_date')
            ->take(5)
            ->get();

        try {
            if (Schema::hasTable('project_activity')) {
                $activities = DB::table('project_activity')
                    ->join('projects', 'projects.id', '=', 'project_activity.project_id')
                    ->select(
                        'project_activity.activity',
                        'project_activity.created_at',
                        'projects.name as project_name'
                    )
                    ->orderByDesc('project_activity.created_at')
                    ->limit(10)
                    ->get();
            } else {
                $activities = collect();
            }
        } catch (\Throwable $e) {
            $activities = collect();
        }

        $totalTimelogsCount = TaskTimer::count();
        $totalTimelogHours = round((float) TaskTimer::sum('total_hours'), 1);
        $totalClient = Client::count();
        $unresolvedTicket = Ticket::where('status', '!=', 'closed')->count();
        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();
        $tasks = Task::all();

        $todayStr = now()->toDateString();
        $attendance = \App\Services\WorkforceAccess::currentAttendance(Auth::user());
        $attendancePolicy = $this->attendancePolicy();
        $officeLatitude = self::OFFICE_LATITUDE;
        $officeLongitude = self::OFFICE_LONGITUDE;
        $officeRadiusMeters = self::OFFICE_RADIUS_METERS;
        $officeAddress = self::OFFICE_ADDRESS;

        return view('dashboard-hr', compact(
            'totalEmployees',
            'activeEmployees',
            'departmentsCount',
            'designationsCount',
            'onLeaveToday',
            'pendingLeaves',
            'newEmployees',
            'exits',
            'approvedLeaves',
            'pendingTasks',
            'monthlyHeadcount',
            'monthlyJoinings',
            'monthlyAttrition',
            'lateDates',
            'leavesTaken',
            'departmentWise',
            'designationWise',
            'genderCounts',
            'roleCounts',
            'totalProjects',
            'activeProjects',
            'pendingTasksTotal',
            'activities',
            'totalTimelogsCount',
            'totalTimelogHours',
            'totalClient',
            'unresolvedTicket',
            'projects',
            'tasks',
            'attendance',
            'attendancePolicy',
            'officeLatitude',
            'officeLongitude',
            'officeRadiusMeters',
            'officeAddress'
        ));
    }
}
