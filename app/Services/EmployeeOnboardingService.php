<?php

namespace App\Services;

use App\Http\Controllers\EmployeeInviteController;
use App\Mail\{
    EmployeeApplicationApprovedMail,
    EmployeeApplicationCorrectionMail,
    EmployeeApplicationRejectedMail,
    EmployeeApplicationSubmittedMail,
    EmployeeOnboardingEmailOtpMail,
    EmployeeOnboardingInvitationMail
};
use App\Models\Central\Company as CentralCompany;
use App\Models\Central\EmployeeInvitationLink;
use App\Models\{
    Company,
    EmployeeInvitation,
    EmployeeOnboardingApplication,
    EmployeeOnboardingAuditLog,
    User
};
use App\Support\CountryPhone;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Crypt, DB, Log, Mail, Schema, Storage};
use Illuminate\Validation\ValidationException;

class EmployeeOnboardingService
{
    public function __construct(
        private EmployeeCreationService $creationService
    ) {}

    public function expiryHours(): int
    {
        return max(1, (int) config('employee_onboarding.expiry_hours', 72));
    }

    public function privateDisk(): string
    {
        return (string) config('employee_onboarding.private_disk', 'local');
    }

    /**
     * Safely ensure onboarding tables exist on the active central and tenant connections
     * (useful in isolated test fixtures or tenants awaiting a migration pass).
     */
    public function ensureSchema(): void
    {
        try {
            if (! Schema::connection('central')->hasTable('central_employee_invitation_links')) {
                (require database_path('migrations/central/2026_11_27_000001_create_central_employee_invitation_links_table.php'))->up();
            }
        } catch (\Throwable $e) {
            // Central connection may be absent in single-DB unit setups; skip cleanly.
        }

        $conn = (new EmployeeInvitation)->getConnectionName() ?: 'tenant';
        if (! Schema::connection($conn)->hasTable('employee_invitations')
            || ! Schema::connection($conn)->hasTable('employee_onboarding_applications')
            || ! Schema::connection($conn)->hasTable('employee_onboarding_audit_logs')) {
            Schema::connection($conn)->hasTable('employee_invitations');
            $migration = require database_path('migrations/tenant/2026_11_27_000001_create_employee_onboarding_tables.php');
            $prev = DB::getDefaultConnection();
            DB::setDefaultConnection($conn);
            try {
                $migration->up();
            } finally {
                DB::setDefaultConnection($prev);
            }
        }
    }

    public function resolveCompanyModel(int $companyId): ?CentralCompany
    {
        try {
            $central = CentralCompany::on('central')->find($companyId);
            if ($central) {
                return $central;
            }
        } catch (\Throwable $e) {
        }

        $local = Company::find($companyId);
        if (! $local) {
            return null;
        }
        $wrapped = new CentralCompany;
        $wrapped->forceFill($local->getAttributes());
        $wrapped->exists = true;

        return $wrapped;
    }

    public function switchTenantDatabase(?string $dbName, ?CentralCompany $company = null): void
    {
        if ($dbName && $dbName !== config('database.connections.tenant.database')) {
            config([
                'database.connections.tenant.database' => $dbName,
                'database.connections.mysql.database' => $dbName,
                'database.connections.mysql.url' => null,
            ]);
            DB::purge('tenant');
            DB::purge('mysql');
        }
        if ($company && app()->bound(CompanyContext::class)) {
            app(CompanyContext::class)->reset($company);
        }
        app(CompanyMailSettings::class)->apply();
    }

    public function generateToken(): array
    {
        $plain = bin2hex(random_bytes(32));

        return [
            'plain' => $plain,
            'hash' => hash('sha256', $plain),
            'encrypted' => Crypt::encryptString($plain),
        ];
    }

    public function sendEmailInvitation(User $actor, int $companyId, string $rawEmail, ?string $personalMessage = null, ?Request $request = null): array
    {
        $this->ensureSchema();
        app(CompanyMailSettings::class)->requireDelivery();

        $email = $this->sanitizeEmail($rawEmail);
        $this->guardInvitableEmail($companyId, $email);

        $company = $this->resolveCompanyModel($companyId);
        abort_unless($company, 422, 'Your company workspace could not be determined.');

        $tokens = $this->generateToken();
        $expiresAt = now()->addHours($this->expiryHours());
        $conn = (new EmployeeInvitation)->getConnectionName() ?: 'tenant';

        DB::connection($conn)->beginTransaction();
        try {
            $invitation = EmployeeInvitation::create([
                'company_id' => $companyId,
                'email' => $email,
                'token_hash' => $tokens['hash'],
                'encrypted_token' => $tokens['encrypted'],
                'invitation_method' => EmployeeInvitation::METHOD_EMAIL,
                'personal_message' => $personalMessage ? trim($personalMessage) : null,
                'status' => EmployeeInvitation::STATUS_PENDING,
                'invited_by' => $actor->id,
                'single_use' => true,
                'expires_at' => $expiresAt,
            ]);

            $this->registerCentralLookup($companyId, $tokens['hash'], 'invite', $expiresAt);
            $onboardingUrl = $invitation->onboardingUrl($tokens['plain']);

            Mail::to($email)->send(new EmployeeOnboardingInvitationMail(
                $company,
                $onboardingUrl,
                $invitation->personal_message,
                $expiresAt,
                $email,
                false
            ));

            $invitation->forceFill([
                'status' => EmployeeInvitation::STATUS_SENT,
                'last_sent_at' => now(),
                'delivery_error' => null,
            ])->save();

            $this->recordAudit($companyId, 'invitation.sent', [
                'invitation_id' => $invitation->id,
                'actor_id' => $actor->id,
                'actor_type' => 'admin',
                'actor_label' => $actor->name,
                'notes' => 'Employee onboarding invitation emailed.',
                'metadata' => ['method' => 'email', 'expires_at' => $expiresAt->toIso8601String()],
            ], $request);

            DB::connection($conn)->commit();

            return [
                'invitation' => $invitation,
                'url' => $onboardingUrl,
            ];
        } catch (\Throwable $e) {
            DB::connection($conn)->rollBack();
            $this->deleteCentralLookup($tokens['hash']);
            throw $e;
        }
    }

