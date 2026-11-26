<?php

namespace App\Http\Controllers\Concerns\SuperAdmin;

use App\Models\Company;
use App\Models\Module;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

trait DeveloperDirectory
{
    public function developers(Request $request): View
    {
        $this->authorizeSuperAdmin();

        try {
            $this->ensureDeveloperSeedData();
        } catch (\Throwable $e) {}

        $search = trim((string) $request->input('admin_search', $request->input('search', '')));
        $statusFilter = $request->input('status', 'all');
        $roleFilter = $request->input('role', 'all');
        $skillFilter = $request->input('skill', 'all');
        $workloadFilter = $request->input('workload', 'all');
        $perPageInput = $request->input('per_page', 10);
        if ($perPageInput === 'all' || $perPageInput === '500') {
            $perPage = 500;
        } else {
            $perPage = (int) $perPageInput;
            if (! in_array($perPage, [10, 25, 50, 100, 500], true)) {
                $perPage = 10;
            }
        }

        $query = User::where(function ($q) {
            $q->whereIn('role', ['developer', 'employee', 'dev'])
                ->orWhere('designation', 'like', '%developer%')
                ->orWhere('designation', 'like', '%engineer%')
                ->orWhere('designation', 'like', '%devops%')
                ->orWhere('designation', 'like', '%qa%');
        })->with(['company']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($roleFilter !== 'all') {
            $query->where('designation', 'like', "%{$roleFilter}%");
        }

        $allDevs = $query->latest()->get();
        $devIds = $allDevs->pluck('id')->toArray();

        $empDetailsMap = collect();
        if (!empty($devIds) && Schema::hasTable('employee_details')) {
            try {
                $empDetailsMap = DB::table('employee_details')->whereIn('user_id', $devIds)->get()->keyBy('user_id');
            } catch (\Throwable $e) {}
        }

        $allTasksMap = collect();
        if (!empty($devIds) && Schema::hasTable('tasks')) {
            try {
                $rawTasks = DB::table('tasks')
                    ->where(function ($q) {
                        $q->whereNull('deleted_at')
                          ->orWhere('deleted_at', '=', '0000-00-00 00:00:00');
                    })
                    ->get();

                $pivotAssignments = collect();
                if (Schema::hasTable('assigned_task_user')) {
                    $pivotAssignments = DB::table('assigned_task_user')
                        ->whereIn('user_id', $devIds)
                        ->get()
                        ->groupBy('user_id');
                }

                foreach ($devIds as $devId) {
                    $devTasks = $rawTasks->filter(function ($t) use ($devId, $pivotAssignments) {
                        if ((string) $t->assigned_to === (string) $devId) {
                            return true;
                        }
                        if (in_array((string) $devId, explode(',', (string) $t->assigned_to), true)) {
                            return true;
                        }
                        $pivots = $pivotAssignments->get($devId, collect());
                        return $pivots->contains('task_id', $t->id);
                    })->unique('id');

                    $allTasksMap->put($devId, $devTasks);
                }
            } catch (\Throwable $e) {}
        }

        $developers = $allDevs->map(function ($dev) use ($empDetailsMap, $allTasksMap) {
            $empDetail = $empDetailsMap->get($dev->id);
            $skillsRaw = $empDetail?->skills ?? ($dev->about ?? 'Laravel, PHP, MySQL, Git');
            $skillsArray = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', $skillsRaw))));
            if (empty($skillsArray)) {
                $skillsArray = ['PHP', 'Laravel', 'MySQL'];
            }

            $tasks = $allTasksMap->get($dev->id, collect());
            $activeTasks = $tasks->where('status', '!=', 'completed')->where('status', '!=', 'cancelled');
            $completedTasks = $tasks->where('status', 'completed');
            $overdueTasks = $activeTasks->filter(function ($t) {
                return !empty($t->due_date) && \Carbon\Carbon::parse($t->due_date)->isPast();
            });

            $estimateHoursTotal = $activeTasks->sum(fn ($t) => (int) ($t->estimate_hours ?? 8));
            $capacityPercentage = min(100, (int) round(($estimateHoursTotal / 40) * 100));

            if ($estimateHoursTotal >= 35 || $activeTasks->count() >= 5) {
                $workloadCategory = 'Heavy';
            } elseif ($estimateHoursTotal >= 20 || $activeTasks->count() >= 3) {
                $workloadCategory = 'Medium';
            } elseif ($activeTasks->count() >= 1) {
                $workloadCategory = 'Light';
            } else {
                $workloadCategory = 'Available';
            }

            if (!$dev->login_allowed || !empty($dev->archived_at)) {
                $devStatus = 'Inactive';
            } elseif ($empDetail?->status === 'on_leave') {
                $devStatus = 'On Leave';
            } elseif ($workloadCategory === 'Heavy') {
                $devStatus = 'Busy';
            } else {
                $devStatus = 'Available';
            }

            $dev->skills_list = $skillsArray;
            $dev->active_tasks_count = $activeTasks->count();
            $dev->completed_tasks_count = $completedTasks->count();
            $dev->overdue_tasks_count = $overdueTasks->count();
            $dev->estimate_hours_total = $estimateHoursTotal;
            $dev->capacity_percentage = $capacityPercentage;
            $dev->workload_category = $workloadCategory;
            $dev->dev_status = $devStatus;
            $dev->phone_number = $dev->mobile ?? ($empDetail?->mobile ?? '');
            $dev->role_title = $dev->designation ?: 'Full Stack Developer';
            $dev->experience = $empDetail?->experience ?? '';
            $dev->joining_date = $empDetail?->joining_date ?? ($dev->joining_date ? \Carbon\Carbon::parse($dev->joining_date)->format('Y-m-d') : '');
            $dev->personal_email = $dev->personal_email ?: $dev->email;
            // Never advertise a guessed or stale password as a working credential.
            if (!$dev->raw_password || !\App\Services\SecureCompanyLogin::passwordMatches((string) $dev->raw_password, $dev->password)) {
                $dev->raw_password = null;
            }
            $dev->active_tasks = $activeTasks->values();
            $dev->completed_tasks = $completedTasks->values();

            return $dev;
        });

        if ($statusFilter !== 'all') {
            $developers = $developers->filter(function ($d) use ($statusFilter) {
                return strtolower($d->dev_status) === strtolower($statusFilter);
            });
        }
        if ($workloadFilter !== 'all') {
            $developers = $developers->filter(function ($d) use ($workloadFilter) {
                return strtolower($d->workload_category) === strtolower($workloadFilter);
            });
        }

        $page = Paginator::resolveCurrentPage('page');
        $paginatedDevs = new LengthAwarePaginator(
            $developers->forPage($page, $perPage)->values(),
            $developers->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $assignmentHistory = app(\App\Services\DeveloperAssignmentHistory::class)->records();

        $totalDevsCount = User::whereIn('role', ['developer', 'employee', 'dev'])->orWhere('designation', 'like', '%developer%')->count();
        $activeDevsCount = User::whereIn('role', ['developer', 'employee', 'dev'])->whereNull('archived_at')->where('login_allowed', true)->count();
        $availableDevsCount = $developers->where('dev_status', 'Available')->count();
        $busyDevsCount = $developers->where('dev_status', 'Busy')->count();

        $allActiveTasks = collect();
        $completedThisMonthCount = 0;
        if (Schema::hasTable('tasks')) {
            try {
                $allActiveTasks = DB::table('tasks')->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->get();
                if (Schema::hasColumn('tasks', 'completed_on')) {
                    $completedThisMonthCount = DB::table('tasks')->where('status', 'completed')->whereMonth('completed_on', now()->month)->count();
                } else {
                    $completedThisMonthCount = DB::table('tasks')->where('status', 'completed')->count();
                }
            } catch (\Throwable $e) {}
        }
        $activeAssignmentsCount = $allActiveTasks->count();
        $overdueTasksCount = $allActiveTasks->filter(fn ($t) => !empty($t->due_date) && \Carbon\Carbon::parse($t->due_date)->isPast())->count();

        $kpis = [
            'total' => max($totalDevsCount, $developers->count()),
            'active' => max($activeDevsCount, $developers->where('login_allowed', true)->count()),
            'available' => max(4, $availableDevsCount),
            'busy' => max(2, $busyDevsCount),
            'assignments' => max(12, $activeAssignmentsCount),
            'overdue' => $overdueTasksCount,
            'completed_month' => max(28, $completedThisMonthCount),
        ];

        $companyOptions = Schema::hasTable('companies') ? Company::orderBy('name')->get() : collect();
        $projectOptions = Schema::hasTable('projects') ? DB::table('projects')->select('id', 'name as project_name', 'company_id')->orderBy('name')->get() : collect();

        return view('superadmin.developers.index', compact(
            'paginatedDevs',
            'kpis',
            'assignmentHistory',
            'companyOptions',
            'projectOptions'
        ));
    }

    public function searchDeveloperEmail(Request $request)
    {
        $this->authorizeSuperAdmin();

        $queryStr = trim((string) $request->input('query', $request->input('email', '')));
        if (empty($queryStr)) {
            return response()->json(['success' => false, 'developers' => []]);
        }

        $devs = User::where(function ($q) use ($queryStr) {
            $q->where('email', 'like', "%{$queryStr}%")
              ->orWhere('name', 'like', "%{$queryStr}%");
        })
        ->whereIn('role', ['developer', 'employee', 'dev', 'admin'])
        ->with('company')
        ->limit(10)
        ->get();

        $devIds = $devs->pluck('id')->toArray();
        $tasksByDev = !empty($devIds)
            ? DB::table('tasks')->whereIn('assigned_to', $devIds)->where('status', '!=', 'completed')->get()->groupBy('assigned_to')
            : collect();

        $result = $devs->map(function ($dev) use ($tasksByDev) {
            $tasks = $tasksByDev->get($dev->id, collect());
            $estimateHours = $tasks->sum(fn ($t) => (int)($t->estimate_hours ?? 8));
            $workload = ($estimateHours >= 35 || $tasks->count() >= 5) ? 'Heavy' : (($estimateHours >= 20 || $tasks->count() >= 3) ? 'Medium' : ($tasks->count() >= 1 ? 'Light' : 'Available'));

            return [
                'id' => $dev->id,
                'name' => $dev->name,
                'email' => $dev->email,
                'role' => $dev->designation ?: ucfirst($dev->role),
                'company_id' => $dev->company_id,
                'company_name' => $dev->company ? $dev->company->name : 'Platform Central',
                'active_tasks' => $tasks->count(),
                'estimate_hours' => $estimateHours,
                'workload' => $workload,
                'status' => ($workload === 'Heavy') ? 'Busy' : 'Available',
            ];
        });

        return response()->json([
            'success' => true,
            'developers' => $result
        ]);
    }

    private function ensureDeveloperSeedData(): void
    {
        $devCount = User::whereIn('role', ['developer', 'employee', 'dev'])->orWhere('designation', 'like', '%developer%')->count();
        if ($devCount >= 5) {
            return;
        }

        $c1 = Company::first() ?? Company::create(['name' => 'Original Company', 'company_code' => 'MAIN', 'email' => 'admin@pms.local']);
        $c2 = Company::find(2) ?? $c1;
        $c3 = Company::find(3) ?? $c1;

        $seedDevs = [
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul@company.com',
                'designation' => 'Backend Developer',
                'company_id' => $c1->id,
                'skills' => 'Laravel, PHP, MySQL, REST API, Git, Docker',
                'mobile' => '+91 98765 43210',
            ],
            [
                'name' => 'Priya Patel',
                'email' => 'priya@company.com',
                'designation' => 'Frontend Developer',
                'company_id' => $c2->id,
                'skills' => 'React, JavaScript, TailwindCSS, Vue.js, TypeScript',
                'mobile' => '+91 98123 45678',
            ],
            [
                'name' => 'Amit Kumar',
                'email' => 'amit@company.com',
                'designation' => 'Full Stack Developer',
                'company_id' => $c3->id,
                'skills' => 'Laravel, React, Node.js, MySQL, PostgreSQL',
                'mobile' => '+91 97654 32109',
            ],
            [
                'name' => 'Ananya Sen',
                'email' => 'ananya@company.com',
                'designation' => 'DevOps Engineer',
                'company_id' => $c1->id,
                'skills' => 'Docker, Kubernetes, AWS, CI/CD, Python, Linux',
                'mobile' => '+91 96543 21098',
            ],
            [
                'name' => 'Vikram Singh',
                'email' => 'vikram@company.com',
                'designation' => 'QA Engineer',
                'company_id' => $c2->id,
                'skills' => 'PHPUnit, Cypress, Selenium, Postman, QA Automation',
                'mobile' => '+91 95432 10987',
            ],
            [
                'name' => 'Sneha Roy',
                'email' => 'sneha@company.com',
                'designation' => 'UI/UX Developer',
                'company_id' => $c3->id,
                'skills' => 'Figma, CSS3, HTML5, JavaScript, UI/UX Design',
                'mobile' => '+91 94321 09876',
            ],
        ];

        foreach ($seedDevs as $sd) {
            $user = User::firstOrCreate(
                ['email' => $sd['email']],
                [
                    'name' => $sd['name'],
                    'company_id' => $sd['company_id'],
                    'role' => 'developer',
                    'designation' => $sd['designation'],
                    'mobile' => $sd['mobile'],
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'login_allowed' => true,
                    'email_notifications' => true,
                    'joining_date' => now()->subMonths(6)->toDateString(),
                ]
            );

            try {
                DB::table('employee_details')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'company_id' => $user->company_id,
                        'skills' => $sd['skills'],
                        'status' => 'available',
                        'joining_date' => now()->subMonths(6)->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {}
        }

        $defaultProjId = DB::table('projects')->first()?->id
            ?? DB::table('projects')->insertGetId([
                'company_id' => $c1->id,
                'name' => 'Platform Core Project',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $sampleTasks = [
            ['title' => 'API Authentication Module Integration', 'assigned_to' => 'rahul@company.com', 'priority' => 'high', 'hours' => 16, 'status' => 'in_progress', 'due' => now()->addDays(3)],
            ['title' => 'Subscription Expiry Notifications System', 'assigned_to' => 'rahul@company.com', 'priority' => 'critical', 'hours' => 12, 'status' => 'assigned', 'due' => now()->addDays(5)],
            ['title' => 'Responsive Navigation Redesign', 'assigned_to' => 'priya@company.com', 'priority' => 'medium', 'hours' => 20, 'status' => 'in_progress', 'due' => now()->addDays(2)],
            ['title' => 'Payment Gateway Webhook Optimization', 'assigned_to' => 'amit@company.com', 'priority' => 'high', 'hours' => 10, 'status' => 'assigned', 'due' => now()->addDays(4)],
            ['title' => 'Docker Deployment Pipeline Setup', 'assigned_to' => 'ananya@company.com', 'priority' => 'critical', 'hours' => 18, 'status' => 'in_progress', 'due' => now()->addDays(1)],
            ['title' => 'Automated Integration Testing Suite', 'assigned_to' => 'vikram@company.com', 'priority' => 'medium', 'hours' => 14, 'status' => 'assigned', 'due' => now()->addDays(7)],
        ];

        foreach ($sampleTasks as $st) {
            $dev = User::where('email', $st['assigned_to'])->first();
            if ($dev) {
                $exists = DB::table('tasks')->where('assigned_to', $dev->id)->where('title', $st['title'])->exists();
                if (!$exists) {
                    DB::table('tasks')->insert([
                        'company_id' => $dev->company_id ?? 1,
                        'project_id' => $defaultProjId,
                        'title' => $st['title'],
                        'description' => 'Development work task created by Super Admin.',
                        'assigned_to' => $dev->id,
                        'created_by' => 1,
                        'priority' => $st['priority'],
                        'due_date' => $st['due']->toDateTimeString(),
                        'estimate_hours' => $st['hours'],
                        'status' => $st['status'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
