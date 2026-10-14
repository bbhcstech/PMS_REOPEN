<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = array_unique(array_filter([
            config('database.default'),
            'tenant',
        ]));

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('holidays')) {
                    Schema::connection($conn)->table('holidays', function (Blueprint $table) use ($conn) {
                        if (! Schema::connection($conn)->hasColumn('holidays', 'group_id')) {
                            $table->string('group_id', 64)->nullable()->index()->after('id');
                        }
                        if (! Schema::connection($conn)->hasColumn('holidays', 'occassion')) {
                            $table->string('occassion', 255)->nullable()->after('title');
                        }
                        if (! Schema::connection($conn)->hasColumn('holidays', 'department_id_json')) {
                            $table->text('department_id_json')->nullable();
                        }
                        if (! Schema::connection($conn)->hasColumn('holidays', 'designation_id_json')) {
                            $table->text('designation_id_json')->nullable();
                        }
                        if (! Schema::connection($conn)->hasColumn('holidays', 'employment_type_json')) {
                            $table->text('employment_type_json')->nullable();
                        }
                        if (! Schema::connection($conn)->hasColumn('holidays', 'notification_sent')) {
                            $table->boolean('notification_sent')->default(false);
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore schema migration exceptions gracefully
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = array_unique(array_filter([
            config('database.default'),
            'tenant',
        ]));

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('holidays')) {
                    Schema::connection($conn)->table('holidays', function (Blueprint $table) use ($conn) {
                        $colsToDrop = [];
                        foreach (['group_id', 'occassion', 'department_id_json', 'designation_id_json', 'employment_type_json', 'notification_sent'] as $col) {
                            if (Schema::connection($conn)->hasColumn('holidays', $col)) {
                                $colsToDrop[] = $col;
                            }
                        }
                        if (! empty($colsToDrop)) {
                            $table->dropColumn($colsToDrop);
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore schema rollback exceptions gracefully
            }
        }
    }
};
