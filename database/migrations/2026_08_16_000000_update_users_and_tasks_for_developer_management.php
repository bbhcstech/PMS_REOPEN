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
        // 1. Update users table for developer password flow & personal email
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'raw_password')) {
                $table->string('raw_password')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'personal_email')) {
                $table->string('personal_email')->nullable()->after('email');
            }
        });

        // 2. Update employee_details table for developer metadata
        Schema::table('employee_details', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_details', 'developer_id')) {
                $table->string('developer_id')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('employee_details', 'experience')) {
                $table->string('experience')->nullable()->after('skills');
            }
            if (! Schema::hasColumn('employee_details', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('status');
            }
        });

        // 3. Update tasks table for assignment details & instructions
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'additional_instructions')) {
                $table->text('additional_instructions')->nullable()->after('description');
            }
            if (! Schema::hasColumn('tasks', 'attachments')) {
                $table->text('attachments')->nullable()->after('additional_instructions');
            }
            if (! Schema::hasColumn('tasks', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
            if (Schema::hasColumn('users', 'personal_email')) {
                $table->dropColumn('personal_email');
            }
        });

        Schema::table('employee_details', function (Blueprint $table) {
            if (Schema::hasColumn('employee_details', 'developer_id')) {
                $table->dropColumn('developer_id');
            }
            if (Schema::hasColumn('employee_details', 'experience')) {
                $table->dropColumn('experience');
            }
            if (Schema::hasColumn('employee_details', 'joining_date')) {
                $table->dropColumn('joining_date');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'additional_instructions')) {
                $table->dropColumn('additional_instructions');
            }
            if (Schema::hasColumn('tasks', 'attachments')) {
                $table->dropColumn('attachments');
            }
            if (Schema::hasColumn('tasks', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
