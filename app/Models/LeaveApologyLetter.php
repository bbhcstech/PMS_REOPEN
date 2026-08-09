<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class LeaveApologyLetter extends TenantModel
{
    protected $fillable = [
        'user_id',
        'leave_id',
        'subject',
        'body',
        'recipient_email',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        'archived_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
