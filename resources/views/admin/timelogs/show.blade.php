@extends('admin.layout.app')

@section('content')
@include('admin.timelogs.partials.workspace-styles')

<div id="pms-timelog-show-page" class="pms-ts-page">
    <div class="pms-ts-topbar">
        <div class="pms-ts-title-wrap">
            <div class="pms-ts-title-icon" aria-hidden="true">
                <i class="bx bx-time-five"></i>
            </div>
            <div>
                <h3 class="pms-ts-title">TimeLog Details</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('timelogs.index') }}" class="text-decoration-none">Timesheet</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Log Details</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="pms-ts-actions">
            <a href="{{ route('timelogs.index') }}" class="btn pms-ts-btn-secondary">
                <i class="bx bx-arrow-back"></i> Back to Timesheet
            </a>
            <a href="{{ route('timelogs.edit', $log->id) }}" class="btn pms-ts-btn-primary">
                <i class="bx bx-edit"></i> Edit Log
            </a>
        </div>
    </div>

    <div class="card pms-ts-card">
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="pms-ts-detail-tile">
                        <span class="pms-ts-label">Start Time</span>
                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($log->start_time)->format('d-m-Y h:i A') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="pms-ts-detail-tile">
                        <span class="pms-ts-label">End Time</span>
                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($log->end_time)->format('d-m-Y h:i A') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="pms-ts-detail-tile">
                        <span class="pms-ts-label">Total Hours</span>
                        <div class="fw-bold text-primary">{{ gmdate('H:i:s', $log->total_hours * 3600) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="pms-ts-detail-tile">
                        <span class="pms-ts-label">Earnings</span>
                        <div class="fw-bold text-dark">₹0.00</div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <span class="pms-ts-label">Project</span>
                    <div class="fw-semibold text-dark">{{ $log->project->name ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <span class="pms-ts-label">Task</span>
                    <div class="fw-semibold text-dark">{{ $log->task->title ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <span class="pms-ts-label">Memo</span>
                    <div class="text-dark">{{ $log->memo ?: 'No memo provided.' }}</div>
                </div>
            </div>

            <div class="mb-4">
                <span class="pms-ts-label">Employee</span>
                <div class="pms-ts-user-box">
                    <strong class="text-dark">{{ $log->user->name ?? '-' }}</strong><br>
                    <small class="text-muted">It's you</small><br>
                    <span class="text-muted small">{{ $log->user->designation ?? 'Employee' }}</span>
                </div>
            </div>

            <div>
                <span class="pms-ts-label">History</span>
                <ul class="list-group rounded-3">
                    @php
                        $startTime = \Carbon\Carbon::parse($log->start_time);
                    @endphp
                    <li class="list-group-item">
                        <strong>Start Date Time:</strong> {{ $startTime->format('d-m-Y h:i A') }}
                    </li>

                    <li class="list-group-item">
                        <strong>Task Name:</strong> {{ $log->task->title ?? '-' }}
                    </li>
                    
                    @php
                        $endTime = \Carbon\Carbon::parse($log->end_time);
                    @endphp
                    <li class="list-group-item">
                        <strong>End Date Time:</strong> {{ $endTime->format('d-m-Y h:i A') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
