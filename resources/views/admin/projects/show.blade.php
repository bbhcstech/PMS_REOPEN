@extends('admin.layout.app')

@section('content')
@php
    $isAdmin = auth()->user()?->role === 'admin';
    $isEmployee = auth()->user()?->role === 'employee';
    $statusOptions = [
        'pending' => 'Pending',
        'not started' => 'Not Started',
        'in progress' => 'In Progress',
        'on hold' => 'On Hold',
        'completed' => 'Completed',
        'delayed' => 'Delayed',
    ];
    $priorityOptions = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
    $status = $project->status ?: 'pending';
@endphp

<main class="project-detail-page">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="project-detail-hero">
        <div>
            <span class="project-eyebrow"><i class="bi bi-kanban"></i> Assigned Project</span>
            <h1>{{ $project->name }}</h1>
            <p>{{ $project->project_code ?: 'Project details' }}</p>
        </div>
        <a href="{{ route('projects.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to Projects</a>
    </section>

    <nav class="project-detail-tabs">
        <a class="active" href="{{ route('projects.show', $project) }}">Overview</a>
        <a href="{{ route('project-members.index', $project) }}">Members</a>
        <a href="{{ route('projects.tasks.index', $project) }}">Tasks</a>
        <a href="{{ route('projects.tasks.board', $project) }}">Task Board</a>
        <a href="{{ route('projects.gantt', $project) }}">Gantt Chart</a>
        @if($isAdmin)
            <a href="{{ route('project-files.index', $project) }}">Files</a>
            <a href="{{ route('milestones.index', $project) }}">Milestones</a>
            <a href="{{ route('projects.timelogs.index', $project) }}">Timesheet</a>
            <a href="{{ route('expenses.index', $project) }}">Expenses</a>
            <a href="{{ route('projects.notes.index', $project) }}">Notes</a>
            <a href="{{ route('projects.discussions.index', $project) }}">Discussion</a>
            <a href="{{ route('projects.burndown', $project) }}">Burndown</a>
            <a href="{{ route('tickets.index', ['project_id' => $project->id]) }}">Ticket</a>
        @endif
    </nav>

    <section class="project-detail-grid">
        <article class="project-detail-card">
            <h2>Project Details</h2>
            <dl>
                <dt>Project Code</dt><dd>{{ $project->project_code ?? 'N/A' }}</dd>
                @if($isAdmin)
                    <dt>Client</dt><dd>{{ $project->client->name ?? 'N/A' }}</dd>
                @endif
                <dt>Start Date</dt><dd>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : 'N/A' }}</dd>
                <dt>Deadline</dt><dd>{{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M Y') : 'No deadline' }}</dd>
                <dt>Priority</dt><dd><span class="project-priority-pill {{ $project->priority ?? 'medium' }}">{{ $priorityOptions[$project->priority ?? 'medium'] ?? 'Medium' }}</span></dd>
                <dt>Status</dt>
                <dd>
                    <span class="project-status-pill {{ str_replace(' ', '-', $status) }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                </dd>
                <dt>Progress</dt>
                <dd>
                    <div class="project-detail-progress">
                        <span style="width: {{ max(0, min(100, (int) ($project->completion_percent ?? 0))) }}%"></span>
                    </div>
                    <strong>{{ (int) ($project->completion_percent ?? 0) }}%</strong>
                </dd>
            </dl>
        </article>

        <article class="project-detail-card">
            <h2>Assigned Members</h2>
            <div class="project-member-list">
                @forelse($project->users as $user)
                    <div class="project-member-row">
                        <span>
                            @if($user->profile_image)
                                <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}">
                            @else
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            @endif
                        </span>
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->employeeDetail?->designation?->name ?? $user->email }}</small>
                        </div>
                    </div>
                @empty
                    <p class="project-muted">No members assigned.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="project-detail-card">
        <h2>Description</h2>
        <p class="project-description">{{ $project->description ?: 'No project description added yet.' }}</p>
    </section>

    <section class="project-detail-card">
        <h2>Remarks</h2>
        <p class="project-description">{{ $project->remarks ?: 'No remarks added yet.' }}</p>
    </section>

    @if($isEmployee)
        <section class="project-detail-card">
            <h2>Update Your Project Status</h2>
            <form method="POST" action="{{ route('projects.updates.store', $project) }}" class="project-update-form">
                @csrf
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Progress (%)</label>
                    <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ old('progress', $project->completion_percent ?? 0) }}" required>
                </div>
                <div class="project-update-remarks">
                    <label class="form-label">Work Remarks / Notes</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="What changed?">{{ old('remarks') }}</textarea>
                </div>
                <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save Update</button>
            </form>
        </section>
    @endif

    <section class="project-detail-card">
        <h2>Update History</h2>
        <div class="project-update-list">
            @forelse($project->updates->sortByDesc('created_at')->take(8) as $update)
                <div class="project-update-row">
                    <div>
                        <strong>{{ $update->employee?->name ?? 'Admin' }}</strong>
                        <span>{{ $statusOptions[$update->status] ?? ucfirst((string) $update->status) }} / {{ $update->progress }}%</span>
                    </div>
                    <p>{{ $update->remarks ?: 'Status/progress updated.' }}</p>
                    <small>{{ $update->created_at?->format('d M Y h:i A') }}</small>
                </div>
            @empty
                <p class="project-muted">No updates recorded yet.</p>
            @endforelse
        </div>
    </section>

    @if($isAdmin)
        <section class="project-detail-grid">
            <article class="project-detail-card">
                <h2>Budget & Hours</h2>
                <dl>
                    <dt>Project Budget</dt><dd>{{ $project->project_budget ?? 0 }}</dd>
                    <dt>Hours Allocated</dt><dd>{{ $project->hours_allocated ?? 0 }}</dd>
                </dl>
            </article>
            <article class="project-detail-card">
                <h2>Admin Flags</h2>
                <dl>
                    <dt>Public Gantt</dt><dd>{{ $project->public_gantt_chart ? 'Enabled' : 'Disabled' }}</dd>
                    <dt>Public Task Board</dt><dd>{{ $project->public_taskboard ? 'Enabled' : 'Disabled' }}</dd>
                    <dt>Client Access</dt><dd>{{ $project->client_access ? 'Enabled' : 'Disabled' }}</dd>
                </dl>
            </article>
        </section>
    @endif
