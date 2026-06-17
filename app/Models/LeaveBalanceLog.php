<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalanceLog extends Model
{
    protected $fillable = [
        'user_id',
        'leave_id',
        'changed_by',
        'leave_year',
        'action',
        'days',
        'before_snapshot',
        'after_snapshot',
        'note',
    ];

    protected $casts = [
        'days' => 'decimal:2',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
}
