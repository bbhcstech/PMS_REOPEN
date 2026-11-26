<?php

namespace App\Http\Controllers\Concerns\Payroll;

use App\Models\BusinessAddress;
use App\Models\EmployeeDetail;
use App\Models\Payroll;
use App\Models\PayrollHistory;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait PayrollProcessing
{
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
}
