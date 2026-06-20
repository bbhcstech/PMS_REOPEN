@extends('admin.layout.app')

@section('title', 'Archived Projects')

@section('content')
<div class="archived-projects-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-archive"></i>
            <span>Dashboard / Projects / <strong>Archived</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <div>
                    <h1>Archived Projects</h1>
                    <p>View and manage archived projects</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                <a href="{{ route('projects.calendar') }}" class="btn btn-outline" title="Calendar">
                    <i class="fas fa-calendar-alt"></i> Calendar
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-archive"></i></div>
                <div>
                    <h3>{{ $projects->count() }}</h3>
                    <span>Archived Projects</span>
                    <p class="stat-sub">Total archived</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3>{{ $projects->where('status', 'completed')->count() }}</h3>
                    <span>Completed</span>
                    <p class="stat-sub">Finished projects</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>{{ $projects->where('status', '!=', 'completed')->count() }}</h3>
                    <span>Incomplete</span>
                    <p class="stat-sub">Not finished</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h3>{{ $projects->where('updated_at', '>=', now()->subDays(30))->count() }}</h3>
                    <span>Recent</span>
                    <p class="stat-sub">Last 30 days</p>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="content-card">
            <div class="table-header">
                <div class="table-title">
                    <div class="table-title-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <h4>Archived Projects List</h4>
                        <span class="muted">{{ $projects->count() }} archived project(s)</span>
                    </div>
                </div>
                <div class="table-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="archiveSearch" placeholder="Search archived projects..." />
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="archive-projects-table" class="archive-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Project Name</th>
                            <th><i class="fas fa-users"></i> Members</th>
                            <th><i class="fas fa-calendar-times"></i> Deadline</th>
                            <th><i class="fas fa-building"></i> Client</th>
                            <th><i class="fas fa-chart-simple"></i> Completion</th>
                            <th><i class="fas fa-circle"></i> Status</th>
                            <th class="action-cell"><i class="fas fa-cog"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>
                                    <div class="project-name-cell">
                                        <div class="project-icon">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <div>
                                            <div class="project-name">{{ $project->name }}</div>
                                            <small class="project-code">{{ $project->project_code ?: 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="members-cell">
                                        @forelse($project->users->take(3) as $user)
                                            <div class="member-avatar" title="{{ $user->name }}">
                                                @if($user->profile_image)
                                                    <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}">
                                                @else
                                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                                @endif
                                            </div>
                                        @empty
                                            <span class="empty-text">No members</span>
                                        @endforelse
                                        @if($project->users->count() > 3)
                                            <div class="member-avatar more">+{{ $project->users->count() - 3 }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : '--' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="client-cell">
                                        <div class="client-avatar">
                                            {{ strtoupper(mb_substr($project->client->name ?? 'N', 0, 1)) }}
                                        </div>
                                        <span>{{ $project->client->name ?? 'No Client' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress-cell">
                                        <div class="progress-bar">
                                            <div style="width: {{ min(100, $project->completion_percent ?? 0) }}%"></div>
                                        </div>
                                        <span class="progress-text">{{ $project->completion_percent ?? 0 }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $status = $project->status ?: 'not started';
                                        $statusClass = str_replace(' ', '-', strtolower($status));
                                        $statusLabels = [
                                            'not started' => 'Not Started',
                                            'in progress' => 'In Progress',
                                            'on hold' => 'On Hold',
                                            'completed' => 'Completed'
                                        ];
                                    @endphp
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statusLabels[$status] ?? ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <div class="action-group">
                                        <!-- Restore Form -->
                                        <form action="{{ route('projects.restore', $project->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="action-btn restore-btn" onclick="return confirm('Restore this project?')" title="Restore Project">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        </form>

                                        <!-- Permanent Delete Form -->
                                        <form action="{{ route('projects.forceDelete', $project->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Permanently delete this project? This action cannot be undone.')" title="Delete Permanently">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-archive"></i>
                                        <h5>No Archived Projects</h5>
                                        <p>There are no archived projects at the moment.</p>
                                        <a href="{{ route('projects.index') }}" class="btn btn-primary">
                                            <i class="fas fa-arrow-left"></i> Go to Projects
                                        </a>
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
                    Showing {{ $projects->count() }} archived project(s)
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
                <i class="fas fa-archive text-primary"></i>
                <span>{{ $projects->count() }}</span> Archived
            </div>
            <div class="status-item">
                <i class="fas fa-check-circle text-success"></i>
                <span>{{ $projects->where('status', 'completed')->count() }}</span> Completed
            </div>
            <div class="status-item">
                <i class="fas fa-clock text-warning"></i>
                <span>{{ $projects->where('status', '!=', 'completed')->count() }}</span> Incomplete
            </div>
            <div class="status-item">
                <i class="fas fa-calendar-alt text-info"></i>
                Last archive: {{ $projects->isNotEmpty() ? $projects->first()->updated_at->diffForHumans() : 'Never' }}
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM ARCHIVED PROJECTS PAGE - GREEN/TEAL THEME ===== */
    .archived-projects-page {
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

    .archive-table {
        width: 100%;
        min-width: 950px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .archive-table thead th {
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

    .archive-table thead th i {
        margin-right: 8px;
        color: #34d399;
        font-size: 0.9rem;
    }

    .archive-table tbody td {
        background: #ffffff;
        padding: 16px 18px;
        border-top: 1px solid rgba(15, 116, 76, .06);
        border-bottom: 1px solid rgba(15, 116, 76, .06);
        vertical-align: middle;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .archive-table tbody td:first-child {
        border-left: 1px solid rgba(15, 116, 76, .06);
        border-radius: 14px 0 0 14px;
    }

    .archive-table tbody td:last-child {
        border-right: 1px solid rgba(15, 116, 76, .06);
        border-radius: 0 14px 14px 0;
    }

    .archive-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(15, 116, 76, .12);
    }

    .action-cell {
        text-align: center;
        width: 120px;
    }

    /* Project Name Cell */
    .project-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .project-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #d1fae5;
        color: #0f744c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .project-name {
        font-weight: 600;
        color: #07130d;
        font-size: 0.95rem;
    }

    .project-code {
        font-size: 0.75rem;
        color: #8ba198;
        display: block;
    }

    /* Members Cell */
    .members-cell {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
    }

    .member-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 2px solid #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #d1fae5;
        color: #0f744c;
        font-weight: 700;
        font-size: 0.75rem;
        overflow: hidden;
    }

    .member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-avatar.more {
        background: #0f744c;
        color: white;
        font-size: 0.7rem;
    }

    .empty-text {
        color: #8ba198;
        font-size: 0.85rem;
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

    /* Client Cell */
    .client-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .client-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #d1fae5;
        color: #0f744c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .client-cell span {
        font-weight: 500;
        color: #07130d;
        font-size: 0.9rem;
    }

    /* Progress Cell */
    .progress-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 100px;
    }

    .progress-bar {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
        min-width: 60px;
    }

    .progress-bar div {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #34d399, #10b981);
        transition: width 0.6s ease;
    }

    .progress-text {
        font-weight: 700;
        font-size: 0.85rem;
        color: #07130d;
        min-width: 40px;
    }

    /* Status Pill */
    .status-pill {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 700;
        color: white;
    }

    .status-pill.not-started { background: #64748b; }
    .status-pill.in-progress { background: #3b82f6; }
    .status-pill.on-hold { background: #f59e0b; }
    .status-pill.completed { background: #10b981; }

    /* Action Group */
    .action-group {
        display: flex;
        gap: 6px;
        justify-content: center;
    }

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
        transform: scale(1.05);
    }

    .action-btn.restore-btn:hover {
        background: #d1fae5;
        color: #059669;
    }

    .action-btn.delete-btn {
        background: #fee2e2;
        color: #dc2626;
    }

    .action-btn.delete-btn:hover {
        background: #fecaca;
        color: #b91c1c;
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
    }

    @media (max-width: 768px) {
        .archived-projects-page {
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
        .archive-table {
            min-width: 800px;
        }
        .archive-table thead th {
            font-size: 0.75rem;
            padding: 12px 12px;
        }
        .archive-table tbody td {
            font-size: 0.85rem;
            padding: 12px 12px;
        }
        .action-cell {
            width: 90px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .archived-projects-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .status-bar {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .table-title h4,
    html[data-pms-theme="dark"] .project-name,
    html[data-pms-theme="dark"] .stat-card h3,
    html[data-pms-theme="dark"] .client-cell span {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .archive-table tbody td {
        background: #102119;
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .archive-table tbody tr:hover td {
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

    html[data-pms-theme="dark"] .project-icon {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .member-avatar {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .client-avatar {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .action-btn {
        background: #183026;
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .action-btn.delete-btn {
        background: rgba(239, 68, 68, .15);
        color: #f87171;
    }

    html[data-pms-theme="dark"] .action-btn.delete-btn:hover {
        background: rgba(239, 68, 68, .25);
    }

    html[data-pms-theme="dark"] .archive-table thead th {
        color: #b7d5c4;
    }

    html[data-pms-theme="dark"] .progress-bar {
        background: #183026;
    }
</style>

@push('scripts')
<script>
$(document).ready(function () {
    // Initialize DataTable
    $('#archive-projects-table').DataTable({
        dom: 'Bfrtip',
        buttons: ['excel'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search archived projects..."
        }
    });

    // Custom search handler
    const searchInput = document.getElementById('archiveSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            $('#archive-projects-table').DataTable().search(this.value).draw();
        });
    }
});
</script>
@endpush
@endsection
