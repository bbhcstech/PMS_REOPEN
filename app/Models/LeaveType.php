<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'annual_limit',
        'is_paid',
        'requires_document',
        'allows_apology',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'annual_limit' => 'decimal:2',
        'is_paid' => 'boolean',
        'requires_document' => 'boolean',
        'allows_apology' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}
