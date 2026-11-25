<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        if (!$schema->hasTable('attendances')) return;
        $columns = [
            'clock_in_latitude' => fn (Blueprint $t) => $t->decimal('clock_in_latitude', 10, 8)->nullable(),
            'clock_in_longitude' => fn (Blueprint $t) => $t->decimal('clock_in_longitude', 11, 8)->nullable(),
            'clock_in_address' => fn (Blueprint $t) => $t->string('clock_in_address', 255)->nullable(),
            'clock_in_photo' => fn (Blueprint $t) => $t->string('clock_in_photo')->nullable(),
            'work_from_type' => fn (Blueprint $t) => $t->string('work_from_type', 30)->default('office'),
        ];
        foreach ($columns as $column => $definition) {
            if ($schema->hasColumn('attendances', $column)) continue;
            try {
                $schema->table('attendances', $definition);
            } catch (\Throwable $e) {
                // Another first-use request may have added this same column.
                if (!$schema->hasColumn('attendances', $column)) throw $e;
            }
        }
    }

    public function down(): void
    {
        // Compatibility repair: columns may predate this migration and contain attendance history.
    }
};
