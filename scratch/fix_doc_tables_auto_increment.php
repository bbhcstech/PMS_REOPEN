<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = ['employee_documents', 'hr_documents', 'manager_documents', 'admin_documents', 'document_views'];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        try {
            $col = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");
            echo "Table {$table} id field info: " . json_encode($col) . "\n";
            
            $extra = strtolower($col[0]->Extra ?? '');
            if (!str_contains($extra, 'auto_increment')) {
                echo "Fixing {$table}: Adding AUTO_INCREMENT to id column...\n";
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                echo "Fixed {$table}!\n";
            }
        } catch (\Throwable $e) {
            echo "Error checking/fixing {$table}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Table {$table} does NOT exist.\n";
    }
}
