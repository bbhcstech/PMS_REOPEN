<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollImportLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'processed_at' => 'datetime',
    ];
}
