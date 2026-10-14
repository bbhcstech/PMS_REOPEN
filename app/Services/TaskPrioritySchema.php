<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Schema\Blueprint;

class TaskPrioritySchema
{
    public static function ensure(): void
    {
        $schema = (new Task())->getConnection()->getSchemaBuilder();
        if (! $schema->hasTable('tasks') || $schema->hasColumn('tasks', 'priority')) {
            return;
        }

        try {
            $schema->table('tasks', function (Blueprint $table) {
                $table->string('priority', 20)->default('medium');
            });
        } catch (\Throwable $exception) {
            // Another request may have added the column after our initial check.
            if (! $schema->hasColumn('tasks', 'priority')) {
                throw $exception;
            }
        }
    }
}
