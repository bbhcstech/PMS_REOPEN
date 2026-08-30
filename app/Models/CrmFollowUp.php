<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\User;

class CrmFollowUp extends TenantModel
{
    protected $table = 'crm_follow_ups';

    protected $fillable = [
        'lead_id',
        'deal_id',
        'follow_up_type',
        'date',
        'time',
        'assigned_to',
        'reminder',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'reminder' => 'boolean',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

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
}
