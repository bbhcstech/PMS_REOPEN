@extends('admin.layout.app')

@section('title', 'Employee Salary Assignments')

@section('content')
@include('admin.payroll.partials.styles')

<div class="payroll-container">
    @include('admin.payroll.partials.tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="pr-card">
        <div class="pr-card-head">
            <h2>👤 Employee Salary Assignments</h2>
            <div class="spacer"></div>
            <button class="pr-btn pr-btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                + Assign Salary
            </button>
        </div>

        <!-- Filters Matching ui.html -->
        <form method="GET" action="{{ route('payroll.employee-salary.index') }}" class="pr-filters">
            <div class="field search-field">
                <label>Search Employee</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, or email..." />
            </div>
            <div class="field">
                <label>Department</label>
                <select name="department_id" onchange="this.form.submit()">
                    <option value="all">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->dpt_name ?? $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Designation</label>
                <select name="designation_id" onchange="this.form.submit()">
                    <option value="all">All Designations</option>
                    @foreach($designations as $des)
                        <option value="{{ $des->id }}" {{ request('designation_id') == $des->id ? 'selected' : '' }}>
                            {{ $des->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="pr-btn">Filter</button>
                <a href="{{ route('payroll.employee-salary.index') }}" class="pr-btn text-muted">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>Emp ID</th>
                        <th>Employee</th>
                        <th>Designation</th>
                        <th>Grade</th>
                        <th class="text-end">Actual Basic</th>
                        <th>HRA</th>
                        <th>Special Allowance</th>
                        <th>Effective From</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    @php
                        $user = $assignment->user;
                        $empDetail = $user?->employeeDetail;
                        $empId = $empDetail?->employee_id ?: ('EMP' . str_pad($user?->id ?? 0, 3, '0', STR_PAD_LEFT));
                        $initials = strtoupper(substr($user?->name ?? 'U', 0, 2));
                        $deptName = $empDetail?->department?->dpt_name ?? ($empDetail?->department?->name ?? 'General');
                    @endphp
                    <tr>
                        <td><b>{{ $empId }}</b></td>
                        <td>
                            <div class="cell-employee">
                                <div class="avatar-badge">{{ $initials }}</div>
                                <div>
                                    <div class="name">{{ $user?->name ?? 'Unknown' }}</div>
                                    <div class="meta">{{ $deptName }} · {{ $user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $assignment->designation?->name ?: ($empDetail?->designation?->name ?: 'Staff') }}</td>
                        <td>
                            <span class="pr-pill calculated">{{ $assignment->grade ?: 'Standard' }}</span>
                        </td>
                        <td class="num">₹{{ number_format($assignment->actual_basic_salary, 2) }}</td>
                        <td>
                            {{ $assignment->hra_type === 'percentage' ? ($assignment->hra_value . '%') : ('₹' . number_format($assignment->hra_value, 2)) }}
                        </td>
                        <td>
                            {{ $assignment->special_allowance_type === 'percentage' ? ($assignment->special_allowance_value . '%') : ('₹' . number_format($assignment->special_allowance_value, 2)) }}
                        </td>
                        <td>
                            {{ $assignment->effective_from ? Carbon\Carbon::parse($assignment->effective_from)->format('d M Y') : '01 Jan 2025' }}
                        </td>
                        <td>
                            <span class="pr-pill {{ $assignment->status === 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="pr-btn pr-btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $assignment->id }}">
                                ✏️ Edit
                            </button>
                            <form action="{{ route('payroll.employee-salary.destroy', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this employee salary assignment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pr-btn pr-btn-sm text-danger">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Assignment Modal -->
                    <div class="modal fade" id="editModal{{ $assignment->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="background: var(--pr-surface); border: 1px solid var(--pr-border);">
                                <form action="{{ route('payroll.employee-salary.update', $assignment->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" style="color: var(--pr-text);">✏️ Edit Salary Assignment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Employee</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $user?->name }} ({{ $empId }})" readonly disabled />
                                            <input type="hidden" name="user_id" value="{{ $assignment->user_id }}" />
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Designation</label>
                                                <select name="designation_id" class="form-select form-select-sm">
                                                    @foreach($designations as $des)
                                                        <option value="{{ $des->id }}" {{ $assignment->designation_id == $des->id ? 'selected' : '' }}>
                                                            {{ $des->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Grade</label>
                                                <input type="text" name="grade" class="form-control form-control-sm" value="{{ $assignment->grade }}" />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Actual Basic Salary (₹) *</label>
                                            <input type="number" step="0.01" name="actual_basic_salary" class="form-control form-control-sm" value="{{ $assignment->actual_basic_salary }}" required />
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">HRA *</label>
                                                <div class="d-flex gap-2 mb-1">
                                                    <label class="small"><input type="radio" name="hra_type" value="percentage" {{ $assignment->hra_type === 'percentage' ? 'checked' : '' }}> % of Basic</label>
                                                    <label class="small"><input type="radio" name="hra_type" value="fixed" {{ $assignment->hra_type === 'fixed' ? 'checked' : '' }}> Fixed</label>
                                                </div>
                                                <input type="number" step="0.01" name="hra_value" class="form-control form-control-sm" value="{{ $assignment->hra_value }}" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Special Allowance *</label>
                                                <div class="d-flex gap-2 mb-1">
                                                    <label class="small"><input type="radio" name="special_allowance_type" value="percentage" {{ $assignment->special_allowance_type === 'percentage' ? 'checked' : '' }}> % of Basic</label>
                                                    <label class="small"><input type="radio" name="special_allowance_type" value="fixed" {{ $assignment->special_allowance_type === 'fixed' ? 'checked' : '' }}> Fixed</label>
                                                </div>
                                                <input type="number" step="0.01" name="special_allowance_value" class="form-control form-control-sm" value="{{ $assignment->special_allowance_value }}" required />
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Effective From *</label>
                                                <input type="date" name="effective_from" class="form-control form-control-sm" value="{{ $assignment->effective_from }}" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Effective To</label>
                                                <input type="date" name="effective_to" class="form-control form-control-sm" value="{{ $assignment->effective_to }}" />
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label small fw-bold">Status *</label>
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="active" {{ $assignment->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $assignment->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="pr-btn" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="pr-btn pr-btn-primary">💾 Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            No custom salary assignments recorded. Default designation salary structures apply. Click "+ Assign Salary" to override.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted">Showing {{ $assignments->firstItem() ?? 0 }}-{{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }}</span>
            {{ $assignments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Create Assignment Modal with Autofill -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--pr-surface); border: 1px solid var(--pr-border);">
            <form action="{{ route('payroll.employee-salary.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--pr-text);">➕ Assign Employee Salary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Employee *</label>
                        <select name="user_id" id="assign_user_id" class="form-select form-select-sm" required onchange="handleEmployeeSelect(this)">
                            <option value="">Select Employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" 
                                    data-designation-id="{{ $emp->employeeDetail?->designation_id }}"
                                    data-grade="{{ $emp->employeeDetail?->designation?->level ?: 'D1' }}">
                                    {{ $emp->name }} ({{ $emp->employeeDetail?->employee_id ?: 'EMP'.$emp->id }}) - {{ $emp->employeeDetail?->designation?->name ?: 'Staff' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Designation</label>
                            <select name="designation_id" id="assign_designation_id" class="form-select form-select-sm" onchange="fetchStructureDefaults()">
                                <option value="">Select Designation...</option>
                                @foreach($designations as $des)
                                    <option value="{{ $des->id }}">{{ $des->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Grade</label>
                            <input type="text" name="grade" id="assign_grade" class="form-control form-control-sm" placeholder="e.g. D1, S1" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Salary Structure Template (Optional)</label>
                        <select name="salary_structure_id" id="assign_structure_id" class="form-select form-select-sm" onchange="fetchStructureDefaults()">
                            <option value="">Custom Override (No template)</option>
                            @foreach($structures as $st)
                                <option value="{{ $st->id }}" 
                                    data-basic="{{ $st->basic_salary }}" 
                                    data-hra-type="{{ $st->hra_type }}" 
                                    data-hra-val="{{ $st->hra_value }}"
                                    data-spl-type="{{ $st->special_allowance_type }}" 
                                    data-spl-val="{{ $st->special_allowance_value }}">
                                    {{ $st->name }} (Basic: ₹{{ number_format($st->basic_salary) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Actual Basic Salary (₹) *</label>
                        <input type="number" step="0.01" name="actual_basic_salary" id="assign_basic" class="form-control form-control-sm" value="30000" required />
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">HRA *</label>
                            <div class="d-flex gap-2 mb-1">
                                <label class="small"><input type="radio" name="hra_type" id="hra_pct" value="percentage" checked> % of Basic</label>
                                <label class="small"><input type="radio" name="hra_type" id="hra_fix" value="fixed"> Fixed</label>
                            </div>
                            <input type="number" step="0.01" name="hra_value" id="assign_hra_val" class="form-control form-control-sm" value="50" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Special Allowance *</label>
                            <div class="d-flex gap-2 mb-1">
                                <label class="small"><input type="radio" name="special_allowance_type" id="spl_pct" value="percentage" checked> % of Basic</label>
                                <label class="small"><input type="radio" name="special_allowance_type" id="spl_fix" value="fixed"> Fixed</label>
                            </div>
                            <input type="number" step="0.01" name="special_allowance_value" id="assign_spl_val" class="form-control form-control-sm" value="50" required />
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Effective From *</label>
                            <input type="date" name="effective_from" class="form-control form-control-sm" value="{{ date('Y-01-01') }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Effective To</label>
                            <input type="date" name="effective_to" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Status *</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pr-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="pr-btn pr-btn-primary">💾 Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleEmployeeSelect(sel) {
    const opt = sel.options[sel.selectedIndex];
    const desId = opt.getAttribute('data-designation-id');
    const gr = opt.getAttribute('data-grade');
    if (desId) {
        document.getElementById('assign_designation_id').value = desId;
    }
    if (gr) {
        document.getElementById('assign_grade').value = gr;
    }
    fetchStructureDefaults();
}

function fetchStructureDefaults() {
    const structSel = document.getElementById('assign_structure_id');
    if (structSel.value) {
        const opt = structSel.options[structSel.selectedIndex];
        document.getElementById('assign_basic').value = opt.getAttribute('data-basic') || 30000;
        document.getElementById('assign_hra_val').value = opt.getAttribute('data-hra-val') || 50;
        document.getElementById('assign_spl_val').value = opt.getAttribute('data-spl-val') || 50;
        if (opt.getAttribute('data-hra-type') === 'fixed') {
            document.getElementById('hra_fix').checked = true;
        } else {
            document.getElementById('hra_pct').checked = true;
        }
        if (opt.getAttribute('data-spl-type') === 'fixed') {
            document.getElementById('spl_fix').checked = true;
        } else {
            document.getElementById('spl_pct').checked = true;
        }
        return;
    }

    const desId = document.getElementById('assign_designation_id').value;
    if (desId) {
        fetch(`{{ route('payroll.employee-salary.defaults') }}?designation_id=${desId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('assign_basic').value = data.basic_salary;
                    document.getElementById('assign_hra_val').value = data.hra_value;
                    document.getElementById('assign_spl_val').value = data.special_allowance_value;
                    if (data.grade && !document.getElementById('assign_grade').value) {
                        document.getElementById('assign_grade').value = data.grade;
                    }
                }
            })
            .catch(() => {});
    }
}
</script>
@endsection
