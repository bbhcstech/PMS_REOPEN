@extends('admin.layout.app')

@section('title', 'Events - Company Events & Activities')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    /* =========================================================================
     | EVENT CARD MODERN DESIGN SYSTEM
     | ========================================================================= */
    .event-card {
        border: 1px solid rgba(15, 116, 76, 0.12);
        border-radius: 18px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }
    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 35px rgba(15, 116, 76, 0.14);
        border-color: rgba(15, 116, 76, 0.3);
    }
    .event-banner-img {
        width: 100%;
        height: 175px;
        object-fit: cover;
        background: linear-gradient(135deg, #0f744c 0%, #094c32 100%);
    }
    .event-banner-placeholder {
        width: 100%;
        height: 155px;
        background: linear-gradient(135deg, #0f744c 0%, #15803d 50%, #047857 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        position: relative;
    }
    
    /* Modern Status Badges */
    .badge-status-published {
        background-color: #ecfdf5 !important;
        color: #065f46 !important;
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem !important;
    }
    .badge-status-draft {
        background-color: #fffbeb !important;
        color: #92400e !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem !important;
    }
    .badge-status-cancelled {
        background-color: #fef2f2 !important;
        color: #991b1b !important;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem !important;
    }
    .badge-status-completed {
        background-color: #eff6ff !important;
        color: #1e40af !important;
        border: 1px solid rgba(59, 130, 246, 0.3) !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem !important;
    }
    .badge-event-type {
        background-color: #6366f1 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        font-size: 0.75rem !important;
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.25) !important;
    }

    /* Modern RSVP Segmented Control */
    .rsvp-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 14px;
        margin-bottom: 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    }
    .rsvp-segmented-bar {
        display: flex;
        background: #e8edf2;
        padding: 4px;
        border-radius: 14px;
        gap: 4px;
        margin-top: 10px;
    }
    .rsvp-segment-btn {
        flex: 1;
        border: none !important;
        outline: none !important;
        border-radius: 10px !important;
        padding: 8px 12px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        line-height: 1.2 !important;
        color: #475569;
        background: transparent;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .rsvp-segment-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.6);
    }
    .rsvp-segment-btn.active-going {
        background: #0d6e46 !important;
        color: #ffffff !important;
        box-shadow: 0 3px 10px rgba(13, 110, 70, 0.35) !important;
    }
    .rsvp-segment-btn.active-maybe {
        background: #d97706 !important;
        color: #ffffff !important;
        box-shadow: 0 3px 10px rgba(217, 119, 6, 0.35) !important;
    }
    .rsvp-segment-btn.active-not_going {
        background: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: 0 3px 10px rgba(220, 38, 38, 0.35) !important;
    }

    /* Action Icons */
    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: none;
        background: transparent;
        font-size: 1.1rem;
    }
    .btn-action-icon:hover {
        background: rgba(15, 116, 76, 0.08);
        transform: translateY(-1px);
    }
    .btn-action-icon.text-primary:hover {
        background: rgba(124, 58, 237, 0.1);
    }
    .btn-action-icon.text-danger:hover {
        background: rgba(239, 68, 68, 0.1);
    }
    .btn-action-icon.text-success:hover {
        background: rgba(16, 185, 129, 0.1);
    }

    /* KPI Summary Cards */
    .kpi-card {
        border-radius: 16px;
        border: 1px solid rgba(15, 116, 76, 0.08);
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* PREMIUM FORM STYLES */
    .form-section-title {
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #0f744c;
        background: #f0fdf4;
        border-left: 4px solid #0f744c;
        padding: 8px 14px;
        border-radius: 8px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .premium-label {
        font-weight: 700;
        font-size: 0.82rem;
        color: #334155;
        margin-bottom: 6px;
        letter-spacing: 0.2px;
    }
    .premium-input, .premium-select {
        background-color: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        padding: 10px 14px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        transition: all 0.2s ease !important;
    }
    .premium-input:focus, .premium-select:focus {
        border-color: #0f744c !important;
        box-shadow: 0 0 0 4px rgba(15, 116, 76, 0.15) !important;
        outline: none !important;
    }

    #eventCalendar {
        background: #ffffff;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid rgba(15, 116, 76, 0.12);
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }

    /* Filter Bar Improvements */
    .events-filter-card {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(15, 116, 76, 0.12);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }
    .events-filter-wrapper {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .filter-search-box {
        flex: 1 1 240px;
        min-width: 210px;
    }
    .filter-select-box {
        flex: 0 1 150px;
        min-width: 130px;
    }
    .filter-btn-nowrap {
        white-space: nowrap !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        padding: 0.5rem 1.1rem !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        line-height: 1.25 !important;
        flex-shrink: 0 !important;
    }
    .filter-btn-group {
        flex-shrink: 0 !important;
        display: inline-flex !important;
    }
    .filter-btn-group .btn {
        white-space: nowrap !important;
        padding: 0.5rem 1rem !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        line-height: 1.25 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    /* Event Meta Cards in Modal */
    .event-meta-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .event-meta-card:hover {
        background: #ffffff;
        border-color: rgba(15, 116, 76, 0.25);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    }

    /* =========================================================================
     | GALLERY & EVENT MEMORIES STYLES
     | ========================================================================= */
    .gallery-card {
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid rgba(15, 116, 76, 0.12);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 35px rgba(15, 116, 76, 0.14);
        border-color: rgba(15, 116, 76, 0.3);
    }
    .gallery-preview-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 3px;
        height: 190px;
        background: #0f172a;
        position: relative;
        overflow: hidden;
    }
    .gallery-preview-grid.single-image {
        grid-template-columns: 1fr;
    }
    .gallery-preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .gallery-card:hover .gallery-preview-img {
        transform: scale(1.05);
    }
    .gallery-overlay-count {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 5px 12px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* DRAG & DROP UPLOADER */
    .photo-dropzone {
        border: 2.5px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 16px;
        padding: 32px 20px;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .photo-dropzone:hover, .photo-dropzone.dragover {
        border-color: #0f744c;
        background: #f0fdf4;
    }
    .upload-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
        margin-top: 15px;
        max-height: 250px;
        overflow-y: auto;
    }
    .upload-preview-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        height: 100px;
    }
    .upload-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .upload-preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(220, 38, 38, 0.9);
        color: #ffffff;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
    }

    /* LIGHTBOX STYLES (EXECUTIVE CLEAN WHITE & SOFT NEUTRAL DESIGN) */
    .lightbox-modal-content {
        background: #ffffff !important;
        border-radius: 20px !important;
        color: #1e293b !important;
        border: none !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.22) !important;
        overflow: hidden;
    }
    .lightbox-img-wrapper {
        position: relative;
        min-height: 420px;
        max-height: 72vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin: 0 10px;
    }
    .lightbox-img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    .lightbox-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #ffffff !important;
        color: #0f744c !important;
        border: 1.5px solid #cbd5e1 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.8rem !important;
        font-weight: bold !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 10;
        cursor: pointer;
    }
    .lightbox-nav-btn:hover {
        background: #0f744c !important;
        color: #ffffff !important;
        border-color: #0f744c !important;
        transform: translateY(-50%) scale(1.1) !important;
    }
    .lightbox-nav-btn i {
        color: inherit !important;
    }
    .lightbox-nav-prev { left: 16px; }
    .lightbox-nav-next { right: 16px; }

    /* UNIVERSAL PREMIUM CLOSE BUTTONS (ALWAYS ALIGNED FAR RIGHT) */
    .modal-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }
    .btn-close-premium, .btn-close-premium-white {
        margin-left: auto !important;
        flex-shrink: 0 !important;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.25rem !important;
        font-weight: bold !important;
        cursor: pointer;
        transition: all 0.2s ease !important;
        line-height: 1 !important;
        padding: 0 !important;
        outline: none !important;
    }
    .btn-close-premium:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
        transform: scale(1.1);
    }
    .btn-close-premium-white {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(4px);
    }
    .btn-close-premium-white:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
        transform: scale(1.1);
    }

    /* PHOTO GRID IN EVENT DETAILS */
    .event-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }
    .event-photo-grid-item {
        position: relative;
        border-radius: 14px;
        height: 130px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        cursor: pointer;
        background: #f8fafc;
    }
    .photo-img-box {
        width: 100%;
        height: 100%;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
    }
    .photo-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .event-photo-grid-item:hover .photo-img-box img {
        transform: scale(1.06);
    }
    /* HIGH CONTRAST CLOSE BUTTONS & 3-DOT ACTION BUTTONS */
    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%) !important;
        opacity: 0.95 !important;
    }
    .btn-close-white:hover {
        opacity: 1 !important;
        transform: scale(1.1);
    }
    .btn-close {
        opacity: 0.85 !important;
        transition: all 0.2s ease !important;
    }
    .btn-close:hover {
        opacity: 1 !important;
        transform: scale(1.1);
    }

    .photo-action-dropdown {
        position: absolute;
        top: 6px;
        right: 6px;
        z-index: 5;
    }
    .photo-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.85) !important;
        color: #ffffff !important;
        border: 1.5px solid rgba(255, 255, 255, 0.4) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.2rem !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        transition: all 0.2s ease !important;
    }
    .photo-action-btn:hover {
        background: #0f744c !important;
        color: #ffffff !important;
        border-color: #ffffff !important;
        transform: scale(1.08);
    }
    .photo-action-btn i {
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- HEADER BLOCK --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bx bx-calendar-event text-success fs-3"></i> Events & Memories
            </h4>
            <p class="text-muted mb-0 small">Company events, activities, and photo archive</p>
        </div>
        @if($canManage)
        <div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-bold shadow-sm" onclick="openCreateModal()">
                <i class="bx bx-plus fs-5"></i> Create Event
            </button>
        </div>
        @endif
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Total Events</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1">{{ number_format($totalEvents) }}</h3>
                    </div>
                    <div class="kpi-icon-box bg-label-primary text-primary">
                        <i class="bx bx-calendar-event"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Upcoming Events</span>
                        <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($upcomingEvents) }}</h3>
                    </div>
                    <div class="kpi-icon-box bg-label-success text-success">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Today's Events</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1">{{ number_format($todayEvents) }}</h3>
                    </div>
                    <div class="kpi-icon-box bg-label-warning text-warning">
                        <i class="bx bx-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card kpi-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Past Events</span>
                        <h3 class="fw-bold mb-0 text-secondary mt-1">{{ number_format($pastEvents) }}</h3>
                    </div>
                    <div class="kpi-icon-box bg-label-secondary text-secondary">
                        <i class="bx bx-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR & VIEW TOGGLE --}}
    <div class="card events-filter-card border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('events.index') }}" id="filterForm">
                <div class="events-filter-wrapper">
                    {{-- Search Input --}}
                    <div class="filter-search-box">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-success"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search title, desc, location..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Event Type Filter --}}
                    <div class="filter-select-box">
                        <select name="event_type" class="form-select bg-light">
                            <option value="all">All Types</option>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="filter-select-box">
                        <select name="status" class="form-select bg-light">
                            <option value="all">All Statuses</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            @if($canManage)
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            @endif
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Time Filter --}}
                    <div class="filter-select-box">
                        <select name="time_filter" class="form-select bg-light">
                            <option value="">All Time</option>
                            <option value="upcoming" {{ request('time_filter') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="today" {{ request('time_filter') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="past" {{ request('time_filter') == 'past' ? 'selected' : '' }}>Past</option>
                        </select>
                    </div>

                    {{-- Filter Action Buttons --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                        <button type="submit" class="btn btn-primary filter-btn-nowrap">
                            <i class="bx bx-filter-alt"></i> Apply
                        </button>
                        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary filter-btn-nowrap">
                            <i class="bx bx-reset"></i> Reset
                        </a>

                        {{-- ENHANCED VIEW TOGGLE BUTTONS [LIST] [CALENDAR] [GALLERY] --}}
                        <div class="btn-group filter-btn-group ms-1" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="btnListView" onclick="switchView('list')">
                                <i class="bx bx-list-ul"></i> List
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnCalendarView" onclick="switchView('calendar')">
                                <i class="bx bx-calendar"></i> Calendar
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnGalleryView" onclick="switchView('gallery')">
                                <i class="bx bx-images"></i> Gallery
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- LIST VIEW SECTION --}}
    <div id="eventsListView">
        @if($events->count() > 0)
            <div class="row g-4">
                @foreach($events as $event)
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="card event-card h-100 d-flex flex-column">
                        {{-- Banner / Header Image --}}
                        @if($event->banner_url)
                            <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="event-banner-img">
                        @else
                            <div class="event-banner-placeholder">
                                <i class="bx bx-calendar-event opacity-50 display-1"></i>
                                <div class="position-absolute bottom-0 start-0 p-3">
                                    <span class="badge badge-event-type">
                                        {{ $event->event_type }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            {{-- Badges Row --}}
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge badge-event-type">
                                    {{ $event->event_type }}
                                </span>
                                @php
                                    $statusClass = match($event->status) {
                                        'published' => 'badge-status-published',
                                        'draft' => 'badge-status-draft',
                                        'cancelled' => 'badge-status-cancelled',
                                        'completed' => 'badge-status-completed',
                                        default => 'badge-status-published'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} text-capitalize">
                                    {{ $event->status }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <h5 class="fw-extrabold text-dark mb-2 text-truncate" style="font-size: 1.15rem;" title="{{ $event->title }}">
                                {{ $event->title }}
                            </h5>

                            {{-- Date & Time --}}
                            <div class="small text-muted mb-2 d-flex align-items-center gap-1.5 fw-semibold">
                                <i class="bx bx-calendar text-success fs-6"></i>
                                <span class="text-dark fw-bold">{{ $event->start_date->format('d M Y') }}</span>
                                @if($event->start_time)
                                    <span>• {{ date('h:i A', strtotime($event->start_time)) }}</span>
                                @endif
                            </div>

                            {{-- Location --}}
                            <div class="small text-muted mb-3 d-flex align-items-center gap-1.5 text-truncate">
                                @if($event->location_type === 'online')
                                    <i class="bx bx-video text-info fs-6"></i>
                                    <a href="{{ $event->meeting_url }}" target="_blank" class="text-info text-decoration-none text-truncate fw-semibold">
                                        Online Meeting
                                    </a>
                                @else
                                    <i class="bx bx-map-pin text-danger fs-6"></i>
                                    <span class="text-truncate text-dark fw-semibold">{{ $event->location ?: 'Physical Venue' }}</span>
                                @endif
                            </div>

                            {{-- Description snippet --}}
                            <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                {{ Str::limit(strip_tags($event->description), 110) }}
                            </p>

                            {{-- EVENT MEMORIES PHOTO COUNT BADGE --}}
                            @if($event->photos_count > 0)
                                <div class="mb-3">
                                    <button type="button" class="btn btn-sm text-success fw-bold pill border-0 d-inline-flex align-items-center gap-1.5" onclick="showEventDetails({{ $event->id }})" style="background: #ecfdf5; color: #0f744c; padding: 6px 14px; border-radius: 20px;">
                                        <i class="bx bx-images fs-5"></i> 📷 {{ $event->photos_count }} Event {{ Str::plural('Photo', $event->photos_count) }}
                                    </button>
                                </div>
                            @endif

                            {{-- RSVP SECTION --}}
                            @if($event->rsvp_required && $event->status !== 'cancelled')
                                <div class="rsvp-box">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-extrabold text-dark d-flex align-items-center gap-1.5" style="font-size: 0.92rem;">
                                            <i class="bx bx-checkbox-checked text-success fs-5"></i> RSVP:
                                        </span>
                                        <span class="badge" style="background: #e8f5e9; color: #16a34a; font-weight: 700; font-size: 0.78rem; padding: 6px 12px; border-radius: 20px;">
                                            <i class="bx bx-user-check me-1"></i> {{ $event->rsvp_counts['going'] }} Going
                                        </span>
                                    </div>
                                    @php $myRsvp = $event->user_rsvp?->response; @endphp
                                    <div class="rsvp-segmented-bar">
                                        <button type="button" class="rsvp-segment-btn {{ $myRsvp === 'going' ? 'active-going' : '' }}" onclick="submitRsvp({{ $event->id }}, 'going')">
                                            Going
                                        </button>
                                        <button type="button" class="rsvp-segment-btn {{ $myRsvp === 'maybe' ? 'active-maybe' : '' }}" onclick="submitRsvp({{ $event->id }}, 'maybe')">
                                            Maybe
                                        </button>
                                        <button type="button" class="rsvp-segment-btn {{ $myRsvp === 'not_going' ? 'active-not_going' : '' }}" onclick="submitRsvp({{ $event->id }}, 'not_going')">
                                            No
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- CARD FOOTER / ACTIONS --}}
                            <div class="pt-2.5 border-top d-flex align-items-center justify-content-between mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs" style="width: 32px; height: 32px;">
                                        <span class="avatar-initial rounded-circle bg-success text-white fw-bold shadow-sm" style="font-size: 11px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f744c, #094c32) !important;">
                                            {{ strtoupper(substr($event->organizer?->name ?? 'C', 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="small fw-bold text-dark text-truncate" style="max-width: 130px;" title="{{ $event->organizer?->name }}">
                                        {{ $event->organizer?->name ?? 'Company' }}
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn-action-icon text-muted" title="View Memories / Details" onclick="showEventDetails({{ $event->id }})">
                                        <i class="bx bx-show"></i>
                                    </button>

                                    @if($canManage)
                                        <button type="button" class="btn-action-icon text-primary" title="Edit Event" onclick="editEvent({{ $event->id }})">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>

                                        @if($event->status === 'draft')
                                            <button type="button" class="btn-action-icon text-success" title="Publish Event" onclick="publishEvent({{ $event->id }})">
                                                <i class="bx bx-send"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn-action-icon text-danger" title="Delete Event" onclick="deleteEvent({{ $event->id }})">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $events->links() }}
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="card border-0 shadow-sm p-5 text-center my-4">
                <div class="avatar avatar-xl bg-label-success rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                    <i class="bx bx-calendar-event fs-1 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No upcoming events</h5>
                <p class="text-muted small mb-4">Company events and activities will appear here.</p>
                @if($canManage)
                    <div>
                        <button type="button" class="btn btn-primary px-4 fw-bold" onclick="openCreateModal()">
                            <i class="bx bx-plus me-1"></i> Create Event
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- CALENDAR VIEW SECTION --}}
    <div id="eventsCalendarView" class="d-none">
        <div id="eventCalendar"></div>
    </div>

    {{-- GALLERY VIEW SECTION (NEW) --}}
    <div id="eventsGalleryView" class="d-none">
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bx bx-images text-success fs-3"></i> Company Event Gallery Archive
            </h5>
            <p class="text-muted small mb-0">Browse photo memories of company tours, celebrations, picnics, and annual events.</p>
        </div>

        @if($events->count() > 0)
            <div class="row g-4">
                @foreach($events as $event)
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="gallery-card h-100 d-flex flex-column cursor-pointer" onclick="showEventDetails({{ $event->id }})">
                        @php
                            $previewPhotos = $event->photos->take(4);
                        @endphp
                        
                        {{-- Photo Matrix Grid --}}
                        <div class="gallery-preview-grid {{ $previewPhotos->count() <= 1 ? 'single-image' : '' }}">
                            @if($previewPhotos->count() > 0)
                                @foreach($previewPhotos as $p)
                                    <img src="{{ $p->image_url }}" alt="{{ $event->title }}" class="gallery-preview-img">
                                @endforeach
                            @elseif($event->banner_url)
                                <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="gallery-preview-img" style="grid-column: span 2;">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary text-white w-100" style="grid-column: span 2;">
                                    <i class="bx bx-images fs-1 opacity-50"></i>
                                </div>
                            @endif

                            @if($event->photos_count > 0)
                                <span class="gallery-overlay-count">
                                    <i class="bx bx-camera me-1"></i> {{ $event->photos_count }} {{ Str::plural('Photo', $event->photos_count) }}
                                </span>
                            @else
                                <span class="gallery-overlay-count bg-dark">
                                    No photos yet
                                </span>
                            @endif
                        </div>

                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge badge-event-type">{{ $event->event_type }}</span>
                                <span class="text-muted small fw-semibold">
                                    <i class="bx bx-calendar text-success me-1"></i> {{ $event->start_date->format('d M Y') }}
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-2 text-truncate" title="{{ $event->title }}">
                                {{ $event->title }}
                            </h5>

                            <p class="text-secondary small mb-3 flex-grow-1 text-truncate">
                                <i class="bx bx-map-pin me-1 text-danger"></i> {{ $event->location ?: 'Company Event' }}
                            </p>

                            <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                                <span class="small fw-semibold text-muted">
                                    Organized by <strong class="text-dark">{{ $event->organizer?->name ?? 'Company' }}</strong>
                                </span>
                                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm" style="background: linear-gradient(135deg, #0f744c, #094c32); border: none;">
                                    View Memories
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm p-5 text-center my-4">
                <i class="bx bx-images fs-1 text-muted mb-2"></i>
                <h5 class="fw-bold text-dark">No Event Memories</h5>
                <p class="text-muted small">No event photo memories have been created yet.</p>
            </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{-- CREATE / EDIT EVENT MODAL (PREMIUM DESIGN) --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.18);">
            <div class="modal-header py-3.5 px-4 text-white" style="background: linear-gradient(135deg, #0f744c 0%, #094c32 100%);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="eventModalTitle">
                    <i class="bx bx-calendar-plus fs-4"></i> Create Company Event
                </h5>
                <button type="button" class="btn-close-premium-white" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <form id="eventForm" enctype="multipart/form-data" method="POST" action="{{ route('events.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="event_id" id="eventId" value="">

                <div class="modal-body p-4">

                    {{-- SECTION 1: EVENT INFORMATION --}}
                    <div class="form-section-title">
                        <i class="bx bx-info-circle fs-5"></i> SECTION 1: EVENT INFORMATION
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label premium-label">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="eventTitleInput" class="form-control premium-input" required placeholder="e.g., Annual General Meeting">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label premium-label">Event Type <span class="text-danger">*</span></label>
                            <select name="event_type" id="eventTypeInput" class="form-select premium-select" required>
                                @foreach($eventTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label premium-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="eventDescriptionInput" class="form-control premium-input" rows="3" required placeholder="Provide details about the company event..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label premium-label">Event Banner / Image</label>
                            <input type="file" name="banner" id="eventBannerInput" class="form-control premium-input" accept="image/jpeg,image/png,image/jpg,image/webp">
                            <small class="text-muted d-block mt-1">Max file size 5MB (JPG, PNG, WEBP)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label premium-label">Organizer</label>
                            <select name="organizer_id" id="eventOrganizerInput" class="form-select premium-select">
                                <option value="">Select Organizer</option>
                                @foreach($users as $usr)
                                    <option value="{{ $usr->id }}" {{ auth()->id() == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} ({{ ucfirst($usr->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- SECTION 2: DATE & TIME --}}
                    <div class="form-section-title">
                        <i class="bx bx-time fs-5"></i> SECTION 2: DATE & TIME
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label premium-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="eventStartDateInput" class="form-control premium-input" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label premium-label">Start Time</label>
                            <input type="time" name="start_time" id="eventStartTimeInput" class="form-control premium-input" value="09:00">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label premium-label">End Date</label>
                            <input type="date" name="end_date" id="eventEndDateInput" class="form-control premium-input" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label premium-label">End Time</label>
                            <input type="time" name="end_time" id="eventEndTimeInput" class="form-control premium-input" value="17:00">
                        </div>
                    </div>

                    {{-- SECTION 3: LOCATION --}}
                    <div class="form-section-title">
                        <i class="bx bx-map-pin fs-5"></i> SECTION 3: LOCATION
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label premium-label">Location Type <span class="text-danger">*</span></label>
                            <select name="location_type" id="eventLocationTypeInput" class="form-select premium-select" onchange="toggleLocationFields()">
                                <option value="physical">Physical</option>
                                <option value="online">Online</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-8" id="venueFieldBlock">
                            <label class="form-label premium-label">Venue / Physical Location</label>
                            <input type="text" name="location" id="eventLocationInput" class="form-control premium-input" placeholder="e.g., Conference Room B, HQ Floor 3">
                        </div>
                        <div class="col-md-8 d-none" id="meetingUrlFieldBlock">
                            <label class="form-label premium-label">Meeting URL</label>
                            <input type="url" name="meeting_url" id="eventMeetingUrlInput" class="form-control premium-input" placeholder="https://zoom.us/j/... or https://meet.google.com/...">
                        </div>
                    </div>

                    {{-- SECTION 4: EVENT SETTINGS --}}
                    <div class="form-section-title">
                        <i class="bx bx-slider-alt fs-5"></i> SECTION 4: EVENT SETTINGS
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="rsvp_required" value="1" id="eventRsvpInput" checked style="width: 2.2em; height: 1.2em;">
                                <label class="form-check-label premium-label ms-1" for="eventRsvpInput">RSVP Required</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label premium-label">Max Participants</label>
                            <input type="number" name="max_participants" id="eventMaxPartInput" class="form-control premium-input" placeholder="Optional cap">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label premium-label">Event Status <span class="text-danger">*</span></label>
                            <select name="status" id="eventStatusInput" class="form-select premium-select" required>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-label-secondary px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitForm" style="background: linear-gradient(135deg, #0f744c, #094c32); border: none;">
                        <i class="bx bx-save me-1"></i> Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- EVENT DETAILS & EVENT MEMORIES MODAL --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 18px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header bg-light py-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="detailModalTitle">
                    <i class="bx bx-calendar-event text-success fs-4"></i> Event Details
                </h5>
                <button type="button" class="btn-close-premium" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>
            <div class="modal-body p-4" id="detailModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MULTI-PHOTO UPLOADER MODAL (FACEBOOK-LIKE DRAG & DROP) --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.2);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f744c 0%, #094c32 100%);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bx bx-images fs-4"></i> Add Photos to <span id="uploadEventTitle">Event</span>
                </h5>
                <button type="button" class="btn-close-premium-white" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <form id="photoUploadForm">
                @csrf
                <input type="hidden" id="uploadEventId" value="">

                <div class="modal-body p-4">
                    {{-- Dropzone Area --}}
                    <div class="photo-dropzone" id="photoDropzone" onclick="document.getElementById('photoFileInput').click()">
                        <i class="bx bx-cloud-upload text-success display-3 mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">Drag & Drop Event Photos Here</h6>
                        <p class="text-muted small mb-3">Supports JPG, JPEG, PNG, WEBP (Max 10MB per photo)</p>
                        <button type="button" class="btn btn-outline-success btn-sm fw-bold px-4 rounded-pill">
                            <i class="bx bx-folder-open me-1"></i> Browse Photos
                        </button>
                        <input type="file" id="photoFileInput" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none" onchange="handleFileSelect(this.files)">
                    </div>

                    {{-- Selected Files Preview Grid --}}
                    <div class="mt-3 d-none" id="previewContainer">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark small" id="selectedCountBadge">0 Photos Selected</span>
                            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold" onclick="clearSelectedFiles()">
                                Clear All
                            </button>
                        </div>
                        <div class="upload-preview-grid" id="uploadPreviewGrid"></div>

                        {{-- Optional Caption Input --}}
                        <div class="mt-3">
                            <label class="form-label premium-label">Album Caption / Memory Note (Optional)</label>
                            <input type="text" id="uploadCaptionInput" class="form-control premium-input" placeholder="e.g., Everyone enjoying the team dinner at company tour...">
                        </div>
                    </div>

                    {{-- Upload Progress Bar --}}
                    <div class="mt-3 d-none" id="uploadProgressContainer">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-dark" id="uploadProgressStatus">Uploading Event Photos...</span>
                            <span class="small fw-bold text-success" id="uploadProgressPercent">0%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 10px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="uploadProgressBar" role="progressbar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-label-secondary px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSubmitUpload" disabled style="background: linear-gradient(135deg, #0f744c, #094c32); border: none;">
                        <i class="bx bx-upload me-1"></i> Upload Photos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- FULLSCREEN PHOTO LIGHTBOX MODAL --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="photoLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content lightbox-modal-content">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-images text-success fs-4"></i>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="lightboxTitle">Event Memory</h5>
                    <span class="badge" style="background: #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.82rem; padding: 6px 14px; border-radius: 20px;" id="lightboxCounter">Photo 1 of 1</span>
                </div>
                <button type="button" class="btn-close-premium" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="modal-body p-3">
                <div class="lightbox-img-wrapper">
                    <button type="button" class="lightbox-nav-btn lightbox-nav-prev" onclick="prevLightboxPhoto()" title="Previous Photo (Left Arrow)">
                        <i class="bx bx-chevron-left"></i>
                    </button>
                    
                    <img src="" alt="Event Memory" id="lightboxImg" class="lightbox-img">

                    <button type="button" class="lightbox-nav-btn lightbox-nav-next" onclick="nextLightboxPhoto()" title="Next Photo (Right Arrow)">
                        <i class="bx bx-chevron-right"></i>
                    </button>
                </div>

                {{-- Photo Meta & Caption --}}
                <div class="mt-3 px-3 py-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <p class="text-dark fw-bold small mb-1.5 fs-6" id="lightboxCaption"></p>
                    <div class="d-flex align-items-center justify-content-between text-muted small">
                        <span id="lightboxUploader" class="fw-semibold text-dark"><i class="bx bx-user text-primary me-1"></i> Uploaded by Admin</span>
                        <span id="lightboxDate" class="fw-semibold text-dark"><i class="bx bx-calendar text-success me-1"></i> 30 Aug 2026</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    let calendarInstance = null;
    let selectedUploadFiles = [];
    let currentLightboxPhotos = [];
    let currentLightboxIndex = 0;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCalendar();
        setupDragAndDrop();

        // Keyboard navigation for Lightbox
        document.addEventListener('keydown', function(e) {
            const lightboxEl = document.getElementById('photoLightboxModal');
            if (lightboxEl && lightboxEl.classList.contains('show')) {
                if (e.key === 'ArrowLeft') prevLightboxPhoto();
                if (e.key === 'ArrowRight') nextLightboxPhoto();
            }
        });
    });

    function switchView(view) {
        const listDiv = document.getElementById('eventsListView');
        const calDiv = document.getElementById('eventsCalendarView');
        const galDiv = document.getElementById('eventsGalleryView');

        const btnList = document.getElementById('btnListView');
        const btnCal = document.getElementById('btnCalendarView');
        const btnGal = document.getElementById('btnGalleryView');

        listDiv.classList.add('d-none');
        calDiv.classList.add('d-none');
        galDiv.classList.add('d-none');

        btnList.classList.remove('active');
        btnCal.classList.remove('active');
        btnGal.classList.remove('active');

        if (view === 'calendar') {
            calDiv.classList.remove('d-none');
            btnCal.classList.add('active');
            if (calendarInstance) calendarInstance.render();
        } else if (view === 'gallery') {
            galDiv.classList.remove('d-none');
            btnGal.classList.add('active');
        } else {
            listDiv.classList.remove('d-none');
            btnList.classList.add('active');
        }
    }

    function initCalendar() {
        const calendarEl = document.getElementById('eventCalendar');
        if (!calendarEl) return;

        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 700,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            events: '{{ route("events.calendar-data") }}',
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                showEventDetails(info.event.id);
            }
        });
    }

    function toggleLocationFields() {
        const type = document.getElementById('eventLocationTypeInput').value;
        const venueBlock = document.getElementById('venueFieldBlock');
        const urlBlock = document.getElementById('meetingUrlFieldBlock');

        if (type === 'physical') {
            venueBlock.classList.remove('d-none');
            urlBlock.classList.add('d-none');
        } else if (type === 'online') {
            venueBlock.classList.add('d-none');
            urlBlock.classList.remove('d-none');
        } else {
            venueBlock.classList.remove('d-none');
            urlBlock.classList.remove('d-none');
        }
    }

    function openCreateModal() {
        document.getElementById('eventModalTitle').innerHTML = '<i class="bx bx-calendar-plus fs-4"></i> Create Company Event';
        document.getElementById('eventForm').action = '{{ route("events.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('eventId').value = '';
        document.getElementById('eventForm').reset();
        toggleLocationFields();

        const modal = new bootstrap.Modal(document.getElementById('eventModal'));
        modal.show();
    }

    function editEvent(id) {
        fetch(`{{ url('events') }}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            const e = data.event;
            document.getElementById('eventModalTitle').innerHTML = '<i class="bx bx-edit-alt fs-4"></i> Edit Company Event';
            document.getElementById('eventForm').action = `{{ url('events') }}/${id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('eventId').value = e.id;

            document.getElementById('eventTitleInput').value = e.title;
            document.getElementById('eventTypeInput').value = e.event_type;
            document.getElementById('eventDescriptionInput').value = e.description;
            document.getElementById('eventOrganizerInput').value = e.organizer_id || '';
            document.getElementById('eventStartDateInput').value = e.start_date ? e.start_date.split('T')[0] : '';
            document.getElementById('eventStartTimeInput').value = e.start_time || '';
            document.getElementById('eventEndDateInput').value = e.end_date ? e.end_date.split('T')[0] : '';
            document.getElementById('eventEndTimeInput').value = e.end_time || '';
            document.getElementById('eventLocationTypeInput').value = e.location_type || 'physical';
            document.getElementById('eventLocationInput').value = e.location || '';
            document.getElementById('eventMeetingUrlInput').value = e.meeting_url || '';
            document.getElementById('eventMaxPartInput').value = e.max_participants || '';
            document.getElementById('eventStatusInput').value = e.status || 'published';
            document.getElementById('eventRsvpInput').checked = !!e.rsvp_required;

            toggleLocationFields();

            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        })
        .catch(err => {
            console.error(err);
            alert('Error loading event details for edit.');
        });
    }

    function showEventDetails(id) {
        const modalEl = document.getElementById('eventDetailsModal');
        const modalBody = document.getElementById('detailModalBody');
        modalBody.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;

        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }
        modal.show();

        fetch(`{{ url('events') }}/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP status ' + res.status);
            return res.json();
        })
        .then(data => {
            const e = data.event;
            const rsvp = data.rsvp_counts || { going: 0, maybe: 0, not_going: 0 };
            const canManage = data.can_manage;
            const photos = data.photos || [];

            currentLightboxPhotos = photos;
            currentLightboxEventId = e.id;
            currentLightboxCanManage = canManage;

            let bannerHtml = data.banner_url ?
                `<img src="${data.banner_url}" class="img-fluid rounded mb-3 w-100" style="max-height: 250px; object-fit: cover; border-radius: 14px;">` : '';

            let locationValue = e.location_type === 'online' ?
                `<a href="${escapeHtml(e.meeting_url || '#')}" target="_blank" class="text-info fw-bold text-decoration-none"><i class="bx bx-video me-1"></i> Online Meeting Link</a>` :
                `<span class="fw-bold text-dark">${escapeHtml(e.location || 'Physical Venue')}</span>`;

            if (e.location_type === 'hybrid') {
                locationValue = `<div><span class="fw-bold text-dark">${escapeHtml(e.location || 'Venue')}</span></div>` +
                    (e.meeting_url ? `<div class="mt-1"><a href="${escapeHtml(e.meeting_url)}" target="_blank" class="text-info fw-bold text-decoration-none small"><i class="bx bx-video me-1"></i> Join Online Link</a></div>` : '');
            }

            const organizerName = e.organizer ? escapeHtml(e.organizer.name) : 'Company Admin';
            const organizerInitials = organizerName.substring(0, 2).toUpperCase();

            // Build Photo Grid HTML for Event Memories
            let photosHtml = '';
            if (photos.length > 0) {
                photosHtml = `<div class="event-photo-grid mb-3">`;
                photos.forEach((p, idx) => {
                    const isCover = p.is_gallery_cover ? '<span class="position-absolute top-0 start-0 m-1.5 badge bg-success fw-bold" style="font-size: 9px; z-index: 4;">Cover</span>' : '';
                    
                    let actionDropdown = '';
                    if (canManage) {
                        actionDropdown = `
                        <div class="dropdown photo-action-dropdown">
                            <button type="button" class="photo-action-btn" data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                                <i class="bx bx-dots-vertical-rounded text-white" style="color: #ffffff !important; font-weight: bold;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" style="z-index: 1050; min-width: 175px;">
                                <li><a class="dropdown-item small py-2 fw-semibold" href="javascript:void(0)" onclick="openPhotoLightbox(${idx})"><i class="bx bx-show me-2 text-muted fs-6"></i> View Photo</a></li>
                                <li><a class="dropdown-item small py-2 fw-semibold" href="javascript:void(0)" onclick="setPhotoAsCover(${e.id}, ${p.id})"><i class="bx bx-star me-2 text-warning fs-6"></i> Set as Cover</a></li>
                                <li><a class="dropdown-item small py-2 fw-semibold" href="javascript:void(0)" onclick="editPhotoCaption(${e.id}, ${p.id}, '${escapeHtml(p.caption || '')}')"><i class="bx bx-edit-alt me-2 text-primary fs-6"></i> Edit Caption</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item small py-2 fw-bold text-danger" href="javascript:void(0)" onclick="deletePhoto(${e.id}, ${p.id})"><i class="bx bx-trash me-2 text-danger fs-6"></i> Delete Photo</a></li>
                            </ul>
                        </div>`;
                    }

                    photosHtml += `
                        <div class="event-photo-grid-item" onclick="openPhotoLightbox(${idx})">
                            <div class="photo-img-box">
                                ${isCover}
                                <img src="${p.image_url}" alt="Event Memory">
                            </div>
                            ${actionDropdown}
                        </div>`;
                });
                photosHtml += `</div>`;
            } else {
                photosHtml = `
                    <div class="text-center py-4 rounded-3" style="background: #f8fafc; border: 1.5px dashed #cbd5e1;">
                        <i class="bx bx-images fs-2 text-muted mb-1"></i>
                        <p class="text-dark fw-bold small mb-1">No Event Memories Yet</p>
                        <p class="text-muted small mb-3">Upload photographs from this event to preserve the celebration for your team.</p>
                        ${canManage ? `<button type="button" class="btn btn-outline-success btn-sm fw-bold px-3 rounded-pill" onclick="openUploadModal(${e.id}, '${escapeHtml(e.title)}')"><i class="bx bx-plus me-1"></i> Upload Photos</button>` : ''}
                    </div>`;
            }

            modalBody.innerHTML = `
                ${bannerHtml}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge badge-event-type">${escapeHtml(e.event_type)}</span>
                    <span class="badge badge-status-published text-capitalize">${escapeHtml(e.status)}</span>
                </div>

                <h4 class="fw-extrabold text-dark mb-2" style="font-size: 1.35rem;">${escapeHtml(e.title)}</h4>

                <div class="small text-muted mb-4 d-flex align-items-center gap-2">
                    <i class="bx bx-calendar text-success fs-5"></i>
                    <span class="fw-bold text-dark">${data.formatted_start_date} ${data.formatted_start_time ? '• ' + data.formatted_start_time : ''}</span>
                </div>

                {{-- DUAL INFO CARDS --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="event-meta-card p-3 rounded-3 d-flex align-items-center gap-3">
                            <div class="avatar avatar-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                <span class="avatar-initial rounded-circle bg-success text-white fw-bold shadow-sm" style="font-size: 12px; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f744c, #094c32) !important;">
                                    ${organizerInitials}
                                </span>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 10px; letter-spacing: 0.6px;">Organizer</span>
                                <span class="fw-bold text-dark fs-6 text-truncate d-block">${organizerName}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="event-meta-card p-3 rounded-3 d-flex align-items-center gap-3">
                            <div class="meta-icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef2f2; border: 1px solid rgba(239, 68, 68, 0.25);">
                                <i class="${e.location_type === 'online' ? 'bx bx-video text-info' : 'bx bx-map-pin text-danger'} fs-4"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 10px; letter-spacing: 0.6px;">Location / Venue</span>
                                <div class="text-truncate">${locationValue}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ABOUT EVENT DESCRIPTION --}}
                <div class="mb-4 p-3.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                        <i class="bx bx-align-left text-success"></i> About Event
                    </h6>
                    <p class="text-secondary small mb-0" style="white-space: pre-line; line-height: 1.6; font-size: 0.9rem;">${escapeHtml(e.description || 'No detailed description provided.')}</p>
                </div>

                {{-- EVENT MEMORIES / PHOTO GALLERY SECTION --}}
                <div class="mb-4 p-3.5 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-6">
                                <i class="bx bx-images text-success fs-5"></i> Event Memories
                                <span class="badge bg-success-subtle text-success rounded-pill fw-bold" style="font-size: 11px;">${photos.length} Photos</span>
                            </h6>
                            <span class="text-muted small">Photos and memories from this event</span>
                        </div>
                        ${canManage ? `
                        <button type="button" class="btn btn-success btn-sm fw-bold px-3 d-inline-flex align-items-center gap-1 rounded-pill shadow-sm" onclick="openUploadModal(${e.id}, '${escapeHtml(e.title)}')" style="background: linear-gradient(135deg, #0f744c, #094c32); border: none;">
                            <i class="bx bx-plus"></i> Add Photos
                        </button>` : ''}
                    </div>

                    ${photosHtml}
                </div>

                {{-- RSVP SUMMARY METRICS --}}
                ${e.rsvp_required ? `
                <div class="card border-0 p-3.5 mb-3" style="border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                        <i class="bx bx-pie-chart-alt-2 text-primary"></i> RSVP Summary
                    </h6>
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="p-3 rounded-3" style="border: 1px solid #d1fae5; background-color: #f0fdf4 !important;">
                                <span class="d-block small text-success fw-bold uppercase-label" style="font-size: 11px; letter-spacing: 0.5px;">Going</span>
                                <h3 class="fw-extrabold text-success mb-0 mt-1">${rsvp.going}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3" style="border: 1px solid #fef3c7; background-color: #fffbeb !important;">
                                <span class="d-block small text-warning fw-bold uppercase-label" style="font-size: 11px; letter-spacing: 0.5px;">Maybe</span>
                                <h3 class="fw-extrabold text-warning mb-0 mt-1">${rsvp.maybe}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3" style="border: 1px solid #fee2e2; background-color: #fef2f2 !important;">
                                <span class="d-block small text-danger fw-bold uppercase-label" style="font-size: 11px; letter-spacing: 0.5px;">Not Going</span>
                                <h3 class="fw-extrabold text-danger mb-0 mt-1">${rsvp.not_going}</h3>
                            </div>
                        </div>
                    </div>
                </div>` : ''}

                {{-- ACTION BUTTONS --}}
                ${canManage ? `
                <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                    <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold d-inline-flex align-items-center gap-1" onclick="bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide(); editEvent(${e.id});">
                        <i class="bx bx-edit"></i> Edit
                    </button>
                    ${e.status === 'draft' ? `
                    <button type="button" class="btn btn-success btn-sm px-3 fw-bold d-inline-flex align-items-center gap-1" onclick="publishEvent(${e.id})">
                        <i class="bx bx-send"></i> Publish
                    </button>` : ''}
                    ${e.status !== 'cancelled' ? `
                    <button type="button" class="btn btn-outline-warning btn-sm px-3 fw-bold d-inline-flex align-items-center gap-1" onclick="cancelEvent(${e.id})">
                        <i class="bx bx-block"></i> Cancel Event
                    </button>` : ''}
                    <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-bold d-inline-flex align-items-center gap-1" onclick="deleteEvent(${e.id})">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                </div>` : ''}
            `;
        })
        .catch(err => {
            console.error(err);
            modalBody.innerHTML = `<div class="alert alert-danger mb-0"><i class="bx bx-error-circle me-1"></i> Failed to load event details. Please try again.</div>`;
        });
    }

    /* =========================================================================
     | MULTI-PHOTO UPLOADER & LIGHTBOX JAVASCRIPT
     | ========================================================================= */
    function openUploadModal(eventId, eventTitle) {
        document.getElementById('uploadEventId').value = eventId;
        document.getElementById('uploadEventTitle').innerText = eventTitle;
        document.getElementById('uploadCaptionInput').value = '';
        clearSelectedFiles();

        const modal = new bootstrap.Modal(document.getElementById('photoUploadModal'));
        modal.show();
    }

    function setupDragAndDrop() {
        const dropzone = document.getElementById('photoDropzone');
        if (!dropzone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFileSelect(files);
        });
    }

    function handleFileSelect(files) {
        if (!files || files.length === 0) return;

        Array.from(files).forEach(file => {
            if (file.type.match('image.*')) {
                selectedUploadFiles.push(file);
            }
        });

        renderUploadPreviews();
    }

    function renderUploadPreviews() {
        const container = document.getElementById('previewContainer');
        const grid = document.getElementById('uploadPreviewGrid');
        const badge = document.getElementById('selectedCountBadge');
        const submitBtn = document.getElementById('btnSubmitUpload');

        grid.innerHTML = '';

        if (selectedUploadFiles.length === 0) {
            container.classList.add('d-none');
            submitBtn.disabled = true;
            return;
        }

        container.classList.remove('d-none');
        submitBtn.disabled = false;
        badge.innerText = `${selectedUploadFiles.length} Photos Selected`;

        selectedUploadFiles.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'upload-preview-item';
                item.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <div class="upload-preview-remove" onclick="removeSelectedFile(${idx})">&times;</div>`;
                grid.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeSelectedFile(index) {
        selectedUploadFiles.splice(index, 1);
        renderUploadPreviews();
    }

    function clearSelectedFiles() {
        selectedUploadFiles = [];
        document.getElementById('photoFileInput').value = '';
        renderUploadPreviews();
    }

    document.getElementById('photoUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const eventId = document.getElementById('uploadEventId').value;
        const caption = document.getElementById('uploadCaptionInput').value;
        const submitBtn = document.getElementById('btnSubmitUpload');
        const progressContainer = document.getElementById('uploadProgressContainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const progressPercent = document.getElementById('uploadProgressPercent');
        const progressStatus = document.getElementById('uploadProgressStatus');

        if (selectedUploadFiles.length === 0) return;

        const formData = new FormData();
        selectedUploadFiles.forEach((file) => {
            formData.append('photos[]', file);
        });
        if (caption) {
            formData.append('caption', caption);
        }

        submitBtn.disabled = true;
        progressContainer.classList.remove('d-none');
        progressBar.style.width = '0%';
        progressPercent.innerText = '0%';
        progressStatus.innerText = `Uploading ${selectedUploadFiles.length} Photos...`;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', `{{ url('events') }}/${eventId}/photos`, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressPercent.innerText = percent + '%';
                progressStatus.innerText = `Uploading Event Photos (${percent}%)...`;
            }
        };

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    progressBar.style.width = '100%';
                    progressPercent.innerText = '100%';
                    progressStatus.innerText = '✓ ' + data.message;

                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('photoUploadModal')).hide();
                        showEventDetails(eventId);
                    }, 800);
                } else {
                    alert(data.error || 'Failed to upload photos.');
                    submitBtn.disabled = false;
                }
            } else {
                alert('Server error while uploading photos.');
                submitBtn.disabled = false;
            }
        };

        xhr.onerror = function() {
            alert('Network error while uploading photos.');
            submitBtn.disabled = false;
        };

        xhr.send(formData);
    });

    /* =========================================================================
     | LIGHTBOX FUNCTIONS
     | ========================================================================= */
    function openPhotoLightbox(index) {
        if (!currentLightboxPhotos || currentLightboxPhotos.length === 0) return;
        currentLightboxIndex = index;
        updateLightboxContent();

        const modal = new bootstrap.Modal(document.getElementById('photoLightboxModal'));
        modal.show();
    }

    function updateLightboxContent() {
        const photo = currentLightboxPhotos[currentLightboxIndex];
        if (!photo) return;

        document.getElementById('lightboxImg').src = photo.image_url;
        document.getElementById('lightboxCounter').innerText = `Photo ${currentLightboxIndex + 1} of ${currentLightboxPhotos.length}`;
        document.getElementById('lightboxCaption').innerText = photo.caption ? `"${photo.caption}"` : '';
        
        let deleteBtnHtml = '';
        if (currentLightboxCanManage) {
            deleteBtnHtml = `<button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 fw-bold rounded-pill ms-2" onclick="deletePhotoFromLightbox(${photo.id})"><i class="bx bx-trash me-1"></i> Delete Photo</button>`;
        }

        document.getElementById('lightboxUploader').innerHTML = `<i class="bx bx-user me-1"></i> Uploaded by ${escapeHtml(photo.uploader ? photo.uploader.name : 'Admin')}`;
        document.getElementById('lightboxDate').innerHTML = `<div class="d-flex align-items-center gap-2"><span><i class="bx bx-calendar text-success me-1"></i> ${new Date(photo.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</span>${deleteBtnHtml}</div>`;
    }

    function deletePhotoFromLightbox(photoId) {
        if (!currentLightboxEventId || !photoId) return;
        deletePhoto(currentLightboxEventId, photoId);
        bootstrap.Modal.getInstance(document.getElementById('photoLightboxModal')).hide();
    }

    function prevLightboxPhoto() {
        if (currentLightboxPhotos.length === 0) return;
        currentLightboxIndex = (currentLightboxIndex - 1 + currentLightboxPhotos.length) % currentLightboxPhotos.length;
        updateLightboxContent();
    }

    function nextLightboxPhoto() {
        if (currentLightboxPhotos.length === 0) return;
        currentLightboxIndex = (currentLightboxIndex + 1) % currentLightboxPhotos.length;
        updateLightboxContent();
    }

    /* =========================================================================
     | PHOTO ACTION OPERATIONS (COVER, CAPTION, DELETE)
     | ========================================================================= */
    function setPhotoAsCover(eventId, photoId) {
        fetch(`{{ url('events') }}/${eventId}/photos/${photoId}/cover`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showEventDetails(eventId);
            }
        });
    }

    function editPhotoCaption(eventId, photoId, currentCaption) {
        const newCaption = prompt('Edit photo memory caption:', currentCaption);
        if (newCaption === null) return;

        fetch(`{{ url('events') }}/${eventId}/photos/${photoId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ caption: newCaption })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showEventDetails(eventId);
            }
        });
    }

    function deletePhoto(eventId, photoId) {
        if (!confirm('Are you sure you want to permanently remove this photograph from Event Memories?')) return;

        fetch(`{{ url('events') }}/${eventId}/photos/${photoId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showEventDetails(eventId);
            }
        });
    }

    function submitRsvp(eventId, response) {
        fetch(`{{ url('events') }}/${eventId}/rsvp`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ response: response })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Failed to update RSVP');
            }
        });
    }

    function publishEvent(eventId) {
        if (!confirm('Publish this event to all company users?')) return;
        fetch(`{{ url('events') }}/${eventId}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function cancelEvent(eventId) {
        if (!confirm('Are you sure you want to mark this event as cancelled?')) return;
        fetch(`{{ url('events') }}/${eventId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function deleteEvent(eventId) {
        if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) return;
        fetch(`{{ url('events') }}/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endsection
