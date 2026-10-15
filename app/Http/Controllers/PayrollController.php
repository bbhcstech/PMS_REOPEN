<?php

namespace App\Http\Controllers;

use App\Models\BonusRule;
use App\Models\BusinessAddress;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\DeductionComponent;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\EmployeeSalaryAssignment;
use App\Models\OvertimeRule;
use App\Models\Payroll;
use App\Models\PayrollArchitecture;
use App\Models\PayrollArchitectureVersion;
use App\Models\PayrollAuditLog;
use App\Models\PayrollCycle;
use App\Models\PayrollFormula;
use App\Models\PayrollHistory;
use App\Models\Payslip;
use App\Models\PayslipTemplate;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureVersion;
use App\Models\TaxRule;
use App\Models\User;
use App\Services\PayrollCalculationService;
use App\Services\PdfDocument as Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    use \App\Http\Controllers\Concerns\ManagesPayrollFormulas;
    /**
     * 1. Payroll Dashboard
     */
    public function index(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $selectedMonth = (int) $request->input('month', now()->month);
        $selectedYear = (int) $request->input('year', now()->year);

        $payrollService = app(PayrollCalculationService::class);
        $eligibleEmployees = $payrollService->getEligibleEmployees($companyId, null, null, null, $selectedYear, $selectedMonth);
        $totalEmployees = $eligibleEmployees->count();
        $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();

        // Use saved employee lines, never cached run totals or unsaved projections.
        // Include department/office runs for the month; a rerun replaces an employee's older line.
        $periodRuns = $this->companyQuery(Payroll::query(), $companyId)
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->orderByDesc('created_at')->orderByDesc('id')->get();
        $currentPayroll = $periodRuns->first();
        $runOrder = $periodRuns->pluck('id')->flip();
        $savedLines = PayrollHistory::whereIn('payroll_id', $periodRuns->pluck('id'))
            ->whereIn('user_id', $eligibleEmployees->pluck('id'))
            ->get()->sort(function ($a, $b) use ($runOrder) {
                return ($runOrder[$a->payroll_id] <=> $runOrder[$b->payroll_id]) ?: ($b->id <=> $a->id);
            })->unique('user_id')->values();
        $processedCount = $savedLines->count();
        $pendingCount = max(0, $totalEmployees - $processedCount);
        $grossTotal = $deductionTotal = $netTotal = $inHandTotal = $employerTotal = $ctcTotal = 0.0;
        $counts = ['finalized' => 0, 'approved' => 0, 'reviewed' => 0, 'calculated' => 0, 'draft' => 0];
        foreach ($savedLines as $line) {
            $snapshot = $line->snapshot ?? [];
            $gross = (float) ($snapshot['gross_salary'] ?? $line->gross_salary ?? 0);
            $deductions = (float) ($snapshot['total_deductions'] ?? $line->total_deductions ?? 0);
            $net = (float) ($snapshot['net_pay'] ?? $line->net_salary ?? 0);
            $employer = (float) ($snapshot['total_employer_contribution'] ?? 0);
            $grossTotal += $gross;
            $deductionTotal += $deductions;
            $netTotal += $net;
            $inHandTotal += (float) ($snapshot['total_in_hand'] ?? $line->total_in_hand ?? $net);
            $employerTotal += $employer;
            $ctcTotal += (float) ($snapshot['ctc'] ?? $line->ctc ?? ($gross + $employer + ($snapshot['total_additional_earnings'] ?? 0)));
            $status = strtolower((string) $line->payroll_status);
            $counts[array_key_exists($status, $counts) ? $status : 'draft']++;
        }
        $finalizedCount = $counts['finalized'];
        $approvedCount = $counts['approved'];
        $reviewedCount = $counts['reviewed'];
        $calculatedCount = $counts['calculated'];
        $draftCount = $counts['draft'];

        $kpis = [
            'total_employees' => $totalEmployees,
            'payroll_processed' => $processedCount,
            'payroll_pending' => $pendingCount,
            'total_gross' => $grossTotal,
            'total_deductions' => $deductionTotal,
            'total_net_pay' => $netTotal,
            'total_in_hand' => $inHandTotal,
            'total_employer_contribution' => $employerTotal,
            'total_ctc' => $ctcTotal,
            'selected_month' => $selectedMonth,
            'selected_year' => $selectedYear,
            'month_name' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F'),
        ];

        $statusBreakdown = [
            'finalized' => $finalizedCount,
            'approved' => $approvedCount,
            'reviewed' => $reviewedCount,
            'calculated' => $calculatedCount,
            'draft' => $draftCount,
            'total' => max(1, $processedCount ?: $totalEmployees),
        ];

        $recentPayrolls = $this->dashboardSection('recent payroll runs', fn () => $this->companyQuery(Payroll::query(), $companyId)->latest()->take(6)->get(), collect());
        $totalPayrollRuns = $this->dashboardSection('payroll run count', fn () => $this->companyQuery(Payroll::query(), $companyId)->count(), 0);
        $structuresCount = $this->dashboardSection('salary structure count', fn () => SalaryStructure::where('status', 'active')
            ->when($companyId && Schema::hasColumn('salary_structures', 'company_id'), fn ($q) => $q->where('company_id', $companyId))
            ->count(), 0);
        $assignmentsCount = $this->dashboardSection('salary assignment count', fn () => EmployeeSalaryAssignment::where('status', 'active')
            ->when($companyId && Schema::hasColumn('employee_salary_assignments', 'company_id'), fn ($q) => $q->where('company_id', $companyId))
            ->count(), 0);
        $payslipCount = $this->dashboardSection('payslip count', fn () => $this->payslipQuery($companyId)->count(), 0);
        $latestPayroll = $currentPayroll;

        return view('admin.payroll.index', compact(
            'kpis',
            'statusBreakdown',
            'recentPayrolls',
            'structuresCount',
            'assignmentsCount',
            'payslipCount',
            'totalPayrollRuns',
            'latestPayroll'
        ));
    }

    /**
     * Working days of a payroll month from the company's Work Schedule: calendar days minus weekly
     * offs, company holidays and special holidays (WorkScheduleService::getHolidaysAndOffDays).
     */
    private function monthWorkingDays(int $year, int $month, ?int $companyId): int
    {
        return app(PayrollCalculationService::class)->workingDaysInMonth($year, $month, $companyId);
    }

    /**
     * Runs one dashboard data section; a failure is logged with its cause and the section falls back
     * to an empty value instead of turning the whole Payroll page into a 500 error.
     */
    private function dashboardSection(string $section, callable $callback, $default)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Payroll dashboard section failed: ' . $section, [
                'company_id' => auth()->user()?->company_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);
            return $default;
        }
    }

    /**
     * 2. Salary Structures
     */
    public function salaryStructures(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = SalaryStructure::with(['designation', 'assignments'])
            ->when($companyId, function ($q) use ($companyId) {
                if (Schema::hasColumn('salary_structures', 'company_id')) {
                    $q->where('company_id', $companyId);
                }
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('grade', 'LIKE', "%{$search}%")
                  ->orWhereHas('designation', fn ($d) => $d->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('designation_id') && $request->input('designation_id') !== 'all') {
            $query->where('designation_id', $request->input('designation_id'));
        }

        if ($request->filled('grade') && $request->input('grade') !== 'all') {
            $level = (string) $request->input('grade');
            if (preg_match('/^L([0-9]+)$/', $level, $matches)) {
                $query->whereHas('designation', fn ($designation) => $designation->where('level', (int) $matches[1]));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $structures = $query->orderBy('id', 'desc')->paginate(15);
        $designations = app(\App\Services\PayrollDesignations::class)->available();
        $grades = $designations->pluck('level')->unique()->sort()->map(fn ($level) => 'L'.$level)->values();

        return view('admin.payroll.salary-structures', compact('structures', 'designations', 'grades'));
    }

    public function storeSalaryStructure(Request $request)
    {
        $this->authorizePayroll('payroll', 'create');

        $data = $request->validate([
            'designation_id' => ['required', 'exists:designations,id'],
            'grade' => ['required', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $designation = app(\App\Services\PayrollDesignations::class)->resolve((int) $data['designation_id'], $data['grade']);
        $data['grade'] = 'L'.$designation->level;
        $name = $designation->name . ' - ' . $data['grade'];

        $payload = array_merge($data, [
            'name' => $name,
            'code' => Str::slug($name) . '-' . Str::upper(Str::random(4)),
            'created_by' => auth()->id(),
            'version' => 1,
            'is_active' => ($data['status'] === 'active'),
        ]);

        if (Schema::hasColumn('salary_structures', 'company_id')) {
            $payload['company_id'] = auth()->user()?->company_id;
        }

        $structure = SalaryStructure::create($payload);

        $this->audit('created_salary_structure', $structure, null, $structure->toArray(), $request);

        return back()->with('success', 'Salary Structure created successfully.');
    }

    public function updateSalaryStructure(Request $request, SalaryStructure $structure)
    {
        $this->authorizePayroll('payroll', 'edit');

        $data = $request->validate([
            'designation_id' => ['required', 'exists:designations,id'],
            'grade' => ['required', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $designation = app(\App\Services\PayrollDesignations::class)->resolve((int) $data['designation_id'], $data['grade']);
        $data['grade'] = 'L'.$designation->level;
        $name = $designation->name . ' - ' . $data['grade'];

        $old = $structure->toArray();
        $structure->update(array_merge($data, [
            'name' => $name,
            'is_active' => ($data['status'] === 'active'),
        ]));

        $this->audit('updated_salary_structure', $structure, $old, $structure->fresh()->toArray(), $request);

        return back()->with('success', 'Salary Structure updated successfully.');
    }

    public function destroySalaryStructure(Request $request, SalaryStructure $structure)
    {
        $this->authorizePayroll('payroll', 'delete');

        $old = $structure->toArray();
        $structure->delete();

        $this->audit('deleted_salary_structure', $structure, $old, null, $request);

        return back()->with('success', 'Salary Structure deleted successfully.');
    }

    /**
     * 3. Employee Salary Assignment
     */
    public function employeeSalary(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = EmployeeSalaryAssignment::with([
                'user.employeeDetail.department',
                'user.employeeDetail.designation',
                'designation',
                'salaryStructure',
            ])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'LIKE', "%{$search}%")
                   ->orWhere('email', 'LIKE', "%{$search}%")
                   ->orWhereHas('employeeDetail', fn ($ed) => $ed->where('employee_id', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('department_id') && $request->input('department_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('department_id', $request->input('department_id')));
        }

        if ($request->filled('designation_id') && $request->input('designation_id') !== 'all') {
            $query->where('designation_id', $request->input('designation_id'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $assignments = $query->orderBy('id', 'desc')->paginate(15);

        // Every staff member from the Employee section (employees, HR, managers, company roles) with their salary status.
        $staff = $this->payrollStaffQuery($companyId)
            ->with(['employeeDetail.designation', 'employeeDetail.department'])
            ->orderBy('name')
            ->get();
        $currentAssignments = EmployeeSalaryAssignment::whereIn('user_id', $staff->pluck('id'))
            ->where('status', 'active')
            ->orderByDesc('effective_from')->orderByDesc('id')
            ->get()->unique('user_id')->keyBy('user_id');
        $roster = $staff->filter(function (User $member) use ($request, $currentAssignments) {
            $detail = $member->employeeDetail;
            if ($request->filled('search')) {
                $needle = mb_strtolower($request->input('search'));
                $haystack = mb_strtolower($member->name . ' ' . $member->email . ' ' . ($detail?->employee_id ?? ''));
                if (! str_contains($haystack, $needle)) return false;
            }
            if ($request->filled('department_id') && $request->input('department_id') !== 'all' && (string) $detail?->department_id !== (string) $request->input('department_id')) return false;
            if ($request->filled('designation_id') && $request->input('designation_id') !== 'all' && (string) $detail?->designation_id !== (string) $request->input('designation_id')) return false;
            $salary = $request->input('salary', 'all');
            if ($salary === 'assigned' && ! $currentAssignments->has($member->id)) return false;
            if ($salary === 'unassigned' && $currentAssignments->has($member->id)) return false;
            return true;
        })->values();
        $rosterSummary = [
            'total' => $staff->count(),
            'assigned' => $staff->filter(fn ($m) => $currentAssignments->has($m->id))->count(),
        ];
        $rosterSummary['unassigned'] = $rosterSummary['total'] - $rosterSummary['assigned'];
        // HR manages everyone's salary except their own, which only the company Admin sets.
        $lockedUserId = $this->ownSalaryLocked(auth()->id()) ? (int) auth()->id() : null;
        $employees = $staff->reject(fn ($member) => $lockedUserId && (int) $member->id === $lockedUserId)->values();

        $designations = Designation::orderBy('name')->get();
        $departments = $this->getDepartments();
        $structures = SalaryStructure::where('status', 'active')->orderBy('name')->get();

        return view('admin.payroll.employee-salary', compact(
            'assignments',
            'employees',
            'roster',
            'rosterSummary',
            'currentAssignments',
            'lockedUserId',
            'designations',
            'departments',
            'structures'
        ));
    }

    public function storeEmployeeSalary(Request $request)
    {
        $this->authorizePayroll('payroll', 'create');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'grade' => ['nullable', 'string', 'max:50'],
            'salary_structure_id' => ['nullable', 'exists:salary_structures,id'],
            'actual_basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->ownSalaryLocked((int) $data['user_id'])) {
            return back()->with('error', 'Your own salary is set by the company Admin. You can manage salaries of every other employee.');
        }

        $user = User::with('employeeDetail')->find($data['user_id']);
        $designationId = ($data['designation_id'] ?? null) ?: ($user?->employeeDetail?->designation_id);
        $grade = ($data['grade'] ?? null) ?:($user?->employeeDetail?->designation?->level ?: 'Standard');

        $payload = array_merge($data, [
            'company_id' => $user?->company_id ?: auth()->user()?->company_id,
            'designation_id' => $designationId,
            'grade' => $grade,
        ]);

        // If newly active, inactivate older overlapping assignment for the same employee
        if ($data['status'] === 'active') {
            EmployeeSalaryAssignment::where('user_id', $data['user_id'])
                ->where('status', 'active')
                ->update(['effective_to' => Carbon::parse($data['effective_from'])->subDay()->format('Y-m-d')]);
        }

        $assignment = EmployeeSalaryAssignment::create($payload);

        $this->audit('created_employee_salary_assignment', $assignment, null, $assignment->toArray(), $request);

        return back()->with('success', 'Employee salary assigned successfully.');
    }

    public function updateEmployeeSalary(Request $request, EmployeeSalaryAssignment $assignment)
    {
        $this->authorizePayroll('payroll', 'edit');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'grade' => ['nullable', 'string', 'max:50'],
            'salary_structure_id' => ['nullable', 'exists:salary_structures,id'],
            'actual_basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authorizeAssignmentCompany($request, $assignment);
        if ($this->ownSalaryLocked((int) $assignment->user_id) || $this->ownSalaryLocked((int) $data['user_id'])) {
            return back()->with('error', 'Your own salary is set by the company Admin. You can manage salaries of every other employee.');
        }

        $old = $assignment->toArray();
        $assignment->update($data);

        $this->audit('updated_employee_salary_assignment', $assignment, $old, $assignment->fresh()->toArray(), $request);

        return back()->with('success', 'Employee salary assignment updated successfully.');
    }

    public function destroyEmployeeSalary(Request $request, EmployeeSalaryAssignment $assignment)
    {
        $this->authorizePayroll('payroll', 'delete');
        $this->authorizeAssignmentCompany($request, $assignment);
        if ($this->ownSalaryLocked((int) $assignment->user_id)) {
            return back()->with('error', 'Your own salary is set by the company Admin. You can manage salaries of every other employee.');
        }

        $old = $assignment->toArray();
        $assignment->delete();

        $this->audit('deleted_employee_salary_assignment', $assignment, $old, null, $request);

        return back()->with('success', 'Employee salary assignment removed successfully.');
    }

    /**
     * AJAX endpoint: Return structure defaults for designation or structure selection
     */
    public function structureDefaults(Request $request)
    {
        $this->authorizePayroll('payroll');

        if ($request->filled('structure_id')) {
            $structure = SalaryStructure::find($request->input('structure_id'));
            if ($structure) {
                return response()->json([
                    'success' => true,
                    'designation_id' => $structure->designation_id,
                    'grade' => $structure->grade,
                    'basic_salary' => (float) $structure->basic_salary,
                    'hra_type' => $structure->hra_type ?: 'percentage',
                    'hra_value' => (float) $structure->hra_value,
                    'special_allowance_type' => $structure->special_allowance_type ?: 'percentage',
                    'special_allowance_value' => (float) $structure->special_allowance_value,
                ]);
            }
        }

        if ($request->filled('designation_id')) {
            $structure = SalaryStructure::where('designation_id', $request->input('designation_id'))
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($structure) {
                return response()->json([
                    'success' => true,
                    'structure_id' => $structure->id,
                    'grade' => $structure->grade,
                    'basic_salary' => (float) $structure->basic_salary,
                    'hra_type' => $structure->hra_type ?: 'percentage',
                    'hra_value' => (float) $structure->hra_value,
                    'special_allowance_type' => $structure->special_allowance_type ?: 'percentage',
                    'special_allowance_value' => (float) $structure->special_allowance_value,
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'basic_salary' => 0.0,
            'hra_type' => 'percentage',
            'hra_value' => 0.0,
            'special_allowance_type' => 'percentage',
            'special_allowance_value' => 0.0,
        ]);
    }

    /**
     * 4. Payroll Processing
     */
    public function processing(Request $request, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $year = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));
        // Default to the company's real working days for the month; the page still lets HR/admin override it.
        $workingDays = (int) ($request->input('working_days') ?: $this->monthWorkingDays($year, $month, $companyId));
        $office = $request->input('office', 'all');
        $departmentId = $request->input('department_id') ? (int) $request->input('department_id') : null;
        $employeeType = $request->input('employee_type', 'all');

        $addressQuery = BusinessAddress::query();
        $addrConn = $addressQuery->getModel()->getConnectionName() ?: config('database.default');
        if ($companyId && Schema::connection($addrConn)->hasColumn('business_addresses', 'company_id')) {
            $addressQuery->where('company_id', $companyId);
        }
        $offices = $this->dashboardSection('processing office list', fn () => $addressQuery->pluck('branch_name')
            ->filter()
            ->unique()
            ->toArray(), []);

        $empDetailQuery = EmployeeDetail::query();
        $empConn = $empDetailQuery->getModel()->getConnectionName() ?: config('database.default');
        if ($companyId && Schema::connection($empConn)->hasColumn('employee_details', 'company_id')) {
            $empDetailQuery->where('company_id', $companyId);
        }
        $dbEmpAddresses = $this->dashboardSection('processing employee offices', fn () => $empDetailQuery->pluck('business_address')
            ->filter()
            ->unique()
            ->toArray(), []);

        $officesList = array_values(array_filter(array_unique(array_merge($offices, $dbEmpAddresses))));
        $departments = $this->dashboardSection('processing departments', fn () => $this->getDepartments(), collect());

        // Check if existing payroll exists for period
        $existingPayroll = $this->dashboardSection('processing existing payroll',
            fn () => $payrollService->checkExistingPayroll($companyId, $year, $month, $office, $employeeType), null);

        $payrollRun = null;
        $payrollItems = collect();
        $calculationFailures = [];
        $staleEmployees = [];

        if ($existingPayroll) {
            $payrollRun = $existingPayroll;
            // Saved rows must obey the same employee filters as an unsaved preview.
            $eligibleEmployeeIds = $this->dashboardSection('processing eligible employees', fn () => $payrollService
                ->getEligibleEmployees($companyId, $office, $employeeType, $departmentId, $year, $month)
                ->pluck('id'), collect());
            $payrollItems = PayrollHistory::where('payroll_id', $existingPayroll->id)
                ->whereIn('user_id', $eligibleEmployeeIds)
                ->orderBy('id', 'asc')->get();
            // Lines saved for employees who no longer have a salary assignment: the run must be recalculated.
            if (in_array($existingPayroll->status, ['draft', 'calculated', 'reviewed'], true) && ($office ?: 'all') === 'all' && ! $departmentId) {
                $staleIds = PayrollHistory::where('payroll_id', $existingPayroll->id)
                    ->whereNotIn('user_id', $eligibleEmployeeIds)
                    ->pluck('user_id');
                $staleEmployees = $staleIds->isEmpty() ? [] : User::whereIn('id', $staleIds)
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->pluck('name')->all();
            }
        } else {
            // Auto calculate preview lines in memory
            $employees = $this->dashboardSection('processing eligible employees',
                fn () => $payrollService->getEligibleEmployees($companyId, $office, $employeeType, $departmentId, $year, $month), collect());
            $srNo = 1;
            foreach ($employees as $employee) {
                // An employee whose salary/attendance data cannot be calculated is listed in a warning
                // (and logged) instead of turning the whole Processing page into a 500 error.
                $line = $this->dashboardSection('payroll processing for user #' . $employee->id,
                    fn () => $payrollService->calculateEmployeePayrollLine($employee, $year, $month, $workingDays, $srNo), null);
                if (! is_array($line)) {
                    $calculationFailures[] = $employee->name ?: ('Employee #' . $employee->id);
                    continue;
                }
                $srNo++;
                $payrollItems->push((object)[
                    'id' => null,
                    'user_id' => $employee->id,
                    'snapshot' => $line,
                    'gross_salary' => $line['gross_salary'],
                    'net_salary' => $line['net_pay'],
                    'total_in_hand' => $line['total_in_hand'],
                    'ctc' => $line['ctc'],
                    'payroll_status' => 'Calculated',
                ]);
            }
        }

        // Summary calculations
        $totalEmployees = $payrollItems->count();
        $totalGross = 0.0;
        $totalDeductions = 0.0;
        $totalNetPay = 0.0;
        $totalInHand = 0.0;
        $totalEmployer = 0.0;
        $totalCtc = 0.0;
        $totalAbsent = 0.0;
        $totalPresent = 0.0;
        $totalLeave = 0.0;

        foreach ($payrollItems as $item) {
            $s = is_array($item->snapshot) ? $item->snapshot : (json_decode($item->snapshot ?? '{}', true) ?: []);
            $totalGross += (float) ($s['gross_salary'] ?? $s['gross'] ?? 0);
            $totalDeductions += (float) ($s['total_deductions'] ?? 0);
            $totalNetPay += (float) ($s['net_pay'] ?? $s['net_salary'] ?? 0);
            $totalInHand += (float) ($s['total_in_hand'] ?? 0);
            $totalEmployer += (float) ($s['total_employer_contribution'] ?? 0);
            $totalCtc += (float) ($s['ctc'] ?? 0);
            $totalAbsent += (float) ($s['total_absent'] ?? 0);
            $totalPresent += (float) ($s['presents'] ?? 0);
            $totalLeave += (float) ($s['total_leave'] ?? 0);
        }

        $summary = [
            'total_employees' => $totalEmployees,
            'total_gross' => round($totalGross, 2),
            'total_deductions' => round($totalDeductions, 2),
            'total_net_pay' => round($totalNetPay, 2),
            'total_in_hand' => round($totalInHand, 2),
            'total_employer_contribution' => round($totalEmployer, 2),
            'total_ctc' => round($totalCtc, 2),
            'total_absent' => round($totalAbsent, 2),
            'total_present' => round($totalPresent, 2),
            'total_leave' => round($totalLeave, 2),
            'status' => $payrollRun ? $payrollRun->status : 'Draft',
        ];

        // Rank labels show the amounts payroll actually pays (Formulas & Rules may override 400 / 200).
        $bestEmployeeBonusRates = $this->dashboardSection('best employee bonus rates', fn () => $payrollService->bestEmployeeBonusRates($year, $month), [1 => 400.0, 2 => 200.0]);

        return view('admin.payroll.processing', compact(
            'year',
            'month',
            'workingDays',
            'bestEmployeeBonusRates',
            'office',
            'departmentId',
            'departments',
            'officesList',
            'existingPayroll',
            'payrollRun',
            'payrollItems',
            'summary',
            'calculationFailures',
            'staleEmployees'
        ));
    }

    public function calculate(Request $request, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'create');

        $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2035'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'working_days' => ['required', 'integer', 'min:1', 'max:31'],
            'office' => ['nullable', 'string'],
            'department_id' => ['nullable', 'integer'],
            'employee_type' => ['nullable', 'string'],
        ]);

        $companyId = $this->selectedCompanyId($request);
        $year = $request->integer('year');
        $month = $request->integer('month');
        $workingDays = $request->integer('working_days');
        $office = $request->input('office', 'all');
        $departmentId = $request->filled('department_id') ? $request->integer('department_id') : null;
        $employeeType = $request->input('employee_type', 'all');

        try {
            $payroll = $payrollService->processPayrollRun($companyId, $year, $month, $workingDays, $office, $employeeType, auth()->id(), $departmentId);

            return redirect()->route('payroll.processing', [
                'year' => $year,
                'month' => $month,
                'working_days' => $workingDays,
                'office' => $office,
                'department_id' => $departmentId,
            ])->with('success', 'Payroll calculated successfully for ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Preview single employee line item
     */
    public function preview(PayrollHistory $history)
    {
        $this->authorizePayroll('payroll');
        $history->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll']);

        $snap = $history->snapshot;

        return view('admin.payroll.preview', compact('history', 'snap'));
    }

    /**
     * Update line item inputs (TA, commission, best employee bonus, adjustments)
     */
    public function updateLineInput(Request $request, PayrollHistory $history, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'edit');

        $inputs = $request->validate([
            'ta' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'best_employee_bonus' => ['nullable', 'numeric', 'min:0'],
            'be_rank' => ['nullable', 'integer', 'in:0,1,2'],
            'attendance_bonus' => ['nullable', 'numeric', 'min:0'],
            'positive_adjustment' => ['nullable', 'numeric', 'min:0'],
            'negative_adjustment' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'other_allowances' => ['nullable', 'numeric', 'min:0'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->ownSalaryLocked((int) $history->user_id)) {
            return back()->with('error', 'Your own payroll line is managed by the company Admin.');
        }

        try {
            $payrollService->updatePayrollHistoryLine($history, $inputs);
            return back()->with('success', 'Employee payroll line updated and recalculated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markReviewed(Payroll $payroll, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'edit');

        try {
            $payrollService->reviewPayroll($payroll, auth()->id());
            return back()->with('success', 'Payroll marked as Reviewed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Payroll $payroll, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'approve');

        try {
            $payrollService->approvePayroll($payroll, auth()->id());
            return back()->with('success', 'Payroll Approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function finalize(Payroll $payroll, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'approve');

        try {
            $payrollService->finalizePayroll($payroll, auth()->id());
            return back()->with('success', 'Payroll run #' . $payroll->id . ' has been Finalized successfully. Complete snapshot preserved.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recalculate(Request $request, Payroll $payroll, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'create');

        $meta = $payroll->metadata ?? [];
        $year = (int) ($meta['year'] ?? date('Y', strtotime($payroll->period_start)));
        $month = (int) ($meta['month'] ?? date('n', strtotime($payroll->period_start)));
        $workingDays = (int) ($meta['working_days'] ?? ($request->integer('working_days') ?: $this->monthWorkingDays($year, $month, $payroll->company_id)));
        $office = $meta['office'] ?? 'all';
        $employeeType = $meta['employee_type'] ?? 'all';

        try {
            if ($payroll->status === 'finalized') {
                $payroll->update(['status' => 'calculated']);
            }
            $payrollService->processPayrollRun($payroll->company_id, $year, $month, $workingDays, $office, $employeeType, auth()->id());
            return back()->with('success', 'Payroll recalculated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 5. Payroll History
     */
    public function history(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = PayrollHistory::with([
                'user.employeeDetail.designation',
                'user.employeeDetail.department',
                'payroll',
            ])
            ->when($companyId, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('company_id', $companyId)));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'LIKE', "%{$search}%")
                   ->orWhere('email', 'LIKE', "%{$search}%")
                   ->orWhereHas('employeeDetail', fn ($ed) => $ed->where('employee_id', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('month') && $request->input('month') !== 'all') {
            $m = (int) $request->input('month');
            $query->whereMonth('period_start', $m);
        }

        if ($request->filled('year') && $request->input('year') !== 'all') {
            $y = (int) $request->input('year');
            $query->whereYear('period_start', $y);
        }

        if ($request->filled('department_id') && $request->input('department_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('department_id', $request->input('department_id')));
        }

        if ($request->filled('designation_id') && $request->input('designation_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('designation_id', $request->input('designation_id')));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('payroll_status', $request->input('status'));
        }

        $histories = $query->orderBy('period_start', 'desc')->orderBy('id', 'desc')->paginate(20);
        $departments = $this->getDepartments();
        $designations = Designation::orderBy('name')->get();

        return view('admin.payroll.history', compact('histories', 'departments', 'designations'));
    }

    /**
     * 6. Payslips
     */
    public function payslips(Request $request)
    {
        $this->authorizePayroll('payslips');
        $companyId = $this->selectedCompanyId($request);

        // Approved/finalized runs whose payslips were never generated (or failed earlier) get them now.
        $this->dashboardSection('generate missing payslips', function () use ($companyId) {
            $service = app(PayrollCalculationService::class);
            $this->companyQuery(Payroll::query(), $companyId)
                ->whereIn('status', ['approved', 'finalized'])
                ->get()
                ->each(function (Payroll $payroll) use ($service) {
                    $missing = PayrollHistory::where('payroll_id', $payroll->id)->whereNotExists(function ($query) {
                        $query->selectRaw('1')->from('payslips')
                            ->whereColumn('payslips.payroll_id', 'payroll_histories.payroll_id')
                            ->whereColumn('payslips.user_id', 'payroll_histories.user_id');
                    })->exists();
                    if ($missing) {
                        $service->generatePayslips($payroll, auth()->id(), true);
                    }
                });
        }, null);

        $query = Payslip::with([
                'user.employeeDetail.designation',
                'user.employeeDetail.department',
                'payroll',
            ])->whereHas('payroll', fn ($run) => $run->whereIn('status', ['approved', 'finalized'])
                ->when($companyId, fn ($own) => $own->where('company_id', $companyId)))
            ->when($companyId, function ($q) use ($companyId) {
                if (Schema::hasColumn('payslips', 'company_id')) {
                    // Older payslips may have no company recorded: match them through the employee.
                    $q->where(fn ($w) => $w->where('company_id', $companyId)
                        ->orWhere(fn ($n) => $n->whereNull('company_id')->whereHas('user', fn ($u) => $u->where('company_id', $companyId))));
                } else {
                    $q->whereHas('user', fn ($u) => $u->where('company_id', $companyId));
                }
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payslip_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'LIKE', "%{$search}%")
                      ->orWhereHas('employeeDetail', fn ($ed) => $ed->where('employee_id', 'LIKE', "%{$search}%")));
            });
        }

        if ($request->filled('month') && $request->input('month') !== 'all') {
            $m = (int) $request->input('month');
            $query->whereHas('payroll', fn ($p) => $p->whereMonth('period_start', $m));
        }

        if ($request->filled('year') && $request->input('year') !== 'all') {
            $y = (int) $request->input('year');
            $query->whereHas('payroll', fn ($p) => $p->whereYear('period_start', $y));
        }

        if ($request->filled('department_id') && $request->input('department_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('department_id', $request->input('department_id')));
        }

        // HR sees their own payslip under My Payslips, not in the payroll workspace.
        if ($this->ownSalaryLocked(auth()->id())) {
            $query->where('user_id', '!=', auth()->id());
        }

        $payslips = $query->latest()->paginate(20);
        $departments = $this->getDepartments();

        return view('admin.payroll.payslips', compact('payslips', 'departments'));
    }

    /**
     * 7. Formulas & Calculation Rules
     */
    protected function resolvePayslipModel($payslip): Payslip
    {
        if ($payslip instanceof Payslip) {
            return $payslip;
        }

        $found = Payslip::find($payslip);
        if ($found) {
            return $found;
        }

        // Fallback: check by payroll_history_id or PayrollHistory ID
        $history = PayrollHistory::find($payslip) ?? PayrollHistory::where('id', $payslip)->first();
        if ($history) {
            $existing = Payslip::where('payroll_history_id', $history->id)->first();
            if ($existing) {
                return $existing;
            }
            $payroll = $history->payroll ?: Payroll::find($history->payroll_id);
            if ($payroll) {
                app(PayrollCalculationService::class)->generatePayslips($payroll, auth()->id());
                $newPayslip = Payslip::where('payroll_history_id', $history->id)->first();
                if ($newPayslip) {
                    return $newPayslip;
                }
            }
        }

        abort(404, 'Payslip not found.');
    }

    /**
     * Company whose name, logo and address brand the payslip.
     */
    protected function payslipCompany(Payslip $payslip)
    {
        $companyId = $payslip->company_id ?: ($payslip->payroll?->company_id ?: ($payslip->user?->company_id ?: auth()->user()?->company_id));

        return ($companyId ? Company::find($companyId) : null) ?: \App\Models\CompanySetting::first();
    }

    public function viewPayslip($payslip)
    {
        $this->authorizePayroll('payslips');
        $payslip = $this->resolvePayslipModel($payslip);
        if ($this->ownSalaryLocked((int) $payslip->user_id)) {
            return redirect()->route('my-payslips.show', $payslip->id);
        }
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = $this->payslipCompany($payslip);
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return view('admin.payroll.payslip-view', compact('payslip', 'company', 'snap', 'history'));
    }

    public function printPayslip($payslip)
    {
        // ?noprint=1 is the read-only document embedded in the payslip view page.
        request()->boolean('noprint') ? $this->authorizePayroll('payslips') : $this->authorizePayroll('payslips', 'export');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = $this->payslipCompany($payslip);
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return view('admin.payroll.payslip-print', compact('payslip', 'company', 'snap', 'history'));
    }

    public function downloadPdf($payslip)
    {
        $this->authorizePayroll('payslips', 'export');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = $this->payslipCompany($payslip);
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        $mode = 'pdf';
        $pdf = Pdf::loadView('admin.payroll.payslip-pdf', compact('payslip', 'company', 'snap', 'history', 'mode'))
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true);
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '_', 'Payslip_' . ($payslip->user?->name ?? 'Employee') . '_' . $payslip->payslip_number);
        return $pdf->download($name . '.pdf');
    }

    public function sendPayslipSingle(Request $request, Payslip $payslip, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payslips', 'create');

        $sent = $payrollService->sendPayslip($payslip);
        if ($sent) {
            return back()->with('success', 'Payslip sent to ' . ($payslip->user?->email ?: 'employee') . ' successfully.');
        }
        return back()->with('error', 'Failed to send payslip. Please check email configuration.');
    }

    public function export(Request $request, Payroll $payroll)
    {
        $this->authorizePayroll('payroll', 'export');

        $items = PayrollHistory::where('payroll_id', $payroll->id)->get();
        $fileName = 'Payroll_' . date('Y_m', strtotime($payroll->period_start)) . '_Export.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Lines saved without their own value use the run's working days, never an assumed number.
        $runWorkingDays = $payroll->metadata['working_days'] ?? '-';
        $callback = function () use ($items, $runWorkingDays) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Sr No', 'Emp ID', 'Employee Name', 'Designation', 'Grade',
                'Working Days', 'Present', 'Paid Leave', 'Unpaid Leave', 'Absent', 'Half Day', 'WFH', 'Working Hours',
                'Basic', 'Current Basic', 'HRA', 'Special Allowance', 'Gross',
                'PF', 'ESI', 'Total Deductions', 'Net Pay',
                'Attendance Bonus', 'Best Emp Bonus', 'TA', 'Overtime', 'Commission', 'Adjustments', 'Total In Hand',
                'Employer PF', 'Employer ESI', 'EDLI', 'CTC', 'Status'
            ]);

            foreach ($items as $item) {
                $s = is_array($item->snapshot) ? $item->snapshot : (json_decode($item->snapshot ?? '{}', true) ?: []);
                fputcsv($file, [
                    $s['sr_no'] ?? '-',
                    $s['employee_id'] ?? '-',
                    $s['employee_name'] ?? '-',
                    $s['designation'] ?? '-',
                    $s['grade'] ?? '-',
                    $s['working_days'] ?? $runWorkingDays,
                    $s['present'] ?? 0,
                    $s['paid_leave'] ?? 0,
                    $s['unpaid_leave'] ?? 0,
                    $s['absent'] ?? 0,
                    $s['half_day'] ?? 0,
                    $s['wfh'] ?? 0,
                    $s['working_hours'] ?? 0,
                    $s['basic'] ?? 0,
                    $s['current_basic'] ?? 0,
                    $s['current_hra'] ?? 0,
                    $s['current_special'] ?? 0,
                    $s['gross_salary'] ?? 0,
                    $s['pf'] ?? 0,
                    $s['esi'] ?? 0,
                    $s['total_deductions'] ?? 0,
                    $s['net_pay'] ?? 0,
                    $s['attendance_bonus'] ?? 0,
                    $s['best_employee_bonus'] ?? 0,
                    $s['ta'] ?? 0,
                    $s['overtime'] ?? 0,
                    $s['commission'] ?? 0,
                    $s['adjustment'] ?? 0,
                    $s['total_in_hand'] ?? 0,
                    $s['employer_pf'] ?? 0,
                    $s['employer_esi'] ?? 0,
                    $s['edli'] ?? 0,
                    $s['ctc'] ?? 0,
                    $item->payroll_status ?? 'Calculated',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Strict Authorization: Admin Workspace ONLY
     */
    /**
     * Staff whose salary is managed in Payroll: every active member of the Employee section
     * (employees, HR, managers and company roles), never the company Admin or platform accounts.
     */
    private function payrollStaffQuery(?int $companyId)
    {
        $schema = Schema::connection((new User)->getConnectionName() ?: config('database.default'));
        $query = User::query()
            ->where(fn ($q) => $q->whereNull('role')->orWhereNotIn('role', ['admin', 'administrator', 'superadmin', 'super_admin', 'client']))
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));
        if ($schema->hasColumn('users', 'archived_at')) {
            $query->whereNull('archived_at');
        }
        return $query;
    }

    /** HR (and any other non-admin payroll user) may not set or change their own salary. */
    private function ownSalaryLocked(?int $userId): bool
    {
        $actor = auth()->user();
        return $actor && $userId && (int) $actor->id === (int) $userId
            && ! in_array($actor->normalizedRole(), ['admin', 'superadmin'], true);
    }

    private function authorizeAssignmentCompany(Request $request, EmployeeSalaryAssignment $assignment): void
    {
        $companyId = $this->selectedCompanyId($request);
        abort_if($companyId && $assignment->company_id && (int) $assignment->company_id !== (int) $companyId, 403, 'This salary assignment belongs to another company.');
    }

    private function authorizePayroll(string $moduleSlug = 'payroll', string $permission = 'view'): void
    {
        $role = strtolower((string)(auth()->user()?->role ?? ''));
        if (!in_array($role, ['admin', 'superadmin', 'administrator', 'hr'], true)) {
            abort(403, 'Unauthorized. Payroll management is restricted to Admin and permitted HR accounts.');
        }

        $user = auth()->user();
        $moduleSlug = app(\App\Services\AdminWorkspaceModules::class)
            ->subdivisionForRoute((string) request()->route()?->getName()) ?? $moduleSlug;
        $allowed = $user?->hasModulePermission($moduleSlug, $permission);
        if (! $allowed) {
            abort(403, 'You do not have permission to access this payroll module.');
        }

        // Company databases not migrated after a deploy are missing payroll tables/columns (500 on every page).
        \App\Services\PayrollSchema::ensure();
    }

    private function payslipQuery(?int $companyId = null)
    {
        $query = Payslip::query();
        if ($companyId) {
            if (Schema::hasColumn('payslips', 'company_id')) {
                $query->where('company_id', $companyId);
            } else {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
            }
        }
        return $query;
    }

    private function payrollHistoryQuery(?int $companyId = null)
    {
        $query = PayrollHistory::query();
        if ($companyId) {
            $query->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
        }
        return $query;
    }

    private function companyQuery($query, ?int $companyId)
    {
        if (! $companyId) {
            return $query;
        }

        $model = $query->getModel();
        $conn = $model->getConnectionName() ?: config('database.default');
        if (Schema::connection($conn)->hasColumn($model->getTable(), 'company_id')) {
            $query->where($model->getTable() . '.company_id', $companyId);
        }

        return $query;
    }

    private function selectedCompanyId(Request $request): ?int
    {
        if (auth()->check() && auth()->user()->company_id) {
            return (int) auth()->user()->company_id;
        }
        return $request->integer('company_id') ?: null;
    }

    private function audit(string $action, object $model, ?array $oldValue, ?array $newValue, Request $request): void
    {
        PayrollAuditLog::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()?->role,
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->id ?? null,
            'ip_address' => $request->ip(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $request->input('reason'),
        ]);
    }

    private function getDepartments()
    {
        $col = Schema::hasColumn('departments', 'dpt_name') ? 'dpt_name' : (Schema::hasColumn('departments', 'name') ? 'name' : 'id');
        return Department::orderBy($col, 'asc')->get();
    }
}
