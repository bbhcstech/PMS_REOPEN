<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'deal_name',
        'lead_name',
        'contact_details',
        'company_name',
        'value',
        'currency',
        'probability',
        'weighted_value',
        'close_date',
        'next_follow_up',
        'deal_agent_id',
        'deal_stage_id',
        'deal_category_id',
        'pipeline',
        'product',
        'priority',
        'lost_reason',
        'lost_notes',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'weighted_value' => 'decimal:2',
        'probability' => 'integer',
        'close_date' => 'date',
        'next_follow_up' => 'date',
        'is_active' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'deal_agent_id');
    }

    public function stage()
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function category()
    {
        return $this->belongsTo(DealCategory::class, 'deal_category_id');
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class, 'deal_watchers', 'deal_id', 'user_id')
                    ->withTimestamps();
    }

    public function lead()
    {
        return $this->belongsTo(LeadContact::class, 'lead_id');
    }

    public function activities()
    {
        return $this->hasMany(CrmActivity::class, 'deal_id')->latest('activity_date');
    }

    public function followUps()
    {
        return $this->hasMany(CrmFollowUp::class, 'deal_id')->latest('date');
    }

    public function calculateWeightedValue(): float
    {
        $val = (float) ($this->value ?? 0);
        $prob = (int) ($this->probability ?? 0);
        return round(($val * $prob) / 100, 2);
    }
}
