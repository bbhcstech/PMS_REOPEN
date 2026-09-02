@extends('layouts.developer')

@section('title', 'Deadlines')
@section('page_title', 'Upcoming Deadlines & Schedule')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- HEADER BANNER -->
    <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; padding: 28px 36px; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bx bx-time-five" style="color: #ffffff;"></i>
                </div>
                <h1 style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: #ffffff;">Deadlines & Target Delivery Dates</h1>
            </div>
            <p style="color: #a7f3d0; font-size: 14px; font-weight: 500;">
                Calculated in real-time from active assigned tasks requiring delivery across tenant platforms.
            </p>
        </div>
    </div>

    <!-- DEADLINES CARDS CONTAINER -->
    <div style="display: flex; flex-direction: column; gap: 18px;">
        @forelse($tasks as $task)
        <div class="dev-card" style="margin-bottom: 0; padding: 24px; border-left: 4px solid {{ $task->deadline_status === 'OVERDUE' ? '#ef4444' : ($task->deadline_status === 'DUE SOON' ? '#f59e0b' : '#10b981') }};">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 14px; text-transform: uppercase; letter-spacing: 0.5px; background: {{ $task->deadline_status === 'OVERDUE' ? '#fef2f2' : ($task->deadline_status === 'DUE SOON' ? '#fffbe6' : '#ecfdf5') }}; color: {{ $task->deadline_status === 'OVERDUE' ? '#dc2626' : ($task->deadline_status === 'DUE SOON' ? '#d97706' : '#059669') }}; border: 1px solid {{ $task->deadline_status === 'OVERDUE' ? '#fecaca' : ($task->deadline_status === 'DUE SOON' ? '#ffe58f' : '#a7f3d0') }};">
                            ● {{ $task->deadline_status }}
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: var(--slate-muted);">
                            {{ $task->deadline_status === 'OVERDUE' ? 'Overdue by ' . abs($task->days_remaining) . ' days' : ($task->days_remaining === 0 ? 'Due Today' : 'Due in ' . $task->days_remaining . ' days') }}
                        </span>
                    </div>

                    <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-dark); margin-bottom: 6px;">{{ $task->title }}</h3>
                    <span style="font-size: 13px; color: var(--slate-muted);">
                        Company: <strong style="color: var(--slate-dark);">{{ $task->company_name ?? 'Central Platform' }}</strong> &bull; Project: <strong style="color: var(--slate-dark);">{{ $task->project_name ?? 'Development' }}</strong>
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="text-align: right;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">TARGET DEADLINE</span>
                        <strong style="font-size: 15px; font-weight: 800; color: {{ $task->deadline_status === 'OVERDUE' ? '#dc2626' : 'var(--slate-dark)' }};">
                            {{ !empty($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No Deadline' }}
                        </strong>
                    </div>

                    <a href="{{ route('developer.my-work') }}" style="padding: 10px 18px; border-radius: var(--radius-md); background: var(--slate-dark); color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; box-shadow: var(--shadow-xs); transition: background 0.2s;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='var(--slate-dark)'">
                        Open Task &rarr;
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="dev-card" style="text-align: center; padding: 56px 24px;">
            <i class="bx bx-check-double" style="font-size: 44px; color: #10b981; margin-bottom: 14px; display: block;"></i>
            <h3 style="font-size: 19px; font-weight: 800; color: var(--slate-dark); margin-bottom: 6px;">No Upcoming Deadlines</h3>
            <p style="font-size: 14px; color: var(--slate-muted); max-width: 480px; margin: 0 auto;">
                All active development tasks are currently up to date or completed. Great job!
            </p>
        </div>
        @endforelse
    </div>

</div>
@endsection

