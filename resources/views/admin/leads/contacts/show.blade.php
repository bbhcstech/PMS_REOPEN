@extends('admin.layout.app')

@section('content')

@push('styles')
<style>
/* Scoped Lead Contact UI/UX Styles */
.lead-details-wrapper {
    font-family: 'Public Sans', system-ui, -apple-system, sans-serif;
}

.lead-header-card {
    background: #ffffff;
    border: 1px solid rgba(15, 116, 76, 0.12);
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
}

.lead-avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, #0f744c, #16a34a);
    color: #ffffff;
    font-weight: 800;
    font-size: 22px;
    box-shadow: 0 8px 18px rgba(15, 116, 76, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Custom High Contrast Badges */
.lead-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 750;
    letter-spacing: 0.01em;
    line-height: 1.2;
}

/* Custom Nav Tabs */
.lead-nav-tabs {
    background: #f1f5f9;
    border: 1px solid #cbd5e1 !important;
    padding: 6px;
    border-radius: 16px;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.lead-nav-tabs .nav-item {
    margin: 0;
}

.lead-nav-tabs .nav-link,
.lead-nav-tabs button.nav-link {
    border: 0 !important;
    border-radius: 12px !important;
    padding: 10px 20px !important;
    color: #334155 !important;
    font-weight: 750 !important;
    font-size: 0.88rem !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent !important;
    box-shadow: none !important;
}

.lead-nav-tabs .nav-link span,
.lead-nav-tabs button.nav-link span {
    color: inherit !important;
    font-weight: 750 !important;
}

.lead-nav-tabs .nav-link i,
.lead-nav-tabs button.nav-link i {
    font-size: 0.95rem;
    transition: color 0.2s ease;
}

/* Inactive Icon Colors */
.lead-nav-tabs .nav-link:not(.active)#overview-tab i { color: #2563eb !important; }
.lead-nav-tabs .nav-link:not(.active)#deals-tab i { color: #16a34a !important; }
.lead-nav-tabs .nav-link:not(.active)#activities-tab i { color: #d97706 !important; }
.lead-nav-tabs .nav-link:not(.active)#followups-tab i { color: #0891b2 !important; }
.lead-nav-tabs .nav-link:not(.active)#notes-tab i { color: #64748b !important; }

/* Tab Hover State */
.lead-nav-tabs .nav-link:not(.active):hover,
.lead-nav-tabs button.nav-link:not(.active):hover {
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
}

.lead-nav-tabs .nav-link:not(.active):hover span,
.lead-nav-tabs button.nav-link:not(.active):hover span {
    color: #0f172a !important;
}

/* Tab ACTIVE / SELECTED State (When clicked or active) */
.lead-nav-tabs .nav-link.active,
.lead-nav-tabs button.nav-link.active,
.lead-nav-tabs .nav-link.active:focus,
.lead-nav-tabs button.nav-link.active:focus,
.lead-nav-tabs .nav-link.active:hover,
.lead-nav-tabs button.nav-link.active:hover {
    background: linear-gradient(135deg, #0f744c, #094c32) !important;
    color: #ffffff !important;
    box-shadow: 0 8px 20px rgba(15, 116, 76, 0.35) !important;
}

/* Force ALL child elements inside active/selected tab to pure white */
.lead-nav-tabs .nav-link.active *,
.lead-nav-tabs button.nav-link.active *,
.lead-nav-tabs .nav-link.active span,
.lead-nav-tabs button.nav-link.active span,
.lead-nav-tabs .nav-link.active i,
.lead-nav-tabs button.nav-link.active i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

/* Tab Count Badges */
.lead-nav-tabs .nav-link .tab-count-badge,
.lead-nav-tabs button.nav-link .tab-count-badge {
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 800;
}

.lead-nav-tabs .nav-link.active .tab-count-badge,
.lead-nav-tabs button.nav-link.active .tab-count-badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
}

.lead-nav-tabs .nav-link:not(.active) .tab-count-badge,
.lead-nav-tabs button.nav-link:not(.active) .tab-count-badge {
    background: #e2e8f0 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}

/* Text Highlight / Selection Styles */
.lead-details-wrapper ::selection {
    background: #0f744c !important;
    color: #ffffff !important;
}

.lead-details-wrapper ::-moz-selection {
    background: #0f744c !important;
    color: #ffffff !important;
}

/* Info Box Cards */
.info-field-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 16px;
    transition: all 0.2s ease;
    height: 100%;
}

.info-field-card:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}

