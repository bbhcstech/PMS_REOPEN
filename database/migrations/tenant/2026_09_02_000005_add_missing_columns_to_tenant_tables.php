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

        // 6. Ensure employee_details table has status, exit_date, and company_id
        if (Schema::hasTable('employee_details')) {
            Schema::table('employee_details', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_details', 'status')) {
                    $table->string('status', 191)->nullable()->default('active')->after('user_id');
                }
                if (! Schema::hasColumn('employee_details', 'exit_date')) {
                    $table->date('exit_date')->nullable()->after('notice_end_date');
                }
                if (! Schema::hasColumn('employee_details', 'last_date')) {
                    $table->date('last_date')->nullable()->after('exit_date');
                }
                if (! Schema::hasColumn('employee_details', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }

        // 7. Ensure parent_departments table has dpt_name, dpt_code, company_id, archived_at
        if (Schema::hasTable('parent_departments')) {
            Schema::table('parent_departments', function (Blueprint $table) {
                if (! Schema::hasColumn('parent_departments', 'dpt_name')) {
                    $table->string('dpt_name')->default('General')->after('id');
                }
                if (! Schema::hasColumn('parent_departments', 'dpt_code')) {
                    $table->string('dpt_code')->nullable()->after('dpt_name');
                }
                if (! Schema::hasColumn('parent_departments', 'added_by')) {
                    $table->unsignedBigInteger('added_by')->nullable()->after('dpt_code');
                }
                if (! Schema::hasColumn('parent_departments', 'last_updated_by')) {
                    $table->unsignedBigInteger('last_updated_by')->nullable()->after('added_by');
                }
                if (! Schema::hasColumn('parent_departments', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('parent_departments', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('updated_at');
                }
            });
        }

        // 8. Ensure departments table has dpt_name, dpt_code, company_id, archived_at
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (! Schema::hasColumn('departments', 'dpt_name')) {
                    $table->string('dpt_name')->default('General')->after('id');
                }
                if (! Schema::hasColumn('departments', 'dpt_code')) {
                    $table->string('dpt_code')->nullable()->after('dpt_name');
                }
                if (! Schema::hasColumn('departments', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('departments', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('updated_at');
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
