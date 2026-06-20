@extends('admin.layout.app')

@section('content')

@php
    $defaultStart = now()->format('Y-m-d');
    $defaultEnd = now()->format('Y-m-d');
    $startDate = request('start_date', $defaultStart);
    $endDate = request('end_date', $defaultEnd);
    $roleName = 'HR';
    $canSeeModule = fn (string $slug) => auth()->user()?->canViewModule($slug) ?? false;
    $attendancePercent = $totalEmployees > 0 ? round(($todayPresent / $totalEmployees) * 100) : 0;
    $absentToday = max(($totalEmployees ?? 0) - ($todayPresent ?? 0) - ($onLeaveToday ?? 0), 0);
    $roleScale = max($totalEmployees ?? 0, $newEmployees ?? 0, $exits ?? 0, $approvedLeaves ?? 0, $todayPresent ?? 0, $pendingTasks ?? 0, $pendingLeaves ?? 0, 1);
    $rolePieCharts = [
        ['slug' => 'employees', 'route' => 'employees.index', 'label' => 'Employees', 'value' => $totalEmployees ?? 0, 'hint' => 'Total workforce', 'percent' => round((($totalEmployees ?? 0) / $roleScale) * 100), 'color' => '#2563eb'],
        ['slug' => 'attendance', 'route' => 'attendance.index', 'label' => 'Presence', 'value' => $todayPresent ?? 0, 'hint' => "{$attendancePercent}% present today", 'percent' => $attendancePercent, 'color' => '#10b981'],
        ['slug' => 'leaves', 'route' => 'leaves.index', 'label' => 'Pending Leaves', 'value' => $pendingLeaves ?? 0, 'hint' => 'Awaiting review', 'percent' => round((($pendingLeaves ?? 0) / $roleScale) * 100), 'color' => '#f59e0b'],
        ['slug' => 'tasks', 'route' => 'tasks.index', 'label' => 'Pending Tasks', 'value' => $pendingTasks ?? 0, 'hint' => 'Open work queue', 'percent' => round((($pendingTasks ?? 0) / $roleScale) * 100), 'color' => '#7c3aed'],
        ['slug' => 'employees', 'route' => 'employees.index', 'label' => 'New Joiners', 'value' => $newEmployees ?? 0, 'hint' => 'In selected range', 'percent' => round((($newEmployees ?? 0) / $roleScale) * 100), 'color' => '#06b6d4'],
        ['slug' => 'employees', 'route' => 'employees.index', 'label' => 'Exits', 'value' => $exits ?? 0, 'hint' => 'In selected range', 'percent' => round((($exits ?? 0) / $roleScale) * 100), 'color' => '#ef4444'],
        ['slug' => 'leaves', 'route' => 'leaves.index', 'label' => 'Approved Leaves', 'value' => $approvedLeaves ?? 0, 'hint' => 'Approved in range', 'percent' => round((($approvedLeaves ?? 0) / $roleScale) * 100), 'color' => '#14b8a6'],
        ['slug' => 'attendance', 'route' => 'attendance.report', 'label' => 'Absent Today', 'value' => $absentToday, 'hint' => 'Not present / leave', 'percent' => $totalEmployees > 0 ? round(($absentToday / $totalEmployees) * 100) : 0, 'color' => '#64748b'],
    ];
