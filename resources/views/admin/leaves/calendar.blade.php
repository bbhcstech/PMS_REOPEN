@extends('admin.layout.app')

@section('title', 'Leave Calendar')

@section('content')

<div class="leave-calendar-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-calendar-alt"></i> Dashboard / Leaves / Calendar
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h1>Leave Calendar</h1>
                <p>Visual overview of all leave requests</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> New Leave Request
            </a>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="view-toggle-card">
        <div class="view-toggle-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-nav mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
                    <li class="breadcrumb-item active">Calendar View</li>
                </ol>
            </nav>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('leaves.index') }}"
                   class="btn btn-outline-primary toggle-btn {{ request()->routeIs('leaves.index') ? 'active' : '' }}"
                   data-bs-toggle="tooltip" title="Table View">
                    <i class="fas fa-list-ul"></i> Table
                </a>
                <a href="{{ route('leaves.calendar') }}"
                   class="btn btn-outline-primary toggle-btn {{ request()->routeIs('leaves.calendar') ? 'active' : '' }}"
                   data-bs-toggle="tooltip" title="Calendar View">
                    <i class="fas fa-calendar"></i> Calendar
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="filter-card">
        <div class="filter-header">
            <i class="fas fa-filter"></i>
            <h6>Filter Calendar</h6>
        </div>
        <div class="filter-body">
            <form id="filterForm" class="filter-grid">
                <!-- Employee Filter -->
                <div class="filter-group">
                    <label for="employee" class="form-label">
                        {{ $isAdmin ? 'Employee' : 'My Leave Data' }}
                        <i class="fas fa-info-circle text-muted ms-1"
                           data-bs-toggle="tooltip"
                           title="{{ $isAdmin ? 'Filter by specific employee' : 'Your leave data only' }}"></i>
                    </label>
                    <select id="employee" name="employee" class="form-control select2">
                        <option value="">{{ $isAdmin ? 'All Employees' : 'My Leaves' }}</option>
                        @foreach($employee_data as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Leave Type Filter -->
                <div class="filter-group">
                    <label for="leave_type" class="form-label">Leave Type</label>
                    <select id="leave_type" name="leave_type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="filter-actions">
                    <button type="button" id="applyFilters" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="button" id="clearFilters" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar Legend -->
    <div class="legend-card">
        <div class="legend-header">
            <i class="fas fa-key"></i>
            <span>Legend</span>
        </div>
        <div class="legend-items">
            <div class="legend-item">
                <span class="legend-dot approved"></span>
                <span>Approved</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot pending"></span>
                <span>Pending</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot rejected"></span>
                <span>Rejected</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot multiple"></span>
                <span>Multiple Days</span>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="calendar-card">
        <div class="calendar-body">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid mt-4">
        <div class="stat-card success">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div>
                <span class="stat-label">Approved Leaves</span>
                <span class="stat-value" id="approvedCount">0</span>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div>
                <span class="stat-label">Pending Leaves</span>
                <span class="stat-value" id="pendingCount">0</span>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div>
                <span class="stat-label">Rejected Leaves</span>
                <span class="stat-value" id="rejectedCount">0</span>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
            <div>
                <span class="stat-label">Total This Month</span>
                <span class="stat-value" id="totalCount">0</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* ===== PREMIUM LEAVE CALENDAR PAGE STYLES ===== */
    .leave-calendar-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .leave-calendar-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeDown 0.4s ease;
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
        font-size: 1.2rem;
    }

    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header Card */
    .header-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-radius: 28px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 28px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        animation: fadeDown 0.5s ease;
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
        transform: translateY(-2px);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .header-icon {
        width: 72px;
        height: 72px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 12px 24px -8px rgba(5, 150, 105, 0.3);
        transition: all 0.3s ease;
    }

    .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .header-card h1 {
        font-size: 34px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0a2e1f, #0f744c);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-card p {
        color: #5a6e63;
        font-size: 1.05rem;
        font-weight: 500;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 16px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 1rem;
        min-height: 50px;
    }

    .btn i {
        font-size: 1.15rem;
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        font-weight: 700;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .btn-outline-primary {
        background: transparent;
        color: #0f744c;
        border: 1.5px solid rgba(16, 185, 129, 0.25);
        font-weight: 700;
        min-height: 40px;
        padding: 8px 18px;
        font-size: 0.95rem;
        border-radius: 12px;
    }

    .btn-outline-primary:hover {
        background: #ecfdf5;
        border-color: #059669;
        transform: translateY(-2px);
    }

    .btn-outline-primary.active {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
    }

    .btn-sm {
        min-height: 40px;
        padding: 8px 18px;
        font-size: 0.95rem;
        border-radius: 12px;
    }

    /* View Toggle Card */
    .view-toggle-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 18px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        padding: 14px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeUp 0.5s ease;
    }

    .view-toggle-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 1rem;
    }

    .breadcrumb-nav .breadcrumb-item {
        color: #6b7280;
        font-weight: 500;
    }

    .breadcrumb-nav .breadcrumb-item a {
        color: #059669;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .breadcrumb-nav .breadcrumb-item a:hover {
        color: #0f744c;
        text-decoration: underline;
    }

    .breadcrumb-nav .breadcrumb-item.active {
        color: #0a2e1f;
        font-weight: 700;
    }

    .breadcrumb-nav .breadcrumb-item+.breadcrumb-item::before {
        content: "/";
        padding: 0 8px;
        color: #9ca3af;
    }

    /* Filter Card */
    .filter-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 22px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.55s ease;
    }

    .filter-header {
        padding: 16px 24px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .filter-header i {
        color: #34d399;
        font-size: 1.1rem;
    }

    .filter-header h6 {
        margin: 0;
        font-weight: 700;
        color: #0a2e1f;
        font-size: 1.05rem;
    }

    .filter-body {
        padding: 20px 24px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .filter-group label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .filter-group label i {
        color: #34d399;
        font-size: 0.9rem;
        cursor: help;
    }

    .filter-group .form-control {
        min-height: 46px;
        padding: 10px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 100%;
    }

    .filter-group .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filter-actions .btn {
        min-height: 46px;
        padding: 0 20px;
        font-size: 0.95rem;
        border-radius: 12px;
    }

    /* Select2 Custom */
    .select2-container--default .select2-selection--single {
        height: 46px;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px;
        padding: 0 16px;
        font-size: 1rem;
        font-weight: 500;
        color: #0a2e1f;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
    }

    .select2-dropdown {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06) !important;
        overflow: hidden;
    }

    .select2-results__option {
        padding: 10px 16px !important;
        font-size: 0.95rem;
        font-weight: 500;
        color: #0a2e1f;
    }

    .select2-results__option--highlighted {
        background: #ecfdf5 !important;
        color: #059669 !important;
    }

    .select2-results__option[aria-selected="true"] {
        background: #d1fae5 !important;
        color: #059669 !important;
    }

    /* Legend Card */
    .legend-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 18px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        padding: 14px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeUp 0.6s ease;
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .legend-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #0a2e1f;
        font-size: 0.95rem;
    }

    .legend-header i {
        color: #34d399;
        font-size: 1rem;
    }

    .legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #374151;
        padding: 4px 12px 4px 8px;
        background: #f8fafc;
        border-radius: 20px;
        border: 1px solid #eef1f5;
        transition: all 0.2s ease;
    }

    .legend-item:hover {
        background: #f0f9f4;
        transform: translateY(-1px);
    }

    .legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        display: inline-block;
        flex-shrink: 0;
    }

    .legend-dot.approved { background: #10b981; }
    .legend-dot.pending { background: #f59e0b; }
    .legend-dot.rejected { background: #ef4444; }
    .legend-dot.multiple { background: #3b82f6; }

    /* Calendar Card */
    .calendar-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 24px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeUp 0.65s ease;
    }

    .calendar-card:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.06);
    }

    .calendar-body {
        padding: 20px;
    }

    /* FullCalendar Custom */
    .fc {
        font-family: inherit;
        font-size: 1rem;
    }

    .fc .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0a2e1f;
    }

    .fc .fc-button {
        background-color: #f8fafc;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
        text-transform: capitalize;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background: linear-gradient(145deg, #34d399, #059669);
        border-color: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
    }

    .fc .fc-button:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .fc-event {
        border: none;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.85rem;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .fc-daygrid-day-frame {
        min-height: 100px;
        background: #ffffff;
        transition: background 0.2s ease;
    }

    .fc-daygrid-day-frame:hover {
        background: #fafefb;
    }

    .fc-daygrid-event {
        margin: 2px 0;
    }

    .fc-daygrid-day-number {
        font-weight: 600;
        padding: 6px !important;
        font-size: 1rem;
        color: #0a2e1f;
    }

    .fc-daygrid-day-number:hover {
        color: #059669;
    }

    .fc-today {
        background-color: rgba(52, 211, 153, 0.06) !important;
    }

    .fc-today .fc-daygrid-day-number {
        color: #059669;
        font-weight: 700;
    }

    .fc-day-other .fc-daygrid-day-number {
        color: #9ca3af;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.7s ease;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        padding: 20px 22px;
        border-radius: 20px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px -10px rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.18);
    }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .stat-card.success .stat-icon {
        background: #d1fae5;
        color: #059669;
    }

    .stat-card.warning .stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-card.danger .stat-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .stat-card.info .stat-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-card .stat-label {
        display: block;
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-card .stat-value {
        display: block;
        font-size: 30px;
        font-weight: 800;
        color: #0a2e1f;
        line-height: 1.2;
        margin-top: 2px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .leave-calendar-page {
            padding: 20px 25px;
        }

        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-group {
            width: 100%;
            justify-content: flex-start;
        }

        .view-toggle-content {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .leave-calendar-page {
            padding: 16px;
        }

        .header-card {
            padding: 20px;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            font-size: 24px;
        }

        .header-card h1 {
            font-size: 26px;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            flex-direction: column;
            width: 100%;
        }

        .filter-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-card .stat-value {
            font-size: 24px;
        }

        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }

        .legend-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .calendar-body {
            padding: 12px;
        }

        .fc .fc-toolbar {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }

        .fc .fc-toolbar-title {
            font-size: 1.2rem;
            text-align: center;
        }

        .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }

        .fc .fc-button {
            font-size: 0.85rem;
            padding: 6px 12px;
        }

        .fc-daygrid-day-frame {
            min-height: 60px;
        }

        .fc-daygrid-day-number {
            font-size: 0.85rem;
            padding: 4px !important;
        }

        .fc-event {
            font-size: 0.75rem;
            padding: 2px 6px;
        }
    }

    @media (max-width: 576px) {
        .leave-calendar-page {
            padding: 12px;
        }

        .header-card {
            padding: 16px;
            border-radius: 20px;
        }

        .header-left {
            gap: 14px;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
            border-radius: 18px;
        }

        .header-card h1 {
            font-size: 22px;
        }

        .header-card p {
            font-size: 0.9rem;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-card {
            padding: 14px;
            gap: 12px;
        }

        .stat-card .stat-value {
            font-size: 20px;
        }

        .stat-card .stat-label {
            font-size: 0.7rem;
        }

        .stat-card .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
            border-radius: 10px;
        }

        .btn {
            font-size: 0.85rem;
            min-height: 40px;
            padding: 0 16px;
        }

        .filter-header {
            padding: 12px 16px;
        }

        .filter-body {
            padding: 14px 16px;
        }

        .view-toggle-card {
            padding: 10px 16px;
        }

        .breadcrumb-nav {
            font-size: 0.9rem;
        }

        .calendar-body {
            padding: 8px;
        }

        .fc-daygrid-day-frame {
            min-height: 50px;
        }

        .fc .fc-button {
            font-size: 0.75rem;
            padding: 4px 10px;
        }

        .fc .fc-toolbar-title {
            font-size: 1rem;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .leave-calendar-page {
        background: linear-gradient(135deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .breadcrumb i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .header-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .header-card h1 {
        background: linear-gradient(135deg, #d9f1e4, #34d399);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .header-card p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .view-toggle-card {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .breadcrumb-nav .breadcrumb-item {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .breadcrumb-nav .breadcrumb-item a {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .breadcrumb-nav .breadcrumb-item.active {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .btn-outline-primary {
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.2);
    }

    html[data-pms-theme="dark"] .btn-outline-primary:hover {
        background: #183026;
        border-color: #34d399;
        color: #6ee7b7;
    }

    html[data-pms-theme="dark"] .btn-outline-primary.active {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        border-color: #059669;
    }

    html[data-pms-theme="dark"] .filter-card {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .filter-header {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .filter-header h6 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .filter-group label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-group .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .filter-group .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .select2-container--default .select2-selection--single {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.15) !important;
    }

    html[data-pms-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .select2-dropdown {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.15) !important;
    }

    html[data-pms-theme="dark"] .select2-results__option {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .select2-results__option--highlighted {
        background: #183026 !important;
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .select2-results__option[aria-selected="true"] {
        background: #183026 !important;
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .legend-card {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .legend-header {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .legend-item {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .legend-item:hover {
        background: #183026;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .calendar-card {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .fc .fc-toolbar-title {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .fc .fc-button {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .fc .fc-button:hover {
        background: #183026;
        border-color: #34d399;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .fc .fc-button-primary:not(:disabled).fc-button-active,
    html[data-pms-theme="dark"] .fc .fc-button-primary:not(:disabled):active {
        background: linear-gradient(145deg, #34d399, #059669);
        border-color: #059669;
        color: white;
    }

    html[data-pms-theme="dark"] .fc-daygrid-day-frame {
        background: #0d1b14;
    }

    html[data-pms-theme="dark"] .fc-daygrid-day-frame:hover {
        background: #102119;
    }

    html[data-pms-theme="dark"] .fc-daygrid-day-number {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .fc-today {
        background-color: rgba(52, 211, 153, 0.08) !important;
    }

    html[data-pms-theme="dark"] .fc-today .fc-daygrid-day-number {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .fc-day-other .fc-daygrid-day-number {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .stat-card {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .stat-card .stat-value {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .stat-card.success .stat-icon {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .stat-card.warning .stat-icon {
        background: #1a1a0d;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .stat-card.danger .stat-icon {
        background: #1a0d0d;
        color: #fca5a5;
    }

    html[data-pms-theme="dark"] .stat-card.info .stat-icon {
        background: #0d1a26;
        color: #60a5fa;
    }

    html[data-pms-theme="dark"] .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1f3d30;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Select2
    $('#employee').select2({
        placeholder: "Search employee...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#employee').parent()
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Calendar initialization
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 700,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        initialDate: new Date(),
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: {
            url: "{{ route('leaves.calendar.data') }}",
            method: 'GET',
            extraParams: function () {
                return {
                    employee: $('#employee').val(),
                    leave_type: $('#leave_type').val(),
                    status: $('#status').val()
                };
            },
            success: function(events) {
                updateStatistics(events);
            },
            failure: function() {
                console.error('Failed to fetch calendar events');
            }
        },
        eventClick: function(info) {
            showLeaveDetails(info.event);
        },
        eventDidMount: function(info) {
            info.el.setAttribute('data-bs-toggle', 'tooltip');
            info.el.setAttribute('title', info.event.title);
            info.el.setAttribute('data-bs-html', 'true');
            new bootstrap.Tooltip(info.el);
        },
        datesSet: function(info) {
            fetchEventsForStatistics(info.start, info.end);
        }
    });

    calendar.render();

    function showLeaveDetails(event) {
        const eventData = event.extendedProps;
        const modalHtml = `
            <div class="modal fade" id="leaveDetailModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Leave Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Employee:</label>
                                    <p class="fw-semibold">${eventData.employee || event.title}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status:</label>
                                    <p><span class="badge bg-${getStatusColor(eventData.status)}">${eventData.status}</span></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Leave Type:</label>
                                    <p class="fw-semibold">${eventData.type || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Duration:</label>
                                    <p class="fw-semibold">${eventData.duration || 'Full Day'}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Reason:</label>
                                    <p>${eventData.reason || 'No reason provided'}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date:</label>
                                    <p class="fw-semibold">${event.start.toLocaleDateString()}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date:</label>
                                    <p class="fw-semibold">${event.end ? event.end.toLocaleDateString() : event.start.toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="${eventData.editUrl || '#'}" class="btn btn-primary">Edit Leave</a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const existingModal = document.getElementById('leaveDetailModal');
        if (existingModal) {
            existingModal.remove();
        }

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('leaveDetailModal'));
        modal.show();
    }

    function getStatusColor(status) {
        switch(status) {
            case 'approved': return 'success';
            case 'pending': return 'warning';
            case 'rejected': return 'danger';
            default: return 'secondary';
        }
    }

    function updateStatistics(events) {
        const counts = {
            approved: 0,
            pending: 0,
            rejected: 0,
            total: events.length
        };

        events.forEach(event => {
            if (event.extendedProps && event.extendedProps.status) {
                const status = event.extendedProps.status.toLowerCase();
                if (counts.hasOwnProperty(status)) {
                    counts[status]++;
                }
            }
        });

        document.getElementById('approvedCount').textContent = counts.approved;
        document.getElementById('pendingCount').textContent = counts.pending;
        document.getElementById('rejectedCount').textContent = counts.rejected;
        document.getElementById('totalCount').textContent = counts.total;
    }

    function fetchEventsForStatistics(start, end) {
        $.ajax({
            url: "{{ route('leaves.calendar.data') }}",
            method: 'GET',
            data: {
                employee: $('#employee').val(),
                leave_type: $('#leave_type').val(),
                status: $('#status').val(),
                start: start.toISOString(),
                end: end.toISOString()
            },
            success: function(events) {
                updateStatistics(events);
            }
        });
    }

    document.getElementById('applyFilters').addEventListener('click', function() {
        calendar.refetchEvents();
    });

    document.getElementById('clearFilters').addEventListener('click', function() {
        $('#employee').val(null).trigger('change');
        $('#leave_type').val('');
        $('#status').val('');
        calendar.refetchEvents();
    });

    $('#employee, #leave_type, #status').on('change', function() {
        calendar.refetchEvents();
    });

    fetchEventsForStatistics(
        calendar.view.activeStart,
        calendar.view.activeEnd
    );
});
</script>
@endsection

