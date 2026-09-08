@extends('layouts.developer')

@section('title', 'Notifications')
@section('page_title', 'Developer Notifications')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- HEADER -->
    <div class="dev-card" style="margin-bottom: 0; padding: 26px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1 style="font-size: 20px; font-weight: 800; color: var(--slate-dark);">Notifications & System Telemetry</h1>
                <p style="font-size: 13.5px; color: var(--slate-muted); margin-top: 4px;">Updates regarding work assignments, deadline shifts, and status changes.</p>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid var(--primary-border);">
                <i class="bx bx-bell"></i>
            </div>
        </div>
    </div>

    <!-- NOTIFICATIONS LIST -->
    <div class="dev-card" style="padding: 26px;">
        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($notifications as $item)
            <div style="display: flex; align-items: flex-start; gap: 16px; padding: 16px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); transition: background 0.15s ease;" onmouseover="this.style.background='#ffffff'" onmouseout="this.style.background='#f8fafc'">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; margin-top: 2px;">
                    <i class="bx bx-info-circle"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <strong style="font-size: 14px; font-weight: 800; color: var(--slate-dark);">Work Telemetry Update: {{ $item->title }}</strong>
                        <span style="font-size: 12px; font-weight: 700; color: var(--slate-muted);">{{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</span>
                    </div>
                    <p style="font-size: 13px; color: var(--slate-body); margin-top: 6px; line-height: 1.5;">
                        Task status is currently <strong style="color: var(--slate-dark);">{{ strtoupper(str_replace('_', ' ', $item->status)) }}</strong>. Company: <strong style="color: var(--slate-dark);">{{ $item->company_name ?? 'Central' }}</strong> &bull; Project: <strong style="color: var(--slate-dark);">{{ $item->project_name ?? 'General' }}</strong>
                    </p>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 48px; color: var(--slate-muted);">
                <i class="bx bx-bell-off" style="font-size: 40px; color: var(--slate-muted); display: block; margin-bottom: 10px;"></i>
                <span style="font-size: 14px; font-weight: 600;">You're all caught up. No unread notifications.</span>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

