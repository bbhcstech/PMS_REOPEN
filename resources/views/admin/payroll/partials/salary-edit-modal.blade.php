{{-- Edit salary assignment modal. Needs $assignment, $user, $empId and $designations. --}}
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
                            <input type="date" name="effective_from" class="form-control form-control-sm" value="{{ optional($assignment->effective_from)->format('Y-m-d') }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Effective To</label>
                            <input type="date" name="effective_to" class="form-control form-control-sm" value="{{ optional($assignment->effective_to)->format('Y-m-d') }}" />
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
