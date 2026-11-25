<?php

// Includes isolated primary/company databases and existing tenant authentication checks.
require __DIR__.'/company_deletion_login.php';

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\{SetTenantConnection, EnsureDeveloperAccess, EnsureCompanySubscriptionActive};
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\SecureCompanyLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, RateLimiter};

config(['database.connections.tenant.database'=>'primary']); DB::purge('tenant');
DB::connection('tenant')->table('users')->insert(['id'=>36,'company_id'=>1,'name'=>'Managed developer','email'=>'managed@example.test','personal_email'=>'personal@example.test','role'=>'developer','password'=>Hash::make('Managed@1234'),'raw_password'=>'Outdated@1234','is_active'=>true,'login_allowed'=>true]);
config(['database.connections.tenant.database'=>'alpha']); DB::purge('tenant');
DB::connection('tenant')->table('users')->insert(['id'=>36,'company_id'=>1,'name'=>'Company copy','email'=>'managed@example.test','role'=>'developer','password'=>Hash::make('Outdated@1234'),'is_active'=>true,'login_allowed'=>true]);
deletionCheck(loginAttempt('managed@example.test','Managed@1234'), 'Company-assigned primary developer credentials were rejected.');
deletionCheck(SecureCompanyLogin::isPlatformDeveloper(Auth::user()) && config('database.connections.tenant.database') === 'primary' && !session('current_company_id'), 'Developer login used the company copy or tenant session.');
deletionCheck(loginAttempt('personal@example.test','Managed@1234'), 'Developer credential inbox alias failed.');
deletionCheck(!loginAttempt('managed@example.test','Outdated@1234'), 'A stale tenant copy or displayed raw password bypassed the authoritative hash.');
deletionCheck(!loginAttempt('managed@example.test','Developer@123'), 'Guessed developer default password accepted.');
deletionCheck(!loginAttempt('managed@example.test','Managed@1234',1), 'Explicit company selection silently crossed into the platform developer realm.');
deletionCheck(loginAttempt('managed@example.test','Managed@1234'), 'Developer could not log in again after rejected credentials.');

// Exercise the real POST /login controller and the following authenticated request.
Auth::forgetGuards(); session()->flush();
$request=LoginRequest::create('/login','POST',['email'=>'managed@example.test','password'=>'Managed@1234']);
$request->setContainer(app()); $request->setLaravelSession(app('session')->driver()); app()->instance('request',$request);
RateLimiter::clear($request->throttleKey());
$response=(new AuthenticatedSessionController)->store($request);
deletionCheck($response->getTargetUrl() === route('developer.dashboard'), 'Successful developer login did not redirect to the developer workspace.');
Auth::forgetGuards();
$next=Request::create('/developer/dashboard','GET'); $next->setLaravelSession($request->session()); app()->instance('request',$next);
$seen=false;
$response=(new SetTenantConnection)->handle($next, function ($r) use (&$seen) {
    return (new EnsureCompanySubscriptionActive)->handle($r, function ($r) use (&$seen) {
        return (new EnsureDeveloperAccess)->handle($r, function () use (&$seen) { $seen=true; return response('developer workspace'); });
    });
});
deletionCheck($seen && $response->getStatusCode()===200 && Auth::id()===36, 'Reloaded developer session could not enter its workspace.');
foreach ([['is_active'=>false],['login_allowed'=>false],['archived_at'=>'2026-10-15 00:00:00']] as $blocked) {
    DB::connection('tenant')->table('users')->where('id',36)->update($blocked);
    deletionCheck(!loginAttempt('managed@example.test','Managed@1234'), 'Unavailable developer account authenticated.');
    DB::connection('tenant')->table('users')->where('id',36)->update(['is_active'=>true,'login_allowed'=>true,'archived_at'=>null]);
}
deletionCheck(loginAttempt('alpha@test.local','Admin-alpha') && session('current_company_id')===1 && !SecureCompanyLogin::isPlatformDeveloper(Auth::user()), 'Normal company admin login changed.');
echo "PASS: primary developer credentials, assigned company metadata, stale-copy/raw/default denial, personal email, redirect, reloaded workspace session, account-state checks and unchanged company authentication.\n";
