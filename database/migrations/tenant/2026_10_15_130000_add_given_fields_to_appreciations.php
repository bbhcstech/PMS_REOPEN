<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        if (!$schema->hasTable('appreciations')) return;
        if (!$schema->hasColumn('appreciations', 'given_to')) {
            $schema->table('appreciations', fn (Blueprint $table) => $table->string('given_to')->nullable());
        }
        if (!$schema->hasColumn('appreciations', 'given_on')) {
            $schema->table('appreciations', fn (Blueprint $table) => $table->date('given_on')->nullable());
        }
    }

    public function down(): void
    {
        // Preserve entered appreciation metadata on rollback.
    }
};
