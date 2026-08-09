<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class PayrollArchive extends TenantModel
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'archived_at' => 'datetime',
    ];
}
