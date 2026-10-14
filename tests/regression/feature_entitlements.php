<?php
/**
 * Super Admin "Manage Features" regression check (no MySQL needed).
 *
 * Run: php artisan tinker --execute="require base_path('tests/regression/feature_entitlements.php');"
 *
 * Central and tenant connections are pointed at in-memory SQLite databases holding only the
 * tables the feature pipeline reads, then two companies with different feature sets are checked
 * through the real code paths: Company::hasFeature, FeatureEntitlements, User::featureEnabled /
 * canViewModule / moduleToggleAllows (sidebar), EnforceRolePermissionToggles (routes) and the
 * Super Admin toggle + feature-states endpoints.
 */

use App\Models\Central\Company as CentralCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$pass = 0;
$fail = 0;
$check = function (string $label, bool $ok) use (&$pass, &$fail) {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '  PASS ' : '  FAIL ') . $label . "\n";
};

// ---- isolated databases ----------------------------------------------------------------------
foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]]);
    DB::purge($connection);
}
$moduleColumns = function ($table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('icon')->nullable();
    $table->text('description')->nullable();
    $table->string('route_name')->nullable();
    $table->string('route_prefix')->nullable();
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_core')->default(false);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
};
Schema::connection('central')->create('modules', $moduleColumns);
Schema::connection('tenant')->create('modules', $moduleColumns);
Schema::connection('central')->create('companies', function ($t) {
    $t->id(); $t->string('name'); $t->string('email')->nullable(); $t->string('db_name')->nullable();
    $t->string('status')->default('active'); $t->boolean('manually_suspended')->default(false);
    $t->timestamp('trial_ends_at')->nullable(); $t->text('settings')->nullable(); $t->timestamps(); $t->softDeletes();
});
Schema::connection('central')->create('company_modules', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('module_id');
    $t->boolean('is_enabled')->default(true); $t->text('settings')->nullable(); $t->timestamps();
});
Schema::connection('central')->create('company_subscriptions', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('plan_id')->nullable();
    $t->string('status')->default('active'); $t->timestamp('ends_at')->nullable(); $t->timestamps();
});
Schema::connection('central')->create('plan_modules', function ($t) {
    $t->id(); $t->unsignedBigInteger('plan_id'); $t->unsignedBigInteger('module_id');
});
Schema::connection('central')->create('audit_logs', function ($t) {
    $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->unsignedBigInteger('company_id')->nullable();
    $t->string('action')->nullable(); $t->string('entity_type')->nullable(); $t->unsignedBigInteger('entity_id')->nullable();
    $t->text('old_values')->nullable(); $t->text('new_values')->nullable(); $t->string('ip_address')->nullable();
    $t->string('user_agent')->nullable(); $t->timestamps();
});
Schema::connection('tenant')->create('role_permissions', function ($t) {
    $t->id(); $t->string('role'); $t->unsignedBigInteger('module_id');
    foreach (['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'] as $p) $t->boolean('can_'.$p)->default(false);
    $t->timestamps();
});

// Platform catalog = sidebar registry + one legacy alias row ("deals") that older data may hold.
$slugs = array_merge(array_keys(config('workspace_modules')), ['deals']);
foreach ($slugs as $i => $slug) {
    $row = ['name' => config("workspace_modules.$slug.name") ?? ucfirst($slug), 'slug' => $slug, 'is_active' => true, 'sort_order' => $i, 'created_at' => now(), 'updated_at' => now()];
    DB::connection('central')->table('modules')->insert($row);
    DB::connection('tenant')->table('modules')->insert($row);
}
$moduleId = fn (string $slug) => (int) DB::connection('central')->table('modules')->where('slug', $slug)->value('id');

$companyA = CentralCompany::on('central')->create(['name' => 'Alpha', 'email' => 'a@example.test', 'db_name' => 'alpha_db', 'status' => 'active']);
$companyB = CentralCompany::on('central')->create(['name' => 'Beta', 'email' => 'b@example.test', 'db_name' => 'beta_db', 'status' => 'active']);

