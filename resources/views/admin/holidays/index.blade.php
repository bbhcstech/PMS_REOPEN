@extends('admin.layout.app')
@section('title', 'Holiday List')

@section('content')

<div class="holiday-list-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-calendar-alt"></i> Dashboard / Holidays
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h1>Holiday List {{ $selectedYear }}</h1>
                <p>Manage yearly holidays, weekly offs, bulk uploads, exports, and employee-visible holiday planning</p>
            </div>
        </div>
        <div class="btn-group">
            @php
                $isAdmin = $isAdmin ?? auth()->user()->role === 'admin';
                $exportParams = array_filter(['year' => $selectedYear, 'month' => $selectedMonth]);
                $holidayIndexRoute = $holidayIndexRoute ?? 'holidays.index';
                $holidayCalendarRoute = $holidayCalendarRoute ?? 'holidays.calendar';
                $holidayExportColumns = $isAdmin ? [1, 2, 3, 4] : [0, 1, 2, 3];
            @endphp
            @if($isAdmin)
                <a href="{{ route('holidays.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Holiday
                </a>
                <a href="{{ route('holidays.archive') }}" class="btn btn-light">
                    <i class="fas fa-box-archive"></i> Archived
                    @if(($archivedCount ?? 0) > 0)
                        <span class="archive-count-badge">{{ $archivedCount }}</span>
                    @endif
                </a>
            @endif
            <a href="{{ route($holidayCalendarRoute, ['year' => $selectedYear]) }}" class="btn btn-light">
                <i class="fas fa-calendar-week"></i> Calendar
            </a>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cloud-download-alt"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end export-menu">
                    <li>
                        <button type="button" class="dropdown-item export-item" onclick="exportTo('copy')">
                            <span class="export-icon copy"><i class="fas fa-copy"></i></span>
                            <span>
                                <strong>Copy</strong>
                                <small>Copy to clipboard</small>
                            </span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item export-item" onclick="exportTo('csv')">
                            <span class="export-icon csv"><i class="fas fa-file-csv"></i></span>
                            <span>
                                <strong>CSV</strong>
                                <small>Export table data</small>
                            </span>
                        </button>
                    </li>
                    <li>
                        <a class="dropdown-item export-item" href="{{ route('holidays.export', $exportParams) }}">
                            <span class="export-icon excel"><i class="fas fa-file-excel"></i></span>
                            <span>
                                <strong>Excel</strong>
                                <small>Download full holiday sheet</small>
                            </span>
                        </a>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item export-item" onclick="exportTo('pdf')">
                            <span class="export-icon pdf"><i class="fas fa-file-pdf"></i></span>
                            <span>
                                <strong>PDF</strong>
                                <small>Export printable PDF</small>
                            </span>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button" class="dropdown-item export-item" onclick="exportTo('print')">
                            <span class="export-icon print"><i class="fas fa-print"></i></span>
                            <span>
                                <strong>Print</strong>
                                <small>Open print view</small>
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
            <button type="button" class="btn btn-light" id="holidayScreenshotBtn">
                <i class="fas fa-camera"></i> Screenshot
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Import notes:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach(session('import_errors') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $monthName = $selectedMonth ? \Carbon\Carbon::create()->month($selectedMonth)->format('F') : 'All Months';
    @endphp

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <span class="stat-label">Total Holidays</span>
                <span class="stat-value">{{ $stats['total'] }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon weekly">
                <i class="fas fa-repeat"></i>
            </div>
            <div>
                <span class="stat-label">Weekly Holidays</span>
                <span class="stat-value">{{ $stats['weekly'] }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon special">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <span class="stat-label">Special Holidays</span>
                <span class="stat-value">{{ $stats['special'] }}</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon months">
                <i class="fas fa-th-large"></i>
            </div>
            <div>
                <span class="stat-label">Covered Months</span>
                <span class="stat-value">{{ $stats['months'] }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Actions -->
    <div class="toolbar-card">
        <form method="GET" action="{{ route($holidayIndexRoute) }}" class="toolbar-filter">
            <div class="filter-group">
                <label>Month</label>
                <select name="month" class="form-control">
                    <option value="">All Months</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (int) $selectedMonth === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Year</label>
                <select name="year" class="form-control">
                    @foreach(range(date('Y') - 2, date('Y') + 3) as $y)
                        <option value="{{ $y }}" {{ (int) $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route($holidayIndexRoute) }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
        <div class="toolbar-actions">
            @if($isAdmin)
                <a href="{{ route('holidays.sample') }}" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i> Sample Excel
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayDefaultModal">
                    <i class="fas fa-calendar-week"></i> Weekly Setup
                </button>
            @endif
        </div>
    </div>

    <!-- Bulk Upload Section -->
    @if($isAdmin)
    <div class="upload-grid">
        <div class="upload-card">
            <div class="upload-header">
                <div>
                    <h6>Bulk Upload by Excel</h6>
                    <p>Use the sample file columns: date, occassion, type, department_ids, designation_ids, employment_types, override_existing</p>
                </div>
                <i class="fas fa-file-excel"></i>
            </div>
            <form method="POST" action="{{ route('holidays.import.excel') }}" enctype="multipart/form-data" class="upload-form">
                @csrf
                <input type="file" name="holiday_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-cloud-upload-alt"></i> Import Excel
                </button>
            </form>
        </div>
        <div class="upload-card">
            <div class="upload-header">
                <div>
                    <h6>Upload Holiday Image</h6>
                    <p>The server will read image text automatically when OCR is available, then update matching holiday dates</p>
                </div>
                <i class="fas fa-image"></i>
            </div>
            <form method="POST" action="{{ route('holidays.import.image') }}" enctype="multipart/form-data" class="upload-form">
                @csrf
                <input type="hidden" name="image_year" value="{{ $selectedYear }}">
                <input type="file" name="holiday_image" class="form-control" accept="image/*" required>
                <label class="upload-check">
                    <input type="checkbox" name="override_existing" value="1" checked>
                    Override existing dates
                </label>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-magic"></i> Scan Image
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Main Table Card -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h5 class="mb-0">{{ $monthName }} Holidays</h5>
                    <span class="muted">Every date clearly shows why the holiday is set</span>
                </div>
            </div>
            @if($isAdmin)
            <div class="table-actions">
                <select class="form-control form-control-sm" id="quick-action-type">
                    <option value="">Bulk Action</option>
                    <option value="archive">Archive Selected</option>
                </select>
                <button class="btn btn-warning btn-sm" id="quick-action-apply" disabled>Apply</button>
            </div>
            @endif
        </div>
        <div class="table-body">
            <div class="table-responsive">
                <table id="holidayTable" class="table holiday-table">
                    <thead>
                        <tr>
                            @if($isAdmin)<th width="50"><input type="checkbox" id="selectAll" class="form-check-input"></th>@endif
                            <th><i class="fas fa-calendar-day"></i> Date</th>
                            <th><i class="fas fa-clock"></i> Day</th>
                            <th><i class="fas fa-info-circle"></i> Why Holiday Is Set</th>
                            <th><i class="fas fa-tag"></i> Type</th>
                            @if($isAdmin)<th width="150" class="text-end"><i class="fas fa-cog"></i> Action</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            @php $date = \Carbon\Carbon::parse($holiday->date); @endphp
                            <tr class="holiday-row">
                                @if($isAdmin)<td><input type="checkbox" class="form-check-input holiday-checkbox" value="{{ $holiday->id }}"></td>@endif
                                <td data-order="{{ $date->format('Y-m-d') }}">
                                    <strong class="holiday-date">{{ $date->format('d M Y') }}</strong>
                                </td>
                                <td>
                                    <span class="day-badge">{{ $date->format('l') }}</span>
                                </td>
                                <td>
                                    <div class="holiday-reason">{{ $holiday->occassion ?: $holiday->title }}</div>
                                    <small class="text-muted">{{ $date->format('F') }} organization holiday</small>
                                </td>
                                <td>
                                    <span class="type-badge {{ $holiday->type === 'weekly_holiday' ? 'weekly' : 'special' }}">
                                        {{ $holiday->type === 'weekly_holiday' ? 'Weekly Holiday' : 'Special Holiday' }}
                                    </span>
                                </td>
                                @if($isAdmin)
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('holidays.edit', $holiday->id) }}" class="btn btn-sm btn-outline-secondary action-btn" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('holidays.archive.action', $holiday->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this holiday? It will move to Archived Holidays and can be restored later.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning action-btn" title="Archive">
                                                    <i class="fas fa-box-archive"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 6 : 5 }}" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times fa-4x"></i>
                                        <h5>No Holidays Found</h5>
                                        <p>No holidays found for this period</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        <i class="fas fa-exclamation-circle"></i>
        <span>Data is updated in real-time. Total holidays: {{ $holidays->count() }}</span>
    </div>
</div>

<!-- Weekly Setup Modal -->
@if($isAdmin)
<div class="modal fade" id="holidayDefaultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('holidays.mark') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-week me-2"></i>Weekly Holiday Setup for {{ $selectedYear }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                            <div class="col-md-4">
                                <label class="holiday-check">
                                    <input type="checkbox" name="office_holiday_days[]" value="{{ $i }}">
                                    {{ $day }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Occasion Name</label>
                        <input type="text" class="form-control" name="occassion" placeholder="Weekend, Public Holiday, etc.">
                    </div>
                    <label class="holiday-check">
                        <input type="checkbox" name="override_existing" value="1" checked>
                        Override holidays already set on those dates
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Weekly Holidays</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
    /* ===== PREMIUM HOLIDAY LIST PAGE STYLES ===== */
    .holiday-list-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .holiday-list-page::before {
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
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
        animation: fadeDown 0.4s ease;
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
    }

    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-16px); }
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
        margin-bottom: 32px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        animation: fadeDown 0.5s ease;
        overflow: visible !important;
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
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0a2e1f, #0f744c);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-card p {
        color: #5a6e63;
        font-size: 15px;
        font-weight: 500;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        position: relative;
        z-index: 60;
    }

    .btn-group .dropdown {
        position: relative;
        z-index: 80;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.9rem;
        min-height: 48px;
    }

    .btn i {
        font-size: 1rem;
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

    .btn-light {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-light:hover {
        background: #e6f3ec;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
        border-color: #34d399;
    }

    .archive-count-badge {
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border-radius: 999px;
        background: #f59e0b;
        color: #fff;
        font-size: 0.76rem;
        font-weight: 800;
        box-shadow: 0 6px 14px rgba(245, 158, 11, 0.28);
    }

    /* Export Dropdown - Premium Styling */
    .holiday-list-page .dropdown-menu {
        border: 1px solid rgba(16, 185, 129, 0.18);
        border-radius: 18px;
        padding: 10px;
        background: #fff;
        box-shadow: 0 24px 48px -14px rgba(15, 23, 42, 0.18);
        min-width: 270px;
        margin-top: 10px !important;
        overflow: visible;
        z-index: 9999 !important;
    }

    .holiday-list-page .export-item {
        width: 100%;
        min-height: 58px;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px 14px;
        border: 0;
        border-radius: 14px;
        background: transparent;
        color: #374151;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .holiday-list-page .export-item:hover {
        background: #ecfdf5;
        color: #059669;
        transform: translateX(4px);
    }

    .holiday-list-page .export-item span:last-child {
        display: grid;
        gap: 1px;
        text-align: left;
    }

    .holiday-list-page .export-item strong {
        font-size: 0.96rem;
        line-height: 1.2;
        font-weight: 800;
        color: inherit;
    }

    .holiday-list-page .export-item small {
        color: #667085;
        font-size: 0.78rem;
        font-weight: 650;
        transition: color 0.25s ease;
    }

    .holiday-list-page .export-item:hover small {
        color: #059669;
    }

    .holiday-list-page .export-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        border-radius: 14px;
        font-size: 1.15rem;
        transition: all 0.25s ease;
    }

    .holiday-list-page .export-item:hover .export-icon {
        transform: scale(1.05);
    }

    .holiday-list-page .export-icon.copy { background: #dbeafe; color: #2563eb; }
    .holiday-list-page .export-icon.csv { background: #dcfce7; color: #16a34a; }
    .holiday-list-page .export-icon.excel { background: #d1fae5; color: #047857; }
    .holiday-list-page .export-icon.pdf { background: #fee2e2; color: #dc2626; }
    .holiday-list-page .export-icon.print { background: #e0f2fe; color: #0284c7; }

    .holiday-list-page .dropdown-divider {
        margin: 6px 0;
        border-color: rgba(16, 185, 129, 0.08);
    }

    .holiday-list-page .dataTables_wrapper .dt-buttons {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin: 0 0 16px;
        padding: 8px;
        border: 1px solid rgba(16, 185, 129, 0.14);
        border-radius: 16px;
        background: #f7fcf9;
    }

    .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 !important;
        padding: 9px 16px !important;
        border: 1px solid rgba(16, 185, 129, 0.18) !important;
        border-radius: 12px !important;
        background: #fff !important;
        color: #0f744c !important;
        box-shadow: 0 4px 12px -10px rgba(5, 150, 105, 0.7);
        font-size: 0.95rem !important;
        font-weight: 750 !important;
        line-height: 1.2 !important;
        transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button span {
        display: inline-flex;
        align-items: center;
    }

    .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button i {
        width: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-right: 8px;
    }

    .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button:hover,
    .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button:focus {
        border-color: #10b981 !important;
        background: linear-gradient(145deg, #34d399, #059669) !important;
        color: #fff !important;
        box-shadow: 0 9px 20px -12px rgba(5, 150, 105, 0.8);
        transform: translateY(-1px);
    }

    .holiday-list-page .dataTables_wrapper .buttons-excel { background: #ecfdf5 !important; }
    .holiday-list-page .dataTables_wrapper .buttons-csv { background: #f0fdf4 !important; }
    .holiday-list-page .dataTables_wrapper .buttons-pdf {
        color: #b91c1c !important;
        background: #fff7f7 !important;
        border-color: rgba(239, 68, 68, 0.18) !important;
    }
    .holiday-list-page .dataTables_wrapper .buttons-print {
        color: #315f75 !important;
        background: #f4fbff !important;
        border-color: rgba(49, 95, 117, 0.18) !important;
    }

    .btn-success {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
    }

    .btn-outline-primary {
        background: transparent;
        color: #059669;
        border: 1.5px solid #34d399;
    }

    .btn-outline-primary:hover {
        background: #ecfdf5;
        border-color: #059669;
        transform: translateY(-2px);
    }

    .btn-outline-warning {
        background: transparent;
        color: #d97706;
        border: 1.5px solid #fbbf24;
    }

    .btn-outline-warning:hover {
        background: #fffbeb;
        border-color: #d97706;
        color: #92400e;
        transform: translateY(-2px);
    }

    .btn-danger {
        background: linear-gradient(145deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(220, 38, 38, 0.35);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(220, 38, 38, 0.45);
    }

    .btn-sm {
        padding: 6px 14px;
        min-height: 36px;
        font-size: 0.8rem;
    }

    /* Alerts */
    .alert {
        border-radius: 18px;
        border: none;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        animation: slideDown 0.4s ease;
        position: relative;
        z-index: 1;
    }

    .alert-success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert i {
        font-size: 1.25rem;
        margin-top: 2px;
    }

    .alert ul {
        margin-bottom: 0;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.6s ease;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 24px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        display: flex;
        gap: 18px;
        align-items: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:nth-child(1)::after { background: linear-gradient(90deg, #34d399, #059669); }
    .stat-card:nth-child(2)::after { background: linear-gradient(90deg, #3b82f6, #2563eb); }
    .stat-card:nth-child(3)::after { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card:nth-child(4)::after { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.total { background: linear-gradient(145deg, #d1fae5, #a7f3d0); color: #059669; }
    .stat-icon.weekly { background: linear-gradient(145deg, #dbeafe, #bfdbfe); color: #2563eb; }
    .stat-icon.special { background: linear-gradient(145deg, #fef3c7, #fde68a); color: #d97706; }
    .stat-icon.months { background: linear-gradient(145deg, #ede9fe, #c4b5fd); color: #7c3aed; }

    .stat-label {
        display: block;
        color: #6b7280;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        display: block;
        color: #0a2e1f;
        font-size: 32px;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Toolbar Card */
    .toolbar-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        margin-bottom: 28px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
        animation: fadeUp 0.65s ease;
    }

    .toolbar-card:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.06);
    }

    .toolbar-filter {
        display: flex;
        align-items: flex-end;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .filter-group label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #8ba198;
    }

    .filter-group .form-control {
        min-height: 44px;
        padding: 8px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 180px;
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
        padding-top: 2px;
    }

    .filter-actions .btn {
        min-height: 44px;
        padding: 8px 20px;
        font-size: 0.85rem;
    }

    .toolbar-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .toolbar-actions .btn {
        min-height: 44px;
        padding: 8px 20px;
        font-size: 0.85rem;
    }

    /* Upload Grid */
    .upload-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 28px;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.7s ease;
    }

    .upload-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        padding: 22px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .upload-card:hover {
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.06);
        transform: translateY(-2px);
    }

    .upload-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
    }

    .upload-header h6 {
        font-weight: 700;
        color: #0a2e1f;
        margin: 0;
        font-size: 0.95rem;
    }

    .upload-header p {
        margin: 4px 0 0;
        color: #8ba198;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .upload-header i {
        font-size: 2rem;
        color: #34d399;
        flex-shrink: 0;
    }

    .upload-form {
        display: grid;
        gap: 12px;
    }

    .upload-form .form-control {
        min-height: 44px;
        padding: 8px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 100%;
    }

    .upload-form .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    .upload-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        cursor: pointer;
    }

    .upload-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #059669;
        cursor: pointer;
    }

    .upload-form .btn {
        min-height: 44px;
        justify-content: center;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.75s ease;
    }

    .table-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
    }

    .table-header {
        padding: 20px 28px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .table-title-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .table-title h5 {
        font-weight: 700;
        color: #0a2e1f;
        margin: 0;
    }

    .table-title .muted {
        font-size: 0.75rem;
        color: #8ba198;
        font-weight: 500;
    }

    .table-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .table-actions .form-control {
        min-height: 36px;
        padding: 4px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #0a2e1f;
        background: #ffffff;
        transition: all 0.2s ease;
        width: 160px;
    }

    .table-actions .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
        outline: none;
    }

    .table-body {
        padding: 20px 28px 28px 28px;
    }

    /* Table Styling */
    .holiday-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0 4px;
        margin: 0;
        font-size: 0.9rem;
    }

    .holiday-table thead th {
        padding: 12px 16px;
        background: #f8fafc !important;
        color: #5a6e63;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .holiday-table thead th i {
        margin-right: 6px;
        color: #34d399;
        font-size: 0.7rem;
    }

    .holiday-table tbody td {
        padding: 14px 16px;
        background: white;
        border-top: 1px solid rgba(16, 185, 129, 0.06);
        border-bottom: 1px solid rgba(16, 185, 129, 0.06);
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
    }

    .holiday-table tbody td:first-child {
        border-left: 1px solid rgba(16, 185, 129, 0.06);
        border-radius: 12px 0 0 12px;
    }

    .holiday-table tbody td:last-child {
        border-right: 1px solid rgba(16, 185, 129, 0.06);
        border-radius: 0 12px 12px 0;
    }

    .holiday-table tbody tr {
        transition: all 0.2s ease;
    }

    .holiday-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(16, 185, 129, 0.12);
    }

    .holiday-date {
        color: #0a2e1f;
        font-weight: 700;
    }

    .day-badge {
        display: inline-flex;
        padding: 6px 14px;
        border-radius: 20px;
        background: #eef2ff;
        color: #3730a3;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .holiday-reason {
        font-weight: 700;
        color: #0a2e1f;
    }

    .holiday-reason + .text-muted {
        font-size: 0.75rem;
        color: #8ba198;
        font-weight: 500;
        display: block;
        margin-top: 2px;
    }

    .type-badge {
        display: inline-flex;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .type-badge.weekly {
        background: #d1fae5;
        color: #047857;
    }

    .type-badge.special {
        background: #dbeafe;
        color: #1d4ed8;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 6px;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        background: white;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .action-btn.btn-outline-secondary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #1e293b;
    }

    .action-btn.btn-outline-danger:hover {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #dc2626;
    }

    .action-btn i {
        font-size: 0.8rem;
    }

    /* Checkboxes */
    .form-check-input {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d9e6;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #059669;
        border-color: #059669;
    }

    /* Holiday Check */
    .holiday-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        cursor: pointer;
        padding: 6px 12px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        width: 100%;
    }

    .holiday-check:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .holiday-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #059669;
        cursor: pointer;
    }

    /* Footer Note */
    .footer-note {
        margin-top: 24px;
        padding: 14px 24px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 500;
        position: relative;
        z-index: 1;
        animation: fadeUp 0.8s ease;
    }

    .footer-note i {
        color: #34d399;
        font-size: 1rem;
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-state i {
        color: #a7f3d0;
        margin-bottom: 16px;
    }

    .empty-state h5 {
        color: #0f744c;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #8ba198;
        margin-bottom: 0;
    }

    /* Modal */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12);
        overflow: hidden;
    }

    .modal-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
    }

    .modal-header .modal-title {
        font-weight: 700;
        color: #0a2e1f;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
        gap: 10px;
    }

    .modal-footer .btn {
        min-height: 44px;
        padding: 8px 24px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .upload-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .holiday-list-page {
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

        .toolbar-card {
            flex-direction: column;
            align-items: stretch;
        }

        .toolbar-filter {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group .form-control {
            width: 100%;
        }

        .filter-actions {
            flex-direction: row;
        }

        .toolbar-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .holiday-list-page {
            padding: 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-value {
            font-size: 24px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            font-size: 18px;
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
            font-size: 24px;
        }

        .table-body {
            padding: 12px;
        }

        .table-header {
            padding: 14px 16px;
            flex-direction: column;
            align-items: flex-start;
        }

        .table-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .table-actions .form-control {
            width: 100%;
        }

        .holiday-list-page .dataTables_wrapper .dt-buttons {
            width: 100%;
        }

        .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button {
            flex: 1 1 120px;
        }

        .holiday-table tbody td {
            padding: 10px 12px;
            font-size: 0.8rem;
        }

        .holiday-table thead th {
            padding: 8px 12px;
            font-size: 0.65rem;
        }

        .day-badge {
            font-size: 0.7rem;
            padding: 4px 10px;
        }

        .type-badge {
            font-size: 0.65rem;
            padding: 4px 10px;
        }

        .action-btn {
            width: 30px;
            height: 30px;
        }

        .upload-card {
            padding: 16px;
        }

        .toolbar-card {
            padding: 16px;
        }
    }

    @media (max-width: 576px) {
        .holiday-list-page {
            padding: 12px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .stat-card {
            padding: 12px;
            gap: 12px;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 16px;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 20px;
        }

        .stat-label {
            font-size: 0.65rem;
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
            font-size: 20px;
        }

        .header-card p {
            font-size: 13px;
        }

        .table-card {
            border-radius: 20px;
        }

        .table-body {
            padding: 8px;
        }

        .holiday-table tbody td {
            padding: 8px 10px;
            font-size: 0.75rem;
        }

        .holiday-date {
            font-size: 0.8rem;
        }

        .filter-actions {
            flex-direction: column;
            width: 100%;
        }

        .filter-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .toolbar-actions {
            flex-direction: column;
            width: 100%;
        }

        .toolbar-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .upload-form .btn {
            width: 100%;
            justify-content: center;
        }

        .modal-body .row .col-md-4 {
            margin-bottom: 6px;
        }
    }
</style>

<style>
    /* Larger typography for better readability */
    .holiday-list-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .holiday-list-page .breadcrumb {
        font-size: 1rem;
    }

    .holiday-list-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .holiday-list-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .holiday-list-page .btn {
        font-size: 1rem;
        padding: 13px 24px;
    }

    .holiday-list-page .stat-value {
        font-size: 36px;
    }

    .holiday-list-page .stat-label {
        font-size: 0.8rem;
    }

    .holiday-list-page .holiday-table thead th {
        font-size: 0.75rem;
    }

    .holiday-list-page .holiday-table tbody td {
        font-size: 0.95rem;
    }

    .holiday-list-page .upload-header h6 {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .holiday-list-page {
            font-size: 15px;
        }

        .holiday-list-page .header-card h1 {
            font-size: 28px;
        }

        .holiday-list-page .header-card p {
            font-size: 15px;
        }

        .holiday-list-page .stat-value {
            font-size: 28px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .holiday-list-page {
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

    html[data-pms-theme="dark"] .btn-light {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-light:hover {
        background: #1f3d30;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .holiday-list-page .dropdown-menu {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, 0.55);
    }

    html[data-pms-theme="dark"] .holiday-list-page .export-item {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .holiday-list-page .export-item:hover {
        background: #102119;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .holiday-list-page .export-item small {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .holiday-list-page .export-item:hover small {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .holiday-list-page .dataTables_wrapper .dt-buttons {
        border-color: rgba(122, 240, 181, 0.16);
        background: #142a20;
    }

    html[data-pms-theme="dark"] .holiday-list-page .dataTables_wrapper .dt-buttons .dt-button {
        border-color: rgba(122, 240, 181, 0.18) !important;
        background: #183026 !important;
        color: #d9f1e4 !important;
    }

    html[data-pms-theme="dark"] .stat-card {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .stat-value {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .stat-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .toolbar-card {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
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

    html[data-pms-theme="dark"] .filter-group label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .upload-card {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .upload-header h6 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .upload-header p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .upload-form .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .upload-form .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .upload-check {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .table-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .table-header {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .table-title h5 {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .holiday-table thead th {
        background: #0d1b14 !important;
        color: #8ba198 !important;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .holiday-table tbody td {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .holiday-table tbody tr:hover td {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .holiday-date {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .holiday-reason {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .day-badge {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .type-badge.weekly {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .type-badge.special {
        background: #1a2638;
        color: #60a5fa;
    }

    html[data-pms-theme="dark"] .action-btn {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.1);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .action-btn.btn-outline-secondary:hover {
        background: #183026;
        border-color: #34d399;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .action-btn.btn-outline-danger:hover {
        background: #1a0d0d;
        border-color: #ef4444;
        color: #fca5a5;
    }

    html[data-pms-theme="dark"] .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1f3d30;
    }

    html[data-pms-theme="dark"] .btn-outline-primary {
        color: #34d399;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .btn-outline-primary:hover {
        background: #183026;
        color: #6ee7b7;
        border-color: #6ee7b7;
    }

    html[data-pms-theme="dark"] .footer-note {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.08);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .footer-note i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .modal-content {
        background: #102119;
    }

    html[data-pms-theme="dark"] .modal-header {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .modal-header .modal-title {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .modal-body .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .modal-body .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .modal-body .form-label {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .modal-footer {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .holiday-check {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .holiday-check:hover {
        background: #102119;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .table-actions .form-control {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.12);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .table-actions .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.08);
    }

    html[data-pms-theme="dark"] .empty-state h5 {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .empty-state p {
        color: #8ba198;
    }
</style>

@push('js')
<script>
    $(function () {
        // Initialize DataTable
        const exportColumns = @json($holidayExportColumns);
        const table = $('#holidayTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="fas fa-copy"></i><span>Copy</span>',
                    exportOptions: { columns: exportColumns }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i><span>CSV</span>',
                    filename: 'holiday-list-{{ $selectedYear }}',
                    exportOptions: { columns: exportColumns }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i><span>Excel</span>',
                    filename: 'holiday-list-{{ $selectedYear }}',
                    exportOptions: { columns: exportColumns }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i><span>PDF</span>',
                    filename: 'holiday-list-{{ $selectedYear }}',
                    exportOptions: { columns: exportColumns }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i><span>Print</span>',
                    exportOptions: { columns: exportColumns }
                }
            ],
            responsive: true,
            pageLength: 25,
            order: [[{{ $isAdmin ? 1 : 0 }}, 'asc']],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search holidays...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        // Export function for buttons
        window.exportTo = function(format) {
            const buttonMap = {
                copy: 0,
                csv: 1,
                excel: 2,
                pdf: 3,
                print: 4
            };

            if (buttonMap[format] === undefined) {
                return;
            }

            table.button(buttonMap[format]).trigger();
        };

        // Select All
        $('#selectAll').on('change', function () {
            $('.holiday-checkbox').prop('checked', this.checked);
            toggleApply();
        });

        $(document).on('change', '.holiday-checkbox, #quick-action-type', toggleApply);

        function toggleApply() {
            $('#quick-action-apply').prop('disabled', !($('.holiday-checkbox:checked').length && $('#quick-action-type').val()));
        }

        // Quick Action Apply
        $('#quick-action-apply').on('click', function () {
            const ids = $('.holiday-checkbox:checked').map(function () { return this.value; }).get();
            const action = $('#quick-action-type').val();
            if (!ids.length || !action || !confirm('Archive selected holidays? They can be restored from Archived Holidays.')) return;

            const btn = $(this);
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Applying...');
            btn.prop('disabled', true);

            $.post('{{ route("holiday.bulkAction") }}', {
                _token: '{{ csrf_token() }}',
                holiday_ids: ids,
                action: action
            })
            .done(function(response) {
                alert(response.message);
                location.reload();
            })
            .fail(function() {
                alert('Something went wrong. Please try again.');
            })
            .always(function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            });
        });

        // Screenshot functionality
        $('#holidayScreenshotBtn').on('click', function () {
            @php
                $snapshotRows = $holidays->map(function ($holiday) {
                    return [
                        'date' => \Carbon\Carbon::parse($holiday->date)->format('d M Y'),
                        'day' => \Carbon\Carbon::parse($holiday->date)->format('l'),
                        'title' => $holiday->occassion ?: $holiday->title,
                        'type' => $holiday->type === 'weekly_holiday' ? 'Weekly' : 'Special',
                    ];
                })->values();
            @endphp
            const rows = @json($snapshotRows);
            downloadHolidaySnapshot('holiday-list-{{ $selectedYear }}.png', 'Holiday List {{ $selectedYear }}', rows);
        });

        function downloadHolidaySnapshot(filename, title, rows) {
            const width = 1200, rowHeight = 44, height = Math.max(420, 160 + rows.length * rowHeight);
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            // Background
            ctx.fillStyle = '#f6f8fb';
            ctx.fillRect(0, 0, width, height);

            // Header gradient
            const gradient = ctx.createLinearGradient(0, 0, width, 0);
            gradient.addColorStop(0, '#0f766e');
            gradient.addColorStop(0.58, '#2563eb');
            gradient.addColorStop(1, '#7c3aed');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, width, 110);

            // Header text
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 34px Arial';
            ctx.fillText(title, 36, 62);

            ctx.font = '16px Arial';
            ctx.fillText('{{ $monthName }} | Total: {{ $holidays->count() }}', 38, 90);

            // Column headers
            ctx.fillStyle = '#172033';
            ctx.font = 'bold 16px Arial';
            const headers = ['Date', 'Day', 'Reason', 'Type'];
            const positions = [40, 190, 360, 980];
            headers.forEach((head, i) => ctx.fillText(head, positions[i], 150));

            // Rows
            ctx.font = '14px Arial';
            rows.forEach((row, index) => {
                const y = 184 + index * rowHeight;
                ctx.fillStyle = index % 2 ? '#ffffff' : '#eef2ff';
                ctx.fillRect(30, y - 24, 1140, 36);
                ctx.fillStyle = '#172033';
                ctx.fillText(row.date, 40, y);
                ctx.fillText(row.day, 190, y);
                ctx.fillText(String(row.title).slice(0, 70), 360, y);
                ctx.fillText(row.type, 980, y);
            });

            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    });
</script>
@endpush

@endsection
