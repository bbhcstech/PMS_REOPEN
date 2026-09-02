<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class TicketGroup extends TenantModel
{
    protected $fillable = [
        'group_name'
    ];
}
