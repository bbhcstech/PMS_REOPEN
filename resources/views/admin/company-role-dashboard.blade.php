@extends('admin.layout.app')
@section('content')
<style>
.company-role-dashboard { padding:24px; color:var(--text-main, var(--bs-body-color)); }
.company-role-dashboard .role-panel { background:var(--bg-surface, var(--bs-body-bg)); border:1px solid var(--border-color, #d8deeb); border-radius:18px; padding:24px; margin-bottom:24px; }
.company-role-dashboard .role-metrics { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; }
.company-role-dashboard .role-metric { padding:20px; border:1px solid var(--border-color,#d8deeb); border-radius:14px; }
.company-role-dashboard .role-metric strong { display:block; font-size:30px; color:var(--brand-primary,#2f6bff); }
.company-role-dashboard .role-tables { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:24px; }
.company-role-dashboard table { width:100%; border-collapse:collapse; }
.company-role-dashboard th, .company-role-dashboard td { text-align:left; padding:12px; border-bottom:1px solid var(--border-color,#d8deeb); }
.company-role-dashboard .role-table-scroll { overflow-x:auto; }
.company-role-dashboard .role-tables > .role-panel { min-width:0; }
.company-role-dashboard :is(#assignedTasksTable, #assignedProjectsTable) { --pms-table-content-min-width:0px!important; }
.company-role-dashboard [data-pms-selection-column] { width:48px; min-width:48px; text-align:center; }
.company-role-dashboard [data-pms-selection-column] input { position:static!important; width:18px; height:18px; margin:0!important; cursor:pointer; }
.company-role-dashboard [data-pms-selection-column] input:disabled { cursor:not-allowed; opacity:.4; }
.company-role-dashboard tbody td:not([data-pms-selection-column]) { overflow-wrap:anywhere; }
@media (max-width:575px) { .company-role-dashboard .role-tables { grid-template-columns:minmax(0,1fr); } .company-role-dashboard { padding:12px; } }
</style>
@php($allows = fn (string $slug) => auth()->user()->moduleToggleAllows($slug))
<div class="company-role-dashboard">
    <div class="role-panel"><h2>{{ $role->name }} Dashboard</h2><p class="mb-0">{{ $company->name }} &middot; Welcome, {{ auth()->user()->name }}</p></div>
    @include('admin.partials.staff-clock')
    <div class="role-panel role-metrics">
        @if($allows('tasks'))<div class="role-metric"><span>My assigned tasks</span><strong>{{ $taskCount ?? '—' }}</strong></div>@endif
        @if($allows('projects'))<div class="role-metric"><span>My projects</span><strong>{{ $projectCount ?? '—' }}</strong></div>@endif
        @if($allows('attendance'))<div class="role-metric"><span>My attendance records this month</span><strong>{{ $attendanceDays ?? '—' }}</strong></div>@endif
        @if($allows('leaves'))<div class="role-metric"><span>My leave records</span><strong>{{ $leaveCount ?? '—' }}</strong></div>@endif
        @if($hr)
        @if($allows('employees'))<div class="role-metric"><span>Company staff</span><strong>{{ $hr['staff'] }}</strong></div>@endif
        @if($allows('leaves'))<div class="role-metric"><span>Company pending leaves</span><strong>{{ $hr['pending_leaves'] ?? '—' }}</strong></div>@endif
        @endif
        @if($manager && $allows('tasks') && $allows('projects'))
        <div class="role-metric"><span>Tasks across my projects</span><strong>{{ $manager['project_tasks'] }}</strong></div>
        @endif
    </div>
    <div class="role-tables">
        @if($allows('tasks'))
        <div class="role-panel"><h4>My assigned tasks</h4><div class="role-table-scroll"><table id="assignedTasksTable"><thead><tr><th data-pms-selection-column><input type="checkbox" class="form-check-input" aria-label="Select all assigned tasks" @disabled($tasks->isEmpty())></th><th>Task</th><th>Status</th><th>Due date</th></tr></thead><tbody>
            @forelse($tasks as $task)<tr data-task-id="{{ $task->id }}"><td data-pms-selection-column><input type="checkbox" class="form-check-input" aria-label="Select task {{ $task->title ?? 'Untitled task' }}" value="{{ $task->id }}"></td><td>{{ $task->title ?? 'Untitled task' }}</td><td>{{ $task->status ?? '—' }}</td><td>{{ $task->due_date ?? 'No due date' }}</td></tr>
            @empty<tr><td colspan="4">No assigned tasks to display.</td></tr>@endforelse
        </tbody></table></div></div>
        @endif
        @if($allows('projects'))
        <div class="role-panel"><h4>My projects</h4><div class="role-table-scroll"><table id="assignedProjectsTable"><thead><tr><th data-pms-selection-column><input type="checkbox" class="form-check-input" aria-label="Select all assigned projects" @disabled($projects->isEmpty())></th><th>Project</th><th>Status</th><th>Deadline</th></tr></thead><tbody>
            @forelse($projects as $project)<tr data-record-id="{{ $project->id }}"><td data-pms-selection-column><input type="checkbox" class="form-check-input" aria-label="Select project {{ $project->name ?? $project->project_name ?? 'Untitled project' }}" value="{{ $project->id }}"></td><td>{{ $project->name ?? $project->project_name ?? 'Untitled project' }}</td><td>{{ $project->status ?? '—' }}</td><td>{{ $project->deadline ?? 'No deadline' }}</td></tr>
            @empty<tr><td colspan="4">No assigned projects to display.</td></tr>@endforelse
        </tbody></table></div></div>
        @endif
    </div>
</div>
@endsection
