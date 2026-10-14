<?php

require __DIR__ . '/company_ai.php';

use App\Models\Central\SuperAdmin;
use App\Models\User;
use App\Services\{CompanyAiAgent, PlatformAiAgent};
use Illuminate\Support\Facades\{Auth, DB, Http, Schema};

Schema::connection('central')->create('super_admins', function ($t) {
    $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->boolean('is_active'); $t->timestamps();
});
DB::connection('central')->table('super_admins')->insert([
    ['id' => 1, 'name' => 'First Owner', 'email' => 'owner@test.com', 'password' => 'SECRET-ONE', 'is_active' => true],
    ['id' => 2, 'name' => 'Other Owner', 'email' => 'other@test.com', 'password' => 'SECRET-TWO', 'is_active' => true],
]);
tenant('alpha-test');
Auth::guard('web')->setUser(User::findOrFail(1)); session(['current_company_id' => 1]);
$platform = app(PlatformAiAgent::class);
denied(fn () => $platform->identity());
Auth::guard('super_admin')->setUser(SuperAdmin::findOrFail(1));
$identity = $platform->identity();
staffCheck($identity['name'] === 'First Owner Platform Assistant', 'Platform assistant name does not follow profile.');
denied(fn () => app(CompanyAiAgent::class)->company());
// Tenant switching must not influence platform identity or include tenant records.
config(['database.connections.tenant.database' => 'beta-test']); DB::purge('tenant');
$records = $platform->records('company registry');
$text = json_encode($records);
staffCheck(!str_contains($text, 'SECRET-') && !str_contains($text, 'Other Owner') && !str_contains($text, 'Alpha annual event'), 'Platform context leaked secrets, another profile, or tenant data.');
DB::connection('central')->table('super_admins')->where('id', 1)->update(['name' => 'Renamed Owner']);
staffCheck($platform->identity()['id'] === $identity['id'] && $platform->identity()['name'] === 'Renamed Owner Platform Assistant', 'Profile rename changed identity or retained stale name.');
Auth::guard('super_admin')->setUser(SuperAdmin::findOrFail(2));
staffCheck($platform->identity()['id'] !== $identity['id'], 'Super Admin agents share an identity.');
$request = req(['question' => 'company registry']); $request->headers->set('X-Company-Workspace', $identity['id']);
denied(fn () => (new App\Http\Controllers\PlatformAiController)->ask($request, $platform), 409);
Auth::guard('super_admin')->setUser(SuperAdmin::findOrFail(1));
Http::swap(new Illuminate\Http\Client\Factory);
Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['in_scope' => true, 'answer' => 'Registry summary.', 'source_ids' => ['platform:registry']])]]]])]);
$result = $platform->answer('company registry');
staffCheck($result['sources'][0]['id'] === 'platform:registry', 'Platform answer not grounded in central sources.');
$request = req(['question' => 'company registry']); $request->headers->set('X-Company-Workspace', $identity['id']);
$response = (new App\Http\Controllers\PlatformAiController)->ask($request, $platform)->getData(true);
staffCheck($response['assistant_name'] === 'Renamed Owner Platform Assistant', 'Chat response retained the old platform profile name.');
$payload = json_encode(Http::recorded()->first()[0]->data());
staffCheck(str_contains($payload, 'authorized_platform_records') && !str_contains($payload, 'authorized_company_records') && !str_contains($payload, 'SECRET-'), 'Platform/company provider contexts mixed.');
DB::connection('central')->table('super_admins')->where('id', 1)->update(['is_active' => false]);
denied(fn () => $platform->identity());
Auth::guard('super_admin')->forgetUser();
tenant('alpha-test'); Auth::guard('web')->setUser(User::findOrFail(1)); session(['current_company_id' => 1]);
$companyAgent = app(CompanyAiAgent::class);
$before = $companyAgent->identity();
DB::connection('central')->table('companies')->where('id', 1)->update(['name' => 'Fresh Alpha']);
DB::connection('tenant')->table('users')->where('id', 1)->update(['name' => 'Fresh Admin']);
$after = $companyAgent->identity();
staffCheck($after['id'] === $before['id'] && $after['name'] === 'Fresh Alpha Assistant · Fresh Admin', 'Company/profile rename failed or changed identity.');
staffCheck(data_get($companyAgent->company()->settings, 'ai_agent.name') === 'Fresh Alpha Assistant', 'Stored company branding was not synchronized.');
config(['database.connections.session_db' => ['driver' => 'sqlite', 'database' => 'platform-primary-test', 'prefix' => '']]); DB::purge('session_db');
Schema::connection('session_db')->create('users', function ($t) {
    $t->id(); $t->integer('company_id')->nullable(); $t->string('name'); $t->string('role'); $t->boolean('is_active'); $t->boolean('login_allowed');
});
DB::connection('session_db')->table('users')->insert(['id' => 1, 'company_id' => null, 'name' => 'Legacy Owner', 'role' => 'superadmin', 'is_active' => true, 'login_allowed' => true]);
$legacy = new User(['company_id' => null, 'name' => 'Stale legacy name', 'role' => 'superadmin']); $legacy->id = 1;
Auth::guard('web')->setUser($legacy);
$legacyIdentity = $platform->identity();
staffCheck($legacyIdentity['name'] === 'Legacy Owner Platform Assistant' && $legacyIdentity['id'] !== $identity['id'], 'Legacy platform identity mixed with central guard or tenant.');
DB::connection('session_db')->table('users')->where('id', 1)->update(['role' => 'admin', 'company_id' => 1]);
denied(fn () => $platform->identity());
echo "PASS: isolated personal platform agents, central-only records, profile/company rename synchronization, stale-account checks and disabled platform accounts.\n";
