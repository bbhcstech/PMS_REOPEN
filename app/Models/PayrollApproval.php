<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollApproval extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
