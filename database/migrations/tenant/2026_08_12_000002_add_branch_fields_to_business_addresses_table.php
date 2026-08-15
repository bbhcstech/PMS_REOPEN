<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_addresses')) {
            Schema::table('business_addresses', function (Blueprint $table) {
                if (!Schema::hasColumn('business_addresses', 'branch_name')) {
                    $table->string('branch_name')->nullable()->after('id');
                }
                if (!Schema::hasColumn('business_addresses', 'email')) {
                    $table->string('email')->nullable()->after('location');
                }
                if (!Schema::hasColumn('business_addresses', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (!Schema::hasColumn('business_addresses', 'logo')) {
                    $table->string('logo')->nullable()->after('tax_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('business_addresses')) {
            Schema::table('business_addresses', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('business_addresses', 'branch_name')) {
                    $columnsToDrop[] = 'branch_name';
                }
                if (Schema::hasColumn('business_addresses', 'email')) {
                    $columnsToDrop[] = 'email';
                }
                if (Schema::hasColumn('business_addresses', 'phone')) {
                    $columnsToDrop[] = 'phone';
                }
                if (Schema::hasColumn('business_addresses', 'logo')) {
                    $columnsToDrop[] = 'logo';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
