@extends('admin.layout.app')

@section('title', 'Add Member - ' . $project->name)

@section('content')
<div class="project-members-add-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-user-plus"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / <strong>Add Member</strong></span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1>Add Member to Project</h1>
                    <p>Assign a new team member to <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
                <a href="{{ route('project-members.index', $project->id) }}" class="btn btn-outline">
                    <i class="fas fa-users"></i> View Members
                </a>
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

        <!-- Main Content Card -->
        <div class="content-card">
            <div class="form-header">
                <div class="form-title">
                    <div class="form-title-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h4>Add New Member</h4>
                        <span class="muted">Assign a team member to this project</span>
                    </div>
                </div>
                <div class="form-helper">
                    <i class="fas fa-info-circle"></i>
                    <span>Fields marked with <span class="text-danger">*</span> are required</span>
                </div>
            </div>

            <form action="{{ route('project-members.store', $project->id) }}" method="POST" class="member-form">
                @csrf

                <div class="form-body">
                    <!-- Project Info Card -->
                    <div class="project-info-card">
                        <div class="project-info-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="project-info-details">
                            <div class="project-info-label">Project</div>
                            <div class="project-info-name">{{ $project->name }}</div>
                            <div class="project-info-meta">
                                <span><i class="fas fa-code"></i> {{ $project->project_code ?: 'N/A' }}</span>
                                <span><i class="fas fa-calendar-alt"></i> {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'Not set' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- User Selection -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Select User <span class="text-danger">*</span>
                            </label>
                            <select name="user_id" class="form-select" required>
                                <option value="">— Select a user —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->employeeDetail?->employee_id ? '(' . $user->employeeDetail->employee_id . ')' : '' }}</option>
                                @endforeach
                            </select>
                            <small class="form-hint">Choose a user to assign to this project</small>
                        </div>

                        <!-- Role Selection -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-badge"></i> Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="">— Select a role —</option>
                                <option value="Project Member" {{ old('role') == 'Project Member' ? 'selected' : '' }}>Project Member</option>
                                <option value="Project Admin" {{ old('role') == 'Project Admin' ? 'selected' : '' }}>Project Admin</option>
                            </select>
                            <small class="form-hint">Admins have full access, Members have limited permissions</small>
                        </div>

                        <!-- Hourly Rate -->
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-dollar-sign"></i> Hourly Rate
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                <input type="number" name="hourly_rate" class="form-control" step="0.01" placeholder="0.00" value="{{ old('hourly_rate') }}">
                            </div>
                            <small class="form-hint">Optional: Set a specific hourly rate for this member on this project</small>
                        </div>
                    </div>
                </div>

                <div class="form-footer">
                    <div class="footer-left">
                        <i class="fas fa-shield-alt"></i>
                        <span>All member assignments are tracked and logged</span>
                    </div>
                    <div class="footer-right">
                        <a href="{{ route('project-members.index', $project->id) }}" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Add Member
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Current Members Preview -->
        <div class="preview-card">
            <div class="preview-header">
                <div class="preview-title">
                    <i class="fas fa-users"></i>
                    <div>
                        <h5>Current Team Members</h5>
                        <span class="muted">Recently added members</span>
                    </div>
                </div>
                <a href="{{ route('project-members.index', $project->id) }}" class="btn btn-sm btn-outline">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="preview-members">
                @php
                    $recentMembers = $project->users->take(5);
                @endphp
                @forelse($recentMembers as $member)
                    <div class="preview-member">
                        <div class="member-avatar">
                            @if($member->profile_image)
                                <img src="{{ asset($member->profile_image) }}" alt="{{ $member->name }}">
                            @else
                                {{ strtoupper(mb_substr($member->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="member-info">
                            <div class="member-name">{{ $member->name }}</div>
                            <div class="member-role">{{ $member->pivot->role ?? 'Member' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="preview-empty">
                        <i class="fas fa-users-slash"></i>
                        <span>No members added yet</span>
                    </div>
                @endforelse
                @if($recentMembers->count() > 0 && $project->users->count() > 5)
                    <div class="preview-more">
                        +{{ $project->users->count() - 5 }} more
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-users text-primary"></i>
                <span>{{ $project->users->count() }}</span> Total Members
            </div>
            <div class="status-item">
                <i class="fas fa-user-shield text-success"></i>
                <span>{{ $project->users->filter(fn($u) => ($u->pivot->role ?? '') === 'Project Admin')->count() }}</span> Admins
            </div>
            <div class="status-item">
                <i class="fas fa-user text-info"></i>
                <span>{{ $project->users->filter(fn($u) => ($u->pivot->role ?? '') === 'Project Member')->count() }}</span> Members
            </div>
            <div class="status-item">
                <i class="fas fa-clock text-warning"></i>
                Last updated: {{ $project->updated_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM PROJECT MEMBERS ADD PAGE - GREEN/TEAL THEME ===== */
    .project-members-add-page {
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

    .btn-sm {
        padding: 8px 16px;
        min-height: 38px;
        font-size: 0.9rem;
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

    /* Content Card */
    .content-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
        overflow: hidden;
        margin-bottom: 28px;
    }

    /* Form Header */
    .form-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .form-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .form-title-icon {
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

    .form-title h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .form-title .muted {
        font-size: 0.95rem;
        color: #8ba198;
        display: block;
        margin-top: 2px;
    }

    .form-helper {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #5a6e63;
        background: #f0f9f4;
        padding: 10px 18px;
        border-radius: 12px;
    }

    .form-helper i {
        color: #0f744c;
        font-size: 1.1rem;
    }

    .text-danger {
        color: #dc2626;
    }

    /* Form Body */
    .form-body {
        padding: 28px;
    }

    /* Project Info Card */
    .project-info-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 24px;
        background: #f0f9f4;
        border-radius: 16px;
        border: 1px solid rgba(15, 116, 76, .1);
        margin-bottom: 28px;
    }

    .project-info-icon {
        width: 56px;
        height: 56px;
        background: #d1fae5;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #0f744c;
    }

    .project-info-details {
        flex: 1;
    }

    .project-info-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #5a6e63;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .project-info-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #07130d;
    }

    .project-info-meta {
        display: flex;
        gap: 20px;
        margin-top: 6px;
        font-size: 0.85rem;
        color: #5a6e63;
    }

    .project-info-meta i {
        color: #0f744c;
        margin-right: 6px;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.95rem;
        color: #07130d;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: #0f744c;
        font-size: 0.95rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .18);
        padding: 12px 16px;
        font-weight: 500;
        font-size: 1rem;
        min-height: 48px;
        transition: all 0.2s ease;
        background: #fafefb;
        width: 100%;
    }

    .form-control:focus, .form-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .1);
    }

    .input-group {
        display: flex;
        align-items: stretch;
    }

    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0 16px;
        background: #f0f9f4;
        border: 1px solid rgba(15, 116, 76, .18);
        border-radius: 12px 0 0 12px;
        color: #0f744c;
        font-size: 1rem;
    }

    .input-group .form-control {
        border-radius: 0 12px 12px 0;
        border-left: none;
    }

    .form-hint {
        font-size: 0.85rem;
        color: #8ba198;
        margin-top: 4px;
    }

    /* Form Footer */
    .form-footer {
        padding: 20px 28px;
        background: #fafefb;
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .footer-left {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.9rem;
        color: #5a6e63;
    }

    .footer-left i {
        color: #0f744c;
        font-size: 1.1rem;
    }

    .footer-right {
        display: flex;
        gap: 12px;
    }

    /* Preview Card */
    .preview-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
        overflow: hidden;
        margin-bottom: 28px;
    }

    .preview-header {
        padding: 18px 24px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        border-bottom: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .preview-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .preview-title i {
        font-size: 1.4rem;
        color: #0f744c;
    }

    .preview-title h5 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        color: #07130d;
    }

    .preview-title .muted {
        font-size: 0.85rem;
        color: #8ba198;
        display: block;
    }

    .preview-members {
        padding: 20px 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
    }

    .preview-member {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        background: #f0f9f4;
        border-radius: 12px;
        border: 1px solid rgba(15, 116, 76, .08);
        min-width: 160px;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #d1fae5;
        color: #0f744c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        overflow: hidden;
    }

    .member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #07130d;
        font-size: 0.95rem;
    }

    .member-role {
        font-size: 0.75rem;
        color: #8ba198;
    }

    .preview-empty {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #8ba198;
        font-size: 0.95rem;
        padding: 12px 0;
    }

    .preview-empty i {
        font-size: 1.4rem;
        color: #a7f3d0;
    }

    .preview-more {
        padding: 10px 18px;
        background: #f0f9f4;
        border-radius: 12px;
        font-weight: 600;
        color: #0f744c;
        font-size: 0.9rem;
        border: 1px dashed rgba(15, 116, 76, .2);
    }

    /* Status Bar */
    .status-bar {
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
    @media (max-width: 992px) {
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .header-actions {
            width: 100%;
        }
        .form-grid {
            grid-template-columns: 1fr;
        }
        .form-group.full-width {
            grid-column: 1;
        }
        .nav-tabs {
            flex-wrap: wrap;
        }
        .nav-link {
            padding: 8px 14px;
            font-size: 0.8rem;
        }
        .project-info-card {
            flex-direction: column;
            text-align: center;
        }
        .project-info-meta {
            justify-content: center;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 768px) {
        .project-members-add-page {
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
        .form-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .form-body {
            padding: 20px;
        }
        .form-footer {
            flex-direction: column;
            align-items: stretch;
        }
        .footer-right {
            justify-content: stretch;
        }
        .footer-right .btn {
            flex: 1;
            justify-content: center;
        }
        .preview-members {
            padding: 16px;
            flex-direction: column;
            align-items: stretch;
        }
        .preview-member {
            width: 100%;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .form-helper {
            width: 100%;
            justify-content: center;
        }
        .project-info-meta {
            flex-direction: column;
            gap: 6px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .project-members-add-page {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .content-card,
    html[data-pms-theme="dark"] .preview-card,
    html[data-pms-theme="dark"] .status-bar,
    html[data-pms-theme="dark"] .nav-tabs-wrapper {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-pms-theme="dark"] .form-title h4,
    html[data-pms-theme="dark"] .project-info-name,
    html[data-pms-theme="dark"] .member-name {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .form-control,
    html[data-pms-theme="dark"] .form-select {
        background: #183026;
        color: #ffffff;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .project-info-card,
    html[data-pms-theme="dark"] .preview-member,
    html[data-pms-theme="dark"] .form-helper {
        background: #183026;
        border-color: rgba(122, 240, 181, .1);
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

    html[data-pms-theme="dark"] .form-header,
    html[data-pms-theme="dark"] .preview-header {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .form-footer {
        background: #142a20;
        border-color: rgba(122, 240, 181, .16);
    }

    html[data-pms-theme="dark"] .input-group-text {
        background: #183026;
        border-color: rgba(122, 240, 181, .18);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .member-avatar {
        background: rgba(122, 240, 181, .15);
        color: #7af0b5;
    }
</style>

@push('scripts')
<script>
    document.getElementById('toggle-more').addEventListener('click', function(e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        if (moreTabs.classList.contains('d-none')) {
            moreTabs.classList.remove('d-none');
            this.innerHTML = '<i class="fas fa-ellipsis-h"></i> Less <i class="fas fa-chevron-up"></i>';
        } else {
            moreTabs.classList.add('d-none');
            this.innerHTML = '<i class="fas fa-ellipsis-h"></i> More <i class="fas fa-chevron-down"></i>';
        }
    });
</script>
@endpush
@endsection
