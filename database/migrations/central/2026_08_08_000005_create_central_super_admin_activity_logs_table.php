<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration.
 * super_admin_activity_logs: audit trail for every super-admin action.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('super_admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('super_admin_id')
                  ->nullable()
                  ->constrained('super_admins')
                  ->nullOnDelete();
            $table->foreignId('company_id')
                  ->nullable()
                  ->constrained('companies')
                  ->nullOnDelete();
            $table->string('action')->comment('e.g. company.created, plan.updated, subscription.suspended');
            $table->text('description')->nullable();
            $table->json('meta')->nullable()->comment('Extra context: old/new values, affected IDs, etc.');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('super_admin_id');
            $table->index('company_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('super_admin_activity_logs');
    }
};
