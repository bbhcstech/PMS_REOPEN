<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Services\TaskPrioritySchema::ensure();
    }

    public function down(): void
    {
        // Preserve existing task priorities when rolling back this repair.
    }
};
