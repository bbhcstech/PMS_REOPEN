<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceLog;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyLog;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function policy(): LeavePolicy
    {
        $year = $this->leaveYearDates();

        return LeavePolicy::firstOrCreate([], [
            'annual_leaves' => 18,
            'sick_leave_limit' => 6,
            'casual_leave_limit' => 12,
            'maternity_leave_limit' => 0,
            'leave_year_start_month' => 4,
            'leave_year_end_month' => 3,
            'casual_advance_days' => 7,
            'casual_manual_review_days' => 2,
            'auto_approve_casual_leave' => false,
            'hr_approval_required' => true,
            'allow_sick_apology' => true,
            'unpaid_leave_handling' => 'unpaid_leave',
            'maternity_is_paid' => true,
            'maternity_requires_document' => true,
            'pro_rate_enabled' => false,
            'fiscal_year_start' => $year['start']->toDateString(),
            'fiscal_year_end' => $year['end']->toDateString(),
            'allow_carry_forward' => false,
            'max_carry_forward' => 0,
            'leave_monetary_value' => 0,
        ]);
    }

    public function ensureDefaultTypes(): void
    {
        $this->syncLeaveTypesWithPolicy($this->policy());
    }

    public function leaveYearDates(?Carbon $date = null): array
    {
        $date = ($date ?: now())->copy();
        $policy = LeavePolicy::first();
        $startMonth = (int) ($policy->leave_year_start_month ?? 4);
        $endMonth = (int) ($policy->leave_year_end_month ?? 3);
        $startYear = $date->month >= $startMonth ? $date->year : $date->year - 1;
        $endYear = $startMonth > $endMonth ? $startYear + 1 : $startYear;

        return [
            'start' => Carbon::create($startYear, $startMonth, 1)->startOfDay(),
            'end' => Carbon::create($endYear, $endMonth, 1)->endOfMonth()->endOfDay(),
            'label' => $startYear . '-' . $endYear,
            'year' => $startYear,
        ];
    }

    public function leaveTypes()
    {
        $this->ensureDefaultTypes();

        return LeaveType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function ensureBalance(User $employee, ?Carbon $date = null): LeaveBalance
    {
        $policy = $this->policy();
        $year = $this->leaveYearDates($date);
        $balance = LeaveBalance::firstOrCreate(
            ['user_id' => $employee->id, 'year' => $year['year']],
            [
                'leave_year' => $year['label'],
                'year_start' => $year['start']->toDateString(),
                'year_end' => $year['end']->toDateString(),
                'allocated_leaves' => $policy->annual_leaves,
                'remaining_leaves' => $policy->annual_leaves,
                'used_leaves' => 0,
                'carried_forward' => 0,
                'sick_allocated' => $policy->sick_leave_limit,
                'casual_allocated' => $policy->casual_leave_limit,
                'maternity_allocated' => $policy->maternity_leave_limit,
            ]
        );

        $this->syncBalanceCounters($employee, $balance);

        return $balance->refresh();
    }

    public function syncBalanceCounters(User $employee, ?LeaveBalance $balance = null): LeaveBalance
    {
        $balance = $balance ?: $this->ensureBalance($employee);
        $yearStart = Carbon::parse($balance->year_start);
        $yearEnd = Carbon::parse($balance->year_end);

        $approved = Leave::where('user_id', $employee->id)
            ->whereIn('status', ['approved'])
            ->where(function ($query) use ($yearStart, $yearEnd) {
                $query->whereBetween('start_date', [$yearStart, $yearEnd])
                    ->orWhereBetween('end_date', [$yearStart, $yearEnd]);
            })
            ->orderBy('approved_at')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $approved = $this->normalizeApprovedLeavePayroll($approved, $balance);

        $usedByCode = ['SL' => 0.0, 'CL' => 0.0, 'ML' => 0.0, 'UL' => 0.0];
        $unpaidUsed = 0.0;

        foreach ($approved as $leave) {
            $code = $this->typeCode($leave);
            $paidDays = (float) ($leave->paid_days ?? 0);
            $unpaidDays = (float) ($leave->unpaid_days ?? 0);

            if ($code === 'UL') {
                $unpaidUsed += $paidDays + $unpaidDays;
                continue;
            }

            $usedByCode[$code] = ($usedByCode[$code] ?? 0) + $paidDays;
            $unpaidUsed += $unpaidDays;
        }

        $pending = Leave::where('user_id', $employee->id)->where('status', 'pending')->count();
        $approvedCount = Leave::where('user_id', $employee->id)->where('status', 'approved')->count();
        $rejected = Leave::where('user_id', $employee->id)->where('status', 'rejected')->count();

        $paidUsed = $usedByCode['SL'] + $usedByCode['CL'] + $usedByCode['ML'];
        $allocated = (float) $balance->allocated_leaves;

        $balance->forceFill([
            'sick_used' => $usedByCode['SL'],
            'casual_used' => $usedByCode['CL'],
            'maternity_used' => $usedByCode['ML'],
            'unpaid_used' => $unpaidUsed,
            'used_leaves' => min($allocated, $paidUsed),
            'remaining_leaves' => max(0, $allocated - $paidUsed),
            'pending_requests' => $pending,
            'approved_requests' => $approvedCount,
            'rejected_requests' => $rejected,
        ])->save();

        $employee->forceFill([
            'annual_leave_balance' => (int) round($balance->allocated_leaves),
            'remaining_leaves' => (int) floor($balance->remaining_leaves),
            'leaves_taken_this_year' => (int) ceil($balance->used_leaves),
            'last_leave_reset' => $balance->year_start,
        ])->save();

        return $balance;
    }

    public function calculateDays(Carbon $start, Carbon $end, bool $halfDay = false): float
    {
        if ($halfDay) {
            return 0.5;
        }

        return (float) ($start->diffInDays($end) + 1);
    }

    public function validateRequest(User $employee, LeaveType $type, Carbon $start, Carbon $end, array $data): array
    {
        $errors = [];
        $policy = $this->policy();
        $days = $this->calculateDays($start, $end, (bool) ($data['half_day_flag'] ?? false));

        $overlap = Leave::where('user_id', $employee->id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($overlap) {
            $errors[] = 'A leave request already exists for the selected date range.';
        }

        $holidayDates = Holiday::whereNull('archived_at')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->all();

        if ($holidayDates) {
            $errors[] = 'Selected range includes organization holiday(s): ' . implode(', ', $holidayDates) . '.';
        }

        if ($type->code === 'CL') {
            $advanceDays = now()->startOfDay()->diffInDays($start->copy()->startOfDay(), false);
            if ($advanceDays < 0) {
                $errors[] = 'Casual Leave cannot be applied for past dates.';
            } elseif ($advanceDays < (int) $policy->casual_manual_review_days && empty($data['emergency_flag'])) {
                $errors[] = 'Casual Leave within ' . $policy->casual_manual_review_days . ' days must be marked as emergency for HR review.';
            }
        }

        if ($type->code === 'SL' && $start->lt(now()->startOfDay()) && $policy->allow_sick_apology && empty($data['apology_note'])) {
            $errors[] = 'Past Sick Leave requires an apology or regularization note.';
        }

        if ($type->requires_document && empty($data['attachment'])) {
            $errors[] = $type->name . ' requires a supporting document.';
        }

        if ($days <= 0) {
            $errors[] = 'Leave duration must be at least half day.';
        }

        return $errors;
    }

    public function createLeave(User $employee, LeaveType $type, array $data, ?User $actor = null): Leave
    {
        return DB::transaction(function () use ($employee, $type, $data, $actor) {
            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);
            $days = $this->calculateDays($start, $end, (bool) ($data['half_day_flag'] ?? false));
            $policy = $this->policy();
            $year = $this->leaveYearDates($start);
            $balance = $this->ensureBalance($employee, $start);
            $status = $data['status'] ?? 'pending';

            if ($status === 'pending' && $this->shouldAutoApprove($type, $start, $data, $policy)) {
                $status = 'approved';
            }

            $paidPlan = $this->paidPlan($balance, $type, $days);
            $isUnpaid = $type->code === 'UL' || $paidPlan['unpaid_days'] > 0;

            $leave = Leave::create([
                'user_id' => $employee->id,
                'leave_type_id' => $type->id,
                'type' => $this->legacyType($type),
                'duration' => ($data['half_day_flag'] ?? false) ? 'half_day' : ($days > 1 ? 'multiple' : 'full_day'),
                'date' => $start->toDateString(),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'total_days' => $days,
                'paid_days' => $paidPlan['paid_days'],
                'unpaid_days' => $paidPlan['unpaid_days'],
                'reason' => $data['reason'] ?? null,
                'files' => $data['attachment'] ?? null,
                'attachment' => $data['attachment'] ?? null,
                'apology_note' => $data['apology_note'] ?? null,
                'emergency_flag' => (bool) ($data['emergency_flag'] ?? false),
                'half_day_flag' => (bool) ($data['half_day_flag'] ?? false),
                'contact_during_leave' => $data['contact_during_leave'] ?? null,
                'status' => $status,
                'approval_status' => $status,
                'is_paid' => ! $isUnpaid,
                'is_unpaid' => $isUnpaid,
                'paid' => ! $isUnpaid,
                'payroll_deduction_flag' => $isUnpaid,
                'leave_year' => $year['label'],
            ]);

            $this->recordApproval($leave, $actor, $status === 'approved' ? 'auto_approved' : 'submitted', $data['admin_note'] ?? null, [
                'paid_days' => $paidPlan['paid_days'],
                'unpaid_days' => $paidPlan['unpaid_days'],
            ]);

            if ($status === 'approved') {
                $this->applyApproval($leave, $actor, $data['admin_note'] ?? null);
            }

            $this->syncBalanceCounters($employee, $balance);

            return $leave;
        });
    }

    public function approve(Leave $leave, User $actor, ?string $note = null, bool $forceUnpaid = false): Leave
    {
        return DB::transaction(function () use ($leave, $actor, $note, $forceUnpaid) {
            if ($forceUnpaid) {
                $leave->forceFill([
                    'leave_type_id' => LeaveType::where('code', 'UL')->value('id') ?: $leave->leave_type_id,
                    'type' => 'leave-without-pay',
                    'is_paid' => false,
                    'is_unpaid' => true,
                    'paid' => false,
                    'paid_days' => 0,
                    'unpaid_days' => (float) ($leave->total_days ?: 1),
                    'payroll_deduction_flag' => true,
                ]);
            }

            $leave->forceFill([
                'status' => 'approved',
                'approval_status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'admin_note' => $note,
            ])->save();

            $this->applyApproval($leave, $actor, $note);
            $this->recordApproval($leave, $actor, $forceUnpaid ? 'converted_to_unpaid' : 'approved', $note);

            return $leave->refresh();
        });
    }

    public function reject(Leave $leave, User $actor, string $reason): Leave
    {
        return DB::transaction(function () use ($leave, $actor, $reason) {
            $leave->forceFill([
                'status' => 'rejected',
                'approval_status' => 'rejected',
                'rejected_by' => $actor->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'admin_note' => $reason,
            ])->save();

            $this->recordApproval($leave, $actor, 'rejected', $reason);
            $this->syncBalanceCounters($leave->user);

            return $leave->refresh();
        });
    }

    public function applyApproval(Leave $leave, ?User $actor = null, ?string $note = null): void
    {
        $employee = $leave->user;
        if (! $employee) {
            return;
        }

        $before = $this->ensureBalance($employee, Carbon::parse($leave->start_date))->toArray();
        $balance = LeaveBalance::where('user_id', $employee->id)
            ->where('leave_year', $leave->leave_year ?: $this->leaveYearDates(Carbon::parse($leave->start_date))['label'])
            ->first();

        if ($balance) {
            $this->normalizeApprovedLeavePayroll(
                Leave::where('user_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('leave_year', $balance->leave_year)
                    ->orderBy('approved_at')
                    ->orderBy('start_date')
                    ->orderBy('id')
                    ->get(),
                $balance
            );
            $leave->refresh();
        }

        foreach (CarbonPeriod::create($leave->start_date, $leave->end_date) as $date) {
            Attendance::updateOrCreate(
                ['user_id' => $employee->id, 'date' => $date->toDateString()],
                [
                    'status' => ((float) ($leave->paid_days ?? 0)) <= 0 ? 'unpaid_leave' : 'leave',
                    'location' => 'Leave',
                    'working_from' => 'Leave',
                ]
            );
        }

        $after = $this->syncBalanceCounters($employee)->toArray();

        LeaveBalanceLog::create([
            'user_id' => $employee->id,
            'leave_id' => $leave->id,
            'changed_by' => $actor?->id,
            'leave_year' => $leave->leave_year ?: $this->leaveYearDates(Carbon::parse($leave->start_date))['label'],
            'action' => 'approved',
            'days' => $leave->total_days,
            'before_snapshot' => $before,
            'after_snapshot' => $after,
            'note' => $note,
        ]);
    }

    public function updatePolicy(array $data, User $actor): LeavePolicy
    {
        return DB::transaction(function () use ($data, $actor) {
            $policy = $this->policy();
            $before = $policy->toArray();
            $policy->fill($data)->save();
            $policy = $policy->fresh();

            LeavePolicyLog::create([
                'leave_policy_id' => $policy->id,
                'changed_by' => $actor->id,
                'before_snapshot' => $before,
                'after_snapshot' => $policy->toArray(),
            ]);

            $this->syncLeaveTypesWithPolicy($policy);

            User::where('role', 'employee')->chunkById(100, function ($employees) use ($policy) {
                foreach ($employees as $employee) {
                    $this->syncEmployeePolicyBalances($employee, $policy);
                }
            });

            return $policy;
        });
    }

    public function policyNotice(?LeavePolicy $policy = null): string
    {
        $policy = $policy ?: $this->policy();

        return "Organization Leave Policy: The leave year runs from April to March. Every employee receives {$policy->annual_leaves} paid leaves per year: {$policy->sick_leave_limit} Sick Leaves and {$policy->casual_leave_limit} Casual Leaves. Maternity Leave is currently {$policy->maternity_leave_limit} by default and may be configured by HR. Sick Leave can be applied on the same day or after absence with an apology request. Casual Leave should be applied at least {$policy->casual_advance_days} days in advance. If Casual Leave is applied within a shorter period, HR approval is required based on organization needs. After all paid leaves are used, further leave will be treated as unpaid leave or absent as per HR decision.";
    }

    public function typeCode(Leave $leave): string
    {
        if ($leave->leaveType?->code) {
            return $leave->leaveType->code;
        }

        return match ($leave->type) {
            'sick', 'sick_leave' => 'SL',
            'casual', 'casual_leave' => 'CL',
            'maternity', 'maternity_leave' => 'ML',
            'leave-without-pay', 'unpaid', 'unpaid_leave' => 'UL',
            default => 'CL',
        };
    }

    private function shouldAutoApprove(LeaveType $type, Carbon $start, array $data, LeavePolicy $policy): bool
    {
        if ($policy->hr_approval_required) {
            return false;
        }

        if ($type->code === 'CL') {
            $advanceDays = now()->startOfDay()->diffInDays($start->copy()->startOfDay(), false);
            return $policy->auto_approve_casual_leave && $advanceDays >= (int) $policy->casual_advance_days;
        }

        return false;
    }

    private function paidPlan(LeaveBalance $balance, LeaveType $type, float $days): array
    {
        if (! $type->is_paid || $type->code === 'UL') {
            return ['paid_days' => 0, 'unpaid_days' => $days];
        }

        $typeRemaining = match ($type->code) {
            'SL' => max(0, (float) $balance->sick_allocated - (float) $balance->sick_used),
            'CL' => max(0, (float) $balance->casual_allocated - (float) $balance->casual_used),
            'ML' => max(0, (float) $balance->maternity_allocated - (float) $balance->maternity_used),
            default => max(0, (float) $balance->remaining_leaves),
        };
        $totalRemaining = max(0, (float) $balance->allocated_leaves - (float) $balance->used_leaves);
        $remaining = min($typeRemaining, $totalRemaining);

        $paid = min($remaining, $days);

        return ['paid_days' => $paid, 'unpaid_days' => max(0, $days - $paid)];
    }

    private function syncLeaveTypesWithPolicy(LeavePolicy $policy): void
    {
        $types = [
            ['code' => 'SL', 'name' => 'Sick Leave', 'annual_limit' => $policy->sick_leave_limit, 'is_paid' => true, 'requires_document' => false, 'allows_apology' => $policy->allow_sick_apology, 'sort_order' => 1],
            ['code' => 'CL', 'name' => 'Casual Leave', 'annual_limit' => $policy->casual_leave_limit, 'is_paid' => true, 'requires_document' => false, 'allows_apology' => false, 'sort_order' => 2],
            ['code' => 'ML', 'name' => 'Maternity Leave', 'annual_limit' => $policy->maternity_leave_limit, 'is_paid' => $policy->maternity_is_paid, 'requires_document' => $policy->maternity_requires_document, 'allows_apology' => false, 'sort_order' => 3],
            ['code' => 'UL', 'name' => 'Unpaid Leave', 'annual_limit' => 0, 'is_paid' => false, 'requires_document' => false, 'allows_apology' => false, 'sort_order' => 4],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type + ['is_active' => true]);
        }
    }

    private function syncEmployeePolicyBalances(User $employee, LeavePolicy $policy): void
    {
        $dates = collect([now()]);

        Leave::where('user_id', $employee->id)
            ->whereNotNull('start_date')
            ->pluck('start_date')
            ->each(fn ($date) => $dates->push(Carbon::parse($date)));

        LeaveBalance::where('user_id', $employee->id)
            ->pluck('year_start')
            ->each(fn ($date) => $dates->push(Carbon::parse($date)));

        $leaveYears = $dates
            ->map(fn ($date) => $this->leaveYearDates($date instanceof Carbon ? $date : Carbon::parse($date)))
            ->unique('year')
            ->values();

        foreach ($leaveYears as $year) {
            $balance = LeaveBalance::firstOrCreate(
                ['user_id' => $employee->id, 'year' => $year['year']],
                [
                    'leave_year' => $year['label'],
                    'year_start' => $year['start']->toDateString(),
                    'year_end' => $year['end']->toDateString(),
                    'remaining_leaves' => $policy->annual_leaves,
                    'used_leaves' => 0,
                    'carried_forward' => 0,
                ]
            );

            $balance->forceFill([
                'leave_year' => $year['label'],
                'year_start' => $year['start']->toDateString(),
                'year_end' => $year['end']->toDateString(),
                'allocated_leaves' => $policy->annual_leaves,
                'sick_allocated' => $policy->sick_leave_limit,
                'casual_allocated' => $policy->casual_leave_limit,
                'maternity_allocated' => $policy->maternity_leave_limit,
            ])->save();

            $this->syncBalanceCounters($employee, $balance);
        }
    }

    private function normalizeApprovedLeavePayroll($approvedLeaves, LeaveBalance $balance)
    {
        $remainingTotal = max(0, (float) $balance->allocated_leaves);
        $remainingByCode = [
            'SL' => max(0, (float) $balance->sick_allocated),
            'CL' => max(0, (float) $balance->casual_allocated),
            'ML' => max(0, (float) $balance->maternity_allocated),
        ];

        foreach ($approvedLeaves as $leave) {
            $days = (float) ($leave->total_days ?: 1);
            $code = $this->typeCode($leave);
            $typeIsPaid = (bool) ($leave->leaveType?->is_paid ?? ($code !== 'UL'));

            if (! $typeIsPaid || $code === 'UL') {
                $paidDays = 0.0;
                $unpaidDays = $days;
            } else {
                $codeRemaining = $remainingByCode[$code] ?? $remainingTotal;
                $availablePaidDays = min($remainingTotal, max(0, $codeRemaining));
                $paidDays = min($days, $availablePaidDays);
                $unpaidDays = max(0, $days - $paidDays);
                $remainingTotal = max(0, $remainingTotal - $paidDays);

                if (array_key_exists($code, $remainingByCode)) {
                    $remainingByCode[$code] = max(0, $remainingByCode[$code] - $paidDays);
                }
            }

            $isFullyPaid = $paidDays > 0 && $unpaidDays <= 0;
            $hasUnpaidDays = $unpaidDays > 0;

            $leave->forceFill([
                'paid_days' => $paidDays,
                'unpaid_days' => $unpaidDays,
                'is_paid' => $isFullyPaid,
                'is_unpaid' => $hasUnpaidDays,
                'paid' => $isFullyPaid,
                'payroll_deduction_flag' => $hasUnpaidDays,
            ]);

            if ($leave->isDirty()) {
                $leave->save();
            }
        }

        return $approvedLeaves;
    }

    private function legacyType(LeaveType $type): string
    {
        return match ($type->code) {
            'SL' => 'sick',
            'CL' => 'casual',
            'ML' => 'maternity',
            'UL' => 'leave-without-pay',
            default => strtolower($type->code),
        };
    }

    private function recordApproval(Leave $leave, ?User $actor, string $action, ?string $note = null, array $meta = []): void
    {
        LeaveApproval::create([
            'leave_id' => $leave->id,
            'user_id' => $actor?->id,
            'action' => $action,
            'note' => $note,
            'meta' => $meta ?: null,
        ]);
    }
}
