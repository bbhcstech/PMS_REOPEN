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

<main id="pms-tasks-page" class="tasks-page">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if($project)
        {{-- Standardized Project Header & 13-Tab Navigation --}}
        <div data-live-key="tasks-project-header">
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'tasks'
        ])
        </div>
    @else
        <div class="header-card" data-live-key="tasks-header">
            <div class="header-left">
                <div class="header-icon" id="tasksHeaderIcon">
                    <i class="fas fa-list-check"></i>
                </div>
                <div>
                    <h1>Tasks</h1>
                    <p>Manage and track task work across projects</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-icon pms-header-search-btn" data-task-search-focus title="Search">
                    <svg class="task-toolbar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/></svg>
                </button>
                <div class="pms-header-view-group">
                    <a href="{{ route('tasks.index') }}" class="btn-icon active" title="List View" aria-label="List View">
                        <svg class="task-toolbar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5h12M9 12h12M9 19h12M3 5h1M3 12h1M3 19h1"/></svg>
                    </a>
                    <a href="{{ route('users.tasks.board') }}" class="btn-icon" title="Task Board">
                        <svg class="task-toolbar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 3v18M3 8h18"/></svg>
                    </a>
                    <a href="{{ route('tasks.calendar') }}" class="btn-icon" title="Calendar">
                        <svg class="task-toolbar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18M7 15h2m6 0h2M7 18h2m6 0h2"/></svg>
                    </a>
                    <a href="{{ route('tasks.waiting-approval') }}" class="btn-icon" title="Waiting Approval">
                        <svg class="task-toolbar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Zm0 6v5m0 3v1"/></svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success" data-live-key="tasks-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" data-live-key="tasks-error">Please fix the task details and try again.</div>
    @endif

    <div class="filter-panel" data-live-key="tasks-filters">
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
            <div class="pms-filter-bottom-row">
                <div class="filter-group search-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" id="taskSearchInput" class="form-control" value="{{ request('search') }}" placeholder="Start typing to search tasks...">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="fas fa-filter"></i> Apply Filters</button>
                    <a href="{{ $indexRoute }}" class="btn btn-outline" style="white-space: nowrap;"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="toolbar" data-live-key="tasks-toolbar">
        <div class="toolbar-left">
            @if($canManageTasks)
                <a href="{{ route('tasks.create', array_merge($project ? ['project_id' => $project->id] : [], ['embedded' => 1])) }}" class="btn btn-primary" data-task-form-open>
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

    <div class="table-card" data-live-key="tasks-list">
        <div class="table-header" data-live-key="tasks-list-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h4>Task List</h4>
                    <span class="muted">{{ $tasks->count() }} task(s) found</span>
                </div>
            </div>
            <div class="pms-table-meta">
                <span>Sorted by: <strong>Created Date</strong></span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </div>
        </div>

        <div class="table-wrapper" data-live-key="tasks-table-wrapper">
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
                            <td><span class="status-pill {{ $statusClass }}">{{ $task->status ?: 'To Do' }}</span></td>
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
                                                        <p><strong>Start:</strong> <time datetime="{{ \Carbon\Carbon::parse($activeTimer->start_time)->toIso8601String() }}">{{ \Carbon\Carbon::parse($activeTimer->start_time)->format('h:i:s A') }}</time></p>
                                                        <p><strong>End:</strong> <time class="end-time" data-live-timer-end data-server-now="{{ now()->getTimestampMs() }}" data-time-zone="{{ config('app.timezone') }}">{{ now()->format('h:i:s A') }}</time></p>
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
                                    <div class="pms-empty-icon-box" aria-hidden="true">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <h5>No tasks found in this view</h5>
                                    <p>No matching tasks found for the current filters. Try clearing your filters or add a new task.</p>
                                    <div class="pms-empty-actions">
                                        @if($canManageTasks)
                                            <a href="{{ route('tasks.create', array_merge($project ? ['project_id' => $project->id] : [], ['embedded' => 1])) }}" class="btn btn-primary" data-task-form-open>
                                                <i class="fas fa-plus"></i> Add First Task
                                            </a>
                                        @endif
                                        <a href="{{ $indexRoute }}" class="btn btn-outline">
                                            <i class="fas fa-undo"></i> Clear All Filters
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="status-bar" data-live-key="tasks-summary">
        <div class="status-item"><i class="fas fa-list-check text-primary"></i><span>{{ $tasks->count() }}</span> Total Tasks</div>
        <div class="status-item"><i class="fas fa-circle text-secondary"></i><span>{{ $tasks->where('status', 'To Do')->count() }}</span> To Do</div>
        <div class="status-item"><i class="fas fa-spinner text-info"></i><span>{{ $tasks->where('status', 'Doing')->count() }}</span> Doing</div>
        <div class="status-item"><i class="fas fa-check-circle text-success"></i><span>{{ $tasks->where('status', 'Completed')->count() }}</span> Completed</div>
        <div class="status-item"><i class="fas fa-triangle-exclamation text-danger"></i><span>{{ $tasks->filter(fn($task) => $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'Completed')->count() }}</span> Overdue</div>
        <div class="status-item"><i class="fas fa-users text-primary"></i><span>{{ $tasks->flatMap(fn($task) => $task->assignees->pluck('id'))->unique()->count() }}</span> Employees</div>
    </div>
