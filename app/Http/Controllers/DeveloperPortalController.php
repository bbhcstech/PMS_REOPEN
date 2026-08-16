<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DeveloperPortalController extends Controller
{
    /**
     * Helper to get authenticated developer user
     */
    private function getDevUser()
    {
        if (session('superadmin_preview_active') && session('superadmin_preview_dev_id')) {
            $dev = User::find(session('superadmin_preview_dev_id'));
            if ($dev) {
                return $dev;
            }
        }
        return Auth::user();
    }

    /**
     * Helper to log activity to central super_admin_activity_logs or audit table
     */
    private function logDevActivity(string $action, string $description, ?array $details = null): void
    {
        $user = Auth::user();
        try {
            if (DB::connection('central')->getSchemaBuilder()->hasTable('super_admin_activity_logs')) {
                DB::connection('central')->table('super_admin_activity_logs')->insert([
                    'super_admin_id' => $user?->id ?? 1,
                    'action' => $action,
                    'company_id' => $user?->company_id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'details' => json_encode(array_merge([
                        'user_id' => $user?->id,
                        'user_name' => $user?->name,
                        'user_email' => $user?->email,
                        'description' => $description,
                    ], $details ?? [])),
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Fail safe
        }
    }

    /**
     * 1. Developer Dashboard
     */
    public function dashboard(): View
    {
        $dev = $this->getDevUser();
        $empDetail = DB::table('employee_details')->where('user_id', $dev->id)->first();

        // Real Tasks Query for Logged-In Developer
        $allDevTasks = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.assigned_to', $dev->id)
            ->whereNull('tasks.deleted_at')
            ->latest('tasks.created_at')
            ->get();

        $activeTasks = $allDevTasks->where('status', '!=', 'completed')->where('status', '!=', 'cancelled');
        $completedTasks = $allDevTasks->where('status', 'completed');
        $inProgressTasks = $allDevTasks->where('status', 'in_progress');
        $overdueTasks = $activeTasks->filter(fn ($t) => !empty($t->due_date) && Carbon::parse($t->due_date)->isPast());
        $upcomingDeadlines = $activeTasks->filter(fn ($t) => !empty($t->due_date) && Carbon::parse($t->due_date)->isFuture() && Carbon::parse($t->due_date)->diffInDays(now()) <= 7);

        $estimateHoursTotal = $activeTasks->sum(fn ($t) => (int) ($t->estimate_hours ?? 8));
        $workloadPercentage = min(100, (int) round(($estimateHoursTotal / 40) * 100));

        $kpis = [
            'total_assigned' => $allDevTasks->count(),
            'in_progress' => $inProgressTasks->count(),
            'completed' => $completedTasks->count(),
            'overdue' => $overdueTasks->count(),
            'upcoming_deadlines' => $upcomingDeadlines->count(),
            'workload_percentage' => $workloadPercentage,
            'estimate_hours_total' => $estimateHoursTotal,
            'available_capacity' => max(0, 40 - $estimateHoursTotal),
        ];

        // Skills List
        $skillsRaw = $empDetail?->skills ?? ($dev->about ?? 'PHP, Laravel, MySQL, REST API, Git');
        $skillsArray = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', $skillsRaw))));

        // Recent Work Items (5 latest)
        $recentWork = $allDevTasks->take(5);

        // Recent Contribution Activity (5 latest completed)
        $recentContributions = $completedTasks->take(5);

        // Developer Notifications / System Alerts
        $notifications = DB::table('tasks')
            ->where('assigned_to', $dev->id)
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('developer.dashboard', compact(
            'dev',
            'empDetail',
            'kpis',
            'skillsArray',
            'recentWork',
            'recentContributions',
            'notifications'
        ));
    }

    /**
     * 2. My Work (Task List & Filtering)
     */
    public function myWork(Request $request): View
    {
        $dev = $this->getDevUser();

        $statusFilter = $request->input('status', 'all');
        $priorityFilter = $request->input('priority', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->leftJoin('users as assigner', 'tasks.created_by', '=', 'assigner.id')
            ->select(
                'tasks.*',
                'companies.name as company_name',
                'projects.name as project_name',
                'assigner.name as assigner_name'
            )
            ->where('tasks.assigned_to', $dev->id)
            ->whereNull('tasks.deleted_at');

        if ($statusFilter !== 'all') {
            $query->where('tasks.status', $statusFilter);
        }

        if ($priorityFilter !== 'all') {
            $query->where('tasks.priority', strtolower($priorityFilter));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('tasks.title', 'like', "%{$search}%")
                    ->orWhere('tasks.description', 'like', "%{$search}%")
                    ->orWhere('companies.name', 'like', "%{$search}%")
                    ->orWhere('projects.name', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest('tasks.created_at')->paginate(15);

        return view('developer.my_work', compact('dev', 'tasks', 'statusFilter', 'priorityFilter', 'search'));
    }

    /**
     * 3. My Contributions (Task-based contribution analytics & history)
     */
    public function myContributions(Request $request): View
    {
        $dev = $this->getDevUser();

        $allTasks = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.assigned_to', $dev->id)
            ->whereNull('tasks.deleted_at')
            ->get();

        $completedTasks = $allTasks->where('status', 'completed');

        // Contributions Breakdown by Project / Platform Section
        $projectBreakdown = $completedTasks->groupBy('project_name')->map(function ($group, $name) {
            return [
                'name' => $name ?: 'Platform Core System',
                'completed_count' => $group->count(),
                'total_hours' => $group->sum('estimate_hours'),
            ];
        })->values();

        // Contributions Breakdown by Company
        $companyBreakdown = $completedTasks->groupBy('company_name')->map(function ($group, $name) {
            return [
                'name' => $name ?: 'Platform Central',
                'completed_count' => $group->count(),
            ];
        })->values();

        // Summary Statistics
        $stats = [
            'total_assigned' => $allTasks->count(),
            'total_completed' => $completedTasks->count(),
            'in_progress' => $allTasks->where('status', 'in_progress')->count(),
            'on_hold' => $allTasks->where('status', 'on_hold')->count(),
            'overdue' => $allTasks->where('status', '!=', 'completed')->filter(fn ($t) => !empty($t->due_date) && Carbon::parse($t->due_date)->isPast())->count(),
            'companies_count' => $completedTasks->pluck('company_id')->unique()->count(),
            'projects_count' => $completedTasks->pluck('project_id')->unique()->count(),
        ];

        // Chronological Completed Work History
        $contributionHistory = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.assigned_to', $dev->id)
            ->where('tasks.status', 'completed')
            ->whereNull('tasks.deleted_at')
            ->latest('tasks.updated_at')
            ->paginate(15);

        return view('developer.my_contributions', compact('dev', 'stats', 'projectBreakdown', 'companyBreakdown', 'contributionHistory'));
    }

    /**
     * 4. Upcoming Deadlines
     */
    public function deadlines(Request $request): View
    {
        $dev = $this->getDevUser();

        $tasks = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.assigned_to', $dev->id)
            ->where('tasks.status', '!=', 'completed')
            ->where('tasks.status', '!=', 'cancelled')
            ->whereNull('tasks.deleted_at')
            ->orderBy('tasks.due_date', 'asc')
            ->get()
            ->map(function ($t) {
                if (empty($t->due_date)) {
                    $t->deadline_status = 'ON TRACK';
                    $t->days_remaining = 99;
                } else {
                    $dueDate = Carbon::parse($t->due_date);
                    if ($dueDate->isPast()) {
                        $t->deadline_status = 'OVERDUE';
                        $t->days_remaining = -$dueDate->diffInDays(now());
                    } elseif ($dueDate->diffInDays(now()) <= 3) {
                        $t->deadline_status = 'DUE SOON';
                        $t->days_remaining = $dueDate->diffInDays(now());
                    } else {
                        $t->deadline_status = 'ON TRACK';
                        $t->days_remaining = $dueDate->diffInDays(now());
                    }
                }
                return $t;
            });

        return view('developer.deadlines', compact('dev', 'tasks'));
    }

    /**
     * 5. Developer Notifications
     */
    public function notifications(): View
    {
        $dev = $this->getDevUser();

        $notifications = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.assigned_to', $dev->id)
            ->whereNull('tasks.deleted_at')
            ->latest('tasks.updated_at')
            ->paginate(15);

        return view('developer.notifications', compact('dev', 'notifications'));
    }

    /**
     * 6. Profile View
     */
    public function profile(): View
    {
        $dev = $this->getDevUser();
        $empDetail = DB::table('employee_details')->where('user_id', $dev->id)->first();

        $allTasks = DB::table('tasks')
            ->where('assigned_to', $dev->id)
            ->whereNull('deleted_at')
            ->get();

        $completedTasks = $allTasks->where('status', 'completed');
        $inProgressTasks = $allTasks->where('status', 'in_progress');
        $overdueTasks = $allTasks->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')
            ->filter(fn ($t) => !empty($t->due_date) && Carbon::parse($t->due_date)->isPast());

        $totalCount = max(1, $allTasks->count());
        $completedCount = $completedTasks->count();
        $completionRate = (int) round(($completedCount / $totalCount) * 100);

        // Calculate average completion time in days
        $completionDays = [];
        foreach ($completedTasks as $ct) {
            if ($ct->completed_on && $ct->created_at) {
                $days = Carbon::parse($ct->created_at)->diffInDays(Carbon::parse($ct->completed_on));
                $completionDays[] = max(1, $days);
            }
        }
        $avgCompletionTime = count($completionDays) > 0 ? round(array_sum($completionDays) / count($completionDays), 1) : 1.5;

        $performance = [
            'total_assigned' => $allTasks->count(),
            'completed' => $completedCount,
            'in_progress' => $inProgressTasks->count(),
            'overdue' => $overdueTasks->count(),
            'completion_rate' => $completionRate,
            'avg_completion_time' => $avgCompletionTime,
        ];

        $recentTasks = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.assigned_to', $dev->id)
            ->whereNull('tasks.deleted_at')
            ->latest('tasks.updated_at')
            ->take(6)
            ->get();

        $skillsRaw = $empDetail?->skills ?? ($dev->about ?? 'PHP, Laravel, MySQL, REST API, Git');
        $skillsArray = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', $skillsRaw))));

        $statusOptions = ['Available', 'Busy', 'On Leave'];

        return view('developer.profile', compact('dev', 'empDetail', 'skillsArray', 'statusOptions', 'performance', 'recentTasks'));
    }

    /**
     * Update Authorized Profile Fields
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $dev = $this->getDevUser();

        $data = $request->validate([
            'mobile' => ['nullable', 'string', 'max:20'],
            'skills' => ['nullable', 'string', 'max:500'],
            'experience' => ['nullable', 'string', 'max:100'],
            'about' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:Available,Busy,On Leave,available,busy,on_leave'],
        ]);

        User::where('id', $dev->id)->update([
            'mobile' => $data['mobile'] ?? $dev->mobile,
            'about' => $data['about'] ?? $dev->about,
        ]);

        $statusKey = strtolower(str_replace(' ', '_', $data['status'] ?? 'available'));

        DB::table('employee_details')->updateOrInsert(
            ['user_id' => $dev->id],
            [
                'company_id' => $dev->company_id,
                'mobile' => $data['mobile'] ?? $dev->mobile,
                'skills' => $data['skills'] ?? 'Laravel, PHP, MySQL',
                'experience' => $data['experience'] ?? '2+ Years',
                'about' => $data['about'] ?? $dev->about,
                'status' => $statusKey,
                'updated_at' => now(),
            ]
        );

        $this->logDevActivity('developer.profile_updated', "Developer {$dev->name} updated their profile details.");

        return back()->with('success', 'Profile details updated successfully.');
    }

    /**
     * 7. Developer Settings (Password Change)
     */
    public function settings(): View
    {
        $dev = $this->getDevUser();
        return view('developer.settings', compact('dev'));
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $dev = User::findOrFail(Auth::id() ?? $this->getDevUser()->id);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $dev->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match.']);
        }

        $dev->update([
            'password' => Hash::make($data['new_password']),
            'raw_password' => $data['new_password'],
            'must_change_password' => false,
        ]);

        $this->logDevActivity('developer.password_changed', "Developer {$dev->name} changed their account password.");

        return back()->with('success', 'Password updated successfully. Account security verified.');
    }

    /**
     * 8. Update Task Status (SYNCHRONIZED WITH SUPER ADMIN & DB)
     */
    public function updateTaskStatus(Request $request, $id)
    {
        $dev = $this->getDevUser();

        $task = DB::table('tasks')->where('id', $id)->where('assigned_to', $dev->id)->first();
        if (! $task) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Task not found or not assigned to you.'], 404);
            }
            return back()->withErrors(['error' => 'Task not found or not assigned to you.']);
        }

        $newStatus = $request->input('status', 'in_progress');
        $validStatuses = ['to_do', 'assigned', 'in_progress', 'on_hold', 'completed', 'cancelled'];
        if (! in_array($newStatus, $validStatuses, true)) {
            $newStatus = 'in_progress';
        }

        $updateData = [
            'status' => $newStatus,
            'updated_at' => now(),
        ];

        if ($newStatus === 'completed') {
            $updateData['completed_on'] = now();
            $updateData['progress'] = 100;
            $updateData['is_completed'] = 1;
        } elseif ($newStatus === 'in_progress') {
            $updateData['progress'] = 50;
            $updateData['completed_on'] = null;
            $updateData['is_completed'] = 0;
        } else {
            $updateData['completed_on'] = null;
            $updateData['is_completed'] = 0;
        }

        $oldStatusFormatted = ucfirst(str_replace('_', ' ', (string)$task->status));
        $newStatusFormatted = ucfirst(str_replace('_', ' ', $newStatus));

        DB::table('tasks')->where('id', $id)->update($updateData);

        // Record Task Activity Timeline in task_history table
        try {
            DB::table('task_history')->insert([
                'task_id' => $id,
                'user_id' => $dev->id,
                'details' => "Status changed from {$oldStatusFormatted} to {$newStatusFormatted} by developer {$dev->name}.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        try {
            if (DB::getSchemaBuilder()->hasTable('task_notes')) {
                DB::table('task_notes')->insert([
                    'task_id' => $id,
                    'user_id' => $dev->id,
                    'note' => "Status changed from {$oldStatusFormatted} to {$newStatusFormatted}.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {}

        $this->logDevActivity(
            'developer.task_status_updated',
            "{$dev->name} changed task '{$task->title}' status from {$oldStatusFormatted} to {$newStatusFormatted}.",
            ['task_id' => $id, 'old_status' => $task->status, 'new_status' => $newStatus]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Task status updated to {$newStatusFormatted}.",
                'status' => $newStatus,
            ]);
        }

        return back()->with('success', "Task '{$task->title}' status updated to {$newStatusFormatted}.");
    }

    /**
     * 9. Add Progress Update Note to Task
     */
    public function addTaskNote(Request $request, $id): RedirectResponse
    {
        $dev = $this->getDevUser();

        $task = DB::table('tasks')->where('id', $id)->where('assigned_to', $dev->id)->first();
        if (! $task) {
            return back()->withErrors(['error' => 'Task not found or not assigned to you.']);
        }

        $noteText = trim((string) $request->input('note', ''));
        if (empty($noteText)) {
            return back()->withErrors(['note' => 'Please enter a valid progress update.']);
        }

        try {
            if (DB::getSchemaBuilder()->hasTable('task_notes')) {
                DB::table('task_notes')->insert([
                    'task_id' => $id,
                    'user_id' => $dev->id,
                    'note' => $noteText,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif (DB::getSchemaBuilder()->hasTable('task_comments')) {
                DB::table('task_comments')->insert([
                    'task_id' => $id,
                    'user_id' => $dev->id,
                    'comment' => $noteText,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {}

        $this->logDevActivity(
            'developer.progress_note_added',
            "{$dev->name} added progress update to task '{$task->title}': {$noteText}",
            ['task_id' => $id]
        );

        return back()->with('success', 'Progress update posted successfully.');
    }

    /**
     * 10. Get Task Details AJAX / Drawer Data
     */
    public function getTaskDetails($id)
    {
        $dev = $this->getDevUser();

        $task = DB::table('tasks')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->leftJoin('users as assigner', 'tasks.created_by', '=', 'assigner.id')
            ->select(
                'tasks.*',
                'companies.name as company_name',
                'projects.name as project_name',
                'assigner.name as assigner_name'
            )
            ->where('tasks.id', $id)
            ->where('tasks.assigned_to', $dev->id)
            ->first();

        if (! $task) {
            return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
        }

        // Fetch task notes
        $notes = [];
        try {
            if (DB::getSchemaBuilder()->hasTable('task_notes')) {
                $notes = DB::table('task_notes')
                    ->where('task_id', $id)
                    ->latest('created_at')
                    ->get();
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'task' => $task,
            'notes' => $notes,
        ]);
    }
}
