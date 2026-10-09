<?php
require __DIR__.'/catalog_distribution.php';
$companies=collect();
for($i=0;$i<27;$i++)$companies->push(catalogCompany('diamond'));
$companies->push(catalogCompany('platinum'));
$counts=app(App\Services\SubscriptionDistribution::class)->currentCounts($companies);
if($counts!==['FREE'=>0,'GOLD'=>0,'PLATINUM'=>1,'DIAMOND'=>27])throw new RuntimeException('Counts lost companies beyond table page or replaced zero');
$blade=file_get_contents(resource_path('views/superadmin/dashboard.blade.php'));
if(!str_contains($blade,'@json($dashboardPlanCounts)')||str_contains($blade,'rand(1, 4)'))throw new RuntimeException('Dashboard not wired to real counts');
token_get_all(app('blade.compiler')->compileString($blade),TOKEN_PARSE);
echo "PASS: all-company distribution beyond pagination, true zero values and dashboard Blade integration.\n";
