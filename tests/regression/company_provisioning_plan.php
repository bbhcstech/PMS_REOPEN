<?php

// Run with: php tests/regression/company_provisioning_plan.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Models\Central\Company;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Services\ProvisioningPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

// Intercept only physical DB creation and migration commands. All plan,
// company, subscription and tenant user writes still use real SQLite queries.
$centralPdo = new class('sqlite::memory:') extends PDO {
    public function exec(string $statement): int|false {
        if (str_starts_with($statement, 'CREATE DATABASE')) return 0;
        return parent::exec($statement);
    }
};
app()->instance('db.connector.sqlite', new class($centralPdo) implements Illuminate\Database\Connectors\ConnectorInterface {
    public function __construct(private PDO $central) {}
    public function connect(array $config) {
        if ($config['name'] === 'central') return $this->central;
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, company_id INTEGER, name TEXT, email TEXT, password TEXT, raw_password TEXT, profile_image TEXT, role TEXT, is_active INTEGER, login_allowed INTEGER, email_notifications INTEGER, created_at TEXT, updated_at TEXT)');
        return $pdo;
    }
});
foreach (['central', 'tenant', 'mysql'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Artisan::swap(new class { public function call($command, $arguments) { return 0; } });
Schema::connection('central')->create('plans', function ($t) {
    $t->id(); $t->string('slug')->unique(); $t->string('name'); $t->decimal('monthly_price')->default(0); $t->decimal('yearly_price')->default(0);
    $t->integer('max_users')->default(0); $t->integer('max_storage_mb')->default(0); $t->timestamps();
});
Schema::connection('central')->create('companies', function ($t) {
    $t->text('settings')->nullable();
    $t->id(); foreach (['company_code', 'name', 'email', 'password', 'phone', 'address', 'logo', 'db_name', 'status', 'highest_plan_slug'] as $field) $t->string($field)->nullable();
    foreach (['max_users', 'max_projects', 'max_clients', 'max_storage_mb', 'highest_plan_level'] as $field) $t->integer($field)->default(0);
    $t->timestamp('trial_ends_at')->nullable(); $t->timestamp('suspended_at')->nullable(); $t->softDeletes(); $t->timestamps();
});
Schema::connection('central')->create('company_subscriptions', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('plan_id'); $t->string('billing_cycle');
    $t->date('starts_at'); $t->date('ends_at'); $t->date('trial_ends_at')->nullable(); $t->decimal('price'); $t->string('status'); $t->boolean('auto_renew'); $t->timestamps();
});
function checkPlan($ok, $message) { if (! $ok) throw new RuntimeException($message); }
$resolver = new ProvisioningPlan;
foreach (['free', 'gold', 'platinum', 'diamond'] as $slug) {
    $plan = $resolver->resolve(Request::create('/', 'POST', ['subscription_plan' => strtoupper($slug)]));
    checkPlan($plan->slug === $slug, "Selection $slug resolved to a different plan.");
}
$gold = Plan::where('slug', 'gold')->firstOrFail();
checkPlan($resolver->resolve(Request::create('/', 'POST', ['plan_id' => $gold->id]))->id === $gold->id, 'Plan ID alias resolved incorrectly.');
foreach ([[], ['subscription_plan' => null], ['subscription_plan' => ''], ['subscription_plan' => '   ']] as $input) {
    checkPlan($resolver->resolve(Request::create('/', 'POST', $input))->slug === 'free', 'An omitted or blank selection did not default to Free.');
}
checkPlan($resolver->resolve(Request::create('/', 'POST', ['subscription_plan' => '', 'plan_id' => $gold->id]))->id === $gold->id, 'Blank selection overrode an explicit plan ID.');
foreach (['unknown', ['gold']] as $bad) {
    try { $resolver->resolve(Request::create('/', 'POST', ['subscription_plan' => $bad])); throw new RuntimeException('Invalid selection became Free.'); }
    catch (ValidationException $e) {}
}
$controller = new CompanyController;
foreach (['gold', 'platinum', 'diamond', 'free', 'omitted', 'blank', 'whitespace'] as $slug) {
    $request = Request::create('/super-admin/companies', 'POST', ['name' => 'Company ' . $slug, 'slug' => 'plan_test_' . $slug,
        'email' => $slug . '@company.test', 'admin_name' => 'Admin ' . $slug, 'admin_email' => $slug . '@company.test',
        'admin_password' => 'Test-password-123', 'subscription_plan' => $slug]);
    if ($slug === 'omitted') $request->request->remove('subscription_plan');
    if ($slug === 'blank') $request->merge(['subscription_plan' => '']);
    if ($slug === 'whitespace') $request->merge(['subscription_plan' => '   ']);
    $expectedSlug = in_array($slug, ['omitted', 'blank', 'whitespace'], true) ? 'free' : $slug;
    $request->setLaravelSession(app('session')->driver());
    app()->instance('request', $request);
    $response = $controller->store($request);
    checkPlan($response->getStatusCode() === 302, 'Provisioning did not finish.');
    $company = Company::where('email', $slug . '@company.test')->firstOrFail();
    $subscriptions = Subscription::where('company_id', $company->id)->get();
    checkPlan($subscriptions->count() === 1 && $subscriptions->first()->plan->slug === $expectedSlug, "$slug provisioned with wrong/duplicate subscription.");
    checkPlan($company->highest_plan_slug === $expectedSlug, 'Company lifecycle tier does not match the resolved plan.');
    $plan = $subscriptions->first()->plan;
    checkPlan($company->max_users === ($plan->max_users > 0 ? $plan->max_users : 999999) && $company->max_storage_mb === $plan->max_storage_mb, 'Plan limits were not applied.');
    checkPlan($subscriptions->first()->price == $plan->monthly_price, 'Provisioned subscription price is incorrect.');
}
// The normal activation path must also retain Gold once lifecycle tables exist.
Schema::connection('central')->create('modules', function ($t) { $t->id(); });
Schema::connection('central')->create('plan_modules', function ($t) {
    $t->id(); $t->unsignedBigInteger('plan_id'); $t->unsignedBigInteger('module_id'); $t->timestamps();
});
Schema::connection('central')->create('subscription_histories', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('subscription_id')->nullable();
    $t->unsignedBigInteger('previous_plan_id')->nullable(); $t->unsignedBigInteger('new_plan_id')->nullable();
    foreach (['previous_plan_name', 'new_plan_name', 'action', 'performed_by', 'reason', 'notes', 'start_date', 'end_date'] as $field) $t->text($field)->nullable();
    $t->timestamps();
});
$normal = Request::create('/super-admin/companies', 'POST', ['name' => 'Normal Gold', 'slug' => 'normal_gold', 'email' => 'normalgold@company.test',
    'admin_name' => 'Gold Admin', 'admin_email' => 'normalgold@company.test', 'admin_password' => 'Test-password-123', 'subscription_plan' => 'gold']);
