<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class PayrollImportLog extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'processed_at' => 'datetime',
    ];
}
