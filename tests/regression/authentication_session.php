<?php

require __DIR__ . '/upper_level_employees.php';

use App\Services\AuthenticationSession;
use App\Models\User;
use Illuminate\Support\Facades\{Auth, Cookie, DB, Schema};

config(['database.connections.session_db' => ['driver' => 'sqlite', 'database' => 'primary-test', 'prefix' => '']]);
DB::purge('session_db');
tenant('beta-test');
Schema::connection('tenant')->table('users', fn ($t) => $t->string('remember_token')->nullable());
$user = User::findOrFail(1);
$user->remember_token = 'old-remember-token'; $user->save();
$request = req([]);
$session = $request->session();
$session->start();
Auth::guard('web')->login($user);
$session->put(['current_company_id' => 2, 'current_company_db' => 'beta-test', 'url.intended' => '/superadmin/dashboard', 'password_confirmed_at' => time(), 'previous_account_filter' => 'private']);
$oldId = $session->getId(); $oldToken = $session->token();
config(['database.connections.mysql.database' => 'beta-test']);
$response = app(AuthenticationSession::class)->logout($request);
staffCheck($session->getId() !== $oldId && $session->token() !== $oldToken, 'Session ID/CSRF token survived logout.');
foreach (['current_company_id', 'current_company_db', 'url.intended', 'password_confirmed_at', 'previous_account_filter'] as $key) staffCheck(!$session->has($key), 'Stale session value survived: ' . $key);
staffCheck(!Auth::guard('web')->check() && !Auth::guard('super_admin')->check(), 'A guard remained authenticated.');
foreach (['web', 'super_admin'] as $guard) staffCheck(Cookie::hasQueued(Auth::guard($guard)->getRecallerName()), 'Remember cookie was not removed.');
staffCheck(config('database.connections.tenant.database') === 'primary-test' && config('database.connections.mysql.database') === 'primary-test', 'Logout retained previous company connection.');
staffCheck(str_contains($response->headers->get('Cache-Control'), 'no-store'), 'Logout redirect may be cached.');
tenant('beta-test');
staffCheck(User::findOrFail(1)->remember_token !== 'old-remember-token', 'Remember token was not revoked in original tenant.');

// An invalid next login cannot inherit a guard, workspace, or saved redirect.
$request = req(['email' => 'lead@test.com', 'password' => 'incorrect-password']);
$session->put(['current_company_id' => 2, 'current_company_db' => 'beta-test', 'url.intended' => '/admin']);
Auth::guard('web')->setUser(User::findOrFail(1));
invalid(fn () => app(App\Services\SecureCompanyLogin::class)->authenticate($request), 'email');
staffCheck(!Auth::guard('web')->check() && !Auth::guard('super_admin')->check() && !$session->has('current_company_id') && !$session->has('url.intended'), 'Failed login retained previous identity.');

$request = req(['email' => 'lead@test.com', 'password' => 'New-password-123']);
app(App\Services\SecureCompanyLogin::class)->authenticate($request);
staffCheck(Auth::user()->company_id === 1 && $session->get('current_company_id') === 1, 'Next credentials selected previous company.');
app(AuthenticationSession::class)->logout($request);
staffCheck(!Auth::check(), 'Staff logout retained authentication.');

Schema::connection('central')->create('super_admins', function ($t) {
    $t->id(); $t->string('name'); $t->string('email'); $t->string('password');
    $t->boolean('is_active')->default(true); $t->string('remember_token')->nullable(); $t->timestamps();
});
$platform = App\Models\Central\SuperAdmin::create(['name' => 'Platform', 'email' => 'platform@test.com', 'password' => Illuminate\Support\Facades\Hash::make('Platform-pass-123'), 'is_active' => true]);
Auth::guard('super_admin')->login($platform);
Auth::shouldUse('super_admin');
$session->put('url.intended', '/admin');
(new App\Http\Controllers\SuperAdmin\AuthController)->logout($request);
staffCheck(!Auth::guard('super_admin')->check() && !Auth::guard('web')->check() && !$session->has('url.intended'), 'Super Admin logout retained identity/redirect.');
staffCheck(Auth::getDefaultDriver() === 'web', 'Super Admin default guard leaked to next account.');
echo "PASS: logout revokes remembered login, rotates session, clears account state, restores primary DB and isolates subsequent credentials.\n";
