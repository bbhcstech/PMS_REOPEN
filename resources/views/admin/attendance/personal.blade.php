@extends('admin.layout.app')
@section('title', 'My Attendance')

@push('styles')
<style>
    #hr-attendance {
        --ha-surface: #fff;
        --ha-soft: #f6f8fc;
        --ha-border: #dce4ef;
        --ha-ink: #192841;
        --ha-muted: #52627c;
        --ha-accent: #244dbc;
        --ha-accent-soft: #eaf0ff;
        --ha-positive: #12644f;
        --ha-positive-soft: #e4f6ed;
        --ha-negative: #9f3447;
        --ha-negative-soft: #fcecf0;
        --ha-warning: #825009;
        --ha-warning-soft: #fff1d9;
        --ha-shadow: 0 2px 4px rgb(28 50 91 / 4%), 0 9px 26px rgb(28 50 91 / 6%);
        display: grid;
        gap: 18px;
        min-width: 0;
        padding: clamp(12px, 2.5vw, 32px);
        color: var(--ha-ink);
    }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) #hr-attendance {
        --ha-surface: #191f33;
        --ha-soft: #222a40;
        --ha-border: #344057;
        --ha-ink: #edf2ff;
        --ha-muted: #b4c1db;
        --ha-accent: #adc4ff;
        --ha-accent-soft: #293965;
        --ha-positive: #92e5c7;
        --ha-positive-soft: #1c3b3c;
        --ha-negative: #ffacbd;
        --ha-negative-soft: #492c3d;
        --ha-warning: #f5cf94;
        --ha-warning-soft: #413432;
        --ha-shadow: 0 6px 22px rgb(0 0 0 / 14%);
    }
    #hr-attendance :is(h1, h2, p) { margin: 0; }
    #hr-attendance :is(.ha-heading, .ha-card) :is(h1, h2, p, span, strong, td, th, a, button, input) { -webkit-text-fill-color: currentColor !important; }
    #hr-attendance i.bx {
        display: inline-block;
        flex: 0 0 auto;
        width: 1em;
        height: 1em;
        background: currentColor !important;
        color: inherit !important;
        -webkit-mask-image: var(--svg) !important;
        mask-image: var(--svg) !important;
    }
    #hr-attendance i.bx::before { content: none !important; }
    #hr-attendance :is(a, button, input):focus-visible {
        outline: 2px solid var(--ha-accent);
        outline-offset: 3px;
    }
    #hr-attendance .ha-heading {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        padding: clamp(19px, 3vw, 32px);
        border: 1px solid #3e5486;
        border-radius: 18px;
        background: radial-gradient(ellipse at 100% 0%, #415c9b, transparent 65%), linear-gradient(120deg, #152647, #243765);
        box-shadow: var(--ha-shadow);
    }
    #hr-attendance .ha-heading h1 { color: #fff !important; font-size: clamp(1.5rem, 2.6vw, 2rem); font-weight: 750; letter-spacing: -.035em; }
    #hr-attendance .ha-heading p { margin-top: 4px; color: #d6e1fa !important; font-size: .9rem; overflow-wrap: anywhere; }
    #hr-attendance :is(.ha-title, .ha-actions, .ha-navigation, .ha-filter, .ha-table-header) { display: flex; align-items: center; gap: 12px; }
    #hr-attendance .ha-title { min-width: 0; }
    #hr-attendance .ha-actions { flex-wrap: wrap; }
    #hr-attendance .ha-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: var(--ha-accent-soft);
        color: var(--ha-accent) !important;
        font-size: 1.25rem;
    }
    #hr-attendance .ha-heading .ha-icon { width: 52px; height: 52px; border: 1px solid #6a7da5; background: #fff2; color: #eff4ff !important; font-size: 1.65rem; }
    #hr-attendance .ha-readonly { display: inline-flex; align-items: center; gap: 5px; color: #eff4ff !important; font-size: .81rem; font-weight: 650; }
    #hr-attendance .ha-card { min-width: 0; border: 1px solid var(--ha-border); border-radius: 15px; background: var(--ha-surface); box-shadow: var(--ha-shadow); }
    #hr-attendance .ha-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    #hr-attendance .ha-metric { display: flex; align-items: center; gap: 14px; padding: clamp(12px, 2vw, 21px); }
    #hr-attendance .ha-metric-label { display: block; color: var(--ha-muted) !important; font-size: .82rem; font-weight: 650; }
    #hr-attendance .ha-metric-value { display: block; color: var(--ha-ink) !important; font-size: clamp(1.32rem, 2.6vw, 1.78rem); font-weight: 750; line-height: 1.15; font-variant-numeric: tabular-nums; }
    #hr-attendance .ha-positive { background: var(--ha-positive-soft); color: var(--ha-positive) !important; }
    #hr-attendance .ha-negative { background: var(--ha-negative-soft); color: var(--ha-negative) !important; }
    #hr-attendance .ha-filter { justify-content: space-between; flex-wrap: wrap; padding: 14px; }
    #hr-attendance .ha-periods { display: flex; flex-wrap: wrap; gap: 4px; padding: 4px; border: 1px solid var(--ha-border); border-radius: 11px; background: var(--ha-soft); }
    #hr-attendance .ha-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        min-height: 40px;
        padding: 6px 12px;
        border: 1px solid var(--ha-border);
        border-radius: 8px;
        background: var(--ha-surface);
        color: var(--ha-ink) !important;
        font: inherit;
        font-size: .84rem;
        font-weight: 650;
        text-decoration: none;
        white-space: nowrap;
    }
    #hr-attendance .ha-button:hover { border-color: var(--ha-accent); color: var(--ha-accent) !important; }
    #hr-attendance .ha-periods .ha-button { border-color: transparent; background: transparent; color: var(--ha-muted) !important; }
    #hr-attendance .ha-periods .ha-button:is(:hover, [aria-current="true"]) { background: var(--ha-accent-soft); color: var(--ha-accent) !important; }
    #hr-attendance .ha-periods .ha-button[aria-current="true"] { border-color: var(--ha-border); background: var(--ha-surface); }
    #hr-attendance .ha-export { padding-inline: 15px; border-color: #e3edff; background: #eff4ff; color: #213d78 !important; }
    #hr-attendance .ha-export:hover { border-color: #fff; background: #fff; color: #1b326a !important; }
    #hr-attendance .ha-export:focus-visible { outline-color: #fff; }
    #hr-attendance .ha-navigation { flex-wrap: wrap; gap: 6px; margin: 0; }
    #hr-attendance .ha-arrow { width: 40px; padding: 0; font-size: 1.18rem; }
    #hr-attendance .ha-date {
        min-height: 40px;
        min-width: 0;
        padding: 6px 10px;
        border: 1px solid var(--ha-border);
        border-radius: 8px;
        background: var(--ha-surface);
        color: var(--ha-ink) !important;
        font: inherit;
        font-size: .85rem;
        color-scheme: light;
    }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) #hr-attendance .ha-date { color-scheme: dark; }
    #hr-attendance .ha-table-header { justify-content: space-between; flex-wrap: wrap; padding: 16px 20px; }
    #hr-attendance .ha-table-header h2 { color: var(--ha-ink) !important; font-size: 1.08rem; font-weight: 750; }
    #hr-attendance .ha-table-header p { margin-top: 3px; color: var(--ha-muted) !important; font-size: .83rem; }
    #hr-attendance .ha-table-scroll { overflow-x: auto; border-radius: 0 0 15px 15px; }
    #hr-attendance .ha-table-scroll:focus-visible { outline: 2px solid var(--ha-accent); outline-offset: -2px; }
    #hr-attendance .ha-table { width: 100%; min-width: 560px; border-collapse: collapse; font-size: .875rem; font-variant-numeric: tabular-nums; }
    #hr-attendance .ha-table :is(th, td), #hr-attendance .ha-table strong { color: var(--ha-ink) !important; }
    #hr-attendance .ha-table :is(th, td) { padding: 10px 20px; border-top: 1px solid var(--ha-border); text-align: left; vertical-align: top; }
    #hr-attendance .ha-table thead th { padding-block: 9px; background: var(--ha-soft); color: var(--ha-muted) !important; font-size: .78rem; font-weight: 700; white-space: nowrap; }
    #hr-attendance .ha-table tbody tr:hover { background: var(--ha-soft); }
    #hr-attendance .ha-table :is(th, td):last-child { text-align: right; }
    #hr-attendance .ha-day-name { display: block; color: var(--ha-muted) !important; font-size: .76rem; font-weight: 500; }
    #hr-attendance .ha-clock { display: block; color: var(--ha-muted) !important; white-space: nowrap; }
    #hr-attendance .ha-status { display: inline-block; padding: 3px 8px; border-radius: 999px; background: var(--ha-soft); color: var(--ha-muted) !important; font-size: .75rem; font-weight: 700; }
    #hr-attendance .ha-status[data-status="present"] { background: var(--ha-positive-soft); color: var(--ha-positive) !important; }
    #hr-attendance .ha-status[data-status="absent"] { background: var(--ha-negative-soft); color: var(--ha-negative) !important; }
    #hr-attendance .ha-status:is([data-status="late"], [data-status="half_day"]) { background: var(--ha-warning-soft); color: var(--ha-warning) !important; }
    #hr-attendance .ha-status:is([data-status="wfh"], [data-status="leave"], [data-status="unpaid_leave"]) { background: var(--ha-accent-soft); color: var(--ha-accent) !important; }
    @media (max-width: 565px) {
        #hr-attendance { gap: 13px; }
        #hr-attendance .ha-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        #hr-attendance .ha-metric:first-child { grid-column: 1 / -1; }
        #hr-attendance .ha-metric { gap: 8px; }
        #hr-attendance .ha-metric .ha-icon { width: 34px; height: 34px; }
        #hr-attendance .ha-filter { align-items: stretch; }
        #hr-attendance .ha-periods { flex: 1 1 100%; }
        #hr-attendance .ha-periods .ha-button { flex: 1; padding-inline: 7px; }
        #hr-attendance .ha-table-header { padding-inline: 12px; }
        #hr-attendance .ha-table { min-width: 0; table-layout: fixed; font-size: .82rem; }
        #hr-attendance .ha-table :is(th, td) { padding-inline: 6px; overflow-wrap: anywhere; }
        #hr-attendance .ha-table thead th { white-space: normal; }
        #hr-attendance .ha-table thead th:first-child { width: 22%; }
        #hr-attendance .ha-table thead th:nth-child(2) { width: 29%; }
    }
