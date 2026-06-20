@extends('admin.layout.app')

@section('title', 'Burndown Chart - ' . $project->name)

@section('content')
<div class="burndown-chart-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-fire"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / <strong>Burndown Chart</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div>
                    <h1>Burndown Chart</h1>
                    <p>Track sprint progress for <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div>
                    <h3>{{ count($actual) > 0 ? $actual[0] : 0 }}</h3>
                    <span>Total Tasks</span>
                    <p class="stat-sub">Initial backlog</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3>{{ count($actual) > 0 ? $actual[0] - end($actual) : 0 }}</h3>
                    <span>Completed</span>
                    <p class="stat-sub">Tasks done</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>{{ count($actual) > 0 ? end($actual) : 0 }}</h3>
                    <span>Remaining</span>
                    <p class="stat-sub">Current backlog</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <h3>
                        @php
                            $totalDays = count($labels) > 0 ? count($labels) - 1 : 0;
                            $totalTasks = count($actual) > 0 ? $actual[0] - end($actual) : 0;
                            $avgDaily = $totalDays > 0 ? round($totalTasks / $totalDays, 1) : 0;
                            echo $avgDaily;
                        @endphp
                    </h3>
                    <span>Avg. Daily Velocity</span>
                    <p class="stat-sub">Tasks per day</p>
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
                    <a class="nav-link" href="{{ route('projects.gantt', $project->id) }}">
                        <i class="fas fa-chart-bar"></i> Gantt Chart
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('projects.burndown', $project->id) }}">
                        <i class="fas fa-fire"></i> Burndown Chart
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
                    <a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">
                        <i class="fas fa-history"></i> Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tickets.index') }}">
                        <i class="fas fa-ticket-alt"></i> Tickets
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content Card -->
        <div class="content-card">
            <div class="chart-header">
                <div class="chart-title">
                    <div class="chart-title-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h4>Burndown Chart</h4>
                        <span class="muted">Visualize sprint progress and velocity</span>
                    </div>
                </div>
                <div class="chart-legend-info">
                    <div class="legend-item">
                        <span class="legend-color actual"></span>
                        <span>Actual Progress</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color ideal"></span>
                        <span>Ideal Progress</span>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-plus"></i> Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') ?? $start->format('Y-m-d') }}">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-check"></i> End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') ?? $end->format('Y-m-d') }}">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="{{ route('projects.burndown', $project->id) }}" class="btn btn-outline">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Chart Container -->
            <div class="chart-container">
                <div id="burndownChart" class="chart-wrapper"></div>
            </div>

            <!-- Chart Data Summary -->
            <div class="chart-summary">
                <div class="summary-item">
                    <span class="summary-label">Sprint Duration</span>
                    <span class="summary-value">{{ count($labels) }} days</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Starting Tasks</span>
                    <span class="summary-value">{{ count($actual) > 0 ? $actual[0] : 0 }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Remaining Tasks</span>
                    <span class="summary-value">{{ count($actual) > 0 ? end($actual) : 0 }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Completion Rate</span>
                    <span class="summary-value">
                        @php
                            $startTasks = count($actual) > 0 ? $actual[0] : 0;
                            $endTasks = count($actual) > 0 ? end($actual) : 0;
                            $completed = $startTasks - $endTasks;
                            $rate = $startTasks > 0 ? round(($completed / $startTasks) * 100, 1) : 0;
                            echo $rate . '%';
                        @endphp
                    </span>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-fire text-primary"></i>
                <span>{{ count($actual) > 0 ? $actual[0] - end($actual) : 0 }}</span> Tasks Completed
            </div>
            <div class="status-item">
                <i class="fas fa-clock text-warning"></i>
                <span>{{ count($actual) > 0 ? end($actual) : 0 }}</span> Tasks Remaining
            </div>
            <div class="status-item">
                <i class="fas fa-chart-line text-success"></i>
                <span>{{ $avgDaily ?? 0 }}</span> Avg. Daily Velocity
            </div>
            <div class="status-item">
                <i class="fas fa-calendar-alt text-info"></i>
                {{ now()->format('M d, Y') }}
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM BURNDOWN CHART PAGE - GREEN/TEAL THEME ===== */
    .burndown-chart-page {
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

    .chart-legend-info {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #5a6e63;
        font-weight: 500;
    }

    .legend-color {
        width: 20px;
        height: 4px;
        border-radius: 2px;
    }

    .legend-color.actual {
        background: #0f744c;
    }

    .legend-color.ideal {
        background: #d1d5db;
        border-top: 2px dashed #9ca3af;
        height: 0;
    }

    /* Filter Section */
    .filter-section {
        padding: 20px 28px;
        border-bottom: 1px solid rgba(15, 116, 76, .08);
        background: #fafefb;
    }

    .filter-form {
        display: flex;
        gap: 16px;
        align-items: end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #5a6e63;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group label i {
        margin-right: 6px;
        color: #34d399;
    }

    .filter-group .form-control {
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .18);
        padding: 12px 16px;
        font-weight: 500;
        font-size: 1rem;
        min-height: 48px;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .filter-group .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* Chart Container */
    .chart-container {
        padding: 24px 28px;
        background: #ffffff;
    }

    .chart-wrapper {
        height: 400px;
        width: 100%;
    }

    /* Chart Summary */
    .chart-summary {
        padding: 18px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .summary-label {
        font-size: 0.85rem;
        color: #8ba198;
        font-weight: 500;
    }

    .summary-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #07130d;
        background: #f0f9f4;
        padding: 4px 14px;
        border-radius: 20px;
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
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-actions {
            flex-wrap: wrap;
        }
        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .chart-legend-info {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .burndown-chart-page {
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
        .chart-container {
            padding: 16px;
        }
        .chart-wrapper {
            height: 300px;
        }
        .chart-summary {
            flex-direction: column;
            align-items: flex-start;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .filter-section {
            padding: 16px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .burndown-chart-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .status-bar,
    html[data-pms-theme="dark"] .nav-tabs-wrapper {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .chart-title h4,
    html[data-pms-theme="dark"] .stat-card h3,
    html[data-pms-theme="dark"] .summary-value {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-section {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .filter-group .form-control {
        background: #183026;
        color: #ffffff;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .chart-container {
        background: #102119;
    }

    html[data-pms-theme="dark"] .chart-summary {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .summary-value {
        background: #183026;
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

    html[data-pms-theme="dark"] .chart-header {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .chart-title-icon {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .legend-item {
        color: #d9f1e4;
    }
</style>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle More Tabs
        const toggleBtn = document.getElementById('toggle-more');
        const moreTabs = document.getElementById('more-tabs');

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

        // Initialize ApexCharts
        var options = {
            chart: {
                type: 'line',
                height: 400,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            series: [
                {
                    name: 'Actual Progress',
                    data: @json($actual),
                    color: '#0f744c'
                },
                {
                    name: 'Ideal Progress',
                    data: @json($ideal),
                    color: '#9ca3af'
                }
            ],
            stroke: {
                curve: 'smooth',
                width: 3,
                dashArray: [0, 8]
            },
            markers: {
                size: 6,
                colors: ['#0f744c', '#9ca3af'],
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: {
                    size: 8
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'horizontal',
                    shadeIntensity: 0.5,
                    gradientToColors: ['#10b981', '#d1d5db'],
                    inverseColors: false,
                    opacityFrom: 0.6,
                    opacityTo: 0.1
                }
            },
            grid: {
                borderColor: '#e5e7eb',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                padding: {
                    top: 10,
                    right: 20,
                    bottom: 10,
                    left: 10
                }
            },
            xaxis: {
                categories: @json($labels),
                title: {
                    text: 'Date',
                    style: {
                        fontSize: '14px',
                        fontWeight: 600,
                        color: '#6b7280'
                    }
                },
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: 500,
                        colors: '#6b7280'
                    },
                    rotate: -45,
                    rotateAlways: false,
                    hideOverlappingLabels: true
                }
            },
            yaxis: {
                title: {
                    text: 'Remaining Tasks',
                    style: {
                        fontSize: '14px',
                        fontWeight: 600,
                        color: '#6b7280'
                    }
                },
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: 500,
                        colors: '#6b7280'
                    }
                },
                min: 0,
                forceNiceScale: true
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontWeight: 600,
                labels: {
                    colors: '#6b7280'
                },
                markers: {
                    width: 20,
                    height: 20,
                    radius: 4
                },
                itemMargin: {
                    horizontal: 20,
                    vertical: 5
                }
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '14px'
                },
                y: {
                    formatter: function(value) {
                        return value + ' tasks remaining';
                    }
                },
                x: {
                    formatter: function(value) {
                        return 'Date: ' + value;
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: {
                        height: 300
                    },
                    markers: {
                        size: 4
                    },
                    legend: {
                        fontSize: '12px'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#burndownChart"), options);
        chart.render();

        // Dark mode support
        function updateChartTheme() {
            const isDark = document.documentElement.getAttribute('data-pms-theme') === 'dark';
            const textColor = isDark ? '#d9f1e4' : '#6b7280';
            const gridColor = isDark ? 'rgba(122, 240, 181, 0.15)' : '#e5e7eb';

            chart.updateOptions({
                grid: {
                    borderColor: gridColor
                },
                xaxis: {
                    title: {
                        style: {
                            color: textColor
                        }
                    },
                    labels: {
                        style: {
                            colors: textColor
                        }
                    }
                },
                yaxis: {
                    title: {
                        style: {
                            color: textColor
                        }
                    },
                    labels: {
                        style: {
                            colors: textColor
                        }
                    }
                },
                legend: {
                    labels: {
                        colors: textColor
                    }
                }
            });
        }

        // Watch for theme changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'data-pms-theme') {
                    updateChartTheme();
                }
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-pms-theme']
        });
    });
</script>
@endsection
@endsection
