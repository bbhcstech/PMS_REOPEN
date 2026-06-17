@extends('admin.layout.app')

@section('title', 'Holiday Calendar')

@section('content')
<main class="main">
    <div class="holiday-calendar-page container-xxl py-4">
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

        <section class="holiday-calendar-hero mb-4">
            <div>
                <span><i class="bi bi-calendar3"></i> Full Calendar View</span>
                <h1>Holiday Calendar {{ $selectedYear }}</h1>
                <p>See the exact date, day, and reason for every organization holiday.</p>
            </div>
            <div class="calendar-actions">
                <form method="GET" action="{{ route('holidays.calendar') }}" class="d-flex gap-2">
                    <select name="year" class="form-select">
                        @foreach(range(date('Y') - 2, date('Y') + 3) as $year)
                            <option value="{{ $year }}" {{ (int) $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-light fw-bold" type="submit">Go</button>
                </form>
                @if($isAdmin)
                    <a href="{{ route('holidays.create') }}" class="btn btn-outline-light fw-bold"><i class="bi bi-plus-circle me-1"></i>Add</a>
                @endif
                <a href="{{ $isAdmin ? route('holidays.index', ['year' => $selectedYear]) : route('employee.holidays', ['year' => $selectedYear]) }}" class="btn btn-outline-light fw-bold"><i class="bi bi-list-ul me-1"></i>List</a>
                <a href="{{ route('holidays.export', ['year' => $selectedYear]) }}" class="btn btn-outline-light fw-bold"><i class="bi bi-file-earmark-excel me-1"></i>Export</a>
                <button type="button" class="btn btn-outline-light fw-bold" id="calendarScreenshotBtn"><i class="bi bi-camera me-1"></i>Screenshot</button>
            </div>
        </section>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="calendar-stat"><small>Total</small><strong>{{ $stats['total'] }}</strong></div></div>
            <div class="col-md-3"><div class="calendar-stat"><small>Special</small><strong>{{ $stats['special'] }}</strong></div></div>
            <div class="col-md-3"><div class="calendar-stat"><small>Weekly</small><strong>{{ $stats['weekly'] }}</strong></div></div>
            <div class="col-md-3"><div class="calendar-stat"><small>Months</small><strong>{{ $stats['months'] }}</strong></div></div>
        </div>

        <div class="holiday-calendar-shell">
            <div id="calendar"></div>
        </div>

        <div class="holiday-details-panel d-none" id="holidayDetailsCard">
            <div id="holidayDetailsContent"></div>
        </div>
    </div>
</main>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    .holiday-calendar-hero { display: flex; justify-content: space-between; align-items: center; gap: 22px; min-height: 220px; padding: 30px; border-radius: 18px; color: #fff; background: linear-gradient(135deg, #0f766e, #2563eb 58%, #7c3aed); box-shadow: 0 22px 55px rgba(31,41,55,.18); animation: holidayRise .5s ease both; }
    .holiday-calendar-hero span { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.18); font-weight: 800; }
    .holiday-calendar-hero h1 { color: #fff; margin: 12px 0 8px; font-size: clamp(2rem, 4vw, 3.4rem); font-weight: 900; letter-spacing: 0; }
    .holiday-calendar-hero p { margin: 0; color: rgba(255,255,255,.9); font-weight: 700; }
    .calendar-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .calendar-stat, .holiday-calendar-shell, .holiday-details-panel { border: 1px solid rgba(23,32,51,.1); border-radius: 16px; background: rgba(255,255,255,.96); box-shadow: 0 14px 35px rgba(28,37,65,.1); animation: holidayRise .55s ease both; }
    .calendar-stat { padding: 18px; } .calendar-stat small { display: block; color: #667085; font-weight: 800; } .calendar-stat strong { display: block; font-size: 2rem; font-weight: 900; line-height: 1; }
    .holiday-calendar-shell { padding: 18px; }
    .holiday-details-panel { margin-top: 18px; padding: 20px; border-left: 5px solid #2563eb; }
    .fc .fc-toolbar-title { font-weight: 900; color: #172033; letter-spacing: 0; }
    .fc .fc-button-primary { background: #2563eb; border-color: #2563eb; border-radius: 10px; font-weight: 800; }
    .fc-event { border: 0 !important; border-radius: 10px !important; padding: 3px 5px; cursor: pointer; box-shadow: 0 8px 16px rgba(37,99,235,.15); }
    .holiday-detail-title { font-size: 1.25rem; font-weight: 900; margin-bottom: 8px; }
    .holiday-pill { display: inline-flex; padding: 7px 10px; border-radius: 999px; font-weight: 900; background: #dbeafe; color: #1d4ed8; }
    .holiday-pill.weekly { background: #d1fae5; color: #047857; }
    @keyframes holidayRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .holiday-calendar-hero { flex-direction: column; align-items: flex-start; } .calendar-actions { justify-content: flex-start; } }
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
            height: 720,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events,
            eventContent: function(arg) {
                const reason = arg.event.extendedProps.description || arg.event.title;
                return { html: `<div title="${escapeHtml(reason)}"><strong>${escapeHtml(arg.event.title)}</strong><br><small>${escapeHtml(reason)}</small></div>` };
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const type = info.event.extendedProps.type === 'weekly_holiday' ? 'Weekly Holiday' : 'Special Holiday';
                const details = `
                    <div class="holiday-detail-title">${escapeHtml(info.event.title)}</div>
                    <p class="mb-2"><strong>Date:</strong> ${escapeHtml(info.event.extendedProps.date_label)}</p>
                    <p class="mb-2"><strong>Why holiday is set:</strong> ${escapeHtml(info.event.extendedProps.description || info.event.title)}</p>
                    <span class="holiday-pill ${info.event.extendedProps.type === 'weekly_holiday' ? 'weekly' : ''}">${type}</span>
                    ${isAdmin && info.event.url ? `<div class="mt-3"><a href="${info.event.url}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i>Edit Holiday</a></div>` : ''}
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
            ctx.fillStyle = '#f6f8fb'; ctx.fillRect(0, 0, width, height);
            ctx.fillStyle = '#2563eb'; ctx.fillRect(0, 0, width, 100);
            ctx.fillStyle = '#fff'; ctx.font = 'bold 34px Arial'; ctx.fillText('Holiday Calendar {{ $selectedYear }}', 36, 60);
            ctx.fillStyle = '#172033'; ctx.font = 'bold 16px Arial';
            ['Date', 'Day', 'Reason', 'Type'].forEach((head, i) => ctx.fillText(head, [40, 190, 360, 980][i], 135));
            ctx.font = '14px Arial';
            rows.forEach((row, index) => {
                const y = 168 + index * rowHeight;
                ctx.fillStyle = index % 2 ? '#ffffff' : '#eef2ff'; ctx.fillRect(30, y - 24, 1140, 34);
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
@endsection
