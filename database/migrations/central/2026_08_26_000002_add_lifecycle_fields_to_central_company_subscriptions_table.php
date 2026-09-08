<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('company_subscriptions', function (Blueprint $table) {
            $hasPrice = Schema::connection('central')->hasColumn('company_subscriptions', 'price');
            $hasPlanId = Schema::connection('central')->hasColumn('company_subscriptions', 'plan_id');
            $hasStatus = Schema::connection('central')->hasColumn('company_subscriptions', 'status');

            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'highest_plan_level')) {
                $col = $table->integer('highest_plan_level')->default(0);
                if ($hasPrice) {
                    $col->after('price');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'current_plan_level')) {
                $col = $table->integer('current_plan_level')->default(0);
                if (Schema::connection('central')->hasColumn('company_subscriptions', 'highest_plan_level')) {
                    $col->after('highest_plan_level');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'previous_plan_id')) {
                $col = $table->unsignedBigInteger('previous_plan_id')->nullable();
                if ($hasPlanId) {
                    $col->after('plan_id');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'activated_at')) {
                $col = $table->timestamp('activated_at')->nullable();
                if ($hasStatus) {
                    $col->after('status');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'expired_at')) {
                $col = $table->timestamp('expired_at')->nullable();
                if (Schema::connection('central')->hasColumn('company_subscriptions', 'activated_at')) {
                    $col->after('activated_at');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'suspended_at')) {
                $col = $table->timestamp('suspended_at')->nullable();
                if (Schema::connection('central')->hasColumn('company_subscriptions', 'expired_at')) {
                    $col->after('expired_at');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'renewed_at')) {
                $col = $table->timestamp('renewed_at')->nullable();
                if (Schema::connection('central')->hasColumn('company_subscriptions', 'suspended_at')) {
                    $col->after('suspended_at');
                }
            }
            if (! Schema::connection('central')->hasColumn('company_subscriptions', 'upgraded_at')) {
                $col = $table->timestamp('upgraded_at')->nullable();
                if (Schema::connection('central')->hasColumn('company_subscriptions', 'renewed_at')) {
                    $col->after('renewed_at');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('company_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'highest_plan_level', 'current_plan_level', 'previous_plan_id',
                'activated_at', 'expired_at', 'suspended_at', 'renewed_at', 'upgraded_at'
            ]);
        });
    }
};
