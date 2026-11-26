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

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark) .orgd-page {
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

    .orgd-modes {
        gap: 4px;
    }

    .orgd-mode {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        height: 32px !important;
        min-height: 0 !important;
        padding: 0 11px !important;
        border: 0 !important;
        border-radius: 8px !important;
        background: transparent !important;
        color: var(--orgd-muted) !important;
        -webkit-text-fill-color: var(--orgd-muted) !important;
        font-size: 0.79rem;
        font-weight: 650;
        cursor: pointer;
        box-shadow: none !important;
        transition: background-color 0.16s, color 0.16s;
    }
    .orgd-mode i,
    .orgd-mode span {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }
    .orgd-mode:hover {
        background: var(--orgd-surface) !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
    }
    .orgd-mode.is-on {
        background: var(--orgd-accent) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.24) !important;
    }

    .orgd-tree-panel:not(.is-chart-mode) .orgd-chart-only {
        display: none !important;
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
        min-height: 480px;
    }

    /* Level Grid (Tree Grid) & All Employee Cards */
    .orgd-level-grid,
    .orgd-cards-grid {
        padding: 20px;
        background: var(--orgd-canvas);
        min-height: 480px;
    }
    .orgd-level-grid[hidden],
    .orgd-cards-grid[hidden],
    .orgd-viewport[hidden] {
        display: none !important;
    }

    .orgd-level-group + .orgd-level-group {
        margin-top: 22px;
    }

    .orgd-level-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--orgd-border);
    }

    .orgd-level-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--orgd-text);
        margin: 0;
    }

    .orgd-level-count {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--orgd-muted);
    }

    .orgd-level-cards,
    .orgd-cards-wrap {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(265px, 1fr));
        gap: 14px;
    }

    .orgd-tile.is-card-mode {
        width: 100%;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .orgd-card-meta {
        display: grid;
        gap: 5px;
        font-size: 0.77rem;
        color: var(--orgd-muted);
    }

    .orgd-card-meta-row {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .orgd-card-meta-row i {
        width: 14px;
        text-align: center;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
        font-size: 0.74rem;
        flex-shrink: 0;
    }

    .orgd-card-meta-row span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .orgd-placement-note {
        margin: 0;
        font-size: 0.74rem;
        font-weight: 600;
        color: var(--orgd-muted);
    }

    .orgd-chain {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
    }

    .orgd-chain-step {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px !important;
        border-radius: 999px !important;
        border: 1px solid var(--orgd-border) !important;
        background: var(--orgd-canvas) !important;
        color: var(--orgd-text) !important;
        -webkit-text-fill-color: var(--orgd-text) !important;
        font-size: 0.74rem;
        font-weight: 600;
        cursor: pointer;
    }

    .orgd-chain-step.is-self {
        border-color: var(--orgd-accent) !important;
        background: var(--orgd-accent-soft) !important;
        color: var(--orgd-accent) !important;
        -webkit-text-fill-color: var(--orgd-accent) !important;
        cursor: default;
    }

    .orgd-chain-sep {
        font-size: 0.65rem;
        color: var(--orgd-muted) !important;
        -webkit-text-fill-color: var(--orgd-muted) !important;
    }

    .orgd-body > .orgd-empty {
        min-height: 380px;
        background: var(--orgd-canvas);
        z-index: 4;
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
        width: 430px;
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
