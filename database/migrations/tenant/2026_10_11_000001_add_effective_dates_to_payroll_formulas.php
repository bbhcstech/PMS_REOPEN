<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company (tenant) copy of 2026_10_05_000002_add_effective_dates_to_payroll_formulas, which was placed in the
 * central migrations folder and therefore never reached company databases. Every column is guarded, so it is
 * safe on databases that already have some or all of them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payroll_formulas')) {
            return;
        }

        $columns = [
            'effective_from' => fn (Blueprint $table) => $table->date('effective_from')->nullable(),
            'effective_to' => fn (Blueprint $table) => $table->date('effective_to')->nullable(),
            'version' => fn (Blueprint $table) => $table->integer('version')->default(1),
            'company_id' => fn (Blueprint $table) => $table->unsignedBigInteger('company_id')->nullable()->index(),
            'is_valid' => fn (Blueprint $table) => $table->boolean('is_valid')->default(false),
            'last_validated_at' => fn (Blueprint $table) => $table->timestamp('last_validated_at')->nullable(),
            'test_inputs' => fn (Blueprint $table) => $table->json('test_inputs')->nullable(),
            'test_result' => fn (Blueprint $table) => $table->decimal('test_result', 16, 2)->nullable(),
            'variables_used' => fn (Blueprint $table) => $table->json('variables_used')->nullable(),
            'created_by' => fn (Blueprint $table) => $table->unsignedBigInteger('created_by')->nullable(),
            'updated_by' => fn (Blueprint $table) => $table->unsignedBigInteger('updated_by')->nullable(),
            'deleted_at' => fn (Blueprint $table) => $table->softDeletes(),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('payroll_formulas', $column)) {
                Schema::table('payroll_formulas', $definition);
            }
        }
    }

    public function down(): void
    {
        // Columns are shared with the original table definition; nothing is dropped.
    }
};
