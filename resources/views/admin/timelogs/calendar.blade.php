@extends('admin.layout.app')

@section('title', 'Timesheet Calendar')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><i class="bx bx-calendar text-primary me-2"></i> Timesheet Calendar</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('timelogs.index') }}" class="text-decoration-none">Timesheet</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Calendar View</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('timelogs.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bx bx-plus-circle me-1"></i> Log Time
            </a>
        </div>
    </div>

    <!-- Actions & Views Switcher Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="text-muted small">
            <i class="bx bx-info-circle text-primary me-1"></i> Showing log entries on calendar view. Click any event to view log details.
        </div>

        <!-- View Switcher -->
        <div class="btn-group" role="group">
            <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" title="Timesheet List">
                <i class="bx bx-list-ul me-1"></i> List
            </a>
            <a href="{{ route('timelogs.calendar') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}" title="Calendar View">
                <i class="bx bx-calendar me-1"></i> Calendar
            </a>
            <a href="{{ route('timelogs.byEmployee')}}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" title="By Employee Summary">
                <i class="bx bx-user me-1"></i> By Employee
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#howItWorksModal" title="How It Works">
                <i class="bx bx-help-circle"></i>
            </button>
        </div>
    </div>

    <!-- Calendar Card Container -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
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
        font-size: 1.35rem;
        font-weight: 700;
        color: #1f2937;
    }
    .fc .fc-button-primary {
        background-color: #0f744c !important;
        border-color: #0f744c !important;
        border-radius: 50rem !important;
        padding: 0.4rem 1rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 4px rgba(15, 116, 76, 0.15) !important;
    }
    .fc .fc-button-primary:hover {
        background-color: #0b593a !important;
        border-color: #0b593a !important;
    }
    .fc .fc-button-primary:disabled {
        background-color: #9ca3af !important;
        border-color: #9ca3af !important;
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