    public function createLinkInvitation(User $actor, int $companyId, ?string $rawEmail = null, ?Request $request = null): array
    {
        $this->ensureSchema();

        $email = $rawEmail ? $this->sanitizeEmail($rawEmail) : null;
        if ($email) {
            $this->guardInvitableEmail($companyId, $email);
        }

        $company = $this->resolveCompanyModel($companyId);
        abort_unless($company, 422, 'Your company workspace could not be determined.');

        $tokens = $this->generateToken();
        $expiresAt = now()->addHours($this->expiryHours());
        $conn = (new EmployeeInvitation)->getConnectionName() ?: 'tenant';

        DB::connection($conn)->beginTransaction();
        try {
            $invitation = EmployeeInvitation::create([
                'company_id' => $companyId,
                'email' => $email,
                'token_hash' => $tokens['hash'],
                'encrypted_token' => $tokens['encrypted'],
                'invitation_method' => EmployeeInvitation::METHOD_LINK,
                'status' => EmployeeInvitation::STATUS_PENDING,
                'invited_by' => $actor->id,
                'single_use' => true,
                'expires_at' => $expiresAt,
            ]);

            $this->registerCentralLookup($companyId, $tokens['hash'], 'invite', $expiresAt);
            $onboardingUrl = $invitation->onboardingUrl($tokens['plain']);

            $this->recordAudit($companyId, 'invitation.link_generated', [
                'invitation_id' => $invitation->id,
                'actor_id' => $actor->id,
                'actor_type' => 'admin',
                'actor_label' => $actor->name,
                'notes' => $email ? 'Email-bound onboarding link generated.' : 'Open single-use onboarding link generated.',
                'metadata' => ['method' => 'link', 'email_bound' => (bool) $email],
            ], $request);

            DB::connection($conn)->commit();

            return [
                'invitation' => $invitation,
                'url' => $onboardingUrl,
            ];
        } catch (\Throwable $e) {
            DB::connection($conn)->rollBack();
            $this->deleteCentralLookup($tokens['hash']);
            throw $e;
        }
    }

    public function regenerateInvitationLink(User $actor, int $companyId, EmployeeInvitation $invitation, ?Request $request = null): array
    {
        abort_unless((int) $invitation->company_id === $companyId, 403);
        if ($invitation->submitted_at !== null || $invitation->status === EmployeeInvitation::STATUS_SUBMITTED) {
            throw ValidationException::withMessages(['invitation' => 'This invitation has already been submitted and cannot be regenerated.']);
        }

        $oldHash = $invitation->token_hash;
        $tokens = $this->generateToken();
        $expiresAt = now()->addHours($this->expiryHours());

        $this->deleteCentralLookup($oldHash);
        $this->registerCentralLookup($companyId, $tokens['hash'], 'invite', $expiresAt);

        $invitation->forceFill([
            'token_hash' => $tokens['hash'],
            'encrypted_token' => $tokens['encrypted'],
            'status' => EmployeeInvitation::STATUS_PENDING,
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'revoked_by' => null,
            'delivery_error' => null,
        ])->save();

        $this->recordAudit($companyId, 'invitation.regenerated', [
            'invitation_id' => $invitation->id,
            'actor_id' => $actor->id,
            'actor_type' => 'admin',
            'actor_label' => $actor->name,
            'notes' => 'Invitation link regenerated; previous token invalidated.',
        ], $request);

        return [
            'invitation' => $invitation->fresh(),
            'url' => $invitation->onboardingUrl($tokens['plain']),
        ];
    }

    public function resendEmailInvitation(User $actor, int $companyId, EmployeeInvitation $invitation, ?Request $request = null): array
    {
        abort_unless((int) $invitation->company_id === $companyId, 403);
        if (empty($invitation->email)) {
            throw ValidationException::withMessages(['email' => 'Open links without an email address cannot be resent by email.']);
        }
        if ($invitation->submitted_at !== null || $invitation->status === EmployeeInvitation::STATUS_SUBMITTED) {
            throw ValidationException::withMessages(['invitation' => 'This invitation already has a submitted onboarding application.']);
        }

        app(CompanyMailSettings::class)->requireDelivery();
        $company = $this->resolveCompanyModel($companyId);
        abort_unless($company, 422, 'Company not found.');

        // Mint a fresh secure token whenever resending expired/revoked/stale invitations.
        $this->deleteCentralLookup($invitation->token_hash);
        $tokens = $this->generateToken();
        $expiresAt = now()->addHours($this->expiryHours());
        $this->registerCentralLookup($companyId, $tokens['hash'], 'invite', $expiresAt);

        $onboardingUrl = $invitation->onboardingUrl($tokens['plain']);

        try {
            Mail::to($invitation->email)->send(new EmployeeOnboardingInvitationMail(
                $company,
                $onboardingUrl,
                $invitation->personal_message,
                $expiresAt,
                $invitation->email,
                true
            ));
        } catch (\Throwable $e) {
            $invitation->forceFill([
                'token_hash' => $tokens['hash'],
                'encrypted_token' => $tokens['encrypted'],
                'expires_at' => $expiresAt,
                'delivery_error' => 'Email delivery failed. Verify Settings > Email and try resending.',
            ])->save();
            throw $e;
        }

        $invitation->forceFill([
            'token_hash' => $tokens['hash'],
            'encrypted_token' => $tokens['encrypted'],
            'status' => EmployeeInvitation::STATUS_SENT,
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'revoked_by' => null,
            'last_sent_at' => now(),
            'resend_count' => (int) $invitation->resend_count + 1,
            'delivery_error' => null,
        ])->save();

        $this->recordAudit($companyId, 'invitation.resent', [
            'invitation_id' => $invitation->id,
            'actor_id' => $actor->id,
            'actor_type' => 'admin',
            'actor_label' => $actor->name,
            'notes' => 'Onboarding invitation email resent.',
        ], $request);

        return [
            'invitation' => $invitation->fresh(),
            'url' => $onboardingUrl,
        ];
    }

