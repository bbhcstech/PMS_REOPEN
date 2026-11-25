<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\Payroll\EmployeeSalaryAssignments;
use App\Http\Controllers\Concerns\Payroll\PayrollHistoryAndExports;
use App\Http\Controllers\Concerns\Payroll\PayrollPayslips;
use App\Http\Controllers\Concerns\Payroll\PayrollProcessing;
use App\Http\Controllers\Concerns\Payroll\SalaryStructures;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeSalaryAssignment;
use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use App\Models\PayrollHistory;
use App\Models\SalaryStructure;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    use SalaryStructures;
    use EmployeeSalaryAssignments;
    use PayrollProcessing;
    use PayrollHistoryAndExports;
    use PayrollPayslips;

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
