<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'name', 'slug', 'description', 'monthly_price', 'yearly_price',
        'max_users', 'max_projects', 'max_clients', 'max_storage_mb',
        'features', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'plan_modules', 'plan_id', 'module_id')
            ->withTimestamps();
    }

    public function companySubscriptions()
    {
        return $this->hasMany(CompanySubscription::class, 'plan_id');
    }

    public function getPriceForBillingCycle(string $cycle): float
    {
        return $cycle === 'monthly' ? $this->monthly_price : $this->yearly_price;
    }
}
