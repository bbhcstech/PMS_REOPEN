<?php
use App\Models\Central\Plan;
use Illuminate\Support\Facades\Blade;
require __DIR__.'/dashboard_subscription_change.php';
foreach(['starter','professional','enterprise'] as $slug)Plan::create(['name'=>ucfirst($slug),'slug'=>$slug,'monthly_price'=>49,'is_active'=>true]);
$company=$a->fresh();$company->manually_suspended=true;$company->status='suspended';
$html=Blade::render(file_get_contents(resource_path('views/subscription/suspended.blade.php')),compact('company'));
foreach(['FREE','GOLD','PLATINUM','DIAMOND'] as $tier)subscriptionCheck(str_contains($html,'>'.$tier.'</h5>'),'Missing '.$tier.' reference card');
foreach(['Starter','Professional','Enterprise'] as $tier)subscriptionCheck(!str_contains($html,'>'.$tier.'</h5>'),'Legacy plan shown: '.$tier);
subscriptionCheck(substr_count($html,'class="plan-card locked-reference-plan"')===4,'Expected four locked cards');
subscriptionCheck(!str_contains($html,'name="plan_id"'),'Suspended page exposes renewal');
subscriptionCheck(str_contains($html,'GB storage')&&str_contains($html,'Access locked'),'Missing reference limits/lock label');
echo "PASS: exactly four supported locked cards, no legacy tiers or renewal form, real plan limits and compiled suspended page.\n";
