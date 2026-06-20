<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_timers', function (Blueprint $table) {
            if (! Schema::hasColumn('task_timers', 'remarks')) {
                $table->text('remarks')->nullable()->after('memo');
            }
        });

        if (! Schema::hasTable('daily_timesheets')) {
            Schema::create('daily_timesheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('work_date')->index();
                $table->decimal('total_hours', 8, 2)->default(0);
                $table->unsignedInteger('log_count')->default(0);
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'work_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_timesheets');

        Schema::table('task_timers', function (Blueprint $table) {
            if (Schema::hasColumn('task_timers', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