</main>

<style>
    .project-detail-page { min-height: 100vh; padding: 24px; background: #f6f8fb; color: #1f2937; }
    .project-detail-hero, .project-detail-card, .project-detail-tabs {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }
    .project-detail-hero { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 22px; margin-bottom: 14px; }
    .project-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: #0f766e; font-size: .76rem; font-weight: 950; text-transform: uppercase; }
    .project-detail-hero h1 { margin: 8px 0 5px; color: #111827; font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 950; }
    .project-detail-hero p, .project-muted { margin: 0; color: #64748b; font-weight: 750; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; border-radius: 8px; font-weight: 900; }
    .btn-light { background: #eef2f7; border: 1px solid #d9e2ec; color: #334155; }
    .project-detail-tabs { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; margin-bottom: 14px; }
    .project-detail-tabs a { padding: 9px 12px; border-radius: 8px; color: #334155; text-decoration: none; font-weight: 850; }
    .project-detail-tabs a.active { background: #0f766e; color: #fff; }
    .project-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px; }
    .project-detail-card { padding: 18px; margin-bottom: 14px; }
    .project-detail-card h2 { margin: 0 0 14px; color: #111827; font-size: 1.15rem; font-weight: 950; }
    .project-detail-card dl { display: grid; grid-template-columns: 140px 1fr; gap: 10px 14px; margin: 0; }
    .project-detail-card dt { color: #64748b; font-size: .78rem; font-weight: 950; text-transform: uppercase; }
    .project-detail-card dd { margin: 0; color: #1f2937; font-weight: 800; overflow-wrap: anywhere; }
    .project-status-pill { display: inline-flex; padding: 7px 10px; border-radius: 999px; background: #0f766e; color: #fff; font-size: .78rem; font-weight: 950; text-transform: capitalize; }
    .project-priority-pill { display: inline-flex; padding: 7px 10px; border-radius: 999px; font-size: .78rem; font-weight: 950; text-transform: capitalize; }
    .project-priority-pill.low { background: #dbeafe; color: #1d4ed8; }
    .project-priority-pill.medium { background: #fef3c7; color: #92400e; }
    .project-priority-pill.high { background: #fed7aa; color: #c2410c; }
    .project-priority-pill.critical { background: #fee2e2; color: #b91c1c; }
    .project-detail-progress { display: inline-block; width: min(180px, 100%); height: 8px; margin-right: 8px; border-radius: 999px; background: #e5e7eb; overflow: hidden; vertical-align: middle; }
    .project-detail-progress span { display: block; height: 100%; background: #0f766e; border-radius: inherit; }
    .project-member-list { display: grid; gap: 10px; }
    .project-member-row { display: grid; grid-template-columns: 42px 1fr; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: #f8fafc; }
    .project-member-row span { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; font-weight: 950; overflow: hidden; }
    .project-member-row img { width: 100%; height: 100%; object-fit: cover; }
    .project-member-row strong, .project-member-row small { display: block; overflow-wrap: anywhere; }
    .project-member-row small { color: #64748b; font-weight: 750; }
    .project-description { white-space: pre-wrap; color: #334155; font-weight: 750; line-height: 1.65; }
    .form-select, .form-control { min-height: 38px; border-radius: 8px; border: 1px solid #d9e2ec; font-weight: 750; }
    .project-update-form { display: grid; grid-template-columns: minmax(160px, .7fr) minmax(140px, .45fr) 1fr auto; gap: 14px; align-items: end; }
    .project-update-remarks { min-width: 240px; }
    .project-update-list { display: grid; gap: 10px; }
    .project-update-row { display: grid; grid-template-columns: minmax(180px, .35fr) 1fr auto; gap: 14px; align-items: start; padding: 12px; border-radius: 8px; background: #f8fafc; }
    .project-update-row strong, .project-update-row span, .project-update-row small { display: block; }
    .project-update-row span, .project-update-row small { color: #64748b; font-weight: 750; }
    .project-update-row p { margin: 0; color: #334155; font-weight: 750; }
    @media (max-width: 767px) {
        .project-detail-page { padding: 12px; }
        .project-detail-hero { align-items: stretch; flex-direction: column; }
        .project-detail-hero .btn { width: 100%; }
        .project-detail-grid { grid-template-columns: 1fr; }
        .project-detail-card dl { grid-template-columns: 1fr; }
        .project-update-form, .project-update-row { grid-template-columns: 1fr; }
    }
</style>

@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    document.querySelectorAll('.project-status-select').forEach(select => {
        select.addEventListener('change', function () {
            const projectId = this.dataset.projectId;
            const status = this.value;
            this.disabled = true;

            fetch("{{ url('admin/projects') }}/" + projectId + "/status", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({_method: 'PATCH', status})
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok || !data.success) throw new Error(data.message || 'Status update failed.');
            })
            .catch(error => alert(error.message))
            .finally(() => this.disabled = false);
        });
    });
});
</script>
@endpush
