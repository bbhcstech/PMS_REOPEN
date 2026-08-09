<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class SalaryStructureVersion extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'effective_date' => 'date',
    ];
}
