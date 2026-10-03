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
        $tables = ['employee_documents', 'hr_documents', 'manager_documents', 'admin_documents', 'document_views'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    $col = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");
                    if (!empty($col)) {
                        $extra = strtolower($col[0]->Extra ?? '');
                        if (!str_contains($extra, 'auto_increment')) {
                            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error("Failed auto increment fix on {$table}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