$entitlements = app(\App\Services\FeatureEntitlements::class);
$setOverride = function ($company, string $slug, bool $enabled) use ($moduleId, $entitlements) {
    DB::connection('central')->table('company_modules')->updateOrInsert(
        ['company_id' => $company->id, 'module_id' => $moduleId($slug)],
        ['is_enabled' => $enabled, 'updated_at' => now()]
    );
    $entitlements->forget();
};
$clearOverrides = function () use ($entitlements) {
    DB::connection('central')->table('company_modules')->delete();
    $entitlements->forget();
};
$fresh = fn ($company) => CentralCompany::on('central')->find($company->id);

// ---- 1. Company::hasFeature ---------------------------------------------------------------------
echo "Company::hasFeature\n";
foreach (array_keys(config('workspace_modules')) as $slug) {
    if (! $fresh($companyA)->hasFeature($slug)) { $check("default on: $slug", false); }
}
$check('every registry feature is on by default for an active company', true);

foreach (['dashboard', 'company-assistant', 'upper-level-employees', 'crm-deals'] as $slug) {
    $clearOverrides();
    $setOverride($companyA, $slug, false);
    $check("disable $slug for Alpha -> Alpha off", ! $fresh($companyA)->hasFeature($slug));
    $check("disable $slug for Alpha -> Beta unaffected", $fresh($companyB)->hasFeature($slug));
    $others = array_diff(array_keys(config('workspace_modules')), $entitlements->ancestry($slug), [$slug]);
    $collateral = array_filter($others, fn ($other) => ! in_array($slug, $entitlements->ancestry($other), true) && ! $fresh($companyA)->hasFeature($other));
    $check("disable $slug -> no unrelated feature switched off (" . implode(',', $collateral) . ')', $collateral === []);
    $setOverride($companyA, $slug, true);
    $check("re-enable $slug -> on again", $fresh($companyA)->hasFeature($slug));
}

// Every other feature as well.
$clearOverrides();
$broken = [];
foreach (array_keys(config('workspace_modules')) as $slug) {
    $setOverride($companyA, $slug, false);
    if ($fresh($companyA)->hasFeature($slug) || ! $fresh($companyB)->hasFeature($slug)) $broken[] = $slug;
    $setOverride($companyA, $slug, true);
    if (! $fresh($companyA)->hasFeature($slug)) $broken[] = "$slug(re-enable)";
}
$check('disable/re-enable works for every registry feature, per company (' . implode(',', $broken) . ')', $broken === []);

$clearOverrides();
$setOverride($companyA, 'leads-contacts', false);
$check('sibling decoupling: Lead Contact off keeps Deals on', $fresh($companyA)->hasFeature('crm-deals'));
$clearOverrides();
$setOverride($companyA, 'leads', false);
$check('parent category: Leads off -> Deals off', ! $fresh($companyA)->hasFeature('crm-deals'));
$check('parent category: Leads off -> Lead Contact off', ! $fresh($companyA)->hasFeature('leads-contacts'));
$check('disabledAncestor reports Leads', $entitlements->disabledAncestor($companyA->id, 'crm-deals') === 'leads');
$setOverride($companyA, 'crm-deals', true);
$check('child explicitly on cannot bypass a parent switched off', ! $fresh($companyA)->hasFeature('crm-deals'));
$clearOverrides();
$setOverride($companyA, 'settings-dashboard', false);
$check('Settings Dashboard off keeps other settings pages on', $fresh($companyA)->hasFeature('leave-settings'));
$clearOverrides();
$setOverride($companyA, 'deals', false);
$check('legacy alias row "deals" off still switches Deals off', ! $fresh($companyA)->hasFeature('crm-deals'));

// Tenant model resolves the same way as the central model.
$clearOverrides();
$setOverride($companyA, 'company-assistant', false);
// App\Models\Company also reads the central table, so it is loaded by the same central id.
$tenantCompany = \App\Models\Company::find($companyA->id);
$check('tenant Company model gives the same answer as the central one', $tenantCompany->hasFeature('company-assistant') === $fresh($companyA)->hasFeature('company-assistant'));

// ---- 2. Sidebar checks (User) ------------------------------------------------------------------
echo "Sidebar visibility (User)\n";
$context = new class { public $company; public function current() { return $this->company; } };
app()->instance(\App\Services\CompanyContext::class, $context);
$admin = (new \App\Models\User)->forceFill(['id' => 501, 'name' => 'Alpha Admin', 'role' => 'admin', 'company_id' => $companyA->id]);
$hr = (new \App\Models\User)->forceFill(['id' => 502, 'name' => 'Alpha HR', 'role' => 'hr', 'company_id' => $companyA->id]);

