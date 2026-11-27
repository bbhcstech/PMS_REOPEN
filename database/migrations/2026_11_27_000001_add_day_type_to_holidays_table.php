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
                    && ! Schema::connection($conn)->hasColumn('holidays', 'day_type')) {
                    Schema::connection($conn)->table('holidays', function (Blueprint $table) {
                        $table->string('day_type', 20)->default('full_day')->after('type');
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
                    && Schema::connection($conn)->hasColumn('holidays', 'day_type')) {
                    Schema::connection($conn)->table('holidays', function (Blueprint $table) {
                        $table->dropColumn('day_type');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore schema rollback exceptions gracefully
            }
        }
    }
};
