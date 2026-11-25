<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Models\Central\{Company, Plan, Subscription};
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};

foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
foreach (['2026_08_08_000001_create_central_companies_table.php', '2026_08_08_000003_create_central_plans_table.php',
    '2026_08_08_000004_create_central_company_subscriptions_table.php', '2026_10_01_000001_add_manually_suspended_to_companies_table.php'] as $file) {
    (require dirname(__DIR__, 2) . '/database/migrations/central/' . $file)->up();
}
Auth::guard('web')->setUser(new User(['name' => 'Platform Admin', 'role' => 'superadmin']));
foreach (App\Support\SupportedPlans::defaults() as $slug => $data) Plan::create($data + ['slug' => $slug, 'is_active' => true]);
$a = Company::create(['name' => 'Alpha', 'email' => 'alpha@test.com', 'db_name' => 'alpha', 'status' => 'trial']);
$b = Company::create(['name' => 'Beta', 'email' => 'beta@test.com', 'db_name' => 'beta', 'status' => 'suspended', 'manually_suspended' => true]);
function subscriptionCheck($ok, $message) { if (! $ok) throw new RuntimeException($message); }
function changeRequest($companyId, $plan, $cycle = null) {
    $r = Request::create('/superadmin/subscriptions/assign', 'POST', ['company_id' => $companyId, 'plan_id' => $plan->id, 'billing_cycle' => $cycle], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $r->setLaravelSession(app('session')->driver()); app()->instance('request', $r); return $r;
}

(require database_path('migrations/central/2026_10_09_190000_add_company_suspension_reasons.php'))->up();
App\Services\SubscriptionChangeSchema::ensure();
$controller = new CompanyController;
$gold=Plan::where('slug','gold')->firstOrFail();
$sub=Subscription::create(['company_id'=>$a->id,'plan_id'=>$gold->id,'starts_at'=>now()->subMonth(),'ends_at'=>now()->subDay(),'status'=>'active','billing_cycle'=>'monthly','price'=>4999]);
$service=app(App\Services\SubscriptionService::class);
subscriptionCheck($service->syncCompanyStatus($a)==='expired','Expired company retained access');
subscriptionCheck($sub->fresh()->status==='expired' && $sub->fresh()->plan_id===$gold->id,'Expiry changed plan or did not expire record');
for($i=0;$i<3;$i++)subscriptionCheck($service->syncCompanyStatus($a->fresh())==='expired','Repeated checks resurrected company');
subscriptionCheck(Subscription::where('company_id',$a->id)->count()===1,'Expiry created Free subscription');
$admin=new User(['name'=>'Company Admin','role'=>'admin','company_id'=>$a->id]);$admin->id=7;Auth::guard('web')->setUser($admin);
$middleware=new App\Http\Middleware\EnsureCompanySubscriptionActive;
function expiryRequest($path,$name,$json=false){$r=Request::create($path,'GET',[],[],[], $json?['HTTP_ACCEPT'=>'application/json']:[]);$r->setLaravelSession(app('session')->driver());$route=new Illuminate\Routing\Route('GET',$path,fn()=>null);$route->name($name);$r->setRouteResolver(fn()=>$route);app()->instance('request',$r);return $r;}
foreach([['/dashboard','dashboard'],['/orders','orders.index'],['/subscriptions/renew','subscriptions.renew'],['/notifications','notifications.index'],['/developer/tasks','developer.tasks']]as[$path,$name]){
 $response=$middleware->handle(expiryRequest($path,$name,true),fn()=>response('Feature ran'));
 subscriptionCheck($response->getStatusCode()===402,'Restricted route allowed: '.$name.' '.$response->getContent());
}
$response=$middleware->handle(expiryRequest('/dashboard','dashboard'),fn()=>response('Feature ran'));
subscriptionCheck($response->isRedirect(route('subscription.suspended')),'Browser did not redirect to expired screen');
subscriptionCheck($middleware->handle(expiryRequest('/subscription/suspended','subscription.suspended'),fn()=>response('Expired screen'))->getContent()==='Expired screen','Expired screen inaccessible');
try{$controller->extendSubscription(changeRequest($a->id,$gold),$a->id);throw new RuntimeException('Company renewed its own subscription');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){subscriptionCheck($e->getStatusCode()===403,'Renewal denial incorrect');}
Auth::guard('web')->setUser(new User(['name'=>'Platform','role'=>'superadmin']));
$request=changeRequest($a->id,$gold);$request->merge(['days'=>30]);
$response=$controller->extendSubscription($request,$a->id);
subscriptionCheck($response->getStatusCode()===200,'Super Admin extension failed: '.$response->getContent());
subscriptionCheck($sub->fresh()->plan_id===$gold->id && $sub->fresh()->status==='active' && $a->fresh()->status==='active','Extension changed plan or did not restore access');
Auth::guard('web')->setUser($admin);
subscriptionCheck($middleware->handle(expiryRequest('/orders','orders.index'),fn()=>response('Feature ran'))->getContent()==='Feature ran','Extension did not restore feature access');
$expiredHtml=Blade::render(file_get_contents(resource_path('views/subscription/expired.blade.php')),['company'=>$a->fresh(),'subscription'=>$sub->fresh()]);
subscriptionCheck(str_contains($expiredHtml,'GOLD')&&!str_contains($expiredHtml,'name="plan_id"'),'Expiry screen lost plan or exposes renewal');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/subscriptions/index.blade.php'))),TOKEN_PARSE);
echo "PASS: assigned-plan preservation, idempotent expiry, feature/renewal/notification/developer blocking, expired screen, platform-only extension and restored access.\n";
