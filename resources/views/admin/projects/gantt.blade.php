@extends('admin.layout.app')

@section('title', 'Gantt Chart - ' . $project->name)

@section('content')
<div class="gantt-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-chart-bar"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / <strong>Gantt Chart</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div>
                    <h1>Gantt Chart</h1>
                    <p>Visualize project timeline for <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-outline">
                    <i class="fas fa-eye"></i> View Project
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div>
                    <h3>{{ $project->tasks->count() }}</h3>
                    <span>Total Tasks</span>
                    <p class="stat-sub">All tasks in project</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3>{{ $project->tasks->where('status', 'completed')->count() }}</h3>
                    <span>Completed</span>
                    <p class="stat-sub">Finished tasks</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                <div>
                    <h3>{{ $project->tasks->where('status', '!=', 'completed')->count() }}</h3>
                    <span>In Progress</span>
                    <p class="stat-sub">Active tasks</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h3>
                        @php
                            $min = $project->tasks->min('start_date');
                            $max = $project->tasks->max('due_date');
                            $startDate = \Carbon\Carbon::parse($min ?? now())->startOfWeek();
                            $endDate = \Carbon\Carbon::parse($max ?? now())->endOfWeek();
                            $totalDays = $startDate->diffInDays($endDate) + 1;
                        @endphp
                        {{ $totalDays }}
                    </h3>
                    <span>Total Days</span>
                    <p class="stat-sub">Project duration</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs-wrapper">
            <ul class="nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.show', $project->id) }}">
                        <i class="fas fa-chart-pie"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('project-members.index', $project->id) }}">
                        <i class="fas fa-users"></i> Members
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('project-files.index', $project->id) }}">
                        <i class="fas fa-folder-open"></i> Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('milestones.index', $project->id) }}">
                        <i class="fas fa-flag-checkered"></i> Milestones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.tasks.index', $project->id) }}">
                        <i class="fas fa-tasks"></i> Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">
                        <i class="fas fa-columns"></i> Task Board
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('projects.gantt', $project->id) }}">
                        <i class="fas fa-chart-bar"></i> Gantt Chart
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.timelogs.index', $project->id) }}">
                        <i class="fas fa-clock"></i> Timesheet
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('expenses.index', $project->id) }}">
                        <i class="fas fa-money-bill-wave"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.notes.index', $project->id) }}">
                        <i class="fas fa-sticky-note"></i> Notes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link more-toggle" href="#" id="toggle-more">
                        <i class="fas fa-ellipsis-h"></i> More <i class="fas fa-chevron-down"></i>
                    </a>
                </li>
            </ul>

            <!-- Collapsible Extra Tabs -->
            <ul class="nav-tabs extra-tabs d-none" id="more-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.discussions.index', $project->id) }}">
                        <i class="fas fa-comments"></i> Discussion
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">
                        <i class="fas fa-fire"></i> Burndown Chart
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">
                        <i class="fas fa-history"></i> Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">
                        <i class="fas fa-ticket-alt"></i> Tickets
                    </a>
                </li>
            </ul>
        </div>

        <!-- Add Task Button -->
        @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
            <div class="action-bar">
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Task
                </a>
            </div>
        @endif

        <!-- Timeline Header -->
        <div class="timeline-header">
            <div class="timeline-info">
                <i class="fas fa-calendar-alt"></i>
                <span>Timeline:</span>
                <strong>{{ $startDate->format('d M, Y') }}</strong>
                <i class="fas fa-arrow-right"></i>
                <strong>{{ $endDate->format('d M, Y') }}</strong>
                <span class="duration-badge">{{ $totalDays }} days</span>
            </div>
            <div class="timeline-dates">
                @for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay())
                    <div class="date-label {{ $date->isToday() ? 'today' : '' }}">
                        <span class="day">{{ $date->format('d') }}</span>
                        <span class="month">{{ $date->format('M') }}</span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-card">
            <div class="chart-header">
                <div class="chart-title">
                    <div class="chart-title-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <h4>Gantt Chart View</h4>
                        <span class="muted">{{ $project->tasks->count() }} tasks displayed</span>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-color complete"></span>
                        <span>Completed</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color incomplete"></span>
                        <span>In Progress</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color today"></span>
                        <span>Today</span>
                    </div>
                    <button type="button" class="btn btn-outline gantt-fullscreen-toggle" id="ganttFullscreenToggle" aria-pressed="false">
                        <i class="fas fa-expand"></i>
                        <span>Full Screen</span>
                    </button>
                </div>
            </div>

            <div class="chart-body">
                <!-- Task List -->
                <div class="task-list">
                    <div class="task-list-header">
                        <span><i class="fas fa-tasks"></i> Task Name</span>
                        <span><i class="fas fa-clock"></i> Start</span>
                        <span><i class="fas fa-hourglass-half"></i> Duration</span>
                    </div>
                    <div class="task-list-body">
                        @foreach($project->tasks as $task)
                            @php
                                $start = \Carbon\Carbon::parse($task->start_date);
                                $end = \Carbon\Carbon::parse($task->due_date);
                                $duration = $start->diffInDays($end) + 1;
                                $isCompleted = $task->status === 'completed';
                            @endphp
                            <div class="task-item {{ $isCompleted ? 'completed' : '' }}">
                                <span class="task-name" title="{{ $task->title }}">{{ Str::limit($task->title, 25) }}</span>
                                <span class="task-start">{{ $start->format('d M, Y') }}</span>
                                <span class="task-duration">{{ $duration }}d</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Gantt Chart -->
                <div class="chart-container">
                    <div id="gantt-chart"></div>
                </div>
            </div>

            <!-- Chart Footer -->
            <div class="chart-footer">
                <div class="footer-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Hover on any bar to see task details</span>
                </div>
                <div class="footer-status">
                    <span class="status-dot"></span>
                    <span>Live data from project tasks</span>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-tasks text-primary"></i>
                <span>{{ $project->tasks->count() }}</span> Total Tasks
            </div>
            <div class="status-item">
                <i class="fas fa-check-circle text-success"></i>
                <span>{{ $project->tasks->where('status', 'completed')->count() }}</span> Completed
            </div>
            <div class="status-item">
                <i class="fas fa-spinner text-warning"></i>
                <span>{{ $project->tasks->where('status', '!=', 'completed')->count() }}</span> In Progress
            </div>
            <div class="status-item">
                <i class="fas fa-calendar-alt text-info"></i>
                {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<style>
    /* ===== PREMIUM GANTT CHART PAGE - GREEN/TEAL THEME ===== */
    .gantt-page {
        padding: 30px 0;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #07130d;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 16px 26px;
        border-radius: 18px;
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .breadcrumb i {
        margin-right: 12px;
        color: #34d399;
        font-size: 1.1rem;
    }

    .breadcrumb a {
        color: #0f744c;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb a:hover {
        color: #10b981;
    }

    .breadcrumb strong {
        color: #07130d;
    }

    /* Header Card */
    .header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 30px 36px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .header-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .2);
    }

    .header-card h1 {
        font-size: 34px;
        font-weight: 700;
        margin-bottom: 6px;
        color: #07130d;
    }

    .header-card p {
        color: #52645a;
        font-size: 17px;
        margin: 0;
    }

    .header-card p strong {
        color: #0f744c;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s ease;
        text-decoration: none;
        min-height: 48px;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(15, 116, 76, .2);
        color: #0f744c;
    }

    .btn-outline:hover {
        background: #edf8f2;
        border-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 116, 76, .1);
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, .25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 185, 129, .35);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        padding: 22px;
        border-radius: 22px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 14px 35px rgba(15, 116, 76, .06);
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(15, 116, 76, .12);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        background: #d1fae5;
        color: #0f744c;
    }

    .stat-card h3 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 2px;
        line-height: 1;
    }

    .stat-card span {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }

    /* Navigation Tabs */
    .nav-tabs-wrapper {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        overflow: hidden;
        margin-bottom: 28px;
        box-shadow: 0 8px 25px rgba(15, 116, 76, .06);
    }

    .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        padding: 8px 12px;
        margin: 0;
        list-style: none;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .08);
    }

    .nav-tabs.extra-tabs {
        border-top: 1px solid rgba(15, 116, 76, .08);
        border-bottom: none;
        padding-top: 8px;
    }

    .nav-item {
        margin: 0;
    }

    .nav-link {
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a6e63;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link i {
        font-size: 0.95rem;
    }

    .nav-link:hover {
        background: #edf8f2;
        color: #0f744c;
    }

    .nav-link.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
    }

    .nav-link.more-toggle {
        color: #0f744c;
        cursor: pointer;
    }

    .nav-link.more-toggle:hover {
        background: #edf8f2;
    }

    /* Action Bar */
    .action-bar {
        margin-bottom: 20px;
    }

    /* Timeline Header */
    .timeline-header {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .12);
        padding: 18px 24px;
        margin-bottom: 28px;
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .timeline-info {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.95rem;
        color: #5a6e63;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .timeline-info i {
        color: #0f744c;
    }

    .timeline-info strong {
        color: #07130d;
    }

    .duration-badge {
        display: inline-block;
        padding: 2px 14px;
        background: #d1fae5;
        color: #0f744c;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .timeline-dates {
        display: flex;
        gap: 4px;
        overflow-x: auto;
        padding: 4px 0;
    }

    .date-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 52px;
        padding: 4px 6px;
        border-radius: 8px;
        background: #fafefb;
        border: 1px solid rgba(15, 116, 76, .06);
        transition: all 0.2s;
    }

    .date-label .day {
        font-weight: 700;
        font-size: 0.9rem;
        color: #07130d;
    }

    .date-label .month {
        font-size: 0.65rem;
        color: #8ba198;
        text-transform: uppercase;
    }

    .date-label.today {
        background: #0f744c;
        border-color: #0f744c;
    }

    .date-label.today .day,
    .date-label.today .month {
        color: white;
    }

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
        overflow: hidden;
    }

    /* Chart Header */
    .chart-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .chart-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .chart-title-icon {
        width: 48px;
        height: 48px;
        background: #e7f5ee;
        color: #0f744c;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .chart-title h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .chart-title .muted {
        font-size: 0.95rem;
        color: #8ba198;
        display: block;
        margin-top: 2px;
    }

    .chart-legend {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #5a6e63;
        font-weight: 500;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }

    .legend-color.complete {
        background: #10b981;
    }

    .legend-color.incomplete {
        background: #f59e0b;
    }

    .legend-color.today {
        background: #0f744c;
        border: 2px solid #34d399;
    }

    /* Chart Body */
    .chart-body {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 0;
        min-height: 450px;
    }

    /* Task List */
    .task-list {
        border-right: 1px solid rgba(15, 116, 76, .08);
        background: #fafefb;
    }

    .task-list-header {
        display: grid;
        grid-template-columns: 1fr 80px 70px;
        padding: 12px 16px;
        background: #f0f9f4;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #5a6e63;
        border-bottom: 1px solid rgba(15, 116, 76, .08);
        gap: 8px;
    }

    .task-list-header i {
        margin-right: 4px;
    }

    .task-list-body {
        max-height: 450px;
        overflow-y: auto;
    }

    .task-item {
        display: grid;
        grid-template-columns: 1fr 80px 70px;
        padding: 10px 16px;
        border-bottom: 1px solid rgba(15, 116, 76, .04);
        font-size: 0.85rem;
        color: #07130d;
        gap: 8px;
        transition: background 0.2s;
        align-items: center;
    }

    .task-item:hover {
        background: #f0f9f4;
    }

    .task-item.completed .task-name {
        text-decoration: line-through;
        color: #8ba198;
    }

    .task-name {
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .task-start {
        font-size: 0.75rem;
        color: #5a6e63;
    }

    .task-duration {
        font-weight: 600;
        font-size: 0.8rem;
        color: #0f744c;
        text-align: center;
        background: #d1fae5;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .task-item.completed .task-duration {
        background: #d1fae5;
        color: #059669;
    }

    /* Chart Container */
    .chart-container {
        background: #ffffff;
        padding: 12px;
        min-height: 450px;
    }

    #gantt-chart {
        height: 450px;
        width: 100%;
    }

    /* Frappe Gantt Customizations */
    .gantt .bar-incomplete {
        fill: #f59e0b !important;
        opacity: 0.85;
    }

    .gantt .bar-complete {
        fill: #10b981 !important;
    }

    .gantt .bar-wrapper:hover .bar {
        opacity: 1 !important;
        stroke: #0f744c !important;
        stroke-width: 2px !important;
    }

    .gantt .bar-label {
        fill: #fff !important;
        font-weight: 600 !important;
        font-size: 10px !important;
    }

    /* Chart Footer */
    .chart-footer {
        padding: 18px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    .footer-info {
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .footer-info i {
        color: #0f744c;
        margin-right: 8px;
        font-size: 1rem;
    }

    .footer-status {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.9rem;
        color: #8ba198;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #10b981;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Status Bar */
    .status-bar {
        margin-top: 28px;
        background: white;
        border-radius: 20px;
        padding: 18px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        border: 1px solid rgba(15, 116, 76, .1);
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .status-item i {
        font-size: 1.1rem;
    }

    .status-item span {
        font-weight: 700;
        color: #07130d;
        margin-right: 4px;
        font-size: 1.05rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .chart-body {
            grid-template-columns: 1fr;
        }
        .task-list {
            border-right: none;
            border-bottom: 1px solid rgba(15, 116, 76, .08);
        }
        .task-list-body {
            max-height: 250px;
        }
    }

    @media (max-width: 992px) {
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
        }
        .nav-tabs {
            flex-wrap: wrap;
        }
        .nav-link {
            padding: 8px 14px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .gantt-page {
            padding: 16px 0;
        }
        .header-card {
            padding: 20px 24px;
        }
        .header-card h1 {
            font-size: 26px;
        }
        .header-card p {
            font-size: 15px;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .chart-legend {
            width: 100%;
            justify-content: flex-start;
        }
        .task-list-header,
        .task-item {
            grid-template-columns: 1fr 70px 60px;
            font-size: 0.75rem;
        }
        .timeline-dates {
            padding: 4px 0;
        }
        .date-label {
            min-width: 40px;
            padding: 2px 4px;
        }
        .date-label .day {
            font-size: 0.75rem;
        }
        .date-label .month {
            font-size: 0.55rem;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .chart-container {
            padding: 8px;
            min-height: 350px;
        }
        #gantt-chart {
            height: 350px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .gantt-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .status-bar,
    html[data-pms-theme="dark"] .nav-tabs-wrapper,
    html[data-pms-theme="dark"] .timeline-header {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .chart-title h4,
    html[data-pms-theme="dark"] .stat-card h3,
    html[data-pms-theme="dark"] .task-item,
    html[data-pms-theme="dark"] .timeline-info strong {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .task-list {
        background: #142a20;
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .task-list-header {
        background: #183026;
        color: #b7d5c4;
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .task-item:hover {
        background: #183026;
    }

    html[data-pms-theme="dark"] .task-duration {
        background: rgba(16, 185, 129, .2);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .task-item.completed .task-duration {
        background: rgba(16, 185, 129, .15);
        color: #34d399;
    }

    html[data-pms-theme="dark"] .chart-container {
        background: #102119;
    }

    html[data-pms-theme="dark"] .chart-header,
    html[data-pms-theme="dark"] .chart-footer {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .chart-title-icon {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .nav-link {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .nav-link:hover {
        background: rgba(122, 240, 181, .1);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .nav-link.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: white;
    }

    html[data-pms-theme="dark"] .date-label {
        background: #142a20;
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .date-label .day {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .date-label .month {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .date-label.today {
        background: #0f744c;
        border-color: #0f744c;
    }

    html[data-pms-theme="dark"] .date-label.today .day,
    html[data-pms-theme="dark"] .date-label.today .month {
        color: white;
    }

    html[data-pms-theme="dark"] .duration-badge {
        background: rgba(16, 185, 129, .2);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .gantt .bar-wrapper:hover .bar {
        stroke: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .gantt .bar-label {
        fill: #fff !important;
    }

    html[data-pms-theme="dark"] .gantt .bar-incomplete {
        fill: #f59e0b !important;
        opacity: 0.7;
    }

    html[data-pms-theme="dark"] .gantt .bar-complete {
        fill: #10b981 !important;
    }

    .gantt .bar-incomplete {
        fill: #ffcccb !important;
    }
    .gantt .bar-complete {
        fill: #90ee90 !important;
    }

    /* ===== CURRENT ADMIN UI ALIGNMENT - FINAL GANTT POLISH ===== */
    .gantt-page {
        --gantt-bg: #eef2ef;
        --gantt-surface: rgba(255, 255, 255, 0.96);
        --gantt-soft: #f7fbf8;
        --gantt-text: #101828;
        --gantt-muted: #667085;
        --gantt-line: rgba(15, 23, 42, 0.09);
        --gantt-primary: #0f744c;
        --gantt-primary-2: #14b8a6;
        --gantt-success: #10b981;
        --gantt-warning: #f59e0b;
        --gantt-danger: #ef4444;
        --gantt-shadow: 0 20px 52px rgba(15, 23, 42, 0.09);
        background:
            radial-gradient(circle at 12% 8%, rgba(20, 184, 166, 0.14), transparent 24rem),
            radial-gradient(circle at 90% 0%, rgba(15, 116, 76, 0.12), transparent 22rem),
            linear-gradient(135deg, #f8fbff 0%, #ffffff 44%, #eef7f2 100%) !important;
        color: var(--gantt-text);
        padding: clamp(0.8rem, 1.8vw, 1.4rem) 0 2rem;
    }

    .gantt-page .container-fluid {
        max-width: 1480px;
        padding-inline: clamp(0.75rem, 2vw, 1.35rem) !important;
    }

    .gantt-page .breadcrumb,
    .gantt-page .header-card,
    .gantt-page .stat-card,
    .gantt-page .nav-tabs-wrapper,
    .gantt-page .timeline-header,
    .gantt-page .content-card,
    .gantt-page .status-bar {
        background: var(--gantt-surface) !important;
        border: 1px solid var(--gantt-line) !important;
        border-radius: 18px !important;
        box-shadow: var(--gantt-shadow) !important;
        backdrop-filter: blur(16px);
    }

    .gantt-page .breadcrumb {
        align-items: center;
        display: flex;
        gap: 0.6rem;
        margin-bottom: 1rem;
        overflow: hidden;
        padding: 0.85rem 1rem;
    }

    .gantt-page .breadcrumb span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .gantt-page .header-card {
        background:
            radial-gradient(circle at 96% 12%, rgba(20, 184, 166, 0.16), transparent 18rem),
            var(--gantt-surface) !important;
        margin-bottom: 1rem;
        padding: clamp(1rem, 2.2vw, 1.55rem);
    }

    .gantt-page .header-left {
        min-width: 0;
    }

    .gantt-page .header-icon,
    .gantt-page .chart-title-icon,
    .gantt-page .stat-icon {
        background: linear-gradient(135deg, var(--gantt-primary), var(--gantt-primary-2)) !important;
        color: #fff !important;
        box-shadow: 0 14px 30px rgba(15, 116, 76, 0.22);
    }

    .gantt-page .header-card h1 {
        color: var(--gantt-text) !important;
        font-size: clamp(1.55rem, 3vw, 2.25rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1.05;
    }

    .gantt-page .header-card p,
    .gantt-page .muted,
    .gantt-page .stat-sub,
    .gantt-page .status-item,
    .gantt-page .legend-item,
    .gantt-page .timeline-info {
        color: var(--gantt-muted) !important;
        font-weight: 750;
    }

    .gantt-page .header-actions,
    .gantt-page .action-bar {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .gantt-page .btn {
        border-radius: 12px !important;
        font-weight: 850 !important;
        justify-content: center;
        min-height: 42px;
        padding: 0.65rem 0.95rem;
        white-space: nowrap;
    }

    .gantt-page .btn-outline {
        background: #fff !important;
        border: 1px solid rgba(15, 116, 76, 0.16) !important;
        color: var(--gantt-primary) !important;
    }

    .gantt-page .btn-primary {
        background: linear-gradient(135deg, var(--gantt-primary), var(--gantt-success)) !important;
        color: #fff !important;
    }

    .gantt-page .stats-grid {
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 1rem;
    }

    .gantt-page .stat-card {
        min-width: 0;
        overflow: hidden;
        padding: 1rem;
        position: relative;
    }

    .gantt-page .stat-card::before {
        content: "";
        inset: 0 auto 0 0;
        position: absolute;
        width: 5px;
        background: linear-gradient(180deg, var(--gantt-primary), var(--gantt-primary-2));
    }

    .gantt-page .stat-card h3 {
        color: var(--gantt-text) !important;
        font-size: clamp(1.45rem, 2.2vw, 1.9rem);
    }

    .gantt-page .stat-card span {
        color: #344054 !important;
    }

    .gantt-page .nav-tabs-wrapper {
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .gantt-page .nav-tabs {
        background: transparent !important;
        border: 0 !important;
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0.75rem;
        scrollbar-width: thin;
    }

    .gantt-page .nav-tabs.extra-tabs {
        border-top: 1px solid var(--gantt-line) !important;
    }

    .gantt-page .nav-link {
        align-items: center;
        border: 1px solid transparent;
        border-radius: 12px;
        color: #344054;
        display: inline-flex;
        flex: 0 0 auto;
        font-weight: 850;
        min-height: 42px;
        padding: 0.62rem 0.85rem;
        white-space: nowrap;
    }

    .gantt-page .nav-link:hover {
        background: rgba(15, 116, 76, 0.08);
        border-color: rgba(15, 116, 76, 0.12);
        color: var(--gantt-primary);
    }

    .gantt-page .nav-link.active {
        background: linear-gradient(135deg, var(--gantt-primary), var(--gantt-success)) !important;
        color: #fff !important;
    }

    .gantt-page .timeline-header {
        margin-bottom: 1rem;
        padding: 1rem;
    }

    .gantt-page .timeline-dates {
        border-radius: 14px;
        gap: 0.4rem;
        overflow-x: auto;
        padding: 0.35rem;
    }

    .gantt-page .date-label {
        background: #fff;
        border: 1px solid var(--gantt-line);
        min-width: 48px;
    }

    .gantt-page .date-label.today {
        background: linear-gradient(135deg, var(--gantt-primary), var(--gantt-success));
        border-color: transparent;
    }

    .gantt-page .content-card {
        overflow: hidden;
    }

    .gantt-page .chart-header,
    .gantt-page .chart-footer {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(255, 255, 255, 0.82)) !important;
        border-color: var(--gantt-line) !important;
        padding: 1rem;
    }

    .gantt-page .chart-title h4 {
        color: var(--gantt-text) !important;
        font-size: clamp(1rem, 1.6vw, 1.25rem);
        font-weight: 900;
    }

    .gantt-page .chart-body {
        background: #fff;
        grid-template-columns: minmax(260px, 310px) minmax(680px, 1fr);
        overflow-x: auto;
        min-height: 520px;
    }

    .gantt-page .task-list {
        background: #f8fafc !important;
        min-width: 260px;
        position: sticky;
        left: 0;
        z-index: 3;
    }

    .gantt-page .task-list-header,
    .gantt-page .task-item {
        grid-template-columns: minmax(0, 1fr) 82px 64px;
    }

    .gantt-page .task-list-header {
        background: #eef7f2 !important;
        border-color: var(--gantt-line) !important;
        color: #344054 !important;
    }

    .gantt-page .task-item {
        min-height: 42px;
    }

    .gantt-page .task-item:hover {
        background: #eef7f2 !important;
    }

    .gantt-page .chart-container {
        background:
            linear-gradient(90deg, rgba(15, 116, 76, 0.035) 1px, transparent 1px),
            #ffffff !important;
        background-size: 48px 100%;
        min-height: 520px;
        min-width: 680px;
        overflow: auto;
        padding: 0.75rem;
    }

    .gantt-page #gantt-chart {
        min-height: 500px;
        min-width: 680px;
    }

    .gantt .grid-header {
        fill: #f8fafc !important;
        stroke: rgba(15, 23, 42, 0.08) !important;
    }

    .gantt .grid-row {
        fill: #ffffff !important;
    }

    .gantt .grid-row:nth-child(even) {
        fill: #f8fbf9 !important;
    }

    .gantt .tick {
        stroke: rgba(15, 23, 42, 0.08) !important;
    }

    .gantt .today-highlight {
        fill: rgba(15, 116, 76, 0.12) !important;
    }

    .gantt .bar {
        rx: 6px;
        ry: 6px;
        filter: drop-shadow(0 8px 12px rgba(15, 23, 42, 0.12));
    }

    .gantt .bar-incomplete {
        fill: var(--gantt-warning) !important;
    }

    .gantt .bar-complete {
        fill: var(--gantt-success) !important;
    }

    .gantt .bar-progress {
        fill: rgba(15, 116, 76, 0.32) !important;
    }

    .gantt .bar-label {
        fill: #fff !important;
        font-size: 11px !important;
        font-weight: 800 !important;
    }

    .gantt-page .status-bar {
        margin-top: 1rem;
    }

    .gantt-page .status-item {
        background: #f8fafc;
        border: 1px solid var(--gantt-line);
        border-radius: 999px;
        padding: 0.5rem 0.75rem;
    }

    .gantt-page .empty-chart {
        min-height: 420px;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .gantt-page .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .gantt-page .chart-body {
            grid-template-columns: minmax(250px, 290px) minmax(640px, 1fr);
        }
    }

    @media (max-width: 767.98px) {
        .gantt-page {
            padding-top: 0.6rem;
        }

        .gantt-page .container-fluid {
            padding-inline: 0.65rem !important;
        }

        .gantt-page .breadcrumb {
            font-size: 0.82rem;
            margin-bottom: 0.75rem;
        }

        .gantt-page .header-card {
            align-items: stretch;
            gap: 1rem;
        }

        .gantt-page .header-left {
            align-items: flex-start;
            gap: 0.8rem;
        }

        .gantt-page .header-icon {
            border-radius: 14px;
            flex: 0 0 48px;
            height: 48px;
            width: 48px;
            font-size: 1.25rem;
        }

        .gantt-page .header-actions,
        .gantt-page .action-bar {
            display: grid;
            grid-template-columns: 1fr;
        }

        .gantt-page .btn {
            width: 100%;
        }

        .gantt-page .stats-grid {
            grid-template-columns: 1fr;
        }

        .gantt-page .stat-card {
            min-height: 96px;
        }

        .gantt-page .nav-tabs {
            flex-wrap: nowrap;
            margin-inline: -0.15rem;
        }

        .gantt-page .timeline-info {
            align-items: flex-start;
            display: grid;
            grid-template-columns: auto 1fr;
        }

        .gantt-page .chart-header,
        .gantt-page .chart-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .gantt-page .chart-legend,
        .gantt-page .footer-status {
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .gantt-page .chart-body {
            display: block;
            min-height: auto;
            overflow-x: auto;
        }

        .gantt-page .task-list {
            border-bottom: 1px solid var(--gantt-line);
            border-right: 0;
            max-height: 240px;
            min-width: 620px;
            position: static;
        }

        .gantt-page .task-list-body {
            max-height: 188px;
        }

        .gantt-page .chart-container,
        .gantt-page #gantt-chart {
            min-height: 390px;
            min-width: 680px;
        }

        .gantt-page .chart-container {
            padding: 0.5rem;
        }

        .gantt-page .status-bar {
            align-items: stretch;
            display: grid;
            grid-template-columns: 1fr;
        }

        .gantt-page .status-item {
            border-radius: 14px;
            width: 100%;
        }
    }

    html[data-pms-theme="dark"] .gantt-page {
        --gantt-bg: #07110d;
        --gantt-surface: rgba(16, 33, 25, 0.96);
        --gantt-soft: #183026;
        --gantt-text: #ffffff;
        --gantt-muted: #c6d8cf;
        --gantt-line: rgba(225, 255, 240, 0.16);
        background:
            radial-gradient(circle at 12% 8%, rgba(20, 184, 166, 0.16), transparent 24rem),
            linear-gradient(135deg, #07110d 0%, #102119 52%, #07110d 100%) !important;
    }

    html[data-pms-theme="dark"] .gantt-page .chart-container,
    html[data-pms-theme="dark"] .gantt-page .chart-body,
    html[data-pms-theme="dark"] .gantt .grid-row {
        background-color: #102119 !important;
        fill: #102119 !important;
    }

    html[data-pms-theme="dark"] .gantt-page .task-list,
    html[data-pms-theme="dark"] .gantt-page .task-list-header,
    html[data-pms-theme="dark"] .gantt-page .status-item {
        background: #183026 !important;
    }

    /* Overflow repair: keep every Gantt detail visible inside a polished scroller. */
    .gantt-page .content-card {
        overflow: visible !important;
    }

    .gantt-page .chart-body {
        border-radius: 0 0 18px 18px;
        overflow: auto !important;
        scrollbar-gutter: stable;
        -webkit-overflow-scrolling: touch;
    }

    .gantt-page .chart-body::-webkit-scrollbar,
    .gantt-page .chart-container::-webkit-scrollbar,
    .gantt-page .timeline-dates::-webkit-scrollbar,
    .gantt-page .nav-tabs::-webkit-scrollbar {
        height: 9px;
        width: 9px;
    }

    .gantt-page .chart-body::-webkit-scrollbar-track,
    .gantt-page .chart-container::-webkit-scrollbar-track,
    .gantt-page .timeline-dates::-webkit-scrollbar-track,
    .gantt-page .nav-tabs::-webkit-scrollbar-track {
        background: rgba(15, 116, 76, 0.08);
        border-radius: 999px;
    }

    .gantt-page .chart-body::-webkit-scrollbar-thumb,
    .gantt-page .chart-container::-webkit-scrollbar-thumb,
    .gantt-page .timeline-dates::-webkit-scrollbar-thumb,
    .gantt-page .nav-tabs::-webkit-scrollbar-thumb {
        background: rgba(15, 116, 76, 0.36);
        border-radius: 999px;
    }

    .gantt-page .chart-container {
        overflow: visible !important;
    }

    .gantt-page #gantt-chart,
    .gantt-page #gantt-chart svg {
        display: block;
        overflow: visible !important;
    }

    .gantt-page .task-list-body {
        scrollbar-width: thin;
    }

    .gantt-page .task-name,
    .gantt-page .task-start,
    .gantt-page .task-duration,
    .gantt-page .stat-card h3,
    .gantt-page .stat-card span,
    .gantt-page .status-item {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .gantt-page .stat-card h3 {
        font-size: clamp(1.35rem, 1.8vw, 1.75rem) !important;
        max-width: 100%;
        word-break: break-word;
    }

    @media (min-width: 768px) {
        .gantt-page .chart-body {
            grid-template-columns: minmax(280px, 320px) minmax(820px, 1fr) !important;
        }

        .gantt-page .chart-container,
        .gantt-page #gantt-chart {
            min-width: 820px !important;
        }
    }

    @media (max-width: 767.98px) {
        .gantt-page .content-card {
            overflow: hidden !important;
        }

        .gantt-page .chart-body {
            border-radius: 0;
        }

        .gantt-page .task-list,
        .gantt-page .chart-container,
        .gantt-page #gantt-chart {
            min-width: 760px !important;
        }

        .gantt-page .chart-container,
        .gantt-page #gantt-chart {
            min-height: 430px !important;
        }
    }

    /* Absolute final Gantt visibility fix: full-screen chart mode and unclipped details. */
    .gantt-page {
        min-width: 0;
    }

    .gantt-page .chart-legend {
        align-items: center;
        flex-wrap: wrap;
    }

    .gantt-page .gantt-fullscreen-toggle {
        align-items: center;
        display: inline-flex;
        gap: 0.5rem;
        min-height: 38px;
        padding: 0.55rem 0.85rem;
        white-space: nowrap;
    }

    .gantt-page .content-card {
        min-width: 0;
    }

    .gantt-page .chart-body {
        max-width: 100%;
        min-height: 560px;
        overflow: auto !important;
        position: relative;
    }

    .gantt-page .chart-container {
        min-height: 560px;
        overflow: visible !important;
        position: relative;
    }

    .gantt-page #gantt-chart {
        min-height: 540px;
        min-width: max(100%, 1120px);
        overflow: visible !important;
        padding-bottom: 48px;
    }

    .gantt-page #gantt-chart svg {
        min-height: 520px;
        overflow: visible !important;
    }

    .gantt-page .popup-wrapper,
    .gantt-page .gantt-popup,
    .gantt-page .details-container {
        max-width: min(360px, calc(100vw - 32px));
        overflow: visible !important;
        white-space: normal;
        z-index: 2105 !important;
    }

    body.gantt-fullscreen-open {
        overflow: hidden !important;
    }

    body.gantt-fullscreen-open .layout-wrapper,
    body.gantt-fullscreen-open .layout-page,
    body.gantt-fullscreen-open .content-wrapper,
    body.gantt-fullscreen-open .container-fluid,
    body.gantt-fullscreen-open .gantt-page {
        overflow: visible !important;
    }

    .gantt-page .content-card.is-gantt-fullscreen {
        background: #f7fbf8 !important;
        border-radius: 0 !important;
        bottom: 0;
        box-shadow: none !important;
        display: flex;
        flex-direction: column;
        left: 0;
        margin: 0 !important;
        overflow: hidden !important;
        padding: 0;
        position: fixed;
        right: 0;
        top: 0;
        width: 100vw;
        z-index: 2050;
    }

    .gantt-page .content-card.is-gantt-fullscreen .chart-header {
        border-radius: 0;
        flex: 0 0 auto;
        padding: 16px 20px;
    }

    .gantt-page .content-card.is-gantt-fullscreen .chart-body {
        border-radius: 0;
        flex: 1 1 auto;
        grid-template-columns: minmax(320px, 360px) minmax(1180px, 1fr) !important;
        min-height: 0 !important;
        overflow: auto !important;
    }

    .gantt-page .content-card.is-gantt-fullscreen .task-list {
        min-height: calc(100vh - 142px);
    }

    .gantt-page .content-card.is-gantt-fullscreen .chart-container,
    .gantt-page .content-card.is-gantt-fullscreen #gantt-chart {
        min-height: calc(100vh - 142px) !important;
        min-width: 1180px !important;
    }

    .gantt-page .content-card.is-gantt-fullscreen #gantt-chart svg {
        min-height: calc(100vh - 170px) !important;
    }

    .gantt-page .content-card.is-gantt-fullscreen .chart-footer {
        border-radius: 0;
        flex: 0 0 auto;
    }

    .gantt-page .content-card.is-gantt-fullscreen .gantt-fullscreen-toggle {
        background: #0f744c !important;
        color: #fff !important;
    }

    html[data-pms-theme="dark"] .gantt-page .content-card.is-gantt-fullscreen {
        background: #07130d !important;
    }

    @media (max-width: 767.98px) {
        .gantt-page .chart-body,
        .gantt-page .chart-container {
            min-height: 520px;
        }

        .gantt-page #gantt-chart {
            min-width: 980px !important;
        }

        .gantt-page .content-card.is-gantt-fullscreen .chart-header {
            align-items: flex-start;
            gap: 10px;
            padding: 12px;
        }

        .gantt-page .content-card.is-gantt-fullscreen .chart-body {
            grid-template-columns: minmax(280px, 320px) minmax(980px, 1fr) !important;
        }

        .gantt-page .content-card.is-gantt-fullscreen .chart-container,
        .gantt-page .content-card.is-gantt-fullscreen #gantt-chart {
            min-width: 980px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Toggle More Tabs
    const toggleBtn = document.getElementById('toggle-more');
    const moreTabs = document.getElementById('more-tabs');
    const ganttCard = document.querySelector('.gantt-page .content-card');
    const fullscreenToggle = document.getElementById('ganttFullscreenToggle');
    let ganttInstance = null;

    if (toggleBtn && moreTabs) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isHidden = moreTabs.classList.contains('d-none');
            moreTabs.classList.toggle('d-none');
            this.innerHTML = isHidden ?
                '<i class="fas fa-ellipsis-h"></i> Less <i class="fas fa-chevron-up"></i>' :
                '<i class="fas fa-ellipsis-h"></i> More <i class="fas fa-chevron-down"></i>';
        });
    }

    function refreshGanttLayout() {
        window.dispatchEvent(new Event('resize'));

        if (ganttInstance && typeof ganttInstance.change_view_mode === 'function') {
            const currentViewMode = (ganttInstance.options && ganttInstance.options.view_mode) || 'Week';
            setTimeout(() => ganttInstance.change_view_mode(currentViewMode), 80);
        }
    }

    function setGanttFullscreen(open) {
        if (!ganttCard || !fullscreenToggle) {
            return;
        }

        ganttCard.classList.toggle('is-gantt-fullscreen', open);
        document.body.classList.toggle('gantt-fullscreen-open', open);
        fullscreenToggle.setAttribute('aria-pressed', open ? 'true' : 'false');
        fullscreenToggle.innerHTML = open
            ? '<i class="fas fa-compress"></i><span>Exit Full Screen</span>'
            : '<i class="fas fa-expand"></i><span>Full Screen</span>';

        setTimeout(refreshGanttLayout, 120);
    }

    if (fullscreenToggle) {
        fullscreenToggle.addEventListener('click', function () {
            setGanttFullscreen(!ganttCard.classList.contains('is-gantt-fullscreen'));
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && ganttCard && ganttCard.classList.contains('is-gantt-fullscreen')) {
            setGanttFullscreen(false);
        }
    });

    // Load Gantt Chart
    fetch("{{ route('projects.gantt-tasks', $project->id) }}")
        .then(response => response.json())
        .then(tasks => {
            const chartContainer = document.getElementById("gantt-chart");
            if (!tasks.length) {
                chartContainer.innerHTML = `
                    <div class="empty-chart">
                        <i class="fas fa-tasks"></i>
                        <p>No tasks to display in Gantt chart</p>
                        <span class="text-muted">Add tasks to the project to see them here</span>
                    </div>
                `;
                return;
            }

            ganttInstance = new Gantt("#gantt-chart", tasks, {
                view_mode: 'Week',
                custom_popup_html: task => `
                    <div class="gantt-popup">
                        <div class="popup-title"><strong>${task.name}</strong></div>
                        <div class="popup-row"><span class="popup-label">Start:</span> ${task.start}</div>
                        <div class="popup-row"><span class="popup-label">End:</span> ${task.end}</div>
                        <div class="popup-row"><span class="popup-label">Progress:</span> ${task.progress}%</div>
                        <div class="popup-row"><span class="popup-label">Status:</span> ${task.progress === 100 ? '✅ Completed' : '🔄 In Progress'}</div>
                    </div>
                `
            });
            refreshGanttLayout();
        })
        .catch(error => {
            console.error("Gantt Chart Load Failed:", error);
            document.getElementById("gantt-chart").innerHTML = `
                <div class="empty-chart error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load chart data</p>
                    <span class="text-muted">Please refresh the page or try again later</span>
                </div>
            `;
        });
});

// Additional styles for Gantt popup and empty state
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .gantt-popup {
            padding: 8px 12px;
            font-size: 0.85rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .popup-title {
            font-size: 1rem;
            margin-bottom: 6px;
            color: #07130d;
        }
        .popup-row {
            display: flex;
            gap: 8px;
            padding: 2px 0;
            color: #5a6e63;
        }
        .popup-label {
            font-weight: 600;
            min-width: 60px;
            color: #07130d;
        }
        .empty-chart {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 350px;
            color: #8ba198;
        }
        .empty-chart i {
            font-size: 3rem;
            color: #a7f3d0;
            margin-bottom: 16px;
        }
        .empty-chart.error i {
            color: #fca5a5;
        }
        .empty-chart p {
            font-size: 1.1rem;
            font-weight: 600;
            color: #07130d;
            margin: 0;
        }
        html[data-pms-theme="dark"] .empty-chart p {
            color: #ffffff;
        }
        html[data-pms-theme="dark"] .gantt-popup {
            background: #183026;
            color: #d9f1e4;
        }
        html[data-pms-theme="dark"] .popup-title {
            color: #ffffff;
        }
        html[data-pms-theme="dark"] .popup-label {
            color: #d9f1e4;
        }
        html[data-pms-theme="dark"] .popup-row {
            color: #8ba198;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush
@endsection
