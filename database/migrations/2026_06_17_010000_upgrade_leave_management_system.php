<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->decimal('annual_limit', 8, 2)->default(0);
                $table->boolean('is_paid')->default(true);
                $table->boolean('requires_document')->default(false);
                $table->boolean('allows_apology')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('leave_policies')) {
            Schema::table('leave_policies', function (Blueprint $table) {
                if (! Schema::hasColumn('leave_policies', 'sick_leave_limit')) {
                    $table->decimal('sick_leave_limit', 8, 2)->default(6)->after('annual_leaves');
                }
                if (! Schema::hasColumn('leave_policies', 'casual_leave_limit')) {
                    $table->decimal('casual_leave_limit', 8, 2)->default(12)->after('sick_leave_limit');
                }
                if (! Schema::hasColumn('leave_policies', 'maternity_leave_limit')) {
                    $table->decimal('maternity_leave_limit', 8, 2)->default(0)->after('casual_leave_limit');
                }
                if (! Schema::hasColumn('leave_policies', 'leave_year_start_month')) {
                    $table->unsignedTinyInteger('leave_year_start_month')->default(4)->after('maternity_leave_limit');
                }
                if (! Schema::hasColumn('leave_policies', 'leave_year_end_month')) {
                    $table->unsignedTinyInteger('leave_year_end_month')->default(3)->after('leave_year_start_month');
                }
                if (! Schema::hasColumn('leave_policies', 'casual_advance_days')) {
                    $table->unsignedTinyInteger('casual_advance_days')->default(7)->after('leave_year_end_month');
                }
                if (! Schema::hasColumn('leave_policies', 'casual_manual_review_days')) {
                    $table->unsignedTinyInteger('casual_manual_review_days')->default(2)->after('casual_advance_days');
                }
                if (! Schema::hasColumn('leave_policies', 'auto_approve_casual_leave')) {
                    $table->boolean('auto_approve_casual_leave')->default(false)->after('casual_manual_review_days');
                }
                if (! Schema::hasColumn('leave_policies', 'hr_approval_required')) {
                    $table->boolean('hr_approval_required')->default(true)->after('auto_approve_casual_leave');
                }
                if (! Schema::hasColumn('leave_policies', 'allow_sick_apology')) {
                    $table->boolean('allow_sick_apology')->default(true)->after('hr_approval_required');
                }
                if (! Schema::hasColumn('leave_policies', 'unpaid_leave_handling')) {
                    $table->string('unpaid_leave_handling')->default('unpaid_leave')->after('allow_sick_apology');
                }
                if (! Schema::hasColumn('leave_policies', 'maternity_is_paid')) {
                    $table->boolean('maternity_is_paid')->default(true)->after('unpaid_leave_handling');
                }
                if (! Schema::hasColumn('leave_policies', 'maternity_requires_document')) {
                    $table->boolean('maternity_requires_document')->default(true)->after('maternity_is_paid');
                }
            });
        }

        if (Schema::hasTable('leave_balances')) {
            Schema::table('leave_balances', function (Blueprint $table) {
                if (! Schema::hasColumn('leave_balances', 'leave_year')) {
                    $table->string('leave_year')->nullable()->after('year');
                }
                if (! Schema::hasColumn('leave_balances', 'year_start')) {
                    $table->date('year_start')->nullable()->after('leave_year');
                }
                if (! Schema::hasColumn('leave_balances', 'year_end')) {
                    $table->date('year_end')->nullable()->after('year_start');
                }
                if (! Schema::hasColumn('leave_balances', 'sick_allocated')) {
                    $table->decimal('sick_allocated', 8, 2)->default(6)->after('remaining_leaves');
                }
                if (! Schema::hasColumn('leave_balances', 'sick_used')) {
                    $table->decimal('sick_used', 8, 2)->default(0)->after('sick_allocated');
                }
                if (! Schema::hasColumn('leave_balances', 'casual_allocated')) {
                    $table->decimal('casual_allocated', 8, 2)->default(12)->after('sick_used');
                }
                if (! Schema::hasColumn('leave_balances', 'casual_used')) {
                    $table->decimal('casual_used', 8, 2)->default(0)->after('casual_allocated');
                }
                if (! Schema::hasColumn('leave_balances', 'maternity_allocated')) {
                    $table->decimal('maternity_allocated', 8, 2)->default(0)->after('casual_used');
                }
                if (! Schema::hasColumn('leave_balances', 'maternity_used')) {
                    $table->decimal('maternity_used', 8, 2)->default(0)->after('maternity_allocated');
                }
                if (! Schema::hasColumn('leave_balances', 'unpaid_used')) {
                    $table->decimal('unpaid_used', 8, 2)->default(0)->after('maternity_used');
                }
                if (! Schema::hasColumn('leave_balances', 'absent_count')) {
                    $table->decimal('absent_count', 8, 2)->default(0)->after('unpaid_used');
                }
                if (! Schema::hasColumn('leave_balances', 'pending_requests')) {
                    $table->unsignedInteger('pending_requests')->default(0)->after('absent_count');
                }
                if (! Schema::hasColumn('leave_balances', 'approved_requests')) {
                    $table->unsignedInteger('approved_requests')->default(0)->after('pending_requests');
                }
                if (! Schema::hasColumn('leave_balances', 'rejected_requests')) {
                    $table->unsignedInteger('rejected_requests')->default(0)->after('approved_requests');
                }
            });
        }

        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                if (! Schema::hasColumn('leaves', 'leave_type_id')) {
                    $table->foreignId('leave_type_id')->nullable()->after('user_id')->constrained('leave_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('leaves', 'total_days')) {
                    $table->decimal('total_days', 8, 2)->default(1)->after('end_date');
                }
                if (! Schema::hasColumn('leaves', 'attachment')) {
                    $table->string('attachment')->nullable()->after('reason');
                }
                if (! Schema::hasColumn('leaves', 'apology_note')) {
                    $table->text('apology_note')->nullable()->after('attachment');
                }
                if (! Schema::hasColumn('leaves', 'emergency_flag')) {
                    $table->boolean('emergency_flag')->default(false)->after('apology_note');
                }
                if (! Schema::hasColumn('leaves', 'half_day_flag')) {
                    $table->boolean('half_day_flag')->default(false)->after('emergency_flag');
                }
                if (! Schema::hasColumn('leaves', 'contact_during_leave')) {
                    $table->string('contact_during_leave')->nullable()->after('half_day_flag');
                }
                if (! Schema::hasColumn('leaves', 'approval_status')) {
                    $table->string('approval_status')->default('pending')->after('status');
                }
                if (! Schema::hasColumn('leaves', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leaves', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (! Schema::hasColumn('leaves', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('leaves', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }
                if (! Schema::hasColumn('leaves', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
                if (! Schema::hasColumn('leaves', 'is_paid')) {
                    $table->boolean('is_paid')->default(true)->after('rejection_reason');
                }
                if (! Schema::hasColumn('leaves', 'is_unpaid')) {
                    $table->boolean('is_unpaid')->default(false)->after('is_paid');
                }
                if (! Schema::hasColumn('leaves', 'payroll_deduction_flag')) {
                    $table->boolean('payroll_deduction_flag')->default(false)->after('is_unpaid');
                }
                if (! Schema::hasColumn('leaves', 'admin_note')) {
                    $table->text('admin_note')->nullable()->after('payroll_deduction_flag');
                }
                if (! Schema::hasColumn('leaves', 'leave_year')) {
                    $table->string('leave_year')->nullable()->after('admin_note');
                }
            });
        }

        if (! Schema::hasTable('leave_approvals')) {
            Schema::create('leave_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_id')->constrained('leaves')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action');
                $table->text('note')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_balance_logs')) {
            Schema::create('leave_balance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('leave_id')->nullable()->constrained('leaves')->nullOnDelete();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('leave_year');
                $table->string('action');
                $table->decimal('days', 8, 2)->default(0);
                $table->json('before_snapshot')->nullable();
                $table->json('after_snapshot')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_policy_logs')) {
            Schema::create('leave_policy_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_policy_id')->nullable()->constrained('leave_policies')->nullOnDelete();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('before_snapshot')->nullable();
                $table->json('after_snapshot')->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        DB::table('leave_types')->updateOrInsert(['code' => 'SL'], [
            'name' => 'Sick Leave',
            'annual_limit' => 6,
            'is_paid' => true,
            'requires_document' => false,
            'allows_apology' => true,
            'is_active' => true,
            'sort_order' => 1,
            'updated_at' => $now,
            'created_at' => $now,
        ]);
        DB::table('leave_types')->updateOrInsert(['code' => 'CL'], [
            'name' => 'Casual Leave',
            'annual_limit' => 12,
            'is_paid' => true,
            'requires_document' => false,
            'allows_apology' => false,
            'is_active' => true,
            'sort_order' => 2,
            'updated_at' => $now,
            'created_at' => $now,
        ]);
        DB::table('leave_types')->updateOrInsert(['code' => 'ML'], [
            'name' => 'Maternity Leave',
            'annual_limit' => 0,
            'is_paid' => true,
            'requires_document' => true,
            'allows_apology' => false,
            'is_active' => true,
            'sort_order' => 3,
            'updated_at' => $now,
            'created_at' => $now,
        ]);
        DB::table('leave_types')->updateOrInsert(['code' => 'UL'], [
            'name' => 'Unpaid Leave',
            'annual_limit' => 0,
            'is_paid' => false,
            'requires_document' => false,
            'allows_apology' => false,
            'is_active' => true,
            'sort_order' => 4,
            'updated_at' => $now,
            'created_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_logs');
        Schema::dropIfExists('leave_balance_logs');
        Schema::dropIfExists('leave_approvals');

        if (Schema::hasTable('leaves') && Schema::hasColumn('leaves', 'leave_type_id')) {
            Schema::table('leaves', function (Blueprint $table) {
                try {
                    $table->dropForeign(['leave_type_id']);
                } catch (\Throwable) {
                    //
                }
                $table->dropColumn('leave_type_id');
            });
        }

        Schema::dropIfExists('leave_types');
    }
};
