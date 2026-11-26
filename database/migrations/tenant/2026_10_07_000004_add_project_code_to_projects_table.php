<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'project_code')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('project_code', 100)->nullable();
            });
        }
    }

    public function down(): void
    {
        // This repairs legacy schemas. Preserve codes on rollback, including
        // columns that already existed before this migration was applied.
    }
};
