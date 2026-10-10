@extends('admin.layout.app')
@section('title', 'Employee Attendance')
@section('content')
@php
    $attendanceRoute = request('staff_category') === 'authority' ? 'admin.authority-attendance' : 'attendance.index';
    $statuses = ['present' => ['P', 'Present'], 'late' => ['LT', 'Late'], 'half_day' => ['HD', 'Half day'], 'absent' => ['A', 'Absent'], 'holiday' => ['H', 'Holiday'], 'leave' => ['L', 'Leave'], 'unpaid_leave' => ['UL', 'Unpaid leave'], 'day_off' => ['OFF', 'Day off'], 'wfh' => ['WFH', 'Work from home'], 'not_marked' => ['—', 'Not marked'], 'not_joined' => ['NJ', 'Before joining']];
    $hours = fn ($seconds) => sprintf('%dh %02dm', intdiv((int) $seconds, 3600), intdiv((int) $seconds % 3600, 60));
    $link = fn ($date) => route($attendanceRoute, ['view' => 'team', 'month' => $date->month, 'year' => $date->year, 'search' => request('search')]);
    $totalColumns = ['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'half_day' => 'Half day', 'holiday' => 'Holiday', 'leave' => 'Leave', 'unpaid_leave' => 'Unpaid', 'day_off' => 'Day off', 'wfh' => 'WFH'];
@endphp
<div id="attendance-team-page" class="attendance-team-page">
    <header class="at-panel at-header">
        <div><span class="at-eyebrow">PEOPLE / ATTENDANCE</span><h1>{{ request('staff_category') === 'authority' ? 'Higher-level attendance' : 'Employee attendance' }}</h1><p>Every employee, every day — one monthly overview.</p></div>
        <div class="at-actions">
            <a class="at-button" href="{{ route($attendanceRoute, ['month' => $month, 'year' => $year]) }}">Employee calendar</a>
            <a class="at-button" href="{{ route($attendanceRoute, ['view' => 'team', 'month' => $month, 'year' => $year, 'search' => request('search'), 'format' => 'csv']) }}">Export CSV</a>
            @if($canManage)<a class="at-button at-primary" href="{{ route('attendance.create', ['staff_category' => request('staff_category')]) }}">+ Mark attendance</a>@endif
        </div>
    </header>
    <form class="at-panel at-filters" method="GET" action="{{ route($attendanceRoute) }}">
        <input type="hidden" name="view" value="team">
        <label class="at-search">Find employee<input type="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Name, employee ID or email"></label>
        <label>Month<select name="month">@for($m = 1; $m <= 12; $m++)<option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(2026, $m, 1)->format('F') }}</option>@endfor</select></label>
        <label>Year<input type="number" name="year" min="2000" max="2100" value="{{ $year }}" required></label>
        <button class="at-button at-primary" type="submit">Apply filters</button>
        <a class="at-button" href="{{ route($attendanceRoute, ['view' => 'team']) }}">Reset</a>
        @foreach(['search', 'month', 'year'] as $field)
            @error($field)<span role="alert">{{ $message }}</span>@enderror
        @endforeach
    </form>
    <section class="at-stats" aria-label="Totals for all matching employees">
        <div class="at-panel"><span>Employees</span><strong>{{ number_format($summary['employees']) }}</strong><small>Matching this view</small></div>
        <div class="at-panel"><span>Present days</span><strong>{{ number_format($summary['present']) }}</strong><small>Includes late attendance</small></div>
        <div class="at-panel"><span>Absent days</span><strong>{{ number_format($summary['absent']) }}</strong><small>According to calendar rules</small></div>
        <div class="at-panel"><span>Recorded hours</span><strong>{{ $hours($summary['seconds']) }}</strong><small>All clock-in sessions</small></div>
    </section>
    <section class="at-panel at-register">
        <div class="at-header"><div><h2>{{ $start->format('F Y') }}</h2><p>Scroll across for every date and monthly totals. Select a marked day for session details.</p></div>
            <nav class="at-actions" aria-label="Month navigation">
                <a class="at-button" href="{{ $link($start->copy()->subMonth()) }}" aria-label="Previous month">&larr; Previous</a>
                <a class="at-button" href="{{ $link($start->copy()->addMonth()) }}" aria-label="Next month">Next &rarr;</a>
            </nav>
        </div>
        <div class="at-legend" aria-label="Attendance status legend">@foreach($statuses as $status => [$code, $label])<span><b class="at-status at-{{ $status }}">{{ $code }}</b>{{ $label }}</span>@endforeach</div>
        <div class="at-scroll" tabindex="0" role="region" aria-label="Monthly employee attendance table">
            <table class="at-table" data-pms-export="off">
                <caption class="visually-hidden">{{ $start->format('F Y') }} attendance for employees within your permissions. Present totals exclude late and half days, shown separately.</caption>
                <thead><tr><th scope="col" class="at-person">Employee</th>
                    @for($d = 1; $d <= $start->daysInMonth; $d++)@php $date = $start->copy()->day($d); @endphp<th scope="col" class="{{ $date->isToday() ? 'at-today' : '' }}"><strong>{{ $d }}</strong><small>{{ $date->format('D') }}</small></th>@endfor
                    @foreach($totalColumns as $label)<th scope="col">{{ $label }}</th>@endforeach<th scope="col">Hours</th><th scope="col">Attendance</th>
                </tr></thead>
                <tbody>
                @forelse($teamRows as $row)
                    @php $employee = $row['employee']; $totals = $row['calendar']['totals']; @endphp
                    <tr><th scope="row" class="at-person"><a href="{{ route($attendanceRoute, ['user_id' => $employee->id, 'month' => $month, 'year' => $year]) }}">{{ $employee->name }}</a><small>{{ $employee->employeeDetail?->employee_id ?: 'ID not assigned' }}</small><small>{{ $employee->companyStaffRole?->name ?? ucfirst($employee->role) }} &middot; {{ $employee->employeeDetail?->designation?->name }} @if($employee->employeeDetail?->designation) &middot; Level {{ $employee->employeeDetail->designation->level }} @endif</small><small>{{ $employee->email }}</small></th>
                    @foreach($row['calendar']['days'] as $day)
                        @php [$code, $label] = $statuses[$day['status']] ?? ['?', ucfirst(str_replace('_', ' ', $day['status']))]; @endphp
                        <td class="{{ $day['date']->isToday() ? 'at-today' : '' }}">
                            @if($day['daily']->isNotEmpty() || $day['holiday'] || $day['leave'])
                            <details class="at-day"><summary aria-label="{{ $employee->name }}: {{ $day['key'] }}, {{ $label }}, {{ $hours($day['seconds']) }}"><span class="at-status at-{{ $day['status'] }}">{{ $code }}</span>@if($day['wfh'])<small>WFH</small>@endif</summary>
                                <div class="at-detail"><strong>{{ $day['date']->format('D, j M') }} · {{ $label }}</strong><p>Total hours: {{ $hours($day['seconds']) }}</p>
                                    @if($day['holiday'])<p>Holiday: {{ $day['holiday']->occassion ?? $day['holiday']->title ?? 'Company holiday' }}</p>@endif
                                    @if($day['leave'])<p>Leave: {{ $day['leave']->reason ?: 'Approved leave' }}</p>@endif
                                    @foreach($day['daily'] as $session)<div class="at-session"><strong>Session {{ $loop->iteration }}</strong><p>In: {{ $session->clock_in ?: 'Not recorded' }}<br>Out: {{ $session->clock_out ?: 'Not recorded' }}<br>Hours: {{ $hours($session->total_seconds) }}</p><p>Work arrangement: {{ $session->work_from_type ?: ($session->working_from ?: 'Not recorded') }}</p><p>Clock-in location: {{ $session->clock_in_address ?: 'Not recorded' }}<br>Clock-out location: {{ $session->clock_out_address ?: 'Not recorded' }}</p></div>@endforeach
                                    <a href="{{ route($attendanceRoute, ['user_id' => $employee->id, 'month' => $month, 'year' => $year]) }}">Open employee calendar</a>
                                </div>
                            </details>
                            @else<span class="at-status at-{{ $day['status'] }}" title="{{ $day['key'] }}: {{ $label }}" aria-label="{{ $label }}">{{ $code }}</span>@endif
                        </td>
                    @endforeach
                    @foreach($totalColumns as $key => $label)<td class="at-total">{{ $totals[$key] }}</td>@endforeach<td class="at-total at-hours">{{ $hours($totals['seconds']) }}</td><td class="at-total">{{ $totals['attendance_rate'] }}%</td>
                    </tr>
                @empty<tr><td colspan="{{ $start->daysInMonth + count($totalColumns) + 3 }}" class="at-empty"><h3>No matching employees</h3><p>Try another search or clear the filters.</p></td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <footer class="at-footer"><p>Showing {{ $teamRows->firstItem() ?? 0 }}–{{ $teamRows->lastItem() ?? 0 }} of {{ $teamRows->total() }} employees. Totals above cover all matching employees.</p>{{ $teamRows->links() }}</footer>
        <p class="at-note">Attendance rate uses the existing calendar rules: present and late days count fully, half days count as half. Holidays and day offs are excluded. Future working days remain unmarked.</p>
    </section>
