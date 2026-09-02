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
        // 1. Ensure tickets table has status and all expected fields
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (! Schema::hasColumn('tickets', 'status')) {
                    $table->string('status', 50)->default('open')->after('priority');
                }
                if (! Schema::hasColumn('tickets', 'deadline')) {
                    $table->date('deadline')->nullable()->after('priority');
                }
                if (! Schema::hasColumn('tickets', 'affected_module')) {
                    $table->string('affected_module')->nullable()->after('project_id');
                }
                if (! Schema::hasColumn('tickets', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }

        // 2. Ensure notifications table exists
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Ensure users table has all critical role and status fields
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('role');
                }
                if (! Schema::hasColumn('users', 'login_allowed')) {
                    $table->boolean('login_allowed')->default(true)->after('is_active');
                }
                if (! Schema::hasColumn('users', 'raw_password')) {
                    $table->string('raw_password')->nullable()->after('password');
                }
                if (! Schema::hasColumn('users', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('updated_at');
                }
            });
        }

        // 4. Ensure tasks table has status and due_date
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('tasks', 'status')) {
                    $table->string('status', 50)->default('Incomplete')->after('description');
                }
                if (! Schema::hasColumn('tasks', 'due_date')) {
                    $table->date('due_date')->nullable()->after('status');
                }
                if (! Schema::hasColumn('tasks', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }

        // 5. Ensure projects table has status and deadline
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (! Schema::hasColumn('projects', 'status')) {
                    $table->string('status', 50)->default('in progress')->after('description');
                }
                if (! Schema::hasColumn('projects', 'deadline')) {
                    $table->date('deadline')->nullable()->after('status');
                }
                if (! Schema::hasColumn('projects', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
