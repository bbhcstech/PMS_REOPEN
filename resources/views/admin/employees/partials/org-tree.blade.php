{{-- Employee organization tree (view switch + pan/zoom chart + member inspector). The list view below stays unchanged. --}}
<div class="eot-switch" role="tablist" aria-label="Employee view">
    <button type="button" class="eot-switch-tab is-active" role="tab" aria-selected="true" data-eot-view="list">
        <i class="fas fa-list" aria-hidden="true"></i><span>Employee List</span>
    </button>
    <button type="button" class="eot-switch-tab" role="tab" aria-selected="false" data-eot-view="tree">
        <i class="fas fa-sitemap" aria-hidden="true"></i><span>Org Tree</span>
    </button>
</div>

<section class="emp-orgtree" id="employeeOrgTree" data-live-preserve hidden
         data-endpoint="{{ route('employees.org-tree', array_filter(['company_id' => $selectedCompanyId ?? null])) }}"
         aria-label="Organization tree">
    <div class="eot-toolbar">
        <div class="eot-title">
            <span class="eot-title-mark"><i class="fas fa-sitemap" aria-hidden="true"></i></span>
            <div>
                <h3>Organization Tree</h3>
                <p id="eotSummary">Loading structure…</p>
            </div>
        </div>
        <div class="eot-controls">
            <label class="eot-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="search" id="eotSearch" placeholder="Find employee…" autocomplete="off" list="eotSearchList" aria-label="Find employee in tree">
                <datalist id="eotSearchList"></datalist>
            </label>
            <select id="eotDepartment" class="eot-select" aria-label="Highlight department">
                <option value="">All departments</option>
            </select>
            <div class="eot-group" role="group" aria-label="Zoom">
                <button type="button" class="eot-tool" data-eot-action="zoom-out" title="Zoom out"><i class="fas fa-minus" aria-hidden="true"></i></button>
                <span class="eot-zoom" id="eotZoom">100%</span>
                <button type="button" class="eot-tool" data-eot-action="zoom-in" title="Zoom in"><i class="fas fa-plus" aria-hidden="true"></i></button>
            </div>
            <div class="eot-group" role="group" aria-label="Layout">
                <button type="button" class="eot-tool" data-eot-action="fit" title="Fit to screen"><i class="fas fa-expand" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool" data-eot-action="center" title="Center on root"><i class="fas fa-crosshairs" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool" data-eot-action="expand" title="Expand all"><i class="fas fa-angles-down" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool" data-eot-action="collapse" title="Collapse all"><i class="fas fa-angles-up" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool" data-eot-action="refresh" title="Refresh tree"><i class="fas fa-rotate" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>

    <div class="eot-body">
        <div class="eot-viewport" id="eotViewport" tabindex="0" aria-label="Organization chart. Drag to move, scroll to slide, Ctrl + scroll to zoom.">
            <div class="eot-stage" id="eotStage"></div>
            <div class="eot-empty" id="eotEmpty" hidden>
                <i class="fas fa-users-slash" aria-hidden="true"></i>
                <strong>No employees to show yet</strong>
                <span>Add an employee with a designation and department and they will appear in the tree automatically.</span>
            </div>
            <div class="eot-pan" role="group" aria-label="Slide chart">
                <button type="button" class="eot-tool" data-eot-action="pan-up" title="Slide up"><i class="fas fa-chevron-up" aria-hidden="true"></i></button>
                <div>
                    <button type="button" class="eot-tool" data-eot-action="pan-left" title="Slide left"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" class="eot-tool" data-eot-action="pan-right" title="Slide right"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                </div>
                <button type="button" class="eot-tool" data-eot-action="pan-down" title="Slide down"><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
            </div>
            <div class="eot-legend">
                <span><i class="eot-dot is-reporting"></i>Reporting manager</span>
                <span><i class="eot-dot is-level"></i>Placed by designation level</span>
            </div>
        </div>

        <aside class="eot-inspector" id="eotInspector" aria-label="Member inspector" aria-hidden="true">
            <div class="eot-inspector-head">
                <span>Member Inspector</span>
                <button type="button" class="eot-tool" data-eot-action="close-inspector" title="Close"><i class="fas fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="eot-inspector-body" id="eotInspectorBody"></div>
        </aside>
    </div>
</section>

