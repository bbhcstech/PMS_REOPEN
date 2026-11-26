<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        // Legacy tenant schemas predate the optional project fields on the client form.
        $definitions = [
            'category_id' => 'unsignedBigInteger',
            'department_id' => 'unsignedBigInteger',
            'currency_id' => 'unsignedBigInteger',
            'without_deadline' => 'boolean',
            'project_budget' => 'decimal',
            'hours_allocated' => 'decimal',
            'completion_percent' => 'unsignedTinyInteger',
            'notes' => 'text',
            'public_gantt_chart' => 'string',
            'public_taskboard' => 'string',
            'client_access' => 'boolean',
            'need_approval_by_admin' => 'boolean',
            'calculate_task_progress' => 'string',
            'public' => 'boolean',
            'allow_client_notification' => 'boolean',
            'manual_timelog' => 'boolean',
            'project_file' => 'string',
        ];
        $missing = array_diff_key($definitions, array_flip(Schema::getColumnListing('projects')));
        if ($missing === []) {
            return;
        }
        Schema::table('projects', function (Blueprint $table) use ($missing) {
            foreach ($missing as $name => $type) {
                $column = $type === 'decimal'
                    ? $table->decimal($name, 15, 2)
                    : $table->{$type}($name);
                $column->nullable();
            }
        });
    }

    public function down(): void
    {
        // Preserve project data and columns that may have existed before this repair.
    }
};
