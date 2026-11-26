@extends('admin.layout.app')

@section('title', 'Timesheet Calendar')

@section('content')
@include('admin.timelogs.partials.workspace-styles')

<div id="pms-timelogs-calendar-page" class="pms-ts-page">
    <!-- Header -->
    <div class="pms-ts-topbar">
        <div class="pms-ts-title-wrap">
            <div class="pms-ts-title-icon" aria-hidden="true">
                <i class="bx bx-calendar"></i>
            </div>
            <div>
                <h3 class="pms-ts-title">Timesheet Calendar</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('timelogs.index') }}" class="text-decoration-none">Timesheet</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Calendar View</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="pms-ts-actions">
            <a href="{{ route('timelogs.create') }}" class="btn pms-ts-btn-primary">
                <i class="bx bx-plus-circle"></i> Log Time
            </a>
        </div>
    </div>

    <!-- Actions & Views Switcher Toolbar -->
    <div class="pms-ts-switcher-bar">
        <div class="text-muted small">
            <i class="bx bx-info-circle text-primary me-1"></i> Showing log entries on calendar view. Click any event to view log details.
        </div>

        <!-- View Switcher -->
        <div class="pms-ts-switcher" role="group" aria-label="Timesheet views">
            <a href="{{ route('timelogs.index') }}" class="pms-ts-tab {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" title="Timesheet List">
                <i class="bx bx-list-ul"></i> List
            </a>
            <a href="{{ route('timelogs.calendar') }}" class="pms-ts-tab {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}" title="Calendar View">
                <i class="bx bx-calendar"></i> Calendar
            </a>
            <a href="{{ route('timelogs.byEmployee')}}" class="pms-ts-tab {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" title="By Employee Summary">
                <i class="bx bx-user"></i> By Employee
            </a>
            <button type="button" class="pms-ts-tab" data-bs-toggle="modal" data-bs-target="#howItWorksModal" title="How It Works">
                <i class="bx bx-help-circle"></i>
            </button>
        </div>
    </div>

    <!-- Calendar Card Container -->
    <div class="card pms-ts-card p-4">
        <div id="calendar" style="min-height: 680px;"></div>
    </div>
</div>

@include('admin.timelogs.partials.how-it-works-modal')
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    .fc {
        font-family: inherit;
    }
    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .fc .fc-button-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        border-radius: 10px !important;
        padding: 0.42rem 0.95rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2) !important;
    }
    .fc .fc-button-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    .fc .fc-button-primary:disabled {
        background-color: #94a3b8 !important;
        border-color: #94a3b8 !important;
    }
    .fc .fc-daygrid-day-number {
        font-weight: 600;
        color: #374151;
        padding: 6px 10px;
    }
    .fc .fc-event {
        border-radius: 8px !important;
        padding: 3px 6px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important;
        cursor: pointer;
    }

    html[data-pms-theme="dark"] .fc .fc-toolbar-title {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .fc .fc-daygrid-day-number,
    html[data-pms-theme="dark"] .fc .fc-col-header-cell-cushion {
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .fc table,
    html[data-pms-theme="dark"] .fc td,
    html[data-pms-theme="dark"] .fc th {
        border-color: rgba(238, 241, 251, 0.08) !important;
    }
    html[data-pms-theme="dark"] .fc .fc-daygrid-day.fc-day-today {
        background: rgba(47, 107, 255, 0.1) !important;
    }
    html[data-pms-theme="dark"] .fc .fc-button-primary {
        background-color: #2F6BFF !important;
        border-color: #2F6BFF !important;
        color: #FFFFFF !important;
    }
    html[data-pms-theme="dark"] .fc .fc-button-primary:hover {
        background-color: #1E4FCC !important;
        border-color: #1E4FCC !important;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 700,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                events: {!! json_encode($timelogs) !!},
                eventClick: function(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                }
            });

            calendar.render();
        }
    });
</script>
@endpush
