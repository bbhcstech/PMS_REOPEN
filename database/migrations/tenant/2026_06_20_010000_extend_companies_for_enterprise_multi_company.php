<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'company_code')) {
                $table->string('company_code')->nullable()->unique();
            }
            if (! Schema::hasColumn('companies', 'short_name')) {
                $table->string('short_name')->nullable();
            }
            if (! Schema::hasColumn('companies', 'favicon')) {
                $table->string('favicon')->nullable();
            }
            if (! Schema::hasColumn('companies', 'website')) {
                $table->string('website')->nullable();
            }
            if (! Schema::hasColumn('companies', 'gst_number')) {
                $table->string('gst_number')->nullable();
            }
            if (! Schema::hasColumn('companies', 'pan_number')) {
                $table->string('pan_number')->nullable();
            }
            if (! Schema::hasColumn('companies', 'registration_number')) {
                $table->string('registration_number')->nullable();
            }
            if (! Schema::hasColumn('companies', 'employee_id_prefix')) {
                $table->string('employee_id_prefix')->nullable();
            }
            if (! Schema::hasColumn('companies', 'leave_prefix')) {
                $table->string('leave_prefix')->nullable();
            }
            if (! Schema::hasColumn('companies', 'payroll_prefix')) {
                $table->string('payroll_prefix')->nullable();
            }
            if (! Schema::hasColumn('companies', 'payslip_prefix')) {
                $table->string('payslip_prefix')->nullable();
            }
            if (! Schema::hasColumn('companies', 'greeting_message')) {
                $table->string('greeting_message')->nullable();
            }
            if (! Schema::hasColumn('companies', 'theme')) {
                $table->json('theme')->nullable();
            }
            if (! Schema::hasColumn('companies', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach ([
                'company_code',
                'short_name',
                'favicon',
                'website',
                'gst_number',
                'pan_number',
                'registration_number',
                'employee_id_prefix',
                'leave_prefix',
                'payroll_prefix',
                'payslip_prefix',
                'greeting_message',
                'theme',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
