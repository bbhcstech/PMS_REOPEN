<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SubscriptionDistribution
{
    public function currentCounts(Collection $companies): array
    {
        $counts = ['FREE' => 0, 'GOLD' => 0, 'PLATINUM' => 0, 'DIAMOND' => 0];
        foreach ($companies as $company) {
            if ($company->manually_suspended || in_array(strtolower((string) $company->status), ['suspended', 'expired', 'inactive'], true)) continue;
            $sub = $company->subscriptions->sortByDesc('id')->first();
            if (!$sub || !in_array($sub->status, ['active', 'trial'], true)) continue;
            if ($sub->starts_at && \Carbon\Carbon::parse($sub->starts_at)->isFuture()) continue;
            if ($sub->ends_at && \Carbon\Carbon::parse($sub->ends_at)->isPast()) continue;
            $tier = strtoupper(trim((string) ($sub->plan?->slug ?: $sub->plan?->name)));
            if (isset($counts[$tier])) $counts[$tier]++;
        }
        return $counts;
    }

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
