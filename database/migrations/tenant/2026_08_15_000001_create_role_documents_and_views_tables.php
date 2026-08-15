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
        // HR Documents table
        if (!Schema::hasTable('hr_documents')) {
            Schema::create('hr_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('document_type');
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('file_type')->nullable();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'document_type']);
            });
        }

        // Manager Documents table
        if (!Schema::hasTable('manager_documents')) {
            Schema::create('manager_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('document_type');
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('file_type')->nullable();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'document_type']);
            });
        }

        // Admin Documents table
        if (!Schema::hasTable('admin_documents')) {
            Schema::create('admin_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('document_type');
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('file_type')->nullable();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'document_type']);
            });
        }

        // Document Views Audit table
        if (!Schema::hasTable('document_views')) {
            Schema::create('document_views', function (Blueprint $table) {
                $table->id();
                $table->string('document_table'); // employee_documents, hr_documents, manager_documents, admin_documents
                $table->unsignedBigInteger('document_id');
                $table->unsignedBigInteger('viewed_by_user_id');
                $table->timestamp('viewed_at')->useCurrent();
                $table->timestamps();

                $table->foreign('viewed_by_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['document_table', 'document_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_views');
        Schema::dropIfExists('admin_documents');
        Schema::dropIfExists('manager_documents');
        Schema::dropIfExists('hr_documents');
    }
};
