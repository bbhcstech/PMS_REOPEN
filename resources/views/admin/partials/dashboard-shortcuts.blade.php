@php($shortcutGroups = app(\App\Services\DashboardShortcuts::class)->groups(auth()->user()))
@if($shortcutGroups->isNotEmpty())
    <style>
        .dashboard-shortcuts-panel { margin-bottom: 1.5rem; }
        .dashboard-shortcut-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr)); gap: .75rem; }
        .dashboard-shortcut-group + .dashboard-shortcut-group { margin-top: 1.5rem; }
        .dashboard-shortcut-group h4 { color: var(--bx-ink-muted); font-size: .75rem; letter-spacing: .06em; margin-bottom: .85rem; }
        .dashboard-shortcut-link { display: flex; align-items: center; gap: .75rem; padding: 1rem; border: 1px solid var(--bx-border); border-radius: 12px; background: var(--bx-surface-2); color: var(--bx-ink); text-decoration: none; font-weight: 600; }
        .dashboard-shortcut-link > .bx:first-child { padding: .6rem; border-radius: 10px; background: var(--bx-blue-soft); color: var(--bx-blue); }
        .dashboard-shortcut-link > .bx:last-child { margin-left: auto; color: var(--bx-blue); }
        .dashboard-shortcut-link:hover, .dashboard-shortcut-link:focus-visible { border-color: var(--bx-blue); color: var(--bx-blue); box-shadow: var(--bx-shadow-sm); }
    </style>
    <section class="industry-panel dashboard-shortcuts-panel" aria-labelledby="dashboard-shortcuts-title">
        <div class="industry-panel-head">
            <div><h3 id="dashboard-shortcuts-title">Feature Shortcuts</h3><p>Your available tools, all in one place.</p></div>
            <a href="{{ route('admin.settings.app') }}">Customize dashboard</a>
        </div>
        @foreach($shortcutGroups as $group => $shortcuts)
            <div class="dashboard-shortcut-group">
                <h4>{{ $group }}</h4>
                <div class="dashboard-shortcut-grid">
                    @foreach($shortcuts as $shortcut)
                        <a class="dashboard-shortcut-link" href="{{ $shortcut['url'] }}"><i class="bx bx-grid-alt" aria-hidden="true"></i><span>{{ $shortcut['label'] }}</span><i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif
