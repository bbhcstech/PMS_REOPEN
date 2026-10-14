<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeDashboardCharts
{
    public function forUser(User $user): array
    {
        $charts = [];
        $schema = Schema::connection('tenant');
        foreach (['tasks', 'projects', 'tickets'] as $table) {
            if (!$schema->hasTable($table)) { $charts[$table] = []; continue; }
            $query = DB::connection('tenant')->table($table);
            if ($schema->hasColumn($table, 'company_id')) {
                $query->where(fn ($q) => $q->where('company_id', $user->company_id)->orWhereNull('company_id'));
            }
            foreach (['deleted_at', 'archived_at'] as $column) {
                if ($schema->hasColumn($table, $column)) $query->whereNull($column);
            }
            if ($table === 'tasks') {
                $query->where(function ($q) use ($user, $schema) {
                    $q->whereRaw('1 = 0');
                    if ($schema->hasTable('assigned_task_user')) {
                        $q->orWhereExists(fn ($pivot) => $pivot->selectRaw('1')->from('assigned_task_user')->whereColumn('assigned_task_user.task_id', 'tasks.id')->where('assigned_task_user.user_id', $user->id));
                    }
                    if ($schema->hasColumn('tasks', 'assigned_to')) {
                        $id = (string) (int) $user->id;
                        $q->orWhere('assigned_to', $id)->orWhere('assigned_to', 'like', $id . ',%')->orWhere('assigned_to', 'like', '%,' . $id)->orWhere('assigned_to', 'like', '%,' . $id . ',%');
                    }
                });
            } elseif ($table === 'projects') {
                if (!$schema->hasTable('project_user')) { $charts[$table] = []; continue; }
                $query->whereExists(fn ($pivot) => $pivot->selectRaw('1')->from('project_user')->whereColumn('project_user.project_id', 'projects.id')->where('project_user.user_id', $user->id));
            } else {
                $query->where(fn ($q) => $q->where('agent_id', $user->id)->orWhere('requester_id', $user->id));
            }
            $counts = [];
            foreach ($query->pluck('status') as $status) {
                $label = ucwords(str_replace(['_', '-'], ' ', strtolower(trim((string) $status)))) ?: 'Unspecified';
                $counts[$label] = ($counts[$label] ?? 0) + 1;
            }
            ksort($counts);
            $charts[$table] = $counts;
        }
        return $charts;
    }
}
