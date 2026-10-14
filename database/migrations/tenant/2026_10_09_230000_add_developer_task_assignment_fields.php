<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    public function up(): void { \App\Services\DeveloperTaskSchema::ensure(); }
    public function down(): void {
        \Illuminate\Support\Facades\Schema::table('tasks', fn ($table) => $table->dropColumn(['priority', 'estimate_hours', 'deleted_at']));
    }
};
