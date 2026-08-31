<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPolicyHistory extends Model
{
    use HasFactory;

    protected $table = 'payroll_policy_histories';

    protected $fillable = [
        'payroll_policy_id',
        'version',
        'changes_summary',
        'snapshot',
        'changed_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'changes_summary' => 'array',
        'snapshot' => 'array',
    ];

    public function policy()
    {
        return $this->belongsTo(PayrollPolicy::class, 'payroll_policy_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
