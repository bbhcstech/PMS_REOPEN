<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Schema\Blueprint;

class TaskLabelsSchema
{
    public static function ensure(): void
    {
        $schema = (new Task())->getConnection()->getSchemaBuilder();
        if (! $schema->hasTable('tasks') || $schema->hasColumn('tasks', 'task_labels')) {
            return;
        }

        try {
            $schema->table('tasks', function (Blueprint $table) {
                $table->text('task_labels')->nullable();
            });
        } catch (\Throwable $exception) {
            if (! $schema->hasColumn('tasks', 'task_labels')) {
                throw $exception;
            }
        }
    }
}
