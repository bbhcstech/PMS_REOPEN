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
                $table->string('company_code')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('companies', 'short_name')) {
                $table->string('short_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('companies', 'favicon')) {
                $table->string('favicon')->nullable()->after('logo');
            }
            if (! Schema::hasColumn('companies', 'website')) {
                $table->string('website')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('companies', 'gst_number')) {
                $table->string('gst_number')->nullable()->after('address');
            }
            if (! Schema::hasColumn('companies', 'pan_number')) {
                $table->string('pan_number')->nullable()->after('gst_number');
            }
            if (! Schema::hasColumn('companies', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('pan_number');
            }
            if (! Schema::hasColumn('companies', 'employee_id_prefix')) {
                $table->string('employee_id_prefix')->nullable()->after('registration_number');
            }
            if (! Schema::hasColumn('companies', 'leave_prefix')) {
                $table->string('leave_prefix')->nullable()->after('employee_id_prefix');
            }
            if (! Schema::hasColumn('companies', 'payroll_prefix')) {
                $table->string('payroll_prefix')->nullable()->after('leave_prefix');
            }
            if (! Schema::hasColumn('companies', 'payslip_prefix')) {
                $table->string('payslip_prefix')->nullable()->after('payroll_prefix');
            }
            if (! Schema::hasColumn('companies', 'greeting_message')) {
                $table->string('greeting_message')->nullable()->after('payslip_prefix');
            }
            if (! Schema::hasColumn('companies', 'theme')) {
                $table->json('theme')->nullable()->after('greeting_message');
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
