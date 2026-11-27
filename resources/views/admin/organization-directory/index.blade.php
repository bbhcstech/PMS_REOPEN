@extends('admin.layout.app')

@section('title', 'Organization Details')

@php
    $treePayload = $treePayload ?? [
        'company' => ['name' => 'Organization', 'logo' => null],
        'nodes' => [],
        'generated_at' => now()->toIso8601String(),
    ];
    $treeNodes = collect($treePayload['nodes'] ?? []);
    $companyContext = app(\App\Services\CompanyContext::class)->current();
    $companyName = $treePayload['company']['name']
        ?? ($companyContext?->display_name ?: $companyContext?->name)
        ?? (($companies ?? collect())->firstWhere('id', auth()->user()?->company_id)?->name ?? 'Organization');

    $treeDepartments = $treeNodes
        ->filter(fn ($n) => !empty($n['department']))
        ->unique(fn ($n) => $n['department_id'] ?? $n['department'])
        ->map(fn ($n) => [
            'id' => (string) ($n['department_id'] ?? $n['department']),
            'name' => (string) $n['department'],
            'count' => $treeNodes->filter(fn ($item) => (string) ($item['department_id'] ?? $item['department'] ?? '') === (string) ($n['department_id'] ?? $n['department']))->count(),
        ])
        ->sortBy('name')
        ->values();

    $designationCountsByKey = $treeNodes
        ->filter(fn ($n) => !empty($n['designation']) && $n['designation'] !== 'No designation')
        ->groupBy(fn ($n) => !empty($n['designation_id']) ? 'id:' . $n['designation_id'] : 'name:' . strtolower((string) $n['designation']))
        ->map->count();

    $designationFilterOptions = collect($designations ?? [])
        ->map(function ($d) use ($designationCountsByKey, $treeNodes) {
            $idKey = 'id:' . $d->id;
            $nameKey = 'name:' . strtolower((string) $d->name);
            $count = $designationCountsByKey->get($idKey)
                ?? $designationCountsByKey->get($nameKey)
                ?? $treeNodes->filter(fn ($n) => strcasecmp((string) ($n['designation'] ?? ''), (string) $d->name) === 0)->count();

            return [
                'id' => (string) $d->id,
                'name' => (string) $d->name,
                'level' => isset($d->level) && $d->level !== null ? (int) $d->level : null,
                'count' => (int) $count,
            ];
        })
        ->merge(
            $treeNodes
                ->filter(fn ($n) => !empty($n['designation']) && $n['designation'] !== 'No designation')
                ->unique(fn ($n) => !empty($n['designation_id']) ? 'id:' . $n['designation_id'] : 'name:' . strtolower((string) $n['designation']))
                ->map(fn ($n) => [
                    'id' => !empty($n['designation_id']) ? (string) $n['designation_id'] : ('name:' . $n['designation']),
                    'name' => (string) $n['designation'],
                    'level' => $n['level'] ?? null,
                    'count' => $treeNodes->filter(fn ($item) => strcasecmp((string) ($item['designation'] ?? ''), (string) $n['designation']) === 0)->count(),
                ])
        )
        ->unique(fn ($item) => strtolower($item['name']))
        ->sort(function ($a, $b) {
            $la = $a['level'] ?? -1;
            $lb = $b['level'] ?? -1;
            if ($la !== $lb) {
                return $lb <=> $la;
            }
            return strcasecmp($a['name'], $b['name']);
        })
        ->values();

    $totalTreeEmployees = $treeNodes->count();
    $representedDesignationsCount = $treeNodes
        ->filter(fn ($n) => !empty($n['designation']) && $n['designation'] !== 'No designation')
        ->unique(fn ($n) => strtolower((string) $n['designation']))
        ->count();
    $representedDepartmentsCount = $treeDepartments->count();
    $reportingLeadsCount = $treeNodes->filter(fn ($n) => ((int) ($n['directs'] ?? 0)) > 0)->count();
@endphp

