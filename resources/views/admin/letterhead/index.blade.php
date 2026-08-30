@extends('admin.layout.app')

@section('title', 'Letter Head Management')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap');

    .lh-mgmt-shell {
        --lh-primary: #0f744c;
        --lh-primary-hover: #0a5c3a;
        --lh-emerald: #10b981;
        --lh-emerald-soft: #ecfdf5;
        --lh-emerald-border: rgba(16, 185, 129, 0.25);
        --lh-slate-900: #0f172a;
        --lh-slate-700: #334155;
        --lh-slate-500: #64748b;
        --lh-slate-200: #e2e8f0;
        --lh-slate-100: #f8fafc;
        --lh-card-bg: #ffffff;
        --lh-card-border: rgba(226, 232, 240, 0.85);
        --lh-shadow: 0 10px 30px -5px rgba(15, 116, 76, 0.06), 0 4px 12px rgba(0, 0, 0, 0.03);
        --lh-shadow-hover: 0 20px 40px -10px rgba(15, 116, 76, 0.12), 0 6px 16px rgba(0, 0, 0, 0.05);

        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        padding-bottom: 3rem;
    }

    /* Ambient Fade In */
    .lh-fade-in {
        animation: lhFadeIn 0.35s ease-out both;
    }
    @keyframes lhFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== BREADCRUMB ===== */
    .lh-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--lh-slate-500);
        margin-bottom: 1.25rem;
    }
    .lh-breadcrumb a {
        color: var(--lh-primary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: color 0.2s ease;
    }
    .lh-breadcrumb a:hover {
        color: var(--lh-primary-hover);
    }
    .lh-breadcrumb .separator {
        color: #94a3b8;
        font-size: 0.95rem;
    }
    .lh-breadcrumb .current {
        color: var(--lh-slate-900);
        font-weight: 700;
    }

    /* ===== ELEVATED HEADER CARD ===== */
    .lh-header-banner {
        background: #ffffff;
        border-radius: 20px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--lh-card-border);
        box-shadow: var(--lh-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .lh-header-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #0f744c 50%, #047857 100%);
    }
    .lh-banner-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }
    .lh-banner-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: linear-gradient(135deg, #10b981 0%, #0f744c 100%);
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        box-shadow: 0 8px 18px -4px rgba(16, 185, 129, 0.4);
        flex-shrink: 0;
    }
    .lh-banner-icon i {
        color: #ffffff !important;
    }
    .lh-banner-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--lh-slate-900);
        margin: 0 0 0.2rem;
        letter-spacing: -0.025em;
        line-height: 1.25;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .lh-live-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.2rem 0.65rem;
        background: var(--lh-emerald-soft);
        color: #065f46;
        border: 1px solid var(--lh-emerald-border);
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .lh-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: lhPulse 2s infinite;
    }
    @keyframes lhPulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .lh-banner-subtitle {
        color: var(--lh-slate-500);
        font-size: 0.9rem;
        margin: 0;
        font-weight: 500;
    }

    /* Force pure white text on emerald primary buttons */
    .btn-lh-emerald,
    .btn-lh-emerald *,
    .btn-lh-emerald i,
    .btn-lh-emerald span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        fill: #ffffff !important;
    }
    .btn-lh-emerald {
        background: linear-gradient(135deg, #10b981 0%, #0f744c 100%) !important;
        border: none !important;
        padding: 0.65rem 1.4rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
        transition: all 0.25s ease;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-lh-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(16, 185, 129, 0.45);
    }

    /* ===== SUMMARY STATS CARDS ===== */
    .lh-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }
    .lh-metric-box {
        background: #ffffff;
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--lh-card-border);
        box-shadow: var(--lh-shadow);
        display: flex;
        align-items: center;
        gap: 1.2rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .lh-metric-box:hover {
        transform: translateY(-3px);
        box-shadow: var(--lh-shadow-hover);
    }
    .lh-metric-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .lh-metric-icon.total { background: #e0f2fe; color: #0284c7; }
    .lh-metric-icon.active { background: #dcfce7; color: #16a34a; }
    .lh-metric-icon.default { background: #fef3c7; color: #d97706; }
    .lh-metric-icon.draft { background: #f1f5f9; color: #475569; }
    .lh-metric-info h4 {
        margin: 0;
        font-weight: 900;
        color: var(--lh-slate-900);
        font-size: 1.6rem;
        line-height: 1.1;
    }
    .lh-metric-info span {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--lh-slate-500);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* ===== FILTER & TOOLBAR CARD ===== */
    .lh-filter-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--lh-card-border);
        box-shadow: var(--lh-shadow);
    }
    .lh-search-input {
        border-radius: 999px;
        padding: 0.55rem 1.1rem 0.55rem 2.4rem;
        border: 1px solid var(--lh-slate-200);
        font-size: 0.88rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .lh-search-input:focus {
        border-color: var(--lh-emerald);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .lh-select {
        border-radius: 999px;
        padding: 0.55rem 1.1rem;
        border: 1px solid var(--lh-slate-200);
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--lh-slate-700);
    }
    .lh-select:focus {
        border-color: var(--lh-emerald);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    /* ===== TABLE CARD ===== */
    .lh-table-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid var(--lh-card-border);
        box-shadow: var(--lh-shadow);
        overflow: hidden;
    }
    .lh-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .lh-table thead th {
        background: #f8fafc;
        color: var(--lh-slate-700);
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1.1rem 1.25rem;
        border-bottom: 1.5px solid var(--lh-slate-200);
        white-space: nowrap;
    }
    .lh-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid #edf2f7;
    }
    .lh-table tbody tr:hover {
        background: rgba(16, 185, 129, 0.03);
    }
    .lh-table tbody td {
        padding: 1.1rem 1.25rem;
        vertical-align: middle;
        font-size: 0.9rem;
        color: var(--lh-slate-700);
    }

    /* Cell Elements */
    .lh-name-cell {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .lh-preview-thumb {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid var(--lh-slate-200);
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .lh-avatar-fallback {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #10b981 0%, #0f744c 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
    }
    .lh-title-text {
        font-weight: 800;
        color: var(--lh-slate-900);
        font-size: 0.95rem;
        margin-bottom: 2px;
    }
    .lh-code-pill {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--lh-slate-500);
        background: #f1f5f9;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
    }

    /* Badges */
    .lh-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: capitalize;
    }
    .lh-type-badge.company { background: #dcfce7; color: #166534; border: 1px solid rgba(22, 101, 52, 0.2); }
    .lh-type-badge.branch { background: #dbeafe; color: #1e40af; border: 1px solid rgba(30, 64, 175, 0.2); }
    .lh-type-badge.department { background: #ede9fe; color: #6b21a8; border: 1px solid rgba(107, 33, 168, 0.2); }
    .lh-type-badge.project { background: #fef3c7; color: #92400e; border: 1px solid rgba(146, 64, 14, 0.2); }
    .lh-type-badge.custom { background: #f1f5f9; color: #334155; border: 1px solid rgba(51, 65, 85, 0.2); }

    .lh-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: capitalize;
    }
    .lh-status-pill.active { background: rgba(16, 185, 129, 0.12); color: #047857; border: 1px solid rgba(16, 185, 129, 0.25); }
    .lh-status-pill.draft { background: rgba(100, 116, 139, 0.12); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); }
    .lh-status-pill.inactive { background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25); }
    .lh-status-pill.archived { background: rgba(239, 68, 68, 0.12); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.25); }

    .lh-default-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        background: #fef3c7;
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .lh-non-default {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 600;
    }

    /* Actions dropdown */
    .lh-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid var(--lh-slate-200);
        background: #ffffff;
        color: var(--lh-slate-700);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .lh-action-btn:hover {
        background: #f8fafc;
        border-color: var(--lh-emerald);
        color: var(--lh-primary);
    }
    .lh-dropdown-menu {
        border-radius: 14px;
        border: 1px solid var(--lh-slate-200);
        box-shadow: 0 16px 36px rgba(0,0,0,0.12);
        padding: 0.5rem;
        min-width: 210px;
    }
    .lh-dropdown-item {
        border-radius: 8px;
        padding: 0.5rem 0.85rem;
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--lh-slate-700);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        transition: all 0.15s ease;
    }
    .lh-dropdown-item:hover {
        background: #f1f5f9;
        color: var(--lh-slate-900);
    }
    .lh-dropdown-item i {
        font-size: 1.1rem;
    }
    .lh-dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* ===== MODAL & LIVE PREVIEW SYSTEM ===== */
    .lh-modal-dialog {
        max-width: 1250px;
    }
    .lh-form-nav {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }
    .lh-form-nav .nav-link {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--lh-slate-700);
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.6rem 1rem;
        border-radius: 0;
        transition: all 0.2s ease;
    }
    .lh-form-nav .nav-link.active {
        color: var(--lh-primary) !important;
        border-bottom-color: var(--lh-primary);
        background: transparent;
    }

    /* Real-Time Live Preview Paper Canvas */
    .lh-preview-wrapper {
        background: #cbd5e1;
        padding: 1.5rem;
        border-radius: 18px;
        overflow-y: auto;
        max-height: 720px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }
    .lh-live-paper {
        background: #ffffff;
        width: 100%;
        max-width: 480px;
        min-height: 640px;
        border-radius: 4px;
        box-shadow: 0 14px 35px rgba(0, 0, 0, 0.15);
        padding: 24px;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
    }
    .lh-live-watermark {
        position: absolute;
        top: 48%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-weight: 900;
        font-size: 28pt;
        text-transform: uppercase;
        letter-spacing: 4px;
        pointer-events: none;
        z-index: 1;
        opacity: 0.08;
        white-space: nowrap;
    }
    .lh-live-header {
        border-bottom: 2px solid #0f744c;
        padding-bottom: 10px;
        margin-bottom: 16px;
        position: relative;
        z-index: 2;
    }
    .lh-live-body {
        flex: 1;
        position: relative;
        z-index: 2;
        font-size: 8pt;
        line-height: 1.5;
        color: #475569;
    }
    .lh-live-footer {
        border-top: 1px solid #e2e8f0;
        padding-top: 8px;
        font-size: 7pt;
        color: #64748b;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    /* Color picker swatches */
    .color-swatch-picker {
        width: 38px;
        height: 38px;
        padding: 0;
        border: 1px solid var(--lh-slate-200);
        border-radius: 10px;
        cursor: pointer;
    }

    /* Empty state */
    .lh-empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    .lh-empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 24px;
        background: #f1f5f9;
        color: var(--lh-slate-500);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 1.25rem;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y lh-mgmt-shell lh-fade-in">

    <!-- BREADCRUMB -->
    <div class="lh-breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="bx bx-home-alt me-1"></i> Dashboard</a>
        <span class="separator">/</span>
        <span>HR</span>
        <span class="separator">/</span>
        <span class="current">Letter Head Management</span>
    </div>

    <!-- ELEVATED HEADER BANNER -->
    <div class="lh-header-banner">
        <div class="lh-banner-left">
            <div class="lh-banner-icon">
                <i class="bx bx-file"></i>
            </div>
            <div>
                <div class="lh-banner-title">
                    Letter Head Management
                    <span class="lh-live-pill">
                        <span class="lh-pulse-dot"></span>
                        Enterprise Module
                    </span>
                </div>
                <p class="lh-banner-subtitle">
                    Create, manage and customize professional letterheads for company, branch, department and project documents.
                </p>
            </div>
        </div>
        <div>
            <a href="{{ route('letterhead.create') }}" class="btn-lh-emerald">
                <i class="bx bx-paper-plane fs-5"></i>
                <span>+ Write & Send Letter</span>
            </a>
        </div>
    </div>

    <!-- DASHBOARD SUMMARY METRIC CARDS -->
    <div class="lh-metrics-grid">
        <div class="lh-metric-box">
            <div class="lh-metric-icon total">
                <i class="bx bx-layer"></i>
            </div>
            <div class="lh-metric-info">
                <h4>{{ $stats['total'] }}</h4>
                <span>Total Letter Heads</span>
            </div>
        </div>

        <div class="lh-metric-box">
            <div class="lh-metric-icon active">
                <i class="bx bx-check-circle"></i>
            </div>
            <div class="lh-metric-info">
                <h4>{{ $stats['active'] }}</h4>
                <span>Active</span>
            </div>
        </div>

        <div class="lh-metric-box">
            <div class="lh-metric-icon default">
                <i class="bx bxs-star"></i>
            </div>
            <div class="lh-metric-info">
                <h4>{{ $stats['default'] }}</h4>
                <span>Default</span>
            </div>
        </div>

        <div class="lh-metric-box">
            <div class="lh-metric-icon draft">
                <i class="bx bx-edit"></i>
            </div>
            <div class="lh-metric-info">
                <h4>{{ $stats['draft'] }}</h4>
                <span>Draft / Inactive</span>
            </div>
        </div>
    </div>

    <!-- SEARCH & ADVANCED FILTER TOOLBAR -->
    <div class="lh-filter-card">
        <form method="GET" action="{{ route('letterhead.index') }}" id="lhFilterForm">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4 col-md-6 position-relative">
                    <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5"></i>
                    <input type="text" name="search" id="lhSearchInput" class="form-control lh-search-input" 
                           placeholder="Search letter heads by name, code, company..." 
                           value="{{ request('search') }}">
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <select name="type" class="form-select lh-select" onchange="document.getElementById('lhFilterForm').submit()">
                        <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>All Types</option>
                        <option value="company" {{ request('type') == 'company' ? 'selected' : '' }}>Company</option>
                        <option value="branch" {{ request('type') == 'branch' ? 'selected' : '' }}>Branch</option>
                        <option value="department" {{ request('type') == 'department' ? 'selected' : '' }}>Department</option>
                        <option value="project" {{ request('type') == 'project' ? 'selected' : '' }}>Project</option>
                        <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <select name="status" class="form-select lh-select" onchange="document.getElementById('lhFilterForm').submit()">
                        <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <select name="is_default" class="form-select lh-select" onchange="document.getElementById('lhFilterForm').submit()">
                        <option value="all" {{ request('is_default') === 'all' || request('is_default') === null ? 'selected' : '' }}>All Defaults</option>
                        <option value="1" {{ request('is_default') === '1' ? 'selected' : '' }}>Default Only</option>
                        <option value="0" {{ request('is_default') === '0' ? 'selected' : '' }}>Non-Default</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-6 text-end">
                    @if(request()->hasAny(['search', 'type', 'status', 'is_default']))
                        <a href="{{ route('letterhead.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-bold">
                            <i class="bx bx-reset"></i> Reset Filters
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- RESPONSIVE LETTERHEAD DATA TABLE -->
    <div class="lh-table-card">
        <div class="table-responsive text-nowrap">
            <table class="lh-table">
                <thead>
                    <tr>
                        <th>LETTER HEAD</th>
                        <th>TYPE</th>
                        <th>ORGANIZATION</th>
                        <th>STATUS</th>
                        <th>DEFAULT</th>
                        <th>UPDATED BY</th>
                        <th>UPDATED AT</th>
                        <th class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($letterheads as $item)
                        <tr>
                            <td>
                                <div class="lh-name-cell">
                                    @if($item->logo)
                                        <img src="{{ asset($item->logo) }}" alt="{{ $item->name }}" class="lh-preview-thumb">
                                    @else
                                        <div class="lh-avatar-fallback">
                                            {{ strtoupper(substr($item->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="lh-title-text">{{ $item->name }}</div>
                                        <span class="lh-code-pill">{{ $item->code ?: 'LH-' . $item->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="lh-type-badge {{ $item->type }}">
                                    @if($item->type === 'company') <i class="bx bx-buildings"></i>
                                    @elseif($item->type === 'branch') <i class="bx bx-map-pin"></i>
                                    @elseif($item->type === 'department') <i class="bx bx-group"></i>
                                    @elseif($item->type === 'project') <i class="bx bx-briefcase"></i>
                                    @else <i class="bx bx-customize"></i>
                                    @endif
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->organization_display_name }}</div>
                                <small class="text-muted">{{ $item->email ?: ($item->phone ?: 'Corporate') }}</small>
                            </td>
                            <td>
                                <span class="lh-status-pill {{ $item->status }}">
                                    <i class="bx bxs-circle" style="font-size: 0.45rem;"></i>
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                @if($item->is_default)
                                    <span class="lh-default-badge">
                                        <i class="bx bxs-star text-warning"></i> Default
                                    </span>
                                @else
                                    <span class="lh-non-default">No</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs">
                                        <span class="avatar-initial rounded-circle bg-label-success fw-bold" style="font-size: 0.7rem;">
                                            {{ strtoupper(substr($item->updater?->name ?: ($item->creator?->name ?: 'Admin'), 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $item->updater?->name ?: ($item->creator?->name ?: 'Admin') }}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">v{{ $item->version ?: 1 }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; font-weight: 600; color: #334155;">{{ $item->updated_at->format('M d, Y') }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $item->updated_at->format('h:i A') }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="lh-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end lh-dropdown-menu">
                                        <li>
                                            <a class="dropdown-item lh-dropdown-item" href="javascript:void(0);" onclick="openViewModal({{ $item->id }})">
                                                <i class="bx bx-show text-primary"></i> View Details & Preview
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item lh-dropdown-item" href="javascript:void(0);" onclick="openEditModal({{ $item->id }})">
                                                <i class="bx bx-edit-alt text-success"></i> Edit Configuration
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item lh-dropdown-item" href="{{ route('letterhead.pdf', $item->id) }}" target="_blank">
                                                <i class="bx bxs-file-pdf text-danger"></i> Generate Sample PDF
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item lh-dropdown-item" href="{{ route('letterhead.print', $item->id) }}" target="_blank">
                                                <i class="bx bx-printer text-info"></i> Print Preview
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        @if(!$item->is_default)
                                            <li>
                                                <form action="{{ route('letterhead.default', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item lh-dropdown-item">
                                                        <i class="bx bx-star text-warning"></i> Set as Default
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('letterhead.duplicate', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item lh-dropdown-item">
                                                    <i class="bx bx-copy text-secondary"></i> Duplicate
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('letterhead.toggle-status', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item lh-dropdown-item">
                                                    <i class="bx bx-power-off {{ $item->status === 'active' ? 'text-warning' : 'text-success' }}"></i>
                                                    {{ $item->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('letterhead.archive', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item lh-dropdown-item">
                                                    <i class="bx bx-archive text-muted"></i>
                                                    {{ $item->status === 'archived' ? 'Restore' : 'Archive' }}
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item lh-dropdown-item text-danger" onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->is_default ? 'true' : 'false' }})">
                                                <i class="bx bx-trash"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="lh-empty-state">
                                    <div class="lh-empty-icon">
                                        <i class="bx bx-file-blank"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-1">No Letter Heads Yet</h4>
                                    <p class="text-muted mb-3" style="max-width: 460px; margin: 0 auto;">
                                        Create your first professional letterhead template to use across company, branch, department, and project documents.
                                    </p>
                                    <button type="button" class="btn-lh-emerald" onclick="openCreateModal()">
                                        <i class="bx bx-plus-circle fs-5"></i>
                                        <span>+ Create Letter Head</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($letterheads->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted fw-bold">
                    Showing {{ $letterheads->firstItem() }} to {{ $letterheads->lastItem() }} of {{ $letterheads->total() }} letterheads
                </small>
                <div>
                    {{ $letterheads->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- CREATE / EDIT MODAL WITH INTERACTIVE REAL-TIME LIVE A4 PREVIEW CANVAS     -->
<!-- ========================================================================= -->
<div class="modal fade" id="lhFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl lh-modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: #ffffff;">
            <div class="modal-header border-bottom px-4 pt-4 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="lh-banner-icon" style="width: 44px; height: 44px; font-size: 1.4rem;">
                        <i class="bx bx-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="lhModalTitle">Create Letter Head</h5>
                        <small class="text-muted">Configure document typography, logo, layout, margins, and branding</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="lhMainForm" method="POST" action="{{ route('letterhead.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="lhFormMethod" value="POST">
                <input type="hidden" name="action_type" id="lhActionType" value="activate">
                <input type="hidden" name="remove_logo" id="lhRemoveLogo" value="0">
                <input type="hidden" name="remove_watermark_image" id="lhRemoveWatermark" value="0">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- LEFT COLUMN: FORM TABS -->
                        <div class="col-lg-7">
                            <ul class="nav nav-tabs lh-form-nav" id="lhFormTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" id="tab-basic" data-bs-toggle="tab" data-bs-target="#pane-basic" type="button">1. Basic Info</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-org" data-bs-toggle="tab" data-bs-target="#pane-org" type="button">2. Organization</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-header" data-bs-toggle="tab" data-bs-target="#pane-header" type="button">3. Header Setup</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-footer" data-bs-toggle="tab" data-bs-target="#pane-footer" type="button">4. Footer Setup</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-page" data-bs-toggle="tab" data-bs-target="#pane-page" type="button">5. Page & Watermark</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-brand" data-bs-toggle="tab" data-bs-target="#pane-brand" type="button">6. Branding</button>
                                </li>
                            </ul>

                            <div class="tab-content pt-2" id="lhTabContent">
                                <!-- TAB 1: BASIC INFO -->
                                <div class="tab-pane fade show active" id="pane-basic">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold text-dark">Letter Head Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="f_name" class="form-control rounded-3" required 
                                                   placeholder="e.g. Corporate Standard Letterhead" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Letter Head Code</label>
                                            <input type="text" name="code" id="f_code" class="form-control rounded-3" 
                                                   placeholder="e.g. LH-2026-001" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Letter Head Type <span class="text-danger">*</span></label>
                                            <select name="type" id="f_type" class="form-select rounded-3" onchange="handleTypeChange(); syncLivePreview();">
                                                <option value="company">Company</option>
                                                <option value="branch">Branch</option>
                                                <option value="department">Department</option>
                                                <option value="project">Project</option>
                                                <option value="custom">Custom</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Status</label>
                                            <select name="status" id="f_status" class="form-select rounded-3">
                                                <option value="active">Active</option>
                                                <option value="draft">Draft</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>

                                        <!-- Contextual Selectors -->
                                        <div class="col-12" id="context_company_wrap">
                                            <label class="form-label fw-bold text-dark">Select Company</label>
                                            <select name="company_id" id="f_company_id" class="form-select rounded-3" onchange="handleCompanySelect(); syncLivePreview();">
                                                <option value="">-- Choose Company --</option>
                                                @foreach($companies as $c)
                                                    <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-email="{{ $c->email }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}" data-website="{{ $c->website }}">{{ $c->name }} ({{ $c->company_code ?: 'HQ' }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 d-none" id="context_branch_wrap">
                                            <label class="form-label fw-bold text-dark">Select Branch Location</label>
                                            <select name="branch_id" id="f_branch_id" class="form-select rounded-3" onchange="handleBranchSelect(); syncLivePreview();">
                                                <option value="">-- Choose Branch --</option>
                                                @foreach($branches as $b)
                                                    <option value="{{ $b->id }}" data-name="{{ $b->branch_name ?: $b->location }}" data-address="{{ $b->address }}" data-phone="{{ $b->phone }}" data-email="{{ $b->email }}">{{ $b->branch_name ?: $b->location }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 d-none" id="context_department_wrap">
                                            <label class="form-label fw-bold text-dark">Select Department</label>
                                            <select name="department_id" id="f_department_id" class="form-select rounded-3" onchange="handleDepartmentSelect(); syncLivePreview();">
                                                <option value="">-- Choose Department --</option>
                                                @foreach($departments as $d)
                                                    <option value="{{ $d->id }}" data-name="{{ $d->dpt_name }}">{{ $d->dpt_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 d-none" id="context_project_wrap">
                                            <label class="form-label fw-bold text-dark">Select Project</label>
                                            <select name="project_id" id="f_project_id" class="form-select rounded-3" onchange="handleProjectSelect(); syncLivePreview();">
                                                <option value="">-- Choose Project --</option>
                                                @foreach($projects as $p)
                                                    <option value="{{ $p->id }}" data-name="{{ $p->name }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_default" id="f_is_default" value="1">
                                                <label class="form-check-label fw-bold text-dark" for="f_is_default">
                                                    Set as Default Letterhead for this scope
                                                </label>
                                                <small class="d-block text-muted">Automatically replaces any current default letterhead for the same type.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: ORGANIZATION & IDENTIFICATION -->
                                <div class="tab-pane fade" id="pane-org">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Organization / Company Name</label>
                                            <input type="text" name="company_name" id="f_company_name" class="form-control rounded-3" 
                                                   placeholder="e.g. Bitroxia Enterprise Ltd." oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Tagline / Slogan</label>
                                            <input type="text" name="tagline" id="f_tagline" class="form-control rounded-3" 
                                                   placeholder="e.g. Next-Gen Workforce Solutions" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Address Line 1</label>
                                            <input type="text" name="address_line_1" id="f_address_line_1" class="form-control rounded-3" 
                                                   placeholder="e.g. Silicon Tower 4, Suite 800" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Address Line 2</label>
                                            <input type="text" name="address_line_2" id="f_address_line_2" class="form-control rounded-3" 
                                                   placeholder="e.g. Technology Corridor" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">City</label>
                                            <input type="text" name="city" id="f_city" class="form-control rounded-3" 
                                                   placeholder="e.g. San Francisco" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">State / Province</label>
                                            <input type="text" name="state" id="f_state" class="form-control rounded-3" 
                                                   placeholder="e.g. California" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Postal / PIN Code</label>
                                            <input type="text" name="postal_code" id="f_postal_code" class="form-control rounded-3" 
                                                   placeholder="e.g. 94107" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Primary Phone</label>
                                            <input type="text" name="phone" id="f_phone" class="form-control rounded-3" 
                                                   placeholder="e.g. +1 (800) 458-9200" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Official Email</label>
                                            <input type="email" name="email" id="f_email" class="form-control rounded-3" 
                                                   placeholder="e.g. contact@bitroxia.com" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Website URL</label>
                                            <input type="text" name="website" id="f_website" class="form-control rounded-3" 
                                                   placeholder="e.g. https://www.bitroxia.com" oninput="syncLivePreview()">
                                        </div>

                                        <!-- Corporate Identification Numbers -->
                                        <div class="col-12"><hr class="my-2"></div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Tax ID / PAN / TIN</label>
                                            <input type="text" name="tax_number" id="f_tax_number" class="form-control rounded-3" 
                                                   placeholder="e.g. EIN-84-9923841" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">GST / VAT Number</label>
                                            <input type="text" name="gst_number" id="f_gst_number" class="form-control rounded-3" 
                                                   placeholder="e.g. GST-9948210A" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Registration / Incorporation No</label>
                                            <input type="text" name="registration_number" id="f_registration_number" class="form-control rounded-3" 
                                                   placeholder="e.g. REG-2026-9948">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">CIN / Company ID</label>
                                            <input type="text" name="cin_number" id="f_cin_number" class="form-control rounded-3" 
                                                   placeholder="e.g. CIN-L72200CA2026">
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: HEADER CONFIGURATION -->
                                <div class="tab-pane fade" id="pane-header">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark">Header Logo</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <div id="f_logo_preview_wrap" class="border rounded-3 p-2 bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                                    <i class="bx bx-image text-muted fs-2" id="f_logo_placeholder"></i>
                                                    <img src="" id="f_logo_img" class="d-none" style="max-height: 50px; max-width: 70px; object-fit: contain;">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="file" name="logo" id="f_logo" class="form-control rounded-3" accept="image/*" onchange="handleLogoChange(event)">
                                                    <small class="text-muted">PNG, JPG, SVG or WEBP (Max 5MB)</small>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill d-none" id="f_remove_logo_btn" onclick="clearLogoInput()">
                                                    <i class="bx bx-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Logo Position</label>
                                            <select name="logo_position" id="f_logo_position" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="left">Left Aligned</option>
                                                <option value="center">Centered</option>
                                                <option value="right">Right Aligned</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Logo Height: <span id="val_logo_height">52</span>px</label>
                                            <input type="range" name="logo_height" id="f_logo_height" class="form-range" min="30" max="100" value="52" 
                                                   oninput="document.getElementById('val_logo_height').textContent = this.value; syncLivePreview();">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Header Font Family</label>
                                            <select name="header_font" id="f_header_font" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                                <option value="Inter">Inter</option>
                                                <option value="Helvetica, Arial, sans-serif">Helvetica / Arial</option>
                                                <option value="Georgia, serif">Georgia (Serif)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Header Text Alignment</label>
                                            <select name="header_alignment" id="f_header_alignment" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="left">Left</option>
                                                <option value="center">Center</option>
                                                <option value="right">Right</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Bottom Border Style</label>
                                            <select name="header_border_style" id="f_header_border_style" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="solid">Solid Line</option>
                                                <option value="double">Double Line</option>
                                                <option value="dashed">Dashed</option>
                                                <option value="none">No Border</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Border Thickness</label>
                                            <select name="header_border_thickness" id="f_header_border_thickness" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="1">1 px (Fine)</option>
                                                <option value="2" selected>2 px (Medium)</option>
                                                <option value="3">3 px (Bold)</option>
                                                <option value="4">4 px (Thick)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Border Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="header_border_color" id="f_header_border_color" class="color-swatch-picker" value="#0f744c" oninput="syncLivePreview()">
                                                <input type="text" id="f_header_border_color_text" class="form-control form-control-sm rounded-3" value="#0f744c" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: FOOTER CONFIGURATION -->
                                <div class="tab-pane fade" id="pane-footer">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark">Footer Main Content / Contact Summary</label>
                                            <input type="text" name="footer_content" id="f_footer_content" class="form-control rounded-3" 
                                                   placeholder="e.g. Official Correspondence • Confidential Document" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark">Footer Disclaimer / Legal Notice</label>
                                            <textarea name="footer_text" id="f_footer_text" class="form-control rounded-3" rows="2" 
                                                      placeholder="e.g. This official document is issued by Bitroxia Enterprise Systems. Any unauthorized reproduction is prohibited." oninput="syncLivePreview()"></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Footer Alignment</label>
                                            <select name="footer_alignment" id="f_footer_alignment" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="center">Center</option>
                                                <option value="left">Left</option>
                                                <option value="right">Right</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Top Border Style</label>
                                            <select name="footer_border_style" id="f_footer_border_style" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="solid">Solid Line</option>
                                                <option value="dashed">Dashed</option>
                                                <option value="none">No Border</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Border Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="footer_border_color" id="f_footer_border_color" class="color-swatch-picker" value="#e2e8f0" oninput="syncLivePreview()">
                                                <input type="text" id="f_footer_border_color_text" class="form-control form-control-sm rounded-3" value="#e2e8f0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 5: PAGE SETTINGS & WATERMARK -->
                                <div class="tab-pane fade" id="pane-page">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Paper Size</label>
                                            <select name="paper_size" id="f_paper_size" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="a4">A4 (210 x 297 mm)</option>
                                                <option value="letter">US Letter (8.5 x 11 in)</option>
                                                <option value="legal">US Legal (8.5 x 14 in)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Orientation</label>
                                            <select name="orientation" id="f_orientation" class="form-select rounded-3" onchange="syncLivePreview()">
                                                <option value="portrait">Portrait</option>
                                                <option value="landscape">Landscape</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label fw-bold text-dark">Top Margin (mm)</label>
                                            <input type="number" name="margin_top" id="f_margin_top" class="form-control rounded-3" value="20" min="5" max="50" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label fw-bold text-dark">Bottom Margin (mm)</label>
                                            <input type="number" name="margin_bottom" id="f_margin_bottom" class="form-control rounded-3" value="20" min="5" max="50" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label fw-bold text-dark">Left Margin (mm)</label>
                                            <input type="number" name="margin_left" id="f_margin_left" class="form-control rounded-3" value="20" min="5" max="50" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <label class="form-label fw-bold text-dark">Right Margin (mm)</label>
                                            <input type="number" name="margin_right" id="f_margin_right" class="form-control rounded-3" value="20" min="5" max="50" oninput="syncLivePreview()">
                                        </div>

                                        <!-- Watermark controls -->
                                        <div class="col-12"><hr class="my-2"></div>
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="watermark_enabled" id="f_watermark_enabled" value="1" onchange="syncLivePreview()">
                                                <label class="form-check-label fw-bold text-dark" for="f_watermark_enabled">Enable Document Watermark</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Watermark Text</label>
                                            <input type="text" name="watermark_text" id="f_watermark_text" class="form-control rounded-3" 
                                                   placeholder="e.g. CONFIDENTIAL / OFFICIAL / DRAFT" value="OFFICIAL" oninput="syncLivePreview()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Watermark Opacity: <span id="val_wm_opacity">8</span>%</label>
                                            <input type="range" name="watermark_opacity" id="f_watermark_opacity" class="form-range" min="0.02" max="0.30" step="0.01" value="0.08" 
                                                   oninput="document.getElementById('val_wm_opacity').textContent = Math.round(this.value * 100); syncLivePreview();">
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 6: BRANDING COLORS -->
                                <div class="tab-pane fade" id="pane-brand">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Primary Brand Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" name="primary_color" id="f_primary_color" class="color-swatch-picker" value="#0f744c" oninput="syncLivePreview()">
                                                <input type="text" id="f_primary_color_text" class="form-control rounded-3" value="#0f744c" readonly>
                                            </div>
                                            <small class="text-muted">Applied to Header title, Borders & Accent titles</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Secondary Accent Color</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" name="secondary_color" id="f_secondary_color" class="color-swatch-picker" value="#10b981" oninput="syncLivePreview()">
                                                <input type="text" id="f_secondary_color_text" class="form-control rounded-3" value="#10b981" readonly>
                                            </div>
                                            <small class="text-muted">Applied to badges, tags and icons</small>
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label class="form-label fw-bold text-dark">Changelog Summary (Optional)</label>
                                            <input type="text" name="change_summary" id="f_change_summary" class="form-control rounded-3" 
                                                   placeholder="e.g. Updated corporate headquarters address and new high-res logo">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: REAL-TIME LIVE A4 PREVIEW -->
                        <div class="col-lg-5">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-extrabold text-dark" style="font-size: 0.88rem;">
                                    <i class="bx bx-tv text-success me-1"></i> LIVE LETTER HEAD PREVIEW
                                </span>
                                <span class="badge bg-label-success rounded-pill" style="font-size: 0.72rem;">Real-Time Synced</span>
                            </div>

                            <div class="lh-preview-wrapper">
                                <div class="lh-live-paper" id="livePaperSheet">
                                    <!-- LIVE WATERMARK -->
                                    <div class="lh-live-watermark d-none" id="liveWatermark">CONFIDENTIAL</div>

                                    <!-- LIVE HEADER -->
                                    <div class="lh-live-header" id="liveHeaderBox">
                                        <div class="d-flex align-items-center justify-content-between gap-2" id="liveHeaderInner">
                                            <div class="d-flex align-items-center gap-2" id="liveLogoLeftWrap">
                                                <img src="" id="liveLogoImg" class="d-none" style="max-height: 44px; max-width: 100px; object-fit: contain;">
                                                <div>
                                                    <div class="fw-extrabold text-dark" id="liveOrgName" style="font-size: 11pt; color: #0f744c; line-height: 1.2;">Bitroxia Solutions Ltd.</div>
                                                    <div class="text-muted" id="liveTagline" style="font-size: 7pt;">Enterprise Project & HR Management</div>
                                                </div>
                                            </div>
                                            <div class="text-end" id="liveContactBox" style="font-size: 6.5pt; color: #64748b; line-height: 1.3;">
                                                <div id="liveAddress">Silicon Tower 4, Level 8, CA 94107</div>
                                                <div id="liveContact">Phone: +1 800-458-9200 | contact@bitroxia.com</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- LIVE BODY (Sample Letter) -->
                                    <div class="lh-live-body">
                                        <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 7pt; font-weight: bold;">
                                            <span>Ref: REF/2026/SAMPLE</span>
                                            <span>Date: {{ now()->format('M d, Y') }}</span>
                                        </div>
                                        <div class="mb-2" style="font-size: 7.5pt; line-height: 1.3;">
                                            <strong>Dr. Eleanor Vance</strong><br>
                                            Director of Operations<br>
                                            Apex Global Systems Ltd.
                                        </div>
                                        <div class="fw-bold mb-2" id="liveSubject" style="font-size: 8pt; color: #0f744c; text-decoration: underline;">
                                            Subject: Formal Strategic Communication & Official Notification
                                        </div>
                                        <p style="margin-bottom: 6px;">
                                            We are pleased to formally present this official communication. This sample preview demonstrates the precise visual layout, borders, font typography, and margins as they will appear when generated or printed.
                                        </p>
                                        <p style="margin-bottom: 10px;">
                                            All corporate identities, prefixes, and legal registration numbers are securely integrated into our enterprise documents.
                                        </p>
                                        <div class="d-flex justify-content-between align-items-end mt-3" style="font-size: 7pt;">
                                            <div>
                                                <div style="width: 90px; border-top: 1px solid #94a3b8; margin-bottom: 2px;"></div>
                                                <strong>Authorized Signatory</strong><br>
                                                <span class="text-muted">Executive Board</span>
                                            </div>
                                            <div style="border: 1px dashed #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 6pt; color: #94a3b8;">
                                                [ OFFICIAL SEAL ]
                                            </div>
                                        </div>
                                    </div>

                                    <!-- LIVE FOOTER -->
                                    <div class="lh-live-footer" id="liveFooterBox">
                                        <div id="liveFooterContent">Corporate Office • Silicon Boulevard • contact@bitroxia.com</div>
                                        <div class="text-muted" id="liveFooterText" style="font-size: 6pt; margin-top: 2px;">
                                            Official communication issued by Bitroxia Enterprise Systems.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success rounded-pill px-4 fw-bold" onclick="submitMainForm('draft')">
                            <i class="bx bx-save me-1"></i> Save as Draft
                        </button>
                        <button type="button" class="btn-lh-emerald" onclick="submitMainForm('activate')">
                            <i class="bx bx-check-circle fs-5"></i>
                            <span>Save & Activate</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- VIEW / DETAILS MODAL                                                      -->
<!-- ========================================================================= -->
<div class="modal fade" id="lhViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: #ffffff;">
            <div class="modal-header border-bottom px-4 pt-4 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="lh-banner-icon" style="width: 44px; height: 44px; font-size: 1.4rem;">
                        <i class="bx bx-file"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="v_title">Letter Head Details</h5>
                        <small class="text-muted" id="v_subtitle">Inspect letterhead configuration and sample document</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">CODE</small>
                        <strong class="text-dark" id="v_code">-</strong>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">TYPE</small>
                        <strong class="text-dark text-capitalize" id="v_type">-</strong>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">STATUS</small>
                        <span id="v_status_badge">-</span>
                    </div>
                    <div class="col-md-3 col-6">
                        <small class="text-muted d-block">DEFAULT</small>
                        <strong class="text-dark" id="v_default">-</strong>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <div class="row g-2" style="font-size: 0.88rem;">
                        <div class="col-md-6"><strong>Organization:</strong> <span id="v_org">-</span></div>
                        <div class="col-md-6"><strong>Phone:</strong> <span id="v_phone">-</span></div>
                        <div class="col-md-6"><strong>Email:</strong> <span id="v_email">-</span></div>
                        <div class="col-md-6"><strong>Website:</strong> <span id="v_website">-</span></div>
                        <div class="col-12"><strong>Address:</strong> <span id="v_address">-</span></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <div class="d-flex gap-2">
                        <a href="#" id="v_print_link" target="_blank" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                            <i class="bx bx-printer me-1"></i> Print Preview
                        </a>
                        <a href="#" id="v_pdf_link" target="_blank" class="btn-lh-emerald">
                            <i class="bx bxs-file-pdf fs-5"></i>
                            <span>Download Sample PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- DELETE CONFIRMATION MODAL                                                 -->
<!-- ========================================================================= -->
<div class="modal fade" id="lhDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: #ffffff;">
            <div class="modal-body p-4 text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 58px; height: 58px; background: #fee2e2; color: #dc2626;">
                    <i class="bx bx-trash fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Delete Letter Head?</h5>
                <p class="text-muted small mb-3" id="del_msg">
                    Are you sure you want to delete this letterhead? This action cannot be undone.
                </p>
                <div id="del_default_warning" class="alert alert-warning small py-2 px-3 d-none mb-3">
                    <strong>Warning:</strong> This is currently set as a default letterhead.
                </div>
                <form id="del_form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-3 fw-bold">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Global Data and Modal Handling
    let activeModalMode = 'create';
    let currentEditingId = null;

    function openCreateModal() {
        activeModalMode = 'create';
        currentEditingId = null;
        document.getElementById('lhModalTitle').textContent = 'Create Letter Head';
        document.getElementById('lhMainForm').reset();
        document.getElementById('lhMainForm').action = "{{ route('letterhead.store') }}";
        document.getElementById('lhFormMethod').value = 'POST';
        document.getElementById('f_remove_logo_btn').classList.add('d-none');
        document.getElementById('f_logo_img').classList.add('d-none');
        document.getElementById('f_logo_placeholder').classList.remove('d-none');

        // Reset live preview defaults
        document.getElementById('f_name').value = 'Corporate Official Letterhead';
        document.getElementById('f_company_name').value = 'Bitroxia Solutions Ltd.';
        document.getElementById('f_tagline').value = 'Enterprise Project & HR Management';
        document.getElementById('f_address_line_1').value = 'Silicon Tower 4, Suite 800';
        document.getElementById('f_city').value = 'San Francisco';
        document.getElementById('f_state').value = 'California';
        document.getElementById('f_postal_code').value = '94107';
        document.getElementById('f_phone').value = '+1 (800) 458-9200';
        document.getElementById('f_email').value = 'contact@bitroxia.local';
        document.getElementById('f_website').value = 'https://www.bitroxia.com';
        document.getElementById('f_footer_content').value = 'Corporate Office • Silicon Boulevard • contact@bitroxia.local';
        document.getElementById('f_footer_text').value = 'Official communication issued by Bitroxia Enterprise Systems.';

        handleTypeChange();
        syncLivePreview();

        var modal = new bootstrap.Modal(document.getElementById('lhFormModal'));
        modal.show();
    }

    function openEditModal(id) {
        activeModalMode = 'edit';
        currentEditingId = id;
        document.getElementById('lhModalTitle').textContent = 'Edit Letter Head Configuration';
        document.getElementById('lhMainForm').reset();
        document.getElementById('lhMainForm').action = "/letterhead/" + id;
        document.getElementById('lhFormMethod').value = 'PUT';

        fetch('/letterhead/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.letterhead) {
                const lh = data.letterhead;

                document.getElementById('f_name').value = lh.name || '';
                document.getElementById('f_code').value = lh.code || '';
                document.getElementById('f_type').value = lh.type || 'company';
                document.getElementById('f_status').value = lh.status || 'active';
                document.getElementById('f_company_id').value = lh.company_id || '';
                document.getElementById('f_branch_id').value = lh.branch_id || '';
                document.getElementById('f_department_id').value = lh.department_id || '';
                document.getElementById('f_project_id').value = lh.project_id || '';
                document.getElementById('f_is_default').checked = !!lh.is_default;

                document.getElementById('f_company_name').value = lh.company_name || '';
                document.getElementById('f_tagline').value = lh.tagline || '';
                document.getElementById('f_address_line_1').value = lh.address_line_1 || '';
                document.getElementById('f_address_line_2').value = lh.address_line_2 || '';
                document.getElementById('f_city').value = lh.city || '';
                document.getElementById('f_state').value = lh.state || '';
                document.getElementById('f_postal_code').value = lh.postal_code || '';
                document.getElementById('f_phone').value = lh.phone || '';
                document.getElementById('f_email').value = lh.email || '';
                document.getElementById('f_website').value = lh.website || '';
                document.getElementById('f_tax_number').value = lh.tax_number || '';
                document.getElementById('f_gst_number').value = lh.gst_number || '';
                document.getElementById('f_registration_number').value = lh.registration_number || '';
                document.getElementById('f_cin_number').value = lh.cin_number || '';

                document.getElementById('f_logo_position').value = lh.logo_position || 'left';
                document.getElementById('f_logo_height').value = lh.logo_height || 52;
                document.getElementById('val_logo_height').textContent = lh.logo_height || 52;
                document.getElementById('f_header_font').value = lh.header_font || 'Plus Jakarta Sans';
                document.getElementById('f_header_alignment').value = lh.header_alignment || 'left';
                document.getElementById('f_header_border_style').value = lh.header_border_style || 'solid';
                document.getElementById('f_header_border_thickness').value = lh.header_border_thickness || 2;
                document.getElementById('f_header_border_color').value = lh.header_border_color || '#0f744c';
                document.getElementById('f_header_border_color_text').value = lh.header_border_color || '#0f744c';

                document.getElementById('f_footer_content').value = lh.footer_content || '';
                document.getElementById('f_footer_text').value = lh.footer_text || '';
                document.getElementById('f_footer_alignment').value = lh.footer_alignment || 'center';
                document.getElementById('f_footer_border_style').value = lh.footer_border_style || 'solid';
                document.getElementById('f_footer_border_color').value = lh.footer_border_color || '#e2e8f0';
                document.getElementById('f_footer_border_color_text').value = lh.footer_border_color || '#e2e8f0';

                document.getElementById('f_paper_size').value = lh.paper_size || 'a4';
                document.getElementById('f_orientation').value = lh.orientation || 'portrait';
                document.getElementById('f_margin_top').value = lh.margin_top || 20;
                document.getElementById('f_margin_bottom').value = lh.margin_bottom || 20;
                document.getElementById('f_margin_left').value = lh.margin_left || 20;
                document.getElementById('f_margin_right').value = lh.margin_right || 20;

                document.getElementById('f_watermark_enabled').checked = !!lh.watermark_enabled;
                document.getElementById('f_watermark_text').value = lh.watermark_text || 'OFFICIAL';
                document.getElementById('f_watermark_opacity').value = lh.watermark_opacity || 0.08;
                document.getElementById('val_wm_opacity').textContent = Math.round((lh.watermark_opacity || 0.08) * 100);

                document.getElementById('f_primary_color').value = lh.primary_color || '#0f744c';
                document.getElementById('f_primary_color_text').value = lh.primary_color || '#0f744c';
                document.getElementById('f_secondary_color').value = lh.secondary_color || '#10b981';
                document.getElementById('f_secondary_color_text').value = lh.secondary_color || '#10b981';

                if (lh.logo) {
                    document.getElementById('f_logo_img').src = '/' + lh.logo;
                    document.getElementById('f_logo_img').classList.remove('d-none');
                    document.getElementById('f_logo_placeholder').classList.add('d-none');
                    document.getElementById('f_remove_logo_btn').classList.remove('d-none');
                    document.getElementById('liveLogoImg').src = '/' + lh.logo;
                    document.getElementById('liveLogoImg').classList.remove('d-none');
                } else {
                    document.getElementById('f_logo_img').classList.add('d-none');
                    document.getElementById('f_logo_placeholder').classList.remove('d-none');
                    document.getElementById('f_remove_logo_btn').classList.add('d-none');
                    document.getElementById('liveLogoImg').classList.add('d-none');
                }

                handleTypeChange();
                syncLivePreview();

                var modal = new bootstrap.Modal(document.getElementById('lhFormModal'));
                modal.show();
            }
        });
    }

    function openViewModal(id) {
        fetch('/letterhead/' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.letterhead) {
                const lh = data.letterhead;
                document.getElementById('v_title').textContent = lh.name;
                document.getElementById('v_code').textContent = lh.code || ('LH-' + lh.id);
                document.getElementById('v_type').textContent = lh.type;
                document.getElementById('v_status_badge').innerHTML = '<span class="lh-status-pill ' + lh.status + '">' + lh.status + '</span>';
                document.getElementById('v_default').textContent = lh.is_default ? 'Yes (Primary Default)' : 'No';
                document.getElementById('v_org').textContent = data.organization_display || lh.company_name;
                document.getElementById('v_phone').textContent = lh.phone || 'N/A';
                document.getElementById('v_email').textContent = lh.email || 'N/A';
                document.getElementById('v_website').textContent = lh.website || 'N/A';
                document.getElementById('v_address').textContent = data.formatted_address || 'N/A';

                document.getElementById('v_pdf_link').href = '/letterhead/' + lh.id + '/pdf';
                document.getElementById('v_print_link').href = '/letterhead/' + lh.id + '/print';

                var modal = new bootstrap.Modal(document.getElementById('lhViewModal'));
                modal.show();
            }
        });
    }

    function confirmDelete(id, name, isDefault) {
        document.getElementById('del_msg').textContent = 'Are you sure you want to delete "' + name + '"?';
        document.getElementById('del_form').action = '/letterhead/' + id;
        const defaultWarn = document.getElementById('del_default_warning');
        if (isDefault) {
            defaultWarn.classList.remove('d-none');
        } else {
            defaultWarn.classList.add('d-none');
        }

        var modal = new bootstrap.Modal(document.getElementById('lhDeleteModal'));
        modal.show();
    }

    function submitMainForm(actionType) {
        document.getElementById('lhActionType').value = actionType;
        document.getElementById('lhMainForm').submit();
    }

    function handleTypeChange() {
        const type = document.getElementById('f_type').value;
        document.getElementById('context_company_wrap').classList.toggle('d-none', type !== 'company');
        document.getElementById('context_branch_wrap').classList.toggle('d-none', type !== 'branch');
        document.getElementById('context_department_wrap').classList.toggle('d-none', type !== 'department');
        document.getElementById('context_project_wrap').classList.toggle('d-none', type !== 'project');
    }

    function handleCompanySelect() {
        const select = document.getElementById('f_company_id');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            if (opt.dataset.name) document.getElementById('f_company_name').value = opt.dataset.name;
            if (opt.dataset.email) document.getElementById('f_email').value = opt.dataset.email;
            if (opt.dataset.phone) document.getElementById('f_phone').value = opt.dataset.phone;
            if (opt.dataset.address) document.getElementById('f_address_line_1').value = opt.dataset.address;
            if (opt.dataset.website) document.getElementById('f_website').value = opt.dataset.website;
        }
    }

    function handleBranchSelect() {
        const select = document.getElementById('f_branch_id');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            if (opt.dataset.name) document.getElementById('f_company_name').value = opt.dataset.name;
            if (opt.dataset.email) document.getElementById('f_email').value = opt.dataset.email;
            if (opt.dataset.phone) document.getElementById('f_phone').value = opt.dataset.phone;
            if (opt.dataset.address) document.getElementById('f_address_line_1').value = opt.dataset.address;
        }
    }

    function handleDepartmentSelect() {
        const select = document.getElementById('f_department_id');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            if (opt.dataset.name) document.getElementById('f_tagline').value = 'Department of ' + opt.dataset.name;
        }
    }

    function handleProjectSelect() {
        const select = document.getElementById('f_project_id');
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            if (opt.dataset.name) document.getElementById('f_tagline').value = 'Project: ' + opt.dataset.name;
        }
    }

    function handleLogoChange(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('f_logo_img').src = e.target.result;
                document.getElementById('f_logo_img').classList.remove('d-none');
                document.getElementById('f_logo_placeholder').classList.add('d-none');
                document.getElementById('f_remove_logo_btn').classList.remove('d-none');
                document.getElementById('lhRemoveLogo').value = '0';

                // Update live preview
                document.getElementById('liveLogoImg').src = e.target.result;
                document.getElementById('liveLogoImg').classList.remove('d-none');
                syncLivePreview();
            };
            reader.readAsDataURL(file);
        }
    }

    function clearLogoInput() {
        document.getElementById('f_logo').value = '';
        document.getElementById('f_logo_img').src = '';
        document.getElementById('f_logo_img').classList.add('d-none');
        document.getElementById('f_logo_placeholder').classList.remove('d-none');
        document.getElementById('f_remove_logo_btn').classList.add('d-none');
        document.getElementById('lhRemoveLogo').value = '1';
        document.getElementById('liveLogoImg').classList.add('d-none');
        syncLivePreview();
    }

    // Real-Time Live Preview Sync Engine
    function syncLivePreview() {
        const orgName = document.getElementById('f_company_name').value || document.getElementById('f_name').value || 'Company Name';
        const tagline = document.getElementById('f_tagline').value || 'Corporate Document Subtitle';
        const address = [
            document.getElementById('f_address_line_1').value,
            document.getElementById('f_city').value,
            document.getElementById('f_state').value
        ].filter(Boolean).join(', ') || 'Silicon Tower 4, Level 8, CA 94107';

        const contact = [
            document.getElementById('f_phone').value ? 'Phone: ' + document.getElementById('f_phone').value : '',
            document.getElementById('f_email').value ? document.getElementById('f_email').value : ''
        ].filter(Boolean).join(' | ') || 'Phone: +1 800-458-9200 | contact@bitroxia.com';

        const footerContent = document.getElementById('f_footer_content').value || (orgName + ' • ' + address);
        const footerText = document.getElementById('f_footer_text').value || 'Official communication issued by corporate systems.';
        const primaryColor = document.getElementById('f_primary_color').value || '#0f744c';
        const headerBorderColor = document.getElementById('f_header_border_color').value || primaryColor;
        const footerBorderColor = document.getElementById('f_footer_border_color').value || '#e2e8f0';

        document.getElementById('f_primary_color_text').value = primaryColor;
        document.getElementById('f_secondary_color_text').value = document.getElementById('f_secondary_color').value;
        document.getElementById('f_header_border_color_text').value = headerBorderColor;
        document.getElementById('f_footer_border_color_text').value = footerBorderColor;

        // Apply text
        document.getElementById('liveOrgName').textContent = orgName;
        document.getElementById('liveOrgName').style.color = primaryColor;
        document.getElementById('liveSubject').style.color = primaryColor;
        document.getElementById('liveTagline').textContent = tagline;
        document.getElementById('liveAddress').textContent = address;
        document.getElementById('liveContact').textContent = contact;
        document.getElementById('liveFooterContent').textContent = footerContent;
        document.getElementById('liveFooterText').textContent = footerText;

        // Header Styling
        const headerBox = document.getElementById('liveHeaderBox');
        const headerStyle = document.getElementById('f_header_border_style').value || 'solid';
        const headerThickness = document.getElementById('f_header_border_thickness').value || 2;
        headerBox.style.borderBottom = headerStyle === 'none' ? 'none' : (headerThickness + 'px ' + headerStyle + ' ' + headerBorderColor);

        // Logo Position
        const logoPos = document.getElementById('f_logo_position').value || 'left';
        const headerInner = document.getElementById('liveHeaderInner');
        if (logoPos === 'center') {
            headerInner.className = 'd-flex flex-column align-items-center text-center gap-1';
        } else if (logoPos === 'right') {
            headerInner.className = 'd-flex flex-row-reverse align-items-center justify-content-between gap-2';
        } else {
            headerInner.className = 'd-flex align-items-center justify-content-between gap-2';
        }

        // Footer Styling
        const footerBox = document.getElementById('liveFooterBox');
        const footerStyle = document.getElementById('f_footer_border_style').value || 'solid';
        footerBox.style.borderTop = footerStyle === 'none' ? 'none' : ('1px ' + footerStyle + ' ' + footerBorderColor);
        footerBox.style.textAlign = document.getElementById('f_footer_alignment').value || 'center';

        // Watermark
        const wmEnabled = document.getElementById('f_watermark_enabled').checked;
        const wmText = document.getElementById('f_watermark_text').value || 'CONFIDENTIAL';
        const wmOpacity = document.getElementById('f_watermark_opacity').value || 0.08;
        const liveWm = document.getElementById('liveWatermark');
        if (wmEnabled) {
            liveWm.textContent = wmText;
            liveWm.style.opacity = wmOpacity;
            liveWm.style.color = primaryColor;
            liveWm.classList.remove('d-none');
        } else {
            liveWm.classList.add('d-none');
        }
    }
</script>
@endsection
