<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollArchive extends Model
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'archived_at' => 'datetime',
    ];
}
