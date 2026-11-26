<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};

/**
 * "Add Upper Level Employee" was removed. The company's copy of its module row is deactivated
 * (not deleted) so it no longer shows in Module Management or Role & Permission; down() restores it.
 * Existing company-role accounts and their roles are not touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('tenant')->hasTable('modules')) {
            DB::connection('tenant')->table('modules')->where('slug', 'upper-level-employees')->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        if (Schema::connection('tenant')->hasTable('modules')) {
            DB::connection('tenant')->table('modules')->where('slug', 'upper-level-employees')->update(['is_active' => true]);
        }
    }
};
