@extends('admin.layout.app')

@section('title', 'Notifications')

@section('content')
<main id="main" class="main">
    <div class="notification-page">
        <section class="notification-hero">
            <div>
                <span class="notification-eyebrow"><i class="fas fa-bell"></i> Notification Center</span>
                <h1>Notifications</h1>
                <p>Open any notification to mark it as read and jump directly to the related section.</p>
            </div>
            <div class="notification-actions">
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button class="btn btn-primary"><i class="fas fa-check-double"></i> Mark All Read</button>
                </form>
                <form action="{{ route('notifications.clearAll') }}" method="POST" onsubmit="return confirm('Clear all notifications?')">
                    @csrf
                    <button class="btn btn-light"><i class="fas fa-trash"></i> Clear All</button>
                </form>
            </div>
        </section>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <section class="notification-list-card">
            @forelse($notifications as $notification)
                @php
                    $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
                    $title = $data['title'] ?? $data['heading'] ?? class_basename($notification->type);
                    $message = $data['message'] ?? $data['body'] ?? $data['msg'] ?? '';
                    $icon = $data['icon'] ?? 'fa-bell';
                    $color = $data['color'] ?? 'info';
                    $isUnread = is_null($notification->read_at);
                @endphp

                <a href="{{ route('notifications.open', $notification->id) }}"
                   class="notification-list-item {{ $isUnread ? 'is-unread' : '' }}">
                    <span class="notification-list-icon color-{{ $color }}">
                        <i class="fas {{ $icon }}"></i>
                    </span>
                    <span class="notification-list-content">
                        <span class="notification-list-title">{{ $title }}</span>
                        @if($message)
                            <span class="notification-list-message">{{ $message }}</span>
                        @endif
                        <span class="notification-list-time"><i class="fas fa-clock"></i>{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                    <span class="notification-list-state">
                        @if($isUnread)
                            <span class="unread-pill">New</span>
                        @else
                            <span class="read-pill">Read</span>
                        @endif
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </a>
            @empty
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No notifications</h3>
                    <p>You are all caught up.</p>
                </div>
            @endforelse
        </section>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
</main>

<style>
    .notification-page {
        min-height: 100vh;
        padding: 24px;
        background: #f6f8fb;
    }

    .notification-hero,
    .notification-list-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .notification-hero {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        padding: 24px;
        margin-bottom: 18px;
    }

    .notification-eyebrow {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        color: #4f46e5;
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .notification-hero h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 900;
        color: #0f172a;
    }

    .notification-hero p {
        margin: 0;
        color: #64748b;
        font-weight: 650;
    }

    .notification-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .notification-page .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        font-weight: 900;
    }

    .notification-page .btn-primary {
        border: 0;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }

    .notification-page .btn-light {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .notification-list-card {
        overflow: hidden;
    }

    .notification-list-item {
        display: grid;
        grid-template-columns: 52px 1fr auto;
        gap: 14px;
        align-items: center;
        padding: 16px 18px;
        color: inherit;
        text-decoration: none;
        border-bottom: 1px solid rgba(15, 23, 42, 0.07);
        transition: background .16s ease, transform .16s ease;
    }

    .notification-list-item:hover {
        background: #f8fafc;
        color: inherit;
        transform: translateX(2px);
    }

    .notification-list-item.is-unread {
        background: #eef2ff;
    }

    .notification-list-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 19px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }

    .notification-list-icon.color-warning { background: linear-gradient(135deg, #f59e0b, #f97316); }
    .notification-list-icon.color-success { background: linear-gradient(135deg, #10b981, #059669); }
    .notification-list-icon.color-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .notification-list-icon.color-info { background: linear-gradient(135deg, #06b6d4, #2563eb); }

    .notification-list-content,
    .notification-list-title,
    .notification-list-message,
    .notification-list-time {
        display: block;
    }

    .notification-list-title {
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .notification-list-message {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 7px;
    }

    .notification-list-time {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 800;
    }

    .notification-list-time i {
        margin-right: 6px;
    }

    .notification-list-state {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
    }

    .unread-pill,
    .read-pill {
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 900;
    }

    .unread-pill {
        color: #fff;
        background: #4f46e5;
    }

    .read-pill {
        color: #64748b;
        background: #f1f5f9;
    }

    .notification-empty {
        padding: 56px 20px;
        text-align: center;
        color: #64748b;
    }

    .notification-empty i {
        font-size: 42px;
        color: #94a3b8;
        margin-bottom: 12px;
    }

    .notification-empty h3 {
        font-weight: 900;
        color: #0f172a;
    }

    @media (max-width: 768px) {
        .notification-page { padding: 14px; }
        .notification-hero { align-items: flex-start; flex-direction: column; }
        .notification-list-item { grid-template-columns: 44px 1fr; }
        .notification-list-state { grid-column: 2; justify-content: space-between; }
    }
</style>
@endsection
