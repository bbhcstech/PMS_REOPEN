<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'priority')) {
                $table->string('priority')->default('medium')->after('status')->index();
            }
            if (! Schema::hasColumn('projects', 'remarks')) {
                $table->text('remarks')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('projects', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('client_id')->constrained('users')->nullOnDelete();
            }
        });

        try {
            DB::statement("ALTER TABLE projects MODIFY status ENUM('pending','not started','in progress','on hold','completed','delayed') DEFAULT 'pending'");
        } catch (\Throwable $e) {
            // Non-MySQL databases used in local tests may not support MODIFY ENUM.
        }

        Schema::table('project_user', function (Blueprint $table) {
            if (! Schema::hasColumn('project_user', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('project_user', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            }
        });

        if (! Schema::hasTable('project_updates')) {
            Schema::create('project_updates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->nullable();
                $table->unsignedTinyInteger('progress')->default(0);
                $table->text('remarks')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_updates');

        Schema::table('project_user', function (Blueprint $table) {
            if (Schema::hasColumn('project_user', 'assigned_by')) {
                $table->dropConstrainedForeignId('assigned_by');
            }
            if (Schema::hasColumn('project_user', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('projects', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('projects', 'priority')) {
                $table->dropColumn('priority');
            }
        });

        try {
            DB::statement("ALTER TABLE projects MODIFY status ENUM('not started','in progress','on hold','completed') DEFAULT 'not started'");
        } catch (\Throwable $e) {
        }
    }
};
