<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\EmployeeOnboardingController as AdminOnboardingController;
use App\Http\Controllers\{EmployeeController, EmployeeInviteController, PublicEmployeeOnboardingController};
use App\Mail\{
    EmployeeApplicationApprovedMail,
    EmployeeApplicationCorrectionMail,
    EmployeeApplicationRejectedMail,
    EmployeeApplicationSubmittedMail,
    EmployeeOnboardingEmailOtpMail,
    EmployeeOnboardingInvitationMail
};
use App\Models\{
    AppSetting,
    Department,
    Designation,
    EmployeeDetail,
    EmployeeInvitation,
    EmployeeOnboardingApplication,
    EmployeeOnboardingAuditLog,
    ParentDepartment,
    User
};
use App\Services\{CompanyMailSettings, EmployeeCreationService, EmployeeOnboardingService, GovernmentIdDobVerifier};
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Auth, DB, Hash, Mail, Schema, Storage};
use Illuminate\Validation\ValidationException;

function obCheck(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function obRequest(string $url, array $data = [], string $method = 'POST', array $files = []): Request
{
    $request = Request::create($url, $method, $data, [], $files);
    $request->headers->set('Accept', 'application/json');
    $request->setLaravelSession(app('session.store'));
    app()->instance('request', $request);
    app('url')->setRequest($request);

    return $request;
}

$tenantFile = tempnam(sys_get_temp_dir(), 'pms-onboarding-');
$privateRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pms-ob-storage-' . uniqid();
mkdir($privateRoot, 0777, true);
$createdPublicFiles = [];

try {
    config([
        'database.connections.tenant' => ['driver' => 'sqlite', 'database' => $tenantFile, 'prefix' => ''],
        'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        'database.default' => 'tenant',
        'session.driver' => 'array',
        'filesystems.disks.local.root' => $privateRoot,
    ]);
    DB::purge('tenant');
    DB::purge('central');
    $app['session']->start();

    Schema::connection('central')->create('companies', function ($t) {
        $t->id();
        $t->string('name');
        $t->string('short_name')->nullable();
        $t->string('email')->nullable();
        $t->string('phone')->nullable();
        $t->string('website')->nullable();
        $t->string('address')->nullable();
        $t->string('logo')->nullable();
        $t->string('employee_id_prefix')->nullable();
        $t->string('db_name');
        $t->text('settings')->nullable();
        $t->string('status')->default('active');
        $t->softDeletes();
        $t->timestamps();
    });
    DB::connection('central')->table('companies')->insert([
        ['id' => 1, 'name' => 'Acme Corp', 'short_name' => 'ACME', 'email' => 'hr@acme.test', 'phone' => '+919999999999', 'db_name' => $tenantFile, 'employee_id_prefix' => 'ACME-EMP', 'settings' => '{}', 'status' => 'active'],
        ['id' => 2, 'name' => 'Other Org', 'short_name' => 'OTHR', 'email' => 'hr@other.test', 'phone' => '+918888888888', 'db_name' => $tenantFile, 'employee_id_prefix' => 'OTHR-EMP', 'settings' => '{}', 'status' => 'active'],
    ]);

    Schema::create('users', function ($t) {
        $t->id();
        $t->string('name');
        $t->string('email')->unique();
        $t->string('mobile')->nullable();
        $t->string('password');
        $t->string('raw_password')->nullable();
        $t->unsignedBigInteger('company_id')->nullable();
        $t->unsignedBigInteger('company_staff_role_id')->nullable();
        $t->string('role')->default('employee');
        $t->string('profile_image')->nullable();
        $t->boolean('login_allowed')->default(true);
        $t->boolean('email_notifications')->default(true);
        $t->boolean('is_active')->default(true);
        $t->unsignedBigInteger('reports_to_id')->nullable();
        $t->unsignedBigInteger('hr_id')->nullable();
        $t->unsignedBigInteger('manager_id')->nullable();
        $t->timestamp('email_verified_at')->nullable();
        $t->string('remember_token')->nullable();
        $t->timestamp('archived_at')->nullable();
        $t->timestamps();
    });

    Schema::create('employee_details', function ($t) {
        $t->id();
        $t->unsignedBigInteger('user_id');
        $t->unsignedBigInteger('company_id')->nullable();
        $t->unsignedBigInteger('designation_id')->nullable();
        $t->unsignedBigInteger('parent_dpt_id')->nullable();
        $t->unsignedBigInteger('department_id')->nullable();
        $t->string('employee_id')->unique();
        $t->string('country')->nullable();
        $t->string('mobile')->nullable();
        $t->string('gender')->nullable();
        $t->date('joining_date')->nullable();
        $t->date('dob')->nullable();
        $t->string('government_id_card')->nullable();
        $t->string('government_id_verification_status')->nullable();
        $t->unsignedBigInteger('reporting_to')->nullable();
        $t->string('language')->nullable();
        $t->integer('user_role')->nullable();
        $t->text('address')->nullable();
        $t->text('about')->nullable();
        $t->text('directory_about')->nullable();
        $t->boolean('login_allowed')->default(true);
        $t->boolean('email_notifications')->default(true);
        $t->decimal('hourly_rate', 10, 2)->nullable();
        $t->text('skills')->nullable();
        $t->string('linkedin_url')->nullable();
        $t->string('portfolio_url')->nullable();
        $t->string('facebook_url')->nullable();
        $t->string('instagram_url')->nullable();
        $t->string('x_url')->nullable();
        $t->string('cv_path')->nullable();
        $t->date('probation_end_date')->nullable();
        $t->date('notice_start_date')->nullable();
        $t->date('notice_end_date')->nullable();
        $t->string('employment_type')->nullable();
        $t->string('marital_status')->nullable();
        $t->string('business_address')->nullable();
        $t->string('status')->default('Active');
        $t->date('exit_date')->nullable();
        $t->timestamps();
    });

    Schema::create('designations', function ($t) {
        $t->id();
        $t->unsignedBigInteger('company_id')->nullable();
        $t->string('name');
        $t->integer('level')->default(1);
        $t->string('status')->default('Active');
        $t->unsignedBigInteger('added_by')->nullable();
        $t->unsignedBigInteger('last_updated_by')->nullable();
        $t->timestamps();
    });
    Schema::create('parent_departments', function ($t) {
        $t->id();
        $t->unsignedBigInteger('company_id')->nullable();
        $t->string('dpt_name');
        $t->string('dpt_code')->nullable();
        $t->string('status')->default('Active');
        $t->timestamps();
    });
    Schema::create('departments', function ($t) {
        $t->id();
        $t->unsignedBigInteger('company_id')->nullable();
        $t->unsignedBigInteger('parent_dpt_id')->nullable();
        $t->string('dpt_name');
        $t->string('dpt_code')->nullable();
        $t->string('status')->default('Active');
        $t->timestamps();
    });
    Schema::create('countries', function ($t) {
        $t->id();
        $t->string('name');
        $t->string('iso_code')->nullable();
        $t->string('phone_code')->nullable();
        $t->integer('min_digits')->default(10);
        $t->integer('max_digits')->default(10);
        $t->string('flag_url')->nullable();
        $t->string('status')->default('Active');
        $t->timestamps();
    });
    Schema::create('app_settings', function ($t) {
        $t->id();
        $t->string('key')->unique();
        $t->text('value')->nullable();
        $t->string('label')->nullable();
        $t->string('page')->nullable();
        $t->string('section')->nullable();
        $t->string('type')->nullable();
        $t->timestamps();
    });
    Schema::create('notifications', function ($t) {
        $t->uuid('id')->primary();
        $t->string('type');
        $t->morphs('notifiable');
        $t->text('data');
        $t->timestamp('read_at')->nullable();
        $t->timestamps();
    });
    Schema::create('workspace_modules', function ($t) {
        $t->id();
        $t->string('slug')->unique();
        $t->string('name');
        $t->boolean('is_active')->default(true);
        $t->timestamps();
    });
    Schema::create('role_permissions', function ($t) {
        $t->id();
        $t->string('role');
        $t->unsignedBigInteger('module_id');
        $t->boolean('can_view')->default(true);
        $t->boolean('can_create')->default(true);
        $t->boolean('can_edit')->default(true);
        $t->boolean('can_delete')->default(true);
        $t->boolean('can_approve')->default(true);
        $t->timestamps();
    });
    DB::table('countries')->insert(['name' => 'India', 'iso_code' => 'IN', 'phone_code' => '+91', 'min_digits' => 10, 'max_digits' => 10, 'status' => 'Active']);
    DB::table('designations')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Software Engineer', 'level' => 2, 'status' => 'Active']);
    DB::table('parent_departments')->insert(['id' => 1, 'company_id' => 1, 'dpt_name' => 'Engineering', 'dpt_code' => 'DEP-0001', 'status' => 'Active']);
    DB::table('departments')->insert(['id' => 1, 'company_id' => 1, 'parent_dpt_id' => 1, 'dpt_name' => 'Platform', 'dpt_code' => 'SUB-01', 'status' => 'Active']);

    $admin = User::create([
        'name' => 'HR Admin', 'email' => 'hradmin@acme.test', 'password' => Hash::make('Secret@123'),
        'company_id' => 1, 'role' => 'admin', 'login_allowed' => 1,
    ]);
    $otherAdmin = User::create([
        'name' => 'Other Admin', 'email' => 'admin@other.test', 'password' => Hash::make('Secret@123'),
        'company_id' => 2, 'role' => 'admin', 'login_allowed' => 1,
    ]);
    $regularEmployee = User::create([
        'name' => 'Regular Emp', 'email' => 'emp@acme.test', 'password' => Hash::make('Secret@123'),
        'company_id' => 1, 'role' => 'employee', 'login_allowed' => 1,
    ]);

    AppSetting::insert([
        ['key' => 'mail_mailer', 'value' => 'smtp'],
        ['key' => 'mail_host', 'value' => 'smtp.example.test'],
        ['key' => 'mail_port', 'value' => '587'],
        ['key' => 'mail_username', 'value' => 'hr@acme.test'],
        ['key' => 'mail_password', 'value' => 'secret'],
        ['key' => 'mail_encryption', 'value' => 'tls'],
        ['key' => 'mail_from_address', 'value' => 'hr@acme.test'],
        ['key' => 'mail_from_name', 'value' => 'Acme HR'],
    ]);

    $service = app(EmployeeOnboardingService::class);
    $service->ensureSchema();
    $empController = new EmployeeController;
    $publicController = new PublicEmployeeOnboardingController($service);
    $adminOnboardingController = new AdminOnboardingController($service, app(EmployeeCreationService::class));

    Auth::guard('web')->setUser($admin);
    session(['current_company_id' => 1, 'current_company_db' => $tenantFile, 'current_company_name' => 'Acme Corp']);
    Mail::fake();

    // 1. Invite By Email -> creates EmployeeInvitation (not User), sends email with /onboarding/ URL
    $inviteRes = $empController->sendInvite(obRequest('/employees/send-invite', [
        'email' => 'candidate1@example.test',
        'message' => 'Please complete your profile by Friday.',
    ]));
    obCheck($inviteRes->getStatusCode() === 200, 'Invite by email failed');
    $inviteData = $inviteRes->getData(true);
    obCheck(str_contains($inviteData['url'], '/onboarding/'), 'Invite URL missing /onboarding/ segment');
    $token = basename(parse_url($inviteData['url'], PHP_URL_PATH));
    obCheck(!User::where('email', 'candidate1@example.test')->exists(), 'Invite by email created a User prematurely');
    Mail::assertSent(EmployeeOnboardingInvitationMail::class, fn ($m) => $m->hasTo('candidate1@example.test'));

    // Token is stored hashed, never plain
    $invModel = EmployeeInvitation::findOrFail($inviteData['invitation_id']);
    obCheck($invModel->token_hash === hash('sha256', $token) && $invModel->token_hash !== $token, 'Invitation token was not hashed');

    // Duplicate active email invite rejected
    try {
        $empController->sendInvite(obRequest('/employees/send-invite', ['email' => 'candidate1@example.test']));
        throw new RuntimeException('Duplicate active invitation allowed');
    } catch (ValidationException $e) {
        obCheck(isset($e->errors()['email']), 'Expected duplicate invite error');
    }

    // 2. Public form access & forged administrative fields ignored; private file upload
    Auth::guard('web')->forgetUser();
    $showRes = $publicController->show($token);
    obCheck(!($showRes instanceof \Illuminate\Http\Response && $showRes->getStatusCode() === 410), 'Valid token failed to open onboarding form');

    $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);
    $cv = UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf');
    $govId = UploadedFile::fake()->image('id.png', 300, 200);

    $submitRes = $publicController->submit(obRequest('/onboarding/' . $token . '/submit', [
        'name' => 'Aarav Sharma',
        'email' => 'hijack@attacker.test', // MUST be ignored because invite is bound to candidate1@example.test
        'country' => 'India',
        'mobile_country_code' => '+91',
        'mobile' => '9876543211',
        'dob' => '1996-05-14',
        'gender' => 'Male',
        'marital_status' => 'single',
        'language' => 'en',
        'address' => '221B Park Street, Kolkata',
        'skills' => 'PHP, Laravel, MySQL',
        'about' => 'Full stack engineer with 5 years experience.',
        'directory_about' => 'Building reliable SaaS tools.',
        'linkedin_url' => 'https://linkedin.com/in/aarav',
        'portfolio_url' => 'https://aarav.dev',
        // Attempted privilege escalation / admin injection
        'user_role' => 'admin',
        'employee_id' => 'HACKED-0001',
        'designation_id' => 999,
        'login_allowed' => '1',
    ], 'POST', [
        'profile_picture' => $avatar,
        'cv_file' => $cv,
        'government_id_card' => $govId,
    ]), $token);

    obCheck($submitRes->getStatusCode() === 200, 'Public application submission failed');
    $appRecord = EmployeeOnboardingApplication::where('email', 'candidate1@example.test')->firstOrFail();
    obCheck($appRecord->status === EmployeeOnboardingApplication::STATUS_PENDING_REVIEW, 'Application status not Pending Review');
    obCheck($appRecord->email === 'candidate1@example.test', 'Email-bound applicant was able to change invited email');
    obCheck(empty($appRecord->admin_configuration), 'Public form persisted forged admin configuration');
    obCheck(!User::where('email', 'candidate1@example.test')->exists(), 'Submission created official User before HR approval');
    obCheck(Storage::disk('local')->exists($appRecord->profile_picture_path), 'Profile picture not stored privately');
    obCheck(Storage::disk('local')->exists($appRecord->cv_file_path), 'CV not stored privately');
    obCheck(Storage::disk('local')->exists($appRecord->government_id_card_path), 'Gov ID not stored privately');
    Mail::assertSent(EmployeeApplicationSubmittedMail::class, fn ($m) => $m->hasTo('candidate1@example.test'));

    // Single-use: reopening submitted invitation token returns 200 status page (already submitted), and re-submit returns 410
    $reopenSubmit = $publicController->submit(obRequest('/onboarding/' . $token . '/submit', ['name' => 'Dup'], 'POST'), $token);
    obCheck($reopenSubmit->getStatusCode() === 410, 'Duplicate submission on used invitation was not blocked');

    // 3. Pending count reflected on Employee Directory & Applications page
    Auth::guard('web')->setUser($admin);
    $indexView = $empController->index(obRequest('/employees', [], 'GET'));
    obCheck($indexView->getData()['pendingOnboardingCount'] === 1, 'Pending onboarding count mismatch on directory page');

    // Cross-company isolation check: company 2 admin cannot review or download company 1 application/docs
    Auth::guard('web')->setUser($otherAdmin);
    session(['current_company_id' => 2]);
    try {
        $adminOnboardingController->review($appRecord->id);
        throw new RuntimeException('Cross-company admin could review another org application');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        obCheck($e->getStatusCode() === 403, 'Wrong cross-company status');
    }
    try {
        $adminOnboardingController->previewDocument(obRequest('/employees/onboarding/' . $appRecord->id . '/documents/cv_file', [], 'GET'), $appRecord->id, 'cv_file');
        throw new RuntimeException('Cross-company admin could access private onboarding file');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        obCheck($e->getStatusCode() === 403, 'Wrong cross-company document status');
    }

    // Regular employee cannot approve/reject
    Auth::guard('web')->setUser($regularEmployee);
    session(['current_company_id' => 1]);
    try {
        $adminOnboardingController->approve(obRequest('/employees/onboarding/' . $appRecord->id . '/approve'), $appRecord->id, new GovernmentIdDobVerifier);
        throw new RuntimeException('Regular employee could approve onboarding');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        obCheck($e->getStatusCode() === 403, 'Unauthorized employee did not get 403');
    }

    // 4. Request Correction + Applicant Resubmission
    Auth::guard('web')->setUser($admin);
    session(['current_company_id' => 1]);
    $adminOnboardingController->requestCorrection(obRequest('/employees/onboarding/' . $appRecord->id . '/request-correction', [
        'correction_reason' => 'Please update your skills list to include PHPUnit.',
    ]), $appRecord->id);
    $appRecord->refresh();
    obCheck($appRecord->status === EmployeeOnboardingApplication::STATUS_CORRECTION_REQUESTED, 'Status not set to Correction Requested');
    Mail::assertSent(EmployeeApplicationCorrectionMail::class, fn ($m) => $m->hasTo('candidate1@example.test'));
    $correctionToken = $appRecord->decryptCorrectionToken();
    obCheck(!empty($correctionToken), 'Correction token missing');

    Auth::guard('web')->forgetUser();
    $resubmit = $publicController->submitCorrection(obRequest('/onboarding/correction/' . $correctionToken . '/submit', [
        'name' => 'Aarav Sharma',
        'country' => 'India',
        'mobile_country_code' => '+91',
        'mobile' => '9876543211',
        'dob' => '1996-05-14',
        'skills' => 'PHP, Laravel, MySQL, PHPUnit',
    ]), $correctionToken);
    obCheck($resubmit->getStatusCode() === 200, 'Correction resubmission failed');
    $appRecord->refresh();
    obCheck($appRecord->status === EmployeeOnboardingApplication::STATUS_PENDING_REVIEW, 'Resubmitted application did not return to Pending Review');
    obCheck(str_contains($appRecord->skills, 'PHPUnit'), 'Updated skills not saved');

    // Old correction link is now invalid (single use)
    $usedCorrection = $publicController->showCorrection($correctionToken);
    obCheck($usedCorrection->getStatusCode() === 410, 'Used correction link stayed valid');

    // 5. HR Final Approval ("Add") -> creates official employee, promotes files, sends activation email without plaintext password, prevents duplicate approval
    Auth::guard('web')->setUser($admin);
    session(['current_company_id' => 1]);
    $approveRes = $adminOnboardingController->approve(obRequest('/employees/onboarding/' . $appRecord->id . '/approve', [
        'name' => 'Aarav Sharma',
        'email' => 'candidate1@example.test',
        'country' => 'India',
        'mobile_country_code' => '+91',
        'mobile' => '9876543211',
        'dob' => '1996-05-14',
        'employee_id_option' => 'auto',
        'designation_id' => 1,
        'parent_dpt_id' => 1,
        'department_id' => 1,
        'employment_type' => 'full_time',
        'joining_date' => now()->toDateString(),
        'reporting_to' => $admin->id,
        'user_role' => 'employee',
        'login_allowed' => '1',
        'email_notifications' => '1',
        'business_address' => 'Kolkata HQ',
        'status' => 'Active',
        'skills' => 'PHP, Laravel, MySQL, PHPUnit',
    ]), $appRecord->id, new GovernmentIdDobVerifier);

    $appRecord->refresh();
    obCheck($appRecord->status === EmployeeOnboardingApplication::STATUS_APPROVED, 'Application not marked Approved');
    $approvedUser = User::where('email', 'candidate1@example.test')->firstOrFail();
    obCheck($approvedUser->raw_password === null, 'Onboarding approval must never store plaintext raw_password');
    obCheck($appRecord->approved_user_id === $approvedUser->id && $appRecord->approved_employee_id === 'ACME-EMP-0001', 'Approved employee metadata missing');
    obCheck($approvedUser->profile_image && file_exists(public_path($approvedUser->profile_image)), 'Profile image not promoted to public uploads');
    $createdPublicFiles[] = public_path($approvedUser->profile_image);
    if ($approvedUser->employeeDetail?->cv_path) $createdPublicFiles[] = public_path($approvedUser->employeeDetail->cv_path);
    if ($approvedUser->employeeDetail?->government_id_card) $createdPublicFiles[] = public_path($approvedUser->employeeDetail->government_id_card);
    Mail::assertSent(EmployeeApplicationApprovedMail::class, fn ($m) => $m->hasTo('candidate1@example.test') && !empty($m->activationUrl));

    // Duplicate approval must be rejected
    try {
        $service->approveApplication($admin, 1, $appRecord, obRequest('/approve', [
            'name' => 'Aarav Sharma', 'email' => 'candidate1@example.test', 'country' => 'India', 'mobile_country_code' => '+91',
            'mobile' => '9876543211', 'dob' => '1996-05-14', 'designation_id' => 1, 'parent_dpt_id' => 1, 'joining_date' => now()->toDateString(),
        ]), new GovernmentIdDobVerifier);
        throw new RuntimeException('Duplicate approval succeeded');
    } catch (ValidationException $e) {
        obCheck(isset($e->errors()['application']), 'Expected duplicate approval error');
    }

    // 6. Open Link Invite (without bound email) -> requires OTP verification before submit, and respects login_allowed = 0 on approval
    $openLinkRes = $empController->generateInviteLink(obRequest('/employees/invite-link', []));
    obCheck($openLinkRes->getStatusCode() === 200, 'Open link invite creation failed');
    $openToken = basename(parse_url($openLinkRes->getData(true)['url'], PHP_URL_PATH));

    Auth::guard('web')->forgetUser();
    try {
        $publicController->submit(obRequest('/onboarding/' . $openToken . '/submit', [
            'name' => 'Unverified Applicant', 'email' => 'unverified@example.test', 'country' => 'India',
            'mobile_country_code' => '+91', 'mobile' => '9876543222', 'dob' => '1995-01-01',
        ], 'POST', ['profile_picture' => UploadedFile::fake()->image('p2.jpg')]), $openToken);
        throw new RuntimeException('Open link allowed unverified email submission');
    } catch (ValidationException $e) {
        obCheck(isset($e->errors()['email']), 'Expected email verification error on open link');
    }

    $otpRes = $publicController->sendOtp(obRequest('/onboarding/' . $openToken . '/send-otp', ['email' => 'openuser@example.test']), $openToken);
    obCheck($otpRes->getStatusCode() === 200, 'OTP send failed');
    $otpMailCode = null;
    Mail::assertSent(EmployeeOnboardingEmailOtpMail::class, function ($mail) use (&$otpMailCode) {
        $otpMailCode = $mail->code;
        return true;
    });
    obCheck(!empty($otpMailCode), 'OTP code not emailed');
    $verifyRes = $publicController->verifyOtp(obRequest('/onboarding/' . $openToken . '/verify-otp', ['email' => 'openuser@example.test', 'otp' => $otpMailCode]), $openToken);
    obCheck($verifyRes->getStatusCode() === 200 && $verifyRes->getData(true)['verified'] === true, 'OTP verify failed');

    $publicController->submit(obRequest('/onboarding/' . $openToken . '/submit', [
        'name' => 'Riya Sen', 'email' => 'openuser@example.test', 'country' => 'India',
        'mobile_country_code' => '+91', 'mobile' => '9876543222', 'dob' => '1995-01-01',
    ], 'POST', ['profile_picture' => UploadedFile::fake()->image('p2.jpg')]), $openToken);
    $openApp = EmployeeOnboardingApplication::where('email', 'openuser@example.test')->firstOrFail();

    Auth::guard('web')->setUser($admin);
    session(['current_company_id' => 1]);
    $service->approveApplication($admin, 1, $openApp, obRequest('/approve', [
        'name' => 'Riya Sen', 'email' => 'openuser@example.test', 'country' => 'India', 'mobile_country_code' => '+91',
        'mobile' => '9876543222', 'dob' => '1995-01-01', 'employee_id_option' => 'auto', 'designation_id' => 1,
        'parent_dpt_id' => 1, 'employment_type' => 'full_time', 'joining_date' => now()->toDateString(),
        'login_allowed' => '0', 'email_notifications' => '1', 'status' => 'Active',
    ]), new GovernmentIdDobVerifier);
    $riyaUser = User::where('email', 'openuser@example.test')->firstOrFail();
    if ($riyaUser->profile_image) $createdPublicFiles[] = public_path($riyaUser->profile_image);
    Mail::assertSent(EmployeeApplicationApprovedMail::class, fn ($m) => $m->hasTo('openuser@example.test') && $m->activationUrl === null);

    // 7. Rejection & Revocation & Expiration checks
    $rejInvite = $service->createLinkInvitation($admin, 1, 'rejectme@example.test');
    $rejToken = basename(parse_url($rejInvite['url'], PHP_URL_PATH));
    Auth::guard('web')->forgetUser();
    $publicController->submit(obRequest('/onboarding/' . $rejToken . '/submit', [
        'name' => 'Reject Candidate', 'email' => 'rejectme@example.test', 'country' => 'India',
        'mobile_country_code' => '+91', 'mobile' => '9876543233', 'dob' => '1995-03-03',
    ], 'POST', ['profile_picture' => UploadedFile::fake()->image('p3.jpg')]), $rejToken);
    $rejApp = EmployeeOnboardingApplication::where('email', 'rejectme@example.test')->firstOrFail();
    Auth::guard('web')->setUser($admin);
    $adminOnboardingController->reject(obRequest('/reject', ['rejection_reason' => 'Incomplete employment history']), $rejApp->id);
    $rejApp->refresh();
    obCheck($rejApp->status === EmployeeOnboardingApplication::STATUS_REJECTED && !$rejApp->canApprove(), 'Rejected application remained approvable');
    Mail::assertSent(EmployeeApplicationRejectedMail::class, fn ($m) => $m->hasTo('rejectme@example.test'));

    // Revoke & Expiry check
    $revInvite = $service->createLinkInvitation($admin, 1, 'revoked@example.test');
    $revToken = basename(parse_url($revInvite['url'], PHP_URL_PATH));
    $service->revokeInvitation($admin, 1, $revInvite['invitation']);
    obCheck($publicController->show($revToken)->getStatusCode() === 410, 'Revoked link remained accessible');

    $expInvite = $service->createLinkInvitation($admin, 1, 'expired@example.test');
    $expToken = basename(parse_url($expInvite['url'], PHP_URL_PATH));
    $expInvite['invitation']->forceFill(['expires_at' => now()->subMinute()])->save();
    obCheck($publicController->show($expToken)->getStatusCode() === 410, 'Expired link remained accessible');

    // Audit trail verification
    obCheck(EmployeeOnboardingAuditLog::count() >= 5, 'Audit log entries missing');

    echo "PASS: complete employee invitation, email OTP, self-onboarding, private storage, correction, rejection, revoke/expiry, approval, activation email, cross-tenant & role security verified.\n";
} finally {
    DB::disconnect('tenant');
    @unlink($tenantFile);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($privateRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $file) {
        $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
    }
    @rmdir($privateRoot);
    foreach ($createdPublicFiles as $pf) {
        if ($pf && file_exists($pf)) @unlink($pf);
    }
}
