<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        if (! Schema::connection('central')->hasTable('subscription_histories')) {
            Schema::connection('central')->create('subscription_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->unsignedBigInteger('previous_plan_id')->nullable();
                $table->unsignedBigInteger('new_plan_id')->nullable();
                $table->string('previous_plan_name')->nullable();
                $table->string('new_plan_name')->nullable();
                $table->string('action')->comment('TRIAL_STARTED, PLAN_PURCHASED, PLAN_RENEWED, PLAN_UPGRADED, SUBSCRIPTION_EXPIRED, COMPANY_SUSPENDED, COMPANY_REACTIVATED');
                $table->string('performed_by')->nullable();
                $table->text('reason')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('subscription_histories');
    }
};
