<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class PayrollReport extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
        'generated_at' => 'datetime',
    ];
}
