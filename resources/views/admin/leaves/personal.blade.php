@extends('admin.layout.app')
@section('title', 'My Leave Report')

@push('styles')
<style>
    #hr-leave {
        --hl-surface: #fff;
        --hl-soft: #f4f8f8;
        --hl-line: #dce7e8;
        --hl-text: #162b35;
        --hl-muted: #526972;
        --hl-accent: #14665b;
        --hl-accent-soft: #e3f4ee;
        --hl-amber: #80510c;
        --hl-amber-soft: #fff0d4;
        --hl-rose: #9e3450;
        --hl-rose-soft: #fceaf0;
        --hl-shadow: 0 2px 5px rgb(22 52 62 / 4%), 0 10px 24px rgb(22 52 62 / 6%);
        display: grid;
        gap: 16px;
        min-width: 0;
        padding: clamp(12px, 2.4vw, 32px);
    }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) #hr-leave {
        --hl-surface: #191f33;
        --hl-soft: #222b40;
        --hl-line: #364658;
        --hl-text: #edf3fc;
        --hl-muted: #b6c6d6;
        --hl-accent: #9be6cc;
        --hl-accent-soft: #203e42;
        --hl-amber: #fad18b;
        --hl-amber-soft: #443729;
        --hl-rose: #ffafc4;
        --hl-rose-soft: #482d41;
        --hl-shadow: 0 8px 24px rgb(0 0 0 / 14%);
    }
    #hr-leave :is(a, button, input, select, span, p, h1, h2, h3, strong, small, time, label) { -webkit-text-fill-color: currentColor !important; }
    #hr-leave :is(a, button, input, select):focus-visible { outline: 2px solid var(--hl-accent); outline-offset: 3px; }
    #hr-leave :is(h1, h2, h3, p) { margin: 0; }
    #hr-leave i.bx {
        width: 1em;
        height: 1em;
        flex: 0 0 auto;
        color: inherit !important;
        background: currentColor !important;
        -webkit-mask-image: var(--svg) !important;
        mask-image: var(--svg) !important;
    }
    #hr-leave i.bx::before { content: none !important; }
    #hr-leave :is(.hl-header, .hl-identity, .hl-actions, .hl-controls, .hl-filter, .hl-date-nav, .hl-periods, .hl-summary-card, .hl-list-title, .hl-request) {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #hr-leave .hl-header {
        flex-wrap: wrap;
        justify-content: space-between;
        padding: clamp(18px, 3vw, 30px);
        border: 1px solid #3f6b68;
        border-radius: 18px;
        background: radial-gradient(ellipse at 100% 0%, #317772, transparent 75%), linear-gradient(120deg, #142d38, #1c494b);
        box-shadow: var(--hl-shadow);
    }
    #hr-leave .hl-header h1 { color: #fff !important; font-size: clamp(1.45rem, 2.7vw, 2rem); font-weight: 750; }
    #hr-leave .hl-header p { margin-top: 4px; color: #dcefeb !important; font-size: .9rem; overflow-wrap: anywhere; }
    #hr-leave :is(.hl-identity, .hl-request-main) { min-width: 0; }
    #hr-leave .hl-actions { flex-wrap: wrap; gap: 8px; }
    #hr-leave .hl-readonly { display: inline-flex; align-items: center; gap: 5px; color: #ecfaf7 !important; font-weight: 650; font-size: .8rem; }
    #hr-leave .hl-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 11px;
        background: var(--hl-accent-soft);
        color: var(--hl-accent) !important;
        font-size: 1.25rem;
    }
    #hr-leave .hl-header .hl-icon { width: 52px; height: 52px; border: 1px solid #ffffff50; background: #ffffff18; color: #effffb !important; font-size: 1.6rem; }
    #hr-leave .hl-panel { min-width: 0; border: 1px solid var(--hl-line); border-radius: 15px; background: var(--hl-surface); box-shadow: var(--hl-shadow); }
    #hr-leave .hl-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    #hr-leave .hl-summary-card { padding: clamp(12px, 2vw, 20px); }
    #hr-leave .hl-stat-label { display: block; color: var(--hl-muted) !important; font-weight: 650; font-size: .8rem; }
    #hr-leave .hl-stat { color: var(--hl-text) !important; font-size: 1.6rem; line-height: 1.15; font-weight: 750; }
    #hr-leave :is(.hl-icon, .hl-status)[data-tone="pending"] { background: var(--hl-amber-soft); color: var(--hl-amber) !important; }
    #hr-leave :is(.hl-icon, .hl-status)[data-tone="rejected"] { background: var(--hl-rose-soft); color: var(--hl-rose) !important; }
    #hr-leave .hl-controls { justify-content: space-between; flex-wrap: wrap; padding: 12px; }
    #hr-leave .hl-periods { gap: 4px; padding: 4px; border: 1px solid var(--hl-line); border-radius: 11px; background: var(--hl-soft); }
    #hr-leave .hl-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        min-height: 38px;
        padding: 5px 11px;
        border: 1px solid var(--hl-line);
        border-radius: 8px;
        background: var(--hl-surface);
        color: var(--hl-text) !important;
        font: inherit;
        font-size: .81rem;
        font-weight: 700;
        text-decoration: none !important;
        white-space: nowrap;
    }
    #hr-leave .hl-button:hover { border-color: var(--hl-accent); color: var(--hl-accent) !important; }
    #hr-leave .hl-header .hl-button { border-color: #ffffff40; background: #ffffff16; color: #fff !important; }
    #hr-leave .hl-header .hl-export { border-color: #e3f7f1; background: #e3f7f1; color: #114a44 !important; }
    #hr-leave .hl-header .hl-button:hover { border-color: #fff; }
    #hr-leave .hl-header .hl-button:focus-visible { outline-color: #fff; }
    #hr-leave .hl-periods .hl-button { border-color: transparent; background: transparent; color: var(--hl-muted) !important; }
    #hr-leave .hl-periods .hl-button[aria-current="true"] { border-color: var(--hl-line); background: var(--hl-surface); color: var(--hl-accent) !important; }
    #hr-leave :is(.hl-filter, .hl-date-nav) { flex-wrap: wrap; gap: 6px; margin: 0; }
    #hr-leave .hl-arrow { width: 38px; padding: 0; font-size: 1.1rem; }
    #hr-leave :is(.hl-date, .hl-status-filter) { min-height: 38px; min-width: 0; border: 1px solid var(--hl-line); border-radius: 8px; padding: 5px 8px; background: var(--hl-surface) !important; color: var(--hl-text) !important; font: inherit; font-size: .81rem; color-scheme: light; }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) #hr-leave :is(.hl-date, .hl-status-filter) { color-scheme: dark; }
    #hr-leave .hl-list-title { padding: 15px 18px; }
    #hr-leave .hl-list-title h2 { color: var(--hl-text) !important; font-size: 1.12rem; font-weight: 750; }
    #hr-leave .hl-list-title p { color: var(--hl-muted) !important; font-size: .82rem; margin-top: 3px; }
    #hr-leave .hl-request { flex-wrap: wrap; padding: 14px 18px; border-top: 1px solid var(--hl-line); }
    #hr-leave .hl-request-main { flex: 1 1 180px; }
    #hr-leave .hl-request h3 { color: var(--hl-text) !important; font-size: .98rem; font-weight: 750; overflow-wrap: anywhere; }
    #hr-leave .hl-request-meta { display: flex; flex-wrap: wrap; gap: 4px 12px; margin-top: 4px; }
    #hr-leave .hl-request-meta > span { color: var(--hl-muted) !important; font-size: .81rem; }
    #hr-leave .hl-request .hl-icon { width: 36px; height: 36px; background: var(--hl-soft); }
    #hr-leave .hl-status { display: inline-block; padding: 4px 9px; border-radius: 999px; background: var(--hl-accent-soft); color: var(--hl-accent) !important; font-size: .75rem; font-weight: 700; }
    #hr-leave .hl-empty { border-top: 1px solid var(--hl-line); padding: 28px 16px; text-align: center; color: var(--hl-muted) !important; font-size: .9rem; }
    #hr-leave .hl-empty p { margin-top: 8px; color: var(--hl-muted) !important; }
    #hr-leave .hl-pagination { padding: 12px 18px; border-top: 1px solid var(--hl-line); }
    @media (max-width: 720px) {
        #hr-leave .hl-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        #hr-leave .hl-summary-card { gap: 8px; }
        #hr-leave .hl-summary-card .hl-icon { width: 33px; height: 33px; }
    }
    @media (max-width: 520px) {
        #hr-leave .hl-periods { flex: 1 1 100%; }
        #hr-leave .hl-periods .hl-button { flex: 1; padding-inline: 6px; }
        #hr-leave .hl-request { gap: 8px; padding-inline: 12px; }
        #hr-leave .hl-request-main { flex-basis: calc(100% - 54px); }
        #hr-leave .hl-request .hl-button { margin-left: auto; }
    }
