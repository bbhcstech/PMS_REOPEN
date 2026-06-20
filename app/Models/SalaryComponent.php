<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'taxable' => 'boolean',
        'required' => 'boolean',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'metadata' => 'array',
    ];

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class);
    }
}