    public function revokeInvitation(User $actor, int $companyId, EmployeeInvitation $invitation, ?Request $request = null): EmployeeInvitation
    {
        abort_unless((int) $invitation->company_id === $companyId, 403);
        if ($invitation->submitted_at !== null || $invitation->status === EmployeeInvitation::STATUS_SUBMITTED) {
            throw ValidationException::withMessages(['invitation' => 'Submitted invitations cannot be revoked. Review the application instead.']);
        }

        $invitation->forceFill([
            'status' => EmployeeInvitation::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_by' => $actor->id,
            'encrypted_token' => null,
        ])->save();

        $this->markCentralRevoked($invitation->token_hash);

        $this->recordAudit($companyId, 'invitation.revoked', [
            'invitation_id' => $invitation->id,
            'actor_id' => $actor->id,
            'actor_type' => 'admin',
            'actor_label' => $actor->name,
            'notes' => 'Invitation revoked.',
        ], $request);

        return $invitation->fresh();
    }

    /**
     * Resolve tenant database and invitation record cleanly from a public invitation token.
     *
     * @return array{company: ?CentralCompany, invitation: ?EmployeeInvitation, state: string, message: string}
     */
    public function resolvePublicInvitation(string $plainToken): array
    {
        $this->ensureSchema();
        $hash = hash('sha256', $plainToken);
        $company = null;

        try {
            $lookup = EmployeeInvitationLink::where('token_hash', $hash)->where('token_type', 'invite')->first();
            if ($lookup) {
                $company = $this->resolveCompanyModel((int) $lookup->company_id);
                if (! $company || ! empty($company->deleted_at)) {
                    return ['company' => null, 'invitation' => null, 'state' => 'invalid', 'message' => 'This organization workspace is no longer active.'];
                }
                $this->switchTenantDatabase($company->db_name, $company);
            }
        } catch (\Throwable $e) {
            Log::warning('Central invitation lookup failed', ['error' => $e->getMessage()]);
        }

        $invitation = EmployeeInvitation::with('application')->where('token_hash', $hash)->first();
        if (! $invitation) {
            return ['company' => $company, 'invitation' => null, 'state' => 'invalid', 'message' => 'This onboarding invitation link is invalid or no longer exists.'];
        }

        if (! $company) {
            $company = $this->resolveCompanyModel((int) $invitation->company_id);
            if ($company) {
                $this->switchTenantDatabase($company->db_name, $company);
            }
        }

        if ($invitation->status === EmployeeInvitation::STATUS_REVOKED || $invitation->revoked_at !== null) {
            return ['company' => $company, 'invitation' => $invitation, 'state' => 'revoked', 'message' => 'This invitation has been revoked by your HR administrator.'];
        }

        if ($invitation->submitted_at !== null || $invitation->status === EmployeeInvitation::STATUS_SUBMITTED || $invitation->application) {
            $app = $invitation->application;
            if ($app && $app->status === EmployeeOnboardingApplication::STATUS_REJECTED) {
                return ['company' => $company, 'invitation' => $invitation, 'state' => 'rejected', 'message' => 'This onboarding application has been closed. Please contact HR for assistance.'];
            }

            return ['company' => $company, 'invitation' => $invitation, 'state' => 'submitted', 'message' => 'Your onboarding application has already been submitted and is currently ' . ($app?->status ?? 'under review') . '.'];
        }

        if ($invitation->isExpired()) {
            if ($invitation->status !== EmployeeInvitation::STATUS_EXPIRED) {
                $invitation->forceFill(['status' => EmployeeInvitation::STATUS_EXPIRED, 'encrypted_token' => null])->save();
            }

            return ['company' => $company, 'invitation' => $invitation, 'state' => 'expired', 'message' => 'This invitation link has expired. Please request a fresh invitation from HR.'];
        }

        return ['company' => $company, 'invitation' => $invitation, 'state' => 'valid', 'message' => ''];
    }

    /**
     * Resolve tenant database and application cleanly from a public correction token.
     *
     * @return array{company: ?CentralCompany, application: ?EmployeeOnboardingApplication, state: string, message: string}
     */
    public function resolveCorrectionApplication(string $plainToken): array
    {
        $this->ensureSchema();
        $hash = hash('sha256', $plainToken);
        $company = null;

        try {
            $lookup = EmployeeInvitationLink::where('token_hash', $hash)->where('token_type', 'correction')->first();
            if ($lookup) {
                $company = $this->resolveCompanyModel((int) $lookup->company_id);
                if (! $company) {
                    return ['company' => null, 'application' => null, 'state' => 'invalid', 'message' => 'This workspace is unavailable.'];
                }
                $this->switchTenantDatabase($company->db_name, $company);
            }
        } catch (\Throwable $e) {
        }

        $application = EmployeeOnboardingApplication::with('invitation')->where('correction_token_hash', $hash)->first();
        if (! $application) {
            return ['company' => $company, 'application' => null, 'state' => 'invalid', 'message' => 'This correction link is invalid or has already been used.'];
        }

        if (! $company) {
            $company = $this->resolveCompanyModel((int) $application->company_id);
            if ($company) {
                $this->switchTenantDatabase($company->db_name, $company);
            }
        }

        if ($application->status === EmployeeOnboardingApplication::STATUS_REJECTED) {
            return ['company' => $company, 'application' => $application, 'state' => 'rejected', 'message' => 'This application has been rejected and can no longer be edited.'];
        }
        if ($application->status === EmployeeOnboardingApplication::STATUS_APPROVED) {
            return ['company' => $company, 'application' => $application, 'state' => 'approved', 'message' => 'Your onboarding application has already been approved.'];
        }
        if (! $application->correctionIsValid()) {
            return ['company' => $company, 'application' => $application, 'state' => 'expired', 'message' => 'This correction link has expired or is no longer active. Please contact HR.'];
        }

        return ['company' => $company, 'application' => $application, 'state' => 'valid', 'message' => ''];
    }

