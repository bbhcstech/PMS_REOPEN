<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        if (!$schema->hasTable('attendances')) return;
        foreach (['clock_in_timezone', 'auto_clocked_out'] as $column) {
            if ($schema->hasColumn('attendances', $column)) continue;
            $schema->table('attendances', function (Blueprint $table) use ($column) {
                if ($column === 'clock_in_timezone') $table->string($column, 64)->nullable();
                else $table->boolean($column)->default(false);
            });
        }
    }
    public function down(): void
    {
        foreach (['clock_in_timezone', 'auto_clocked_out'] as $column) {
            if (Schema::connection('tenant')->hasColumn('attendances', $column)) Schema::connection('tenant')->table('attendances', fn (Blueprint $table) => $table->dropColumn($column));
        }
    }
};
