<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        if (!$schema->hasColumn('projects', 'enable_miroboard')) {
            $schema->table('projects', function (Blueprint $table) {
                $table->boolean('enable_miroboard')->default(false);
            });
        }
    }

    public function down(): void
    {
        // Preserve saved project settings on rollback.
    }
};
