<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollCalculationService;
use App\Services\WorkforceAccess;
use App\Services\WorkScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Realistic sample data for trying the payroll flow end to end in one company:
 * departments, designations with levels, salary structures per level, employees (Employee section),
 * salary assignments, attendance, leave (paid / unpaid / pending), projects + tasks + timesheets,
 * then a processed + approved payroll run with payslips.
 *
 * Every sample employee has an e-mail ending in "@<company>.demo", so the data can be removed
 * again with --fresh. Existing departments / designations / structures with the same names are reused.
 *
 *   php artisan payroll:demo-data                 (first company, last full month)
 *   php artisan payroll:demo-data 3 --month=9 --year=2026 --fresh
 */
class SeedPayrollDemoData extends Command
{
    protected $signature = 'payroll:demo-data
        {company? : Company id (default: the first company)}
        {--month= : Payroll month to process (default: last full month)}
        {--year= : Payroll year}
        {--fresh : Remove previously seeded sample data first}';

    protected $description = 'Seed sample employees, salary structures, attendance, leave, projects and a processed payroll with payslips.';

    private const DEPARTMENTS = ['Engineering', 'Human Resources', 'Sales & Marketing', 'Finance & Accounts'];

    /** [designation, department, level, basic salary of the level structure] */
    private const DESIGNATIONS = [
        ['Engineering Manager', 'Engineering', 1, 95000],
        ['Senior Software Engineer', 'Engineering', 2, 70000],
        ['Software Engineer', 'Engineering', 3, 45000],
        ['QA Engineer', 'Engineering', 3, 38000],
        ['Junior Developer', 'Engineering', 4, 25000],
        ['HR Manager', 'Human Resources', 2, 60000],
        ['HR Executive', 'Human Resources', 4, 28000],
        ['Sales Manager', 'Sales & Marketing', 2, 62000],
        ['Sales Executive', 'Sales & Marketing', 4, 26000],
        ['Accountant', 'Finance & Accounts', 3, 36000],
    ];

    /** [name, designation, role, attendance pattern, leave plan] */
    private const EMPLOYEES = [
        ['Ananya Bose', 'Engineering Manager', 'manager', 'punctual', 'paid'],
        ['Arjun Mehta', 'Senior Software Engineer', 'employee', 'late', 'none'],
        ['Rohan Sharma', 'Software Engineer', 'employee', 'punctual', 'unpaid'],
        ['Priya Nair', 'Software Engineer', 'employee', 'half', 'pending'],
        ['Karan Patel', 'QA Engineer', 'employee', 'absent', 'none'],
        ['Sneha Das', 'Junior Developer', 'employee', 'punctual', 'paid'],
        ['Meera Iyer', 'HR Manager', 'hr', 'punctual', 'none'],
        ['Vikram Rao', 'HR Executive', 'employee', 'late', 'pending'],
        ['Aditya Kulkarni', 'Sales Manager', 'manager', 'punctual', 'unpaid'],
        ['Pooja Singh', 'Sales Executive', 'employee', 'half', 'paid'],
        ['Rahul Verma', 'Accountant', 'employee', 'absent', 'none'],
    ];

    private string $tenant = 'tenant';
    private array $columns = [];

