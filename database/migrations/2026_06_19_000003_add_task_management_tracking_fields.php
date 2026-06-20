<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('status');
            }
            if (! Schema::hasColumn('tasks', 'remarks')) {
                $table->text('remarks')->nullable()->after('description');
            }
            if (! Schema::hasColumn('tasks', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tasks', 'completed_on')) {
                $table->timestamp('completed_on')->nullable()->after('progress');
            }
        });

        Schema::table('assigned_task_user', function (Blueprint $table) {
            if (! Schema::hasColumn('assigned_task_user', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('assigned_task_user', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            }
        });

        Schema::table('task_timers', function (Blueprint $table) {
            if (! Schema::hasColumn('task_timers', 'project_id')) {
                $table->foreignId('project_id')->nullable()->after('task_id')->constrained('projects')->nullOnDelete();
            }
            if (! Schema::hasColumn('task_timers', 'start_date')) {
                $table->date('start_date')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('task_timers', 'end_date')) {
                $table->date('end_date')->nullable()->after('end_time');
            }
            if (! Schema::hasColumn('task_timers', 'pause_time')) {
                $table->timestamp('pause_time')->nullable()->after('end_time');
            }
            if (! Schema::hasColumn('task_timers', 'memo')) {
                $table->text('memo')->nullable()->after('pause_time');
            }
            if (! Schema::hasColumn('task_timers', 'total_hours')) {
                $table->decimal('total_hours', 8, 2)->default(0)->after('memo');
            }
            if (! Schema::hasColumn('task_timers', 'status')) {
                $table->string('status')->nullable()->after('total_hours');
            }
        });

        if (! Schema::hasTable('task_updates')) {
            Schema::create('task_updates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->nullable();
                $table->unsignedTinyInteger('progress')->default(0);
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_updates');

        Schema::table('task_timers', function (Blueprint $table) {
            foreach (['status', 'total_hours', 'memo', 'pause_time', 'end_date', 'start_date'] as $column) {
                if (Schema::hasColumn('task_timers', $column)) {
                    $table->dropColumn($column);
                }
            }
            if (Schema::hasColumn('task_timers', 'project_id')) {
                $table->dropConstrainedForeignId('project_id');
            }
        });

        Schema::table('assigned_task_user', function (Blueprint $table) {
            if (Schema::hasColumn('assigned_task_user', 'assigned_by')) {
                $table->dropConstrainedForeignId('assigned_by');
            }
            if (Schema::hasColumn('assigned_task_user', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            foreach (['completed_on', 'remarks', 'progress'] as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
