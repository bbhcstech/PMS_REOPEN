<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTimesheet extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'total_hours',
        'log_count',
        'remarks',
    ];

    protected $casts = [
        'work_date' => 'date',
        'total_hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
