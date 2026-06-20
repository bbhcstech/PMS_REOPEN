@extends('admin.layout.app')

@section('title', 'Project Calendar')

@section('content')
<div class="calendar-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-calendar-alt"></i>
            <span>Dashboard / Projects / <strong>Calendar</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1>Project Calendar</h1>
                    <p>View all project timelines and deadlines in one place</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="btn-group project-view-switcher" role="group" aria-label="Project view switcher">
                    <a href="{{ route('projects.index') }}" class="btn btn-outline view-switch-btn" title="List View">
                        <i class="fas fa-list-ul"></i>
                        <span>List</span>
                    </a>
                    <a href="{{ route('projects.archive') }}" class="btn btn-outline">
                        <i class="fas fa-archive"></i> Archive
                    </a>
                    <a href="{{ route('projects.calendar') }}" class="btn btn-primary active view-switch-btn" title="Calendar View">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Calendar</span>
                    </a>
                </div>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Project
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <h3 id="totalEvents">0</h3>
                    <span>Total Events</span>
                    <p class="stat-sub">All project events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-flag-checkered"></i></div>
                <div>
                    <h3 id="upcomingEvents">0</h3>
                    <span>Upcoming</span>
                    <p class="stat-sub">Next 7 days</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div>
                    <h3 id="activeProjects">0</h3>
                    <span>Active Projects</span>
                    <p class="stat-sub">In progress</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3 id="completedProjects">0</h3>
                    <span>Completed</span>
                    <p class="stat-sub">Finished projects</p>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="content-card">
            <div class="calendar-header">
                <div class="calendar-title">
                    <div class="calendar-title-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h4>Project Calendar</h4>
                        <span class="muted">View all project deadlines and milestones</span>
                    </div>
                </div>
                <div class="calendar-legend">
                    <div class="legend-item">
                        <span class="legend-color deadline"></span>
                        <span>Deadlines</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color milestone"></span>
                        <span>Milestones</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color task"></span>
                        <span>Tasks</span>
                    </div>
                </div>
            </div>

            <!-- Calendar Wrapper -->
            <div class="calendar-wrapper">
                <div id="project-calendar" class="calendar-container"></div>
            </div>

            <!-- Calendar Footer -->
            <div class="calendar-footer">
                <div class="footer-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Click on any event to view details</span>
                </div>
                <div class="footer-status">
                    <span class="status-dot"></span>
                    <span>Live updates</span>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-calendar-alt text-primary"></i>
                <span id="footerTotalEvents">0</span> Total Events
            </div>
            <div class="status-item">
                <i class="fas fa-clock text-warning"></i>
                <span id="footerUpcomingEvents">0</span> Upcoming
            </div>
            <div class="status-item">
                <i class="fas fa-tasks text-success"></i>
                <span id="footerActiveProjects">0</span> Active Projects
            </div>
            <div class="status-item">
                <i class="fas fa-check-circle text-info"></i>
                Last updated: {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM CALENDAR PAGE - GREEN/TEAL THEME ===== */
    .calendar-page {
        padding: 30px 0;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #07130d;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 16px 26px;
        border-radius: 18px;
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .breadcrumb i {
        margin-right: 12px;
        color: #34d399;
        font-size: 1.1rem;
    }

    .breadcrumb strong {
        color: #07130d;
    }

    /* Header Card */
    .header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 30px 36px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .header-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .2);
    }

    .header-card h1 {
        font-size: 34px;
        font-weight: 700;
        margin-bottom: 6px;
        color: #07130d;
    }

    .header-card p {
        color: #52645a;
        font-size: 17px;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-group {
        display: flex;
        gap: 4px;
        background: #f0f9f4;
        padding: 4px;
        border-radius: 12px;
    }

    .btn {
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
        text-decoration: none;
        min-height: 44px;
    }

    .btn-outline {
        background: transparent;
        color: #5a6e63;
        border: 1px solid transparent;
    }

    .btn-outline:hover {
        background: #d1fae5;
        color: #0f744c;
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, .25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 185, 129, .35);
    }

    .btn-primary.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        padding: 22px;
        border-radius: 22px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 14px 35px rgba(15, 116, 76, .06);
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(15, 116, 76, .12);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        background: #d1fae5;
        color: #0f744c;
    }

    .stat-card h3 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 2px;
        line-height: 1;
    }

    .stat-card span {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
        overflow: hidden;
    }

    /* Calendar Header */
    .calendar-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .calendar-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .calendar-title-icon {
        width: 48px;
        height: 48px;
        background: #e7f5ee;
        color: #0f744c;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .calendar-title h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .calendar-title .muted {
        font-size: 0.95rem;
        color: #8ba198;
        display: block;
        margin-top: 2px;
    }

    .calendar-legend {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #5a6e63;
        font-weight: 500;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }

    .legend-color.deadline {
        background: #0f744c;
    }

    .legend-color.milestone {
        background: #f59e0b;
    }

    .legend-color.task {
        background: #3b82f6;
    }

    /* Calendar Wrapper */
    .calendar-wrapper {
        padding: 20px;
        background: #ffffff;
    }

    .calendar-container {
        min-height: 550px;
    }

    /* FullCalendar Custom Overrides */
    .fc {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .fc .fc-toolbar-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #07130d;
    }

    .fc .fc-button {
        background: #f0f9f4;
        border: 1px solid rgba(15, 116, 76, .15);
        color: #0f744c;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
        text-transform: capitalize;
    }

    .fc .fc-button:hover {
        background: #d1fae5;
        border-color: #34d399;
    }

    .fc .fc-button-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        border-color: transparent;
        color: white;
    }

    .fc .fc-button-primary:hover {
        background: linear-gradient(145deg, #059669, #0f744c);
        border-color: transparent;
    }

    .fc .fc-button-primary:focus {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .25);
    }

    .fc .fc-daygrid-day-number {
        font-weight: 600;
        color: #07130d;
        font-size: 0.9rem;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background: #f0f9f4;
    }

    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: #0f744c;
        color: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fc .fc-daygrid-event {
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .fc .fc-daygrid-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .fc .fc-daygrid-event .fc-event-title {
        font-weight: 600;
        font-size: 0.8rem;
    }

    .fc .fc-daygrid-event .fc-event-time {
        font-weight: 500;
        font-size: 0.7rem;
        opacity: 0.8;
    }

    .fc .fc-daygrid-day-frame {
        padding: 4px;
    }

    .fc .fc-daygrid-more-link {
        font-weight: 600;
        color: #0f744c;
        font-size: 0.8rem;
    }

    .fc .fc-daygrid-more-link:hover {
        color: #059669;
    }

    .fc .fc-popover {
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .15);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    }

    .fc .fc-popover-header {
        background: #f5fbf7;
        padding: 10px 14px;
        border-radius: 12px 12px 0 0;
        font-weight: 700;
        color: #07130d;
    }

    .fc .fc-popover-body {
        padding: 8px 12px;
    }

    .fc .fc-popover-body .fc-daygrid-event {
        border-radius: 6px;
        margin-bottom: 4px;
    }

    .fc .fc-scrollgrid {
        border-color: rgba(15, 116, 76, .08);
    }

    .fc .fc-scrollgrid td {
        border-color: rgba(15, 116, 76, .08);
    }

    .fc .fc-col-header-cell {
        background: #fafefb;
        padding: 10px 0;
    }

    .fc .fc-col-header-cell-cushion {
        font-weight: 700;
        color: #5a6e63;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Calendar Footer */
    .calendar-footer {
        padding: 18px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    .footer-info {
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .footer-info i {
        color: #0f744c;
        margin-right: 8px;
        font-size: 1rem;
    }

    .footer-status {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.9rem;
        color: #8ba198;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #10b981;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Status Bar */
    .status-bar {
        margin-top: 28px;
        background: white;
        border-radius: 20px;
        padding: 18px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        border: 1px solid rgba(15, 116, 76, .1);
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .status-item i {
        font-size: 1.1rem;
    }

    .status-item span {
        font-weight: 700;
        color: #07130d;
        margin-right: 4px;
        font-size: 1.05rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
            flex-wrap: wrap;
        }
        .btn-group {
            flex-wrap: wrap;
        }
        .calendar-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .calendar-legend {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .calendar-page {
            padding: 16px 0;
        }
        .header-card {
            padding: 20px 24px;
        }
        .header-card h1 {
            font-size: 26px;
        }
        .header-card p {
            font-size: 15px;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .calendar-wrapper {
            padding: 10px;
        }
        .calendar-container {
            min-height: 400px;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .fc .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        .fc .fc-toolbar-title {
            font-size: 1.1rem;
        }
        .fc .fc-button {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        .fc .fc-daygrid-day-number {
            font-size: 0.8rem;
        }
        .fc .fc-daygrid-event {
            font-size: 0.7rem;
            padding: 2px 6px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .calendar-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .status-bar {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .calendar-title h4,
    html[data-pms-theme="dark"] .stat-card h3 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .btn-group {
        background: #183026;
    }

    html[data-pms-theme="dark"] .btn-outline {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .btn-outline:hover {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .calendar-header,
    html[data-pms-theme="dark"] .calendar-footer {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .calendar-title-icon {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .fc .fc-toolbar-title {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .fc .fc-button {
        background: #183026;
        border-color: rgba(122, 240, 181, .18);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .fc .fc-button:hover {
        background: rgba(122, 240, 181, .15);
    }

    html[data-pms-theme="dark"] .fc .fc-button-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-day-number {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-day.fc-day-today {
        background: #183026;
    }

    html[data-pms-theme="dark"] .fc .fc-col-header-cell {
        background: #142a20;
    }

    html[data-pms-theme="dark"] .fc .fc-col-header-cell-cushion {
        color: #b7d5c4;
    }

    html[data-pms-theme="dark"] .fc .fc-scrollgrid {
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .fc .fc-scrollgrid td {
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-more-link {
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .fc .fc-popover {
        background: #183026;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .fc .fc-popover-header {
        background: #142a20;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .fc .fc-popover-body {
        background: #183026;
    }

    html[data-pms-theme="dark"] .calendar-wrapper {
        background: #102119;
    }

    html[data-pms-theme="dark"] .legend-item {
        color: #d9f1e4;
    }

    /* Final project view switcher polish: list and calendar buttons align cleanly. */
    .calendar-page .project-view-switcher {
        align-items: center;
        border: 1px solid rgba(15, 116, 76, 0.1);
        display: inline-flex;
        gap: 4px;
        padding: 4px;
    }

    .calendar-page .project-view-switcher .view-switch-btn {
        align-items: center;
        display: inline-flex;
        gap: 0.5rem;
        justify-content: center;
        min-width: 96px;
        white-space: nowrap;
    }

    .calendar-page .project-view-switcher .view-switch-btn i {
        display: block;
        font-size: 1rem;
        line-height: 1;
        margin: 0;
    }

    .calendar-page .header-actions .btn {
        justify-content: center;
    }

    @media (max-width: 575.98px) {
        .calendar-page .project-view-switcher {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        .calendar-page .project-view-switcher .btn:not(.view-switch-btn) {
            grid-column: 1 / -1;
        }

        .calendar-page .project-view-switcher .view-switch-btn {
            min-width: 0;
            width: 100%;
        }
    }
</style>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<style>
    /* Additional FullCalendar dark mode overrides */
    html[data-pms-theme="dark"] .fc .fc-daygrid-day-frame {
        background: #102119;
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-day-events {
        background: #102119;
    }

    html[data-pms-theme="dark"] .fc .fc-day-other .fc-daygrid-day-number {
        color: #5a6e63;
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: #0f744c;
        color: white;
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-event {
        background: rgba(16, 185, 129, 0.2);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .fc .fc-daygrid-event:hover {
        background: rgba(16, 185, 129, 0.3);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('project-calendar');
    const events = {!! json_encode($events) !!};

    // Calculate stats
    const totalEvents = events.length;
    const upcomingEvents = events.filter(e => {
        const eventDate = new Date(e.start);
        const now = new Date();
        const sevenDaysLater = new Date();
        sevenDaysLater.setDate(now.getDate() + 7);
        return eventDate >= now && eventDate <= sevenDaysLater;
    }).length;

    const activeProjects = events.filter(e => e.status === 'in progress' || e.status === 'in_progress').length;
    const completedProjects = events.filter(e => e.status === 'completed').length;

    // Update stats
    document.getElementById('totalEvents').textContent = totalEvents;
    document.getElementById('upcomingEvents').textContent = upcomingEvents;
    document.getElementById('activeProjects').textContent = activeProjects || 0;
    document.getElementById('completedProjects').textContent = completedProjects || 0;
    document.getElementById('footerTotalEvents').textContent = totalEvents;
    document.getElementById('footerUpcomingEvents').textContent = upcomingEvents;
    document.getElementById('footerActiveProjects').textContent = activeProjects || 0;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventColor: '#0f744c',
        eventTextColor: '#fff',
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        eventDidMount: function(info) {
            // Custom styling for different event types
            if (info.event.extendedProps.type === 'milestone') {
                info.el.style.backgroundColor = '#f59e0b';
            } else if (info.event.extendedProps.type === 'task') {
                info.el.style.backgroundColor = '#3b82f6';
            }
        },
        eventClick: function(info) {
            // Show event details
            const event = info.event;
            const title = event.title;
            const start = event.start ? event.start.toLocaleDateString() : 'N/A';
            const end = event.end ? event.end.toLocaleDateString() : 'N/A';
            const type = event.extendedProps.type || 'General';
            const status = event.extendedProps.status || 'N/A';

            alert(
                '📋 Event Details\n\n' +
                'Title: ' + title + '\n' +
                'Type: ' + type.charAt(0).toUpperCase() + type.slice(1) + '\n' +
                'Status: ' + (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'N/A') + '\n' +
                'Start: ' + start + '\n' +
                'End: ' + end
            );
        }
    });

    calendar.render();

    // Dark mode support - refresh calendar on theme change
    function refreshCalendar() {
        calendar.render();
    }

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-pms-theme') {
                setTimeout(refreshCalendar, 100);
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-pms-theme']
    });
});
</script>
@endpush
@endsection
