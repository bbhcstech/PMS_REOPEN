@extends('admin.layout.app')

@section('content')

@php
    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
    $canSeeModule = fn (string $slug) => auth()->user()?->canViewModule($slug) ?? false;
    $statusTotal = max(array_sum($statusWiseCounts ?? []), 1);
    $managerFeatureLinks = [
        ['slug' => 'projects', 'route' => 'projects.index', 'label' => 'Projects', 'hint' => 'Project workspace', 'icon' => 'bx-briefcase-alt-2', 'value' => $totalProjects ?? 0, 'color' => '#2563eb'],
        ['slug' => 'tasks', 'route' => 'tasks.index', 'label' => 'Tasks', 'hint' => 'Team assignments', 'icon' => 'bx-task', 'value' => 'Open', 'color' => '#7c3aed'],
        ['slug' => 'timelogs', 'route' => 'timelogs.index', 'label' => 'Timesheet', 'hint' => 'Logged work', 'icon' => 'bx-time-five', 'value' => 'Log', 'color' => '#10b981'],
        ['slug' => 'tickets', 'route' => 'tickets.index', 'label' => 'Tickets', 'hint' => 'Project support', 'icon' => 'bx-support', 'value' => 'Queue', 'color' => '#ef4444'],
        ['slug' => 'clients', 'route' => 'clients.index', 'label' => 'Clients', 'hint' => 'Client records', 'icon' => 'bx-user-circle', 'value' => 'CRM', 'color' => '#06b6d4'],
        ['slug' => 'reports', 'route' => 'attendance.report', 'label' => 'Reports', 'hint' => 'Operational reports', 'icon' => 'bx-bar-chart-alt-2', 'value' => 'View', 'color' => '#64748b'],
        ['slug' => 'organization', 'route' => 'organization.index', 'label' => 'Organization', 'hint' => 'Team directory', 'icon' => 'bx-sitemap', 'value' => 'Org', 'color' => '#14b8a6'],
        ['slug' => 'leaves', 'route' => 'leaves.index', 'label' => 'Leaves', 'hint' => 'Team availability', 'icon' => 'bx-calendar-minus', 'value' => 'HR', 'color' => '#f59e0b'],
    ];
    $managerPieCharts = collect($statusWiseCounts ?? [])->map(function ($value, $label) use ($statusTotal) {
        return [
            'label' => $label ?: 'Unknown',
            'value' => $value,
            'percent' => round(($value / $statusTotal) * 100),
        ];
    })->values();
@endphp

