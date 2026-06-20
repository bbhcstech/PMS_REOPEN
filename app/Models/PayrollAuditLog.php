<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAuditLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
