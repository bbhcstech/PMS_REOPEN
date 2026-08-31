<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\User;

class CrmActivity extends TenantModel
{
    protected $table = 'crm_activities';

    protected $fillable = [
        'lead_id',
        'deal_id',
        'type',
        'title',
        'description',
        'created_by',
        'activity_date',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lead()
    {
        return $this->belongsTo(LeadContact::class, 'lead_id');
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function getTypeBadgeClassAttribute()
    {
        return match ($this->type) {
            'call' => 'bg-info text-white',
            'email' => 'bg-primary text-white',
            'meeting' => 'bg-purple text-white',
            'note' => 'bg-secondary text-white',
            'follow_up' => 'bg-warning text-dark',
            'status_change' => 'bg-success text-white',
            'priority_change' => 'bg-danger text-white',
            'deal_created' => 'bg-teal text-white',
            'converted' => 'bg-emerald text-white',
            default => 'bg-secondary text-white',
        };
    }
}
