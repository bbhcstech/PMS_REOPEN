<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Central Subscription model.
 * Maps to pms_central.company_subscriptions.
 */
class Subscription extends Model
{
    protected $connection = 'central';
    protected $table = 'company_subscriptions';

    protected $fillable = [
        'company_id', 'plan_id', 'billing_cycle',
        'starts_at', 'ends_at', 'trial_ends_at',
        'price', 'status', 'auto_renew', 'notes',
    ];

    protected $casts = [
        'starts_at'     => 'date',
        'ends_at'       => 'date',
        'trial_ends_at' => 'date',
        'price'         => 'decimal:2',
        'auto_renew'    => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    /**
     * Check if this subscription's plan includes the given feature slug.
     */
    public function hasFeature(string $featureSlug): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $plan = $this->plan;
        if (! $plan || ! is_array($plan->features)) {
            return false;
        }

        return in_array('*', $plan->features, true) || in_array($featureSlug, $plan->features, true);
    }
}
