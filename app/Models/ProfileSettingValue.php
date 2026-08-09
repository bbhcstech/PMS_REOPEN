<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class ProfileSettingValue extends TenantModel
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];
}
