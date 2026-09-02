@extends('admin.layout.app')

@section('content')
<div class="container-fluid px-4 py-4">
    @if($ticket->project)
        {{-- Standardized Project Header & 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $ticket->project,
            'activeTab' => 'tickets'
        ])
    @endif

    <!-- Top Header & Breadcrumbs -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold mb-0 text-dark">Ticket #{{ $ticket->id }}: {{ Str::limit($ticket->subject, 50) }}</h3>
                @if(strtolower((string)$ticket->status) == 'open')
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">Open</span>
                @elseif(strtolower((string)$ticket->status) == 'reopened')
                    <span class="badge bg-danger px-3 py-2 fs-6 animate-pulse"><i class="bx bx-redo me-1"></i> REOPENED</span>
                @elseif(strtolower((string)$ticket->status) == 'resolved')
                    <span class="badge bg-success px-3 py-2 fs-6">Resolved</span>
                @elseif(strtolower((string)$ticket->status) == 'closed')
                    <span class="badge bg-secondary px-3 py-2 fs-6">Closed</span>
                @else
                    <span class="badge bg-info px-3 py-2 fs-6">{{ ucfirst($ticket->status) }}</span>
                @endif

                @php
                    $priorityColors = ['low' => 'bg-secondary', 'medium' => 'bg-info', 'high' => 'bg-warning text-dark', 'critical' => 'bg-danger'];
                @endphp
                <span class="badge {{ $priorityColors[strtolower((string)$ticket->priority)] ?? 'bg-primary' }} px-3 py-2 fs-6">
                    <i class="bx bx-flag me-1"></i> {{ ucfirst($ticket->priority) }} Priority
                </span>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}" class="text-decoration-none">Tickets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ticket #{{ $ticket->id }}</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="bx bx-edit me-1"></i> Edit Ticket
            </a>

            <!-- Log Time Modal Trigger -->
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#logTimeModal">
                <i class="bx bx-time me-1"></i> Log Time (Timesheet)
            </button>

            @if(in_array(strtolower((string)auth()->user()->role), ['admin', 'hr', 'manager'], true))
                <!-- Reopen Ticket Modal Trigger -->
                <button type="button" class="btn btn-outline-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#reopenTicketModal">
                    <i class="bx bx-redo me-1"></i> Reopen Ticket
                </button>
            @endif

            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Main Panel -->
        <div class="col-lg-8">
            <!-- Ticket Detail Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-file-find text-primary me-2"></i> Issue & Problem Details</h5>
                    <small class="text-muted">Raised {{ $ticket->created_at->format('d M Y, h:i A') }} ({{ $ticket->created_at->diffForHumans() }})</small>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold text-dark mb-2">{{ $ticket->subject }}</h4>
                    
                    <!-- Affected Module & Target Deadline bar -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4 p-3 bg-light rounded-3 border">
                        <div>
                            <span class="text-muted small d-block">Affected Project Part / Module:</span>
                            <span class="fw-bold text-dark"><i class="bx bx-layer text-primary me-1"></i> {{ $ticket->affected_module ?? 'General Application Feature' }}</span>
                        </div>
                        <div class="vr d-none d-sm-block"></div>
                        <div>
                            <span class="text-muted small d-block">Target Resolution Deadline:</span>
                            @if($ticket->deadline)
                                @php
                                    $isOverdue = \Carbon\Carbon::parse($ticket->deadline)->isPast() && !in_array(strtolower((string)$ticket->status), ['resolved', 'closed']);
                                @endphp
                                <span class="fw-bold {{ $isOverdue ? 'text-danger' : 'text-dark' }}">
                                    <i class="bx bx-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($ticket->deadline)->format('d M Y') }}
                                    @if($isOverdue)
                                        <span class="badge bg-danger text-white ms-1">OVERDUE</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted italic">No Deadline Specified</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-3 mb-4 border text-secondary" style="white-space: pre-line; line-height: 1.6;">
                        {{ $ticket->description }}
                    </div>

                    @if($ticket->attachment)
                        <div class="p-3 bg-white border rounded-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 bg-primary-subtle text-primary rounded-3">
                                    <i class="bx bx-file-blank fs-2"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ basename($ticket->attachment) }}</div>
                                    <small class="text-muted">Error Log / Bug Sheet Attachment for Developer</small>
                                </div>
                            </div>
                            <a href="{{ asset($ticket->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bx bx-download me-1"></i> Download File
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Time Logged / Timesheet Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-time-five text-info me-2"></i> Timesheet & Logged Hours</h5>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#logTimeModal">
                        + Log Hours
                    </button>
                </div>
                <div class="card-body p-4">
                    @forelse($timeLogs as $log)
                        <div class="d-flex align-items-center justify-content-between p-3 border-bottom last-border-0">
                            <div>
                                <div class="fw-semibold text-dark">{{ $log->employee?->name ?? 'Developer' }}</div>
                                <small class="text-muted">{{ $log->memo }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary-subtle text-primary fw-bold fs-6 px-3 py-1">{{ $log->total_hours }} hrs</span>
                                <div class="text-muted small mt-1">{{ \Carbon\Carbon::parse($log->start_date)->format('d M Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="bx bx-info-circle fs-3 d-block mb-1 opacity-50"></i>
                            No hours logged on this ticket yet. Click "+ Log Hours" to add developer working time to timesheets.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Conversation & Replies Timeline -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-conversation text-success me-2"></i> Conversation & Updates</h5>
                </div>
                <div class="card-body p-4">
                    @forelse($replies as $reply)
                        <div class="d-flex gap-3 mb-4 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                                    {{ strtoupper(substr($reply->user->name ?? 'U', 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $reply->user->name ?? 'User' }} <span class="badge bg-light text-secondary ms-2 small">{{ ucfirst($reply->user->role ?? 'user') }}</span></h6>
                                    <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-secondary mb-2" style="white-space: pre-line;">{{ $reply->message }}</p>
                                @if($reply->attachment)
                                    <a href="{{ asset($reply->attachment) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill">
                                        <i class="bx bx-paperclip me-1 text-primary"></i> {{ basename($reply->attachment) }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No conversation updates yet.</p>
                    @endforelse

                    <!-- Add Reply Form -->
                    <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Add Update / Comment</label>
                            <textarea name="message" class="form-control rounded-3" rows="3" placeholder="Type status update, developer notes, or client response..." required></textarea>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <input type="file" name="attachment" class="form-control form-control-sm w-auto" />
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bx bx-send me-1"></i> Post Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side Panel (Client, Project, Agent Details & Admin Controls) -->
        <div class="col-lg-4">
            <!-- Client & Project Details Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-briefcase text-primary me-2"></i> Client & Project Info</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Project Info -->
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Delivered Product / Project</label>
                        @if($ticket->project)
                            <div class="fw-bold text-dark fs-5">{{ $ticket->project->name }}</div>
                            <div class="small text-muted mb-2">Category: {{ $ticket->project->category?->name ?? 'Product' }}</div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                Status: {{ ucfirst($ticket->project->status ?? 'Active') }}
                            </span>
                        @else
                            <div class="text-muted italic">No specific project linked</div>
                        @endif
                    </div>

                    <hr />

                    <!-- Client Info -->
                    <div>
                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Client / Requester Details</label>
                        <div class="d-flex align-items-center gap-3 mt-2">
                            <div class="avatar bg-info text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($ticket->requester_name ?? 'C', 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $ticket->requester_name ?? 'Client' }}</div>
                                <div class="small text-muted">{{ $ticket->requester?->email ?? 'Client Account' }}</div>
                                <div class="small text-primary"><i class="bx bx-user me-1"></i> {{ ucfirst($ticket->requester_type ?? 'Client') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Developer & Ticket Management Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-user-check text-success me-2"></i> Assigned Developer</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tickets.updateDetails', $ticket->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assigned Developer / Agent</label>
                            <select name="agent_id" class="form-select rounded-3">
                                <option value="">-- Select Developer --</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ $ticket->agent_id == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }} ({{ $agent->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ticket Group</label>
                            <select name="group_id" class="form-select rounded-3">
                                <option value="">-- Select Group --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ $ticket->group_id == $group->id ? 'selected' : '' }}>
                                        {{ $group->group_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select rounded-3">
                                @foreach(['low', 'medium', 'high', 'critical'] as $p)
                                    <option value="{{ $p }}" {{ strtolower($ticket->priority) == $p ? 'selected' : '' }}>
                                        {{ ucfirst($p) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select rounded-3">
                                @foreach(['open', 'pending', 'resolved', 'closed', 'reopened'] as $st)
                                    <option value="{{ $st }}" {{ strtolower($ticket->status) == $st ? 'selected' : '' }}>
                                        {{ ucfirst($st) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                            <i class="bx bx-save me-1"></i> Update Ticket Details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Log Time Modal -->
<div class="modal fade" id="logTimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('tickets.logTime', $ticket->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="bx bx-time text-primary me-2"></i> Log Hours on Ticket Timesheet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="start_date" class="form-control rounded-3" value="{{ now()->toDateString() }}" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hours Worked</label>
                        <input type="number" step="0.25" min="0.25" max="24" name="hours" class="form-control rounded-3" placeholder="e.g. 2.5" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Work Summary / Memo</label>
                        <textarea name="memo" class="form-control rounded-3" rows="3" placeholder="Describe work done to resolve ticket..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Log to Timesheet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Reopen Ticket Modal -->
<div class="modal fade" id="reopenTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('tickets.reopen', $ticket->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom bg-danger-subtle">
                    <h5 class="modal-title fw-bold text-danger"><i class="bx bx-redo me-2"></i> Reopen Ticket #{{ $ticket->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary mb-3">Reopening this ticket will mark the status as <strong>REOPENED</strong> and send an instant notification to the assigned developer <strong>({{ $ticket->agent?->name ?? 'Assigned Developer' }})</strong> on their notification menu and Personal Dashboard.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason / Client Feedback Statement</label>
                        <textarea name="reason" class="form-control rounded-3" rows="3" placeholder="Enter reason for reopening (e.g. Client reported issue still occurs on checkout page)..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Reopen & Notify Developer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
