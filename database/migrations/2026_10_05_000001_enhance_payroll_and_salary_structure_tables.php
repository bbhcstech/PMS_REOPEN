<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update salary_structures table
        if (Schema::hasTable('salary_structures')) {
            Schema::table('salary_structures', function (Blueprint $table) {
                if (!Schema::hasColumn('salary_structures', 'designation_id')) {
                    $table->unsignedBigInteger('designation_id')->nullable()->after('company_id')->index();
                }
                if (!Schema::hasColumn('salary_structures', 'grade')) {
                    $table->string('grade', 50)->nullable()->after('designation_id');
                }
                if (!Schema::hasColumn('salary_structures', 'basic_salary')) {
                    $table->decimal('basic_salary', 14, 2)->default(0)->after('grade');
                }
                if (!Schema::hasColumn('salary_structures', 'hra_type')) {
                    $table->string('hra_type', 20)->default('percent')->after('basic_salary');
                }
                if (!Schema::hasColumn('salary_structures', 'hra_value')) {
                    $table->decimal('hra_value', 14, 2)->default(50)->after('hra_type');
                }
                if (!Schema::hasColumn('salary_structures', 'special_allowance_type')) {
                    $table->string('special_allowance_type', 20)->default('percent')->after('hra_value');
                }
                if (!Schema::hasColumn('salary_structures', 'special_allowance_value')) {
                    $table->decimal('special_allowance_value', 14, 2)->default(50)->after('special_allowance_type');
                }
                if (!Schema::hasColumn('salary_structures', 'effective_from')) {
                    $table->date('effective_from')->nullable()->after('special_allowance_value');
                }
                if (!Schema::hasColumn('salary_structures', 'effective_to')) {
                    $table->date('effective_to')->nullable()->after('effective_from');
                }
                if (!Schema::hasColumn('salary_structures', 'status')) {
                    $table->string('status', 20)->default('active')->after('effective_to');
                }
            });
        }

        // 2. Create employee_salary_assignments table
        if (!Schema::hasTable('employee_salary_assignments')) {
            Schema::create('employee_salary_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('designation_id')->nullable()->index();
                $table->string('grade', 50)->nullable();
                $table->unsignedBigInteger('salary_structure_id')->nullable()->index();
                $table->decimal('actual_basic_salary', 14, 2)->default(0);
                $table->string('hra_type', 20)->default('percent');
                $table->decimal('hra_value', 14, 2)->default(50);
                $table->string('special_allowance_type', 20)->default('percent');
                $table->decimal('special_allowance_value', 14, 2)->default(50);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->string('status', 20)->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Update payrolls table
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (!Schema::hasColumn('payrolls', 'total_in_hand')) {
                    $table->decimal('total_in_hand', 16, 2)->default(0)->after('net_total');
                }
                if (!Schema::hasColumn('payrolls', 'total_employer_contribution')) {
                    $table->decimal('total_employer_contribution', 16, 2)->default(0)->after('total_in_hand');
                }
                if (!Schema::hasColumn('payrolls', 'total_ctc')) {
                    $table->decimal('total_ctc', 16, 2)->default(0)->after('total_employer_contribution');
                }
                if (!Schema::hasColumn('payrolls', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                }
                if (!Schema::hasColumn('payrolls', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by_admin');
                }
                if (!Schema::hasColumn('payrolls', 'finalized_at')) {
                    $table->timestamp('finalized_at')->nullable()->after('locked_at');
                }
            });
        }

        // 4. Update payroll_histories table
        if (Schema::hasTable('payroll_histories')) {
            Schema::table('payroll_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_histories', 'total_in_hand')) {
                    $table->decimal('total_in_hand', 16, 2)->default(0)->after('net_salary');
                }
                if (!Schema::hasColumn('payroll_histories', 'ctc')) {
                    $table->decimal('ctc', 16, 2)->default(0)->after('total_in_hand');
                }
            });
        }
    }

    public function down(): void
    {
        // Safe reversible migration
        Schema::dropIfExists('employee_salary_assignments');
    }
};
