@extends('admin.layout.app')

@section('title', 'Project Members - ' . $project->name)

@section('content')
@php
    $isAdmin = auth()->user()?->role === 'admin';
@endphp

<div class="project-members-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-users"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / <strong>Members</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1>Project Members</h1>
                    <p>Manage team members for <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                @if($isAdmin)
                    <a href="{{ route('project-members.create', $project->id) }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Member
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div>
                    <h3>{{ $members->count() }}</h3>
                    <span>Total Members</span>
                    <p class="stat-sub">Team size</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                <div>
                    <h3>{{ $members->filter(fn($m) => ($m->pivot->role ?? '') === 'Project Admin')->count() }}</h3>
                    <span>Admins</span>
                    <p class="stat-sub">Project administrators</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user"></i></div>
                <div>
                    <h3>{{ $members->filter(fn($m) => ($m->pivot->role ?? '') === 'Project Member')->count() }}</h3>
                    <span>Members</span>
                    <p class="stat-sub">Team contributors</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>{{ $members->where('pivot.updated_at', '>=', now()->subDays(7))->count() }}</h3>
                    <span>Recently Active</span>
                    <p class="stat-sub">Last 7 days</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs-wrapper">
            <ul class="nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.show', $project->id) }}">
                        <i class="fas fa-chart-pie"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('project-members.index', $project->id) }}">
                        <i class="fas fa-users"></i> Members
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('project-files.index', $project->id) }}">
                        <i class="fas fa-folder-open"></i> Files
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('milestones.index', $project->id) }}">
                        <i class="fas fa-flag-checkered"></i> Milestones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.tasks.index', $project->id) }}">
                        <i class="fas fa-tasks"></i> Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.tasks.board', $project->id) }}">
                        <i class="fas fa-columns"></i> Task Board
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.gantt', $project->id) }}">
                        <i class="fas fa-chart-bar"></i> Gantt Chart
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.timelogs.index', $project->id) }}">
                        <i class="fas fa-clock"></i> Timesheet
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('expenses.index', $project->id) }}">
                        <i class="fas fa-money-bill-wave"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.notes.index', $project->id) }}">
                        <i class="fas fa-sticky-note"></i> Notes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link more-toggle" href="#" id="toggle-more">
                        <i class="fas fa-ellipsis-h"></i> More <i class="fas fa-chevron-down"></i>
                    </a>
                </li>
            </ul>

            <!-- Collapsible Extra Tabs -->
            <ul class="nav-tabs extra-tabs d-none" id="more-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.discussions.index', $project->id) }}">
                        <i class="fas fa-comments"></i> Discussion
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('projects.burndown', $project->id) }}">
                        <i class="fas fa-fire"></i> Burndown Chart
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.activities.project', $project->id) }}">
                        <i class="fas fa-history"></i> Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tickets.index', ['project_id' => $project->id]) }}">
                        <i class="fas fa-ticket-alt"></i> Tickets
                    </a>
                </li>
            </ul>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Main Content Card -->
        <div class="content-card">
            <div class="table-header">
                <div class="table-title">
                    <div class="table-title-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <h4>Member List</h4>
                        <span class="muted">{{ $members->count() }} members in this project</span>
                    </div>
                </div>
                <div class="table-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="memberSearch" placeholder="Search members..." />
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="memberTable" class="member-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> #</th>
                            <th><i class="fas fa-user"></i> Name</th>
                            <th><i class="fas fa-dollar-sign"></i> Hourly Rate</th>
                            <th><i class="fas fa-badge"></i> Role</th>
                            <th><i class="fas fa-calendar-alt"></i> Joined</th>
                            @if($isAdmin)
                                <th class="action-cell"><i class="fas fa-cog"></i> Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            <tr>
                                <td>
                                    <span class="index-badge">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <div class="member-info">
                                        <div class="member-avatar">
                                            @if($member->profile_image)
                                                <img src="{{ asset($member->profile_image) }}" alt="{{ $member->name }}">
                                            @else
                                                {{ strtoupper(mb_substr($member->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="member-name">{{ $member->name }}</div>
                                            <small class="member-email">{{ $member->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="rate-badge">
                                        <i class="fas fa-coins"></i>
                                        ${{ number_format($member->pivot->hourly_rate ?? 0, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $role = $member->pivot->role ?? 'Project Member';
                                        $roleClass = $role === 'Project Admin' ? 'admin' : 'member';
                                    @endphp
                                    <span class="role-badge role-{{ $roleClass }}">
                                        <i class="fas fa-{{ $role === 'Project Admin' ? 'user-shield' : 'user' }}"></i>
                                        {{ $role }}
                                    </span>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $member->pivot->created_at ? \Carbon\Carbon::parse($member->pivot->created_at)->format('M d, Y') : '-' }}
                                    </div>
                                </td>
                                @if($isAdmin)
                                    <td class="action-cell">
                                        <form action="{{ route('project-members.destroy', [$project->id, $member->id]) }}" method="POST" onsubmit="return confirm('Remove this member from the project?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Remove Member">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 6 : 5 }}">
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash"></i>
                                        <h5>No Members Found</h5>
                                        <p>This project doesn't have any members yet.</p>
                                        @if($isAdmin)
                                            <a href="{{ route('project-members.create', $project->id) }}" class="btn btn-primary">
                                                <i class="fas fa-user-plus"></i> Add First Member
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <div class="footer-info">
                    <i class="fas fa-info-circle"></i>
                    Showing {{ $members->count() }} member(s)
                </div>
                <div class="footer-status">
                    <span class="status-dot"></span>
                    <span>Last updated: {{ now()->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-users text-primary"></i>
                <span>{{ $members->count() }}</span> Total Members
            </div>
            <div class="status-item">
                <i class="fas fa-user-shield text-success"></i>
                <span>{{ $members->filter(fn($m) => ($m->pivot->role ?? '') === 'Project Admin')->count() }}</span> Admins
            </div>
            <div class="status-item">
                <i class="fas fa-user text-info"></i>
                <span>{{ $members->filter(fn($m) => ($m->pivot->role ?? '') === 'Project Member')->count() }}</span> Members
            </div>
            <div class="status-item">
                <i class="fas fa-clock text-warning"></i>
                <span>{{ $members->where('pivot.updated_at', '>=', now()->subDays(7))->count() }}</span> Active (7d)
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM PROJECT MEMBERS PAGE - GREEN/TEAL THEME ===== */
    .project-members-page {
        padding: 30px 0;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #07130d;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 16px 26px;
        border-radius: 18px;
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .breadcrumb i {
        margin-right: 12px;
        color: #34d399;
        font-size: 1.1rem;
    }

    .breadcrumb a {
        color: #0f744c;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb a:hover {
        color: #10b981;
    }

    .breadcrumb strong {
        color: #07130d;
    }

    /* Header Card */
    .header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 30px 36px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .header-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .2);
    }

    .header-card h1 {
        font-size: 34px;
        font-weight: 700;
        margin-bottom: 6px;
        color: #07130d;
    }

    .header-card p {
        color: #52645a;
        font-size: 17px;
        margin: 0;
    }

    .header-card p strong {
        color: #0f744c;
    }

    .header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s ease;
        text-decoration: none;
        min-height: 48px;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(15, 116, 76, .2);
        color: #0f744c;
    }

    .btn-outline:hover {
        background: #edf8f2;
        border-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 116, 76, .1);
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, .25);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 185, 129, .35);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        padding: 22px;
        border-radius: 22px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 14px 35px rgba(15, 116, 76, .06);
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(15, 116, 76, .12);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        background: #d1fae5;
        color: #0f744c;
    }

    .stat-card h3 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 2px;
        line-height: 1;
    }

    .stat-card span {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }

    /* Navigation Tabs */
    .nav-tabs-wrapper {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        overflow: hidden;
        margin-bottom: 28px;
        box-shadow: 0 8px 25px rgba(15, 116, 76, .06);
    }

    .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        padding: 8px 12px;
        margin: 0;
        list-style: none;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .08);
    }

    .nav-tabs.extra-tabs {
        border-top: 1px solid rgba(15, 116, 76, .08);
        border-bottom: none;
        padding-top: 8px;
    }

    .nav-item {
        margin: 0;
    }

    .nav-link {
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a6e63;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link i {
        font-size: 0.95rem;
    }

    .nav-link:hover {
        background: #edf8f2;
        color: #0f744c;
    }

    .nav-link.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
    }

    .nav-link.more-toggle {
        color: #0f744c;
        cursor: pointer;
    }

    .nav-link.more-toggle:hover {
        background: #edf8f2;
    }

    /* Alerts */
    .alert {
        border-radius: 16px;
        padding: 18px 24px;
        margin-bottom: 22px;
        border: none;
        font-weight: 600;
        font-size: 1rem;
    }

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
        overflow: hidden;
    }

    /* Table Header */
    .table-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .table-title-icon {
        width: 48px;
        height: 48px;
        background: #e7f5ee;
        color: #0f744c;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .table-title h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .table-title .muted {
        font-size: 0.95rem;
        color: #8ba198;
        display: block;
        margin-top: 2px;
    }

    .table-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding: 12px 18px 12px 44px;
        border-radius: 40px;
        border: 1px solid rgba(15, 116, 76, .18);
        outline: none;
        font-weight: 500;
        font-size: 1rem;
        min-width: 260px;
        transition: all 0.2s;
        background: #fafefb;
    }

    .search-box input:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #0f744c;
        font-size: 1.1rem;
    }

    /* Table */
    .table-responsive {
        padding: 8px 20px 20px 20px;
        overflow-x: auto;
    }

    .member-table {
        width: 100%;
        min-width: 750px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .member-table thead th {
        text-align: left;
        padding: 14px 18px;
        color: #5a6e63;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: transparent;
        border-bottom: 2px solid rgba(15, 116, 76, .1);
        white-space: nowrap;
    }

    .member-table thead th i {
        margin-right: 8px;
        color: #34d399;
        font-size: 0.9rem;
    }

    .member-table tbody td {
        background: #ffffff;
        padding: 16px 18px;
        border-top: 1px solid rgba(15, 116, 76, .06);
        border-bottom: 1px solid rgba(15, 116, 76, .06);
        vertical-align: middle;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .member-table tbody td:first-child {
        border-left: 1px solid rgba(15, 116, 76, .06);
        border-radius: 14px 0 0 14px;
    }

    .member-table tbody td:last-child {
        border-right: 1px solid rgba(15, 116, 76, .06);
        border-radius: 0 14px 14px 0;
    }

    .member-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(15, 116, 76, .12);
    }

    .action-cell {
        text-align: center;
        width: 80px;
    }

    /* Index Badge */
    .index-badge {
        display: inline-block;
        width: 32px;
        height: 32px;
        background: #f0f9f4;
        color: #0f744c;
        border-radius: 50%;
        text-align: center;
        line-height: 32px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    /* Member Info */
    .member-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .member-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #d1fae5;
        color: #0f744c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        overflow: hidden;
        flex-shrink: 0;
    }

    .member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-name {
        font-weight: 600;
        color: #07130d;
        font-size: 0.95rem;
    }

    .member-email {
        font-size: 0.8rem;
        color: #8ba198;
    }

    /* Rate Badge */
    .rate-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #f0f9f4;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #0f744c;
    }

    .rate-badge i {
        color: #34d399;
        font-size: 0.8rem;
    }

    /* Role Badge */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .role-badge.role-admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .role-badge.role-member {
        background: #d1fae5;
        color: #065f46;
    }

    /* Date Cell */
    .date-cell {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #5a6e63;
    }

    .date-cell i {
        color: #0f744c;
        font-size: 0.9rem;
    }

    /* Action Button */
    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: none;
        background: #f0f9f4;
        color: #0f744c;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-size: 1rem;
    }

    .action-btn:hover {
        background: #d1fae5;
        transform: scale(1.05);
    }

    .action-btn.delete-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Table Footer */
    .table-footer {
        padding: 18px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    .footer-info {
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .footer-info i {
        color: #0f744c;
        margin-right: 8px;
        font-size: 1rem;
    }

    .footer-status {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.9rem;
        color: #8ba198;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #10b981;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #a7f3d0;
        margin-bottom: 18px;
    }

    .empty-state h5 {
        color: #0f744c;
        font-weight: 700;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #8ba198;
        font-size: 1rem;
        margin-bottom: 20px;
    }

    /* Status Bar */
    .status-bar {
        margin-top: 28px;
        background: white;
        border-radius: 20px;
        padding: 18px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        border: 1px solid rgba(15, 116, 76, .1);
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        color: #5a6e63;
    }

    .status-item i {
        font-size: 1.1rem;
    }

    .status-item span {
        font-weight: 700;
        color: #07130d;
        margin-right: 4px;
        font-size: 1.05rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
        }
        .nav-tabs {
            flex-wrap: wrap;
        }
        .nav-link {
            padding: 8px 14px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .project-members-page {
            padding: 16px 0;
        }
        .header-card {
            padding: 20px 24px;
        }
        .header-card h1 {
            font-size: 26px;
        }
        .header-card p {
            font-size: 15px;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .table-header {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box input {
            min-width: 180px;
            width: 100%;
        }
        .table-responsive {
            padding: 8px 12px 12px 12px;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .member-table {
            min-width: 600px;
        }
        .member-table thead th {
            font-size: 0.75rem;
            padding: 12px 12px;
        }
        .member-table tbody td {
            font-size: 0.85rem;
            padding: 12px 12px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .project-members-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .status-bar,
    html[data-pms-theme="dark"] .nav-tabs-wrapper {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .table-title h4,
    html[data-pms-theme="dark"] .member-name,
    html[data-pms-theme="dark"] .stat-card h3 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .member-table tbody td {
        background: #102119;
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .member-table tbody tr:hover td {
        background: #183026;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .search-box input {
        background: #183026;
        color: #ffffff;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .table-header,
    html[data-pms-theme="dark"] .table-footer {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .member-avatar {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .index-badge {
        background: #183026;
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .rate-badge {
        background: #183026;
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .role-badge.role-admin {
        background: rgba(59, 130, 246, .2);
        color: #60a5fa;
    }

    html[data-pms-theme="dark"] .role-badge.role-member {
        background: rgba(16, 185, 129, .2);
        color: #34d399;
    }

    html[data-pms-theme="dark"] .nav-link {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .nav-link:hover {
        background: rgba(122, 240, 181, .1);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .nav-link.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: white;
    }

    html[data-pms-theme="dark"] .alert-success {
        background: rgba(16, 185, 129, .15);
        color: #34d399;
    }

    html[data-pms-theme="dark"] .alert-danger {
        background: rgba(239, 68, 68, .15);
        color: #f87171;
    }

    html[data-pms-theme="dark"] .member-table thead th {
        color: #b7d5c4;
    }
</style>

@push('js')
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#memberTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search members..."
            }
        });

        // Custom search handler
        const searchInput = document.getElementById('memberSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                $('#memberTable').DataTable().search(this.value).draw();
            });
        }
    });

    // Toggle More Tabs
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-more');
        const moreTabs = document.getElementById('more-tabs');

        if (toggleBtn && moreTabs) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const isHidden = moreTabs.classList.contains('d-none');
                moreTabs.classList.toggle('d-none');
                this.innerHTML = isHidden ?
                    '<i class="fas fa-ellipsis-h"></i> Less <i class="fas fa-chevron-up"></i>' :
                    '<i class="fas fa-ellipsis-h"></i> More <i class="fas fa-chevron-down"></i>';
            });
        }
    });
</script>
@endpush
@endsection
