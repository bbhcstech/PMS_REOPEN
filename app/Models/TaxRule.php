<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRule extends Model
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
