<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends TenantModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'basic_salary' => 'decimal:2',
        'hra_value' => 'decimal:2',
        'special_allowance_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $base = !empty($model->name) ? \Illuminate\Support\Str::slug($model->name) : 'SS';
                $model->code = $base . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        });
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function assignments()
    {
        return $this->hasMany(EmployeeSalaryAssignment::class, 'salary_structure_id');
    }

    public function components()
    {
        return $this->hasMany(SalaryComponent::class)->orderBy('sort_order')->orderBy('name');
    }

    public function getComputedHraAttribute(): float
    {
        $basic = (float) $this->basic_salary;
        if ($this->hra_type === 'fixed') {
            return (float) $this->hra_value;
        }
        return round($basic * ((float) ($this->hra_value ?: 50) / 100), 2);
    }

    public function getComputedSpecialAllowanceAttribute(): float
    {
        $basic = (float) $this->basic_salary;
        if ($this->special_allowance_type === 'fixed') {
            return (float) $this->special_allowance_value;
        }
        return round($basic * ((float) ($this->special_allowance_value ?: 50) / 100), 2);
    }

    public function getComputedGrossAttribute(): float
    {
        return round((float) $this->basic_salary + $this->computed_hra + $this->computed_special_allowance, 2);
    }
}
