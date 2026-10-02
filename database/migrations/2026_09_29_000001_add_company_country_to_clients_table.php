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
        if (Schema::hasTable('clients') && !Schema::hasColumn('clients', 'company_country')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('company_country', 100)->nullable()->after('company_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'company_country')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('company_country');
            });
        }
    }
};
