<form method="POST" action="{{ route('admin.settings.performance.update') }}">
    @csrf
    <div class="alert alert-info">
        Admin and HR review employees and managers. HR's own appraisal is reserved for the company administrator. Self-rating and manager editing are disabled.
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <label for="appraisalCycle" class="form-label fw-semibold">Review cycle</label>
            <select id="appraisalCycle" name="appraisal_cycle" class="form-select" @disabled($isSettingsReadOnly)>
                @foreach(['Monthly', 'Quarterly', 'Bi-Annually', 'Annually'] as $cycle)
                    <option value="{{ $cycle }}" @selected(old('appraisal_cycle', $settings['appraisal_cycle']) === $cycle)>{{ $cycle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Cultural behaviour rating</label>
            <div class="form-control bg-light">1–10: teamwork, communication and professional conduct</div>
            <small class="text-muted">These three ratings are averaged and converted to a score out of 100.</small>
        </div>
        <div class="col-12"><h5 class="mb-1">Three appraisal criteria</h5><p class="text-muted small mb-0">Weights must total 100%. Attendance and work drafts use recorded data. Reviewers must supply evidence when adjusting these scores.</p></div>
        @foreach(['attendance_weight' => 'Attendance', 'work_weight' => 'Work delivery', 'behaviour_weight' => 'Cultural behaviour'] as $key => $label)
            <div class="col-md-4">
                <label for="{{ $key }}" class="form-label fw-semibold">{{ $label }} weight (%)</label>
                <input id="{{ $key }}" type="number" name="{{ $key }}" class="form-control" min="1" max="98" step="1" value="{{ old($key, $settings[$key]) }}" required @disabled($isSettingsReadOnly)>
            </div>
        @endforeach
        <div class="col-12">
            <div class="form-check form-switch">
                <input id="adminApproval" type="checkbox" name="admin_approval_required" value="1" class="form-check-input" @checked(old('admin_approval_required', $settings['admin_approval_required']) === '1') @disabled($isSettingsReadOnly)>
                <label for="adminApproval" class="form-check-label fw-semibold">Require administrator approval of HR evaluations</label>
            </div>
            <small class="text-muted">When enabled, HR submits evaluations and the administrator reviews and approves them. When disabled, HR needs the Approve role permission to finalize another person's appraisal.</small>
        </div>
        <div class="col-12 border-top pt-4">
            <h5>Salary increase recommendations</h5>
            <div class="form-check form-switch mb-2">
                <input id="incrementEnabled" type="checkbox" name="increment_enabled" value="1" class="form-check-input" @checked(old('increment_enabled', $settings['increment_enabled']) === '1') @disabled($isSettingsReadOnly)>
                <label for="incrementEnabled" class="form-check-label">Calculate a recommended increase from the appraisal grade</label>
            </div>
            <p class="text-muted small">These percentages are recorded on the evaluation. Payroll salary changes remain a separate administrator action in Employee Salary. Existing approved evaluations retain their saved policy.</p>
            @if(\Illuminate\Support\Facades\Route::has('payroll.employee-salary.index'))<a href="{{ route('payroll.employee-salary.index') }}" class="btn btn-sm btn-outline-primary mb-3">Manage employee salaries</a>@endif
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Grade</th><th>Overall score</th><th>Recommended increase (%)</th></tr></thead>
                    <tbody>
                        @foreach(['a_plus' => ['A+', '90–100'], 'a' => ['A', '80–89.99'], 'b' => ['B', '70–79.99'], 'c' => ['C', '60–69.99'], 'd' => ['D', 'Below 60']] as $key => [$grade, $range])
                            <tr><td class="fw-semibold">{{ $grade }}</td><td>{{ $range }}</td><td><input type="number" name="increment_{{ $key }}" class="form-control" min="0" max="100" step="0.01" value="{{ old('increment_'.$key, $settings['increment_'.$key]) }}" aria-label="Recommended increase for Grade {{ $grade }}" required @disabled($isSettingsReadOnly)></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-3">
        <a href="{{ route('appraisal.index') }}" class="btn btn-outline-primary">Open appraisals</a>
        @unless($isSettingsReadOnly)<button type="submit" class="btn-save-address">Save appraisal policy</button>@endunless
    </div>
</form>
