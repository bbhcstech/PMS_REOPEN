<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollArchitecture extends Model
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
