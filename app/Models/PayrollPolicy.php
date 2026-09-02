<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payroll_policies';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'status',
        'version',
        'is_default',
        'effective_from',
        'effective_until',
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
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'version' => 'integer',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'salary_earnings_rules' => 'array',
        'working_days_rules' => 'array',
        'leave_absence_rules' => 'array',
        'overtime_rules' => 'array',
        'deductions_rules' => 'array',
        'tax_rules' => 'array',
        'bonus_rules' => 'array',
        'attendance_rules' => 'array',
        'processing_rules' => 'array',
        'rounding_rules' => 'array',
        'payslip_rules' => 'array',
        'compliance_rules' => 'array',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories()
    {
        return $this->hasMany(PayrollPolicyHistory::class, 'payroll_policy_id')->orderBy('version', 'desc');
    }

    // Default Fallbacks for each Category
    public static function getDefaultRules(): array
    {
        return [
            'salary_earnings_rules' => [
                'basic_percentage' => 50,
                'hra_percentage' => 40,
                'special_allowance_mode' => 'fixed',
                'special_allowance_default' => 3000,
                'formula_type' => 'standard',
            ],
            'working_days_rules' => [
                'calculation_method' => 'fixed',
                'standard_working_days' => 22,
                'weekly_off' => ['Saturday', 'Sunday'],
                'holiday_treatment' => 'paid',
            ],
            'leave_absence_rules' => [
                'full_day_leave' => 'paid',
                'half_day_leave' => 'paid',
                'leave_beyond_balance' => 'unpaid',
                'deduction_method' => 'per_day_gross',
                'absence_full_day' => 'deduct',
                'absence_half_day' => 'deduct_half',
            ],
            'overtime_rules' => [
                'enabled' => true,
                'calculation_basis' => 'hourly_gross',
                'normal_multiplier' => 1.0,
                'weekend_multiplier' => 1.5,
                'holiday_multiplier' => 2.0,
            ],
            'deductions_rules' => [
                'pf_enabled' => true,
                'pf_percentage' => 12.0,
                'pf_max_limit' => 1800,
                'esi_enabled' => true,
                'esi_percentage' => 0.75,
                'pt_enabled' => true,
                'pt_fixed_amount' => 200,
                'custom_rules' => [],
            ],
            'tax_rules' => [
                'enabled' => true,
                'method' => 'slab_based',
                'slabs' => [
                    ['min' => 0, 'max' => 300000, 'rate' => 0],
                    ['min' => 300001, 'max' => 600000, 'rate' => 5],
                    ['min' => 600001, 'max' => 1200000, 'rate' => 10],
                    ['min' => 1200001, 'max' => 99999999, 'rate' => 20],
                ],
            ],
            'bonus_rules' => [
                'enabled' => true,
                'bonus_type' => 'percentage_basic',
                'percentage' => 10,
                'frequency' => 'yearly',
                'min_service_months' => 6,
            ],
            'attendance_rules' => [
                'late_grace_minutes' => 15,
                'late_deduction_enabled' => true,
                'early_exit_deduction' => true,
                'missing_attendance' => 'manual_review',
                'lock_date' => 25,
            ],
            'processing_rules' => [
                'frequency' => 'monthly',
                'period_from_day' => 1,
                'period_to_day' => 30,
                'payment_date_day' => 5,
                'approval_required' => true,
                'allow_recalculation' => true,
                'lock_after_approval' => true,
            ],
            'rounding_rules' => [
                'rounding_mode' => 'nearest_rupee',
                'decimal_precision' => 2,
            ],
            'payslip_rules' => [
                'show_emp_id' => true,
                'show_dept' => true,
                'show_designation' => true,
                'show_attendance' => true,
                'show_leave' => true,
                'show_earnings' => true,
                'show_deductions' => true,
                'show_net_salary' => true,
                'footer_text' => 'This is a computer-generated payslip. Signature not required.',
                'authorized_by' => 'HR & Payroll Manager',
            ],
            'compliance_rules' => [
                'pf_compliance' => true,
                'esi_compliance' => true,
                'pt_compliance' => true,
                'min_wage_check' => true,
            ],
        ];
    }
}
