<?php
namespace App\Services;

use Illuminate\Support\Facades\{DB, Schema};

/**
 * Super Admin work is stored in the platform (primary) database. A developer who signs in through a
 * company workspace reads tasks from that company's database, so every platform task assigned to the
 * developer (matched by email) is mirrored into the workspace they use, linked by platform_task_id.
 */
class DeveloperTaskSync
{
    private const MIRRORED = ['title', 'description', 'additional_instructions', 'attachments', 'priority',
        'start_date', 'due_date', 'estimate_hours', 'status', 'progress', 'completed_on', 'is_completed', 'deleted_at'];
    private static array $done = [];

    public static function pull($dev): void
    {
        if (! $dev || empty($dev->id)) return;
        try {
            $platformDb = DB::connection('session_db')->getDatabaseName();
            $workspaceDb = DB::connection()->getDatabaseName();
            $key = $workspaceDb . ':' . $dev->id;
            if (! $platformDb || $platformDb === $workspaceDb || isset(self::$done[$key])) return;
            self::$done[$key] = true;

            $platform = DB::connection('session_db');
            $platformSchema = Schema::connection('session_db');
            if (! Schema::hasTable('tasks') || ! $platformSchema->hasTable('tasks') || ! $platformSchema->hasTable('users')) return;

            $emails = array_values(array_unique(array_filter([
                strtolower(trim((string) ($dev->email ?? ''))),
                strtolower(trim((string) ($dev->personal_email ?? ''))),
            ])));
            if (! $emails) return;
            $platformDevIds = $platform->table('users')->where(function ($q) use ($emails, $platformSchema) {
                $q->whereIn(DB::raw('LOWER(email)'), $emails);
                if ($platformSchema->hasColumn('users', 'personal_email')) $q->orWhereIn(DB::raw('LOWER(personal_email)'), $emails);
            })->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! $platformDevIds) return;

            $query = $platform->table('tasks')->where(function ($q) use ($platformDevIds, $platformSchema) {
                $q->whereIn('tasks.assigned_to', $platformDevIds);
                foreach ($platformDevIds as $id) $q->orWhereRaw('FIND_IN_SET(?, tasks.assigned_to)', [(string) $id]);
                if ($platformSchema->hasTable('assigned_task_user')) {
                    $q->orWhereExists(fn ($sub) => $sub->select(DB::raw(1))->from('assigned_task_user')
                        ->whereColumn('assigned_task_user.task_id', 'tasks.id')->whereIn('assigned_task_user.user_id', $platformDevIds));
                }
            });
            $platformTasks = $query->get();
            if ($platformTasks->isEmpty()) return;

            DeveloperTaskSchema::ensure();
            $columns = array_flip(Schema::getColumnListing('tasks'));
            $existing = DB::table('tasks')->whereIn('platform_task_id', $platformTasks->pluck('id')->all())
                ->get()->keyBy('platform_task_id');
            $companyId = session('current_company_id') && Schema::hasTable('companies')
                ? DB::table('companies')->where('id', session('current_company_id'))->value('id') : null;
            $hasPivot = Schema::hasTable('assigned_task_user');

            foreach ($platformTasks as $source) {
                $source = (array) $source;
                $copy = $existing->get($source['id']);
                if ($copy) {
                    // Keep the workspace copy current with Super Admin changes (status, deadline, deletion...).
                    $changes = [];
                    foreach (self::MIRRORED as $column) {
                        if (isset($columns[$column]) && array_key_exists($column, $source) && (string) ($copy->$column ?? '') !== (string) ($source[$column] ?? '')) {
                            $changes[$column] = $source[$column];
                        }
                    }
                    if ($changes) DB::table('tasks')->where('id', $copy->id)->update($changes + (isset($columns['updated_at']) ? ['updated_at' => $source['updated_at'] ?? now()] : []));
                    continue;
                }
                if (! empty($source['deleted_at']) && $source['deleted_at'] !== '0000-00-00 00:00:00') continue;

                $attributes = $source;
                unset($attributes['id']);
                $attributes['platform_task_id'] = $source['id'];
                $attributes['assigned_to'] = $dev->id;
                $attributes['created_by'] = $dev->id;
                $attributes['company_id'] = $companyId;
                $attributes['project_id'] = null;
                if (! empty($source['project_id']) && Schema::hasTable('projects') && $platformSchema->hasTable('projects')) {
                    $projectName = $platform->table('projects')->where('id', $source['project_id'])->value('name');
                    $attributes['project_id'] = $projectName ? DB::table('projects')->where('name', $projectName)->value('id') : null;
                }
                if (! $attributes['project_id'] && Schema::hasTable('projects')) {
                    // tasks.project_id is required on most installs: use the workspace's platform project.
                    $attributes['project_id'] = self::workspaceProjectId($companyId);
                }
                $attributes = array_intersect_key($attributes, $columns);
                try {
                    DB::transaction(function () use ($attributes, $dev, $hasPivot) {
                        $taskId = DB::table('tasks')->insertGetId($attributes);
                        if ($hasPivot) {
                            DB::table('assigned_task_user')->insertOrIgnore([
                                'task_id' => $taskId, 'user_id' => $dev->id, 'assigned_by' => $dev->id,
                                'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                            ]);
                        }
                    });
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function workspaceProjectId($companyId): ?int
    {
        $id = DB::table('projects')->where('name', 'Internal Platform Project')->value('id');
        if ($id) return (int) $id;
        try {
            $columns = array_flip(Schema::getColumnListing('projects'));
            return (int) DB::table('projects')->insertGetId(array_intersect_key([
                'company_id' => $companyId, 'name' => 'Internal Platform Project',
                'created_at' => now(), 'updated_at' => now(),
            ], $columns)) ?: null;
        } catch (\Throwable $e) {
            $id = DB::table('projects')->value('id');
            return $id ? (int) $id : null;
        }
    }

    /** A developer's change to a workspace copy is written back to the platform task the Super Admin sees. */
    public static function push(object $task, array $changes): void
    {
        if (empty($task->platform_task_id)) return;
        try {
            $platform = DB::connection('session_db');
            if ($platform->getDatabaseName() === DB::connection()->getDatabaseName()) return;
            $columns = array_flip(Schema::connection('session_db')->getColumnListing('tasks'));
            $changes = array_intersect_key($changes, $columns);
            if ($changes) $platform->table('tasks')->where('id', $task->platform_task_id)->update($changes);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
