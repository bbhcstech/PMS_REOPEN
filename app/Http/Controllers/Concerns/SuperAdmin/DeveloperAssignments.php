<?php

namespace App\Http\Controllers\Concerns\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

trait DeveloperAssignments
{
    public function assignWork(Request $request): RedirectResponse
    {
        @set_time_limit(120);
        config(['mail.mailers.smtp.timeout' => 5]);

        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'developer_id' => ['nullable', 'integer'],
            'developer_email' => ['required', 'email'],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'task_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'additional_instructions' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:' . ($request->filled('start_date') ? 'start_date' : now()->toDateString())],
            'estimate_hours' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'attachments' => ['nullable', 'string'],
        ], [
            'due_date.after_or_equal' => 'Deadline must be on or after the Start Date.',
        ]);

        $developerEmail = strtolower(trim($data['developer_email']));

        // Check if developer account ALREADY EXISTS
        $developer = null;
        if (!empty($data['developer_id'])) {
            $developer = User::find($data['developer_id']);
        }
        if (!$developer) {
            $developer = User::where(function ($query) use ($developerEmail) {
                $query->where('email', $developerEmail);
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'personal_email')) {
                    $query->orWhere('personal_email', $developerEmail);
                }
            })->first();
        }

        $wasCreated = false;
        $tempPassword = null;

        // IF DEVELOPER ACCOUNT DOES NOT EXIST -> CREATE IT ONCE ONLY
        if (!$developer) {
            $compCompanyId = !empty($data['company_id']) ? $data['company_id'] : null;
            $comp = $compCompanyId ? Company::find($compCompanyId) : Company::first();

            $tempPassword = \App\Support\DeveloperPassword::generate();
            $devName = !empty($data['developer_name']) ? $data['developer_name'] : explode('@', $developerEmail)[0];
            $devDesignation = !empty($data['designation']) ? $data['designation'] : 'Developer';

            $developer = User::create([
                'company_id' => $comp?->id,
                'name' => ucfirst($devName),
                'email' => $developerEmail,
                'personal_email' => $developerEmail,
                'role' => 'developer',
                'designation' => $devDesignation,
                'password' => Hash::make($tempPassword),
                'raw_password' => $tempPassword,
                'must_change_password' => false,
                'is_active' => true,
                'login_allowed' => true,
                'email_notifications' => true,
                'joining_date' => now()->toDateString(),
            ]);

            $devCode = 'DEV-' . str_pad((string) $developer->id, 4, '0', STR_PAD_LEFT);
            try {
                DB::table('employee_details')->updateOrInsert(
                    ['user_id' => $developer->id],
                    [
                        'company_id' => $comp?->id,
                        'developer_id' => $devCode,
                        'skills' => 'Laravel, PHP, MySQL',
                        'status' => 'available',
                        'joining_date' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {}

            // If company has a tenant database, sync developer record to tenant DB
            if ($comp && !empty($comp->db_name)) {
                $assignmentOriginalDatabase = config('database.connections.tenant.database');
                try {
                    config(['database.connections.tenant.database' => $comp->db_name]);
                    DB::purge('tenant');

                    $tenantDev = User::on('tenant')->where('email', $developerEmail)->first();
                    if (!$tenantDev) {
                        User::on('tenant')->create([
                            'company_id' => $comp->id,
                            'name' => ucfirst($devName),
                            'email' => $developerEmail,
                            'personal_email' => $developerEmail,
                            'role' => 'developer',
                            'designation' => $devDesignation,
                            'password' => Hash::make($tempPassword),
                            'raw_password' => $tempPassword,
                            'must_change_password' => false,
                            'is_active' => true,
                            'login_allowed' => true,
                            'joining_date' => now()->toDateString(),
                        ]);
                    }
                } catch (\Throwable $e) {}
                finally {
                    config(['database.connections.tenant.database' => $assignmentOriginalDatabase]);
                    DB::purge('tenant');
                }
            }

            // Send credential email for NEW developer account safely (non-blocking)
            $targetEmail = $developer->personal_email ?: $developerEmail;
            $this->safelyDispatchMail($targetEmail, new \App\Mail\DeveloperAccountCreated($developer, $tempPassword, route('login')));

            $wasCreated = true;
        }
        // IF DEVELOPER ALREADY EXISTS: DO NOT CREATE NEW ACCOUNT, DO NOT GENERATE NEW PASSWORD, DO NOT SEND LOGIN CREDENTIALS AGAIN.

        $requestedCompanyId = !empty($data['company_id']) ? $data['company_id'] : ($developer->company_id ?: Company::first()?->id);

        // Ensure company_id exists in current database's companies table (or fallback to any valid company, or null)
        $companyId = null;
        if ($requestedCompanyId) {
            $companyId = DB::table('companies')->where('id', $requestedCompanyId)->value('id');
        }
        if (!$companyId) {
            $companyId = DB::table('companies')->first()?->id;
        }

        $estimateHours = !empty($data['estimate_hours']) ? $data['estimate_hours'] : 8;

        // Ensure project_id exists in current database's projects table
        $projectId = null;
        if (!empty($data['project_id'])) {
            $projectId = DB::table('projects')->where('id', $data['project_id'])->value('id');
        }
        if (!$projectId && $companyId) {
            $projectId = DB::table('projects')->where('company_id', $companyId)->first()?->id;
        }
        if (!$projectId) {
            $projectId = DB::table('projects')->first()?->id;
        }
        if (!$projectId) {
            try {
                $projectId = DB::table('projects')->insertGetId([
                    'company_id' => $companyId,
                    'name' => 'Internal Platform Project',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {}
        }

        // Resolve valid created_by user ID (nullable foreign key to users.id)
        $creatorId = null;
        if (auth()->check()) {
            $creatorId = DB::table('users')->where('id', auth()->id())->value('id');
        }
        if (!$creatorId && auth('super_admin')->check()) {
            $saEmail = auth('super_admin')->user()?->email;
            if ($saEmail) {
                $creatorId = DB::table('users')->where('email', $saEmail)->value('id');
            }
        }
        if (!$creatorId && $companyId) {
            $creatorId = DB::table('users')->where('company_id', $companyId)->whereIn('role', ['admin', 'superadmin', 'administrator'])->value('id')
                ?? DB::table('users')->where('company_id', $companyId)->value('id');
        }
        if (!$creatorId && !empty($developer?->id)) {
            $creatorId = DB::table('users')->where('id', $developer->id)->value('id');
        }
        if (!$creatorId) {
            $creatorId = DB::table('users')->value('id');
        }

        \App\Services\DeveloperTaskSchema::ensure();
        $taskId = DB::table('tasks')->insertGetId([
            'company_id' => $companyId,
            'title' => $data['task_title'],
            'description' => $data['description'] ?? '',
            'additional_instructions' => $data['additional_instructions'] ?? null,
            'attachments' => $data['attachments'] ?? null,
            'project_id' => $projectId,
            'assigned_to' => $developer->id,
            'created_by' => $creatorId,
            'priority' => strtolower($data['priority']),
            'start_date' => !empty($data['start_date']) ? \Carbon\Carbon::parse($data['start_date'])->toDateTimeString() : now()->toDateTimeString(),
            'due_date' => !empty($data['due_date']) ? \Carbon\Carbon::parse($data['due_date'])->toDateTimeString() : now()->addDays(5)->toDateTimeString(),
            'estimate_hours' => $estimateHours,
            'status' => 'assigned',
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            DB::table('assigned_task_user')->insertOrIgnore([
                'task_id' => $taskId,
                'user_id' => $developer->id,
                'assigned_by' => $creatorId,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        // Tenant IDs are independent of the platform registry IDs.
        if ($requestedCompanyId) {
            $sourceTask = (array) DB::table('tasks')->where('id', $taskId)->first();
            $sourceProject = $projectId ? DB::table('projects')->where('id', $projectId)->first() : null;
            $assignmentOriginalDatabase = config('database.connections.tenant.database');
            try {
                $comp = Company::find($requestedCompanyId);
                if ($comp && !empty($comp->db_name) && $comp->db_name !== $assignmentOriginalDatabase) {
                    config(['database.connections.tenant.database' => $comp->db_name]);
                    DB::purge('tenant');
                    $tenant = DB::connection('tenant');
                    if (Schema::connection('tenant')->hasTable('tasks')) {
                        $tenantDeveloper = $tenant->table('users')->where('email', $developer->email)->first();
                        if (!$tenantDeveloper) {
                            $attributes = array_intersect_key($developer->getAttributes(), array_flip(Schema::connection('tenant')->getColumnListing('users')));
                            unset($attributes['id']);
                            $attributes['company_id'] = $comp->id;
                            $tenantDeveloperId = $tenant->table('users')->insertGetId($attributes);
                        } else {
                            $tenantDeveloperId = $tenantDeveloper->id;
                        }
                        \App\Services\DeveloperTaskSchema::ensure('tenant');
                        $tenant->transaction(function () use ($tenant, $sourceTask, $sourceProject, $tenantDeveloperId, $comp, $taskId) {
                            $attributes = $sourceTask;
                            unset($attributes['id']);
                            $attributes['platform_task_id'] = $taskId;
                            $attributes['assigned_to'] = $tenantDeveloperId;
                            $attributes['company_id'] = $tenant->table('companies')->where('id', $comp->id)->value('id');
                            $attributes['created_by'] = $tenantDeveloperId;
                            $attributes['project_id'] = $sourceProject && Schema::connection('tenant')->hasTable('projects')
                                ? $tenant->table('projects')->where('name', $sourceProject->name)
                                    ->when(Schema::connection('tenant')->hasColumn('projects', 'company_id'), fn ($query) => $query->where('company_id', $comp->id))
                                    ->value('id') : null;
                            if (!$attributes['project_id'] && Schema::connection('tenant')->hasTable('projects')) {
                                $attributes['project_id'] = \App\Services\DeveloperTaskSync::workspaceProjectId($comp->id, 'tenant');
                            }
                            $attributes = array_intersect_key($attributes, array_flip(Schema::connection('tenant')->getColumnListing('tasks')));
                            $tenantTaskId = $tenant->table('tasks')->insertGetId($attributes);
                            if (Schema::connection('tenant')->hasTable('assigned_task_user')) {
                                $tenant->table('assigned_task_user')->insert([
                                    'task_id' => $tenantTaskId, 'user_id' => $tenantDeveloperId,
                                    'assigned_by' => $tenantDeveloperId, 'assigned_at' => now(),
                                    'created_at' => now(), 'updated_at' => now(),
                                ]);
                            }
                        });
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            } finally {
                config(['database.connections.tenant.database' => $assignmentOriginalDatabase]);
                DB::purge('tenant');
            }
        }

        // Log task history
        try {
            DB::table('task_history')->insert([
                'task_id' => $taskId,
                'user_id' => $creatorId ?? $developer->id,
                'details' => 'Task assigned to ' . $developer->name . ' by Super Admin.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        // Dispatch task assignment email notification to developer personal email (non-blocking)
        $notifyEmail = $developer->personal_email ?: $developer->email;
        $dueDateFormatted = !empty($data['due_date']) ? \Carbon\Carbon::parse($data['due_date'])->format('M d, Y') : 'In 5 Days';
        $instructions = $data['additional_instructions'] ?? null;
        $this->safelyDispatchMail($notifyEmail, new \App\Mail\WorkAssignedNotification(
            $developer,
            $data['task_title'],
            $data['priority'],
            $dueDateFormatted,
            $instructions,
            url('/developer/my-work')
        ));

        $this->logAction('developer.work_assigned', Company::find($companyId), [
            'developer_id' => $developer->id,
            'developer_email' => $developer->email,
            'task_title' => $data['task_title'],
            'priority' => $data['priority'],
            'was_account_created' => $wasCreated,
        ]);

        $msg = 'Work assigned successfully to existing developer ' . $developer->name . ' (' . $developer->email . ').';
        if ($wasCreated) {
            $msg = 'New developer account created and work assigned to ' . $developer->name . '. Temporary password: ' . $tempPassword;
        }

        return back()->with('success', $msg);
    }

    public function updateTaskStatus(Request $request, $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:to_do,assigned,in_progress,on_hold,completed,cancelled'],
        ]);

        $newStatus = $data['status'];
        $updateData = ['status' => $newStatus, 'updated_at' => now()];

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

        $task = DB::table('tasks')->where('id', $id)->first();
        $oldStatus = $task?->status;
        DB::table('tasks')->where('id', $id)->update($updateData);

        // Also sync update to tenant database if company has a dedicated db_name
        if ($task && !empty($task->company_id)) {
            try {
                $comp = Company::find($task->company_id);
                if ($comp && !empty($comp->db_name)) {
                    config(['database.connections.tenant.database' => $comp->db_name]);
                    DB::purge('tenant');
                    if (Schema::connection('tenant')->hasTable('tasks')) {
                        $tenantTasks = DB::connection('tenant')->table('tasks');
                        if (Schema::connection('tenant')->hasColumn('tasks', 'platform_task_id')) {
                            $tenantTasks->where('platform_task_id', $id);
                        } else {
                            $tenantTasks->where('id', $id);
                        }
                        $tenantTasks->update($updateData);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Record Task History
        try {
            DB::table('task_history')->insert([
                'task_id' => $id,
                'user_id' => auth()->id() ?? 1,
                'details' => 'Status changed from ' . ucfirst(str_replace('_', ' ', (string)$oldStatus)) . ' to ' . ucfirst(str_replace('_', ' ', $newStatus)) . ' by Super Admin.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Task status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . ' successfully.');
    }

    public function getTaskHistory(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $task = DB::table('tasks')
            ->leftJoin('users as dev', 'tasks.assigned_to', '=', 'dev.id')
            ->leftJoin('companies', 'tasks.company_id', '=', 'companies.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->select('tasks.*', 'dev.name as developer_name', 'dev.email as developer_email', 'companies.name as company_name', 'projects.name as project_name')
            ->where('tasks.id', $id)
            ->first();

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found']);
        }

        $history = DB::table('task_history')
            ->leftJoin('users', 'task_history.user_id', '=', 'users.id')
            ->select('task_history.*', 'users.name as user_name')
            ->where('task_id', $id)
            ->latest('task_history.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'task' => $task,
            'history' => $history
        ]);
    }
}