$clearOverrides();
$context->company = $fresh($companyA);
$check('admin sees Company Assistant when enabled', $admin->moduleToggleAllows('company-assistant'));
$check('admin sees Deals when enabled', $admin->moduleToggleAllows('crm-deals'));
foreach (['dashboard' => 'canViewModule', 'company-assistant' => 'moduleToggleAllows', 'upper-level-employees' => 'canViewModule', 'crm-deals' => 'moduleToggleAllows'] as $slug => $method) {
    $clearOverrides();
    $setOverride($companyA, $slug, false);
    $context->company = $fresh($companyA);
    $check("admin sidebar hides $slug when Super Admin disables it ($method)", ! $admin->{$method}($slug));
    $check("hr sidebar hides $slug when Super Admin disables it ($method)", ! $hr->{$method}($slug));
    $context->company = $fresh($companyB);
    $adminB = (new \App\Models\User)->forceFill(['id' => 601, 'role' => 'admin', 'company_id' => $companyB->id]);
    $check("Beta admin still sees $slug", $adminB->{$method}($slug));
}

// ---- 3. Direct URLs (middleware) ------------------------------------------------------------------
echo "Direct route access (EnforceRolePermissionToggles)\n";
$middleware = app(\App\Http\Middleware\EnforceRolePermissionToggles::class);
$call = function (string $routeName, $user, string $method = 'GET') use ($middleware) {
    $route = Route::getRoutes()->getByName($routeName);
    $request = Request::create('/' . ltrim($route->uri(), '/'), $method);
    $request->headers->set('Accept', 'application/json');
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => $user);
    $response = $middleware->handle($request, fn () => response('passed', 200));
    return $response->getStatusCode() === 200 && $response->getContent() === 'passed';
};
$clearOverrides();
$context->company = $fresh($companyA);
$check('admin reaches admin.deals.store when Deals enabled', $call('admin.deals.store', $admin, 'POST'));
$setOverride($companyA, 'crm-deals', false);
$context->company = $fresh($companyA);
$check('admin blocked from admin.deals.store when Deals disabled', ! $call('admin.deals.store', $admin, 'POST'));
$check('admin blocked from admin.deals.index when Deals disabled', ! $call('admin.deals.index', $admin));
$check('hr blocked from admin.deals.index when Deals disabled', ! $call('admin.deals.index', $hr));
$setOverride($companyA, 'company-assistant', false);
$setOverride($companyA, 'upper-level-employees', false);
$context->company = $fresh($companyA);
$check('admin blocked from company.ai.ask when Company Assistant disabled', ! $call('company.ai.ask', $admin, 'POST'));
$check('admin blocked from admin.upper-level-employees.index when disabled', ! $call('admin.upper-level-employees.index', $admin));
$check('admin still reaches leads.contacts.index (sibling of Deals)', $call('leads.contacts.index', $admin));
$setOverride($companyA, 'dashboard', false);
$context->company = $fresh($companyA);
$check('dashboard stays reachable as the landing page', $call('dashboard', $admin));
$check('admin keeps repair access to Module Management', $call('admin.modules.index', $admin));
$check('routes outside any feature are untouched (profile.edit)', $call('profile.edit', $admin));

// ---- 4. Super Admin toggle + feature-states endpoint ----------------------------------------------
echo "Super Admin Manage Features endpoints\n";
$clearOverrides();
$controller = app(\App\Http\Controllers\SuperAdmin\CompanyController::class);
$setOverride($companyA, 'deals', false); // stale legacy decision
$toggle = Request::create('/super-admin/subscriptions/toggle-override', 'POST', ['company_id' => $companyA->id, 'module_id' => $moduleId('crm-deals'), 'enabled' => 1]);
$toggle->headers->set('Accept', 'application/json');
app()->instance('request', $toggle);
$response = $controller->toggleCompanyOverride($toggle);
$payload = $response->getData(true);
$check('toggle responds with success and fresh states', ($payload['success'] ?? false) && isset($payload['states'][$moduleId('crm-deals')]));
$check('enabling Deals removes the stale legacy "deals" row', ! DB::connection('central')->table('company_modules')->where('company_id', $companyA->id)->where('module_id', $moduleId('deals'))->exists());
$check('Deals effective state reported on after toggle', ($payload['states'][$moduleId('crm-deals')]['effective'] ?? null) === 1);
$check('legacy alias "deals" is not listed as its own feature', ! isset($payload['states'][$moduleId('deals')]));

