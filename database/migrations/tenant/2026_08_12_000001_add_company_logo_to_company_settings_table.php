<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_settings') && !Schema::hasColumn('company_settings', 'company_logo')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->string('company_logo')->nullable()->after('company_location');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_settings') && Schema::hasColumn('company_settings', 'company_logo')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->dropColumn('company_logo');
            });
        }
    }
};
