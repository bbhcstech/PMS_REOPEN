<?php

require __DIR__ . '/upper_level_employees.php';

use App\Models\Central\Company;
use App\Models\User;
use App\Services\{CompanyAiAgent, CompanyAiKnowledge};
use Illuminate\Support\Facades\{Auth, DB, Http, Schema};

Schema::connection('central')->table('companies', function ($t) {
    $t->string('website')->nullable(); $t->string('address')->nullable();
});
$company = Company::create(['name' => 'New Company', 'email' => 'new@test.com', 'db_name' => 'new-test', 'employee_id_prefix' => 'NEW', 'settings' => ['existing' => 'kept']]);
staffCheck(data_get($company->settings, 'ai_agent.id') && data_get($company->settings, 'ai_agent.name') === 'New Company Assistant', 'New company agent was not created.');
$identity = data_get($company->settings, 'ai_agent.id');
$company->update(['name' => 'Renamed Company', 'settings' => ['existing' => 'updated']]);
staffCheck(data_get($company->settings, 'ai_agent.id') === $identity && data_get($company->settings, 'ai_agent.name') === 'Renamed Company Assistant', 'Agent identity/name lost on company update.');
staffCheck($company->settings['existing'] === 'updated', 'Agent damaged unrelated company settings.');

tenant('alpha-test');
Schema::connection('tenant')->create('modules', function ($t) {
    $t->id(); $t->string('slug'); $t->boolean('is_active');
});
Schema::connection('tenant')->create('role_permissions', function ($t) {
    $t->id(); $t->string('role'); $t->integer('module_id'); $t->boolean('can_view');
});
$user = User::findOrFail(1); Auth::guard('web')->setUser($user);
session(['current_company_id' => 1, 'current_company_db' => 'alpha-test']);
Schema::connection('tenant')->create('events', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('title'); $t->text('description'); $t->string('status');
});
DB::connection('tenant')->table('events')->insert([
    ['id' => 1, 'company_id' => 1, 'title' => 'Alpha annual event', 'description' => 'Only Alpha colleagues', 'status' => 'published'],
    ['id' => 2, 'company_id' => 2, 'title' => 'Beta secret event', 'description' => 'Beta private data', 'status' => 'published'],
    ['id' => 3, 'company_id' => 1, 'title' => 'Alpha draft event', 'description' => 'Unpublished data', 'status' => 'draft'],
]);
Schema::connection('tenant')->create('tasks', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('title'); $t->integer('assigned_to'); $t->string('password')->nullable();
});
DB::connection('tenant')->table('tasks')->insert([
    ['id' => 1, 'company_id' => 1, 'title' => 'My task', 'assigned_to' => 1, 'password' => 'DO-NOT-SEND'],
    ['id' => 2, 'company_id' => 1, 'title' => 'Other task', 'assigned_to' => 2, 'password' => 'DO-NOT-SEND'],
]);
$agent = app(CompanyAiAgent::class);
staffCheck(data_get($agent->company()->settings, 'ai_agent.id'), 'Existing company agent was not initialized.');
$records = app(CompanyAiKnowledge::class)->retrieve($agent->company(), $user, 'company events');
staffCheck(!str_contains(json_encode($records), 'Beta'), 'Foreign records leaked to knowledge retrieval.');
$viewer = User::findOrFail(1); $viewer->role = 'employee'; Auth::guard('web')->setUser($viewer);
$records = app(CompanyAiKnowledge::class)->retrieve($agent->company(), $viewer, 'events');
staffCheck(!str_contains(json_encode($records), 'draft'), 'Unpublished event exposed to employee.');
$records = app(CompanyAiKnowledge::class)->retrieve($agent->company(), $viewer, 'tasks');
staffCheck(count($records) === 1 && $records[0]['id'] === 'tasks:1' && !str_contains(json_encode($records), 'DO-NOT-SEND'), 'Assignment or secret field filtering failed.');
Auth::guard('web')->setUser($user);
// Limits are partitioned by company as well as user IDs that overlap between tenants.
$limiter = Illuminate\Support\Facades\RateLimiter::limiter('company-ai');
$request = req([]); $request->setUserResolver(fn () => $user);
$firstLimit = $limiter($request)->key;
$other = clone $user; $other->company_id = 2;
$request->setUserResolver(fn () => $other);
staffCheck($firstLimit !== $limiter($request)->key, 'AI request limits mixed company identities.');
$request = req(['question' => 'events', 'company_id' => 2]);
invalid(fn () => (new App\Http\Controllers\CompanyAiController)->ask($request, $agent), 'company_id');
$request = req(['question' => 'events']); $request->headers->set('X-Company-Workspace', '2');
denied(fn () => (new App\Http\Controllers\CompanyAiController)->ask($request, $agent), 409);
config(['company_ai.api_key' => 'fake-test-key']);
Http::preventStrayRequests();
Http::swap(new Illuminate\Http\Client\Factory);
Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['in_scope' => true, 'answer' => 'Alpha annual event is published.', 'source_ids' => ['events:1']])]]]])]);
$result = $agent->answer('events');
staffCheck($result['sources'][0]['id'] === 'events:1', 'Grounded answer missing sources.');
$sent = Http::recorded()->contains(function ($pair) {
    $request = $pair[0];
    $payload = json_encode($request->data());
    return !str_contains($payload, 'Beta') && !str_contains($payload, 'DO-NOT-SEND') && str_contains($payload, 'authorized_company_records');
});
staffCheck($sent, 'Provider request leaked unrelated records or omitted company context.');
$request = req(['question' => 'events']); $request->headers->set('X-Company-Workspace', 'company:1:user:1');
staffCheck((new App\Http\Controllers\CompanyAiController)->ask($request, $agent)->getStatusCode() === 200, 'Valid company page cannot ask questions.');
Http::swap(new Illuminate\Http\Client\Factory);
Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['in_scope' => true, 'answer' => 'Forged answer', 'source_ids' => ['events:999']])]]]])]);
staffCheck(!$agent->answer('events')['sources'], 'Forged model sources accepted.');
Http::swap(new Illuminate\Http\Client\Factory);
Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['in_scope' => false, 'answer' => 'Unrelated answer', 'source_ids' => []])]]]])]);
staffCheck(!$agent->answer('company tell me about planets')['sources'], 'Out-of-scope model answer accepted.');
Http::swap(new Illuminate\Http\Client\Factory);
Http::fake();
staffCheck(!$agent->answer('quantum physics theory')['sources'], 'Unrelated question produced sources.');
staffCheck(Http::recorded()->isEmpty(), 'Unrelated question reached provider.');
session(['current_company_id' => 2]); denied(fn () => $agent->answer('events'));
session(['current_company_id' => 1]); config(['database.connections.tenant.database' => 'beta-test']); DB::purge('tenant');
denied(fn () => $agent->answer('events'));
tenant('alpha-test'); session(['current_company_id' => 1]);
$user->login_allowed = false; Auth::guard('web')->setUser($user); denied(fn () => $agent->answer('events'));
echo "PASS: automatic agent identity, rename stability, authorized context, tenant isolation, secrets exclusion, unsupported answer refusal and disabled-account denial.\n";
