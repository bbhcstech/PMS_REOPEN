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
        // 1. Community Messages Table
        if (!Schema::hasTable('community_messages')) {
            Schema::create('community_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index(); // Quoted / Replied Message
                $table->text('message')->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_type')->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->boolean('is_pinned')->default(false)->index();
                $table->unsignedBigInteger('pinned_by')->nullable();
                $table->timestamp('pinned_at')->nullable();
                $table->timestamp('edited_at')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('parent_id')->references('id')->on('community_messages')->onDelete('set null');
            });
        }

        // 2. Community Reactions Table
        if (!Schema::hasTable('community_reactions')) {
            Schema::create('community_reactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('message_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('emoji', 32);
                $table->timestamps();

                $table->unique(['message_id', 'user_id', 'emoji'], 'community_reactions_unique');
                $table->foreign('message_id')->references('id')->on('community_messages')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Community User State (Last Read Tracking)
        if (!Schema::hasTable('community_user_states')) {
            Schema::create('community_user_states', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('last_read_message_id')->nullable();
                $table->timestamp('last_read_at')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'user_id'], 'community_user_states_unique');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_user_states');
        Schema::dropIfExists('community_reactions');
        Schema::dropIfExists('community_messages');
    }
};
