@extends('admin.layout.app')
@section('title', 'Attendance Calendar')
@section('content')
@php
    $totals = $calendar['totals'];
    $labels = ['present' => ['✅', 'Present'], 'late' => ['⏰', 'Late'], 'half_day' => ['🌓', 'Half day'], 'absent' => ['❌', 'Absent'], 'holiday' => ['🎉', 'Holiday'], 'leave' => ['🌴', 'Leave'], 'unpaid_leave' => ['📋', 'Unpaid leave'], 'day_off' => ['☕', 'Day off'], 'not_marked' => ['○', 'Not marked'], 'not_joined' => ['—', 'Before joining']];
    $previous = $start->copy()->subMonth();
    $next = $start->copy()->addMonth();
    $navigation = fn ($date) => route('attendance.index', ['user_id' => $selectedEmployee?->id, 'month' => $date->month, 'year' => $date->year]);
    $hours = fn ($seconds) => sprintf('%dh %02dm', intdiv((int) $seconds, 3600), intdiv((int) $seconds % 3600, 60));
    $exportRows = collect($calendar['days'])->map(fn ($day) => [$day['key'], $day['status'], $day['seconds'], $day['wfh'] ? 'Yes' : 'No'])->values();
@endphp
<div class="attendance-calendar-page">
    <header class="ac-panel ac-heading">
        <div class="ac-title"><span class="ac-title-icon" aria-hidden="true">📅</span><div><span class="ac-eyebrow">PEOPLE · ATTENDANCE</span><h1>Attendance calendar</h1><p>A clear picture of every working day.</p></div></div>
        <div class="ac-actions">
            <a class="ac-button" href="{{ route('attendance.index', ['view' => 'grid', 'month' => $month, 'year' => $year]) }}">Grid / table view</a>
            @if($canManage)
                <a class="ac-button" href="{{ route('attendance.archive') }}">Archived</a>
                <a class="ac-button" href="{{ route('attendance.settings') }}">Settings</a>
                <a class="ac-button ac-primary" href="{{ route('attendance.create', ['user_id' => $selectedEmployee?->id]) }}">＋ Mark attendance</a>
            @endif
        </div>
    </header>
    @if(session('success'))<div role="status" class="ac-panel">{{ session('success') }}</div>@endif
    <form class="ac-panel ac-toolbar" method="GET" action="{{ route('attendance.index') }}">
        <div class="ac-employee-picker"><label for="ac-employee">Employee</label><select name="user_id" id="ac-employee" required>
            @foreach($employees as $employee)<option value="{{ $employee->id }}" @selected($selectedEmployee?->id === $employee->id)>{{ $employee->name }}{{ $employee->employeeDetail?->employee_id ? ' · ' . $employee->employeeDetail->employee_id : '' }}</option>@endforeach
        </select></div>
        <a class="ac-button ac-arrow" href="{{ $navigation($previous) }}" aria-label="Previous month">‹</a>
        <div><label for="ac-month">Month</label><select id="ac-month" name="month">@foreach(range(1, 12) as $m)<option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}</option>@endforeach</select></div>
        <div><label for="ac-year">Year</label><input id="ac-year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" required></div>
        <a class="ac-button ac-arrow" href="{{ $navigation($next) }}" aria-label="Next month">›</a>
        <button class="ac-button ac-primary" type="submit">View month</button>
        <a class="ac-button" href="{{ $navigation(now()) }}">Today</a>
        <a class="ac-button" href="{{ $navigation($start) }}">Refresh</a>
    </form>
    @if($selectedEmployee)
    <section class="ac-panel ac-profile">
        <div class="ac-person"><div class="ac-avatar">{{ mb_substr($selectedEmployee->name, 0, 1) }}</div><div><h2>{{ $selectedEmployee->name }}</h2><p>{{ $selectedEmployee->employeeDetail?->employee_id ?: 'Employee ID not set' }} · {{ ucfirst($selectedEmployee->role) }}</p><p>{{ $selectedEmployee->email }}</p></div></div>
        <div class="ac-metrics">
            @foreach(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'half_day' => 'Half days', 'holiday' => 'Holidays', 'leave' => 'Leave', 'unpaid_leave' => 'Unpaid', 'wfh' => 'WFH'] as $key => $label)
                <div class="ac-metric ac-{{ $key }}"><strong>{{ $totals[$key] }}</strong><span>{{ $label }}</span></div>
            @endforeach
            <div class="ac-metric"><strong>{{ $hours($totals['seconds']) }}</strong><span>Total hours</span></div>
            <div class="ac-metric"><strong>{{ $totals['attendance_rate'] }}%</strong><span>Attendance</span></div>
        </div>
    </section>
    <section class="ac-panel ac-month-panel" aria-label="{{ $start->format('F Y') }} attendance">
        <div class="ac-month-heading"><div><span class="ac-eyebrow">MONTH AT A GLANCE</span><h2>{{ $start->format('F Y') }}</h2></div><div class="ac-actions"><button type="button" class="ac-button" id="ac-export">Export month CSV</button>@if($canManage)<a class="ac-button" href="{{ route('attendance.byHour', ['user_id' => $selectedEmployee->id, 'month' => $month, 'year' => $year]) }}">Hours report</a>@endif</div></div>
        <p class="ac-help">Select any date for clock times, locations, leave and holiday details. Hours include all recorded sessions.</p>
        <div class="ac-calendar-scroll"><div class="ac-calendar">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)<div class="ac-weekday">{{ $weekday }}</div>@endforeach
            @for($blank = 0; $blank < $calendar['offset']; $blank++)<div class="ac-day ac-blank" aria-hidden="true"></div>@endfor
            @foreach($calendar['days'] as $day)
                @php [$emoji, $label] = $labels[$day['status']] ?? ['📌', ucfirst(str_replace('_', ' ', $day['status']))]; @endphp
                <button type="button" class="ac-day ac-{{ $day['status'] }} {{ $day['date']->isToday() ? 'ac-today' : '' }}" data-day="ac-day-{{ $day['key'] }}" aria-label="{{ $day['date']->format('d F Y') }}: {{ $label }}">
                    <span class="ac-date">{{ $day['date']->day }} @if($day['date']->isToday())<small>Today</small>@endif</span>
                    <span class="ac-day-status"><span aria-hidden="true">{{ $emoji }}</span> {{ $label }}</span>
                    @if($day['wfh'])<span class="ac-day-note">🏠 Work from home</span>@endif
                    @if($day['holiday'])<span class="ac-day-note">{{ $day['holiday']->occassion ?? $day['holiday']->title ?? 'Holiday' }}</span>@endif
                    @if($day['leave'] && !in_array($day['status'], ['leave', 'unpaid_leave']))<span class="ac-day-note">🌴 Approved {{ $day['leave']->duration ?? 'leave' }}</span>@endif
                    @if($day['daily']->isNotEmpty())<span class="ac-day-hours">{{ $hours($day['seconds']) }}@if($day['daily']->contains(fn ($record) => $record->clock_in && ! $record->clock_out)) · Active @endif</span>@endif
                </button>
            @endforeach
            @for($blank = 0; $blank < (7 - (($calendar['offset'] + count($calendar['days'])) % 7)) % 7; $blank++)<div class="ac-day ac-blank" aria-hidden="true"></div>@endfor
        </div></div>
        <div class="ac-legend">@foreach($labels as $key => [$emoji, $label])@if($key !== 'not_joined')<span>{{ $emoji }} {{ $label }}</span>@endif @endforeach<span>🏠 Work from home</span></div>
        <p class="ac-help">Attendance % = present + late + half of half days, divided by elapsed scheduled days. Holidays and days off are excluded.</p>
    </section>
    @foreach($calendar['days'] as $day)
        <dialog class="ac-dialog" id="ac-day-{{ $day['key'] }}">
            <div class="ac-dialog-heading"><div><h2>{{ $day['date']->format('l, j F Y') }}</h2><p>{{ $selectedEmployee->name }}</p></div><button class="ac-button" type="button" data-close aria-label="Close day details">✕</button></div>
            <p>{{ ($labels[$day['status']][0] ?? '📌') . ' ' . ($labels[$day['status']][1] ?? $day['status']) }} · {{ $hours($day['seconds']) }}</p>
            @if($day['holiday'])<p>🎉 {{ $day['holiday']->occassion ?? $day['holiday']->title ?? 'Holiday' }}</p>@endif
            @if($day['leave'])<p>🌴 {{ ucfirst($day['leave']->type ?? 'Leave') }} · {{ $day['leave']->duration ?? 'Full day' }}</p><p>{{ $day['leave']->reason }}</p>@endif
            @forelse($day['daily'] as $record)
                <article class="ac-session"><h3>Attendance session {{ $loop->iteration }}</h3><dl><dt>Clock in</dt><dd>{{ $record->clock_in ?: '—' }}</dd><dt>Clock out</dt><dd>{{ $record->clock_out ?: 'Active / not recorded' }}</dd><dt>Work arrangement</dt><dd>{{ $record->work_from_type ?: ($record->working_from ?: 'Not recorded') }}</dd><dt>Clock-in location</dt><dd>{{ $record->clock_in_address ?: 'Not recorded' }}</dd><dt>Clock-out location</dt><dd>{{ $record->clock_out_address ?: 'Not recorded' }}</dd></dl>
                @if($canManage)
                <details><summary>Edit attendance</summary><form class="ac-edit" action="{{ route('attendance.update', $record->id) }}" method="POST">@csrf @method('PUT')
                    <label>Status<select name="status">@foreach(['present', 'late', 'half_day', 'absent', 'holiday', 'leave', 'unpaid_leave', 'day_off'] as $status)<option value="{{ $status }}" @selected($record->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></label>
                    <label>Clock in<input name="clock_in" type="time" value="{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '' }}"></label>
                    <label>Clock out<input name="clock_out" type="time" value="{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '' }}"></label>
                    <label>Location / address<input name="clock_in_address" maxlength="1000" value="{{ $record->clock_in_address }}"></label>
                    <label>Work arrangement<select name="work_from_type">@foreach(['office' => 'Office', 'home' => 'Work from home', 'other' => 'Other'] as $value => $name)<option value="{{ $value }}" @selected($record->work_from_type === $value)>{{ $name }}</option>@endforeach</select></label>
                    <p class="ac-edit-message" role="status"></p><button type="submit" class="ac-button ac-primary">Save attendance</button>
                </form></details>
                @endif</article>
            @empty<p>No clock-in session recorded for this date.</p>@endforelse
            @if($canManage)<a class="ac-button ac-primary" href="{{ route('attendance.create', ['user_id' => $selectedEmployee->id, 'date' => $day['key']]) }}">＋ Mark attendance for this date</a>@endif
        </dialog>
    @endforeach
    @else<section class="ac-panel"><h2>No employees available</h2><p>No employees are available within your attendance permissions.</p></section>@endif
</div>
<style>
.attendance-calendar-page{--ac-surface:#fff;--ac-bg:#f2f5fc;--ac-text:#17213b;--ac-muted:#62708a;--ac-border:#e2e8f2;--ac-blue:#245be0;background:var(--ac-bg);color:var(--ac-text);padding:24px;min-height:85vh;font-family:inherit}
:is([data-pms-theme="dark"],[data-bs-theme="dark"],[data-theme="dark"],.dark,.dark-mode) .attendance-calendar-page{--ac-surface:#111a32;--ac-bg:#090f20;--ac-text:#edf2ff;--ac-muted:#a9b6ce;--ac-border:#2a3856;--ac-blue:#82acff}
.attendance-calendar-page :is(h1,h2,h3,p,span,label,summary,dt,dd){color:inherit;-webkit-text-fill-color:currentColor}.attendance-calendar-page .ac-panel{background:var(--ac-surface);border:1px solid var(--ac-border);border-radius:22px;padding:24px;margin-bottom:22px;box-shadow:0 6px 24px #15275205}
.attendance-calendar-page h1{font-size:clamp(24px,2.3vw,34px);font-weight:800;margin:4px 0 8px}.attendance-calendar-page h2{font-size:21px;font-weight:750;margin:4px 0 8px}.attendance-calendar-page h3{font-size:16px;font-weight:700}.attendance-calendar-page p{margin:0 0 6px;color:var(--ac-muted)}
.attendance-calendar-page :is(.ac-heading,.ac-toolbar,.ac-month-heading,.ac-dialog-heading,.ac-actions,.ac-title,.ac-person){display:flex;align-items:center;gap:12px;flex-wrap:wrap}.attendance-calendar-page :is(.ac-heading,.ac-month-heading,.ac-dialog-heading){justify-content:space-between}.attendance-calendar-page .ac-title{gap:18px}.attendance-calendar-page .ac-title-icon{display:grid;place-items:center;width:64px;height:64px;border-radius:20px;background:#eaf0ff;font-size:32px}.attendance-calendar-page .ac-eyebrow{font-size:10px;letter-spacing:.13em;font-weight:800;color:var(--ac-blue)}
.attendance-calendar-page .ac-button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:9px 15px;background:var(--ac-surface);border:1px solid var(--ac-border);border-radius:12px;text-decoration:none;font-weight:650;font-size:13px;cursor:pointer;color:var(--ac-text)!important;-webkit-text-fill-color:var(--ac-text)!important}.attendance-calendar-page .ac-primary{background:#245be0;color:white!important;-webkit-text-fill-color:white!important;border-color:#245be0}.attendance-calendar-page .ac-button:hover{box-shadow:0 0 0 3px #5984ff25}.attendance-calendar-page :is(button,a,input,select,summary):focus-visible{outline:3px solid #6997ff;outline-offset:3px}.attendance-calendar-page .ac-arrow{font-size:24px;padding:2px 16px}
.attendance-calendar-page :is(select,input){display:block;min-height:42px;max-width:100%;border:1px solid var(--ac-border);border-radius:11px;padding:9px 12px;background:var(--ac-surface);color:var(--ac-text)!important;-webkit-text-fill-color:var(--ac-text)!important}.attendance-calendar-page label{display:block;font-size:12px;font-weight:700;margin-bottom:6px}.attendance-calendar-page .ac-toolbar{align-items:end}.attendance-calendar-page .ac-employee-picker{flex:1;min-width:210px}.attendance-calendar-page .ac-employee-picker select{width:100%}.attendance-calendar-page #ac-year{width:100px}
.attendance-calendar-page .ac-profile{display:flex;gap:24px;align-items:center;flex-wrap:wrap}.attendance-calendar-page .ac-person{min-width:220px}.attendance-calendar-page .ac-person p{font-size:12px;overflow-wrap:anywhere}.attendance-calendar-page .ac-avatar{display:grid;place-items:center;background:#e7efff;color:#245be0;font-size:26px;font-weight:800;border-radius:50%;width:58px;height:58px}.attendance-calendar-page .ac-metrics{display:grid;grid-template-columns:repeat(5,minmax(80px,1fr));gap:8px;flex:1}.attendance-calendar-page .ac-metric{text-align:center;padding:13px 8px;border:1px solid var(--ac-border);border-radius:12px}.attendance-calendar-page .ac-metric strong{display:block;color:var(--ac-blue)!important;-webkit-text-fill-color:var(--ac-blue)!important;font-size:21px;font-weight:800}.attendance-calendar-page .ac-metric span{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.04em;color:var(--ac-muted)}
.attendance-calendar-page .ac-help{font-size:12px;margin:10px 0 18px}.attendance-calendar-page .ac-calendar-scroll{overflow-x:auto}.attendance-calendar-page .ac-calendar{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px;min-width:650px}.attendance-calendar-page .ac-weekday{text-align:center;font-size:12px;font-weight:800;padding:14px;background:var(--ac-bg);border-radius:10px;color:var(--ac-muted)}.attendance-calendar-page .ac-day{min-height:130px;text-align:left;padding:12px;border-radius:12px;border:1px solid var(--ac-border);background:var(--ac-surface);color:var(--ac-text)!important;-webkit-text-fill-color:var(--ac-text)!important;display:flex;flex-direction:column;align-items:flex-start;gap:9px;cursor:pointer;transition:box-shadow .15s}.attendance-calendar-page .ac-day:hover{box-shadow:0 0 0 2px #6391ff80}.attendance-calendar-page .ac-blank{background:var(--ac-bg);border-color:transparent;cursor:default;box-shadow:none}.attendance-calendar-page .ac-date{font-weight:800;font-size:16px;width:100%;display:flex;justify-content:space-between}.attendance-calendar-page .ac-date small{font-size:9px;background:#245be0;color:#fff!important;-webkit-text-fill-color:#fff!important;padding:3px 5px;border-radius:5px}.attendance-calendar-page .ac-day-status{font-size:12px;font-weight:750}.attendance-calendar-page .ac-day-note{font-size:10px;color:var(--ac-muted);overflow-wrap:anywhere}.attendance-calendar-page .ac-day-hours{font-size:11px;color:var(--ac-blue);font-weight:700;margin-top:auto}.attendance-calendar-page .ac-present{border-top:3px solid #10b981}.attendance-calendar-page .ac-late{border-top:3px solid #f59e0b}.attendance-calendar-page .ac-half_day{border-top:3px solid #8b5cf6}.attendance-calendar-page .ac-absent{border-top:3px solid #ef4444}.attendance-calendar-page .ac-holiday{border-top:3px solid #3b82f6}.attendance-calendar-page :is(.ac-leave,.ac-unpaid_leave){border-top:3px solid #ec4899}.attendance-calendar-page .ac-today{outline:2px solid #245be0;outline-offset:-2px}.attendance-calendar-page .ac-legend{display:flex;gap:16px;flex-wrap:wrap;justify-content:center;padding-top:20px;font-size:11px}
.attendance-calendar-page .ac-dialog{background:var(--ac-surface);color:var(--ac-text);border:1px solid var(--ac-border);border-radius:20px;padding:24px;max-width:600px;width:calc(100% - 28px);max-height:85vh;overflow:auto}.attendance-calendar-page .ac-dialog::backdrop{background:#020818aa;backdrop-filter:blur(3px)}.attendance-calendar-page .ac-session{border-block:1px solid var(--ac-border);padding:16px 0;margin-bottom:16px}.attendance-calendar-page dl{display:grid;grid-template-columns:140px 1fr;gap:8px;font-size:13px}.attendance-calendar-page dd{margin:0;overflow-wrap:anywhere}.attendance-calendar-page dt{color:var(--ac-muted)}.attendance-calendar-page .ac-edit{display:grid;gap:10px;margin-top:14px}.attendance-calendar-page .ac-edit input,.attendance-calendar-page .ac-edit select{width:100%}.attendance-calendar-page summary{cursor:pointer;font-weight:700;color:var(--ac-blue)}
@media(max-width:700px){.attendance-calendar-page{padding:12px}.attendance-calendar-page .ac-panel{padding:16px;border-radius:16px}.attendance-calendar-page .ac-metrics{grid-template-columns:repeat(2,minmax(90px,1fr));width:100%}.attendance-calendar-page .ac-person{width:100%}.attendance-calendar-page .ac-title-icon{width:48px;height:48px;font-size:25px}.attendance-calendar-page .ac-actions{gap:8px}.attendance-calendar-page .ac-button{font-size:12px}.attendance-calendar-page dl{grid-template-columns:100px 1fr}}
</style>
@endsection
@push('js')
<script>
document.querySelectorAll('.attendance-calendar-page [data-day]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.day).showModal()));
document.querySelectorAll('.attendance-calendar-page [data-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
document.getElementById('ac-employee')?.addEventListener('change', event => event.target.form.requestSubmit());
document.querySelectorAll('.attendance-calendar-page .ac-edit').forEach(form => form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    const message = form.querySelector('.ac-edit-message');
    button.disabled = true;
    message.textContent = 'Saving…';
    try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Unable to save attendance.');
        window.location.reload();
    } catch (error) { message.textContent = error.message; button.disabled = false; }
}));
const calendarExportRows = @json($exportRows);
document.getElementById('ac-export')?.addEventListener('click', () => {
    const rows = [['Date', 'Status', 'Working seconds', 'Work from home'], ...calendarExportRows];
    const csv = rows.map(row => row.map(value => '"' + String(value).replaceAll('"', '""') + '"').join(',')).join('\r\n');
    const url = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' }));
    const link = document.createElement('a'); link.href = url; link.download = 'attendance-{{ $year }}-{{ $month }}.csv'; link.click(); URL.revokeObjectURL(url);
});
</script>
@endpush
