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
$controller = new CompanyController;
$gold = Plan::where('slug', 'gold')->firstOrFail();
$response = $controller->assignPlan(changeRequest($a->id, $gold));
subscriptionCheck($response->getStatusCode() === 200, 'Legacy-schema plan change failed: ' . $response->getContent());
subscriptionCheck($a->fresh()->activeSubscription->plan_id === $gold->id && $a->fresh()->max_users === 25, 'Dashboard relation/limits did not reflect Gold.');
subscriptionCheck(Subscription::where('company_id', $b->id)->count() === 0, 'Another company was changed.');
subscriptionCheck(DB::connection('central')->table('subscription_histories')->count() === 1, 'Plan history was not persisted.');
subscriptionCheck(! Schema::connection('tenant')->hasTable('subscription_histories'), 'Repair modified tenant schema.');
$platinum = Plan::where('slug', 'platinum')->firstOrFail();
$response = $controller->assignPlan(changeRequest($a->id, $platinum, 'yearly'));
subscriptionCheck($response->getStatusCode() === 200 && $a->fresh()->activeSubscription->plan_id === $platinum->id, 'Second selected plan did not persist.');
$sub = $a->fresh()->activeSubscription;
subscriptionCheck($sub->billing_cycle === 'yearly' && $sub->price == $platinum->yearly_price, 'Billing cycle/price incorrect.');
subscriptionCheck(Subscription::where('company_id', $a->id)->where('status', 'active')->count() === 1, 'Duplicate active subscriptions.');
subscriptionCheck($controller->assignPlan(changeRequest($a->id, $gold))->getStatusCode() === 422, 'Existing downgrade policy changed.');
subscriptionCheck($a->fresh()->activeSubscription->plan_id === $platinum->id, 'Rejected downgrade changed company.');
$response = $controller->assignPlan(changeRequest($b->id, $gold));
subscriptionCheck($response->getStatusCode() === 200 && $response->getData(true)['status'] === 'suspended' && $b->fresh()->status === 'suspended', 'Manual suspension was removed.');
DB::connection('central')->statement("CREATE TRIGGER reject_plan_change BEFORE INSERT ON company_subscriptions BEGIN SELECT RAISE(ABORT, 'test atomic failure'); END");
$diamond = Plan::where('slug', 'diamond')->firstOrFail();
$response = $controller->assignPlan(changeRequest($a->id, $diamond));
subscriptionCheck($response->getStatusCode() === 500 && $a->fresh()->activeSubscription->plan_id === $platinum->id && $a->fresh()->highest_plan_slug === 'platinum', 'Failed save left partial changes.');
DB::connection('central')->statement('DROP TRIGGER reject_plan_change');
Auth::guard('web')->setUser(new User(['role' => 'admin', 'company_id' => $a->id]));
try { $controller->assignPlan(changeRequest($a->id, $diamond)); throw new RuntimeException('Tenant admin could change subscriptions.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { subscriptionCheck($e->getStatusCode() === 403, 'Wrong access denial.'); }
$template = file_get_contents(dirname(__DIR__, 2) . '/resources/views/superadmin/dashboard.blade.php');
$start = strpos($template, '  <!-- PLAN CHANGE MODAL -->'); $end = strpos($template, '  <!-- HIDDEN FORMS', $start);
$html = Blade::render(substr($template, $start, $end - $start), ['plans' => App\Models\SubscriptionPlan::standard()->get()]);
$dom = new DOMDocument; @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
subscriptionCheck($xpath->query('//input[@type="radio" and @name="plan_id" and @form="assignPlanForm"]')->length === 4, 'Plan cards do not submit the selected tier.');
subscriptionCheck($xpath->query('//input[@type="hidden" and @name="plan_id"]')->length === 0, 'Hidden default plan overrides selection.');
$companiesTemplate = file_get_contents(dirname(__DIR__, 2) . '/resources/views/superadmin/companies/index.blade.php');
$start = strpos($companiesTemplate, '<!-- CHANGE SUBSCRIPTION MODAL -->');
$end = strpos($companiesTemplate, '<!-- COMPANY DETAIL DRAWER -->', $start);
$html = Blade::render(substr($companiesTemplate, $start, $end - $start), ['plans' => Plan::standard()->where('is_active', true)->get()]);
$dom = new DOMDocument; @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
subscriptionCheck($xpath->query('//form[@id="companyPlanChangeForm" and @method="POST"]')->length === 1, 'Companies modal must submit a real subscription form.');
subscriptionCheck($xpath->query('//form[@id="companyPlanChangeForm"]//input[@type="radio" and @name="plan_id"]')->length === 4, 'Companies modal must submit the selected plan ID.');
subscriptionCheck($xpath->query('//form[@id="companyPlanChangeForm"]//input[@name="company_id"]')->length === 1, 'Companies modal must submit the selected company.');
subscriptionCheck($xpath->query('//form[@id="companyPlanChangeForm"]//button[@id="confirmPlanChangeBtn" and @type="submit"]')->length === 1, 'Confirm Change must submit instead of just closing the dialog.');
token_get_all(app('blade.compiler')->compileString($companiesTemplate), TOKEN_PARSE);
echo "PASS: selected-plan persistence, billing/limits, isolation, suspension, downgrade policy, rollback and Dashboard/Companies native modal submissions.\n";
