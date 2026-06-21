@extends('admin.layout.app')

@section('title', 'Employee Dashboard')

@section('content')
@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    $clockInLabel = $attendance && $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('h:i A') : 'Not Clocked In';
    $clockOutLabel = $attendance && $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('h:i A') : 'Pending';
    $todayStatus = $attendance && $attendance->status ? str_replace('_', ' ', ucfirst($attendance->status)) : 'No Entry';
    $workedDurationLabel = $attendance && $attendance->clock_in && $attendance->clock_out ? $attendance->total_duration : '00:00:00';

    $weekDays = collect(CarbonPeriod::create(now()->startOfWeek(), now()->endOfWeek()));
    $weeklyLabels = [];
    $weeklyHours = [];
    $attendanceSummary = [
        'Present' => 0,
        'Absent' => 0,
        'On Leave' => 0,
        'Work From Home' => 0,
        'Half Day' => 0,
    ];

    foreach ($weekDays as $day) {
        $entry = $weeklyLogs[$day->toDateString()] ?? null;
        $weeklyLabels[] = $day->format('D');
        $hours = 0;

        if ($entry && $entry->clock_in && $entry->clock_out) {
            $hours = round(((int) ($entry->total_seconds ?? 0)) / 3600, 1);
        }

        $weeklyHours[] = $hours;

        if (!$entry) {
            $attendanceSummary['Absent']++;
            continue;
        }

        $rawStatus = strtolower((string) ($entry->status ?? 'present'));
        if (str_contains($rawStatus, 'leave')) {
            $attendanceSummary['On Leave']++;
        } elseif (str_contains($rawStatus, 'home') || str_contains($rawStatus, 'wfh')) {
            $attendanceSummary['Work From Home']++;
        } elseif (str_contains($rawStatus, 'half')) {
            $attendanceSummary['Half Day']++;
        } elseif (str_contains($rawStatus, 'absent') || str_contains($rawStatus, 'day_off')) {
            $attendanceSummary['Absent']++;
        } else {
            $attendanceSummary['Present']++;
        }
    }

    $taskChart = [
        max(0, (int) $pendingTasksCount),
        max(0, (int) $overdueTasksCount),
        max(0, (int) $openTasksCount - (int) $pendingTasksCount),
    ];
    $projectChart = [
        max(0, (int) $inProgressCount),
        max(0, (int) $overdueCount),
        max(0, (int) $totalProjects - (int) $inProgressCount - (int) $overdueCount),
    ];
    $resolvedTickets = $myTickets->filter(fn ($ticket) => strtolower((string) $ticket->status) === 'resolved')->count();
    $ticketChart = [max(0, (int) $openTicketsCount), max(0, (int) $resolvedTickets)];
    $attendanceChart = array_values($attendanceSummary);
    $weeklyTotalHours = array_sum($weeklyHours);
    $attendancePercent = count($weekDays) > 0 ? round(($attendanceSummary['Present'] / count($weekDays)) * 100) : 0;
    $officeLatitude = 22.49682;
    $officeLongitude = 88.39462;
    $officeRadiusMeters = 10;
    $officeAddress = '11 Hospital Link Road, Satavisha Building, Kolkata, West Bengal 700075';
    $attendancePolicy = $attendancePolicy ?? null;
    $lateTime = $attendancePolicy?->late_time ?? '09:30:00';
    $halfDayMinutes = (int) ($attendancePolicy?->half_day_threshold_minutes ?? 510);
    $dayOffMinutes = (int) ($attendancePolicy?->day_off_threshold_minutes ?? 270);
    $formatPolicyTime = function ($time) {
        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            return '09:30 AM';
        }
    };
    $formatPolicyDuration = function (int $minutes) {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return $mins > 0 ? $hours . ':' . str_pad((string) $mins, 2, '0', STR_PAD_LEFT) . ' h' : $hours . ' h';
    };
    $lateTimeLabel = $formatPolicyTime($lateTime);
    $halfDayLabel = $formatPolicyDuration($halfDayMinutes);
    $dayOffLabel = $formatPolicyDuration($dayOffMinutes);
@endphp

