<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Services\TaskLabelsSchema::ensure();
    }

    public function down(): void
    {
        // Preserve labels on existing tasks when rolling back this repair.
    }
};
