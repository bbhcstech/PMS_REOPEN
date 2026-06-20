<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
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
}
