@php
    $isAdmin = in_array(strtolower((string) auth()->user()?->role), ['admin', 'manager', 'hr'], true);
    $projectModel = $project ?? $currentProject ?? null;
    $projectId = $projectModel?->id;
    $active = $activeTab ?? 'overview';

    $tabs = [
        'overview' => [
            'label' => 'Overview',
            'url' => $projectModel ? route('projects.show', $projectId) : '#',
            'icon' => 'fa-chart-pie',
        ],
        'members' => [
            'label' => 'Members',
            'url' => $projectModel ? route('project-members.index', $projectId) : '#',
            'icon' => 'fa-users',
            'count' => $projectModel?->users?->count(),
        ],
        'tasks' => [
            'label' => 'Tasks',
            'url' => $projectModel ? route('projects.tasks.index', $projectId) : '#',
            'icon' => 'fa-tasks',
            'count' => $projectModel?->tasks?->count(),
        ],
        'gantt' => [
            'label' => 'Gantt Chart',
            'url' => $projectModel ? route('projects.gantt', $projectId) : '#',
            'icon' => 'fa-chart-bar',
        ],
        'files' => [
            'label' => 'Files',
            'url' => $projectModel ? route('project-files.index', $projectId) : '#',
            'icon' => 'fa-folder-open',
            'count' => $projectModel?->files?->count(),
        ],
        'milestones' => [
            'label' => 'Milestones',
            'url' => $projectModel ? route('milestones.index', $projectId) : '#',
            'icon' => 'fa-flag-checkered',
            'count' => $projectModel?->milestones?->count(),
        ],
        'timesheet' => [
            'label' => 'Timesheet',
            'url' => $projectModel ? route('projects.timelogs.index', $projectId) : '#',
            'icon' => 'fa-clock',
        ],
        'expenses' => [
            'label' => 'Expenses',
            'url' => $projectModel ? route('expenses.index', $projectId) : '#',
            'icon' => 'fa-wallet',
        ],
        'notes' => [
            'label' => 'Notes',
            'url' => $projectModel ? route('projects.notes.index', $projectId) : '#',
            'icon' => 'fa-sticky-note',
        ],
        'discussions' => [
            'label' => 'Discussion',
            'url' => $projectModel ? route('projects.discussions.index', $projectId) : '#',
            'icon' => 'fa-comments',
        ],
        'burndown' => [
            'label' => 'Burndown',
            'url' => $projectModel ? route('projects.burndown', $projectId) : '#',
            'icon' => 'fa-fire',
        ],
        'tickets' => [
            'label' => 'Tickets',
            'url' => $projectModel ? route('tickets.index', ['project_id' => $projectId]) : route('tickets.index'),
            'icon' => 'fa-ticket-alt',
        ],
    ];

    $statusOptions = [
        'pending' => 'Pending',
        'not started' => 'Not Started',
        'in progress' => 'In Progress',
        'on hold' => 'On Hold',
        'completed' => 'Completed',
        'delayed' => 'Delayed',
    ];
    $statusKey = strtolower($projectModel?->status ?: 'pending');
@endphp

<div class="pms-project-header-container mb-4">
    {{-- Top Hero Bar --}}
    <div class="pms-project-hero-card">
        <div class="hero-left-content">
            <div class="eyebrow-tag">
                <i class="fas fa-layer-group"></i>
                <span>ASSIGNED PROJECT</span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap mt-1">
                <h1 class="project-heading mb-0">{{ $projectModel?->name ?? 'Project Details' }}</h1>
                @if($projectModel?->project_code)
                    <span class="project-code-tag">{{ $projectModel->project_code }}</span>
                @endif
                <span class="project-status-badge status-{{ str_replace(' ', '-', $statusKey) }}">
                    <span class="status-dot"></span>
                    {{ $statusOptions[$statusKey] ?? ucfirst($statusKey) }}
                </span>
            </div>
        </div>

        <div class="hero-right-actions">
            <a href="{{ route('projects.index') }}" class="btn-back-projects">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Projects</span>
            </a>
        </div>
    </div>

    {{-- Single-Row 13-Tab Navigation Bar --}}
    <div class="pms-project-nav-wrapper">
        <nav class="pms-project-nav-bar" id="pmsProjectNavBar">
            @foreach($tabs as $key => $tab)
                @php
                    $isActive = ($active === $key) || ($key === 'discussions' && $active === 'discussion') || ($key === 'timesheet' && $active === 'timelogs');
                @endphp
                <a href="{{ $tab['url'] }}"
                   class="pms-nav-tab {{ $isActive ? 'active' : '' }}"
                   data-tab="{{ $key }}">
                    <i class="fas {{ $tab['icon'] }} tab-icon"></i>
                    <span class="tab-label">{{ $tab['label'] }}</span>
                    @if(!empty($tab['count']))
                        <span class="tab-badge">{{ $tab['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>
</div>

<style>
    /* ===== UNIFIED PROJECT HEADER & 13-TAB NAV STYLING ===== */
    .pms-project-header-container {
        margin-bottom: 24px;
    }

    /* Hero Top Section */
    .pms-project-hero-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }

    .eyebrow-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #0f766e;
        margin-bottom: 2px;
    }

    .project-heading {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
    }

    .project-code-tag {
        background: #f1f5f9;
        color: #475569;
        font-family: monospace;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .project-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .project-status-badge .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .project-status-badge.status-completed { background: #dcfce7; color: #15803d; }
    .project-status-badge.status-in-progress { background: #e0f2fe; color: #0369a1; }
    .project-status-badge.status-pending { background: #fef3c7; color: #b45309; }
    .project-status-badge.status-on-hold { background: #f3e8ff; color: #7e22ce; }
    .project-status-badge.status-delayed { background: #fee2e2; color: #b91c1c; }

    .btn-back-projects {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #334155;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        white-space: nowrap;
    }

    .btn-back-projects:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    /* 13-Tab Navigation Bar */
    .pms-project-nav-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 12px 12px;
        padding: 8px 14px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .pms-project-nav-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        overflow-x: auto;
        flex-wrap: nowrap;
        white-space: nowrap;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
        padding: 4px 2px 8px;
    }

    .pms-project-nav-bar::-webkit-scrollbar {
        height: 6px;
    }

    .pms-project-nav-bar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .pms-project-nav-bar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .pms-project-nav-bar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .pms-nav-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 14px;
        border-radius: 8px;
        color: #475569;
        font-size: 0.86rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    .pms-nav-tab .tab-icon {
        font-size: 0.88rem;
        color: #64748b;
        transition: color 0.15s ease;
    }

    .pms-nav-tab:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .pms-nav-tab:hover .tab-icon {
        color: #0f766e;
    }

    /* Active Green/Teal Pill Highlight */
    .pms-nav-tab.active {
        background: #0f766e !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(15, 118, 110, 0.25);
    }

    .pms-nav-tab.active .tab-icon {
        color: #ffffff !important;
    }

    .tab-badge {
        background: rgba(0, 0, 0, 0.08);
        color: inherit;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 999px;
    }

    .pms-nav-tab.active .tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pms-project-hero-card {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px;
        }
        .hero-right-actions {
            width: 100%;
        }
        .btn-back-projects {
            width: 100%;
            justify-content: center;
        }
        .pms-project-nav-wrapper {
            padding: 6px 8px;
        }
        .pms-nav-tab {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    }
</style>
