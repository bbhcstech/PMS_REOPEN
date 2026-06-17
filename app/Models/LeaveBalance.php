<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'leave_year',
        'year_start',
        'year_end',
        'allocated_leaves',
        'used_leaves',
        'remaining_leaves',
        'carried_forward',
        'total_amount',
        'sick_allocated',
        'sick_used',
        'casual_allocated',
        'casual_used',
        'maternity_allocated',
        'maternity_used',
        'unpaid_used',
        'absent_count',
        'pending_requests',
        'approved_requests',
        'rejected_requests',
    ];

    protected $casts = [
        'year_start' => 'date',
        'year_end' => 'date',
        'allocated_leaves' => 'decimal:2',
        'used_leaves' => 'decimal:2',
        'remaining_leaves' => 'decimal:2',
        'carried_forward' => 'decimal:2',
        'sick_allocated' => 'decimal:2',
        'sick_used' => 'decimal:2',
        'casual_allocated' => 'decimal:2',
        'casual_used' => 'decimal:2',
        'maternity_allocated' => 'decimal:2',
        'maternity_used' => 'decimal:2',
        'unpaid_used' => 'decimal:2',
        'absent_count' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
