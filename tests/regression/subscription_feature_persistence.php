<?php
require __DIR__.'/feature_entitlements.php';

use App\Models\Central\{Company, Plan, SuperAdmin};
use App\Models\Module;
use App\Services\{FeatureEntitlements, PlanFeatureEntitlements};
use Illuminate\Support\Facades\{Auth, DB, Schema, Blade};
use Illuminate\Http\Request;

function persistenceCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
Schema::connection('central')->table('plans', fn ($t) => $t->text('features')->nullable());
Schema::connection('central')->table('plan_modules', fn ($t) => $t->timestamps());
Schema::connection('central')->create('super_admins', function ($t) {
    $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->rememberToken();
});
DB::connection('central')->table('super_admins')->insert(['id' => 1, 'name' => 'Platform', 'email' => 'platform@test.local', 'password' => bcrypt('test-password')]);
config(['session.driver' => 'array']);
app()->instance('session.store', app('session')->driver('array'));
Auth::forgetGuards();
$platform = SuperAdmin::findOrFail(1);
Auth::guard('super_admin')->login($platform);
$controller = app(App\Http\Controllers\SuperAdmin\CompanyController::class);
$post = function ($data, $method) use ($controller) {
    $request = Request::create('/super-admin/test', 'POST', $data, [], [], ['HTTP_ACCEPT' => 'application/json']);
    app()->instance('request', $request);
    $response = $controller->$method($request);
    persistenceCheck($response->getStatusCode() === 200 && $response->getData(true)['success'], 'Feature write failed: '.$response->getContent());
    return $response->getData(true);
};
$freshServices = function () {
    app(PlanFeatureEntitlements::class)->forget();
    app(FeatureEntitlements::class)->forget();
};
$modules = Module::all();
DB::connection('central')->table('company_modules')->delete();
DB::connection('central')->table('companies')->update(['status' => 'active']);
foreach (['free', 'gold', 'platinum', 'diamond'] as $tier) {
    $plan = Plan::firstOrCreate(['slug' => $tier], ['name' => strtoupper($tier)]);
    DB::connection('central')->table('plan_modules')->where('plan_id', $plan->id)->delete();
    $freshServices();
    $before = $modules->mapWithKeys(fn ($module) => [$module->slug => app(PlanFeatureEntitlements::class)->allows($plan, $module->slug)])->all();
    $target = $modules->firstWhere('slug', 'tasks');
    $post(['plan_id' => $plan->id, 'module_id' => $target->id, 'enabled' => false], 'togglePlanModule');
    $freshServices();
    persistenceCheck(!app(PlanFeatureEntitlements::class)->allows($plan->fresh(), 'tasks'), $tier.' off state did not persist');
    // Catalog reads and registry synchronization must not rewrite any saved decision.
    $stored = DB::connection('central')->table('plan_modules')->where('plan_id', $plan->id)->orderBy('module_id')->get()->toJson();
    app(App\Services\AdminWorkspaceModules::class)->all();
    persistenceCheck(DB::connection('central')->table('plan_modules')->where('plan_id', $plan->id)->orderBy('module_id')->get()->toJson() === $stored, 'Registry sync reset plan access');
    foreach ($modules->whereNotIn('slug', ['tasks']) as $module) {
        persistenceCheck(app(PlanFeatureEntitlements::class)->allows($plan, $module->slug) === $before[$module->slug], 'First edit changed sibling '.$tier.':'.$module->slug);
    }
    // Start with all features allowed, then round-trip every matrix module independently.
    foreach ($modules as $module) $post(['plan_id' => $plan->id, 'module_id' => $module->id, 'enabled' => true], 'togglePlanModule');
    foreach ($modules->reject(fn ($m) => app(FeatureEntitlements::class)->isLegacyAlias($m->slug)) as $module) {
        $post(['plan_id' => $plan->id, 'module_id' => $module->id, 'enabled' => false], 'togglePlanModule');
        $freshServices();
        persistenceCheck(!app(PlanFeatureEntitlements::class)->allows($plan->fresh(), $module->slug), $tier.' refreshed feature turned on: '.$module->slug);
        $post(['plan_id' => $plan->id, 'module_id' => $module->id, 'enabled' => true], 'togglePlanModule');
        persistenceCheck(app(PlanFeatureEntitlements::class)->allows($plan, $module->slug), $tier.' could not re-enable '.$module->slug);
    }
    $post(['plan_id' => $plan->id, 'module_id' => $target->id, 'enabled' => false], 'togglePlanModule');
    Auth::guard('super_admin')->logout();
    Auth::forgetGuards();
    Auth::guard('super_admin')->login(SuperAdmin::findOrFail(1));
    $freshServices();
    persistenceCheck(!app(PlanFeatureEntitlements::class)->allows($plan->fresh(), 'tasks'), $tier.' logout/login reset disabled feature');
    echo 'PASS: '.$tier.' every module on/off, first-edit isolation, registry sync and logout/login persistence.'.PHP_EOL;
}
$gold = Plan::where('slug', 'gold')->firstOrFail();
DB::connection('central')->table('company_subscriptions')->delete();
foreach ([$companyA, $companyB] as $company) DB::connection('central')->table('company_subscriptions')->insert([
    'company_id' => $company->id, 'plan_id' => $gold->id, 'status' => 'active', 'ends_at' => now()->addMonth(),
]);
$freshServices();
$task = $modules->firstWhere('slug', 'tasks');
$state = $controller->companyFeatureStates($companyA->id)->getData(true)['states'][$task->id];
persistenceCheck($state['override'] === null && $state['plan'] === 0 && $state['effective'] === 0, 'Unset company override did not inherit disabled plan feature');
$post(['company_id' => $companyA->id, 'module_id' => $task->id, 'enabled' => true], 'toggleCompanyOverride');
$freshServices();
persistenceCheck(Company::find($companyA->id)->hasFeature('tasks') && !Company::find($companyB->id)->hasFeature('tasks'), 'Company grant leaked to another company');
persistenceCheck(!app(PlanFeatureEntitlements::class)->allows($gold, 'tasks'), 'Company grant changed global plan');
$post(['company_id' => $companyA->id, 'module_id' => $task->id, 'enabled' => false], 'toggleCompanyOverride');
$post(['plan_id' => $gold->id, 'module_id' => $task->id, 'enabled' => true], 'togglePlanModule');
$freshServices();
persistenceCheck(!Company::find($companyA->id)->hasFeature('tasks') && Company::find($companyB->id)->hasFeature('tasks'), 'Explicit company off did not override plan on');
Auth::guard('super_admin')->logout(); Auth::forgetGuards(); $freshServices();
$context->company = Company::find($companyA->id);
persistenceCheck(!$admin->canViewModule('tasks') && !$admin->moduleToggleAllows('tasks'), 'Disabled feature leaked into sidebar');
persistenceCheck(!$call('tasks.index', $admin) && !$call('tasks.store', $admin, 'POST'), 'Disabled feature accessible through direct URL');
try { $post(['plan_id' => $gold->id, 'module_id' => $task->id, 'enabled' => true], 'togglePlanModule'); throw new RuntimeException('Non-platform plan write allowed'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { persistenceCheck($e->getStatusCode() === 403, 'Wrong plan authorization'); }
Auth::guard('super_admin')->login(SuperAdmin::findOrFail(1));
// A fully disabled plan must not fall back to tier defaults when no enabled rows remain.
DB::connection('central')->table('plan_modules')->where('plan_id', $gold->id)->update(['is_enabled' => false]); $freshServices();
foreach ($modules as $module) persistenceCheck(!app(PlanFeatureEntitlements::class)->allows($gold, $module->slug), 'All-disabled plan reverted to defaults');
$planBeforeSeed = DB::connection('central')->table('plan_modules')->orderBy('plan_id')->orderBy('module_id')->get()->toJson();
(new Database\Seeders\SubscriptionCatalogSeeder)->run(); $freshServices();
persistenceCheck(DB::connection('central')->table('plan_modules')->orderBy('plan_id')->orderBy('module_id')->get()->toJson() === $planBeforeSeed, 'Catalog seeding reset saved features');
Auth::guard('super_admin')->logout(); Auth::forgetGuards(); $freshServices();
$context->company = Company::find($companyB->id);
$testedRoutes = 0;
foreach (['admin', 'hr', 'manager', 'employee'] as $role) {
    $tenantActor = (new App\Models\User)->forceFill(['id' => 800, 'role' => $role, 'company_id' => $companyB->id]);
    foreach (config('workspace_modules') as $slug => $item) {
        persistenceCheck(!$tenantActor->featureEnabled($slug), 'Disabled matrix feature leaked to '.$role.': '.$slug);
        if (!$item['route'] || !Illuminate\Support\Facades\Route::has($item['route'])
            || in_array($item['route'], ['dashboard', 'admin.modules.index', 'admin.role-permissions.index'], true)) continue;
        persistenceCheck(!$call($item['route'], $tenantActor), 'Disabled route leaked to '.$role.': '.$item['route']);
        $testedRoutes++;
    }
}
Module::firstOrCreate(['slug' => 'contracts'], ['name' => 'Contracts', 'route_prefix' => 'admin/contracts', 'is_active' => true]);
if (Illuminate\Support\Facades\Route::has('admin.contracts.index')) {
    persistenceCheck(!$call('admin.contracts.index', $tenantActor), 'Catalog-only module bypassed route enforcement');
}
echo 'PASS: '.$testedRoutes.' registered route checks across admin/HR/manager/employee, plus catalog-only route protection.'.PHP_EOL;
if (!Schema::connection('tenant')->hasTable('app_settings')) Schema::connection('tenant')->create('app_settings', function ($t) { $t->id(); $t->string('key'); $t->text('value'); });
Auth::guard('web')->setUser((new App\Models\User)->forceFill(['id' => 801, 'role' => 'admin', 'company_id' => $companyB->id]));
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
config(['database.default' => 'tenant']);
$dashboard = preg_replace('/^@extends\([^\n]+\)\s*$/m', '', file_get_contents(resource_path('views/dashboard.blade.php')));
$dashboardHtml = Blade::render($dashboard."\n@yield('content')", ['currentCompany' => $context->company]);
foreach (['employees.index', 'projects.index', 'tasks.index', 'clients.index', 'tickets.index', 'attendance.report'] as $name) {
    persistenceCheck(!str_contains($dashboardHtml, 'href="'.route($name)), 'Disabled feature link leaked into dashboard: '.$name);
}
// Compile the unchanged layout with the new resolver; do not silently reset toggles in Blade.
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/subscriptions/index.blade.php'))), TOKEN_PARSE);
echo "PASS: company plan defaults, explicit on/off precedence, isolation, route/sidebar enforcement, authorization, all-disabled plans and seed preservation.\n";
