<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $hasStatus = Schema::hasColumn('companies', 'status');
                $hasTrialEndsAt = Schema::hasColumn('companies', 'trial_ends_at');

                if (! Schema::hasColumn('companies', 'highest_plan_level')) {
                    $col = $table->integer('highest_plan_level')->default(0)->comment('0=FREE, 1=GOLD, 2=PLATINUM, 3=DIAMOND');
                    if ($hasStatus) {
                        $col->after('status');
                    }
                }
                if (! Schema::hasColumn('companies', 'highest_plan_slug')) {
                    $col = $table->string('highest_plan_slug')->default('free');
                    if (Schema::hasColumn('companies', 'highest_plan_level')) {
                        $col->after('highest_plan_level');
                    }
                }
                if (! Schema::hasColumn('companies', 'suspended_at')) {
                    $col = $table->timestamp('suspended_at')->nullable();
                    if ($hasTrialEndsAt) {
                        $col->after('trial_ends_at');
                    }
                }
            });
        }

        if (Schema::hasTable('company_subscriptions')) {
            Schema::table('company_subscriptions', function (Blueprint $table) {
                $hasPrice = Schema::hasColumn('company_subscriptions', 'price');
                $hasPlanId = Schema::hasColumn('company_subscriptions', 'plan_id');
                $hasStatus = Schema::hasColumn('company_subscriptions', 'status');

                if (! Schema::hasColumn('company_subscriptions', 'highest_plan_level')) {
                    $col = $table->integer('highest_plan_level')->default(0);
                    if ($hasPrice) {
                        $col->after('price');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'current_plan_level')) {
                    $col = $table->integer('current_plan_level')->default(0);
                    if (Schema::hasColumn('company_subscriptions', 'highest_plan_level')) {
                        $col->after('highest_plan_level');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'previous_plan_id')) {
                    $col = $table->unsignedBigInteger('previous_plan_id')->nullable();
                    if ($hasPlanId) {
                        $col->after('plan_id');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'activated_at')) {
                    $col = $table->timestamp('activated_at')->nullable();
                    if ($hasStatus) {
                        $col->after('status');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'expired_at')) {
                    $col = $table->timestamp('expired_at')->nullable();
                    if (Schema::hasColumn('company_subscriptions', 'activated_at')) {
                        $col->after('activated_at');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'suspended_at')) {
                    $col = $table->timestamp('suspended_at')->nullable();
                    if (Schema::hasColumn('company_subscriptions', 'expired_at')) {
                        $col->after('expired_at');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'renewed_at')) {
                    $col = $table->timestamp('renewed_at')->nullable();
                    if (Schema::hasColumn('company_subscriptions', 'suspended_at')) {
                        $col->after('suspended_at');
                    }
                }
                if (! Schema::hasColumn('company_subscriptions', 'upgraded_at')) {
                    $col = $table->timestamp('upgraded_at')->nullable();
                    if (Schema::hasColumn('company_subscriptions', 'renewed_at')) {
                        $col->after('renewed_at');
                    }
                }
            });
        }

        if (! Schema::hasTable('subscription_histories')) {
            Schema::create('subscription_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->unsignedBigInteger('previous_plan_id')->nullable();
                $table->unsignedBigInteger('new_plan_id')->nullable();
                $table->string('previous_plan_name')->nullable();
                $table->string('new_plan_name')->nullable();
                $table->string('action');
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
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn(['highest_plan_level', 'highest_plan_slug', 'suspended_at']);
            });
        }
        if (Schema::hasTable('company_subscriptions')) {
            Schema::table('company_subscriptions', function (Blueprint $table) {
                $table->dropColumn([
                    'highest_plan_level', 'current_plan_level', 'previous_plan_id',
                    'activated_at', 'expired_at', 'suspended_at', 'renewed_at', 'upgraded_at'
                ]);
            });
        }
        Schema::dropIfExists('subscription_histories');
    }
};
