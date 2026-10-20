@extends('admin.layout.app')

@section('content')

@php
    use Carbon\Carbon;

    $defaultStart = now()->format('Y-m-d');
    $defaultEnd = now()->format('Y-m-d');
    $startDate = request('start_date', $defaultStart);
    $endDate = request('end_date', $defaultEnd);
    $roleName = ucfirst(auth()->user()?->role ?? 'HR');
    if ($roleName === 'Admin') {
        $roleName = 'Admin';
    } elseif ($roleName === 'Manager') {
        $roleName = 'Manager';
    } else {
        $roleName = 'HR';
    }
    $canSeeModule = fn (string $slug) => auth()->user()?->canViewModule($slug) ?? false;
    $attendancePercent = $totalEmployees > 0 ? round(($todayPresent / $totalEmployees) * 100) : 0;
    $absentToday = max(($totalEmployees ?? 0) - ($todayPresent ?? 0) - ($onLeaveToday ?? 0), 0);
    $roleScale = max($totalEmployees ?? 0, $totalProjects ?? 0, $newEmployees ?? 0, $exits ?? 0, $approvedLeaves ?? 0, $todayPresent ?? 0, $pendingTasks ?? 0, $pendingLeaves ?? 0, 1);
    $rolePieCharts = [
        ['slug' => 'projects', 'route' => 'projects.index', 'label' => 'Projects', 'value' => $totalProjects ?? 0, 'hint' => ($activeProjects ?? 0) . ' active projects', 'percent' => round((($totalProjects ?? 0) / $roleScale) * 100), 'color' => '#2F6BFF'],
        ['slug' => 'tasks', 'route' => 'tasks.index', 'label' => 'Pending Tasks', 'value' => $pendingTasks ?? 0, 'hint' => 'Open work queue', 'percent' => round((($pendingTasks ?? 0) / $roleScale) * 100), 'color' => '#8B5CF6'],
        ['slug' => 'timelogs', 'route' => 'timelogs.index', 'label' => 'Timesheet Hours', 'value' => $totalTimelogHours ?? 0, 'hint' => ($totalTimelogsCount ?? 0) . ' logged entries', 'percent' => min(100, round((($totalTimelogHours ?? 0) / max($roleScale * 8, 1)) * 100)), 'color' => '#10B981'],
        ['slug' => 'employees', 'route' => 'employees.index', 'label' => 'Employees', 'value' => $totalEmployees ?? 0, 'hint' => 'Total workforce', 'percent' => round((($totalEmployees ?? 0) / $roleScale) * 100), 'color' => '#22D3EE'],
        ['slug' => 'attendance', 'route' => 'attendance.index', 'label' => 'Presence', 'value' => $todayPresent ?? 0, 'hint' => "{$attendancePercent}% present today", 'percent' => $attendancePercent, 'color' => '#06B6D4'],
        ['slug' => 'leaves', 'route' => 'leaves.index', 'label' => 'Pending Leaves', 'value' => $pendingLeaves ?? 0, 'hint' => 'Awaiting review', 'percent' => round((($pendingLeaves ?? 0) / $roleScale) * 100), 'color' => '#F59E0B'],
        ['slug' => 'employees', 'route' => 'employees.index', 'label' => 'New Joiners', 'value' => $newEmployees ?? 0, 'hint' => 'In selected range', 'percent' => round((($newEmployees ?? 0) / $roleScale) * 100), 'color' => '#6366F1'],
        ['slug' => 'attendance', 'route' => 'attendance.report', 'label' => 'Absent Today', 'value' => $absentToday, 'hint' => 'Not present / leave', 'percent' => $totalEmployees > 0 ? round(($absentToday / $totalEmployees) * 100) : 0, 'color' => '#64748B'],
    ];

    $clockInLabel = $attendance && $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('h:i A') : 'Not Clocked In';
    $clockOutLabel = $attendance && $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('h:i A') : 'Pending';
    $todayStatus = $attendance && $attendance->status ? str_replace('_', ' ', ucfirst($attendance->status)) : 'No Entry';
    $workedDurationLabel = $attendance && $attendance->clock_in && $attendance->clock_out ? $attendance->total_duration : '00:00:00';
    $officeLatitude = $officeLatitude ?? 22.49682;
    $officeLongitude = $officeLongitude ?? 88.39462;
    $officeRadiusMeters = $officeRadiusMeters ?? 10;
    $officeAddress = $officeAddress ?? '11 Hospital Link Road, Satavisha Building, Kolkata, West Bengal 700075';
