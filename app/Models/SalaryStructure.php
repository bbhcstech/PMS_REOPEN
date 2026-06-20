<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    public function components()
    {
        return $this->hasMany(SalaryComponent::class)->orderBy('sort_order')->orderBy('name');
    }
}
