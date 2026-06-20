<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayslipTemplate extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];
}
