<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\{EmployeeController, EmployeeInviteController};
use App\Http\Controllers\Admin\Settings\EmailSettingsController;
use App\Mail\EmployeeInvite;
use App\Models\{AppSetting, User};
use App\Services\CompanyMailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Crypt, DB, Hash, Mail, Schema, URL};
use Illuminate\Validation\ValidationException;

function inviteCheck(bool $condition, string $message): void {
    if (! $condition) throw new RuntimeException($message);
}
function inviteRequest(string $url, array $data = [], string $method = 'POST'): Request {
    $request = Request::create($url, $method, $data);
    $request->headers->set('Accept', 'application/json');
    $request->setLaravelSession(app('session.store'));
    app()->instance('request', $request);
    app('url')->setRequest($request);
    return $request;
}

$tenantFile = tempnam(sys_get_temp_dir(), 'pms-invites-');
try {
    config([
        'database.connections.tenant' => ['driver' => 'sqlite', 'database' => $tenantFile, 'prefix' => ''],
        'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        'database.default' => 'tenant', 'session.driver' => 'array', 'mail.default' => 'log',
    ]);
    DB::purge('tenant'); DB::purge('central');
    $app['session']->start();
    Schema::connection('central')->create('companies', function ($t) {
        $t->id(); $t->string('name'); $t->string('short_name')->nullable(); $t->string('employee_id_prefix')->nullable();
        $t->string('db_name'); $t->text('settings')->nullable(); $t->softDeletes(); $t->timestamps();
    });
    DB::connection('central')->table('companies')->insert(['id' => 1, 'name' => 'Fixture Company', 'db_name' => $tenantFile, 'settings' => '{}']);
    Schema::create('users', function ($t) {
        $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('password');
        $t->unsignedBigInteger('company_id')->nullable(); $t->string('role'); $t->boolean('login_allowed')->default(true);
        $t->string('remember_token')->nullable(); $t->timestamp('archived_at')->nullable(); $t->timestamps();
    });
    Schema::create('employee_details', function ($t) {
        $t->id(); $t->unsignedBigInteger('user_id'); $t->string('status'); $t->string('business_address');
        $t->string('employee_id')->unique(); $t->timestamps();
    });
    Schema::create('app_settings', function ($t) {
        $t->id(); $t->string('key')->unique(); $t->text('value')->nullable();
        $t->string('label')->nullable(); $t->string('page')->nullable(); $t->string('section')->nullable();
        $t->string('type')->nullable(); $t->timestamps();
    });
    $actor = new User;
    $actor->forceFill(['id' => 100, 'name' => 'Administrator', 'company_id' => 1, 'role' => 'admin']);
    Auth::guard('web')->setUser($actor);
    $app->forgetInstance(CompanyMailSettings::class);
    $service = app(CompanyMailSettings::class);
    $controller = new EmployeeController;
    try {
        $controller->sendInvite(inviteRequest('/employees/send-invite', ['email' => 'missing@example.test']));
        throw new RuntimeException('Log-only delivery reported success');
    } catch (ValidationException $e) {
        inviteCheck(str_contains($e->errors()['email'][0], 'SMTP'), 'Missing configuration error is unclear');
    }
    inviteCheck(User::count() === 0, 'Missing SMTP created an account');

    $settings = new EmailSettingsController;
    $smtp = [
        'mail_mailer' => 'smtp', 'mail_host' => 'smtp.example.test', 'mail_port' => 587,
        'mail_username' => 'fixture-user', 'mail_password' => 'fixture-password', 'mail_encryption' => 'tls',
        'mail_from_address' => 'hr@example.test', 'mail_from_name' => 'Fixture HR',
    ];
    $settings->update(inviteRequest('/admin/settings/email', $smtp));
    $stored = AppSetting::valueFor('mail_password');
    inviteCheck(str_starts_with($stored, 'encrypted:') && !str_contains($stored, 'fixture-password'), 'Password stored unencrypted');
    inviteCheck(Crypt::decryptString(substr($stored, 10)) === 'fixture-password', 'Password cannot be decrypted');
    inviteCheck(config('mail.mailers.smtp.password') === 'fixture-password', 'Saved credentials not applied');
    inviteCheck(config('mail.mailers.smtp.host') === 'smtp.example.test' && config('mail.from.address') === 'hr@example.test', 'Saved SMTP sender/host ignored');
    inviteCheck(config('mail.mailers.smtp.require_tls') === true, 'STARTTLS not required');
    $transport = Mail::mailer('smtp')->getSymfonyTransport();
    inviteCheck($transport->getUsername() === 'fixture-user' && $transport->getStream()->getHost() === 'smtp.example.test'
        && $transport->getStream()->getPort() === 587 && $transport->isTlsRequired(), 'Constructed SMTP transport ignored saved configuration');
    $data = $settings->index()->getData()['settings'];
    inviteCheck($data['mail_password'] === '' && $data['has_mail_password'], 'Settings exposed saved password');
    $smtp['mail_password'] = '';
    $settings->update(inviteRequest('/admin/settings/email', $smtp));
    inviteCheck(AppSetting::valueFor('mail_password') === $stored, 'Blank password replaced saved credentials');
    foreach (['mail_port' => 0, 'mail_encryption' => 'invalid'] as $key => $value) {
        try {
            $settings->update(inviteRequest('/admin/settings/email', array_replace($smtp, [$key => $value])));
            throw new RuntimeException('Invalid SMTP configuration accepted');
        } catch (ValidationException $e) {
            inviteCheck(isset($e->errors()[$key]), 'Wrong SMTP validation error');
        }
    }
    AppSetting::where('key', 'mail_encryption')->update(['value' => 'ssl']);
    AppSetting::where('key', 'mail_port')->update(['value' => '465']);
    $service->apply();
    inviteCheck(config('mail.mailers.smtp.scheme') === 'smtps', 'Implicit TLS not configured');
    AppSetting::where('key', 'mail_encryption')->update(['value' => 'none']);
    $service->apply();
    inviteCheck(config('mail.mailers.smtp.auto_tls') === false, 'No-encryption selection ignored');
    AppSetting::where('key', 'mail_encryption')->update(['value' => 'tls']);
    AppSetting::where('key', 'mail_port')->update(['value' => '587']);
    Auth::guard('web')->forgetUser();
    $service->apply();
    inviteCheck(config('mail.default') === 'log' && config('mail.mailers.smtp.password') !== 'fixture-password', 'Tenant SMTP leaked outside signed-in workspace');
    Auth::guard('web')->setUser($actor);

    // Exercise the administrator's test-email action without a network connection.
    $manager = Mail::getFacadeRoot();
    $probe = new class {
        public bool $sent = false;
        public function forgetMailers() {}
        public function raw($body, $callback) { $this->sent = true; }
    };
    Mail::swap($probe);
    $settings->testEmail(inviteRequest('/admin/settings/email/test', ['test_email' => 'test@example.test']));
    inviteCheck($probe->sent, 'SMTP test action did not use configured mail');
    AppSetting::where('key', 'mail_mailer')->update(['value' => 'log']);
    $probe->sent = false;
    $settings->testEmail(inviteRequest('/admin/settings/email/test', ['test_email' => 'test@example.test']));
    inviteCheck(! $probe->sent && session('error'), 'Log-only SMTP test reported a delivery');
    AppSetting::where('key', 'mail_mailer')->update(['value' => 'smtp']);
    Mail::swap($manager);

    // Framework mail fake: test content and flow without contacting an SMTP server.
    Mail::fake();
    $response = $controller->sendInvite(inviteRequest('/employees/send-invite', ['email' => 'MAILTO: New@Example.test ', 'message' => 'Welcome aboard']));
    inviteCheck($response->getStatusCode() === 200, 'Configured invitation failed');
    inviteCheck(Mail::sent(\App\Mail\EmployeeOnboardingInvitationMail::class, fn ($mail) => $mail->hasTo('new@example.test') && $mail->personalMessage === 'Welcome aboard' && $mail->company?->name === 'Fixture Company')->count() === 1, 'Invitation recipient/content incorrect');
    inviteCheck(!User::where('email', 'new@example.test')->exists(), 'Invitation must not immediately create an official user account');
    $invited = \App\Models\EmployeeInvitation::where('email', 'new@example.test')->firstOrFail();
    $html = (new \App\Mail\EmployeeOnboardingInvitationMail(
        \App\Models\Central\Company::on('central')->find(1),
        $response->getData(true)['url'],
        'Welcome aboard',
        $invited->expires_at,
        'new@example.test',
        false
    ))->render();
    inviteCheck(str_contains($html, 'Welcome aboard') && str_contains($html, '/onboarding/'), 'Invitation email template failed');

    // SMTP failure must not leave an active invitation or user account.
    $fake = Mail::getFacadeRoot();
    Mail::swap(new class {
        public function forgetMailers() {}
        public function to($recipient) { return $this; }
        public function send($mail) { throw new RuntimeException('Simulated transport failure'); }
    });
    $response = $controller->sendInvite(inviteRequest('/employees/send-invite', ['email' => 'failure@example.test']));
    inviteCheck($response->getStatusCode() === 500 && !User::where('email', 'failure@example.test')->exists() && !\App\Models\EmployeeInvitation::where('email', 'failure@example.test')->exists(), 'Failed send kept an account or invitation');
    Mail::swap($fake);

    AppSetting::where('key', 'mail_mailer')->update(['value' => 'log']);
    $response = $controller->generateInviteLink(inviteRequest('/employees/invite-link', ['email' => 'link@example.test']));
    inviteCheck($response->getStatusCode() === 200, 'Manual invitation requires SMTP');
    $link = $response->getData(true)['url'];
    inviteCheck(str_contains($link, '/onboarding/'), 'Manual invitation link did not point to self-onboarding route');
    $linkInv = \App\Models\EmployeeInvitation::where('email', 'link@example.test')->firstOrFail();
    inviteCheck($linkInv->expires_at->greaterThan(now()->addHours(71)) && $linkInv->expires_at->lessThanOrEqualTo(now()->addHours(73)), 'Default onboarding expiry should be 72 hours');

    // Verify signed account activation route (used after HR approval) still enforces signature & single-use
    $linkUser = User::create([
        'name' => 'Approved Candidate', 'email' => 'approved@example.test', 'password' => Hash::make('TempRandom#99'),
        'company_id' => 1, 'role' => 'employee', 'login_allowed' => 1,
    ]);
    $activationLink = EmployeeInviteController::inviteUrl($linkUser, 1);
    $request = inviteRequest($activationLink, [], 'GET');
    inviteCheck(URL::hasValidSignature($request), 'Activation link signature invalid');
    $accept = new EmployeeInviteController;
    $view = $accept->show($request, 1, $linkUser->id);
    inviteCheck($view->getData()['invalid'] === false, 'Valid activation link cannot open password setup');
    $submitUrl = $view->getData()['submitUrl'];
    $request = inviteRequest($submitUrl, ['name' => 'Invited Employee', 'password' => 'Fixture@12345', 'password_confirmation' => 'Fixture@12345']);
    $accept->complete($request, 1, $linkUser->id);
    inviteCheck(Hash::check('Fixture@12345', $linkUser->fresh()->password), 'Onboarding password not saved');
    $response = $accept->show(inviteRequest($activationLink, [], 'GET'), 1, $linkUser->id);
    inviteCheck($response->getStatusCode() === 410, 'Used activation link can be reused');
    inviteCheck(!URL::hasValidSignature(inviteRequest(str_replace('company=1', 'company=2', $activationLink).'&changed=1', [], 'GET')), 'Tampered signature accepted');

    Auth::guard('web')->setUser($actor);
    $response = $controller->generateInviteLink(inviteRequest('/employees/invite-link', ['email' => 'approved@example.test']));
    inviteCheck($response->getStatusCode() === 422, 'Manual link could invite an already existing employee');
    $actor->role = 'employee';
    try {
        $controller->generateInviteLink(inviteRequest('/employees/invite-link', ['email' => 'blocked@example.test']));
        throw new RuntimeException('Employee could issue invitations');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        inviteCheck($e->getStatusCode() === 403, 'Wrong invitation permission response');
    }
    foreach (['admin/employees/index', 'admin/settings/email', 'auth/employee-invite-accept'] as $view) {
        token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/'.$view.'.blade.php'))), TOKEN_PARSE);
    }
    echo "PASS: SMTP settings/encryption/password preservation, mail content, missing SMTP, failed-send rollback, manual links, seven-day signatures, onboarding, single-use protection, role checks and templates. No real email sent.\n";
} finally {
    DB::disconnect('tenant');
    unlink($tenantFile);
}
