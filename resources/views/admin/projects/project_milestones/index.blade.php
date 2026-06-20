@extends('admin.layout.app')

@section('title', 'Project Milestones - ' . $project->name)

@section('content')
<div class="project-milestones-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-flag-checkered"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / Milestones</span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-flag-checkered"></i>
                </div>
                <div>
                    <h1>Project Milestones</h1>
                    <p>Track and manage milestones for <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMilestoneModal">
                    <i class="fas fa-plus-circle"></i> Create Milestone
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div>
                    <h3>{{ $milestones->count() }}</h3>
                    <span>Total Milestones</span>
                    <p class="stat-sub">Project milestones</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3>{{ $milestones->where('status', 'completed')->count() }}</h3>
                    <span>Completed</span>
                    <p class="stat-sub">Finished milestones</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                <div>
                    <h3>{{ $milestones->where('status', 'in_progress')->count() }}</h3>
                    <span>In Progress</span>
                    <p class="stat-sub">Active milestones</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>{{ $milestones->where('status', 'pending')->count() }}</h3>
                    <span>Pending</span>
                    <p class="stat-sub">Not yet started</p>
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
                    <a class="nav-link active" href="{{ route('milestones.index', $project->id) }}">
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

        <!-- Main Content Card -->
        <div class="content-card">
            <!-- Table Header -->
            <div class="table-header">
                <div class="table-title">
                    <div class="table-title-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <h4>Milestone List</h4>
                        <span class="muted">{{ $milestones->count() }} milestones in this project</span>
                    </div>
                </div>
                <div class="table-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="milestoneSearch" placeholder="Search milestones..." />
                    </div>
                </div>
            </div>

            <!-- Milestone Table -->
            <div class="table-responsive">
                <table id="mileTable" class="milestone-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Title</th>
                            <th><i class="fas fa-money-bill"></i> Cost</th>
                            <th><i class="fas fa-circle"></i> Status</th>
                            <th><i class="fas fa-wallet"></i> Budget</th>
                            <th><i class="fas fa-calendar-plus"></i> Start Date</th>
                            <th><i class="fas fa-calendar-check"></i> End Date</th>
                            <th><i class="fas fa-clock"></i> Created</th>
                            <th class="text-end"><i class="fas fa-cog"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($milestones as $milestone)
                            <tr>
                                <td>
                                    <div class="milestone-title">
                                        <div class="milestone-icon">
                                            <i class="fas fa-flag"></i>
                                        </div>
                                        <div>
                                            <div class="title-text">{{ $milestone->title }}</div>
                                            <small class="text-muted">{{ Str::limit($milestone->summary, 50) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="cost-badge">
                                        <i class="fas fa-coins"></i>
                                        {{ $milestone->cost ? number_format($milestone->cost, 2) : '—' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'primary',
                                            'completed' => 'success'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'in_progress' => 'In Progress',
                                            'completed' => 'Completed'
                                        ];
                                        $color = $statusColors[$milestone->status] ?? 'secondary';
                                    @endphp
                                    <span class="status-badge status-{{ $color }}">
                                        <i class="fas fa-{{ $milestone->status == 'completed' ? 'check' : ($milestone->status == 'in_progress' ? 'sync-alt' : 'clock') }}"></i>
                                        {{ $statusLabels[$milestone->status] ?? $milestone->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="budget-badge {{ $milestone->add_to_budget ? 'yes' : 'no' }}">
                                        <i class="fas fa-{{ $milestone->add_to_budget ? 'check-circle' : 'times-circle' }}"></i>
                                        {{ $milestone->add_to_budget ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $milestone->start_date ? \Carbon\Carbon::parse($milestone->start_date)->format('M d, Y') : '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $milestone->end_date ? \Carbon\Carbon::parse($milestone->end_date)->format('M d, Y') : '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-clock"></i>
                                        {{ $milestone->created_at->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('milestones.destroy', $milestone->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this milestone?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete-btn" title="Delete Milestone">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-flag-checkered"></i>
                                        <h5>No Milestones Found</h5>
                                        <p>Create your first milestone to track project progress</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMilestoneModal">
                                            <i class="fas fa-plus-circle"></i> Create Milestone
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <div class="footer-info">
                    <i class="fas fa-info-circle"></i>
                    Showing {{ $milestones->count() }} milestone(s)
                </div>
                <div class="footer-status">
                    <span class="status-dot"></span>
                    <span>Last updated: {{ $milestones->isNotEmpty() ? $milestones->first()->updated_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-flag-checkered"></i> <span>{{ $milestones->count() }}</span> Total Milestones
            </div>
            <div class="status-item">
                <i class="fas fa-check-circle text-success"></i> <span>{{ $milestones->where('status', 'completed')->count() }}</span> Completed
            </div>
            <div class="status-item">
                <i class="fas fa-spinner text-primary"></i> <span>{{ $milestones->where('status', 'in_progress')->count() }}</span> In Progress
            </div>
            <div class="status-item">
                <i class="fas fa-clock text-warning"></i> <span>{{ $milestones->where('status', 'pending')->count() }}</span> Pending
            </div>
        </div>
    </div>
</div>

<!-- Create Milestone Modal -->
<div class="modal fade" id="createMilestoneModal" tabindex="-1" aria-labelledby="milestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('milestones.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-flag-checkered text-primary me-2"></i>
                        Create New Milestone
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Milestone Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required placeholder="Enter milestone title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Milestone Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" name="cost" class="form-control" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Milestone Summary <span class="text-danger">*</span></label>
                                <textarea name="summary" class="form-control" rows="3" required placeholder="Enter milestone summary"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="add_to_budget" value="1" id="budgetSwitch">
                                <label class="form-check-label" for="budgetSwitch">
                                    <i class="fas fa-wallet me-1"></i> Add Cost To Project Budget
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Milestone
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    /* ===== PROJECT MILESTONES PAGE - GREEN/TEAL THEME ===== */
    .project-milestones-page {
        padding: 30px 0;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #07130d;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 22px;
        border-radius: 18px;
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 25px;
        color: #0f744c;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .breadcrumb i {
        margin-right: 10px;
        color: #34d399;
    }

    .breadcrumb a {
        color: #0f744c;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb a:hover {
        color: #10b981;
    }

    /* Header Card */
    .header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 65px;
        height: 65px;
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .2);
    }

    .header-card h1 {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #07130d;
    }

    .header-card p {
        color: #52645a;
        font-size: 15px;
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
        padding: 12px 22px;
        border-radius: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
        text-decoration: none;
        font-size: 0.9rem;
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
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #5a6e63;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link i {
        font-size: 0.9rem;
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

    /* Table Header */
    .table-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .table-title-icon {
        width: 44px;
        height: 44px;
        background: #e7f5ee;
        color: #0f744c;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .table-title h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .table-title .muted {
        font-size: 0.8rem;
        color: #8ba198;
    }

    .table-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding: 10px 16px 10px 40px;
        border-radius: 40px;
        border: 1px solid rgba(15, 116, 76, .18);
        outline: none;
        font-weight: 500;
        min-width: 250px;
        transition: all 0.2s;
        background: #fafefb;
    }

    .search-box input:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #0f744c;
    }

    /* Table Styles */
    .table-responsive {
        padding: 8px 20px 20px 20px;
        overflow-x: auto;
    }

    .milestone-table {
        width: 100%;
        min-width: 1000px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .milestone-table thead th {
        text-align: left;
        padding: 14px 18px;
        color: #5a6e63;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: transparent;
        border-bottom: 2px solid rgba(15, 116, 76, .1);
    }

    .milestone-table thead th i {
        margin-right: 8px;
        color: #34d399;
    }

    .milestone-table tbody td {
        background: #ffffff;
        padding: 16px 18px;
        border-top: 1px solid rgba(15, 116, 76, .08);
        border-bottom: 1px solid rgba(15, 116, 76, .08);
        vertical-align: middle;
        transition: all 0.2s ease;
    }

    .milestone-table tbody td:first-child {
        border-left: 1px solid rgba(15, 116, 76, .08);
        border-radius: 16px 0 0 16px;
    }

    .milestone-table tbody td:last-child {
        border-right: 1px solid rgba(15, 116, 76, .08);
        border-radius: 0 16px 16px 0;
    }

    .milestone-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(15, 116, 76, .15);
    }

    /* Milestone Title */
    .milestone-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .milestone-icon {
        width: 38px;
        height: 38px;
        background: #d1fae5;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f744c;
    }

    .title-text {
        font-weight: 600;
        color: #07130d;
    }

    .milestone-title small {
        font-size: 0.7rem;
        color: #8ba198;
        display: block;
    }

    /* Cost Badge */
    .cost-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #f0f9f4;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #0f744c;
    }

    .cost-badge i {
        font-size: 0.75rem;
        color: #34d399;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .status-badge i {
        font-size: 0.7rem;
    }

    .status-success {
        background: #d1fae5;
        color: #065f46;
    }

    .status-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-secondary {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Budget Badge */
    .budget-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .budget-badge.yes {
        background: #d1fae5;
        color: #065f46;
    }

    .budget-badge.no {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Date Cell */
    .date-cell {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #5a6e63;
    }

    .date-cell i {
        color: #0f744c;
        font-size: 0.8rem;
    }

    /* Action Button */
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        background: #f0f9f4;
        color: #0f744c;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        background: #d1fae5;
        transform: scale(1.05);
    }

    .action-btn.delete-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Table Footer */
    .table-footer {
        padding: 16px 24px;
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .footer-info {
        font-size: 0.85rem;
        color: #5a6e63;
    }

    .footer-info i {
        color: #0f744c;
        margin-right: 8px;
    }

    .footer-status {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8rem;
        color: #8ba198;
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0% { opacity: 1; }
        50% { opacity: 0.3; }
        100% { opacity: 1; }
    }

    /* Status Bar */
    .status-bar {
        margin-top: 24px;
        background: white;
        border-radius: 20px;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid rgba(15, 116, 76, .1);
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #5a6e63;
    }

    .status-item i {
        font-size: 1rem;
    }

    .status-item span {
        font-weight: 700;
        color: #07130d;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 3.5rem;
        color: #a7f3d0;
        margin-bottom: 16px;
    }

    .empty-state h5 {
        color: #0f744c;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #8ba198;
        margin-bottom: 20px;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .12);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        padding: 20px 24px;
    }

    .modal-header .modal-title {
        font-weight: 700;
        font-size: 1.2rem;
        color: #07130d;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        padding: 16px 24px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #07130d;
        margin-bottom: 6px;
    }

    .form-label .text-danger {
        color: #dc2626;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .18);
        padding: 10px 16px;
        transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .input-group-text {
        background: #f0f9f4;
        border: 1px solid rgba(15, 116, 76, .18);
        border-radius: 12px 0 0 12px;
        color: #0f744c;
    }

    .form-check-input:checked {
        background-color: #0f744c;
        border-color: #0f744c;
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
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .table-header {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box input {
            min-width: 180px;
            width: 100%;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .table-responsive {
            padding: 8px 12px 12px 12px;
        }
        .modal-dialog {
            margin: 10px;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#mileTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search milestones..."
            }
        });

        // Custom search handler
        const searchInput = document.getElementById('milestoneSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                $('#mileTable').DataTable().search(this.value).draw();
            });
        }
    });

    // Toggle More Tabs
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endpush

@endsection
