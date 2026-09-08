@extends('layouts.developer')

@section('title', 'My Work')
@section('page_title', 'My Work & Assigned Tasks')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- TOP FILTER BAR -->
    <div class="dev-card" style="margin-bottom: 0; padding: 22px;">
        <form method="GET" action="{{ route('developer.my-work') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
                <div style="position: relative; width: 100%;">
                    <i class="bx bx-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--slate-muted); font-size: 19px;"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search work by title, description, company, project..." style="width: 100%; padding: 10px 14px 10px 42px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 500; outline: none; background: #ffffff; color: var(--slate-dark); box-shadow: var(--shadow-xs);">
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <select name="status" onchange="this.form.submit()" style="padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-weight: 700; background: #ffffff; color: var(--slate-dark); box-shadow: var(--shadow-xs); cursor: pointer;">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="assigned" {{ $statusFilter === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="on_hold" {{ $statusFilter === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>

                <select name="priority" onchange="this.form.submit()" style="padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-weight: 700; background: #ffffff; color: var(--slate-dark); box-shadow: var(--shadow-xs); cursor: pointer;">
                    <option value="all" {{ $priorityFilter === 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="high" {{ $priorityFilter === 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="medium" {{ $priorityFilter === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="low" {{ $priorityFilter === 'low' ? 'selected' : '' }}>Low Priority</option>
                </select>

                <button type="submit" style="padding: 10px 18px; border-radius: var(--radius-md); background: var(--slate-dark); color: #ffffff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: var(--shadow-xs); transition: background 0.2s;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='var(--slate-dark)'">
                    Filter
                </button>

                @if($statusFilter !== 'all' || $priorityFilter !== 'all' || !empty($search))
                    <a href="{{ route('developer.my-work') }}" style="padding: 10px 16px; border-radius: var(--radius-md); background: #f1f5f9; color: var(--slate-muted); text-decoration: none; font-size: 13px; font-weight: 700;">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- WORK TASKS LIST -->
    <div style="display: flex; flex-direction: column; gap: 18px;">
        @forelse($tasks as $task)
        <div class="dev-card" style="margin-bottom: 0; padding: 26px; border-left: 4px solid {{ $task->status === 'completed' ? '#10b981' : ($task->status === 'in_progress' ? '#2563eb' : '#f59e0b') }};">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 18px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-family: monospace; font-weight: 800; background: #f1f5f9; color: var(--slate-dark); padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        <span style="font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; background: {{ strtolower($task->priority ?? '') === 'high' ? '#fef2f2' : '#f1f5f9' }}; color: {{ strtolower($task->priority ?? '') === 'high' ? '#dc2626' : '#475569' }}; border: 1px solid {{ strtolower($task->priority ?? '') === 'high' ? '#fecaca' : '#cbd5e1' }};">
                            {{ strtoupper($task->priority ?? 'MEDIUM') }} PRIORITY
                        </span>
                        <span class="status-badge status-{{ $task->status }}">
                            ● {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>

                    <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-dark); margin-bottom: 8px; line-height: 1.3;">
                        {{ $task->title }}
                    </h3>

                    <p style="font-size: 14px; color: var(--slate-body); line-height: 1.55; max-width: 880px;">
                        {{ $task->description ?? 'No detailed instructions specified for this work assignment.' }}
                    </p>
                </div>

                <!-- UPDATE STATUS DROPDOWN -->
                <div style="display: flex; align-items: center; gap: 12px;">
                    <form method="POST" action="{{ route('developer.tasks.status', $task->id) }}" style="margin: 0;">
                        @csrf
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">Update Status:</span>
                            <select name="status" onchange="this.form.submit()" style="padding: 8px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 800; border: 1.5px solid var(--primary-border); background: var(--primary-light); color: var(--primary); cursor: pointer; box-shadow: var(--shadow-xs);">
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
            <div style="background: #f8fafc; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; font-size: 13px;">
                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">COMPANY</span>
                    <strong style="color: var(--slate-dark); font-size: 13.5px; font-weight: 700;">{{ $task->company_name ?? 'Central Platform' }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">PROJECT / MODULE</span>
                    <strong style="color: var(--slate-dark); font-size: 13.5px; font-weight: 700;">{{ $task->project_name ?? 'Development Module' }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">ASSIGNED DATE</span>
                    <strong style="color: var(--slate-dark); font-size: 13.5px; font-weight: 700;">{{ \Carbon\Carbon::parse($task->created_at)->format('d M Y') }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">DEADLINE</span>
                    <strong style="color: {{ !empty($task->due_date) && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed' ? '#dc2626' : 'var(--slate-dark)' }}; font-size: 13.5px; font-weight: 700;">
                        {{ !empty($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No Deadline' }}
                    </strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">ESTIMATED HOURS</span>
                    <strong style="color: var(--slate-dark); font-size: 13.5px; font-weight: 700;">{{ $task->estimate_hours ?? 8 }} Hours</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">ASSIGNED BY</span>
                    <strong style="color: var(--primary); font-size: 13.5px; font-weight: 700;">{{ $task->assigner_name ?? 'Super Admin' }}</strong>
                </div>
            </div>

            <!-- PROGRESS UPDATE FORM & TIMELINE TOGGLE -->
            <div style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <form method="POST" action="{{ route('developer.tasks.notes', $task->id) }}" style="display: flex; align-items: center; gap: 10px; flex: 1; max-width: 620px;">
                    @csrf
                    <input type="text" name="note" placeholder="Add progress update (e.g. Completed API validation)..." required style="flex: 1; padding: 8px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 13px; font-weight: 500; outline: none;">
                    <button type="submit" style="padding: 8px 16px; border-radius: var(--radius-sm); background: var(--primary); color: #ffffff; border: none; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='var(--primary-hover)'" onmouseout="this.style.background='var(--primary)'">
                        Post Update
                    </button>
                </form>

                <button type="button" onclick="openTaskDetailsModal({{ $task->id }})" style="background: #ffffff; border: 1px solid var(--border-color); padding: 8px 16px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 700; color: var(--slate-dark); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: var(--shadow-xs);">
                    <i class="bx bx-show" style="font-size: 17px; color: var(--primary);"></i> Task Details & Timeline
                </button>
            </div>
        </div>
        @empty
        <div class="dev-card" style="text-align: center; padding: 56px 24px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; color: var(--slate-muted); display: flex; align-items: center; justify-content: center; font-size: 34px; margin: 0 auto 16px;">
                <i class="bx bx-task-x"></i>
            </div>
            <h3 style="font-size: 19px; font-weight: 800; color: var(--slate-dark); margin-bottom: 6px;">No Work Assigned</h3>
            <p style="font-size: 14px; color: var(--slate-muted); max-width: 480px; margin: 0 auto 16px;">
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
    <div style="background: #ffffff; border-radius: var(--radius-xl); max-width: 680px; width: 100%; max-height: 88vh; overflow-y: auto; padding: 32px; box-shadow: var(--shadow-lg); position: relative;">
        <button onclick="closeTaskDetailsModal()" style="position: absolute; right: 24px; top: 24px; background: none; border: none; font-size: 26px; color: var(--slate-muted); cursor: pointer; line-height: 1;">
            &times;
        </button>

        <div id="modalContentArea">
            <div style="text-align: center; padding: 48px; color: var(--slate-muted);">
                <i class="bx bx-loader-alt bx-spin" style="font-size: 36px; color: var(--primary);"></i>
                <p style="margin-top: 12px; font-size: 13.5px; font-weight: 600;">Loading task details...</p>
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
                    content.innerHTML = `<p style="color: #dc2626; font-weight:700;">${data.message}</p>`;
                    return;
                }
                const t = data.task;
                const notes = data.notes || [];

                content.innerHTML = `
                    <div style="margin-bottom: 22px; border-bottom: 1px solid var(--border-color); padding-bottom: 18px;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.6px;">TASK TELEMETRY & TIMELINE</span>
                        <h2 style="font-size: 22px; font-weight: 800; color: var(--slate-dark); margin-top: 6px;">${t.title}</h2>
                        <p style="font-size: 14px; color: var(--slate-body); margin-top: 10px; line-height: 1.55;">${t.description || 'No description provided.'}</p>
                    </div>

                    <!-- TASK TIMELINE VISUALIZATION -->
                    <div style="margin-bottom: 24px; background: #f8fafc; padding: 20px; border-radius: 14px; border: 1px solid var(--border-color);">
                        <strong style="font-size: 11.5px; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 14px; letter-spacing: 0.5px;">TASK TIMELINE STAGES</strong>
                        <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                            <div style="text-align: center; flex: 1;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-weight: 800; font-size: 14px;">✓</div>
                                <span style="font-size: 12px; font-weight: 700; color: var(--slate-dark);">Assigned</span>
                            </div>
                            <div style="text-align: center; flex: 1;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: ${t.status === 'in_progress' || t.status === 'completed' ? '#10b981' : '#cbd5e1'}; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-weight: 800; font-size: 14px;">2</div>
                                <span style="font-size: 12px; font-weight: 700; color: var(--slate-dark);">In Progress</span>
                            </div>
                            <div style="text-align: center; flex: 1;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: ${t.status === 'completed' ? '#10b981' : '#cbd5e1'}; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-weight: 800; font-size: 14px;">3</div>
                                <span style="font-size: 12px; font-weight: 700; color: var(--slate-dark);">Completed</span>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS HISTORY NOTES -->
                    <div>
                        <strong style="font-size: 14px; color: var(--slate-dark); display: block; margin-bottom: 12px; font-weight: 800;">Progress Updates (${notes.length})</strong>
                        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 220px; overflow-y: auto;">
                            ${notes.map(n => `
                                <div style="padding: 12px 14px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border-color); font-size: 13px;">
                                    <div style="display: flex; justify-content: space-between; color: var(--slate-muted); font-size: 11.5px; margin-bottom: 4px;">
                                        <strong>Update</strong>
                                        <span>${new Date(n.created_at).toLocaleString()}</span>
                                    </div>
                                    <p style="color: var(--slate-dark); font-weight: 600;">${n.note}</p>
                                </div>
                            `).join('') || '<p style="font-size: 13px; color: var(--slate-muted);">No progress notes posted yet.</p>'}
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
