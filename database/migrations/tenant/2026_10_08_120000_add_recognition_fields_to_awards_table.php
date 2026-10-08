<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        foreach (['appreciation_id', 'photo', 'status'] as $column) {
            if (!$schema->hasColumn('awards', $column)) {
                $schema->table('awards', function (Blueprint $table) use ($column) {
                    if ($column === 'appreciation_id') {
                        $table->unsignedBigInteger($column)->nullable()->index();
                    } elseif ($column === 'status') {
                        $table->string($column)->default('active');
                    } else {
                        $table->string($column)->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // Preserve recognition links and uploaded photo paths on rollback.
    }
};
