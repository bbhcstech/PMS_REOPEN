<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_settings') && !Schema::hasColumn('company_settings', 'company_location')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->string('company_location')->nullable()->after('company_website');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_settings') && Schema::hasColumn('company_settings', 'company_location')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->dropColumn('company_location');
            });
        }
    }
};
