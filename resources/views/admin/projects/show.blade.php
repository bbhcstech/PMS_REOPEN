@extends('admin.layout.app')

@section('title', 'Project Overview - ' . $project->name)

@section('content')
@php
    $isAdmin = in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true);
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
    $status = strtolower($project->status ?: 'pending');

    $totalTasks = $stats['total_tasks'] ?? ($project->tasks ? $project->tasks->count() : 0);
    $completedTasks = $stats['completed_tasks'] ?? ($project->tasks ? $project->tasks->where('status', 'Completed')->count() : 0);
    $doingTasks = $project->tasks ? $project->tasks->whereIn('status', ['Doing', 'In Progress'])->count() : 0;
    $todoTasks = $project->tasks ? $project->tasks->whereIn('status', ['To Do', 'pending'])->count() : 0;
    $overdueTasks = $project->tasks ? $project->tasks->filter(fn($t) => $t->due_date && \Carbon\Carbon::parse($t->due_date)->isPast() && $t->status !== 'Completed')->count() : 0;

    $totalHours = $stats['total_hours'] ?? ($project->timelogs ? round((float) $project->timelogs->sum('total_hours'), 2) : 0);
    $allocatedHours = (float) ($project->hours_allocated ?? 0);
    $hoursPercent = $allocatedHours > 0 ? min(100, round(($totalHours / $allocatedHours) * 100)) : 0;

    $totalExpenses = $stats['total_expenses'] ?? ($project->expenses ? (float) $project->expenses->sum('price') : 0);
    $budget = (float) ($project->project_budget ?? 0);
    $budgetPercent = $budget > 0 ? min(100, round(($totalExpenses / $budget) * 100)) : 0;

    $completionPercent = max(0, min(100, (int) ($project->completion_percent ?? 0)));
@endphp

