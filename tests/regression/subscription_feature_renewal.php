<?php
require __DIR__.'/dashboard_subscription_change.php';
Auth::guard('web')->setUser(new App\Models\User(['name' => 'Platform Admin', 'role' => 'superadmin']));

$module = App\Models\Module::create(['name' => 'Tasks', 'slug' => 'tasks', 'is_active' => true]);
$plan = $a->fresh()->activeSubscription->plan;
app(App\Services\PlanFeatureEntitlements::class)->set($plan, $module, false);
DB::connection('central')->table('company_modules')->insert(['company_id' => $a->id, 'module_id' => $module->id, 'is_enabled' => false]);
$planSnapshot = DB::connection('central')->table('plan_modules')->orderBy('id')->get()->toJson();
$companySnapshot = DB::connection('central')->table('company_modules')->orderBy('id')->get()->toJson();
$response = $controller->assignPlan(changeRequest($a->id, $plan, 'monthly'));
subscriptionCheck($response->getStatusCode() === 200 && $response->getData(true)['success'], 'Renewal failed');
subscriptionCheck(DB::connection('central')->table('plan_modules')->orderBy('id')->get()->toJson() === $planSnapshot, 'Renewal reset plan access');
subscriptionCheck(DB::connection('central')->table('company_modules')->orderBy('id')->get()->toJson() === $companySnapshot, 'Renewal reset company override');
subscriptionCheck(!$a->fresh()->hasFeature('tasks'), 'Renewal enabled disabled feature');
echo "PASS: subscription renewal preserves plan decisions and company overrides without materializing new company grants.\n";
