<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('companies', function (Blueprint $table) {
            $hasStatus = Schema::connection('central')->hasColumn('companies', 'status');
            $hasTrialEndsAt = Schema::connection('central')->hasColumn('companies', 'trial_ends_at');

            if (! Schema::connection('central')->hasColumn('companies', 'highest_plan_level')) {
                $col = $table->integer('highest_plan_level')->default(0)->comment('0=FREE, 1=GOLD, 2=PLATINUM, 3=DIAMOND');
                if ($hasStatus) {
                    $col->after('status');
                }
            }
            if (! Schema::connection('central')->hasColumn('companies', 'highest_plan_slug')) {
                $col = $table->string('highest_plan_slug')->default('free');
                if (Schema::connection('central')->hasColumn('companies', 'highest_plan_level')) {
                    $col->after('highest_plan_level');
                }
            }
            if (! Schema::connection('central')->hasColumn('companies', 'suspended_at')) {
                $col = $table->timestamp('suspended_at')->nullable();
                if ($hasTrialEndsAt) {
                    $col->after('trial_ends_at');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('companies', function (Blueprint $table) {
            $table->dropColumn(['highest_plan_level', 'highest_plan_slug', 'suspended_at']);
        });
    }
};
