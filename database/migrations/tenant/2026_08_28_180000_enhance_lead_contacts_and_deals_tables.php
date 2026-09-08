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
        // 1. Enhance lead_contacts table
        Schema::table('lead_contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_contacts', 'priority')) {
                $table->string('priority', 20)->default('medium')->after('status');
            }
            if (!Schema::hasColumn('lead_contacts', 'job_title')) {
                $table->string('job_title')->nullable()->after('contact_name');
            }
            if (!Schema::hasColumn('lead_contacts', 'alternate_phone')) {
                $table->string('alternate_phone', 30)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('lead_contacts', 'whatsapp')) {
                $table->string('whatsapp', 30)->nullable()->after('alternate_phone');
            }
            if (!Schema::hasColumn('lead_contacts', 'expected_value')) {
                $table->decimal('expected_value', 15, 2)->nullable()->after('lead_score');
            }
            if (!Schema::hasColumn('lead_contacts', 'expected_closing_date')) {
                $table->date('expected_closing_date')->nullable()->after('expected_value');
            }
            if (!Schema::hasColumn('lead_contacts', 'last_contacted_at')) {
                $table->timestamp('last_contacted_at')->nullable()->after('expected_closing_date');
            }
            if (!Schema::hasColumn('lead_contacts', 'next_follow_up')) {
                $table->date('next_follow_up')->nullable()->after('last_contacted_at');
            }
            if (!Schema::hasColumn('lead_contacts', 'lead_owner_designation')) {
                $table->string('lead_owner_designation')->nullable()->after('lead_owner_id');
            }
            if (!Schema::hasColumn('lead_contacts', 'added_by_designation')) {
                $table->string('added_by_designation')->nullable()->after('added_by');
            }
            if (!Schema::hasColumn('lead_contacts', 'converted_at')) {
                $table->timestamp('converted_at')->nullable();
            }
            if (!Schema::hasColumn('lead_contacts', 'converted_by')) {
                $table->unsignedBigInteger('converted_by')->nullable();
            }
        });

        // 2. Enhance deals table
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'lead_id')) {
                $table->unsignedBigInteger('lead_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('deals', 'company_name')) {
                $table->string('company_name')->nullable()->after('lead_name');
            }
            if (!Schema::hasColumn('deals', 'priority')) {
                $table->string('priority', 20)->default('medium')->after('is_active');
            }
            if (!Schema::hasColumn('deals', 'currency')) {
                $table->string('currency', 10)->default('INR')->after('value');
            }
            if (!Schema::hasColumn('deals', 'probability')) {
                $table->integer('probability')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('deals', 'weighted_value')) {
                $table->decimal('weighted_value', 15, 2)->nullable()->after('probability');
            }
            if (!Schema::hasColumn('deals', 'lost_reason')) {
                $table->string('lost_reason')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('deals', 'lost_notes')) {
                $table->text('lost_notes')->nullable()->after('lost_reason');
            }
        });

        // 3. Create crm_activities table
        if (!Schema::hasTable('crm_activities')) {
            Schema::create('crm_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable()->index();
                $table->unsignedBigInteger('deal_id')->nullable()->index();
                $table->string('type', 50); // call, email, meeting, note, follow_up, status_change, priority_change, created, assigned, deal_created, deal_updated, converted
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('activity_date')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 4. Create crm_follow_ups table
        if (!Schema::hasTable('crm_follow_ups')) {
            Schema::create('crm_follow_ups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable()->index();
                $table->unsignedBigInteger('deal_id')->nullable()->index();
                $table->string('follow_up_type', 50)->default('call'); // call, email, meeting, task
                $table->date('date');
                $table->string('time', 20)->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->boolean('reminder')->default(false);
                $table->text('description')->nullable();
                $table->string('status', 20)->default('pending'); // pending, completed, cancelled
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_follow_ups');
        Schema::dropIfExists('crm_activities');

        Schema::table('deals', function (Blueprint $table) {
            $columns = ['lead_id', 'company_name', 'priority', 'currency', 'probability', 'weighted_value', 'lost_reason', 'lost_notes'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('deals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('lead_contacts', function (Blueprint $table) {
            $columns = ['priority', 'job_title', 'alternate_phone', 'whatsapp', 'expected_value', 'expected_closing_date', 'last_contacted_at', 'next_follow_up', 'lead_owner_designation', 'added_by_designation', 'converted_at', 'converted_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('lead_contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
