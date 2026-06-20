@extends('admin.layout.app')

@section('title', 'Ticket Dashboard')

@section('content')

@php
    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
@endphp

<style>
    .small-chart {
        height: 240px !important;
        max-height: 240px !important;
        width: 100% !important;
    }
</style>
<div class="container-fluid py-4 ticket-dashboard-page">
    <section class="dashboard-polish-shell">
        <div class="dashboard-polish-hero">
            <div>
                <span class="dashboard-polish-eyebrow">Support workspace</span>
                <h1>Ticket Dashboard</h1>
                <p>Track unresolved, resolved, unassigned, channel, type, and priority signals in one responsive view.</p>
                <form method="GET" class="dashboard-polish-filter">
                    <label>Date Range</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDateFormatted }}">
                    <span>to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDateFormatted }}">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
            </div>
            <div class="dashboard-polish-focus">
                <span>Open Queue</span>
                <strong>{{ $unresolved }}</strong>
                <small>{{ $resolved }} resolved</small>
            </div>
        </div>

        <div class="dashboard-polish-grid dashboard-polish-grid-3">
            <div class="dashboard-polish-card">
                <span>Unresolved</span>
                <strong>{{ $unresolved }}</strong>
                <small>Tickets waiting</small>
            </div>
            <div class="dashboard-polish-card">
                <span>Resolved</span>
                <strong>{{ $resolved }}</strong>
                <small>Closed work</small>
            </div>
            <div class="dashboard-polish-card">
                <span>Unassigned</span>
                <strong>{{ $unassigned }}</strong>
                <small>Needs owner</small>
            </div>
        </div>

    <!-- Charts -->
    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="card dashboard-polish-panel h-100">
                <div class="card-header"><h6 class="mb-0">Type Wise Ticket</h6></div>
                <div class="card-body p-3">
                    <canvas id="typeWiseChart" class="small-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-polish-panel h-100">
                <div class="card-header"><h6 class="mb-0">Status Wise Ticket</h6></div>
                <div class="card-body p-3 text-center">
                    @if(empty($statusWiseData))
                        <p class="text-muted">- Not enough data -</p>
                    @else
                        <canvas id="statusWiseChart" class="small-chart"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Channel and Open Tickets -->
    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="card dashboard-polish-panel h-100">
                <div class="card-header"><h6 class="mb-0">Channel Wise Ticket</h6></div>
                <div class="card-body p-3 text-center">
                    @if(empty($channelWiseData))
                        <p class="text-muted">- Not enough data -</p>
                    @else
                        <canvas id="channelWiseChart"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-polish-panel h-100">
                <div class="card-header"><h6 class="mb-0">Open Tickets</h6></div>
                <div class="card-body p-3">
                    @forelse($openTickets as $ticket)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $ticket->subject }}</strong><br>
                                <small>{{ $ticket->employee->name ?? 'N/A' }}</small>
                            </div>
                            <div class="text-end">
                                <small>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d-m-Y') }}</small><br>
                                <span class="badge bg-warning text-dark">{{ ucfirst($ticket->priority) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No open tickets.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    </section>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const typeCtx = document.getElementById('typeWiseChart');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($typeWiseData)) !!},
            datasets: [{
                data: {!! json_encode(array_values($typeWiseData)) !!},
                backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107']
            }]
        }
    });

    const statusCtx = document.getElementById('statusWiseChart');
    @if(!empty($statusWiseData))
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusWiseData)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusWiseData)) !!},
                backgroundColor: ['#17a2b8', '#6f42c1', '#fd7e14', '#20c997']
            }]
        }
    });
    @endif

    const channelCtx = document.getElementById('channelWiseChart');
    @if(!empty($channelWiseData))
    new Chart(channelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($channelWiseData)) !!},
            datasets: [{
                label: 'Tickets',
                data: {!! json_encode(array_values($channelWiseData)) !!},
                backgroundColor: '#6c757d'
            }]
        }
    });
    @endif
</script>
@endpush
