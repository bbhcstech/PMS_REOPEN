<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class PayrollAuditLog extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
