<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRule extends TenantModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'slabs' => 'array',
        'exemptions' => 'array',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];
}
