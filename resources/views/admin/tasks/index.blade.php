@extends('admin.layout.app')

@section('title', $project ? 'Project Tasks' : 'Tasks')

@section('content')
@php
    $canManageTasks = in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true);
    $statusOptions = ['Waiting for Approval', 'To Do', 'Doing', 'Incomplete', 'Completed'];
    $priorityOptions = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];
    $statusColor = [
        'Waiting for Approval' => 'waiting',
        'To Do' => 'todo',
        'Doing' => 'doing',
        'Incomplete' => 'incomplete',
        'Completed' => 'completed',
    ];
    $indexRoute = $project ? route('projects.tasks.index', $project->id) : route('tasks.index');
@endphp

<main class="tasks-page">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="breadcrumb">
        <i class="fas fa-tasks"></i>
        <span>Dashboard / @if($project)<a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / @endif<strong>Tasks</strong></span>
    </div>

    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-list-check"></i>
            </div>
            <div>
                <h1>{{ $project ? 'Project Tasks' : 'Tasks' }}</h1>
                <p>{{ $project ? 'Manage task work for ' . $project->name : 'Manage and track task work across projects' }}</p>
            </div>
        </div>
        <div class="header-actions">
            <button type="button" class="btn-icon" data-task-search-focus title="Search">
                <i class="fas fa-search"></i>
            </button>
            <a href="{{ route('users.tasks.board') }}" class="btn-icon" title="Task Board">
                <i class="fas fa-columns"></i>
            </a>
            <a href="{{ route('tasks.calendar') }}" class="btn-icon" title="Calendar">
                <i class="fas fa-calendar-alt"></i>
            </a>
            <a href="{{ route('tasks.waiting-approval') }}" class="btn-icon" title="Waiting Approval">
                <i class="fas fa-triangle-exclamation"></i>
            </a>
        </div>
    </div>

    @if($project)
        <div class="project-task-tabs">
            <a href="{{ route('projects.show', $project->id) }}"><i class="fas fa-chart-pie"></i> Overview</a>
            <a href="{{ route('project-members.index', $project->id) }}"><i class="fas fa-users"></i> Members</a>
            <a href="{{ route('project-files.index', $project->id) }}"><i class="fas fa-folder-open"></i> Files</a>
            <a href="{{ route('milestones.index', $project->id) }}"><i class="fas fa-flag-checkered"></i> Milestones</a>
            <a class="active" href="{{ route('projects.tasks.index', $project->id) }}"><i class="fas fa-tasks"></i> Tasks</a>
            <a href="{{ route('projects.tasks.board', $project->id) }}"><i class="fas fa-columns"></i> Task Board</a>
            <a href="{{ route('projects.gantt', $project->id) }}"><i class="fas fa-chart-bar"></i> Gantt</a>
            <a href="{{ route('projects.timelogs.index', $project->id) }}"><i class="fas fa-clock"></i> Timesheet</a>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">Please fix the task details and try again.</div>
    @endif

    <div class="filter-panel">
        <form method="GET" action="{{ $indexRoute }}" class="filter-grid">
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
                    <option value="pending" @selected(request('status') === 'pending')>Hide Completed Task</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            @if($canManageTasks)
                <div class="filter-group">
                    <label><i class="fas fa-user"></i> Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        @foreach(($employees ?? collect()) as $employee)
                            <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if(!$project)
                <div class="filter-group">
                    <label><i class="fas fa-folder-open"></i> Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach(($projects ?? collect()) as $filterProject)
                            <option value="{{ $filterProject->id }}" @selected((string) request('project_id') === (string) $filterProject->id)>{{ $filterProject->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="filter-group">
                <label><i class="fas fa-flag"></i> Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    @foreach($priorityOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group search-group">
                <label><i class="fas fa-search"></i> Search</label>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="taskSearchInput" class="form-control" value="{{ request('search') }}" placeholder="Start typing to search">
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="{{ $indexRoute }}" class="btn btn-outline"><i class="fas fa-undo"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            @if($canManageTasks)
                <a href="{{ route('tasks.create', $project ? ['project_id' => $project->id] : []) }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Task
                </a>
            @endif
            <button type="button" class="btn btn-outline" id="filterMyTasks">
                <i class="fas fa-user"></i> My Tasks
            </button>
            <button type="button" class="btn btn-outline" id="exportTasksCsv">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>

        <div class="toolbar-right">
            @if($canManageTasks)
                <select id="bulkStatus" class="form-select-sm" disabled>
                    <option value="">Change Status</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
                <button type="button" id="applyBulkStatus" class="btn btn-sm btn-primary" disabled>Apply</button>
                <button type="button" id="bulkDeleteTasks" class="btn btn-sm btn-danger" disabled title="Delete selected">
                    <i class="fas fa-trash-alt"></i>
                </button>
            @endif
            <div class="view-toggle">
                <a href="{{ route('tasks.index') }}" class="view-btn active" title="List View"><i class="fas fa-list-ul"></i></a>
                <a href="{{ route('users.tasks.board') }}" class="view-btn" title="Board View"><i class="fas fa-columns"></i></a>
                <a href="{{ route('tasks.calendar') }}" class="view-btn" title="Calendar View"><i class="fas fa-calendar-alt"></i></a>
                <button type="button" class="view-btn show-pinned" title="Pinned Tasks"><i class="fas fa-thumbtack"></i></button>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h4>Task List</h4>
                    <span class="muted">{{ $tasks->count() }} task(s) found</span>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="taskTable" class="task-table">
                <thead>
                    <tr>
                        @if($canManageTasks)
                            <th class="check-cell"><input type="checkbox" id="selectAllTasks"></th>
                        @endif
                        <th><i class="fas fa-code"></i> Code</th>
                        <th><i class="fas fa-stopwatch"></i> Timer</th>
                        <th><i class="fas fa-tag"></i> Task</th>
                        <th><i class="fas fa-check"></i> Completed On</th>
                        <th><i class="fas fa-calendar-plus"></i> Start Date</th>
                        <th><i class="fas fa-calendar-times"></i> Due Date</th>
                        <th><i class="fas fa-hourglass-half"></i> Estimated</th>
                        <th><i class="fas fa-clock"></i> Logged</th>
                        <th><i class="fas fa-user"></i> Assigned To</th>
                        <th><i class="fas fa-flag"></i> Priority</th>
                        <th><i class="fas fa-circle"></i> Status</th>
                        <th><i class="fas fa-chart-line"></i> Progress</th>
                        <th><i class="fas fa-comment-dots"></i> Latest Update</th>
                        <th class="action-cell"><i class="fas fa-cog"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks->where('parent_id', null) as $task)
                        @php
                            $assignedUsers = $task->assignees ?? collect();
                            $activeTimer = $task->activeTimer;
                            $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'Completed';
                            $statusClass = $statusColor[$task->status] ?? 'default';
                            $progress = (int) ($task->progress ?? 0);
                            $priority = strtolower($task->priority ?? 'medium');
                        @endphp
                        <tr data-task-id="{{ $task->id }}" data-assignee-ids="{{ $assignedUsers->pluck('id')->implode(',') }}" data-pinned="{{ $task->is_pinned ? '1' : '0' }}">
                            @if($canManageTasks)
                                <td class="check-cell"><input type="checkbox" class="task-checkbox" value="{{ $task->id }}"></td>
                            @endif
                            <td><span class="code-badge">{{ $task->task_short_code ?: 'TASK-' . str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                            <td>
                                @if($activeTimer)
                                    <div class="timer-actions">
                                        @if($activeTimer->pause_time)
                                            <form method="POST" action="{{ route('task-timer.resume', $task->id) }}">@csrf<button class="timer-btn resume" title="Resume"><i class="fas fa-play"></i></button></form>
                                        @else
                                            <form method="POST" action="{{ route('task-timer.pause', $task->id) }}">@csrf<button class="timer-btn pause" title="Pause"><i class="fas fa-pause"></i></button></form>
                                        @endif
                                        <button class="timer-btn stop" data-bs-toggle="modal" data-bs-target="#stopTimerModal-{{ $task->id }}" title="Stop"><i class="fas fa-stop"></i></button>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('task-timer.start', $task->id) }}">@csrf<button class="timer-btn start" title="Start"><i class="fas fa-play"></i></button></form>
                                @endif
                            </td>
                            <td>
                                <div class="name-cell">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="task-name">{{ $task->title }}</a>
                                    <span class="sub-text">{{ $task->project->name ?? 'No project' }}</span>
                                </div>
                            </td>
                            <td>{{ $task->completed_on ? \Carbon\Carbon::parse($task->completed_on)->format('M d, Y') : '--' }}</td>
                            <td><div class="date-cell"><i class="fas fa-calendar-alt"></i>{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('M d, Y') : '--' }}</div></td>
                            <td>
                                <div class="date-cell {{ $isOverdue ? 'overdue' : '' }}">
                                    <i class="fas fa-calendar-times"></i>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '--' }}
                                    @if($isOverdue)<span class="overdue-badge">Overdue</span>@endif
                                </div>
                            </td>
                            <td>{{ (int) ($task->estimate_hours ?? 0) }}h {{ (int) ($task->estimate_minutes ?? 0) }}m</td>
                            <td>{{ $task->total_logged_formatted ?? '00h 00m 00s' }}</td>
                            <td>
                                <div class="members-cell">
                                    @forelse($assignedUsers->take(3) as $user)
                                        <div class="avatar" title="{{ $user->name }}">
                                            @if($user->profile_image)
                                                <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}">
                                            @else
                                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                    @empty
                                        <span class="empty-text">Unassigned</span>
                                    @endforelse
                                    @if($assignedUsers->count() > 3)<div class="avatar more">+{{ $assignedUsers->count() - 3 }}</div>@endif
                                </div>
                            </td>
                            <td><span class="priority-pill {{ $priority }}">{{ ucfirst($priority) }}</span></td>
                            <td>
                                <div class="status-cell">
                                    <span class="status-pill {{ $statusClass }}">{{ $task->status ?: 'To Do' }}</span>
                                    <select class="status-select status-dropdown" data-task-id="{{ $task->id }}">
                                        @foreach($statusOptions as $status)
                                            <option value="{{ $status }}" @selected($task->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="progress-cell">
                                    <div class="progress-bar"><div style="width: {{ max(0, min(100, $progress)) }}%"></div></div>
                                    <span>{{ $progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="update-cell">
                                    <strong>{{ $task->latestUpdate?->user?->name ?? 'No update' }}</strong>
                                    <span>{{ $task->latestUpdate?->remarks ? \Illuminate\Support\Str::limit($task->latestUpdate->remarks, 42) : ($task->remarks ? \Illuminate\Support\Str::limit($task->remarks, 42) : '--') }}</span>
                                </div>
                            </td>
                            <td class="action-cell">
                                <div class="dropdown">
                                    <button class="action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}"><i class="fas fa-eye"></i> View</a></li>
                                        <li><button class="dropdown-item pin-task-btn" type="button" data-task-id="{{ $task->id }}"><i class="fas fa-thumbtack"></i> <span>{{ $task->is_pinned ? 'Unpin' : 'Pin' }}</span></button></li>
                                        @if($canManageTasks)
                                            <li><a class="dropdown-item" href="{{ route('tasks.edit', $task->id) }}"><i class="fas fa-pen"></i> Edit</a></li>
                                            <li><a class="dropdown-item" href="{{ route('tasks.create', ['duplicate_id' => $task->id]) }}"><i class="fas fa-copy"></i> Duplicate</a></li>
                                            <li>
                                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" data-confirm-submit="Delete this task?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit"><i class="fas fa-trash"></i> Delete</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                @if($activeTimer)
                                    <div class="modal fade" id="stopTimerModal-{{ $task->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('task-timer.stop', $task->id) }}">
                                                @csrf
                                                <input type="hidden" name="timer_id" value="{{ $activeTimer->id }}">
                                                <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                                                <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($activeTimer->start_time)->format('Y-m-d') }}">
                                                <input type="hidden" name="end_date" value="{{ now()->format('Y-m-d') }}">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Stop Timer</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i A') }}</p>
                                                        <p><strong>End:</strong> <span class="end-time"></span></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Memo <span class="text-danger">*</span></label>
                                                            <textarea name="memo" class="form-control" rows="3" maxlength="500" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageTasks ? 15 : 14 }}">
                                <div class="empty-state">
                                    <i class="fas fa-tasks"></i>
                                    <h5>No tasks found</h5>
                                    <p>Create a task or adjust the current filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="status-bar">
        <div class="status-item"><i class="fas fa-list-check text-primary"></i><span>{{ $tasks->count() }}</span> Total Tasks</div>
        <div class="status-item"><i class="fas fa-circle text-secondary"></i><span>{{ $tasks->where('status', 'To Do')->count() }}</span> To Do</div>
        <div class="status-item"><i class="fas fa-spinner text-info"></i><span>{{ $tasks->where('status', 'Doing')->count() }}</span> Doing</div>
        <div class="status-item"><i class="fas fa-check-circle text-success"></i><span>{{ $tasks->where('status', 'Completed')->count() }}</span> Completed</div>
        <div class="status-item"><i class="fas fa-triangle-exclamation text-danger"></i><span>{{ $tasks->filter(fn($task) => $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'Completed')->count() }}</span> Overdue</div>
        <div class="status-item"><i class="fas fa-users text-primary"></i><span>{{ $tasks->flatMap(fn($task) => $task->assignees->pluck('id'))->unique()->count() }}</span> Employees</div>
    </div>
