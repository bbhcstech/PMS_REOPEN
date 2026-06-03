<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'price',
        'status',
        'auto_renew',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'trial_ends_at' => 'date',
        'price' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