@endphp
<style>
    .role-dashboard-shell {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.04) 0%, var(--bx-bg, #f6f7fc) 52%, rgba(139, 92, 246, 0.04) 100%);
        border: 1px solid var(--bx-border, rgba(16, 20, 44, 0.08));
        border-radius: 28px;
        margin-bottom: 1.5rem;
        overflow: hidden;
        padding: clamp(1rem, 2vw, 1.5rem);
        position: relative;
    }
    .role-dashboard-shell::before {
        content: "";
        position: absolute;
        inset: -20% -10% auto auto;
        width: 460px;
        height: 460px;
        background: radial-gradient(circle, rgba(47, 107, 255, .15), transparent 68%);
        pointer-events: none;
    }
    .role-hero {
        align-items: stretch;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr) minmax(240px, .65fr);
        position: relative;
        z-index: 1;
    }
    .role-hero-card,
    .role-panel,
    .role-stat-card,
    .role-feature-card,
    .role-pie-card {
        background: var(--bx-surface, rgba(255,255,255,.9));
        border: 1px solid var(--bx-border, rgba(16, 20, 44, 0.08));
        box-shadow: 0 12px 32px rgba(15, 23, 42, .05);
        backdrop-filter: blur(16px);
    }
    .role-hero-card {
        border-radius: 24px;
        padding: clamp(1.25rem, 3vw, 2.25rem);
    }
    .role-eyebrow {
        background: rgba(47, 107, 255, .1);
        border: 1px solid rgba(47, 107, 255, .2);
        border-radius: 999px;
        color: var(--bx-primary, #2F6BFF);
        display: inline-flex;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        margin-bottom: .9rem;
        padding: .42rem .72rem;
        text-transform: uppercase;
    }
    .role-hero-card h1 {
        color: var(--bx-ink, #10142C);
        font-size: clamp(1.8rem, 4vw, 3.4rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.05;
        margin: 0 0 .8rem;
    }
    .role-hero-card p {
        color: var(--bx-ink-muted, #545D82);
        font-weight: 600;
        margin: 0;
        max-width: 680px;
    }
    .role-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-top: 1.15rem;
    }
    .role-btn {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-weight: 800;
        gap: .45rem;
        min-height: 42px;
        padding: .7rem 1.1rem;
        text-decoration: none;
        transition: all .2s ease;
    }
    .role-btn-primary {
        background: linear-gradient(135deg, #1E4FCC, #2F6BFF, #8B5CF6);
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.3);
    }
    .role-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(47, 107, 255, 0.4);
    }
    .role-btn-light {
        background: var(--bx-surface, #fff);
        border: 1px solid var(--bx-border, rgba(47,107,255,.16));
        color: var(--bx-ink, #10142C);
    }
    .role-btn-light:hover {
        border-color: var(--bx-primary, #2F6BFF);
        color: var(--bx-primary, #2F6BFF);
        transform: translateY(-1px);
    }
    .role-date-filter {
        align-items: center !important;
        background: var(--bx-surface-2, rgba(255,255,255,.85));
        border: 1px solid var(--bx-border, rgba(47,107,255,.16));
        border-radius: 16px;
        display: inline-flex !important;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding: 0.5rem 0.85rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        max-width: 100%;
    }
    .role-date-label {
        color: var(--bx-ink, #10142C);
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 38px !important;
        line-height: 1 !important;
        margin: 0 !important;
        white-space: nowrap;
    }
    .role-date-label i {
        font-size: 1.1rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        margin-right: 0.35rem !important;
        margin-bottom: 1px;
    }
    .role-date-inputs {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem;
        flex-wrap: wrap;
        height: 38px !important;
    }
    .role-date-input {
        background: var(--bx-surface, #fff);
        border: 1px solid var(--bx-border, rgba(47,107,255,.2));
        color: var(--bx-ink, #10142C);
        border-radius: 10px;
        height: 38px !important;
        padding: 0 0.75rem !important;
        font-size: 0.85rem;
        font-weight: 600;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        display: inline-flex !important;
        align-items: center !important;
        line-height: 38px !important;
    }
    .role-date-input:focus {
        border-color: var(--bx-primary, #2F6BFF);
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.15);
    }
    .role-date-separator {
        color: var(--bx-ink-muted, #64748B);
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 38px !important;
        line-height: 1 !important;
        margin: 0 !important;
    }
    .role-date-btn {
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC);
        border: none;
        color: #FFFFFF !important;
        border-radius: 10px;
        height: 38px !important;
        padding: 0 1.25rem !important;
        font-size: 0.85rem;
        font-weight: 700;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.4rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 3px 10px rgba(47, 107, 255, 0.3);
        white-space: nowrap;
        line-height: 1 !important;
    }
    .role-date-btn i {
        font-size: 1rem !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .role-date-btn:hover {
        background: linear-gradient(135deg, #1E4FCC, #163BA0);
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(47, 107, 255, 0.4);
    }
    .role-focus-card {
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 240px;
        padding: 1.25rem;
    }
    .role-gauge {
        align-items: center;
        background: conic-gradient(#10B981 0%, #2F6BFF calc(var(--percent) * 1%), rgba(148, 163, 184, 0.2) 0);
        border-radius: 50%;
        display: flex;
        height: 156px;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
        width: 156px;
    }
    .role-gauge::after {
        background: var(--bx-surface, #fff);
        border-radius: 50%;
        content: "";
        inset: 18px;
        position: absolute;
    }
    .role-gauge strong {
        color: var(--bx-ink, #10142C);
        font-size: 2rem;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }
    .role-stat-grid,
    .role-pie-grid,
    .role-feature-grid {
        display: grid;
        gap: 1rem;
        margin-top: 1rem;
        position: relative;
        z-index: 1;
    }
    .role-stat-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .role-stat-card {
        border-radius: 20px;
        min-height: 128px;
        padding: 1rem;
    }
    .role-stat-card span,
    .role-pie-card p,
    .role-feature-card small {
        color: var(--bx-ink-muted, #667085);
        font-size: .78rem;
        font-weight: 700;
    }
    .role-stat-card strong {
        color: var(--bx-ink, #10142C);
        display: block;
        font-size: 2rem;
        font-weight: 800;
        margin-top: .4rem;
    }
    .role-panel {
        border-radius: 24px;
        margin-top: 1rem;
        padding: 1.15rem;
        position: relative;
        z-index: 1;
    }
    .role-panel-head {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    .role-panel-head h3 {
        color: var(--bx-ink, #10142C);
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0;
    }
    .role-panel-head p {
        color: var(--bx-ink-muted, #667085);
        font-size: .84rem;
        font-weight: 600;
        margin: .2rem 0 0;
    }
    .role-pie-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .role-pie-card {
        border-radius: 18px;
        min-height: 220px;
        padding: 1rem;
        text-align: center;
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .role-pie-card:hover,
    .role-feature-card:hover {
        box-shadow: 0 20px 48px rgba(47, 107, 255, .12);
        transform: translateY(-4px);
    }
    .role-donut {
        --accent: #2F6BFF;
        align-items: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), rgba(148, 163, 184, 0.2) 0);
        border-radius: 50%;
        display: flex;
        height: 118px;
        justify-content: center;
        margin: 0 auto .8rem;
        position: relative;
        width: 118px;
    }
    .role-donut::after {
        background: var(--bx-surface, #fff);
        border-radius: 50%;
        content: "";
        inset: 15px;
        position: absolute;
    }
    .role-donut strong {
        color: var(--bx-ink, #10142C);
        font-size: 1.25rem;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }
    .role-pie-card h4 {
        color: var(--bx-ink, #10142C);
        font-size: .95rem;
        font-weight: 800;
        margin-bottom: .2rem;
    }
    .role-pie-card a {
        color: var(--bx-primary, #2F6BFF);
        display: inline-flex;
        font-size: .8rem;
        font-weight: 800;
        margin-top: .55rem;
        text-decoration: none;
    }
    .role-feature-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .role-feature-card {
        align-items: center;
        border-radius: 18px;
        color: var(--bx-ink, #10142C);
        display: grid;
        gap: .75rem;
        grid-template-columns: 42px minmax(0, 1fr) auto;
        min-height: 82px;
        padding: .85rem;
        text-decoration: none;
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .role-feature-card i {
        align-items: center;
        background: linear-gradient(135deg, rgba(47, 107, 255, .12), rgba(34, 211, 238, .12));
        border-radius: 14px;
        color: var(--bx-primary, #2F6BFF);
        display: inline-flex;
        font-size: 1.25rem;
        height: 42px;
        justify-content: center;
        width: 42px;
    }
    .role-feature-card strong,
    .role-feature-card small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .role-feature-card strong {
        font-size: .88rem;
        font-weight: 800;
    }
    .role-feature-card em {
        background: rgba(47, 107, 255, .08);
        border-radius: 999px;
        color: var(--bx-primary, #2F6BFF);
        font-size: .76rem;
        font-style: normal;
        font-weight: 800;
        padding: .34rem .5rem;
    }
    @keyframes roleFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .role-hero-card,
    .role-focus-card,
    .role-stat-card,
    .role-panel {
        animation: roleFadeUp .65s ease both;
    }
    @media (max-width: 1399.98px) {
        .role-hero {
            grid-template-columns: minmax(0, 1.3fr) minmax(320px, 1fr);
        }
        .role-focus-card {
            grid-column: span 2;
        }
    }
    @media (max-width: 1199.98px) {
        .role-stat-grid,
        .role-pie-grid,
        .role-feature-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 991.98px) {
        .role-hero {
            grid-template-columns: 1fr;
        }
        .role-focus-card {
            grid-column: span 1;
        }
    }
    @media (max-width: 575.98px) {
        .container-fluid {
            padding-left: .75rem;
            padding-right: .75rem;
        }
        .role-stat-grid,
        .role-pie-grid,
        .role-feature-grid {
            grid-template-columns: 1fr;
        }
        .role-panel-head {
            align-items: flex-start;
            flex-direction: column;
        }
        .role-date-filter {
            width: 100%;
            justify-content: space-between;
        }
        .role-date-inputs {
            width: 100%;
        }
        .role-date-input,
        .role-date-btn {
            width: 100%;
        }
    }

    /* HR Clock in Card & Components */
    .role-clock-card {
        border-radius: 24px;
        padding: clamp(1.25rem, 2.2vw, 1.75rem);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }
    .hr-clock {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }
    .hr-clock-time {
        font-size: 1.75rem;
        font-weight: 900;
        line-height: 1.1;
        color: var(--bx-ink, #10142C);
        letter-spacing: -0.02em;
        font-variant-numeric: tabular-nums;
    }
    .hr-clock-date {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--bx-ink-muted, #545D82);
    }
    .employee-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 12px;
        padding: 12px 18px;
        color: #fff;
        font-weight: 800;
        font-size: 0.95rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .employee-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(0, 0, 0, 0.18);
    }
    .employee-action-btn.is-in {
        background: linear-gradient(135deg, #10B981, #059669);
    }
    .employee-action-btn.is-out {
        background: linear-gradient(135deg, #EF4444, #DC2626);
    }
    .employee-complete {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        padding: 11px 16px;
        color: #fff !important;
        background: linear-gradient(135deg, #10B981, #059669);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.24);
        font-weight: 800;
    }
    .clock-requirements {
        margin-top: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--bx-ink-muted, #545D82);
    }
    .clock-status-line {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
        color: var(--bx-ink-muted, #545D82);
    }
    .clock-status-line.is-error {
        color: #EF4444 !important;
    }
    .clock-attendance-card {
        margin-top: 10px;
        width: 100%;
        padding: 12px;
        border-radius: 16px;
        background: rgba(47, 107, 255, 0.04);
        border: 1px solid rgba(47, 107, 255, 0.12);
    }
    .clock-attendance-card img {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(47, 107, 255, 0.15);
        margin-bottom: 8px;
    }
    .clock-location-note {
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        background: rgba(47, 107, 255, 0.06);
        border: 1px solid rgba(47, 107, 255, 0.12);
        color: var(--bx-ink, #10142C);
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.35;
    }
    .clock-location-note span {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--bx-primary, #2F6BFF);
        font-weight: 800;
        margin-bottom: 2px;
    }
    .clock-policy-warning-text {
        margin: 6px 0 0;
        color: #EF4444 !important;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1.35;
    }
    .clock-live-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: 10px;
    }
    .clock-live-box {
        padding: 8px 10px;
        border-radius: 10px;
        background: rgba(47, 107, 255, 0.06);
        border: 1px solid rgba(47, 107, 255, 0.1);
    }
    .clock-live-box span {
        display: block;
        font-size: 0.7rem;
        color: var(--bx-ink-muted, #545D82);
        font-weight: 700;
        margin-bottom: 2px;
    }
    .clock-live-box strong {
        font-size: 0.96rem;
        color: var(--bx-primary, #2F6BFF);
        font-weight: 800;
    }
    .clock-camera-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(8, 15, 30, 0.75);
        backdrop-filter: blur(4px);
        z-index: 9999;
    }
    .clock-camera-modal.is-open {
        display: flex;
    }
    .clock-camera-panel {
        width: min(620px, 100%);
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 28px 80px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }
    .clock-camera-header,
    .clock-camera-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 18px;
        border-bottom: 1px solid rgba(16, 20, 44, 0.08);
    }
    .clock-camera-footer {
        border-top: 1px solid rgba(16, 20, 44, 0.08);
        border-bottom: 0;
        flex-wrap: wrap;
    }
    .clock-camera-body {
        padding: 16px;
        background: #f8fafc;
    }
    .clock-camera-preview {
        width: 100%;
        aspect-ratio: 4 / 3;
        background: #0f172a;
        border-radius: 14px;
        overflow: hidden;
    }
    .clock-camera-preview video,
    .clock-camera-preview canvas,
    .clock-camera-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .clock-camera-preview canvas,
    .clock-camera-preview img {
        display: none;
    }
    .clock-camera-preview.has-photo video {
        display: none;
    }
    .clock-camera-preview.has-photo img {
        display: block;
    }
    .clock-modal-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 0;
        border-radius: 10px;
        padding: 9px 14px;
        font-weight: 800;
        font-size: 0.85rem;
        color: #fff;
        background: #2F6BFF;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .clock-modal-btn:hover {
        transform: translateY(-1px);
        opacity: 0.95;
    }
    .clock-modal-btn.secondary {
        background: #64748B;
    }
    .clock-modal-btn.danger {
        background: #EF4444;
    }
    .clock-modal-btn.success {
        background: #10B981;
    }

    /* HR Dashboard Dark Mode Overrides */
    html[data-pms-theme="dark"] .role-dashboard-shell,
    html[data-theme="dark"] .role-dashboard-shell {
        background: linear-gradient(135deg, #070B1A 0%, #0F1530 52%, #141B3D 100%);
        border-color: rgba(238, 241, 251, 0.09);
    }
    html[data-pms-theme="dark"] .role-hero-card,
    html[data-pms-theme="dark"] .role-panel,
    html[data-pms-theme="dark"] .role-stat-card,
    html[data-pms-theme="dark"] .role-feature-card,
    html[data-pms-theme="dark"] .role-pie-card,
    html[data-theme="dark"] .role-hero-card,
    html[data-theme="dark"] .role-panel,
    html[data-theme="dark"] .role-stat-card,
    html[data-theme="dark"] .role-feature-card,
    html[data-theme="dark"] .role-pie-card {
        background: var(--bx-surface, #0F1530);
        border-color: rgba(238, 241, 251, 0.09);
        box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
    }
    html[data-pms-theme="dark"] .role-btn-light,
    html[data-theme="dark"] .role-btn-light {
        background: var(--bx-surface-2, #141B3D);
        border-color: rgba(238, 241, 251, 0.12);
        color: #EEF1FB;
    }
    html[data-pms-theme="dark"] .role-btn-light:hover,
    html[data-theme="dark"] .role-btn-light:hover {
        border-color: var(--bx-accent, #22D3EE);
        color: var(--bx-accent, #22D3EE);
    }
    html[data-pms-theme="dark"] .role-date-filter,
    html[data-theme="dark"] .role-date-filter {
        background: rgba(20, 27, 61, 0.85);
        border-color: rgba(238, 241, 251, 0.14);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }
    html[data-pms-theme="dark"] .role-date-label,
    html[data-theme="dark"] .role-date-label {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .role-date-separator,
    html[data-theme="dark"] .role-date-separator {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .role-date-input,
    html[data-theme="dark"] .role-date-input {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.18) !important;
        color: #EEF1FB !important;
        color-scheme: dark;
    }
    html[data-pms-theme="dark"] .role-date-input:focus,
    html[data-theme="dark"] .role-date-input:focus {
        border-color: #2F6BFF !important;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.25) !important;
    }
    html[data-pms-theme="dark"] .card-header.bg-white,
    html[data-theme="dark"] .card-header.bg-white {
        background: var(--bx-surface, #0F1530) !important;
        color: #EEF1FB !important;
        border-bottom-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .hr-clock-time,
    html[data-theme="dark"] .hr-clock-time {
        color: #EEF1FB;
    }
    html[data-pms-theme="dark"] .hr-clock-date,
    html[data-theme="dark"] .hr-clock-date,
    html[data-pms-theme="dark"] .clock-requirements,
    html[data-theme="dark"] .clock-requirements,
    html[data-pms-theme="dark"] .clock-status-line,
    html[data-theme="dark"] .clock-status-line {
        color: #9AA3C7;
    }
    html[data-pms-theme="dark"] .clock-attendance-card,
    html[data-theme="dark"] .clock-attendance-card {
        background: rgba(238, 241, 251, 0.04);
        border-color: rgba(238, 241, 251, 0.08);
    }
    html[data-pms-theme="dark"] .clock-location-note,
    html[data-theme="dark"] .clock-location-note {
        background: rgba(238, 241, 251, 0.06);
        border-color: rgba(238, 241, 251, 0.1);
        color: #EEF1FB;
    }
    html[data-pms-theme="dark"] .clock-live-box,
    html[data-theme="dark"] .clock-live-box {
        background: rgba(238, 241, 251, 0.06);
        border-color: rgba(238, 241, 251, 0.08);
    }
    html[data-pms-theme="dark"] .clock-live-box span,
    html[data-theme="dark"] .clock-live-box span {
        color: #9AA3C7;
    }
    html[data-pms-theme="dark"] .clock-live-box strong,
    html[data-theme="dark"] .clock-live-box strong {
        color: #22D3EE;
    }
    html[data-pms-theme="dark"] .clock-camera-panel,
    html[data-theme="dark"] .clock-camera-panel {
        background: #0F1530;
        color: #EEF1FB;
    }
    html[data-pms-theme="dark"] .clock-camera-header,
    html[data-pms-theme="dark"] .clock-camera-footer,
    html[data-theme="dark"] .clock-camera-header,
    html[data-theme="dark"] .clock-camera-footer {
        border-color: rgba(238, 241, 251, 0.09);
    }
    html[data-pms-theme="dark"] .clock-camera-body,
    html[data-theme="dark"] .clock-camera-body {
        background: #070B1A;
    }
</style>
<div class="container-fluid">

    <section class="role-dashboard-shell">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="role-hero">
            <div class="role-hero-card">
                <span class="role-eyebrow">{{ $roleName }} command center</span>
                <h1>{{ $roleName }} Dashboard</h1>
                <p>Monitor workforce health, leave flow, attendance, tasks, and HR analytics from one responsive workspace.</p>
                <div class="role-hero-actions">
                    @if(Route::has('leaves.create'))
                        <a href="{{ route('leaves.create') }}" class="role-btn role-btn-primary"><i class="bx bx-calendar-plus"></i> Apply Leave</a>
                    @endif
                    @if(Route::has('projects.create'))
                        <a href="{{ route('projects.create') }}" class="role-btn role-btn-light"><i class="bx bx-plus"></i> Add Project</a>
                    @endif
                    @if(Route::has('tasks.create'))
                        <a href="{{ route('tasks.create') }}" class="role-btn role-btn-light"><i class="bx bx-task"></i> New Task</a>
                    @endif
                    @if(Route::has('projects.index') && ($canSeeModule('projects') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true)))
                        <a href="{{ route('projects.index') }}" class="role-btn role-btn-light"><i class="bx bx-briefcase-alt-2"></i> Projects</a>
                    @endif
                    @if(Route::has('tasks.index') && ($canSeeModule('tasks') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true)))
                        <a href="{{ route('tasks.index') }}" class="role-btn role-btn-light"><i class="bx bx-task"></i> Tasks</a>
                    @endif
                    @if(Route::has('timelogs.index') && ($canSeeModule('timelogs') || in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true)))
                        <a href="{{ route('timelogs.index') }}" class="role-btn role-btn-light"><i class="bx bx-time-five"></i> Timesheet</a>
                    @endif
                    @if(Route::has('employees.index') && $canSeeModule('employees'))
                        <a href="{{ route('employees.index') }}" class="role-btn role-btn-light"><i class="bx bx-group"></i> Employees</a>
                    @endif
                    @if(Route::has('leaves.index') && $canSeeModule('leaves'))
                        <a href="{{ route('leaves.index') }}" class="role-btn role-btn-light"><i class="bx bx-calendar-minus"></i> Leaves</a>
                    @endif
                    @if(Route::has('attendance.report') && $canSeeModule('attendance'))
                        <a href="{{ route('attendance.report') }}" class="role-btn role-btn-light"><i class="bx bx-bar-chart-alt-2"></i> Reports</a>
                    @endif
                </div>
                <form method="GET" class="role-date-filter">
                    <span class="role-date-label"><i class="bx bx-calendar me-1"></i> Date Range</span>
                    <div class="role-date-inputs">
                        <input type="date" name="start_date" class="role-date-input" value="{{ $startDate }}">
                        <span class="role-date-separator">to</span>
                        <input type="date" name="end_date" class="role-date-input" value="{{ $endDate }}">
                    </div>
                    <button type="submit" class="role-date-btn">
                        <i class="bx bx-filter-alt"></i> Filter
                    </button>
                </form>
            </div>

            <!-- HR Clock In/Out Card -->
            <div class="role-panel role-clock-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="role-eyebrow mb-0"><i class="bx bx-time-five me-1"></i> My Attendance</span>
                    <div class="text-end">
                        <div class="hr-clock-time employee-time" id="hrClockTime" data-live-clock="time">{{ now()->format('h:i:s A') }}</div>
                        <div class="hr-clock-date text-muted small" id="hrClockDate" data-live-clock="date">{{ now()->format('l, d M Y') }}</div>
                    </div>
                </div>

                <div class="employee-clock hr-clock">
                    @if ($attendance && $attendance->clock_in && !$attendance->clock_out)
                        <form method="POST" action="{{ route('dashboard.clockout') }}">
                            @csrf
                            <button class="employee-action-btn is-out w-100 justify-content-center" type="submit">
                                <i class="bx bx-log-out-circle"></i> Clock Out
                            </button>
                        </form>
                    @elseif(!$attendance || !$attendance->clock_in)
                        <form method="POST" action="{{ route('dashboard.clockin') }}" id="employeeClockInForm">
                            @csrf
                            <input type="hidden" name="clock_in_latitude" id="clockInLatitude">
                            <input type="hidden" name="clock_in_longitude" id="clockInLongitude">
                            <input type="hidden" name="clock_in_accuracy" id="clockInAccuracy">
                            <input type="hidden" name="clock_in_address" id="clockInAddress">
                            <input type="hidden" name="clock_in_selfie" id="clockInSelfie">
                            <button class="employee-action-btn is-in w-100 justify-content-center" type="submit" id="employeeClockInButton">
                                <i class="bx bx-log-in-circle"></i> Clock In
                            </button>
                        </form>
                        <div class="clock-requirements" id="clockRequirementStatus">
                            <div class="clock-status-line"><i class="bx bx-map-pin"></i><span>Share current location to save your clock-in place.</span></div>
                            <div class="clock-status-line"><i class="bx bx-camera"></i><span>Capture photo to complete clock in.</span></div>
                        </div>
                    @else
                        <span class="employee-complete justify-content-center"><i class="bx bx-check-circle"></i> Shift Completed</span>
                    @endif

                    @if($attendance && $attendance->clock_in)
                        <div class="clock-attendance-card">
                            @if($attendance->clock_in_photo && !$attendance->clock_out)
                                <img src="{{ asset($attendance->clock_in_photo) }}" alt="Clock in photo">
                            @endif
                            @php
                                $clockInLocationText = $attendance->clock_in_address
                                    ?: $attendance->location
                                    ?: (
                                        $attendance->clock_in_latitude && $attendance->clock_in_longitude
                                            ? 'Current location: ' . $attendance->clock_in_latitude . ', ' . $attendance->clock_in_longitude
                                            : null
                                    );
                                $clockedInOutsideOffice = strtolower((string) $attendance->work_from_type) === 'field';
                                $attendanceDateStr = ($attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : (string) $attendance->date);
                            @endphp
                            @if($clockInLocationText)
                                <div class="clock-location-note">
                                    <span>Clock-in location</span>
                                    {{ $clockInLocationText }}
                                </div>
                            @endif
                            @if($clockedInOutsideOffice)
                                <p class="clock-policy-warning-text">
                                    <i class="bx bx-error-circle me-1"></i>
                                    You did not clock in from the organization area. This may affect your appraisal later. Please maintain the office policy properly.
                                </p>
                            @endif
                            <div class="clock-live-grid">
                                <div class="clock-live-box">
                                    <span>Current Time</span>
                                    <strong id="employeeIstClock">{{ now()->format('h:i:s A') }}</strong>
                                </div>
                                <div class="clock-live-box">
                                    <span>{{ $attendance->clock_out ? 'Worked Time' : 'Working Time' }}</span>
                                    <strong
                                        id="employeeWorkTimer"
                                        data-clock-in="{{ \Carbon\Carbon::parse($attendanceDateStr . ' ' . $attendance->clock_in, config('app.timezone', 'Asia/Kolkata'))->toIso8601String() }}"
                                        data-clock-out="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendanceDateStr . ' ' . $attendance->clock_out, config('app.timezone', 'Asia/Kolkata'))->toIso8601String() : '' }}"
                                        data-fixed-duration="{{ $attendance->clock_out ? $workedDurationLabel : '' }}"
                                    >{{ $attendance->clock_out ? $workedDurationLabel : '00:00:00' }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="role-panel role-focus-card">
                <div class="role-gauge" style="--percent: {{ max(0, min(100, $attendancePercent)) }};">
                    <strong>{{ $attendancePercent }}%</strong>
                </div>
                <div class="text-center">
                    <h5 class="fw-bold mb-1">Today Presence</h5>
                    <p class="text-muted mb-0">{{ $todayPresent ?? 0 }} present, {{ $onLeaveToday ?? 0 }} on leave, {{ $absentToday }} absent</p>
                </div>
            </div>
        </div>

        <div class="role-stat-grid">
            <div class="role-stat-card"><span>Total Projects</span><strong>{{ $totalProjects ?? 0 }}</strong></div>
            <div class="role-stat-card"><span>Pending Tasks</span><strong>{{ $pendingTasks ?? 0 }}</strong></div>
            <div class="role-stat-card"><span>Logged Work</span><strong>{{ $totalTimelogHours ?? 0 }}h</strong></div>
            <div class="role-stat-card"><span>Total Employees</span><strong>{{ $totalEmployees ?? 0 }}</strong></div>
        </div>

        <div class="role-panel">
            <div class="role-panel-head">
                <div>
                    <h3>HR Overview Pie Charts</h3>
                    <p>Quick workforce, attendance, leave, and task signals.</p>
                </div>
            </div>
            <div class="role-pie-grid">
                @foreach($rolePieCharts as $chart)
                    @if(Route::has($chart['route']) && $canSeeModule($chart['slug']))
                        <div class="role-pie-card">
                            <div class="role-donut" style="--percent: {{ max(3, min(100, $chart['percent'])) }}; --accent: {{ $chart['color'] }};">
                                <strong>{{ $chart['value'] }}</strong>
                            </div>
                            <h4>{{ $chart['label'] }}</h4>
                            <p>{{ $chart['hint'] }}</p>
                            <a href="{{ route($chart['route']) }}">Open {{ $chart['label'] }}</a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- Additional HR Metrics -->
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['title' => 'Employee Exits', 'value' => $exits],
                ['title' => 'Approved Leaves', 'value' => $approvedLeaves],
                ['title' => 'Average Attendance', 'value' => $averageAttendance . '%'],
                ['title' => 'On Leave Today', 'value' => $onLeaveToday ?? 0],
            ];
        @endphp

        @foreach ($cards as $card)
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-4">
                    <h6 class="text-muted">{{ $card['title'] }}</h6>
                    <h3 class="fw-bold mb-0">{{ $card['value'] }}</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!--chart-->

   <div class="row mt-4">
    <!-- Department-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Department-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;"> <!-- Fixed height -->
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Designation-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Designation-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="designationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Gender-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Gender-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Role-wise Chart -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Role-wise Employees</h6>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="roleChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Charts -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Monthly Joinings</h6>
                </div>
                <div class="card-body">
                    <div style="height:250px">
                        <canvas id="joiningChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Monthly Attritions</h6>
                </div>
                <div class="card-body">
                    <div style="height:250px">
                        <canvas id="exitChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaves Taken -->
<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Leaves Taken</h6>
            </div>
            <br>
            <div class="card-body">
                <ul class="list-group">
                    @forelse ($leavesTaken as $leave)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $leave->user->name }}<br>
                            <small>{{ $leave->user->employeeDetail->salutation ?? '' }} {{ $leave->user->employeeDetail->full_name ?? '' }}</small><br>
                            <small>{{ $leave->user->employeeDetail->designation->name ?? '-' }}</small>
                            <span class="badge bg-primary rounded-pill">{{ $leave->total }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">- No leave records found. -</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Late Attendance -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-semibold">Late Attendance</h6>
            </div>
            <br>
            <div class="card-body">
                <ul class="list-group">
                    @forelse ($lateAttendances as $userId => $attendances)
                        @php $user = $attendances->first()->user ?? null; @endphp
                        @if ($user)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $user->name }}<br>
                                <small>{{ $user->employeeDetail->salutation ?? '' }} {{ $user->employeeDetail->full_name ?? '' }}</small><br>
                                <small>{{ $user->employeeDetail->designation->name ?? '-' }}</small>
                                <span class="badge bg-danger rounded-pill">{{ $attendances->count() }}</span>
                            </li>
                        @endif
                    @empty
                        <li class="list-group-item text-muted">- No late attendance found. -</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============ WORK MANAGEMENT SECTION (PROJECTS, TASKS, RECENT ACTIVITIES, TIMESHEET) ============ -->
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-store text-primary me-2"></i>Work Section (Projects & Tasks)</h6>
                    <small class="text-muted">Live view of projects, pending task queue, timesheets, and recent deliverables.</small>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
                    @if(Route::has('projects.create'))
                        <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary px-3 py-2 text-nowrap d-inline-flex align-items-center gap-1" style="min-width: max-content;">
                            <i class="bx bx-plus me-1"></i> Add Project
                        </a>
                    @endif
                    @if(Route::has('tasks.create'))
                        <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-outline-primary px-3 py-2 text-nowrap d-inline-flex align-items-center gap-1" style="min-width: max-content;">
                            <i class="bx bx-task me-1"></i> New Task
                        </a>
                    @endif
                    @if(Route::has('timelogs.create'))
                        <a href="{{ route('timelogs.create') }}" class="btn btn-sm btn-outline-success px-3 py-2 text-nowrap d-inline-flex align-items-center gap-1" style="min-width: max-content;">
                            <i class="bx bx-time-five me-1"></i> Log Time
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-4">
                    <!-- Left column: Active Projects & Timesheet stats -->
                    <div class="col-lg-4">
                        <!-- Project Highlights Card -->
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-briefcase-alt-2 text-primary me-1"></i> Projects Summary</h6>
                                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-link text-primary p-0 text-nowrap">View All <i class="bx bx-chevron-right"></i></a>
                            </div>
                            <div class="row g-2 text-center my-2">
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border">
                                        <span class="text-muted d-block small">Total</span>
                                        <strong class="h5 mb-0 text-primary">{{ $totalProjects ?? 0 }}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border">
                                        <span class="text-muted d-block small">Active</span>
                                        <strong class="h5 mb-0 text-success">{{ $activeProjects ?? 0 }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary py-2 text-nowrap d-flex align-items-center justify-content-center">
                                    <i class="bx bx-folder-open me-1"></i> Open Projects Workspace
                                </a>
                            </div>
                        </div>

                        <!-- Timesheet Highlights Card -->
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-time-five text-success me-1"></i> Timesheet Overview</h6>
                                <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-link text-success p-0 text-nowrap">Timesheets <i class="bx bx-chevron-right"></i></a>
                            </div>
                            <div class="row g-2 text-center my-2">
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border">
                                        <span class="text-muted d-block small">Total Hours</span>
                                        <strong class="h5 mb-0 text-success">{{ $totalTimelogHours ?? 0 }}h</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white p-2 rounded border">
                                        <span class="text-muted d-block small">Logged Entries</span>
                                        <strong class="h5 mb-0 text-dark">{{ $totalTimelogsCount ?? 0 }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-outline-success py-2 text-nowrap d-flex align-items-center justify-content-center">
                                    <i class="bx bx-list-check me-1"></i> Review Timesheets
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Middle column: Pending Tasks List -->
                    <div class="col-lg-4">
                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-list-check text-warning me-1"></i> Pending Tasks Queue</h6>
                                <a href="{{ route('tasks.index', ['exclude_completed' => true]) }}" class="btn btn-sm btn-link text-primary p-0">View All <i class="bx bx-chevron-right"></i></a>
                            </div>
                            <div class="d-flex flex-column gap-2" style="max-height: 290px; overflow-y: auto;">
                                @forelse($pendingTasksTotal ?? [] as $task)
                                    <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-start">
                                        <div class="me-2 text-truncate">
                                            <a href="{{ route('tasks.show', $task->id) }}" class="fw-semibold text-dark text-decoration-none d-block text-truncate" title="{{ $task->title }}">
                                                {{ $task->title ?? 'N/A' }}
                                            </a>
                                            <small class="text-muted d-block text-truncate">
                                                <i class="bx bx-folder me-1"></i>{{ $task->project->name ?? 'No project' }}
                                            </small>
                                        </div>
                                        <span class="badge bg-warning text-dark text-capitalize" style="font-size: 0.72rem;">
                                            {{ $task->status ?? 'Pending' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="bx bx-check-circle fs-2 text-success mb-2"></i>
                                        <p class="mb-0 small">No pending tasks! All caught up! 🎉</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Recent Project Activities -->
                    <div class="col-lg-4">
                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-pulse text-info me-1"></i> Recent Project Activities</h6>
                                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-link text-primary p-0">All Projects <i class="bx bx-chevron-right"></i></a>
                            </div>
                            <div class="d-flex flex-column gap-2" style="max-height: 290px; overflow-y: auto;">
                                @forelse($activities ?? [] as $activity)
                                    <div class="p-2 border-bottom pb-2">
                                        <div class="text-dark small fw-semibold">{{ $activity->activity ?? 'No activity' }}</div>
                                        <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                                            <span><i class="bx bx-folder me-1"></i>{{ $activity->project_name ?? 'N/A' }}</span>
                                            <span><i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($activity->created_at ?? now())->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="bx bx-time fs-2 text-muted mb-2"></i>
                                        <p class="mb-0 small">No recent activities logged.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ============ END WORK MANAGEMENT SECTION ============ -->

<!-- ============ LEAVE MANAGEMENT SECTION - ADDED HERE ============ -->
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Leave Management</h6>
                    <div>
                        <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bi bi-list-ul me-1"></i> All Leaves
                        </a>
                        <a href="{{ route('leaves.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> New Leave
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Leave Statistics -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 border rounded">
                            <div class="text-warning fw-bold h4 mb-1">{{ $pendingLeaves ?? 0 }}</div>
                            <small class="text-muted">Pending Leaves</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 border rounded">
                            <div class="text-success fw-bold h4 mb-1">{{ $approvedLeaves ?? 0 }}</div>
                            <small class="text-muted">Approved Leaves</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 border rounded">
                            <div class="text-info fw-bold h4 mb-1">{{ $onLeaveToday ?? 0 }}</div>
                            <small class="text-muted">On Leave Today</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="text-center p-3 border rounded">
                            @php
                                $absentToday = $totalEmployees - $todayPresent - $onLeaveToday;
                            @endphp
                            <div class="text-danger fw-bold h4 mb-1">{{ $absentToday ?? 0 }}</div>
                            <small class="text-muted">Absent Today</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('leaves.index', ['status' => 'pending']) }}"
                       class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-clock-history me-1"></i> Pending Approvals
                    </a>
                    <a href="{{ route('leaves.calendar') }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-calendar-week me-1"></i> Calendar View
                    </a>
                    <a href="{{ route('admin.leave.report') }}"
                       class="btn btn-sm btn-outline-info">
                        <i class="bi bi-graph-up me-1"></i> Reports
                    </a>
                    <a href="{{ route('leaves.create') }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Leave Request
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="clock-camera-modal" id="clockCameraModal" aria-hidden="true">
        <div class="clock-camera-panel">
            <div class="clock-camera-header">
                <h5 class="mb-0 fw-bold">Capture Clock In Photo</h5>
                <button type="button" class="clock-modal-btn danger" id="clockCameraClose"><i class="bx bx-x"></i> Close</button>
            </div>
            <div class="clock-camera-body">
                <div class="clock-camera-preview" id="clockCameraPreview">
                    <video id="clockCameraVideo" autoplay playsinline muted></video>
                    <canvas id="clockCameraCanvas"></canvas>
                    <img id="clockCameraPhoto" alt="Captured clock in photo">
                </div>
                <p class="text-muted mt-3 mb-0 small">Take a clear face photo. You can flip camera on mobile, retake, then use photo for clock in.</p>
            </div>
            <div class="clock-camera-footer">
                <button type="button" class="clock-modal-btn secondary" id="clockCameraFlip"><i class="bx bx-refresh"></i> Flip</button>
                <button type="button" class="clock-modal-btn" id="clockCameraCapture"><i class="bx bx-camera"></i> Capture</button>
                <button type="button" class="clock-modal-btn secondary" id="clockCameraRetake"><i class="bx bx-undo"></i> Retake</button>
                <button type="button" class="clock-modal-btn success" id="clockCameraUse"><i class="bx bx-check"></i> Use Photo & Clock In</button>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const genderChart = new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($genderCounts->keys()) !!},
            datasets: [{
                data: {!! json_encode($genderCounts->values()) !!},
                backgroundColor: ['#2F6BFF', '#22D3EE', '#8B5CF6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    const roleChart = new Chart(document.getElementById('roleChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($roleCounts->keys()) !!},
            datasets: [{
                data: {!! json_encode($roleCounts->values()) !!},
                backgroundColor: ['#2F6BFF', '#8B5CF6', '#22D3EE', '#10B981', '#F59E0B']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>

<script>
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const designationCtx = document.getElementById('designationChart').getContext('2d');

   const departmentChart = new Chart(document.getElementById('departmentChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode($departmentWise->map(fn($d) => $d->department_name ?? 'N/A')) !!},
        datasets: [{
            data: {!! json_encode($departmentWise->pluck('total')) !!},
            backgroundColor: ['#2F6BFF', '#8B5CF6', '#22D3EE', '#10B981', '#F59E0B', '#6366F1']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
  });


    const designationChart = new Chart(designationCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($designationWise->map(fn($d) => $d->designation->name ?? 'N/A')) !!},
            datasets: [{
                data: {!! json_encode($designationWise->pluck('total')) !!},
                backgroundColor: ['#2F6BFF', '#22D3EE', '#8B5CF6', '#10B981', '#F59E0B', '#EC4899']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

<script>
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const joiningData = {!! json_encode(array_values($monthlyJoinings->toArray())) !!};
    const exitData = {!! json_encode(array_values($monthlyAttrition->toArray())) !!};

    const chartOptions = {
        type: 'bar',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    };

    new Chart(document.getElementById('joiningChart').getContext('2d'), {
        ...chartOptions,
        data: {
            labels: months,
            datasets: [{
                label: 'Joinings',
                data: joiningData,
                backgroundColor: 'rgba(47, 107, 255, 0.85)',
                borderRadius: 6
            }]
        }
    });

    new Chart(document.getElementById('exitChart').getContext('2d'), {
        ...chartOptions,
        data: {
            labels: months,
            datasets: [{
                label: 'Attritions',
                data: exitData,
                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                borderRadius: 6
            }]
        }
    });
</script>

<script>
(function () {
    const officeLocation = {
        lat: @json($officeLatitude),
        lng: @json($officeLongitude),
        radius: @json($officeRadiusMeters),
        address: @json($officeAddress)
    };

    const clockInForm = document.getElementById('employeeClockInForm');
    const clockInButton = document.getElementById('employeeClockInButton');
    const requirementStatus = document.getElementById('clockRequirementStatus');
    const modal = document.getElementById('clockCameraModal');
    const video = document.getElementById('clockCameraVideo');
    const canvas = document.getElementById('clockCameraCanvas');
    const photo = document.getElementById('clockCameraPhoto');
    const preview = document.getElementById('clockCameraPreview');
    const closeCamera = document.getElementById('clockCameraClose');
    const flipCamera = document.getElementById('clockCameraFlip');
    const captureCamera = document.getElementById('clockCameraCapture');
    const retakeCamera = document.getElementById('clockCameraRetake');
    const useCamera = document.getElementById('clockCameraUse');
    const latitudeInput = document.getElementById('clockInLatitude');
    const longitudeInput = document.getElementById('clockInLongitude');
    const accuracyInput = document.getElementById('clockInAccuracy');
    const addressInput = document.getElementById('clockInAddress');
    const selfieInput = document.getElementById('clockInSelfie');
    let cameraStream = null;
    let cameraFacingMode = 'user';
    let capturedSelfie = '';
    let canSubmitClockIn = false;

    const setClockStatus = (message, type = 'info') => {
        if (!requirementStatus) {
            return;
        }

        const icon = type === 'success' ? 'bx-check-circle' : (type === 'error' ? 'bx-error-circle' : 'bx-info-circle');
        const className = type === 'error' ? 'clock-status-line is-error' : 'clock-status-line';
        requirementStatus.innerHTML = `<div class="${className}"><i class="bx ${icon}"></i><span>${message}</span></div>`;
    };

    const distanceInMeters = (lat1, lng1, lat2, lng2) => {
        const earthRadius = 6371000;
        const toRad = value => value * Math.PI / 180;
        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
            + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
            * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    };

    const stopCamera = () => {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
    };

    const startCamera = async () => {
        stopCamera();
        capturedSelfie = '';
        preview?.classList.remove('has-photo');

        if (video) {
            video.style.transform = cameraFacingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
        }

        const constraintsList = [
            { video: { facingMode: { exact: cameraFacingMode }, width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false },
            { video: { facingMode: cameraFacingMode, width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false },
            { video: { facingMode: cameraFacingMode }, audio: false },
            { video: true, audio: false }
        ];

        let stream = null;
        for (const constraints of constraintsList) {
            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                if (stream) break;
            } catch (e) {
                // Try next fallback constraint
            }
        }

        if (!stream) {
            throw new Error('Unable to access camera');
        }

        cameraStream = stream;
        if (video) {
            video.srcObject = cameraStream;
            try {
                await video.play();
            } catch (e) {}
        }
    };

    const openCamera = async () => {
        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        await startCamera();
    };

    const closeCameraModal = () => {
        stopCamera();
        modal?.classList.remove('is-open');
        modal?.setAttribute('aria-hidden', 'true');
    };

    const requestLocation = () => new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Location is not supported in this browser.'));
            return;
        }

        navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
    });

    const compactAddress = address => {
        if (!address || typeof address !== 'object') {
            return '';
        }

        const parts = [
            address.road,
            address.neighbourhood || address.suburb || address.quarter,
            address.city || address.town || address.village || address.municipality,
            address.county || address.state_district,
            address.state,
            address.postcode
        ];

        return [...new Set(parts.filter(Boolean))]
            .join(', ')
            .slice(0, 180);
    };

    const reverseGeocodeLocation = async (lat, lng) => {
        const url = new URL('https://nominatim.openstreetmap.org/reverse');
        url.searchParams.set('format', 'jsonv2');
        url.searchParams.set('lat', lat);
        url.searchParams.set('lon', lng);
        url.searchParams.set('zoom', '18');
        url.searchParams.set('addressdetails', '1');

        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Location name lookup failed.');
        }

        const data = await response.json();
        return compactAddress(data.address) || (data.display_name || '').slice(0, 180);
    };

    if (clockInForm) {
        clockInForm.addEventListener('submit', async event => {
            if (canSubmitClockIn) {
                return;
            }

            event.preventDefault();

            try {
                clockInButton.disabled = true;
                setClockStatus('Requesting current location permission...', 'info');
                const position = await requestLocation();
                const currentLat = position.coords.latitude;
                const currentLng = position.coords.longitude;
                const distance = distanceInMeters(officeLocation.lat, officeLocation.lng, currentLat, currentLng);
                const coordinateLabel = `${currentLat.toFixed(8)}, ${currentLng.toFixed(8)} (${distance.toFixed(1)}m from office)`;

                latitudeInput.value = currentLat.toFixed(8);
                longitudeInput.value = currentLng.toFixed(8);
                accuracyInput.value = Math.round(position.coords.accuracy || 0);

                setClockStatus('Finding exact location name...', 'info');
                try {
                    const placeName = await reverseGeocodeLocation(currentLat, currentLng);
                    addressInput.value = placeName
                        ? `${placeName} | ${coordinateLabel}`
                        : `Current location: ${coordinateLabel}`;
                    setClockStatus(`${placeName || 'Location captured'} detected. Opening camera...`, 'success');
                } catch (lookupError) {
                    addressInput.value = `Current location: ${coordinateLabel}`;
                    setClockStatus(`Location captured. Opening camera...`, 'success');
                }

                await openCamera();
            } catch (error) {
                setClockStatus(error.message || 'Please allow current location and camera permission to clock in.', 'error');
            } finally {
                clockInButton.disabled = false;
            }
        });
    }

    closeCamera?.addEventListener('click', closeCameraModal);

    flipCamera?.addEventListener('click', async () => {
        cameraFacingMode = cameraFacingMode === 'user' ? 'environment' : 'user';
        try {
            await startCamera();
        } catch (error) {
            cameraFacingMode = cameraFacingMode === 'user' ? 'environment' : 'user';
            setClockStatus('Camera flip failed. Switched to primary camera.', 'error');
        }
    });

    captureCamera?.addEventListener('click', () => {
        if (!video || !canvas || !photo) {
            return;
        }

        const width = video.videoWidth || 960;
        const height = video.videoHeight || 720;
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, width, height);

        if (cameraFacingMode === 'user') {
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
        }

        ctx.drawImage(video, 0, 0, width, height);
        capturedSelfie = canvas.toDataURL('image/jpeg', 0.92);
        photo.src = capturedSelfie;
        preview?.classList.add('has-photo');
    });

    retakeCamera?.addEventListener('click', () => {
        capturedSelfie = '';
        if (photo) {
            photo.removeAttribute('src');
        }
        preview?.classList.remove('has-photo');
    });

    useCamera?.addEventListener('click', () => {
        if (!capturedSelfie) {
            setClockStatus('Please capture your photo before using it.', 'error');
            return;
        }

        selfieInput.value = capturedSelfie;
        canSubmitClockIn = true;
        setClockStatus('Photo captured. Completing clock in...', 'success');
        closeCameraModal();
        clockInForm.submit();
    });

    const istClock = document.getElementById('employeeIstClock');
    const workTimer = document.getElementById('employeeWorkTimer');
    const hrClockTime = document.getElementById('hrClockTime');
    const hrClockDate = document.getElementById('hrClockDate');

    const formatLocalTime = (date, includeSeconds = true) => {
        try {
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                second: includeSeconds ? '2-digit' : undefined
            };
            const formatted = new Intl.DateTimeFormat(undefined, options).format(date);
            return formatted.replace(/[\u202f\u00a0]/g, ' ').replace(/\b(am|pm)\b/gi, match => match.toUpperCase());
        } catch (e) {
            let h = date.getHours();
            const m = String(date.getMinutes()).padStart(2, '0');
            const s = String(date.getSeconds()).padStart(2, '0');
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            const hStr = String(h).padStart(2, '0');
            return includeSeconds ? `${hStr}:${m}:${s} ${ampm}` : `${hStr}:${m} ${ampm}`;
        }
    };

    const formatLocalDate = (date) => {
        try {
            const parts = new Intl.DateTimeFormat('en-US', {
                weekday: 'long',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }).formatToParts(date);

            let weekday = '', day = '', month = '', year = '';
            for (const p of parts) {
                if (p.type === 'weekday') weekday = p.value;
                else if (p.type === 'day') day = p.value;
                else if (p.type === 'month') month = p.value;
                else if (p.type === 'year') year = p.value;
            }
            return `${weekday}, ${day} ${month} ${year}`;
        } catch (e) {
            return date.toLocaleDateString();
        }
    };

    const updateClockWidgets = () => {
        const now = new Date();

        if (hrClockTime) {
            hrClockTime.textContent = formatLocalTime(now, true);
        }

        if (hrClockDate) {
            hrClockDate.textContent = formatLocalDate(now);
        }

        if (istClock) {
            istClock.textContent = formatLocalTime(now, true);
        }

        document.querySelectorAll('[data-live-clock="time"]').forEach(el => {
            if (el !== hrClockTime && el !== istClock) el.textContent = formatLocalTime(now, true);
        });

        document.querySelectorAll('[data-live-clock="date"]').forEach(el => {
            if (el !== hrClockDate) el.textContent = formatLocalDate(now);
        });

        if (workTimer && workTimer.dataset.clockIn) {
            if (workTimer.dataset.fixedDuration) {
                workTimer.textContent = workTimer.dataset.fixedDuration;
                return;
            }

            const started = new Date(workTimer.dataset.clockIn);
            const ended = workTimer.dataset.clockOut ? new Date(workTimer.dataset.clockOut) : now;
            if (workTimer.dataset.clockOut && ended < started) {
                ended.setDate(ended.getDate() + 1);
            }
            const diffSeconds = Math.max(0, Math.floor((ended - started) / 1000));
            const hours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diffSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(diffSeconds % 60).padStart(2, '0');
            workTimer.textContent = `${hours}:${minutes}:${seconds}`;
        }
    };

    updateClockWidgets();
    setInterval(updateClockWidgets, 1000);

    const loadedDateKey = new Intl.DateTimeFormat('en-CA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).format(new Date());

    setInterval(() => {
        const currentDateKey = new Intl.DateTimeFormat('en-CA', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());

        if (currentDateKey !== loadedDateKey) {
            window.location.reload();
        }
    }, 60000);
})();
</script>
@endsection
