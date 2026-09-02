<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PayrollPolicy;
use App\Models\PayrollPolicyHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollPolicyService
{
    /**
     * Get active policy for a company or system default.
     */
    public function getActivePolicy(?int $companyId = null, ?string $date = null): PayrollPolicy
    {
        $targetDate = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');

        // 1. Try company-specific published policy
        if ($companyId) {
            $policy = PayrollPolicy::where('company_id', $companyId)
                ->where('status', 'published')
                ->where(function ($q) use ($targetDate) {
                    $q->whereNull('effective_from')->orWhere('effective_from', '<=', $targetDate);
                })
                ->where(function ($q) use ($targetDate) {
                    $q->whereNull('effective_until')->orWhere('effective_until', '>=', $targetDate);
                })
                ->orderBy('version', 'desc')
                ->first();

            if ($policy) {
                return $policy;
            }
        }

        // 2. Try default active policy
        $defaultPolicy = PayrollPolicy::where('status', 'published')
            ->orderBy('is_default', 'desc')
            ->orderBy('version', 'desc')
            ->first();

        if ($defaultPolicy) {
            return $defaultPolicy;
        }

        // 3. Seed initial default policy if empty
        return $this->seedDefaultPolicy($companyId);
    }

    /**
     * Seed initial system default policy record.
     */
    public function seedDefaultPolicy(?int $companyId = null): PayrollPolicy
    {
        $defaults = PayrollPolicy::getDefaultRules();

        return PayrollPolicy::create(array_merge([
            'company_id' => $companyId,
            'name' => 'Standard Corporate Payroll Policy',
            'code' => 'POL-STD-' . strtoupper(substr(md5(uniqid()), 0, 4)),
            'status' => 'published',
            'version' => 1,
            'is_default' => true,
            'effective_from' => now()->startOfYear()->format('Y-m-d'),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ], $defaults));
    }

    /**
     * Save or update policy with automatic versioning & history snapshot.
     */
    public function savePolicy(array $data, ?int $policyId = null, ?int $userId = null): PayrollPolicy
    {
        return DB::transaction(function () use ($data, $policyId, $userId) {
            $currentUserId = $userId ?: auth()->id();
            $policy = $policyId ? PayrollPolicy::find($policyId) : null;

            $isNew = !$policy;
            $oldSnapshot = $policy ? $policy->toArray() : [];

            $rulesCategories = [
                'salary_earnings_rules',
                'working_days_rules',
                'leave_absence_rules',
                'overtime_rules',
                'deductions_rules',
                'tax_rules',
                'bonus_rules',
                'attendance_rules',
                'processing_rules',
                'rounding_rules',
                'payslip_rules',
                'compliance_rules',
            ];

            $updatePayload = [
                'name' => $data['name'] ?? ($policy ? $policy->name : 'Standard Payroll Policy'),
                'status' => $data['status'] ?? 'published',
                'company_id' => $data['company_id'] ?? ($policy ? $policy->company_id : null),
                'effective_from' => $data['effective_from'] ?? ($policy ? $policy->effective_from : now()->format('Y-m-d')),
                'effective_until' => $data['effective_until'] ?? ($policy ? $policy->effective_until : null),
                'updated_by' => $currentUserId,
            ];

            foreach ($rulesCategories as $cat) {
                if (isset($data[$cat]) && is_array($data[$cat])) {
                    $updatePayload[$cat] = $data[$cat];
                } elseif ($isNew) {
                    $defaults = PayrollPolicy::getDefaultRules();
                    $updatePayload[$cat] = $defaults[$cat] ?? [];
                }
            }

            if ($isNew) {
                $updatePayload['code'] = 'POL-' . strtoupper(substr(md5(uniqid()), 0, 6));
                $updatePayload['version'] = 1;
                $updatePayload['created_by'] = $currentUserId;
                $policy = PayrollPolicy::create($updatePayload);
            } else {
                $updatePayload['version'] = $policy->version + 1;
                $policy->update($updatePayload);
            }

            // Save history snapshot
            PayrollPolicyHistory::create([
                'payroll_policy_id' => $policy->id,
                'version' => $policy->version,
                'changes_summary' => [
                    'action' => $isNew ? 'Policy Created' : 'Policy Updated',
                    'updated_fields' => array_keys($updatePayload),
                ],
                'snapshot' => $policy->fresh()->toArray(),
                'changed_by' => $currentUserId,
            ]);

            // Audit log integration if model exists
            try {
                if (class_exists(AuditLog::class)) {
                    AuditLog::create([
                        'user_id' => $currentUserId,
                        'event' => $isNew ? 'policy_created' : 'policy_updated',
                        'auditable_type' => PayrollPolicy::class,
                        'auditable_id' => $policy->id,
                        'old_values' => json_encode($oldSnapshot),
                        'new_values' => json_encode($policy->toArray()),
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore audit log error if table differs
            }

            return $policy;
        }
    );
    }

    /**
     * Simulate policy impact for an employee without saving to DB.
     */
    public function simulatePolicyImpact(User $user, array $simulatedRules, int $year, int $month, int $workingDays = 22): array
    {
        $calcService = app(PayrollCalculationService::class);

        // 1. Current Active Policy Result
        $currentPolicy = $this->getActivePolicy($user->company_id);
        $currentLine = $calcService->calculateEmployeePayrollLine($user, $year, $month, $workingDays);

        // 2. Compute Simulated Policy Line
        $simBasic = (float) ($currentLine['basic'] ?? 6000);
        $simHra = (float) ($currentLine['hra'] ?? 3000);
        $simSpecial = (float) ($currentLine['special'] ?? 3000);

        // Override using simulated earnings rules if passed
        $earnRules = $simulatedRules['salary_earnings_rules'] ?? $currentPolicy->salary_earnings_rules;
        if (!empty($earnRules['basic_percentage'])) {
            $tot = $simBasic + $simHra + $simSpecial;
            $simBasic = round($tot * ($earnRules['basic_percentage'] / 100), 2);
            $simHra = round($simBasic * (($earnRules['hra_percentage'] ?? 40) / 100), 2);
            $simSpecial = round(max(0, $tot - ($simBasic + $simHra)), 2);
        }

        $payableDays = (float) ($currentLine['payable_days'] ?? $workingDays);
        $ratio = $workingDays > 0 ? ($payableDays / $workingDays) : 1.0;

        $simAcBasic = round($simBasic * $ratio, 2);
        $simAcHra = round($simHra * $ratio, 2);
        $simAcSpecial = round($simSpecial * $ratio, 2);
        $simAcGross = round($simAcBasic + $simAcHra + $simAcSpecial, 2);

        // Overtime Calculation
        $otRules = $simulatedRules['overtime_rules'] ?? $currentPolicy->overtime_rules;
        $simOvertimePay = 0.0;
        if (!empty($otRules['enabled'])) {
            $hourlyRate = ($simAcGross / ($workingDays * 8));
            $mult = (float) ($otRules['normal_multiplier'] ?? 1.0);
            $simOvertimePay = round(5 * $hourlyRate * $mult, 2); // Sample 5 OT hours
        }

        // Deductions (PF/ESI/PT)
        $dedRules = $simulatedRules['deductions_rules'] ?? $currentPolicy->deductions_rules;
        $simPf = (!empty($dedRules['pf_enabled'])) ? min((float)($dedRules['pf_max_limit'] ?? 1800), round($simAcBasic * (($dedRules['pf_percentage'] ?? 12) / 100), 2)) : 0.0;
        $simEsi = (!empty($dedRules['esi_enabled']) && $simAcGross <= 21000) ? round($simAcGross * (($dedRules['esi_percentage'] ?? 0.75) / 100), 2) : 0.0;
        $simPt = (!empty($dedRules['pt_enabled'])) ? (float) ($dedRules['pt_fixed_amount'] ?? 200) : 0.0;
        $simTotalDeductions = round($simPf + $simEsi + $simPt, 2);

        $simNetSalary = round($simAcGross + $simOvertimePay - $simTotalDeductions, 2);

        // Current Net Calculation for Comparison
        $currAcGross = (float) ($currentLine['ac_gross'] ?? $simAcGross);
        $currDeductions = round(($currAcGross * 0.12) + 200, 2);
        $currNetSalary = round($currAcGross - $currDeductions, 2);

        $diff = round($simNetSalary - $currNetSalary, 2);

        return [
            'employee_name' => $user->name,
            'employee_id' => $currentLine['employee_id'] ?? ('EMP-' . $user->id),
            'period' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            'current' => [
                'basic' => $currentLine['ac_basic'],
                'hra' => $currentLine['ac_hra'],
                'special' => $currentLine['ac_special'],
                'gross' => $currAcGross,
                'deductions' => $currDeductions,
                'net_salary' => $currNetSalary,
            ],
            'simulated' => [
                'basic' => $simAcBasic,
                'hra' => $simAcHra,
                'special' => $simAcSpecial,
                'gross' => $simAcGross,
                'overtime' => $simOvertimePay,
                'deductions' => $simTotalDeductions,
                'net_salary' => $simNetSalary,
            ],
            'difference' => $diff,
            'is_positive' => $diff >= 0,
        ];
    }
}
