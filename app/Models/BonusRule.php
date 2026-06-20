<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonusRule extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'approval_flow' => 'array',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];
}
