<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CompanyGrowth
{
    public function series(Collection $companies, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::now();
        $records = $companies->filter(fn ($company) => $company->created_at !== null)
            ->map(fn ($company) => [
                'created' => CarbonImmutable::parse($company->created_at),
                'active' => strtolower((string) $company->status) === 'active',
            ]);
        $result = [];
        foreach (['daily' => 30, 'weekly' => 12, 'monthly' => 12] as $frequency => $length) {
            $series = ['labels' => [], 'total' => [], 'active' => []];
            for ($offset = $length - 1; $offset >= 0; $offset--) {
                $period = match ($frequency) {
                    'daily' => $today->startOfDay()->subDays($offset),
                    'weekly' => $today->startOfWeek()->subWeeks($offset),
                    'monthly' => $today->startOfMonth()->subMonths($offset),
                };
                $end = match ($frequency) {
                    'daily' => $period->endOfDay(),
                    'weekly' => $period->endOfWeek(),
                    'monthly' => $period->endOfMonth(),
                };
                if ($end->greaterThan($today)) $end = $today;
                $registered = $records->filter(fn ($record) => $record['created']->lessThanOrEqualTo($end));
                $series['labels'][] = $period->format($frequency === 'monthly' ? 'M Y' : 'd M Y');
                $series['total'][] = $registered->count();
                // Current active companies grouped by registration date; historical status is not inferred.
                $series['active'][] = $registered->where('active', true)->count();
            }
            $result[$frequency] = $series;
        }
        return $result;
    }
}