    public function handle(PayrollCalculationService $payroll): int
    {
        $company = $this->company();
        if (! $company) {
            $this->error('Company not found.');
            return self::FAILURE;
        }
        $this->useCompanyDatabase($company);

        $admin = User::where('company_id', $company->id)->whereIn('role', ['admin', 'administrator'])->orderBy('id')->first();
        if (! $admin) {
            $this->error("Company {$company->id} has no admin account to run payroll as.");
            return self::FAILURE;
        }
        Auth::guard('web')->setUser($admin);
        \App\Services\PayrollSchema::ensure();
        \App\Services\AttendanceClockPolicy::ensureSchema();

        $period = $this->option('month')
            ? Carbon::createFromDate((int) ($this->option('year') ?: now()->year), (int) $this->option('month'), 1)
            : now()->subMonthNoOverflow()->startOfMonth();
        $period = $period->startOfMonth();
        $domain = Str::slug($company->short_name ?: $company->name) . '.demo';

        if ($this->option('fresh')) {
            $this->removeSampleData($domain, (int) $company->id);
        } elseif (User::where('email', 'like', '%@' . $domain)->exists()) {
            $this->warn("Sample data already exists for {$company->name} (e-mails @{$domain}). Use --fresh to rebuild it.");
            return self::SUCCESS;
        }

        DB::connection($this->tenant)->transaction(function () use ($company, $admin, $domain, $period) {
            $departments = $this->departments($company, $admin);
            $designations = $this->designations($company, $admin, $departments);
            $structures = $this->salaryStructures($company, $admin, $designations);
            $employees = $this->employees($company, $admin, $domain, $designations, $departments);
            $this->salaryAssignments($company, $employees, $structures, $designations);
            $this->workData($company, $admin, $employees, $period);
        });

        // Process and approve the month exactly like Payroll > Processing does; approval creates the payslips.
        $workingDays = $this->workingDays($period, (int) $company->id);
        $run = $payroll->processPayrollRun((int) $company->id, $period->year, $period->month, $workingDays, 'all', 'all', $admin->id);
        $payroll->approvePayroll($run->fresh(), $admin->id);
        $payslips = \App\Models\Payslip::where('payroll_id', $run->id)->count();

        $this->info("Sample payroll data ready for {$company->name}:");
        $this->line('  ' . count(self::EMPLOYEES) . ' employees in ' . count(self::DEPARTMENTS) . ' departments, ' . count(self::DESIGNATIONS) . ' designations / salary structures');
        $this->line("  Attendance, leave and timesheets for {$period->format('F Y')} and " . now()->format('F Y') . ' (to date)');
        $this->line("  Payroll run #{$run->id} for {$period->format('F Y')} ({$workingDays} working days) approved with {$payslips} payslips");
        $this->line("  Sample logins: <email>@{$domain} / password Demo@12345");

        return self::SUCCESS;
    }

    private function company(): ?Company
    {
        $id = $this->argument('company');
        return $id ? Company::find((int) $id) : Company::orderBy('id')->first();
    }

    /** Point the tenant connection at the company database (multi-database installs). */
    private function useCompanyDatabase(Company $company): void
    {
        if (! empty($company->db_name) && $company->db_name !== DB::connection('tenant')->getDatabaseName()) {
            $config = DB::connection('mysql')->getConfig();
            $config['url'] = null;
            $config['database'] = $company->db_name;
            config(['database.connections.tenant' => $config]);
            DB::purge('tenant');
        }
    }

    private function cols(string $table): array
    {
        return $this->columns[$table] ??= DB::connection($this->tenant)->getSchemaBuilder()->hasTable($table)
            ? DB::connection($this->tenant)->getSchemaBuilder()->getColumnListing($table)
            : [];
    }

    /** Insert only the columns this database has (installations differ in migrations). */
    private function insert(string $table, array $row): int
    {
        $columns = array_flip($this->cols($table));
        if (! $columns) {
            return 0;
        }
        $row += ['created_at' => now(), 'updated_at' => now()];
        return (int) DB::connection($this->tenant)->table($table)->insertGetId(array_intersect_key($row, $columns));
    }

    private function departments(Company $company, User $admin): array
    {
        $ids = [];
        foreach (self::DEPARTMENTS as $i => $name) {
            $existing = DB::connection($this->tenant)->table('departments')->where('dpt_name', $name)
                ->when(in_array('company_id', $this->cols('departments'), true), fn ($q) => $q->where('company_id', $company->id))->value('id');
            $ids[$name] = $existing ?: $this->insert('departments', [
                'dpt_name' => $name, 'dpt_code' => 'DEP-' . strtoupper(Str::substr(Str::slug($name, ''), 0, 4)) . ($i + 1),
                'company_id' => $company->id, 'added_by' => $admin->id,
            ]);
        }
        return $ids;
    }

