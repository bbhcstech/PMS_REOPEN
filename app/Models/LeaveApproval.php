<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends TenantModel
{
    protected $fillable = [
        'leave_id',
        'user_id',
        'action',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