<style>
    .emp-orgtree, .eot-switch {
        --eot-surface: #ffffff;
        --eot-canvas: #f6f4fb;
        --eot-grid: rgba(107, 78, 158, .09);
        --eot-border: #e5e0f0;
        --eot-text: #1f2937;
        --eot-muted: #64748b;
        --eot-accent: #6b4e9e;
        --eot-accent-soft: #efe9fa;
        --eot-line: #c9bfdd;
        --eot-path: #8b5cf6;
        --eot-ok: #16a34a;
        --eot-ok-soft: #dcfce7;
        --eot-warn: #b45309;
        --eot-warn-soft: #fef3c7;
        --eot-shadow: 0 10px 30px rgba(31, 41, 55, .08);
    }
    html[data-pms-theme="dark"] :is(.emp-orgtree, .eot-switch),
    html[data-bs-theme="dark"] :is(.emp-orgtree, .eot-switch),
    html[data-theme="dark"] :is(.emp-orgtree, .eot-switch),
    html.dark :is(.emp-orgtree, .eot-switch),
    body[data-pms-theme="dark"] :is(.emp-orgtree, .eot-switch),
    body[data-bs-theme="dark"] :is(.emp-orgtree, .eot-switch),
    body.dark :is(.emp-orgtree, .eot-switch) {
        --eot-surface: #161b2e;
        --eot-canvas: #0f1424;
        --eot-grid: rgba(167, 139, 250, .08);
        --eot-border: #2a3150;
        --eot-text: #e5e7eb;
        --eot-muted: #9ca3af;
        --eot-accent: #a78bfa;
        --eot-accent-soft: rgba(167, 139, 250, .16);
        --eot-line: #3b4466;
        --eot-path: #a78bfa;
        --eot-ok: #4ade80;
        --eot-ok-soft: rgba(74, 222, 128, .14);
        --eot-warn: #fbbf24;
        --eot-warn-soft: rgba(251, 191, 36, .14);
        --eot-shadow: 0 10px 30px rgba(0, 0, 0, .35);
    }

    .employee-dashboard.eot-tree-mode .filter-export-card,
    .employee-dashboard.eot-tree-mode .table-card { display: none !important; }

    .eot-switch { display: inline-flex; gap: 4px; padding: 4px; margin: 0 0 16px; border-radius: 14px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); max-width: 100%; }
    .eot-switch .eot-switch-tab { display: inline-flex !important; align-items: center; gap: 8px; padding: 9px 16px !important; border: 0 !important; border-radius: 10px !important; background: transparent !important; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; font-weight: 600; font-size: 14px; cursor: pointer; width: auto !important; min-height: 0 !important; box-shadow: none !important; transition: background .2s, color .2s; }
    .eot-switch .eot-switch-tab i { color: inherit !important; -webkit-text-fill-color: currentColor !important; font-size: 13px; }
    .eot-switch .eot-switch-tab.is-active { background: var(--eot-accent) !important; color: #fff !important; -webkit-text-fill-color: #fff !important; }
    .eot-switch .eot-switch-tab:focus-visible { outline: 2px solid var(--eot-accent); outline-offset: 2px; }

    .emp-orgtree { margin-bottom: 24px; border-radius: 22px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); overflow: hidden; color: var(--eot-text); }
    .emp-orgtree[hidden] { display: none !important; }
    .emp-orgtree i { font-style: normal; }
    .eot-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--eot-border); }
    .eot-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .eot-title-mark { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; background: var(--eot-accent-soft); flex-shrink: 0; }
    .eot-title-mark i { color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; font-size: 18px; }
    .eot-title h3 { margin: 0; font-size: 18px; font-weight: 700; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-title p { margin: 2px 0 0; font-size: 13px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-controls { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .eot-search { position: relative; display: flex; align-items: center; margin: 0; }
    .eot-search i { position: absolute; left: 12px; font-size: 13px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; pointer-events: none; }
    .emp-orgtree .eot-search input, .emp-orgtree .eot-select { height: 38px; border-radius: 10px !important; border: 1px solid var(--eot-border) !important; background: var(--eot-canvas) !important; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; font-size: 13px; outline: none; box-shadow: none !important; }
    .emp-orgtree .eot-search input { width: 200px; padding: 0 12px 0 34px !important; }
    .emp-orgtree .eot-select { padding: 0 10px !important; max-width: 200px; }
    .emp-orgtree .eot-search input:focus, .emp-orgtree .eot-select:focus { border-color: var(--eot-accent) !important; }
    .eot-group { display: inline-flex; align-items: center; gap: 2px; padding: 3px; border-radius: 12px; border: 1px solid var(--eot-border); background: var(--eot-canvas); }
    .emp-orgtree .eot-tool { width: 32px !important; height: 32px !important; min-width: 32px !important; min-height: 0 !important; padding: 0 !important; display: inline-grid !important; place-items: center; border: 0 !important; border-radius: 8px !important; background: transparent !important; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; cursor: pointer; box-shadow: none !important; flex: 0 0 auto; }
    .emp-orgtree .eot-tool i { color: inherit !important; -webkit-text-fill-color: currentColor !important; font-size: 13px; }
    .emp-orgtree .eot-tool:hover { background: var(--eot-accent-soft) !important; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; }
    .emp-orgtree .eot-tool:focus-visible { outline: 2px solid var(--eot-accent); outline-offset: 1px; }
    .eot-zoom { min-width: 46px; text-align: center; font-size: 12px; font-weight: 700; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }

    .eot-body { position: relative; display: flex; }
    .eot-viewport { position: relative; flex: 1; height: min(72vh, 760px); min-height: 460px; overflow: hidden; cursor: grab; touch-action: none; user-select: none; outline: none;
        background-color: var(--eot-canvas); background-image: radial-gradient(var(--eot-grid) 1.2px, transparent 1.2px); background-size: 22px 22px; }
    .eot-viewport.is-dragging { cursor: grabbing; }
    .eot-stage { position: absolute; left: 0; top: 0; width: max-content; transform-origin: 0 0; padding: 40px 40px 60px; will-change: transform; }
    .eot-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 24px; text-align: center; color: var(--eot-muted) !important; }
    .eot-empty[hidden] { display: none; }
    .eot-empty i { font-size: 34px; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; }
    .eot-empty strong { color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-empty span { max-width: 360px; font-size: 13px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-pan { position: absolute; left: 16px; bottom: 16px; display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px; border-radius: 12px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); }
    .eot-pan > div { display: flex; gap: 30px; }
    .eot-legend { position: absolute; right: 16px; bottom: 16px; display: flex; flex-wrap: wrap; gap: 12px; padding: 8px 12px; border-radius: 10px; background: var(--eot-surface); border: 1px solid var(--eot-border); font-size: 12px; color: var(--eot-muted); }
    .eot-legend span { display: inline-flex; align-items: center; gap: 6px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-dot { width: 18px; height: 0; border-top: 2px solid var(--eot-line); display: inline-block; }
    .eot-dot.is-level { border-top-style: dashed; }

    /* Tree connectors */
    .eot-tree, .eot-tree ul { margin: 0; padding: 0; }
    .eot-tree ul { display: flex; justify-content: center; position: relative; padding-top: 26px; }
    .eot-tree li { list-style: none; flex-shrink: 0; position: relative; display: flex; flex-direction: column; align-items: center; padding: 26px 10px 0; }
    .eot-tree li::before, .eot-tree li::after { content: ''; position: absolute; top: 0; right: 50%; width: 50%; height: 26px; border-top: 2px solid var(--eot-line); }
    .eot-tree li::after { right: auto; left: 50%; border-left: 2px solid var(--eot-line); }
    .eot-tree li:only-child::before, .eot-tree li:only-child::after { display: none; }
    .eot-tree li:only-child { padding-top: 0; }
    .eot-tree li:first-child::before, .eot-tree li:last-child::after { border: 0 none; }
    .eot-tree li:last-child::before { border-right: 2px solid var(--eot-line); border-radius: 0 10px 0 0; }
    .eot-tree li:first-child::after { border-radius: 10px 0 0 0; }
    .eot-tree ul ul::before { content: ''; position: absolute; top: 0; left: 50%; height: 26px; border-left: 2px solid var(--eot-line); }
    .eot-tree > ul { padding-top: 0; }
    .eot-tree li.is-level-placed::after, .eot-tree li.is-level-placed:last-child::before { border-style: dashed; }
    .eot-tree li.is-path::after { border-left-color: var(--eot-path); }
    .eot-tree li.is-path:last-child::before { border-right-color: var(--eot-path); }
    .eot-tree li.is-path > ul::before { border-left-color: var(--eot-path); }
    .eot-tree li.is-collapsed > ul { display: none; }

    /* Member tiles */
    .eot-tile { position: relative; width: 236px; padding: 14px 14px 12px; border-radius: 16px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); cursor: pointer; text-align: left; transition: border-color .2s, box-shadow .2s, transform .2s, opacity .2s; }
    .eot-tile:hover { border-color: var(--eot-accent); transform: translateY(-2px); }
    .eot-tile.is-selected { border-color: var(--eot-accent); box-shadow: 0 0 0 3px var(--eot-accent-soft), var(--eot-shadow); }
    .eot-tile.is-path { border-color: var(--eot-path); }
    .eot-tile.is-dim { opacity: .35; }
    .eot-tile.is-match { box-shadow: 0 0 0 3px var(--eot-warn-soft), var(--eot-shadow); border-color: var(--eot-warn); }
    .eot-tile-flag { position: absolute; top: -10px; left: 14px; padding: 2px 8px; border-radius: 999px; background: var(--eot-path); color: #fff !important; -webkit-text-fill-color: #fff !important; font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; display: none; }
    .eot-tile.is-path .eot-tile-flag, .eot-tile.is-selected .eot-tile-flag { display: inline-block; }
    .eot-tile-top { display: flex; align-items: center; gap: 10px; }
    .eot-photo { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; overflow: hidden; display: grid; place-items: center; background: linear-gradient(135deg, #8b5cf6, #6b4e9e); color: #fff !important; -webkit-text-fill-color: #fff !important; font-weight: 700; font-size: 15px; }
    .eot-photo img { width: 100% !important; height: 100% !important; max-width: none !important; object-fit: cover; display: block; }
    .eot-photo.is-large { width: 72px; height: 72px; font-size: 24px; }
    .eot-photo.is-company { border-radius: 12px; }
    .eot-who { min-width: 0; flex: 1; }
    .eot-name { display: flex; align-items: center; gap: 5px; margin: 0; font-size: 14px; font-weight: 700; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .eot-name > span { overflow: hidden; text-overflow: ellipsis; }
    .eot-name i { color: #3b82f6 !important; -webkit-text-fill-color: #3b82f6 !important; font-size: 12px; flex-shrink: 0; }
    .eot-role { margin: 2px 0 0; font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .eot-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
    .eot-tag { display: inline-flex; align-items: center; gap: 4px; max-width: 100%; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: var(--eot-accent-soft); color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .eot-tag.is-dept { background: var(--eot-canvas); color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; border: 1px solid var(--eot-border); }
    .eot-tag.is-ok { background: var(--eot-ok-soft); color: var(--eot-ok) !important; -webkit-text-fill-color: var(--eot-ok) !important; }
    .eot-tag.is-warn { background: var(--eot-warn-soft); color: var(--eot-warn) !important; -webkit-text-fill-color: var(--eot-warn) !important; }
    .eot-tile-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--eot-border); font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-tile-foot strong { color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .emp-orgtree .eot-toggle { position: absolute; left: 50%; bottom: -13px; transform: translateX(-50%); width: 26px !important; height: 26px !important; min-height: 0 !important; padding: 0 !important; border-radius: 50% !important; border: 1px solid var(--eot-border) !important; background: var(--eot-surface) !important; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; display: grid !important; place-items: center; cursor: pointer; z-index: 2; box-shadow: var(--eot-shadow) !important; font-size: 11px; font-weight: 700; }
    .emp-orgtree .eot-toggle i { color: inherit !important; -webkit-text-fill-color: currentColor !important; font-size: 11px; transition: transform .2s; }
    .eot-tree li.is-collapsed > .eot-tile .eot-toggle i { transform: rotate(-90deg); }
    .eot-tree li > .eot-tile { margin-bottom: 14px; }
    .eot-tree li.is-leaf > .eot-tile { margin-bottom: 0; }
    .eot-tile.is-company { width: 260px; background: linear-gradient(135deg, var(--eot-accent-soft), var(--eot-surface)); }

    /* Inspector */
    .eot-inspector { position: absolute; top: 0; right: 0; bottom: 0; width: 380px; max-width: 100%; display: flex; flex-direction: column; background: var(--eot-surface); border-left: 1px solid var(--eot-border); box-shadow: -12px 0 30px rgba(0, 0, 0, .12); transform: translateX(105%); visibility: hidden; transition: transform .28s ease, visibility 0s linear .28s; z-index: 5; }
    .eot-inspector.is-open { transform: translateX(0); visibility: visible; transition: transform .28s ease; }
    .eot-inspector-head { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--eot-border); font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-inspector-head span { color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-inspector-body { flex: 1; overflow-y: auto; padding: 18px 16px 22px; overscroll-behavior: contain; }
    .eot-hero { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px; }
    .eot-hero h4 { margin: 6px 0 0; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 6px; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-hero h4 i { color: #3b82f6 !important; -webkit-text-fill-color: #3b82f6 !important; font-size: 14px; }
    .eot-hero p { margin: 0; font-size: 13px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-hero .eot-tags { justify-content: center; margin-top: 4px; }
    .eot-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin: 16px 0; }
    .emp-orgtree .eot-action { display: flex !important; flex-direction: column; align-items: center; gap: 4px; padding: 10px 4px !important; border-radius: 12px !important; border: 1px solid var(--eot-border) !important; background: var(--eot-canvas) !important; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; font-size: 12px; font-weight: 600; text-decoration: none !important; cursor: pointer; width: auto !important; min-height: 0 !important; box-shadow: none !important; }
    .emp-orgtree .eot-action i { color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; font-size: 15px; }
    .emp-orgtree .eot-action:hover { border-color: var(--eot-accent) !important; }
    .emp-orgtree .eot-action.is-disabled { opacity: .45; pointer-events: none; }
    .eot-section { margin-top: 16px; }
    .eot-section h5 { margin: 0 0 8px; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-person { display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 10px !important; border-radius: 12px !important; border: 1px solid var(--eot-border) !important; background: var(--eot-canvas) !important; text-align: left; cursor: pointer; min-height: 0 !important; box-shadow: none !important; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-person + .eot-person { margin-top: 6px; }
    .eot-person:hover { border-color: var(--eot-accent) !important; }
    .eot-person .eot-photo { width: 34px; height: 34px; font-size: 12px; }
    .eot-person strong { display: block; font-size: 13px; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-person small { display: block; font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-person > i { margin-left: auto; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; font-size: 11px; }
    .eot-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .eot-stats div { padding: 12px; border-radius: 12px; background: var(--eot-accent-soft); text-align: center; }
    .eot-stats strong { display: block; font-size: 22px; font-weight: 800; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; }
    .eot-stats span { font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-facts { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 0; }
    .eot-facts div { min-width: 0; padding: 10px; border-radius: 12px; border: 1px solid var(--eot-border); }
    .eot-facts div.is-wide { grid-column: 1 / -1; }
    .eot-facts dt { font-size: 11px; font-weight: 600; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-facts dd { margin: 2px 0 0; font-size: 13px; font-weight: 600; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; overflow-wrap: anywhere; }
    .eot-exports { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .eot-note { font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; margin: 0; }

    @media (max-width: 991.98px) {
        .eot-inspector { position: fixed; top: 0; bottom: 0; right: 0; width: min(100vw, 400px); z-index: 1065; }
        .eot-legend { display: none; }
    }
    @media (max-width: 575.98px) {
        .eot-switch { display: flex; }
        .eot-switch .eot-switch-tab { flex: 1; justify-content: center; padding: 9px 10px !important; }
        .eot-toolbar { padding: 14px 16px; }
        .eot-controls, .eot-search, .emp-orgtree .eot-search input, .emp-orgtree .eot-select { width: 100%; max-width: none; }
        .eot-viewport { height: 64vh; min-height: 380px; }
        .eot-pan { left: 10px; bottom: 10px; }
    }
    @media print {
        .eot-switch, .emp-orgtree { display: none !important; }
    }
</style>

@push('js')
<script>
(function () {
    const root = document.getElementById('employeeOrgTree');
    if (!root) return;
    const dashboard = root.closest('.employee-dashboard') || document.body;
    const tabs = Array.from(document.querySelectorAll('.eot-switch [data-eot-view]'));
    const viewport = document.getElementById('eotViewport');
    const stage = document.getElementById('eotStage');
    const emptyState = document.getElementById('eotEmpty');
    const summary = document.getElementById('eotSummary');
    const zoomLabel = document.getElementById('eotZoom');
    const searchInput = document.getElementById('eotSearch');
    const searchList = document.getElementById('eotSearchList');
    const departmentSelect = document.getElementById('eotDepartment');
    const inspector = document.getElementById('eotInspector');
    const inspectorBody = document.getElementById('eotInspectorBody');
    const VIEW_KEY = 'pmsEmployeeView';
    const MIN_SCALE = 0.2, MAX_SCALE = 2.5;

    const state = { nodes: new Map(), children: new Map(), roots: [], company: null, collapsed: new Set(), selected: null, loaded: false, loading: false, x: 0, y: 0, scale: 1 };

    const storage = {
        get(key) { try { return window.localStorage.getItem(key); } catch (e) { return null; } },
        set(key, value) { try { window.localStorage.setItem(key, value); } catch (e) {} },
    };

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }
    function icon(name) { const i = el('i', 'fas ' + name); i.setAttribute('aria-hidden', 'true'); return i; }
    function display(value) { return value === null || value === undefined || value === '' ? '—' : String(value); }
    function levelLabel(node) { return node.level === null || node.level === undefined ? 'No level' : 'L' + node.level; }
    function isActive(node) { return String(node.status || '').toLowerCase() === 'active'; }

    function photo(node, extra) {
        const box = el('span', 'eot-photo' + (extra ? ' ' + extra : ''));
        if (node.photo) {
            const img = document.createElement('img');
            img.src = node.photo; img.alt = ''; img.loading = 'lazy';
            img.onerror = () => { img.remove(); box.textContent = node.initials || '?'; };
            box.appendChild(img);
        } else {
            box.textContent = node.initials || '?';
        }
        return box;
    }

    /* ---------- View switch ---------- */
    function setView(view, persist) {
        const tree = view === 'tree';
        dashboard.classList.toggle('eot-tree-mode', tree);
        root.hidden = !tree;
        tabs.forEach(tab => {
            const active = tab.dataset.eotView === view;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        if (persist) storage.set(VIEW_KEY, view);
        if (tree) {
            if (!state.loaded) load(true); else requestAnimationFrame(() => { if (!state.userMoved) fit(); });
        } else {
            closeInspector();
        }
    }
    tabs.forEach(tab => tab.addEventListener('click', () => setView(tab.dataset.eotView, true)));

    /* ---------- Data ---------- */
    async function load(fitAfter) {
        if (state.loading) return;
        state.loading = true;
        if (!state.loaded) summary.textContent = 'Loading structure…';
        try {
            const response = await fetch(root.dataset.endpoint, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            ingest(data);
            const firstLoad = !state.loaded;
            state.loaded = true;
            render();
            if (firstLoad || fitAfter) requestAnimationFrame(fit);
            if (state.selected && state.nodes.has(state.selected)) openInspector(state.selected, false);
            else if (state.selected) closeInspector();
        } catch (error) {
            summary.textContent = 'Unable to load the organization tree. Please refresh and try again.';
        } finally {
            state.loading = false;
        }
    }

    function ingest(data) {
        state.company = data.company || { name: 'Organization' };
        state.nodes = new Map();
        state.children = new Map();
        (data.nodes || []).forEach(node => state.nodes.set(node.id, node));
        state.nodes.forEach(node => {
            const parent = node.parent !== null && state.nodes.has(node.parent) ? node.parent : null;
            node.parent = parent;
            if (!state.children.has(parent)) state.children.set(parent, []);
            state.children.get(parent).push(node.id);
        });
        state.children.forEach(list => list.sort((a, b) => {
            const na = state.nodes.get(a), nb = state.nodes.get(b);
            const la = na.level ?? 999, lb = nb.level ?? 999;
            return la - lb || String(na.department || '').localeCompare(String(nb.department || '')) || na.name.localeCompare(nb.name);
        }));
        state.roots = state.children.get(null) || [];
        if (!state.initialCollapse) {
            state.initialCollapse = true;
            // Large organizations open three levels deep; everything stays one click away.
            if (state.nodes.size > 40) state.nodes.forEach(node => { if (depthOf(node.id) >= 2 && (state.children.get(node.id) || []).length) state.collapsed.add(node.id); });
        }
        const departments = new Map();
        state.nodes.forEach(node => { if (node.department_id) departments.set(String(node.department_id), node.department); });
        const current = departmentSelect.value;
        departmentSelect.replaceChildren(new Option('All departments', ''));
        Array.from(departments.entries()).sort((a, b) => String(a[1]).localeCompare(String(b[1]))).forEach(([id, name]) => departmentSelect.add(new Option(name, id)));
        departmentSelect.value = departments.has(current) ? current : '';
        searchList.replaceChildren(...Array.from(state.nodes.values()).map(node => new Option(node.name + (node.designation ? ' — ' + node.designation : ''), node.name)));
        const roots = state.roots.length;
        summary.textContent = state.nodes.size + ' employee' + (state.nodes.size === 1 ? '' : 's') + ' · ' + departments.size + ' department' + (departments.size === 1 ? '' : 's') + ' · ' + (roots > 1 ? roots + ' top-level leads' : 'single reporting line');
    }

    function depthOf(id) {
        let depth = 0, current = state.nodes.get(id)?.parent ?? null;
        while (current !== null && depth < 500) { depth++; current = state.nodes.get(current)?.parent ?? null; }
        return depth;
    }
    function ancestors(id) {
        const list = [];
        let current = state.nodes.get(id)?.parent ?? null;
        while (current !== null && list.length < 500) { list.push(current); current = state.nodes.get(current)?.parent ?? null; }
        return list;
    }

    /* ---------- Rendering ---------- */
    function tile(node) {
        const card = el('div', 'eot-tile');
        card.dataset.id = node.id;
        card.tabIndex = 0;
        card.setAttribute('role', 'button');
        card.setAttribute('aria-label', node.name + (node.designation ? ', ' + node.designation : ''));
        card.appendChild(el('span', 'eot-tile-flag', 'Active branch'));
        const top = el('div', 'eot-tile-top');
        top.appendChild(photo(node));
        const who = el('div', 'eot-who');
        const name = el('p', 'eot-name');
        name.appendChild(el('span', '', node.name));
        if (isActive(node)) { const badge = icon('fa-circle-check'); badge.title = 'Active employee'; name.appendChild(badge); }
        who.appendChild(name);
        who.appendChild(el('p', 'eot-role', node.designation || 'No designation'));
        top.appendChild(who);
        card.appendChild(top);
        const tags = el('div', 'eot-tags');
        tags.appendChild(el('span', 'eot-tag', levelLabel(node)));
        tags.appendChild(el('span', 'eot-tag is-dept', node.department || 'No department'));
        if (!isActive(node)) tags.appendChild(el('span', 'eot-tag is-warn', display(node.status)));
        card.appendChild(tags);
        const foot = el('div', 'eot-tile-foot');
        const counts = el('span');
        counts.appendChild(el('strong', '', node.directs)); counts.appendChild(document.createTextNode(' Directs · '));
        counts.appendChild(el('strong', '', node.total)); counts.appendChild(document.createTextNode(' Total'));
        foot.appendChild(counts);
        foot.appendChild(el('span', '', node.employee_code || ''));
        card.appendChild(foot);
        if ((state.children.get(node.id) || []).length) {
            const toggle = el('button', 'eot-toggle');
            toggle.type = 'button';
            toggle.dataset.toggle = node.id;
            toggle.title = state.collapsed.has(node.id) ? 'Expand team' : 'Collapse team';
            toggle.setAttribute('aria-expanded', state.collapsed.has(node.id) ? 'false' : 'true');
            toggle.appendChild(icon('fa-chevron-down'));
            card.appendChild(toggle);
        }
        return card;
    }

    function branch(id) {
        const node = state.nodes.get(id);
        const li = el('li');
        li.dataset.node = id;
        if (node.placement === 'level') li.classList.add('is-level-placed');
        const kids = state.children.get(id) || [];
        if (!kids.length) li.classList.add('is-leaf');
        if (state.collapsed.has(id)) li.classList.add('is-collapsed');
        li.appendChild(tile(node));
        if (kids.length) {
            const ul = el('ul');
            kids.forEach(kid => ul.appendChild(branch(kid)));
            li.appendChild(ul);
        }
        return li;
    }

    function companyTile() {
        const card = el('div', 'eot-tile is-company');
        const top = el('div', 'eot-tile-top');
        const mark = el('span', 'eot-photo is-company');
        if (state.company.logo) { const img = document.createElement('img'); img.src = state.company.logo; img.alt = ''; img.onerror = () => { img.remove(); mark.textContent = (state.company.name || 'O').slice(0, 2).toUpperCase(); }; mark.appendChild(img); }
        else mark.textContent = (state.company.name || 'O').slice(0, 2).toUpperCase();
        top.appendChild(mark);
        const who = el('div', 'eot-who');
        who.appendChild(el('p', 'eot-name', state.company.name || 'Organization'));
        who.appendChild(el('p', 'eot-role', 'Organization'));
        top.appendChild(who);
        card.appendChild(top);
        const foot = el('div', 'eot-tile-foot');
        const counts = el('span');
        counts.appendChild(el('strong', '', state.roots.length)); counts.appendChild(document.createTextNode(' Leads · '));
        counts.appendChild(el('strong', '', state.nodes.size)); counts.appendChild(document.createTextNode(' Total'));
        foot.appendChild(counts);
        card.appendChild(foot);
        return card;
    }

    function render() {
        const tree = el('div', 'eot-tree');
        const top = el('ul');
        if (state.roots.length > 1) {
            const li = el('li');
            li.appendChild(companyTile());
            const ul = el('ul');
            state.roots.forEach(id => ul.appendChild(branch(id)));
            li.appendChild(ul);
            top.appendChild(li);
        } else {
            state.roots.forEach(id => top.appendChild(branch(id)));
        }
        tree.appendChild(top);
        stage.replaceChildren(tree);
        emptyState.hidden = state.nodes.size > 0;
        paintHighlights();
    }

    function paintHighlights() {
        const path = new Set(state.selected ? ancestors(state.selected) : []);
        const dept = departmentSelect.value;
        const query = searchInput.value.trim().toLowerCase();
        stage.querySelectorAll('.eot-tile[data-id]').forEach(card => {
            const id = Number(card.dataset.id);
            const node = state.nodes.get(id);
            card.classList.toggle('is-selected', id === state.selected);
            card.classList.toggle('is-path', path.has(id));
            card.classList.toggle('is-dim', !!dept && String(node.department_id) !== dept);
            card.classList.toggle('is-match', !!query && query.length > 1 && node.name.toLowerCase().includes(query));
        });
        stage.querySelectorAll('li[data-node]').forEach(li => {
            const id = Number(li.dataset.node);
            li.classList.toggle('is-path', path.has(id) || id === state.selected);
        });
    }

    /* ---------- Pan & zoom ---------- */
    function apply() {
        stage.style.transform = 'translate(' + state.x + 'px,' + state.y + 'px) scale(' + state.scale + ')';
        zoomLabel.textContent = Math.round(state.scale * 100) + '%';
    }
    function zoomAt(nextScale, px, py) {
        const scale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, nextScale));
        if (px === undefined) { px = viewport.clientWidth / 2; py = viewport.clientHeight / 2; }
        state.x = px - (px - state.x) * (scale / state.scale);
        state.y = py - (py - state.y) * (scale / state.scale);
        state.scale = scale;
        state.userMoved = true;
        apply();
    }
    function fit() {
        const width = stage.offsetWidth, height = stage.offsetHeight;
        const vw = viewport.clientWidth, vh = viewport.clientHeight;
        if (!width || !height || !vw || !vh) return;
        const scale = Math.min(1, Math.max(MIN_SCALE, Math.min(vw / width, vh / height)));
        state.scale = scale;
        state.x = (vw - width * scale) / 2;
        state.y = Math.max(0, (vh - height * scale) / 2);
        state.userMoved = false;
        apply();
    }
    function centerOn(id, smooth) {
        const card = stage.querySelector('.eot-tile[data-id="' + id + '"]');
        if (!card) return;
        let left = 0, top = 0, current = card;
        while (current && current !== stage) { left += current.offsetLeft; top += current.offsetTop; current = current.offsetParent; }
        const panel = inspector.classList.contains('is-open') && window.innerWidth >= 992 ? inspector.offsetWidth : 0;
        const vw = viewport.clientWidth - panel;
        state.x = vw / 2 - (left + card.offsetWidth / 2) * state.scale;
        state.y = viewport.clientHeight / 3 - (top + card.offsetHeight / 2) * state.scale;
        state.userMoved = true;
        if (smooth) { stage.style.transition = 'transform .35s ease'; setTimeout(() => { stage.style.transition = ''; }, 380); }
        apply();
    }
    function pan(dx, dy) { state.x += dx; state.y += dy; state.userMoved = true; stage.style.transition = 'transform .2s ease'; setTimeout(() => { stage.style.transition = ''; }, 220); apply(); }

    const pointers = new Map();
    let drag = null, pinch = null, suppressClick = false;
    viewport.addEventListener('pointerdown', event => {
        if (event.button !== 0 || event.target.closest('button, a, input, select, .eot-pan, .eot-legend')) return;
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        if (pointers.size === 1) { drag = { x: event.clientX, y: event.clientY, ox: state.x, oy: state.y, moved: false }; }
        if (pointers.size === 2) {
            const [a, b] = Array.from(pointers.values());
            pinch = { distance: Math.hypot(a.x - b.x, a.y - b.y), scale: state.scale };
            drag = null;
        }
    });
    viewport.addEventListener('pointermove', event => {
        if (!pointers.has(event.pointerId)) return;
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        if (pinch && pointers.size === 2) {
            const [a, b] = Array.from(pointers.values());
            const rect = viewport.getBoundingClientRect();
            zoomAt(pinch.scale * Math.hypot(a.x - b.x, a.y - b.y) / pinch.distance, (a.x + b.x) / 2 - rect.left, (a.y + b.y) / 2 - rect.top);
            suppressClick = true;
            return;
        }
        if (!drag) return;
        const dx = event.clientX - drag.x, dy = event.clientY - drag.y;
        if (!drag.moved && Math.hypot(dx, dy) < 5) return;
        if (!drag.moved) { try { viewport.setPointerCapture(event.pointerId); } catch (e) {} }
        drag.moved = true; suppressClick = true;
        viewport.classList.add('is-dragging');
        state.x = drag.ox + dx; state.y = drag.oy + dy; state.userMoved = true;
        apply();
    });
    function endPointer(event) {
        pointers.delete(event.pointerId);
        if (pointers.size < 2) pinch = null;
        if (!pointers.size) { drag = null; viewport.classList.remove('is-dragging'); setTimeout(() => { suppressClick = false; }, 0); }
    }
    viewport.addEventListener('pointerup', endPointer);
    viewport.addEventListener('pointercancel', endPointer);
    viewport.addEventListener('wheel', event => {
        event.preventDefault();
        const rect = viewport.getBoundingClientRect();
        if (event.ctrlKey || event.metaKey) {
            zoomAt(state.scale * Math.exp(-event.deltaY * 0.0015), event.clientX - rect.left, event.clientY - rect.top);
        } else {
            state.x -= event.shiftKey ? event.deltaY : event.deltaX;
            state.y -= event.shiftKey ? 0 : event.deltaY;
            state.userMoved = true;
            apply();
        }
    }, { passive: false });
    viewport.addEventListener('keydown', event => {
        const step = 80;
        const map = { ArrowUp: [0, step], ArrowDown: [0, -step], ArrowLeft: [step, 0], ArrowRight: [-step, 0] };
        if (map[event.key] && !event.target.closest('.eot-tile')) { event.preventDefault(); pan(map[event.key][0], map[event.key][1]); }
        if (event.key === '+' || event.key === '=') { event.preventDefault(); zoomAt(state.scale * 1.2); }
        if (event.key === '-') { event.preventDefault(); zoomAt(state.scale / 1.2); }
    });

    /* ---------- Interaction ---------- */
    stage.addEventListener('click', event => {
        if (suppressClick) return;
        const toggle = event.target.closest('[data-toggle]');
        if (toggle) { toggleBranch(Number(toggle.dataset.toggle)); return; }
        const card = event.target.closest('.eot-tile[data-id]');
        if (card) openInspector(Number(card.dataset.id), true);
    });
    stage.addEventListener('keydown', event => {
        const card = event.target.closest('.eot-tile[data-id]');
        if (card && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); openInspector(Number(card.dataset.id), true); }
    });

    function toggleBranch(id, forceOpen) {
        if (forceOpen === true) state.collapsed.delete(id);
        else if (state.collapsed.has(id)) state.collapsed.delete(id);
        else state.collapsed.add(id);
        const li = stage.querySelector('li[data-node="' + id + '"]');
        if (!li) return;
        const collapsed = state.collapsed.has(id);
        li.classList.toggle('is-collapsed', collapsed);
        const toggle = li.querySelector(':scope > .eot-tile [data-toggle]');
        if (toggle) { toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true'); toggle.title = collapsed ? 'Expand team' : 'Collapse team'; }
    }
    function reveal(id) {
        let changed = false;
        ancestors(id).forEach(parent => { if (state.collapsed.has(parent)) { toggleBranch(parent, true); changed = true; } });
        return changed;
    }

    root.addEventListener('click', event => {
        const action = event.target.closest('[data-eot-action]')?.dataset.eotAction;
        if (!action) return;
        const step = 120;
        switch (action) {
            case 'zoom-in': zoomAt(state.scale * 1.2); break;
            case 'zoom-out': zoomAt(state.scale / 1.2); break;
            case 'fit': fit(); break;
            case 'center': if (state.roots.length === 1) centerOn(state.roots[0], true); else fit(); break;
            case 'expand': state.collapsed.clear(); render(); break;
            case 'collapse': state.nodes.forEach(node => { if ((state.children.get(node.id) || []).length && node.parent !== null) state.collapsed.add(node.id); }); render(); fit(); break;
            case 'refresh': load(false); break;
            case 'pan-up': pan(0, step); break;
            case 'pan-down': pan(0, -step); break;
            case 'pan-left': pan(step, 0); break;
            case 'pan-right': pan(-step, 0); break;
            case 'close-inspector': closeInspector(); break;
            case 'export-csv': exportCsv(); break;
            case 'export-excel': exportExcel(); break;
            case 'export-pdf': exportPdf(); break;
        }
    });

    departmentSelect.addEventListener('change', paintHighlights);
    searchInput.addEventListener('input', paintHighlights);
    searchInput.addEventListener('keydown', event => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const query = searchInput.value.trim().toLowerCase();
        if (!query) return;
        const nodes = Array.from(state.nodes.values());
        const found = nodes.find(node => node.name.toLowerCase() === query) || nodes.find(node => node.name.toLowerCase().includes(query) || String(node.employee_code || '').toLowerCase() === query);
        if (found) openInspector(found.id, true);
    });
    searchInput.addEventListener('change', () => {
        const found = Array.from(state.nodes.values()).find(node => node.name === searchInput.value);
        if (found) openInspector(found.id, true);
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && inspector.classList.contains('is-open')) closeInspector(); });

    /* ---------- Member inspector ---------- */
    function personButton(node, hint) {
        const button = el('button', 'eot-person');
        button.type = 'button';
        button.dataset.person = node.id;
        button.appendChild(photo(node));
        const text = el('span');
        text.appendChild(el('strong', '', node.name));
        text.appendChild(el('small', '', hint || node.designation || 'No designation'));
        button.appendChild(text);
        button.appendChild(icon('fa-chevron-right'));
        return button;
    }
    function action(label, iconName, href, opts) {
        const link = el(href ? 'a' : 'button', 'eot-action' + (opts?.disabled ? ' is-disabled' : ''));
        if (href) { link.href = href; if (opts?.blank) { link.target = '_blank'; link.rel = 'noopener'; } }
        else { link.type = 'button'; if (opts?.action) link.dataset.eotAction = opts.action; }
        if (opts?.disabled) link.setAttribute('aria-disabled', 'true');
        link.appendChild(icon(iconName));
        link.appendChild(el('span', '', label));
        return link;
    }
    function fact(label, value, wide) {
        const box = el('div', wide ? 'is-wide' : '');
        box.appendChild(el('dt', '', label));
        box.appendChild(el('dd', '', display(value)));
        return box;
    }
    function section(title) { const box = el('div', 'eot-section'); box.appendChild(el('h5', '', title)); return box; }

    function openInspector(id, focus) {
        const node = state.nodes.get(id);
        if (!node) return;
        state.selected = id;
        const body = inspectorBody;
        body.replaceChildren();

        const hero = el('div', 'eot-hero');
        hero.appendChild(photo(node, 'is-large'));
        const name = el('h4', '', node.name);
        if (isActive(node)) name.appendChild(icon('fa-circle-check'));
        hero.appendChild(name);
        hero.appendChild(el('p', '', node.designation || 'No designation'));
        const tags = el('div', 'eot-tags');
        tags.appendChild(el('span', 'eot-tag', 'Grade ' + levelLabel(node)));
        tags.appendChild(el('span', 'eot-tag is-dept', node.role));
        tags.appendChild(el('span', 'eot-tag ' + (isActive(node) ? 'is-ok' : 'is-warn'), display(node.status)));
        hero.appendChild(tags);
        body.appendChild(hero);

        const actions = el('div', 'eot-actions');
        actions.appendChild(action('Email', 'fa-envelope', node.email ? 'mailto:' + node.email : null, { disabled: !node.email }));
        actions.appendChild(action('Call', 'fa-phone', node.mobile ? 'tel:' + String(node.mobile).replace(/[^\d+]/g, '') : null, { disabled: !node.mobile }));
        actions.appendChild(action('Profile', 'fa-id-badge', node.show_url));
        body.appendChild(actions);

        const manager = node.parent !== null ? state.nodes.get(node.parent) : null;
        const reports = section(node.placement === 'level' ? 'Placed under (by designation level)' : 'Reports directly to');
        if (manager) reports.appendChild(personButton(manager));
        else reports.appendChild(el('p', 'eot-note', node.reporting_to_name ? node.reporting_to_name + ' (outside this view)' : 'Top of the organization'));
        body.appendChild(reports);

        const stats = el('div', 'eot-stats eot-section');
        const directs = el('div'); directs.appendChild(el('strong', '', node.directs)); directs.appendChild(el('span', '', 'Direct Leads'));
        const total = el('div'); total.appendChild(el('strong', '', node.total)); total.appendChild(el('span', '', 'Total Staff'));
        stats.appendChild(directs); stats.appendChild(total);
        body.appendChild(stats);

        const details = section('Employee details');
        const facts = el('dl', 'eot-facts');
        detailRows(node).forEach(([label, value, wide]) => facts.appendChild(fact(label, value, wide)));
        details.appendChild(facts);
        body.appendChild(details);

        const kids = state.children.get(id) || [];
        const team = section('Direct reports (' + kids.length + ')');
        if (kids.length) kids.forEach(kid => team.appendChild(personButton(state.nodes.get(kid))));
        else team.appendChild(el('p', 'eot-note', 'No direct reports.'));
        body.appendChild(team);

        const exports = section('Export employee details');
        const row = el('div', 'eot-exports');
        row.appendChild(action('CSV', 'fa-file-csv', null, { action: 'export-csv' }));
        row.appendChild(action('Excel', 'fa-file-excel', null, { action: 'export-excel' }));
        row.appendChild(action('PDF', 'fa-file-pdf', null, { action: 'export-pdf' }));
        exports.appendChild(row);
        body.appendChild(exports);

        body.querySelectorAll('[data-person]').forEach(button => button.addEventListener('click', () => openInspector(Number(button.dataset.person), true)));
        inspector.classList.add('is-open');
        inspector.setAttribute('aria-hidden', 'false');
        body.scrollTop = 0;
        const expanded = reveal(id);
        paintHighlights();
        if (focus) requestAnimationFrame(() => centerOn(id, !expanded));
    }
    function closeInspector() {
        state.selected = null;
        inspector.classList.remove('is-open');
        inspector.setAttribute('aria-hidden', 'true');
        paintHighlights();
    }

    function detailRows(node) {
        const manager = node.parent !== null ? state.nodes.get(node.parent) : null;
        const location = [node.address, node.country].filter(Boolean).join(', ');
        return [
            ['Employee ID', node.employee_code],
            ['Designation', node.designation],
            ['Designation level', levelLabel(node)],
            ['Role', node.role],
            ['Department', node.department],
            ['Parent department', node.parent_department],
            ['Reporting manager', node.reporting_to_name],
            ['Placed under', manager ? manager.name : 'Top level'],
            ['Joining date', node.joining_date],
            ['Tenure', node.tenure],
            ['Employment type', node.employment_type],
            ['Status', node.status],
            ['Gender', node.gender],
            ['Mobile', node.mobile],
            ['Email', node.email, true],
            ['Company', node.company, true],
            ['Location', location, true],
            ['Skills', node.skills, true],
        ];
    }

    /* ---------- Export (selected employee) ---------- */
    function exportRows() {
        const node = state.nodes.get(state.selected);
        if (!node) return null;
        const rows = [['Field', 'Value']];
        detailRows(node).forEach(([label, value]) => rows.push([label, display(value)]));
        rows.push(['Direct reports', String(node.directs)], ['Total staff', String(node.total)]);
        const kids = state.children.get(node.id) || [];
        const team = [['Direct report', 'Employee ID', 'Designation', 'Level', 'Department', 'Email']];
        kids.forEach(kid => { const k = state.nodes.get(kid); team.push([k.name, display(k.employee_code), display(k.designation), levelLabel(k), display(k.department), display(k.email)]); });
        return { node, rows, team };
    }
    function fileName(node, extension) {
        const slug = String(node.name || 'employee').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'employee';
        return 'employee-' + slug + '-' + new Date().toISOString().slice(0, 10) + '.' + extension;
    }
    function download(blob, name) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url; link.download = name;
        document.body.appendChild(link); link.click(); link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    function exportCsv() {
        const data = exportRows();
        if (!data) return;
        const cell = value => {
            let text = String(value ?? '');
            if (/^[=+\-@\t\r]/.test(text)) text = "'" + text;
            return '"' + text.replace(/"/g, '""') + '"';
        };
        const lines = data.rows.map(row => row.map(cell).join(','));
        if (data.team.length > 1) { lines.push(''); data.team.forEach(row => lines.push(row.map(cell).join(','))); }
        download(new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' }), fileName(data.node, 'csv'));
    }
    function exportExcel() {
        const data = exportRows();
        if (!data) return;
        if (!window.XLSX) { exportCsv(); return; }
        const book = XLSX.utils.book_new();
        const details = XLSX.utils.aoa_to_sheet(data.rows);
        details['!cols'] = [{ wch: 22 }, { wch: 48 }];
        XLSX.utils.book_append_sheet(book, details, 'Employee');
        if (data.team.length > 1) XLSX.utils.book_append_sheet(book, XLSX.utils.aoa_to_sheet(data.team), 'Direct Reports');
        XLSX.writeFile(book, fileName(data.node, 'xlsx'));
    }
    function exportPdf() {
        const data = exportRows();
        if (!data) return;
        if (!window.pdfMake) { exportCsv(); return; }
        const content = [
            { text: data.node.name, style: 'title' },
            { text: [data.node.designation || 'No designation', data.node.department].filter(Boolean).join(' · '), margin: [0, 0, 0, 12] },
            { table: { headerRows: 1, widths: [150, '*'], body: data.rows.map((row, index) => index === 0 ? row.map(text => ({ text, style: 'tableHeader' })) : row) } },
        ];
        if (data.team.length > 1) {
            content.push({ text: 'Direct reports', style: 'title', margin: [0, 16, 0, 6] });
            content.push({ table: { headerRows: 1, body: data.team.map((row, index) => index === 0 ? row.map(text => ({ text, style: 'tableHeader' })) : row) } });
        }
        const pdfDocument = { pageSize: 'A4', content };
        if (window.PmsPdfExports?.styleDocument) window.PmsPdfExports.styleDocument(pdfDocument);
        window.pdfMake.createPdf(pdfDocument).download(fileName(data.node, 'pdf'));
    }

    /* ---------- Lifecycle ---------- */
    // Live refresh keeps this section intact; reload the tree data so new or moved employees appear in place.
    document.addEventListener('pms:records-updated', () => { if (state.loaded && !root.hidden) load(false); else state.loaded = false; });
    window.addEventListener('resize', () => { if (!root.hidden && !state.userMoved) fit(); });

    const requested = new URLSearchParams(window.location.search).get('view');
    setView(requested === 'tree' || requested === 'list' ? requested : (storage.get(VIEW_KEY) === 'tree' ? 'tree' : 'list'), false);
})();
</script>
@endpush
