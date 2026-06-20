@extends('admin.layout.app')

@section('title', 'Project Files - ' . $project->name)

@section('content')
<div class="project-files-page">
    <div class="container-fluid px-4">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <i class="fas fa-folder-open"></i>
            <span>Dashboard / Projects / <a href="{{ route('projects.show', $project->id) }}">{{ $project->name }}</a> / Files</span>
        </div>

        <!-- Header Card -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <h1>Project Files</h1>
                    <p>Manage all files and documents for <strong>{{ $project->name }}</strong></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file"></i></div>
                <div>
                    <h3>{{ $files->count() }}</h3>
                    <span>Total Files</span>
                    <p class="stat-sub">Uploaded documents</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-pdf"></i></div>
                <div>
                    <h3>{{ $files->where('mime_type', 'application/pdf')->count() }}</h3>
                    <span>PDF Files</span>
                    <p class="stat-sub">Documents</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-image"></i></div>
                <div>
                    <h3>{{ $files->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/gif'])->count() }}</h3>
                    <span>Images</span>
                    <p class="stat-sub">Visual assets</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-archive"></i></div>
                <div>
                    <h3>{{ $files->whereIn('mime_type', ['application/zip', 'application/x-rar-compressed'])->count() }}</h3>
                    <span>Archives</span>
                    <p class="stat-sub">Compressed files</p>
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
                    <a class="nav-link" href="{{ route('project-members.index', $project->id) }}">
                        <i class="fas fa-users"></i> Members
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('project-files.index', $project->id) }}">
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

        <!-- Main Content -->
        <div class="content-card">
            <!-- Upload Section -->
            <div class="upload-section">
                <div class="upload-header">
                    <div class="upload-title">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div>
                            <h4>Upload File</h4>
                            <p class="muted">Upload new files to the project repository</p>
                        </div>
                    </div>
                </div>
                <form action="{{ route('project-files.store', $project->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-form">
                        <div class="file-input-wrapper">
                            <input type="file" name="file" class="file-input" id="fileInput" required>
                            <label for="fileInput" class="file-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose file or drag & drop</span>
                                <small>Supported: PDF, DOC, XLS, PNG, JPG, ZIP</small>
                            </label>
                            <span class="file-name" id="fileName">No file selected</span>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    </div>
                </form>
            </div>

            <!-- Files List -->
            <div class="files-section">
                <div class="files-header">
                    <div class="files-title">
                        <i class="fas fa-list"></i>
                        <div>
                            <h4>Uploaded Files</h4>
                            <span class="muted">{{ $files->count() }} files in repository</span>
                        </div>
                    </div>
                    <div class="files-actions">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="fileSearch" placeholder="Search files..." />
                        </div>
                    </div>
                </div>

                <div class="files-grid">
                    @forelse($files as $file)
                    <div class="file-card" data-filename="{{ strtolower($file->filename) }}">
                        <div class="file-icon">
                            @php
                                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                                $icon = 'fa-file';
                                $color = '#6b7280';
                                if(in_array($ext, ['pdf'])) { $icon = 'fa-file-pdf'; $color = '#dc2626'; }
                                elseif(in_array($ext, ['doc', 'docx'])) { $icon = 'fa-file-word'; $color = '#2563eb'; }
                                elseif(in_array($ext, ['xls', 'xlsx'])) { $icon = 'fa-file-excel'; $color = '#16a34a'; }
                                elseif(in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg'])) { $icon = 'fa-file-image'; $color = '#8b5cf6'; }
                                elseif(in_array($ext, ['zip', 'rar', '7z'])) { $icon = 'fa-file-archive'; $color = '#f59e0b'; }
                                elseif(in_array($ext, ['txt'])) { $icon = 'fa-file-alt'; $color = '#6b7280'; }
                            @endphp
                            <i class="fas {{ $icon }}" style="color: {{ $color }};"></i>
                        </div>
                        <div class="file-info">
                            <div class="file-name">
                                <a href="{{ asset($file->file_path) }}" target="_blank">{{ $file->filename }}</a>
                            </div>
                            <div class="file-meta">
                                <span><i class="fas fa-calendar-alt"></i> {{ $file->created_at->format('M d, Y') }}</span>
                                <span><i class="fas fa-user"></i> {{ $file->uploadedBy->name ?? 'System' }}</span>
                                <span><i class="fas fa-file"></i> {{ strtoupper($ext) }}</span>
                            </div>
                        </div>
                        <div class="file-actions">
                            <a href="{{ asset($file->file_path) }}" target="_blank" class="btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('project-files.destroy', [$project->id, $file->id]) }}" method="POST" onsubmit="return confirm('Delete this file?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h5>No Files Uploaded</h5>
                        <p>Upload your first file to get started</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Footer Status -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-file"></i> <span>{{ $files->count() }}</span> Total Files
            </div>
            <div class="status-item">
                <i class="fas fa-cloud-upload-alt"></i> Last upload: {{ $files->isNotEmpty() ? $files->first()->created_at->diffForHumans() : 'None' }}
            </div>
            <div class="status-item">
                <i class="fas fa-hdd"></i> Storage: {{ number_format($files->sum('size') / 1024, 2) }} KB
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ===== PROJECT FILES PAGE - GREEN/TEAL THEME ===== */
    .project-files-page {
        padding: 30px 0;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #07130d;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 22px;
        border-radius: 18px;
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 25px;
        color: #0f744c;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .breadcrumb i {
        margin-right: 10px;
        color: #34d399;
    }

    .breadcrumb a {
        color: #0f744c;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb a:hover {
        color: #10b981;
    }

    /* Header Card */
    .header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 65px;
        height: 65px;
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .2);
    }

    .header-card h1 {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #07130d;
    }

    .header-card p {
        color: #52645a;
        font-size: 15px;
        margin: 0;
    }

    .header-card p strong {
        color: #0f744c;
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        border: none;
        padding: 12px 22px;
        border-radius: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
        text-decoration: none;
        font-size: 0.9rem;
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
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #5a6e63;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link i {
        font-size: 0.9rem;
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
    }

    /* Upload Section */
    .upload-section {
        padding: 24px 28px;
        border-bottom: 1px solid rgba(15, 116, 76, .08);
        background: linear-gradient(135deg, #fafefb, #f0f9f4);
    }

    .upload-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
    }

    .upload-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .upload-title i {
        font-size: 1.8rem;
        color: #0f744c;
        background: #d1fae5;
        padding: 12px;
        border-radius: 14px;
    }

    .upload-title h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .upload-title .muted {
        font-size: 0.8rem;
        color: #8ba198;
        margin: 0;
    }

    .upload-form {
        display: flex;
        gap: 16px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .file-input-wrapper {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .file-input {
        position: absolute;
        opacity: 0;
        width: 0.1px;
        height: 0.1px;
    }

    .file-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px;
        border: 2px dashed rgba(15, 116, 76, .2);
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        text-align: center;
    }

    .file-label:hover {
        border-color: #34d399;
        background: #fafefb;
    }

    .file-label i {
        font-size: 2rem;
        color: #0f744c;
        margin-bottom: 8px;
    }

    .file-label span {
        font-weight: 600;
        color: #07130d;
    }

    .file-label small {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    .file-name {
        display: block;
        margin-top: 8px;
        font-size: 0.85rem;
        color: #0f744c;
        font-weight: 500;
    }

    /* Files Section */
    .files-section {
        padding: 24px 28px;
    }

    .files-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }

    .files-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .files-title i {
        font-size: 1.4rem;
        color: #0f744c;
        background: #d1fae5;
        padding: 10px;
        border-radius: 12px;
    }

    .files-title h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .files-title .muted {
        font-size: 0.8rem;
        color: #8ba198;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding: 10px 16px 10px 40px;
        border-radius: 40px;
        border: 1px solid rgba(15, 116, 76, .18);
        outline: none;
        font-weight: 500;
        min-width: 250px;
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
    }

    /* Files Grid */
    .files-grid {
        display: grid;
        gap: 12px;
    }

    .file-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background: #fafefb;
        border-radius: 16px;
        border: 1px solid rgba(15, 116, 76, .08);
        transition: all 0.2s ease;
    }

    .file-card:hover {
        background: #ffffff;
        border-color: rgba(15, 116, 76, .2);
        box-shadow: 0 4px 15px rgba(15, 116, 76, .08);
        transform: translateX(4px);
    }

    .file-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #f0f9f4;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .file-info {
        flex: 1;
        min-width: 0;
    }

    .file-name {
        font-weight: 600;
        color: #07130d;
        margin-bottom: 4px;
    }

    .file-name a {
        color: #07130d;
        text-decoration: none;
        transition: color 0.2s;
    }

    .file-name a:hover {
        color: #0f744c;
    }

    .file-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 0.75rem;
        color: #8ba198;
    }

    .file-meta i {
        margin-right: 4px;
        color: #0f744c;
    }

    .file-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        background: #f0f9f4;
        color: #0f744c;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-icon:hover {
        background: #d1fae5;
        transform: scale(1.05);
    }

    .btn-icon.delete:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
    }

    .empty-state i {
        font-size: 3.5rem;
        color: #a7f3d0;
        margin-bottom: 16px;
    }

    .empty-state h5 {
        color: #0f744c;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #8ba198;
    }

    /* Status Bar */
    .status-bar {
        margin-top: 24px;
        background: white;
        border-radius: 20px;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid rgba(15, 116, 76, .1);
        box-shadow: 0 8px 25px rgba(15, 116, 76, .04);
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #5a6e63;
    }

    .status-item i {
        color: #0f744c;
        font-size: 1rem;
    }

    .status-item span {
        font-weight: 700;
        color: #07130d;
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
        .project-files-page {
            padding: 16px 0;
        }
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .upload-form {
            flex-direction: column;
            align-items: stretch;
        }
        .file-card {
            flex-wrap: wrap;
        }
        .file-actions {
            width: 100%;
            justify-content: flex-end;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .search-box input {
            min-width: 180px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle More Tabs
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

        // File input label update
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');

        if (fileInput && fileName) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                } else {
                    fileName.textContent = 'No file selected';
                }
            });
        }

        // File search filtering
        const searchInput = document.getElementById('fileSearch');
        const fileCards = document.querySelectorAll('.file-card');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = this.value.toLowerCase().trim();
                fileCards.forEach(card => {
                    const filename = card.dataset.filename || '';
                    if (filename.includes(query)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
