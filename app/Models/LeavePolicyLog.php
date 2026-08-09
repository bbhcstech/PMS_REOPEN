<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class LeavePolicyLog extends TenantModel
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