@endphp
<style>
    .role-dashboard-shell {
        background: linear-gradient(135deg, #eef7ff 0%, #f9fafb 52%, #f1f5f9 100%);
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
        background: radial-gradient(circle, rgba(37, 99, 235, .18), transparent 68%);
        pointer-events: none;
    }
    .role-hero {
        align-items: stretch;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
        position: relative;
        z-index: 1;
    }
    .role-hero-card,
    .role-panel,
    .role-stat-card,
    .role-feature-card,
    .role-pie-card {
        background: rgba(255,255,255,.9);
        border: 1px solid rgba(255,255,255,.75);
        box-shadow: 0 22px 55px rgba(15, 23, 42, .1);
        backdrop-filter: blur(16px);
    }
    .role-hero-card {
        border-radius: 24px;
        padding: clamp(1.25rem, 3vw, 2.25rem);
    }
    .role-eyebrow {
        background: rgba(16,185,129,.12);
        border: 1px solid rgba(16,185,129,.2);
        border-radius: 999px;
        color: #047857;
        display: inline-flex;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: .08em;
        margin-bottom: .9rem;
        padding: .42rem .72rem;
        text-transform: uppercase;
    }
    .role-hero-card h1 {
        color: #111827;
        font-size: clamp(1.8rem, 4vw, 3.4rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1.03;
        margin: 0 0 .8rem;
    }
    .role-hero-card p {
        color: #5b6472;
        font-weight: 700;
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
        font-weight: 900;
        gap: .45rem;
        min-height: 42px;
        padding: .7rem 1rem;
        text-decoration: none;
    }
    .role-btn-primary {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
    }
    .role-btn-light {
        background: #fff;
        border: 1px solid rgba(37,99,235,.16);
        color: #1f2937;
    }
    .role-date-filter {
        align-items: center;
        background: rgba(255,255,255,.75);
        border: 1px solid rgba(37,99,235,.14);
        border-radius: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: 1rem;
        padding: .7rem;
    }
    .role-date-filter label,
    .role-date-filter span {
        color: #334155;
        font-size: .82rem;
        font-weight: 900;
        margin: 0;
    }
    .role-date-filter .form-control {
        border-color: rgba(37,99,235,.16);
        border-radius: 12px;
        min-height: 38px;
        width: auto;
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
        background: conic-gradient(#10b981 0%, #2563eb calc(var(--percent) * 1%), #e5e7eb 0);
        border-radius: 50%;
        display: flex;
        height: 156px;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
        width: 156px;
    }
    .role-gauge::after {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 18px;
        position: absolute;
    }
    .role-gauge strong {
        color: #111827;
        font-size: 2rem;
        font-weight: 900;
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
        color: #667085;
        font-size: .78rem;
        font-weight: 800;
    }
    .role-stat-card strong {
        color: #111827;
        display: block;
        font-size: 2rem;
        font-weight: 900;
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
        color: #111827;
        font-size: 1.05rem;
        font-weight: 900;
        margin: 0;
    }
    .role-panel-head p {
        color: #667085;
        font-size: .84rem;
        font-weight: 700;
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
        box-shadow: 0 26px 58px rgba(37,99,235,.14);
        transform: translateY(-4px);
    }
    .role-donut {
        --accent: #2563eb;
        align-items: center;
        background: conic-gradient(var(--accent) calc(var(--percent) * 1%), #e5e7eb 0);
        border-radius: 50%;
        display: flex;
        height: 118px;
        justify-content: center;
        margin: 0 auto .8rem;
        position: relative;
        width: 118px;
    }
    .role-donut::after {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 15px;
        position: absolute;
    }
    .role-donut strong {
        color: #111827;
        font-size: 1.25rem;
        font-weight: 900;
        position: relative;
        z-index: 1;
    }
    .role-pie-card h4 {
        color: #111827;
        font-size: .95rem;
        font-weight: 900;
        margin-bottom: .2rem;
    }
    .role-pie-card a {
        color: #2563eb;
        display: inline-flex;
        font-size: .8rem;
        font-weight: 900;
        margin-top: .55rem;
        text-decoration: none;
    }
    .role-feature-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .role-feature-card {
        align-items: center;
        border-radius: 18px;
        color: #111827;
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
        background: linear-gradient(135deg, rgba(37,99,235,.12), rgba(16,185,129,.12));
        border-radius: 14px;
        color: #2563eb;
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
        font-weight: 900;
    }
    .role-feature-card em {
        background: rgba(37,99,235,.08);
        border-radius: 999px;
        color: #2563eb;
        font-size: .76rem;
        font-style: normal;
        font-weight: 900;
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
        .role-date-filter,
        .role-date-filter .form-control,
        .role-date-filter .btn {
            width: 100%;
        }
    }
</style>
<div class="container-fluid">

    <section class="role-dashboard-shell">
        <div class="role-hero">
            <div class="role-hero-card">
                <span class="role-eyebrow">{{ $roleName }} command center</span>
                <h1>{{ $roleName }} Dashboard</h1>
                <p>Monitor workforce health, leave flow, attendance, tasks, and HR analytics from one responsive workspace.</p>
                <div class="role-hero-actions">
                    @if(Route::has('employees.index') && $canSeeModule('employees'))
                        <a href="{{ route('employees.index') }}" class="role-btn role-btn-primary"><i class="bx bx-group"></i> Employees</a>
                    @endif
                    @if(Route::has('leaves.index') && $canSeeModule('leaves'))
                        <a href="{{ route('leaves.index') }}" class="role-btn role-btn-light"><i class="bx bx-calendar-minus"></i> Leaves</a>
                    @endif
                    @if(Route::has('attendance.report') && $canSeeModule('attendance'))
                        <a href="{{ route('attendance.report') }}" class="role-btn role-btn-light"><i class="bx bx-bar-chart-alt-2"></i> Reports</a>
                    @endif
                </div>
                <form method="GET" class="role-date-filter">
                    <label>Date Range</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                    <span>to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
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
            <div class="role-stat-card"><span>Total Employees</span><strong>{{ $totalEmployees ?? 0 }}</strong></div>
            <div class="role-stat-card"><span>New Employees</span><strong>{{ $newEmployees ?? 0 }}</strong></div>
            <div class="role-stat-card"><span>Pending Leaves</span><strong>{{ $pendingLeaves ?? 0 }}</strong></div>
            <div class="role-stat-card"><span>Pending Tasks</span><strong>{{ $pendingTasks ?? 0 }}</strong></div>
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
<!-- ============ END LEAVE MANAGEMENT SECTION ============ -->

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
                backgroundColor: ['#fcbf49', '#90be6d', '#577590']
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
                backgroundColor: ['#ff6b6b', '#4ecdc4', '#1a535c']
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
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8e44ad', '#2ecc71', '#e67e22']
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
                backgroundColor: ['#FF9F40', '#36A2EB', '#FF6384', '#4BC0C0', '#9966FF', '#00a65a']
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
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
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
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            }]
        }
    });
</script>
@endsection