</div>
<style>
.attendance-team-page{--at-bg:#f4f6fb;--at-panel:#fff;--at-text:#17233c;--at-muted:#65738d;--at-border:#dfe6f2;padding:24px;background:var(--at-bg);color:var(--at-text);min-height:85vh}
:is([data-pms-theme="dark"],[data-theme="dark"],[data-bs-theme="dark"],.dark,.dark-mode) .attendance-team-page{--at-bg:#090f20;--at-panel:#111a32;--at-text:#edf2ff;--at-muted:#a9b6ce;--at-border:#2a3856}
.attendance-team-page .at-panel{background:var(--at-panel);border:1px solid var(--at-border);border-radius:20px;padding:24px;margin-bottom:20px}.attendance-team-page :is(h1,h2,h3){color:var(--at-text)!important}.attendance-team-page h1{font-size:30px;font-weight:800;margin:6px 0}.attendance-team-page h2{font-size:23px;font-weight:750;margin:0 0 6px}.attendance-team-page p{margin:0;color:var(--at-muted)!important;-webkit-text-fill-color:var(--at-muted)!important;font-size:13px}.attendance-team-page .at-eyebrow{color:#6d9dff!important;-webkit-text-fill-color:#6d9dff!important;font-size:11px;letter-spacing:.12em;font-weight:750}
.attendance-team-page :is(.at-header,.at-actions,.at-filters,.at-footer){display:flex;align-items:center;gap:12px;flex-wrap:wrap}.attendance-team-page :is(.at-header,.at-footer){justify-content:space-between}.attendance-team-page .at-button{display:inline-flex;justify-content:center;align-items:center;padding:11px 16px;min-height:44px;border:1px solid var(--at-border);border-radius:11px;background:var(--at-panel);color:var(--at-text)!important;-webkit-text-fill-color:var(--at-text)!important;font-weight:650;font-size:13px;text-decoration:none;cursor:pointer}.attendance-team-page .at-primary{background:#245be0;color:#fff!important;-webkit-text-fill-color:#fff!important;border-color:#245be0}.attendance-team-page :is(a,button,input,select,summary,.at-scroll):focus-visible{outline:3px solid #6b9cff;outline-offset:3px}
.attendance-team-page .at-filters{align-items:flex-end}.attendance-team-page label{display:grid;gap:8px;font-size:12px;font-weight:650}.attendance-team-page .at-search{flex:1;min-width:220px}.attendance-team-page :is(input,select){border:1px solid var(--at-border);border-radius:10px;background:var(--at-panel);color:var(--at-text)!important;min-height:44px;padding:10px 12px}.attendance-team-page input[type=number]{width:110px}.attendance-team-page .at-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.attendance-team-page .at-stats .at-panel{display:grid;gap:8px}.attendance-team-page .at-stats strong{font-size:26px;color:#6797f5!important;-webkit-text-fill-color:#6797f5!important}.attendance-team-page .at-stats :is(span,small){font-size:12px}.attendance-team-page .at-legend{display:flex;flex-wrap:wrap;gap:12px;margin:20px 0}.attendance-team-page .at-legend>span{display:flex;align-items:center;gap:6px;font-size:11px}
.attendance-team-page .at-scroll{overflow:auto;max-height:70vh;border:1px solid var(--at-border);border-radius:12px}.attendance-team-page .at-table{border-collapse:separate;border-spacing:0;width:100%;font-size:12px}.attendance-team-page .at-table :is(th,td){border-bottom:1px solid var(--at-border);border-right:1px solid var(--at-border);padding:12px 8px;text-align:center;min-width:58px;background:var(--at-panel);color:var(--at-text)!important}.attendance-team-page .at-table thead th{position:sticky;top:0;z-index:3;background:var(--at-bg);font-size:11px;white-space:nowrap}.attendance-team-page .at-table small{display:block;font-weight:400;font-size:10px;margin-top:4px}.attendance-team-page .at-table .at-person{position:sticky;left:0;text-align:left;min-width:230px;max-width:270px;z-index:2;box-shadow:4px 0 8px #00000008}.attendance-team-page .at-table thead .at-person{z-index:4}.attendance-team-page .at-person a{color:var(--at-text)!important;text-decoration:none;font-weight:750}.attendance-team-page .at-person small{overflow-wrap:anywhere;color:var(--at-muted)!important;-webkit-text-fill-color:var(--at-muted)!important}.attendance-team-page .at-table tbody tr:hover> :is(td,th){background:var(--at-bg)}.attendance-team-page .at-table .at-today{box-shadow:inset 0 3px #427bfa}.attendance-team-page .at-total{font-weight:700}.attendance-team-page .at-hours{white-space:nowrap}
.attendance-team-page .at-status{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:29px;padding:0 5px;border-radius:8px;font-size:10px;font-weight:750;background:#e8edf5;color:#50627e!important;-webkit-text-fill-color:#50627e!important}.attendance-team-page :is(.at-present,.at-wfh){background:#d8f5e8;color:#116247!important;-webkit-text-fill-color:#116247!important}.attendance-team-page .at-late{background:#fff0cc;color:#825000!important;-webkit-text-fill-color:#825000!important}.attendance-team-page .at-absent{background:#ffe1e6;color:#a5263e!important;-webkit-text-fill-color:#a5263e!important}.attendance-team-page :is(.at-half_day,.at-leave){background:#ece3ff;color:#6534a4!important;-webkit-text-fill-color:#6534a4!important}.attendance-team-page .at-holiday{background:#dce9ff;color:#2358a7!important;-webkit-text-fill-color:#2358a7!important}.attendance-team-page .at-unpaid_leave{background:#ffe5f1;color:#972b61!important;-webkit-text-fill-color:#972b61!important}
.attendance-team-page .at-day summary{cursor:pointer;list-style:none}.attendance-team-page .at-day summary::-webkit-details-marker{display:none}.attendance-team-page .at-day[open]{min-width:260px;text-align:left}.attendance-team-page .at-detail{max-width:310px;min-width:250px;padding:12px;border:1px solid var(--at-border);border-radius:10px;margin-top:10px;overflow-wrap:anywhere}.attendance-team-page .at-detail p{margin:8px 0}.attendance-team-page .at-session{border-top:1px solid var(--at-border);padding-top:10px;margin-top:10px}.attendance-team-page .at-footer{margin-top:18px}.attendance-team-page .at-note{margin-top:14px;font-size:11px}.attendance-team-page .at-empty{padding:40px!important}
@media(max-width:900px){.attendance-team-page{padding:12px}.attendance-team-page .at-panel{padding:18px}.attendance-team-page .at-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.attendance-team-page .at-table .at-person{min-width:180px;max-width:200px}.attendance-team-page .at-filters .at-search{flex-basis:100%}}

/* Status badges keep their own colours everywhere (global dark-mode table rules turned the codes white in the grid). */
html body .attendance-team-page .at-status{background:#e8edf5!important;color:#50627e!important;-webkit-text-fill-color:#50627e!important;opacity:1!important;filter:none!important}
html body .attendance-team-page :is(.at-status.at-present,.at-status.at-wfh){background:#d8f5e8!important;color:#116247!important;-webkit-text-fill-color:#116247!important}
html body .attendance-team-page .at-status.at-late{background:#fff0cc!important;color:#825000!important;-webkit-text-fill-color:#825000!important}
html body .attendance-team-page .at-status.at-absent{background:#ffe1e6!important;color:#a5263e!important;-webkit-text-fill-color:#a5263e!important}
html body .attendance-team-page :is(.at-status.at-half_day,.at-status.at-leave){background:#ece3ff!important;color:#6534a4!important;-webkit-text-fill-color:#6534a4!important}
html body .attendance-team-page .at-status.at-holiday{background:#dce9ff!important;color:#2358a7!important;-webkit-text-fill-color:#2358a7!important}
html body .attendance-team-page .at-status.at-unpaid_leave{background:#ffe5f1!important;color:#972b61!important;-webkit-text-fill-color:#972b61!important}

/* Keep daily codes readable despite the global dark theme's white table text. */
:is([data-pms-theme="dark"],[data-theme="dark"],[data-bs-theme="dark"],.dark,.dark-mode) #attendance-team-page .at-status {
    background:var(--at-status-bg,#e8edf5)!important;
    color:var(--at-status-ink,#334155)!important;
    -webkit-text-fill-color:var(--at-status-ink,#334155)!important;
    opacity:1!important;
    filter:none!important;
}
#attendance-team-page :is(.at-status.at-present,.at-status.at-wfh){--at-status-bg:#d8f5e8;--at-status-ink:#116247}
#attendance-team-page .at-status.at-late{--at-status-bg:#fff0cc;--at-status-ink:#825000}
#attendance-team-page .at-status.at-absent{--at-status-bg:#ffe1e6;--at-status-ink:#a5263e}
#attendance-team-page :is(.at-status.at-half_day,.at-status.at-leave){--at-status-bg:#ece3ff;--at-status-ink:#6534a4}
#attendance-team-page .at-status.at-holiday{--at-status-bg:#dce9ff;--at-status-ink:#2358a7}
#attendance-team-page .at-status.at-unpaid_leave{--at-status-bg:#ffe5f1;--at-status-ink:#972b61}
</style>
@endsection