<style>
    :root {
        --employee-primary: #5b5ce2;
        --employee-success: #12a66a;
        --employee-warning: #f59e0b;
        --employee-danger: #e5484d;
        --employee-info: #168aad;
        --employee-ink: #172033;
        --employee-muted: #667085;
        --employee-border: rgba(23, 32, 51, 0.1);
        --employee-surface: rgba(255, 255, 255, 0.92);
        --employee-shadow: 0 18px 45px rgba(28, 37, 65, 0.12);
    }

    .employee-dashboard {
        min-height: 100vh;
        padding: 28px;
        background:
            linear-gradient(135deg, rgba(91, 92, 226, 0.12), rgba(18, 166, 106, 0.08) 38%, rgba(245, 158, 11, 0.08)),
            #f6f8fb;
        color: var(--employee-ink);
    }

    .employee-dashboard a {
        color: inherit;
    }

    .employee-hero,
    .employee-card,
    .employee-panel,
    .employee-table-card {
        background: var(--employee-surface);
        border: 1px solid var(--employee-border);
        border-radius: 18px;
        box-shadow: var(--employee-shadow);
        animation: employeeFadeUp 0.55s ease both;
    }

    .employee-hero {
        position: relative;
        overflow: hidden;
        padding: 28px;
        color: white !important;
        background:
            linear-gradient(135deg, #0f3d91 0%, #0b66c3 48%, #0ea5e9 100%);
    }

    .employee-hero::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 6px;
        background: linear-gradient(90deg, #bfdbfe, #60a5fa, #ffffff);
    }

    .employee-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        color: white !important;
        font-size: 0.9rem;
        font-weight: 800;
    }

    .employee-title {
        margin: 16px 0 8px;
        font-size: clamp(2rem, 4vw, 3.25rem);
        color: white !important;
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1.08;
    }

    .employee-subtitle,
    .employee-muted {
        color: var(--employee-muted);
    }

    .employee-hero .employee-subtitle {
        max-width: 680px;
        color: #fff !important;
        font-size: 1.12rem;
        font-weight: 800;
    }

    .employee-hero .employee-eyebrow,
    .employee-hero .employee-eyebrow i,
    .employee-hero .employee-title {
        color: white  !important;
    }

    .employee-hero .col-lg-8,
    .employee-hero .col-lg-8 *,
    .employee-hero .col-lg-8 p,
    .employee-hero .col-lg-8 span,
    .employee-hero .col-lg-8 h1 {
        color: white !important;
        font-weight: 800;
    }

    main#main .employee-dashboard .employee-hero,
    main#main .employee-dashboard .employee-hero *,
    main#main .employee-dashboard .employee-hero h1,
    main#main .employee-dashboard .employee-hero p,
    main#main .employee-dashboard .employee-hero span,
    main#main .employee-dashboard .employee-hero i,
    main#main .employee-dashboard .employee-hero div {
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    .employee-clock {
        display: grid;
        gap: 12px;
        justify-items: end;
    }

    .employee-time {
        font-size: 2rem;
        color: #fff;
        font-weight: 900;
        line-height: 1;
    }

    .employee-hero .employee-clock,
    .employee-hero .employee-clock div {
        color: #fff;
        font-weight: 800;
    }

    .employee-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-radius: 12px;
        padding: 11px 16px;
        color: #fff;
        font-weight: 800;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .employee-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(0, 0, 0, 0.22);
    }

    .employee-action-btn.is-in {
        background: var(--employee-success);
    }

    .employee-action-btn.is-out {
        background: var(--employee-danger);
    }

    .employee-complete {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        padding: 11px 16px;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        background: rgba(18, 166, 106, 0.92);
        border: 1px solid rgba(255, 255, 255, 0.42);
        box-shadow: 0 14px 30px rgba(18, 166, 106, 0.28);
        font-weight: 800;
    }

    .clock-requirements {
        margin-top: 12px;
        max-width: 360px;
        padding: 0;
        background: transparent;
        border: 0;
        color: #fff;
        font-size: 0.92rem;
        font-weight: 800;
    }

    .clock-status-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        color: #fff;
    }

    .clock-status-line.is-error {
        color: #ff3b3b !important;
        -webkit-text-fill-color: #ff3b3b !important;
    }

    .clock-status-line:last-child {
        margin-bottom: 0;
    }

    .clock-attendance-card {
        margin-top: 14px;
        width: min(360px, 100%);
        padding: 12px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.32);
        color: #fff;
    }

    .clock-attendance-card img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid rgba(255, 255, 255, 0.45);
    }

    .clock-live-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .clock-live-box {
        padding: 10px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
    }

    .clock-live-box strong {
        display: block;
        font-size: 1.05rem;
    }

    .clock-location-note {
        margin-top: 12px;
        padding: 11px 12px;
        border-radius: 12px;
        font-weight: 800;
        line-height: 1.35;
    }

    .clock-location-note {
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #fff;
    }

    .clock-location-note span {
        display: block;
        margin-bottom: 3px;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.82;
    }

    .clock-policy-warning-text {
        margin: 10px 0 0;
        color: #ff2d2d !important;
        -webkit-text-fill-color: #ff2d2d !important;
        font-weight: 900;
        line-height: 1.35;
        text-shadow: none;
    }

    .clock-camera-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(8, 15, 30, 0.72);
        z-index: 9999;
    }

    .clock-camera-modal.is-open {
        display: flex;
    }

    .clock-camera-panel {
        width: min(620px, 100%);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 28px 80px rgba(0, 0, 0, 0.35);
        overflow: hidden;
    }

    .clock-camera-header,
    .clock-camera-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--employee-border);
    }

    .clock-camera-footer {
        border-top: 1px solid var(--employee-border);
        border-bottom: 0;
        flex-wrap: wrap;
    }

    .clock-camera-body {
        padding: 16px;
        background: #f3f6fb;
    }

    .clock-camera-preview {
        width: 100%;
        aspect-ratio: 4 / 3;
        background: #111827;
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
        padding: 9px 13px;
        font-weight: 800;
        color: #fff;
        background: #0b66c3;
    }

    .clock-modal-btn.secondary {
        background: #475467;
    }

    .clock-modal-btn.success {
        background: #12a66a;
    }

    .clock-modal-btn.danger {
        background: #e5484d;
    }

    .employee-welcome-overlay {
        position: fixed;
        inset: 0;
        z-index: 10050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 22px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(8, 47, 73, 0.96), rgba(14, 116, 144, 0.94), rgba(22, 163, 74, 0.92));
        color: #fff;
    }

    .employee-welcome-overlay.is-hidden {
        display: none;
    }

    .employee-welcome-card {
        position: relative;
        width: min(760px, 100%);
        min-height: 520px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 34px;
        border-radius: 26px;
        border: 1px solid rgba(255, 255, 255, 0.32);
        background: rgba(255, 255, 255, 0.12);
        box-shadow: 0 32px 90px rgba(0, 0, 0, 0.34);
        backdrop-filter: blur(16px);
        text-align: center;
        overflow: hidden;
    }

    .employee-welcome-stage,
    .employee-policy-stage {
        position: relative;
        z-index: 2;
        width: 100%;
    }

    .employee-policy-stage {
        display: none;
        text-align: left;
    }

    .employee-welcome-card.show-policy .employee-welcome-stage {
        display: none;
    }

    .employee-welcome-card.show-policy .employee-policy-stage {
        display: block;
    }

    .employee-welcome-logo {
        width: 132px;
        height: 132px;
        object-fit: contain;
        padding: 16px;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 46px rgba(0, 0, 0, 0.22);
        animation: welcomeLogoPop 1.1s ease both, welcomeLogoFloat 4s ease-in-out 1.2s infinite;
    }

    .employee-welcome-title {
        margin: 24px 0 10px;
        color: #fff;
        font-size: clamp(2rem, 5vw, 3.6rem);
        font-weight: 900;
        line-height: 1.05;
    }

    .employee-welcome-message {
        max-width: 610px;
        margin: 0 auto;
        color: #fff;
        font-size: 1.22rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .employee-welcome-countdown {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 22px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        font-weight: 900;
    }

    .welcome-progress {
        width: min(420px, 100%);
        height: 8px;
        margin: 22px auto 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        overflow: hidden;
    }

    .welcome-progress span {
        display: block;
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #bbf7d0, #38bdf8, #fff);
        transition: width 1s linear;
    }

    .welcome-balloon {
        position: absolute;
        bottom: -120px;
        width: 54px;
        height: 70px;
        border-radius: 50% 50% 45% 45%;
        opacity: 0.88;
        animation: balloonRise 9s linear infinite;
    }

    .welcome-balloon::after {
        content: "";
        position: absolute;
        left: 50%;
        top: 68px;
        width: 2px;
        height: 86px;
        background: rgba(255, 255, 255, 0.62);
    }

    .welcome-balloon:nth-child(1) { left: 8%; background: #22c55e; animation-delay: 0s; }
    .welcome-balloon:nth-child(2) { left: 24%; background: #38bdf8; animation-delay: 1.2s; }
    .welcome-balloon:nth-child(3) { left: 78%; background: #f59e0b; animation-delay: 2.4s; }
    .welcome-balloon:nth-child(4) { left: 90%; background: #ec4899; animation-delay: 3.5s; }

    .welcome-confetti {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .welcome-confetti i {
        position: absolute;
        top: -18px;
        width: 10px;
        height: 18px;
        border-radius: 3px;
        animation: confettiFall 4.8s linear infinite;
    }

    .welcome-policy-title {
        color: #fff;
        font-size: clamp(1.65rem, 3vw, 2.4rem);
        font-weight: 900;
        margin-bottom: 16px;
    }

    .welcome-policy-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .welcome-policy-item {
        min-height: 108px;
        padding: 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.26);
        color: #fff;
    }

    .welcome-policy-item strong {
        display: block;
        margin-bottom: 8px;
        color: #fff;
        font-size: 1.05rem;
    }

    .welcome-policy-item span {
        color: rgba(255, 255, 255, 0.86);
        font-weight: 700;
    }

    .welcome-close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        display: none;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 50%;
        background: #fff;
        color: #0f3d91;
        font-size: 1.35rem;
        font-weight: 900;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.24);
    }

    .employee-welcome-card.show-policy .welcome-close-btn {
        display: inline-flex;
    }

    @keyframes welcomeLogoPop {
        from { opacity: 0; transform: scale(0.55) rotate(-8deg); }
        to { opacity: 1; transform: scale(1) rotate(0deg); }
    }

    @keyframes welcomeLogoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes balloonRise {
        0% { transform: translateY(0) translateX(0); opacity: 0; }
        10% { opacity: 0.9; }
        100% { transform: translateY(-115vh) translateX(34px); opacity: 0; }
    }

    @keyframes confettiFall {
        0% { transform: translateY(-20px) rotate(0deg); opacity: 0; }
        10% { opacity: 1; }
        100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
    }

    .employee-card,
    .employee-panel,
    .employee-table-card {
        padding: 22px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .employee-card:hover,
    .employee-panel:hover,
    .employee-table-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 52px rgba(28, 37, 65, 0.16);
    }

    .employee-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .employee-card-title {
        margin: 0;
        font-size: 1.12rem;
        font-weight: 800;
    }

    .employee-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        font-size: 1.35rem;
    }

    .employee-icon.primary { background: rgba(91, 92, 226, 0.12); color: var(--employee-primary); }
    .employee-icon.success { background: rgba(18, 166, 106, 0.12); color: var(--employee-success); }
    .employee-icon.warning { background: rgba(245, 158, 11, 0.14); color: var(--employee-warning); }
    .employee-icon.danger { background: rgba(229, 72, 77, 0.12); color: var(--employee-danger); }
    .employee-icon.info { background: rgba(22, 138, 173, 0.12); color: var(--employee-info); }

    .employee-metric {
        font-size: 2.35rem;
        font-weight: 850;
        line-height: 1;
    }

    .employee-label {
        color: var(--employee-muted);
        font-size: 0.95rem;
        font-weight: 650;
    }

    .employee-profile {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 18px;
        align-items: center;
    }

    .employee-profile-meta {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        margin-top: 18px;
    }

    .employee-profile-meta-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid var(--employee-border);
        border-radius: 14px;
        background: #f8fafc;
    }

    .employee-profile-meta-item i {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        border-radius: 12px;
        background: rgba(91, 92, 226, 0.12);
        color: var(--employee-primary);
        font-size: 1.25rem;
    }

    .employee-profile-meta-item span {
        display: block;
        color: var(--employee-muted);
        font-size: 0.72rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .employee-profile-meta-item strong {
        display: block;
        color: var(--employee-text);
        font-size: 0.96rem;
        font-weight: 850;
        line-height: 1.25;
    }

    .employee-avatar {
        width: 112px;
        height: 112px;
        min-width: 112px;
        min-height: 112px;
        max-width: 112px;
        max-height: 112px;
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        border: 4px solid #fff;
        box-shadow: 0 16px 28px rgba(23, 32, 51, 0.16);
    }

    .employee-quick-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .employee-quick-action {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 54px;
        padding: 12px;
        border: 1px solid var(--employee-border);
        border-radius: 14px;
        background: #fff;
        font-weight: 800;
        text-decoration: none;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .employee-quick-action:hover {
        border-color: rgba(91, 92, 226, 0.45);
        transform: translateY(-2px);
    }

    .employee-rule {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--employee-border);
        min-height: 78px;
    }

    .employee-chart {
        min-height: 260px;
    }

    .employee-chart.small {
        min-height: 220px;
    }

    .employee-status-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }

    .employee-status-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--employee-border);
        font-weight: 800;
    }

    .employee-status-chip span:last-child {
        margin-left: auto;
        font-size: 1.15rem;
    }

    .employee-table-card {
        overflow: hidden;
        padding: 0;
    }

    .employee-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--employee-border);
    }

    .employee-table {
        margin: 0;
        font-size: 0.98rem;
    }

    .employee-table thead th {
        background: #f3f6fb;
        color: #344054;
        border-bottom: 0;
        font-weight: 800;
    }

    .employee-table td,
    .employee-table th {
        padding: 14px 16px;
        vertical-align: middle;
    }

    .employee-calendar-list {
        display: grid;
        gap: 10px;
    }

    .employee-calendar-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border: 1px solid var(--employee-border);
        border-radius: 14px;
        background: #fff;
    }

    .employee-people-list {
        display: grid;
        gap: 10px;
    }

    .employee-people-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--employee-border);
    }

    .employee-people-item:last-child {
        border-bottom: 0;
    }

    .employee-empty {
        margin: 0;
        color: var(--employee-muted);
        font-weight: 650;
    }

    .employee-animate-1 { animation-delay: 0.04s; }
    .employee-animate-2 { animation-delay: 0.08s; }
    .employee-animate-3 { animation-delay: 0.12s; }
    .employee-animate-4 { animation-delay: 0.16s; }

    @keyframes employeeFadeUp {
        from {
            opacity: 0;
            transform: translateY(18px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1199px) {
        .employee-quick-actions,
        .employee-status-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .employee-dashboard {
            padding: 16px;
        }

        .employee-hero {
            padding: 22px;
        }

        .employee-clock {
            justify-items: start;
            margin-top: 20px;
        }

        .employee-profile {
            grid-template-columns: 1fr;
        }

        .employee-avatar {
            width: 96px;
            height: 96px;
            min-width: 96px;
            min-height: 96px;
            max-width: 96px;
            max-height: 96px;
        }

        .employee-profile-meta {
            margin-top: 14px;
        }

        .employee-quick-actions,
        .employee-status-grid {
            grid-template-columns: 1fr;
        }

        .employee-calendar-row {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

@if($showEmployeeWelcome ?? false)
    @php
        $welcomeCompanyName = $currentCompany?->display_name ?? 'our company';
        $welcomeCompanyLogo = $currentCompany?->logoUrl() ?? asset('logos/Bengal IT Hub_05.png');
    @endphp
    <div class="employee-welcome-overlay" id="employeeWelcomeOverlay" data-seen-url="{{ route('dashboard.employeeWelcomeSeen') }}">
        <div class="welcome-balloon"></div>
        <div class="welcome-balloon"></div>
        <div class="welcome-balloon"></div>
        <div class="welcome-balloon"></div>
        <div class="welcome-confetti" id="welcomeConfetti"></div>
        <audio id="welcomeCelebrationSound" src="{{ asset('sound/celebration sound.mp3') }}" preload="auto"></audio>

        <div class="employee-welcome-card" id="employeeWelcomeCard">
            <button type="button" class="welcome-close-btn" id="employeeWelcomeClose" aria-label="Close welcome policy">
                <i class="bx bx-x"></i>
            </button>

            <div class="employee-welcome-stage">
                <img src="{{ $welcomeCompanyLogo }}" alt="{{ $welcomeCompanyName }}" class="employee-welcome-logo">
                <h2 class="employee-welcome-title">{{ $currentCompany?->greeting_message ?: 'Welcome to' }} {{ $welcomeCompanyName }}, {{ $user->name }}</h2>
                <p class="employee-welcome-message">
                    We are delighted to have you with us. Wishing you a confident start, meaningful growth, strong teamwork, and a successful journey with our organization.
                </p>
                <div class="employee-welcome-countdown">
                    <i class="bx bx-time-five"></i>
                    Policy briefing starts in <span id="welcomeCountdown">01:00</span>
                </div>
                <div class="welcome-progress"><span id="welcomeProgressBar"></span></div>
            </div>

            <div class="employee-policy-stage">
                <h2 class="welcome-policy-title">Clock In & Timing Policy</h2>
                <div class="welcome-policy-grid">
                    <div class="welcome-policy-item">
                        <strong><i class="bx bx-map-pin me-1"></i> Location Required</strong>
                        <span>Clock in is allowed after sharing your current location.</span>
                    </div>
                    <div class="welcome-policy-item">
                        <strong><i class="bx bx-current-location me-1"></i> Office Radius</strong>
                        <span>Your distance from 11 Hospital Link Road, Satavisha Building, Kolkata, West Bengal 700075 will be recorded.</span>
                    </div>
                    <div class="welcome-policy-item">
                        <strong><i class="bx bx-camera me-1"></i> Photo Capture</strong>
                        <span>A live camera photo is required before clock in can be completed.</span>
                    </div>
                    <div class="welcome-policy-item">
                        <strong><i class="bx bx-time-five me-1"></i> Attendance Timing</strong>
                        <span>Clock-in after {{ $lateTimeLabel }} is marked late. Working time below {{ $halfDayLabel }} is half day, and below {{ $dayOffLabel }} is day off.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<main id="main" class="main">
    <div class="employee-dashboard">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
            @endif

            <section class="employee-hero mb-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 text-white" style="color: white !important;">
                        <span class="employee-eyebrow" style="color: white !important;"><i class="bx bx-home-smile" style="color: white !important;"></i> Employee Dashboard</span>
                        <h1 class="employee-title" style="color: white !important;">Welcome {{ $user->name }}</h1>
                        <p class="employee-subtitle mb-0" style="color: white !important;">
                            Your attendance, work, tickets, projects, and weekly performance are ready from one place.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="employee-clock">
                            <div class="text-lg-end">
                                <div class="employee-time">{{ now()->format('h:i A') }}</div>
                                <div>{{ now()->format('l, d M Y') }}</div>
                            </div>

                            @if ($attendance && $attendance->clock_in && !$attendance->clock_out)
                                <form method="POST" action="{{ route('dashboard.clockout') }}">
                                    @csrf
                                    <button class="employee-action-btn is-out" type="submit">
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
                                    <button class="employee-action-btn is-in" type="submit" id="employeeClockInButton">
                                        <i class="bx bx-log-in-circle"></i> Clock In
                                    </button>
                                </form>
                                <div class="clock-requirements" id="clockRequirementStatus">
                                    <div class="clock-status-line"><i class="bx bx-map-pin"></i><span>Share current location to save your clock-in place.</span></div>
                                    <div class="clock-status-line"><i class="bx bx-camera"></i><span>Capture photo to complete clock in.</span></div>
                                </div>
                            @else
                                <span class="employee-complete"><i class="bx bx-check-circle"></i> Shift Completed</span>
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
                                            <span>IST Time</span>
                                            <strong id="employeeIstClock">{{ now()->format('h:i:s A') }}</strong>
                                        </div>
                                        <div class="clock-live-box">
                                            <span>{{ $attendance->clock_out ? 'Worked Time' : 'Working Time' }}</span>
                                            <strong
                                                id="employeeWorkTimer"
                                                data-clock-in="{{ \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $attendance->clock_in)->toIso8601String() }}"
                                                data-clock-out="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $attendance->clock_out)->toIso8601String() : '' }}"
                                                data-fixed-duration="{{ $attendance->clock_out ? $workedDurationLabel : '' }}"
                                            >{{ $attendance->clock_out ? $workedDurationLabel : '00:00:00' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

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
                        <p class="employee-muted mt-3 mb-0">Take a clear face photo. You can flip camera on mobile, retake, then use photo for clock in.</p>
                    </div>
                    <div class="clock-camera-footer">
                        <button type="button" class="clock-modal-btn secondary" id="clockCameraFlip"><i class="bx bx-refresh"></i> Flip</button>
                        <button type="button" class="clock-modal-btn" id="clockCameraCapture"><i class="bx bx-camera"></i> Capture</button>
                        <button type="button" class="clock-modal-btn secondary" id="clockCameraRetake"><i class="bx bx-undo"></i> Retake</button>
                        <button type="button" class="clock-modal-btn success" id="clockCameraUse"><i class="bx bx-check"></i> Use Photo & Clock In</button>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-4 col-lg-6">
                    <div class="employee-card employee-animate-1 h-100">
                        <div class="employee-profile">
                            <img
                                src="{{ $user && $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png') }}"
                                alt="User Profile"
                                class="employee-avatar"
                            />
                            <div>
                                <h2 class="h4 fw-bold mb-1">{{ strtoupper($user->name) }}</h2>
                                <p class="employee-muted mb-1">{{ ucfirst($user->role) }}</p>
                                <p class="employee-muted mb-0">Employee Id: {{ $user->employeeDetail?->employee_id ?? $user->id }}</p>
                            </div>
                        </div>
                        <div class="employee-profile-meta">
                            <div class="employee-profile-meta-item">
                                <i class="bx bx-briefcase"></i>
                                <div>
                                    <span>Designation</span>
                                    <strong>{{ $user->employeeDetail?->designation?->name ?? $user->designation ?? 'Employee' }}</strong>
                                </div>
                            </div>
                            <div class="employee-profile-meta-item">
                                <i class="bx bx-buildings"></i>
                                <div>
                                    <span>Department</span>
                                    <strong>{{ $user->employeeDetail?->department?->dpt_name ?? 'Not Assigned' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-4">
                                <a href="{{ route('tasks.index') }}" class="text-decoration-none">
                                    <div class="employee-metric">{{ $openTasksCount }}</div>
                                    <div class="employee-label">Open Tasks</div>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('projects.index') }}" class="text-decoration-none">
                                    <div class="employee-metric">{{ $projectsCount }}</div>
                                    <div class="employee-label">Projects</div>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('tickets.index') }}" class="text-decoration-none">
                                    <div class="employee-metric">{{ $openTicketsCount }}</div>
                                    <div class="employee-label">Tickets</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-6 col-md-6">
                    <a href="{{ route('tasks.index') }}" class="employee-card employee-animate-2 h-100 d-block text-decoration-none">
                        <div class="employee-card-header">
                            <span class="employee-icon primary"><i class="bx bx-task"></i></span>
                        </div>
                        <div class="employee-metric">{{ $pendingTasksCount }}</div>
                        <div class="employee-label">Pending Tasks</div>
                        <div class="text-danger fw-bold mt-2">{{ $overdueTasksCount }} Overdue</div>
                    </a>
                </div>

                <div class="col-xl-2 col-lg-6 col-md-6">
                    <a href="{{ route('projects.index') }}" class="employee-card employee-animate-3 h-100 d-block text-decoration-none">
                        <div class="employee-card-header">
                            <span class="employee-icon success"><i class="bx bx-briefcase-alt-2"></i></span>
                        </div>
                        <div class="employee-metric">{{ $totalProjects }}</div>
                        <div class="employee-label">My Projects</div>
                        <div class="text-success fw-bold mt-2">{{ $inProgressCount }} In Progress</div>
                    </a>
                </div>

                <div class="col-xl-2 col-lg-6 col-md-6">
                    <a href="{{ route('tickets.index') }}" class="employee-card employee-animate-4 h-100 d-block text-decoration-none">
                        <div class="employee-card-header">
                            <span class="employee-icon warning"><i class="bx bx-support"></i></span>
                        </div>
                        <div class="employee-metric">{{ $openTicketsCount }}</div>
                        <div class="employee-label">Open Tickets</div>
                        <div class="text-muted fw-bold mt-2">{{ $resolvedTickets }} Recent Resolved</div>
                    </a>
                </div>

                <div class="col-xl-2 col-lg-6 col-md-6">
                    <a href="{{ Route::has('attendance.report') ? route('attendance.report') : '#' }}" class="employee-card employee-animate-4 h-100 d-block text-decoration-none">
                        <div class="employee-card-header">
                            <span class="employee-icon info"><i class="bx bx-calendar-check"></i></span>
                        </div>
                        <div class="employee-metric">{{ $attendancePercent }}%</div>
                        <div class="employee-label">Weekly Present</div>
                        <div class="text-muted fw-bold mt-2">{{ number_format($weeklyTotalHours, 1) }} Hours</div>
                    </a>
                </div>
            </div>

            <div class="employee-panel mb-4">
                <div class="employee-card-header">
                    <div>
                        <h3 class="employee-card-title">Quick Actions</h3>
                        <p class="employee-muted mb-0">Open your daily work areas directly.</p>
                    </div>
                </div>
                <div class="employee-quick-actions">
                    <a href="{{ route('tasks.index') }}" class="employee-quick-action"><span class="employee-icon primary"><i class="bx bx-list-check"></i></span> Tasks</a>
                    <a href="{{ route('projects.index') }}" class="employee-quick-action"><span class="employee-icon success"><i class="bx bx-folder-open"></i></span> Projects</a>
                    <a href="{{ route('tickets.index') }}" class="employee-quick-action"><span class="employee-icon warning"><i class="bx bx-message-square-detail"></i></span> Tickets</a>
                    <a href="{{ Route::has('attendance.report') ? route('attendance.report') : '#' }}" class="employee-quick-action"><span class="employee-icon info"><i class="bx bx-bar-chart-alt-2"></i></span> Attendance</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <div>
                                <h3 class="employee-card-title">Weekly Timelog Graph</h3>
                                <p class="employee-muted mb-0">Clocked hours from Monday to Sunday.</p>
                            </div>
                            <span class="employee-icon info"><i class="bx bx-line-chart"></i></span>
                        </div>
                        <div id="employeeWeeklyHoursChart" class="employee-chart"></div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <div>
                                <h3 class="employee-card-title">Attendance Pie Chart</h3>
                                <p class="employee-muted mb-0">Present, absent, leave, and work from home.</p>
                            </div>
                            <span class="employee-icon success"><i class="bx bx-pie-chart-alt-2"></i></span>
                        </div>
                        <div id="employeeAttendanceChart" class="employee-chart"></div>
                    </div>
                </div>
            </div>

            <div class="employee-panel mb-4">
                <div class="employee-card-header">
                    <div>
                        <h3 class="employee-card-title">Summary Attendance</h3>
                        <p class="employee-muted mb-0">Clear icons for every attendance status this week.</p>
                    </div>
                </div>
                <div class="employee-status-grid">
                    <div class="employee-status-chip"><i class="bx bx-check-circle text-success fs-4"></i> Present <span>{{ $attendanceSummary['Present'] }}</span></div>
                    <div class="employee-status-chip"><i class="bx bx-x-circle text-danger fs-4"></i> Absent <span>{{ $attendanceSummary['Absent'] }}</span></div>
                    <div class="employee-status-chip"><i class="bx bx-calendar-minus text-warning fs-4"></i> On Leave <span>{{ $attendanceSummary['On Leave'] }}</span></div>
                    <div class="employee-status-chip"><i class="bx bx-home-heart text-info fs-4"></i> Work From Home <span>{{ $attendanceSummary['Work From Home'] }}</span></div>
                    <div class="employee-status-chip"><i class="bx bx-time-five text-primary fs-4"></i> Half Day <span>{{ $attendanceSummary['Half Day'] }}</span></div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-4 col-lg-6">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">Tasks Pie Chart</h3>
                            <span class="employee-icon primary"><i class="bx bx-task"></i></span>
                        </div>
                        <div id="employeeTaskChart" class="employee-chart small"></div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">Projects Pie Chart</h3>
                            <span class="employee-icon success"><i class="bx bx-briefcase"></i></span>
                        </div>
                        <div id="employeeProjectChart" class="employee-chart small"></div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-12">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">Tickets Pie Chart</h3>
                            <span class="employee-icon warning"><i class="bx bx-support"></i></span>
                        </div>
                        <div id="employeeTicketChart" class="employee-chart small"></div>
                    </div>
                </div>
            </div>

            <div class="employee-panel mb-4">
                <div class="employee-card-header">
                    <div>
                        <h3 class="employee-card-title">Attendance Rules & Regulations</h3>
                        <p class="employee-muted mb-0">Readable rules for daily attendance status.</p>
                    </div>
                    <span class="employee-icon success"><i class="bx bx-clipboard"></i></span>
                </div>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="employee-rule"><i class="bx bx-time-five text-warning fs-3"></i><span>Clock-in after <strong>{{ $lateTimeLabel }}</strong> is marked as <strong>Late</strong>.</span></div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="employee-rule"><i class="bx bx-star text-primary fs-3"></i><span>Working time below <strong>{{ $halfDayLabel }}</strong> is marked as <strong>Half Day</strong>.</span></div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="employee-rule"><i class="bx bx-calendar-x text-danger fs-3"></i><span>Working time below <strong>{{ $dayOffLabel }}</strong> is marked as <strong>Day Off</strong>.</span></div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="employee-rule"><i class="bx bx-plane-alt text-info fs-3"></i><span>Approved leave is shown automatically from the leave table.</span></div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-6">
                    <div class="employee-table-card h-100">
                        <div class="employee-table-head">
                            <a href="{{ route('tasks.index') }}" class="text-decoration-none"><h3 class="employee-card-title mb-0">My Tasks</h3></a>
                            <span class="employee-icon primary"><i class="bx bx-list-check"></i></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table employee-table">
                                <thead>
                                    <tr>
                                        <th>Task#</th>
                                        <th>Task</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($myTasks as $task)
                                        <tr>
                                            <td>#{{ $task->id }}</td>
                                            <td class="fw-semibold">{{ $task->title }}</td>
                                            <td>
                                                @if($task->status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($task->status == 'Doing')
                                                    <span class="badge bg-primary">Doing</span>
                                                @elseif($task->status == 'Incomplete')
                                                    <span class="badge bg-danger">Incomplete</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $task->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $task->due_date ? Carbon::parse($task->due_date)->format('d-m-Y') : 'NA' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">No tasks assigned</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="employee-table-card h-100">
                        <div class="employee-table-head">
                            <a href="{{ route('tickets.index') }}" class="text-decoration-none"><h3 class="employee-card-title mb-0">Tickets</h3></a>
                            <span class="employee-icon warning"><i class="bx bx-message-detail"></i></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table employee-table">
                                <thead>
                                    <tr>
                                        <th>Ticket#</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($myTickets as $ticket)
                                        <tr>
                                            <td>#{{ $ticket->id }}</td>
                                            <td class="fw-semibold">{{ $ticket->subject }}</td>
                                            <td>
                                                @if($ticket->status == 'open')
                                                    <span class="badge bg-warning">Open</span>
                                                @elseif($ticket->status == 'resolved')
                                                    <span class="badge bg-success">Resolved</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($ticket->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $ticket->created_at->format('d-m-Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">No tickets found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-5">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <div>
                                <h3 class="employee-card-title">Today Attendance</h3>
                                <p class="employee-muted mb-0">Current day clock details.</p>
                            </div>
                            <span class="employee-icon info"><i class="bx bx-time"></i></span>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="employee-label">Clock In</div>
                                <div class="fw-bold fs-5">{{ $clockInLabel }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="employee-label">Clock Out</div>
                                <div class="fw-bold fs-5">{{ $clockOutLabel }}</div>
                            </div>
                            <div class="col-sm-4">
                                <div class="employee-label">Status</div>
                                <div class="fw-bold fs-5">{{ $todayStatus }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <div>
                                <h3 class="employee-card-title">My Calendar</h3>
                                <p class="employee-muted mb-0">{{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d, Y') }}</p>
                            </div>
                            <span class="employee-icon primary"><i class="bx bx-calendar"></i></span>
                        </div>
                        <div class="employee-calendar-list">
                            @foreach($weekDays as $day)
                                @php
                                    $entry = $weeklyLogs[$day->toDateString()] ?? null;
                                @endphp
                                <div class="employee-calendar-row">
                                    <span class="fw-bold">{{ $day->format('l, F d, Y') }}</span>
                                    <span>
                                        @if ($entry)
                                            <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $entry->status ?? 'present')) }}</span>
                                            <small class="text-muted ms-2">In: {{ $entry->clock_in ?? 'NA' }} | Out: {{ $entry->clock_out ?? 'NA' }}</small>
                                        @else
                                            <span class="badge bg-secondary">No Entry</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-3 col-md-6">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">Birthdays Today</h3>
                            <span class="employee-icon warning"><i class="bx bx-cake"></i></span>
                        </div>
                        <div class="employee-people-list">
                            @forelse($birthdaysToday as $emp)
                                <div class="employee-people-item"><span>{{ $emp->user->name ?? 'N/A' }}</span><strong>{{ Carbon::parse($emp->dob)->format('d M') }}</strong></div>
                            @empty
                                <p class="employee-empty">No record found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">Appreciations</h3>
                            <span class="employee-icon success"><i class="bx bx-award"></i></span>
                        </div>
                        <div class="employee-people-list">
                            @forelse($appreciations as $award)
                                <div class="employee-people-item"><span>{{ $award->user->name ?? 'N/A' }}</span><strong>{{ $award->title }}</strong></div>
                            @empty
                                <p class="employee-empty">No record found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">On Leave Today</h3>
                            <span class="employee-icon info"><i class="bx bx-calendar-minus"></i></span>
                        </div>
                        <div class="employee-people-list">
                            @forelse($onLeaveToday as $leave)
                                <div class="employee-people-item"><span>{{ $leave->user->name ?? 'N/A' }}</span><strong>{{ $leave->type }}</strong></div>
                            @empty
                                <p class="employee-empty">No record found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="employee-panel h-100">
                        <div class="employee-card-header">
                            <h3 class="employee-card-title">Joinings & Anniversaries</h3>
                            <span class="employee-icon danger"><i class="bx bx-party"></i></span>
                        </div>
                        <p class="fw-bold mb-2">Today's Joinings</p>
                        @forelse($todaysJoinings as $emp)
                            <div class="employee-people-item"><span>{{ $emp->user->name ?? 'N/A' }}</span></div>
                        @empty
                            <p class="employee-empty mb-3">No record found.</p>
                        @endforelse

                        <p class="fw-bold mt-3 mb-2">Work Anniversaries</p>
                        @forelse($workAnniversaries as $emp)
                            <div class="employee-people-item"><span>{{ $emp->user->name ?? 'N/A' }}</span><strong>{{ Carbon::parse($emp->joining_date)->diffInYears() }} year(s)</strong></div>
                        @empty
                            <p class="employee-empty">No record found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const welcomeOverlay = document.getElementById('employeeWelcomeOverlay');
        const welcomeCard = document.getElementById('employeeWelcomeCard');
        const welcomeClose = document.getElementById('employeeWelcomeClose');
        const welcomeCountdown = document.getElementById('welcomeCountdown');
        const welcomeProgressBar = document.getElementById('welcomeProgressBar');
        const welcomeConfetti = document.getElementById('welcomeConfetti');
        const welcomeCelebrationSound = document.getElementById('welcomeCelebrationSound');

        if (welcomeOverlay && welcomeCard) {
            const welcomeDuration = 60;
            let remaining = welcomeDuration;
            let countdownTimer = null;
            let welcomeSeenMarked = false;

            const formatCountdown = seconds => {
                const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                const secs = String(seconds % 60).padStart(2, '0');
                return `${minutes}:${secs}`;
            };

            const createConfetti = () => {
                if (!welcomeConfetti) {
                    return;
                }

                const colors = ['#ffffff', '#bbf7d0', '#38bdf8', '#facc15', '#fb7185', '#a7f3d0'];
                for (let i = 0; i < 72; i++) {
                    const piece = document.createElement('i');
                    piece.style.left = `${Math.random() * 100}%`;
                    piece.style.background = colors[i % colors.length];
                    piece.style.animationDelay = `${Math.random() * 4}s`;
                    piece.style.animationDuration = `${3.6 + Math.random() * 3.2}s`;
                    welcomeConfetti.appendChild(piece);
                }
            };

            const playCelebrationSound = () => {
                if (!welcomeCelebrationSound) {
                    return;
                }

                welcomeCelebrationSound.loop = false;
                welcomeCelebrationSound.volume = 0.45;
                welcomeCelebrationSound.play().catch(() => {});
            };

            const stopCelebrationSound = () => {
                if (!welcomeCelebrationSound) {
                    return;
                }

                welcomeCelebrationSound.pause();
                welcomeCelebrationSound.currentTime = 0;
            };

            const showPolicy = () => {
                welcomeCard.classList.add('show-policy');
                if (welcomeCountdown) {
                    welcomeCountdown.textContent = '00:00';
                }
                if (welcomeProgressBar) {
                    welcomeProgressBar.style.width = '100%';
                }
                stopCelebrationSound();
            };

            const markWelcomeSeen = () => {
                if (welcomeSeenMarked || !welcomeOverlay.dataset.seenUrl) {
                    return Promise.resolve();
                }

                welcomeSeenMarked = true;

                return fetch(welcomeOverlay.dataset.seenUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({})
                }).catch(() => {});
            };

            const closeWelcome = () => {
                welcomeOverlay.classList.add('is-hidden');
                if (countdownTimer) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                }
                stopCelebrationSound();
                markWelcomeSeen();
            };

            createConfetti();
            markWelcomeSeen();
            playCelebrationSound();
            welcomeCelebrationSound?.addEventListener('ended', stopCelebrationSound);

            countdownTimer = setInterval(() => {
                remaining -= 1;
                if (welcomeCountdown) {
                    welcomeCountdown.textContent = formatCountdown(Math.max(remaining, 0));
                }
                if (welcomeProgressBar) {
                    welcomeProgressBar.style.width = `${((welcomeDuration - remaining) / welcomeDuration) * 100}%`;
                }

                if (remaining <= 0) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                    showPolicy();
                }
            }, 1000);

            welcomeClose?.addEventListener('click', closeWelcome);
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !welcomeOverlay.classList.contains('is-hidden')) {
                    closeWelcome();
                }
            });
            window.addEventListener('pagehide', stopCelebrationSound);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopCelebrationSound();
                }
            });
        }

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

            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: cameraFacingMode, width: { ideal: 1280 }, height: { ideal: 960 } },
                audio: false
            });

            if (video) {
                video.srcObject = cameraStream;
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
                setClockStatus('Camera flip failed. Please continue with available camera.', 'error');
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
            canvas.getContext('2d').drawImage(video, 0, 0, width, height);
            capturedSelfie = canvas.toDataURL('image/jpeg', 0.88);
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

        const updateClockWidgets = () => {
            const now = new Date();

            if (istClock) {
                istClock.textContent = new Intl.DateTimeFormat('en-IN', {
                    timeZone: 'Asia/Kolkata',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                }).format(now);
            }

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

        const scheduleMidnightRefresh = () => {
            const now = new Date();
            const midnight = new Date(now);
            midnight.setHours(24, 0, 3, 0);
            setTimeout(() => window.location.reload(), Math.max(1000, midnight - now));
        };

        scheduleMidnightRefresh();

        const loadedDateKey = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Kolkata',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());

        setInterval(() => {
            const currentDateKey = new Intl.DateTimeFormat('en-CA', {
                timeZone: 'Asia/Kolkata',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).format(new Date());

            if (currentDateKey !== loadedDateKey) {
                window.location.reload();
            }
        }, 60000);

        if (typeof ApexCharts === 'undefined') {
            return;
        }

        const chartTextColor = '#667085';
        const chartGridColor = 'rgba(23, 32, 51, 0.08)';
        const commonDonut = {
            chart: {
                type: 'donut',
                height: 255,
                animations: { enabled: true, easing: 'easeinout', speed: 750 },
                toolbar: { show: false }
            },
            legend: {
                position: 'bottom',
                labels: { colors: chartTextColor }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '13px', fontWeight: 800 }
            },
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '64%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                color: chartTextColor
                            }
                        }
                    }
                }
            }
        };

        const renderChart = (selector, options) => {
            const element = document.querySelector(selector);
            if (!element) {
                return;
            }

            new ApexCharts(element, options).render();
        };

        renderChart('#employeeWeeklyHoursChart', {
            chart: {
                type: 'area',
                height: 300,
                animations: { enabled: true, easing: 'easeinout', speed: 850 },
                toolbar: { show: false }
            },
            series: [{
                name: 'Hours',
                data: @json($weeklyHours)
            }],
            xaxis: {
                categories: @json($weeklyLabels),
                labels: { style: { colors: chartTextColor, fontWeight: 700 } }
            },
            yaxis: {
                min: 0,
                labels: { style: { colors: chartTextColor }, formatter: value => `${value}h` }
            },
            colors: ['#168aad'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 0.8, opacityFrom: 0.38, opacityTo: 0.04, stops: [0, 90, 100] }
            },
            stroke: { curve: 'smooth', width: 4 },
            grid: { borderColor: chartGridColor, strokeDashArray: 4 },
            markers: { size: 5, colors: ['#fff'], strokeColors: '#168aad', strokeWidth: 3 },
            tooltip: { y: { formatter: value => `${value} hours` } }
        });

        renderChart('#employeeAttendanceChart', {
            ...commonDonut,
            series: @json($attendanceChart),
            labels: @json(array_keys($attendanceSummary)),
            colors: ['#12a66a', '#e5484d', '#f59e0b', '#168aad', '#5b5ce2']
        });

        renderChart('#employeeTaskChart', {
            ...commonDonut,
            series: @json($taskChart),
            labels: ['Pending', 'Overdue', 'Other Open'],
            colors: ['#5b5ce2', '#e5484d', '#f59e0b']
        });

        renderChart('#employeeProjectChart', {
            ...commonDonut,
            series: @json($projectChart),
            labels: ['In Progress', 'Overdue', 'Other'],
            colors: ['#12a66a', '#e5484d', '#168aad']
        });

        renderChart('#employeeTicketChart', {
            ...commonDonut,
            series: @json($ticketChart),
            labels: ['Open', 'Recent Resolved'],
            colors: ['#f59e0b', '#12a66a']
        });
    });
</script>
@endpush
