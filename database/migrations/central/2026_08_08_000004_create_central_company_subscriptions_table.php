<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration.
 * company_subscriptions: links a company to a plan with billing details.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->date('trial_ends_at')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending', 'trial'])->default('trial');
            $table->boolean('auto_renew')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('company_subscriptions');
    }
};
