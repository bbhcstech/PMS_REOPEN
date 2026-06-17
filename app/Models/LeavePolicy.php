<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    protected $fillable = [
        'annual_leaves',
        'sick_leave_limit',
        'casual_leave_limit',
        'maternity_leave_limit',
        'leave_year_start_month',
        'leave_year_end_month',
        'casual_advance_days',
        'casual_manual_review_days',
        'auto_approve_casual_leave',
        'hr_approval_required',
        'allow_sick_apology',
        'unpaid_leave_handling',
        'maternity_is_paid',
        'maternity_requires_document',
        'pro_rate_enabled',
        'fiscal_year_start',
        'fiscal_year_end',
        'allow_carry_forward',
        'max_carry_forward',
        'leave_monetary_value'
    ];

    protected $casts = [
        'pro_rate_enabled' => 'boolean',
        'allow_carry_forward' => 'boolean',
        'auto_approve_casual_leave' => 'boolean',
        'hr_approval_required' => 'boolean',
        'allow_sick_apology' => 'boolean',
        'maternity_is_paid' => 'boolean',
        'maternity_requires_document' => 'boolean',
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
    ];

    public function logs()
    {
        return $this->hasMany(LeavePolicyLog::class);
    }
}
