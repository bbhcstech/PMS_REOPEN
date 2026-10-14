<?php
require 'vendor/autoload.php';$app=require 'bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
function revenueCompany($tier,$price,$cycle='monthly',$status='active',$end='2026-11-01'){
 $plan=(object)['slug'=>strtolower($tier),'name'=>$tier];
 return (object)['subscriptions'=>collect([(object)['id'=>1,'plan'=>$plan,'price'=>999999,'billing_cycle'=>'monthly','status'=>'cancelled','starts_at'=>'2026-01-01','ends_at'=>'2027-01-01'],(object)['id'=>2,'plan'=>$plan,'price'=>$price,'billing_cycle'=>$cycle,'status'=>$status,'starts_at'=>'2026-10-01','ends_at'=>$end]])];
}
$companies=collect([revenueCompany('GOLD',4999),revenueCompany('GOLD',59988,'yearly'),revenueCompany('PLATINUM',9999),revenueCompany('DIAMOND',19999,'monthly','expired'),revenueCompany('GOLD',4999,'monthly','trial'),revenueCompany('FREE',100),revenueCompany('DIAMOND',19999,'monthly','active','2026-10-05')]);
$totals=app(App\Services\PlanMonthlyRevenue::class)->totals($companies,Carbon\CarbonImmutable::parse('2026-10-10'));
if($totals!==['FREE'=>0.0,'GOLD'=>9998.0,'PLATINUM'=>9999.0,'DIAMOND'=>0.0])throw new RuntimeException('Incorrect plan MRR: '.json_encode($totals));
if(array_sum(app(App\Services\PlanMonthlyRevenue::class)->totals(collect()))!=0)throw new RuntimeException('Empty data not zero');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/plans/index.blade.php'))),TOKEN_PARSE);
echo "PASS: assigned prices, tier totals, monthly/yearly normalization, cancelled history, expired/trial/Free exclusions, empty data and Blade compilation.\n";
