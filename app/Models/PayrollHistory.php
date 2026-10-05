<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class PayrollHistory extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class, 'payroll_history_id');
    }
}

