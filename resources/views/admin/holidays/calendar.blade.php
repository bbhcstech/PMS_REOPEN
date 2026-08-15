@extends('admin.layout.app')

@section('title', 'Holiday Calendar')

@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
    $snapshotRows = $holidayRows->map(function ($holiday) {
        return [
            'date' => \Carbon\Carbon::parse($holiday->date)->format('d M Y'),
            'day' => \Carbon\Carbon::parse($holiday->date)->format('l'),
            'title' => $holiday->occassion ?: $holiday->title,
            'type' => $holiday->type === 'weekly_holiday' ? 'Weekly' : 'Special',
        ];
    })->values();
@endphp

<div class="holiday-calendar-page">
    <div class="holiday-calendar-shell">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>

        <div class="content-wrapper">
            <!-- Breadcrumb -->
            <div class="breadcrumb-custom">
                <i class="fas fa-calendar-alt"></i>
                <a href="{{ route('admin.settings.index') }}">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.settings.index') }}">Settings</a>
                <span>/</span>
                <a href="{{ route('holidays.index') }}">Holidays</a>
                <span>/</span>
                <span>Calendar View</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header mb-4">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="header-title">
                        <h1>Holiday Calendar {{ $selectedYear }}</h1>
                        <p>See the exact date, day, and reason for every organization holiday.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                    <form method="GET" action="{{ route('holidays.calendar') }}" class="d-flex align-items-center gap-2">
                        <div class="input-group-custom" style="min-width: 120px;">
                            <select name="year" class="form-select border-0 bg-transparent fw-bold" style="height: 44px; color: #0a2e1f;" onchange="this.form.submit()">
                                @foreach(range(date('Y') - 2, date('Y') + 3) as $year)
                                    <option value="{{ $year }}" {{ (int) $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if($isAdmin)
                        <a href="{{ route('holidays.create') }}" class="btn-save-address text-decoration-none py-2.5 px-4 d-inline-flex align-items-center gap-2" style="height: 44px;">
                            <i class="fas fa-plus-circle"></i> Add Holiday
                        </a>
                    @endif

                    <a href="{{ $isAdmin ? route('holidays.index', ['year' => $selectedYear]) : route('employee.holidays', ['year' => $selectedYear]) }}" class="btn-action-pill text-decoration-none">
                        <i class="fas fa-list-ul me-1.5"></i> List View
                    </a>
                    <a href="{{ route('holidays.export', ['year' => $selectedYear]) }}" class="btn-action-pill text-decoration-none">
                        <i class="fas fa-file-excel me-1.5"></i> Export
                    </a>
                    <button type="button" class="btn-action-pill" id="calendarScreenshotBtn">
                        <i class="fas fa-camera me-1.5"></i> Screenshot
                    </button>
                </div>
            </div>

            <!-- Executive Summary Stats Grid -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Total Holidays</h6>
                        <h3>{{ $stats['total'] }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon special">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Special Holidays</h6>
                        <h3>{{ $stats['special'] }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon weekly">
                        <i class="fas fa-repeat"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Weekly Holidays</h6>
                        <h3>{{ $stats['weekly'] }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon months">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Covered Months</h6>
                        <h3>{{ $stats['months'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- FullCalendar Container Card -->
            <div class="address-card-elevated p-4 p-md-4">
                <div id="calendar"></div>
            </div>

            <!-- Holiday Details Interactive Drawer/Panel -->
            <div class="holiday-details-panel d-none mt-4 p-4 address-card-elevated" id="holidayDetailsCard" style="border-left: 5px solid #059669 !important;">
                <div id="holidayDetailsContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    .holiday-calendar-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .holiday-calendar-shell {
        position: relative;
        max-width: 1500px;
        margin: 0 auto;
    }

    /* Ambient Orbs */
    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(52, 211, 153, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    @keyframes orbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -30px) scale(1.05); }
        66% { transform: translate(-30px, 40px) scale(0.95); }
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
    }

    .breadcrumb-custom a {
        color: #059669;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-custom a:hover {
        color: #047857;
    }

    /* ===== HEADER CARD ===== */
    .branches-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-left-box {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .header-icon-badge {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.35);
        flex-shrink: 0;
    }

    .header-title h1 {
        font-size: 1.95rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0a2e1f, #059669, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin: 0 0 0.2rem 0;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
    }

    .btn-action-pill {
        background-color: #ecfdf5;
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #059669 !important;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 40px;
        padding: 0.6rem 1.3rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.1);
        display: inline-flex;
        align-items: center;
        height: 44px;
        cursor: pointer;
    }

    .btn-action-pill:hover {
        background-color: #d1fae5;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.2);
    }

    .btn-save-address {
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.9rem;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }

    .btn-save-address:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(5, 150, 105, 0.45);
        color: white !important;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
    }

    .stat-card,
    .holiday-calendar-page .stat-card,
    .holiday-calendar-page .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem;
        border: 1px solid rgba(16, 185, 129, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #0a2e1f !important;
    }

    .holiday-calendar-page .stat-card:first-of-type *,
    .holiday-calendar-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .holiday-calendar-page .stat-card h3,
    .holiday-calendar-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .holiday-calendar-page .stat-card h6,
    .holiday-calendar-page .stat-card span,
    .holiday-calendar-page .stat-card:first-of-type span,
    .holiday-calendar-page .stat-card:first-of-type h6 {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #34d399, #059669);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.total,
    .holiday-calendar-page .stat-card:first-of-type .stat-icon.total {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.special,
    .holiday-calendar-page .stat-card .stat-icon.special {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.weekly,
    .holiday-calendar-page .stat-card .stat-icon.weekly {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.months,
    .holiday-calendar-page .stat-card .stat-icon.months {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .stat-info h6 {
        font-size: 0.72rem;
        color: #64748b;
        margin: 0 0 0.2rem 0;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .stat-info h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== FULLCALENDAR STYLING ===== */
    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
    }

    .fc .fc-toolbar-title {
        font-weight: 800;
        font-size: 1.6rem;
        color: #0a2e1f;
        letter-spacing: -0.02em;
    }

    .fc .fc-button-primary {
        background: linear-gradient(145deg, #34d399, #059669) !important;
        border: none !important;
        border-radius: 30px !important;
        font-weight: 700 !important;
        padding: 0.5rem 1.2rem !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2) !important;
        transition: all 0.25s ease !important;
    }

    .fc .fc-button-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.3) !important;
    }

    .fc .fc-button-primary:disabled {
        opacity: 0.6;
    }

    .fc .fc-button-group > .fc-button {
        border-radius: 0 !important;
    }

    .fc .fc-button-group > .fc-button:first-child {
        border-top-left-radius: 30px !important;
        border-bottom-left-radius: 30px !important;
    }

    .fc .fc-button-group > .fc-button:last-child {
        border-top-right-radius: 30px !important;
        border-bottom-right-radius: 30px !important;
    }

    .fc-col-header-cell {
        background: #f0fdf4;
        padding: 12px 0;
        font-weight: 800;
        color: #059669;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        border-color: rgba(16, 185, 129, 0.15) !important;
    }

    .fc-theme-standard td, .fc-theme-standard th {
        border-color: rgba(16, 185, 129, 0.12) !important;
    }

    .fc-daygrid-day-number {
        font-weight: 800;
        color: #0a2e1f;
        padding: 8px 12px !important;
        font-size: 0.9rem;
    }

    .fc-event {
        border: none !important;
        border-radius: 12px !important;
        padding: 4px 8px !important;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15) !important;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #065f46 !important;
        transition: transform 0.2s ease !important;
    }

    .fc-event:hover {
        transform: scale(1.03) translateY(-1px) !important;
    }

    .holiday-detail-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0a2e1f;
        margin-bottom: 8px;
    }

    .holiday-pill {
        display: inline-flex;
        padding: 6px 14px;
        border-radius: 40px;
        font-weight: 800;
        font-size: 0.82rem;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid rgba(5, 150, 105, 0.2);
    }

    .holiday-pill.weekly {
        background: #e0f2fe;
        color: #0284c7;
        border-color: rgba(2, 132, 199, 0.2);
    }

    .input-group-custom {
        border-radius: 40px;
        border: 1px solid rgba(16, 185, 129, 0.25);
        background-color: #ffffff;
        transition: all 0.25s ease;
        overflow: hidden;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .holiday-calendar-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
        const events = {!! $holidays !!};

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: '{{ $selectedYear }}-01-01',
            height: 750,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events,
            eventContent: function(arg) {
                const reason = arg.event.extendedProps.description || arg.event.title;
                return { html: `<div title="${escapeHtml(reason)}" class="p-1"><strong>${escapeHtml(arg.event.title)}</strong><br><small style="opacity: 0.85;">${escapeHtml(reason)}</small></div>` };
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const type = info.event.extendedProps.type === 'weekly_holiday' ? 'Weekly Holiday' : 'Special Holiday';
                const details = `
                    <div class="holiday-detail-title">${escapeHtml(info.event.title)}</div>
                    <p class="mb-2 text-secondary"><strong>Date:</strong> ${escapeHtml(info.event.extendedProps.date_label)}</p>
                    <p class="mb-3 text-secondary"><strong>Why holiday is set:</strong> ${escapeHtml(info.event.extendedProps.description || info.event.title)}</p>
                    <span class="holiday-pill ${info.event.extendedProps.type === 'weekly_holiday' ? 'weekly' : ''}">${type}</span>
                    ${isAdmin && info.event.url ? `<div class="mt-3"><a href="${info.event.url}" class="btn-save-address text-decoration-none py-2 px-3.5 d-inline-flex align-items-center gap-1.5"><i class="fas fa-edit me-1"></i>Edit Holiday</a></div>` : ''}
                `;
                document.getElementById('holidayDetailsContent').innerHTML = details;
                document.getElementById('holidayDetailsCard').classList.remove('d-none');
                document.getElementById('holidayDetailsCard').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        calendar.render();

        document.getElementById('calendarScreenshotBtn')?.addEventListener('click', function () {
            const rows = @json($snapshotRows);
            downloadCalendarSnapshot('holiday-calendar-{{ $selectedYear }}.png', rows);
        });

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, match => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[match]));
        }

        function downloadCalendarSnapshot(filename, rows) {
            const width = 1200, rowHeight = 42, height = Math.max(420, 150 + rows.length * rowHeight);
            const canvas = document.createElement('canvas');
            canvas.width = width; canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#f0fdf4'; ctx.fillRect(0, 0, width, height);
            ctx.fillStyle = '#059669'; ctx.fillRect(0, 0, width, 100);
            ctx.fillStyle = '#fff'; ctx.font = 'bold 34px Arial'; ctx.fillText('Holiday Calendar {{ $selectedYear }}', 36, 60);
            ctx.fillStyle = '#0a2e1f'; ctx.font = 'bold 16px Arial';
            ['Date', 'Day', 'Reason', 'Type'].forEach((head, i) => ctx.fillText(head, [40, 190, 360, 980][i], 135));
            ctx.font = '14px Arial';
            rows.forEach((row, index) => {
                const y = 168 + index * rowHeight;
                ctx.fillStyle = index % 2 ? '#ffffff' : '#ecfdf5'; ctx.fillRect(30, y - 24, 1140, 34);
                ctx.fillStyle = '#0a2e1f';
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
@endsection
