@extends('layouts.developer')

@section('title', 'My Work')
@section('page_title', 'My Work & Assigned Tasks')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- TOP FILTER BAR -->
    <div class="dev-card" style="margin-bottom: 0; padding: 20px;">
        <form method="GET" action="{{ route('developer.my-work') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 260px;">
                <div style="position: relative; width: 100%;">
                    <i class="bx bx-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--slate-muted); font-size: 18px;"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search work by title, description, company, project..." style="width: 100%; padding: 9px 12px 9px 38px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-weight: 500;">
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <select name="status" onchange="this.form.submit()" style="padding: 9px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-weight: 600; background: #ffffff;">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="assigned" {{ $statusFilter === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="on_hold" {{ $statusFilter === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>

                <select name="priority" onchange="this.form.submit()" style="padding: 9px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-weight: 600; background: #ffffff;">
                    <option value="all" {{ $priorityFilter === 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="high" {{ $priorityFilter === 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="medium" {{ $priorityFilter === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="low" {{ $priorityFilter === 'low' ? 'selected' : '' }}>Low Priority</option>
                </select>

                <button type="submit" style="padding: 9px 16px; border-radius: var(--radius-md); background: var(--slate-dark); color: #ffffff; border: none; font-size: 13px; font-weight: 700; cursor: pointer;">
                    Filter
                </button>

                @if($statusFilter !== 'all' || $priorityFilter !== 'all' || !empty($search))
                    <a href="{{ route('developer.my-work') }}" style="padding: 9px 14px; border-radius: var(--radius-md); background: #f1f5f9; color: var(--slate-muted); text-decoration: none; font-size: 13px; font-weight: 600;">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- WORK TASKS LIST -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @forelse($tasks as $task)
        <div class="dev-card" style="margin-bottom: 0; padding: 24px; border-left: 4px solid {{ $task->status === 'completed' ? '#10b981' : ($task->status === 'in_progress' ? '#2563eb' : '#f59e0b') }};">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-family: monospace; font-weight: 800; background: #f1f5f9; color: var(--slate-dark); padding: 3px 8px; border-radius: 4px;">
                            TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        <span style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; background: {{ strtolower($task->priority ?? '') === 'high' ? '#fef2f2' : '#f1f5f9' }}; color: {{ strtolower($task->priority ?? '') === 'high' ? '#dc2626' : '#475569' }}; border: 1px solid {{ strtolower($task->priority ?? '') === 'high' ? '#fecaca' : '#cbd5e1' }};">
                            {{ strtoupper($task->priority ?? 'MEDIUM') }} PRIORITY
                        </span>
                        <span class="status-badge status-{{ $task->status }}">
                            ● {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>

                    <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin-bottom: 6px;">
                        {{ $task->title }}
                    </h3>

                    <p style="font-size: 13.5px; color: var(--slate-body); line-height: 1.5; max-width: 850px;">
                        {{ $task->description ?? 'No detailed instructions specified for this work assignment.' }}
                    </p>
                </div>

                <!-- UPDATE STATUS DROPDOWN -->
                <div style="display: flex; align-items: center; gap: 12px;">
                    <form method="POST" action="{{ route('developer.tasks.status', $task->id) }}" style="margin: 0;">
                        @csrf
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                            <span style="font-size: 11px; font-weight: 700; color: var(--slate-muted); text-transform: uppercase;">Update Status:</span>
                            <select name="status" onchange="this.form.submit()" style="padding: 8px 14px; border-radius: var(--radius-md); font-size: 13px; font-weight: 700; border: 1.5px solid var(--primary-border); background: var(--primary-light); color: var(--primary); cursor: pointer; box-shadow: var(--shadow-sm);">
                                <option value="assigned" {{ $task->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="on_hold" {{ $task->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TASK METADATA FOOTER GRID -->
            <div style="background: #f8fafc; padding: 14px 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; font-size: 12.5px;">
                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">COMPANY</span>
                    <strong style="color: var(--slate-dark); font-size: 13px;">{{ $task->company_name ?? 'Central Platform' }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">PROJECT / MODULE</span>
                    <strong style="color: var(--slate-dark); font-size: 13px;">{{ $task->project_name ?? 'Development Module' }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">ASSIGNED DATE</span>
                    <strong style="color: var(--slate-dark); font-size: 13px;">{{ \Carbon\Carbon::parse($task->created_at)->format('d M Y') }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">DEADLINE</span>
                    <strong style="color: {{ !empty($task->due_date) && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed' ? '#dc2626' : 'var(--slate-dark)' }}; font-size: 13px;">
                        {{ !empty($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No Deadline' }}
                    </strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">ESTIMATED HOURS</span>
                    <strong style="color: var(--slate-dark); font-size: 13px;">{{ $task->estimate_hours ?? 8 }} Hours</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">ASSIGNED BY</span>
                    <strong style="color: var(--primary); font-size: 13px;">{{ $task->assigner_name ?? 'Super Admin' }}</strong>
                </div>
            </div>

            <!-- PROGRESS UPDATE FORM & TIMELINE TOGGLE -->
            <div style="margin-top: 16px; padding-top: 14px; border-top: 1px dashed var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <form method="POST" action="{{ route('developer.tasks.notes', $task->id) }}" style="display: flex; align-items: center; gap: 10px; flex: 1; max-width: 600px;">
                    @csrf
                    <input type="text" name="note" placeholder="Add progress update (e.g. Completed API validation)..." required style="flex: 1; padding: 7px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 12.5px;">
                    <button type="submit" style="padding: 7px 14px; border-radius: var(--radius-sm); background: var(--primary); color: #ffffff; border: none; font-size: 12px; font-weight: 700; cursor: pointer;">
                        Post Update
                    </button>
                </form>

                <button type="button" onclick="openTaskDetailsModal({{ $task->id }})" style="background: none; border: 1px solid var(--border-color); padding: 7px 14px; border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 700; color: var(--slate-dark); cursor: pointer;">
                    <i class="bx bx-show"></i> Task Details & Timeline
                </button>
            </div>
        </div>
        @empty
        <div class="dev-card" style="text-align: center; padding: 48px 24px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: #f1f5f9; color: var(--slate-muted); display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px;">
                <i class="bx bx-task-x"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-dark); margin-bottom: 6px;">No Work Assigned</h3>
            <p style="font-size: 13.5px; color: var(--slate-muted); max-width: 450px; margin: 0 auto 16px;">
                You currently have no active development tasks matching your filters. Work assigned by the Super Admin will appear here automatically.
            </p>
        </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    @if($tasks->hasPages())
    <div style="padding: 16px; background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); display: flex; justify-content: center;">
        {{ $tasks->appends(request()->query())->links() }}
    </div>
    @endif

</div>

<!-- TASK DETAILS MODAL DRAWER -->
<div id="taskModalDrawer" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: var(--radius-xl); max-width: 650px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 28px; box-shadow: var(--shadow-lg); position: relative;">
        <button onclick="closeTaskDetailsModal()" style="position: absolute; right: 20px; top: 20px; background: none; border: none; font-size: 24px; color: var(--slate-muted); cursor: pointer;">
            &times;
        </button>

        <div id="modalContentArea">
            <div style="text-align: center; padding: 40px; color: var(--slate-muted);">
                <i class="bx bx-loader-alt bx-spin" style="font-size: 32px; color: var(--primary);"></i>
                <p style="margin-top: 10px; font-size: 13px; font-weight: 600;">Loading task telemetry details...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openTaskDetailsModal(taskId) {
        const modal = document.getElementById('taskModalDrawer');
        const content = document.getElementById('modalContentArea');
        modal.style.display = 'flex';

        fetch(`/developer/tasks/${taskId}/details`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    content.innerHTML = `<p style="color: #dc2626;">${data.message}</p>`;
                    return;
                }
                const t = data.task;
                const notes = data.notes || [];

                content.innerHTML = `
                    <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;">TASK TELEMETRY & TIMELINE</span>
                        <h2 style="font-size: 20px; font-weight: 800; color: var(--slate-dark); margin-top: 4px;">${t.title}</h2>
                        <p style="font-size: 13.5px; color: var(--slate-body); margin-top: 8px; line-height: 1.5;">${t.description || 'No description provided.'}</p>
                    </div>

                    <!-- TASK TIMELINE VISUALIZATION -->
                    <div style="margin-bottom: 24px; background: #f8fafc; padding: 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                        <strong style="font-size: 12px; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 12px;">TASK TIMELINE STAGES</strong>
                        <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                            <div style="text-align: center; flex: 1;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px; font-weight: 800; font-size: 12px;">✓</div>
                                <span style="font-size: 11px; font-weight: 700; color: var(--slate-dark);">Assigned</span>
                            </div>
                            <div style="text-align: center; flex: 1;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: ${t.status === 'in_progress' || t.status === 'completed' ? '#10b981' : '#cbd5e1'}; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px; font-weight: 800; font-size: 12px;">2</div>
                                <span style="font-size: 11px; font-weight: 700; color: var(--slate-dark);">In Progress</span>
                            </div>
                            <div style="text-align: center; flex: 1;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: ${t.status === 'completed' ? '#10b981' : '#cbd5e1'}; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px; font-weight: 800; font-size: 12px;">3</div>
                                <span style="font-size: 11px; font-weight: 700; color: var(--slate-dark);">Completed</span>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS HISTORY NOTES -->
                    <div>
                        <strong style="font-size: 13px; color: var(--slate-dark); display: block; margin-bottom: 10px;">Progress Updates (${notes.length})</strong>
                        <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                            ${notes.map(n => `
                                <div style="padding: 10px; background: #f1f5f9; border-radius: 8px; font-size: 12.5px;">
                                    <div style="display: flex; justify-content: space-between; color: var(--slate-muted); font-size: 11px; margin-bottom: 2px;">
                                        <strong>Update</strong>
                                        <span>${new Date(n.created_at).toLocaleString()}</span>
                                    </div>
                                    <p style="color: var(--slate-dark); font-weight: 600;">${n.note}</p>
                                </div>
                            `).join('') || '<p style="font-size: 12.5px; color: var(--slate-muted);">No progress notes posted yet.</p>'}
                        </div>
                    </div>
                `;
            });
    }

    function closeTaskDetailsModal() {
        document.getElementById('taskModalDrawer').style.display = 'none';
    }
</script>
@endsection
