<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeveloperAssignmentHistory
{
    public function records(): Collection
    {
        if (!Schema::hasTable('tasks')) return collect();
        $tasks = DB::table('tasks')->orderByDesc('id')->get();
        $users = Schema::hasTable('users') ? DB::table('users')->get()->keyBy('id') : collect();
        $assignments = Schema::hasTable('assigned_task_user')
            ? DB::table('assigned_task_user')->get()->groupBy('task_id') : collect();
        $companies = \App\Models\Central\Company::on('central')->withTrashed()->get()->keyBy('id');
        return $tasks->map(function ($task) use ($users, $assignments, $companies) {
            $pivots = $assignments->get($task->id, collect());
            $ids = $pivots->pluck('user_id')->push($task->assigned_to ?? null)->filter()->unique();
            $developers = $ids->map(fn ($id) => $users->get($id))->filter();
            $task->developer_name = $developers->pluck('name')->filter()->implode(', ');
            $task->developer_email = $developers->pluck('email')->filter()->implode(', ');
            $task->company_name = $companies->get($task->company_id ?? null)?->name;
            $task->assigner_name = $users->get($pivots->first()?->assigned_by ?? $task->created_by ?? null)?->name;
            $task->created_at = $pivots->first()?->assigned_at ?? $task->created_at ?? $task->updated_at ?? null;
            $task->status = $task->status ?? 'assigned';
            $task->title = $task->title ?? $task->heading ?? 'Untitled task';
            return $task;
        })->sortByDesc(fn ($task) => $task->updated_at ?? $task->created_at)->values();
    }
}
