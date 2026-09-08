@extends('admin.layout.app')

@section('content')

<style>
.pipeline-progress {
    display: flex;
    border-radius: 10px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
}
.pipeline-step {
    flex: 1;
    padding: 12px 10px;
    text-align: center;
    font-size: 12.5px;
    font-weight: 600;
    color: #64748b;
    position: relative;
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    transition: all 0.2s;
}
.pipeline-step:last-child {
    border-right: none;
}
.pipeline-step.active {
    background: #3b82f6;
    color: #ffffff;
}
.pipeline-step.completed {
    background: #10b981;
    color: #ffffff;
}
.pipeline-step.lost {
    background: #ef4444;
    color: #ffffff;
}
</style>

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.deals.index') }}" class="text-muted text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Deals</a>
                <span class="text-muted">/</span>
                <span class="fw-bold text-dark">{{ $deal->deal_name }}</span>
            </div>
            <h4 class="fw-bold mb-0 text-dark">
                {{ $deal->deal_name }}
                @if($deal->company_name)
                    <span class="text-muted fs-6 fw-normal">for {{ $deal->company_name }}</span>
                @endif
            </h4>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addDealActivityModal">
                <i class="fas fa-plus me-1"></i> Add Activity
            </button>
            <button class="btn btn-warning btn-sm text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#scheduleDealFollowUpModal">
                <i class="fas fa-calendar-plus me-1"></i> Schedule Follow-up
            </button>
            @if(str_contains(strtolower($deal->stage->name ?? ''), 'won') || str_contains(strtolower($deal->stage->name ?? ''), 'concreted'))
                <button class="btn btn-success btn-sm fw-semibold" id="btnConvertDealClient">
                    <i class="fas fa-user-check me-1"></i> Convert to Client
                </button>
            @endif
            <a href="{{ route('admin.deals.edit', $deal->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-edit me-1"></i> Edit Deal
            </a>
        </div>
    </div>

    {{-- VISUAL PIPELINE STEPPER --}}
    <div class="pipeline-progress shadow-sm">
        @php
            $currentStageOrder = $deal->stage->order ?? 1;
            $isLost = str_contains(strtolower($deal->stage->name ?? ''), 'lost');
        @endphp
        @foreach($stages as $stg)
            @php
                $stepClass = '';
                if ($isLost && $stg->id === $deal->deal_stage_id) {
                    $stepClass = 'lost';
                } elseif ($stg->id === $deal->deal_stage_id) {
                    $stepClass = 'active';
                } elseif ($stg->order < $currentStageOrder && !$isLost) {
                    $stepClass = 'completed';
                }
            @endphp
            <div class="pipeline-step {{ $stepClass }}">
                <span>{{ $stg->name }}</span>
                <small class="d-block opacity-75" style="font-size: 10px;">{{ $stg->default_probability }}%</small>
            </div>
        @endforeach
    </div>

    {{-- TOP SUMMARY CARD --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <span class="text-muted small d-block">Deal Opportunity Value</span>
                    <h3 class="fw-bold text-success mb-1">{{ $deal->currency }} {{ number_format($deal->value, 2) }}</h3>
                    <span class="small text-muted">Weighted Value: <strong>{{ $deal->currency }} {{ number_format($deal->weighted_value ?? $deal->calculateWeightedValue(), 2) }}</strong></span>
                </div>

                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-3 justify-content-md-end align-items-center">
                        <div class="text-center px-3 border-end">
                            <span class="text-muted small d-block">Stage</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1 mt-1 fs-6">
                                {{ $deal->stage->name ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="text-center px-3 border-end">
                            <span class="text-muted small d-block">Win Probability</span>
                            <span class="fw-bold text-dark fs-5 mt-1 d-block">{{ $deal->probability }}%</span>
                        </div>

                        <div class="text-center px-3 border-end">
                            <span class="text-muted small d-block">Priority</span>
                            @php
                                $prio = strtolower($deal->priority ?? 'medium');
                                $prioBadge = match($prio) {
                                    'urgent' => 'bg-danger text-white',
                                    'high' => 'bg-warning text-dark',
                                    'low' => 'bg-secondary text-white',
                                    default => 'bg-info text-white',
                                };
                            @endphp
                            <span class="badge {{ $prioBadge }} px-3 py-1 text-capitalize mt-1">{{ $prio }}</span>
                        </div>

                        <div class="text-center px-3">
                            <span class="text-muted small d-block">Deal Agent</span>
                            <span class="fw-bold small text-dark d-block mt-1">
                                <i class="fas fa-user-circle me-1 text-secondary"></i>{{ $deal->agent->name ?? 'Unassigned' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs border-bottom-0 mb-3" id="dealTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold text-dark" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane"><i class="fas fa-info-circle me-2 text-primary"></i>Overview</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-dark" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane"><i class="fas fa-stream me-2 text-warning"></i>Activities ({{ $deal->activities->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-dark" id="followups-tab" data-bs-toggle="tab" data-bs-target="#followups-pane"><i class="fas fa-calendar-check me-2 text-info"></i>Follow-ups ({{ $deal->followUps->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-dark" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-pane"><i class="fas fa-sticky-note me-2 text-secondary"></i>Notes</button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- OVERVIEW --}}
        <div class="tab-pane fade show active" id="overview-pane">
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark">Opportunity Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Lead Contact</span>
                                    @if($deal->lead)
                                        <a href="{{ route('leads.contacts.show', $deal->lead->id) }}" class="fw-bold text-primary">{{ $deal->lead_name }}</a>
                                    @else
                                        <span class="fw-semibold text-dark">{{ $deal->lead_name }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Company Name</span>
                                    <span class="fw-semibold text-dark">{{ $deal->company_name ?: 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Contact Details</span>
                                    <span class="fw-semibold text-dark">{{ $deal->contact_details }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Pipeline</span>
                                    <span class="fw-semibold text-dark">{{ $deal->pipeline }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Category</span>
                                    <span class="fw-semibold text-dark">{{ $deal->category->name ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Product / Service</span>
                                    <span class="fw-semibold text-dark">{{ $deal->product ?: 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($deal->lost_reason)
                        <div class="card border-danger shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-danger text-white py-2">
                                <h6 class="fw-bold mb-0"><i class="fas fa-exclamation-circle me-1"></i> Lost Deal Reason</h6>
                            </div>
                            <div class="card-body">
                                <p class="fw-bold text-danger mb-1">{{ $deal->lost_reason }}</p>
                                <p class="text-muted small mb-0">{{ $deal->lost_notes ?: 'No extra notes provided.' }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark">Schedule & Dates</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="text-muted small d-block">Expected Close Date</span>
                                <span class="fw-bold text-dark">{{ $deal->close_date ? $deal->close_date->format('M d, Y') : 'Not Set' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Next Follow-up</span>
                                <span class="fw-bold text-primary">{{ $deal->next_follow_up ? $deal->next_follow_up->format('M d, Y') : 'None Scheduled' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Created On</span>
                                <span class="text-secondary small">{{ $deal->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIVITIES TIMELINE --}}
        <div class="tab-pane fade" id="activities-pane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Deal Activity Log</h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDealActivityModal"><i class="fas fa-plus me-1"></i> Log Activity</button>
                </div>
                <div class="card-body">
                    <div class="timeline ps-3">
                        @forelse($deal->activities as $act)
                            <div class="d-flex gap-3 mb-4">
                                <div class="badge rounded-circle p-3 d-flex align-items-center justify-content-center {{ $act->type_badge_class }}" style="width: 40px; height: 40px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="flex-grow-1 bg-light p-3 rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 text-dark">{{ $act->title }}</h6>
                                        <small class="text-muted">{{ $act->activity_date ? $act->activity_date->diffForHumans() : $act->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 text-secondary small">{{ $act->description }}</p>
                                    <small class="text-muted">By {{ $act->creator->name ?? 'System' }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">No activity records logged for this deal yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- FOLLOW-UPS --}}
        <div class="tab-pane fade" id="followups-pane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">Deal Follow-ups</h6>
                    <button class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#scheduleDealFollowUpModal"><i class="fas fa-calendar-plus me-1"></i> Schedule</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                    <th>Assigned Agent</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deal->followUps as $fl)
                                    <tr>
                                        <td class="fw-bold text-capitalize"><i class="fas fa-bell text-warning me-1"></i>{{ $fl->follow_up_type }}</td>
                                        <td>{{ $fl->date ? $fl->date->format('M d, Y') : '' }} {{ $fl->time }}</td>
                                        <td>{{ $fl->assignee->name ?? 'Unassigned' }}</td>
                                        <td><span class="badge bg-secondary text-capitalize">{{ $fl->status }}</span></td>
                                        <td class="small">{{ $fl->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No follow-ups scheduled for this deal yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- NOTES --}}
        <div class="tab-pane fade" id="notes-pane">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">Notes & Description</h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary" style="white-space: pre-wrap;">{{ $deal->notes ?: 'No description or notes added.' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: LOG ACTIVITY --}}
<div class="modal fade" id="addDealActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Log Deal Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.deals.activities.store', $deal->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Activity Type</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="call">Call Completed</option>
                            <option value="email">Email Sent</option>
                            <option value="meeting">Meeting Held</option>
                            <option value="note">Note Added</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Activity Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Contract Review Meeting">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Outcome / Notes</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: SCHEDULE FOLLOW-UP --}}
<div class="modal fade" id="scheduleDealFollowUpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Schedule Deal Follow-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.deals.follow-ups.store', $deal->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Follow-up Type</label>
                        <select name="follow_up_type" class="form-select form-select-sm" required>
                            <option value="call">Phone Call</option>
                            <option value="email">Send Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="task">Task / Action</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Date</label>
                            <input type="date" name="date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Time</label>
                            <input type="time" name="time" class="form-control form-control-sm" value="10:00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign Agent</label>
                        <select name="assigned_to" class="form-select form-select-sm">
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}" {{ $deal->deal_agent_id == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Instructions / Notes</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-dark px-4 fw-semibold">Schedule Follow-up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnConvert = document.getElementById('btnConvertDealClient');
    if (btnConvert) {
        btnConvert.addEventListener('click', function() {
            if (confirm('Convert Won Deal "{{ $deal->deal_name }}" to a Client?')) {
                fetch('{{ route("admin.deals.convert-to-client", $deal->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(async r => {
                    const contentType = r.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        return r.json();
                    }
                    const text = await r.text();
                    throw new Error('Server returned unexpected response (Status ' + r.status + ')');
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Failed to convert: ' + err));
            }
        });
    }
});
</script>

@endsection
