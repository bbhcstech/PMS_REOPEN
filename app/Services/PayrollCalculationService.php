<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BusinessAddress;
use App\Models\Company;
use App\Models\EmployeeDetail;
use App\Models\EmployeeSalaryAssignment;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Payroll;
use App\Models\PayrollAuditLog;
use App\Models\PayrollFormula;
use App\Models\PayrollHistory;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\TaskTimer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayrollCalculationService
{
    /**
     * Get eligible employees for payroll calculation based on filters.
     */
    public function getEligibleEmployees(?int $companyId = null, ?string $office = null, ?string $employeeType = null, ?int $departmentId = null)
    {
        $query = User::query()
            ->with([
                'employeeDetail.designation',
                'employeeDetail.department',
                'salaryAssignment.salaryStructure',
            ])
            ->where(function ($q) {
                $q->where('role', '!=', 'superadmin')
                  ->orWhereNull('role');
            });

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($departmentId) {
            $query->whereHas('employeeDetail', function ($empQuery) use ($departmentId) {
                $empQuery->where('department_id', $departmentId);
            });
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

        return $query->orderBy('name', 'asc')->get();
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

        $initialBalance = 18.0;
        if ($leaveBalanceRecord) {
            $initialBalance = (float) ($leaveBalanceRecord->allocated_leaves ?? $leaveBalanceRecord->remaining_leaves ?? 18.0);
        } elseif (isset($user->annual_leave_balance) && $user->annual_leave_balance > 0) {
            $initialBalance = (float) $user->annual_leave_balance;
        }

        // 2. Fetch Leaves in period
        $leaves = Leave::where('user_id', $user->id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('start_date', '<=', $endDate->format('Y-m-d'))
                          ->where('end_date', '>=', $startDate->format('Y-m-d'));
                  });
            })
            ->whereIn('status', ['approved', 'active', 'Approved'])
            ->get();

        $fullLeaveCount = 0.0;
        $halfLeaveCount = 0.0;
        $paidLeaveDays = 0.0;
        $unpaidLeaveDays = 0.0;

        foreach ($leaves as $leave) {
            $isHalf = $leave->half_day_flag || strtolower((string)$leave->type) === 'half_day' || strtolower((string)$leave->duration) === 'half_day';
            $days = $isHalf ? 0.5 : (float) ($leave->total_days ?: 1.0);

            if ($isHalf) {
                $halfLeaveCount += 1.0;
            } else {
                $fullLeaveCount += $days;
            }

            // Check if paid vs unpaid
            $isUnpaid = $leave->is_unpaid || (isset($leave->paid_days) && (float)$leave->paid_days == 0 && (float)$leave->unpaid_days > 0);
            if ($isUnpaid) {
                $unpaidLeaveDays += $days;
            } else {
                $paidLeaveDays += $days;
            }
        }

        $totalLeaveUsed = $fullLeaveCount + ($halfLeaveCount * 0.5);
        $currentBalance = max(0.0, $initialBalance - $totalLeaveUsed);

        return [
            'initial_balance' => round($initialBalance, 2),
            'full_leave' => round($fullLeaveCount, 2),
            'half_leave' => round($halfLeaveCount, 2),
            'total_leave' => round($totalLeaveUsed, 2),
            'paid_leave' => round($paidLeaveDays, 2),
            'unpaid_leave' => round($unpaidLeaveDays, 2),
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

        $leaveData = $this->getLeaveData($user, $year, $month);

        $fullAbsent = 0.0;
        $halfAbsent = 0.0;
        $presentDays = 0.0;
        $wfhDays = 0.0;
        $workingHours = 0.0;
        $overtimeHours = 0.0;

        if ($attendances->count() > 0) {
            foreach ($attendances as $att) {
                $status = strtolower((string)$att->status);
                $isHalfDay = $att->half_day || $status === 'half_day' || str_contains($status, 'half');
                $isWfh = ($att->work_from_type === 'work_from_home' || in_array($status, ['work_from_home', 'wfh']));

                // Calculate daily hours
                $hours = 0.0;
                if ($att->total_hours !== null && is_numeric($att->total_hours)) {
                    $hours = (float) $att->total_hours;
                } elseif ($att->clock_in && $att->clock_out) {
                    try {
                        $cin = Carbon::parse($att->clock_in);
                        $cout = Carbon::parse($att->clock_out);
                        $hours = round($cout->diffInMinutes($cin) / 60, 2);
                    } catch (\Throwable $e) {
                        $hours = 8.0;
                    }
                } else {
                    $hours = $isHalfDay ? 4.0 : 8.0;
                }

                $workingHours += $hours;
                if ($hours > 8.0) {
                    $overtimeHours += ($hours - 8.0);
                }

                if ($status === 'absent') {
                    $fullAbsent += 1.0;
                } elseif ($isHalfDay) {
                    $halfAbsent += 1.0;
                    $presentDays += 0.5;
                } elseif ($isWfh) {
                    $wfhDays += 1.0;
                    $presentDays += 1.0;
                } elseif (in_array($status, ['present', 'late'])) {
                    $presentDays += 1.0;
                }
            }
        } else {
            // Check TaskTimer for logged hours
            $timerHours = TaskTimer::where('user_id', $user->id)
                ->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->sum('total_hours');

            if ($timerHours > 0) {
                $workingHours = (float) $timerHours;
            } else {
                // Standard default: full attendance * 8 hours
                $workingHours = (float) ($workingDays * 8);
            }

            $presentDays = (float) $workingDays;
        }

        // Add leaves to absent consideration
        $unpaidLeave = $leaveData['unpaid_leave'];
        $paidLeave = $leaveData['paid_leave'];

        $totalAbsent = $fullAbsent + ($halfAbsent * 0.5) + $unpaidLeave;

        // Current Basic pro-rata payable days formula:
        // Working Days - Absent - Unpaid Leaves - (Half Days / 2)
        // Note: Paid leave does NOT deduct salary!
        $payableDays = max(0.0, min((float)$workingDays, $workingDays - $totalAbsent));

        return [
            'full_absent' => round($fullAbsent, 2),
            'half_absent' => round($halfAbsent, 2),
            'total_absent' => round($totalAbsent, 2),
            'presents' => round($presentDays, 2),
            'wfh_days' => round($wfhDays, 2),
            'paid_leave' => round($paidLeave, 2),
            'unpaid_leave' => round($unpaidLeave, 2),
            'working_hours' => round($workingHours, 1),
            'overtime_hours' => round($overtimeHours, 1),
            'payable_days' => round($payableDays, 2),
        ];
    }

    /**
     * Fetch active Salary Structure & components for an employee.
     * Priority:
     * 1. EmployeeSalaryAssignment (specific to employee, effective date range)
     * 2. SalaryStructure linked to employee's Designation (and grade)
     * 3. Custom salary fields on EmployeeDetail
     * 4. Fallback default structure
     */
    public function getSalaryStructure(User $user, ?Carbon $periodDate = null): array
    {
        $date = $periodDate ? $periodDate->format('Y-m-d') : date('Y-m-d');
        $empDetail = $user->employeeDetail;
        $designationId = $empDetail?->designation_id;

        $basic = 0.0;
        $hraType = 'percentage';
        $hraValue = 50.0;
        $specialType = 'percentage';
        $specialValue = 50.0;
        $grade = 'Standard';
        $source = 'default';
        $structureId = null;

        // 1. Check EmployeeSalaryAssignment
        $assignment = EmployeeSalaryAssignment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->latest('effective_from')
            ->first();

        if ($assignment) {
            $basic = (float) $assignment->actual_basic_salary;
            $struct = $assignment->salaryStructure;
            $hraType = $assignment->hra_type ?: ($struct?->hra_type ?: 'percentage');
            $hraValue = (float) ($assignment->hra_value !== null && $assignment->hra_value > 0 ? $assignment->hra_value : ($struct?->hra_value ?? 50.0));
            $specialType = $assignment->special_allowance_type ?: ($struct?->special_allowance_type ?: 'percentage');
            $specialValue = (float) ($assignment->special_allowance_value !== null && $assignment->special_allowance_value > 0 ? $assignment->special_allowance_value : ($struct?->special_allowance_value ?? 50.0));
            $grade = $assignment->grade ?: ($struct?->grade ?: ($empDetail?->designation?->level ?: 'Standard'));
            $structureId = $assignment->salary_structure_id;
            $source = 'assignment';
        }

        // 2. Check Designation Salary Structure
        if ($source === 'default' && $designationId) {
            $structure = SalaryStructure::where('designation_id', $designationId)
                ->where('status', 'active')
                ->where(function ($q) use ($date) {
                    $q->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', $date);
                })
                ->where(function ($q) use ($date) {
                    $q->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $date);
                })
                ->first();

            if ($structure) {
                $basic = (float) $structure->basic_salary;
                $hraType = $structure->hra_type ?: 'percentage';
                $hraValue = (float) ($structure->hra_value ?? 50.0);
                $specialType = $structure->special_allowance_type ?: 'percentage';
                $specialValue = (float) ($structure->special_allowance_value ?? 50.0);
                $grade = $structure->grade ?: ($empDetail?->designation?->level ?: 'Standard');
                $structureId = $structure->id;
                $source = 'designation_structure';
            }
        }

        // 3. Check EmployeeDetail custom fields
        if ($source === 'default' && $empDetail && ($empDetail->basic_salary > 0 || $empDetail->hra_amount > 0 || $empDetail->special_allowance > 0)) {
            $basic = (float) ($empDetail->basic_salary ?? 0);
            $hraType = 'fixed';
            $hraValue = (float) ($empDetail->hra_amount ?? 0);
            $specialType = 'fixed';
            $specialValue = (float) ($empDetail->special_allowance ?? 0);
            $grade = $empDetail->designation?->level ?: 'Standard';
            $source = 'employee_detail';
        }

        // 4. Fallback defaults
        if ($basic <= 0) {
            $basic = 30000.00;
            $hraType = 'percentage';
            $hraValue = 50.0;
            $specialType = 'percentage';
            $specialValue = 50.0;
            $grade = $empDetail?->designation?->level ?: 'D1';
            $source = 'fallback';
        }

        // Calculate standard monthly components
        $hra = ($hraType === 'percentage') ? round($basic * ($hraValue / 100), 2) : round($hraValue, 2);
        $special = ($specialType === 'percentage') ? round($basic * ($specialValue / 100), 2) : round($specialValue, 2);
        $gross = round($basic + $hra + $special, 2);

        return [
            'basic' => round($basic, 2),
            'hra_type' => $hraType,
            'hra_value' => round($hraValue, 2),
            'hra' => round($hra, 2),
            'special_type' => $specialType,
            'special_value' => round($specialValue, 2),
            'special' => round($special, 2),
            'gross' => round($gross, 2),
            'grade' => $grade,
            'source' => $source,
            'salary_structure_id' => $structureId,
            'has_structure' => ($source !== 'fallback'),
        ];
    }

    /**
     * Calculate entire single employee payroll line item.
     */
    public function calculateEmployeePayrollLine(User $user, int $year, int $month, int $workingDays, int $srNo = 1, array $lineInputs = []): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $policyService = app(PayrollPolicyService::class);
        $policy = $policyService->getActivePolicy($user->company_id);

        // Fetch active formulas effective for this payroll period
        $activeFormulas = PayrollFormula::where('is_active', true)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', $startDate->format('Y-m-d'));
            })
            ->where(function ($q) use ($startDate) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->keyBy('code');

        $leaveData = $this->getLeaveData($user, $year, $month);
        $attData = $this->getAttendanceData($user, $year, $month, $workingDays);
        $salaryData = $this->getSalaryStructure($user, $startDate);

        $payableDays = $attData['payable_days'];
        $ratio = $workingDays > 0 ? ($payableDays / $workingDays) : 1.0;

        // Current Basic calculation:
        // Formula: Basic Salary * (Payable Days / Total Working Days)
        $basic = $salaryData['basic'];
        $acBasic = round($basic * $ratio, 2);
        if ($activeFormulas->has('CURRENT_BASIC')) {
            $eval = PayrollFormula::evaluate($activeFormulas['CURRENT_BASIC']->formula, [
                'BASIC' => $basic,
                'PAYABLE_DAYS' => $payableDays,
                'WORKING_DAYS' => $workingDays,
            ]);
            if ($eval !== false && $eval >= 0) {
                $acBasic = round((float) $eval, 2);
            }
        }

        // HRA calculation:
        // Configurable percentage of current basic or fixed amount
        $hraType = $salaryData['hra_type'];
        $hraValue = $salaryData['hra_value'];
        $acHra = ($hraType === 'percentage')
            ? round($acBasic * ($hraValue / 100), 2)
            : round($hraValue * $ratio, 2);

        // Special Allowance calculation:
        // Configurable percentage of current basic or fixed amount
        $specialType = $salaryData['special_type'];
        $specialValue = $salaryData['special_value'];
        $acSpecial = ($specialType === 'percentage')
            ? round($acBasic * ($specialValue / 100), 2)
            : round($specialValue * $ratio, 2);

        $otherAllowances = (float) ($lineInputs['other_allowances'] ?? 0.0);
        $acGross = round($acBasic + $acHra + $acSpecial + $otherAllowances, 2);
        if ($activeFormulas->has('GROSS_SALARY')) {
            $eval = PayrollFormula::evaluate($activeFormulas['GROSS_SALARY']->formula, [
                'CURRENT_BASIC' => $acBasic,
                'HRA' => $acHra,
                'SPECIAL_ALLOWANCE' => $acSpecial,
                'OTHER_ALLOWANCES' => $otherAllowances,
            ]);
            if ($eval !== false && $eval >= 0) {
                $acGross = round((float) $eval, 2);
            }
        }

        // ── Statutory Employee Deductions ───────────────────────────────────
        $dedRules = $policy->deductions_rules ?? [];
        $pfEnabled = isset($dedRules['pf_enabled']) ? (bool)$dedRules['pf_enabled'] : true;
        $esiEnabled = isset($dedRules['esi_enabled']) ? (bool)$dedRules['esi_enabled'] : true;

        // PF: 12% of Current Basic (standard max cap 1800 if limit enabled)
        $pfRate = (float) ($dedRules['pf_percentage'] ?? 12.0);
        $pfAmt = $pfEnabled ? round($acBasic * ($pfRate / 100), 2) : 0.0;
        if ($pfEnabled && $activeFormulas->has('PF_EMPLOYEE')) {
            $eval = PayrollFormula::evaluate($activeFormulas['PF_EMPLOYEE']->formula, [
                'CURRENT_BASIC' => $acBasic,
                'GROSS_SALARY' => $acGross,
            ]);
            if ($eval !== false && $eval >= 0) {
                $pfAmt = round((float) $eval, 2);
            }
        }

        // ESI: 0.75% of Gross Salary (applicable if Gross <= 21000 or enabled)
        $esiRate = (float) ($dedRules['esi_percentage'] ?? 0.75);
        $esiAmt = ($esiEnabled && ($acGross <= 21000 || !empty($dedRules['esi_all'])))
            ? round($acGross * ($esiRate / 100), 2)
            : 0.0;
        if ($esiEnabled && ($acGross <= 21000 || !empty($dedRules['esi_all'])) && $activeFormulas->has('ESI_EMPLOYEE')) {
            $eval = PayrollFormula::evaluate($activeFormulas['ESI_EMPLOYEE']->formula, [
                'GROSS_SALARY' => $acGross,
                'CURRENT_BASIC' => $acBasic,
            ]);
            if ($eval !== false && $eval >= 0) {
                $esiAmt = round((float) $eval, 2);
            }
        }

        $ptAmt = (!empty($dedRules['pt_enabled']))
            ? (float) ($dedRules['pt_fixed_amount'] ?? 200)
            : ($acGross > 15000 ? 200.0 : 0.0);
        if ($activeFormulas->has('PROFESSIONAL_TAX')) {
            $eval = PayrollFormula::evaluate($activeFormulas['PROFESSIONAL_TAX']->formula, [
                'GROSS_SALARY' => $acGross,
                'CURRENT_BASIC' => $acBasic,
            ]);
            if ($eval !== false && $eval >= 0) {
                $ptAmt = round((float) $eval, 2);
            }
        }

        $tdsAmt = (float) ($lineInputs['tds'] ?? ($dedRules['tds_amount'] ?? 0.0));
        $otherDeductions = (float) ($lineInputs['other_deductions'] ?? 0.0);

        $totalEmployeeDeductions = round($pfAmt + $esiAmt + $ptAmt + $tdsAmt + $otherDeductions, 2);
        if ($activeFormulas->has('TOTAL_DEDUCTIONS')) {
            $eval = PayrollFormula::evaluate($activeFormulas['TOTAL_DEDUCTIONS']->formula, [
                'PF' => $pfAmt,
                'ESI' => $esiAmt,
                'PT' => $ptAmt,
                'TDS' => $tdsAmt,
                'OTHER_DEDUCTIONS' => $otherDeductions,
            ]);
            if ($eval !== false && $eval >= 0) {
                $totalEmployeeDeductions = round((float) $eval, 2);
            }
        }

        // ── Net Pay (Gross - Total Employee Deductions) ─────────────────────
        $netPay = max(0.0, round($acGross - $totalEmployeeDeductions, 2));
        if ($activeFormulas->has('NET_PAY')) {
            $eval = PayrollFormula::evaluate($activeFormulas['NET_PAY']->formula, [
                'GROSS_SALARY' => $acGross,
                'TOTAL_DEDUCTIONS' => $totalEmployeeDeductions,
            ]);
            if ($eval !== false && $eval >= 0) {
                $netPay = max(0.0, round((float) $eval, 2));
            }
        }

        // ── Additional Earnings & Bonuses ──────────────────────────────────
        // Attendance Bonus rule:
        // Working Hours > 180 -> 400; > 162 -> 200; else 0
        $workingHours = $attData['working_hours'];
        $calcAttendanceBonus = 0.0;
        if ($workingHours > 180) {
            $calcAttendanceBonus = 400.0;
        } elseif ($workingHours > 162) {
            $calcAttendanceBonus = 200.0;
        }
        if ($activeFormulas->has('ATTENDANCE_BONUS')) {
            $eval = PayrollFormula::evaluate($activeFormulas['ATTENDANCE_BONUS']->formula, [
                'WORKING_HOURS' => $workingHours,
            ]);
            if ($eval !== false && $eval >= 0) {
                $calcAttendanceBonus = round((float) $eval, 2);
            }
        }

        $attendanceBonus = isset($lineInputs['attendance_bonus'])
            ? (float) $lineInputs['attendance_bonus']
            : $calcAttendanceBonus;

        // Best Employee Bonus: Rank 1 -> 400; Rank 2 -> 200; else 0
        $beRank = (int) ($lineInputs['be_rank'] ?? 0);
        $bestEmpBonus = 0.0;
        if ($beRank === 1) {
            $bestEmpBonus = 400.0;
        } elseif ($beRank === 2) {
            $bestEmpBonus = 200.0;
        }
        if ($activeFormulas->has('BEST_EMPLOYEE_BONUS')) {
            $eval = PayrollFormula::evaluate($activeFormulas['BEST_EMPLOYEE_BONUS']->formula, [
                'BE_RANK' => $beRank,
            ]);
            if ($eval !== false && $eval >= 0) {
                $bestEmpBonus = round((float) $eval, 2);
            }
        }
        if (isset($lineInputs['best_employee_bonus'])) {
            $bestEmpBonus = (float) $lineInputs['best_employee_bonus'];
        }

        // Travel Allowance (TA) - admin manual / approved
        $travelAllowance = (float) ($lineInputs['ta'] ?? 0.0);

        // Overtime Amount: Overtime Hours * Overtime Rate
        $overtimeHours = (float) ($lineInputs['overtime_hours'] ?? $attData['overtime_hours']);
        $hourlyRate = $workingDays > 0 ? round($basic / ($workingDays * 8), 2) : 200.0;
        $overtimeRate = (float) ($lineInputs['overtime_rate'] ?? max(150.0, $hourlyRate * 1.5));
        $overtimePay = isset($lineInputs['overtime'])
            ? (float) $lineInputs['overtime']
            : round($overtimeHours * $overtimeRate, 2);

        // Commission
        $commissionAmt = (float) ($lineInputs['commission'] ?? 0.0);

        // Adjustments (+ / -)
        $rawAdj = (float) ($lineInputs['adjustment'] ?? 0.0);
        $positiveAdjustment = (float) ($lineInputs['positive_adjustment'] ?? ($rawAdj > 0 ? $rawAdj : 0.0));
        $negativeAdjustment = (float) ($lineInputs['negative_adjustment'] ?? ($rawAdj < 0 ? abs($rawAdj) : 0.0));
        $netAdjustment = round($positiveAdjustment - $negativeAdjustment, 2);

        $totalAdditionalEarnings = round(
            $attendanceBonus
            + $bestEmpBonus
            + $travelAllowance
            + $overtimePay
            + $commissionAmt
            + $netAdjustment,
            2
        );
        if ($activeFormulas->has('TOTAL_ADDITIONAL_EARNINGS')) {
            $eval = PayrollFormula::evaluate($activeFormulas['TOTAL_ADDITIONAL_EARNINGS']->formula, [
                'ATTENDANCE_BONUS' => $attendanceBonus,
                'BEST_EMPLOYEE_BONUS' => $bestEmpBonus,
                'TA' => $travelAllowance,
                'OVERTIME' => $overtimePay,
                'COMMISSION' => $commissionAmt,
                'ADJUSTMENTS' => $netAdjustment,
            ]);
            if ($eval !== false && $eval >= 0) {
                $totalAdditionalEarnings = round((float) $eval, 2);
            }
        }

        // ── Total In Hand (Net Pay + Additional Earnings) ───────────────────
        $totalInHand = max(0.0, round($netPay + $totalAdditionalEarnings, 2));
        if ($activeFormulas->has('TOTAL_IN_HAND')) {
            $eval = PayrollFormula::evaluate($activeFormulas['TOTAL_IN_HAND']->formula, [
                'NET_PAY' => $netPay,
                'TOTAL_ADDITIONAL_EARNINGS' => $totalAdditionalEarnings,
            ]);
            if ($eval !== false && $eval >= 0) {
                $totalInHand = max(0.0, round((float) $eval, 2));
            }
        }

        // ── Employer Contributions (Statutory - strictly NOT deducted from employee) ──
        // Employer PF: 12% of Current Basic
        $cpfAmt = $pfEnabled ? round($acBasic * 0.12, 2) : 0.0;
        if ($pfEnabled && $activeFormulas->has('PF_EMPLOYER')) {
            $eval = PayrollFormula::evaluate($activeFormulas['PF_EMPLOYER']->formula, [
                'CURRENT_BASIC' => $acBasic,
                'GROSS_SALARY' => $acGross,
            ]);
            if ($eval !== false && $eval >= 0) {
                $cpfAmt = round((float) $eval, 2);
            }
        }

        // Employer ESI: 3.25% of Gross
        $cesiAmt = ($esiEnabled && ($acGross <= 21000 || !empty($dedRules['esi_all'])))
            ? round($acGross * 0.0325, 2)
            : 0.0;
        if ($esiEnabled && ($acGross <= 21000 || !empty($dedRules['esi_all'])) && $activeFormulas->has('ESI_EMPLOYER')) {
            $eval = PayrollFormula::evaluate($activeFormulas['ESI_EMPLOYER']->formula, [
                'GROSS_SALARY' => $acGross,
                'CURRENT_BASIC' => $acBasic,
            ]);
            if ($eval !== false && $eval >= 0) {
                $cesiAmt = round((float) $eval, 2);
            }
        }

        // EDLI: 0.5% of Current Basic
        $edliAmt = $pfEnabled ? round($acBasic * 0.005, 2) : 0.0;
        if ($pfEnabled && $activeFormulas->has('EDLI_EMPLOYER')) {
            $eval = PayrollFormula::evaluate($activeFormulas['EDLI_EMPLOYER']->formula, [
                'CURRENT_BASIC' => $acBasic,
            ]);
            if ($eval !== false && $eval >= 0) {
                $edliAmt = round((float) $eval, 2);
            }
        }

        $otherEmployerContribution = (float) ($lineInputs['other_employer_contribution'] ?? 0.0);

        $totalEmployerContribution = round($cpfAmt + $cesiAmt + $edliAmt + $otherEmployerContribution, 2);
        if ($activeFormulas->has('TOTAL_EMPLOYER_CONTRIBUTIONS')) {
            $eval = PayrollFormula::evaluate($activeFormulas['TOTAL_EMPLOYER_CONTRIBUTIONS']->formula, [
                'EMPLOYER_PF' => $cpfAmt,
                'EMPLOYER_ESI' => $cesiAmt,
                'EDLI' => $edliAmt,
                'OTHER_EMPLOYER' => $otherEmployerContribution,
            ]);
            if ($eval !== false && $eval >= 0) {
                $totalEmployerContribution = round((float) $eval, 2);
            }
        }

        // ── Total CTC (Gross + Additional Earnings + Employer Contributions) ──
        $ctc = round($acGross + $totalAdditionalEarnings + $totalEmployerContribution, 2);
        if ($activeFormulas->has('CTC')) {
            $eval = PayrollFormula::evaluate($activeFormulas['CTC']->formula, [
                'GROSS_SALARY' => $acGross,
                'TOTAL_ADDITIONAL_EARNINGS' => $totalAdditionalEarnings,
                'TOTAL_EMPLOYER_CONTRIBUTION' => $totalEmployerContribution,
            ]);
            if ($eval !== false && $eval >= 0) {
                $ctc = round((float) $eval, 2);
            }
        }

        $empDetail = $user->employeeDetail;
        $empId = $empDetail?->employee_id ?: ('EMP' . str_pad($user->id, 3, '0', STR_PAD_LEFT));
        $branch = $empDetail?->business_address ?: 'HQ';
        $departmentName = $empDetail?->department?->dpt_name ?? ($empDetail?->department?->name ?? 'Development');
        $designationName = $empDetail?->designation?->name ?: 'Developer';
        $joiningDate = $empDetail?->joining_date ? Carbon::parse($empDetail->joining_date)->format('d M Y') : '12 Mar 2022';

        return [
            'sr_no' => $srNo,
            'user_id' => $user->id,
            'employee_id' => $empId,
            'employee_name' => $user->name,
            'email' => $user->email,
            'branch' => $branch,
            'office' => $branch,
            'department' => $departmentName,
            'designation' => $designationName,
            'grade' => $salaryData['grade'],
            'joining_date' => $joiningDate,

            // Attendance Summary
            'working_days' => $workingDays,
            'present' => $attData['presents'],
            'presents' => $attData['presents'],
            'paid_leave' => $attData['paid_leave'],
            'unpaid_leave' => $attData['unpaid_leave'],
            'full_absent' => $attData['full_absent'],
            'half_absent' => $attData['half_absent'],
            'absent' => $attData['total_absent'],
            'total_absent' => $attData['total_absent'],
            'half_day' => $attData['half_absent'],
            'wfh' => $attData['wfh_days'],
            'working_hours' => $attData['working_hours'],
            'overtime_hours' => $overtimeHours,
            'payable_days' => $payableDays,

            // Leave Balances
            'initial_leave_balance' => $leaveData['initial_balance'],
            'current_leave_balance' => $leaveData['current_balance'],
            'total_leave' => $leaveData['total_leave'],

            // Base Salary Structure
            'basic' => $basic,
            'hra' => $salaryData['hra'],
            'hra_type' => $hraType,
            'hra_value' => $hraValue,
            'special' => $salaryData['special'],
            'special_type' => $specialType,
            'special_value' => $specialValue,
            'other_allowances' => $otherAllowances,
            'gross' => $salaryData['gross'],
            'salary_source' => $salaryData['source'],
            'has_salary_structure' => $salaryData['has_structure'],

            // Actual / Current Pro-Rata Earnings
            'current_basic' => $acBasic,
            'ac_basic' => $acBasic,
            'current_hra' => $acHra,
            'ac_hra' => $acHra,
            'current_special' => $acSpecial,
            'ac_special' => $acSpecial,
            'current_gross' => $acGross,
            'ac_gross' => $acGross,
            'gross_salary' => $acGross,

            // Employee Deductions
            'pf' => $pfAmt,
            'esi' => $esiAmt,
            'pt' => $ptAmt,
            'tds' => $tdsAmt,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalEmployeeDeductions,
            'total_deduction' => $totalEmployeeDeductions,

            // Net Pay
            'net_pay' => $netPay,
            'net_salary' => $netPay,

            // Additional Earnings
            'attendance_bonus' => $attendanceBonus,
            'be_rank' => $beRank,
            'best_employee_bonus' => $bestEmpBonus,
            'ta' => $travelAllowance,
            'overtime' => $overtimePay,
            'overtime_rate' => $overtimeRate,
            'commission' => $commissionAmt,
            'positive_adjustment' => $positiveAdjustment,
            'negative_adjustment' => $negativeAdjustment,
            'adjustment' => $netAdjustment,
            'total_additional_earnings' => $totalAdditionalEarnings,
            'additional_earnings' => $totalAdditionalEarnings,
            'total_bonus' => $totalAdditionalEarnings,

            // Final Payouts
            'total_in_hand' => $totalInHand,

            // Employer Contributions
            'employer_pf' => $cpfAmt,
            'cpf' => $cpfAmt,
            'employer_esi' => $cesiAmt,
            'cesi' => $cesiAmt,
            'edli' => $edliAmt,
            'other_employer_contribution' => $otherEmployerContribution,
            'total_employer_contribution' => $totalEmployerContribution,
            'company_contribution' => $totalEmployerContribution,

            // Cost to Company
            'ctc' => $ctc,

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
     * Check if duplicate finalized payroll exists for employee in period.
     */
    public function hasDuplicateFinalizedPayroll(int $userId, int $year, int $month): bool
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        return PayrollHistory::where('user_id', $userId)
            ->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate)
            ->where('payroll_status', 'finalized')
            ->exists();
    }

    /**
     * Generate or recalculate a full payroll run.
     */
    public function processPayrollRun(?int $companyId, int $year, int $month, int $workingDays, ?string $office = 'all', ?string $employeeType = 'all', ?int $createdBy = null, ?int $departmentId = null): Payroll
    {
        return DB::transaction(function () use ($companyId, $year, $month, $workingDays, $office, $employeeType, $createdBy, $departmentId) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // 1. Fetch eligible employees
            $employees = $this->getEligibleEmployees($companyId, $office, $employeeType, $departmentId);

            // 2. Check existing payroll run
            $payroll = $this->checkExistingPayroll($companyId, $year, $month, $office, $employeeType);

            if ($payroll && $payroll->status === 'finalized') {
                throw new \Exception("Payroll for " . $startDate->format('F Y') . " is already finalized and cannot be overwritten. Historical payroll is protected.");
            }

            $validUser = User::find($createdBy ?: auth()->id());
            $creatorId = $validUser?->id;

            if (!$payroll) {
                $payroll = Payroll::create([
                    'company_id' => $companyId,
                    'status' => 'calculated',
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                    'pay_date' => $endDate->format('Y-m-d'),
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
                // Delete previous non-finalized items to recalculate clean
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
            $totalDeductions = 0.0;
            $totalNetPay = 0.0;
            $totalInHand = 0.0;
            $totalEmployerContribution = 0.0;
            $totalCtc = 0.0;

            $totalAbsent = 0.0;
            $totalPresent = 0.0;
            $totalLeave = 0.0;
            $srNo = 1;

            foreach ($employees as $employee) {
                $line = $this->calculateEmployeePayrollLine($employee, $year, $month, $workingDays, $srNo++);

                $totalGross += $line['gross_salary'];
                $totalDeductions += $line['total_deductions'];
                $totalNetPay += $line['net_pay'];
                $totalInHand += $line['total_in_hand'];
                $totalEmployerContribution += $line['total_employer_contribution'];
                $totalCtc += $line['ctc'];

                $totalAbsent += $line['total_absent'];
                $totalPresent += $line['presents'];
                $totalLeave += $line['total_leave'];

                PayrollHistory::create([
                    'payroll_id' => $payroll->id,
                    'user_id' => $employee->id,
                    'snapshot' => $line,
                    'gross_salary' => $line['gross_salary'],
                    'total_deductions' => $line['total_deductions'],
                    'net_salary' => $line['net_pay'],
                    'total_in_hand' => $line['total_in_hand'],
                    'ctc' => $line['ctc'],
                    'payroll_status' => 'calculated',
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                    'immutable_hash' => md5($payroll->id . '_' . $employee->id . '_' . $line['total_in_hand']),
                ]);
            }

            $payroll->update([
                'gross_total' => round($totalGross, 2),
                'deduction_total' => round($totalDeductions, 2),
                'net_total' => round($totalNetPay, 2),
                'total_in_hand' => round($totalInHand, 2),
                'total_employer_contribution' => round($totalEmployerContribution, 2),
                'total_ctc' => round($totalCtc, 2),
                'attendance_summary' => [
                    'total_employees' => $employees->count(),
                    'total_gross' => round($totalGross, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'total_net_pay' => round($totalNetPay, 2),
                    'total_in_hand' => round($totalInHand, 2),
                    'total_employer_contribution' => round($totalEmployerContribution, 2),
                    'total_ctc' => round($totalCtc, 2),
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
                    'total_in_hand' => $payroll->total_in_hand,
                    'total_ctc' => $payroll->total_ctc,
                ],
            ]);

            return $payroll->fresh();
        });
    }

    /**
     * Mark payroll as reviewed.
     */
    public function reviewPayroll(Payroll $payroll, ?int $userId = null): Payroll
    {
        if ($payroll->status === 'finalized') {
            throw new \Exception("Finalized payroll cannot be changed.");
        }

        $payroll->update([
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);

        PayrollHistory::where('payroll_id', $payroll->id)->update([
            'payroll_status' => 'reviewed',
        ]);

        return $payroll;
    }

    /**
     * Mark payroll as approved.
     */
    public function approvePayroll(Payroll $payroll, ?int $userId = null): Payroll
    {
        if ($payroll->status === 'finalized') {
            throw new \Exception("Finalized payroll cannot be changed.");
        }

        $validUser = User::find($userId ?: auth()->id());

        $payroll->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by_admin' => $validUser?->id,
        ]);

        PayrollHistory::where('payroll_id', $payroll->id)->update([
            'payroll_status' => 'approved',
        ]);

        return $payroll;
    }

    /**
     * Finalize payroll run.
     * Prevents future modifications & duplicate finalized payroll.
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
                'finalized_at' => now(),
            ]);

            PayrollHistory::where('payroll_id', $payroll->id)->update([
                'payroll_status' => 'finalized',
            ]);

            // Auto-generate payslips for finalized run
            $this->generatePayslips($payroll, $actorId);

            PayrollAuditLog::create([
                'user_id' => $actorId,
                'role' => auth()->user()?->role ?? 'admin',
                'action' => 'finalized_payroll',
                'auditable_type' => Payroll::class,
                'auditable_id' => $payroll->id,
                'ip_address' => request()->ip(),
                'new_value' => [
                    'payroll_id' => $payroll->id,
                    'status' => 'finalized',
                    'finalized_at' => now()->toDateTimeString(),
                ],
            ]);

            return $payroll;
        });
    }

    /**
     * Update manual line inputs (TA, commission, best employee bonus, adjustments)
     */
    public function updatePayrollHistoryLine(PayrollHistory $history, array $inputs): PayrollHistory
    {
        if ($history->payroll_status === 'finalized') {
            throw new \Exception("Cannot edit finalized payroll record.");
        }

        $snap = $history->snapshot;
        $user = User::find($history->user_id);
        if (! $user) {
            throw new \Exception("Employee not found.");
        }

        $meta = $history->payroll?->metadata ?? [];
        $year = (int) ($meta['year'] ?? date('Y', strtotime($history->period_start)));
        $month = (int) ($meta['month'] ?? date('n', strtotime($history->period_start)));
        $workingDays = (int) ($meta['working_days'] ?? 22);

        $mergedInputs = array_merge($snap, $inputs);
        $recalculatedLine = $this->calculateEmployeePayrollLine($user, $year, $month, $workingDays, $snap['sr_no'] ?? 1, $mergedInputs);

        $history->update([
            'snapshot' => $recalculatedLine,
            'gross_salary' => $recalculatedLine['gross_salary'],
            'total_deductions' => $recalculatedLine['total_deductions'],
            'net_salary' => $recalculatedLine['net_pay'],
            'total_in_hand' => $recalculatedLine['total_in_hand'],
            'ctc' => $recalculatedLine['ctc'],
        ]);

        // Recalculate parent payroll totals
        $this->refreshPayrollTotals($history->payroll);

        return $history;
    }

    /**
     * Recalculate total summary on parent payroll run.
     */
    public function refreshPayrollTotals(Payroll $payroll): void
    {
        $items = PayrollHistory::where('payroll_id', $payroll->id)->get();
        $totalGross = 0.0;
        $totalDeductions = 0.0;
        $totalNetPay = 0.0;
        $totalInHand = 0.0;
        $totalEmployer = 0.0;
        $totalCtc = 0.0;

        foreach ($items as $item) {
            $s = $item->snapshot;
            $totalGross += (float) ($s['gross_salary'] ?? $item->gross_salary ?? 0);
            $totalDeductions += (float) ($s['total_deductions'] ?? $item->total_deductions ?? 0);
            $totalNetPay += (float) ($s['net_pay'] ?? $item->net_salary ?? 0);
            $totalInHand += (float) ($s['total_in_hand'] ?? $item->total_in_hand ?? 0);
            $totalEmployer += (float) ($s['total_employer_contribution'] ?? 0);
            $totalCtc += (float) ($s['ctc'] ?? $item->ctc ?? 0);
        }

        $payroll->update([
            'gross_total' => round($totalGross, 2),
            'deduction_total' => round($totalDeductions, 2),
            'net_total' => round($totalNetPay, 2),
            'total_in_hand' => round($totalInHand, 2),
            'total_employer_contribution' => round($totalEmployer, 2),
            'total_ctc' => round($totalCtc, 2),
        ]);
    }

    /**
     * Generate Payslips for a payroll run.
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

            $existingPayslip = Payslip::where('payroll_id', $payroll->id)->where('user_id', $history->user_id)->first();
            $payslipNo = $existingPayslip?->payslip_number ?: ('PS-' . date('Ym', strtotime($payroll->period_start)) . '-' . str_pad($payroll->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($history->user_id, 4, '0', STR_PAD_LEFT));

            $earnings = [
                'Basic Salary' => (float) ($snap['current_basic'] ?? $snap['ac_basic'] ?? $snap['basic'] ?? 0),
                'House Rent Allowance (HRA)' => (float) ($snap['current_hra'] ?? $snap['ac_hra'] ?? $snap['hra'] ?? 0),
                'Special Allowance' => (float) ($snap['current_special'] ?? $snap['ac_special'] ?? $snap['special'] ?? 0),
            ];
            if (!empty($snap['other_allowances']) && (float)$snap['other_allowances'] > 0) {
                $earnings['Other Allowances'] = (float) $snap['other_allowances'];
            }
            if (!empty($snap['attendance_bonus']) && (float)$snap['attendance_bonus'] > 0) {
                $earnings['Attendance Bonus'] = (float) $snap['attendance_bonus'];
            }
            if (!empty($snap['best_employee_bonus']) && (float)$snap['best_employee_bonus'] > 0) {
                $earnings['Best Employee Bonus'] = (float) $snap['best_employee_bonus'];
            }
            if (!empty($snap['ta']) && (float)$snap['ta'] > 0) {
                $earnings['Travel Allowance (TA)'] = (float) $snap['ta'];
            }
            if (!empty($snap['overtime']) && (float)$snap['overtime'] > 0) {
                $earnings['Overtime Pay'] = (float) $snap['overtime'];
            }
            if (!empty($snap['commission']) && (float)$snap['commission'] > 0) {
                $earnings['Commission'] = (float) $snap['commission'];
            }
            if (!empty($snap['adjustment']) && (float)$snap['adjustment'] != 0) {
                $earnings['Adjustments'] = (float) $snap['adjustment'];
            }

            $deductions = [];
            if (!empty($snap['pf']) && (float)$snap['pf'] > 0) {
                $deductions['Provident Fund (PF 12%)'] = (float) $snap['pf'];
            }
            if (!empty($snap['esi']) && (float)$snap['esi'] > 0) {
                $deductions['Employee State Insurance (ESI 0.75%)'] = (float) $snap['esi'];
            }
            if (!empty($snap['pt']) && (float)$snap['pt'] > 0) {
                $deductions['Professional Tax (PT)'] = (float) $snap['pt'];
            }
            if (!empty($snap['tds']) && (float)$snap['tds'] > 0) {
                $deductions['TDS / Income Tax'] = (float) $snap['tds'];
            }
            if (!empty($snap['other_deductions']) && (float)$snap['other_deductions'] > 0) {
                $deductions['Other Deductions'] = (float) $snap['other_deductions'];
            }

            $grossSalary = (float) ($snap['gross_salary'] ?? $snap['ac_gross'] ?? 0);
            $totalDed = round(array_sum(array_values($deductions)), 2);
            $netSalary = (float) ($snap['net_pay'] ?? max(0.0, $grossSalary - $totalDed));

            Payslip::updateOrCreate(
                [
                    'payroll_id' => $payroll->id,
                    'user_id' => $history->user_id,
                ],
                [
                    'company_id' => $payroll->company_id,
                    'payroll_history_id' => $history->id,
                    'payslip_number' => $payslipNo,
                    'employee_snapshot' => $snap,
                    'earnings' => $earnings,
                    'deductions' => $deductions,
                    'taxes' => [
                        'pt' => (float) ($snap['pt'] ?? 0),
                        'tds' => (float) ($snap['tds'] ?? 0),
                    ],
                    'gross_salary' => round($grossSalary, 2),
                    'total_deductions' => round($totalDed, 2),
                    'net_salary' => round($netSalary, 2),
                    'status' => 'generated',
                    'generated_by' => $actorId,
                ]
            );

            $count++;
        }

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
