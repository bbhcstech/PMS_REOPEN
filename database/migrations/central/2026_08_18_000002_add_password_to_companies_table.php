<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('central')->hasTable('companies') && ! Schema::connection('central')->hasColumn('companies', 'password')) {
            Schema::connection('central')->table('companies', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
            });
        }

        if (Schema::connection('tenant')->hasTable('companies') && ! Schema::connection('tenant')->hasColumn('companies', 'password')) {
            Schema::connection('tenant')->table('companies', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('central')->hasTable('companies') && Schema::connection('central')->hasColumn('companies', 'password')) {
            Schema::connection('central')->table('companies', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }

        if (Schema::connection('tenant')->hasTable('companies') && Schema::connection('tenant')->hasColumn('companies', 'password')) {
            Schema::connection('tenant')->table('companies', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};
