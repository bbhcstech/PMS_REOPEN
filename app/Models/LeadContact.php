<?php

namespace App\Models;

use App\Models\TenantModel;
use App\Models\User;

class LeadContact extends TenantModel
{
    protected $fillable = [
        'type',
        // Contact Information
        'salutation',
        'contact_name',
        'job_title',
        'email',
        'mobile',
        'alternate_phone',
        'whatsapp',

        // Company Information
        'company_name',
        'website',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'industry',

        // Lead Source, Status, Priority
        'lead_source',
        'status',
        'priority',
        'lead_score',
        'expected_value',
        'expected_closing_date',
        'last_contacted_at',
        'next_follow_up',
        'tags',

        // Assignment
        'lead_owner_id',
        'added_by',
        'lead_owner_designation',
        'added_by_designation',

        // Deal Information
        'create_deal',
        'deal_name',
        'deal_value',
        'deal_currency',
        'deal_agent_id',
        'pipeline',
        'deal_stage',
        'deal_category',
        'close_date',
        'products',

        // Additional Information
        'description',
        'converted_at',
        'converted_by',
    ];

    // Cast the JSON fields to arrays
    protected $casts = [
        'products' => 'array',
        'close_date' => 'date',
        'expected_closing_date' => 'date',
        'next_follow_up' => 'date',
        'last_contacted_at' => 'datetime',
        'converted_at' => 'datetime',
        'create_deal' => 'boolean',
        'deal_value' => 'decimal:2',
        'expected_value' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'lead_owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function dealAgent()
    {
        return $this->belongsTo(User::class, 'deal_agent_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'lead_id');
    }

    public function activities()
    {
        return $this->hasMany(CrmActivity::class, 'lead_id')->latest('activity_date');
    }

    public function followUps()
    {
        return $this->hasMany(CrmFollowUp::class, 'lead_id')->latest('date');
    }

    public function getFormattedDealValueAttribute()
    {
        if (!$this->deal_value) {
            return null;
        }

        $currencies = [
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $currencies[$this->deal_currency] ?? $this->deal_currency;
        return $symbol . number_format($this->deal_value, 2);
    }

    public function getLeadScoreCategoryAttribute(): string
    {
        $score = $this->lead_score ?? $this->calculateLeadScore();
        if ($score >= 81) {
            return 'Very Hot';
        } elseif ($score >= 61) {
            return 'Hot';
        } elseif ($score >= 31) {
            return 'Warm';
        } else {
            return 'Cold';
        }
    }

    public function getLeadScoreBadgeClassAttribute(): string
    {
        $score = $this->lead_score ?? $this->calculateLeadScore();
        if ($score >= 81) {
            return 'bg-danger text-white'; // Deep Red / Very Hot
        } elseif ($score >= 61) {
            return 'bg-warning text-dark'; // Hot / Orange-Yellow
        } elseif ($score >= 31) {
            return 'bg-info text-white'; // Warm / Blue
        } else {
            return 'bg-secondary text-white'; // Cold / Gray
        }
    }

    public function getLeadScoreColorAttribute()
    {
        $score = $this->lead_score ?? 0;
        if ($score >= 75) {
            return 'success';
        } elseif ($score >= 50) {
            return 'warning';
        } elseif ($score >= 25) {
            return 'info';
        } else {
            return 'danger';
        }
    }

    public function getTagsArrayAttribute()
    {
        if (!$this->tags) {
            return [];
        }

        return array_map('trim', explode(',', $this->tags));
    }

    public function hasDeal()
    {
        return ($this->create_deal && $this->deal_name && $this->deal_value) || $this->deals()->exists();
    }

    public function calculateLeadScore(): int
    {
        $score = 20; // Base score for creating lead

        if (!empty($this->email)) $score += 10;
        if (!empty($this->mobile) || !empty($this->phone)) $score += 10;
        if (!empty($this->company_name)) $score += 10;
        if (!empty($this->job_title)) $score += 10;
        if (!empty($this->city) || !empty($this->country)) $score += 10;
        if ($this->hasDeal() || ($this->expected_value && $this->expected_value > 0)) $score += 15;
        if (in_array(strtolower($this->priority ?? ''), ['high', 'urgent'])) $score += 15;

        return min(100, max(0, $score));
    }
}
