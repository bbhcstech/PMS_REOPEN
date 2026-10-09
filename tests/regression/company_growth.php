<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$today = Carbon\CarbonImmutable::parse('2026-10-10 12:00:00');
$records = collect([
    (object)['created_at' => '2025-01-01', 'status' => 'active'],
    (object)['created_at' => '2026-10-09', 'status' => 'inactive'],
    (object)['created_at' => '2026-10-10 10:00:00', 'status' => 'active'],
    (object)['created_at' => '2026-10-11', 'status' => 'active'],
]);
$result = app(App\Services\CompanyGrowth::class)->series($records, $today);
foreach (['daily'=>30, 'weekly'=>12, 'monthly'=>12] as $freq=>$count) {
    if (count($result[$freq]['labels']) !== $count || end($result[$freq]['total']) !== 3 || end($result[$freq]['active']) !== 2) throw new RuntimeException('Incorrect '.$freq.' series');
}
if ($result['daily']['total'][0] !== 1 || $result['daily']['total'][28] !== 2) throw new RuntimeException('Baseline or date bucketing failed');
foreach (app(App\Services\CompanyGrowth::class)->series(collect(), $today) as $series) if (array_sum($series['total']) !== 0) throw new RuntimeException('Empty series must be zero');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/companies/metrics.blade.php'))), TOKEN_PARSE);
echo "PASS: real record counts, baseline, daily/weekly/monthly periods, future exclusion, empty data and Blade compilation.\n";
