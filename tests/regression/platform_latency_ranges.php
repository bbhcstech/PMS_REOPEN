<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Blade};
use App\Services\PlatformLatency;
config(['database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('central');
Illuminate\Support\Carbon::setTestNow('2026-10-09 12:00:00');
$migration = require database_path('migrations/central/2026_10_09_200000_create_platform_latency_samples.php');
$migration->up(); $migration->up();
$service = new PlatformLatency;
if ($service->chart('1H')['api'] !== null) throw new RuntimeException('Empty history fabricated readings.');
foreach ([0 => 10, 2 => 20, 12 => 30, 48 => 40, 240 => 50, 744 => 999] as $hours => $ms) {
    DB::connection('central')->table('platform_latency_samples')->insert([
        'api_ms' => $ms, 'db_ms' => $ms / 2, 'sampled_at' => now()->subHours($hours),
    ]);
}
foreach (['1H' => 10, '6H' => 15, '24H' => 20, '7D' => 25, '30D' => 30] as $range => $average) {
    $chart = $service->chart($range);
    if ($chart['range'] !== $range || $chart['api'] != $average || $chart['db'] != $average / 2 || count($chart['points']) !== 7) {
        throw new RuntimeException('Incorrect selected range: ' . $range);
    }
}
if ($service->chart('bad')['range'] !== '24H') throw new RuntimeException('Default range failed.');
$compiled = Blade::compileString(file_get_contents(resource_path('views/superadmin/system_health/index.blade.php')));
try { token_get_all($compiled, TOKEN_PARSE); }
catch (ParseError $e) {
    echo implode("\n", array_slice(explode("\n", $compiled), $e->getLine() - 4, 7));
    throw $e;
}
echo "PASS: all five latency ranges, window exclusion, averages, empty history, default, repeat migration and Blade syntax.\n";
