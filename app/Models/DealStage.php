<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealStage extends TenantModel
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'order', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
