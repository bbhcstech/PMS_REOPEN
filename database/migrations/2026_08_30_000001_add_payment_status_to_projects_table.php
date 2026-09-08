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
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'payment_status')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('payment_status', 50)->nullable()->default('unpaid')->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'payment_status')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('payment_status');
            });
        }
    }
};
