<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
        'generated_at' => 'datetime',
    ];
}
