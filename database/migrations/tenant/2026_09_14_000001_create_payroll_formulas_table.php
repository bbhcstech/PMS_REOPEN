<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category')->default('custom'); // earnings, deduction, tax, bonus, custom
            $table->text('description')->nullable();
            $table->text('formula')->nullable(); // e.g. "BASIC * 0.12"
            $table->json('variables_used')->nullable(); // ['BASIC', 'GROSS', 'HRA']
            $table->json('test_inputs')->nullable(); // sample values for testing
            $table->decimal('test_result', 16, 2)->nullable();
            $table->boolean('is_valid')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_validated_at')->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_formulas');
    }
};
