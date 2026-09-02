<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollArchitecture extends TenantModel
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'settings' => 'array',
    ];

    public function versions()
    {
        return $this->hasMany(PayrollArchitectureVersion::class);
    }
}
