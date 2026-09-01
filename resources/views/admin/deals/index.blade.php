@extends('admin.layout.app')

@section('content')

<style>
.crm-kpi-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    border: 1px solid #eef2f6;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}
.crm-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
}
.crm-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.crm-kpi-title {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.crm-kpi-value {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0;
}
.top-bar {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    background: #ffffff;
    padding: 16px 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    border: 1px solid #eef2f6;
}
.kanban-container {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding-bottom: 20px;
}
.kanban-col {
    flex: 0 0 310px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    max-height: 800px;
}
.kanban-col-header {
    padding: 14px 16px;
    border-bottom: 2px solid;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top-left-radius: 9px;
    border-top-right-radius: 9px;
    background: #ffffff;
}
.kanban-col-body {
    padding: 12px;
    overflow-y: auto;
    flex-grow: 1;
    min-height: 200px;
}
.kanban-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    border: 1px solid #cbd5e1;
    margin-bottom: 12px;
    cursor: grab;
    transition: transform 0.15s, box-shadow 0.15s;
}
.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Premium Table UI/UX Styles */
.premium-table-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
    transition: all 0.2s ease;
}
html[data-pms-theme="dark"] .premium-table-card {
    background: #102119;
    border-color: rgba(225, 255, 240, 0.12);
}
.premium-table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.premium-table thead tr {
    background-color: #f8fafc;
    border-bottom: 2px solid #cbd5e1;
}
html[data-pms-theme="dark"] .premium-table thead tr {
    background-color: #183026;
    border-bottom-color: rgba(225, 255, 240, 0.15);
}
.premium-table th {
    font-size: 0.73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #475569;
    padding: 1rem 1.1rem;
    border-bottom: 2px solid #cbd5e1;
    border-right: 1px solid #e2e8f0;
    white-space: nowrap;
}
.premium-table th:last-child {
    border-right: none;
}
html[data-pms-theme="dark"] .premium-table th {
    color: #cbd5e1;
    border-bottom-color: rgba(225, 255, 240, 0.15);
    border-right-color: rgba(225, 255, 240, 0.1);
}
.premium-table tbody tr {
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.15s ease-in-out;
}
html[data-pms-theme="dark"] .premium-table tbody tr {
    border-bottom-color: rgba(225, 255, 240, 0.08);
}
.premium-table tbody tr:hover {
    background-color: #f8fafc;
}
html[data-pms-theme="dark"] .premium-table tbody tr:hover {
    background-color: #162a21;
}
.premium-table td {
    padding: 1.1rem 1.1rem;
    vertical-align: middle;
    font-size: 0.86rem;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
}
.premium-table td:last-child {
    border-right: none;
}
html[data-pms-theme="dark"] .premium-table td {
    color: #e2e8f0;
    border-bottom-color: rgba(225, 255, 240, 0.08);
    border-right-color: rgba(225, 255, 240, 0.1);
}
.badge-pill-priority-urgent {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    border-radius: 50px;
    padding: 0.32rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-high {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
    border-radius: 50px;
    padding: 0.32rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-medium {
    background-color: #e0f2fe;
    color: #0284c7;
    border: 1px solid #93c5fd;
    border-radius: 50px;
    padding: 0.32rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.badge-pill-priority-low {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.32rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.btn-action-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.btn-action-circle:hover, .btn-action-circle:focus {
    background-color: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1;
}
html[data-pms-theme="dark"] .btn-action-circle {
    background-color: #183026;
    border-color: rgba(225, 255, 240, 0.15);
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .btn-action-circle:hover {
    background-color: #204033;
    color: #ffffff;
}
.dropdown-menu-premium {
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    padding: 0.5rem;
}
html[data-pms-theme="dark"] .dropdown-menu-premium {
    background: #102119;
    border-color: rgba(225, 255, 240, 0.15);
}
.dropdown-menu-premium .dropdown-item {
    border-radius: 8px;
    padding: 0.55rem 0.85rem;
    font-size: 0.84rem;
    font-weight: 500;
    color: #334155;
    transition: all 0.15s ease;
}
.dropdown-menu-premium .dropdown-item:hover {
    background-color: #f8fafc;
    color: #0f172a;
}
html[data-pms-theme="dark"] .dropdown-menu-premium .dropdown-item {
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .dropdown-menu-premium .dropdown-item:hover {
    background-color: #183026;
    color: #ffffff;
}
.premium-table-footer {
    background-color: #e2e8f0;
    border-top: 1px solid #cbd5e1;
    padding: 0.9rem 1.25rem;
    font-size: 0.84rem;
    color: #475569;
    font-weight: 600;
}
html[data-pms-theme="dark"] .premium-table-footer {
    background-color: #14281e;
    border-top-color: rgba(225, 255, 240, 0.08);
    color: #94a3b8;
}
</style>

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;"><i class="fas fa-handshake me-2 text-success"></i>Deals & Sales Pipeline</h4>
            <p class="text-muted small mb-0">Track pipeline stages, deal probabilities, weighted values, and close rates</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.deals.index', array_merge(request()->query(), ['view' => request('view') === 'kanban' ? 'table' : 'kanban'])) }}" class="btn btn-outline-primary fw-semibold px-3">
                <i class="fas {{ request('view') === 'kanban' ? 'fa-table' : 'fa-columns' }} me-1"></i>
                Switch to {{ request('view') === 'kanban' ? 'Table View' : 'Kanban View' }}
            </a>
            <a href="{{ route('admin.deals.create') }}" class="btn btn-success fw-semibold shadow-sm px-3">
                <i class="fas fa-plus me-1"></i> Add Deal
            </a>
            <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#importDealsModal">
                <i class="fas fa-file-import me-1"></i> Import
            </button>
            <a href="{{ route('admin.deals.export', request()->query()) }}" class="btn btn-outline-secondary px-3">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    {{-- DEAL KPI CARDS ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Total Deals</span>
                    <div class="crm-kpi-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-handshake"></i></div>
                </div>
                <h3 class="crm-kpi-value">{{ number_format($kpiStats['total'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Open Deals</span>
                    <div class="crm-kpi-icon bg-info bg-opacity-10 text-info"><i class="fas fa-folder-open"></i></div>
                </div>
                <h3 class="crm-kpi-value text-info">{{ number_format($kpiStats['open'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Won Deals</span>
                    <div class="crm-kpi-icon bg-success bg-opacity-10 text-success"><i class="fas fa-trophy"></i></div>
                </div>
                <h3 class="crm-kpi-value text-success">{{ number_format($kpiStats['won'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Lost Deals</span>
                    <div class="crm-kpi-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-times-circle"></i></div>
                </div>
                <h3 class="crm-kpi-value text-danger">{{ number_format($kpiStats['lost'] ?? 0) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Pipeline Value</span>
                    <div class="crm-kpi-icon bg-purple bg-opacity-10 text-purple"><i class="fas fa-chart-line"></i></div>
                </div>
                <h3 class="crm-kpi-value text-primary fs-5">₹{{ number_format($kpiStats['pipeline_value'] ?? 0, 2) }}</h3>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="crm-kpi-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="crm-kpi-title">Weighted Value</span>
                    <div class="crm-kpi-icon bg-success bg-opacity-10 text-success"><i class="fas fa-coins"></i></div>
                </div>
                <h3 class="crm-kpi-value text-success fs-5">₹{{ number_format($kpiStats['weighted_value'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>

    {{-- TOP FILTER BAR --}}
    <form method="GET" action="{{ route('admin.deals.index') }}" id="dealFilterForm">
        <input type="hidden" name="view" value="{{ request('view', 'table') }}">
        <div class="top-bar">
            <div class="flex-grow-1 position-relative" style="min-width: 220px;">
                <input type="text" name="search" class="form-control ps-4" placeholder="Search by deal name, lead, company..." value="{{ request('search') }}">
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 13px;"></i>
            </div>

            <div style="min-width: 140px;">
                <select name="pipeline" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="All">All Pipelines</option>
                    @foreach($pipelines as $pipe)
                        <option value="{{ $pipe }}" {{ request('pipeline') == $pipe ? 'selected' : '' }}>{{ $pipe }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 140px;">
                <select name="agent_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Agents</option>
                    @foreach($agents as $ag)
                        <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 140px;">
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
            @if(request()->hasAny(['search', 'pipeline', 'agent_id', 'priority', 'start_date', 'end_date']))
                <a href="{{ route('admin.deals.index', ['view' => request('view')]) }}" class="btn btn-outline-danger btn-sm">Reset</a>
            @endif
        </div>
    </form>

    {{-- KANBAN OR TABLE VIEW --}}
    @if(request('view') === 'kanban')
        {{-- KANBAN BOARD VIEW --}}
        <div class="kanban-container">
            @foreach($stages as $stg)
                <div class="kanban-col">
                    <div class="kanban-col-header" style="border-bottom-color: {{ $stg->color ?? '#3b82f6' }};">
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #0f172a;">{{ $stg->name }}</h6>
                            <small class="text-muted">{{ count($dealsByStage[$stg->id] ?? []) }} deals</small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $stg->default_probability }}% Prob</span>
                    </div>

                    <div class="kanban-col-body kanban-column" data-stage-id="{{ $stg->id }}" data-stage-name="{{ strtolower($stg->name) }}">
                        @forelse($dealsByStage[$stg->id] ?? [] as $deal)
                            <div class="kanban-card deal-card" data-deal-id="{{ $deal->id }}" draggable="true">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0"><a href="{{ route('admin.deals.show', $deal->id) }}" class="text-dark text-decoration-none">{{ $deal->deal_name }}</a></h6>
                                    @php
                                        $prio = strtolower($deal->priority ?? 'medium');
                                        $prioBadge = match($prio) {
                                            'urgent' => 'bg-danger text-white',
                                            'high' => 'bg-warning text-dark',
                                            'low' => 'bg-secondary text-white',
                                            default => 'bg-info text-white',
                                        };
                                    @endphp
                                    <span class="badge {{ $prioBadge }}" style="font-size: 10px;">{{ $prio }}</span>
                                </div>

                                <p class="text-muted small mb-2"><i class="far fa-building me-1"></i>{{ $deal->company_name ?: ($deal->lead_name ?: 'N/A') }}</p>

                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <div>
                                        <div class="fw-bold text-success" style="font-size: 14px;">{{ $deal->currency }} {{ number_format($deal->value, 2) }}</div>
                                        <small class="text-muted" style="font-size: 11px;">Wt: {{ $deal->currency }} {{ number_format($deal->weighted_value ?? $deal->calculateWeightedValue(), 2) }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block" style="font-size: 11px;"><i class="far fa-calendar-alt me-1"></i>{{ $deal->close_date ? $deal->close_date->format('M d') : 'N/A' }}</small>
                                        <span class="small fw-semibold text-secondary"><i class="fas fa-user-circle me-1"></i>{{ $deal->agent->name ?? 'None' }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No deals in this stage</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- TABLE VIEW --}}
        <div class="premium-table-card">
            <div class="table-responsive">
                <table class="table premium-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 text-center" style="width: 45px; min-width: 45px;"><input type="checkbox" class="form-check-input" id="selectAllDeals"></th>
                            <th style="min-width: 220px;">DEAL NAME & LEAD</th>
                            <th style="min-width: 170px;">COMPANY</th>
                            <th style="min-width: 140px;">DEAL VALUE</th>
                            <th class="text-center" style="min-width: 190px;">STAGE & PROBABILITY</th>
                            <th style="min-width: 150px;">WEIGHTED VALUE</th>
                            <th style="min-width: 130px;">CLOSE DATE</th>
                            <th style="min-width: 140px;">AGENT</th>
                            <th class="text-center" style="min-width: 130px;">PRIORITY</th>
                            <th class="text-end pe-4" style="min-width: 90px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deals as $dl)
                            <tr>
                                <td class="ps-4"><input type="checkbox" class="form-check-input deal-checkbox" value="{{ $dl->id }}"></td>
                                <td>
                                    <a href="{{ route('admin.deals.show', $dl->id) }}" class="fw-bold text-decoration-none d-block" style="color: #0f172a; font-size: 0.9rem;">{{ $dl->deal_name }}</a>
                                    <span class="text-muted d-block mt-1" style="font-size: 0.79rem;"><i class="far fa-user me-1"></i>{{ $dl->lead_name }}</span>
                                </td>
                                <td><span class="fw-semibold text-secondary" style="font-size: 0.88rem;">{{ $dl->company_name ?: 'N/A' }}</span></td>
                                <td class="fw-bold text-success" style="font-size: 0.9rem;">{{ $dl->currency }} {{ number_format($dl->value, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge-pill-source text-primary" style="border-color: rgba(15, 116, 76, 0.2);">
                                        <i class="fas fa-layer-group me-1"></i>{{ $dl->stage->name ?? 'N/A' }} ({{ $dl->probability }}%)
                                    </span>
                                </td>
                                <td class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $dl->currency }} {{ number_format($dl->weighted_value ?? $dl->calculateWeightedValue(), 2) }}</td>
                                <td class="text-secondary" style="font-size: 0.84rem; font-weight: 500;">{{ $dl->close_date ? $dl->close_date->format('M d, Y') : 'N/A' }}</td>
                                <td style="font-size: 0.84rem; font-weight: 600; color: #334155;">{{ $dl->agent->name ?? 'Unassigned' }}</td>
                                <td class="text-center">
                                    @php
                                        $prio = strtolower($dl->priority ?? 'medium');
                                        $prioClass = match($prio) {
                                            'urgent' => 'badge-pill-priority-urgent',
                                            'high' => 'badge-pill-priority-high',
                                            'low' => 'badge-pill-priority-low',
                                            default => 'badge-pill-priority-medium',
                                        };
                                    @endphp
                                    <span class="{{ $prioClass }} text-capitalize">{{ $prio }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn-action-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-premium shadow-sm">
                                            <li><a class="dropdown-item" href="{{ route('admin.deals.show', $dl->id) }}"><i class="fas fa-eye text-primary me-2"></i>View Details</a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.deals.edit', $dl->id) }}"><i class="fas fa-edit text-warning me-2"></i>Edit Deal</a></li>
                                            @if(str_contains(strtolower($dl->stage->name ?? ''), 'won') || str_contains(strtolower($dl->stage->name ?? ''), 'concreted'))
                                                <li><a class="dropdown-item btn-convert-deal-client" href="javascript:void(0)" data-id="{{ $dl->id }}" data-name="{{ $dl->deal_name }}"><i class="fas fa-user-check text-success me-2"></i>Convert to Client</a></li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.deals.destroy', $dl->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this deal?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-handshake-slash fa-3x mb-3 text-muted opacity-50"></i>
                                    <h5 class="fw-bold">No Deals Found</h5>
                                    <p class="small mb-3">Create your first deal to start tracking your sales pipeline.</p>
                                    <a href="{{ route('admin.deals.create') }}" class="btn-submit-emerald d-inline-flex align-items-center text-decoration-none px-3 py-2" style="font-size: 0.85rem;"><i class="fas fa-plus me-1"></i> Add First Deal</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($deals) && ($deals->hasPages() || $deals->total() > 0))
                <div class="premium-table-footer d-flex justify-content-between align-items-center flex-wrap">
                    <div>Showing {{ $deals->firstItem() ?? 0 }} to {{ $deals->lastItem() ?? 0 }} of {{ $deals->total() }} deals</div>
                    <div>{{ $deals->links() }}</div>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- IMPORT DEALS MODAL --}}
<div class="modal fade" id="importDealsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-import me-2 text-primary"></i>Import Deals</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.deals.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select CSV File</label>
                        <input type="file" name="file" class="form-control" accept=".csv, .txt" required>
                    </div>
                    <div class="alert alert-info small mb-0">
                        Required columns: <code>Deal Name, Lead Name, Value, Close Date, Stage</code>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Import Deals</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- LOST REASON MODAL --}}
<div class="modal fade" id="lostReasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Lost Deal Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="lostReasonForm">
                <input type="hidden" id="lostDealId">
                <input type="hidden" id="lostStageId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Loss <span class="text-danger">*</span></label>
                        <select id="lostReasonSelect" class="form-select form-select-sm" required>
                            <option value="">Select Lost Reason</option>
                            <option value="Price / High Cost">Price / High Cost</option>
                            <option value="Competitor Chosen">Competitor Chosen</option>
                            <option value="No Requirement">No Requirement / Project Cancelled</option>
                            <option value="Budget Issue">Budget Issue</option>
                            <option value="Not Interested">Not Interested</option>
                            <option value="Timing / Delayed">Timing / Delayed</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Additional Notes</label>
                        <textarea id="lostNotesText" class="form-control form-control-sm" rows="3" placeholder="Provide extra feedback or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4 fw-semibold">Save & Mark Lost</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Drag and Drop for Kanban Columns
    const columns = document.querySelectorAll('.kanban-column');
    const cards = document.querySelectorAll('.deal-card');
    let draggedCard = null;

    cards.forEach(card => {
        card.addEventListener('dragstart', function(e) {
            draggedCard = this;
            e.dataTransfer.setData('text/plain', this.dataset.dealId);
        });
    });

    columns.forEach(col => {
        col.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        col.addEventListener('drop', function(e) {
            e.preventDefault();
            if (!draggedCard) return;

            const dealId = draggedCard.dataset.dealId;
            const targetStageId = this.dataset.stageId;
            const targetStageName = (this.dataset.stageName || '').toLowerCase();

            if (targetStageName.includes('lost')) {
                const lostModalEl = document.getElementById('lostReasonModal');
                if (lostModalEl) {
                    document.getElementById('lostDealId').value = dealId;
                    document.getElementById('lostStageId').value = targetStageId;
                    window.draggedCardRef = draggedCard;
                    window.targetColRef = this;
                    const modal = new bootstrap.Modal(lostModalEl);
                    modal.show();
                    return;
                }
            }

            updateStageAjax(dealId, targetStageId, draggedCard, this);
        });
    });

    const lostReasonForm = document.getElementById('lostReasonForm');
    if (lostReasonForm) {
        lostReasonForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const dealId = document.getElementById('lostDealId').value;
            const stageId = document.getElementById('lostStageId').value;
            const lostReason = document.getElementById('lostReasonSelect').value;
            const lostNotes = document.getElementById('lostNotesText').value;

            if (!lostReason) {
                alert('Please select a reason for loss.');
                return;
            }

            updateStageAjax(dealId, stageId, window.draggedCardRef, window.targetColRef, lostReason, lostNotes);
            const lostModalEl = document.getElementById('lostReasonModal');
            const modalInstance = bootstrap.Modal.getInstance(lostModalEl);
            if (modalInstance) modalInstance.hide();
        });
    }

    function updateStageAjax(dealId, stageId, cardElement, columnElement, lostReason = null, lostNotes = null) {
        const payload = { stage_id: stageId };
        if (lostReason) payload.lost_reason = lostReason;
        if (lostNotes) payload.lost_notes = lostNotes;

        fetch(`/admin/deals/${dealId}/update-stage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(async r => {
            const contentType = r.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                return r.json();
            }
            const text = await r.text();
            throw new Error('Server returned unexpected response (Status ' + r.status + ')');
        })
        .then(data => {
            if (data.success) {
                if (columnElement && cardElement) {
                    columnElement.appendChild(cardElement);
                }
                window.location.reload();
            } else {
                alert('Error updating stage: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('Failed to update stage: ' + err.message));
    }

    // Convert Deal to Client handler
    document.querySelectorAll('.btn-convert-deal-client').forEach(btn => {
        btn.addEventListener('click', function() {
            const dealId = this.dataset.id;
            const dealName = this.dataset.name;
            if (confirm(`Convert Won Deal "${dealName}" to a Client?`)) {
                fetch(`/admin/deals/${dealId}/convert-to-client`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(async r => {
                    const contentType = r.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        return r.json();
                    }
                    const text = await r.text();
                    throw new Error('Server returned unexpected response (Status ' + r.status + ')');
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to convert deal'));
                    }
                })
                .catch(err => alert('Failed to convert deal: ' + err.message));
            }
        });
    });
});
</script>

@endsection
