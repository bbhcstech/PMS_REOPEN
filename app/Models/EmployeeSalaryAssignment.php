<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalaryAssignment extends TenantModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'actual_basic_salary' => 'decimal:2',
        'hra_value' => 'decimal:2',
        'special_allowance_value' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Computed HRA amount based on actual basic salary
     */
    public function getComputedHraAttribute(): float
    {
        $basic = (float) $this->actual_basic_salary;
        if ($this->hra_type === 'fixed') {
            return (float) $this->hra_value;
        }
        return round($basic * ((float) $this->hra_value / 100), 2);
    }

    /**
     * Computed Special Allowance amount based on actual basic salary
     */
    public function getComputedSpecialAllowanceAttribute(): float
    {
        $basic = (float) $this->actual_basic_salary;
        if ($this->special_allowance_type === 'fixed') {
            return (float) $this->special_allowance_value;
        }
        return round($basic * ((float) $this->special_allowance_value / 100), 2);
    }

    /**
     * Computed Gross Salary
     */
    public function getComputedGrossAttribute(): float
    {
        return round((float) $this->actual_basic_salary + $this->computed_hra + $this->computed_special_allowance, 2);
    }
}
