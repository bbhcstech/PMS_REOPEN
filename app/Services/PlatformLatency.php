<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class PlatformLatency
{
    public const RANGES = ['1H' => 3600, '6H' => 21600, '24H' => 86400, '7D' => 604800, '30D' => 2592000];

    public function chart(string $range): array
    {
        $range = isset(self::RANGES[$range]) ? $range : '24H';
        $end = now();
        $start = $end->copy()->subSeconds(self::RANGES[$range]);
        $samples = DB::connection('central')->table('platform_latency_samples')
            ->whereBetween('sampled_at', [$start, $end])->orderBy('sampled_at')->get();
        $points = [];
        $width = self::RANGES[$range] / 7;
        for ($i = 0; $i < 7; $i++) {
            $bucket = $samples->filter(function ($sample) use ($start, $width, $i) {
                $offset = \Carbon\Carbon::parse($sample->sampled_at)->timestamp - $start->timestamp;
                return min(6, (int) floor($offset / $width)) === $i;
            });
            $time = $start->copy()->addSeconds((int) (($i + 1) * $width));
            $points[] = ['time' => $time->format(in_array($range, ['7D', '30D']) ? 'd M' : 'H:i'),
                'api' => $bucket->isEmpty() ? null : round($bucket->avg('api_ms'), 1),
                'db' => $bucket->isEmpty() ? null : round($bucket->avg('db_ms'), 1)];
        }
        $sorted = $samples->pluck('api_ms')->sort()->values();
        return ['range' => $range, 'points' => $points,
            'scale' => max(1, $samples->max('api_ms') ?? 0, $samples->max('db_ms') ?? 0),
            'api' => $samples->isEmpty() ? null : round($samples->avg('api_ms'), 1),
            'db' => $samples->isEmpty() ? null : round($samples->avg('db_ms'), 1),
            'percentile' => $sorted->isEmpty() ? null : round($sorted[(int) ceil($sorted->count() * .999) - 1], 1)];
    }
}