$toggle = Request::create('/super-admin/subscriptions/toggle-override', 'POST', ['company_id' => $companyA->id, 'module_id' => $moduleId('leads'), 'enabled' => 0]);
$toggle->headers->set('Accept', 'application/json');
app()->instance('request', $toggle);
$payload = $controller->toggleCompanyOverride($toggle)->getData(true);
$deals = $payload['states'][$moduleId('crm-deals')] ?? [];
$check('turning Leads off reports Deals off via Leads', ($deals['effective'] ?? null) === 0 && ($deals['blocked_by'] ?? null) === 'Leads');
$states = $controller->companyFeatureStates($companyB->id)->getData(true)['states'];
$check('Beta feature states unaffected by Alpha changes', ($states[$moduleId('leads')]['effective'] ?? null) === 1 && ($states[$moduleId('crm-deals')]['effective'] ?? null) === 1);
$check('feature-states lists every registry feature', count($states) >= count(config('workspace_modules')));

// ---- 5. Existing behaviour that must not change -----------------------------------------------------
echo "Unchanged behaviour (role permissions, plans)\n";
$clearOverrides();
$context->company = $fresh($companyA);
$check('hr with no saved role decisions keeps default access to Deals', $hr->moduleToggleAllows('crm-deals'));
DB::connection('tenant')->table('role_permissions')->insert(['role' => 'hr', 'module_id' => (int) DB::connection('tenant')->table('modules')->where('slug', 'crm-deals')->value('id'), 'can_view' => false]);
$hr = (new \App\Models\User)->forceFill(['id' => 503, 'role' => 'hr', 'company_id' => $companyA->id]);
$check('saved role decision OFF still hides Deals for hr', ! $hr->moduleToggleAllows('crm-deals'));
$check('saved role decision OFF does not affect admin', $admin->moduleToggleAllows('crm-deals'));
DB::connection('tenant')->table('role_permissions')->update(['can_view' => true]);
$hr = (new \App\Models\User)->forceFill(['id' => 504, 'role' => 'hr', 'company_id' => $companyA->id]);
$check('saved role decision ON shows Deals for hr', $hr->moduleToggleAllows('crm-deals'));
DB::connection('tenant')->table('role_permissions')->delete();

Schema::connection('central')->create('plans', function ($t) {
    $t->id(); $t->string('name'); $t->string('slug'); $t->boolean('is_active')->default(true); $t->timestamps();
});
$freePlan = DB::connection('central')->table('plans')->insertGetId(['name' => 'Free', 'slug' => 'free']);
$customPlan = DB::connection('central')->table('plans')->insertGetId(['name' => 'Custom', 'slug' => 'custom']);
DB::connection('central')->table('company_subscriptions')->insert(['company_id' => $companyB->id, 'plan_id' => $freePlan, 'status' => 'active', 'ends_at' => now()->addMonth()]);
$entitlements->forget();
$check('free plan keeps its default list (employees on)', $fresh($companyB)->hasFeature('employees'));
$check('free plan keeps its default list (payroll off)', ! $fresh($companyB)->hasFeature('payroll'));
$setOverride($companyB, 'payroll', true);
$check('Super Admin can still grant a feature outside the plan', $fresh($companyB)->hasFeature('payroll'));
$clearOverrides();
DB::connection('central')->table('company_subscriptions')->update(['plan_id' => $customPlan]);
DB::connection('central')->table('plan_modules')->insert(['plan_id' => $customPlan, 'module_id' => $moduleId('tasks')]);
$check('plan_modules list grants listed feature', $fresh($companyB)->hasFeature('tasks'));
$check('plan_modules list denies unlisted feature', ! $fresh($companyB)->hasFeature('payroll'));
DB::connection('central')->table('companies')->where('id', $companyB->id)->update(['status' => 'suspended']);
$check('suspended company has no features', ! $fresh($companyB)->hasFeature('tasks'));

echo "\nRESULT: $pass passed, $fail failed\n";
