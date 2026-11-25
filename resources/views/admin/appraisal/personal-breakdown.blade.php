@php($savedPolicy = $record->policy_snapshot ?: ['work_weight' => 40, 'attendance_weight' => 30, 'behaviour_weight' => 30])
<section class="app-table-card p-4 mb-4" aria-label="Your appraisal breakdown">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div><h5 class="mb-1">Your appraisal breakdown</h5><span class="text-muted">{{ $record->appraisal_period }} · {{ ucfirst($record->status ?? 'approved') }}</span></div>
        <strong>{{ $record->overall_grade === 'Pending' ? 'Awaiting final review' : number_format($record->overall_score, 2).' / 100' }}</strong>
    </div>
    <div class="row g-3">
        @foreach(['work' => ['Work delivery', 'project_score', 'project_remarks'], 'attendance' => ['Attendance', 'attendance_score', 'attendance_remarks'], 'behaviour' => ['Cultural behaviour', 'behaviour_score', 'behaviour_remarks']] as $criterion => [$label, $scoreField, $remarksField])
            <div class="col-md-4"><div class="border rounded p-3 h-100">
                <h6>{{ $label }}</h6>
                <div class="fs-4 fw-bold">{{ number_format($record->$scoreField, 2) }} / 100</div>
                <p class="small text-muted mb-1">Full marks at {{ $savedPolicy[$criterion.'_full_marks_at'] ?? ($criterion === 'behaviour' ? 10 : 100) }}{{ $criterion === 'behaviour' ? '/10 average rating' : '%' }}</p>
                <p class="small text-muted mb-2">{{ $savedPolicy[$criterion.'_weight'] }}% weight · {{ number_format($record->$scoreField * $savedPolicy[$criterion.'_weight'] / 100, 2) }} points toward your total</p>
                <p class="small mb-0">{{ $record->$remarksField ?: 'Reviewer remarks pending.' }}</p>
            </div></div>
        @endforeach
    </div>
    <p class="small text-muted mt-3 mb-0">Your saved appraisal uses the policy in effect when it was calculated. Draft and submitted marks are provisional until approved.</p>
</section>
