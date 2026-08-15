@extends('layouts.developer')

@section('title', 'Deadlines')
@section('page_title', 'Upcoming Deadlines & Schedule')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- HEADER BANNER -->
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 24px 30px; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <i class="bx bx-time-five" style="font-size: 24px; color: #f59e0b;"></i>
                <h1 style="font-size: 22px; font-weight: 800; letter-spacing: -0.4px;">Deadlines & Target Delivery Dates</h1>
            </div>
            <p style="color: #94a3b8; font-size: 13.5px; font-weight: 500;">
                Calculated in real-time from active assigned tasks requiring delivery across tenant platforms.
            </p>
        </div>
    </div>

    <!-- DEADLINES CARDS CONTAINER -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @forelse($tasks as $task)
        <div class="dev-card" style="margin-bottom: 0; padding: 20px; border-left: 4px solid {{ $task->deadline_status === 'OVERDUE' ? '#ef4444' : ($task->deadline_status === 'DUE SOON' ? '#f59e0b' : '#10b981') }};">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 12px; text-transform: uppercase; background: {{ $task->deadline_status === 'OVERDUE' ? '#fef2f2' : ($task->deadline_status === 'DUE SOON' ? '#fffbe6' : '#ecfdf5') }}; color: {{ $task->deadline_status === 'OVERDUE' ? '#dc2626' : ($task->deadline_status === 'DUE SOON' ? '#d97706' : '#059669') }}; border: 1px solid {{ $task->deadline_status === 'OVERDUE' ? '#fecaca' : ($task->deadline_status === 'DUE SOON' ? '#ffe58f' : '#a7f3d0') }};">
                            ● {{ $task->deadline_status }}
                        </span>
                        <span style="font-size: 11.5px; font-weight: 700; color: var(--slate-muted);">
                            {{ $task->deadline_status === 'OVERDUE' ? 'Overdue by ' . abs($task->days_remaining) . ' days' : ($task->days_remaining === 0 ? 'Due Today' : 'Due in ' . $task->days_remaining . ' days') }}
                        </span>
                    </div>

                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin-bottom: 4px;">{{ $task->title }}</h3>
                    <span style="font-size: 12.5px; color: var(--slate-muted);">
                        Company: <strong>{{ $task->company_name ?? 'Central Platform' }}</strong> &bull; Project: <strong>{{ $task->project_name ?? 'Development' }}</strong>
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="text-align: right;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--slate-muted); text-transform: uppercase; display: block;">TARGET DEADLINE</span>
                        <strong style="font-size: 14px; color: {{ $task->deadline_status === 'OVERDUE' ? '#dc2626' : 'var(--slate-dark)' }};">
                            {{ !empty($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No Deadline' }}
                        </strong>
                    </div>

                    <a href="{{ route('developer.my-work') }}" style="padding: 8px 14px; border-radius: var(--radius-md); background: var(--slate-dark); color: #ffffff; text-decoration: none; font-size: 12.5px; font-weight: 700;">
                        Open Task &rarr;
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="dev-card" style="text-align: center; padding: 48px 24px;">
            <i class="bx bx-check-double" style="font-size: 40px; color: #10b981; margin-bottom: 12px; display: block;"></i>
            <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-dark); margin-bottom: 6px;">No Upcoming Deadlines</h3>
            <p style="font-size: 13.5px; color: var(--slate-muted); max-width: 450px; margin: 0 auto;">
                All active development tasks are currently up to date or completed. Great job!
            </p>
        </div>
        @endforelse
    </div>

</div>
@endsection