.info-icon-box {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #0f744c;
    flex-shrink: 0;
}

.info-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b !important;
    margin-bottom: 2px;
}

.info-value {
    font-size: 0.94rem;
    font-weight: 700;
    color: #0f172a !important;
    word-break: break-word;
}

/* Metric Callout Box */
.opportunity-callout-box {
    background: linear-gradient(135deg, rgba(15, 116, 76, 0.08), rgba(22, 163, 74, 0.04));
    border: 1px solid rgba(15, 116, 76, 0.2);
    border-radius: 16px;
    padding: 18px;
}

/* Button Refinement */
.btn-action-primary {
    background: linear-gradient(135deg, #0f744c, #094c32);
    color: #ffffff !important;
    border: none;
    border-radius: 12px;
    padding: 8px 16px;
    font-weight: 700;
    box-shadow: 0 6px 16px rgba(15, 116, 76, 0.22);
    transition: all 0.2s ease;
}

.btn-action-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(15, 116, 76, 0.3);
    color: #ffffff !important;
}

.btn-action-warning {
    background: #f59e0b;
    color: #1e1b4b !important;
    border: none;
    border-radius: 12px;
    padding: 8px 16px;
    font-weight: 750;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.22);
    transition: all 0.2s ease;
}

.btn-action-warning:hover {
    transform: translateY(-1px);
    background: #d97706;
    color: #ffffff !important;
}

.btn-action-success {
    background: #10b981;
    color: #ffffff !important;
    border: none;
    border-radius: 12px;
    padding: 8px 16px;
    font-weight: 700;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.22);
    transition: all 0.2s ease;
}

.btn-action-success:hover {
    transform: translateY(-1px);
    background: #059669;
    color: #ffffff !important;
}

.btn-action-info {
    background: #6366f1;
    color: #ffffff !important;
    border: none;
    border-radius: 12px;
    padding: 8px 16px;
    font-weight: 700;
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.22);
    transition: all 0.2s ease;
}

.btn-action-info:hover {
    transform: translateY(-1px);
    background: #4f46e5;
    color: #ffffff !important;
}

.btn-action-outline {
    background: #ffffff;
    color: #334155 !important;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 8px 16px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.btn-action-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a !important;
}

.back-btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #334155;
    transition: all 0.2s ease;
}

.back-btn-icon:hover {
    background: #0f744c;
    border-color: #0f744c;
    color: #ffffff;
}

/* Timeline Styling */
.crm-timeline-item {
    position: relative;
    padding-left: 50px;
    padding-bottom: 24px;
}

.crm-timeline-item::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 36px;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.crm-timeline-item:last-child::before {
    display: none;
}

.crm-timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

/* Dark Mode Support */
html[data-pms-theme="dark"] .lead-header-card,
html[data-pms-theme="dark"] .info-field-card {
    background: #102119 !important;
    border-color: rgba(225, 255, 240, 0.15) !important;
}

html[data-pms-theme="dark"] .lead-nav-tabs {
    background: #183026 !important;
    border-color: rgba(225, 255, 240, 0.15) !important;
}

html[data-pms-theme="dark"] .info-icon-box {
    background: #183026 !important;
    border-color: rgba(225, 255, 240, 0.15) !important;
    color: #40d48c !important;
}

html[data-pms-theme="dark"] .info-value,
html[data-pms-theme="dark"] .lead-header-title {
    color: #ffffff !important;
}

