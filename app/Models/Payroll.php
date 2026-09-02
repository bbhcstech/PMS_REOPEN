<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends TenantModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'locked_at' => 'datetime',
        'attendance_summary' => 'array',
        'metadata' => 'array',
    ];

    public function cycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
    }

    public function items()
    {
        return $this->hasMany(PayrollHistory::class, 'payroll_id');
    }

    public function histories()
    {
        return $this->hasMany(PayrollHistory::class, 'payroll_id');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'payroll_id');
    }

    public function approvals()
    {
        return $this->hasMany(PayrollApproval::class, 'payroll_id');
    }
}