</style>
@endpush

@section('content')
@php
    $reportUrl = fn ($reportPeriod, $reportDate, $extra = []) => route('attendance.hr', [
        'period' => $reportPeriod, 'date' => $reportDate->toDateString(),
    ] + $extra);
    $range = match ($period) {
        'day' => $start->format('l, d M Y'),
        'week' => $start->format('d M Y') . ' – ' . $end->format('d M Y'),
        'month' => $start->format('F Y'),
    };
    $metrics = [
        ['bx-time-five', '', 'Total hours', $hours($totalSeconds)],
        ['bx-check-circle', 'ha-positive', 'Days attended', $daysWorked],
        ['bx-calendar-x', 'ha-negative', 'Days absent', $rows->where('status', 'absent')->count()],
    ];
@endphp
<div id="hr-attendance">
    <header class="ha-heading">
        <div class="ha-title">
            <span class="ha-icon" aria-hidden="true"><i class="bx bx-calendar-check"></i></span>
            <div>
                <h1>My Attendance</h1>
                <p>{{ $employee->name }}</p>
            </div>
        </div>
        <div class="ha-actions">
            <span class="ha-readonly"><i class="bx bx-lock-alt" aria-hidden="true"></i> Read only</span>
            <a class="ha-button ha-export" href="{{ $reportUrl($period, $date, ['format' => 'csv']) }}">
                <i class="bx bx-download" aria-hidden="true"></i> Export CSV
            </a>
        </div>
    </header>

    <div class="ha-metrics" aria-label="Attendance summary for {{ $range }}">
        @foreach ($metrics as [$icon, $tone, $label, $value])
            <div class="ha-card ha-metric">
                <span class="ha-icon {{ $tone }}" aria-hidden="true"><i class="bx {{ $icon }}"></i></span>
                <div><span class="ha-metric-label">{{ $label }}</span><strong class="ha-metric-value">{{ $value }}</strong></div>
            </div>
        @endforeach
    </div>

    <div class="ha-card ha-filter">
        <nav class="ha-periods" aria-label="Report period">
            @foreach (['day' => ['Daily', 'bx-calendar'], 'week' => ['Weekly', 'bx-calendar-week'], 'month' => ['Monthly', 'bx-calendar-alt']] as $key => [$label, $icon])
                <a class="ha-button" href="{{ $reportUrl($key, $date) }}" @if ($period === $key) aria-current="true" @endif>
                    <i class="bx {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                </a>
            @endforeach
        </nav>
        <form class="ha-navigation" method="GET" action="{{ route('attendance.hr') }}" aria-label="Choose a report date">
            <input type="hidden" name="period" value="{{ $period }}">
            <a class="ha-button ha-arrow" href="{{ $reportUrl($period, $previous) }}" aria-label="Previous {{ $period }}">
                <i class="bx bx-chevron-left" aria-hidden="true"></i>
            </a>
            <input class="ha-date" type="date" name="date" value="{{ $date->toDateString() }}" aria-label="Report date" required>
            <a class="ha-button ha-arrow" href="{{ $reportUrl($period, $next) }}" aria-label="Next {{ $period }}">
                <i class="bx bx-chevron-right" aria-hidden="true"></i>
            </a>
            <button class="ha-button" type="submit">Apply</button>
            <a class="ha-button" href="{{ $reportUrl($period, $today) }}">Today</a>
        </form>
    </div>

    <section class="ha-card" aria-labelledby="ha-report-heading">
        <div class="ha-table-header">
            <div class="ha-title">
                <span class="ha-icon" aria-hidden="true"><i class="bx bx-list-ul"></i></span>
                <div>
                    <h2 id="ha-report-heading">{{ $range }}</h2>
                    <p>Attendance records · Hours in HH:MM</p>
                </div>
            </div>
        </div>
        <div class="ha-table-scroll" role="region" aria-labelledby="ha-report-heading" tabindex="0">
            <table class="ha-table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Clock in</th>
                        <th scope="col">Clock out</th>
                        <th scope="col">Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <th scope="row">
                                <time datetime="{{ $row['date']->toDateString() }}">{{ $row['date']->format('d M') }}</time>
                                <span class="ha-day-name">{{ $row['date']->format('l') }}</span>
                            </th>
                            <td>
                                <span class="ha-status" data-status="{{ $row['status'] }}">{{ $statuses[$row['status']] ?? 'Not marked' }}</span>
                                @if ($row['wfh'] && $row['status'] !== 'wfh')<span class="ha-day-name">Work from home</span>@endif
                            </td>
                            @foreach (['clock_in', 'clock_out'] as $column)
                                <td>
                                    @forelse ($row[$column] as $time)
                                        <span class="ha-clock">{{ $time ?? '—' }}</span>
                                    @empty
                                        <span class="ha-clock">—</span>
                                    @endforelse
                                </td>
                            @endforeach
                            <td><strong>{{ $hours($row['seconds']) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th scope="row" colspan="4">Total hours</th><td><strong>{{ $hours($totalSeconds) }}</strong></td></tr>
                </tfoot>
            </table>
        </div>
    </section>
</div>
@endsection
