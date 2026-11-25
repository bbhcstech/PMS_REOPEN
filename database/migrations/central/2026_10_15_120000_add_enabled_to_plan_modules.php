<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'central';

    public function up(): void
    {
        $schema = Schema::connection('central');
        if ($schema->hasTable('plan_modules') && !$schema->hasColumn('plan_modules', 'is_enabled')) {
            $schema->table('plan_modules', fn (Blueprint $table) => $table->boolean('is_enabled')->default(true));
        }
    }

    public function down(): void
    {
        // Keep explicit denials: dropping this column would silently grant disabled features.
    }
};
