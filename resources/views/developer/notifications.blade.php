@extends('layouts.developer')

@section('title', 'Notifications')
@section('page_title', 'Developer Notifications')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- HEADER -->
    <div class="dev-card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1 style="font-size: 20px; font-weight: 800; color: var(--slate-dark);">Notifications & System Telemetry</h1>
                <p style="font-size: 13px; color: var(--slate-muted); margin-top: 4px;">Updates regarding work assignments, deadline shifts, and status changes.</p>
            </div>
            <i class="bx bx-bell" style="font-size: 28px; color: var(--primary);"></i>
        </div>
    </div>

    <!-- NOTIFICATIONS LIST -->
    <div class="dev-card">
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($notifications as $item)
            <div style="display: flex; align-items: flex-start; gap: 14px; padding: 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="bx bx-info-circle"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <strong style="font-size: 13.5px; color: var(--slate-dark);">Work Telemetry Update: {{ $item->title }}</strong>
                        <span style="font-size: 11.5px; font-weight: 600; color: var(--slate-muted);">{{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</span>
                    </div>
                    <p style="font-size: 12.5px; color: var(--slate-body); margin-top: 4px;">
                        Task status is currently <strong>{{ strtoupper(str_replace('_', ' ', $item->status)) }}</strong>. Company: <strong>{{ $item->company_name ?? 'Central' }}</strong> &bull; Project: <strong>{{ $item->project_name ?? 'General' }}</strong>
                    </p>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 32px; color: var(--slate-muted);">
                <i class="bx bx-bell-off" style="font-size: 36px; color: var(--slate-muted); display: block; margin-bottom: 8px;"></i>
                <span>You're all caught up. No unread notifications.</span>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div style="margin-top: 16px; display: flex; justify-content: center;">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
