@php
    $behaviourEmployees = $employees->merge($appraisals->pluck('employee')->filter())->unique('id');
    $isBehaviourAdmin = $canManage;
@endphp
<p class="small text-muted px-3 pt-3 mb-2">Enter observed ratings from 1 to 10 with remarks. An average of {{ $settings['behaviour_full_marks_at'] }}/10 earns 100 marks; lower averages earn proportional marks. Saving ratings leaves the appraisal awaiting final review.</p>
<div class="table-responsive" data-behaviour-section data-behaviour-open="{{ session('appraisal_tab') === 'behaviour' || old('behaviour_rating_form') ? '1' : '0' }}" data-behaviour-employee="{{ old('behaviour_rating_form') ? (int) old('employee_id') : '' }}">
    <table class="table app-table align-middle mb-0">
        <thead><tr>
            <th style="min-width:200px">Employee</th><th>Teamwork (1–10)</th><th>Communication (1–10)</th>
            <th>Professional conduct (1–10)</th><th>Behaviour Score (0–100)</th><th style="min-width:250px">Conduct Remarks</th>
            @if($isBehaviourAdmin)<th data-pms-export-ignore>Ratings</th>@endif
        </tr></thead>
        <tbody>
        @forelse($behaviourEmployees as $employee)
            @php
                $record = $appraisals->firstWhere('employee_id', $employee->id);
                $hasRatings = $record && $record->teamwork_score >= 1 && $record->communication_score >= 1 && $record->punctuality_score >= 1;
                $canRate = $isBehaviourAdmin && ($record ? $canEdit : $canCreate) && in_array($employee->id, $editableEmployeeIds, true);
            @endphp
            <tr>
                <td><span class="fw-bold text-dark">{{ $employee->name }}</span></td>
                <td>{{ $hasRatings ? number_format($record->teamwork_score, 1) : 'Not rated' }}</td>
                <td>{{ $hasRatings ? number_format($record->communication_score, 1) : 'Not rated' }}</td>
                <td>{{ $hasRatings ? number_format($record->punctuality_score, 1) : 'Not rated' }}</td>
                <td><span class="fw-bold text-warning">{{ $hasRatings ? number_format($record->behaviour_score, 1).' / 100' : 'Awaiting ratings' }}</span></td>
                <td><small class="text-muted">{{ $hasRatings ? $record->behaviour_remarks : 'Reviewer feedback required.' }}</small></td>
                @if($isBehaviourAdmin)<td>
                    @if($canRate)
                    <button type="button" class="btn btn-sm btn-purple-primary" data-bs-toggle="modal" data-bs-target="#behaviourRating-{{ $employee->id }}">{{ $hasRatings ? 'Edit ratings' : 'Add ratings' }}</button>
                    @else<span class="text-muted small">Read only</span>@endif
                </td>@endif
            </tr>
        @empty
            <tr><td colspan="{{ $isBehaviourAdmin ? 7 : 6 }}" class="text-center py-4">No employees found for this period.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@push('appraisal-behaviour-modals')
@if($isBehaviourAdmin)
    @foreach($behaviourEmployees as $employee)
        @php($record = $appraisals->firstWhere('employee_id', $employee->id))
        @if(($record ? $canEdit : $canCreate) && in_array($employee->id, $editableEmployeeIds, true))
        <div class="modal fade" id="behaviourRating-{{ $employee->id }}" tabindex="-1" aria-labelledby="behaviourTitle-{{ $employee->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
                <form method="POST" action="{{ route('appraisal.behaviour.save') }}" data-behaviour-rating-form data-behaviour-target="{{ $settings['behaviour_full_marks_at'] }}">
                    @csrf
                    <input type="hidden" name="behaviour_rating_form" value="1">
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="appraisal_period" value="{{ $selectedPeriod }}">
                    <div class="modal-header"><h5 class="modal-title" id="behaviourTitle-{{ $employee->id }}">Behaviour ratings: {{ $employee->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <p class="text-muted">{{ $selectedPeriod }} · Rate observed behaviour. These ratings are entered manually.</p>
                        @if($record && in_array($record->status, ['submitted', 'approved'], true))<div class="alert alert-warning">Changing ratings returns this appraisal to draft. Its grade and salary recommendation require a new review and approval.</div>@endif
                        <div class="row g-3">
                        @foreach(['teamwork_score' => 'Teamwork', 'communication_score' => 'Communication', 'punctuality_score' => 'Professional conduct'] as $field => $label)
                            <div class="col-md-4"><label class="form-label" for="rating-{{ $employee->id }}-{{ $field }}">{{ $label }} (1–10)</label>
                                <input type="number" class="form-control" id="rating-{{ $employee->id }}-{{ $field }}" name="{{ $field }}" value="{{ (int) old('employee_id') === $employee->id ? old($field) : ($record?->$field ?: '') }}" min="1" max="10" step="0.1" required data-behaviour-rating>
                            </div>
                        @endforeach
                            <div class="col-12"><label class="form-label" for="rating-evidence-{{ $employee->id }}">Feedback and examples</label>
                                <textarea class="form-control" id="rating-evidence-{{ $employee->id }}" name="behaviour_remarks" maxlength="5000" rows="3" required>{{ (int) old('employee_id') === $employee->id ? old('behaviour_remarks') : ($record && $record->behaviour_score > 0 ? $record->behaviour_remarks : '') }}</textarea>
                            </div>
                            <div class="col-12"><strong>Behaviour score:</strong> <output data-behaviour-preview aria-live="polite">Enter all three ratings</output></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-purple-primary">Save ratings</button></div>
                </form>
            </div></div>
        </div>
        @endif
    @endforeach
@endif
@endpush
@once
@push('scripts')
<script src="{{ asset('admin/assets/js/pms-appraisal-ratings.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-appraisal-ratings.js')) ?: 1 }}" defer></script>
@endpush
@endonce
