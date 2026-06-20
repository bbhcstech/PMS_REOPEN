<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'template_version_snapshot' => 'array',
        'company_snapshot' => 'array',
        'employee_snapshot' => 'array',
        'earnings' => 'array',
        'deductions' => 'array',
        'taxes' => 'array',
        'generated_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
