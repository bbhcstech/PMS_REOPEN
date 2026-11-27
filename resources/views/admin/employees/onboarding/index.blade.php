@extends('admin.layout.app')

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
<div id="employeeDirectoryPage" class="employee-dashboard employee-directory-modern">

    <div class="emp-breadcrumb-row">
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-house" aria-hidden="true"></i> Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('employees.index') }}">Employees</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Onboarding Applications</li>
                </ol>
            </nav>
        </div>
    </div>

    <header class="directory-hero">
        <div class="directory-hero-intro">
            <span class="directory-hero-icon" aria-hidden="true"><i class="fas fa-user-check"></i></span>
            <div>
                <h1>Employee Onboarding Applications</h1>
                <p>Track invitations, review self-submitted profiles, request corrections, and approve new employees.</p>
            </div>
        </div>
        <div class="directory-hero-actions">
            <a href="{{ route('employees.index') }}" class="btn directory-button">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>Employee Directory
            </a>
            <a href="{{ route('employees.create') }}" class="btn directory-button directory-button-primary">
                <i class="fas fa-user-plus" aria-hidden="true"></i>Add Employee
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-circle-exclamation me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div id="onboardingLiveAlert" class="alert d-none mb-4" role="status"></div>

    {{-- 7 Summary Cards --}}
    <div class="onboarding-stats-grid mb-4">
        @php
            $summaryCards = [
                ['label' => 'Total Invitations', 'value' => $counts['total_invitations'], 'status' => '', 'icon' => 'fa-paper-plane', 'tone' => 'total'],
                ['label' => 'Awaiting Submission', 'value' => $counts['awaiting_submission'], 'status' => 'awaiting', 'icon' => 'fa-hourglass-half', 'tone' => 'awaiting'],
                ['label' => 'Pending Review', 'value' => $counts['pending_review'], 'status' => 'pending_review', 'icon' => 'fa-clipboard-check', 'tone' => 'pending'],
                ['label' => 'Correction Requested', 'value' => $counts['correction_requested'], 'status' => 'correction_requested', 'icon' => 'fa-pen-to-square', 'tone' => 'correction'],
                ['label' => 'Approved', 'value' => $counts['approved'], 'status' => 'approved', 'icon' => 'fa-circle-check', 'tone' => 'approved'],
                ['label' => 'Rejected', 'value' => $counts['rejected'], 'status' => 'rejected', 'icon' => 'fa-circle-xmark', 'tone' => 'rejected'],
                ['label' => 'Expired Invitations', 'value' => $counts['expired_invitations'], 'status' => 'expired', 'icon' => 'fa-clock-rotate-left', 'tone' => 'expired'],
            ];
        @endphp
        @foreach($summaryCards as $card)
            <a href="{{ route('employees.onboarding.index', array_filter(['status' => $card['status'], 'company_id' => $selectedCompanyId])) }}"
               class="onboarding-stat-card {{ request('status', '') === $card['status'] ? 'is-selected' : '' }} tone-{{ $card['tone'] }}">
                <div class="stat-top">
                    <span class="stat-label">{{ $card['label'] }}</span>
                    <span class="stat-badge-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                </div>
                <div class="stat-val">{{ $card['value'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="filter-export-card mb-4">
        <div class="directory-panel-header">
            <div class="directory-panel-title">
                <span class="directory-panel-icon" aria-hidden="true"><i class="fas fa-filter"></i></span>
                <h2>Filter Invitations & Applications</h2>
            </div>
        </div>
        <div class="directory-filter-body">
            <form method="GET" action="{{ route('employees.onboarding.index') }}" class="row g-3 align-items-end">
                @if($isPlatformAdmin && $companies->isNotEmpty())
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Organization</label>
                        <select name="company_id" class="form-select">
                            <option value="">All Organizations</option>
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" {{ (string) $selectedCompanyId === (string) $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search Applicant / Email</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email or employee ID...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="awaiting" {{ request('status') === 'awaiting' ? 'selected' : '' }}>Awaiting Submission</option>
                        <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="correction_requested" {{ request('status') === 'correction_requested' ? 'selected' : '' }}>Correction Requested</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Method</label>
                    <select name="method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="email" {{ request('method') === 'email' ? 'selected' : '' }}>Email</option>
                        <option value="link" {{ request('method') === 'link' ? 'selected' : '' }}>Link</option>
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn directory-button directory-button-primary">
                        <i class="fas fa-search"></i> Apply
                    </button>
                    <a href="{{ route('employees.onboarding.index') }}" class="btn directory-button">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Applications Table --}}
    <div class="table-card">
        <div class="directory-panel-header">
            <div class="directory-panel-title">
                <span class="directory-panel-icon" aria-hidden="true"><i class="fas fa-inbox"></i></span>
                <h2>Applications Queue</h2>
                <span class="directory-count">{{ $records->total() }} records</span>
            </div>
        </div>

        @if($records->isEmpty())
            <div class="empty-state">
                <span class="directory-panel-icon" aria-hidden="true"><i class="fas fa-envelope-open-text"></i></span>
                <h3>No onboarding invitations or applications found</h3>
                <p>Send an invitation from the Employee Directory or adjust your filters.</p>
                <a href="{{ route('employees.index') }}" class="btn directory-button directory-button-primary">
                    <i class="fas fa-arrow-left"></i> Go to Employee Directory
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 employee-list-table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Employee Name</th>
                            <th>Photo</th>
                            <th>Email Address</th>
                            <th>Method</th>
                            <th>Invited By</th>
                            <th>Invitation Date</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $idx => $inv)
                            @php
                                $app = $inv->application;
                                $invStatus = $inv->effectiveStatus();
                                $displayStatus = $app?->status ?? ($invStatus === 'Pending' || $invStatus === 'Sent' ? 'Awaiting Submission' : $invStatus);
                                $badgeTone = match ($displayStatus) {
                                    'Pending Review' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                                    'Correction Requested' => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                                    'Approved' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                    'Rejected', 'Revoked' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                    'Expired' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
                                    default => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
                                };
                                $applicantName = $app?->name ?: 'Pending Submission';
                                $applicantEmail = $app?->email ?: ($inv->email ?: 'Open Link (OTP Pending)');
                            @endphp
                            <tr>
                                <td>{{ ($records->currentPage() - 1) * $records->perPage() + $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold employee-name">{{ $applicantName }}</div>
                                    @if($app?->approved_employee_id)
                                        <small class="text-muted">{{ $app->approved_employee_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($app && $app->profile_picture_path)
                                        <img src="{{ route('employees.onboarding.document', [$app->id, 'profile_picture']) }}"
                                             alt="{{ $applicantName }}" class="rounded-circle object-fit-cover"
                                             style="width:38px;height:38px;border:1px solid var(--dir-border);">
                                    @elseif($app && $app->approvedUser?->profile_image)
                                        <img src="{{ asset($app->approvedUser->profile_image) }}"
                                             alt="{{ $applicantName }}" class="rounded-circle object-fit-cover"
                                             style="width:38px;height:38px;border:1px solid var(--dir-border);">
                                    @else
                                        <span class="profile-placeholder d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                            <span class="profile-initials">{{ strtoupper(substr($applicantName, 0, 1)) }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $applicantEmail }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">{{ $inv->invitation_method }}</span>
                                </td>
                                <td>{{ $inv->inviter?->name ?? 'System' }}</td>
                                <td>{{ $inv->created_at?->format('d M Y, h:i A') }}</td>
                                <td>{{ $app?->submitted_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $badgeTone }}">{{ $displayStatus }}</span>
                                    @if($app?->status === 'Approved' && $app?->activation_email_error)
                                        <small class="d-block text-danger mt-1">Email failed</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($app)
                                            <a href="{{ route('employees.onboarding.review', $app->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas {{ $app->canApprove() ? 'fa-clipboard-check' : 'fa-eye' }} me-1"></i>
                                                {{ $app->canApprove() ? 'Review' : 'View' }}
                                            </a>
                                        @endif

                                        @if(!$app && $inv->isUsable())
                                            <button type="button" class="btn btn-sm btn-outline-primary ob-copy-link"
                                                    data-url="{{ route('employees.invitations.copy-link', $inv->id) }}"
                                                    title="Copy Invitation Link">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        @endif

                                        @if(!$app && !empty($inv->email) && in_array($invStatus, ['Pending', 'Sent', 'Expired'], true))
                                            <button type="button" class="btn btn-sm btn-outline-secondary ob-resend-invite"
                                                    data-url="{{ route('employees.invitations.resend', $inv->id) }}"
                                                    title="Resend Invitation Email">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        @endif

                                        @if(!$app && $inv->isUsable())
                                            <button type="button" class="btn btn-sm btn-outline-danger ob-revoke-invite"
                                                    data-url="{{ route('employees.invitations.revoke', $inv->id) }}"
                                                    title="Revoke Invitation">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif

                                        @if($app && $app->canRequestCorrection())
                                            <button type="button" class="btn btn-sm btn-outline-warning ob-open-correction"
                                                    data-action="{{ route('employees.onboarding.request-correction', $app->id) }}"
                                                    data-name="{{ $app->name }}"
                                                    title="Request Correction">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        @endif

                                        @if($app && $app->canReject())
                                            <button type="button" class="btn btn-sm btn-outline-danger ob-open-reject"
                                                    data-action="{{ route('employees.onboarding.reject', $app->id) }}"
                                                    data-name="{{ $app->name }}"
                                                    title="Reject Application">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        @if($app && $app->approved_user_id)
                                            <a href="{{ route('employees.show', $app->approved_user_id) }}"
                                               class="btn btn-sm btn-outline-success" title="View Approved Employee">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            @if($app->activation_email_error)
                                                <form method="POST" action="{{ route('employees.onboarding.resend-activation', $app->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-info" title="Resend Activation Email">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="pagination-info">
                    Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} entries
                </div>
                <div>{{ $records->links() }}</div>
            </div>
        @endif
    </div>
</div>

@include('admin.employees.partials.directory-styles')
@include('admin.employees.partials.directory-presentation')

<style>
    .onboarding-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
        gap: 12px;
    }
    .onboarding-stat-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 10px;
        padding: 16px;
        border-radius: 16px;
        background: var(--dir-surface, #fff);
        border: 1px solid var(--dir-border, #dce5f1);
        text-decoration: none;
        color: var(--dir-ink, #0f172a) !important;
        transition: border-color 0.16s ease, box-shadow 0.16s ease;
    }
    .onboarding-stat-card:hover,
    .onboarding-stat-card.is-selected {
        border-color: var(--dir-primary, #2563eb);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
    }
    .onboarding-stat-card .stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .onboarding-stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 650;
        color: var(--dir-muted, #52637b);
    }
    .onboarding-stat-card .stat-badge-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: var(--dir-accent-soft, #eaf0ff);
        color: var(--dir-accent, #2563eb);
    }
    .onboarding-stat-card .stat-val {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
        color: var(--dir-ink, #0f172a);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #employeeDirectoryPage .form-control,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #employeeDirectoryPage .form-select {
        background: var(--emp-surface-subtle) !important;
        border-color: var(--emp-border) !important;
        color: var(--emp-text) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #employeeDirectoryPage .badge.bg-light {
        background: var(--emp-surface-subtle) !important;
        color: var(--emp-text) !important;
        border-color: var(--emp-border) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #correctionRequestModal .modal-content,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #rejectApplicationModal .modal-content {
        background: #0f1530;
        color: #eef1fb;
        border: 1px solid rgba(148, 163, 184, 0.22);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #correctionRequestModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #correctionRequestModal .modal-footer,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #rejectApplicationModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #rejectApplicationModal .modal-footer {
        border-color: rgba(148, 163, 184, 0.16);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #correctionRequestModal .form-control,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #rejectApplicationModal .form-control {
        background: #141b3d;
        color: #eef1fb;
        border-color: rgba(148, 163, 184, 0.25);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #correctionRequestModal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #rejectApplicationModal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>
@endsection

@push('modals')
<div class="modal fade" id="correctionRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="correctionRequestForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen-to-square text-warning me-2"></i>Request Correction — <span id="correctionApplicantName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold" for="correctionReasonInput">Correction Message <span class="text-danger">*</span></label>
                <textarea id="correctionReasonInput" name="correction_reason" class="form-control" rows="4" required minlength="5" maxlength="2000"
                          placeholder="Explain clearly what details or documents the applicant should update..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Send Correction Request</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="rejectApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="rejectApplicationForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-circle-xmark text-danger me-2"></i>Reject Application — <span id="rejectApplicantName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold" for="rejectReasonInput">Rejection Reason <span class="text-danger">*</span></label>
                <textarea id="rejectReasonInput" name="rejection_reason" class="form-control" rows="4" required minlength="5" maxlength="2000"
                          placeholder="State the reason for rejecting this onboarding application..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const liveAlert = document.getElementById('onboardingLiveAlert');

    function showNotice(message, ok = true) {
        if (!liveAlert) return;
        liveAlert.className = 'alert mb-4 ' + (ok ? 'alert-success' : 'alert-danger');
        liveAlert.textContent = message;
        liveAlert.classList.remove('d-none');
    }

    document.querySelectorAll('.ob-copy-link').forEach(btn => {
        btn.addEventListener('click', async function () {
            btn.disabled = true;
            try {
                const res = await fetch(btn.dataset.url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!res.ok || !data.url) throw new Error(data.message || 'Could not retrieve link.');
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(data.url);
                } else {
                    window.prompt('Copy invitation link:', data.url);
                }
                showNotice('Invitation link copied to clipboard.', true);
            } catch (err) {
                showNotice(err.message || 'Failed to copy invitation link.', false);
            } finally {
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('.ob-resend-invite').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (!confirm('Resend the onboarding invitation email with a fresh 72-hour link?')) return;
            btn.disabled = true;
            try {
                const res = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Could not resend invitation.');
                showNotice(data.message || 'Invitation resent.', true);
            } catch (err) {
                showNotice(err.message || 'Failed to resend invitation.', false);
            } finally {
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('.ob-revoke-invite').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (!confirm('Revoke this invitation? The recipient will no longer be able to submit using this link.')) return;
            btn.disabled = true;
            try {
                const res = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Could not revoke invitation.');
                location.reload();
            } catch (err) {
                showNotice(err.message || 'Failed to revoke invitation.', false);
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('.ob-open-correction').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('correctionRequestForm').action = btn.dataset.action;
            document.getElementById('correctionApplicantName').textContent = btn.dataset.name;
            document.getElementById('correctionReasonInput').value = '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('correctionRequestModal')).show();
        });
    });

    document.querySelectorAll('.ob-open-reject').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('rejectApplicationForm').action = btn.dataset.action;
            document.getElementById('rejectApplicantName').textContent = btn.dataset.name;
            document.getElementById('rejectReasonInput').value = '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectApplicationModal')).show();
        });
    });
});
</script>
@endpush
