<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Real Monthly Recurring Revenue (MRR) history for the Super Admin "Revenue Overview" card,
 * built from the companies' subscription records.
 *
 * MRR on a date = sum of the monthly-equivalent price of every paid subscription running that day.
 * A subscription runs from starts_at to ends_at; a cancelled one only until it was cancelled
 * (so an upgrade does not count the old and the new plan twice). Trials and free plans add nothing.
 */
class RevenueOverview
{
    public const RANGES = ['7d', '30d', '90d', '1y'];

    /**
     * @return array<string, array{labels: array<int, string>, values: array<int, float>, summary: array<string, mixed>}>
     */
    public function series(Collection $companies, ?CarbonInterface $now = null): array
    {
        $now = $now ? Carbon::instance($now) : now();
        $subscriptions = $this->paidSubscriptions($companies);

        $result = [];
        foreach (self::RANGES as $range) {
            $points = $this->points($range, $now);
            $values = array_map(fn (CarbonInterface $at) => round($this->mrrAt($subscriptions, $at), 2), $points);

            $start = $values[0] ?? 0.0;
            $current = $this->mrrAt($subscriptions, $now);
            $payingTenants = $this->payingTenantsAt($subscriptions, $now);

            $result[$range] = [
                'labels' => array_map(fn (CarbonInterface $at) => $this->label($range, $at), $points),
                'values' => $values,
                'summary' => [
                    'mrr' => round($current, 2),
                    'growth_pct' => $start > 0 ? round((($current - $start) / $start) * 100, 1) : ($current > 0 ? null : 0.0),
                    'arpu' => $payingTenants > 0 ? round($current / $payingTenants, 2) : 0.0,
                    'paying_tenants' => $payingTenants,
                    'period' => strtoupper($range),
                ],
            ];
        }

        return $result;
    }

    /**
     * Flatten every paid subscription into [company_id, monthly amount, active from, active until].
     */
    private function paidSubscriptions(Collection $companies): array
    {
        $rows = [];
        foreach ($companies as $company) {
            foreach ($company->subscriptions ?? [] as $sub) {
                $status = strtolower((string) $sub->status);
                if (in_array($status, ['trial', 'pending'], true)) {
                    continue;
                }

                $monthly = $this->monthlyAmount((float) $sub->price, (string) $sub->billing_cycle);
                if ($monthly <= 0 || ! $sub->starts_at) {
                    continue;
                }

                $from = Carbon::parse($sub->starts_at)->startOfDay();
                $until = $sub->ends_at ? Carbon::parse($sub->ends_at)->endOfDay() : null;
                if ($status === 'cancelled' && $sub->updated_at) {
                    $cancelledAt = Carbon::parse($sub->updated_at);
                    $until = $until ? $until->min($cancelledAt) : $cancelledAt;
                }

                $rows[] = ['company_id' => $company->id, 'monthly' => $monthly, 'from' => $from, 'until' => $until];
            }
        }

        return $rows;
    }

    private function monthlyAmount(float $price, string $cycle): float
    {
        return match (strtolower(trim($cycle))) {
            'yearly', 'annual', 'annually', 'year' => $price / 12,
            'quarterly', 'quarter' => $price / 3,
            'half-yearly', 'half_yearly', 'semiannual', 'semi-annual' => $price / 6,
            'weekly', 'week' => $price * 52 / 12,
            default => $price,
        };
    }

    private function isRunning(array $row, CarbonInterface $at): bool
    {
        return $row['from']->lte($at) && ($row['until'] === null || $row['until']->gte($at));
    }

    private function mrrAt(array $subscriptions, CarbonInterface $at): float
    {
        $total = 0.0;
        foreach ($subscriptions as $row) {
            if ($this->isRunning($row, $at)) {
                $total += $row['monthly'];
            }
        }

        return $total;
    }

    private function payingTenantsAt(array $subscriptions, CarbonInterface $at): int
    {
        $companies = [];
        foreach ($subscriptions as $row) {
            if ($this->isRunning($row, $at)) {
                $companies[$row['company_id']] = true;
            }
        }

        return count($companies);
    }

    /**
     * Sample dates for a range, oldest first, ending now.
     *
     * @return array<int, CarbonInterface>
     */
    private function points(string $range, CarbonInterface $now): array
    {
        [$count, $step] = match ($range) {
            '7d' => [7, 'day'],
            '30d' => [30, 'day'],
            '90d' => [13, 'week'],
            default => [12, 'month'],
        };

        $points = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $at = match ($step) {
                'day' => $now->copy()->subDays($i),
                'week' => $now->copy()->subWeeks($i),
                default => $now->copy()->subMonthsNoOverflow($i),
            };
            // Past points are measured at the end of their day; the last point is "now".
            $points[] = $i === 0 ? $now->copy() : $at->endOfDay();
        }

        return $points;
    }

    private function label(string $range, CarbonInterface $at): string
    {
        return match ($range) {
            '7d' => $at->format('D d'),
            '30d', '90d' => $at->format('d M'),
            default => $at->format('M y'),
        };
    }
}