</style>
@endpush

@section('content')
@php
    $range = match ($period) {
        'day' => $start->format('l, d M Y'),
        'week' => $start->format('d M Y') . ' – ' . $end->format('d M Y'),
        'month' => $start->format('F Y'),
    };
    $periodParams = fn ($value, $anchor) => array_filter(['period' => $value, 'date' => $anchor->toDateString(), 'status' => $status]);
    $report = fn ($value, $anchor) => route('leaves.mine', $periodParams($value, $anchor));
    $metrics = [
        ['bx-list-check', '', 'Requests', $counts->sum()],
        ['bx-time-five', 'pending', 'Pending', $counts->get('pending', 0)],
        ['bx-check-circle', 'approved', 'Approved', $counts->get('approved', 0)],
        ['bx-x-circle', 'rejected', 'Rejected', $counts->get('rejected', 0)],
    ];
@endphp
<div id="hr-leave">
    <header class="hl-header">
        <div class="hl-identity">
            <span class="hl-icon" aria-hidden="true"><i class="bx bx-calendar-check"></i></span>
            <div><h1>My Leave Report</h1><p>{{ $employee->name }}</p></div>
        </div>
        <div class="hl-actions">
            <span class="hl-readonly"><i class="bx bx-lock-alt" aria-hidden="true"></i> Read only</span>
            <a class="hl-button hl-export" href="{{ route('leaves.mine', $periodParams($period, $date) + ['format' => 'csv']) }}">
                <i class="bx bx-download" aria-hidden="true"></i> Export CSV
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success mb-0" role="status">{{ session('success') }}</div>
    @endif
    @error('date')
        <div class="alert alert-danger mb-0" role="alert">{{ $message }}</div>
    @enderror

    <div class="hl-summary" aria-label="Leave request totals for {{ $range }}">
        @foreach($metrics as [$icon, $tone, $label, $value])
            <div class="hl-panel hl-summary-card">
                <span class="hl-icon" data-tone="{{ $tone }}" aria-hidden="true"><i class="bx {{ $icon }}"></i></span>
                <div><span class="hl-stat-label">{{ $label }}</span><strong class="hl-stat">{{ $value }}</strong></div>
            </div>
        @endforeach
    </div>

    <div class="hl-panel hl-controls">
        <nav class="hl-periods" aria-label="Report period">
            @foreach(['day' => ['Daily', 'bx-calendar'], 'week' => ['Weekly', 'bx-calendar-week'], 'month' => ['Monthly', 'bx-calendar-alt']] as $key => [$label, $icon])
                <a class="hl-button" href="{{ $report($key, $date) }}" @if($period === $key) aria-current="true" @endif>
                    <i class="bx {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                </a>
            @endforeach
        </nav>
        <form class="hl-filter" method="GET" action="{{ route('leaves.mine') }}" aria-label="Filter leave requests">
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="hl-date-nav">
                <a class="hl-button hl-arrow" href="{{ $report($period, $previous) }}" aria-label="Previous {{ $period }}"><i class="bx bx-chevron-left" aria-hidden="true"></i></a>
                <input class="hl-date" type="date" name="date" value="{{ $date->toDateString() }}" aria-label="Report date" required>
                <a class="hl-button hl-arrow" href="{{ $report($period, $next) }}" aria-label="Next {{ $period }}"><i class="bx bx-chevron-right" aria-hidden="true"></i></a>
            </div>
            <select class="hl-status-filter" name="status" aria-label="Leave status">
                <option value="">All statuses</option>
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'unpaid' => 'Unpaid'] as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="hl-button" type="submit">Apply</button>
            <a class="hl-button" href="{{ $report($period, $today) }}">Today</a>
        </form>
    </div>

    <section class="hl-panel" aria-labelledby="hl-range">
        <header class="hl-list-title">
            <span class="hl-icon" aria-hidden="true"><i class="bx bx-list-ul"></i></span>
            <div>
                <h2 id="hl-range">{{ $range }}</h2>
                <p>{{ $leaves->total() }} {{ \Illuminate\Support\Str::plural('request', $leaves->total()) }}</p>
            </div>
        </header>
        @forelse($leaves as $leave)
            <article class="hl-request">
                <span class="hl-icon" aria-hidden="true"><i class="bx bx-calendar-event"></i></span>
                <div class="hl-request-main">
                    <h3>{{ $leave->type_label ?: 'Leave request' }}</h3>
                    <div class="hl-request-meta">
                        <span><time datetime="{{ $leave->start_date?->toDateString() }}">{{ $leave->start_date?->format('d M Y') }}</time>
                            @if(!$leave->end_date?->isSameDay($leave->start_date))
                                – <time datetime="{{ $leave->end_date?->toDateString() }}">{{ $leave->end_date?->format('d M Y') }}</time>
                            @endif
                        </span>
                        @if($leave->total_days !== null)
                            <span>{{ (float) $leave->total_days }} {{ (float) $leave->total_days === 1.0 ? 'day' : 'days' }}</span>
                        @endif
                        @if($leave->status === 'approved')<span>{{ $leave->is_unpaid ? 'Unpaid' : 'Paid' }}</span>@endif
                    </div>
                </div>
                <span class="hl-status" data-tone="{{ $leave->status }}">{{ ucfirst($leave->status ?? 'Pending') }}</span>
                <a class="hl-button" href="{{ route('leaves.show', ['leave' => $leave->id] + $periodParams($period, $date)) }}">
                    View details <i class="bx bx-chevron-right" aria-hidden="true"></i>
                </a>
            </article>
        @empty
            <div class="hl-empty">
                <span class="hl-icon" aria-hidden="true"><i class="bx bx-calendar-x"></i></span>
                <p>No leave requests match this period and status.</p>
            </div>
        @endforelse
        @if($leaves->hasPages())
            <div class="hl-pagination">{{ $leaves->links() }}</div>
        @endif
    </section>
</div>
@endsection