</main>

<style>
    .tasks-page{padding:30px;min-height:100vh;background:linear-gradient(145deg,#f7fbf9,#eef7f2);color:#07130d}.breadcrumb{background:rgba(255,255,255,.85);backdrop-filter:blur(10px);padding:16px 26px;border-radius:18px;border:1px solid rgba(15,116,76,.12);margin-bottom:28px;color:#0f744c;font-weight:600;font-size:1.05rem}.breadcrumb i{margin-right:12px;color:#34d399}.breadcrumb a{color:#0f744c;text-decoration:none}.header-card{background:#fff;border-radius:24px;padding:30px 36px;display:flex;justify-content:space-between;align-items:center;gap:24px;box-shadow:0 18px 45px rgba(15,116,76,.09);border:1px solid rgba(15,116,76,.12);margin-bottom:28px}.header-left{display:flex;align-items:center;gap:24px}.header-icon{width:70px;height:70px;background:linear-gradient(145deg,#34d399,#10b981);color:#fff;border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 10px 25px rgba(16,185,129,.2)}.header-card h1{font-size:34px;font-weight:700;margin:0 0 6px;color:#07130d}.header-card p{color:#52645a;font-size:17px;margin:0}.header-actions{display:flex;gap:12px}.btn-icon,.view-btn{width:46px;height:46px;border-radius:14px;border:1px solid rgba(15,116,76,.18);background:#fff;color:#0f744c;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:.2s}.btn-icon:hover,.view-btn:hover,.view-btn.active{background:#edf8f2;border-color:#34d399;color:#0f744c;transform:translateY(-1px)}.project-task-tabs{display:flex;flex-wrap:wrap;gap:4px;background:linear-gradient(135deg,#fff,#f5fbf7);border:1px solid rgba(15,116,76,.12);border-radius:20px;padding:10px 16px;margin-bottom:28px;box-shadow:0 8px 25px rgba(15,116,76,.06)}.project-task-tabs a{display:flex;align-items:center;gap:9px;padding:10px 18px;border-radius:12px;text-decoration:none;color:#5a6e63;font-weight:700;font-size:.9rem}.project-task-tabs a.active,.project-task-tabs a:hover{background:linear-gradient(145deg,#0f744c,#10b981);color:#fff}.alert{border-radius:16px;padding:16px 22px;margin-bottom:22px;border:none}.alert-success{background:#ecfdf5;color:#065f46}.alert-danger{background:#fef2f2;color:#991b1b}.filter-panel,.table-card{background:#fff;border-radius:24px;border:1px solid rgba(15,116,76,.12);box-shadow:0 14px 35px rgba(15,116,76,.07);margin-bottom:24px}.filter-panel{padding:24px}.filter-grid{display:grid;grid-template-columns:repeat(5,minmax(160px,1fr));gap:18px;align-items:end}.filter-group label{display:flex;align-items:center;gap:8px;margin-bottom:8px;font-weight:700;color:#07130d}.form-control,.form-select,.form-select-sm,.status-select{border-radius:12px;border:1px solid rgba(15,116,76,.18);padding:11px 14px;background:#fafefb;color:#07130d;min-height:46px}.search-box{position:relative}.search-box i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#8ba198}.search-box input{padding-left:40px}.filter-actions{display:flex;gap:10px}.btn{border:none;padding:12px 20px;border-radius:14px;font-weight:700;display:inline-flex;align-items:center;gap:9px;text-decoration:none;min-height:46px}.btn-primary{background:linear-gradient(145deg,#34d399,#10b981);color:#fff}.btn-outline{background:transparent;border:1px solid rgba(15,116,76,.22);color:#0f744c}.btn-danger{background:#ef4444;color:#fff}.toolbar{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:24px}.toolbar-left,.toolbar-right{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.view-toggle{display:flex;gap:6px;background:#fff;border:1px solid rgba(15,116,76,.12);padding:5px;border-radius:16px}.table-header{padding:22px 26px;background:linear-gradient(135deg,#fff,#f5fbf7);border-bottom:1px solid rgba(15,116,76,.1)}.table-title{display:flex;align-items:center;gap:14px}.table-title-icon{width:46px;height:46px;border-radius:14px;background:#e7f5ee;color:#0f744c;display:flex;align-items:center;justify-content:center;font-size:20px}.table-title h4{margin:0;font-size:1.25rem;font-weight:800}.muted,.sub-text,.empty-text{color:#8ba198}.table-wrapper{overflow-x:auto}.task-table{width:100%;border-collapse:separate;border-spacing:0}.task-table th{padding:16px 18px;background:#f5fbf7;color:#0f744c;font-size:.82rem;text-transform:uppercase;letter-spacing:.03em;white-space:nowrap;border-bottom:1px solid rgba(15,116,76,.1)}.task-table td{padding:16px 18px;border-bottom:1px solid rgba(15,116,76,.08);vertical-align:middle}.task-table tr:hover td{background:#fafefb}.check-cell{width:44px;text-align:center}.code-badge{display:inline-flex;padding:7px 11px;border-radius:999px;background:#edf8f2;color:#0f744c;font-weight:800;font-size:.82rem;white-space:nowrap}.name-cell{display:flex;flex-direction:column;gap:4px;min-width:230px}.task-name{color:#07130d;font-weight:800;text-decoration:none}.task-name:hover{color:#0f744c}.date-cell{display:flex;align-items:center;gap:8px;white-space:nowrap}.date-cell i{color:#34d399}.overdue{color:#dc2626}.overdue-badge{margin-left:6px;font-size:.68rem;background:#fee2e2;color:#991b1b;padding:2px 7px;border-radius:999px}.members-cell{display:flex;align-items:center;gap:6px;min-width:115px}.avatar{width:32px;height:32px;border-radius:50%;background:#d1fae5;color:#0f744c;display:inline-flex;align-items:center;justify-content:center;font-weight:800;border:2px solid #fff;overflow:hidden}.avatar img{width:100%;height:100%;object-fit:cover}.avatar.more{background:#0f744c;color:#fff;font-size:.78rem}.status-cell{display:flex;align-items:center;gap:8px;min-width:230px}.status-pill,.priority-pill{display:inline-flex;align-items:center;padding:7px 11px;border-radius:999px;font-weight:800;font-size:.78rem;white-space:nowrap}.status-pill.todo{background:#eef2ff;color:#3730a3}.status-pill.doing{background:#e0f2fe;color:#0369a1}.status-pill.incomplete{background:#fee2e2;color:#991b1b}.status-pill.completed{background:#dcfce7;color:#166534}.status-pill.waiting{background:#fef3c7;color:#92400e}.status-pill.default{background:#f1f5f9;color:#334155}.priority-pill.low{background:#dcfce7;color:#166534}.priority-pill.medium{background:#fef3c7;color:#92400e}.priority-pill.high{background:#fee2e2;color:#991b1b}.status-select{min-height:38px;padding:8px 10px;max-width:150px}.progress-cell{display:flex;align-items:center;gap:10px;min-width:120px}.progress-bar{width:76px;height:8px;border-radius:999px;background:#e5e7eb;overflow:hidden}.progress-bar div{height:100%;background:linear-gradient(90deg,#34d399,#10b981)}.update-cell{display:flex;flex-direction:column;gap:3px;min-width:170px}.update-cell strong{font-size:.85rem;color:#07130d}.update-cell span{font-size:.8rem;color:#8ba198}.status-bar{margin-top:24px;background:#fff;border:1px solid rgba(15,116,76,.12);border-radius:20px;padding:18px 24px;display:flex;flex-wrap:wrap;gap:18px;box-shadow:0 8px 25px rgba(15,116,76,.05)}.status-item{display:flex;align-items:center;gap:9px;color:#5a6e63;font-weight:700}.status-item span{font-size:1.05rem;color:#07130d}.action-cell{text-align:center;width:72px}.action-btn{width:38px;height:38px;border-radius:12px;border:1px solid rgba(15,116,76,.16);background:#fff;color:#0f744c}.dropdown-item{display:flex;align-items:center;gap:9px}.timer-actions{display:flex;gap:6px}.timer-btn{width:34px;height:34px;border:0;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;color:#fff}.timer-btn.start,.timer-btn.resume{background:#10b981}.timer-btn.pause{background:#f59e0b}.timer-btn.stop{background:#ef4444}.empty-state{text-align:center;padding:44px 20px;color:#8ba198}.empty-state i{font-size:42px;color:#34d399;margin-bottom:14px}@media(max-width:1200px){.filter-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:768px){.tasks-page{padding:16px}.header-card,.toolbar{flex-direction:column;align-items:flex-start}.header-card{padding:22px}.filter-grid{grid-template-columns:1fr}.filter-actions,.toolbar-left,.toolbar-right{width:100%}.btn{justify-content:center}.header-card h1{font-size:28px}}
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const currentUserId = '{{ auth()->id() }}';
    const canManage = @json($canManageTasks);

    document.querySelector('[data-task-search-focus]')?.addEventListener('click', () => {
        document.getElementById('taskSearchInput')?.focus();
    });

    document.querySelectorAll('[data-confirm-submit]').forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!confirm(form.dataset.confirmSubmit || 'Are you sure?')) event.preventDefault();
        });
    });

    document.querySelectorAll('.status-dropdown').forEach(select => {
        select.dataset.previous = select.value;
        select.addEventListener('change', function () {
            const previous = select.dataset.previous;
            const status = select.value;
            const taskId = select.dataset.taskId;

            if (!confirm(`Change status to "${status}"?`)) {
                select.value = previous;
                return;
            }

            fetch(`{{ url('/tasks') }}/${taskId}/update-status`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: JSON.stringify({status})
            }).then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) throw new Error(data.message || 'Status update failed.');
                select.dataset.previous = status;
                const pill = select.closest('.status-cell').querySelector('.status-pill');
                const classMap = {'Waiting for Approval':'waiting','To Do':'todo','Doing':'doing','Incomplete':'incomplete','Completed':'completed'};
                pill.className = `status-pill ${classMap[status] || 'default'}`;
                pill.textContent = status;
                const row = select.closest('tr');
                const completedCell = row?.children[canManage ? 4 : 3];
                if (completedCell && data.completed_on) completedCell.textContent = data.completed_on;
                const progressCell = row?.querySelector('.progress-cell');
                if (progressCell && data.progress !== undefined) {
                    progressCell.querySelector('.progress-bar div').style.width = `${data.progress}%`;
                    progressCell.querySelector('span').textContent = `${data.progress}%`;
                }
            }).catch(error => {
                alert(error.message);
                select.value = previous;
            });
        });
    });

    if (canManage) {
        const selectAll = document.getElementById('selectAllTasks');
        const checkboxes = () => Array.from(document.querySelectorAll('.task-checkbox'));
        const bulkStatus = document.getElementById('bulkStatus');
        const applyBulk = document.getElementById('applyBulkStatus');
        const bulkDelete = document.getElementById('bulkDeleteTasks');

        function updateBulkControls() {
            const boxes = checkboxes();
            const selected = boxes.filter(checkbox => checkbox.checked);
            const hasSelection = selected.length > 0;
            [bulkStatus, applyBulk, bulkDelete].forEach(el => { if (el) el.disabled = !hasSelection; });
            if (selectAll) {
                selectAll.checked = boxes.length > 0 && selected.length === boxes.length;
                selectAll.indeterminate = selected.length > 0 && selected.length < boxes.length;
            }
        }

        selectAll?.addEventListener('change', function () {
            checkboxes().forEach(checkbox => checkbox.checked = selectAll.checked);
            updateBulkControls();
        });

        document.addEventListener('change', event => {
            if (event.target.classList.contains('task-checkbox')) updateBulkControls();
        });

        applyBulk?.addEventListener('click', function () {
            const ids = checkboxes().filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
            const status = bulkStatus.value;
            if (!ids.length || !status) return alert('Select tasks and a status.');
            if (!confirm(`Change selected tasks to "${status}"?`)) return;

            fetch('{{ route('tasks.bulkStatusUpdate') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: JSON.stringify({ids, status})
            }).then(response => {
                if (!response.ok) throw new Error('Bulk status update failed.');
                location.reload();
            }).catch(error => alert(error.message));
        });

        bulkDelete?.addEventListener('click', function () {
            const ids = checkboxes().filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);
            if (!ids.length) return alert('Select at least one task.');
            if (!confirm('Delete selected tasks?')) return;

            fetch('{{ route('tasks.bulkDelete') }}', {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: JSON.stringify({ids})
            }).then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) throw new Error(data.message || 'Bulk delete failed.');
                ids.forEach(id => document.querySelector(`tr[data-task-id="${id}"]`)?.remove());
                updateBulkControls();
            }).catch(error => alert(error.message));
        });

        updateBulkControls();
    }

    document.getElementById('filterMyTasks')?.addEventListener('click', function () {
        document.querySelectorAll('#taskTable tbody tr[data-task-id]').forEach(row => {
            const ids = (row.dataset.assigneeIds || '').split(',').filter(Boolean);
            row.style.display = ids.includes(currentUserId) ? '' : 'none';
        });
        document.querySelectorAll('.task-checkbox').forEach(checkbox => checkbox.checked = false);
        const selectAll = document.getElementById('selectAllTasks');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        ['bulkStatus', 'applyBulkStatus', 'bulkDeleteTasks'].forEach(id => {
            const control = document.getElementById(id);
            if (control) control.disabled = true;
        });
    });

    let pinnedOnly = false;
    document.querySelector('.show-pinned')?.addEventListener('click', function () {
        pinnedOnly = !pinnedOnly;
        this.classList.toggle('active', pinnedOnly);
        document.querySelectorAll('#taskTable tbody tr[data-task-id]').forEach(row => {
            row.style.display = !pinnedOnly || row.dataset.pinned === '1' ? '' : 'none';
        });
        document.querySelectorAll('.task-checkbox').forEach(checkbox => checkbox.checked = false);
        const selectAll = document.getElementById('selectAllTasks');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        ['bulkStatus', 'applyBulkStatus', 'bulkDeleteTasks'].forEach(id => {
            const control = document.getElementById(id);
            if (control) control.disabled = true;
        });
    });

    document.querySelectorAll('.pin-task-btn').forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.dataset.taskId;
            this.disabled = true;

            fetch(`{{ url('/tasks') }}/${taskId}/toggle-pin`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
                body: JSON.stringify({})
            }).then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) throw new Error(data.message || 'Pin update failed.');
                const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
                if (row) row.dataset.pinned = data.is_pinned ? '1' : '0';
                this.querySelector('span').textContent = data.is_pinned ? 'Unpin' : 'Pin';
                if (pinnedOnly && row && row.dataset.pinned !== '1') row.style.display = 'none';
            }).catch(error => alert(error.message)).finally(() => {
                this.disabled = false;
            });
        });
    });

    document.getElementById('exportTasksCsv')?.addEventListener('click', function () {
        const skippedHeaders = ['Timer', 'Action'];
        const headerCells = Array.from(document.querySelectorAll('#taskTable thead th'));
        const exportIndexes = headerCells
            .map((th, index) => ({index, label: th.innerText.replace(/\s+/g, ' ').trim()}))
            .filter(item => item.label && item.label !== '' && !skippedHeaders.includes(item.label));
        const rows = [exportIndexes.map(item => item.label)];

        document.querySelectorAll('#taskTable tbody tr[data-task-id]').forEach(row => {
            if (row.style.display === 'none') return;
            const cells = Array.from(row.children).map(cell => cell.innerText.replace(/\s+/g, ' ').trim());
            rows.push(exportIndexes.map(item => cells[item.index] || ''));
        });
        const csv = rows.map(row => row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'tasks.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });

    document.querySelectorAll('[id^="stopTimerModal-"]').forEach(modal => {
        let intervalId;
        modal.addEventListener('show.bs.modal', function () {
            const endTimeSpan = modal.querySelector('.end-time');
            intervalId = setInterval(() => {
                if (endTimeSpan) endTimeSpan.textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', hour12: true});
            }, 1000);
        });
        modal.addEventListener('hide.bs.modal', () => clearInterval(intervalId));
    });
});
</script>
@endpush
@endsection
