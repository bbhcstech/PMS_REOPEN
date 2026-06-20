<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollExportLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'exported_at' => 'datetime',
    ];
}