    private function designations(Company $company, User $admin, array $departments): array
    {
        $ids = [];
        $parents = [];
        $hasCompany = in_array('company_id', $this->cols('designations'), true);
        foreach (self::DESIGNATIONS as [$name, $department, $level]) {
            // Designation names can be unique across the whole table: reuse this company's (or an unowned) one,
            // and give the sample one a company suffix when another company already uses the name.
            $existing = DB::connection($this->tenant)->table('designations')->where('name', $name)->first();
            if ($existing && (! $hasCompany || ! $existing->company_id || (int) $existing->company_id === (int) $company->id)) {
                if ($hasCompany && ! $existing->company_id) {
                    DB::connection($this->tenant)->table('designations')->where('id', $existing->id)->update(['company_id' => $company->id]);
                }
                if ($existing->level === null) {
                    DB::connection($this->tenant)->table('designations')->where('id', $existing->id)->update(['level' => $level]);
                }
                $ids[$name] = ['id' => $existing->id, 'level' => (int) ($existing->level ?? $level), 'department' => $department, 'name' => $name];
                continue;
            }
            $storedName = $existing ? $name . ' (' . ($company->short_name ?: $company->name) . ')' : $name;
            // The top designation of each department is the parent of the lower levels.
            $parentId = isset($parents[$department]) && $parents[$department]['level'] < $level ? $parents[$department]['id'] : null;
            $id = $this->insert('designations', [
                'name' => $storedName, 'level' => $level, 'company_id' => $company->id, 'added_by' => $admin->id,
                'parent_id' => $parentId, 'status' => 'active', 'order' => count($ids) + 1,
            ]);
            if (in_array('unique_code', $this->cols('designations'), true)) {
                DB::connection($this->tenant)->table('designations')->where('id', $id)->update(['unique_code' => 'DGN-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)]);
            }
            $parents[$department] ??= ['id' => $id, 'level' => $level];
            $ids[$name] = ['id' => $id, 'level' => $level, 'department' => $department, 'name' => $storedName];
        }
        return $ids;
    }

    private function salaryStructures(Company $company, User $admin, array $designations): array
    {
        $ids = [];
        foreach (self::DESIGNATIONS as [$name, , , $basic]) {
            $designation = $designations[$name];
            $grade = 'L' . $designation['level'];
            $structureName = "{$designation['name']} - {$grade}";
            $existing = DB::connection($this->tenant)->table('salary_structures')->where('designation_id', $designation['id'])->where('status', 'active')->first();
            $ids[$name] = $existing ? ['id' => $existing->id, 'basic' => (float) $existing->basic_salary] : [
                'id' => $this->insert('salary_structures', [
                    'company_id' => $company->id, 'name' => $structureName,
                    'code' => Str::slug($structureName) . '-' . Str::upper(Str::random(4)),
                    'designation_id' => $designation['id'], 'grade' => $grade, 'basic_salary' => $basic,
                    'hra_type' => 'percentage', 'hra_value' => 40, 'special_allowance_type' => 'percentage', 'special_allowance_value' => 20,
                    'effective_from' => '2026-01-01', 'status' => 'active', 'is_active' => 1, 'version' => 1,
                    'description' => "Standard {$grade} structure for {$name}", 'created_by' => $admin->id,
                ]),
                'basic' => (float) $basic,
            ];
        }
        return $ids;
    }

    private function employees(Company $company, User $admin, string $domain, array $designations, array $departments): array
    {
        $prefix = rtrim(trim((string) ($company->employee_id_prefix ?? '')) ?: strtoupper(Str::slug($company->short_name ?: $company->name)) . '-EMP', '-');
        $number = (int) DB::connection($this->tenant)->table('employee_details')->where('employee_id', 'like', $prefix . '-%')->count();
        $people = [];
        foreach (self::EMPLOYEES as $i => [$name, $designationName, $role, $pattern, $leavePlan]) {
            $designation = $designations[$designationName];
            do {
                $employeeId = $prefix . '-' . str_pad((string) ++$number, 4, '0', STR_PAD_LEFT);
            } while (DB::connection($this->tenant)->table('employee_details')->where('employee_id', $employeeId)->exists());

            $userId = $this->insert('users', [
                'name' => $name, 'email' => Str::slug($name, '.') . '@' . $domain, 'password' => Hash::make('Demo@12345'),
                'role' => $role, 'company_id' => $company->id, 'is_active' => 1, 'login_allowed' => 1, 'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $this->insert('employee_details', [
                'user_id' => $userId, 'company_id' => $company->id, 'employee_id' => $employeeId, 'status' => 'Active',
                'designation_id' => $designation['id'], 'department_id' => $departments[$designation['department']],
                'mobile' => '98' . str_pad((string) (31000000 + $i * 7919), 8, '0', STR_PAD_LEFT), 'gender' => $i % 2 ? 'Male' : 'Female',
                'joining_date' => Carbon::create(2024, 1 + ($i % 12), 5 + $i)->toDateString(), 'dob' => Carbon::create(1990 + ($i % 8), 1 + ($i % 12), 10)->toDateString(),
                'employment_type' => $i === 5 ? 'probation' : 'full_time', 'business_address' => $i % 3 ? 'Kolkata' : 'Bengaluru',
                'country' => 'India', 'login_allowed' => 1, 'reporting_to' => $admin->id,
            ]);
            $people[] = ['id' => $userId, 'name' => $name, 'designation' => $designationName, 'pattern' => $pattern, 'leave' => $leavePlan, 'index' => $i];
        }
        return $people;
    }

    private function salaryAssignments(Company $company, array $employees, array $structures, array $designations): void
    {
        foreach ($employees as $person) {
            $structure = $structures[$person['designation']];
            $designation = (object) $designations[$person['designation']];
            // Individual pay differs a little from the level structure (experience / negotiation).
            $basic = round($structure['basic'] * (1 + (($person['index'] % 4) * 0.025)), -2);
            $this->insert('employee_salary_assignments', [
                'company_id' => $company->id, 'user_id' => $person['id'], 'designation_id' => $designation?->id,
                'salary_structure_id' => $structure['id'], 'grade' => 'L' . ($designation?->level ?? 0), 'actual_basic_salary' => $basic,
                'hra_type' => 'percentage', 'hra_value' => 40, 'special_allowance_type' => 'percentage', 'special_allowance_value' => 20,
                'effective_from' => '2026-01-01', 'status' => 'active', 'remarks' => 'Sample salary assignment',
            ]);
        }
    }

    /** Attendance, leave and work logs for the payroll month and the current month to date. */
    private function workData(Company $company, User $admin, array $employees, Carbon $period): void
    {
        $clientId = $this->insert('clients', ['name' => 'Northwind Retail', 'email' => 'projects@northwind.demo', 'password' => Hash::make(Str::random(24)), 'login_allowed' => 0, 'company_name' => 'Northwind Retail Pvt Ltd', 'company_id' => $company->id, 'added_by' => $admin->id]);
        $projects = [];
        foreach (['Customer Portal Revamp', 'Mobile Sales App'] as $p => $projectName) {
            $projects[] = $this->insert('projects', [
                'client_id' => $clientId, 'name' => $projectName, 'project_code' => 'PRJ-' . (100 + $p), 'status' => 'in progress',
                'start_date' => $period->copy()->subMonth()->toDateString(), 'deadline' => $period->copy()->addMonths(3)->toDateString(),
                'priority' => 'high', 'company_id' => $company->id, 'created_by' => $admin->id, 'description' => 'Sample project',
            ]);
        }

        foreach ([$period->copy(), now()->startOfMonth()] as $month) {
            if ($month->gt(now())) continue;
            $offDays = WorkScheduleService::getHolidaysAndOffDays($month->copy()->startOfMonth(), $month->copy()->endOfMonth(), (int) $company->id);
            $lastDay = $month->isSameMonth(now()) ? now()->subDay()->startOfDay() : $month->copy()->endOfMonth();
            $days = [];
            for ($day = $month->copy(); $day->lte($lastDay); $day->addDay()) {
                if (! isset($offDays[$day->toDateString()])) $days[] = $day->copy();
            }
            if (! $days) continue;

            foreach ($employees as $person) {
                $user = User::find($person['id']);
                $leaveDays = $this->leaves($company, $person, $days, $month);
                $projectId = $projects[$person['index'] % count($projects)];
                $taskId = $this->insert('tasks', [
                    'project_id' => $projectId, 'title' => $person['designation'] . ' work – ' . $month->format('M Y'),
                    'assigned_to' => $person['id'], 'status' => $month->isSameMonth(now()) ? 'in progress' : 'completed',
                    'company_id' => $company->id, 'created_by' => $admin->id, 'priority' => 'medium',
                    'due_date' => $month->copy()->endOfMonth()->toDateString(), 'progress' => $month->isSameMonth(now()) ? 40 : 100,
                ]);
                if ($this->cols('project_user') && ! DB::connection($this->tenant)->table('project_user')->where(['project_id' => $projectId, 'user_id' => $person['id']])->exists()) {
                    $this->insert('project_user', ['project_id' => $projectId, 'user_id' => $person['id']]);
                }

                foreach ($days as $n => $day) {
                    $date = $day->toDateString();
                    if (in_array($date, $leaveDays, true)) continue;
                    [$status, $in, $out] = $this->attendanceFor($person['pattern'], $n);
                    if ($status === 'unmarked') continue; // no clock-in at all: payroll counts it as absent
                    $hours = $in && $out ? round(Carbon::parse($out)->diffInMinutes(Carbon::parse($in), true) / 60, 2) : 0;
                    $this->insert('attendances', array_merge($this->workforceStamp($user, 'attendances'), [
                        'user_id' => $person['id'], 'company_id' => $company->id, 'date' => $date, 'status' => $status,
                        'clock_in' => $in, 'clock_out' => $out, 'total_hours' => $hours,
                        'late' => $status === 'late' ? 'yes' : 'no', 'half_day' => $status === 'half_day' ? 'yes' : 'no',
                        'working_from' => 'office', 'work_from_type' => 'office', 'clock_in_timezone' => 'Asia/Kolkata',
                    ]));
                    // Timesheet entry for most worked days.
                    if ($status !== 'absent' && $n % 3 !== 2) {
                        $logHours = $status === 'half_day' ? 3.5 : 6.5;
                        $this->insert('time_logs', [
                            'user_id' => $person['id'], 'project_id' => $projectId, 'task_id' => $taskId,
                            'start_date' => $date, 'start_time' => '10:00:00', 'end_date' => $date,
                            'end_time' => Carbon::parse('10:00')->addMinutes((int) ($logHours * 60))->format('H:i:s'),
                            'memo' => 'Sample work log', 'total_hours' => $logHours,
                        ]);
                    }
                }
            }
        }
    }

    /** [status, clock in, clock out] for working day number $n of an attendance pattern. */
    private function attendanceFor(string $pattern, int $n): array
    {
        return match (true) {
            $pattern === 'late' && $n % 4 === 1 => ['late', '09:58:00', '18:05:00'],
            $pattern === 'half' && $n % 6 === 3 => ['half_day', '09:25:00', '13:45:00'],
            $pattern === 'absent' && $n % 7 === 4 => ['absent', null, null],
            $pattern === 'absent' && $n % 9 === 6 => ['unmarked', null, null],
            default => ['present', '09:' . str_pad((string) (($n * 7) % 19), 2, '0', STR_PAD_LEFT) . ':00', '17:' . str_pad((string) (5 + ($n * 5) % 25), 2, '0', STR_PAD_LEFT) . ':00'],
        };
    }

    /** Leave for the month according to the person's plan; returns the dates covered by approved leave. */
    private function leaves(Company $company, array $person, array $days, Carbon $month): array
    {
        $plan = $person['leave'];
        if ($plan === 'none' || count($days) < 12) return [];
        $user = User::find($person['id']);
        $typeId = fn (string $code) => $this->cols('leave_types') ? DB::connection($this->tenant)->table('leave_types')->where('code', $code)->value('id') : null;
        $base = [
            'user_id' => $person['id'], 'company_id' => $company->id, 'emergency_flag' => 0, 'half_day_flag' => 0,
            'leave_year' => (string) $month->year, 'duration' => 'full_day',
        ];
        $make = function (array $dates, array $extra) use ($base, $user) {
            $this->insert('leaves', array_merge($this->workforceStamp($user, 'leaves'), $base, [
                'date' => $dates[0], 'start_date' => $dates[0], 'end_date' => end($dates), 'total_days' => count($dates),
            ], $extra));
        };
        $pick = fn (int $from, int $count) => array_map(fn ($d) => $d->toDateString(), array_slice($days, $from, $count));

        if ($plan === 'paid') {
            $dates = $pick(8, 2);
            $make($dates, ['type' => 'casual', 'leave_type_id' => $typeId('CL'), 'reason' => 'Family function', 'status' => 'approved', 'approval_status' => 'approved', 'approved_at' => now(), 'is_paid' => 1, 'is_unpaid' => 0, 'paid_days' => 2, 'unpaid_days' => 0]);
            return $dates;
        }
        if ($plan === 'unpaid') {
            $dates = $pick(10, 1);
            $make($dates, ['type' => 'casual', 'leave_type_id' => $typeId('CL'), 'reason' => 'Personal work (no paid balance used)', 'status' => 'approved', 'approval_status' => 'approved', 'approved_at' => now(), 'is_paid' => 0, 'is_unpaid' => 1, 'payroll_deduction_flag' => 1, 'paid_days' => 0, 'unpaid_days' => 1]);
            return $dates;
        }
        // Pending request at the end of the month: shown on Processing / payslip, not paid or deducted yet.
        $make($pick(count($days) - 2, 2), ['type' => 'sick', 'leave_type_id' => $typeId('SL'), 'reason' => 'Medical check-up', 'status' => 'pending', 'approval_status' => 'pending']);
        return [];
    }

    private function workforceStamp(User $user, string $table): array
    {
        $stamp = [];
        foreach (WorkforceAccess::snapshot($user) as $column => $value) {
            if (in_array($column, $this->cols($table), true)) $stamp[$column] = $value;
        }
        return $stamp;
    }

    private function workingDays(Carbon $period, int $companyId): int
    {
        $offDays = WorkScheduleService::getHolidaysAndOffDays($period->copy()->startOfMonth(), $period->copy()->endOfMonth(), $companyId);
        return $period->daysInMonth - count($offDays);
    }

    private function removeSampleData(string $domain, int $companyId): void
    {
        $db = DB::connection($this->tenant);
        $ids = $db->table('users')->where('email', 'like', '%@' . $domain)->pluck('id');
        if ($ids->isEmpty()) return;
        $runs = $db->table('payroll_histories')->whereIn('user_id', $ids)->pluck('payroll_id')->unique();
        foreach (['payslips', 'payroll_histories', 'employee_salary_assignments', 'attendances', 'leaves', 'time_logs', 'project_user', 'employee_details'] as $table) {
            if ($this->cols($table) && in_array('user_id', $this->cols($table), true)) $db->table($table)->whereIn('user_id', $ids)->delete();
        }
        if ($this->cols('tasks')) $db->table('tasks')->whereIn('assigned_to', $ids)->delete();
        // Runs that only contained sample employees disappear with them.
        foreach ($runs as $runId) {
            if (! $db->table('payroll_histories')->where('payroll_id', $runId)->exists()) $db->table('payrolls')->where('id', $runId)->delete();
        }
        $db->table('users')->whereIn('id', $ids)->delete();
        $clientIds = $db->table('clients')->where('email', 'projects@northwind.demo')->pluck('id');
        if ($clientIds->isNotEmpty()) {
            $db->table('projects')->whereIn('client_id', $clientIds)->delete();
            $db->table('clients')->whereIn('id', $clientIds)->delete();
        }
        $this->line("Removed previous sample data ({$ids->count()} employees).");
    }
}
