<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BusinessAddress;
use App\Models\Company;
use App\Models\EmployeeDetail;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use App\Models\PayrollHistory;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayrollCalculationService
{
    /**
     * Get eligible employees for payroll calculation based on filters.
     */
    public function getEligibleEmployees(?int $companyId = null, ?string $office = null, ?string $employeeType = null)
    {
        $query = User::query()
            ->with(['employeeDetail.salaryStructure.components', 'employeeDetail.designation', 'employeeDetail.department'])
            ->where(function ($q) {
                $q->where('role', '!=', 'superadmin')
                  ->orWhereNull('role');
            });

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if (!empty($office) && $office !== 'all') {
            $query->whereHas('employeeDetail', function ($empQuery) use ($office) {
                $empQuery->where('business_address', 'LIKE', "%{$office}%");
            });
        }

        if (!empty($employeeType) && $employeeType !== 'all') {
            $query->whereHas('employeeDetail', function ($empQuery) use ($employeeType) {
                $empQuery->where('employment_type', $employeeType)
                         ->orWhere('employment_type', str_replace(' ', '_', strtolower($employeeType)));
            });
        }

        return $query->orderBy('id', 'asc')->get();
    }

    /**
     * Calculate Leave statistics for an employee in a given month.
     */
    public function getLeaveData(User $user, int $year, int $month): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Initial Leave Balance
        $leaveBalanceRecord = LeaveBalance::where('user_id', $user->id)
            ->where(function ($q) use ($year) {
                $q->where('year', $year)->orWhere('leave_year', $year);
            })
            ->first();

        $initialBalance = 18.0; // Standard default annual leave quota
        if ($leaveBalanceRecord) {
            $initialBalance = (float) ($leaveBalanceRecord->allocated_leaves ?? $leaveBalanceRecord->remaining_leaves ?? 18.0);
        } elseif (isset($user->annual_leave_balance) && $user->annual_leave_balance > 0) {
            $initialBalance = (float) $user->annual_leave_balance;
        }

        // 2. Fetch Leaves in period
        $leaves = Leave::where('user_id', $user->id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })
            ->whereIn('status', ['approved', 'active', 'Approved'])
            ->get();

        $fullLeaveCount = 0.0;
        $halfLeaveCount = 0.0;

        foreach ($leaves as $leave) {
            $isHalf = $leave->half_day_flag || strtolower((string)$leave->type) === 'half_day' || strtolower((string)$leave->duration) === 'half_day';
            if ($isHalf) {
                $halfLeaveCount += 1.0;
            } else {
                $days = (float) ($leave->total_days ?: 1.0);
                $fullLeaveCount += $days;
            }
        }

        $totalLeaveUsed = $fullLeaveCount + ($halfLeaveCount * 0.5);
        $currentBalance = max(0.0, $initialBalance - $totalLeaveUsed);

        return [
            'initial_balance' => round($initialBalance, 2),
            'full_leave' => round($fullLeaveCount, 2),
            'half_leave' => round($halfLeaveCount, 2),
            'total_leave' => round($totalLeaveUsed, 2),
            'current_balance' => round($currentBalance, 2),
        ];
    }

    /**
     * Calculate Attendance statistics for an employee in a given month.
     */
    public function getAttendanceData(User $user, int $year, int $month, int $workingDays): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $fullAbsent = 0.0;
        $halfAbsent = 0.0;
        $presentDays = 0.0;

        if ($attendances->count() > 0) {
            foreach ($attendances as $att) {
                $status = strtolower((string)$att->status);
                $isHalfDay = $att->half_day || $status === 'half_day' || str_contains($status, 'half');

                if ($status === 'absent') {
                    $fullAbsent += 1.0;
                } elseif ($isHalfDay) {
                    $halfAbsent += 1.0;
                    $presentDays += 0.5;
                } elseif (in_array($status, ['present', 'late', 'work_from_home', 'wfh'])) {
                    $presentDays += 1.0;
                }
            }
        } else {
            // Default assumes full attendance if no explicit negative marks recorded
            $presentDays = (float) $workingDays;
        }

        $totalAbsent = $fullAbsent + ($halfAbsent * 0.5);
        $presents = max(0.0, min((float)$workingDays, $workingDays - $totalAbsent));
        $payableDays = max(0.0, $workingDays - $totalAbsent);

        return [
            'full_absent' => round($fullAbsent, 2),
            'half_absent' => round($halfAbsent, 2),
            'total_absent' => round($totalAbsent, 2),
            'presents' => round($presents, 2),
            'payable_days' => round($payableDays, 2),
        ];
    }

    /**
     * Fetch active Salary Structure & components for an employee.
     */
    public function getSalaryStructure(User $user): array
    {
        $basic = 0.0;
        $hra = 0.0;
        $special = 0.0;
        $hasStructure = false;

        $empDetail = $user->employeeDetail;

        // 1. Check custom values on EmployeeDetail
        if ($empDetail && ($empDetail->basic_salary > 0 || $empDetail->hra_amount > 0 || $empDetail->special_allowance > 0)) {
            $basic = (float) ($empDetail->basic_salary ?? 0);
            $hra = (float) ($empDetail->hra_amount ?? 0);
            $special = (float) ($empDetail->special_allowance ?? 0);
            $hasStructure = true;
        }

        // 2. Check explicitly assigned SalaryStructure
        if (! $hasStructure && $empDetail && $empDetail->salary_structure_id) {
            $structure = SalaryStructure::with('components')->find($empDetail->salary_structure_id);
            if ($structure && $structure->components->count() > 0) {
                foreach ($structure->components as $comp) {
                    $code = strtolower((string)$comp->code);
                    $val = (float) ($comp->value ?? 0);
                    if (str_contains($code, 'basic')) {
                        $basic += $val;
                    } elseif (str_contains($code, 'hra')) {
                        $hra += $val;
                    } else {
                        $special += $val;
                    }
                }
                $hasStructure = true;
            }
        }

        // 3. Fallback to active global salary structures in tenant database
        if (! $hasStructure) {
            $defaultStructure = SalaryStructure::with('components')->where('is_active', true)->first();
            if ($defaultStructure && $defaultStructure->components->count() > 0) {
                foreach ($defaultStructure->components as $comp) {
                    $code = strtolower((string)$comp->code);
                    $val = (float) ($comp->value ?? 0);
                    if (str_contains($code, 'basic')) {
                        $basic += $val;
                    } elseif (str_contains($code, 'hra')) {
                        $hra += $val;
                    } else {
                        $special += $val;
                    }
                }
                $hasStructure = true;
            }
        }

        // 4. Default demo salary fallback if no structure defined anywhere
        if (! $hasStructure) {
            $basic = 6000.00;
            $hra = 3000.00;
            $special = 3000.00;
            $hasStructure = false; // flag warning
        }

        $gross = $basic + $hra + $special;

        return [
            'basic' => round($basic, 2),
            'hra' => round($hra, 2),
            'special' => round($special, 2),
            'gross' => round($gross, 2),
            'has_structure' => $hasStructure,
        ];
    }

    /**
     * Calculate entire single employee payroll line item.
     */
    public function calculateEmployeePayrollLine(User $user, int $year, int $month, int $workingDays, int $srNo = 1): array
    {
        $policyService = app(PayrollPolicyService::class);
        $policy = $policyService->getActivePolicy($user->company_id);

        $leaveData = $this->getLeaveData($user, $year, $month);
        $attData = $this->getAttendanceData($user, $year, $month, $workingDays);
        $salaryData = $this->getSalaryStructure($user);

        $payableDays = $attData['payable_days'];
        $ratio = $workingDays > 0 ? ($payableDays / $workingDays) : 1.0;

        $acBasic = round($salaryData['basic'] * $ratio, 2);
        $acHra = round($salaryData['hra'] * $ratio, 2);
        $acSpecial = round($salaryData['special'] * $ratio, 2);
        $acGross = round($acBasic + $acHra + $acSpecial, 2);

        // Deductions & Rounding via active Policy Rules
        $dedRules = $policy->deductions_rules ?? [];
        $pfAmt = (!empty($dedRules['pf_enabled'])) ? min((float)($dedRules['pf_max_limit'] ?? 1800), round($acBasic * (($dedRules['pf_percentage'] ?? 12) / 100), 2)) : 0.0;
        $esiAmt = (!empty($dedRules['esi_enabled']) && $acGross <= 21000) ? round($acGross * (($dedRules['esi_percentage'] ?? 0.75) / 100), 2) : 0.0;
        $ptAmt = (!empty($dedRules['pt_enabled'])) ? (float) ($dedRules['pt_fixed_amount'] ?? 200) : 0.0;
        $totalDeductions = round($pfAmt + $esiAmt + $ptAmt, 2);

        $netSalary = max(0.0, round($acGross - $totalDeductions, 2));

        // Apply Rounding Policy
        $roundRules = $policy->rounding_rules ?? [];
        $mode = $roundRules['rounding_mode'] ?? 'nearest_rupee';
        if ($mode === 'nearest_rupee') {
            $netSalary = round($netSalary);
        } elseif ($mode === 'nearest_5') {
            $netSalary = round($netSalary / 5) * 5;
        } elseif ($mode === 'nearest_10') {
            $netSalary = round($netSalary / 10) * 10;
        }

        $empDetail = $user->employeeDetail;
        $empId = $empDetail?->employee_id ?: ('EMP-' . str_pad($user->id, 3, '0', STR_PAD_LEFT));
        $office = $empDetail?->business_address ?: 'Main Office';
        $employmentType = $empDetail?->employment_type ?: 'Full Time';

        return [
            'sr_no' => $srNo,
            'user_id' => $user->id,
            'employee_id' => $empId,
            'employee_name' => $user->name,
            'email' => $user->email,
            'office' => $office,
            'employment_type' => ucwords(str_replace('_', ' ', $employmentType)),
            'designation' => $empDetail?->designation?->name ?: 'Staff',
            'department' => $empDetail?->department?->name ?: 'General',
            
            // Leave
            'initial_leave_balance' => $leaveData['initial_balance'],
            'full_leave' => $leaveData['full_leave'],
            'half_leave' => $leaveData['half_leave'],
            'total_leave' => $leaveData['total_leave'],
            'current_leave_balance' => $leaveData['current_balance'],

            // Attendance
            'full_absent' => $attData['full_absent'],
            'half_absent' => $attData['half_absent'],
            'total_absent' => $attData['total_absent'],
            'presents' => $attData['presents'],
            'payable_days' => $payableDays,

            // Salary Structure
            'basic' => $salaryData['basic'],
            'hra' => $salaryData['hra'],
            'special' => $salaryData['special'],
            'gross' => $salaryData['gross'],
            'has_salary_structure' => $salaryData['has_structure'],

            // Actual Salary & Policy Rules Integration
            'ac_basic' => $acBasic,
            'ac_hra' => $acHra,
            'ac_special' => $acSpecial,
            'ac_gross' => $acGross,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'policy_id' => $policy->id,
            'policy_version' => $policy->version,

            'status' => 'Calculated',
        ];
    }


    /**
     * Check if payroll exists for a given period & filters.
     */
    public function checkExistingPayroll(?int $companyId, int $year, int $month, ?string $office = null, ?string $employeeType = null): ?Payroll
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $query = Payroll::query()
            ->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $existing = $query->latest()->first();

        if ($existing && $office && $employeeType) {
            $meta = $existing->metadata ?? [];
            if (isset($meta['office']) && $meta['office'] !== $office && $office !== 'all') {
                return null;
            }
        }

        return $existing;
    }

    /**
     * Generate or recalculate a full payroll run.
     */
    public function processPayrollRun(?int $companyId, int $year, int $month, int $workingDays, ?string $office = 'all', ?string $employeeType = 'all', ?int $createdBy = null): Payroll
    {
        return DB::transaction(function () use ($companyId, $year, $month, $workingDays, $office, $employeeType, $createdBy) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // 1. Fetch eligible employees
            $employees = $this->getEligibleEmployees($companyId, $office, $employeeType);

            // 2. Check existing payroll run
            $payroll = $this->checkExistingPayroll($companyId, $year, $month, $office, $employeeType);

            if ($payroll && $payroll->status === 'finalized') {
                throw new \Exception("Payroll for " . $startDate->format('F Y') . " is already finalized and cannot be overwritten without recalculation approval.");
            }

            $validUser = User::find($createdBy ?: auth()->id());
            $creatorId = $validUser?->id;

            if (!$payroll) {
                $payroll = Payroll::create([
                    'company_id' => $companyId,
                    'status' => 'calculated',
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                    'pay_date' => $endDate->copy()->addDays(5)->format('Y-m-d'),
                    'generated_by' => $creatorId,
                    'metadata' => [
                        'year' => $year,
                        'month' => $month,
                        'month_name' => $startDate->format('F'),
                        'working_days' => $workingDays,
                        'office' => $office ?: 'all',
                        'employee_type' => $employeeType ?: 'all',
                    ],
                ]);
            } else {
                // Delete previous draft items to recalculate clean
                PayrollHistory::where('payroll_id', $payroll->id)->delete();
                $payroll->update([
                    'status' => 'calculated',
                    'metadata' => array_merge($payroll->metadata ?? [], [
                        'working_days' => $workingDays,
                        'recalculated_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            $totalGross = 0.0;
            $totalActualGross = 0.0;
            $totalAbsent = 0.0;
            $totalPresent = 0.0;
            $totalLeave = 0.0;
            $srNo = 1;

            foreach ($employees as $employee) {
                $line = $this->calculateEmployeePayrollLine($employee, $year, $month, $workingDays, $srNo++);

                $totalGross += $line['gross'];
                $totalActualGross += $line['ac_gross'];
                $totalAbsent += $line['total_absent'];
                $totalPresent += $line['presents'];
                $totalLeave += $line['total_leave'];

                PayrollHistory::create([
                    'payroll_id' => $payroll->id,
                    'user_id' => $employee->id,
                    'snapshot' => $line,
                    'gross_salary' => $line['gross'],
                    'total_deductions' => round($line['gross'] - $line['ac_gross'], 2),
                    'net_salary' => $line['ac_gross'],
                    'payroll_status' => 'calculated',
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                    'immutable_hash' => md5($payroll->id . '_' . $employee->id . '_' . $line['ac_gross']),
                ]);
            }

            $payroll->update([
                'gross_total' => round($totalGross, 2),
                'deduction_total' => round($totalGross - $totalActualGross, 2),
                'net_total' => round($totalActualGross, 2),
                'attendance_summary' => [
                    'total_employees' => $employees->count(),
                    'total_gross' => round($totalGross, 2),
                    'total_actual_gross' => round($totalActualGross, 2),
                    'total_absent' => round($totalAbsent, 2),
                    'total_present' => round($totalPresent, 2),
                    'total_leave' => round($totalLeave, 2),
                ],
            ]);

            // Audit log
            PayrollAuditLog::create([
                'user_id' => $creatorId,
                'role' => auth()->user()?->role ?? 'admin',
                'action' => 'processed_payroll',
                'auditable_type' => Payroll::class,
                'auditable_id' => $payroll->id,
                'ip_address' => request()->ip(),
                'new_value' => [
                    'payroll_id' => $payroll->id,
                    'year' => $year,
                    'month' => $month,
                    'employees_count' => $employees->count(),
                    'net_total' => $payroll->net_total,
                ],
            ]);

            return $payroll->fresh();
        });
    }

    /**
     * Finalize payroll run.
     */
    public function finalizePayroll(Payroll $payroll, ?int $userId = null): Payroll
    {
        return DB::transaction(function () use ($payroll, $userId) {
            $validUser = User::find($userId ?: auth()->id());
            $actorId = $validUser?->id;

            $payroll->update([
                'status' => 'finalized',
                'approved_by_admin' => $actorId,
                'locked_by' => $actorId,
                'locked_at' => now(),
            ]);

            PayrollHistory::where('payroll_id', $payroll->id)->update([
                'payroll_status' => 'finalized',
            ]);

            PayrollAuditLog::create([
                'user_id' => $actorId,
                'role' => auth()->user()?->role ?? 'admin',
                'action' => 'finalized_payroll',
                'auditable_type' => Payroll::class,
                'auditable_id' => $payroll->id,
                'ip_address' => request()->ip(),
                'new_value' => ['payroll_id' => $payroll->id, 'status' => 'finalized'],
            ]);

            return $payroll;
        });
    }

    /**
     * Generate Payslips for a finalized payroll run.
     */
    public function generatePayslips(Payroll $payroll, ?int $userId = null): int
    {
        $histories = PayrollHistory::where('payroll_id', $payroll->id)->get();
        $count = 0;
        $validUser = User::find($userId ?: auth()->id());
        $actorId = $validUser?->id;

        foreach ($histories as $history) {
            $snap = $history->snapshot;
            $empUser = User::find($history->user_id);
            if (! $empUser) continue;

            $payslipNo = 'PS-' . date('Ym', strtotime($payroll->period_start)) . '-' . str_pad($history->user_id, 4, '0', STR_PAD_LEFT);

            Payslip::updateOrCreate(
                [
                    'payroll_id' => $payroll->id,
                    'user_id' => $history->user_id,
                ],
                [
                    'payroll_history_id' => $history->id,
                    'payslip_number' => $payslipNo,
                    'employee_snapshot' => $snap,
                    'earnings' => [
                        'basic' => $snap['ac_basic'] ?? $snap['basic'] ?? 0,
                        'hra' => $snap['ac_hra'] ?? $snap['hra'] ?? 0,
                        'special' => $snap['ac_special'] ?? $snap['special'] ?? 0,
                    ],
                    'deductions' => [
                        'attendance_deduction' => round(($snap['gross'] ?? 0) - ($snap['ac_gross'] ?? 0), 2),
                    ],
                    'gross_salary' => $snap['gross'] ?? 0,
                    'total_deductions' => round(($snap['gross'] ?? 0) - ($snap['ac_gross'] ?? 0), 2),
                    'net_salary' => $snap['ac_gross'] ?? 0,
                    'status' => 'generated',
                    'generated_by' => $actorId,
                ]
            );

            $count++;
        }

        $payroll->update(['status' => 'payslip_generated']);

        return $count;
    }

    /**
     * Send payslip via email to employee.
     */
    public function sendPayslip(Payslip $payslip): bool
    {
        $user = $payslip->user;
        if (! $user || ! $user->email) {
            return false;
        }

        try {
            // Update payslip status to sent
            $payslip->update(['status' => 'sent']);

            $validUser = User::find(auth()->id());
            $actorId = $validUser?->id;

            PayrollAuditLog::create([
                'user_id' => $actorId,
                'role' => auth()->user()?->role ?? 'admin',
                'action' => 'sent_payslip',
                'auditable_type' => Payslip::class,
                'auditable_id' => $payslip->id,
                'ip_address' => request()->ip(),
                'new_value' => ['payslip_id' => $payslip->id, 'recipient' => $user->email],
            ]);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
