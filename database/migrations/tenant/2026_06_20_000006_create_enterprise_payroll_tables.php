<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_architectures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->default('standard');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payroll_architecture_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_architecture_id')->constrained('payroll_architectures')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->date('effective_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['payroll_architecture_id', 'version'], 'payroll_architecture_version_unique');
        });

        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('salary_structure_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained('salary_structures')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->date('effective_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['salary_structure_id', 'version']);
        });

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->nullable()->constrained('salary_structures')->nullOnDelete();
            $table->string('name');
            $table->string('code')->index();
            $table->string('component_type')->default('allowance');
            $table->string('calculation_type')->default('fixed');
            $table->decimal('value', 14, 2)->nullable();
            $table->text('formula')->nullable();
            $table->boolean('taxable')->default(false);
            $table->boolean('required')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deduction_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('deduction_type')->default('custom');
            $table->string('calculation_type')->default('fixed');
            $table->decimal('value', 14, 2)->nullable();
            $table->text('formula')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bonus_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('bonus_type')->default('custom');
            $table->string('calculation_type')->default('formula');
            $table->decimal('value', 14, 2)->nullable();
            $table->text('formula')->nullable();
            $table->json('approval_flow')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('country')->default('custom');
            $table->json('slabs')->nullable();
            $table->text('formula')->nullable();
            $table->json('exemptions')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('overtime_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('overtime_type')->default('weekday');
            $table->decimal('multiplier', 8, 2)->default(1.00);
            $table->text('formula')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payroll_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cycle_type')->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date')->nullable();
            $table->date('lock_date')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_cycle_id')->nullable()->constrained('payroll_cycles')->nullOnDelete();
            $table->foreignId('payroll_architecture_version_id')->nullable()->constrained('payroll_architecture_versions')->nullOnDelete();
            $table->foreignId('salary_structure_version_id')->nullable()->constrained('salary_structure_versions')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('pay_date')->nullable();
            $table->decimal('gross_total', 16, 2)->default(0);
            $table->decimal('deduction_total', 16, 2)->default(0);
            $table->decimal('tax_total', 16, 2)->default(0);
            $table->decimal('net_total', 16, 2)->default(0);
            $table->json('attendance_summary')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_manager')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_admin')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payroll_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->json('snapshot');
            $table->decimal('gross_salary', 16, 2)->default(0);
            $table->decimal('total_deductions', 16, 2)->default(0);
            $table->decimal('net_salary', 16, 2)->default(0);
            $table->string('payroll_status')->default('draft');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->unsignedInteger('architecture_version')->nullable();
            $table->unsignedInteger('salary_structure_version')->nullable();
            $table->string('immutable_hash')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('payroll_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('action');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamps();
        });

        Schema::create('payslip_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('template_type')->default('classic');
            $table->json('content')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('version')->default(1);
            $table->date('effective_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->foreignId('payroll_history_id')->nullable()->constrained('payroll_histories')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('payslip_number')->nullable()->unique();
            $table->json('template_version_snapshot')->nullable();
            $table->json('company_snapshot')->nullable();
            $table->json('employee_snapshot')->nullable();
            $table->json('earnings')->nullable();
            $table->json('deductions')->nullable();
            $table->json('taxes')->nullable();
            $table->decimal('gross_salary', 16, 2)->default(0);
            $table->decimal('total_deductions', 16, 2)->default(0);
            $table->decimal('net_salary', 16, 2)->default(0);
            $table->string('net_salary_words')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('password_hash')->nullable();
            $table->string('status')->default('generated');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payroll_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('report_type');
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->string('export_type')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_archives', function (Blueprint $table) {
            $table->id();
            $table->string('archivable_type');
            $table->unsignedBigInteger('archivable_id');
            $table->json('snapshot');
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['archivable_type', 'archivable_id']);
        });

        Schema::create('payroll_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending');
            $table->json('summary')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_export_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending');
            $table->json('summary')->nullable();
            $table->foreignId('exported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('exported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_logs');
        Schema::dropIfExists('payroll_export_logs');
        Schema::dropIfExists('payroll_import_logs');
        Schema::dropIfExists('payroll_archives');
        Schema::dropIfExists('payroll_reports');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payslip_templates');
        Schema::dropIfExists('payroll_approvals');
        Schema::dropIfExists('payroll_histories');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_cycles');
        Schema::dropIfExists('overtime_rules');
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('bonus_rules');
        Schema::dropIfExists('deduction_components');
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('salary_structure_versions');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('payroll_architecture_versions');
        Schema::dropIfExists('payroll_architectures');
    }
};