<style>
    .manager-shell {
        background: linear-gradient(135deg, #eef7ff, #ffffff 48%, #f1f5f9);
        border-radius: 28px;
        margin-bottom: 1.5rem;
        padding: clamp(1rem, 2vw, 1.5rem);
    }
    .manager-hero,
    .manager-panel,
    .manager-stat,
    .manager-feature,
    .manager-pie {
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(255,255,255,.78);
        box-shadow: 0 22px 55px rgba(15,23,42,.1);
        backdrop-filter: blur(16px);
    }
    .manager-hero {
        border-radius: 24px;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1.25fr) minmax(260px, .75fr);
        padding: clamp(1.25rem, 3vw, 2.25rem);
    }
    .manager-hero h1 {
        color: #111827;
        font-size: clamp(1.8rem, 4vw, 3.35rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1.04;
        margin-bottom: .75rem;
    }
    .manager-hero p {
        color: #667085;
        font-weight: 700;
        max-width: 650px;
    }
    .manager-eyebrow {
        background: rgba(37,99,235,.1);
        border-radius: 999px;
        color: #2563eb;
        display: inline-flex;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: .08em;
        margin-bottom: .85rem;
        padding: .42rem .72rem;
        text-transform: uppercase;
    }
    .manager-quick {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-top: 1rem;
    }
    .manager-btn {
        border-radius: 999px;
        display: inline-flex;
        font-weight: 900;
        gap: .45rem;
        min-height: 42px;
        padding: .7rem 1rem;
        text-decoration: none;
    }
    .manager-btn-primary {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
    }
    .manager-btn-light {
        background: #fff;
        border: 1px solid rgba(37,99,235,.16);
        color: #1f2937;
    }
    .manager-date-filter {
        align-items: center;
        background: rgba(255,255,255,.76);
        border: 1px solid rgba(37,99,235,.14);
        border-radius: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: 1rem;
        padding: .7rem;
    }
    .manager-date-filter label,
    .manager-date-filter span {
        color: #334155;
        font-size: .82rem;
        font-weight: 900;
        margin: 0;
    }
    .manager-date-filter .form-control {
        border-color: rgba(37,99,235,.16);
        border-radius: 12px;
        min-height: 38px;
        width: auto;
    }
    .manager-focus {
        align-items: center;
        display: flex;
        justify-content: center;
        text-align: center;
    }
    .manager-gauge {
        align-items: center;
        background: conic-gradient(#ef4444 calc(var(--percent) * 1%), #e5e7eb 0);
        border-radius: 50%;
        display: flex;
        height: 150px;
        justify-content: center;
        margin: 0 auto .8rem;
        position: relative;
        width: 150px;
    }
    .manager-gauge::after {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 18px;
        position: absolute;
    }
    .manager-gauge strong {
        color: #111827;
        font-size: 1.9rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
    }
    .manager-grid,
    .manager-pie-grid,
    .manager-feature-grid {
        display: grid;
        gap: 1rem;
        margin-top: 1rem;
    }
    .manager-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .manager-stat,
    .manager-panel,
    .manager-pie,
    .manager-feature {
        border-radius: 20px;
        padding: 1rem;
    }
    .manager-stat span,
    .manager-pie p,
    .manager-feature small {
        color: #667085;
        font-size: .78rem;
        font-weight: 800;
    }
    .manager-stat strong {
        color: #111827;
        display: block;
        font-size: 2rem;
        font-weight: 900;
        margin-top: .35rem;
    }
    .manager-panel h3 {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 900;
        margin-bottom: .25rem;
    }
    .manager-panel > p {
        color: #667085;
        font-size: .84rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .manager-pie-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .manager-pie {
        min-height: 190px;
        text-align: center;
    }
    .manager-donut {
        --accent: #2563eb;
        align-items: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e5e7eb 0);
        border-radius: 50%;
        display: flex;
        height: 112px;
        justify-content: center;
        margin: 0 auto .75rem;
        position: relative;
        width: 112px;
    }
    .manager-donut::after {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 14px;
        position: absolute;
    }
    .manager-donut strong {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
    }
    .manager-feature-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .manager-feature {
        align-items: center;
        color: #111827;
        display: grid;
        gap: .75rem;
        grid-template-columns: 42px minmax(0, 1fr) auto;
        min-height: 82px;
        text-decoration: none;
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .manager-feature:hover,
    .manager-pie:hover {
        box-shadow: 0 26px 58px rgba(37,99,235,.14);
        transform: translateY(-4px);
    }
    .manager-feature i {
        align-items: center;
        background: linear-gradient(135deg, rgba(37,99,235,.12), rgba(16,185,129,.12));
        border-radius: 14px;
        color: #2563eb;
        display: inline-flex;
        font-size: 1.25rem;
        height: 42px;
        justify-content: center;
        width: 42px;
    }
    .manager-feature strong,
    .manager-feature small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .manager-feature strong {
        font-size: .88rem;
        font-weight: 900;
    }
    .manager-feature em {
        background: rgba(37,99,235,.08);
        border-radius: 999px;
        color: #2563eb;
        font-size: .76rem;
        font-style: normal;
        font-weight: 900;
        padding: .34rem .5rem;
    }
    .manager-table-card {
        overflow: hidden;
        padding: 0;
    }
    .manager-table-head {
        align-items: center;
        border-bottom: 1px solid rgba(15,23,42,.08);
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem 1.15rem;
    }
    .manager-table-head h3,
    .manager-table-head p {
        margin: 0;
    }
    .manager-table-wrap {
        overflow-x: auto;
        padding: 1rem;
        -webkit-overflow-scrolling: touch;
    }
    .manager-table {
        min-width: 680px;
    }
    .manager-empty {
        align-items: center;
        color: #667085;
        display: grid;
        font-weight: 800;
        min-height: 150px;
        place-items: center;
    }
    @media (max-width: 1199.98px) {
        .manager-grid,
        .manager-pie-grid,
        .manager-feature-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 991.98px) {
        .manager-hero {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 575.98px) {
        .manager-grid,
        .manager-pie-grid,
        .manager-feature-grid {
            grid-template-columns: 1fr;
        }
        .manager-date-filter,
        .manager-date-filter .form-control,
        .manager-date-filter .btn {
            width: 100%;
        }
        .manager-table-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid py-4 manager-dashboard-page">

        <section class="manager-shell">
            <div class="manager-hero">
                <div>
                    <span class="manager-eyebrow">Manager command center</span>
                    <h1>Project Dashboard</h1>
                    <p>Track delivery health, overdue work, milestones, and every enabled project-management feature from one responsive workspace.</p>
                    <div class="manager-quick">
                        @if(Route::has('projects.index') && $canSeeModule('projects'))
                            <a href="{{ route('projects.index') }}" class="manager-btn manager-btn-primary"><i class="bx bx-briefcase-alt-2"></i> Projects</a>
                        @endif
                        @if(Route::has('tasks.index') && $canSeeModule('tasks'))
                            <a href="{{ route('tasks.index') }}" class="manager-btn manager-btn-light"><i class="bx bx-task"></i> Tasks</a>
                        @endif
                        @if(Route::has('timelogs.index') && $canSeeModule('timelogs'))
                            <a href="{{ route('timelogs.index') }}" class="manager-btn manager-btn-light"><i class="bx bx-time-five"></i> Timesheet</a>
                        @endif
                    </div>
                    <form method="GET" class="manager-date-filter">
                        <label>Date Range</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDateFormatted }}">
                        <span>to</span>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDateFormatted }}">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </div>
                <div class="manager-focus">
                    <div>
                        <div class="manager-gauge" style="--percent: {{ $totalProjects > 0 ? round(($overdueProjects / max($totalProjects, 1)) * 100) : 0 }};">
                            <strong>{{ $overdueProjects }}</strong>
                        </div>
                        <h5 class="fw-bold mb-1">Overdue Projects</h5>
                        <p class="text-muted mb-0">{{ $totalProjects }} total projects in selected range</p>
                    </div>
                </div>
            </div>

            <div class="manager-grid">
                <div class="manager-stat"><span>Total Projects</span><strong>{{ $totalProjects }}</strong></div>
                <div class="manager-stat"><span>Overdue Projects</span><strong>{{ $overdueProjects }}</strong></div>
                <div class="manager-stat"><span>Pending Milestones</span><strong>{{ $pendingMilestones->count() }}</strong></div>
            </div>

            <div class="manager-panel">
                <h3>Status Pie Charts</h3>
                <p>Project status pies are generated from the current filtered data.</p>
                <div class="manager-pie-grid">
                    @forelse($managerPieCharts as $index => $chart)
                        <div class="manager-pie">
                            <div class="manager-donut" style="--percent: {{ max(3, min(100, $chart['percent'])) }}; --accent: {{ ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#7c3aed', '#06b6d4'][$index % 6] }};">
                                <strong>{{ $chart['value'] }}</strong>
                            </div>
                            <h6 class="fw-bold mb-1">{{ ucfirst($chart['label']) }}</h6>
                            <p class="mb-0">{{ $chart['percent'] }}% of filtered projects</p>
                        </div>
                    @empty
                        <div class="manager-pie">
                            <div class="manager-donut" style="--percent: 3; --accent: #64748b;"><strong>0</strong></div>
                            <h6 class="fw-bold mb-1">No Projects</h6>
                            <p class="mb-0">No status data found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="manager-panel">
                <h3>Accessible Features</h3>
                <p>Feature shortcuts follow the modules enabled by admin for this role.</p>
                <div class="manager-feature-grid">
                    @foreach($managerFeatureLinks as $feature)
                        @if(Route::has($feature['route']) && $canSeeModule($feature['slug']))
                            <a href="{{ route($feature['route']) }}" class="manager-feature">
                                <i class="bx {{ $feature['icon'] }}"></i>
                                <span>
                                    <strong>{{ $feature['label'] }}</strong>
                                    <small>{{ $feature['hint'] }}</small>
                                </span>
                                <em>{{ $feature['value'] }}</em>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="manager-panel manager-table-card">
                <div class="manager-table-head">
                    <div>
                        <h3>Pending Milestones</h3>
                        <p>Milestones that still need manager attention.</p>
                    </div>
                </div>
                <div class="manager-table-wrap">
                    @if($pendingMilestones->isEmpty())
                        <div class="manager-empty">No pending milestones found.</div>
                    @else
                        <table class="table align-middle table-bordered mb-0 manager-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Milestone Title</th>
                                    <th>Cost</th>
                                    <th>Project</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingMilestones as $index => $milestone)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-medium">{{ $milestone->title }}</td>
                                        <td>{{ $milestone->cost }}</td>
                                        <td>{{ $milestone->project->name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </section>
</div>
@endsection
