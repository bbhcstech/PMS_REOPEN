<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessAddress extends TenantModel
{
    use HasFactory;

    protected $table = 'business_addresses'; // safe & explicit

    protected $fillable = [
        'branch_name',
        'location',
        'email',
        'phone',
        'address',
        'country',
        'tax_name',
        'logo',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->branch_name ?: ($this->location ?: 'Branch Office');
    }
}
