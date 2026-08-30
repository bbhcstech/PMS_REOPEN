@extends('admin.layout.app')

@section('title', 'Products & Projects Directory')

@section('content')
<style>
    .project-card-header {
        border-bottom: 1px solid #f1f5f9;
    }
    .avatar-group .avatar {
        transition: transform 0.2s ease;
        margin-left: -8px;
    }
    .avatar-group .avatar:first-child {
        margin-left: 0;
    }
    .avatar-group .avatar:hover {
        transform: translateY(-2px);
        z-index: 5;
    }
    .cost-badge {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        letter-spacing: -0.01em;
    }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Top Header & Breadcrumbs -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="bx bx-package text-primary me-2 align-middle"></i>Products & Projects
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Projects</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Products</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
                <a href="{{ route('admin.deals.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                    <i class="bx bx-target-lock me-1"></i> View Deals
                </a>
                <a href="{{ route('projects.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bx bx-plus-circle me-1"></i> Add Project
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Projects -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">Total Products / Projects</span>
                        <h4 class="fw-bold text-dark mb-0">{{ $stats['total_projects'] }}</h4>
                        <small class="text-muted">{{ $stats['in_progress_count'] }} in progress • {{ $stats['completed_count'] }} completed</small>
                    </div>
                    <div class="p-3 rounded-4 bg-primary-subtle text-primary">
                        <i class="bx bx-cube fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Home Projects Count -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">🏠 Home Projects (In-House)</span>
                        <h4 class="fw-bold text-info mb-0">{{ $stats['home_projects_count'] }}</h4>
                        <small class="text-muted">Total Cost: ${{ number_format($stats['home_cost'], 2) }}</small>
                    </div>
                    <div class="p-3 rounded-4 bg-info-subtle text-info">
                        <i class="bx bx-home-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Projects Count -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">💼 Client Projects (Client)</span>
                        <h4 class="fw-bold text-success mb-0">{{ $stats['client_projects_count'] }}</h4>
                        <small class="text-muted">Total Cost: ${{ number_format($stats['client_cost'], 2) }}</small>
                    </div>
                    <div class="p-3 rounded-4 bg-success-subtle text-success">
                        <i class="bx bx-buildings fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Deal Value / Budget -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">💰 Total Pipeline &amp; Deal Value</span>
                        <h4 class="fw-bold text-dark mb-0">${{ number_format($stats['total_deal_cost'], 2) }}</h4>
                        <small class="text-success"><i class="bx bx-trending-up me-1"></i>Combined Project Valuation</small>
                    </div>
                    <div class="p-3 rounded-4 bg-warning-subtle text-warning">
                        <i class="bx bx-dollar-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section & Navigation Tabs -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3 align-items-center">
                <div class="col-lg-5 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by project name, code, client or description..." value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="in progress" {{ ($status ?? '') === 'in progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="not started" {{ ($status ?? '') === 'not started' ? 'selected' : '' }}>Not Started</option>
                        <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="on hold" {{ ($status ?? '') === 'on hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="canceled" {{ ($status ?? '') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($priority ?? '') === 'all' ? 'selected' : '' }}>All Priorities</option>
                        <option value="high" {{ ($priority ?? '') === 'high' ? 'selected' : '' }}>High Priority</option>
                        <option value="medium" {{ ($priority ?? '') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                        <option value="low" {{ ($priority ?? '') === 'low' ? 'selected' : '' }}>Low Priority</option>
                        <option value="urgent" {{ ($priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3 rounded-pill flex-grow-1">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                    @if(!empty($search) || ($status ?? 'all') !== 'all' || ($priority ?? 'all') !== 'all')
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filters">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TABLE 1: HOME PROJECTS (IN-HOUSE / INTERNAL PRODUCTS)                 -->
    <!-- ===================================================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between project-card-header">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center">
                    <i class="bx bx-home-alt fs-4"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Home Projects</h5>
                    <small class="text-muted">In-house products and internal company projects</small>
                </div>
            </div>
            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2 fw-semibold">
                {{ count($homeProjects) }} Home Project(s)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-secondary text-uppercase fw-semibold" style="font-size: 0.76rem;">
                        <th class="ps-4">Project / Product</th>
                        <th>Assigned Team</th>
                        <th>Progress</th>
                        <th>Timeline</th>
                        <th>Cost / Value</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeProjects as $project)
                        @php
                            $progPercent = max(0, min(100, (int) ($project->completion_percent ?? 0)));
                            $statusClass = match(strtolower(trim((string) $project->status))) {
                                'completed' => 'bg-success-subtle text-success border-success-subtle',
                                'in progress', 'in_progress' => 'bg-primary-subtle text-primary border-primary-subtle',
                                'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
                                'on hold', 'on_hold' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                'canceled', 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                default => 'bg-light text-dark border-secondary-subtle'
                            };
                            $priorityClass = match(strtolower(trim((string) $project->priority))) {
                                'high', 'urgent' => 'text-danger bg-danger-subtle',
                                'medium' => 'text-warning bg-warning-subtle',
                                default => 'text-secondary bg-light'
                            };
                        @endphp
                        <tr>
                            <!-- Project / Product Name -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bx bx-cube-alt fs-3"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.show', $project->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $project->name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.72rem;">{{ $project->project_code ?: ('PRJ-' . $project->id) }}</span>
                                            @if($project->priority)
                                                <span class="badge rounded-pill px-2 {{ $priorityClass }}" style="font-size: 0.68rem;">{{ ucfirst($project->priority) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Assigned Team -->
                            <td>
                                @if($project->users && $project->users->count() > 0)
                                    <div class="avatar-group d-flex align-items-center">
                                        @foreach($project->users->take(4) as $member)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" 
                                                  title="{{ $member->name }} ({{ $member->employeeDetail?->designation?->name ?: 'Member' }})"
                                                  style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </span>
                                        @endforeach
                                        @if($project->users->count() > 4)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-light text-muted d-inline-flex align-items-center justify-content-center small fw-bold" style="width: 32px; height: 32px; font-size: 0.72rem;">
                                                +{{ $project->users->count() - 4 }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $project->users->count() }} member(s)</small>
                                @else
                                    <span class="text-muted small fst-italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Progress -->
                            <td style="min-width: 150px;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-dark">{{ $progPercent }}%</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $project->tasks_count ?? 0 }} task(s)</small>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-info rounded-pill" role="progressbar" style="width: {{ $progPercent }}%" aria-valuenow="{{ $progPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>

                            <!-- Timeline -->
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <i class="bx bx-calendar-event text-info me-1"></i>
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'Not set' }}
                                </div>
                                <div class="small text-muted">
                                    to {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : ($project->without_deadline ? 'No deadline' : 'Ongoing') }}
                                </div>
                            </td>

                            <!-- Cost / Value (Fetched from Deals / Budget) -->
                            <td>
                                <div class="fw-bold text-dark fs-6 cost-badge">
                                    ${{ number_format($project->calculated_cost, 2) }}
                                </div>
                                @if($project->cost_source === 'deal')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.68rem;" title="Fetched automatically from Deals section">
                                        <i class="bx bx-check-circle me-1"></i>Deal: {{ Str::limit($project->deal_reference, 16) }}
                                    </span>
                                @elseif($project->cost_source === 'budget')
                                    <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 0.68rem;">
                                        Budget Allocated
                                    </span>
                                @elseif($project->cost_source === 'expenses')
                                    <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 0.68rem;">
                                        Expenses Sum
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-2" style="font-size: 0.68rem;">
                                        Unspecified
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge {{ $statusClass }} border rounded-pill px-3 py-1 fw-semibold text-capitalize">
                                    {{ str_replace('_', ' ', $project->status ?: 'In Progress') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.show', $project->id) }}">
                                                <i class="bx bx-show text-primary me-2"></i> View Project Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('tasks.index', ['project_id' => $project->id]) }}">
                                                <i class="bx bx-check-square text-info me-2"></i> Manage Tasks
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.edit', $project->id) }}">
                                                <i class="bx bx-edit text-warning me-2"></i> Edit Project
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bx bx-home-alt fs-1 d-block mb-2 text-secondary"></i>
                                No Home Projects found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TABLE 2: CLIENT PROJECTS (CLIENT COMMISSIONED PRODUCTS)               -->
    <!-- ===================================================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between project-card-header">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center">
                    <i class="bx bx-buildings fs-4"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Client Projects</h5>
                    <small class="text-muted">Client-commissioned products, commercial builds &amp; client contracts</small>
                </div>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                {{ count($clientProjects) }} Client Project(s)
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-secondary text-uppercase fw-semibold" style="font-size: 0.76rem;">
                        <th class="ps-4">Project / Product</th>
                        <th>Client Details</th>
                        <th>Assigned Team</th>
                        <th>Progress</th>
                        <th>Timeline</th>
                        <th>Deal Cost / Value</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientProjects as $project)
                        @php
                            $progPercent = max(0, min(100, (int) ($project->completion_percent ?? 0)));
                            $statusClass = match(strtolower(trim((string) $project->status))) {
                                'completed' => 'bg-success-subtle text-success border-success-subtle',
                                'in progress', 'in_progress' => 'bg-primary-subtle text-primary border-primary-subtle',
                                'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
                                'on hold', 'on_hold' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                'canceled', 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                default => 'bg-light text-dark border-secondary-subtle'
                            };
                            $priorityClass = match(strtolower(trim((string) $project->priority))) {
                                'high', 'urgent' => 'text-danger bg-danger-subtle',
                                'medium' => 'text-warning bg-warning-subtle',
                                default => 'text-secondary bg-light'
                            };
                        @endphp
                        <tr>
                            <!-- Project / Product Name -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bx bx-briefcase-alt fs-3"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.show', $project->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $project->name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.72rem;">{{ $project->project_code ?: ('PRJ-' . $project->id) }}</span>
                                            @if($project->priority)
                                                <span class="badge rounded-pill px-2 {{ $priorityClass }}" style="font-size: 0.68rem;">{{ ucfirst($project->priority) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Client Details -->
                            <td>
                                @if($project->client)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($project->client->name ?? 'C', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $project->client->name }}</div>
                                            <small class="text-muted">{{ $project->client->company_name ?: ($project->client->email ?: 'Client') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">No Client Profile</span>
                                @endif
                            </td>

                            <!-- Assigned Team -->
                            <td>
                                @if($project->users && $project->users->count() > 0)
                                    <div class="avatar-group d-flex align-items-center">
                                        @foreach($project->users->take(4) as $member)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" 
                                                  title="{{ $member->name }} ({{ $member->employeeDetail?->designation?->name ?: 'Member' }})"
                                                  style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </span>
                                        @endforeach
                                        @if($project->users->count() > 4)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-light text-muted d-inline-flex align-items-center justify-content-center small fw-bold" style="width: 32px; height: 32px; font-size: 0.72rem;">
                                                +{{ $project->users->count() - 4 }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $project->users->count() }} member(s)</small>
                                @else
                                    <span class="text-muted small fst-italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Progress -->
                            <td style="min-width: 140px;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-dark">{{ $progPercent }}%</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $project->tasks_count ?? 0 }} task(s)</small>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $progPercent }}%" aria-valuenow="{{ $progPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>

                            <!-- Timeline -->
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <i class="bx bx-calendar-event text-success me-1"></i>
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'Not set' }}
                                </div>
                                <div class="small text-muted">
                                    to {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : ($project->without_deadline ? 'No deadline' : 'Ongoing') }}
                                </div>
                            </td>

                            <!-- Cost / Deal Value (Fetched from Deals Section) -->
                            <td>
                                <div class="fw-bold text-dark fs-6 cost-badge">
                                    ${{ number_format($project->calculated_cost, 2) }}
                                </div>
                                @if($project->cost_source === 'deal')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.68rem;" title="Fetched automatically from Deals section">
                                        <i class="bx bx-check-circle me-1"></i>From Deal: {{ Str::limit($project->deal_reference, 16) }}
                                    </span>
                                @elseif($project->cost_source === 'budget')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2" style="font-size: 0.68rem;">
                                        Budget Allocated
                                    </span>
                                @elseif($project->cost_source === 'expenses')
                                    <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 0.68rem;">
                                        Expenses Sum
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-2" style="font-size: 0.68rem;">
                                        Pending Deal Value
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge {{ $statusClass }} border rounded-pill px-3 py-1 fw-semibold text-capitalize">
                                    {{ str_replace('_', ' ', $project->status ?: 'In Progress') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.show', $project->id) }}">
                                                <i class="bx bx-show text-primary me-2"></i> View Project Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('tasks.index', ['project_id' => $project->id]) }}">
                                                <i class="bx bx-check-square text-info me-2"></i> Manage Tasks
                                            </a>
                                        </li>
                                        @if($project->client)
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('clients.show', $project->client_id) }}">
                                                    <i class="bx bx-user text-success me-2"></i> View Client Profile
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.edit', $project->id) }}">
                                                <i class="bx bx-edit text-warning me-2"></i> Edit Project
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bx bx-buildings fs-1 d-block mb-2 text-secondary"></i>
                                No Client Projects found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
