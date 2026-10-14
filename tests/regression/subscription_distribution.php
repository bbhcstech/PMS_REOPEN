<?php
require 'vendor/autoload.php';
$app=require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
function sub($id,$date,$slug){return (object)['id'=>$id,'created_at'=>$date,'plan'=>(object)['slug'=>$slug,'name'=>strtoupper($slug)]];}
$old=sub(1,'2026-01-01','free');$new=sub(2,'2026-10-01','diamond');
$companies=collect([
(object)['activeSubscription'=>$new,'subscriptions'=>collect([$old,$new])],
(object)['activeSubscription'=>null,'subscriptions'=>collect([$old,$new])],
(object)['activeSubscription'=>sub(3,'2026-10-02','gold'),'subscriptions'=>collect([$new])],
(object)['activeSubscription'=>null,'subscriptions'=>collect()],
]);
$counts=app(App\Services\SubscriptionDistribution::class)->counts($companies);
if($counts!==['FREE'=>1,'GOLD'=>1,'PLATINUM'=>0,'DIAMOND'=>2])throw new RuntimeException('Incorrect current tier distribution');
if(array_sum(app(App\Services\SubscriptionDistribution::class)->counts(collect()))!==0)throw new RuntimeException('Incorrect empty counts');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/companies/metrics.blade.php'))),TOKEN_PARSE);
echo "PASS: current subscription, latest fallback, historical records, unassigned companies, empty data and Blade compilation.\n";
