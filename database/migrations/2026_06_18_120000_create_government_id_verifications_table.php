<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('government_id_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_detail_id')->nullable()->constrained('employee_details')->nullOnDelete();
            $table->date('submitted_dob')->nullable();
            $table->string('image_path');
            $table->longText('ocr_text')->nullable();
            $table->string('ocr_detected_dob')->nullable();
            $table->string('verification_status')->default('pending_admin_verification')->index();
            $table->text('ocr_message')->nullable();
            $table->json('ocr_errors')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'government_id_verification_status')) {
                $table->string('government_id_verification_status')->nullable()->after('government_id_card');
            }
        });

        Schema::table('employee_details', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_details', 'government_id_verification_status')) {
                $table->string('government_id_verification_status')->nullable()->after('government_id_card');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            if (Schema::hasColumn('employee_details', 'government_id_verification_status')) {
                $table->dropColumn('government_id_verification_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'government_id_verification_status')) {
                $table->dropColumn('government_id_verification_status');
            }
        });

        Schema::dropIfExists('government_id_verifications');
    }
};
