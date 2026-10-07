@extends('admin.layout.app')

@section('title', 'Salary Structures')

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
            <h2>🏗️ Salary Structures</h2>
            <div class="spacer"></div>
            <button class="pr-btn pr-btn-primary" data-bs-toggle="modal" data-bs-target="#createStructureModal">
                + Add Structure
            </button>
        </div>

        <!-- Filters Matching ui.html -->
        <form method="GET" action="{{ route('payroll.salary-structures.index') }}" class="pr-filters">
            <div class="field search-field">
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search structure or designation..." />
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
                <label>Grade</label>
                <select name="grade" onchange="this.form.submit()">
                    <option value="all">All Grades</option>
                    @foreach($grades as $gr)
                        <option value="{{ $gr }}" {{ request('grade') === $gr ? 'selected' : '' }}>{{ $gr }}</option>
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
                <a href="{{ route('payroll.salary-structures.index') }}" class="pr-btn text-muted">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Designation</th>
                        <th>Grade</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-center">HRA</th>
                        <th class="text-center">Special Allowance</th>
                        <th>Effective From</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($structures as $index => $structure)
                    <tr>
                        <td>{{ $structures->firstItem() + $index }}</td>
                        <td>
                            <b>{{ $structure->designation?->name ?: ($structure->name ?: 'Standard Role') }}</b>
                        </td>
                        <td>
                            <span class="pr-pill calculated">{{ $structure->grade ?: 'Standard' }}</span>
                        </td>
                        <td class="num">₹{{ number_format($structure->basic_salary, 2) }}</td>
                        <td class="text-center">
                            {{ $structure->hra_type === 'percentage' ? ($structure->hra_value . '%') : ('₹' . number_format($structure->hra_value, 2)) }}
                        </td>
                        <td class="text-center">
                            {{ $structure->special_allowance_type === 'percentage' ? ($structure->special_allowance_value . '%') : ('₹' . number_format($structure->special_allowance_value, 2)) }}
                        </td>
                        <td>
                            {{ $structure->effective_from ? Carbon\Carbon::parse($structure->effective_from)->format('d M Y') : '01 Jan 2025' }}
                        </td>
                        <td>
                            <span class="pr-pill {{ $structure->status === 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($structure->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="pr-btn pr-btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $structure->id }}">
                                ✏️ Edit
                            </button>
                            <form action="{{ route('payroll.salary-structures.destroy', $structure->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary structure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pr-btn pr-btn-sm text-danger">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $structure->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="background: var(--pr-surface); border: 1px solid var(--pr-border);">
                                <form action="{{ route('payroll.salary-structures.update', $structure->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" style="color: var(--pr-text);">✏️ Edit Salary Structure</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Designation *</label>
                                                <select name="designation_id" class="form-select form-select-sm" required>
                                                    @foreach($designations as $des)
                                                        <option value="{{ $des->id }}" {{ $structure->designation_id == $des->id ? 'selected' : '' }}>
                                                            {{ $des->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Grade / Level *</label>
                                                <select name="grade" class="form-select form-select-sm" required>
                                                    @foreach($grades as $gr)
                                                        <option value="{{ $gr }}" {{ $structure->grade === $gr ? 'selected' : '' }}>{{ $gr }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Basic Salary (₹) *</label>
                                            <input type="number" step="0.01" name="basic_salary" class="form-control form-control-sm" value="{{ $structure->basic_salary }}" required />
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">HRA *</label>
                                                <div class="d-flex gap-2 mb-1">
                                                    <label class="small"><input type="radio" name="hra_type" value="percentage" {{ $structure->hra_type === 'percentage' ? 'checked' : '' }}> % of Basic</label>
                                                    <label class="small"><input type="radio" name="hra_type" value="fixed" {{ $structure->hra_type === 'fixed' ? 'checked' : '' }}> Fixed</label>
                                                </div>
                                                <input type="number" step="0.01" name="hra_value" class="form-control form-control-sm" value="{{ $structure->hra_value }}" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Special Allowance *</label>
                                                <div class="d-flex gap-2 mb-1">
                                                    <label class="small"><input type="radio" name="special_allowance_type" value="percentage" {{ $structure->special_allowance_type === 'percentage' ? 'checked' : '' }}> % of Basic</label>
                                                    <label class="small"><input type="radio" name="special_allowance_type" value="fixed" {{ $structure->special_allowance_type === 'fixed' ? 'checked' : '' }}> Fixed</label>
                                                </div>
                                                <input type="number" step="0.01" name="special_allowance_value" class="form-control form-control-sm" value="{{ $structure->special_allowance_value }}" required />
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Effective From *</label>
                                                <input type="date" name="effective_from" class="form-control form-control-sm" value="{{ $structure->effective_from }}" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Effective To</label>
                                                <input type="date" name="effective_to" class="form-control form-control-sm" value="{{ $structure->effective_to }}" />
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label small fw-bold">Status *</label>
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="active" {{ $structure->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $structure->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                        <td colspan="9" class="text-center py-4 text-muted">
                            No salary structures created yet. Click "+ Add Structure" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted">Showing {{ $structures->firstItem() ?? 0 }}-{{ $structures->lastItem() ?? 0 }} of {{ $structures->total() }}</span>
            {{ $structures->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Create Structure Modal -->
<div class="modal fade" id="createStructureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--pr-surface); border: 1px solid var(--pr-border);">
            <form action="{{ route('payroll.salary-structures.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--pr-text);">➕ Create Salary Structure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Designation *</label>
                            <select name="designation_id" class="form-select form-select-sm" required>
                                <option value="">Select Designation</option>
                                @foreach($designations as $des)
                                    <option value="{{ $des->id }}">{{ $des->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Grade / Level *</label>
                            <select name="grade" class="form-select form-select-sm" required>
                                @foreach($grades as $gr)
                                    <option value="{{ $gr }}">{{ $gr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Basic Salary (₹) *</label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control form-control-sm" placeholder="0.00" value="{{ old('basic_salary') }}" required />
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">HRA *</label>
                            <div class="d-flex gap-2 mb-1">
                                <label class="small"><input type="radio" name="hra_type" value="percentage" checked> % of Basic</label>
                                <label class="small"><input type="radio" name="hra_type" value="fixed"> Fixed</label>
                            </div>
                            <input type="number" step="0.01" name="hra_value" class="form-control form-control-sm" value="50" placeholder="50%" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Special Allowance *</label>
                            <div class="d-flex gap-2 mb-1">
                                <label class="small"><input type="radio" name="special_allowance_type" value="percentage" checked> % of Basic</label>
                                <label class="small"><input type="radio" name="special_allowance_type" value="fixed"> Fixed</label>
                            </div>
                            <input type="number" step="0.01" name="special_allowance_value" class="form-control form-control-sm" value="50" placeholder="50%" required />
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
                    <button type="submit" class="pr-btn pr-btn-primary">💾 Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
