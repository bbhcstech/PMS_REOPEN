@extends('admin.layout.app')

@section('content')
@include('admin.timelogs.partials.workspace-styles')

<div id="pms-timelog-edit-page" class="pms-ts-page">
    <div class="pms-ts-topbar">
        <div class="pms-ts-title-wrap">
            <div class="pms-ts-title-icon" aria-hidden="true">
                <i class="bx bx-edit"></i>
            </div>
            <div>
                <h3 class="pms-ts-title">Edit Time Log</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('timelogs.index') }}" class="text-decoration-none">Timesheet</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Log</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="pms-ts-actions">
            <a href="{{ route('timelogs.index') }}" class="btn pms-ts-btn-secondary">
                <i class="bx bx-arrow-back"></i> Back to Timesheet
            </a>
            <a href="{{ route('timelogs.show', $log->id) }}" class="btn pms-ts-btn-secondary">
                <i class="bx bx-show"></i> View Details
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card pms-ts-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('timelogs.update', $log->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Project --}}
                    <div class="col-md-6">
                        <label class="pms-ts-label">Project <span class="text-danger">*</span></label>
                        <select name="project_id" id="project_id" class="form-select" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $project->id == $log->project_id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Task --}}
                    <div class="col-md-6">
                        <label class="pms-ts-label">Task <span class="text-danger">*</span></label>
                        <select name="task_id" id="task_id" class="form-select" required>
                            <option value="">Select Task or Sub-Task</option>
                            @foreach($tasks as $task)
                                @if($task->project_id == $log->project_id)
                                    <option value="{{ $task->id }}" {{ $task->id == $log->task_id ? 'selected' : '' }}>
                                        {{ $task->parent_id ? '↳ ' : '' }}{{ $task->title }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Start --}}
                    <div class="col-md-6">
                        <label class="pms-ts-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $log->start_date }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="pms-ts-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="{{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}" required>
                        @error('start_time')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- End --}}
                    <div class="col-md-6">
                        <label class="pms-ts-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $log->end_date }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="pms-ts-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="{{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}" required>
                        @error('end_time')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Memo --}}
                    <div class="col-md-12">
                        <label class="pms-ts-label">Updated Note</label>
                        <textarea name="memo" class="form-control" rows="4">{{ $log->memo }}</textarea>
                    </div>

                    {{-- Total Hours --}}
                    <div class="col-md-6">
                        <label class="pms-ts-label">Total Hours</label>
                        <input type="text" id="total_hours" class="form-control" readonly>
                    </div>

                    {{-- Submit --}}
                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('timelogs.index') }}" class="btn pms-ts-btn-secondary">Cancel</a>
                        <button type="submit" class="btn pms-ts-btn-primary">
                            <i class="bx bx-save"></i> Update Log
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

// AJAX load tasks by project
    document.getElementById('project_id').addEventListener('change', function () {
        const projectId = this.value;
        const taskSelect = document.getElementById('task_id');
        taskSelect.innerHTML = '<option value="">Loading...</option>';

       fetch("{{ url('/project') }}/" + projectId + "/tasks")
            .then(res => res.json())
            .then(tasks => {
            taskSelect.innerHTML = '<option value="">Select Task or Sub-Task</option>';

            tasks.forEach(task => {
                let indent = task.parent_id ? '&nbsp;&nbsp;↳ ' : '';
                taskSelect.innerHTML += `<option value="${task.id}">${indent}${task.title}</option>`;
            });
        })

            .catch(() => {
                taskSelect.innerHTML = '<option value="">Failed to load</option>';
            });
    });
document.addEventListener('DOMContentLoaded', () => {
    const taskSelect = document.getElementById('task_id');
    const selectedTaskId = "{{ $log->task_id }}";
    const selectedProjectId = document.getElementById('project_id').value;

    if (selectedProjectId) {
        fetch(`{{ url('/projects') }}/${selectedProjectId}/tasks`)
            .then(res => res.json())
            .then(tasks => {
                taskSelect.innerHTML = '<option value="">Select Task or Sub-Task</option>';
                tasks.forEach(task => {
                    const indent = task.parent_id ? '&nbsp;&nbsp;↳ ' : '';
                    const selected = task.id == selectedTaskId ? 'selected' : '';
                    taskSelect.innerHTML += `<option value="${task.id}" ${selected}>${indent}${task.title}</option>`;
                });
            });
    }

    // Duration calculator
    const updateDuration = () => {
        const startDate = document.getElementById('start_date').value;
        const startTime = document.getElementById('start_time').value;
        const endDate = document.getElementById('end_date').value;
        const endTime = document.getElementById('end_time').value;

        if (startDate && startTime && endDate && endTime) {
            const start = new Date(`${startDate}T${startTime}`);
            const end = new Date(`${endDate}T${endTime}`);
            const diffMs = end - start;

            if (diffMs >= 0) {
                const hours = Math.floor(diffMs / 1000 / 60 / 60);
                const mins = Math.floor((diffMs / 1000 / 60) % 60);
                document.getElementById('total_hours').value = `${hours} hrs ${mins} mins`;
            } else {
                document.getElementById('total_hours').value = "Invalid Time Range";
            }
        }
    };

    ['start_date', 'start_time', 'end_date', 'end_time'].forEach(id => {
        document.getElementById(id).addEventListener('change', updateDuration);
    });

    updateDuration();
});
</script>
@endpush
