@extends('admin.layout.app')


@section('content')
@php
    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
@endphp
<div class="container-fluid py-4 client-dashboard-page">
    <section class="dashboard-polish-shell">
        <div class="dashboard-polish-hero">
            <div>
                <span class="dashboard-polish-eyebrow">Client workspace</span>
                <h1>Client Dashboard</h1>
                <p>Review client totals and filtered client activity from one responsive workspace.</p>
                <form method="GET" class="dashboard-polish-filter">
                    <label>Date Range</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDateFormatted }}">
                    <span>to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDateFormatted }}">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
            </div>
            <div class="dashboard-polish-focus">
                <span>Total Clients</span>
                <strong>{{ $totalClients }}</strong>
                <small>Current filtered view</small>
            </div>
        </div>

        <div class="dashboard-polish-grid dashboard-polish-grid-3">
            <div class="dashboard-polish-card">
                <span>Total Clients</span>
                <strong>{{ $totalClients }}</strong>
                <small>All client records</small>
            </div>
            <div class="dashboard-polish-card">
                <span>Date From</span>
                <strong>{{ \Carbon\Carbon::parse($startDateFormatted)->format('d M') }}</strong>
                <small>Filter start</small>
            </div>
            <div class="dashboard-polish-card">
                <span>Date To</span>
                <strong>{{ \Carbon\Carbon::parse($endDateFormatted)->format('d M') }}</strong>
                <small>Filter end</small>
            </div>
        </div>
    </section>
</div>
@endsection