</main>

<style>
    .tasks-page{padding:30px;min-height:100vh;background:linear-gradient(145deg,#F8FAFC,#EEF2FF);color:#070B1A}.breadcrumb{background:rgba(255,255,255,.85);backdrop-filter:blur(10px);padding:16px 26px;border-radius:18px;border:1px solid rgba(47,107,255,.12);margin-bottom:28px;color:#2F6BFF;font-weight:600;font-size:1.05rem}.breadcrumb i{margin-right:12px;color:#60A5FA}.breadcrumb a{color:#2F6BFF;text-decoration:none}.header-card{background:#fff;border-radius:24px;padding:30px 36px;display:flex;justify-content:space-between;align-items:center;gap:24px;box-shadow:0 18px 45px rgba(47,107,255,.07);border:1px solid rgba(47,107,255,.12);margin-bottom:28px}.header-left{display:flex;align-items:center;gap:24px}.header-icon{width:70px;height:70px;background:linear-gradient(145deg,#4F83FF,#2F6BFF);color:#fff;border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:0 10px 25px rgba(47,107,255,.25)}.header-card h1{font-size:34px;font-weight:700;margin:0 0 6px;color:#070B1A}.header-card p{color:#64748B;font-size:17px;margin:0}.header-actions{display:flex;gap:12px}.btn-icon,.view-btn{width:46px;height:46px;border-radius:14px;border:1px solid rgba(47,107,255,.18);background:#fff;color:#2F6BFF;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:.2s}.btn-icon:hover,.view-btn:hover,.view-btn.active{background:#EEF2FF;border-color:#2F6BFF;color:#2F6BFF;transform:translateY(-1px)}.project-task-tabs{display:flex;flex-wrap:wrap;gap:4px;background:linear-gradient(135deg,#fff,#f8faff);border:1px solid rgba(47,107,255,.12);border-radius:20px;padding:10px 16px;margin-bottom:28px;box-shadow:0 8px 25px rgba(47,107,255,.06)}.project-task-tabs a{display:flex;align-items:center;gap:9px;padding:10px 18px;border-radius:12px;text-decoration:none;color:#64748B;font-weight:700;font-size:.9rem}.project-task-tabs a.active,.project-task-tabs a:hover{background:linear-gradient(145deg,#4F83FF,#2F6BFF);color:#fff}.alert{border-radius:16px;padding:16px 22px;margin-bottom:22px;border:none}.alert-success{background:#ECFDF5;color:#065F46}.alert-danger{background:#fef2f2;color:#991b1b}.filter-panel,.table-card{background:#fff;border-radius:24px;border:1px solid rgba(47,107,255,.12);box-shadow:0 14px 35px rgba(47,107,255,.07);margin-bottom:24px}.filter-panel{padding:24px}.filter-grid{display:grid;grid-template-columns:repeat(5,minmax(160px,1fr));gap:18px;align-items:end}.filter-group label{display:flex;align-items:center;gap:8px;margin-bottom:8px;font-weight:700;color:#070B1A}.form-control,.form-select,.form-select-sm,.status-select{border-radius:12px;border:1px solid rgba(47,107,255,.18);padding:11px 14px;background:#fafbff;color:#070B1A;min-height:46px}.search-box{position:relative}.search-box i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748B}.search-box input{padding-left:40px}.filter-actions{display:flex;gap:12px;grid-column:span 2;align-items:center;white-space:nowrap}.filter-actions .btn{white-space:nowrap;flex-shrink:0}.btn{border:none;padding:12px 20px;border-radius:14px;font-weight:700;display:inline-flex;align-items:center;gap:9px;text-decoration:none;min-height:46px;white-space:nowrap}.btn-primary{background:linear-gradient(145deg,#4F83FF,#2F6BFF);color:#fff}.btn-outline{background:transparent;border:1px solid rgba(47,107,255,.22);color:#2F6BFF}.btn-danger{background:#ef4444;color:#fff}.toolbar{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:24px}.toolbar-left,.toolbar-right{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.view-toggle{display:flex;gap:6px;background:#fff;border:1px solid rgba(47,107,255,.12);padding:5px;border-radius:16px}.table-header{padding:22px 26px;background:linear-gradient(135deg,#fff,#f8faff);border-bottom:1px solid rgba(47,107,255,.1)}.table-title{display:flex;align-items:center;gap:14px}.table-title-icon{width:46px;height:46px;border-radius:14px;background:#EEF2FF;color:#2F6BFF;display:flex;align-items:center;justify-content:center;font-size:20px}.table-title h4{margin:0;font-size:1.25rem;font-weight:800}.muted,.sub-text,.empty-text{color:#64748B}.table-wrapper{overflow-x:auto}.task-table{width:100%;border-collapse:separate;border-spacing:0}.task-table th{padding:16px 18px;background:#f8faff;color:#2F6BFF;font-size:.82rem;text-transform:uppercase;letter-spacing:.03em;white-space:nowrap;border-bottom:1px solid rgba(47,107,255,.1)}.task-table td{padding:16px 18px;border-bottom:1px solid rgba(47,107,255,.08);vertical-align:middle}.task-table tr:hover td{background:#f8faff}.check-cell{width:44px;text-align:center}.code-badge{display:inline-flex;padding:7px 11px;border-radius:999px;background:#EEF2FF;color:#2F6BFF;font-weight:800;font-size:.82rem;white-space:nowrap}.name-cell{display:flex;flex-direction:column;gap:4px;min-width:230px}.task-name{color:#070B1A;font-weight:800;text-decoration:none}.task-name:hover{color:#2F6BFF}.date-cell{display:flex;align-items:center;gap:8px;white-space:nowrap}.date-cell i{color:#60A5FA}.overdue{color:#dc2626}.overdue-badge{margin-left:6px;font-size:.68rem;background:#fee2e2;color:#991b1b;padding:2px 7px;border-radius:999px}.members-cell{display:flex;align-items:center;gap:6px;min-width:115px}.avatar{width:32px;height:32px;border-radius:50%;background:#E0E7FF;color:#2F6BFF;display:inline-flex;align-items:center;justify-content:center;font-weight:800;border:2px solid #fff;overflow:hidden}.avatar img{width:100%;height:100%;object-fit:cover}.avatar.more{background:#2F6BFF;color:#fff;font-size:.78rem}.status-cell{display:inline-flex;align-items:center;gap:8px}.status-pill,.priority-pill{display:inline-flex;align-items:center;padding:7px 11px;border-radius:999px;font-weight:800;font-size:.78rem;white-space:nowrap}.status-pill.todo{background:#eef2ff;color:#3730a3}.status-pill.doing{background:#e0f2fe;color:#0369a1}.status-pill.incomplete{background:#fee2e2;color:#991b1b}.status-pill.completed{background:#dcfce7;color:#166534}.status-pill.waiting{background:#fef3c7;color:#92400e}.status-pill.default{background:#f1f5f9;color:#334155}.priority-pill.low{background:#dcfce7;color:#166534}.priority-pill.medium{background:#fef3c7;color:#92400e}.priority-pill.high{background:#fee2e2;color:#991b1b}.status-select{min-height:38px;padding:8px 10px;max-width:150px}.progress-cell{display:flex;align-items:center;gap:10px;min-width:120px}.progress-bar{width:76px;height:8px;border-radius:999px;background:#e5e7eb;overflow:hidden}.progress-bar div{height:100%;background:linear-gradient(90deg,#4F83FF,#2F6BFF)}.update-cell{display:flex;flex-direction:column;gap:3px;min-width:170px}.update-cell strong{font-size:.85rem;color:#070B1A}.update-cell span{font-size:.8rem;color:#64748B}.status-bar{margin-top:24px;background:#fff;border:1px solid rgba(47,107,255,.12);border-radius:20px;padding:18px 24px;display:flex;flex-wrap:wrap;gap:18px;box-shadow:0 8px 25px rgba(47,107,255,.05)}.status-item{display:flex;align-items:center;gap:9px;color:#64748B;font-weight:700}.status-item span{font-size:1.05rem;color:#070B1A}.action-cell{text-align:center;width:72px}.action-btn{width:38px;height:38px;border-radius:12px;border:1px solid rgba(47,107,255,.16);background:#fff;color:#2F6BFF}.dropdown-item{display:flex;align-items:center;gap:9px}.timer-actions{display:flex;gap:6px}.timer-btn{width:34px;height:34px;border:0;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;color:#fff}.timer-btn.start,.timer-btn.resume{background:#10b981}.timer-btn.pause{background:#f59e0b}.timer-btn.stop{background:#ef4444}.empty-state{text-align:center;padding:44px 20px;color:#64748B}.empty-state > i{font-size:42px;color:#60A5FA;margin-bottom:14px}@media(max-width:1200px){.filter-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:768px){.tasks-page{padding:16px}.header-card,.toolbar{flex-direction:column;align-items:flex-start}.header-card{padding:22px}.filter-grid{grid-template-columns:1fr}.filter-actions,.toolbar-left,.toolbar-right{width:100%}.btn{justify-content:center}}
    html[data-pms-theme="dark"] .tasks-page {
        background: #070B1A !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .breadcrumb {
        background: transparent !important;
        border-color: rgba(238, 241, 251, 0.08) !important;
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .header-card,
    html[data-pms-theme="dark"] .tasks-page .filter-panel,
    html[data-pms-theme="dark"] .tasks-page .table-card,
    html[data-pms-theme="dark"] .tasks-page .status-bar {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.35) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .header-card h1 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .header-card p {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .btn-icon,
    html[data-pms-theme="dark"] .tasks-page .view-btn,
    html[data-pms-theme="dark"] .tasks-page .action-btn {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
        color: #CBD5E1 !important;
    }
    /* Header action toolbar icons: light and dark mode rules */
    .tasks-page .header-actions .btn-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
        padding: 0;
    }
    .tasks-page .header-actions .task-toolbar-icon,
    .tasks-page .header-actions .task-toolbar-icon * {
        background: transparent !important;
        background-color: transparent !important;
        filter: none !important;
    }
    .tasks-page .header-actions .task-toolbar-icon {
        width: 22px;
        height: 22px;
        fill: none !important;
        stroke: currentColor !important;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
        pointer-events: none;
    }
    html[data-pms-theme="dark"] .tasks-page .header-actions .btn-icon,
    html[data-theme="dark"] .tasks-page .header-actions .btn-icon,
    html[data-bs-theme="dark"] .tasks-page .header-actions .btn-icon,
    [data-pms-theme="dark"] .tasks-page .header-actions .btn-icon,
    .dark-mode .tasks-page .header-actions .btn-icon {
        background: #141B3D !important;
        background-color: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }
    html[data-pms-theme="dark"] .tasks-page .header-actions .btn-icon:hover,
    html[data-theme="dark"] .tasks-page .header-actions .btn-icon:hover,
    html[data-bs-theme="dark"] .tasks-page .header-actions .btn-icon:hover,
    [data-pms-theme="dark"] .tasks-page .header-actions .btn-icon:hover,
    .dark-mode .tasks-page .header-actions .btn-icon:hover {
        background: #1A2247 !important;
        background-color: #1A2247 !important;
        border-color: #4F83FF !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        transform: translateY(-1px);
    }
    html[data-pms-theme="dark"] .tasks-page .header-actions .task-toolbar-icon,
    html[data-pms-theme="dark"] .tasks-page .header-actions .task-toolbar-icon *,
    html[data-theme="dark"] .tasks-page .header-actions .task-toolbar-icon,
    html[data-theme="dark"] .tasks-page .header-actions .task-toolbar-icon *,
    html[data-bs-theme="dark"] .tasks-page .header-actions .task-toolbar-icon,
    html[data-bs-theme="dark"] .tasks-page .header-actions .task-toolbar-icon *,
    [data-pms-theme="dark"] .tasks-page .header-actions .task-toolbar-icon,
    [data-pms-theme="dark"] .tasks-page .header-actions .task-toolbar-icon *,
    .dark-mode .tasks-page .header-actions .task-toolbar-icon,
    .dark-mode .tasks-page .header-actions .task-toolbar-icon * {
        background: transparent !important;
        background-color: transparent !important;
        fill: none !important;
        stroke: #93C5FD !important;
        stroke-width: 2 !important;
        stroke-linecap: round !important;
        stroke-linejoin: round !important;
    }
    html[data-pms-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon,
    html[data-pms-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon *,
    html[data-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon,
    html[data-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon *,
    html[data-bs-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon,
    html[data-bs-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon *,
    [data-pms-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon,
    [data-pms-theme="dark"] .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon *,
    .dark-mode .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon,
    .dark-mode .tasks-page .header-actions .btn-icon:hover .task-toolbar-icon * {
        stroke: #FFFFFF !important;
    }
    html:not([data-pms-theme="dark"]) .tasks-page .header-actions .btn-icon {
        background: #FFFFFF !important;
        background-color: #FFFFFF !important;
        border: 1px solid rgba(47, 107, 255, 0.18) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }
    html:not([data-pms-theme="dark"]) .tasks-page .header-actions .btn-icon:hover {
        background: #EEF2FF !important;
        background-color: #EEF2FF !important;
        border-color: #2F6BFF !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        transform: translateY(-1px);
    }
    html:not([data-pms-theme="dark"]) .tasks-page .header-actions .task-toolbar-icon,
    html:not([data-pms-theme="dark"]) .tasks-page .header-actions .task-toolbar-icon * {
        background: transparent !important;
        background-color: transparent !important;
        fill: none !important;
        stroke: #2F6BFF !important;
        stroke-width: 2 !important;
        stroke-linecap: round !important;
        stroke-linejoin: round !important;
    }
    #tasksHeaderIcon {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: #ffffff !important;
    }
    #tasksHeaderIcon > i,
    #tasksHeaderIcon > svg {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: transparent !important;
        background-color: transparent !important;
    }
    html[data-pms-theme="dark"] .tasks-page .filter-group label {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .form-control,
    html[data-pms-theme="dark"] .tasks-page .form-select,
    html[data-pms-theme="dark"] .tasks-page .status-select {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .table-header {
        background: #141B3D !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .table-title h4 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .task-table th {
        background: #141B3D !important;
        color: #9AA3C7 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .task-table td {
        border-bottom-color: rgba(238, 241, 251, 0.06) !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .task-table tr:hover td {
        background: rgba(47, 107, 255, 0.08) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .task-name {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .task-name:hover {
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .tasks-page .update-cell strong {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-item {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-item span {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .code-badge {
        background: #141B3D !important;
        color: #60A5FA !important;
        border: 1px solid rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .project-task-tabs {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .project-task-tabs a {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .project-task-tabs a:hover {
        background: #141B3D !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .tasks-page .project-task-tabs a.active {
        background: #2F6BFF !important;
        color: #FFFFFF !important;
    }
    html[data-pms-theme="dark"] .tasks-page .btn-outline {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .overdue-badge,
    html[data-pms-theme="dark"] .tasks-page span.overdue-badge {
        background: rgba(239, 68, 68, 0.22) !important;
        color: #FCA5A5 !important;
        border: 1px solid rgba(239, 68, 68, 0.4) !important;
        font-weight: 700 !important;
        display: inline-block !important;
    }
    html[data-pms-theme="dark"] .tasks-page .date-cell.overdue {
        color: #FCA5A5 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .date-cell i {
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-pill.todo {
        background: rgba(99, 102, 241, 0.18) !important;
        color: #A5B4FC !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-pill.doing {
        background: rgba(47, 107, 255, 0.18) !important;
        color: #93C5FD !important;
        border: 1px solid rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-pill.incomplete {
        background: rgba(239, 68, 68, 0.18) !important;
        color: #FCA5A5 !important;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-pill.completed {
        background: rgba(47, 107, 255, 0.18) !important;
        color: #93C5FD !important;
        border: 1px solid rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-pill.waiting {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #FCD34D !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .status-pill.default {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border: 1px solid rgba(238, 241, 251, 0.16) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .priority-pill.low {
        background: rgba(47, 107, 255, 0.18) !important;
        color: #93C5FD !important;
        border: 1px solid rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .priority-pill.medium {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #FCD34D !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .priority-pill.high {
        background: rgba(249, 115, 22, 0.18) !important;
        color: #FDBA74 !important;
        border: 1px solid rgba(249, 115, 22, 0.3) !important;
    }
    html[data-pms-theme="dark"] .tasks-page .members-cell .avatar {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border: 2px solid #0F1530 !important;
    }
    html[data-pms-theme="dark"] .tasks-page .members-cell .avatar.more {
        background: #1A2247 !important;
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .tasks-page input[type="checkbox"],
    html[data-pms-theme="dark"] .tasks-page .task-checkbox,
    html[data-pms-theme="dark"] .tasks-page #selectAllTasks {
        accent-color: #2F6BFF !important;
    }

    /* Clean SaaS Workspace Redesign (Scoped to #pms-tasks-page) */
    #pms-tasks-page.tasks-page {
        padding: 0 0 2rem !important;
        margin: 0 !important;
        min-height: 0 !important;
        background: transparent !important;
        font-family: 'Inter', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 16px !important;
    }

    #pms-tasks-page .header-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 20px 24px !important;
        margin: 0 !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 8px 20px rgba(15, 23, 42, 0.03) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
    }

    #pms-tasks-page .header-left {
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
    }

    #pms-tasks-page #tasksHeaderIcon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.25rem !important;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22) !important;
        flex-shrink: 0 !important;
    }

    #pms-tasks-page .header-left h1 {
        margin: 0 !important;
        font-size: 1.35rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        letter-spacing: -0.02em !important;
        line-height: 1.25 !important;
    }

    #pms-tasks-page .header-left p {
        margin: 3px 0 0 !important;
        font-size: 0.85rem !important;
        color: #64748b !important;
    }

    #pms-tasks-page .header-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    #pms-tasks-page .header-actions .pms-header-search-btn {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #475569 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    #pms-tasks-page .header-actions .pms-header-search-btn:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .pms-header-view-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        padding: 4px !important;
        border-radius: 12px !important;
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
    }

    #pms-tasks-page .pms-header-view-group .btn-icon {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        border: none !important;
        background: transparent !important;
        color: #64748b !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: none !important;
        transition: all 0.16s ease !important;
    }

    #pms-tasks-page .pms-header-view-group .btn-icon:hover {
        background: #ffffff !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .pms-header-view-group .btn-icon.active {
        background: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.28) !important;
    }

    #pms-tasks-page .header-actions .task-toolbar-icon {
        width: 16px !important;
        height: 16px !important;
        stroke: currentColor !important;
        fill: none !important;
        stroke-width: 2 !important;
    }

    #pms-tasks-page .pms-header-view-group .btn-icon.active .task-toolbar-icon,
    #pms-tasks-page .pms-header-view-group .btn-icon.active .task-toolbar-icon * {
        stroke: #ffffff !important;
    }

    /* Filter Panel */
    #pms-tasks-page .filter-panel {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 20px 24px !important;
        margin: 0 !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    #pms-tasks-page .filter-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 16px !important;
        align-items: start !important;
    }

    #pms-tasks-page .filter-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
        margin: 0 !important;
    }

    #pms-tasks-page .filter-group > label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #334155 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 0 !important;
    }

    #pms-tasks-page .filter-group > label i {
        color: #2563eb !important;
        font-size: 0.72rem !important;
        width: 13px !important;
        text-align: center !important;
    }

    #pms-tasks-page .filter-panel .form-control,
    #pms-tasks-page .filter-panel .form-select {
        height: 40px !important;
        min-height: 40px !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-size: 0.85rem !important;
        padding: 0 12px !important;
        box-shadow: none !important;
    }

    #pms-tasks-page .filter-panel .form-select {
        padding-right: 32px !important;
    }

    #pms-tasks-page .filter-panel .form-control:focus,
    #pms-tasks-page .filter-panel .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    #pms-tasks-page .pms-filter-bottom-row {
        grid-column: 1 / -1 !important;
        display: flex !important;
        align-items: flex-end !important;
        justify-content: space-between !important;
        gap: 16px !important;
        padding-top: 2px !important;
        flex-wrap: wrap !important;
    }

    #pms-tasks-page .pms-filter-bottom-row .search-group {
        flex: 1 1 320px !important;
        max-width: 520px !important;
    }

    #pms-tasks-page .search-box {
        position: relative !important;
    }

    #pms-tasks-page .search-box > i {
        position: absolute !important;
        left: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #94a3b8 !important;
        font-size: 0.82rem !important;
        pointer-events: none !important;
    }

    #pms-tasks-page .search-box .form-control {
        padding-left: 36px !important;
    }

    #pms-tasks-page .filter-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        margin: 0 !important;
        grid-column: auto !important;
    }

    /* Uniform Rectangular Buttons */
    #pms-tasks-page .btn,
    #pms-tasks-page .filter-actions .btn,
    #pms-tasks-page .toolbar .btn:not(.btn-sm),
    #pms-tasks-page .pms-empty-actions .btn {
        border-radius: 10px !important;
        height: 38px !important;
        min-height: 38px !important;
        padding: 0 16px !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        box-shadow: none !important;
        transition: all 0.16s ease !important;
    }

    #pms-tasks-page .btn-primary {
        background: #2563eb !important;
        border: 1px solid #2563eb !important;
        color: #ffffff !important;
    }

    #pms-tasks-page .btn-primary:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.24) !important;
    }

    #pms-tasks-page .btn-outline {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .btn-outline:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .pms-empty-actions .btn i {
        font-size: 0.85rem !important;
        line-height: 1 !important;
        margin: 0 !important;
        color: inherit !important;
    }

    /* Toolbar */
    #pms-tasks-page .toolbar {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        padding: 14px 18px !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    #pms-tasks-page .toolbar-right #bulkDeleteTasks {
        width: 36px !important;
        height: 36px !important;
        min-height: 36px !important;
        padding: 0 !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #pms-tasks-page .toolbar-right .view-toggle {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        background: transparent !important;
        padding: 0 !important;
        border: none !important;
    }

    #pms-tasks-page .toolbar-right .view-btn {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #475569 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: none !important;
        text-decoration: none !important;
    }

    #pms-tasks-page .toolbar-right .view-btn:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .toolbar-right .view-btn.active {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.24) !important;
    }

    /* Task List Card & Table */
    #pms-tasks-page .table-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        margin: 0 !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        overflow: hidden !important;
    }

    #pms-tasks-page .table-header {
        background: #ffffff !important;
        padding: 16px 22px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
    }

    #pms-tasks-page .table-title-icon {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        background: #eff6ff !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.92rem !important;
    }

    #pms-tasks-page .table-title h4 {
        margin: 0 !important;
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .table-title .muted {
        font-size: 0.78rem !important;
        color: #64748b !important;
    }

    #pms-tasks-page .pms-table-meta {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-size: 0.8rem !important;
        color: #64748b !important;
    }

    #pms-tasks-page .pms-table-meta strong {
        color: #0f172a !important;
        font-weight: 600 !important;
    }

    #pms-tasks-page .pms-table-meta i {
        font-size: 0.7rem !important;
        color: #64748b !important;
    }

    #pms-tasks-page #taskTable thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 12px 14px !important;
    }

    #pms-tasks-page #taskTable tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        padding: 14px !important;
    }

    /* Empty State */
    #pms-tasks-page .empty-state {
        padding: 52px 24px !important;
        text-align: center !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #pms-tasks-page .empty-state .pms-empty-icon-box {
        width: 58px !important;
        height: 58px !important;
        border-radius: 16px !important;
        background: #eff6ff !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.45rem !important;
        margin-bottom: 16px !important;
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.12) !important;
    }

    #pms-tasks-page .empty-state .pms-empty-icon-box i {
        margin: 0 !important;
        color: #2563eb !important;
        font-size: 1.4rem !important;
    }

    #pms-tasks-page .empty-state h5 {
        margin: 0 0 8px !important;
        font-size: 1.05rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    #pms-tasks-page .empty-state p {
        margin: 0 0 20px !important;
        max-width: 440px !important;
        font-size: 0.84rem !important;
        line-height: 1.5 !important;
        color: #64748b !important;
    }

    #pms-tasks-page .pms-empty-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    /* Summary Status Bar */
    #pms-tasks-page .status-bar {
        margin: 0 !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        padding: 14px 20px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 20px !important;
    }

    #pms-tasks-page .status-item {
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
    }

    #pms-tasks-page .status-item span {
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    /* Dark Mode Overrides */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .header-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .filter-panel,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .toolbar,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .table-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .table-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .status-bar {
        background: #0f172a !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
        box-shadow: 0 10px 26px rgba(2, 6, 23, 0.35) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .header-left h1,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .table-title h4,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .pms-table-meta strong,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .empty-state h5,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .status-item span {
        color: #f8fafc !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .filter-group > label {
        color: #cbd5e1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .filter-panel .form-control,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .filter-panel .form-select,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .btn-outline,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .header-actions .pms-header-search-btn,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .toolbar-right .view-btn:not(.active) {
        background-color: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        color: #e2e8f0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .pms-header-view-group {
        background: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .table-title-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page .empty-state .pms-empty-icon-box {
        background: rgba(37, 99, 235, 0.16) !important;
        color: #60a5fa !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-tasks-page #taskTable thead th {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
    }

    @media (max-width: 1100px) {
        #pms-tasks-page .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 640px) {
        #pms-tasks-page .header-card,
        #pms-tasks-page .toolbar,
        #pms-tasks-page .table-header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        #pms-tasks-page .filter-grid {
            grid-template-columns: 1fr !important;
        }
        #pms-tasks-page .pms-filter-bottom-row {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        #pms-tasks-page .pms-filter-bottom-row .search-group {
            max-width: none !important;
        }
    }
</style>

@push('js')
<script src="{{ asset('admin/assets/js/pms-task-timer-clock.js') }}?v={{ filemtime(public_path('admin/assets/js/pms-task-timer-clock.js')) }}" defer></script>
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

    if (canManage) {
        const selectAll = document.getElementById('selectAllTasks');
        const checkboxes = () => Array.from(document.querySelectorAll('.task-checkbox'));
        const bulkDelete = document.getElementById('bulkDeleteTasks');

        function updateBulkControls() {
            const boxes = checkboxes();
            const selected = boxes.filter(checkbox => checkbox.checked);
            const hasSelection = selected.length > 0;
            if (bulkDelete) bulkDelete.disabled = !hasSelection;
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

    // Stop-timer modal End time is driven by pms-task-timer-clock.js (server-synced, live, with seconds).
});
</script>
@endpush
@include('admin.tasks.partials.create-dialog')
@endsection
