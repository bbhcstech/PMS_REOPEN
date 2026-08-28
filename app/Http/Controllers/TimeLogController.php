<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StoreTimeLogRequest;
use App\Http\Requests\UpdateTimeLogRequest;
use App\Models\TimeLog;
use App\Models\TaskTimer;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Services\TimeLogService;
use DB;

class TimeLogController extends Controller
{
    public function __construct(private TimeLogService $timeLogService)
    {
    }

    public function index(Request $request, ?Project $project = null)
    {
        $canReviewTimeLogs = $this->canManageTimelogs();

        // Query all tasks of all projects (or specific project if provided)
        $query = Task::with(['project', 'assignee', 'assignees', 'timers.user', 'activeTimer']);

        // Project filter
        if ($project) {
            $query->where('project_id', $project->id);
        }

        // Scope for employee role
        if (! $canReviewTimeLogs) {
            $user = auth()->user();
            $query->where(function ($q) use ($user) {
                $this->applyEmployeeTaskScope($q, $user);
            });
        }

        // Employee filter from request
        if ($request->filled('user_id') && $canReviewTimeLogs) {
            $targetUser = User::find($request->user_id);
            if ($targetUser) {
                $query->where(function ($q) use ($targetUser) {
                    $this->applyEmployeeTaskScope($q, $targetUser);
                });
            }
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('due_date', [$request->start_date, $request->end_date])
                  ->orWhereHas('timers', fn($tq) => $tq->whereBetween('start_time', [
                      $request->start_date . " 00:00:00",
                      $request->end_date . " 23:59:59"
                  ]));
            });
        } elseif ($request->filled('start_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('start_date', '>=', $request->start_date)
                  ->orWhereHas('timers', fn($tq) => $tq->whereDate('start_time', '>=', $request->start_date));
            });
        } elseif ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('due_date', '<=', $request->end_date)
                  ->orWhereHas('timers', fn($tq) => $tq->whereDate('end_time', '<=', $request->end_date));
            });
        }

        $tasks = $query->orderByDesc('id')->get();

        $totalEstimatedHours = $tasks->sum(function ($t) {
            return (float) ($t->estimate_hours ?? 0) + ((float) ($t->estimate_minutes ?? 0) / 60);
        });

        $totalLoggedHours = $tasks->sum(function ($t) {
            return (float) $t->timers->sum('total_hours');
        });

        $stats = [
            'total_tasks' => $tasks->count(),
            'total_hours' => round($totalLoggedHours, 2),
            'total_estimated_hours' => round($totalEstimatedHours, 2),
            'employees' => $tasks->flatMap(function ($t) {
                $ids = $t->assignees->pluck('id')->all();
                if ($t->assigned_to) {
                    $ids = array_merge($ids, explode(',', (string) $t->assigned_to));
                }
                return $ids;
            })->filter()->unique()->count(),
            'projects' => $tasks->pluck('project_id')->filter()->unique()->count(),
        ];

        $productivity = [];
        foreach ($tasks as $t) {
            foreach ($t->timers as $timer) {
                $empName = $timer->user?->name ?? 'Unknown';
                $empId = $timer->user_id;
                if (!isset($productivity[$empId])) {
                    $productivity[$empId] = [
                        'employee' => $empName,
                        'hours' => 0,
                        'logs' => 0,
                    ];
                }
                $productivity[$empId]['hours'] += (float) ($timer->total_hours ?? 0);
                $productivity[$empId]['logs'] += 1;
            }
        }
        $employeeProductivity = collect($productivity)->sortByDesc('hours')->values();

        // Dropdown employees list
        if ($canReviewTimeLogs) {
            $employees = User::where('role', 'employee')->orderBy('name')->get();
        } else {
            $employees = User::where('id', auth()->id())->get();
        }

        $logs = $tasks;

        return view('admin.timelogs.index', compact('tasks', 'logs', 'project', 'employees', 'stats', 'employeeProductivity'));
    }

    public function create(Request $request)
    {
        $canReviewTimeLogs = $this->canManageTimelogs();
        $projects = $canReviewTimeLogs
            ? Project::orderBy('name')->get()
            : Project::whereHas('users', fn ($users) => $users->where('users.id', auth()->id()))->orderBy('name')->get();
        $tasks = $canReviewTimeLogs
            ? Task::orderBy('title')->get()
            : Task::where(function ($query) {
                $this->applyEmployeeTaskScope($query);
            })->orderBy('title')->get();
        $employees = $canReviewTimeLogs ? User::where('role', 'employee')->orderBy('name')->get() : collect();

        $selectedProjectId = $request->query('project_id');
        $selectedTaskId = $request->query('task_id');

        return view('admin.timelogs.create', compact('projects', 'tasks', 'employees', 'selectedProjectId', 'selectedTaskId'));
    }

