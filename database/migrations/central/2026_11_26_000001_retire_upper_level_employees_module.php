<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};

/**
 * "Add Upper Level Employee" was removed. Its stored module row is deactivated (not deleted) so it
 * no longer appears in plan, feature or module lists; down() re-activates it.
 */
return new class extends Migration {
    protected $connection = 'central';

    public function up(): void
    {
        if (Schema::connection('central')->hasTable('modules')) {
            DB::connection('central')->table('modules')->where('slug', 'upper-level-employees')->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        if (Schema::connection('central')->hasTable('modules')) {
            DB::connection('central')->table('modules')->where('slug', 'upper-level-employees')->update(['is_active' => true]);
        }
    }
};
