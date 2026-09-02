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
        Schema::create('recruitment_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('title');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('department_name')->nullable();
            $table->integer('positions')->default(1);
            $table->string('employment_type')->default('Full-time'); // Full-time, Part-time, Contract, Remote, Internship
            $table->string('experience_required')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements_summary')->nullable();
            $table->string('status')->default('open'); // open, in_progress, closed, cancelled
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_requirements');
    }
};
