<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class ProfileSetting extends TenantModel
{
    protected $fillable = [
        'key',
        'label',
        'type',
        'options',
        'required',
        'visible',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'visible' => 'boolean',
    ];
}
