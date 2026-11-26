{{-- Employee organization chart, level grid, and profile inspector. --}}
<div class="eot-switch" data-live-key="employee-view-switch" role="tablist" aria-label="Employee view">
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
                <p id="eotSummary" role="status">Loading structure…</p>
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
            <select id="eotLevel" class="eot-select" aria-label="Jump to designation level">
                <option value="">Jump to level</option>
            </select>
            <div class="eot-group eot-modes" role="group" aria-label="Tree layout">
                <button type="button" class="eot-mode is-on" data-eot-action="mode-chart" aria-pressed="true" title="Chart view"><i class="fas fa-sitemap" aria-hidden="true"></i><span>Chart</span></button>
                <button type="button" class="eot-mode" data-eot-action="mode-grid" aria-pressed="false" title="Employee cards by designation level"><i class="fas fa-border-all" aria-hidden="true"></i><span>Level Grid</span></button>
                <button type="button" class="eot-mode" data-eot-action="mode-table" aria-pressed="false" title="Table view with reporting flow"><i class="fas fa-table-list" aria-hidden="true"></i><span>Table</span></button>
            </div>
            <div class="eot-group eot-chart-only" role="group" aria-label="Zoom">
                <button type="button" class="eot-tool" data-eot-action="zoom-out" title="Zoom out"><i class="fas fa-minus" aria-hidden="true"></i></button>
                <span class="eot-zoom" id="eotZoom">100%</span>
                <button type="button" class="eot-tool" data-eot-action="zoom-in" title="Zoom in"><i class="fas fa-plus" aria-hidden="true"></i></button>
            </div>
            <div class="eot-group" role="group" aria-label="Layout">
                <button type="button" class="eot-tool eot-chart-only" data-eot-action="fit" title="Fit to screen"><i class="fas fa-expand" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool eot-chart-only" data-eot-action="center" title="Center on root"><i class="fas fa-crosshairs" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool eot-branch-control" data-eot-action="expand" title="Expand all"><i class="fas fa-angles-down" aria-hidden="true"></i></button>
                <button type="button" class="eot-tool eot-branch-control" data-eot-action="collapse" title="Collapse all"><i class="fas fa-angles-up" aria-hidden="true"></i></button>
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
                <span>Employees in this workspace will appear here when they’re available to you.</span>
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
                <span><i class="eot-dot is-reporting"></i>Recorded reporting line</span>
                <span><i class="eot-dot is-level"></i>Placed by designation level</span>
            </div>
        </div>

        <div class="eot-level-grid" id="eotLevelGrid" aria-label="Employees by designation level" hidden></div>

        <div class="eot-tablewrap" id="eotTableWrap" hidden>
            <table class="eot-flow" aria-label="Organization table with reporting flow">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Designation</th>
                        <th scope="col">Level</th>
                        <th scope="col">Department</th>
                        <th scope="col">Reporting flow</th>
                        <th scope="col">Team</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Details</span></th>
                    </tr>
                </thead>
                <tbody id="eotTableBody"></tbody>
            </table>
        </div>

        <dialog class="eot-inspector" id="eotInspector" aria-labelledby="eotInspectorTitle" inert>
            <div class="eot-inspector-head">
                <span id="eotInspectorTitle">Employee details</span>
                <button type="button" class="eot-tool" data-eot-action="close-inspector" aria-label="Close employee details"><i class="fas fa-xmark" aria-hidden="true"></i></button>
            </div>
            <div class="eot-inspector-body" id="eotInspectorBody"></div>
        </dialog>
    </div>
</section>

