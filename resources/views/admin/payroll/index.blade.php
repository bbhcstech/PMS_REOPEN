@extends('admin.layout.app')

@section('title', 'Payroll Dashboard')

@section('content')
@include('admin.payroll.partials.styles')

<div class="payroll-container">
    @include('admin.payroll.partials.tabs')

    <!-- Top Action & Filter Row -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--pr-text);">💰 Payroll Dashboard</h4>
            <p class="text-muted small mb-0">Admin Workspace · Real-time salary and statutory overview</p>
        </div>
        <form method="GET" action="{{ route('payroll.index') }}" class="d-flex gap-2 align-items-center">
            <select name="month" class="form-select form-select-sm" style="min-width: 130px; background: var(--pr-surface); color: var(--pr-text); border-color: var(--pr-border);" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $kpis['selected_month'] == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm" style="min-width: 100px; background: var(--pr-surface); color: var(--pr-text); border-color: var(--pr-border);" onchange="this.form.submit()">
                @for($y = date('Y') + 1; $y >= 2022; $y--)
                    <option value="{{ $y }}" {{ $kpis['selected_year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('payroll.processing', ['year' => $kpis['selected_year'], 'month' => $kpis['selected_month']]) }}" class="pr-btn pr-btn-primary">
                ⚙️ Process {{ $kpis['month_name'] }}
            </a>
        </form>
    </div>

    <!-- 8 KPI Cards Matching ui.html -->
    <div class="kpi-grid">
        <div class="kpi">
            <div class="label">👥 Total Employees</div>
            <div class="value">{{ number_format($kpis['total_employees']) }}</div>
            <div class="sub">Active in payroll</div>
        </div>
        <div class="kpi green">
            <div class="label">✅ Payroll Processed</div>
            <div class="value">{{ number_format($kpis['payroll_processed']) }}</div>
            <div class="sub">{{ $kpis['month_name'] }} {{ $kpis['selected_year'] }}</div>
        </div>
        <div class="kpi amber">
            <div class="label">⏳ Payroll Pending</div>
            <div class="value">{{ number_format($kpis['payroll_pending']) }}</div>
            <div class="sub">Awaiting processing</div>
        </div>
        <div class="kpi cyan">
            <div class="label">💵 Total Gross</div>
            <div class="value">₹{{ number_format($kpis['total_gross'], 2) }}</div>
            <div class="sub">Earned gross total</div>
        </div>
        <div class="kpi red">
            <div class="label">📉 Total Deductions</div>
            <div class="value">₹{{ number_format($kpis['total_deductions'], 2) }}</div>
            <div class="sub">PF + ESI + PT + Tax</div>
        </div>
        <div class="kpi green">
            <div class="label">💰 Total Net Pay</div>
            <div class="value">₹{{ number_format($kpis['total_net_pay'], 2) }}</div>
            <div class="sub">After deductions</div>
        </div>
        <div class="kpi purple">
            <div class="label">🏦 Employer Contribution</div>
            <div class="value">₹{{ number_format($kpis['total_employer_contribution'], 2) }}</div>
            <div class="sub">Employer PF + ESI + EDLI</div>
        </div>
        <div class="kpi">
            <div class="label">📊 Total CTC</div>
            <div class="value">₹{{ number_format($kpis['total_ctc'], 2) }}</div>
            <div class="sub">Full cost to company</div>
        </div>
    </div>

    <!-- Status Breakdown & Quick Links -->
    <div class="row g-3">
        <!-- Status Breakdown Card -->
        <div class="col-lg-6">
            <div class="pr-card h-100">
                <div class="pr-card-head">
                    <h2>📊 Payroll Status Breakdown</h2>
                    <div class="spacer"></div>
                    <span class="badge bg-label-primary">{{ $kpis['month_name'] }} {{ $kpis['selected_year'] }}</span>
                </div>
                <div class="pr-card-body breakdown">
                    @php
                        $tot = max(1, $statusBreakdown['total']);
                        $finPct = round(($statusBreakdown['finalized'] / $tot) * 100);
                        $appPct = round(($statusBreakdown['approved'] / $tot) * 100);
                        $revPct = round(($statusBreakdown['reviewed'] / $tot) * 100);
                        $calcPct = round(($statusBreakdown['calculated'] / $tot) * 100);
                    @endphp
                    <div class="bd-row">
                        <div class="name">Finalized</div>
                        <div class="bd-bar">
                            <div class="bd-fill" style="width: {{ $finPct }}%; background: #64748b;"></div>
                        </div>
                        <div class="count">{{ $statusBreakdown['finalized'] }}</div>
                    </div>
                    <div class="bd-row">
                        <div class="name">Approved</div>
                        <div class="bd-bar">
                            <div class="bd-fill" style="width: {{ $appPct }}%; background: #10b981;"></div>
                        </div>
                        <div class="count">{{ $statusBreakdown['approved'] }}</div>
                    </div>
                    <div class="bd-row">
                        <div class="name">Reviewed</div>
                        <div class="bd-bar">
                            <div class="bd-fill" style="width: {{ $revPct }}%; background: #8b5cf6;"></div>
                        </div>
                        <div class="count">{{ $statusBreakdown['reviewed'] }}</div>
                    </div>
                    <div class="bd-row">
                        <div class="name">Calculated</div>
                        <div class="bd-bar">
                            <div class="bd-fill" style="width: {{ $calcPct }}%; background: #2563eb;"></div>
                        </div>
                        <div class="count">{{ $statusBreakdown['calculated'] }}</div>
                    </div>
                    @php
                        $draftPct = $tot > 0 ? round(($statusBreakdown['draft'] / $tot) * 100) : 0;
                    @endphp
                    <div class="bd-row">
                        <div class="name">Draft / Other</div>
                        <div class="bd-bar">
                            <div class="bd-fill" style="width: {{ $draftPct }}%; background: #f59e0b;"></div>
                        </div>
                        <div class="count">{{ $statusBreakdown['draft'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Modules & Actions -->
        <div class="col-lg-6">
            <div class="pr-card h-100">
                <div class="pr-card-head">
                    <h2>⚡ Quick Actions</h2>
                </div>
                <div class="pr-card-body">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ route('payroll.processing') }}" class="pr-btn pr-btn-primary">
                            <span>⚙️</span> Process Payroll
                        </a>
                        <a href="{{ route('payroll.salary-structures.index') }}" class="pr-btn">
                            <span>🏗️</span> New Salary Structure
                        </a>
                        <a href="{{ route('payroll.employee-salary.index') }}" class="pr-btn">
                            <span>👤</span> Assign Employee Salary
                        </a>
                        <a href="{{ route('payroll.history') }}" class="pr-btn">
                            <span>📚</span> View History
                        </a>
                        <a href="{{ route('payroll.payslips.index') }}" class="pr-btn">
                            <span>🧾</span> View Payslips
                        </a>
                    </div>

                    <h6 class="fw-bold mb-2 text-uppercase small text-muted">System Counts</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="info-box py-2">
                                <div class="small text-muted">Salary Structures</div>
                                <div class="fs-5 fw-bold" style="color: var(--pr-text);">{{ $structuresCount }} Active</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box py-2">
                                <div class="small text-muted">Salary Assignments</div>
                                <div class="fs-5 fw-bold" style="color: var(--pr-text);">{{ $assignmentsCount }} Assigned</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box py-2">
                                <div class="small text-muted">Generated Payslips</div>
                                <div class="fs-5 fw-bold" style="color: var(--pr-text);">{{ $payslipCount }} Total</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box py-2">
                                <div class="small text-muted">Total Payroll Runs</div>
                                <div class="fs-5 fw-bold text-success">{{ $totalPayrollRuns }} Runs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Runs -->
    @if($recentPayrolls->count() > 0)
    <div class="pr-card mt-3">
        <div class="pr-card-head">
            <h2>🕒 Recent Payroll Runs</h2>
            <div class="spacer"></div>
            <a href="{{ route('payroll.history') }}" class="small fw-bold text-decoration-none">View All Runs →</a>
        </div>
        <div class="table-wrap">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Pay Date</th>
                        <th>Status</th>
                        <th class="text-end">Gross Total</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">In Hand Total</th>
                        <th class="text-end">CTC</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayrolls as $run)
                    <tr>
                        <td>
                            <b>{{ Carbon\Carbon::parse($run->period_start)->format('M Y') }}</b>
                            <div class="small text-muted">{{ $run->period_start }} to {{ $run->period_end }}</div>
                        </td>
                        <td>{{ $run->pay_date ?: '-' }}</td>
                        <td>
                            <span class="pr-pill {{ strtolower($run->status) }}">
                                {{ ucfirst($run->status) }}
                            </span>
                        </td>
                        <td class="num">₹{{ number_format($run->gross_total, 2) }}</td>
                        <td class="num text-danger">₹{{ number_format($run->deduction_total, 2) }}</td>
                        <td class="num text-success fw-bold">₹{{ number_format($run->total_in_hand ?? $run->net_total, 2) }}</td>
                        <td class="num text-purple fw-bold">₹{{ number_format($run->total_ctc ?? $run->gross_total, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('payroll.processing', ['year' => date('Y', strtotime($run->period_start)), 'month' => date('n', strtotime($run->period_start))]) }}" class="pr-btn pr-btn-sm">
                                👁️ View Run
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
