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
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->string('appraisal_period')->default('2026 Q3');

            // Project Performance Appraisal Metrics
            $table->integer('projects_count')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->decimal('project_score', 5, 2)->default(80.00); // 0-100
            $table->text('project_remarks')->nullable();

            // Attendance Appraisal Metrics
            $table->integer('present_days')->default(0);
            $table->integer('total_working_days')->default(22);
            $table->decimal('attendance_percentage', 5, 2)->default(90.00); // 0-100%
            $table->decimal('attendance_score', 5, 2)->default(90.00); // 0-100
            $table->text('attendance_remarks')->nullable();

            // Behaviour & Soft Skills Appraisal Metrics
            $table->decimal('teamwork_score', 4, 2)->default(8.5); // 1-10
            $table->decimal('communication_score', 4, 2)->default(8.5); // 1-10
            $table->decimal('punctuality_score', 4, 2)->default(8.5); // 1-10
            $table->decimal('behaviour_score', 5, 2)->default(85.00); // 0-100
            $table->text('behaviour_remarks')->nullable();

            // Overall Summary Metrics
            $table->decimal('overall_score', 5, 2)->default(85.00); // Weighted: 40% Proj + 30% Att + 30% Beh
            $table->string('overall_grade')->default('A'); // A+, A, B, C, D
            $table->string('recommendation')->default('Salary Increment & Continuation');
            $table->string('status')->default('approved'); // draft, submitted, approved
            $table->unsignedBigInteger('evaluated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
