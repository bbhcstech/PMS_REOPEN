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
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'project_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('project_type', 50)->default('client')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'project_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('project_type');
            });
        }
    }
};
