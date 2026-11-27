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
                if (! Schema::connection($conn)->hasTable('holidays')) {
                    continue;
                }

                Schema::connection($conn)->table('holidays', function (Blueprint $table) use ($conn) {
                    if (Schema::connection($conn)->hasColumn('holidays', 'title')) {
                        $table->text('title')->change();
                    }
                    if (Schema::connection($conn)->hasColumn('holidays', 'occassion')) {
                        $table->text('occassion')->nullable()->change();
                    }
                });
            } catch (\Throwable $e) {
                // Ignore schema migration exceptions gracefully
            }
        }
    }

    public function down(): void
    {
        // Avoid truncating longer holiday occasions on rollback
    }
};