<div class="project-overview-page">
    <div class="container-fluid px-3 px-md-4">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Standardized Project Header & 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'overview'
        ])

        {{-- Overview Summary Stats Cards --}}
        <div class="overview-stats-grid mb-4">
            <div class="overview-stat-card">
                <div class="stat-icon-wrapper icon-tasks">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total Tasks</span>
                    <h3 class="stat-number">{{ $totalTasks }}</h3>
                    <div class="stat-subtext">
                        <span class="text-success fw-bold">{{ $completedTasks }}</span> completed &bull; <span class="text-info fw-bold">{{ $doingTasks }}</span> active
                    </div>
                </div>
            </div>

            <div class="overview-stat-card">
                <div class="stat-icon-wrapper icon-progress">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Project Progress</span>
                    <h3 class="stat-number">{{ $completionPercent }}%</h3>
                    <div class="stat-progress-bar">
                        <div class="stat-progress-fill" style="width: {{ $completionPercent }}%;"></div>
                    </div>
                </div>
            </div>

            <div class="overview-stat-card">
                <div class="stat-icon-wrapper icon-time">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Hours Logged</span>
                    <h3 class="stat-number">{{ number_format($totalHours, 1) }}h</h3>
                    <div class="stat-subtext">
                        <span>{{ (int) $allocatedHours }}h allocated ({{ $hoursPercent }}%)</span>
                    </div>
                </div>
            </div>

            <div class="overview-stat-card">
                <div class="stat-icon-wrapper icon-expenses">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Total Expenses</span>
                    <h3 class="stat-number">₹{{ number_format($totalExpenses, 2) }}</h3>
                    <div class="stat-subtext">
                        <span>Budget: ₹{{ number_format($budget, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Row 1: Project Details & Assigned Members --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header">
                        <div class="header-icon"><i class="fas fa-info-circle"></i></div>
                        <h2>Project Details</h2>
                    </div>
                    <div class="overview-card-body">
                        <dl class="details-dl">
                            <div class="dl-row">
                                <dt>Project Code</dt>
                                <dd><span class="badge-code">{{ $project->project_code ?? 'N/A' }}</span></dd>
                            </div>
                            @if($isAdmin || $project->client)
                                <div class="dl-row">
                                    <dt>Client</dt>
                                    <dd>{{ $project->client->name ?? ($project->client->company_name ?? 'N/A') }}</dd>
                                </div>
                            @endif
                            <div class="dl-row">
                                <dt>Start Date</dt>
                                <dd>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : 'N/A' }}</dd>
                            </div>
                            <div class="dl-row">
                                <dt>Deadline</dt>
                                <dd>
                                    @if($project->deadline)
                                        @php
                                            $isOverdue = \Carbon\Carbon::parse($project->deadline)->isPast() && $project->status !== 'completed';
                                        @endphp
                                        <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                            {{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}
                                            @if($isOverdue) <span class="badge bg-danger-subtle text-danger ms-1">Overdue</span> @endif
                                        </span>
                                    @else
                                        <span class="text-muted">No deadline</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="dl-row">
                                <dt>Priority</dt>
                                <dd>
                                    <span class="project-priority-pill priority-{{ $project->priority ?? 'medium' }}">
                                        {{ $priorityOptions[$project->priority ?? 'medium'] ?? ucfirst($project->priority ?? 'medium') }}
                                    </span>
                                </dd>
                            </div>
                            <div class="dl-row">
                                <dt>Status</dt>
                                <dd>
                                    <span class="project-status-pill status-{{ str_replace(' ', '-', $status) }}">
                                        {{ $statusOptions[$status] ?? ucfirst($status) }}
                                    </span>
                                </dd>
                            </div>
                            @if($project->description)
                                <div class="dl-row full-width">
                                    <dt>Description</dt>
                                    <dd class="text-muted">{{ Str::limit($project->description, 160) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            {{-- 1. Members Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-users"></i></div>
                            <h2>Members</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ $project->users ? $project->users->count() : 0 }}</span>
                        </div>
                        <a href="{{ route('project-members.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="project-members-list">
                            @forelse($project->users ? $project->users->take(4) : [] as $user)
                                <div class="project-member-item">
                                    <div class="member-avatar-circle">
                                        @if($user->profile_image)
                                            <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}">
                                        @else
                                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="member-meta">
                                        <strong class="member-name">{{ $user->name }}</strong>
                                        <small class="member-subtext">{{ $user->employeeDetail?->designation?->name ?? $user->email }}</small>
                                    </div>
                                    <div class="member-role-badge">
                                        <span class="badge bg-light text-secondary border">{{ $user->pivot->role ?? 'Member' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-user-slash text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No members assigned to this project yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Tasks & Gantt Chart Roadmap --}}
        <div class="row g-4 mb-4">
            {{-- 2. Tasks Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-tasks"></i></div>
                            <h2>Tasks</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ $totalTasks }}</span>
                        </div>
                        <a href="{{ route('projects.tasks.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        {{-- Task Status Pills --}}
                        <div class="task-mini-stats mb-3">
                            <span class="badge bg-light text-dark border"><i class="fas fa-circle text-secondary me-1"></i> To Do ({{ $todoTasks }})</span>
                            <span class="badge bg-light text-info border"><i class="fas fa-spinner me-1"></i> Doing ({{ $doingTasks }})</span>
                            <span class="badge bg-light text-success border"><i class="fas fa-check-circle me-1"></i> Done ({{ $completedTasks }})</span>
                            @if($overdueTasks > 0)
                                <span class="badge bg-danger-subtle text-danger border"><i class="fas fa-exclamation-triangle me-1"></i> Overdue ({{ $overdueTasks }})</span>
                            @endif
                        </div>

                        {{-- Latest Tasks List --}}
                        <div class="mini-item-list">
                            @forelse($project->tasks ? $project->tasks->sortByDesc('id')->take(4) : [] as $t)
                                <div class="mini-list-row">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.68rem;">{{ $t->task_short_code ?: 'T-'.$t->id }}</span>
                                        <a href="{{ route('tasks.show', $t->id) }}" class="mini-row-title text-truncate" title="{{ $t->title }}">{{ $t->title }}</a>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="badge {{ $t->status === 'Completed' ? 'bg-success' : ($t->status === 'Doing' ? 'bg-info text-white' : 'bg-secondary') }}" style="font-size: 0.72rem;">{{ $t->status }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-tasks text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No tasks created yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Gantt Chart Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-chart-gantt"></i></div>
                            <h2>Gantt Chart & Schedule</h2>
                        </div>
                        <a href="{{ route('projects.gantt', $project->id) }}" class="feature-view-all">
                            Open Chart <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-gantt-preview p-3 rounded bg-light border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-dark"><i class="fas fa-calendar-alt text-teal me-1"></i> Project Timeline Span</span>
                                <span class="badge bg-teal text-white">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M') : 'Start' }} &rarr; {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M Y') : 'Ongoing' }}</span>
                            </div>
                            <div class="progress mb-2" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar" style="width: {{ $completionPercent }}%; background: linear-gradient(90deg, #0f744c, #10b981);"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                                <span>Planned: {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M Y') : 'Start' }}</span>
                                <span class="fw-bold text-teal">{{ $completionPercent }}% Completed</span>
                                <span>Deadline: {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M Y') : 'Open' }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><i class="fas fa-layer-group me-1"></i> {{ $project->milestones ? $project->milestones->count() : 0 }} Milestones &bull; {{ $totalTasks }} Tasks Scheduled</span>
                            <a href="{{ route('projects.gantt', $project->id) }}" class="btn btn-sm btn-outline-teal">
                                <i class="fas fa-timeline me-1"></i> View Interactive Timeline
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Files & Milestones --}}
        <div class="row g-4 mb-4">
            {{-- 4. Files Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-folder-open"></i></div>
                            <h2>Files & Attachments</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ $project->files ? $project->files->count() : 0 }}</span>
                        </div>
                        <a href="{{ route('project-files.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-item-list">
                            @forelse($project->files ? $project->files->sortByDesc('id')->take(4) : [] as $file)
                                <div class="mini-list-row">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <i class="fas fa-file-lines text-teal fa-lg flex-shrink-0"></i>
                                        <span class="mini-row-title text-truncate" title="{{ $file->filename }}">{{ $file->filename }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $file->size ? number_format($file->size / 1024, 1) . ' KB' : '' }}</small>
                                        <a href="{{ asset($file->file_path) }}" target="_blank" class="btn btn-sm btn-light border p-1" title="Download">
                                            <i class="fas fa-download text-secondary" style="font-size: 0.75rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-folder-open text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No files uploaded yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. Milestones Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-flag-checkered"></i></div>
                            <h2>Milestones</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ $project->milestones ? $project->milestones->count() : 0 }}</span>
                        </div>
                        <a href="{{ route('milestones.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-item-list">
                            @forelse($project->milestones ? $project->milestones->sortByDesc('id')->take(3) : [] as $milestone)
                                <div class="mini-milestone-box p-2 mb-2 rounded border bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark small text-truncate" title="{{ $milestone->title }}">{{ $milestone->title }}</strong>
                                        <span class="badge {{ strtolower($milestone->status) === 'complete' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.68rem;">
                                            {{ ucfirst($milestone->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                        <span>Due: {{ $milestone->end_date ? \Carbon\Carbon::parse($milestone->end_date)->format('d M Y') : 'N/A' }}</span>
                                        <span>Cost: ₹{{ number_format((float) ($milestone->cost ?? 0), 2) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-flag text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No milestones defined yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Timesheet & Expenses --}}
        <div class="row g-4 mb-4">
            {{-- 6. Timesheet Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-clock"></i></div>
                            <h2>Timesheet (Timelogs)</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ number_format($totalHours, 1) }}h</span>
                        </div>
                        <a href="{{ route('projects.timelogs.index', $project->id) }}" class="feature-view-all">
                            View Timesheet <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-item-list">
                            @forelse($project->timelogs ? $project->timelogs->sortByDesc('id')->take(3) : [] as $log)
                                <div class="mini-list-row">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <div class="member-avatar-circle" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                            {{ strtoupper(mb_substr($log->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="d-block small text-dark">{{ $log->user?->name ?? 'Employee' }}</strong>
                                            <small class="text-muted text-truncate d-block" style="max-width: 220px; font-size: 0.72rem;">{{ $log->memo ?: 'Task work logged' }}</small>
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="badge bg-teal text-white">{{ number_format($log->total_hours, 1) }}h</span>
                                        <small class="d-block text-muted" style="font-size: 0.68rem;">{{ $log->start_time ? \Carbon\Carbon::parse($log->start_time)->format('d M') : '' }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-clock text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No timelogs recorded yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7. Expenses Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-wallet"></i></div>
                            <h2>Expenses</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">₹{{ number_format($totalExpenses, 2) }}</span>
                        </div>
                        <a href="{{ route('expenses.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-item-list">
                            @forelse($project->expenses ? $project->expenses->sortByDesc('id')->take(3) : [] as $exp)
                                <div class="mini-list-row">
                                    <div class="overflow-hidden">
                                        <strong class="d-block small text-dark text-truncate" title="{{ $exp->item_name }}">{{ $exp->item_name }}</strong>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $exp->purchase_date ? \Carbon\Carbon::parse($exp->purchase_date)->format('d M Y') : '' }}</small>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <strong class="text-dark small">₹{{ number_format($exp->price, 2) }}</strong>
                                        <span class="badge {{ $exp->status === 'approved' ? 'bg-success' : 'bg-secondary' }} d-block mt-1" style="font-size: 0.65rem;">
                                            {{ ucfirst($exp->status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-wallet text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No project expenses recorded yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 5: Notes & Discussions --}}
        <div class="row g-4 mb-4">
            {{-- 8. Notes Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-sticky-note"></i></div>
                            <h2>Project Notes</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ ($project->projectNotes ?? collect())->count() }}</span>
                        </div>
                        <a href="{{ route('projects.notes.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-notes-grid">
                            @forelse(($project->projectNotes ?? collect())->sortByDesc('id')->take(2) as $note)
                                <div class="mini-note-card p-2 mb-2 rounded border bg-warning-subtle">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small text-dark text-truncate" title="{{ $note->title }}">{{ $note->title }}</strong>
                                        <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">{{ $note->type === 0 ? 'Public' : 'Private' }}</span>
                                    </div>
                                    <p class="text-muted mb-0 small text-truncate" style="font-size: 0.75rem;">{{ strip_tags($note->details) }}</p>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-sticky-note text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No notes created yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 9. Discussions Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-comments"></i></div>
                            <h2>Discussions</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ $project->discussions ? $project->discussions->count() : 0 }}</span>
                        </div>
                        <a href="{{ route('projects.discussions.index', $project->id) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-item-list">
                            @forelse($project->discussions ? $project->discussions->sortByDesc('id')->take(3) : [] as $disc)
                                <div class="mini-list-row">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <div class="member-avatar-circle" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                            {{ strtoupper(mb_substr($disc->user?->name ?? 'D', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="d-block small text-dark text-truncate" title="{{ $disc->title }}">{{ $disc->title }}</strong>
                                            <small class="text-muted" style="font-size: 0.7rem;">By {{ $disc->user?->name ?? 'User' }} &bull; {{ $disc->created_at?->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-light text-dark border" style="font-size: 0.7rem;"><i class="fas fa-comment me-1 text-teal"></i> {{ $disc->replies ? $disc->replies->count() : 0 }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-comments text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No discussions started yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 6: Burndown & Tickets --}}
        <div class="row g-4 mb-4">
            {{-- 10. Burndown Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-fire text-danger"></i></div>
                            <h2>Burndown & Velocity</h2>
                        </div>
                        <a href="{{ route('projects.burndown', $project->id) }}" class="feature-view-all">
                            View Burndown <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="d-flex justify-content-around text-center p-3 rounded bg-light border mb-2">
                            <div>
                                <span class="d-block text-muted small">Total Scope</span>
                                <h4 class="mb-0 fw-bold text-dark">{{ $totalTasks }}</h4>
                            </div>
                            <div class="border-start ps-3">
                                <span class="d-block text-muted small">Completed</span>
                                <h4 class="mb-0 fw-bold text-success">{{ $completedTasks }}</h4>
                            </div>
                            <div class="border-start ps-3">
                                <span class="d-block text-muted small">Remaining</span>
                                <h4 class="mb-0 fw-bold text-danger">{{ max(0, $totalTasks - $completedTasks) }}</h4>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Target Sprint velocity tracking</small>
                            <a href="{{ route('projects.burndown', $project->id) }}" class="btn btn-sm btn-outline-teal">
                                <i class="fas fa-chart-line me-1"></i> Open Burndown Curve
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 11. Tickets Feature Summary --}}
            <div class="col-lg-6">
                <div class="overview-card h-100">
                    <div class="overview-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="header-icon"><i class="fas fa-ticket-alt"></i></div>
                            <h2>Support Tickets</h2>
                            <span class="badge bg-light text-dark fw-bold border ms-1">{{ $project->tickets ? $project->tickets->count() : 0 }}</span>
                        </div>
                        <a href="{{ route('tickets.index', ['project_id' => $project->id]) }}" class="feature-view-all">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="overview-card-body">
                        <div class="mini-item-list">
                            @forelse($project->tickets ? $project->tickets->sortByDesc('id')->take(3) : [] as $ticket)
                                <div class="mini-list-row">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">#{{ $ticket->id }}</span>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="mini-row-title text-truncate" title="{{ $ticket->subject }}">{{ $ticket->subject }}</a>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge {{ $ticket->status === 'closed' || $ticket->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.68rem;">
                                            {{ ucfirst($ticket->status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-feature text-center py-3">
                                    <i class="fas fa-ticket-alt text-muted fa-2x mb-1"></i>
                                    <p class="text-muted mb-0 small">No support tickets linked to this project.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employee Update Form (if employee) --}}
        @if($isEmployee)
            <div class="overview-card mb-4">
                <div class="overview-card-header">
                    <div class="header-icon"><i class="fas fa-edit"></i></div>
                    <h2>Update Your Project Status</h2>
                </div>
                <div class="overview-card-body">
                    <form method="POST" action="{{ route('projects.updates.store', $project->id) }}" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Progress (%)</label>
                            <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ old('progress', $completionPercent) }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Work Remarks / Notes</label>
                            <input type="text" name="remarks" class="form-control" placeholder="What changed in your progress?" value="{{ old('remarks') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-teal text-white w-100">
                                <i class="fas fa-check-circle me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Update History Log --}}
        <div class="overview-card mb-4">
            <div class="overview-card-header">
                <div class="header-icon"><i class="fas fa-history"></i></div>
                <h2>Recent Project Updates</h2>
            </div>
            <div class="overview-card-body">
                <div class="project-updates-timeline">
                    @forelse($project->updates ? $project->updates->sortByDesc('created_at')->take(4) : [] as $update)
                        <div class="update-timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark">{{ $update->employee?->name ?? 'Admin' }}</strong>
                                    <small class="text-muted">{{ $update->created_at?->format('d M Y h:i A') }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-light text-dark border">{{ $statusOptions[$update->status] ?? ucfirst((string) $update->status) }}</span>
                                    <span class="badge bg-success-subtle text-success">{{ $update->progress }}% progress</span>
                                </div>
                                <p class="text-muted mb-0 small">{{ $update->remarks ?: 'Status and progress updated.' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0 small">No updates recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Budget, Hours & Admin Flags (Admin Only) --}}
        @if($isAdmin)
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="overview-card h-100">
                        <div class="overview-card-header">
                            <div class="header-icon"><i class="fas fa-coins"></i></div>
                            <h2>Budget & Hours Allocated</h2>
                        </div>
                        <div class="overview-card-body">
                            <dl class="details-dl">
                                <div class="dl-row">
                                    <dt>Project Budget</dt>
                                    <dd>₹{{ number_format((float) ($project->project_budget ?? 0), 2) }}</dd>
                                </div>
                                <div class="dl-row">
                                    <dt>Hours Allocated</dt>
                                    <dd>{{ $project->hours_allocated ?? 0 }} Hours</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="overview-card h-100">
                        <div class="overview-card-header">
                            <div class="header-icon"><i class="fas fa-toggle-on"></i></div>
                            <h2>Project Configuration & Flags</h2>
                        </div>
                        <div class="overview-card-body">
                            <dl class="details-dl">
                                <div class="dl-row">
                                    <dt>Public Gantt</dt>
                                    <dd><span class="badge {{ $project->public_gantt_chart ? 'bg-success' : 'bg-secondary' }}">{{ $project->public_gantt_chart ? 'Enabled' : 'Disabled' }}</span></dd>
                                </div>
                                <div class="dl-row">
                                    <dt>Client Access</dt>
                                    <dd><span class="badge {{ $project->client_access ? 'bg-success' : 'bg-secondary' }}">{{ $project->client_access ? 'Enabled' : 'Disabled' }}</span></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* ===== PROJECT OVERVIEW EXECUTIVE DASHBOARD STYLES ===== */
    .project-overview-page {
        min-height: 100vh;
        padding: 20px 0 40px;
        background: #f8fafc;
        color: #1e293b;
    }

    .btn-teal {
        background: #0f744c;
        border-color: #0f744c;
        color: #ffffff;
    }
    .btn-teal:hover {
        background: #0a5638;
        border-color: #0a5638;
        color: #ffffff;
    }

    .btn-outline-teal {
        color: #0f744c;
        border-color: #0f744c;
        background: transparent;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 10px;
    }
    .btn-outline-teal:hover {
        background: #0f744c;
        color: #ffffff;
    }

    .text-teal { color: #0f744c !important; }
    .bg-teal { background-color: #0f744c !important; }

    /* Stats Grid */
    .overview-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .overview-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .overview-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .icon-tasks { background: #e0f2fe; color: #0284c7; }
    .icon-progress { background: #dcfce7; color: #16a34a; }
    .icon-time { background: #fef3c7; color: #d97706; }
    .icon-expenses { background: #f3e8ff; color: #9333ea; }

    .stat-content {
        flex-grow: 1;
        overflow: hidden;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        display: block;
        margin-bottom: 2px;
    }

    .stat-number {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.2;
    }

    .stat-subtext {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 4px;
    }

    .stat-progress-bar {
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 6px;
    }

    .stat-progress-fill {
        height: 100%;
        background: #10b981;
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    /* Cards */
    .overview-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }

    .overview-card-header {
        padding: 14px 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .overview-card-header h2 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .header-icon {
        width: 32px;
        height: 32px;
        background: #ecfdf5;
        color: #0f744c;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    .feature-view-all {
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f744c;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: transform 0.15s ease;
    }

    .feature-view-all:hover {
        color: #0a5638;
        transform: translateX(2px);
    }

    .overview-card-body {
        padding: 16px 20px;
    }

    /* Details List */
    .details-dl {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin: 0;
    }

    .dl-row {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .dl-row.full-width {
        grid-column: span 2;
    }

    .dl-row dt {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.03em;
    }

    .dl-row dd {
        font-size: 0.88rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }

    .badge-code {
        background: #f1f5f9;
        color: #334155;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-block;
    }

    .project-priority-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .priority-low { background: #dcfce7; color: #166534; }
    .priority-medium { background: #fef3c7; color: #92400e; }
    .priority-high { background: #fee2e2; color: #991b1b; }
    .priority-critical { background: #7f1d1d; color: #ffffff; }

    .project-status-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-completed { background: #dcfce7; color: #166534; }
    .status-in-progress, .status-in_progress { background: #e0f2fe; color: #0369a1; }
    .status-pending, .status-not-started { background: #f1f5f9; color: #475569; }
    .status-on-hold, .status-delayed { background: #fee2e2; color: #991b1b; }

    /* Members List */
    .project-members-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .project-member-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 10px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #f1f5f9;
    }

    .member-avatar-circle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #0f744c;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }

    .member-avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-meta {
        flex-grow: 1;
        overflow: hidden;
    }

    .member-name {
        display: block;
        font-size: 0.85rem;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .member-subtext {
        font-size: 0.72rem;
        color: #64748b;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Mini Lists */
    .mini-item-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mini-list-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 10px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        gap: 8px;
    }

    .mini-row-title {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
    }

    .mini-row-title:hover {
        color: #0f744c;
    }

    /* Updates Timeline */
    .project-updates-timeline {
        display: flex;
        flex-direction: column;
        gap: 12px;
        position: relative;
        padding-left: 16px;
    }

    .project-updates-timeline::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #e2e8f0;
    }

    .update-timeline-item {
        position: relative;
    }

    .timeline-dot {
        position: absolute;
        left: -16px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #0f744c;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #d1fae5;
    }

    .timeline-content {
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .overview-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 576px) {
        .overview-stats-grid { grid-template-columns: 1fr; }
        .details-dl { grid-template-columns: 1fr; }
        .dl-row.full-width { grid-column: span 1; }
    }
</style>
@endsection
