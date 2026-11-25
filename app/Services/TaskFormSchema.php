<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Schema\Blueprint;

class TaskFormSchema
{
    public static function ensure(): void
    {
        $schema = (new Task)->getConnection()->getSchemaBuilder();
        if (! $schema->hasTable('tasks')) return;

        $columns = [
            'milestone_id' => 'unsignedBigInteger',
            'board_column_id' => 'unsignedBigInteger',
            'dependent_task_id' => 'unsignedBigInteger',
            'category_id' => 'unsignedBigInteger',
            'start_date' => 'dateTime',
            'due_date' => 'dateTime',
            'is_private' => 'boolean',
            'billable' => 'boolean',
            'estimate_hours' => 'unsignedInteger',
            'estimate_minutes' => 'unsignedInteger',
            'repeat' => 'boolean',
            'repeat_complete' => 'boolean',
            'repeat_count' => 'unsignedInteger',
            'repeat_type' => 'string',
            'repeat_cycles' => 'unsignedInteger',
            'image_url' => 'string',
        ];
        $existing = $schema->getColumnListing('tasks');
        foreach ($columns as $name => $type) {
            if (in_array($name, $existing, true)) continue;
            try {
                $schema->table('tasks', function (Blueprint $table) use ($name, $type) {
                    $table->{$type}($name)->nullable();
                });
            } catch (\Throwable $exception) {
                // A concurrent request may have completed the same repair.
                if (! $schema->hasColumn('tasks', $name)) throw $exception;
            }
        }
    }
}
