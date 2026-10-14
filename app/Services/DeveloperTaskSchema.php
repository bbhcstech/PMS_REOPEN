<?php
namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeveloperTaskSchema
{
    public static function ensure(?string $connection = null): void
    {
        $schema = Schema::connection($connection);
        foreach (['priority', 'estimate_hours', 'deleted_at', 'platform_task_id'] as $column) {
            if (!$schema->hasColumn('tasks', $column)) {
                $schema->table('tasks', function (Blueprint $table) use ($column) {
                    match ($column) {
                        'platform_task_id' => $table->unsignedBigInteger('platform_task_id')->nullable()->index(),
                        'priority' => $table->string('priority', 20)->default('medium'),
                        'estimate_hours' => $table->decimal('estimate_hours', 8, 2)->nullable(),
                        'deleted_at' => $table->timestamp('deleted_at')->nullable(),
                    };
                });
            }
        }
    }
}
