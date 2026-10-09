<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PlanMonthlyRevenue
{
    public function totals(Collection $companies, ?CarbonImmutable $at = null): array
    {
        $at ??= CarbonImmutable::now();
        $totals = ['FREE' => 0.0, 'GOLD' => 0.0, 'PLATINUM' => 0.0, 'DIAMOND' => 0.0];
        foreach ($companies as $company) {
            $sub = $company->subscriptions->sortByDesc('id')->first();
            if (!$sub || $sub->status !== 'active' || !$sub->starts_at || !$sub->ends_at) continue;
            if (CarbonImmutable::parse($sub->starts_at)->greaterThan($at) || CarbonImmutable::parse($sub->ends_at)->lessThan($at)) continue;
            $tier = strtoupper(trim((string) ($sub->plan?->slug ?: $sub->plan?->name)));
            if (!isset($totals[$tier]) || $tier === 'FREE') continue;
            $divisor = match (strtolower((string) $sub->billing_cycle)) {
                'yearly', 'annual', 'annually' => 12,
                'quarterly' => 3,
                'half-yearly', 'half_yearly' => 6,
                default => 1,
            };
            $totals[$tier] += max(0, (float) $sub->price) / $divisor;
        }
        return array_map(fn ($total) => round($total, 2), $totals);
    }
}
