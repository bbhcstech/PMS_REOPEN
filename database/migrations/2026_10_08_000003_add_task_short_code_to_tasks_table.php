<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('tasks', 'task_short_code')) {
                    $table->string('task_short_code', 100)->nullable()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'task_short_code')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('task_short_code');
            });
        }
    }
};