$normal->setLaravelSession(app('session')->driver());
app()->instance('request', $normal);
$controller->store($normal);
$normalCompany = Company::where('email', 'normalgold@company.test')->firstOrFail();
checkPlan($normalCompany->activeSubscription->plan->slug === 'gold' && $normalCompany->max_users === 25, 'Normal activation path lost Gold.');
checkPlan(DB::connection('central')->table('subscription_histories')->where('company_id', $normalCompany->id)->count() === 1, 'Normal path fell back instead of recording activation.');
$source = file_get_contents(resource_path('views/superadmin/companies/create.blade.php'));
token_get_all(app('blade.compiler')->compileString($source), TOKEN_PARSE);
// Test the actual plan cards, including redisplay following validation errors.
$from = strpos($source, '<div class="subscription-tier-grid">');
$to = strpos($source, '<!-- ACTION FOOTER', $from);
$cards = substr($source, $from, $to - $from);
app('session')->driver()->flashInput(['subscription_plan' => 'gold']);
$html = Blade::render($cards);
checkPlan(substr_count($html, 'name="subscription_plan"') === 4 && ! str_contains($html, 'type="hidden"'), 'Selection still depends on a default Free hidden input.');
checkPlan(preg_match('/value="gold"[^>]*checked/', $html) === 1 && preg_match('/value="free"[^>]*checked/', $html) === 0, 'Gold selection was lost when form redisplayed.');
echo "Company provisioning selected-plan and form checks passed.\n";
