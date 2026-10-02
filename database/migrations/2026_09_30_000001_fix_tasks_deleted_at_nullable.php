<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'deleted_at')) {
            DB::statement("ALTER TABLE `tasks` MODIFY `deleted_at` TIMESTAMP NULL DEFAULT NULL;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep nullable to avoid automatic soft deletion of new records
    }
};
