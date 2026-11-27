<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_invitations')) {
            Schema::create('employee_invitations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('email')->nullable()->index();
                $table->string('token_hash', 64)->unique();
                $table->text('encrypted_token')->nullable();
                $table->string('invitation_method', 20)->default('email'); // email | link
                $table->text('personal_message')->nullable();
                $table->string('status', 32)->default('Pending')->index(); // Pending | Sent | Submitted | Expired | Revoked
                $table->unsignedBigInteger('invited_by')->nullable()->index();
                $table->boolean('single_use')->default(true);
                $table->unsignedInteger('resend_count')->default(0);
                $table->timestamp('last_sent_at')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('email_verification_code_hash', 64)->nullable();
                $table->timestamp('email_verification_expires_at')->nullable();
                $table->unsignedTinyInteger('email_verification_attempts')->default(0);
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->unsignedBigInteger('revoked_by')->nullable();
                $table->text('delivery_error')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'email', 'status']);
            });
        }

        if (! Schema::hasTable('employee_onboarding_applications')) {
            Schema::create('employee_onboarding_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('invitation_id')->nullable()->index();
                $table->string('name', 191);
                $table->string('email', 191)->index();
                $table->string('mobile_country_code', 10)->default('+91');
                $table->string('mobile', 32);
                $table->string('gender', 40)->nullable();
                $table->date('dob')->nullable();
                $table->string('country', 120)->nullable();
                $table->string('marital_status', 40)->nullable();
                $table->string('language', 20)->nullable()->default('en');
                $table->text('address')->nullable();
                $table->text('about')->nullable();
                $table->text('skills')->nullable();
                $table->text('directory_about')->nullable();
                $table->string('linkedin_url', 255)->nullable();
                $table->string('portfolio_url', 255)->nullable();
                $table->string('facebook_url', 255)->nullable();
                $table->string('instagram_url', 255)->nullable();
                $table->string('x_url', 255)->nullable();

                // Private storage paths (disk: local) + metadata
                $table->string('profile_picture_path')->nullable();
                $table->string('profile_picture_name')->nullable();
                $table->string('cv_file_path')->nullable();
                $table->string('cv_file_name')->nullable();
                $table->string('government_id_card_path')->nullable();
                $table->string('government_id_card_name')->nullable();

                // Admin-owned configuration saved during review or upon approval
                $table->json('admin_configuration')->nullable();
                $table->string('status', 40)->default('Pending Review')->index(); // Pending Review | Correction Requested | Approved | Rejected
                $table->string('correction_token_hash', 64)->nullable()->unique();
                $table->text('encrypted_correction_token')->nullable();
                $table->timestamp('correction_token_expires_at')->nullable();
                $table->text('correction_reason')->nullable();
                $table->text('rejection_reason')->nullable();

                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('submitted_at')->nullable()->index();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('rejected_by')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->unsignedBigInteger('approved_user_id')->nullable()->index();
                $table->string('approved_employee_id', 64)->nullable();
                $table->timestamp('activation_email_sent_at')->nullable();
                $table->text('activation_email_error')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->unique('invitation_id');
            });
        }

        if (! Schema::hasTable('employee_onboarding_audit_logs')) {
            Schema::create('employee_onboarding_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('invitation_id')->nullable()->index();
                $table->unsignedBigInteger('application_id')->nullable()->index();
                $table->string('event', 80)->index();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('actor_type', 40)->nullable(); // admin | applicant | system
                $table->string('actor_label', 191)->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboarding_audit_logs');
        Schema::dropIfExists('employee_onboarding_applications');
        Schema::dropIfExists('employee_invitations');
    }
};
