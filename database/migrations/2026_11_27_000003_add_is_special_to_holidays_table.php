<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = array_unique(array_filter([
            config('database.default'),
            'tenant',
        ]));

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('holidays')
                    && ! Schema::connection($conn)->hasColumn('holidays', 'is_special')) {
                    Schema::connection($conn)->table('holidays', function (Blueprint $table) {
                        $table->boolean('is_special')->default(false)->after('day_type');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore schema migration exceptions gracefully
            }
        }
    }

    public function down(): void
    {
        $connections = array_unique(array_filter([
            config('database.default'),
            'tenant',
        ]));

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('holidays')
                    && Schema::connection($conn)->hasColumn('holidays', 'is_special')) {
                    Schema::connection($conn)->table('holidays', function (Blueprint $table) {
                        $table->dropColumn('is_special');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore schema rollback exceptions gracefully
            }
        }
    }
};
