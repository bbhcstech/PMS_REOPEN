<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class PayrollExportLog extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'exported_at' => 'datetime',
    ];
}
