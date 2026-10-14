<?php
require 'vendor/autoload.php';$app=require 'bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
function catalogCompany($tier,$status='active',$expired=false){return (object)['status'=>$status,'manually_suspended'=>$status==='suspended','subscriptions'=>collect([(object)['id'=>1,'status'=>'cancelled','plan'=>(object)['slug'=>'free'],'starts_at'=>now()->subMonth(),'ends_at'=>now()->addYear()],(object)['id'=>2,'status'=>$status==='trial'?'trial':'active','plan'=>(object)['slug'=>$tier],'starts_at'=>now()->subDay(),'ends_at'=>$expired?now()->subDay():now()->addMonth()]])];}
$companies=collect([catalogCompany('gold'),catalogCompany('gold'),catalogCompany('platinum'),catalogCompany('free','trial'),catalogCompany('diamond','suspended'),catalogCompany('diamond','active',true)]);
$counts=app(App\Services\SubscriptionDistribution::class)->currentCounts($companies);
if($counts!==['FREE'=>1,'GOLD'=>2,'PLATINUM'=>1,'DIAMOND'=>0])throw new RuntimeException('Wrong company distribution: '.json_encode($counts));
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/plans/index.blade.php'))),TOKEN_PARSE);
echo "PASS: real current plan company counts, expired/suspended exclusion, trial Free, cancelled history and Blade compilation.\n";
