<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_formulas')) {
            Schema::table('payroll_formulas', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_formulas', 'effective_from')) {
                    $table->date('effective_from')->nullable()->after('last_validated_at');
                }
                if (!Schema::hasColumn('payroll_formulas', 'effective_to')) {
                    $table->date('effective_to')->nullable()->after('effective_from');
                }
                if (!Schema::hasColumn('payroll_formulas', 'version')) {
                    $table->integer('version')->default(1)->after('effective_to');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_formulas')) {
            Schema::table('payroll_formulas', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('payroll_formulas', 'effective_from')) $columns[] = 'effective_from';
                if (Schema::hasColumn('payroll_formulas', 'effective_to')) $columns[] = 'effective_to';
                if (Schema::hasColumn('payroll_formulas', 'version')) $columns[] = 'version';
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
