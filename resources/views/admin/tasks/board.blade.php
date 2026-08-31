@extends('admin.layout.app')

@section('title', 'Task Board - ' . ($project->name ?? 'Project'))

@section('content')
@php
    $canManageTasks = in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true);
    $statuses = ['Waiting for Approval', 'To Do', 'Doing', 'Incomplete', 'Completed'];
    $statusIcons = [
        'Waiting for Approval' => 'fa-clock',
        'To Do' => 'fa-clipboard-list',
        'Doing' => 'fa-spinner',
        'Incomplete' => 'fa-exclamation-circle',
        'Completed' => 'fa-check-circle',
    ];
@endphp

<div class="project-taskboard-page">
    <div class="container-fluid px-3 px-md-4">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if(isset($project))
            {{-- Standardized Project Header & 13-Tab Navigation --}}
            @include('admin.projects.partials.header', [
                'project' => $project,
                'activeTab' => 'board'
            ])
        @else
            <div class="breadcrumb mb-4">
                <i class="fas fa-columns text-primary me-2"></i>
                <span>Dashboard / <strong>Task Board</strong></span>
            </div>
        @endif

        {{-- Top Info Bar --}}
        <div class="board-header-bar mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="board-icon-badge">
                    <i class="fas fa-columns"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold text-dark">Task Kanban Board</h4>
                    <p class="text-muted small mb-0">Drag and drop tasks between columns to update status instantly</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($canManageTasks && isset($project))
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> Add Task
                    </a>
                @endif
                <a href="{{ isset($project) ? route('projects.tasks.index', $project->id) : route('tasks.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-list me-1"></i> List View
                </a>
            </div>
        </div>

        {{-- Toast Alert for Drop updates --}}
        <div id="boardNotification" class="alert alert-success d-none position-fixed bottom-0 end-0 m-4 shadow-lg" style="z-index: 1050; min-width: 280px;" role="alert">
            <i class="fas fa-check-circle me-2"></i> <span id="boardNotificationText">Status updated</span>
        </div>

        {{-- Kanban Columns Container --}}
        <div class="kanban-board-container">
            <div class="kanban-columns-row">
                @foreach($statuses as $status)
                    @php
                        $columnTasks = $tasks->where('status', $status);
                        $colIcon = $statusIcons[$status] ?? 'fa-circle';
                    @endphp
                    <div class="kanban-column-wrapper" data-column-status="{{ $status }}">
                        <div class="kanban-column-card">
                            {{-- Column Header --}}
                            <div class="kanban-column-header header-status-{{ Str::slug($status) }}">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas {{ $colIcon }}"></i>
                                    <span class="column-title">{{ $status }}</span>
                                </div>
                                <span class="badge rounded-pill bg-white text-dark shadow-sm px-2 column-count-badge" id="count-{{ Str::slug($status) }}">
                                    {{ $columnTasks->count() }}
                                </span>
                            </div>

                            {{-- Task Dropzone --}}
                            <div class="kanban-task-list"
                                 data-status="{{ $status }}"
                                 id="column-{{ Str::slug($status) }}"
                                 ondrop="dropTask(event)"
                                 ondragover="allowTaskDrop(event)"
                                 ondragenter="dragEnterColumn(event)"
                                 ondragleave="dragLeaveColumn(event)">

                                @forelse($columnTasks as $task)
                                    @php
                                        $assignedUsers = $task->assignees ?? collect();
                                        if($assignedUsers->isEmpty() && $task->assignee) {
                                            $assignedUsers = collect([$task->assignee]);
                                        }
                                        $priority = strtolower($task->priority ?? 'medium');
                                        $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'Completed';
                                        $currentUser = auth()->user();
                                        $canDrag = $currentUser && strtolower((string) $currentUser->role) === 'employee' && (
                                            $task->assignees()->where('users.id', $currentUser->id)->exists()
                                            || collect(explode(',', (string) $task->assigned_to))->contains((string) $currentUser->id)
                                        );
                                    @endphp
                                    <div class="kanban-task-card shadow-sm"
                                         id="task-{{ $task->id }}"
                                         draggable="{{ ($canDrag && $status !== 'Waiting for Approval') ? 'true' : 'false' }}"
                                         ondragstart="dragTask(event)"
                                         style="{{ !$canDrag ? 'cursor: default;' : '' }}"
                                         data-task-id="{{ $task->id }}"
                                         data-current-status="{{ $task->status }}">

                                        {{-- Top tags --}}
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="task-code-badge">{{ $task->task_short_code ?: 'TASK-' . str_pad($task->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            <span class="task-priority-badge priority-{{ $priority }}">{{ ucfirst($priority) }}</span>
                                        </div>

                                        {{-- Title --}}
                                        <a href="{{ route('tasks.show', $task->id) }}" class="task-card-title text-decoration-none" title="{{ $task->title }}">
                                            {{ $task->title }}
                                        </a>

                                        {{-- Description snippet --}}
                                        @if($task->description)
                                            <p class="task-card-desc">{{ Str::limit(strip_tags($task->description), 55) }}</p>
                                        @endif

                                        {{-- Progress Bar --}}
                                        @if($task->progress !== null && (int)$task->progress > 0)
                                            <div class="task-card-progress mb-2">
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar bg-success" style="width: {{ (int)$task->progress }}%;"></div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Footer: Due Date & Assignees --}}
                                        <div class="task-card-footer">
                                            <div class="task-due-date {{ $isOverdue ? 'overdue' : '' }}">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M') : 'No date' }}
                                                @if($isOverdue)
                                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Late</span>
                                                @endif
                                            </div>

                                            <div class="task-assignees-group">
                                                @forelse($assignedUsers->take(2) as $assignee)
                                                    <div class="task-avatar-mini" title="{{ $assignee->name }}">
                                                        @if($assignee->profile_image)
                                                            <img src="{{ asset($assignee->profile_image) }}" alt="{{ $assignee->name }}">
                                                        @else
                                                            {{ strtoupper(mb_substr($assignee->name, 0, 1)) }}
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="text-muted small"><i class="fas fa-user-slash"></i></span>
                                                @endforelse
                                                @if($assignedUsers->count() > 2)
                                                    <span class="task-avatar-more">+{{ $assignedUsers->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="kanban-empty-dropzone text-center py-4">
                                        <p class="text-muted small mb-0">No tasks in this column</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Column Footer Action --}}
                            @if($canManageTasks && isset($project) && $status !== 'Waiting for Approval')
                                <div class="kanban-column-footer">
                                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn-quick-add-task">
                                        <i class="fas fa-plus"></i> Add Task
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== KANBAN TASK BOARD MODERN STYLES ===== */
    .project-taskboard-page {
        min-height: 100vh;
        padding: 20px 0 40px;
        background: #f8fafc;
    }

    .board-header-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }

    .board-icon-badge {
        width: 42px;
        height: 42px;
        background: #e0f2fe;
        color: #0284c7;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .kanban-board-container {
        overflow-x: auto;
        padding-bottom: 16px;
    }

    .kanban-columns-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(270px, 1fr));
        gap: 16px;
        align-items: start;
    }

    .kanban-column-card {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 280px);
        min-height: 380px;
    }

    .kanban-column-header {
        padding: 12px 16px;
        border-radius: 11px 11px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 0.88rem;
        background: #e2e8f0;
        color: #334155;
    }

    .header-status-waiting-for-approval { background: #fef3c7; color: #92400e; }
    .header-status-to-do { background: #e2e8f0; color: #334155; }
    .header-status-doing { background: #e0f2fe; color: #0369a1; }
    .header-status-incomplete { background: #fee2e2; color: #991b1b; }
    .header-status-completed { background: #dcfce7; color: #15803d; }

    .column-title {
        text-transform: capitalize;
    }

    .column-count-badge {
        font-size: 0.76rem;
        font-weight: 800;
    }

    .kanban-task-list {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
        flex-grow: 1;
        transition: background-color 0.2s ease;
    }

    .kanban-task-list.drag-over {
        background-color: #e2e8f0;
        border-radius: 8px;
    }

    .kanban-task-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        cursor: grab;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        position: relative;
    }

    .kanban-task-card:active {
        cursor: grabbing;
    }

    .kanban-task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08) !important;
        border-color: #cbd5e1;
    }

    .task-code-badge {
        font-size: 0.72rem;
        font-weight: 800;
        color: #0f766e;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }

    .task-priority-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .task-priority-badge.priority-low { background: #f1f5f9; color: #475569; }
    .task-priority-badge.priority-medium { background: #fef3c7; color: #b45309; }
    .task-priority-badge.priority-high { background: #fed7aa; color: #c2410c; }
    .task-priority-badge.priority-critical { background: #fee2e2; color: #b91c1c; }

    .task-card-title {
        display: block;
        font-weight: 700;
        font-size: 0.92rem;
        color: #0f172a;
        margin-bottom: 6px;
        line-height: 1.35;
        transition: color 0.15s ease;
    }

    .task-card-title:hover {
        color: #0f766e;
    }

    .task-card-desc {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .task-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 8px;
        border-top: 1px dashed #f1f5f9;
        font-size: 0.78rem;
    }

    .task-due-date {
        color: #64748b;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .task-due-date.overdue {
        color: #ef4444;
        font-weight: 700;
    }

    .task-assignees-group {
        display: flex;
        align-items: center;
    }

    .task-avatar-mini {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0f766e;
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #ffffff;
        margin-left: -6px;
        overflow: hidden;
    }

    .task-avatar-mini:first-child {
        margin-left: 0;
    }

    .task-avatar-mini img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .task-avatar-more {
        font-size: 0.68rem;
        color: #64748b;
        margin-left: 4px;
        font-weight: 700;
    }

    .kanban-empty-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.4);
    }

    .kanban-column-footer {
        padding: 8px 12px;
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 0 0 11px 11px;
    }

    .btn-quick-add-task {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 6px 12px;
        border-radius: 6px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .btn-quick-add-task:hover {
        background: #edf8f2;
        border-color: #0f766e;
        color: #0f766e;
    }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    window.allowTaskDrop = function(ev) {
        ev.preventDefault();
    };

    window.dragEnterColumn = function(ev) {
        const dropzone = ev.currentTarget;
        if (dropzone && dropzone.classList.contains('kanban-task-list')) {
            dropzone.classList.add('drag-over');
        }
    };

    window.dragLeaveColumn = function(ev) {
        const dropzone = ev.currentTarget;
        if (dropzone && dropzone.classList.contains('kanban-task-list')) {
            dropzone.classList.remove('drag-over');
        }
    };

    window.dragTask = function(ev) {
        ev.dataTransfer.setData("text/plain", ev.target.id);
        ev.dataTransfer.effectAllowed = "move";
    };

    window.dropTask = function(ev) {
        ev.preventDefault();
        const dropzone = ev.currentTarget;
        dropzone.classList.remove('drag-over');

        const taskElementId = ev.dataTransfer.getData("text/plain");
        const draggedCard = document.getElementById(taskElementId);
        if (!draggedCard) return;

        const taskId = draggedCard.getAttribute('data-task-id');
        const previousStatus = draggedCard.getAttribute('data-current-status');
        const newStatus = dropzone.getAttribute('data-status');

        if (newStatus === previousStatus) return;

        if (newStatus === 'Waiting for Approval') {
            showNotification("Cannot move task to 'Waiting for Approval' via drag & drop.", 'danger');
            return;
        }

        // Remove empty state from target if present
        const emptyState = dropzone.querySelector('.kanban-empty-dropzone');
        if (emptyState) {
            emptyState.remove();
        }

        // Move DOM element immediately
        dropzone.appendChild(draggedCard);
        draggedCard.setAttribute('data-current-status', newStatus);

        // Update column counters
        updateColumnCounters();

        // AJAX update
        const updateUrl = "{{ url('tasks') }}/" + taskId + "/update-status";

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification("Task moved to " + newStatus, 'success');
            } else {
                throw new Error(data.message || 'Status update failed.');
            }
        })
        .catch(err => {
            console.error('Drag drop status update failed:', err);
            showNotification("Failed to update status: " + err.message, 'danger');
            // Rollback DOM position
            const previousDropzone = document.getElementById('column-' + slugify(previousStatus));
            if (previousDropzone) {
                previousDropzone.appendChild(draggedCard);
                draggedCard.setAttribute('data-current-status', previousStatus);
                updateColumnCounters();
            }
        });
    };

    function updateColumnCounters() {
        document.querySelectorAll('.kanban-task-list').forEach(col => {
            const status = col.getAttribute('data-status');
            const count = col.querySelectorAll('.kanban-task-card').length;
            const badge = document.getElementById('count-' + slugify(status));
            if (badge) {
                badge.textContent = count;
            }
        });
    }

    function slugify(text) {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    function showNotification(message, type) {
        const notifyEl = document.getElementById('boardNotification');
        const textEl = document.getElementById('boardNotificationText');
        if (!notifyEl || !textEl) return;

        notifyEl.className = `alert alert-${type} position-fixed bottom-0 end-0 m-4 shadow-lg`;
        textEl.textContent = message;
        notifyEl.classList.remove('d-none');

        setTimeout(() => {
            notifyEl.classList.add('d-none');
        }, 3500);
    }
});
</script>
@endpush
@endsection
