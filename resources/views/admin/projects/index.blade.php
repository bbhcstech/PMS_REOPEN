@extends('admin.layout.app')

@section('title', 'Projects')

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
    $priorityOptions = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];
    $progressOptions = [
        '0-20' => '0% - 20%',
        '21-40' => '21% - 40%',
        '41-60' => '41% - 60%',
        '61-80' => '61% - 80%',
        '81-99' => '81% - 99%',
        '100-100' => '100%',
    ];
@endphp

<main class="projects-page">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-folder-open"></i>
        <span>Dashboard / <strong>Projects</strong></span>
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-project-diagram"></i>
            </div>
            <div>
                <h1>Projects</h1>
                <p>Manage and track all your projects across the organization</p>
            </div>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-icon" data-project-search-focus title="Search">
                <i class="fas fa-search"></i>
            </button>
            <a href="{{ route('projects.index') }}" class="btn-icon active" title="List View" aria-label="List View">
                <i class="fas fa-list-ul"></i>
            </a>
            <a href="{{ route('projects.calendar') }}" class="btn-icon" title="Calendar">
                <i class="fas fa-calendar-alt"></i>
            </a>
            @if($isAdmin)
                <a href="{{ route('projects.archive') }}" class="btn-icon" title="Archive">
                    <i class="fas fa-archive"></i>
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">Please fix the requested project details and try again.</div>
    @endif

    <!-- Filter Panel -->
    <div class="filter-panel">
        <form method="GET" action="{{ route('projects.index') }}" class="filter-grid">
            <div class="filter-group">
                <label><i class="fas fa-calendar-plus"></i> Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar-check"></i> End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-circle"></i> Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-bolt"></i> Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    @foreach($priorityOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($isAdmin)
                <div class="filter-group">
                    <label><i class="fas fa-user-check"></i> Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('employee_id') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="filter-group">
                <label><i class="fas fa-calendar-xmark"></i> Deadline</label>
                <select name="deadline_state" class="form-select">
                    <option value="">Any Deadline</option>
                    <option value="delayed" @selected(request('deadline_state') === 'delayed')>Delayed / Overdue</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-chart-line"></i> Progress</label>
                <select name="progress[]" class="form-select" multiple>
                    @foreach($progressOptions as $value => $label)
                        <option value="{{ $value }}" @selected(in_array($value, request('progress', []), true))>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($isAdmin)
                <div class="filter-group">
                    <label><i class="fas fa-building"></i> Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) request('client_id') === (string) $client->id)>{{ $client->name ?? $client->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="filter-group search-group">
                <label><i class="fas fa-search"></i> Search</label>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="projectSearchInput" class="form-control" value="{{ request('search') }}" placeholder="Search projects...">
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="{{ route('projects.index') }}" class="btn btn-outline"><i class="fas fa-undo"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            @if($isAdmin)
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Project
                </a>
                <button type="button" class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#projectTemplateModal">
                    <i class="fas fa-copy"></i> Project Template
                </button>
                <button type="button" class="btn btn-outline" data-bs-toggle="modal" data-bs-target="#projectImportModal">
                    <i class="fas fa-file-import"></i> Import
                </button>
            @endif
            <button type="button" id="exportProjectsCsv" class="btn btn-outline">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>

        <div class="toolbar-right">
            @if($isAdmin)
                <select id="bulkProjectStatus" class="form-select-sm" disabled>
                    <option value="">Change Status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button id="applyBulkProjectStatus" class="btn btn-sm btn-primary" disabled>Apply</button>
                <button id="bulkDeleteProjects" class="btn btn-sm btn-danger" disabled>
                    <i class="fas fa-trash-alt"></i>
                </button>
            @endif
            <div class="view-toggle">
                <a href="{{ route('projects.index') }}" class="view-btn active" title="List View">
                    <i class="fas fa-list-ul"></i>
                </a>
                <a href="{{ route('projects.calendar') }}" class="view-btn" title="Calendar View">
                    <i class="fas fa-calendar-alt"></i>
                </a>
                <button type="button" class="view-btn show-pinned" title="Pinned Projects">
                    <i class="fas fa-thumbtack"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h4>Project List</h4>
                    <span class="muted">{{ $projects->count() }} projects found</span>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="projectTable" class="project-table">
                <thead>
                    <tr>
                        @if($isAdmin)
                            <th class="check-cell">
                                <input type="checkbox" id="selectAllProjects">
                            </th>
                        @endif
                        <th><i class="fas fa-code"></i> Code</th>
                        <th><i class="fas fa-tag"></i> Project Name</th>
                        <th><i class="fas fa-users"></i> Members</th>
                        <th><i class="fas fa-calendar-plus"></i> Start Date</th>
                        <th><i class="fas fa-calendar-times"></i> Deadline</th>
                        @if($isAdmin)
                            <th><i class="fas fa-building"></i> Client</th>
                        @endif
                        <th><i class="fas fa-bolt"></i> Priority</th>
                        <th><i class="fas fa-circle"></i> Status</th>
                        <th><i class="fas fa-chart-simple"></i> Progress</th>
                        <th><i class="fas fa-comment-dots"></i> Latest Update</th>
                        <th class="action-cell"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        @php
                            $progress = (int) ($project->completion_percent ?? 0);
                            $status = $project->status ?: 'pending';
                            $statusClass = str_replace(' ', '-', strtolower($status));
                            $isOverdue = $project->deadline && \Carbon\Carbon::parse($project->deadline)->isPast() && $status !== 'completed';
                            $priority = $project->priority ?: 'medium';
                        @endphp
                        <tr data-project-id="{{ $project->id }}">
                            @if($isAdmin)
                                <td class="check-cell">
                                    <input type="checkbox" class="project-checkbox" value="{{ $project->id }}">
                                </td>
                            @endif
                            <td>
                                <div class="code-cell">
                                    <span class="code-badge">{{ $project->project_code ?: 'PRJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="name-cell">
                                    <a href="{{ route('projects.show', $project) }}" class="project-name">{{ $project->name }}</a>
                                    <span class="sub-text">{{ $project->tasks_count ?? $project->tasks?->count() ?? 0 }} task(s)</span>
                                </div>
                            </td>
                            <td>
                                <div class="members-cell">
                                    @forelse($project->users->take(4) as $member)
                                        <div class="avatar" title="{{ $member->name }}">
                                            @if($member->profile_image)
                                                <img src="{{ asset($member->profile_image) }}" alt="{{ $member->name }}">
                                            @else
                                                {{ strtoupper(mb_substr($member->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    @empty
                                        <span class="empty-text">No members</span>
                                    @endforelse
                                    @if($project->users->count() > 4)
                                        <div class="avatar more">+{{ $project->users->count() - 4 }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="date-cell">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="date-cell {{ $isOverdue ? 'overdue' : '' }}">
                                    <i class="fas fa-calendar-times"></i>
                                    {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : '--' }}
                                    @if($isOverdue)
                                        <span class="overdue-badge">Overdue</span>
                                    @endif
                                </div>
                            </td>
                            @if($isAdmin)
                                <td>
                                    <div class="client-cell">
                                        <div class="client-avatar">
                                            {{ strtoupper(mb_substr(optional($project->client)->name ?? optional($project->client)->company_name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="client-name">{{ optional($project->client)->name ?? 'No Client' }}</div>
                                            <small>{{ optional($project->client)->company_name ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <span class="priority-pill {{ $priority }}">{{ $priorityOptions[$priority] ?? ucfirst($priority) }}</span>
                            </td>
                            <td>
                                @if($isAdmin || $isEmployee)
                                    <select class="status-select status-{{ $statusClass }}" data-project-id="{{ $project->id }}">
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="status-pill {{ $statusClass }}">{{ $statusOptions[$status] ?? ucfirst($status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress-cell">
                                    <div class="progress-bar">
                                        <div style="width: {{ max(0, min(100, $progress)) }}%"></div>
                                    </div>
                                    <span class="progress-text">{{ $progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="latest-update-cell">
                                    @if($project->latestUpdate)
                                        <strong>{{ $project->latestUpdate->employee?->name ?? 'Admin' }}</strong>
                                        <span>{{ $project->latestUpdate->remarks ? \Illuminate\Support\Str::limit($project->latestUpdate->remarks, 42) : 'Updated progress/status' }}</span>
                                    @elseif($project->remarks)
                                        <strong>Remarks</strong>
                                        <span>{{ \Illuminate\Support\Str::limit($project->remarks, 42) }}</span>
                                    @else
                                        <span class="empty-text">No updates</span>
                                    @endif
                                </div>
                            </td>
                            <td class="action-cell">
                                <div class="dropdown project-action-dropdown">
                                    <button class="action-btn" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="dynamic" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><h6 class="dropdown-header">{{ \Illuminate\Support\Str::limit($project->name, 28) }}</h6></li>
                                        <li><a class="dropdown-item" href="{{ route('projects.show', $project) }}"><i class="fas fa-eye"></i> View</a></li>
                                        @if($isEmployee)
                                            <li><a class="dropdown-item" href="{{ route('projects.gantt', $project) }}"><i class="fas fa-chart-bar"></i> Gantt Chart</a></li>
                                            <li><a class="dropdown-item" href="{{ route('projects.tasks.board', $project) }}"><i class="fas fa-columns"></i> Task Board</a></li>
                                        @endif
                                        @if($isAdmin)
                                            <li><a class="dropdown-item" href="{{ route('projects.edit', $project) }}"><i class="fas fa-pen"></i> Edit</a></li>
                                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#duplicateProjectModal{{ $project->id }}"><i class="fas fa-copy"></i> Duplicate</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="{{ route('projects.gantt', $project) }}"><i class="fas fa-chart-bar"></i> Gantt Chart</a></li>
                                            <li><a class="dropdown-item" href="{{ route('projects.public-gantt', $project) }}" target="_blank"><i class="fas fa-external-link-alt"></i> Public Gantt</a></li>
                                            <li><button class="dropdown-item" type="button" data-copy-url="{{ route('projects.public-gantt', $project) }}"><i class="fas fa-link"></i> Copy Gantt Link</button></li>
                                            <li><a class="dropdown-item" href="{{ route('projects.tasks.board', $project) }}" target="_blank"><i class="fas fa-columns"></i> Public Task Board</a></li>
                                            <li><button class="dropdown-item" type="button" data-copy-url="{{ route('projects.tasks.board', $project) }}"><i class="fas fa-link"></i> Copy Board Link</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('projects.archive.action', $project) }}" method="POST" data-confirm-submit="Archive this project?">
                                                    @csrf
                                                    <button class="dropdown-item text-warning" type="submit"><i class="fas fa-archive"></i> Archive</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('projects.destroy', $project) }}" method="POST" data-confirm-submit="Delete this project permanently?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit"><i class="fas fa-trash-alt"></i> Delete</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 12 : 10 }}">
                                <div class="empty-state">
                                    <i class="fas fa-project-diagram"></i>
                                    <h5>{{ $isEmployee ? 'No assigned projects found' : 'No projects found' }}</h5>
                                    <p>{{ $isEmployee ? 'Only projects assigned to you will appear here.' : 'Try changing the filters or create the first project.' }}</p>
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
                Showing {{ $projects->count() }} project(s)
            </div>
            <div class="footer-status">
                <span class="status-dot"></span>
                <span>Last updated: {{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Duplicate Modals -->
    @foreach($projects as $project)
        @if($isAdmin)
            <div class="modal fade" id="duplicateProjectModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-copy text-primary me-2"></i>Duplicate Project</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('projects.duplicate', $project) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="copy-options">
                                    <label><input type="checkbox" name="task"> <i class="fas fa-tasks"></i> Tasks</label>
                                    <label><input type="checkbox" name="sub_task"> <i class="fas fa-list"></i> Sub Tasks</label>
                                    <label><input type="checkbox" name="same_assignee"> <i class="fas fa-user-check"></i> Same Assignees</label>
                                    <label><input type="checkbox" name="milestone"> <i class="fas fa-flag-checkered"></i> Milestones</label>
                                    <label><input type="checkbox" name="file"> <i class="fas fa-file"></i> Files</label>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Project Name <span class="text-danger">*</span></label>
                                        <input type="text" name="project_name" class="form-control" value="{{ $project->name }} Copy" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Short Code</label>
                                        <input type="text" name="project_code" class="form-control" value="{{ $project->project_code }} Copy">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Deadline</label>
                                        <input type="date" name="deadline" class="form-control" value="{{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Client</label>
                                        <select name="client_id" class="form-select">
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}" @selected($project->client_id === $client->id)>{{ $client->name ?? $client->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Members</label>
                                        <select name="user_id[]" class="form-select" multiple>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" @selected($project->users->contains('id', $user->id))>{{ $user->name }}{{ $user->employeeDetail?->employee_id ? ' (' . $user->employeeDetail->employee_id . ')' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-copy"></i> Duplicate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Template Modal -->
    @if($isAdmin)
        <div class="modal fade" id="projectTemplateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-copy text-primary me-2"></i>Project Templates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="template-list">
                            @forelse($projects as $project)
                                <div class="template-item">
                                    <div>
                                        <strong>{{ $project->name }}</strong>
                                        <span>{{ $project->project_code ?: 'No code' }} / {{ $project->users->count() }} member(s)</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" data-template-project="{{ $project->id }}">Use Template</button>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-copy"></i>
                                    <h5>No project templates available</h5>
                                    <p>Create a project first, then duplicate it as a template.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="projectImportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('projects.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-file-import text-primary me-2"></i>Import Projects</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="project_import" class="form-control" accept=".csv,text/csv" required>
                                <p class="import-note">Supported columns: name, project_code, client_id, start_date, deadline, status, progress, description.</p>
                                @error('project_import')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary"><i class="fas fa-file-import"></i> Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Status Bar -->
    <div class="status-bar">
        <div class="status-item">
            <i class="fas fa-project-diagram text-primary"></i>
            <span>{{ $projects->count() }}</span> Total Projects
        </div>
        <div class="status-item">
            <i class="fas fa-check-circle text-success"></i>
            <span>{{ $projects->where('status', 'completed')->count() }}</span> Completed
        </div>
        <div class="status-item">
            <i class="fas fa-spinner text-primary"></i>
            <span>{{ $projects->where('status', 'in progress')->count() }}</span> In Progress
        </div>
        <div class="status-item">
            <i class="fas fa-clock text-warning"></i>
            <span>{{ $projects->whereIn('status', ['pending', 'not started'])->count() }}</span> Pending
        </div>
        <div class="status-item">
            <i class="fas fa-triangle-exclamation text-danger"></i>
            <span>{{ $projects->filter(fn($p) => $p->status === 'delayed' || ($p->deadline && \Carbon\Carbon::parse($p->deadline)->isPast() && $p->status !== 'completed'))->count() }}</span> Delayed
        </div>
        @if($isAdmin)
            <div class="status-item">
                <i class="fas fa-user-check text-primary"></i>
                <span>{{ $projects->flatMap(fn($project) => $project->users->pluck('id'))->unique()->count() }}</span> Employees Assigned
            </div>
        @endif
    </div>
</main>

<style>
    /* ===== PREMIUM PROJECTS PAGE - ENLARGED TEXT ===== */
    .projects-page {
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

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .btn-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        border: 1px solid rgba(15, 116, 76, .15);
        background: #ffffff;
        color: #0f744c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 1.2rem;
    }

    .btn-icon:hover {
        background: #edf8f2;
        border-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 116, 76, .1);
    }

    /* Alerts */
    .alert {
        border-radius: 16px;
        padding: 18px 24px;
        margin-bottom: 22px;
        border: none;
        font-weight: 600;
        font-size: 1rem;
    }

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    /* Filter Panel */
    .filter-panel {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .12);
        padding: 24px 28px;
        margin-bottom: 22px;
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #5a6e63;
    }

    .filter-group label i {
        margin-right: 6px;
        color: #34d399;
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .18);
        padding: 12px 16px;
        font-weight: 500;
        font-size: 1rem;
        min-height: 48px;
        transition: all 0.2s ease;
        background: #fafefb;
    }

    .form-control:focus, .form-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    select[multiple].form-select {
        min-height: 48px;
        max-height: 90px;
        font-size: 0.95rem;
    }

    .search-group {
        grid-column: span 1;
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #0f744c;
        font-size: 1.1rem;
    }

    .search-box .form-control {
        padding-left: 44px;
        font-size: 1rem;
    }

    .filter-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Buttons */
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

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, .25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 185, 129, .35);
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

    .btn-danger {
        background: #dc2626;
        color: white;
    }

    .btn-danger:hover {
        background: #b91c1c;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 8px 16px;
        min-height: 38px;
        font-size: 0.9rem;
    }

    .form-select-sm {
        padding: 8px 14px;
        min-height: 38px;
        font-size: 0.9rem;
        border-radius: 10px;
    }

    /* Toolbar */
    .toolbar {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .12);
        padding: 18px 26px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .toolbar-left, .toolbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .view-toggle {
        display: flex;
        gap: 4px;
        background: #f0f9f4;
        padding: 4px;
        border-radius: 12px;
    }

    .view-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: none;
        background: transparent;
        color: #5a6e63;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 1.1rem;
    }

    .view-btn:hover {
        background: #d1fae5;
        color: #0f744c;
    }

    .view-btn.active {
        background: #0f744c;
        color: white;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
        overflow: hidden;
    }

    .table-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .table-title-icon {
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

    .table-title h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .table-title .muted {
        font-size: 0.95rem;
        color: #8ba198;
        display: block;
        margin-top: 2px;
    }

    /* Table */
    .table-wrapper {
        overflow-x: auto;
        padding: 0 18px 18px 18px;
    }

    .project-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .project-table thead th {
        text-align: left;
        padding: 16px 18px;
        color: #5a6e63;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: transparent;
        border-bottom: 2px solid rgba(15, 116, 76, .1);
        white-space: nowrap;
    }

    .project-table thead th i {
        margin-right: 8px;
        color: #34d399;
        font-size: 0.9rem;
    }

    .project-table tbody td {
        background: #ffffff;
        padding: 16px 18px;
        border-top: 1px solid rgba(15, 116, 76, .06);
        border-bottom: 1px solid rgba(15, 116, 76, .06);
        vertical-align: middle;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .project-table tbody td:first-child {
        border-left: 1px solid rgba(15, 116, 76, .06);
        border-radius: 14px 0 0 14px;
    }

    .project-table tbody td:last-child {
        border-right: 1px solid rgba(15, 116, 76, .06);
        border-radius: 0 14px 14px 0;
    }

    .project-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(15, 116, 76, .12);
    }

    .check-cell {
        width: 48px;
        text-align: center;
    }

    .action-cell {
        width: 64px;
        text-align: center;
    }

    .form-check-input {
        width: 20px;
        height: 20px;
        border-radius: 5px;
        border: 2px solid #a7f3d0;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #0f744c;
        border-color: #0f744c;
    }

    /* Code Cell */
    .code-badge {
        display: inline-block;
        padding: 6px 16px;
        background: #e7f5ee;
        color: #0f744c;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    /* Name Cell */
    .name-cell {
        display: flex;
        flex-direction: column;
    }

    .project-name {
        color: #07130d;
        font-weight: 700;
        font-size: 1rem;
        text-decoration: none;
        transition: color 0.2s;
    }

    .project-name:hover {
        color: #0f744c;
    }

    .sub-text {
        font-size: 0.85rem;
        color: #8ba198;
        margin-top: 3px;
    }

    /* Members Cell */
    .members-cell {
        display: flex;
        align-items: center;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #d1fae5;
        color: #0f744c;
        font-weight: 700;
        font-size: 0.85rem;
        margin-left: -6px;
        overflow: hidden;
    }

    .avatar:first-child {
        margin-left: 0;
    }

    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar.more {
        background: #0f744c;
        color: white;
        font-size: 0.8rem;
    }

    .empty-text {
        color: #8ba198;
        font-size: 0.9rem;
    }

    /* Date Cell */
    .date-cell {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .date-cell i {
        color: #0f744c;
        font-size: 0.9rem;
    }

    .date-cell.overdue {
        color: #dc2626;
    }

    .overdue-badge {
        display: inline-block;
        padding: 3px 10px;
        background: #fee2e2;
        color: #dc2626;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-left: 6px;
    }

    /* Client Cell */
    .client-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #d1fae5;
        color: #0f744c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .client-name {
        font-weight: 600;
        color: #07130d;
        font-size: 0.95rem;
    }

    .client-cell small {
        font-size: 0.8rem;
        color: #8ba198;
        display: block;
    }

    /* Status Select */
    .status-select {
        padding: 8px 14px;
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .2);
        font-weight: 600;
        font-size: 0.85rem;
        min-width: 140px;
        background: #fafefb;
        cursor: pointer;
        transition: all 0.2s;
        min-height: 40px;
    }

    .status-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .status-pill {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 700;
        color: white;
    }

    .status-pill.not-started { background: #64748b; }
    .status-pill.pending { background: #f59e0b; }
    .status-pill.in-progress { background: #3b82f6; }
    .status-pill.on-hold { background: #f59e0b; }
    .status-pill.completed { background: #10b981; }
    .status-pill.delayed { background: #dc2626; }

    .priority-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: capitalize;
    }

    .priority-pill.low { background: #dbeafe; color: #1d4ed8; }
    .priority-pill.medium { background: #fef3c7; color: #92400e; }
    .priority-pill.high { background: #fed7aa; color: #c2410c; }
    .priority-pill.critical { background: #fee2e2; color: #b91c1c; }

    .latest-update-cell {
        display: flex;
        flex-direction: column;
        gap: 3px;
        max-width: 210px;
    }

    .latest-update-cell strong {
        color: #07130d;
        font-size: 0.9rem;
    }

    .latest-update-cell span {
        color: #8ba198;
        font-size: 0.85rem;
    }

    /* Progress Cell */
    .progress-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .progress-bar {
        flex: 1;
        height: 8px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
        min-width: 90px;
    }

    .progress-bar div {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #34d399, #10b981);
        transition: width 0.6s ease;
    }

    .progress-text {
        font-weight: 700;
        font-size: 0.9rem;
        color: #07130d;
        min-width: 44px;
    }

    /* Action Button */
    .action-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: none;
        background: #f0f9f4;
        color: #0f744c;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-size: 1.1rem;
    }

    .action-btn:hover {
        background: #d1fae5;
        transform: scale(1.05);
    }

    /* Dropdown */
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(15, 116, 76, .15);
        border-radius: 16px;
        padding: 8px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        min-width: 240px;
    }

    .dropdown-header {
        color: #07130d;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 10px 14px 12px;
        border-bottom: 1px solid rgba(15, 116, 76, .08);
    }

    .dropdown-item {
        padding: 10px 16px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.95rem;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        background: none;
        width: 100%;
    }

    .dropdown-item i {
        width: 20px;
        color: #5a6e63;
        font-size: 1rem;
    }

    .dropdown-item:hover {
        background: #ecfdf5;
        color: #059669;
    }

    .dropdown-item.text-danger:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .dropdown-item.text-warning:hover {
        background: #fef3c7;
        color: #d97706;
    }

    /* Table Footer */
    .table-footer {
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

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #a7f3d0;
        margin-bottom: 18px;
    }

    .empty-state h5 {
        color: #0f744c;
        font-weight: 700;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #8ba198;
        font-size: 1rem;
    }

    /* Modal */
    .modal-content {
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .12);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        padding: 22px 28px;
    }

    .modal-header .modal-title {
        font-weight: 700;
        font-size: 1.2rem;
        color: #07130d;
    }

    .modal-body {
        padding: 28px;
    }

    .modal-footer {
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        padding: 18px 28px;
    }

    .copy-options {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .copy-options label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        border-radius: 12px;
        background: #f0f9f4;
        border: 1px solid rgba(15, 116, 76, .1);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .copy-options label:hover {
        background: #d1fae5;
        border-color: #34d399;
    }

    .copy-options label i {
        color: #0f744c;
        font-size: 1rem;
    }

    .template-list {
        display: grid;
        gap: 12px;
    }

    .template-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-radius: 14px;
        background: #f0f9f4;
        border: 1px solid rgba(15, 116, 76, .08);
        transition: all 0.2s;
    }

    .template-item:hover {
        background: #d1fae5;
        border-color: #34d399;
    }

    .template-item strong {
        display: block;
        color: #07130d;
        font-weight: 700;
        font-size: 1rem;
    }

    .template-item span {
        font-size: 0.9rem;
        color: #8ba198;
    }

    .import-note {
        font-size: 0.9rem;
        color: #8ba198;
        margin-top: 10px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.95rem;
        color: #07130d;
        margin-bottom: 6px;
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .filter-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .filter-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 12px;
        }
    }

    @media (max-width: 992px) {
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
        .filter-actions {
            grid-column: 1 / -1;
        }
        .search-group {
            grid-column: 1 / -1;
        }
        .table-title h4 {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 768px) {
        .projects-page {
            padding: 16px 0;
        }
        .filter-grid {
            grid-template-columns: 1fr;
        }
        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .toolbar-left, .toolbar-right {
            flex-wrap: wrap;
            justify-content: center;
        }
        .view-toggle {
            width: 100%;
            justify-content: center;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .table-wrapper {
            padding: 0 10px 10px 10px;
        }
        .header-actions {
            width: 100%;
            justify-content: flex-start;
        }
        .header-card h1 {
            font-size: 26px;
        }
        .header-card p {
            font-size: 15px;
        }
        .filter-group label {
            font-size: 0.8rem;
        }
        .project-table thead th {
            font-size: 0.75rem;
            padding: 12px 12px;
        }
        .project-table tbody td {
            font-size: 0.85rem;
            padding: 12px 12px;
        }
    }

    /* Final project filter/action polish: aligned buttons, no cramped controls. */
    .projects-page .filter-panel {
        overflow: visible !important;
    }

    .projects-page .filter-grid {
        align-items: end !important;
        grid-template-columns: repeat(4, minmax(180px, 1fr)) !important;
        gap: 1rem !important;
    }

    .projects-page .filter-group,
    .projects-page .search-group,
    .projects-page .filter-actions {
        min-width: 0;
    }

    .projects-page .filter-group label {
        align-items: center;
        display: inline-flex;
        gap: 0.45rem;
        line-height: 1.2;
        min-height: 20px;
    }

    .projects-page .filter-group .form-control,
    .projects-page .filter-group .form-select,
    .projects-page .search-box {
        min-height: 46px;
        width: 100%;
    }

    .projects-page .filter-actions {
        align-items: end;
        display: grid !important;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 0.7rem;
    }

    .projects-page .filter-actions .btn {
        border-radius: 14px !important;
        justify-content: center;
        min-height: 46px;
        width: 100%;
        white-space: nowrap;
    }

    .projects-page .filter-actions .btn-outline {
        background: #fff !important;
        border: 1px solid rgba(15, 116, 76, 0.18) !important;
        color: #0f744c !important;
    }

    .projects-page .stat-card h3,
    .projects-page .stat-card span,
    .projects-page .status-bar span,
    .projects-page .project-count,
    .projects-page .progress-text {
        max-width: 100%;
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .projects-page .stat-card h3 {
        font-size: clamp(1.35rem, 2vw, 1.85rem) !important;
        line-height: 1.05;
    }

    @media (max-width: 1399.98px) {
        .projects-page .filter-grid {
            grid-template-columns: repeat(3, minmax(180px, 1fr)) !important;
        }
    }

    @media (max-width: 991.98px) {
        .projects-page .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 575.98px) {
        .projects-page .filter-grid {
            grid-template-columns: 1fr !important;
        }

        .projects-page .filter-actions {
            grid-template-columns: 1fr !important;
        }

        .projects-page .filter-actions .btn {
            white-space: normal;
        }
    }

    /* Final view button polish: list/calendar icons stay aligned and readable. */
    .projects-page .header-actions {
        align-items: center;
        display: flex;
        gap: 0.65rem;
    }

    .projects-page .header-actions .btn-icon,
    .projects-page .view-toggle .view-btn {
        align-items: center !important;
        aspect-ratio: 1 / 1;
        border: 1px solid rgba(15, 116, 76, 0.14) !important;
        display: inline-flex !important;
        flex: 0 0 auto;
        height: 44px !important;
        justify-content: center !important;
        line-height: 1 !important;
        min-height: 44px !important;
        padding: 0 !important;
        text-decoration: none !important;
        width: 44px !important;
    }

    .projects-page .header-actions .btn-icon i,
    .projects-page .view-toggle .view-btn i {
        display: block;
        font-size: 1rem;
        line-height: 1;
        margin: 0 !important;
    }

    .projects-page .header-actions .btn-icon.active,
    .projects-page .view-toggle .view-btn.active {
        background: linear-gradient(145deg, #0f744c, #10b981) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 10px 22px rgba(16, 185, 129, 0.22);
    }

    .projects-page .view-toggle {
        align-items: center;
        border: 1px solid rgba(15, 116, 76, 0.1);
        display: inline-flex !important;
        flex: 0 0 auto;
        padding: 4px !important;
    }

    @media (max-width: 575.98px) {
        .projects-page .header-actions {
            justify-content: flex-start !important;
            width: 100%;
        }

        .projects-page .toolbar-right {
            align-items: stretch;
            justify-content: flex-start !important;
        }

        .projects-page .view-toggle {
            justify-content: flex-start !important;
            width: auto !important;
        }
    }

    /* Final row action dropdown fix: three-dot menus must escape table/card clipping. */
    .projects-page .table-card,
    .projects-page .table-wrapper,
    .projects-page .project-table,
    .projects-page .project-table tbody,
    .projects-page .project-table tr,
    .projects-page .project-table td.action-cell,
    .projects-page .project-action-dropdown {
        overflow: visible !important;
    }

    .projects-page .table-card {
        position: relative;
        z-index: 1;
    }

    .projects-page .table-wrapper {
        overflow-x: auto !important;
        overflow-y: visible !important;
        padding-bottom: 140px;
    }

    .projects-page .project-action-dropdown .dropdown-menu {
        border: 1px solid rgba(15, 116, 76, 0.14) !important;
        border-radius: 14px !important;
        box-shadow: 0 22px 55px rgba(15, 23, 42, 0.18) !important;
        max-height: min(70vh, 520px);
        min-width: 240px;
        overflow-y: auto;
        padding: 8px !important;
        z-index: 2090 !important;
    }

    body > .project-floating-action-menu {
        border: 1px solid rgba(15, 116, 76, 0.14) !important;
        border-radius: 14px !important;
        box-shadow: 0 22px 55px rgba(15, 23, 42, 0.2) !important;
        max-height: min(70vh, 520px);
        min-width: 240px;
        overflow-y: auto;
        padding: 8px !important;
        z-index: 3000 !important;
    }

    .projects-page .project-action-dropdown .dropdown-menu.show {
        display: block !important;
    }

    .projects-page .project-action-dropdown .dropdown-item {
        align-items: center;
        border-radius: 10px;
        display: flex;
        gap: 0.65rem;
        min-height: 38px;
        white-space: normal;
    }

    .projects-page .project-action-dropdown .dropdown-item i {
        flex: 0 0 18px;
        margin: 0 !important;
        text-align: center;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const selectAll = document.getElementById('selectAllProjects');
    const checkboxes = () => Array.from(document.querySelectorAll('.project-checkbox'));
    const bulkStatus = document.getElementById('bulkProjectStatus');
    const bulkApply = document.getElementById('applyBulkProjectStatus');
    const bulkDelete = document.getElementById('bulkDeleteProjects');

    function selectedIds() {
        return checkboxes().filter(box => box.checked).map(box => box.value);
    }

    function refreshBulkControls() {
        const selected = selectedIds();
        if (bulkStatus) bulkStatus.disabled = selected.length === 0;
        if (bulkApply) bulkApply.disabled = selected.length === 0;
        if (bulkDelete) bulkDelete.disabled = selected.length === 0;

        if (!selectAll) return;
        const all = checkboxes();
        selectAll.checked = all.length > 0 && selected.length === all.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < all.length;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes().forEach(box => box.checked = selectAll.checked);
            refreshBulkControls();
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('project-checkbox')) {
            refreshBulkControls();
        }
    });

    document.querySelectorAll('.status-select').forEach(select => {
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

    if (bulkApply) {
        bulkApply.addEventListener('click', function () {
            const ids = selectedIds();
            const status = bulkStatus.value;
            if (!ids.length || !status) {
                alert('Select projects and choose a status.');
                return;
            }

            bulkApply.disabled = true;
            fetch("{{ route('projects.bulk-status') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ids, status})
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok || !data.success) throw new Error(data.message || 'Bulk status update failed.');
                window.location.reload();
            })
            .catch(error => {
                alert(error.message);
                refreshBulkControls();
            });
        });
    }

    if (bulkDelete) {
        bulkDelete.addEventListener('click', function () {
            const ids = selectedIds();
            if (!ids.length) return;
            if (!confirm('Delete selected projects permanently?')) return;

            bulkDelete.disabled = true;
            fetch("{{ route('projects.bulk-delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ids})
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok || !data.success) throw new Error(data.message || 'Bulk delete failed.');
                ids.forEach(id => document.querySelector('tr[data-project-id="' + id + '"]')?.remove());
                refreshBulkControls();
            })
            .catch(error => {
                alert(error.message);
                refreshBulkControls();
            });
        });
    }

    document.querySelectorAll('[data-confirm-submit]').forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!confirm(this.dataset.confirmSubmit || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-copy-url]').forEach(button => {
        button.addEventListener('click', function () {
            const url = this.dataset.copyUrl;
            const oldText = this.innerHTML;

            function done() {
                button.innerHTML = '<i class="fas fa-check"></i> Copied';
                setTimeout(() => button.innerHTML = oldText, 1400);
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(done).catch(() => prompt('Copy this link:', url));
            } else {
                prompt('Copy this link:', url);
            }
        });
    });

    document.getElementById('exportProjectsCsv')?.addEventListener('click', function () {
        const rows = Array.from(document.querySelectorAll('#projectTable tbody tr')).filter(row => row.querySelectorAll('td').length > 1);
        const csv = [['Code', 'Project Name', 'Start Date', 'Deadline', 'Client', 'Priority', 'Status', 'Progress', 'Latest Update']];

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const offset = {{ $isAdmin ? 1 : 0 }};
            csv.push([
                cells[offset]?.innerText.trim() || '',
                cells[offset + 1]?.querySelector('.project-name')?.innerText.trim() || '',
                cells[offset + 3]?.innerText.trim() || '',
                cells[offset + 4]?.innerText.trim().replace(/Overdue/g, '').trim() || '',
                {{ $isAdmin ? "cells[offset + 5]?.innerText.trim().replace(/\\n+/g, ' ').replace(/No Client/g, '') || ''" : "''" }},
                cells[{{ $isAdmin ? 'offset + 6' : 'offset + 5' }}]?.innerText.trim() || '',
                cells[{{ $isAdmin ? 'offset + 7' : 'offset + 6' }}]?.innerText.trim() || '',
                cells[{{ $isAdmin ? 'offset + 8' : 'offset + 7' }}]?.innerText.trim() || '',
                cells[{{ $isAdmin ? 'offset + 9' : 'offset + 8' }}]?.innerText.trim().replace(/\n+/g, ' ') || ''
            ]);
        });

        const blob = new Blob([csv.map(cols => cols.map(col => '"' + String(col).replace(/"/g, '""') + '"').join(',')).join('\n')], {type: 'text/csv;charset=utf-8;'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'projects.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });

    document.querySelector('[data-project-search-focus]')?.addEventListener('click', function () {
        document.getElementById('projectSearchInput')?.focus();
    });

    document.querySelector('.show-pinned')?.addEventListener('click', function () {
        alert('Pinned projects view is ready for a pin data field. No pinned project flag exists yet.');
    });

    document.querySelectorAll('.project-action-dropdown').forEach(dropdown => {
        const button = dropdown.querySelector('[data-bs-toggle="dropdown"]');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (!button || !menu) return;

        let originalParent = null;
        let originalNextSibling = null;

        function positionFloatingMenu() {
            const rect = button.getBoundingClientRect();
            const menuWidth = Math.max(menu.offsetWidth || 240, 240);
            const viewportGap = 12;
            const left = Math.max(viewportGap, Math.min(window.innerWidth - menuWidth - viewportGap, rect.right - menuWidth));
            const opensUp = rect.bottom + menu.offsetHeight + viewportGap > window.innerHeight;
            const top = opensUp
                ? Math.max(viewportGap, rect.top - menu.offsetHeight - 8)
                : Math.min(window.innerHeight - viewportGap, rect.bottom + 8);

            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        }

        dropdown.addEventListener('show.bs.dropdown', function () {
            originalParent = menu.parentNode;
            originalNextSibling = menu.nextSibling;
        });

        dropdown.addEventListener('shown.bs.dropdown', function () {
            document.body.appendChild(menu);
            menu.classList.add('project-floating-action-menu');
            menu.style.position = 'fixed';
            menu.style.right = 'auto';
            menu.style.bottom = 'auto';
            menu.style.transform = 'none';
            positionFloatingMenu();
        });

        dropdown.addEventListener('hide.bs.dropdown', function () {
            menu.classList.remove('project-floating-action-menu');
            menu.removeAttribute('style');

            if (originalParent) {
                originalParent.insertBefore(menu, originalNextSibling);
            }
        });

        window.addEventListener('resize', function () {
            if (menu.classList.contains('show') && menu.classList.contains('project-floating-action-menu')) {
                positionFloatingMenu();
            }
        });

        window.addEventListener('scroll', function () {
            if (menu.classList.contains('show') && menu.classList.contains('project-floating-action-menu')) {
                positionFloatingMenu();
            }
        }, true);
    });

    document.querySelectorAll('[data-template-project]').forEach(button => {
        button.addEventListener('click', function () {
            const projectId = this.dataset.templateProject;
            const templateModal = bootstrap.Modal.getInstance(document.getElementById('projectTemplateModal'));
            templateModal?.hide();

            setTimeout(() => {
                const duplicateModal = document.getElementById('duplicateProjectModal' + projectId);
                if (duplicateModal) {
                    bootstrap.Modal.getOrCreateInstance(duplicateModal).show();
                }
            }, 250);
        });
    });

    refreshBulkControls();
});
</script>
@endsection
