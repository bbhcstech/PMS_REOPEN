<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SubscriptionDistribution
{
    public function counts(Collection $companies): array
    {
        $counts = ['FREE' => 0, 'GOLD' => 0, 'PLATINUM' => 0, 'DIAMOND' => 0];
        foreach ($companies as $company) {
            $subscription = $company->activeSubscription
                ?? $company->subscriptions->sortByDesc('id')->sortByDesc('created_at')->first();
            $plan = $subscription?->plan;
            $tier = strtoupper(trim((string) ($plan?->slug ?: $plan?->name ?: 'FREE')));
            $counts[array_key_exists($tier, $counts) ? $tier : 'FREE']++;
        }
        return $counts;
    }
}
