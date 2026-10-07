<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_categories') && ! Schema::hasColumn('client_categories', 'name')) {
            Schema::table('client_categories', function (Blueprint $table) {
                $table->string('name')->after('id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('client_categories') && Schema::hasColumn('client_categories', 'name')) {
            Schema::table('client_categories', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
