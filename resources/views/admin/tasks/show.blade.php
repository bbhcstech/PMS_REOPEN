@extends('admin.layout.app')

@section('title', 'Task #' . ($task->task_short_code ?? $task->id) . ' - ' . $task->title)

@section('content')
<style>
    :root {
        --tsk-primary: #0f744c;
        --tsk-primary-hover: #0c5d3d;
        --tsk-primary-light: #10b981;
        --tsk-primary-soft: #eaf6f0;
        --tsk-slate-dark: #0f172a;
        --tsk-slate-body: #334155;
        --tsk-slate-muted: #64748b;
        --tsk-slate-light: #f8fafc;
        --tsk-border: rgba(226, 232, 240, 0.85);
        --tsk-card-bg: #ffffff;
        --tsk-shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.03);
        --tsk-shadow: 0 12px 30px -8px rgba(15, 116, 76, 0.08), 0 4px 12px rgba(0, 0, 0, 0.03);
        --tsk-shadow-hover: 0 20px 40px -10px rgba(15, 116, 76, 0.14);
    }

    .task-page-shell {
        padding: 1.25rem 0 3rem;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Breadcrumbs & Header */
    .task-breadcrumb .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0.5rem;
    }
    .task-breadcrumb .breadcrumb-item a {
        color: var(--tsk-slate-muted);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .task-breadcrumb .breadcrumb-item.active {
        color: var(--tsk-primary);
        font-weight: 700;
        font-size: 0.85rem;
    }

    /* Hero Banner Card */
    .task-hero-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbf9 100%);
        border: 1px solid var(--tsk-border);
        border-radius: 20px;
        box-shadow: var(--tsk-shadow);
        padding: 1.75rem 2rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .task-hero-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }

    .task-hero-title {
        font-size: 1.65rem;
        font-weight: 900;
        color: var(--tsk-slate-dark);
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem;
        line-height: 1.25;
    }

    .task-code-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        background: var(--tsk-primary-soft);
        color: var(--tsk-primary);
        font-weight: 800;
        font-size: 0.82rem;
        border-radius: 999px;
        border: 1px solid rgba(15, 116, 76, 0.2);
    }

    /* Quick Metrics Strip */
    .task-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .task-metric-box {
        background: var(--tsk-card-bg);
        border: 1px solid var(--tsk-border);
        border-radius: 16px;
        padding: 1.1rem 1.25rem;
        box-shadow: var(--tsk-shadow-sm);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .task-metric-box:hover {
        transform: translateY(-2px);
        box-shadow: var(--tsk-shadow);
    }

    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .metric-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--tsk-slate-muted);
        letter-spacing: 0.05em;
        margin-bottom: 0.2rem;
    }
    .metric-val {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--tsk-slate-dark);
        line-height: 1.2;
    }

    /* Main Container Cards */
    .task-card {
        background: var(--tsk-card-bg);
        border: 1px solid var(--tsk-border);
        border-radius: 20px;
        box-shadow: var(--tsk-shadow);
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    .task-card-header {
        padding: 1.25rem 1.5rem;
        background: rgba(248, 250, 249, 0.85);
        border-bottom: 1px solid var(--tsk-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .task-card-header h5 {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--tsk-slate-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .task-card-body {
        padding: 1.5rem;
    }

    /* Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1rem;
    }

    .detail-item {
        background: #fbfdfc;
        border: 1px solid rgba(226, 232, 240, 0.7);
        border-radius: 14px;
        padding: 0.9rem 1.1rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .detail-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--tsk-slate-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .detail-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--tsk-slate-dark);
    }

    /* User Avatar Pill */
    .user-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.65rem 0.25rem 0.35rem;
        background: #f1f5f9;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--tsk-slate-dark);
    }
    .user-avatar-circle {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, #26a96c, #4ecb91);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* Interactive Pill Tabs */
    .task-nav-pills {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .task-nav-pills .nav-link {
        color: var(--tsk-slate-muted);
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .task-nav-pills .nav-link:hover {
        background: #f1f5f9;
        color: var(--tsk-slate-dark);
        border-color: #cbd5e1;
    }
    .task-nav-pills .nav-link.active {
        background: linear-gradient(135deg, #0f744c 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border-color: #0f744c;
        box-shadow: 0 6px 16px rgba(15, 116, 76, 0.25);
    }
    .task-nav-pills .nav-link.active * {
        color: #ffffff !important;
    }

    /* Action Buttons */
    .btn-tsk-primary {
        background: linear-gradient(135deg, #0f744c 0%, #10b981 100%);
        color: #ffffff !important;
        font-weight: 800;
        font-size: 0.88rem;
        padding: 0.55rem 1.25rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 4px 14px rgba(15, 116, 76, 0.25);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
    }
    .btn-tsk-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 116, 76, 0.35);
        color: #ffffff !important;
    }
    .btn-tsk-primary * {
        color: #ffffff !important;
    }

    .btn-tsk-outline {
        background: rgba(15, 116, 76, 0.08);
        color: var(--tsk-primary) !important;
        font-weight: 800;
        font-size: 0.88rem;
        padding: 0.5rem 1.15rem;
        border-radius: 999px;
        border: 1px solid rgba(15, 116, 76, 0.25);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        text-decoration: none;
    }
    .btn-tsk-outline:hover {
        background: var(--tsk-primary);
        color: #ffffff !important;
        border-color: var(--tsk-primary);
        transform: translateY(-2px);
    }
    .btn-tsk-outline:hover * {
        color: #ffffff !important;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: capitalize;
        color: #000000 !important;
    }
    .status-pill.completed {
        background: #d1fae5 !important;
        color: #000000 !important;
        border: 1px solid #86efac !important;
    }
    .status-pill.doing, .status-pill.in-progress {
        background: #dbeafe !important;
        color: #000000 !important;
        border: 1px solid #93c5fd !important;
    }
    .status-pill.to-do {
        background: #e2e8f0 !important;
        color: #000000 !important;
        border: 1px solid #cbd5e1 !important;
    }
    .status-pill.waiting-for-approval {
        background: #ffedd5 !important;
        color: #000000 !important;
        border: 1px solid #fdba74 !important;
    }
    .status-pill.incomplete {
        background: #fee2e2 !important;
        color: #000000 !important;
        border: 1px solid #fca5a5 !important;
    }

    /* Priority Pills */
    .priority-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.8rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: capitalize;
    }
    .priority-pill.high, .priority-pill.urgent {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }
    .priority-pill.medium {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .priority-pill.low {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    /* Progress bar custom */
    .task-progress-bar {
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .task-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    /* Update Feed */
    .update-timeline-item {
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .update-timeline-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y task-page-shell">

    {{-- Breadcrumbs --}}
    <div class="task-breadcrumb mb-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt me-1"></i>Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                <li class="breadcrumb-item active" aria-current="page">Task #{{ $task->task_short_code ?? 'TASK_' . str_pad($task->id, 3, '0', STR_PAD_LEFT) }}</li>
            </ol>
        </nav>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert" style="background: rgba(16, 185, 129, 0.12); color: #047857; border: 1px solid rgba(16, 185, 129, 0.3);">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <strong>{{ session('success') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Hero Banner Card --}}
    <div class="task-hero-card">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <span class="task-code-badge">
                        <i class="bx bx-hash"></i> {{ $task->task_short_code ?? 'TASK_' . str_pad($task->id, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    @php
                        $st = strtolower(str_replace(' ', '-', (string)($task->status ?? 'to-do')));
                        $pr = strtolower((string)($task->priority ?? 'medium'));
                    @endphp
                    <span class="status-pill {{ $st }}">
                        <i class="bx bxs-circle" style="font-size: 0.45rem;"></i> {{ $task->status ?? 'To Do' }}
                    </span>
                    <span class="priority-pill {{ $pr }}">
                        <i class="bx bx-flag"></i> {{ ucfirst($task->priority ?? 'Medium') }} Priority
                    </span>
                </div>
                <h2 class="task-hero-title mb-1">{{ $task->title }}</h2>
                <div class="text-muted small">
                    <i class="bx bx-calendar-event me-1"></i> Created on {{ $task->created_at->format('d M, Y \a\t h:i A') }}
                    @if($task->project)
                        &bull; <i class="bx bx-folder me-1 text-primary"></i> <a href="{{ route('projects.show', $task->project_id) }}" class="text-primary text-decoration-none fw-bold">{{ $task->project->name }}</a> ({{ $task->project->project_code }})
                    @endif
                </div>
            </div>

            {{-- Action Controls --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Mark as Complete Button --}}
                @if($task->status !== 'Completed')
                    <a href="{{ route('tasks.markComplete', $task->id) }}" class="btn-tsk-outline">
                        <i class="bx bx-check-circle fs-5"></i> Mark as Complete
                    </a>
                @else
                    <span class="btn-tsk-outline text-success" style="background: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.3);">
                        <i class="bx bx-check-double fs-5"></i> Completed
                    </span>
                @endif

                {{-- Live Timer Actions --}}
                @php
                    $activeTimer = $task->activeTimer;
                @endphp

                @if($activeTimer)
                    @if($activeTimer->pause_time)
                        {{-- Resume Button --}}
                        <form method="POST" action="{{ route('task-timer.resume', $task->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info text-white rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                                <i class="bx bx-play-circle fs-5"></i> Resume Timer
                            </button>
                        </form>
                    @else
                        {{-- Pause Button --}}
                        <form method="POST" action="{{ route('task-timer.pause', $task->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning text-dark rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                                <i class="bx bx-pause-circle fs-5"></i> Pause Timer
                            </button>
                        </form>
                    @endif

                    {{-- Stop Timer Modal Trigger --}}
                    <button class="btn btn-danger rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#stopTimerModal-{{ $task->id }}">
                        <i class="bx bx-stop-circle fs-5"></i> Stop
                    </button>
                @else
                    {{-- Start Timer Button --}}
                    <form method="POST" action="{{ route('task-timer.start', $task->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-tsk-primary">
                            <i class="bx bx-play-circle fs-5"></i> Start Timer
                        </button>
                    </form>
                @endif

                {{-- Action Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle p-2 border shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded fs-5 text-dark"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('tasks.edit', $task->id) }}">
                                <i class="bx bx-edit text-warning me-2"></i> Edit Task
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('timelogs.create', ['project_id' => $task->project_id, 'task_id' => $task->id]) }}">
                                <i class="bx bx-time text-success me-2"></i> Log Time
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="javascript:;" onclick="navigator.clipboard.writeText(window.location.href); alert('Task link copied to clipboard!');">
                                <i class="bx bx-copy text-primary me-2"></i> Copy Task Link
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Metric Cards Strip --}}
    <div class="task-metrics-grid">
        <div class="task-metric-box">
            <div class="metric-icon-box" style="background: rgba(15, 116, 76, 0.1); color: var(--tsk-primary);">
                <i class="bx bx-folder"></i>
            </div>
            <div>
                <div class="metric-label">Project</div>
                <div class="metric-val text-truncate" style="max-width: 180px;">
                    {{ $task->project->name ?? 'No Project' }}
                </div>
            </div>
        </div>

        <div class="task-metric-box">
            <div class="metric-icon-box" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                <i class="bx bx-user-pin"></i>
            </div>
            <div>
                <div class="metric-label">Assigned By</div>
                <div class="metric-val text-truncate" style="max-width: 180px;">
                    {{ $task->createdBy->name ?? 'System' }}
                </div>
            </div>
        </div>

        <div class="task-metric-box">
            <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="bx bx-calendar"></i>
            </div>
            <div>
                <div class="metric-label">Due Date</div>
                <div class="metric-val">
                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M, Y') : 'No Due Date' }}
                </div>
            </div>
        </div>

        <div class="task-metric-box">
            <div class="metric-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="bx bx-time-five"></i>
            </div>
            <div>
                <div class="metric-label">Hours Logged</div>
                <div class="metric-val text-success">
                    {{ $task->total_logged_formatted ?? '00h 00m 00s' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Section --}}
    <div class="row g-4">
        {{-- Left Column: Details & Tabs --}}
        <div class="col-lg-8">
            {{-- Task Overview Card --}}
            <div class="task-card">
                <div class="task-card-header">
                    <h5><i class="bx bx-file-blank text-success"></i> Task Specification & Attributes</h5>
                    @if(($task->estimate_hours ?? 0) > 0 || ($task->estimate_minutes ?? 0) > 0)
                        <span class="badge bg-label-info text-dark">
                            <i class="bx bx-hourglass-split me-1"></i>Est: {{ (int)($task->estimate_hours ?? 0) }}h {{ (int)($task->estimate_minutes ?? 0) }}m
                        </span>
                    @endif
                </div>
                <div class="task-card-body">
                    {{-- Description --}}
                    <div class="mb-4">
                        <label class="detail-label mb-2"><i class="bx bx-align-left"></i> Description</label>
                        <div class="p-3 rounded-3 bg-light border text-dark" style="font-size: 0.93rem; line-height: 1.6;">
                            {!! !empty($task->description) ? nl2br(e($task->description)) : '<span class="text-muted fst-italic">No description provided for this task.</span>' !!}
                        </div>
                    </div>

                    {{-- Attributes Grid --}}
                    <div class="details-grid">
                        {{-- Assigned To --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-group"></i> Assigned To</span>
                            <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                @php
                                    $assignedUsers = $task->assignees ?? collect();
                                    if ($assignedUsers->isEmpty() && $task->assigned_to) {
                                        $ids = explode(',', (string) $task->assigned_to);
                                        $assignedUsers = \App\Models\User::whereIn('id', $ids)->get();
                                    }
                                @endphp
                                @forelse($assignedUsers as $u)
                                    <div class="user-pill">
                                        <div class="user-avatar-circle">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                        <span>{{ $u->name }}</span>
                                    </div>
                                @empty
                                    <span class="text-muted">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Milestone --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-trip"></i> Milestone</span>
                            <div class="detail-value">
                                {{ $task->milestone->name ?? '--' }}
                            </div>
                        </div>

                        {{-- Task Category --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-category"></i> Task Category</span>
                            <div class="detail-value">
                                {{ $task->category->category_name ?? 'General' }}
                            </div>
                        </div>

                        {{-- Labels / Tags --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-tag"></i> Labels</span>
                            <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                @if(!empty($task->task_labels))
                                    @foreach(explode(',', (string)$task->task_labels) as $lbl)
                                        @if(trim($lbl))
                                            <span class="badge bg-light text-dark border">{{ trim($lbl) }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </div>
                        </div>

                        {{-- Private Task --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-lock-alt"></i> Visibility</span>
                            <div class="detail-value">
                                {{ $task->is_private ? 'Private Task' : 'Public Project Task' }}
                            </div>
                        </div>

                        {{-- Billable --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-dollar-circle"></i> Billable</span>
                            <div class="detail-value">
                                {{ $task->billable ? 'Billable Item' : 'Non-Billable' }}
                            </div>
                        </div>

                        {{-- Repeats --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-repeat"></i> Repeats</span>
                            <div class="detail-value">
                                @if($task->repeat)
                                    Every {{ $task->repeat_count }} {{ Str::plural($task->repeat_type, $task->repeat_count) }} ({{ $task->repeat_cycles }} cycles)
                                @else
                                    One-time Task
                                @endif
                            </div>
                        </div>

                        {{-- Depends On --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-git-repo-forked"></i> Depends On</span>
                            <div class="detail-value">
                                @if($task->dependentTask)
                                    <a href="{{ route('tasks.show', $task->dependentTask->id) }}" class="text-primary text-decoration-none">
                                        {{ $task->dependentTask->title }}
                                    </a>
                                @else
                                    None
                                @endif
                            </div>
                        </div>

                        {{-- Attached File --}}
                        <div class="detail-item">
                            <span class="detail-label"><i class="bx bx-paperclip"></i> Attached File</span>
                            <div class="detail-value">
                                @if($task->image_url)
                                    <a href="{{ asset($task->image_url) }}" target="_blank" class="text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-download"></i> {{ basename($task->image_url) }}
                                    </a>
                                @else
                                    <span class="text-muted">No File Attached</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interactive Section Tabs --}}
            <div class="task-card">
                <div class="task-card-body p-4">
                    <ul class="nav task-nav-pills" id="taskDetailsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="files-tab" data-bs-toggle="pill" data-bs-target="#files" type="button" role="tab">
                                <i class="bx bx-folder"></i> Files
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="subtasks-tab" data-bs-toggle="pill" data-bs-target="#sub-tasks" type="button" role="tab">
                                <i class="bx bx-list-check"></i> Sub Tasks
                                @if($task->subTasks->count())
                                    <span class="badge bg-white text-dark ms-1 rounded-pill">{{ $task->subTasks->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="comments-tab" data-bs-toggle="pill" data-bs-target="#comments" type="button" role="tab">
                                <i class="bx bx-comment-detail"></i> Comments
                                @if($task->comments->count())
                                    <span class="badge bg-white text-dark ms-1 rounded-pill">{{ $task->comments->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timesheet-tab" data-bs-toggle="pill" data-bs-target="#timesheet" type="button" role="tab">
                                <i class="bx bx-time-five"></i> Timesheet
                                @if($task->tasktimeLogs->count())
                                    <span class="badge bg-white text-dark ms-1 rounded-pill">{{ $task->tasktimeLogs->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="pill" data-bs-target="#notes" type="button" role="tab">
                                <i class="bx bx-note"></i> Notes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history" type="button" role="tab">
                                <i class="bx bx-history"></i> History
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="taskDetailsTabContent">
                        <div class="tab-pane fade show active" id="files" role="tabpanel">
                            @include('admin.tasks.files')
                        </div>
                        <div class="tab-pane fade" id="sub-tasks" role="tabpanel">
                            @include('admin.tasks.subtasks')
                        </div>
                        <div class="tab-pane fade" id="comments" role="tabpanel">
                            @include('admin.tasks.comments')
                        </div>
                        <div class="tab-pane fade" id="timesheet" role="tabpanel">
                            @include('admin.tasks.timesheet')
                        </div>
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            @include('admin.tasks.notes')
                        </div>
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            @include('admin.tasks.history')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Status & Timeline --}}
        <div class="col-lg-4">
            {{-- Status & Progress Card --}}
            <div class="task-card">
                <div class="task-card-header">
                    <h5><i class="bx bx-check-shield text-primary"></i> Progress & Status</h5>
                </div>
                <div class="task-card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="status-pill {{ $st }}">
                            <i class="bx bxs-circle" style="font-size: 0.45rem;"></i> {{ $task->status ?? 'To Do' }}
                        </span>
                        <span class="fw-bold text-dark fs-5">{{ (int) ($task->progress ?? 0) }}%</span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="task-progress-bar mb-4">
                        <div class="task-progress-fill" style="width: {{ (int) ($task->progress ?? 0) }}%;"></div>
                    </div>

                    @php
                        $currentUser = auth()->user();
                        $isAssignedEmployee = $currentUser && strtolower((string) $currentUser->role) === 'employee' && (
                            $task->assignees()->where('users.id', $currentUser->id)->exists()
                            || collect(explode(',', (string) $task->assigned_to))->contains((string) $currentUser->id)
                        );
                    @endphp

                    @if($isAssignedEmployee)
                        <form id="taskStatusUpdateForm" data-task-id="{{ $task->id }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Update Status</label>
                                <select name="status" class="form-select status-dropdown rounded-3" data-task-id="{{ $task->id }}">
                                    @foreach(['To Do', 'Doing', 'Incomplete', 'Completed'] as $status)
                                        <option value="{{ $status }}" @selected($task->status === $status)>{{ $status }}</option>
                                    @endforeach
                                    @if($task->status === 'Waiting for Approval')
                                        <option value="Waiting for Approval" selected>Waiting for Approval</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Progress Percentage (%)</label>
                                <input type="number" name="progress" class="form-control rounded-3" min="0" max="100" value="{{ (int) ($task->progress ?? 0) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Work Remarks</label>
                                <textarea name="remarks" class="form-control rounded-3" rows="3" maxlength="2000" placeholder="Notes about today's progress...">{{ $task->remarks }}</textarea>
                            </div>
                            <button type="submit" class="btn-tsk-primary w-100 justify-content-center">
                                <i class="bx bx-save"></i> Save Work Update
                            </button>
                        </form>
                    @else
                        @if($task->remarks)
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <small class="text-muted fw-bold d-block mb-1 text-uppercase" style="font-size: 0.75rem;">Latest Employee Remarks</small>
                                <p class="small text-dark mb-0">{{ $task->remarks }}</p>
                            </div>
                        @endif
                        <small class="text-muted d-block" style="font-size: 0.78rem;">
                            <i class="bx bx-info-circle me-1"></i> Status and progress are managed by the assigned employee.
                        </small>
                    @endif
                </div>
            </div>

            {{-- Schedule Timeline Card --}}
            <div class="task-card">
                <div class="task-card-header">
                    <h5><i class="bx bx-calendar-check text-warning"></i> Key Timelines</h5>
                </div>
                <div class="task-card-body">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-bold"><i class="bx bx-calendar-plus me-1"></i> Start Date</span>
                        <span class="fw-bold text-dark">{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d M, Y') : '--' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-bold"><i class="bx bx-calendar-x me-1"></i> Due Date</span>
                        <span class="fw-bold text-danger">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M, Y') : '--' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small fw-bold"><i class="bx bx-time me-1"></i> Total Logged</span>
                        <span class="fw-bold text-success">{{ $task->total_logged_formatted ?? '00h 00m 00s' }}</span>
                    </div>
                    @if($task->completed_on)
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted small fw-bold"><i class="bx bx-check-double me-1 text-success"></i> Completed On</span>
                            <span class="fw-bold text-success">{{ \Carbon\Carbon::parse($task->completed_on)->format('d M, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Latest Updates Feed --}}
            <div class="task-card">
                <div class="task-card-header">
                    <h5><i class="bx bx-pulse text-info"></i> Latest Activity Updates</h5>
                </div>
                <div class="task-card-body">
                    @forelse($task->updates->take(5) as $update)
                        <div class="update-timeline-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark small">{{ $update->user?->name ?? 'System' }}</strong>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $update->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-light text-dark border">{{ $update->status ?? '--' }}</span>
                                <span class="small fw-bold text-success">{{ (int) $update->progress }}% completed</span>
                            </div>
                            @if($update->remarks)
                                <p class="mb-0 small text-muted fst-italic">{{ $update->remarks }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0 text-center py-3">No activity updates recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stop Timer Modal --}}
@if($activeTimer)
<div class="modal fade" id="stopTimerModal-{{ $task->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('task-timer.stop', $task->id) }}" class="w-100">
            @csrf
            <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
            <input type="hidden" name="project_id" value="{{ $task->project_id }}">
            <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($activeTimer->start_time)->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ now()->format('Y-m-d') }}">
        
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bx bx-stop-circle text-danger me-1"></i> Stop Timer & Save Log
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded-3 bg-light border">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Start Time:</span>
                                <strong>{{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">End Time:</span>
                                <strong>{{ now()->format('h:i A') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Memo / Work Summary <span class="text-danger">*</span></label>
                        <textarea name="memo" class="form-control rounded-3" rows="3" placeholder="Briefly describe what you worked on..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-tsk-primary">Save & Stop Timer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('js')
<script>
$(document).ready(function () {
    // Toggle Sub-Tasks
    document.querySelectorAll('.toggle-subtasks').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const row = document.getElementById('subtasks-' + id);
            if (row) {
                if (row.style.display === 'none') {
                    row.style.display = '';
                    this.innerText = 'Hide Sub-Tasks';
                } else {
                    row.style.display = 'none';
                    this.innerText = 'Show Sub-Tasks';
                }
            }
        });
    });

    // Handle Task Status Update AJAX Form
    $('#taskStatusUpdateForm').on('submit', function (event) {
        event.preventDefault();

        const form = $(this);
        const taskId = form.data('task-id');

        $.ajax({
            url: "{{ url('/tasks') }}/" + taskId + "/update-status",
            type: "POST",
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    alert('Task update saved successfully.');
                    location.reload();
                }
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Task update failed.');
            }
        });
    });
});
</script>
@endpush
sh
