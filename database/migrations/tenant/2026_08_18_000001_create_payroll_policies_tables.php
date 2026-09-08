<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payroll_policies')) {
            Schema::create('payroll_policies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('name')->default('Standard Payroll Policy');
                $table->string('code')->unique()->default('STD-POLICY-01');
                $table->string('status')->default('published')->index(); // draft, published, archived
                $table->unsignedInteger('version')->default(1);
                $table->boolean('is_default')->default(true);
                $table->date('effective_from')->nullable();
                $table->date('effective_until')->nullable();

                // 12 Policy Categories stored as normalized JSON blocks
                $table->json('salary_earnings_rules')->nullable();
                $table->json('working_days_rules')->nullable();
                $table->json('leave_absence_rules')->nullable();
                $table->json('overtime_rules')->nullable();
                $table->json('deductions_rules')->nullable();
                $table->json('tax_rules')->nullable();
                $table->json('bonus_rules')->nullable();
                $table->json('attendance_rules')->nullable();
                $table->json('processing_rules')->nullable();
                $table->json('rounding_rules')->nullable();
                $table->json('payslip_rules')->nullable();
                $table->json('compliance_rules')->nullable();

                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('payroll_policy_histories')) {
            Schema::create('payroll_policy_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_policy_id')->constrained('payroll_policies')->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->json('changes_summary')->nullable();
                $table->json('snapshot');
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_policy_histories');
        Schema::dropIfExists('payroll_policies');
    }
};
