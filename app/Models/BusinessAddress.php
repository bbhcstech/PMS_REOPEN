<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAddress extends TenantModel
{
    use HasFactory;

    protected $table = 'business_addresses'; // safe & explicit

    protected $fillable = [
        'location',
        'address',
        'country',
        'tax_name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
