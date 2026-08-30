@extends('admin.layout.app')

@section('content')

<style>
.crm-kpi-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    border: 1px solid #eef2f6;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}
.crm-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
}
.crm-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.crm-kpi-title {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.crm-kpi-value {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0;
}
.top-bar {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    background: #ffffff;
    padding: 16px 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    border: 1px solid #eef2f6;
}
.filter-sidebar {
    position: fixed;
    top: 0;
    right: -420px;
    width: 400px;
    height: 100vh;
    background: #fff;
    box-shadow: -4px 0 15px rgba(0,0,0,0.1);
    z-index: 1050;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}
.filter-sidebar.active {
    right: 0;
}
.filter-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(2px);
    z-index: 1040;
    display: none;
}
.filter-overlay.active {
    display: block;
}
.filter-header {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}
.filter-body {
    padding: 20px;
}
.badge-priority-urgent { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.badge-priority-high { background-color: #ffedd5; color: #9a3412; border: 1px solid #fdba74; }
.badge-priority-medium { background-color: #e0f2fe; color: #075985; border: 1px solid #7dd3fc; }
.badge-priority-low { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.badge-status { font-weight: 600; padding: 5px 10px; border-radius: 6px; font-size: 12px; }
.type-badge { font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase; }
.type-lead { background-color: #fef3c7; color: #92400e; }
.type-client { background-color: #d1fae5; color: #065f46; }

/* Premium Table UI/UX Styles */
.premium-table-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
    transition: all 0.2s ease;
}
html[data-pms-theme="dark"] .premium-table-card {
    background: #102119;
    border-color: rgba(225, 255, 240, 0.12);
}
.premium-table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.premium-table thead tr {
    background-color: #f8fafc;
    border-bottom: 2px solid #cbd5e1;
}
html[data-pms-theme="dark"] .premium-table thead tr {
    background-color: #183026;
    border-bottom-color: rgba(225, 255, 240, 0.15);
}
.premium-table th {
    font-size: 0.73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #475569;
    padding: 1rem 1.1rem;
    border-bottom: 2px solid #cbd5e1;
    border-right: 1px solid #e2e8f0;
    white-space: nowrap;
}
.premium-table th:last-child {
    border-right: none;
}
html[data-pms-theme="dark"] .premium-table th {
    color: #cbd5e1;
    border-bottom-color: rgba(225, 255, 240, 0.15);
    border-right-color: rgba(225, 255, 240, 0.1);
}
.premium-table tbody tr {
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.15s ease-in-out;
}
html[data-pms-theme="dark"] .premium-table tbody tr {
    border-bottom-color: rgba(225, 255, 240, 0.08);
}
.premium-table tbody tr:hover {
    background-color: #f8fafc;
}
html[data-pms-theme="dark"] .premium-table tbody tr:hover {
    background-color: #162a21;
}
.premium-table td {
    padding: 1.1rem 1.1rem;
    vertical-align: middle;
    font-size: 0.86rem;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
}
.premium-table td:last-child {
    border-right: none;
}
html[data-pms-theme="dark"] .premium-table td {
    color: #e2e8f0;
    border-bottom-color: rgba(225, 255, 240, 0.08);
    border-right-color: rgba(225, 255, 240, 0.1);
}
.avatar-badge-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 13.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}
.badge-pill-source {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
}
html[data-pms-theme="dark"] .badge-pill-source {
    background-color: #183026;
    color: #cbd5e1;
    border-color: rgba(225, 255, 240, 0.15);
}
.badge-score-very-hot,
.badge-score-hot,
.badge-score-warm,
.badge-score-cold,
.badge-pill-priority-urgent,
.badge-pill-priority-high,
.badge-pill-priority-medium,
.badge-pill-priority-low,
.badge-pill-status-qualified,
.badge-pill-status-new,
.badge-pill-status-contacted,
.badge-pill-status-converted,
.badge-pill-status-lost {
    white-space: nowrap !important;
    display: inline-block !important;
    text-align: center;
}
.badge-score-very-hot {
    background-color: #fce7f3;
    color: #be123c;
    border: 1px solid #fbcfe8;
    border-radius: 50px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-score-hot {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
    border-radius: 50px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-score-warm {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    border-radius: 50px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-score-cold {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.35rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-urgent {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-high {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-medium {
    background-color: #e0f2fe;
    color: #0284c7;
    border: 1px solid #93c5fd;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-low {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-status-qualified {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-status-new {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-status-contacted {
    background-color: #f3e8ff;
    color: #7e22ce;
    border: 1px solid #e9d5ff;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-status-converted {
    background-color: #d1fae5;
    color: #047857;
    border: 1px solid #a7f3d0;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-status-lost {
    background-color: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.35rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.btn-action-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.btn-action-circle:hover, .btn-action-circle:focus {
    background-color: #f8fafc;
    color: #0f172a;
    border-color: #94a3b8;
}
html[data-pms-theme="dark"] .btn-action-circle {
    background-color: #183026;
    border-color: rgba(225, 255, 240, 0.15);
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .btn-action-circle:hover {
    background-color: #204033;
    color: #ffffff;
}
.dropdown-menu-premium {
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    padding: 0.5rem;
}
html[data-pms-theme="dark"] .dropdown-menu-premium {
    background: #102119;
    border-color: rgba(225, 255, 240, 0.15);
}
.dropdown-menu-premium .dropdown-item {
    border-radius: 8px;
    padding: 0.55rem 0.85rem;
    font-size: 0.84rem;
    font-weight: 500;
    color: #334155;
    transition: all 0.15s ease;
}
.dropdown-menu-premium .dropdown-item:hover {
    background-color: #f8fafc;
    color: #0f172a;
}
html[data-pms-theme="dark"] .dropdown-menu-premium .dropdown-item {
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .dropdown-menu-premium .dropdown-item:hover {
    background-color: #183026;
    color: #ffffff;
}
.premium-table-footer {
    background-color: #e2e8f0;
    border-top: 1px solid #cbd5e1;
    padding: 0.9rem 1.25rem;
    font-size: 0.84rem;
    color: #475569;
    font-weight: 600;
}
html[data-pms-theme="dark"] .premium-table-footer {
    background-color: #14281e;
    border-top-color: rgba(225, 255, 240, 0.08);
    color: #94a3b8;
}
</style>

<div class="container-fluid py-3">

    {{-- HEADER & BREADCRUMB --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;"><i class="fas fa-users-cog me-2 text-success"></i>Lead Contact Management</h4>
            <p class="text-muted small mb-0">Manage your organization's sales leads, prospects, and contact details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('leads.contacts.create') }}" class="btn btn-success fw-semibold shadow-sm px-3">
                <i class="fas fa-plus me-1"></i> Add Lead Contact
            </a>
            <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Import
            </button>
            <a href="{{ route('leads.contacts.export', request()->query()) }}" class="btn btn-outline-secondary px-3">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    {{-- KPI CARDS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Total Leads</span>
                    <div class="crm-kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h3 class="crm-kpi-value">{{ number_format($kpiStats['total'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">New Leads</span>
                    <div class="crm-kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <h3 class="crm-kpi-value text-info">{{ number_format($kpiStats['new'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Qualified</span>
                    <div class="crm-kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <h3 class="crm-kpi-value text-warning">{{ number_format($kpiStats['qualified'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Hot Leads</span>
                    <div class="crm-kpi-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
                <h3 class="crm-kpi-value text-danger">{{ number_format($kpiStats['hot'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Converted</span>
                    <div class="crm-kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <h3 class="crm-kpi-value text-success">{{ number_format($kpiStats['converted'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Lost Leads</span>
                    <div class="crm-kpi-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
                <h3 class="crm-kpi-value text-secondary">{{ number_format($kpiStats['lost'] ?? 0) }}</h3>
            </div>
        </div>
    </div>

    {{-- TOP FILTER & SEARCH BAR --}}
    <form method="GET" action="{{ route('leads.contacts.index') }}" id="searchFilterForm">
        <div class="top-bar">
            <div class="flex-grow-1 position-relative" style="min-width: 220px;">
                <input type="text" name="search" class="form-control ps-4" placeholder="Search by name, email, company, phone..." value="{{ request('search') }}">
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 13px;"></i>
            </div>

            <div style="min-width: 140px;">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                    <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                    <option value="unqualified" {{ request('status') == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                    <option value="nurturing" {{ request('status') == 'nurturing' ? 'selected' : '' }}>Nurturing</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <div style="min-width: 150px;">
                <select name="lead_owner_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Lead Owners</option>
                    @foreach($users as $usr)
                        <option value="{{ $usr->id }}" {{ request('lead_owner_id') == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm" id="openFilterBtn">
                <i class="fas fa-sliders-h me-1"></i> More Filters
            </button>

            @if(request()->hasAny(['search', 'status', 'priority', 'lead_owner_id', 'lead_source', 'industry', 'score_rating', 'created_from', 'created_to']))
                <a href="{{ route('leads.contacts.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-undo me-1"></i> Reset Filters
                </a>
            @endif
        </div>
    </form>

    {{-- FILTER OVERLAY & SIDEBAR --}}
    <div class="filter-overlay" id="filterOverlay"></div>
    <div class="filter-sidebar" id="filterSidebar">
        <div class="filter-header">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-filter text-primary me-2"></i>Advanced Lead Filters</h5>
            <button type="button" class="btn-close" id="closeFilter"></button>
        </div>

        <form method="GET" action="{{ route('leads.contacts.index') }}" id="sidebarFilterForm">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <div class="filter-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lead Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="unqualified" {{ request('status') == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                        <option value="nurturing" {{ request('status') == 'nurturing' ? 'selected' : '' }}>Nurturing</option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="all">All Priorities</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lead Owner</label>
                    <select name="lead_owner_id" class="form-select form-select-sm">
                        <option value="all">All Owners</option>
                        @foreach($users as $usr)
                            <option value="{{ $usr->id }}" {{ request('lead_owner_id') == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lead Score Category</label>
                    <select name="score_rating" class="form-select form-select-sm">
                        <option value="">All Ratings</option>
                        <option value="very_hot" {{ request('score_rating') == 'very_hot' ? 'selected' : '' }}>Very Hot (81 - 100)</option>
                        <option value="hot" {{ request('score_rating') == 'hot' ? 'selected' : '' }}>Hot (61 - 80)</option>
                        <option value="warm" {{ request('score_rating') == 'warm' ? 'selected' : '' }}>Warm (31 - 60)</option>
                        <option value="cold" {{ request('score_rating') == 'cold' ? 'selected' : '' }}>Cold (0 - 30)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lead Source</label>
                    <select name="lead_source" class="form-select form-select-sm">
                        <option value="all">All Sources</option>
                        @foreach($sources as $src)
                            <option value="{{ $src }}" {{ request('lead_source') == $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Industry</label>
                    <select name="industry" class="form-select form-select-sm">
                        <option value="all">All Industries</option>
                        @foreach($industries as $ind)
                            <option value="{{ $ind }}" {{ request('industry') == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Created Date Range</label>
                    <div class="d-flex gap-2">
                        <input type="date" name="created_from" class="form-control form-control-sm" value="{{ request('created_from') }}" placeholder="From">
                        <input type="date" name="created_to" class="form-control form-control-sm" value="{{ request('created_to') }}" placeholder="To">
                    </div>
                </div>

            </div>
            <div class="p-3 border-top d-flex gap-2 bg-light">
                <a href="{{ route('leads.contacts.index') }}" class="btn btn-secondary btn-sm flex-fill">Clear All</a>
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Apply Filters</button>
            </div>
        </form>
    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table premium-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4 text-center" style="width: 45px; min-width: 45px;">
                            <input type="checkbox" class="form-check-input" id="selectAllLeads">
                        </th>
                        <th style="min-width: 250px;">CONTACT INFO</th>
                        <th style="min-width: 170px;">COMPANY & INDUSTRY</th>
                        <th style="min-width: 140px;">LEAD SOURCE</th>
                        <th class="text-center" style="min-width: 150px;">LEAD SCORE</th>
                        <th class="text-center" style="min-width: 130px;">PRIORITY</th>
                        <th class="text-center" style="min-width: 130px;">STATUS</th>
                        <th style="min-width: 140px;">LEAD OWNER</th>
                        <th class="text-end pe-4" style="min-width: 90px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td class="ps-4 text-center">
                                <input type="checkbox" class="form-check-input lead-checkbox" value="{{ $lead->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-badge-circle">
                                        {{ strtoupper(substr($lead->contact_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('leads.contacts.show', $lead->id) }}" class="fw-bold text-decoration-none d-block" style="color: #0f172a; font-size: 0.9rem;">
                                            {{ $lead->salutation ? $lead->salutation . ' ' : '' }}{{ $lead->contact_name }}
                                        </a>
                                        @if($lead->job_title)
                                            <div class="fw-semibold text-muted" style="font-size: 0.78rem;">{{ $lead->job_title }}</div>
                                        @endif
                                        <div class="mt-1" style="font-size: 0.79rem; color: #475569;">
                                            <div><i class="far fa-envelope text-muted me-1"></i>{{ $lead->email }}</div>
                                            @if($lead->mobile || $lead->phone)
                                                <div class="mt-1"><i class="fas fa-phone-alt text-muted me-1"></i>{{ $lead->mobile ?? $lead->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold" style="color: #1e293b; font-size: 0.88rem;">{{ $lead->company_name ?: 'N/A' }}</div>
                                @if($lead->industry)
                                    <div class="text-muted small mt-1"><i class="far fa-building me-1"></i>{{ $lead->industry }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge-pill-source">
                                    <i class="fas fa-globe me-1 text-primary" style="font-size: 11px;"></i>{{ ucfirst($lead->lead_source) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $score = $lead->lead_score ?? 0;
                                    $scoreClass = match(true) {
                                        $score >= 81 => 'badge-score-very-hot',
                                        $score >= 61 => 'badge-score-hot',
                                        $score >= 31 => 'badge-score-warm',
                                        default => 'badge-score-cold',
                                    };
                                @endphp
                                <span class="{{ $scoreClass }}" title="Score: {{ $score }}">
                                    {{ $lead->lead_score_category }} ({{ $score }})
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $prio = strtolower($lead->priority ?? 'medium');
                                    $prioClass = match($prio) {
                                        'urgent' => 'badge-pill-priority-urgent',
                                        'high' => 'badge-pill-priority-high',
                                        'low' => 'badge-pill-priority-low',
                                        default => 'badge-pill-priority-medium',
                                    };
                                @endphp
                                <span class="{{ $prioClass }} text-capitalize">
                                    {{ $prio }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $st = strtolower($lead->status ?? 'new');
                                    $statusClass = match($st) {
                                        'qualified' => 'badge-pill-status-qualified',
                                        'new' => 'badge-pill-status-new',
                                        'contacted' => 'badge-pill-status-contacted',
                                        'converted' => 'badge-pill-status-converted',
                                        'lost' => 'badge-pill-status-lost',
                                        default => 'badge-pill-status-new',
                                    };
                                @endphp
                                <span class="{{ $statusClass }} text-capitalize">
                                    {{ $st }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="font-size: 0.84rem; font-weight: 600; color: #334155;">
                                    <i class="fas fa-user-circle text-secondary fs-6"></i>
                                    <span>{{ $lead->owner->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn-action-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-premium shadow-sm">
                                        <li><a class="dropdown-item" href="{{ route('leads.contacts.show', $lead->id) }}"><i class="fas fa-eye text-primary me-2"></i>View Details</a></li>
                                        <li><a class="dropdown-item" href="{{ route('leads.contacts.edit', $lead->id) }}"><i class="fas fa-edit text-warning me-2"></i>Edit Lead</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.deals.create', ['lead_id' => $lead->id]) }}"><i class="fas fa-handshake text-success me-2"></i>Create Deal</a></li>
                                        @if($lead->type !== 'client' && $lead->status !== 'converted')
                                            <li><a class="dropdown-item btn-convert-client" href="javascript:void(0)" data-id="{{ $lead->id }}" data-name="{{ $lead->contact_name }}"><i class="fas fa-user-check text-info me-2"></i>Convert to Client</a></li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('leads.contacts.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3 text-muted opacity-50"></i>
                                <h5 class="fw-bold">No Lead Contacts Found</h5>
                                <p class="small mb-3">No lead contacts have been added yet or match your current filter options.</p>
                                <a href="{{ route('leads.contacts.create') }}" class="btn-submit-emerald d-inline-flex align-items-center text-decoration-none px-3 py-2" style="font-size: 0.85rem;"><i class="fas fa-plus me-1"></i> Add First Lead Contact</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages() || $leads->total() > 0)
            <div class="premium-table-footer d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} leads
                </div>
                <div>
                    {{ $leads->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- IMPORT MODAL --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-import me-2 text-primary"></i>Import Leads</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('leads.contacts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Excel/CSV File</label>
                        <input type="file" name="file" class="form-control" accept=".csv, .xlsx, .xls" required>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i> Download sample template:
                        <a href="{{ route('leads.contacts.template') }}" class="fw-bold text-decoration-underline">leads-template.xlsx</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSidebar = document.getElementById('filterSidebar');
    const filterOverlay = document.getElementById('filterOverlay');
    const openFilterBtn = document.getElementById('openFilterBtn');
    const closeFilterBtn = document.getElementById('closeFilter');

    if (openFilterBtn) {
        openFilterBtn.addEventListener('click', function() {
            filterSidebar.classList.add('active');
            filterOverlay.classList.add('active');
        });
    }

    function closeSidebar() {
        if (filterSidebar) filterSidebar.classList.remove('active');
        if (filterOverlay) filterOverlay.classList.remove('active');
    }

    if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeSidebar);
    if (filterOverlay) filterOverlay.addEventListener('click', closeSidebar);

    // Convert to client handler
    document.querySelectorAll('.btn-convert-client').forEach(btn => {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.id;
            const leadName = this.dataset.name;
            if (confirm(`Convert lead "${leadName}" to Client? This will create a permanent client record.`)) {
                fetch('{{ route("leads.contacts.convert") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lead_id: leadId })
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
                .catch(err => alert('Failed to convert lead: ' + err));
            }
        });
    });
});
</script>

@endsection
