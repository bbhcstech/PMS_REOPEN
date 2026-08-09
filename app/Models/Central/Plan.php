<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Central Plan model — subscription plan catalog.
 * Lives in pms_central.plans.
 */
class Plan extends Model
{
    protected $connection = 'central';
    protected $table = 'plans';

    protected $fillable = [
        'name', 'slug', 'description',
        'monthly_price', 'yearly_price',
        'max_users', 'max_projects', 'max_clients', 'max_storage_mb',
        'features', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'features'      => 'array',
        'is_active'     => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price'  => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPriceForCycle(string $cycle): float
    {
        return $cycle === 'monthly' ? (float) $this->monthly_price : (float) $this->yearly_price;
    }
}