public function store(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'task_id'    => 'required|exists:tasks,id',
        'start_date' => 'required|date',
        'start_time' => 'required',
        'end_date'   => 'required|date',
        'end_time'   => 'required',
        'memo'       => 'nullable|string',
        'employee_id'=> 'nullable|exists:users,id',
    ]);

    // combine datetimes
    $startDatetime = $request->start_date . ' ' . $request->start_time . ':00';
    $endDatetime   = $request->end_date   . ' ' . $request->end_time   . ':00';

    $start = strtotime($startDatetime);
    $end   = strtotime($endDatetime);

    if ($start === $end) {
        return back()->withErrors(['end_time' => 'Start Time and End Time cannot be the same'])->withInput();
    }
    if ($start > $end) {
        return back()->withErrors(['end_time' => 'End Time must be after Start Time'])->withInput();
    }

    $total_hours = ($end - $start) / 3600;
    $employeeId  = $request->employee_id ?? auth()->id();

    // Use transaction to be safe
    DB::beginTransaction();
    try {
        // 1) create record without code
        $taskTimer = TaskTimer::create([
            'user_id'     => $employeeId,
            'project_id'  => $request->project_id,
            'task_id'     => $request->task_id,
            'start_date'  => $request->start_date,
            'start_time'  => $startDatetime,
            'end_date'    => $request->end_date,
            'end_time'    => $endDatetime,
            'memo'        => $request->memo,
            'total_hours' => $total_hours,
        ]);

        // 2) build code from created id (deterministic)
        $project = Project::find($request->project_id);
        $prefix = ($project && !empty($project->project_code)) ? $project->project_code : 'Xink25-26/';

        $generatedCode = $prefix . str_pad($taskTimer->id, 4, '0', STR_PAD_LEFT);

        // 3) update code if column exists (safe check)
        if (\Illuminate\Support\Facades\Schema::hasColumn('task_timers', 'code')) {
            $taskTimer->update(['code' => $generatedCode]);
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        // log and return error
        \Log::error('TimeLog store error: '.$e->getMessage());
        return back()->withErrors(['error' => 'Failed to save time log'])->withInput();
    }

    if ($request->has('redirect_to_project')) {
        return redirect()->route('projects.show', $request->project_id)->with('success', 'Time log added.');
    }

    return redirect()->route('timelogs.index')->with('success', 'Time log added.');
}


    public function edit($id)
    {
        $log = TaskTimer::findOrFail($id);
        $projects = Project::all();
        $tasks = Task::where('project_id', $log->project_id)->get();
        return view('admin.timelogs.edit', compact('log', 'projects', 'tasks'));
    }

    public function getTasks($id)
    {
        $tasks = Task::where('project_id', $id)->get();
        return response()->json($tasks);
    }

    public function update(Request $request, $id)
    {
        $log = TaskTimer::findOrFail($id);

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'required|exists:tasks,id',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date',
            'end_time' => 'required',
            'memo' => 'nullable|string',
        ]);

        // Combine start and end into full datetime strings
        $startDatetime = $request->start_date . ' ' . $request->start_time . ':00';
        $endDatetime = $request->end_date . ' ' . $request->end_time . ':00';

        // Convert to timestamps
        $start = strtotime($startDatetime);
        $end = strtotime($endDatetime);

        // Custom error if start == end
        if ($start === $end) {
            return back()->withErrors(['end_time' => 'Start Time and End Time cannot be the same'])->withInput();
        }

        // Custom error if start > end
        if ($start > $end) {
            return back()->withErrors(['end_time' => 'End Time must be after Start Time'])->withInput();
        }

        $total_hours = ($end - $start) / 3600;

        $log->update([
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
            'start_date' => $request->start_date,
            'start_time' => $startDatetime,
            'end_date' => $request->end_date,
            'end_time' => $endDatetime,
            'memo' => $request->memo,
            'total_hours' => $total_hours,
        ]);

        return redirect()->route('timelogs.index')->with('success', 'Time log updated.');
    }

    public function destroy($id)
    {
        TaskTimer::findOrFail($id)->delete();
        return back()->with('success', 'Time log deleted.');
    }

    public function show($id)
    {
        $log = TaskTimer::with(['project', 'task', 'user'])->findOrFail($id);
        return view('admin.timelogs.show', compact('log'));
    }

    public function createForProject(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $tasks = Task::where('project_id', $projectId)->get();
        $employee_data = User::all();

        $logsQuery = TaskTimer::where('project_id', $projectId)
            ->with(['task', 'employee']);

        // Apply filters
        if ($request->filled('employee_id')) {
            $logsQuery->where('user_id', $request->employee_id);
        }

        if ($request->filled('invoice_id')) {
            $logsQuery->where('invoice_id', $request->invoice_id === 'Yes' ? 1 : 0);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $logsQuery->where(function ($q) use ($searchTerm) {
                $q->whereHas('task', function ($q2) use ($searchTerm) {
                    $q2->where('title', 'like', "%$searchTerm%");
                })->orWhereHas('employee', function ($q3) use ($searchTerm) {
                    $q3->where('name', 'like', "%$searchTerm%");
                });
            });
        }

        $logs = $logsQuery->orderByDesc('id')->get();

        return view('admin.timelogs.create_project_log', compact('project', 'tasks', 'logs', 'employee_data'));
    }

    public function getTaskEmployee($taskId)
    {
        $task = Task::with('assignee')->findOrFail($taskId);

        if ($task->assignee) {
            return response()->json([
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
            ]);
        }

        return response()->json(null);
    }

    public function getTasksByProject($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get(['id', 'title']);
        return response()->json($tasks);
    }

    private function canManageTimelogs(): bool
    {
        return in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true);
    }

    private function applyEmployeeTaskScope($query, ?User $user = null): void
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return;
        }

        $userId = $user->id;
        $query->where(function ($q) use ($userId) {
            $q->whereHas('assignees', function ($aq) use ($userId) {
                $aq->where('users.id', $userId);
            })
            ->orWhereRaw('FIND_IN_SET(?, assigned_to)', [$userId])
            ->orWhere('assigned_to', $userId)
            ->orWhereHas('timers', function ($tq) use ($userId) {
                $tq->where('user_id', $userId);
            });
        });
    }

    public function calendar()
    {
        $canReview = $this->canManageTimelogs();
        $tasksQuery = Task::with(['project', 'assignees', 'timers.user'])->whereNotNull('start_date');

        if (! $canReview) {
            $user = auth()->user();
            $tasksQuery->where(function ($q) use ($user) {
                $this->applyEmployeeTaskScope($q, $user);
            });
        }

        $tasks = $tasksQuery->get();
        $timelogs = collect();

        foreach ($tasks as $t) {
            $assignedNames = $t->assignees->pluck('name')->join(', ') ?: ($t->assignee?->name ?? 'Unassigned');
            $start = $t->start_date ? \Carbon\Carbon::parse($t->start_date)->toIso8601String() : null;
            $end = $t->due_date ? \Carbon\Carbon::parse($t->due_date)->toIso8601String() : $start;

            if ($start) {
                $timelogs->push([
                    'title' => ($t->project?->name ? $t->project->name . ' - ' : '') . $t->title . ' (' . $assignedNames . ')',
                    'start' => $start,
                    'end'   => $end,
                    'allDay' => true,
                ]);
            }

            foreach ($t->timers as $timer) {
                if ($timer->start_time) {
                    $timelogs->push([
                        'title' => ($timer->user?->name ?? 'User') . ' - ' . $t->title . ' (' . ($timer->total_hours ?? 0) . 'h)',
                        'start' => \Carbon\Carbon::parse($timer->start_time)->toIso8601String(),
                        'end'   => $timer->end_time ? \Carbon\Carbon::parse($timer->end_time)->toIso8601String() : null,
                        'allDay' => false,
                    ]);
                }
            }
        }

        return view('admin.timelogs.calendar', compact('timelogs'));
    }

    public function byEmployee(Request $request)
    {
        $canReview = $this->canManageTimelogs();
        $taskQuery = Task::with(['project', 'assignees', 'timers.user', 'assignee']);

        // Filter by employee
        if ($request->filled('user_id') && $canReview) {
            $targetUser = User::find($request->user_id);
            if ($targetUser) {
                $taskQuery->where(function ($q) use ($targetUser) {
                    $this->applyEmployeeTaskScope($q, $targetUser);
                });
            }
        } elseif (! $canReview) {
            $taskQuery->where(function ($q) {
                $this->applyEmployeeTaskScope($q, auth()->user());
            });
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $taskQuery->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        // Search (by project/task/user name)
        if ($request->filled('search')) {
            $search = $request->search;
            $taskQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('task_short_code', 'like', "%$search%")
                  ->orWhereHas('project', fn($pq) => $pq->where('name', 'like', "%$search%")->orWhere('project_code', 'like', "%$search%"));
            });
        }

        $tasks = $taskQuery->latest()->get();
        $logs = TaskTimer::with(['project', 'task', 'user'])->latest()->get();

        if ($canReview) {
            $employees = User::where('role', 'employee')->orderBy('name')->get();
        } else {
            $employees = User::where('id', auth()->id())->get();
        }

        return view('admin.timelogs.by-employee', compact('tasks', 'logs', 'employees'));
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'status' => 'required|string',
        ]);

        $ids = $request->ids;
        $status = strtolower($request->status);

        DB::beginTransaction();
        try {
            Task::whereIn('id', $ids)->update(['status' => ucfirst($status)]);
            TaskTimer::whereIn('task_id', $ids)->orWhereIn('id', $ids)->update(['status' => $status]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Status updated successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('bulkStatusUpdate failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
        ]);

        $ids = $request->ids;

        DB::beginTransaction();
        try {
            TaskTimer::whereIn('task_id', $ids)->orWhereIn('id', $ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Selected time logs deleted',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('bulkDelete failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }
    }




}
