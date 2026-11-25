<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        if (!$schema->hasTable('appraisals')) return;
        if (!$schema->hasColumn('appraisals', 'policy_snapshot')) $schema->table('appraisals', fn (Blueprint $table) => $table->json('policy_snapshot')->nullable());
        if (!$schema->hasColumn('appraisals', 'recommended_increment_percent')) $schema->table('appraisals', fn (Blueprint $table) => $table->decimal('recommended_increment_percent', 5, 2)->nullable());
    }
    public function down(): void
    {
        // Preserve policy snapshots and compensation recommendations used in reviews.
    }
};
