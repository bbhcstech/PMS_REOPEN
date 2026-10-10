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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    /**
     * 1. Payroll Dashboard
     */
    public function index(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $selectedMonth = (int) $request->input('month', date('n'));
        $selectedYear = (int) $request->input('year', date('Y'));

        // Eligible employees count
        $totalEmployees = User::where(function ($q) {
                $q->where('role', '!=', 'superadmin')->orWhereNull('role');
            })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->count();

        // Get payroll run for the selected period
        $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->format('Y-m-d');

        $currentPayroll = $this->dashboardSection('current payroll run', fn () => Payroll::query()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('period_start', [$startDate, $endDate])
                  ->orWhereBetween('period_end', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('period_start', '<=', $startDate)
                          ->where('period_end', '>=', $endDate);
                  });
            })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->latest()
            ->first(), null);

        $processedCount = 0;
        $grossTotal = 0.0;
        $deductionTotal = 0.0;
        $netTotal = 0.0;
        $inHandTotal = 0.0;
        $employerTotal = 0.0;
        $ctcTotal = 0.0;

        $finalizedCount = 0;
        $approvedCount = 0;
        $reviewedCount = 0;
        $calculatedCount = 0;
        $draftCount = 0;

        if ($currentPayroll) {
            $this->dashboardSection('payroll run totals', function () use ($currentPayroll, &$processedCount, &$grossTotal, &$deductionTotal, &$netTotal, &$inHandTotal, &$employerTotal, &$ctcTotal, &$finalizedCount, &$approvedCount, &$reviewedCount, &$calculatedCount, &$draftCount) {
            $processedCount = PayrollHistory::where('payroll_id', $currentPayroll->id)->count();
            $grossTotal = (float) $currentPayroll->gross_total;
            $deductionTotal = (float) $currentPayroll->deduction_total;
            $netTotal = (float) $currentPayroll->net_total;
            $inHandTotal = (float) ($currentPayroll->total_in_hand ?? $currentPayroll->net_total);
            $employerTotal = (float) ($currentPayroll->total_employer_contribution ?? 0);
            $ctcTotal = (float) ($currentPayroll->total_ctc ?? ($currentPayroll->gross_total + $employerTotal));

            $finalizedCount = PayrollHistory::where('payroll_id', $currentPayroll->id)
                ->where('payroll_status', 'finalized')
                ->count();
            $approvedCount = PayrollHistory::where('payroll_id', $currentPayroll->id)
                ->where('payroll_status', 'approved')
                ->count();
            $reviewedCount = PayrollHistory::where('payroll_id', $currentPayroll->id)
                ->where('payroll_status', 'reviewed')
                ->count();
            $calculatedCount = PayrollHistory::where('payroll_id', $currentPayroll->id)
                ->where('payroll_status', 'calculated')
                ->count();
            $draftCount = ($currentPayroll->status === 'draft')
                ? $processedCount
                : max(0, $processedCount - ($finalizedCount + $approvedCount + $reviewedCount + $calculatedCount));
            }, null);
        } else {
            // Live real-time projection across eligible employees for the selected month/year
            $payrollService = app(PayrollCalculationService::class);
            $employees = $this->dashboardSection('eligible employees', fn () => $payrollService->getEligibleEmployees($companyId), collect());
            $workingDays = 22;
            $srNo = 1;
            foreach ($employees as $employee) {
                // One employee's incomplete salary/attendance data must not take the whole dashboard down.
                $line = $this->dashboardSection('payroll projection for user #' . $employee->id,
                    fn () => $payrollService->calculateEmployeePayrollLine($employee, $selectedYear, $selectedMonth, $workingDays, $srNo), []);
                $srNo++;
                $grossTotal += (float) ($line['gross_salary'] ?? 0);
                $deductionTotal += (float) ($line['total_deductions'] ?? 0);
                $netTotal += (float) ($line['net_pay'] ?? 0);
                $inHandTotal += (float) ($line['total_in_hand'] ?? 0);
                $employerTotal += (float) ($line['total_employer_contribution'] ?? 0);
                $ctcTotal += (float) ($line['ctc'] ?? 0);
            }
        }

        $pendingCount = max(0, $totalEmployees - $processedCount);

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
            $query->where('grade', $request->input('grade'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $structures = $query->orderBy('id', 'desc')->paginate(15);
        $designations = Designation::orderBy('name')->get();
        $grades = ['D1', 'D2', 'S1', 'S2', 'M1', 'M2', 'E1', 'Standard'];

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

        $designation = Designation::find($data['designation_id']);
        $name = ($designation?->name ?: 'Designation') . ' - ' . $data['grade'];

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

        $designation = Designation::find($data['designation_id']);
        $name = ($designation?->name ?: 'Designation') . ' - ' . $data['grade'];

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

        $employees = User::where(function ($q) {
                $q->where('role', '!=', 'superadmin')->orWhereNull('role');
            })
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->with(['employeeDetail.designation', 'employeeDetail.department'])
            ->orderBy('name')
            ->get();

        $designations = Designation::orderBy('name')->get();
        $departments = $this->getDepartments();
        $structures = SalaryStructure::where('status', 'active')->orderBy('name')->get();

        return view('admin.payroll.employee-salary', compact(
            'assignments',
            'employees',
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

        $user = User::with('employeeDetail')->find($data['user_id']);
        $designationId = $data['designation_id'] ?: ($user?->employeeDetail?->designation_id);
        $grade = $data['grade'] ?: ($user?->employeeDetail?->designation?->level ?: 'Standard');

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

        $old = $assignment->toArray();
        $assignment->update($data);

        $this->audit('updated_employee_salary_assignment', $assignment, $old, $assignment->fresh()->toArray(), $request);

        return back()->with('success', 'Employee salary assignment updated successfully.');
    }

    public function destroyEmployeeSalary(Request $request, EmployeeSalaryAssignment $assignment)
    {
        $this->authorizePayroll('payroll', 'delete');

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
        $workingDays = (int) $request->input('working_days', 22);
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

        if ($existingPayroll) {
            $payrollRun = $existingPayroll;
            // Saved rows must obey the same employee filters as an unsaved preview.
            $eligibleEmployeeIds = $this->dashboardSection('processing eligible employees', fn () => $payrollService
                ->getEligibleEmployees($companyId, $office, $employeeType, $departmentId)
                ->pluck('id'), collect());
            $payrollItems = PayrollHistory::where('payroll_id', $existingPayroll->id)
                ->whereIn('user_id', $eligibleEmployeeIds)
                ->orderBy('id', 'asc')->get();
        } else {
            // Auto calculate preview lines in memory
            $employees = $this->dashboardSection('processing eligible employees',
                fn () => $payrollService->getEligibleEmployees($companyId, $office, $employeeType, $departmentId), collect());
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

        return view('admin.payroll.processing', compact(
            'year',
            'month',
            'workingDays',
            'office',
            'departmentId',
            'departments',
            'officesList',
            'existingPayroll',
            'payrollRun',
            'payrollItems',
            'summary',
            'calculationFailures'
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
        $this->authorizePayroll('payroll', 'edit');

        try {
            $payrollService->approvePayroll($payroll, auth()->id());
            return back()->with('success', 'Payroll Approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function finalize(Payroll $payroll, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payroll', 'create');

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
        $workingDays = (int) ($meta['working_days'] ?? $request->integer('working_days', 22));
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

        $query = Payslip::with([
                'user.employeeDetail.designation',
                'user.employeeDetail.department',
                'payroll',
            ])
            ->when($companyId, function ($q) use ($companyId) {
                if (Schema::hasColumn('payslips', 'company_id')) {
                    $q->where('company_id', $companyId);
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

        $payslips = $query->latest()->paginate(20);
        $departments = $this->getDepartments();

        return view('admin.payroll.payslips', compact('payslips', 'departments'));
    }

    /**
     * 7. Formulas & Calculation Rules
     */
    public function formulas(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = PayrollFormula::query();

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('formula', 'LIKE', "%{$search}%");
            });
        }

        $formulas = $query->orderBy('category')->orderBy('id', 'asc')->get();

        $summary = [
            'total' => PayrollFormula::count(),
            'active' => PayrollFormula::where('is_active', true)->count(),
            'earnings' => PayrollFormula::where('category', 'earnings')->count(),
            'deductions' => PayrollFormula::where('category', 'deduction')->count(),
            'bonuses' => PayrollFormula::where('category', 'bonus')->count(),
            'statutory' => PayrollFormula::whereIn('category', ['custom', 'statutory'])->count(),
        ];

        $categories = [
            'earnings' => 'Earnings & Base',
            'deduction' => 'Deductions',
            'bonus' => 'Bonuses & Incentives',
            'custom' => 'Employer Contributions & CTC',
        ];

        $commonVariables = [
            'BASIC' => 'Monthly Base Salary (from structure or assignment)',
            'PAYABLE_DAYS' => 'Total days present + paid leaves + WFH',
            'WORKING_DAYS' => 'Total scheduled working days in month (e.g. 22)',
            'CURRENT_BASIC' => 'Prorated basic salary based on attendance',
            'HRA' => 'House Rent Allowance amount',
            'SPECIAL_ALLOWANCE' => 'Special Allowance amount',
            'OTHER_ALLOWANCES' => 'Sum of other earnings',
            'GROSS_SALARY' => 'Gross monthly earnings',
            'PF' => 'Employee Provident Fund deduction',
            'ESI' => 'Employee State Insurance deduction',
            'PT' => 'Professional Tax deduction',
            'TDS' => 'Tax Deducted at Source',
            'TOTAL_DEDUCTIONS' => 'Sum of all employee deductions',
            'NET_PAY' => 'Net salary before bonuses and incentives',
            'WORKING_HOURS' => 'Total logged working hours in month',
            'BE_RANK' => 'Best Employee performance rank (1 or 2)',
            'OVERTIME_HOURS' => 'Total approved overtime hours',
            'OVERTIME_RATE' => 'Hourly overtime rate multiplier',
            'TA' => 'Approved Travel Allowance',
            'COMMISSION' => 'Approved Commission amount',
            'ADJUSTMENTS' => 'Net positive/negative payroll adjustment',
            'TOTAL_ADDITIONAL_EARNINGS' => 'Sum of bonus, TA, OT, commission & adj',
            'EMPLOYER_PF' => 'Employer PF matching contribution (12%)',
            'EMPLOYER_ESI' => 'Employer ESI statutory contribution (3.25%)',
            'EDLI' => 'Employer EDLI statutory contribution (0.5%)',
            'TOTAL_EMPLOYER_CONTRIBUTION' => 'Sum of all employer statutory costs',
        ];

        return view('admin.payroll.formulas', compact('formulas', 'summary', 'categories', 'commonVariables'));
    }

    public function storeFormula(Request $request)
    {
        $this->authorizePayroll('payroll', 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:60', 'unique:payroll_formulas,code'],
            'category' => ['required', 'string', 'in:earnings,deduction,bonus,custom,statutory'],
            'formula' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable'],
        ]);

        $cleanFormula = rtrim(trim($data['formula']), ";, \t\n\r\0\x0B");
        preg_match_all('/\b[A-Z_]{2,}\b/', $cleanFormula, $varMatches);
        $variablesUsed = array_values(array_unique($varMatches[0] ?? []));

        $testInputs = $request->input('test_inputs', []);
        if (is_string($testInputs)) {
            $testInputs = json_decode($testInputs, true) ?: [];
        }

        $testResult = PayrollFormula::evaluate($cleanFormula, $testInputs);
        $isValid = ($testResult !== false);

        $formula = PayrollFormula::create([
            'name' => $data['name'],
            'code' => Str::upper(Str::slug($data['code'], '_')),
            'category' => $data['category'],
            'formula' => $cleanFormula,
            'variables_used' => $variablesUsed,
            'description' => $data['description'] ?? null,
            'effective_from' => $data['effective_from'] ?: date('Y-m-d'),
            'effective_to' => $data['effective_to'] ?: null,
            'version' => 1,
            'is_valid' => $isValid,
            'is_active' => $request->boolean('is_active', true),
            'test_inputs' => $testInputs,
            'test_result' => $isValid ? (float)$testResult : null,
            'last_validated_at' => now(),
            'company_id' => auth()->user()?->company_id,
            'created_by' => auth()->id(),
        ]);

        $this->audit('created_payroll_formula', $formula, null, $formula->toArray(), $request);

        return back()->with('success', "Payroll formula '{$formula->name}' created and made effective successfully.");
    }

    public function updateFormula(Request $request, PayrollFormula $formula)
    {
        $this->authorizePayroll('payroll', 'edit');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:earnings,deduction,bonus,custom,statutory'],
            'formula' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable'],
        ]);

        $old = $formula->toArray();
        $cleanFormula = rtrim(trim($data['formula']), ";, \t\n\r\0\x0B");
        preg_match_all('/\b[A-Z_]{2,}\b/', $cleanFormula, $varMatches);
        $variablesUsed = array_values(array_unique($varMatches[0] ?? []));

        $testInputs = $request->input('test_inputs', $formula->test_inputs ?: []);
        if (is_string($testInputs)) {
            $testInputs = json_decode($testInputs, true) ?: [];
        }

        $testResult = PayrollFormula::evaluate($cleanFormula, $testInputs);
        $isValid = ($testResult !== false);

        $formula->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'formula' => $cleanFormula,
            'variables_used' => $variablesUsed,
            'description' => $data['description'] ?? null,
            'effective_from' => $data['effective_from'] ?: date('Y-m-d'),
            'effective_to' => $data['effective_to'] ?: null,
            'version' => ($formula->version ?? 1) + 1,
            'is_valid' => $isValid,
            'is_active' => $request->boolean('is_active', true),
            'test_inputs' => $testInputs,
            'test_result' => $isValid ? (float)$testResult : null,
            'last_validated_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $this->audit('updated_payroll_formula', $formula, $old, $formula->fresh()->toArray(), $request);

        return back()->with('success', "Payroll formula '{$formula->name}' (v{$formula->version}) updated and made effective.");
    }

    public function toggleFormulaActive(Request $request, PayrollFormula $formula)
    {
        $this->authorizePayroll('payroll', 'edit');

        $formula->is_active = !$formula->is_active;
        if ($formula->is_active && empty($formula->effective_from)) {
            $formula->effective_from = date('Y-m-d');
        }
        $formula->save();

        $statusStr = $formula->is_active ? 'activated & made effective' : 'deactivated';
        return back()->with('success', "Payroll formula '{$formula->name}' has been {$statusStr}.");
    }

    public function testFormulaLive(Request $request)
    {
        $this->authorizePayroll('payroll');

        $formula = (string) $request->input('formula', '');
        $inputs = (array) $request->input('variables', []);

        if (trim($formula) === '') {
            return response()->json(['success' => false, 'error' => 'Formula expression cannot be empty.']);
        }

        $result = PayrollFormula::evaluate($formula, $inputs);

        if ($result === false) {
            return response()->json([
                'success' => false,
                'error' => 'Formula evaluation failed. Please check syntax and ensure only valid mathematical expressions are used.',
            ]);
        }

        return response()->json([
            'success' => true,
            'result' => round($result, 2),
            'formatted' => '₹' . number_format($result, 2),
        ]);
    }

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

    public function viewPayslip($payslip)
    {
        $this->authorizePayroll('payslips');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = Company::find($payslip->company_id ?: auth()->user()?->company_id) ?: \App\Models\CompanySetting::first();
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return view('admin.payroll.payslip-view', compact('payslip', 'company', 'snap', 'history'));
    }

    public function printPayslip($payslip)
    {
        $this->authorizePayroll('payslips');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = Company::find($payslip->company_id ?: auth()->user()?->company_id) ?: \App\Models\CompanySetting::first();
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return view('admin.payroll.payslip-print', compact('payslip', 'company', 'snap', 'history'));
    }

    public function downloadPdf($payslip)
    {
        $this->authorizePayroll('payslips');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = Company::find($payslip->company_id ?: auth()->user()?->company_id) ?: \App\Models\CompanySetting::first();
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        $pdf = Pdf::loadView('admin.payroll.payslip-pdf', compact('payslip', 'company', 'snap', 'history'));
        return $pdf->download('Payslip_' . $payslip->payslip_number . '.pdf');
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
        $this->authorizePayroll('payroll');

        $items = PayrollHistory::where('payroll_id', $payroll->id)->get();
        $fileName = 'Payroll_' . date('Y_m', strtotime($payroll->period_start)) . '_Export.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items) {
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
                    $s['working_days'] ?? 22,
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
    private function authorizePayroll(string $moduleSlug = 'payroll', string $permission = 'view'): void
    {
        $role = strtolower((string)(auth()->user()?->role ?? ''));
        if (!in_array($role, ['admin', 'superadmin', 'administrator', 'hr'], true)) {
            abort(403, 'Unauthorized. Payroll management is restricted to Admin and permitted HR accounts.');
        }

        if (! auth()->user()?->hasModulePermission($moduleSlug, $permission)) {
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
