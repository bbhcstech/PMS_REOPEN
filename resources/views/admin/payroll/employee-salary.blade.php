@extends('admin.layout.app')

@section('title', 'Employee Salary Assignments')

@section('content')
@include('admin.payroll.partials.styles')
<style>
    #employeeSalaryTable [data-pms-selection-column] { width:48px; min-width:48px; padding:12px!important; text-align:center; vertical-align:middle; }
    #employeeSalaryTable [data-pms-selection-column] input { position:static!important; display:inline-block; width:18px; height:18px; margin:0!important; cursor:pointer; accent-color:var(--pr-primary); }
    #employeeSalaryTable [data-pms-selection-column] input:disabled { cursor:not-allowed; opacity:.4; }
    #employeeSalaryTable [data-pms-selection-column] input:focus-visible { outline:2px solid var(--pr-primary); outline-offset:3px; }
</style>

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
                <label>Salary</label>
                <select name="salary" onchange="this.form.submit()">
                    <option value="all">All Employees</option>
                    <option value="assigned" {{ request('salary') === 'assigned' ? 'selected' : '' }}>Salary Assigned</option>
                    <option value="unassigned" {{ request('salary') === 'unassigned' ? 'selected' : '' }}>Not Assigned Yet</option>
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

        {{-- Every employee from the Employee section, with their details and salary status --}}
        <div class="pr-roster-head d-flex flex-wrap align-items-center gap-2 px-3 pt-3">
            <h3 class="m-0 me-auto" style="font-size:15px;font-weight:800;color:var(--pr-text);">👥 Employees &amp; Salary Status</h3>
            <span class="pr-pill calculated">{{ $rosterSummary['total'] }} staff</span>
            <span class="pr-pill active">{{ $rosterSummary['assigned'] }} salary assigned</span>
            <span class="pr-pill inactive">{{ $rosterSummary['unassigned'] }} not assigned</span>
        </div>
        <div class="table-wrap">
            <table class="pr-table" id="employeeRosterTable">
                <thead>
                    <tr>
                        <th>Emp ID</th>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Employment</th>
                        <th>Joined</th>
                        <th>Branch</th>
                        <th class="text-end">Monthly Gross</th>
                        <th>Salary</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roster as $member)
                    @php
                        $detail = $member->employeeDetail;
                        $current = $currentAssignments->get($member->id);
                        $memberEmpId = $detail?->employee_id ?: ('EMP' . str_pad($member->id, 3, '0', STR_PAD_LEFT));
                        $isLocked = $lockedUserId && (int) $member->id === (int) $lockedUserId;
                    @endphp
                    <tr>
                        <td class="text-nowrap"><b>{{ $memberEmpId }}</b></td>
                        <td>
                            <div class="cell-employee">
                                <div class="avatar-badge">{{ strtoupper(substr($member->name ?? 'U', 0, 2)) }}</div>
                                <div>
                                    <div class="name">{{ $member->name }}</div>
                                    <div class="meta">{{ $member->email }}@if($detail?->mobile) · {{ $detail->mobile }}@endif</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $member->companyStaffRole?->name ?? (strtolower((string) $member->role) === 'hr' ? 'HR' : ucfirst((string) $member->role)) }}</td>
                        <td>{{ $detail?->department?->dpt_name ?? ($detail?->department?->name ?? '—') }}</td>
                        <td>
                            {{ $detail?->designation?->name ?? '—' }}
                            @if($detail?->designation?->level !== null)<span class="badge bg-label-secondary small">L{{ $detail->designation->level }}</span>@endif
                        </td>
                        <td>{{ $detail?->employment_type ? ucwords(str_replace('_', ' ', $detail->employment_type)) : '—' }}</td>
                        <td>{{ $detail?->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d M Y') : '—' }}</td>
                        <td>{{ $detail?->business_address ?: '—' }}</td>
                        <td class="num">{{ $current ? '₹' . number_format($current->computed_gross, 2) : '—' }}</td>
                        <td>
                            @if($current)
                                <span class="pr-pill active">Assigned</span>
                                <div class="small text-muted">Basic ₹{{ number_format($current->actual_basic_salary, 2) }} · from {{ \Carbon\Carbon::parse($current->effective_from)->format('d M Y') }}</div>
                            @else
                                <span class="pr-pill inactive">Not assigned</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isLocked)
                                <span class="small text-muted" title="Your own salary is set by the company Admin">🔒 Set by Admin</span>
                            @elseif($current)
                                <button type="button" class="pr-btn pr-btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $current->id }}">✏️ Edit</button>
                            @else
                                <button type="button" class="pr-btn pr-btn-sm pr-btn-primary" onclick="openAssignFor({{ $member->id }})">+ Assign</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">No employees match these filters. Add employees in the HR › Employee section.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h3 class="px-3 pt-4 m-0" style="font-size:15px;font-weight:800;color:var(--pr-text);">🧾 Salary Assignment Records</h3>
        <div class="table-wrap">
            <table class="pr-table" id="employeeSalaryTable">
                <thead>
                    <tr>
                        <th data-pms-selection-column><input type="checkbox" class="form-check-input" aria-label="Select all salary assignments" @disabled($assignments->isEmpty())></th>
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
                        <td data-pms-selection-column><input type="checkbox" class="form-check-input" aria-label="Select salary assignment for {{ $user?->name ?? 'employee' }}" value="{{ $assignment->id }}"></td>
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
                            @if($lockedUserId && (int) $assignment->user_id === (int) $lockedUserId)
                                <span class="small text-muted" title="Your own salary is set by the company Admin">🔒 Set by Admin</span>
                            @else
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
                            @endif
                        </td>
                    </tr>

                    <!-- Edit Assignment Modal -->
                    @if(! ($lockedUserId && (int) $assignment->user_id === (int) $lockedUserId))
                    @include('admin.payroll.partials.salary-edit-modal')
                    @endif
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
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

@php($pageAssignmentIds = $assignments->pluck('id')->all())
@foreach($currentAssignments as $current)
    @continue(in_array($current->id, $pageAssignmentIds) || ($lockedUserId && (int) $current->user_id === (int) $lockedUserId))
    @php($assignment = $current)
    @php($user = $roster->firstWhere('id', $current->user_id) ?? $employees->firstWhere('id', $current->user_id))
    @php($empId = $user?->employeeDetail?->employee_id ?: ('EMP' . str_pad($user?->id ?? 0, 3, '0', STR_PAD_LEFT)))
    @include('admin.payroll.partials.salary-edit-modal')
@endforeach

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
                        <input type="number" step="0.01" name="actual_basic_salary" id="assign_basic" class="form-control form-control-sm" placeholder="0.00" value="{{ old('actual_basic_salary') }}" required />
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">HRA *</label>
                            <div class="d-flex gap-2 mb-1">
                                <label class="small"><input type="radio" name="hra_type" id="hra_pct" value="percentage" checked> % of Basic</label>
                                <label class="small"><input type="radio" name="hra_type" id="hra_fix" value="fixed"> Fixed</label>
                            </div>
                            <input type="number" step="0.01" name="hra_value" id="assign_hra_val" class="form-control form-control-sm" placeholder="0.00" value="{{ old('hra_value') }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Special Allowance *</label>
                            <div class="d-flex gap-2 mb-1">
                                <label class="small"><input type="radio" name="special_allowance_type" id="spl_pct" value="percentage" checked> % of Basic</label>
                                <label class="small"><input type="radio" name="special_allowance_type" id="spl_fix" value="fixed"> Fixed</label>
                            </div>
                            <input type="number" step="0.01" name="special_allowance_value" id="assign_spl_val" class="form-control form-control-sm" placeholder="0.00" value="{{ old('special_allowance_value') }}" required />
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
function openAssignFor(userId) {
    const sel = document.getElementById('assign_user_id');
    sel.value = String(userId);
    handleEmployeeSelect(sel);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('createAssignmentModal')).show();
}

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
        document.getElementById('assign_basic').value = opt.getAttribute('data-basic') || '';
        document.getElementById('assign_hra_val').value = opt.getAttribute('data-hra-val') || '';
        document.getElementById('assign_spl_val').value = opt.getAttribute('data-spl-val') || '';
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
