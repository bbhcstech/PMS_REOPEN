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
        // 1. appreciations
        if (!Schema::hasTable('appreciations')) {
            Schema::create('appreciations', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('summary')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->string('icon')->nullable();
                $table->string('color_code');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();
            });
        }

        // 2. countries
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
            });
        }

        // 3. currencies
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('currency_name', 191);
                $table->string('currency_symbol', 191)->nullable();
                $table->string('currency_code', 191);
                $table->double('exchange_rate')->nullable();
                $table->enum('is_cryptocurrency', ['yes', 'no'])->default('no');
                $table->double('usd_price')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->enum('currency_position', ['left', 'right', 'left_with_space', 'right_with_space'])->default('left');
                $table->unsignedBigInteger('no_of_decimal');
                $table->string('thousand_separator', 191)->nullable();
                $table->string('decimal_separator', 191)->nullable();
            });
        }

        // 4. bank_accounts
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('type', 191)->nullable();
                $table->string('bank_name', 191)->nullable();
                $table->string('account_name', 191)->nullable();
                $table->string('account_number', 191)->nullable();
                $table->string('account_type', 191)->nullable();
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->string('contact_number', 191)->nullable();
                $table->double('opening_balance', 15, 2)->nullable();
                $table->string('bank_logo', 191)->nullable();
                $table->tinyInteger('status')->nullable();
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->double('bank_balance', 16, 2)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            });
        }

        // 5. company_addresses
        if (!Schema::hasTable('company_addresses')) {
            Schema::create('company_addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->mediumText('address');
                $table->tinyInteger('is_default');
                $table->string('tax_number', 191)->nullable();
                $table->string('tax_name', 191)->nullable();
                $table->string('location', 191)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
            });
        }

        // 6. discussion_categories
        if (!Schema::hasTable('discussion_categories')) {
            Schema::create('discussion_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->integer('order')->default(1);
                $table->string('name', 191);
                $table->string('color', 20);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 7. discussions
        if (!Schema::hasTable('discussions')) {
            Schema::create('discussions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('discussion_category_id')->nullable()->default(1);
                $table->unsignedBigInteger('project_id')->nullable();
                $table->string('title', 191);
                $table->string('color', 20)->nullable()->default('#232629');
                $table->unsignedBigInteger('user_id');
                $table->tinyInteger('pinned')->default(0);
                $table->tinyInteger('closed')->default(0);
                $table->timestamp('deleted_at')->nullable();
                $table->timestamp('last_reply_at')->useCurrent();
                $table->unsignedBigInteger('best_answer_id')->nullable();
                $table->unsignedBigInteger('last_reply_by_id')->nullable();
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 8. discussion_replies
        if (!Schema::hasTable('discussion_replies')) {
            Schema::create('discussion_replies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('discussion_id');
                $table->unsignedBigInteger('user_id');
                $table->longText('body');
                $table->timestamp('deleted_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 9. discussion_files
        if (!Schema::hasTable('discussion_files')) {
            Schema::create('discussion_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('discussion_id')->nullable();
                $table->unsignedBigInteger('discussion_reply_id')->nullable();
                $table->string('filename', 191);
                $table->text('description')->nullable();
                $table->string('google_url', 191)->nullable();
                $table->string('hashname', 191)->nullable();
                $table->string('size', 191)->nullable();
                $table->string('dropbox_link', 191)->nullable();
                $table->string('external_link_name', 191)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 10. employee_activity
        if (!Schema::hasTable('employee_activity')) {
            Schema::create('employee_activity', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('emp_id');
                $table->string('employee_activity', 191);
                $table->unsignedBigInteger('leave_id')->nullable();
                $table->unsignedBigInteger('task_id')->nullable();
                $table->unsignedBigInteger('proj_id')->nullable();
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->unsignedBigInteger('proposal_id')->nullable();
                $table->unsignedBigInteger('estimate_id')->nullable();
                $table->unsignedBigInteger('deal_id')->nullable();
                $table->unsignedBigInteger('deal_followup_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('expenses_id')->nullable();
                $table->unsignedBigInteger('timelog_id')->nullable();
                $table->unsignedBigInteger('event_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('credit_note_id')->nullable();
                $table->unsignedBigInteger('payment_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('contract_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 11. employee_leave_quota_histories
        if (!Schema::hasTable('employee_leave_quota_histories')) {
            Schema::create('employee_leave_quota_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('leave_type_id');
                $table->double('no_of_leaves');
                $table->double('leaves_used')->default(0);
                $table->double('leaves_remaining')->default(0);
                $table->date('for_month');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 12. employee_leave_quotas
        if (!Schema::hasTable('employee_leave_quotas')) {
            Schema::create('employee_leave_quotas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('leave_type_id');
                $table->double('no_of_leaves');
                $table->double('leaves_used')->default(0);
                $table->double('leaves_remaining')->default(0);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->text('carry_forward_status')->nullable();
                $table->tinyInteger('leave_type_impact')->default(0);
            });
        }

        // 13. expenses_category
        if (!Schema::hasTable('expenses_category')) {
            Schema::create('expenses_category', function (Blueprint $table) {
                $table->id();
                $table->string('category_name', 191);
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 14. project_category
        if (!Schema::hasTable('project_category')) {
            Schema::create('project_category', function (Blueprint $table) {
                $table->id();
                $table->string('category_name', 191);
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 15. project_activity
        if (!Schema::hasTable('project_activity')) {
            Schema::create('project_activity', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id');
                $table->text('activity');
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent();
            });
        }

        // 16. project_notes
        if (!Schema::hasTable('project_notes')) {
            Schema::create('project_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->nullable();
                $table->string('title', 191);
                $table->tinyInteger('type')->default(0);
                $table->integer('employee_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->tinyInteger('is_client_show')->default(0);
                $table->tinyInteger('ask_password')->default(0);
                $table->longText('details');
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 17. sub_tasks
        if (!Schema::hasTable('sub_tasks')) {
            Schema::create('sub_tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->text('title');
                $table->dateTime('due_date')->nullable();
                $table->date('start_date')->nullable();
                $table->enum('status', ['incomplete', 'complete'])->default('incomplete');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->text('description')->nullable();
                $table->string('files')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 18. sub_task_files
        if (!Schema::hasTable('sub_task_files')) {
            Schema::create('sub_task_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('sub_task_id');
                $table->string('filename', 191);
                $table->text('description')->nullable();
                $table->string('google_url', 191)->nullable();
                $table->string('hashname', 191)->nullable();
                $table->string('size', 191)->nullable();
                $table->string('dropbox_link', 191)->nullable();
                $table->string('external_link', 191)->nullable();
                $table->string('external_link_name', 191)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 19. task_category
        if (!Schema::hasTable('task_category')) {
            Schema::create('task_category', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('category_name', 191);
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 20. task_comments
        if (!Schema::hasTable('task_comments')) {
            Schema::create('task_comments', function (Blueprint $table) {
                $table->id();
                $table->longText('comment');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 21. task_history
        if (!Schema::hasTable('task_history')) {
            Schema::create('task_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('sub_task_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->text('details');
                $table->unsignedBigInteger('board_column_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 22. task_label_list
        if (!Schema::hasTable('task_label_list')) {
            Schema::create('task_label_list', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->nullable();
                $table->string('label_name', 191);
                $table->string('color', 191)->nullable();
                $table->string('description', 191)->nullable();
                $table->integer('task_id')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 23. task_notes
        if (!Schema::hasTable('task_notes')) {
            Schema::create('task_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->integer('user_id')->nullable();
                $table->longText('note')->nullable();
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 24. ticket_groups
        if (!Schema::hasTable('ticket_groups')) {
            Schema::create('ticket_groups', function (Blueprint $table) {
                $table->id();
                $table->string('group_name')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();
            });
        }

        // 25. ticket_activities
        if (!Schema::hasTable('ticket_activities')) {
            Schema::create('ticket_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->integer('project_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->unsignedBigInteger('channel_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('type_id')->nullable();
                $table->string('status', 191)->nullable()->default('open');
                $table->string('priority', 191)->default('medium');
                $table->string('type', 191)->default('create');
                $table->longText('content')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 26. user_activities
        if (!Schema::hasTable('user_activities')) {
            Schema::create('user_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->text('activity');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('ticket_activities');
        Schema::dropIfExists('ticket_groups');
        Schema::dropIfExists('task_notes');
        Schema::dropIfExists('task_label_list');
        Schema::dropIfExists('task_history');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_category');
        Schema::dropIfExists('sub_task_files');
        Schema::dropIfExists('sub_tasks');
        Schema::dropIfExists('project_notes');
        Schema::dropIfExists('project_activity');
        Schema::dropIfExists('project_category');
        Schema::dropIfExists('expenses_category');
        Schema::dropIfExists('employee_leave_quotas');
        Schema::dropIfExists('employee_leave_quota_histories');
        Schema::dropIfExists('employee_activity');
        Schema::dropIfExists('discussion_files');
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('discussions');
        Schema::dropIfExists('discussion_categories');
        Schema::dropIfExists('company_addresses');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('appreciations');
    }
};
