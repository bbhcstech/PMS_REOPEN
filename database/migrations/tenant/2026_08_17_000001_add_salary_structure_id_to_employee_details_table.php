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
        if (Schema::hasTable('employee_details')) {
            Schema::table('employee_details', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_details', 'salary_structure_id')) {
                    $table->foreignId('salary_structure_id')->nullable()->constrained('salary_structures')->nullOnDelete();
                }
                if (!Schema::hasColumn('employee_details', 'basic_salary')) {
                    $table->decimal('basic_salary', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('employee_details', 'hra_amount')) {
                    $table->decimal('hra_amount', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('employee_details', 'special_allowance')) {
                    $table->decimal('special_allowance', 14, 2)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_details')) {
            Schema::table('employee_details', function (Blueprint $table) {
                if (Schema::hasColumn('employee_details', 'salary_structure_id')) {
                    $table->dropForeign(['salary_structure_id']);
                    $table->dropColumn('salary_structure_id');
                }
                if (Schema::hasColumn('employee_details', 'basic_salary')) {
                    $table->dropColumn('basic_salary');
                }
                if (Schema::hasColumn('employee_details', 'hra_amount')) {
                    $table->dropColumn('hra_amount');
                }
                if (Schema::hasColumn('employee_details', 'special_allowance')) {
                    $table->dropColumn('special_allowance');
                }
            });
        }
    }
};
