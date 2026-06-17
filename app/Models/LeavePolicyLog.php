<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicyLog extends Model
{
    protected $fillable = [
        'leave_policy_id',
        'changed_by',
        'before_snapshot',
        'after_snapshot',
    ];

    protected $casts = [
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
    ];
}
