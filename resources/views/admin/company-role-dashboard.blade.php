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
</style>
<div class="company-role-dashboard">
    <div class="role-panel"><h2>{{ $role->name }} Dashboard</h2><p class="mb-0">{{ $company->name }} &middot; Welcome, {{ auth()->user()->name }}</p></div>
    @include('admin.partials.staff-clock')
    <div class="role-panel role-metrics">
        <div class="role-metric"><span>My assigned tasks</span><strong>{{ $taskCount ?? '—' }}</strong></div>
        <div class="role-metric"><span>My projects</span><strong>{{ $projectCount ?? '—' }}</strong></div>
        <div class="role-metric"><span>My attendance records this month</span><strong>{{ $attendanceDays ?? '—' }}</strong></div>
        <div class="role-metric"><span>My leave records</span><strong>{{ $leaveCount ?? '—' }}</strong></div>
        @if($hr)
        <div class="role-metric"><span>Company staff</span><strong>{{ $hr['staff'] }}</strong></div>
        <div class="role-metric"><span>Company pending leaves</span><strong>{{ $hr['pending_leaves'] ?? '—' }}</strong></div>
        @endif
        @if($manager)
        <div class="role-metric"><span>Tasks across my projects</span><strong>{{ $manager['project_tasks'] }}</strong></div>
        @endif
    </div>
    <div class="role-tables">
        <div class="role-panel"><h4>My assigned tasks</h4><div class="role-table-scroll"><table><thead><tr><th>Task</th><th>Status</th><th>Due date</th></tr></thead><tbody>
            @forelse($tasks as $task)<tr data-task-id="{{ $task->id }}"><td>{{ $task->title ?? 'Untitled task' }}</td><td>{{ $task->status ?? '—' }}</td><td>{{ $task->due_date ?? 'No due date' }}</td></tr>
            @empty<tr><td colspan="3">No assigned tasks to display.</td></tr>@endforelse
        </tbody></table></div></div>
        <div class="role-panel"><h4>My projects</h4><div class="role-table-scroll"><table><thead><tr><th>Project</th><th>Status</th><th>Deadline</th></tr></thead><tbody>
            @forelse($projects as $project)<tr data-record-id="{{ $project->id }}"><td>{{ $project->name ?? $project->project_name ?? 'Untitled project' }}</td><td>{{ $project->status ?? '—' }}</td><td>{{ $project->deadline ?? 'No deadline' }}</td></tr>
            @empty<tr><td colspan="3">No assigned projects to display.</td></tr>@endforelse
        </tbody></table></div></div>
    </div>
</div>
@endsection
