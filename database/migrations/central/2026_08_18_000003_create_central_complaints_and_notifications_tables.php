<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations on central database.
     */
    public function up(): void
    {
        Schema::connection('central')->create('company_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id', 50)->unique();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('raised_by_id')->nullable();
            $table->string('raised_by_type', 50)->default('company_admin');
            $table->string('raised_by_name', 150);
            $table->string('raised_by_email', 150);
            $table->string('subject', 255);
            $table->string('category', 100)->default('Technical Issue');
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->enum('status', ['OPEN', 'IN PROGRESS', 'WAITING FOR COMPANY', 'RESOLVED', 'CLOSED', 'REOPENED'])->default('OPEN');
            $table->string('related_module', 100)->nullable();
            $table->string('related_record_id', 100)->nullable();
            $table->longText('description');
            $table->unsignedBigInteger('assigned_super_admin_id')->nullable();
            $table->string('assigned_to_name', 150)->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('central')->create('complaint_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->enum('sender_type', ['super_admin', 'company_admin'])->default('company_admin');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name', 150);
            $table->string('sender_email', 150)->nullable();
            $table->longText('message');
            $table->timestamps();

            $table->foreign('complaint_id')->references('id')->on('company_complaints')->onDelete('cascade');
        });

        Schema::connection('central')->create('complaint_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('original_name', 255);
            $table->string('file_path', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->string('uploaded_by_type', 50)->default('company_admin');
            $table->timestamps();

            $table->foreign('complaint_id')->references('id')->on('company_complaints')->onDelete('cascade');
            $table->foreign('conversation_id')->references('id')->on('complaint_conversations')->onDelete('cascade');
        });

        Schema::connection('central')->create('complaint_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->string('actor_type', 50)->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name', 150);
            $table->string('action', 100);
            $table->string('previous_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('complaint_id')->references('id')->on('company_complaints')->onDelete('cascade');
        });

        Schema::connection('central')->create('central_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id', 50)->unique();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('type', 100);
            $table->string('title', 255);
            $table->text('message');
            $table->enum('severity', ['INFO', 'SUCCESS', 'WARNING', 'ERROR', 'CRITICAL'])->default('INFO');
            $table->string('related_module', 100)->nullable();
            $table->string('related_record_id', 100)->nullable();
            $table->string('action_url', 255)->nullable();
            $table->enum('target_audience', ['super_admin', 'company_admin', 'all'])->default('all');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('central_notifications');
        Schema::connection('central')->dropIfExists('complaint_activities');
        Schema::connection('central')->dropIfExists('complaint_attachments');
        Schema::connection('central')->dropIfExists('complaint_conversations');
        Schema::connection('central')->dropIfExists('company_complaints');
    }
};