    public function sendVerificationOtp(EmployeeInvitation $invitation, CentralCompany $company, string $rawEmail): void
    {
        if (! empty($invitation->email)) {
            throw ValidationException::withMessages(['email' => 'This invitation is already bound to an invited email address.']);
        }

        app(CompanyMailSettings::class)->requireDelivery();
        $email = $this->sanitizeEmail($rawEmail);
        $this->guardInvitableEmail((int) $invitation->company_id, $email, $invitation->id);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes((int) config('employee_onboarding.otp_expiry_minutes', 15));

        $invitation->forceFill([
            'email_verification_code_hash' => hash('sha256', $email . '|' . $code),
            'email_verification_expires_at' => $expiresAt,
            'email_verification_attempts' => 0,
        ])->save();

        Mail::to($email)->send(new EmployeeOnboardingEmailOtpMail($company, $code, $expiresAt));
    }

    public function verifyEmailOtp(EmployeeInvitation $invitation, string $rawEmail, string $code): bool
    {
        $email = $this->sanitizeEmail($rawEmail);
        if (! $invitation->email_verification_code_hash || ! $invitation->email_verification_expires_at || $invitation->email_verification_expires_at->isPast()) {
            return false;
        }
        if ((int) $invitation->email_verification_attempts >= 5) {
            return false;
        }

        $expected = hash('sha256', $email . '|' . trim($code));
        if (! hash_equals($invitation->email_verification_code_hash, $expected)) {
            $invitation->increment('email_verification_attempts');

            return false;
        }

        $invitation->forceFill([
            'email' => $email,
            'email_verified_at' => now(),
            'email_verification_code_hash' => null,
            'email_verification_expires_at' => null,
        ])->save();

        return true;
    }

