<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructureVersion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'effective_date' => 'date',
    ];
}
