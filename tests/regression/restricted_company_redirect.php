<?php
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Route;
require __DIR__.'/subscription_expiry_access.php';
app(CompanyContext::class)->reset($a->fresh());
$action=Route::getRoutes()->getByName('subscription.suspended')->getAction('uses');
$request=expiryRequest('/subscription/suspended','subscription.suspended',true);
$response=$action($request);
subscriptionCheck($response->getData(true)['status']==='active','Status polling did not see restored subscription');
$request=expiryRequest('/subscription/suspended','subscription.suspended');
$response=$action($request);
subscriptionCheck($response->isRedirect(route('dashboard')),'Active company stayed on restricted page');
subscriptionCheck(session('success')==='Your company access has been restored. Welcome back.','Missing restored-access notification');
$sub->update(['status'=>'expired','ends_at'=>now()->subDay()]);app(CompanyContext::class)->reset($a->fresh());
$response=$action(expiryRequest('/subscription/suspended','subscription.suspended'));
subscriptionCheck($response->name()==='subscription.expired','Expired company did not stay on expiry page');
foreach(['subscription/expired','subscription/suspended']as $view)token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/'.$view.'.blade.php'))),TOKEN_PARSE);
echo "PASS: restricted-page status polling, active Dashboard redirect/notification, genuine expiry screen and Blade compilation.\n";