<style>
    .emp-orgtree, .eot-switch {
        --eot-surface: #ffffff;
        --eot-canvas: #f4f7fc;
        --eot-grid: rgba(36, 87, 214, .11);
        --eot-border: #dce5f1;
        --eot-text: #14223a;
        --eot-muted: #52637b;
        --eot-accent: #2457d6;
        --eot-accent-soft: #eaf0ff;
        --eot-accent-contrast: #ffffff;
        --eot-line: #aebed6;
        --eot-path: #2457d6;
        --eot-ok: #117054;
        --eot-ok-soft: #dcfce7;
        --eot-warn: #b45309;
        --eot-warn-soft: #fef3c7;
        --eot-shadow: 0 4px 16px rgba(20, 34, 58, .07);
    }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) :is(.emp-orgtree, .eot-switch) {
        --eot-surface: #121e32;
        --eot-canvas: #0d1728;
        --eot-grid: rgba(168, 193, 255, .12);
        --eot-border: #2b3b54;
        --eot-text: #edf3ff;
        --eot-muted: #a8b8cf;
        --eot-accent: #a8c1ff;
        --eot-accent-soft: #21355b;
        --eot-accent-contrast: #13294c;
        --eot-line: #526887;
        --eot-path: #a8c1ff;
        --eot-ok: #4ade80;
        --eot-ok-soft: rgba(74, 222, 128, .14);
        --eot-warn: #fbbf24;
        --eot-warn-soft: rgba(251, 191, 36, .14);
        --eot-shadow: 0 4px 16px rgba(0, 0, 0, .24);
    }

    /* Employee List: reporting flow under "Reports To" */
    .employee-dashboard .role-info .reports-flow { display: flex; align-items: center; gap: 5px; min-width: 0; max-width: 260px; font-size: .75rem; font-weight: 600; color: var(--emp-reports-color, #64748b) !important; -webkit-text-fill-color: var(--emp-reports-color, #64748b) !important; }
    .employee-dashboard .role-info .reports-flow > span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    .employee-dashboard .role-info .reports-flow i { flex-shrink: 0; font-size: .7rem; font-style: normal; color: #2457d6 !important; -webkit-text-fill-color: #2457d6 !important; }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .employee-dashboard .role-info .reports-flow i { color: #a8c1ff !important; -webkit-text-fill-color: #a8c1ff !important; }

    .employee-dashboard.eot-tree-mode .filter-export-card,
    .employee-dashboard.eot-tree-mode .table-card { display: none !important; }

    .eot-switch {
        display: inline-flex;
        gap: 4px;
        max-width: 100%;
        padding: 4px;
        margin: 0 0 16px;
        border: 1px solid var(--eot-border);
        border-radius: 13px;
        background: var(--eot-surface);
        box-shadow: var(--eot-shadow);
    }

    #employeeDirectoryPage .eot-switch-tab {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px !important;
        width: auto !important;
        padding: 8px 15px !important;
        border: 1px solid transparent !important;
        border-radius: 9px !important;
        background: transparent !important;
        color: var(--eot-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-size: .875rem;
        font-weight: 650;
        line-height: 1.2;
        cursor: pointer;
        transition: background-color .18s, color .18s, border-color .18s;
    }

    #employeeDirectoryPage .eot-switch-tab:hover {
        background: var(--eot-canvas) !important;
        color: var(--eot-text) !important;
    }

    #employeeDirectoryPage .eot-switch-tab.is-active {
        border-color: var(--eot-border) !important;
        background: var(--eot-accent-soft) !important;
        color: var(--eot-accent) !important;
    }

    #employeeDirectoryPage .eot-switch-tab > :is(span, i),
    #employeeOrgTree .eot-mode > :is(span, i) {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #employeeDirectoryPage .eot-switch-tab > span { font-weight: inherit !important; }
    #employeeDirectoryPage .eot-switch-tab > i { font-size: .8125rem; filter: none !important; }
    #employeeDirectoryPage .eot-switch-tab:focus-visible,
    #employeeOrgTree .eot-mode:focus-visible {
        outline: 2px solid var(--eot-accent) !important;
        outline-offset: 2px;
    }

    .emp-orgtree { margin-bottom: 24px; border-radius: 22px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); overflow: hidden; color: var(--eot-text); }
    .emp-orgtree[hidden] { display: none !important; }
    #employeeOrgTree i { font-style: normal; filter: none !important; }
    .eot-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--eot-border); }
    .eot-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .eot-title-mark { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; background: var(--eot-accent-soft); flex-shrink: 0; }
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
    #employeeOrgTree .eot-zoom { min-width: 46px; text-align: center; font-size: 12px; font-weight: 700 !important; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }

    .eot-body { position: relative; }
    .eot-viewport { position: relative; height: min(72vh, 760px); min-height: 460px; overflow: hidden; cursor: grab; touch-action: none; user-select: none; outline: none;
        background-color: var(--eot-canvas); background-image: radial-gradient(var(--eot-grid) 1.2px, transparent 1.2px); background-size: 22px 22px; }
    .eot-viewport.is-dragging { cursor: grabbing; }
    .eot-stage { position: absolute; left: 0; top: 0; width: max-content; transform-origin: 0 0; padding: 40px 40px 60px; will-change: transform; }
    .eot-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 24px; text-align: center; color: var(--eot-muted) !important; }
    .eot-empty[hidden] { display: none; }
    .eot-empty i { font-size: 34px; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; }
    .eot-empty strong { color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    #employeeOrgTree .eot-empty span { max-width: 360px; font-size: 13px; font-weight: 400 !important; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-pan { position: absolute; left: 16px; bottom: 16px; display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px; border-radius: 12px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); }
    .eot-pan > div { display: flex; gap: 30px; }
    .eot-legend { position: absolute; right: 16px; bottom: 16px; display: flex; flex-wrap: wrap; gap: 12px; padding: 8px 12px; border-radius: 10px; background: var(--eot-surface); border: 1px solid var(--eot-border); font-size: 12px; color: var(--eot-muted); }
    #employeeOrgTree .eot-legend span { display: inline-flex; align-items: center; gap: 6px; font-weight: 500 !important; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-dot { width: 18px; height: 0; border-top: 2px solid var(--eot-line); display: inline-block; }
    .eot-dot.is-level { border-top-style: dashed; }

    .eot-chart { position: relative; }
    .eot-connectors { position: absolute; inset: 0; pointer-events: none; }
    .eot-connector { fill: none; stroke: var(--eot-line); stroke-width: 2; }
    .eot-connector.is-level-placed { stroke-dasharray: 6 4; }
    .eot-connector.is-company-root { stroke-dasharray: 2 4; }
    .eot-connector.is-path { stroke: var(--eot-path); stroke-width: 3; }
    #employeeOrgTree .eot-lane {
        position: absolute; left: 0; right: 0; height: 157px; padding: 8px 10px;
        border-top: 1px solid var(--eot-border); background: var(--eot-accent-soft);
        color: var(--eot-accent) !important; -webkit-text-fill-color: currentColor !important;
        font-size: 12px; font-weight: 700; pointer-events: none;
    }

    /* Member tiles */
    .eot-tile { position: relative; width: 236px; padding: 14px 14px 12px; border-radius: 16px; background: var(--eot-surface); border: 1px solid var(--eot-border); box-shadow: var(--eot-shadow); cursor: pointer; text-align: left; transition: border-color .2s, box-shadow .2s, transform .2s, opacity .2s; }
    .eot-tile:hover { border-color: var(--eot-accent); transform: translateY(-2px); }
    .eot-tile.is-selected { border-color: var(--eot-accent); box-shadow: 0 0 0 3px var(--eot-accent-soft), var(--eot-shadow); }
    .eot-tile.is-path { border-color: var(--eot-path); }
    .eot-tile.is-dim { opacity: .35; }
    .eot-tile.is-match { box-shadow: 0 0 0 3px var(--eot-warn-soft), var(--eot-shadow); border-color: var(--eot-warn); }
    #employeeOrgTree .eot-tile-flag { position: absolute; top: -10px; left: 14px; padding: 2px 8px; border-radius: 999px; background: var(--eot-path); color: var(--eot-accent-contrast) !important; -webkit-text-fill-color: currentColor !important; font-size: 10px; font-weight: 700 !important; letter-spacing: .06em; text-transform: uppercase; display: none; }
    #employeeOrgTree :is(.eot-tile.is-path, .eot-tile.is-selected) .eot-tile-flag { display: inline-block; }
    .eot-tile-top { display: flex; align-items: center; gap: 10px; }
    #employeeOrgTree .eot-photo { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; overflow: hidden; display: grid; place-items: center; background: var(--eot-accent-soft); color: var(--eot-accent) !important; -webkit-text-fill-color: currentColor !important; font-weight: 700 !important; font-size: 15px; }
    .eot-photo img { width: 100% !important; height: 100% !important; max-width: none !important; object-fit: cover; display: block; }
    #employeeOrgTree .eot-photo.is-large { width: 72px; height: 72px; font-size: 24px; }
    #employeeOrgTree .eot-photo.is-company { border-radius: 12px; }
    .eot-who { min-width: 0; flex: 1; }
    .eot-name { display: flex; align-items: center; gap: 5px; margin: 0; font-size: 14px; font-weight: 700; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .eot-name > span { overflow: hidden; text-overflow: ellipsis; }
    #employeeOrgTree :is(.eot-name, .eot-tile-foot, .eot-action) > span { color: inherit !important; -webkit-text-fill-color: currentColor !important; font-weight: inherit !important; }
    .eot-name i { color: var(--eot-accent) !important; -webkit-text-fill-color: currentColor !important; font-size: 12px; flex-shrink: 0; }
    .eot-role { margin: 2px 0 0; font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .eot-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
    #employeeOrgTree .eot-tag { display: inline-flex; align-items: center; gap: 4px; max-width: 100%; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600 !important; background: var(--eot-accent-soft); color: var(--eot-accent) !important; -webkit-text-fill-color: currentColor !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    #employeeOrgTree .eot-tag.is-dept { background: var(--eot-canvas); color: var(--eot-muted) !important; border: 1px solid var(--eot-border); }
    #employeeOrgTree .eot-tag.is-ok { background: var(--eot-ok-soft); color: var(--eot-ok) !important; }
    #employeeOrgTree .eot-tag.is-warn { background: var(--eot-warn-soft); color: var(--eot-warn) !important; }
    .eot-tile-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--eot-border); font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-tile-foot strong { color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .emp-orgtree .eot-toggle { position: absolute; left: 50%; bottom: -13px; transform: translateX(-50%); width: 26px !important; height: 26px !important; min-height: 0 !important; padding: 0 !important; border-radius: 50% !important; border: 1px solid var(--eot-border) !important; background: var(--eot-surface) !important; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; display: grid !important; place-items: center; cursor: pointer; z-index: 2; box-shadow: var(--eot-shadow) !important; font-size: 11px; font-weight: 700; }
    .emp-orgtree .eot-toggle i { color: inherit !important; -webkit-text-fill-color: currentColor !important; font-size: 11px; transition: transform .2s; }
    .eot-toggle[aria-expanded="false"] i { transform: rotate(-90deg); }
    .eot-chart .eot-tile { position: absolute; display: flex; flex-direction: column; gap: 7px; }
    .eot-chart .eot-tags { margin: 0; flex-wrap: nowrap; }
    .eot-chart .eot-tags .is-dept { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .eot-chart .eot-tile-foot { margin-top: auto; }
    .eot-tile-foot > span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .eot-tile-foot > span:first-child { flex-shrink: 0; }
    #employeeOrgTree .eot-card-chevron { color: var(--eot-accent) !important; -webkit-text-fill-color: currentColor !important; font-size: 11px; }
    .eot-chart .eot-tile.is-company { cursor: default; background: linear-gradient(135deg, var(--eot-accent-soft), var(--eot-surface)); }
    .eot-chart .eot-tile:hover { transform: none; }
    #employeeOrgTree .eot-card-open {
        position: absolute; inset: 0; background: transparent !important; border: 0 !important;
        border-radius: inherit !important; box-shadow: none !important; cursor: pointer;
    }
    #employeeOrgTree .eot-card-open:focus-visible { outline: 2px solid var(--eot-accent) !important; outline-offset: 3px; }

    /* Inspector */
    #employeeOrgTree .eot-inspector {
        position: fixed; inset: 0; margin: auto; width: min(760px, calc(100% - 28px)); max-width: none;
        max-height: min(880px, calc(100% - 28px)); padding: 0; overflow: hidden;
        border: 1px solid var(--eot-border); border-radius: 18px;
        background: var(--eot-surface); color: var(--eot-text); box-shadow: 0 18px 54px rgb(0 0 0 / 25%);
    }
    #employeeOrgTree .eot-inspector[open] { display: flex; flex-direction: column; }
    #employeeOrgTree .eot-inspector:not([open]) { display: none !important; }
    #employeeOrgTree .eot-inspector::backdrop { background: rgb(9 19 38 / 55%); }
    .eot-inspector-head { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--eot-border); font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    #employeeOrgTree .eot-inspector-head span { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    .eot-inspector-body { flex: 1; min-height: 0; overflow-y: auto; padding: 18px 20px 24px; overscroll-behavior: contain; }
    #employeeOrgTree .eot-profile-status { margin: 10px 0; }
    .eot-hero {
        display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px; padding: 16px 12px;
        background: linear-gradient(160deg, var(--eot-accent-soft), var(--eot-surface)); border: 1px solid var(--eot-border); border-radius: 14px;
    }
    .eot-hero h4 { margin: 6px 0 0; font-size: 21px; font-weight: 700; display: flex; align-items: center; gap: 6px; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-hero h4 i { color: var(--eot-accent) !important; -webkit-text-fill-color: currentColor !important; font-size: 14px; }
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
    #employeeOrgTree .eot-person .eot-photo { width: 34px; height: 34px; font-size: 12px; }
    .eot-person strong { display: block; font-size: 13px; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-person small { display: block; font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-person > i { margin-left: auto; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; font-size: 11px; }
    .eot-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .eot-stats div { padding: 12px; border-radius: 12px; background: var(--eot-accent-soft); text-align: center; }
    .eot-stats strong { display: block; font-size: 22px; font-weight: 800; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; }
    #employeeOrgTree .eot-stats span { font-size: 12px; font-weight: 600 !important; color: var(--eot-muted) !important; -webkit-text-fill-color: currentColor !important; }
    .eot-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 0; }
    .eot-facts div { min-width: 0; padding: 10px; border-radius: 12px; border: 1px solid var(--eot-border); }
    .eot-facts div.is-wide { grid-column: 1 / -1; }
    .eot-facts dt { font-size: 11px; font-weight: 600; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-facts dd { margin: 2px 0 0; font-size: 13px; font-weight: 600; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; overflow-wrap: anywhere; }
    .eot-exports { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .eot-note { font-size: 12px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; margin: 0; }

    /* Layout switch */
    #employeeOrgTree .eot-mode { display: inline-flex !important; align-items: center; gap: 6px; height: 32px; padding: 0 10px !important; border: 0 !important; border-radius: 8px !important; background: transparent !important; color: var(--eot-muted) !important; -webkit-text-fill-color: currentColor !important; font-size: 12px; font-weight: 650; cursor: pointer; width: auto !important; min-height: 0 !important; box-shadow: none !important; }
    #employeeOrgTree .eot-mode i { font-size: 12px; }
    #employeeOrgTree .eot-mode > span { font-weight: inherit !important; }
    #employeeOrgTree .eot-mode:hover { color: var(--eot-text) !important; }
    #employeeOrgTree .eot-mode.is-on { background: var(--eot-accent-soft) !important; color: var(--eot-accent) !important; }
    .emp-orgtree:is(.is-table-mode, .is-grid-mode) :is(.eot-chart-only, .eot-viewport),
    .emp-orgtree.is-grid-mode .eot-branch-control { display: none !important; }
    #employeeOrgTree .eot-level-grid[hidden] { display: none !important; }
    #employeeOrgTree .eot-level-grid {
        display: grid; gap: 15px; max-height: min(72vh, 760px); overflow: auto; padding: clamp(12px, 2vw, 22px);
        background: var(--eot-canvas); overscroll-behavior: contain;
    }
    #employeeOrgTree .eot-level-group { min-width: 0; padding-bottom: 10px; border-bottom: 1px solid var(--eot-border); }
    #employeeOrgTree .eot-level-group:last-child { border-bottom: 0; padding-bottom: 0; }
    #employeeOrgTree .eot-level-title {
        display: flex; align-items: center; gap: 10px; margin: 0 0 10px;
        color: var(--eot-text) !important; -webkit-text-fill-color: currentColor !important; font-size: .95rem !important;
    }
    #employeeOrgTree .eot-level-title .eot-tag { min-width: 34px; justify-content: center; }
    #employeeOrgTree .eot-level-title:only-child { margin-bottom: 0; color: var(--eot-muted) !important; }
    #employeeOrgTree .eot-level-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 220px), 1fr)); gap: 10px; }
    #employeeOrgTree .eot-level-cards .eot-tile { width: 100%; min-width: 0; }
    #employeeOrgTree .eot-grid-manager { margin: 7px 0 0; color: var(--eot-muted) !important; -webkit-text-fill-color: currentColor !important; font-size: .76rem; overflow-wrap: anywhere; }
    .eot-tablewrap { min-width: 0; height: min(72vh, 760px); min-height: 460px; overflow: auto; background: var(--eot-surface); }
    .eot-tablewrap[hidden] { display: none !important; }

    /* The shared export toolbar is inserted before the reporting table. */
    #employeeOrgTree:not(.is-table-mode) .eot-body > .pms-table-tools { display: none !important; }
    #employeeOrgTree .eot-body > .pms-table-tools {
        border: 0 !important;
        border-bottom: 1px solid var(--eot-border) !important;
        border-radius: 0;
        background: var(--eot-surface) !important;
    }
    #employeeOrgTree .eot-tablewrap.pms-table-scroll-host {
        border: 0;
        border-radius: 0;
        background: var(--eot-surface);
        box-shadow: none;
    }

    .emp-orgtree .eot-flow { width: 100%; min-width: 980px; border-collapse: separate; border-spacing: 0; margin: 0; background: transparent !important; }
    .emp-orgtree .eot-flow th { position: sticky; top: 0; z-index: 1; padding: 11px 14px; background: var(--eot-canvas) !important; border-bottom: 1px solid var(--eot-border); font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; text-align: left; white-space: nowrap; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .emp-orgtree .eot-flow td { padding: 10px 14px; border-bottom: 1px solid var(--eot-border); vertical-align: middle; font-size: 13px; background: transparent !important; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .emp-orgtree .eot-flow tbody tr { cursor: pointer; transition: background .15s, opacity .15s; }
    .emp-orgtree .eot-flow tbody tr:hover td { background: var(--eot-accent-soft) !important; }
    .emp-orgtree .eot-flow tbody tr.is-selected td { background: var(--eot-accent-soft) !important; }
    .emp-orgtree .eot-flow tbody tr.is-selected td:first-child { box-shadow: inset 3px 0 0 var(--eot-accent); }
    .emp-orgtree .eot-flow tbody tr.is-path td:first-child { box-shadow: inset 3px 0 0 var(--eot-path); }
    .emp-orgtree .eot-flow tbody tr.is-dim { opacity: .35; }
    .emp-orgtree .eot-flow tbody tr.is-match td { background: var(--eot-warn-soft) !important; }
    .emp-orgtree .eot-flow tbody tr:focus-visible { outline: 2px solid var(--eot-accent); outline-offset: -2px; }
    .eot-cell-person { display: flex; align-items: center; gap: 8px; min-width: 220px; }
    .eot-indent { display: inline-block; flex-shrink: 0; align-self: stretch; }
    .emp-orgtree .eot-rowtoggle { width: 22px !important; height: 22px !important; min-width: 22px !important; min-height: 0 !important; padding: 0 !important; display: inline-grid !important; place-items: center; border: 1px solid var(--eot-border) !important; border-radius: 6px !important; background: var(--eot-surface) !important; color: var(--eot-accent) !important; -webkit-text-fill-color: var(--eot-accent) !important; cursor: pointer; box-shadow: none !important; flex-shrink: 0; }
    .emp-orgtree .eot-rowtoggle i { color: inherit !important; -webkit-text-fill-color: currentColor !important; font-size: 10px; transition: transform .2s; }
    .emp-orgtree .eot-rowtoggle[aria-expanded="false"] i { transform: rotate(-90deg); }
    .eot-rowspacer { width: 22px; flex-shrink: 0; display: inline-grid; place-items: center; }
    .eot-rowspacer::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--eot-line); }
    #employeeOrgTree .eot-cell-person .eot-photo { width: 32px; height: 32px; font-size: 12px; }
    .eot-cell-person strong { display: block; font-size: 13px; white-space: nowrap; color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }
    .eot-cell-person small { display: block; font-size: 11px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-chain { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; max-width: 360px; }
    #employeeOrgTree .eot-chain-step { padding: 2px 8px !important; border-radius: 999px !important; border: 1px solid var(--eot-border) !important; background: var(--eot-canvas) !important; font-size: 11px; font-weight: 600 !important; white-space: nowrap; color: var(--eot-muted) !important; -webkit-text-fill-color: currentColor !important; }
    #employeeOrgTree .eot-chain-step.is-self { border-color: var(--eot-accent) !important; background: var(--eot-accent-soft) !important; color: var(--eot-accent) !important; }
    .emp-orgtree button.eot-chain-step { cursor: pointer; min-height: 0 !important; box-shadow: none !important; width: auto !important; line-height: 1.5; }
    #employeeOrgTree button.eot-chain-step:hover { border-color: var(--eot-accent) !important; color: var(--eot-accent) !important; }
    .emp-orgtree .eot-chain-sep { font-size: 9px; color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-team { white-space: nowrap; }
    .emp-orgtree .eot-flow td.eot-team { color: var(--eot-muted) !important; -webkit-text-fill-color: var(--eot-muted) !important; }
    .eot-team strong { color: var(--eot-text) !important; -webkit-text-fill-color: var(--eot-text) !important; }

    @media (max-width: 991.98px) {
        .eot-legend { display: none; }
    }
    @media (max-width: 575.98px) {
        .eot-switch { display: flex; }
        #employeeDirectoryPage .eot-switch-tab { flex: 1; padding-inline: 10px !important; }
        .eot-toolbar { padding: 14px 16px; }
        .eot-controls, .eot-search, .emp-orgtree .eot-search input, .emp-orgtree .eot-select { width: 100%; max-width: none; }
        .eot-viewport { height: 64vh; min-height: 380px; }
        .eot-pan { left: 10px; bottom: 10px; }
        #employeeOrgTree .eot-inspector { width: calc(100% - 16px); max-height: calc(100% - 16px); border-radius: 12px; }
        #employeeOrgTree .eot-inspector-body { padding: 14px; }
        .eot-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (prefers-reduced-motion: reduce) {
        #employeeDirectoryPage .eot-switch-tab { transition: none; }
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
    const levelSelect = document.getElementById('eotLevel');
    const inspector = document.getElementById('eotInspector');
    const inspectorBody = document.getElementById('eotInspectorBody');
    const grid = document.getElementById('eotLevelGrid');
    const tableWrap = document.getElementById('eotTableWrap');
    const tableBody = document.getElementById('eotTableBody');
    const MODE_KEY = 'pmsEmployeeTreeMode';
    const VIEW_KEY = 'pmsEmployeeView';
    const MAX_SCALE = 2.5;
    const CARD_WIDTH = 236, CARD_HEIGHT = 132, COLUMN_WIDTH = 268, LEVEL_HEIGHT = 184;

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
            if (!state.loaded) load(true); else requestAnimationFrame(() => { if (!state.userMoved) fit(true); });
        } else {
            closeInspector();
        }
    }
    tabs.forEach(tab => tab.addEventListener('click', () => setView(tab.dataset.eotView, true)));

    /* ---------- Data ---------- */
    async function load(fitAfter) {
        if (state.loading) { state.reloadPending = true; return; }
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
            if (firstLoad || fitAfter) requestAnimationFrame(() => fit(true));
            if (state.selected !== null && !state.nodes.has(state.selected)) closeInspector();
        } catch (error) {
            summary.textContent = 'Unable to load the organization tree. Please refresh and try again.';
        } finally {
            state.loading = false;
            if (state.reloadPending) {
                state.reloadPending = false;
                if (!root.hidden) load(false); else state.loaded = false;
            }
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
            const la = na.level ?? -1, lb = nb.level ?? -1;
            return lb - la || String(na.department || '').localeCompare(String(nb.department || '')) || na.name.localeCompare(nb.name);
        }));
        state.roots = state.children.get(null) || [];
        state.collapsed = new Set([...state.collapsed].filter(id => state.children.has(id)));
        const departments = new Map();
        state.nodes.forEach(node => { if (node.department_id) departments.set(String(node.department_id), node.department); });
        const current = departmentSelect.value;
        departmentSelect.replaceChildren(new Option('All departments', ''));
        Array.from(departments.entries()).sort((a, b) => String(a[1]).localeCompare(String(b[1]))).forEach(([id, name]) => departmentSelect.add(new Option(name, id)));
        departmentSelect.value = departments.has(current) ? current : '';
        const levels = [...new Set([...state.nodes.values()].map(node => node.level ?? -1))].sort((a, b) => b - a);
        state.levels = [...new Set([6, 5, 4, 3, 2, 1, 0, ...levels])].sort((a, b) => b - a);
        levelSelect.replaceChildren(new Option('Jump to level', ''));
        levels.forEach(level => levelSelect.add(new Option(level === -1 ? 'No level' : 'L' + level, level)));
        const suggestions = document.createDocumentFragment();
        state.nodes.forEach(node => suggestions.appendChild(new Option([node.name, node.designation, node.employee_code].filter(Boolean).join(' — '), node.employee_code || node.name)));
        searchList.replaceChildren(suggestions);
        const assignedLevels = levels.filter(level => level !== -1).length;
        summary.textContent = [
            state.nodes.size + (state.nodes.size === 1 ? ' employee' : ' employees'),
            assignedLevels + (assignedLevels === 1 ? ' designation level' : ' designation levels'),
            departments.size + (departments.size === 1 ? ' department' : ' departments'),
        ].join(' · ');
    }

    function ancestors(id) {
        const list = [];
        let current = state.nodes.get(id)?.parent ?? null;
        while (current !== null) { list.push(current); current = state.nodes.get(current)?.parent ?? null; }
        return list;
    }

    /* ---------- Rendering ---------- */
    function tile(node, inGrid = false) {
        const card = el('div', 'eot-tile');
        card.dataset.id = node.id;
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
        if (inGrid) {
            const manager = state.nodes.get(node.parent);
            if (manager) card.appendChild(el('p', 'eot-grid-manager', (node.placement === 'level' ? 'Placed under ' : 'Reports to ') + manager.name));
        }
        const foot = el('div', 'eot-tile-foot');
        const counts = el('span');
        counts.appendChild(el('strong', '', node.directs)); counts.appendChild(document.createTextNode(' Directs · '));
        counts.appendChild(el('strong', '', node.total)); counts.appendChild(document.createTextNode(' Total'));
        foot.appendChild(counts);
        foot.appendChild(el('span', '', node.employee_code || ''));
        foot.appendChild(icon('fa-chevron-right eot-card-chevron'));
        card.appendChild(foot);
        const open = el('button', 'eot-card-open');
        open.type = 'button';
        open.setAttribute('aria-label', 'View details for ' + node.name + ', ' + levelLabel(node));
        open.setAttribute('aria-controls', 'eotInspector');
        card.appendChild(open);
        if (!inGrid && (state.children.get(node.id) || []).length) {
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

    function visibleBranches() {
        const branches = [];
        const pending = [...state.roots].reverse().map(id => ({ id, depth: 0 }));
        while (pending.length) {
            const { id, depth } = pending.pop();
            const children = state.collapsed.has(id) ? [] : state.children.get(id) || [];
            branches.push({ id, depth, children });
            for (let i = children.length - 1; i >= 0; i--) pending.push({ id: children[i], depth: depth + 1 });
        }
        return branches;
    }

    function plotBranches(branches) {
        const positions = new Map();
        const slots = new Map();
        const rowLevels = branches.length ? state.levels : [];
        const hasCompanyRoot = state.roots.length > 1;
        const rows = new Map(rowLevels.map((level, index) => [level, (index + Number(hasCompanyRoot)) * LEVEL_HEIGHT]));
        let leaf = 0;
        branches.forEach(({ id, children }) => { if (!children.length) slots.set(id, [leaf, leaf++]); });
        for (let index = branches.length - 1; index >= 0; index--) {
            const { id, children } = branches[index];
            if (children.length) slots.set(id, [slots.get(children[0])[0], slots.get(children.at(-1))[1]]);
            const [first, last] = slots.get(id);
            positions.set(id, { x: 98 + ((first + last) / 2 + .5) * COLUMN_WIDTH, y: rows.get(state.nodes.get(id).level ?? -1) + 12 });
        }
        return { positions, rows, width: 98 + leaf * COLUMN_WIDTH, height: rows.size * LEVEL_HEIGHT + (hasCompanyRoot ? LEVEL_HEIGHT : 0), hasCompanyRoot };
    }

    function renderChart(branches) {
        const chart = el('div', 'eot-chart');
        const { positions, rows, width, height, hasCompanyRoot } = plotBranches(branches);
        chart.style.width = width + 'px';
        chart.style.height = height + 'px';
        rows.forEach((y, level) => {
            const lane = el('div', 'eot-lane', level === -1 ? 'No level' : 'L' + level);
            lane.style.top = y + 'px';
            chart.appendChild(lane);
        });
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'eot-connectors');
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('width', width);
        svg.setAttribute('height', height);
        chart.appendChild(svg);

        const place = (card, point) => {
            card.style.left = (point.x - CARD_WIDTH / 2) + 'px';
            card.style.top = point.y + 'px';
            card.style.width = CARD_WIDTH + 'px';
            card.style.height = CARD_HEIGHT + 'px';
            chart.appendChild(card);
        };
        const company = hasCompanyRoot ? { x: 98 + (width - 98) / 2, y: 12 } : null;
        if (company) place(companyTile(), company);
        branches.forEach(({ id }) => {
            const node = state.nodes.get(id);
            const point = positions.get(id);
            const parent = positions.get(node.parent) || company;
            if (parent) {
                const line = document.createElementNS(svg.namespaceURI, 'path');
                const kind = node.parent === null ? ' is-company-root' : node.placement === 'level' ? ' is-level-placed' : '';
                line.setAttribute('class', 'eot-connector' + kind);
                line.dataset.child = id;
                const start = parent.y + CARD_HEIGHT;
                line.setAttribute('d', `M ${parent.x} ${start} V ${start + 22} H ${point.x} V ${point.y}`);
                svg.appendChild(line);
            }
            place(tile(node), point);
        });
        stage.replaceChildren(chart);
    }

    function render() {
        const branches = visibleBranches();
        renderChart(branches);
        emptyState.hidden = state.nodes.size > 0;
        renderTable(branches);
        if (state.mode === 'grid') renderGrid();
        paintHighlights();
    }

    function renderGrid() {
        if (!state.nodes.size) {
            grid.replaceChildren(el('p', 'eot-note', 'No employees to show yet.'));
            return;
        }
        const byLevel = new Map(state.levels.map(level => [level, []]));
        state.nodes.forEach(node => byLevel.get(node.level ?? -1).push(node));
        const fragment = document.createDocumentFragment();
        byLevel.forEach((members, level) => {
            const group = el('section', 'eot-level-group');
            group.dataset.level = level;
            const title = el('h4', 'eot-level-title');
            title.append(el('span', 'eot-tag', level === -1 ? 'No level' : 'L' + level), document.createTextNode(members.length + (members.length === 1 ? ' employee' : ' employees')));
            group.appendChild(title);
            if (members.length) {
                const cards = el('div', 'eot-level-cards');
                members.sort((a, b) => a.name.localeCompare(b.name)).forEach(node => cards.appendChild(tile(node, true)));
                group.appendChild(cards);
            }
            fragment.appendChild(group);
        });
        grid.replaceChildren(fragment);
    }

    /* ---------- Table view (same hierarchy, reporting flow per row) ---------- */
    function chainOf(id) { return ancestors(id).reverse().concat([id]); }
    function chainElement(id, clickable) {
        const box = el('div', 'eot-chain');
        const chain = chainOf(id);
        chain.forEach((step, index) => {
            const node = state.nodes.get(step);
            const self = step === id;
            const item = el(clickable && !self ? 'button' : 'span', 'eot-chain-step' + (self ? ' is-self' : ''), node.name);
            if (clickable && !self) { item.type = 'button'; item.dataset.person = step; item.title = node.designation || ''; }
            box.appendChild(item);
            if (index < chain.length - 1) box.appendChild(icon('fa-chevron-right eot-chain-sep'));
        });
        return box;
    }
    function tableRow(id, depth) {
        const node = state.nodes.get(id);
        const kids = state.children.get(id) || [];
        const tr = el('tr');
        tr.dataset.row = id;
        tr.tabIndex = 0;
        const person = el('td');
        const cell = el('div', 'eot-cell-person');
        const indent = el('span', 'eot-indent');
        indent.style.width = (Math.min(depth, 8) * 22) + 'px';
        cell.appendChild(indent);
        if (kids.length) {
            const toggle = el('button', 'eot-rowtoggle');
            toggle.type = 'button';
            toggle.dataset.toggle = id;
            toggle.setAttribute('aria-expanded', state.collapsed.has(id) ? 'false' : 'true');
            toggle.title = state.collapsed.has(id) ? 'Expand team' : 'Collapse team';
            toggle.appendChild(icon('fa-chevron-down'));
            cell.appendChild(toggle);
        } else {
            cell.appendChild(el('span', 'eot-rowspacer'));
        }
        cell.appendChild(photo(node));
        const who = el('span');
        who.appendChild(el('strong', '', node.name));
        who.appendChild(el('small', '', node.employee_code || node.email || ''));
        cell.appendChild(who);
        person.appendChild(cell);
        tr.appendChild(person);
        tr.appendChild(el('td', '', node.designation || 'No designation'));
        const level = el('td'); level.appendChild(el('span', 'eot-tag', levelLabel(node))); tr.appendChild(level);
        tr.appendChild(el('td', '', node.department || 'No department'));
        const flow = el('td'); flow.appendChild(chainElement(id, true)); tr.appendChild(flow);
        const team = el('td', 'eot-team');
        team.appendChild(el('strong', '', node.directs)); team.appendChild(document.createTextNode(' directs · '));
        team.appendChild(el('strong', '', node.total)); team.appendChild(document.createTextNode(' total'));
        tr.appendChild(team);
        const status = el('td'); status.appendChild(el('span', 'eot-tag ' + (isActive(node) ? 'is-ok' : 'is-warn'), display(node.status))); tr.appendChild(status);
        const more = el('td');
        const open = el('button', 'eot-tool');
        open.type = 'button'; open.dataset.person = id; open.title = 'View details';
        open.appendChild(icon('fa-arrow-right'));
        more.appendChild(open);
        tr.appendChild(more);
        return tr;
    }
    function renderTable(branches) {
        const fragment = document.createDocumentFragment();
        branches.forEach(({ id, depth }) => fragment.appendChild(tableRow(id, depth)));
        tableBody.replaceChildren(fragment);
    }
    tableBody.addEventListener('click', event => {
        const toggle = event.target.closest('[data-toggle]');
        if (toggle) { toggleBranch(Number(toggle.dataset.toggle)); return; }
        const target = event.target.closest('[data-person], tr[data-row]');
        if (target) openInspector(Number(target.dataset.person || target.dataset.row));
    });
    tableBody.addEventListener('keydown', event => {
        const row = event.target.closest('tr[data-row]');
        if (row && event.target === row && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); openInspector(Number(row.dataset.row)); }
    });
    function setMode(mode, persist) {
        state.mode = ['grid', 'table'].includes(mode) ? mode : 'chart';
        const table = state.mode === 'table';
        const levelGrid = state.mode === 'grid';
        root.classList.toggle('is-table-mode', table);
        root.classList.toggle('is-grid-mode', levelGrid);
        tableWrap.hidden = !table;
        grid.hidden = !levelGrid;
        if (levelGrid && state.loaded) {
            renderGrid();
            paintHighlights();
        }
        root.querySelectorAll('.eot-mode').forEach(button => {
            const on = button.dataset.eotAction === 'mode-' + state.mode;
            button.classList.toggle('is-on', on);
            button.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (persist) storage.set(MODE_KEY, state.mode);
        if (state.mode === 'chart' && state.loaded) requestAnimationFrame(() => { if (!state.userMoved) fit(true); });
    }
    function scrollRowIntoView(id) {
        const row = tableBody.querySelector('tr[data-row="' + id + '"]');
        if (row) row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function paintHighlights() {
        const path = new Set(state.selected ? ancestors(state.selected) : []);
        const dept = departmentSelect.value;
        const query = searchInput.value.trim().toLowerCase();
        (state.mode === 'grid' ? grid : stage).querySelectorAll('.eot-tile[data-id]').forEach(card => {
            const id = Number(card.dataset.id);
            const node = state.nodes.get(id);
            card.classList.toggle('is-selected', id === state.selected);
            card.classList.toggle('is-path', path.has(id));
            card.classList.toggle('is-dim', !!dept && String(node.department_id) !== dept);
            card.classList.toggle('is-match', !!query && query.length > 1 && node.name.toLowerCase().includes(query));
        });
        stage.querySelectorAll('.eot-connector').forEach(line => {
            const id = Number(line.dataset.child);
            line.classList.toggle('is-path', path.has(id) || id === state.selected);
        });
        tableBody.querySelectorAll('tr[data-row]').forEach(tr => {
            const id = Number(tr.dataset.row);
            const node = state.nodes.get(id);
            tr.classList.toggle('is-selected', id === state.selected);
            tr.classList.toggle('is-path', path.has(id));
            tr.classList.toggle('is-dim', !!dept && String(node.department_id) !== dept);
            tr.classList.toggle('is-match', !!query && query.length > 1 && node.name.toLowerCase().includes(query));
        });
    }

    /* ---------- Pan & zoom ---------- */
    function apply() {
        stage.style.transform = 'translate(' + state.x + 'px,' + state.y + 'px) scale(' + state.scale + ')';
        zoomLabel.textContent = Math.round(state.scale * 100) + '%';
    }
    function zoomAt(nextScale, px, py) {
        const scale = Math.min(MAX_SCALE, Math.max(Math.min(.12, state.scale), nextScale));
        if (px === undefined) { px = viewport.clientWidth / 2; py = viewport.clientHeight / 2; }
        state.x = px - (px - state.x) * (scale / state.scale);
        state.y = py - (py - state.y) * (scale / state.scale);
        state.scale = scale;
        state.userMoved = true;
        apply();
    }
    function fit(readable = false) {
        const width = stage.offsetWidth, height = stage.offsetHeight;
        const vw = viewport.clientWidth, vh = viewport.clientHeight;
        if (!width || !height || !vw || !vh) return;
        const scale = Math.min(1, vw / width, vh / height);
        if (readable && scale < .72) {
            state.scale = .85;
            const company = stage.querySelector('.eot-tile.is-company');
            if (company) centerCard(company, false);
            else centerOn(state.roots[0], false);
            state.userMoved = false;
            return;
        }
        state.scale = scale;
        state.x = (vw - width * scale) / 2;
        state.y = Math.max(0, (vh - height * scale) / 2);
        state.userMoved = false;
        apply();
    }
    function centerOn(id, smooth) {
        centerCard(stage.querySelector('.eot-tile[data-id="' + id + '"]'), smooth);
    }
    function centerCard(card, smooth) {
        if (!card) return;
        let left = 0, top = 0, current = card;
        while (current && current !== stage) { left += current.offsetLeft; top += current.offsetTop; current = current.offsetParent; }
        state.x = viewport.clientWidth / 2 - (left + card.offsetWidth / 2) * state.scale;
        state.y = viewport.clientHeight / 3 - (top + card.offsetHeight / 2) * state.scale;
        state.userMoved = true;
        if (smooth) { stage.style.transition = 'transform .35s ease'; setTimeout(() => { stage.style.transition = ''; }, 380); }
        apply();
    }
    function pan(dx, dy) { state.x += dx; state.y += dy; state.userMoved = true; stage.style.transition = 'transform .2s ease'; setTimeout(() => { stage.style.transition = ''; }, 220); apply(); }

    const pointers = new Map();
    let drag = null, pinch = null, suppressClick = false;
    viewport.addEventListener('pointerdown', event => {
        if (event.button !== 0 || event.target.closest('button:not(.eot-card-open), a, input, select, .eot-pan, .eot-legend')) return;
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
        if (card) openInspector(Number(card.dataset.id));
    });
    grid.addEventListener('click', event => {
        const card = event.target.closest('.eot-tile[data-id]');
        if (card) openInspector(Number(card.dataset.id));
    });
    function toggleBranch(id) {
        const selector = '.eot-tile[data-id="' + id + '"]';
        const before = stage.querySelector(selector)?.getBoundingClientRect();
        const restoreFocus = document.activeElement?.matches('[data-toggle="' + id + '"]');
        if (state.collapsed.has(id)) state.collapsed.delete(id);
        else state.collapsed.add(id);
        render();
        const after = stage.querySelector(selector)?.getBoundingClientRect();
        if (state.mode === 'chart' && before && after) {
            state.x += before.left - after.left;
            state.y += before.top - after.top;
            apply();
        }
        if (restoreFocus) (state.mode === 'chart' ? stage : tableBody).querySelector('[data-toggle="' + id + '"]')?.focus({ preventScroll: true });
    }
    function reveal(id) {
        const hiddenBy = ancestors(id).filter(parent => state.collapsed.has(parent));
        hiddenBy.forEach(parent => state.collapsed.delete(parent));
        if (hiddenBy.length) render();
        return hiddenBy.length > 0;
    }
    function jumpTo(id) {
        if (state.mode === 'grid') {
            grid.querySelector('.eot-tile[data-id="' + id + '"]')?.scrollIntoView({ block: 'nearest' });
            return;
        }
        reveal(id);
        if (state.mode === 'table') scrollRowIntoView(id);
        else {
            state.scale = Math.max(.85, state.scale);
            centerOn(id, true);
        }
    }

    root.addEventListener('click', event => {
        const action = event.target.closest('[data-eot-action]')?.dataset.eotAction;
        if (!action) return;
        const step = 120;
        switch (action) {
            case 'zoom-in': zoomAt(state.scale * 1.2); break;
            case 'zoom-out': zoomAt(state.scale / 1.2); break;
            case 'fit': fit(); break;
            case 'center': if (state.roots.length === 1) jumpTo(state.roots[0]); else centerCard(stage.querySelector('.eot-tile.is-company'), true); break;
            case 'expand': state.collapsed.clear(); render(); break;
            case 'collapse': state.nodes.forEach(node => { if ((state.children.get(node.id) || []).length && node.parent !== null) state.collapsed.add(node.id); }); render(); fit(true); break;
            case 'refresh': load(false); break;
            case 'mode-chart': setMode('chart', true); break;
            case 'mode-grid': setMode('grid', true); break;
            case 'mode-table': setMode('table', true); break;
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
    levelSelect.addEventListener('change', () => {
        if (levelSelect.value === '') return;
        const node = [...state.nodes.values()].find(node => (node.level ?? -1) === Number(levelSelect.value));
        if (node) jumpTo(node.id);
        levelSelect.value = '';
    });
    searchInput.addEventListener('input', paintHighlights);
    searchInput.addEventListener('keydown', event => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const query = searchInput.value.trim().toLowerCase();
        if (!query) return;
        const nodes = Array.from(state.nodes.values());
        const found = nodes.find(node => String(node.employee_code || '').toLowerCase() === query || node.name.toLowerCase() === query) || nodes.find(node => node.name.toLowerCase().includes(query));
        if (found) openInspector(found.id);
    });
    searchInput.addEventListener('change', () => {
        const found = Array.from(state.nodes.values()).find(node => node.employee_code === searchInput.value || node.name === searchInput.value);
        if (found) openInspector(found.id);
    });
    inspector.addEventListener('cancel', event => { event.preventDefault(); closeInspector(); });

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

    async function loadProfile(node, target) {
        const request = new AbortController();
        state.profileRequest = request;
        const status = el('p', 'eot-note eot-profile-status', 'Loading additional profile details…');
        status.setAttribute('role', 'status');
        target.replaceChildren(status);

        try {
            const response = await fetch(node.profile_url, {
                headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal: request.signal,
            });
            if (!response.ok) throw new Error('Profile request failed');
            const profile = await response.json();
            if (request.signal.aborted || state.selected !== node.id) return;
            if (profile.id !== node.id) throw new Error('Unexpected employee profile');

            const content = document.createDocumentFragment();
            Object.entries(profile.sections).forEach(([title, fields]) => {
                const group = section(title);
                const facts = el('dl', 'eot-facts');
                Object.entries(fields).forEach(([label, value]) => facts.appendChild(fact(label, value, label === 'About' || label === 'Business address')));
                group.appendChild(facts);
                content.appendChild(group);
            });
            target.replaceChildren(content);
        } catch (error) {
            if (request.signal.aborted || state.selected !== node.id) return;
            status.textContent = 'Additional profile details could not be loaded.';
            const retry = action('Retry details', 'fa-rotate', null);
            retry.addEventListener('click', () => loadProfile(node, target));
            target.appendChild(retry);
        }
    }

    function openInspector(id) {
        const node = state.nodes.get(id);
        if (!node) return;
        if (!inspector.open) state.inspectorTrigger = document.activeElement;
        state.profileRequest?.abort();
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
        tags.appendChild(el('span', 'eot-tag', levelLabel(node)));
        tags.appendChild(el('span', 'eot-tag is-dept', node.role));
        tags.appendChild(el('span', 'eot-tag ' + (isActive(node) ? 'is-ok' : 'is-warn'), display(node.status)));
        hero.appendChild(tags);
        body.appendChild(hero);

        const actions = el('div', 'eot-actions');
        actions.appendChild(action('Email', 'fa-envelope', node.email ? 'mailto:' + node.email : null, { disabled: !node.email }));
        actions.appendChild(action('Call', 'fa-phone', node.mobile ? 'tel:' + String(node.mobile).replace(/[^\d+]/g, '') : null, { disabled: !node.mobile }));
        actions.appendChild(action('Full profile', 'fa-id-badge', node.show_url));
        body.appendChild(actions);

        const details = section('Employee details');
        const facts = el('dl', 'eot-facts');
        detailRows(node).forEach(([label, value, wide]) => facts.appendChild(fact(label, value, wide)));
        details.appendChild(facts);
        body.appendChild(details);

        if (node.profile_url) {
            const additional = el('div');
            body.appendChild(additional);
            loadProfile(node, additional);
        }

        const manager = node.parent !== null ? state.nodes.get(node.parent) : null;
        const reports = section(node.placement === 'level' ? 'Chart placement by level' : 'Reporting manager');
        if (manager) reports.appendChild(personButton(manager));
        else reports.appendChild(el('p', 'eot-note', 'Top of the visible organization'));
        body.appendChild(reports);

        const flow = section('Reporting flow');
        flow.appendChild(chainElement(id, true));
        body.appendChild(flow);

        const stats = el('div', 'eot-stats eot-section');
        const directs = el('div'); directs.appendChild(el('strong', '', node.directs)); directs.appendChild(el('span', '', 'Direct team'));
        const total = el('div'); total.appendChild(el('strong', '', node.total)); total.appendChild(el('span', '', 'Total Staff'));
        stats.appendChild(directs); stats.appendChild(total);
        body.appendChild(stats);

        const kids = state.children.get(id) || [];
        const team = section('Direct team (' + kids.length + ')');
        if (kids.length) kids.forEach(kid => team.appendChild(personButton(state.nodes.get(kid), state.nodes.get(kid).placement === 'level' ? 'Placed by level' : 'Recorded report')));
        else team.appendChild(el('p', 'eot-note', 'No team members under this employee.'));
        body.appendChild(team);

        const exports = section('Export employee details');
        const row = el('div', 'eot-exports');
        row.appendChild(action('CSV', 'fa-file-csv', null, { action: 'export-csv' }));
        row.appendChild(action('Excel', 'fa-file-excel', null, { action: 'export-excel' }));
        row.appendChild(action('PDF', 'fa-file-pdf', null, { action: 'export-pdf' }));
        exports.appendChild(row);
        body.appendChild(exports);

        body.querySelectorAll('[data-person]').forEach(button => button.addEventListener('click', () => openInspector(Number(button.dataset.person))));
        inspector.inert = false;
        if (!inspector.open) inspector.showModal();
        body.scrollTop = 0;
        const expanded = state.mode === 'grid' ? false : reveal(id);
        paintHighlights();
        inspector.querySelector('[data-eot-action="close-inspector"]').focus({ preventScroll: true });
        requestAnimationFrame(() => {
            if (state.selected !== id) return;
            if (state.mode === 'chart') {
                state.scale = Math.max(.85, state.scale);
                centerOn(id, !expanded);
            } else jumpTo(id);
        });
    }
    function closeInspector() {
        const selected = state.selected;
        state.selected = null;
        state.profileRequest?.abort();
        if (inspector.open) {
            inspector.close();
            const cards = state.mode === 'grid' ? grid : stage;
            const card = cards.querySelector('.eot-tile[data-id="' + selected + '"] .eot-card-open');
            const fallback = state.mode === 'table' ? tableBody.querySelector('[data-row="' + selected + '"]') : card;
            const trigger = state.inspectorTrigger?.isConnected ? state.inspectorTrigger : fallback;
            if (!root.hidden) (trigger || fallback || viewport).focus({ preventScroll: true });
        }
        state.inspectorTrigger = null;
        inspector.inert = true;
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
    window.addEventListener('resize', () => { if (!root.hidden && !state.userMoved) fit(true); });

    setMode(storage.get(MODE_KEY) || (window.matchMedia('(max-width: 575px)').matches ? 'grid' : 'chart'), false);
    const requested = new URLSearchParams(window.location.search).get('view');
    setView(requested === 'tree' || requested === 'list' ? requested : (storage.get(VIEW_KEY) === 'tree' ? 'tree' : 'list'), false);
})();
</script>
@endpush
