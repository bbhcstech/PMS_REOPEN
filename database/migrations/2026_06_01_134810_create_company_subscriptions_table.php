<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->date('trial_ends_at')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('active');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('company_subscriptions'); }
};
