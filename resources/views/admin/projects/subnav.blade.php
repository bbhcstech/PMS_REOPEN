@php
    $tabs = [
        'overview' => ['label' => 'Overview', 'route' => 'admin.projects.show', 'icon' => 'fa-chart-pie'],
        'members' => ['label' => 'Members', 'route' => 'admin.project-members.index', 'icon' => 'fa-users'],
        'files' => ['label' => 'Files', 'route' => 'admin.project-files.index', 'icon' => 'fa-folder-open'],
        'milestones' => ['label' => 'Milestones', 'route' => 'admin.milestones.index', 'icon' => 'fa-flag-checkered'],
        'tasks' => ['label' => 'Tasks', 'route' => 'admin.projects.tasks.index', 'icon' => 'fa-tasks'],
        'board' => ['label' => 'Task Board', 'route' => 'admin.projects.tasks.board', 'icon' => 'fa-columns'],
        'gantt' => ['label' => 'Gantt Chart', 'route' => 'admin.projects.gantt', 'icon' => 'fa-chart-bar'],
        'timesheet' => ['label' => 'Timesheet', 'route' => 'admin.projects.timelogs.index', 'icon' => 'fa-clock'],
        'expenses' => ['label' => 'Expenses', 'route' => 'admin.expenses.index', 'icon' => 'fa-money-bill-wave'],
        'notes' => ['label' => 'Notes', 'route' => 'admin.projects.notes.index', 'icon' => 'fa-sticky-note'],
        'discussion' => ['label' => 'Discussion', 'route' => 'admin.projects.discussions.index', 'icon' => 'fa-comments'],
        'burndown' => ['label' => 'Burndown Chart', 'route' => 'admin.projects.burndown', 'icon' => 'fa-fire'],
        'activity' => ['label' => 'Activity', 'route' => 'admin.activities.project', 'icon' => 'fa-history'],
        'tickets' => ['label' => 'Tickets', 'route' => 'admin.tickets.index', 'icon' => 'fa-ticket-alt'],
    ];
@endphp

