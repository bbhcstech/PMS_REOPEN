@extends('admin.layout.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Log Time</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('timelogs.store') }}">
        @csrf

        <div class="row g-4">

            {{-- Auto Generated Code --}}
            <div class="col-md-6">
                <label class="form-label">Generated Code</label>
                <input type="text" id="generated_code" class="form-control" readonly>
            </div>

            {{-- Project --}}
            <div class="col-md-6">
                <label class="form-label">Project <span class="text-danger">*</span></label>
                <select name="project_id" id="project_id" class="form-select" required>
                    <option value="">Select Project</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}"
                                data-code="{{ $proj->project_code }}"
                                {{ (old('project_id', $selectedProjectId ?? request('project_id')) == $proj->id) ? 'selected' : '' }}>
                            {{ $proj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Task --}}
            <div class="col-md-6">
                <label class="form-label">Task <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center gap-2">
                <select name="task_id" id="task_id" class="form-control flex-grow-1" style="min-width: 0;" required>
                    <option value="">Select Task</option>
                    @foreach($tasks as $t)
                        <option value="{{ $t->id }}"
                                {{ (old('task_id', $selectedTaskId ?? request('task_id')) == $t->id) ? 'selected' : '' }}>
                            {{ $t->title }}
                        </option>
                    @endforeach
                </select>
                @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager', 'administrator', 'superadmin'], true) || \Illuminate\Support\Facades\Auth::guard('super_admin')->check())
                    <a href="{{ route('tasks.create') }}" id="add_timelog_task" class="btn btn-primary flex-shrink-0" target="_blank" rel="noopener" title="Add Task" aria-label="Add Task" style="width: 44px; height: 44px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                        <span aria-hidden="true" style="font-size: 24px; line-height: 1;">+</span>
                    </a>
                @endif
                </div>
            </div>


            {{-- Employee (Admins, Managers, HR) --}}
            @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true))
                <div class="col-md-6">
                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} ({{ $emp->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="employee_id" value="{{ auth()->id() }}">
            @endif

            {{-- User Info --}}
            <div class="col-md-12">
                <div class="border p-3 rounded bg-light">
                    <strong>{{ auth()->user()->name }}</strong><br>
                    <small>It's you</small><br>
                    <span class="text-muted">{{ auth()->user()->designation ?? 'Employee' }}</span>
                </div>
            </div>

            {{-- Start Date --}}
            <div class="col-md-6">
                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control" required>
            </div>

            {{-- Start Time --}}
            <div class="col-md-6">
                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                <input type="time" name="start_time" id="start_time" class="form-control" required>
            </div>

            {{-- End Date --}}
            <div class="col-md-6">
                <label class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control" required>
            </div>

            {{-- End Time --}}
            <div class="col-md-6">
                <label class="form-label">End Time <span class="text-danger">*</span></label>
                <input type="time" name="end_time" id="end_time" class="form-control" required>
            </div>

            {{-- Memo --}}
            <div class="col-md-12">
                <label class="form-label">Memo</label>
                <textarea name="memo" class="form-control" rows="5" placeholder="e.g. Worked on branding"></textarea>
            </div>

            {{-- Total Hours --}}
            <div class="col-md-6">
                <label class="form-label">Total Hours</label>
                <input type="text" id="total_hours" class="form-control" readonly>
            </div>

            {{-- Submit --}}
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save Log</button>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const now = new Date();
    const pad = n => n.toString().padStart(2, '0');
    const isoDate = now.toISOString().split('T')[0];

    // default date/time
    document.getElementById('start_date').value = isoDate;
    document.getElementById('end_date').value = isoDate;
    document.getElementById('start_time').value = pad(now.getHours()) + ':' + pad(now.getMinutes());
    document.getElementById('end_time').value = pad(now.getHours()) + ':' + pad(now.getMinutes());

    // Generate Auto Code
    function generateCode() {
        const selected = document.querySelector('#project_id option:checked');
        let projectCode = selected?.dataset.code ?? 'Xink25-26/';

        let nextNumber = {{ ($lastId ?? 0) + 1 }};
        let padded = String(nextNumber).padStart(4, '0');

        document.getElementById('generated_code').value = projectCode + padded;
    }

    // Task Loader
    const projectSelect = document.getElementById('project_id');
    const taskSelect = document.getElementById('task_id');
    const addTaskLink = document.getElementById('add_timelog_task');
    let taskRequest = 0;
    let refreshAfterTaskCreation = false;

    function updateAddTaskLink() {
        if (!addTaskLink) return;
        const url = new URL(@json(route('tasks.create')), window.location.origin);
        if (projectSelect.value) url.searchParams.set('project_id', projectSelect.value);
        addTaskLink.href = url.toString();
    }

    function loadTasks(preserveSelection = false) {
        generateCode();
        updateAddTaskLink();

        const projectId = projectSelect.value;
        const selectedTask = preserveSelection ? taskSelect.value : '';
        const requestId = ++taskRequest;
        taskSelect.innerHTML = '<option value="">Loading...</option>';

        if (!projectId) {
            taskSelect.innerHTML = '<option value="">Select Task</option>';
            return;
        }

        // Use named route so URL is always correct
        const url = "{{ route('timelogs.tasks.byProject', ':id') }}".replace(':id', projectId);

        fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(res => {
                if (!res.ok) throw new Error('Unable to load tasks');
                return res.json();
            })
            .then(tasks => {
                if (requestId !== taskRequest) return;
                taskSelect.innerHTML = '<option value="">Select Task</option>';

                if (!tasks.length) {
                    taskSelect.innerHTML = '<option value="">No tasks found for this project</option>';
                    return;
                }

                tasks.forEach(task => {
                    const opt = document.createElement('option');
                    opt.value = task.id;
                    opt.innerText = task.title;
                    taskSelect.appendChild(opt);
                });
                if (selectedTask && tasks.some(task => String(task.id) === selectedTask)) {
                    taskSelect.value = selectedTask;
                } else if (preserveSelection && tasks.length === 1) {
                    taskSelect.value = String(tasks[0].id);
                }
            })
            .catch(() => {
                if (requestId !== taskRequest) return;
                taskSelect.innerHTML = '<option value="">Failed to Load</option>';
            });
    }

    projectSelect.addEventListener('change', () => loadTasks());
    if (addTaskLink) {
        addTaskLink.addEventListener('click', () => {
            updateAddTaskLink();
            refreshAfterTaskCreation = true;
        });
    }
    window.addEventListener('focus', () => {
        if (refreshAfterTaskCreation) loadTasks(true);
    });

    // Calculate Duration
    function updateDuration() {
        const sd = document.getElementById('start_date').value;
        const st = document.getElementById('start_time').value;
        const ed = document.getElementById('end_date').value;
        const et = document.getElementById('end_time').value;

        if (sd && st && ed && et) {
            const start = new Date(`${sd}T${st}`);
            const end = new Date(`${ed}T${et}`);
            const diff = end - start;

            if (diff >= 0) {
                const hours = Math.floor(diff / 1000 / 60 / 60);
                const mins = Math.floor((diff / 1000 / 60) % 60);
                document.getElementById('total_hours').value = `${hours} hrs ${mins} mins`;
            } else {
                document.getElementById('total_hours').value = "Invalid Time Range";
            }
        }
    }

    ['start_date', 'start_time', 'end_date', 'end_time']
        .forEach(id => document.getElementById(id).addEventListener('change', updateDuration));

    // Initial code
    generateCode();
    updateAddTaskLink();
    if (projectSelect.value) loadTasks(true);
});
</script>
@endpush
