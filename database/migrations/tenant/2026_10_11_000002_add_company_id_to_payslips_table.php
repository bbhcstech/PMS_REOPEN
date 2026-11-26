<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payslip generation writes company_id, but the original payslips table had no such column, so generating
 * payslips (and finalizing payroll, which generates them) failed. Adds the column and back-fills it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payslips')) {
            return;
        }
        if (! Schema::hasColumn('payslips', 'company_id')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }

        // Back-fill from the payroll run, then from the employee.
        if (Schema::hasTable('payrolls') && Schema::hasColumn('payrolls', 'company_id')) {
            DB::table('payslips')->whereNull('company_id')->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $companyId = DB::table('payrolls')->where('id', $row->payroll_id)->value('company_id')
                        ?: DB::table('users')->where('id', $row->user_id)->value('company_id');
                    if ($companyId) {
                        DB::table('payslips')->where('id', $row->id)->update(['company_id' => $companyId]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payslips', 'company_id')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
};