<style>
    /* ===== PREMIUM NAV TABS - GREEN/TEAL THEME ===== */
    .nav-tabs-wrapper {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, .12);
        overflow: hidden;
        margin-bottom: 28px;
        box-shadow: 0 8px 25px rgba(15, 116, 76, .06);
        transition: all 0.3s ease;
    }

    .nav-tabs-wrapper:hover {
        box-shadow: 0 12px 35px rgba(15, 116, 76, .1);
        border-color: rgba(15, 116, 76, .18);
    }

    .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 10px 16px;
        margin: 0;
        list-style: none;
        background: linear-gradient(135deg, #ffffff 0%, #f5fbf7 100%);
        border-bottom: 1px solid rgba(15, 116, 76, .08);
    }

    .nav-tabs.extra-tabs {
        border-top: 1px solid rgba(15, 116, 76, .08);
        border-bottom: none;
        padding-top: 10px;
        background: linear-gradient(135deg, #fafefb 0%, #f5fbf7 100%);
    }

    .nav-item {
        margin: 0;
        position: relative;
    }

    .nav-link {
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #5a6e63;
        text-decoration: none;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
        background: transparent;
        position: relative;
        min-height: 44px;
    }

    .nav-link i {
        font-size: 1rem;
        color: #8ba198;
        transition: all 0.25s ease;
        width: 18px;
        text-align: center;
    }

    .nav-link:hover {
        background: #edf8f2;
        color: #0f744c;
        transform: translateY(-1px);
    }

    .nav-link:hover i {
        color: #0f744c;
    }

    .nav-link.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, .30);
        font-weight: 700;
    }

    .nav-link.active i {
        color: #ffffff;
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 50%;
        transform: translateX(-50%);
        width: 30%;
        height: 3px;
        background: #ffffff;
        border-radius: 3px 3px 0 0;
    }

    .nav-link.more-toggle {
        color: #0f744c;
        cursor: pointer;
        border: 1px dashed rgba(15, 116, 76, .25);
        background: rgba(15, 116, 76, .04);
    }

    .nav-link.more-toggle:hover {
        background: #edf8f2;
        border-color: #34d399;
        transform: translateY(-1px);
    }

    .nav-link.more-toggle i {
        color: #0f744c;
    }

    .nav-link .badge-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0f744c;
        color: white;
        border-radius: 30px;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 8px;
        min-width: 20px;
        height: 20px;
        line-height: 1;
        margin-left: 4px;
    }

    .nav-link.active .badge-count {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .nav-tabs {
            padding: 8px 12px;
            gap: 2px;
        }
        .nav-link {
            padding: 8px 14px;
            font-size: 0.8rem;
            min-height: 38px;
            gap: 6px;
        }
        .nav-link i {
            font-size: 0.85rem;
            width: 16px;
        }
    }

    @media (max-width: 768px) {
        .nav-tabs-wrapper {
            border-radius: 16px;
            margin-bottom: 20px;
        }
        .nav-tabs {
            padding: 6px 10px;
            gap: 2px;
        }
        .nav-link {
            padding: 6px 12px;
            font-size: 0.75rem;
            min-height: 34px;
            gap: 4px;
            border-radius: 8px;
        }
        .nav-link i {
            font-size: 0.75rem;
            width: 14px;
        }
        .nav-link .badge-count {
            font-size: 0.55rem;
            padding: 1px 6px;
            min-width: 16px;
            height: 16px;
        }
    }

    /* Dark Mode Support */
    html[data-pms-theme="dark"] .nav-tabs-wrapper {
        background: #102119;
        border-color: rgba(122, 240, 181, .18);
    }

    html[data-pms-theme="dark"] .nav-tabs {
        background: linear-gradient(135deg, #102119 0%, #142a20 100%);
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .nav-tabs.extra-tabs {
        background: linear-gradient(135deg, #142a20 0%, #102119 100%);
        border-color: rgba(122, 240, 181, .08);
    }

    html[data-pms-theme="dark"] .nav-link {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .nav-link i {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .nav-link:hover {
        background: rgba(122, 240, 181, .10);
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .nav-link:hover i {
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .nav-link.active {
        background: linear-gradient(145deg, #0f744c, #10b981);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, .30);
    }

    html[data-pms-theme="dark"] .nav-link.active i {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-link.more-toggle {
        color: #7af0b5;
        border-color: rgba(122, 240, 181, .25);
        background: rgba(122, 240, 181, .05);
    }

    html[data-pms-theme="dark"] .nav-link.more-toggle:hover {
        background: rgba(122, 240, 181, .10);
        border-color: #7af0b5;
    }

    html[data-pms-theme="dark"] .nav-link .badge-count {
        background: #10b981;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-link.active .badge-count {
        background: rgba(255, 255, 255, 0.20);
        color: white;
    }
</style>

<div class="nav-tabs-wrapper">
    <ul class="nav-tabs">
        @foreach($tabs as $key => $tab)
            @php
                $isActive = $activeTab == $key;
                $route = isset($tab['route']) && $tab['route'] != '#' ? (isset($project) ? route($tab['route'], $project->id) : '#') : '#';
                $icon = $tab['icon'] ?? 'fa-circle';
                $badge = $tab['badge'] ?? null;
                $isMoreTab = in_array($key, ['discussion', 'burndown', 'activity', 'tickets']);
            @endphp

            @if(!$isMoreTab || ($isMoreTab && isset($showMore) && $showMore))
                <li class="nav-item">
                    <a
                        class="nav-link {{ $isActive ? 'active' : '' }}"
                        href="{{ $route }}"
                        data-tab="{{ $key }}"
                        title="{{ $tab['label'] }}"
                    >
                        <i class="fas {{ $icon }}"></i>
                        {{ $tab['label'] }}
                        @if($badge)
                            <span class="badge-count">{{ $badge }}</span>
                        @endif
                    </a>
                </li>
            @endif
        @endforeach

        {{-- More Toggle Button --}}
        @if(isset($showMore))
            <li class="nav-item">
                <a class="nav-link more-toggle" href="#" id="toggle-more">
                    <i class="fas fa-ellipsis-h"></i>
                    <span id="moreToggleText">More</span>
                    <i class="fas fa-chevron-down" id="moreToggleIcon"></i>
                </a>
            </li>
        @endif
    </ul>

    {{-- Extra Tabs (Collapsible) --}}
    @if(isset($showMore))
        <ul class="nav-tabs extra-tabs d-none" id="more-tabs">
            @foreach($tabs as $key => $tab)
                @php
                    $isMoreTab = in_array($key, ['discussion', 'burndown', 'activity', 'tickets']);
                @endphp
                @if($isMoreTab)
                    <li class="nav-item">
                        <a
                            class="nav-link {{ $activeTab == $key ? 'active' : '' }}"
                            href="{{ isset($project) ? route($tab['route'], $project->id) : '#' }}"
                            data-tab="{{ $key }}"
                            title="{{ $tab['label'] }}"
                        >
                            <i class="fas {{ $tab['icon'] ?? 'fa-circle' }}"></i>
                            {{ $tab['label'] }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    @endif
</div>

@push('js')
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

            const toggleText = document.getElementById('moreToggleText');
            const toggleIcon = document.getElementById('moreToggleIcon');

            if (toggleText) {
                toggleText.textContent = isHidden ? 'Less' : 'More';
            }
            if (toggleIcon) {
                toggleIcon.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            }
        });
    }
});
</script>
@endpush
