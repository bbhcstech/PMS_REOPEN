<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('tenant');
        if ($schema->hasTable('attendances') && !$schema->hasColumn('attendances', 'clock_out_photo')) {
            $schema->table('attendances', fn (Blueprint $table) => $table->string('clock_out_photo')->nullable());
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('tenant');
        if ($schema->hasTable('attendances') && $schema->hasColumn('attendances', 'clock_out_photo')) {
            $schema->table('attendances', fn (Blueprint $table) => $table->dropColumn('clock_out_photo'));
        }
    }
};