html[data-pms-theme="dark"] .info-label {
    color: #94a3b8 !important;
}

html[data-pms-theme="dark"] .btn-action-outline {
    background: #102119 !important;
    color: #ffffff !important;
    border-color: rgba(225, 255, 240, 0.2) !important;
}
</style>
@endpush

<div class="container-fluid py-3 lead-details-wrapper">

    @php
        // -------------------------------------------------------------
        // HIGH CONTRAST BADGE COLOR COMPUTATIONS (WCAG AAA Compliant)
        // -------------------------------------------------------------

        // Lead Score Badge
        $score = $lead->lead_score ?? 0;
        $scoreCategory = $lead->lead_score_category;
        if ($score >= 81) {
            $scoreStyle = 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;'; // Very Hot
            $scoreIcon = 'fa-fire text-danger';
        } elseif ($score >= 61) {
            $scoreStyle = 'background: #fffbe6; color: #b45309; border: 1px solid #fef08a;'; // Hot
            $scoreIcon = 'fa-bolt text-warning';
        } elseif ($score >= 31) {
            $scoreStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;'; // Warm
            $scoreIcon = 'fa-temperature-half text-primary';
        } else {
            $scoreStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;'; // Cold
            $scoreIcon = 'fa-snowflake text-secondary';
        }

        // Priority Badge
        $prio = strtolower($lead->priority ?? 'medium');
        $prioStyle = match($prio) {
            'urgent' => 'background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;',
            'high' => 'background: #fffbe6; color: #b45309; border: 1px solid #fef08a;',
            'low' => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
            default => 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;', // Medium
        };

        // Status Badge
        $st = strtolower($lead->status ?? 'new');
        $statusStyle = match($st) {
            'converted' => 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;',
            'new' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
            'qualified' => 'background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff;',
            'contacted', 'in_progress', 'hot' => 'background: #fef9c3; color: #854d0e; border: 1px solid #fef08a;',
            'lost' => 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;',
            default => 'background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
        };
    @endphp

    {{-- HEADER & BREADCRUMB --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('leads.contacts.index') }}" class="back-btn-icon text-decoration-none" title="Back to Lead Contacts">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <span class="text-muted small fw-semibold">Lead Contacts</span>
                <i class="fas fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="fw-bold text-dark fs-6">{{ $lead->contact_name }}</span>
            </div>
            <h3 class="fw-extrabold mb-0 lead-header-title text-dark">
                {{ $lead->salutation ? $lead->salutation . ' ' : '' }}{{ $lead->contact_name }}
                @if($lead->company_name)
                    <span class="text-muted fs-6 fw-normal ms-1">at <strong class="text-secondary">{{ $lead->company_name }}</strong></span>
                @endif
            </h3>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button class="btn btn-action-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                <i class="fas fa-plus-circle me-1"></i> Add Activity
            </button>
            <button class="btn btn-action-warning btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleFollowUpModal">
                <i class="fas fa-calendar-plus me-1"></i> Schedule Follow-up
            </button>
            <a href="{{ route('admin.deals.create', ['lead_id' => $lead->id]) }}" class="btn btn-action-success btn-sm">
                <i class="fas fa-handshake me-1"></i> Create Deal
            </a>
            @if($lead->type !== 'client' && $lead->status !== 'converted')
                <button class="btn btn-action-info btn-sm" id="btnConvertClient">
                    <i class="fas fa-user-check me-1"></i> Convert to Client
                </button>
            @endif
            <a href="{{ route('leads.contacts.edit', $lead->id) }}" class="btn btn-action-outline btn-sm">
                <i class="fas fa-pen me-1"></i> Edit
            </a>
        </div>
    </div>

    {{-- TOP SUMMARY CARD --}}
    <div class="card lead-header-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-5 col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="lead-avatar-circle">
                            {{ strtoupper(substr($lead->contact_name, 0, 2)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">{{ $lead->contact_name }}</h5>
                            <p class="text-muted small mb-1 fw-semibold">
                                <i class="fas fa-briefcase me-1 text-secondary"></i>{{ $lead->job_title ?: 'No Job Title Specified' }}
                            </p>
                            <a href="mailto:{{ $lead->email }}" class="small text-primary text-decoration-none fw-bold">
                                <i class="far fa-envelope me-1"></i>{{ $lead->email }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 col-md-6">
                    <div class="d-flex flex-wrap gap-4 justify-content-md-end align-items-center">
                        {{-- Lead Score --}}
                        <div class="text-center px-2">
                            <span class="text-muted small d-block fw-bold uppercase-label mb-1">Lead Score</span>
                            <span class="lead-badge" style="{{ $scoreStyle }}">
                                <i class="fas {{ $scoreIcon }}"></i>
                                {{ $scoreCategory }} ({{ $score }})
                            </span>
                        </div>

                        {{-- Priority --}}
                        <div class="text-center px-2 border-start">
                            <span class="text-muted small d-block fw-bold uppercase-label mb-1">Priority</span>
                            <span class="lead-badge text-capitalize" style="{{ $prioStyle }}">
                                <i class="fas fa-layer-group"></i>
                                {{ $prio }}
                            </span>
                        </div>

                        {{-- Status --}}
                        <div class="text-center px-2 border-start">
                            <span class="text-muted small d-block fw-bold uppercase-label mb-1">Status</span>
                            <span class="lead-badge text-capitalize" style="{{ $statusStyle }}">
                                <i class="fas fa-check-circle"></i>
                                {{ $lead->status ?? 'new' }}
                            </span>
                        </div>

                        {{-- Lead Owner --}}
                        <div class="text-center px-2 border-start">
                            <span class="text-muted small d-block fw-bold uppercase-label mb-1">Lead Owner</span>
                            <div class="d-flex align-items-center justify-content-center gap-1 mt-1">
                                <i class="fas fa-user-circle text-primary fs-6"></i>
                                <span class="fw-bold small text-dark">
                                    {{ $lead->owner->name ?? 'Unassigned' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <ul class="nav lead-nav-tabs mb-4" id="leadTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button">
                <i class="fas fa-info-circle me-1"></i>
                <span>Overview</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="deals-tab" data-bs-toggle="tab" data-bs-target="#deals-pane" type="button">
                <i class="fas fa-handshake me-1"></i>
                <span>Deals</span>
                <span class="tab-count-badge">{{ $lead->deals->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities-pane" type="button">
                <i class="fas fa-stream me-1"></i>
                <span>Activity Timeline</span>
                <span class="tab-count-badge">{{ $lead->activities->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="followups-tab" data-bs-toggle="tab" data-bs-target="#followups-pane" type="button">
                <i class="fas fa-calendar-check me-1"></i>
                <span>Follow-ups</span>
                <span class="tab-count-badge">{{ $lead->followUps->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-pane" type="button">
                <i class="fas fa-sticky-note me-1"></i>
                <span>Notes</span>
            </button>
        </li>
    </ul>

    {{-- TAB CONTENTS --}}
    <div class="tab-content">

        {{-- TAB 1: OVERVIEW --}}
        <div class="tab-pane fade show active" id="overview-pane">
            <div class="row g-4">
                {{-- LEFT COLUMN: Details & Address --}}
                <div class="col-lg-8">
                    {{-- CONTACT & COMPANY DETAILS --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-id-card text-primary"></i> Contact & Company Details
                            </h6>
                            <span class="badge bg-light text-secondary border px-2 py-1 small">10 Fields</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Full Name --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-user"></i></div>
                                        <div>
                                            <div class="info-label">Full Name</div>
                                            <div class="info-value">{{ $lead->contact_name }}</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Job Title --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-briefcase"></i></div>
                                        <div>
                                            <div class="info-label">Job Title</div>
                                            <div class="info-value">{{ $lead->job_title ?: 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Email --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-envelope"></i></div>
                                        <div class="overflow-hidden">
                                            <div class="info-label">Email</div>
                                            <a href="mailto:{{ $lead->email }}" class="info-value text-primary text-decoration-none text-truncate d-block">
                                                {{ $lead->email }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                {{-- Primary Phone --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-phone"></i></div>
                                        <div>
                                            <div class="info-label">Primary Phone</div>
                                            @if($lead->phone)
                                                <a href="tel:{{ $lead->phone }}" class="info-value text-dark text-decoration-none">{{ $lead->phone }}</a>
                                            @else
                                                <div class="info-value text-muted">N/A</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- Mobile / Cell --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-mobile-alt"></i></div>
                                        <div>
                                            <div class="info-label">Mobile / Cell</div>
                                            @if($lead->mobile)
                                                <a href="tel:{{ $lead->mobile }}" class="info-value text-dark text-decoration-none">{{ $lead->mobile }}</a>
                                            @else
                                                <div class="info-value text-muted">N/A</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- WhatsApp --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fab fa-whatsapp text-success fs-5"></i></div>
                                        <div>
                                            <div class="info-label">WhatsApp</div>
                                            @if($lead->whatsapp)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp) }}" target="_blank" class="info-value text-success text-decoration-none fw-bold">
                                                    {{ $lead->whatsapp }} <i class="fas fa-external-link-alt ms-1 small"></i>
                                                </a>
                                            @else
                                                <div class="info-value text-muted">N/A</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- Company Name --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-building"></i></div>
                                        <div>
                                            <div class="info-label">Company Name</div>
                                            <div class="info-value">{{ $lead->company_name ?: 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Website --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-globe"></i></div>
                                        <div class="overflow-hidden">
                                            <div class="info-label">Website</div>
                                            @if($lead->website)
                                                <a href="{{ $lead->website }}" target="_blank" class="info-value text-primary text-decoration-none text-truncate d-block">
                                                    {{ $lead->website }} <i class="fas fa-external-link-alt ms-1 small"></i>
                                                </a>
                                            @else
                                                <div class="info-value text-muted">N/A</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- Industry --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-industry"></i></div>
                                        <div>
                                            <div class="info-label">Industry</div>
                                            <div class="info-value">{{ $lead->industry ?: 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Lead Source --}}
                                <div class="col-sm-6">
                                    <div class="info-field-card d-flex align-items-center gap-3">
                                        <div class="info-icon-box"><i class="fas fa-bullhorn"></i></div>
                                        <div>
                                            <div class="info-label">Lead Source</div>
                                            <div class="info-value">{{ ucfirst($lead->lead_source) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LOCATION ADDRESS CARD --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-map-marker-alt text-danger"></i> Location Address
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="info-icon-box bg-light text-danger"><i class="fas fa-map-pin"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">{{ $lead->address ?: 'No street address provided.' }}</h6>
                                    <p class="mb-2 text-muted small fw-semibold">
                                        {{ implode(', ', array_filter([$lead->city, $lead->state, $lead->country, $lead->postal_code])) ?: 'Location details not specified.' }}
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @if($lead->city)<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-city me-1 text-muted"></i>{{ $lead->city }}</span>@endif
                                        @if($lead->state)<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-map me-1 text-muted"></i>{{ $lead->state }}</span>@endif
                                        @if($lead->country)<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-flag me-1 text-muted"></i>{{ $lead->country }}</span>@endif
                                        @if($lead->postal_code)<span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-mail-bulk me-1 text-muted"></i>{{ $lead->postal_code }}</span>@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Opportunity Metrics --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-chart-pie text-success"></i> Opportunity Metrics
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            {{-- Expected Value Box --}}
                            <div class="opportunity-callout-box mb-4">
                                <div class="info-label text-success">EXPECTED VALUE</div>
                                <h3 class="fw-extrabold text-success mb-0 mt-1">₹{{ number_format($lead->expected_value ?? 0, 2) }}</h3>
                            </div>

                            {{-- Expected Closing Date --}}
                            <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-light">
                                <div class="info-icon-box text-primary"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <div class="info-label">Expected Closing Date</div>
                                    <div class="fw-bold text-dark">{{ $lead->expected_closing_date ? $lead->expected_closing_date->format('M d, Y') : 'Not Set' }}</div>
                                </div>
                            </div>

                            {{-- Last Contacted --}}
                            <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-light">
                                <div class="info-icon-box text-info"><i class="fas fa-clock"></i></div>
                                <div>
                                    <div class="info-label">Last Contacted</div>
                                    <div class="fw-bold text-dark">{{ $lead->last_contacted_at ? $lead->last_contacted_at->diffForHumans() : 'Never' }}</div>
                                </div>
                            </div>

                            {{-- Next Follow-up --}}
                            <div class="d-flex align-items-center gap-3 mb-4 p-2 rounded-3 bg-light">
                                <div class="info-icon-box text-warning"><i class="fas fa-bell"></i></div>
                                <div>
                                    <div class="info-label">Next Follow-up</div>
                                    <div class="fw-bold {{ $lead->next_follow_up ? 'text-primary' : 'text-success' }}">
                                        {{ $lead->next_follow_up ? $lead->next_follow_up->format('M d, Y') : 'None Scheduled' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Tags --}}
                            <div class="border-top pt-3">
                                <div class="info-label mb-2"><i class="fas fa-tags me-1"></i> Tags</div>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($lead->tags_array as $tg)
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">{{ $tg }}</span>
                                    @empty
                                        <span class="text-muted small fw-semibold">No tags assigned</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: DEALS --}}
        <div class="tab-pane fade" id="deals-pane">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-handshake text-success"></i> Associated Deals
                    </h6>
                    <a href="{{ route('admin.deals.create', ['lead_id' => $lead->id]) }}" class="btn btn-action-success btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Deal
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Deal Name</th>
                                    <th>Stage</th>
                                    <th>Value</th>
                                    <th>Probability</th>
                                    <th>Weighted Value</th>
                                    <th>Close Date</th>
                                    <th>Agent</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lead->deals as $dl)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <a href="{{ route('admin.deals.show', $dl->id) }}" class="text-dark text-decoration-none hover-primary">
                                                {{ $dl->deal_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white px-3 py-1 rounded-pill">
                                                {{ $dl->stage->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-success">{{ $dl->currency }} {{ number_format($dl->value, 2) }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $dl->probability }}%</span></td>
                                        <td class="fw-bold text-dark">{{ $dl->currency }} {{ number_format($dl->weighted_value ?? $dl->calculateWeightedValue(), 2) }}</td>
                                        <td class="small">{{ $dl->close_date ? $dl->close_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="small fw-semibold text-secondary">
                                                <i class="fas fa-user-circle me-1"></i>{{ $dl->agent->name ?? 'Unassigned' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.deals.show', $dl->id) }}" class="btn btn-sm btn-action-outline py-1 px-3">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-handshake text-muted mb-2 fs-2 d-block opacity-50"></i>
                                            No deals associated with this lead contact yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: ACTIVITIES TIMELINE --}}
        <div class="tab-pane fade" id="activities-pane">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-stream text-warning"></i> CRM Activity Timeline
                    </h6>
                    <button class="btn btn-action-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                        <i class="fas fa-plus me-1"></i> Log Activity
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="timeline ps-2">
                        @forelse($lead->activities as $act)
                            @php
                                $actType = strtolower($act->type ?? 'note');
                                $actIcon = match($actType) {
                                    'call' => 'fa-phone-alt bg-primary text-white',
                                    'email' => 'fa-envelope bg-info text-white',
                                    'meeting' => 'fa-users bg-success text-white',
                                    'created' => 'fa-plus bg-teal text-white',
                                    default => 'fa-sticky-note bg-warning text-dark',
                                };
                            @endphp
                            <div class="crm-timeline-item">
                                <div class="crm-timeline-icon {{ $actIcon }}">
                                    <i class="fas {{ strtok($actIcon, ' ') }}"></i>
                                </div>
                                <div class="bg-light p-3 rounded-4 border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 text-dark">{{ $act->title }}</h6>
                                        <small class="text-muted fw-semibold">{{ $act->activity_date ? $act->activity_date->diffForHumans() : $act->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-2 text-secondary small fw-semibold" style="white-space: pre-wrap;">{{ $act->description }}</p>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-white text-dark border small"><i class="fas fa-user me-1 text-primary"></i>By {{ $act->creator->name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-stream text-muted mb-2 fs-2 d-block opacity-50"></i>
                                No activity records logged for this lead contact yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: FOLLOW-UPS --}}
        <div class="tab-pane fade" id="followups-pane">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-calendar-check text-info"></i> Scheduled Follow-ups
                    </h6>
                    <button class="btn btn-action-warning btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleFollowUpModal">
                        <i class="fas fa-calendar-plus me-1"></i> Schedule Follow-up
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Type</th>
                                    <th>Date & Time</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lead->followUps as $fl)
                                    <tr>
                                        <td class="ps-4 fw-bold text-capitalize">
                                            <i class="fas fa-bell text-warning me-2"></i>{{ $fl->follow_up_type }}
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            {{ $fl->date ? $fl->date->format('M d, Y') : '' }} <span class="text-muted small ms-1">{{ $fl->time }}</span>
                                        </td>
                                        <td>
                                            <span class="small fw-bold text-dark">
                                                <i class="fas fa-user-circle me-1 text-primary"></i>{{ $fl->assignee->name ?? 'Unassigned' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-capitalize px-3 py-1 rounded-pill">
                                                {{ $fl->status }}
                                            </span>
                                        </td>
                                        <td class="small text-secondary">{{ $fl->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-check text-muted mb-2 fs-2 d-block opacity-50"></i>
                                            No follow-ups scheduled yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 5: NOTES --}}
        <div class="tab-pane fade" id="notes-pane">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-sticky-note text-secondary"></i> Description & Notes
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="bg-light p-4 rounded-4 border">
                        <p class="text-dark mb-0 fw-semibold" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">{{ $lead->description ?: 'No additional notes added for this lead contact.' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL: ADD ACTIVITY --}}
<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Log Lead Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.contacts.activities.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Activity Type</label>
                        <select name="type" class="form-select" required>
                            <option value="call">Call Completed</option>
                            <option value="email">Email Sent</option>
                            <option value="meeting">Meeting Held</option>
                            <option value="note">Note Added</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Activity Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Discussed Enterprise Pricing">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Details / Outcome</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter discussion summary..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-action-outline btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4">Log Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: SCHEDULE FOLLOW-UP --}}
<div class="modal fade" id="scheduleFollowUpModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-plus text-warning me-2"></i>Schedule Follow-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.contacts.follow-ups.store', $lead->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Follow-up Type</label>
                        <select name="follow_up_type" class="form-select" required>
                            <option value="call">Phone Call</option>
                            <option value="email">Send Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="task">Task / Action</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Date</label>
                            <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Time</label>
                            <input type="time" name="time" class="form-control" value="10:00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $lead->lead_owner_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Instructions / Notes</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="What needs to be discussed?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-action-outline btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-action-warning btn-sm px-4">Schedule Follow-up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnConvertClient = document.getElementById('btnConvertClient');
    if (btnConvertClient) {
        btnConvertClient.addEventListener('click', function() {
            if (confirm('Convert lead "{{ $lead->contact_name }}" to Client? This will create a permanent Client record.')) {
                fetch('{{ route("leads.contacts.convert") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lead_id: {{ $lead->id }} })
                })
                .then(r => r.json())
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

