<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\Currency;
use App\Models\Deal;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Shared currency symbol resolution for Deal-matched Projects, Products, and Orders.
 */
class CurrencyDisplay
{
    public const SYMBOL_FALLBACKS = [
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'AED' => 'AED ',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'SGD' => 'S$',
    ];

    /**
     * Load tenant currency symbols and workspace localization fallback once per request.
     *
     * @return array{0: Collection, 1: array<string, string>, 2: string, 3: string}
     */
    public static function context(): array
    {
        $symbolsByCode = self::SYMBOL_FALLBACKS;
        $currenciesById = collect();

        try {
            $connection = (new Currency)->getConnectionName() ?: config('database.default');
            if (Schema::connection($connection)->hasTable('currencies')) {
                $currencies = Currency::query()
                    ->select(['id', 'currency_code', 'currency_symbol'])
                    ->get();
                $currenciesById = $currencies->keyBy('id');

                foreach ($currencies as $currency) {
                    $code = strtoupper(trim((string) ($currency->currency_code ?? '')));
                    $symbol = trim((string) ($currency->currency_symbol ?? ''));
                    if ($code !== '' && $symbol !== '') {
                        $symbolsByCode[$code] = $symbol;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fall back safely to default map when currencies table is absent.
        }

        $defaultCode = 'USD';
        $defaultSymbol = '$';
        try {
            $defaultCode = strtoupper(trim((string) AppSetting::valueFor('loc_currency', 'USD'))) ?: 'USD';
            $defaultSymbol = (string) AppSetting::valueFor('loc_currency_symbol', $symbolsByCode[$defaultCode] ?? '$');
        } catch (\Throwable $e) {
            // Keep safe defaults if app_settings is unavailable.
        }

        return [$currenciesById, $symbolsByCode, $defaultCode, $defaultSymbol];
    }

    public static function resolveSymbol(?string $currencyCode, array $symbolsByCode, string $fallback): string
    {
        $normalized = strtoupper(trim((string) $currencyCode));
        if ($normalized === '') {
            return $fallback;
        }

        if (isset($symbolsByCode[$normalized])) {
            return $symbolsByCode[$normalized];
        }

        if (in_array($normalized, array_values($symbolsByCode), true)) {
            return $normalized;
        }

        return strlen($normalized) <= 4 ? $normalized . ' ' : $fallback;
    }

    /**
     * Resolve display currency for a Project, preferring a matched Deal's currency when cost comes from that Deal.
     *
     * @return array{code: string, symbol: string}
     */
    public static function resolveProjectCurrency(Project $project, ?Deal $matchedDeal, bool $fromDeal, array $context): array
    {
        [$currenciesById, $symbolsByCode, $defaultCode, $defaultSymbol] = $context;

        $projectCurrency = $project->currency_id ? $currenciesById->get((int) $project->currency_id) : null;
        $projectCode = strtoupper(trim((string) ($projectCurrency->currency_code ?? $defaultCode)));
        $projectSymbol = $projectCurrency?->currency_symbol
            ?: self::resolveSymbol($projectCode, $symbolsByCode, $defaultSymbol);

        if ($fromDeal && $matchedDeal) {
            $dealCode = strtoupper(trim((string) ($matchedDeal->currency ?: $projectCode)));
            return [
                'code' => $dealCode,
                'symbol' => self::resolveSymbol($dealCode, $symbolsByCode, $projectSymbol),
            ];
        }

        return [
            'code' => $projectCode,
            'symbol' => $projectSymbol,
        ];
    }

    public static function resolveSummarySymbol(Collection $symbols, string $fallback): string
    {
        $unique = $symbols->filter()->unique()->values();

        return $unique->count() === 1 ? (string) $unique->first() : $fallback;
    }
}
