<?php
namespace App\Services;

use App\Models\{Company, CompanyStaffRole, User};
use Illuminate\Support\Facades\{DB, Schema};

class CompanyRoleDashboard
{
    public function data(User $user): array
    {
        $companyId = TenantScope::companyId();
        abort_unless($companyId && (int) $user->company_id === $companyId && $user->company_staff_role_id, 403);
        abort_unless($user->is_active && $user->login_allowed && !$user->archived_at, 403);
        $company = Company::findOrFail($companyId);
        abort_unless($company->db_name === DB::connection('tenant')->getDatabaseName(), 403);
        $role = CompanyStaffRole::where('company_id', $companyId)->findOrFail($user->company_staff_role_id);
        abort_unless(in_array($role->access_role, ['hr', 'manager', 'employee'], true) && $role->access_role === $user->role, 403);
        $schema = Schema::connection('tenant');
        $query = fn ($table) => DB::connection('tenant')->table($table);
        $tasks = collect(); $projects = collect();
        $taskCount = null; $projectCount = null; $attendanceDays = null; $leaveCount = null;
        $manager = null;
        if ($schema->hasTable('tasks') && $schema->hasColumn('tasks', 'company_id')) {
            $owned = $query('tasks')->where('tasks.company_id', $companyId);
            $hasAssigned = $schema->hasColumn('tasks', 'assigned_to');
            $hasPivot = $schema->hasTable('assigned_task_user');
            $owned->where(function ($q) use ($user, $hasAssigned, $hasPivot) {
                $q->whereRaw('1 = 0');
                if ($hasAssigned) {
                    // Match complete comma-separated IDs, never substrings such as 7 in 17.
                    $q->orWhere('assigned_to', (string) $user->id)
                        ->orWhere('assigned_to', 'like', $user->id . ',%')
                        ->orWhere('assigned_to', 'like', '%,' . $user->id . ',%')
                        ->orWhere('assigned_to', 'like', '%,' . $user->id);
                }
                if ($hasPivot) $q->orWhereExists(function ($pivot) use ($user) {
                    $pivot->selectRaw('1')->from('assigned_task_user')->whereColumn('assigned_task_user.task_id', 'tasks.id')->where('assigned_task_user.user_id', $user->id);
                });
            });
            $taskCount = (clone $owned)->count();
            $tasks = $owned->select(array_intersect(['id', 'title', 'status', 'due_date', 'project_id'], $schema->getColumnListing('tasks')))->orderByDesc('id')->limit(10)->get();
        }
        if ($schema->hasTable('projects') && $schema->hasColumn('projects', 'company_id') && $schema->hasTable('project_user')) {
            $canSeeCreated = $role->access_role === 'manager' && $schema->hasColumn('projects', 'created_by');
            $owned = $query('projects')->where('projects.company_id', $companyId)->where(function ($projects) use ($user, $canSeeCreated) {
                $projects->whereExists(function ($pivot) use ($user) {
                    $pivot->selectRaw('1')->from('project_user')->whereColumn('project_user.project_id', 'projects.id')->where('project_user.user_id', $user->id);
                });
                if ($canSeeCreated) $projects->orWhere('projects.created_by', $user->id);
            });
            if ($role->access_role === 'manager' && $schema->hasTable('tasks') && $schema->hasColumn('tasks', 'company_id') && $schema->hasColumn('tasks', 'project_id')) {
                $projectTasks = $query('tasks')->where('company_id', $companyId)->whereIn('project_id', (clone $owned)->select('projects.id'));
                $manager = ['project_tasks' => $projectTasks->count()];
            }
            $projectCount = (clone $owned)->count();
            $projects = $owned->select(array_intersect(['id', 'name', 'project_name', 'status', 'deadline'], $schema->getColumnListing('projects')))->orderByDesc('id')->limit(10)->get();
        }
        foreach (['attendances' => 'attendanceDays', 'leaves' => 'leaveCount'] as $table => $metric) {
            if (!$schema->hasTable($table) || !$schema->hasColumn($table, 'user_id')) continue;
            $owned = $query($table)->where('user_id', $user->id);
            if ($schema->hasColumn($table, 'company_id')) $owned->where('company_id', $companyId);
            if ($table === 'attendances' && $schema->hasColumn($table, 'date')) $owned->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
            $$metric = $owned->count();
        }
        $hr = null;
        if ($role->access_role === 'hr') {
            $hr = ['staff' => User::where('company_id', $companyId)->count(), 'pending_leaves' => null];
            if ($schema->hasTable('leaves') && $schema->hasColumn('leaves', 'company_id') && $schema->hasColumn('leaves', 'status')) {
                $hr['pending_leaves'] = $query('leaves')->where('company_id', $companyId)->where('status', 'pending')->count();
            }
        }
        WorkforceRecordSchema::ensure();
        $attendance = WorkforceAccess::currentAttendance($user);
        $attendancePolicy = \App\Models\AttendanceSetting::first();
        return compact('role', 'company', 'tasks', 'projects', 'taskCount', 'projectCount', 'attendanceDays', 'leaveCount', 'hr', 'manager', 'attendance', 'attendancePolicy');
    }
}
