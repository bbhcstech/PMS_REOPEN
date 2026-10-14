<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Services\TaskFormSchema::ensure();
    }

    public function down(): void
    {
        // Preserve existing task data when rolling back this repair.
    }
};