@push('styles')
<style>
    .orgd-page {
        --orgd-surface: #ffffff;
        --orgd-canvas: #f8fafc;
        --orgd-grid: rgba(99, 102, 241, 0.08);
        --orgd-border: #e2e8f0;
        --orgd-text: #0f172a;
        --orgd-muted: #64748b;
        --orgd-accent: #4f46e5;
        --orgd-accent-strong: #4338ca;
        --orgd-accent-soft: #eef2ff;
        --orgd-line: #cbd5e1;
        --orgd-path: #7c3aed;
        --orgd-ok: #15803d;
        --orgd-ok-soft: #dcfce7;
        --orgd-warn: #b45309;
        --orgd-warn-soft: #fef3c7;
        --orgd-shadow: 0 6px 24px rgba(15, 23, 42, 0.06);
        padding: 4px 2px 28px;
        color: var(--orgd-text);
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) .orgd-page {
        --orgd-surface: #161b2e;
        --orgd-canvas: #0f1424;
        --orgd-grid: rgba(167, 139, 250, 0.08);
        --orgd-border: #2a3150;
        --orgd-text: #f1f5f9;
        --orgd-muted: #94a3b8;
        --orgd-accent: #818cf8;
        --orgd-accent-strong: #6366f1;
        --orgd-accent-soft: rgba(129, 140, 248, 0.16);
        --orgd-line: #3b4466;
        --orgd-path: #a78bfa;
        --orgd-ok: #4ade80;
        --orgd-ok-soft: rgba(74, 222, 128, 0.14);
        --orgd-warn: #fbbf24;
        --orgd-warn-soft: rgba(251, 191, 36, 0.14);
        --orgd-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    }

    .orgd-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .orgd-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 14px;
        border-radius: 10px;
        border: 1px solid var(--orgd-border);
        background: var(--orgd-surface);
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none !important;
        transition: border-color 0.2s, background 0.2s, transform 0.15s;
    }
    .orgd-back-btn:hover {
        border-color: var(--orgd-accent);
        background: var(--orgd-accent-soft);
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }

    .orgd-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 0.83rem;
        color: var(--orgd-muted);
    }
    .orgd-breadcrumb a {
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
        text-decoration: none;
        font-weight: 600;
    }
    .orgd-breadcrumb a:hover { text-decoration: underline; }
    .orgd-breadcrumb [aria-current="page"] {
        color: var(--orgd-text);
        font-weight: 600;
    }

    /* Header Card */
    .orgd-header-card {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 24px;
        margin-bottom: 18px;
        border-radius: 18px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        box-shadow: var(--orgd-shadow);
    }

    .orgd-header-main {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        min-width: 0;
        flex: 1;
    }

    .orgd-header-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: var(--orgd-accent-soft);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .orgd-header-icon i {
        font-size: 1.25rem;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }

    .orgd-header-text h1 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--orgd-text);
        line-height: 1.25;
    }
    .orgd-header-text p {
        margin: 4px 0 0;
        font-size: 0.9rem;
        color: var(--orgd-muted);
        max-width: 68ch;
    }

    .orgd-company-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--orgd-canvas);
        border: 1px solid var(--orgd-border);
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--orgd-muted);
    }
    .orgd-company-pill i {
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }

    .orgd-header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .orgd-btn {
        display: inline-flex !important;
        align-items: center;
        gap: 7px;
        height: 38px;
        padding: 0 14px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-surface) !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none !important;
        transition: border-color 0.2s, background 0.2s, transform 0.15s;
    }
    .orgd-btn i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }
    .orgd-btn:hover {
        border-color: var(--orgd-accent) !important;
        background: var(--orgd-accent-soft) !important;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }
    .orgd-btn-primary {
        border-color: var(--orgd-accent) !important;
        background: var(--orgd-accent) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .orgd-btn-primary:hover {
        background: var(--orgd-accent-strong) !important;
        border-color: var(--orgd-accent-strong) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .orgd-btn:focus-visible,
    .orgd-control:focus-visible,
    .orgd-chip:focus-visible,
    .orgd-tile:focus-visible,
    .orgd-tool:focus-visible {
        outline: 2px solid var(--orgd-accent) !important;
        outline-offset: 2px;
    }

    /* Summary KPI Grid */
    .orgd-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .orgd-kpi-card {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 16px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        box-shadow: var(--orgd-shadow);
    }
    .orgd-kpi-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--orgd-muted);
    }
    .orgd-kpi-value {
        margin-top: 6px;
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--orgd-text);
    }
    .orgd-kpi-sub {
        margin-top: 4px;
        font-size: 0.78rem;
        color: var(--orgd-muted);
    }
    .orgd-kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        background: var(--orgd-accent-soft);
    }
    .orgd-kpi-icon i {
        font-size: 1rem;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }
    .orgd-kpi-icon.is-green { background: var(--orgd-ok-soft); }
    .orgd-kpi-icon.is-green i { color: var(--orgd-ok) !important; -webkit-text-fill-color: var(--orgd-ok) !important; }
    .orgd-kpi-icon.is-amber { background: var(--orgd-warn-soft); }
    .orgd-kpi-icon.is-amber i { color: var(--orgd-warn) !important; -webkit-text-fill-color: var(--orgd-warn) !important; }

    /* Filter & Search Card */
    .orgd-filter-card {
        padding: 18px 20px;
        margin-bottom: 18px;
        border-radius: 18px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        box-shadow: var(--orgd-shadow);
    }

    .orgd-filter-grid {
        display: grid;
        grid-template-columns: minmax(220px, 1.5fr) minmax(190px, 1fr) minmax(180px, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    .orgd-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    .orgd-field label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--orgd-text);
        margin: 0;
    }

    .orgd-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .orgd-input-wrap i {
        position: absolute;
        left: 12px;
        font-size: 0.85rem;
        color: var(--orgd-muted) !important;
        -webkit-text-fill-color: var(--orgd-muted) !important;
        pointer-events: none;
    }

    .orgd-control {
        width: 100%;
        height: 40px;
        border-radius: 10px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-canvas) !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        font-size: 0.87rem;
        padding: 0 12px !important;
        box-shadow: none !important;
    }
    .orgd-input-wrap .orgd-control {
        padding-left: 36px !important;
    }

    .orgd-filter-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .orgd-toggle-btn {
        display: inline-flex !important;
        align-items: center;
        gap: 7px;
        height: 40px;
        padding: 0 13px !important;
        border-radius: 10px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-canvas) !important;
        color: var(--orgd-muted) !important;
        -webkit-text-fill-color: var(--orgd-muted) !important;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
    }
    .orgd-toggle-btn i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }
    .orgd-toggle-btn[aria-pressed="true"] {
        border-color: var(--orgd-accent) !important;
        background: var(--orgd-accent-soft) !important;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }

    .orgd-chips-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed var(--orgd-border);
    }
    .orgd-chips-label {
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--orgd-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-right: 4px;
    }
    .orgd-chip {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        padding: 5px 11px !important;
        border-radius: 999px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-canvas) !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }
    .orgd-chip .orgd-chip-count {
        padding: 1px 6px;
        border-radius: 999px;
        background: var(--orgd-surface);
        color: var(--orgd-muted);
        font-size: 0.72rem;
        font-weight: 700;
    }
    .orgd-chip.is-active {
        border-color: var(--orgd-accent) !important;
        background: var(--orgd-accent) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .orgd-chip.is-active .orgd-chip-count {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    /* Tree Panel */
    .orgd-tree-panel {
        border-radius: 20px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        box-shadow: var(--orgd-shadow);
        overflow: hidden;
    }
    .orgd-tree-panel i { font-style: normal; }

    .orgd-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--orgd-border);
    }

    .orgd-toolbar-info h2 {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--orgd-text);
    }
    .orgd-toolbar-status {
        margin: 2px 0 0;
        font-size: 0.82rem;
        color: var(--orgd-muted);
    }

    .orgd-toolbar-controls {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .orgd-group {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        padding: 3px;
        border-radius: 12px;
        border: 1px solid var(--orgd-border);
        background: var(--orgd-canvas);
    }

    .orgd-tool {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        min-height: 0 !important;
        padding: 0 !important;
        display: inline-grid !important;
        place-items: center;
        border: 0 !important;
        border-radius: 8px !important;
        background: transparent !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        cursor: pointer;
    }
    .orgd-tool i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: 0.82rem;
    }
    .orgd-tool:hover {
        background: var(--orgd-accent-soft) !important;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }
    .orgd-zoom-label {
        min-width: 46px;
        text-align: center;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--orgd-muted);
    }

    .orgd-body {
        position: relative;
        width: 100%;
    }

    .orgd-viewport {
        position: relative;
        width: 100%;
        height: min(72vh, 740px);
        min-height: 480px;
        overflow: hidden;
        cursor: grab;
        touch-action: none;
        user-select: none;
        outline: none;
        background-color: var(--orgd-canvas);
        background-image: radial-gradient(var(--orgd-grid) 1.25px, transparent 1.25px);
        background-size: 22px 22px;
    }
    .orgd-viewport.is-dragging { cursor: grabbing; }

    .orgd-stage {
        position: absolute;
        left: 0;
        top: 0;
        width: max-content;
        transform-origin: 0 0;
        padding: 40px 44px 68px;
        will-change: transform;
    }

    .orgd-empty {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 28px;
        text-align: center;
        color: var(--orgd-muted);
    }
    .orgd-empty[hidden] { display: none; }
    .orgd-empty i {
        font-size: 2.2rem;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
    }
    .orgd-empty strong { color: var(--orgd-text); font-size: 1rem; }
    .orgd-empty span { max-width: 400px; font-size: 0.85rem; }

    .orgd-pan {
        position: absolute;
        left: 16px;
        bottom: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 4px;
        border-radius: 12px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        box-shadow: var(--orgd-shadow);
    }
    .orgd-pan > div { display: flex; gap: 30px; }

    .orgd-legend {
        position: absolute;
        right: 16px;
        bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 8px 12px;
        border-radius: 10px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        font-size: 0.76rem;
        color: var(--orgd-muted);
    }
    .orgd-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .orgd-dot { width: 18px; height: 0; border-top: 2px solid var(--orgd-line); display: inline-block; }
    .orgd-dot.is-level { border-top-style: dashed; }

    /* CSS Hierarchy Tree Connectors */
    .orgd-tree, .orgd-tree ul { margin: 0; padding: 0; }
    .orgd-tree ul {
        display: flex;
        justify-content: center;
        position: relative;
        padding-top: 26px;
    }
    .orgd-tree li {
        list-style: none;
        flex-shrink: 0;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 26px 10px 0;
    }
    .orgd-tree li::before, .orgd-tree li::after {
        content: '';
        position: absolute;
        top: 0;
        right: 50%;
        width: 50%;
        height: 26px;
        border-top: 2px solid var(--orgd-line);
    }
    .orgd-tree li::after {
        right: auto;
        left: 50%;
        border-left: 2px solid var(--orgd-line);
    }
    .orgd-tree li:only-child::before, .orgd-tree li:only-child::after { display: none; }
    .orgd-tree li:only-child { padding-top: 0; }
    .orgd-tree li:first-child::before, .orgd-tree li:last-child::after { border: 0 none; }
    .orgd-tree li:last-child::before {
        border-right: 2px solid var(--orgd-line);
        border-radius: 0 10px 0 0;
    }
    .orgd-tree li:first-child::after { border-radius: 10px 0 0 0; }
    .orgd-tree ul ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        height: 26px;
        border-left: 2px solid var(--orgd-line);
    }
    .orgd-tree > ul { padding-top: 0; }
    .orgd-tree li.is-level-placed::after,
    .orgd-tree li.is-level-placed:last-child::before { border-style: dashed; }
    .orgd-tree li.is-path::after { border-left-color: var(--orgd-path); }
    .orgd-tree li.is-path:last-child::before { border-right-color: var(--orgd-path); }
    .orgd-tree li.is-path > ul::before { border-left-color: var(--orgd-path); }
    .orgd-tree li.is-collapsed > ul { display: none; }
    .orgd-tree li > .orgd-tile { margin-bottom: 14px; }
    .orgd-tree li.is-leaf > .orgd-tile { margin-bottom: 0; }

    /* Employee Tiles */
    .orgd-tile {
        position: relative;
        width: 244px;
        padding: 14px 14px 12px;
        border-radius: 16px;
        background: var(--orgd-surface);
        border: 1px solid var(--orgd-border);
        box-shadow: var(--orgd-shadow);
        cursor: pointer;
        text-align: left;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s, opacity 0.2s;
    }
    .orgd-tile:hover { border-color: var(--orgd-accent); transform: translateY(-2px); }
    .orgd-tile.is-selected {
        border-color: var(--orgd-accent);
        box-shadow: 0 0 0 3px var(--orgd-accent-soft), var(--orgd-shadow);
    }
    .orgd-tile.is-path { border-color: var(--orgd-path); }
    .orgd-tile.is-dim { opacity: 0.34; }
    .orgd-tile.is-match {
        border-color: var(--orgd-warn);
        box-shadow: 0 0 0 3px var(--orgd-warn-soft), var(--orgd-shadow);
    }
    .orgd-tile.is-company {
        width: 266px;
        background: linear-gradient(135deg, var(--orgd-accent-soft), var(--orgd-surface));
    }

    .orgd-tile-flag {
        position: absolute;
        top: -10px;
        left: 14px;
        padding: 2px 8px;
        border-radius: 999px;
        background: var(--orgd-path);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        display: none;
    }
    .orgd-tile.is-path .orgd-tile-flag,
    .orgd-tile.is-selected .orgd-tile-flag { display: inline-block; }

    .orgd-tile-top { display: flex; align-items: center; gap: 10px; }
    .orgd-photo {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        flex-shrink: 0;
        overflow: hidden;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .orgd-photo img { width: 100% !important; height: 100% !important; object-fit: cover; display: block; }
    .orgd-photo.is-large { width: 72px; height: 72px; font-size: 1.5rem; }
    .orgd-photo.is-company { border-radius: 12px; }

    .orgd-who { min-width: 0; flex: 1; }
    .orgd-name {
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--orgd-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .orgd-name > span { overflow: hidden; text-overflow: ellipsis; }
    .orgd-name i {
        color: #3b82f6 !important;
        -webkit-text-fill-color: #3b82f6 !important;
        font-size: 0.75rem;
        flex-shrink: 0;
    }
    .orgd-designation {
        margin: 2px 0 0;
        font-size: 0.77rem;
        font-weight: 600;
        color: var(--orgd-accent);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .orgd-tile.is-match .orgd-designation {
        color: var(--orgd-warn);
    }

    .orgd-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
    .orgd-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        max-width: 100%;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.69rem;
        font-weight: 600;
        background: var(--orgd-accent-soft);
        color: var(--orgd-accent);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .orgd-tag.is-dept {
        background: var(--orgd-canvas);
        color: var(--orgd-muted);
        border: 1px solid var(--orgd-border);
    }
    .orgd-tag.is-ok { background: var(--orgd-ok-soft); color: var(--orgd-ok); }
    .orgd-tag.is-warn { background: var(--orgd-warn-soft); color: var(--orgd-warn); }

    .orgd-tile-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px dashed var(--orgd-border);
        font-size: 0.74rem;
        color: var(--orgd-muted);
    }
    .orgd-tile-foot strong { color: var(--orgd-text); }

    .orgd-toggle {
        position: absolute;
        left: 50%;
        bottom: -13px;
        transform: translateX(-50%);
        width: 26px !important;
        height: 26px !important;
        min-height: 0 !important;
        padding: 0 !important;
        border-radius: 50% !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-surface) !important;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
        display: grid !important;
        place-items: center;
        cursor: pointer;
        z-index: 2;
        box-shadow: var(--orgd-shadow) !important;
        font-size: 0.7rem;
    }
    .orgd-toggle i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        transition: transform 0.2s;
    }
    .orgd-tree li.is-collapsed > .orgd-tile .orgd-toggle i { transform: rotate(-90deg); }

    /* Inspector Drawer */
    .orgd-inspector {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 380px;
        max-width: 100%;
        display: flex;
        flex-direction: column;
        background: var(--orgd-surface);
        border-left: 1px solid var(--orgd-border);
        box-shadow: -12px 0 30px rgba(0, 0, 0, 0.12);
        transform: translateX(105%);
        visibility: hidden;
        transition: transform 0.26s ease, visibility 0s linear 0.26s;
        z-index: 6;
    }
    .orgd-inspector.is-open {
        transform: translateX(0);
        visibility: visible;
        transition: transform 0.26s ease;
    }
    .orgd-inspector-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid var(--orgd-border);
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--orgd-muted);
    }
    .orgd-inspector-body {
        flex: 1;
        overflow-y: auto;
        padding: 18px 16px 22px;
        overscroll-behavior: contain;
    }
    .orgd-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 6px;
    }
    .orgd-hero h3 {
        margin: 6px 0 0;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--orgd-text);
    }
    .orgd-hero h3 i {
        color: #3b82f6 !important;
        -webkit-text-fill-color: #3b82f6 !important;
        font-size: 0.9rem;
    }
    .orgd-hero p { margin: 0; font-size: 0.84rem; color: var(--orgd-muted); }
    .orgd-hero .orgd-tags { justify-content: center; margin-top: 4px; }

    .orgd-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 16px 0; }
    .orgd-action {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 10px 6px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-canvas) !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none !important;
        cursor: pointer;
    }
    .orgd-action i {
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
        font-size: 0.95rem;
    }
    .orgd-action:hover { border-color: var(--orgd-accent) !important; }
    .orgd-action.is-disabled { opacity: 0.45; pointer-events: none; }

    .orgd-section { margin-top: 16px; }
    .orgd-section h4 {
        margin: 0 0 8px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--orgd-muted);
    }
    .orgd-person {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 8px 10px !important;
        border-radius: 12px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-canvas) !important;
        text-align: left;
        cursor: pointer;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
    }
    .orgd-person + .orgd-person { margin-top: 6px; }
    .orgd-person:hover { border-color: var(--orgd-accent) !important; }
    .orgd-person .orgd-photo { width: 34px; height: 34px; font-size: 0.76rem; }
    .orgd-person strong { display: block; font-size: 0.82rem; color: var(--orgd-text); }
    .orgd-person small { display: block; font-size: 0.75rem; color: var(--orgd-muted); }
    .orgd-person > i {
        margin-left: auto;
        color: var(--orgd-muted) !important;
        -webkit-text-fill-color: var(--orgd-muted) !important;
        font-size: 0.72rem;
    }

    .orgd-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .orgd-stats div { padding: 12px; border-radius: 12px; background: var(--orgd-accent-soft); text-align: center; }
    .orgd-stats strong { display: block; font-size: 1.35rem; font-weight: 800; color: var(--orgd-accent); }
    .orgd-stats span { font-size: 0.76rem; color: var(--orgd-muted); }

    .orgd-facts { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 0; }
    .orgd-facts div { min-width: 0; padding: 10px; border-radius: 12px; border: 1px solid var(--orgd-border); }
    .orgd-facts div.is-wide { grid-column: 1 / -1; }
    .orgd-facts dt { font-size: 0.7rem; font-weight: 600; color: var(--orgd-muted); }
    .orgd-facts dd { margin: 2px 0 0; font-size: 0.82rem; font-weight: 600; color: var(--orgd-text); overflow-wrap: anywhere; }
    .orgd-note { font-size: 0.78rem; color: var(--orgd-muted); margin: 0; }

    @media (max-width: 1100px) {
        .orgd-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 991.98px) {
        .orgd-inspector { position: fixed; top: 0; bottom: 0; right: 0; width: min(100vw, 400px); z-index: 1065; }
        .orgd-legend { display: none; }
    }
    @media (max-width: 640px) {
        .orgd-filter-grid { grid-template-columns: 1fr; }
        .orgd-viewport { height: 64vh; min-height: 380px; }
        .orgd-pan { left: 10px; bottom: 10px; }
    }
    @media print {
        .orgd-topbar, .orgd-filter-card, .orgd-header-actions, .orgd-pan, .orgd-inspector { display: none !important; }
        .orgd-viewport { height: auto !important; overflow: visible !important; background: #fff !important; }
        .orgd-stage { position: static !important; transform: none !important; padding: 12px !important; }
    }
</style>
@endpush

@section('content')
<div class="orgd-page">
    <div class="orgd-topbar">
        <a href="{{ route('dashboard') }}" class="orgd-back-btn">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            <span>Back</span>
        </a>
        <nav class="orgd-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="fas fa-home" aria-hidden="true"></i> Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true" style="font-size: 0.68rem;"></i>
            <span aria-current="page">Organization Details</span>
        </nav>
    </div>

    <header class="orgd-header-card">
        <div class="orgd-header-main">
            <div class="orgd-header-icon" aria-hidden="true">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="orgd-header-text">
                <h1 id="organization-details-heading">Organization Details</h1>
                <p>Interactive employee hierarchy tree fetched directly from Employee records, with designation-wise search and reporting chain inspection.</p>
                <div class="orgd-company-pill">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span id="orgCompanyBadge">{{ $companyName }}</span>
                </div>
            </div>
        </div>
        <div class="orgd-header-actions">
            <button type="button" class="orgd-btn" id="orgRefreshBtn">
                <i class="fas fa-rotate" aria-hidden="true"></i>
                <span>Refresh Tree</span>
            </button>
            <button type="button" class="orgd-btn" id="orgExportCsvBtn">
                <i class="fas fa-file-csv" aria-hidden="true"></i>
                <span>Export CSV</span>
            </button>
            <button type="button" class="orgd-btn orgd-btn-primary" id="orgPrintBtn">
                <i class="fas fa-print" aria-hidden="true"></i>
                <span>Print Chart</span>
            </button>
        </div>
    </header>

    <section class="orgd-kpis" aria-label="Organization summary" data-live-preserve>
        <div class="orgd-kpi-card">
            <div>
                <div class="orgd-kpi-label">Employees in Tree</div>
                <div class="orgd-kpi-value" id="orgKpiTotal">{{ $totalTreeEmployees }}</div>
                <div class="orgd-kpi-sub">Fetched from Employee records</div>
            </div>
            <div class="orgd-kpi-icon" aria-hidden="true">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="orgd-kpi-card">
            <div>
                <div class="orgd-kpi-label">Designations</div>
                <div class="orgd-kpi-value" id="orgKpiDesignations">{{ $representedDesignationsCount }}</div>
                <div class="orgd-kpi-sub">{{ $designationFilterOptions->count() }} configured roles</div>
            </div>
            <div class="orgd-kpi-icon is-amber" aria-hidden="true">
                <i class="fas fa-id-badge"></i>
            </div>
        </div>
        <div class="orgd-kpi-card">
            <div>
                <div class="orgd-kpi-label">Departments</div>
                <div class="orgd-kpi-value" id="orgKpiDepartments">{{ $representedDepartmentsCount }}</div>
                <div class="orgd-kpi-sub">Active teams in structure</div>
            </div>
            <div class="orgd-kpi-icon is-green" aria-hidden="true">
                <i class="fas fa-building-user"></i>
            </div>
        </div>
        <div class="orgd-kpi-card">
            <div>
                <div class="orgd-kpi-label">Reporting Leads</div>
                <div class="orgd-kpi-value" id="orgKpiLeads">{{ $reportingLeadsCount }}</div>
                <div class="orgd-kpi-sub">Employees with direct reports</div>
            </div>
            <div class="orgd-kpi-icon" aria-hidden="true">
                <i class="fas fa-diagram-project"></i>
            </div>
        </div>
    </section>

    <section class="orgd-filter-card" aria-label="Designation-wise search and tree filters" data-live-preserve>
        <div class="orgd-filter-grid" role="search" aria-label="Search organization tree by designation or employee">
            <div class="orgd-field">
                <label for="orgSearchInput">Search by Designation or Employee</label>
                <div class="orgd-input-wrap">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        type="search"
                        id="orgSearchInput"
                        class="orgd-control"
                        list="orgSearchSuggestions"
                        placeholder="Type designation (e.g. Manager, Developer), name, or ID..."
                        autocomplete="off"
                    >
                </div>
                <datalist id="orgSearchSuggestions">
                    @foreach($designationFilterOptions as $desigOption)
                        <option value="{{ $desigOption['name'] }}"></option>
                    @endforeach
                    @foreach($treeNodes as $empNode)
                        <option value="{{ $empNode['name'] }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="orgd-field">
                <label for="orgDesignationSelect">Designation</label>
                <select id="orgDesignationSelect" class="orgd-control">
                    <option value="">All Designations ({{ $totalTreeEmployees }})</option>
                    @foreach($designationFilterOptions as $desigOption)
                        <option value="{{ $desigOption['name'] }}">
                            {{ $desigOption['name'] }}@if($desigOption['level'] !== null) (L{{ $desigOption['level'] }})@endif — {{ $desigOption['count'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="orgd-field">
                <label for="orgDepartmentSelect">Department</label>
                <select id="orgDepartmentSelect" class="orgd-control">
                    <option value="">All Departments</option>
                    @foreach($treeDepartments as $deptOption)
                        <option value="{{ $deptOption['name'] }}">
                            {{ $deptOption['name'] }} ({{ $deptOption['count'] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="orgd-filter-actions">
                <button
                    type="button"
                    id="orgIsolateBtn"
                    class="orgd-toggle-btn"
                    aria-pressed="false"
                    title="Show only matching designation branch and reporting ancestors"
                >
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    <span>Isolate Branch</span>
                </button>
                <button type="button" id="orgResetFiltersBtn" class="orgd-btn">
                    <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        @if($designationFilterOptions->where('count', '>', 0)->isNotEmpty())
            <div class="orgd-chips-row" aria-label="Quick designation filters">
                <span class="orgd-chips-label">By Designation:</span>
                <button type="button" class="orgd-chip is-active" data-designation-chip="">
                    <span>All</span>
                    <span class="orgd-chip-count">{{ $totalTreeEmployees }}</span>
                </button>
                @foreach($designationFilterOptions->where('count', '>', 0) as $desigChip)
                    <button type="button" class="orgd-chip" data-designation-chip="{{ $desigChip['name'] }}">
                        <span>{{ $desigChip['name'] }}</span>
                        <span class="orgd-chip-count">{{ $desigChip['count'] }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    <section
        class="orgd-tree-panel"
        id="orgTreeRoot"
        data-endpoint="{{ route('organization.tree') }}"
        data-live-preserve
        aria-labelledby="organization-details-heading"
    >
        <div class="orgd-toolbar">
            <div class="orgd-toolbar-info">
                <h2>Employee Hierarchy Structure</h2>
                <p class="orgd-toolbar-status" id="orgStatusText" aria-live="polite">
                    {{ $totalTreeEmployees > 0
                        ? 'Showing ' . $totalTreeEmployees . ' active ' . ($totalTreeEmployees === 1 ? 'employee' : 'employees') . ' across the organization hierarchy.'
                        : 'No active employee records found.' }}
                </p>
            </div>

            <div class="orgd-toolbar-controls">
                <div class="orgd-group" aria-label="Expand or collapse branches">
                    <button type="button" class="orgd-tool" id="orgExpandAllBtn" title="Expand all branches" aria-label="Expand all branches">
                        <i class="fas fa-angles-down" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="orgd-tool" id="orgCollapseAllBtn" title="Collapse all branches" aria-label="Collapse all branches">
                        <i class="fas fa-angles-up" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="orgd-group orgd-chart-only" aria-label="Zoom and fit controls">
                    <button type="button" class="orgd-tool" id="orgZoomOutBtn" title="Zoom out" aria-label="Zoom out">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                    </button>
                    <span class="orgd-zoom-label" id="orgZoomLabel" aria-label="Current zoom level">100%</span>
                    <button type="button" class="orgd-tool" id="orgZoomInBtn" title="Zoom in" aria-label="Zoom in">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="orgd-tool" id="orgFitBtn" title="Fit tree to screen" aria-label="Fit tree to screen">
                        <i class="fas fa-expand" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="orgd-tool" id="orgResetViewBtn" title="Reset zoom and position" aria-label="Reset zoom and position">
                        <i class="fas fa-crosshairs" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="orgd-body">
            <div class="orgd-viewport" id="orgViewport" tabindex="0" aria-label="Interactive employee hierarchy canvas">
                <div class="orgd-stage" id="orgStage"></div>

                <div class="orgd-empty" id="orgEmptyState" hidden>
                    <i class="fas fa-users-slash" aria-hidden="true"></i>
                    <strong id="orgEmptyTitle">No matching employees found</strong>
                    <span id="orgEmptyHint">Try selecting another designation or clearing your search filters.</span>
                </div>

                <div class="orgd-pan orgd-chart-only" aria-label="Canvas pan controls">
                    <button type="button" class="orgd-tool" data-pan-x="0" data-pan-y="90" title="Pan up" aria-label="Pan up">
                        <i class="fas fa-chevron-up" aria-hidden="true"></i>
                    </button>
                    <div>
                        <button type="button" class="orgd-tool" data-pan-x="90" data-pan-y="0" title="Pan left" aria-label="Pan left">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="orgd-tool" data-pan-x="-90" data-pan-y="0" title="Pan right" aria-label="Pan right">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                    <button type="button" class="orgd-tool" data-pan-x="0" data-pan-y="-90" title="Pan down" aria-label="Pan down">
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="orgd-legend orgd-chart-only" aria-label="Connector legend">
                    <span><i class="orgd-dot" aria-hidden="true"></i> Direct reporting manager</span>
                    <span><i class="orgd-dot is-level" aria-hidden="true"></i> Designation level hierarchy</span>
                </div>
            </div>

            <aside class="orgd-inspector" id="orgInspector" aria-label="Selected employee details" aria-hidden="true">
                <div class="orgd-inspector-head">
                    <span>Employee Details</span>
                    <button type="button" class="orgd-tool" id="orgCloseInspectorBtn" aria-label="Close employee details">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="orgd-inspector-body" id="orgInspectorBody"></div>
            </aside>
        </div>
    </section>
</div>

<script type="application/json" id="orgTreeInitialData">@json($treePayload)</script>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('orgTreeRoot');
    if (!root) return;

    const initialDataEl = document.getElementById('orgTreeInitialData');
    const viewport = document.getElementById('orgViewport');
    const stage = document.getElementById('orgStage');
    const emptyState = document.getElementById('orgEmptyState');
    const emptyTitle = document.getElementById('orgEmptyTitle');
    const emptyHint = document.getElementById('orgEmptyHint');
    const statusText = document.getElementById('orgStatusText');
    const zoomLabel = document.getElementById('orgZoomLabel');
    const searchInput = document.getElementById('orgSearchInput');
    const designationSelect = document.getElementById('orgDesignationSelect');
    const departmentSelect = document.getElementById('orgDepartmentSelect');
    const isolateBtn = document.getElementById('orgIsolateBtn');
    const resetFiltersBtn = document.getElementById('orgResetFiltersBtn');
    const refreshBtn = document.getElementById('orgRefreshBtn');
    const exportCsvBtn = document.getElementById('orgExportCsvBtn');
    const printBtn = document.getElementById('orgPrintBtn');
    const inspector = document.getElementById('orgInspector');
    const inspectorBody = document.getElementById('orgInspectorBody');
    const closeInspectorBtn = document.getElementById('orgCloseInspectorBtn');
    const designationChips = Array.from(document.querySelectorAll('[data-designation-chip]'));

    const MIN_SCALE = 0.2;
    const MAX_SCALE = 2.4;

    const state = {
        nodes: new Map(),
        children: new Map(),
        roots: [],
        company: { name: 'Organization', logo: null },
        collapsed: new Set(),
        selected: null,
        isolateBranch: false,
        x: 0,
        y: 0,
        scale: 1,
        userMoved: false,
        lastFocusBeforeInspector: null,
    };

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function icon(faClass) {
        const i = el('i', 'fas ' + faClass);
        i.setAttribute('aria-hidden', 'true');
        return i;
    }

    function display(val) {
        return val === null || val === undefined || val === '' ? '—' : String(val);
    }

    function levelBadge(node) {
        return node.level === null || node.level === undefined ? 'No level' : 'L' + node.level;
    }

    function isActiveStatus(node) {
        return String(node.status || '').toLowerCase() === 'active';
    }

    function createPhoto(node, extraClass) {
        const box = el('span', 'orgd-photo' + (extraClass ? ' ' + extraClass : ''));
        if (node.photo) {
            const img = document.createElement('img');
            img.src = node.photo;
            img.alt = '';
            img.loading = 'lazy';
            img.onerror = function () {
                img.remove();
                box.textContent = node.initials || '?';
            };
            box.appendChild(img);
        } else {
            box.textContent = node.initials || '?';
        }
        return box;
    }

    function hydratePayload(payload, shouldFit) {
        state.nodes.clear();
        state.children.clear();
        state.roots = [];
        state.company = payload && payload.company ? payload.company : { name: 'Organization', logo: null };

        const rawNodes = Array.isArray(payload && payload.nodes) ? payload.nodes : [];
        rawNodes.forEach(function (n) {
            state.nodes.set(n.id, n);
            state.children.set(n.id, []);
        });

        rawNodes.forEach(function (n) {
            if (n.parent && state.children.has(n.parent)) {
                state.children.get(n.parent).push(n.id);
            } else {
                state.roots.push(n.id);
            }
        });

        if (rawNodes.length > 40 && state.collapsed.size === 0) {
            const depthOf = function (id) {
                let d = 0, cur = state.nodes.get(id), guard = 0;
                while (cur && cur.parent && guard < 200) {
                    d++;
                    cur = state.nodes.get(cur.parent);
                    guard++;
                }
                return d;
            };
            rawNodes.forEach(function (n) {
                if (depthOf(n.id) >= 2 && (state.children.get(n.id) || []).length > 0) {
                    state.collapsed.add(n.id);
                }
            });
        }

        if (state.selected && !state.nodes.has(state.selected)) {
            state.selected = null;
            closeInspector(false);
        }

        const badge = document.getElementById('orgCompanyBadge');
        if (badge && state.company && state.company.name) {
            badge.textContent = state.company.name;
        }

        updateKpisFromNodes(rawNodes);
        renderAll(Boolean(shouldFit));
    }

    function updateKpisFromNodes(nodes) {
        const totalEl = document.getElementById('orgKpiTotal');
        const desigEl = document.getElementById('orgKpiDesignations');
        const deptEl = document.getElementById('orgKpiDepartments');
        const leadsEl = document.getElementById('orgKpiLeads');

        const desigSet = new Set();
        const deptSet = new Set();
        let leads = 0;

        nodes.forEach(function (n) {
            if (n.designation && n.designation !== 'No designation') {
                desigSet.add(String(n.designation).toLowerCase());
            }
            if (n.department) {
                deptSet.add(String(n.department).toLowerCase());
            }
            if (Number(n.directs || 0) > 0) {
                leads++;
            }
        });

        if (totalEl) totalEl.textContent = String(nodes.length);
        if (desigEl) desigEl.textContent = String(desigSet.size);
        if (deptEl) deptEl.textContent = String(deptSet.size);
        if (leadsEl) leadsEl.textContent = String(leads);
    }

    function computeFilterState() {
        const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const selectedDesignation = (designationSelect ? designationSelect.value : '').trim().toLowerCase();
        const selectedDepartment = (departmentSelect ? departmentSelect.value : '').trim().toLowerCase();
        const hasFilter = Boolean(q || selectedDesignation || selectedDepartment);

        const matches = new Set();
        const visibleSet = new Set();

        state.nodes.forEach(function (node) {
            if (!hasFilter) return;

            const designationText = String(node.designation || '').toLowerCase();
            const departmentText = String(node.department || '').toLowerCase();

            if (selectedDesignation && designationText !== selectedDesignation) {
                return;
            }
            if (selectedDepartment && departmentText !== selectedDepartment) {
                return;
            }
            if (q) {
                const haystack = [
                    node.designation,
                    node.name,
                    node.employee_code,
                    node.department,
                    node.email,
                    levelBadge(node),
                ].filter(Boolean).join(' ').toLowerCase();

                if (!haystack.includes(q)) {
                    return;
                }
            }

            matches.add(node.id);
        });

        if (hasFilter) {
            matches.forEach(function (id) {
                let cursor = state.nodes.get(id);
                let safety = 0;
                while (cursor && safety < 200) {
                    visibleSet.add(cursor.id);
                    cursor = cursor.parent ? state.nodes.get(cursor.parent) : null;
                    safety++;
                }
                // Include direct reports one hop down so branch context remains clear
                (state.children.get(id) || []).forEach(function (childId) {
                    visibleSet.add(childId);
                });
            });

            // Auto-expand ancestor branches to reveal matching designations/employees
            matches.forEach(function (id) {
                let cursor = state.nodes.get(id);
                let parentId = cursor ? cursor.parent : null;
                let safety = 0;
                while (parentId && safety < 200) {
                    state.collapsed.delete(parentId);
                    const pNode = state.nodes.get(parentId);
                    parentId = pNode ? pNode.parent : null;
                    safety++;
                }
            });
        }

        const pathIds = new Set();
        if (state.selected && state.nodes.has(state.selected)) {
            let cursor = state.nodes.get(state.selected);
            let safety = 0;
            while (cursor && safety < 200) {
                pathIds.add(cursor.id);
                cursor = cursor.parent ? state.nodes.get(cursor.parent) : null;
                safety++;
            }
        }

        return { q, selectedDesignation, selectedDepartment, hasFilter, matches, visibleSet, pathIds };
    }

    function syncChipSelection() {
        const currentDesig = (designationSelect ? designationSelect.value : '').trim().toLowerCase();
        designationChips.forEach(function (chip) {
            const chipVal = (chip.getAttribute('data-designation-chip') || '').trim().toLowerCase();
            chip.classList.toggle('is-active', chipVal === currentDesig);
        });
    }

    function renderAll(shouldFit) {
        const filterState = computeFilterState();
        syncChipSelection();

        renderTree(filterState);
        updateStatusSummary(filterState);

        if (shouldFit) {
            requestAnimationFrame(fitToScreen);
        }
    }

    function updateStatusSummary(filterState) {
        const total = state.nodes.size;
        if (!statusText) return;

        if (total === 0) {
            statusText.textContent = 'No active employee records found.';
            return;
        }

        if (!filterState.hasFilter) {
            statusText.textContent = 'Showing ' + total + ' active employee' + (total === 1 ? '' : 's') + ' across the organization hierarchy.';
            return;
        }

        const count = filterState.matches.size;
        const parts = [];
        if (filterState.selectedDesignation) parts.push('designation "' + designationSelect.value + '"');
        if (filterState.selectedDepartment) parts.push('department "' + departmentSelect.value + '"');
        if (filterState.q) parts.push('query "' + searchInput.value.trim() + '"');

        const filterDesc = parts.join(' & ');
        statusText.textContent = count > 0
            ? 'Matched ' + count + ' employee' + (count === 1 ? '' : 's') + ' for ' + filterDesc + (state.isolateBranch ? ' (isolated branch).' : '.')
            : 'No employees matched ' + filterDesc + '.';
    }

    function shouldRenderBranch(id, filterState) {
        if (!filterState.hasFilter || !state.isolateBranch) return true;
        return filterState.visibleSet.has(id);
    }

    function renderTree(filterState) {
        if (state.nodes.size === 0) {
            stage.replaceChildren();
            emptyTitle.textContent = 'No employees in tree';
            emptyHint.textContent = 'Active employees added in the Employee section will appear here automatically.';
            emptyState.hidden = false;
            return;
        }

        if (filterState.hasFilter && filterState.matches.size === 0) {
            stage.replaceChildren();
            emptyTitle.textContent = 'No matching designation or employee';
            emptyHint.textContent = 'Adjust your designation filter or search query, or click Reset to view the full organization tree.';
            emptyState.hidden = false;
            return;
        }

        emptyState.hidden = true;

        const visibleRoots = state.roots.filter(function (id) {
            return shouldRenderBranch(id, filterState);
        });

        const treeRoot = el('div', 'orgd-tree');
        const rootUl = el('ul');

        if (visibleRoots.length > 1) {
            const companyLi = el('li');
            const companyTile = el('div', 'orgd-tile is-company');
            const top = el('div', 'orgd-tile-top');
            const badge = createPhoto({ photo: state.company.logo, initials: (state.company.name || 'ORG').slice(0, 2).toUpperCase() }, 'is-company');
            const who = el('div', 'orgd-who');
            const title = el('div', 'orgd-name');
            title.appendChild(el('span', '', state.company.name || 'Organization'));
            const sub = el('p', 'orgd-designation', state.nodes.size + ' active employees');
            who.append(title, sub);
            top.append(badge, who);
            companyTile.appendChild(top);
            companyLi.appendChild(companyTile);

            const childrenUl = el('ul');
            visibleRoots.forEach(function (rootId) {
                childrenUl.appendChild(buildNodeLi(rootId, filterState));
            });
            companyLi.appendChild(childrenUl);
            rootUl.appendChild(companyLi);
        } else {
            visibleRoots.forEach(function (rootId) {
                rootUl.appendChild(buildNodeLi(rootId, filterState));
            });
        }

        treeRoot.appendChild(rootUl);
        stage.replaceChildren(treeRoot);
    }

    function buildNodeLi(id, filterState) {
        const node = state.nodes.get(id);
        const childIds = (state.children.get(id) || []).filter(function (cid) {
            return shouldRenderBranch(cid, filterState);
        });
        const isLeaf = childIds.length === 0;
        const isCollapsed = state.collapsed.has(id);

        const li = el('li');
        if (isLeaf) li.classList.add('is-leaf');
        if (isCollapsed && !isLeaf) li.classList.add('is-collapsed');
        if (node.placement === 'level') li.classList.add('is-level-placed');
        if (filterState.pathIds.has(id) && state.selected !== id) li.classList.add('is-path');

        const tile = el('div', 'orgd-tile');
        tile.tabIndex = 0;
        tile.setAttribute('role', 'button');
        tile.setAttribute('aria-label', node.name + ', ' + display(node.designation));
        if (state.selected === id) tile.classList.add('is-selected');
        if (filterState.pathIds.has(id) && state.selected !== id) tile.classList.add('is-path');
        if (filterState.hasFilter) {
            if (filterState.matches.has(id)) {
                tile.classList.add('is-match');
            } else if (!state.isolateBranch) {
                tile.classList.add('is-dim');
            }
        }

        const flag = el('span', 'orgd-tile-flag', state.selected === id ? 'Selected' : 'In Path');
        tile.appendChild(flag);

        const top = el('div', 'orgd-tile-top');
        top.appendChild(createPhoto(node));

        const who = el('div', 'orgd-who');
        const nameEl = el('div', 'orgd-name');
        nameEl.appendChild(el('span', '', node.name));
        if (isActiveStatus(node)) {
            nameEl.appendChild(icon('fa-circle-check'));
        }
        const desigEl = el('p', 'orgd-designation', display(node.designation));
        who.append(nameEl, desigEl);
        top.appendChild(who);
        tile.appendChild(top);

        const tags = el('div', 'orgd-tags');
        tags.appendChild(el('span', 'orgd-tag', levelBadge(node)));
        if (node.department) {
            tags.appendChild(el('span', 'orgd-tag is-dept', node.department));
        }
        tile.appendChild(tags);

        const foot = el('div', 'orgd-tile-foot');
        const codeSpan = el('span', '', node.employee_code || 'EMP');
        const teamSpan = document.createElement('span');
        teamSpan.appendChild(el('strong', '', String(node.directs || 0)));
        teamSpan.appendChild(document.createTextNode(' direct'));
        foot.append(codeSpan, teamSpan);
        tile.appendChild(foot);

        if (!isLeaf) {
            const toggleBtn = el('button', 'orgd-toggle');
            toggleBtn.type = 'button';
            toggleBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            toggleBtn.setAttribute('aria-label', (isCollapsed ? 'Expand ' : 'Collapse ') + node.name + ' team');
            toggleBtn.appendChild(icon('fa-chevron-down'));
            toggleBtn.addEventListener('click', function (ev) {
                ev.stopPropagation();
                if (state.collapsed.has(id)) {
                    state.collapsed.delete(id);
                } else {
                    state.collapsed.add(id);
                }
                renderAll(false);
            });
            tile.appendChild(toggleBtn);
        }

        tile.addEventListener('click', function () {
            selectNode(id, tile);
        });
        tile.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                selectNode(id, tile);
            }
        });

        li.appendChild(tile);

        if (!isLeaf) {
            const ul = el('ul');
            childIds.forEach(function (cid) {
                ul.appendChild(buildNodeLi(cid, filterState));
            });
            li.appendChild(ul);
        }

        return li;
    }

    function selectNode(id, triggerEl) {
        const node = state.nodes.get(id);
        if (!node) return;
        state.selected = id;
        state.lastFocusBeforeInspector = triggerEl || document.activeElement;
        renderAll(false);
        openInspector(node);
    }

    function openInspector(node) {
        inspectorBody.innerHTML = '';

        const hero = el('div', 'orgd-hero');
        hero.appendChild(createPhoto(node, 'is-large'));
        const h3 = el('h3');
        h3.appendChild(el('span', '', node.name));
        if (isActiveStatus(node)) h3.appendChild(icon('fa-circle-check'));
        hero.appendChild(h3);
        hero.appendChild(el('p', '', display(node.designation) + (node.department ? ' • ' + node.department : '')));

        const tags = el('div', 'orgd-tags');
        tags.appendChild(el('span', 'orgd-tag', levelBadge(node)));
        tags.appendChild(el('span', 'orgd-tag ' + (isActiveStatus(node) ? 'is-ok' : 'is-warn'), display(node.status || 'Active')));
        if (node.employee_code) {
            tags.appendChild(el('span', 'orgd-tag is-dept', node.employee_code));
        }
        hero.appendChild(tags);
        inspectorBody.appendChild(hero);

        const actions = el('div', 'orgd-actions');
        const profileLink = el('a', 'orgd-action' + (node.show_url ? '' : ' is-disabled'));
        profileLink.href = node.show_url || '#';
        profileLink.append(icon('fa-id-card'), el('span', '', 'View Profile'));

        const filterDesigBtn = el('button', 'orgd-action');
        filterDesigBtn.type = 'button';
        filterDesigBtn.append(icon('fa-filter'), el('span', '', 'Filter Same Role'));
        filterDesigBtn.addEventListener('click', function () {
            if (designationSelect && node.designation && node.designation !== 'No designation') {
                designationSelect.value = node.designation;
                state.isolateBranch = true;
                if (isolateBtn) isolateBtn.setAttribute('aria-pressed', 'true');
                renderAll(true);
            }
        });
        actions.append(profileLink, filterDesigBtn);
        inspectorBody.appendChild(actions);

        const statsSec = el('div', 'orgd-section');
        statsSec.appendChild(el('h4', '', 'Span of Control'));
        const statsGrid = el('div', 'orgd-stats');
        const directBox = document.createElement('div');
        directBox.append(el('strong', '', String(node.directs || 0)), el('span', '', 'Direct Reports'));
        const totalBox = document.createElement('div');
        totalBox.append(el('strong', '', String(node.total || 0)), el('span', '', 'Total Team'));
        statsGrid.append(directBox, totalBox);
        statsSec.appendChild(statsGrid);
        inspectorBody.appendChild(statsSec);

        if (node.parent && state.nodes.has(node.parent)) {
            const parentNode = state.nodes.get(node.parent);
            const mgrSec = el('div', 'orgd-section');
            mgrSec.appendChild(el('h4', '', 'Reports To'));
            mgrSec.appendChild(makePersonButton(parentNode));
            inspectorBody.appendChild(mgrSec);
        }

        const directIds = state.children.get(node.id) || [];
        const dirSec = el('div', 'orgd-section');
        dirSec.appendChild(el('h4', '', 'Direct Reports (' + directIds.length + ')'));
        if (directIds.length === 0) {
            dirSec.appendChild(el('p', 'orgd-note', 'Individual contributor with no direct reports.'));
        } else {
            directIds.forEach(function (cid) {
                const cNode = state.nodes.get(cid);
                if (cNode) dirSec.appendChild(makePersonButton(cNode));
            });
        }
        inspectorBody.appendChild(dirSec);

        const factsSec = el('div', 'orgd-section');
        factsSec.appendChild(el('h4', '', 'Employee Record Details'));
        const dl = el('dl', 'orgd-facts');
        [
            ['Email', node.email, true],
            ['Phone', node.mobile, false],
            ['Department', node.department, false],
            ['Parent Dept', node.parent_department, false],
            ['Joining Date', node.joining_date, false],
            ['Tenure', node.tenure, false],
            ['Employment', node.employment_type, false],
            ['Skills', node.skills, true],
        ].forEach(function (row) {
            const item = el('div', row[2] ? 'is-wide' : '');
            item.append(el('dt', '', row[0]), el('dd', '', display(row[1])));
            dl.appendChild(item);
        });
        factsSec.appendChild(dl);
        inspectorBody.appendChild(factsSec);

        inspector.classList.add('is-open');
        inspector.setAttribute('aria-hidden', 'false');
        closeInspectorBtn.focus();
    }

    function makePersonButton(person) {
        const btn = el('button', 'orgd-person');
        btn.type = 'button';
        btn.appendChild(createPhoto(person));
        const info = document.createElement('div');
        info.append(el('strong', '', person.name), el('small', '', display(person.designation)));
        btn.append(info, icon('fa-chevron-right'));
        btn.addEventListener('click', function () {
            selectNode(person.id, btn);
        });
        return btn;
    }

    function closeInspector(restoreFocus) {
        inspector.classList.remove('is-open');
        inspector.setAttribute('aria-hidden', 'true');
        if (restoreFocus && state.lastFocusBeforeInspector && typeof state.lastFocusBeforeInspector.focus === 'function') {
            state.lastFocusBeforeInspector.focus();
        }
    }

    /* ---------- Transform / Pan / Zoom ---------- */
    function applyTransform() {
        stage.style.transform = 'translate3d(' + state.x + 'px, ' + state.y + 'px, 0) scale(' + state.scale + ')';
        if (zoomLabel) {
            zoomLabel.textContent = Math.round(state.scale * 100) + '%';
        }
    }

    function fitToScreen() {
        const vw = viewport.clientWidth;
        const vh = viewport.clientHeight;
        const sw = stage.scrollWidth;
        const sh = stage.scrollHeight;
        if (!vw || !vh || !sw || !sh) return;

        const pad = 28;
        const nextScale = Math.max(MIN_SCALE, Math.min(1, (vw - pad * 2) / sw, (vh - pad * 2) / sh));
        state.scale = nextScale;
        state.x = Math.round((vw - sw * nextScale) / 2);
        state.y = Math.max(18, Math.round((vh - sh * nextScale) / 2));
        applyTransform();
    }

    function zoomBy(factor, cx, cy) {
        const rect = viewport.getBoundingClientRect();
        const px = cx !== undefined ? cx - rect.left : viewport.clientWidth / 2;
        const py = cy !== undefined ? cy - rect.top : viewport.clientHeight / 2;
        const nextScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, state.scale * factor));
        const ratio = nextScale / state.scale;
        state.x = px - (px - state.x) * ratio;
        state.y = py - (py - state.y) * ratio;
        state.scale = nextScale;
        state.userMoved = true;
        applyTransform();
    }

    /* ---------- Pointer Drag & Wheel ---------- */
    let drag = null;
    viewport.addEventListener('pointerdown', function (ev) {
        if (ev.button !== 0) return;
        if (ev.target.closest('button, .orgd-tile, .orgd-pan, .orgd-legend')) return;
        drag = { sx: ev.clientX, sy: ev.clientY, ox: state.x, oy: state.y };
        viewport.classList.add('is-dragging');
        viewport.setPointerCapture(ev.pointerId);
    });
    viewport.addEventListener('pointermove', function (ev) {
        if (!drag) return;
        state.x = drag.ox + (ev.clientX - drag.sx);
        state.y = drag.oy + (ev.clientY - drag.sy);
        state.userMoved = true;
        applyTransform();
    });
    function endDrag(ev) {
        if (!drag) return;
        drag = null;
        viewport.classList.remove('is-dragging');
        try { viewport.releasePointerCapture(ev.pointerId); } catch (e) {}
    }
    viewport.addEventListener('pointerup', endDrag);
    viewport.addEventListener('pointercancel', endDrag);

    viewport.addEventListener('wheel', function (ev) {
        if (ev.ctrlKey || ev.metaKey) {
            ev.preventDefault();
            zoomBy(ev.deltaY < 0 ? 1.12 : 0.89, ev.clientX, ev.clientY);
        } else {
            ev.preventDefault();
            state.x -= ev.deltaX;
            state.y -= ev.deltaY;
            state.userMoved = true;
            applyTransform();
        }
    }, { passive: false });

    /* ---------- Controls & Events ---------- */
    document.getElementById('orgExpandAllBtn').addEventListener('click', function () {
        state.collapsed.clear();
        renderAll(false);
    });
    document.getElementById('orgCollapseAllBtn').addEventListener('click', function () {
        state.nodes.forEach(function (n) {
            if ((state.children.get(n.id) || []).length > 0 && !state.roots.includes(n.id)) {
                state.collapsed.add(n.id);
            }
        });
        renderAll(true);
    });
    document.getElementById('orgZoomInBtn').addEventListener('click', function () { zoomBy(1.18); });
    document.getElementById('orgZoomOutBtn').addEventListener('click', function () { zoomBy(0.85); });
    document.getElementById('orgFitBtn').addEventListener('click', function () { state.userMoved = false; fitToScreen(); });
    document.getElementById('orgResetViewBtn').addEventListener('click', function () {
        state.scale = 1;
        state.userMoved = false;
        fitToScreen();
    });

    document.querySelectorAll('[data-pan-x]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.x += Number(btn.getAttribute('data-pan-x') || 0);
            state.y += Number(btn.getAttribute('data-pan-y') || 0);
            state.userMoved = true;
            applyTransform();
        });
    });

    let filterTimer = null;
    function scheduleFilterRender(shouldFit) {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function () {
            renderAll(Boolean(shouldFit));
        }, 120);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            scheduleFilterRender(false);
        });
    }
    if (designationSelect) {
        designationSelect.addEventListener('change', function () {
            renderAll(state.isolateBranch);
        });
    }
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            renderAll(state.isolateBranch);
        });
    }
    if (isolateBtn) {
        isolateBtn.addEventListener('click', function () {
            state.isolateBranch = !state.isolateBranch;
            isolateBtn.setAttribute('aria-pressed', state.isolateBranch ? 'true' : 'false');
            renderAll(true);
        });
    }

    designationChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const val = chip.getAttribute('data-designation-chip') || '';
            if (designationSelect) designationSelect.value = val;
            renderAll(state.isolateBranch);
        });
    });

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            if (designationSelect) designationSelect.value = '';
            if (departmentSelect) departmentSelect.value = '';
            state.isolateBranch = false;
            if (isolateBtn) isolateBtn.setAttribute('aria-pressed', 'false');
            state.selected = null;
            closeInspector(false);
            renderAll(true);
        });
    }

    if (closeInspectorBtn) {
        closeInspectorBtn.addEventListener('click', function () {
            closeInspector(true);
        });
    }

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && inspector.classList.contains('is-open')) {
            closeInspector(true);
        }
    });

    if (refreshBtn) {
        refreshBtn.addEventListener('click', async function () {
            refreshBtn.disabled = true;
            statusText.textContent = 'Refreshing employee hierarchy…';
            try {
                const resp = await fetch(root.dataset.endpoint, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const payload = await resp.json();
                hydratePayload(payload, !state.userMoved);
            } catch (err) {
                statusText.textContent = 'Could not refresh organization tree. Please try again.';
            } finally {
                refreshBtn.disabled = false;
            }
        });
    }

    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function () {
            const filterState = computeFilterState();
            const exportNodes = [];
            state.nodes.forEach(function (n) {
                if (filterState.hasFilter && state.isolateBranch && !filterState.visibleSet.has(n.id)) return;
                if (filterState.hasFilter && !state.isolateBranch && !filterState.matches.has(n.id)) return;
                exportNodes.push(n);
            });
            const header = ['Employee Code', 'Name', 'Designation', 'Level', 'Department', 'Reports To', 'Email', 'Direct Reports', 'Total Team', 'Status'];
            const lines = [header.map(function (c) { return '"' + c + '"'; }).join(',')];
            exportNodes.forEach(function (n) {
                const row = [
                    n.employee_code || '',
                    n.name || '',
                    n.designation || '',
                    levelBadge(n),
                    n.department || '',
                    n.reporting_to_name || 'Top Level',
                    n.email || '',
                    n.directs || 0,
                    n.total || 0,
                    n.status || 'Active',
                ];
                lines.push(row.map(function (val) {
                    return '"' + String(val).replace(/"/g, '""') + '"';
                }).join(','));
            });
            const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'organization_details_' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        });
    }

    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    // Boot immediately with initial server payload
    if (typeof window.ResizeObserver === 'function') {
        const ro = new window.ResizeObserver(function () {
            if (!state.userMoved && state.nodes.size > 0) {
                requestAnimationFrame(fitToScreen);
            }
        });
        ro.observe(viewport);
    }

    try {
        const parsed = initialDataEl ? JSON.parse(initialDataEl.textContent || '{}') : {};
        hydratePayload(parsed, true);
    } catch (e) {
        statusText.textContent = 'Unable to parse initial organization data.';
    }
})();
</script>
@endpush
