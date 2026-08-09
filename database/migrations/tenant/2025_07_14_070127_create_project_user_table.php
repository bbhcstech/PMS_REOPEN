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
        if (! Schema::hasTable('project_user')) {
            Schema::create('project_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->string('role')->default('Project Member');
                $table->timestamps();
            });
        } else {
            Schema::table('project_user', function (Blueprint $table) {
                if (! Schema::hasColumn('project_user', 'hourly_rate')) {
                    $table->decimal('hourly_rate', 10, 2)->default(0);
                }
                if (! Schema::hasColumn('project_user', 'role')) {
                    $table->string('role')->default('Project Member');
                }
                if (! Schema::hasColumn('project_user', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