    /**
     * Validate and submit a candidate's public onboarding application.
     */
    public function submitPublicApplication(EmployeeInvitation $invitation, CentralCompany $company, Request $request): EmployeeOnboardingApplication
    {
        $conn = (new EmployeeInvitation)->getConnectionName() ?: 'tenant';
        $companyId = (int) $invitation->company_id;

        // Email-bound invitations never allow changing the invited email address.
        $email = ! empty($invitation->email)
            ? $this->sanitizeEmail($invitation->email)
            : $this->sanitizeEmail((string) $request->input('email', ''));

        if (empty($invitation->email) && ! $invitation->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => 'Please verify your email address with the verification code before submitting your application.',
            ]);
        }
        if ($invitation->email_verified_at && $invitation->email && $email !== strtolower($invitation->email)) {
            throw ValidationException::withMessages([
                'email' => 'Please use the verified email address (' . $invitation->email . ') for this application.',
            ]);
        }

        $validated = $this->validatePublicSubmission($request, $email, true);
        $this->guardInvitableEmail($companyId, $email, $invitation->id);
        $this->guardUniqueMobile($companyId, $validated['mobile_country_code'], $validated['mobile']);

        DB::connection($conn)->beginTransaction();
        try {
            $lockedInvite = EmployeeInvitation::where('id', $invitation->id)->lockForUpdate()->firstOrFail();
            if (! $lockedInvite->isUsable()) {
                throw ValidationException::withMessages(['invitation' => 'This invitation has already been submitted, revoked, or expired.']);
            }
            if (EmployeeOnboardingApplication::where('invitation_id', $lockedInvite->id)->exists()) {
                throw ValidationException::withMessages(['invitation' => 'An onboarding application has already been submitted for this invitation.']);
            }

            $files = $this->storePrivateSubmissionFiles($companyId, $request);

            $application = EmployeeOnboardingApplication::create([
                'company_id' => $companyId,
                'invitation_id' => $lockedInvite->id,
                'name' => trim($validated['name']),
                'email' => $email,
                'mobile_country_code' => $validated['mobile_country_code'],
                'mobile' => $validated['mobile'],
                'gender' => $validated['gender'] ?? null,
                'dob' => $validated['dob'],
                'country' => $validated['country'],
                'marital_status' => $validated['marital_status'] ?? null,
                'language' => $validated['language'] ?? 'en',
                'address' => $validated['address'] ?? null,
                'about' => $validated['about'] ?? null,
                'skills' => $this->creationService->sanitizeSkills($validated['skills'] ?? null),
                'directory_about' => $validated['directory_about'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'facebook_url' => $validated['facebook_url'] ?? null,
                'instagram_url' => $validated['instagram_url'] ?? null,
                'x_url' => $validated['x_url'] ?? null,
                'profile_picture_path' => $files['profile_picture_path'] ?? null,
                'profile_picture_name' => $files['profile_picture_name'] ?? null,
                'cv_file_path' => $files['cv_file_path'] ?? null,
                'cv_file_name' => $files['cv_file_name'] ?? null,
                'government_id_card_path' => $files['government_id_card_path'] ?? null,
                'government_id_card_name' => $files['government_id_card_name'] ?? null,
                'status' => EmployeeOnboardingApplication::STATUS_PENDING_REVIEW,
                'email_verified_at' => $lockedInvite->email_verified_at ?: now(),
                'submitted_at' => now(),
            ]);

            $lockedInvite->forceFill([
                'email' => $email,
                'status' => EmployeeInvitation::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'encrypted_token' => null,
            ])->save();

            $this->markCentralRevoked($lockedInvite->token_hash);

            $this->recordAudit($companyId, 'application.submitted', [
                'invitation_id' => $lockedInvite->id,
                'application_id' => $application->id,
                'actor_type' => 'applicant',
                'actor_label' => $application->name,
                'notes' => 'Candidate submitted self-onboarding application.',
            ], $request);

            DB::connection($conn)->commit();
        } catch (\Throwable $e) {
            DB::connection($conn)->rollBack();
            throw $e;
        }

        $this->notifySubmissionStakeholders($company, $application, false);

        return $application->fresh();
    }

    public function resubmitCorrectionApplication(EmployeeOnboardingApplication $application, CentralCompany $company, Request $request): EmployeeOnboardingApplication
    {
        $conn = (new EmployeeOnboardingApplication)->getConnectionName() ?: 'tenant';
        $companyId = (int) $application->company_id;
        $email = $this->sanitizeEmail($application->email); // email locked during correction

        $validated = $this->validatePublicSubmission($request, $email, empty($application->profile_picture_path));
        $this->guardUniqueMobile($companyId, $validated['mobile_country_code'], $validated['mobile'], $application->id);

        DB::connection($conn)->beginTransaction();
        try {
            $locked = EmployeeOnboardingApplication::where('id', $application->id)->lockForUpdate()->firstOrFail();
            if (! $locked->correctionIsValid()) {
                throw ValidationException::withMessages(['application' => 'This correction request is no longer active.']);
            }

            $files = $this->storePrivateSubmissionFiles($companyId, $request, [
                'profile_picture_path' => $locked->profile_picture_path,
                'profile_picture_name' => $locked->profile_picture_name,
                'cv_file_path' => $locked->cv_file_path,
                'cv_file_name' => $locked->cv_file_name,
                'government_id_card_path' => $locked->government_id_card_path,
                'government_id_card_name' => $locked->government_id_card_name,
            ]);

            $oldCorrectionHash = $locked->correction_token_hash;
            $locked->forceFill([
                'name' => trim($validated['name']),
                'mobile_country_code' => $validated['mobile_country_code'],
                'mobile' => $validated['mobile'],
                'gender' => $validated['gender'] ?? null,
                'dob' => $validated['dob'],
                'country' => $validated['country'],
                'marital_status' => $validated['marital_status'] ?? null,
                'language' => $validated['language'] ?? 'en',
                'address' => $validated['address'] ?? null,
                'about' => $validated['about'] ?? null,
                'skills' => $this->creationService->sanitizeSkills($validated['skills'] ?? null),
                'directory_about' => $validated['directory_about'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'facebook_url' => $validated['facebook_url'] ?? null,
                'instagram_url' => $validated['instagram_url'] ?? null,
                'x_url' => $validated['x_url'] ?? null,
                'profile_picture_path' => $files['profile_picture_path'],
                'profile_picture_name' => $files['profile_picture_name'],
                'cv_file_path' => $files['cv_file_path'],
                'cv_file_name' => $files['cv_file_name'],
                'government_id_card_path' => $files['government_id_card_path'],
                'government_id_card_name' => $files['government_id_card_name'],
                'status' => EmployeeOnboardingApplication::STATUS_PENDING_REVIEW,
                'correction_token_hash' => null,
                'encrypted_correction_token' => null,
                'correction_token_expires_at' => null,
                'submitted_at' => now(),
            ])->save();

            $this->markCentralRevoked($oldCorrectionHash);

            $this->recordAudit($companyId, 'application.correction_resubmitted', [
                'invitation_id' => $locked->invitation_id,
                'application_id' => $locked->id,
                'actor_type' => 'applicant',
                'actor_label' => $locked->name,
                'notes' => 'Applicant resubmitted corrected onboarding details.',
            ], $request);

            DB::connection($conn)->commit();
        } catch (\Throwable $e) {
            DB::connection($conn)->rollBack();
            throw $e;
        }

        $this->notifySubmissionStakeholders($company, $application->fresh(), true);

        return $application->fresh();
    }

    public function requestCorrection(User $actor, int $companyId, EmployeeOnboardingApplication $application, string $reason, ?Request $request = null): EmployeeOnboardingApplication
    {
        abort_unless((int) $application->company_id === $companyId, 403);
        if (! $application->canRequestCorrection()) {
            throw ValidationException::withMessages(['status' => 'Corrections cannot be requested for an approved or rejected application.']);
        }

        app(CompanyMailSettings::class)->requireDelivery();
        $company = $this->resolveCompanyModel($companyId);
        abort_unless($company, 422, 'Company workspace not found.');

        if ($application->correction_token_hash) {
            $this->markCentralRevoked($application->correction_token_hash);
        }

        $tokens = $this->generateToken();
        $expiresAt = now()->addHours($this->expiryHours());
        $this->registerCentralLookup($companyId, $tokens['hash'], 'correction', $expiresAt);

        $application->forceFill([
            'status' => EmployeeOnboardingApplication::STATUS_CORRECTION_REQUESTED,
            'correction_reason' => trim($reason),
            'correction_token_hash' => $tokens['hash'],
            'encrypted_correction_token' => $tokens['encrypted'],
            'correction_token_expires_at' => $expiresAt,
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
        ])->save();

        $correctionUrl = $application->correctionUrl($tokens['plain']);
        Mail::to($application->email)->send(new EmployeeApplicationCorrectionMail(
            $company,
            $application,
            $correctionUrl,
            $expiresAt
        ));

        $this->recordAudit($companyId, 'application.correction_requested', [
            'invitation_id' => $application->invitation_id,
            'application_id' => $application->id,
            'actor_id' => $actor->id,
            'actor_type' => 'admin',
            'actor_label' => $actor->name,
            'notes' => trim($reason),
        ], $request);

        return $application->fresh();
    }

    public function rejectApplication(User $actor, int $companyId, EmployeeOnboardingApplication $application, string $reason, ?Request $request = null): EmployeeOnboardingApplication
    {
        abort_unless((int) $application->company_id === $companyId, 403);
        if (! $application->canReject()) {
            throw ValidationException::withMessages(['status' => 'This application can no longer be rejected.']);
        }

        $company = $this->resolveCompanyModel($companyId);
        $oldCorrectionHash = $application->correction_token_hash;

        $application->forceFill([
            'status' => EmployeeOnboardingApplication::STATUS_REJECTED,
            'rejection_reason' => trim($reason),
            'correction_token_hash' => null,
            'encrypted_correction_token' => null,
            'correction_token_expires_at' => null,
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
        ])->save();

        if ($oldCorrectionHash) {
            $this->markCentralRevoked($oldCorrectionHash);
        }
        if ($application->invitation) {
            $this->markCentralRevoked($application->invitation->token_hash);
            if (in_array($application->invitation->status, [EmployeeInvitation::STATUS_PENDING, EmployeeInvitation::STATUS_SENT], true)) {
                $application->invitation->forceFill([
                    'status' => EmployeeInvitation::STATUS_REVOKED,
                    'revoked_at' => now(),
                    'revoked_by' => $actor->id,
                    'encrypted_token' => null,
                ])->save();
            }
        }

        $this->recordAudit($companyId, 'application.rejected', [
            'invitation_id' => $application->invitation_id,
            'application_id' => $application->id,
            'actor_id' => $actor->id,
            'actor_type' => 'admin',
            'actor_label' => $actor->name,
            'notes' => trim($reason),
        ], $request);

        if ($company) {
            try {
                app(CompanyMailSettings::class)->apply();
                if (config('mail.default') !== 'log') {
                    Mail::to($application->email)->send(new EmployeeApplicationRejectedMail($company, $application));
                }
            } catch (\Throwable $e) {
                Log::warning('Rejection email delivery failed', ['application_id' => $application->id, 'error' => $e->getMessage()]);
            }
        }

        return $application->fresh();
    }

    /**
     * Atomically approve an onboarding application, create the official Employee + User,
     * link them, and send the account activation / welcome email outside the lock.
     *
     * @return array{application: EmployeeOnboardingApplication, user: User, activation_email_sent: bool}
     */
    public function approveApplication(
        User $actor,
        int $companyId,
        EmployeeOnboardingApplication $application,
        Request $request,
        GovernmentIdDobVerifier $dobVerifier
    ): array {
        abort_unless((int) $application->company_id === $companyId, 403);
        $company = $this->resolveCompanyModel($companyId);
        abort_unless($company, 422, 'Company workspace not found.');

        $conn = (new EmployeeOnboardingApplication)->getConnectionName() ?: config('database.default', 'mysql');

        DB::connection($conn)->beginTransaction();
        try {
            $locked = EmployeeOnboardingApplication::where('id', $application->id)
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->canApprove()) {
                throw ValidationException::withMessages([
                    'application' => $locked->status === EmployeeOnboardingApplication::STATUS_APPROVED
                        ? 'This application has already been approved.'
                        : 'Only pending or correction-requested applications can be approved.',
                ]);
            }

            // Lock email uniqueness inside the transaction.
            $canonicalEmail = $this->sanitizeEmail((string) ($request->input('email') ?: $locked->email));
            $request->merge(['email' => $canonicalEmail]);

            $adminSnapshot = $request->only([
                'employee_id_option', 'employee_id', 'designation_id', 'parent_dpt_id', 'department_id',
                'employment_type', 'joining_date', 'reporting_to', 'user_role', 'login_allowed',
                'email_notifications', 'business_address', 'status', 'exit_date',
                'probation_end_date', 'notice_start_date', 'notice_end_date',
            ]);

            $result = $this->creationService->createFromValidatedRequest(
                $request,
                $actor,
                $companyId,
                $dobVerifier,
                [
                    'onboarding_approval' => true,
                    'private_disk' => $this->privateDisk(),
                    'private_files' => [
                        'profile_picture_path' => $locked->profile_picture_path,
                        'profile_picture_name' => $locked->profile_picture_name,
                        'cv_file_path' => $locked->cv_file_path,
                        'cv_file_name' => $locked->cv_file_name,
                        'government_id_card_path' => $locked->government_id_card_path,
                        'government_id_card_name' => $locked->government_id_card_name,
                    ],
                ]
            );

            $user = $result['user'];
            $detail = $result['detail'];
            $oldCorrectionHash = $locked->correction_token_hash;

            $locked->forceFill([
                'name' => $user->name,
                'email' => $user->email,
                'mobile_country_code' => $request->mobile_country_code ?: $locked->mobile_country_code,
                'mobile' => $detail->mobile,
                'admin_configuration' => $adminSnapshot,
                'status' => EmployeeOnboardingApplication::STATUS_APPROVED,
                'correction_token_hash' => null,
                'encrypted_correction_token' => null,
                'correction_token_expires_at' => null,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'approved_user_id' => $user->id,
                'approved_employee_id' => $detail->employee_id,
            ])->save();

            if ($oldCorrectionHash) {
                $this->markCentralRevoked($oldCorrectionHash);
            }

            $this->recordAudit($companyId, 'application.approved', [
                'invitation_id' => $locked->invitation_id,
                'application_id' => $locked->id,
                'actor_id' => $actor->id,
                'actor_type' => 'admin',
                'actor_label' => $actor->name,
                'notes' => 'Application approved and official employee account created.',
                'metadata' => ['user_id' => $user->id, 'employee_id' => $detail->employee_id],
            ], $request);

            DB::connection($conn)->commit();
        } catch (\Throwable $e) {
            if (DB::connection($conn)->transactionLevel() > 0) {
                DB::connection($conn)->rollBack();
            }
            throw $e;
        }

        // Send welcome / account activation email after releasing transaction locks.
        // If SMTP fails, keep the created employee intact and record error for resend.
        $emailSent = $this->sendApprovalEmail($company, $locked->fresh(), $user);

        try {
            SystemNotificationService::notifyAdmins(
                'Employee Onboarding Approved',
                $actor->name . ' approved onboarding for ' . $user->name . ' (' . $detail->employee_id . ').',
                route('employees.show', $user->id),
                ['employee_id' => $user->id, 'type' => 'employee_created', 'icon' => 'fa-user-check', 'color' => 'success']
            );
        } catch (\Throwable $e) {
            Log::warning('Admin approval in-app notification failed', ['error' => $e->getMessage()]);
        }

        return [
            'application' => $locked->fresh(),
            'user' => $user,
            'activation_email_sent' => $emailSent,
        ];
    }

    public function sendApprovalEmail(CentralCompany $company, EmployeeOnboardingApplication $application, User $user): bool
    {
        try {
            app(CompanyMailSettings::class)->requireDelivery();
            $user->loadMissing(['employeeDetail.designation', 'employeeDetail.department']);
            $activationUrl = $user->login_allowed
                ? EmployeeInviteController::inviteUrl($user, (int) $company->id)
                : null;

            Mail::to($user->email)->send(new EmployeeApplicationApprovedMail($company, $user, $activationUrl));

            $application->forceFill([
                'activation_email_sent_at' => now(),
                'activation_email_error' => null,
            ])->save();

            return true;
        } catch (\Throwable $e) {
            Log::warning('Approval welcome email failed after employee creation', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            $application->forceFill([
                'activation_email_error' => 'Activation email delivery failed. You can resend it from Applications or Employee profile.',
            ])->save();

            return false;
        }
    }

    public function recordAudit(int $companyId, string $event, array $attributes = [], ?Request $request = null): void
    {
        try {
            EmployeeOnboardingAuditLog::create([
                'company_id' => $companyId,
                'invitation_id' => $attributes['invitation_id'] ?? null,
                'application_id' => $attributes['application_id'] ?? null,
                'event' => $event,
                'actor_id' => $attributes['actor_id'] ?? null,
                'actor_type' => $attributes['actor_type'] ?? 'system',
                'actor_label' => $attributes['actor_label'] ?? null,
                'notes' => $attributes['notes'] ?? null,
                'metadata' => $attributes['metadata'] ?? null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Onboarding audit write failed', ['event' => $event, 'error' => $e->getMessage()]);
        }
    }

    public function sanitizeEmail(string $email): string
    {
        $sanitized = trim($email);
        $sanitized = preg_replace('/^(www\.|mailto:)/i', '', $sanitized);

        return strtolower(trim((string) $sanitized));
    }

    private function guardInvitableEmail(int $companyId, string $email, ?int $ignoreInvitationId = null): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['email' => 'Please enter a valid email address.']);
        }

        if (User::query()->withoutGlobalScopes()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'An account with this email address already exists in this organization.']);
        }

        $pendingApp = EmployeeOnboardingApplication::where('company_id', $companyId)
            ->where('email', $email)
            ->whereIn('status', [
                EmployeeOnboardingApplication::STATUS_PENDING_REVIEW,
                EmployeeOnboardingApplication::STATUS_CORRECTION_REQUESTED,
            ])
            ->exists();
        if ($pendingApp) {
            throw ValidationException::withMessages(['email' => 'A pending onboarding application already exists for this email address.']);
        }

        $activeInvite = EmployeeInvitation::where('company_id', $companyId)
            ->where('email', $email)
            ->when($ignoreInvitationId, fn ($q) => $q->where('id', '!=', $ignoreInvitationId))
            ->whereIn('status', [EmployeeInvitation::STATUS_PENDING, EmployeeInvitation::STATUS_SENT])
            ->whereNull('revoked_at')
            ->whereNull('submitted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
        if ($activeInvite) {
            throw ValidationException::withMessages(['email' => 'An active invitation already exists for this email address. You can resend or revoke it from Onboarding Applications.']);
        }
    }

    private function guardUniqueMobile(int $companyId, string $dialCode, string $mobile, ?int $ignoreApplicationId = null): void
    {
        $full = $dialCode . $mobile;
        if (User::where('mobile', $full)->exists()) {
            throw ValidationException::withMessages(['mobile' => 'This mobile number is already registered to an employee.']);
        }

        $pendingMobile = EmployeeOnboardingApplication::where('company_id', $companyId)
            ->when($ignoreApplicationId, fn ($q) => $q->where('id', '!=', $ignoreApplicationId))
            ->where('mobile_country_code', $dialCode)
            ->where('mobile', $mobile)
            ->whereIn('status', [
                EmployeeOnboardingApplication::STATUS_PENDING_REVIEW,
                EmployeeOnboardingApplication::STATUS_CORRECTION_REQUESTED,
            ])
            ->exists();
        if ($pendingMobile) {
            throw ValidationException::withMessages(['mobile' => 'This mobile number is already associated with a pending onboarding application.']);
        }
    }

    private function validatePublicSubmission(Request $request, string $email, bool $requireProfilePicture): array
    {
        $request->merge(['email' => $email]);
        if (! $request->filled('mobile_country_code')) {
            $request->merge(['mobile_country_code' => '+91']);
        }

        $phoneRules = CountryPhone::getDigitRules((string) $request->input('mobile_country_code', '+91'));
        $minDigits = $phoneRules['min_digits'] ?? 6;
        $maxDigits = $phoneRules['max_digits'] ?? 15;
        $phoneCountryName = $phoneRules['name'] ?? 'selected country';

        $rules = [
            'name' => 'required|string|max:191',
            'email' => 'required|email:rfc|max:191',
            'country' => 'required|string|max:120',
            'mobile_country_code' => 'required|string|regex:/^\+\d{1,4}$/',
            'mobile' => ['required', 'string', 'regex:/^[1-9]\d*$/', "min:{$minDigits}", "max:{$maxDigits}"],
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
            'dob' => 'required|date|before:' . now()->subYears(14)->toDateString(),
            'marital_status' => 'nullable|in:single,married,widower,widow,separate,divorced,engaged',
            'language' => 'nullable|in:en,bn,hi,fr,de',
            'address' => 'nullable|string|max:1000',
            'about' => 'nullable|string|max:2000',
            'skills' => 'nullable|string|max:1000',
            'directory_about' => 'nullable|string|max:3000',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'x_url' => 'nullable|url|max:255',
            'profile_picture' => ($requireProfilePicture ? 'required|' : 'nullable|') . 'image|mimes:jpeg,png,jpg|max:4096',
            'government_id_card' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:4096',
        ];

        $messages = [
            'dob.before' => 'Please enter a valid date of birth.',
            'mobile.regex' => 'The mobile number must contain digits only and cannot start with 0.',
            'mobile.min' => $minDigits === $maxDigits
                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
            'mobile.max' => $minDigits === $maxDigits
                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
        ];

        return $request->validate($rules, $messages);
    }

    private function storePrivateSubmissionFiles(int $companyId, Request $request, array $existing = []): array
    {
        $disk = $this->privateDisk();
        $baseDir = 'onboarding/' . $companyId;
        $stored = $existing;

        foreach ([
            'profile_picture' => ['profile_picture_path', 'profile_picture_name'],
            'cv_file' => ['cv_file_path', 'cv_file_name'],
            'government_id_card' => ['government_id_card_path', 'government_id_card_name'],
        ] as $input => [$pathKey, $nameKey]) {
            if ($request->hasFile($input)) {
                /** @var UploadedFile $file */
                $file = $request->file($input);
                if (! empty($existing[$pathKey]) && Storage::disk($disk)->exists($existing[$pathKey])) {
                    Storage::disk($disk)->delete($existing[$pathKey]);
                }
                $stored[$pathKey] = $file->store($baseDir . '/' . $input, $disk);
                $stored[$nameKey] = $file->getClientOriginalName();
            }
        }

        return $stored;
    }

    private function notifySubmissionStakeholders(CentralCompany $company, EmployeeOnboardingApplication $application, bool $isResubmission): void
    {
        try {
            app(CompanyMailSettings::class)->apply();
            if (config('mail.default') !== 'log') {
                Mail::to($application->email)->send(new EmployeeApplicationSubmittedMail($company, $application, $isResubmission));
            }
        } catch (\Throwable $e) {
            Log::warning('Applicant confirmation email failed', ['application_id' => $application->id, 'error' => $e->getMessage()]);
        }

        try {
            $recipients = SystemNotificationService::adminsAndHr((int) $company->id);
            $title = $isResubmission ? 'Onboarding Application Resubmitted' : 'New Employee Onboarding Application';
            $message = $application->name . ' (' . $application->email . ') submitted an onboarding application for HR review.';
            SystemNotificationService::send(
                $recipients,
                $title,
                $message,
                route('employees.onboarding.review', $application->id),
                [
                    'company_id' => (int) $company->id,
                    'application_id' => $application->id,
                    'type' => 'employee_onboarding_submitted',
                    'icon' => 'fa-user-clock',
                    'color' => 'info',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('HR onboarding notification failed', ['application_id' => $application->id, 'error' => $e->getMessage()]);
        }
    }

    private function registerCentralLookup(int $companyId, string $tokenHash, string $type, $expiresAt): void
    {
        try {
            if (Schema::connection('central')->hasTable('central_employee_invitation_links')) {
                EmployeeInvitationLink::updateOrCreate(
                    ['token_hash' => $tokenHash],
                    [
                        'company_id' => $companyId,
                        'token_type' => $type,
                        'expires_at' => $expiresAt,
                        'revoked_at' => null,
                    ]
                );
            }
        } catch (\Throwable $e) {
        }
    }

    private function markCentralRevoked(?string $tokenHash): void
    {
        if (! $tokenHash) {
            return;
        }
        try {
            if (Schema::connection('central')->hasTable('central_employee_invitation_links')) {
                EmployeeInvitationLink::where('token_hash', $tokenHash)->update(['revoked_at' => now()]);
            }
        } catch (\Throwable $e) {
        }
    }

    private function deleteCentralLookup(?string $tokenHash): void
    {
        if (! $tokenHash) {
            return;
        }
        try {
            if (Schema::connection('central')->hasTable('central_employee_invitation_links')) {
                EmployeeInvitationLink::where('token_hash', $tokenHash)->delete();
            }
        } catch (\Throwable $e) {
        }
    }
}
