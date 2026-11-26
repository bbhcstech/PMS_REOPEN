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
    @include('admin.organization-directory.styles')
    @include('admin.organization-directory.tree-styles')
@endpush

@section('content')
<div id="pms-org-page" class="org-page orgd-page">
    <!-- Page Header Card -->
    <div class="org-header-card">
        <div class="org-header-title">
            <div class="org-header-icon">
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
    </div>

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
            <div class="orgd-chips-row" id="orgDesignationChipsRow" aria-label="Quick designation filters">
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
        @else
            <div class="orgd-chips-row" id="orgDesignationChipsRow" aria-label="Quick designation filters" hidden></div>
        @endif
    </section>

    <section
        class="orgd-tree-panel is-chart-mode"
        id="orgTreeRoot"
        data-endpoint="{{ route('organization.tree', array_filter(['company_id' => request()->integer('company_id') ?: null])) }}"
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
                <div class="orgd-group orgd-modes" role="group" aria-label="Organization view mode">
                    <button type="button" class="orgd-mode is-on" data-org-mode="chart" aria-pressed="true" title="Hierarchical tree chart">
                        <i class="fas fa-sitemap" aria-hidden="true"></i>
                        <span>Tree Chart</span>
                    </button>
                    <button type="button" class="orgd-mode" data-org-mode="grid" aria-pressed="false" title="Employee cards organized by designation level tiers">
                        <i class="fas fa-layer-group" aria-hidden="true"></i>
                        <span>Tree Grid</span>
                    </button>
                    <button type="button" class="orgd-mode" data-org-mode="cards" aria-pressed="false" title="All employee and user cards">
                        <i class="fas fa-grip" aria-hidden="true"></i>
                        <span>All Cards</span>
                    </button>
                </div>

                <div class="orgd-group orgd-chart-only" aria-label="Expand or collapse branches">
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

            <div class="orgd-level-grid" id="orgLevelGrid" aria-label="Employees organized by designation level" hidden></div>

            <div class="orgd-cards-grid" id="orgCardsGrid" aria-label="All employees and users cards" hidden></div>

            <div class="orgd-empty" id="orgEmptyState" hidden>
                <i class="fas fa-users-slash" aria-hidden="true"></i>
                <strong id="orgEmptyTitle">No matching employees found</strong>
                <span id="orgEmptyHint">Try selecting another designation or clearing your search filters.</span>
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
    const levelGrid = document.getElementById('orgLevelGrid');
    const cardsGrid = document.getElementById('orgCardsGrid');
    const emptyState = document.getElementById('orgEmptyState');
    const emptyTitle = document.getElementById('orgEmptyTitle');
    const emptyHint = document.getElementById('orgEmptyHint');
    const statusText = document.getElementById('orgStatusText');
    const zoomLabel = document.getElementById('orgZoomLabel');
    const searchInput = document.getElementById('orgSearchInput');
    const searchSuggestions = document.getElementById('orgSearchSuggestions');
    const designationSelect = document.getElementById('orgDesignationSelect');
    const departmentSelect = document.getElementById('orgDepartmentSelect');
    const chipsRow = document.getElementById('orgDesignationChipsRow');
    const isolateBtn = document.getElementById('orgIsolateBtn');
    const resetFiltersBtn = document.getElementById('orgResetFiltersBtn');
    const refreshBtn = document.getElementById('orgRefreshBtn');
    const exportCsvBtn = document.getElementById('orgExportCsvBtn');
    const printBtn = document.getElementById('orgPrintBtn');
    const inspector = document.getElementById('orgInspector');
    const inspectorBody = document.getElementById('orgInspectorBody');
    const closeInspectorBtn = document.getElementById('orgCloseInspectorBtn');
    const modeButtons = Array.from(root.querySelectorAll('[data-org-mode]'));

    const MODE_KEY = 'pms.orgd.mode';
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
        mode: 'chart',
        x: 0,
        y: 0,
        scale: 1,
        userMoved: false,
        lastFocusBeforeInspector: null,
        profileRequest: null,
        loading: false,
    };

    const storage = {
        get: function (k) { try { return window.localStorage.getItem(k); } catch (e) { return null; } },
        set: function (k, v) { try { window.localStorage.setItem(k, v); } catch (e) {} },
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
        syncFilterControls(rawNodes);
        renderAll(Boolean(shouldFit));

        if (state.selected && state.nodes.has(state.selected) && inspector.classList.contains('is-open')) {
            openInspector(state.nodes.get(state.selected), false);
        }
    }

    function syncFilterControls(nodes) {
        const deptCounts = new Map();
        const desigStats = new Map();

        nodes.forEach(function (n) {
            if (n.department) {
                const dk = String(n.department);
                deptCounts.set(dk, (deptCounts.get(dk) || 0) + 1);
            }
            if (n.designation && n.designation !== 'No designation') {
                const name = String(n.designation);
                const key = name.toLowerCase();
                const prev = desigStats.get(key) || { name: name, level: n.level, count: 0 };
                prev.count++;
                if (prev.level === null || prev.level === undefined) prev.level = n.level;
                desigStats.set(key, prev);
            }
        });

        const sortedDesigs = Array.from(desigStats.values()).sort(function (a, b) {
            const la = a.level === null || a.level === undefined ? -1 : Number(a.level);
            const lb = b.level === null || b.level === undefined ? -1 : Number(b.level);
            if (lb !== la) return lb - la;
            return a.name.localeCompare(b.name);
        });

        if (designationSelect) {
            const prevDesig = designationSelect.value;
            designationSelect.replaceChildren(new Option('All Designations (' + nodes.length + ')', ''));
            sortedDesigs.forEach(function (d) {
                const lvl = d.level !== null && d.level !== undefined ? ' (L' + d.level + ')' : '';
                designationSelect.add(new Option(d.name + lvl + ' — ' + d.count, d.name));
            });
            const hasPrev = sortedDesigs.some(function (d) { return d.name === prevDesig; });
            designationSelect.value = hasPrev ? prevDesig : '';
        }

        if (departmentSelect) {
            const prevDept = departmentSelect.value;
            departmentSelect.replaceChildren(new Option('All Departments', ''));
            Array.from(deptCounts.entries())
                .sort(function (a, b) { return a[0].localeCompare(b[0]); })
                .forEach(function (entry) {
                    departmentSelect.add(new Option(entry[0] + ' (' + entry[1] + ')', entry[0]));
                });
            departmentSelect.value = deptCounts.has(prevDept) ? prevDept : '';
        }

        if (chipsRow) {
            if (sortedDesigs.length === 0) {
                chipsRow.hidden = true;
                chipsRow.replaceChildren();
            } else {
                chipsRow.hidden = false;
                const frag = document.createDocumentFragment();
                frag.appendChild(el('span', 'orgd-chips-label', 'By Designation:'));
                const makeChip = function (value, label, count) {
                    const btn = el('button', 'orgd-chip');
                    btn.type = 'button';
                    btn.setAttribute('data-designation-chip', value);
                    btn.append(el('span', '', label), el('span', 'orgd-chip-count', String(count)));
                    return btn;
                };
                frag.appendChild(makeChip('', 'All', nodes.length));
                sortedDesigs.forEach(function (d) {
                    frag.appendChild(makeChip(d.name, d.name, d.count));
                });
                chipsRow.replaceChildren(frag);
            }
        }

        if (searchSuggestions) {
            const frag = document.createDocumentFragment();
            const seen = new Set();
            sortedDesigs.forEach(function (d) {
                if (!seen.has(d.name)) {
                    seen.add(d.name);
                    frag.appendChild(new Option(d.name, d.name));
                }
            });
            nodes.forEach(function (n) {
                if (n.name && !seen.has(n.name)) {
                    seen.add(n.name);
                    frag.appendChild(new Option(n.name, n.name));
                }
            });
            searchSuggestions.replaceChildren(frag);
        }
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
        if (!chipsRow) return;
        chipsRow.querySelectorAll('[data-designation-chip]').forEach(function (chip) {
            const chipVal = (chip.getAttribute('data-designation-chip') || '').trim().toLowerCase();
            chip.classList.toggle('is-active', chipVal === currentDesig);
        });
    }

    function setMode(mode, persist) {
        state.mode = (mode === 'grid' || mode === 'cards') ? mode : 'chart';
        root.classList.toggle('is-chart-mode', state.mode === 'chart');
        root.classList.toggle('is-grid-mode', state.mode === 'grid');
        root.classList.toggle('is-cards-mode', state.mode === 'cards');
        if (viewport) viewport.hidden = state.mode !== 'chart';
        if (levelGrid) levelGrid.hidden = state.mode !== 'grid';
        if (cardsGrid) cardsGrid.hidden = state.mode !== 'cards';

        modeButtons.forEach(function (btn) {
            const active = btn.getAttribute('data-org-mode') === state.mode;
            btn.classList.toggle('is-on', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (persist) storage.set(MODE_KEY, state.mode);
        renderAll(state.mode === 'chart');
    }

    function renderAll(shouldFit) {
        const filterState = computeFilterState();
        syncChipSelection();

        if (state.mode === 'grid') {
            renderLevelGrid(filterState);
        } else if (state.mode === 'cards') {
            renderCardsGrid(filterState);
        } else {
            renderTree(filterState);
        }
        updateStatusSummary(filterState);

        if (shouldFit && state.mode === 'chart') {
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

    function visibleNodesList(filterState) {
        const list = [];
        state.nodes.forEach(function (node) {
            if (filterState.hasFilter && !filterState.matches.has(node.id)) return;
            list.push(node);
        });
        return list.sort(function (a, b) {
            const la = a.level === null || a.level === undefined ? -1 : Number(a.level);
            const lb = b.level === null || b.level === undefined ? -1 : Number(b.level);
            if (lb !== la) return lb - la;
            return String(a.name || '').localeCompare(String(b.name || ''));
        });
    }

    function buildEmployeeCard(node, filterState, showPlacement) {
        const tile = el('div', 'orgd-tile is-card-mode');
        tile.tabIndex = 0;
        tile.setAttribute('role', 'button');
        tile.setAttribute('aria-label', node.name + ', ' + display(node.designation));
        if (state.selected === node.id) tile.classList.add('is-selected');
        if (filterState.pathIds.has(node.id) && state.selected !== node.id) tile.classList.add('is-path');
        if (filterState.hasFilter && filterState.matches.has(node.id)) tile.classList.add('is-match');

        tile.appendChild(el('span', 'orgd-tile-flag', state.selected === node.id ? 'Selected' : 'In Path'));

        const top = el('div', 'orgd-tile-top');
        top.appendChild(createPhoto(node));
        const who = el('div', 'orgd-who');
        const nameEl = el('div', 'orgd-name');
        nameEl.appendChild(el('span', '', node.name));
        if (isActiveStatus(node)) {
            nameEl.appendChild(icon('fa-circle-check'));
        }
        who.append(nameEl, el('p', 'orgd-designation', display(node.designation)));
        top.appendChild(who);
        tile.appendChild(top);

        const tags = el('div', 'orgd-tags');
        tags.appendChild(el('span', 'orgd-tag', levelBadge(node)));
        if (node.role) {
            tags.appendChild(el('span', 'orgd-tag is-dept', node.role));
        }
        if (node.department) {
            tags.appendChild(el('span', 'orgd-tag is-dept', node.department));
        }
        tile.appendChild(tags);

        const meta = el('div', 'orgd-card-meta');
        if (node.email) {
            const row = el('div', 'orgd-card-meta-row');
            row.append(icon('fa-envelope'), el('span', '', node.email));
            meta.appendChild(row);
        }
        if (node.mobile) {
            const row = el('div', 'orgd-card-meta-row');
            row.append(icon('fa-phone'), el('span', '', node.mobile));
            meta.appendChild(row);
        }
        if (showPlacement) {
            const mgr = node.parent && state.nodes.has(node.parent) ? state.nodes.get(node.parent) : null;
            if (mgr) {
                tile.appendChild(el('p', 'orgd-placement-note', (node.placement === 'level' ? 'Placed under ' : 'Reports to ') + mgr.name));
            }
        }
        if (meta.childNodes.length) {
            tile.appendChild(meta);
        }

        const foot = el('div', 'orgd-tile-foot');
        const counts = document.createElement('span');
        counts.append(
            el('strong', '', String(node.directs || 0)),
            document.createTextNode(' direct • '),
            el('strong', '', String(node.total || 0)),
            document.createTextNode(' team')
        );
        foot.append(el('span', '', node.employee_code || 'EMP'), counts);
        tile.appendChild(foot);

        tile.addEventListener('click', function () {
            selectNode(node.id, tile);
        });
        tile.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                selectNode(node.id, tile);
            }
        });

        return tile;
    }

    function renderLevelGrid(filterState) {
        if (!levelGrid) return;
        const list = visibleNodesList(filterState);
        if (state.nodes.size === 0 || (filterState.hasFilter && list.length === 0)) {
            levelGrid.replaceChildren();
            emptyTitle.textContent = state.nodes.size === 0 ? 'No employees in tree' : 'No matching designation or employee';
            emptyHint.textContent = state.nodes.size === 0
                ? 'Active employees added in the Employee section will appear here automatically.'
                : 'Adjust your designation filter or search query, or click Reset to view all employees.';
            emptyState.hidden = false;
            return;
        }
        emptyState.hidden = true;

        const tiers = new Map();
        list.forEach(function (node) {
            const lvl = node.level === null || node.level === undefined ? -1 : Number(node.level);
            if (!tiers.has(lvl)) tiers.set(lvl, []);
            tiers.get(lvl).push(node);
        });

        const sortedKeys = Array.from(tiers.keys()).sort(function (a, b) { return b - a; });
        const fragment = document.createDocumentFragment();
        sortedKeys.forEach(function (lvl) {
            const members = tiers.get(lvl);
            const group = el('section', 'orgd-level-group');
            const head = el('div', 'orgd-level-head');
            const title = el('h3', 'orgd-level-title');
            title.append(
                el('span', 'orgd-tag', lvl === -1 ? 'No level' : 'Level ' + lvl),
                el('span', '', lvl === -1 ? 'Unassigned Designation Level' : 'Designation Tier L' + lvl)
            );
            head.append(title, el('span', 'orgd-level-count', members.length + (members.length === 1 ? ' member' : ' members')));
            group.appendChild(head);

            const cards = el('div', 'orgd-level-cards');
            members.forEach(function (node) {
                cards.appendChild(buildEmployeeCard(node, filterState, true));
            });
            group.appendChild(cards);
            fragment.appendChild(group);
        });
        levelGrid.replaceChildren(fragment);
    }

    function renderCardsGrid(filterState) {
        if (!cardsGrid) return;
        const list = visibleNodesList(filterState);
        if (state.nodes.size === 0 || (filterState.hasFilter && list.length === 0)) {
            cardsGrid.replaceChildren();
            emptyTitle.textContent = state.nodes.size === 0 ? 'No employees in directory' : 'No matching employees or users found';
            emptyHint.textContent = state.nodes.size === 0
                ? 'Active employees added in the Employee section will appear here automatically.'
                : 'Try another search query or designation filter, or click Reset.';
            emptyState.hidden = false;
            return;
        }
        emptyState.hidden = true;

        const wrap = el('div', 'orgd-cards-wrap');
        list.forEach(function (node) {
            wrap.appendChild(buildEmployeeCard(node, filterState, true));
        });
        cardsGrid.replaceChildren(wrap);
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
        openInspector(node, true);
    }

    function reportingChain(id) {
        const chain = [];
        const seen = new Set();
        let cursor = state.nodes.get(id);
        while (cursor && !seen.has(cursor.id) && chain.length < 40) {
            seen.add(cursor.id);
            chain.unshift(cursor);
            cursor = cursor.parent ? state.nodes.get(cursor.parent) : null;
        }
        return chain;
    }

    async function loadProfileDetails(node, target) {
        if (!node.profile_url) return;
        if (state.profileRequest) state.profileRequest.abort();
        const controller = new AbortController();
        state.profileRequest = controller;

        const status = el('p', 'orgd-note', 'Loading complete employee details…');
        target.replaceChildren(status);

        try {
            const resp = await fetch(node.profile_url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
            });
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const profile = await resp.json();
            if (controller.signal.aborted || state.selected !== node.id) return;

            const frag = document.createDocumentFragment();
            Object.entries(profile.sections || {}).forEach(function (entry) {
                const secTitle = entry[0];
                const fields = entry[1] || {};
                const sec = el('div', 'orgd-section');
                sec.appendChild(el('h4', '', secTitle));
                const dl = el('dl', 'orgd-facts');
                Object.entries(fields).forEach(function (pair) {
                    const wide = pair[0] === 'About' || pair[0] === 'Business address';
                    const item = el('div', wide ? 'is-wide' : '');
                    item.append(el('dt', '', pair[0]), el('dd', '', display(pair[1])));
                    dl.appendChild(item);
                });
                sec.appendChild(dl);
                frag.appendChild(sec);
            });
            target.replaceChildren(frag);
        } catch (err) {
            if (controller.signal.aborted || state.selected !== node.id) return;
            status.textContent = 'Extended employee details could not be loaded.';
        }
    }

    function openInspector(node, focusClose) {
        if (state.profileRequest) state.profileRequest.abort();
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
        if (node.role) {
            tags.appendChild(el('span', 'orgd-tag is-dept', node.role));
        }
        tags.appendChild(el('span', 'orgd-tag ' + (isActiveStatus(node) ? 'is-ok' : 'is-warn'), display(node.status || 'Active')));
        if (node.employee_code) {
            tags.appendChild(el('span', 'orgd-tag is-dept', node.employee_code));
        }
        hero.appendChild(tags);
        inspectorBody.appendChild(hero);

        const actions = el('div', 'orgd-actions');
        const dirProfileUrl = node.org_show_url || node.show_url || '#';
        const dirLink = el('a', 'orgd-action' + (dirProfileUrl !== '#' ? '' : ' is-disabled'));
        dirLink.href = dirProfileUrl;
        dirLink.append(icon('fa-id-card'), el('span', '', 'Directory Profile'));

        const hrProfileLink = el('a', 'orgd-action' + (node.show_url ? '' : ' is-disabled'));
        hrProfileLink.href = node.show_url || '#';
        hrProfileLink.append(icon('fa-user-tie'), el('span', '', 'HR Profile'));

        const emailLink = el('a', 'orgd-action' + (node.email ? '' : ' is-disabled'));
        emailLink.href = node.email ? 'mailto:' + node.email : '#';
        emailLink.append(icon('fa-envelope'), el('span', '', 'Email'));

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
        actions.append(dirLink, hrProfileLink, emailLink, filterDesigBtn);
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

        const chain = reportingChain(node.id);
        if (chain.length > 0) {
            const chainSec = el('div', 'orgd-section');
            chainSec.appendChild(el('h4', '', 'Reporting Flow'));
            const chainWrap = el('div', 'orgd-chain');
            chain.forEach(function (step, idx) {
                const isSelf = step.id === node.id;
                const stepEl = el(isSelf ? 'span' : 'button', 'orgd-chain-step' + (isSelf ? ' is-self' : ''), step.name);
                if (!isSelf) {
                    stepEl.type = 'button';
                    stepEl.addEventListener('click', function () {
                        selectNode(step.id, stepEl);
                    });
                }
                chainWrap.appendChild(stepEl);
                if (idx < chain.length - 1) {
                    chainWrap.appendChild(icon('fa-chevron-right orgd-chain-sep'));
                }
            });
            chainSec.appendChild(chainWrap);
            inspectorBody.appendChild(chainSec);
        }

        if (node.parent && state.nodes.has(node.parent)) {
            const parentNode = state.nodes.get(node.parent);
            const mgrSec = el('div', 'orgd-section');
            mgrSec.appendChild(el('h4', '', node.placement === 'level' ? 'Placed Under (Level Hierarchy)' : 'Reports To'));
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

        const location = [node.address, node.country].filter(Boolean).join(', ');
        const factsSec = el('div', 'orgd-section');
        factsSec.appendChild(el('h4', '', 'Complete Employee Record'));
        const dl = el('dl', 'orgd-facts');
        [
            ['Employee ID', node.employee_code, false],
            ['Designation', node.designation, false],
            ['Level', levelBadge(node), false],
            ['Role', node.role, false],
            ['Department', node.department, false],
            ['Parent Dept', node.parent_department, false],
            ['Reporting Manager', node.reporting_to_name || 'Top level', false],
            ['Hierarchy Link', node.placement === 'level' ? 'By designation level' : (node.placement === 'reporting' ? 'Direct reporting manager' : 'Root level'), false],
            ['Joining Date', node.joining_date, false],
            ['Tenure', node.tenure, false],
            ['Employment Type', node.employment_type, false],
            ['Status', node.status, false],
            ['Gender', node.gender, false],
            ['Phone', node.mobile, false],
            ['Email', node.email, true],
            ['Company', node.company, true],
            ['Location', location, true],
            ['Skills', node.skills, true],
        ].forEach(function (row) {
            const item = el('div', row[2] ? 'is-wide' : '');
            item.append(el('dt', '', row[0]), el('dd', '', display(row[1])));
            dl.appendChild(item);
        });
        factsSec.appendChild(dl);
        inspectorBody.appendChild(factsSec);

        const extendedHost = el('div', 'orgd-extended-profile');
        inspectorBody.appendChild(extendedHost);
        loadProfileDetails(node, extendedHost);

        inspector.classList.add('is-open');
        inspector.setAttribute('aria-hidden', 'false');
        if (focusClose !== false) {
            closeInspectorBtn.focus();
        }
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

    modeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setMode(btn.getAttribute('data-org-mode'), true);
        });
    });

    if (chipsRow) {
        chipsRow.addEventListener('click', function (ev) {
            const chip = ev.target.closest('[data-designation-chip]');
            if (!chip) return;
            const val = chip.getAttribute('data-designation-chip') || '';
            if (designationSelect) designationSelect.value = val;
            renderAll(state.isolateBranch);
        });
    }

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

    async function refreshTreeData(manual) {
        if (state.loading) return;
        state.loading = true;
        if (refreshBtn && manual) refreshBtn.disabled = true;
        if (manual && statusText) statusText.textContent = 'Refreshing employee hierarchy…';
        try {
            const resp = await fetch(root.dataset.endpoint, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const payload = await resp.json();
            hydratePayload(payload, !state.userMoved && state.mode === 'chart');
        } catch (err) {
            if (manual && statusText) {
                statusText.textContent = 'Could not refresh organization tree. Please try again.';
            }
        } finally {
            state.loading = false;
            if (refreshBtn && manual) refreshBtn.disabled = false;
        }
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            refreshTreeData(true);
        });
    }

    // Keep Organization automatically synchronized when employees are added, updated, or deleted.
    document.addEventListener('pms:records-updated', function () {
        refreshTreeData(false);
    });
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) refreshTreeData(false);
    });

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
        const requestedMode = new URLSearchParams(window.location.search).get('view');
        const initialMode = (requestedMode === 'grid' || requestedMode === 'cards' || requestedMode === 'chart')
            ? requestedMode
            : (storage.get(MODE_KEY) || 'chart');
        state.mode = (initialMode === 'grid' || initialMode === 'cards') ? initialMode : 'chart';
        root.classList.toggle('is-chart-mode', state.mode === 'chart');
        root.classList.toggle('is-grid-mode', state.mode === 'grid');
        root.classList.toggle('is-cards-mode', state.mode === 'cards');
        if (viewport) viewport.hidden = state.mode !== 'chart';
        if (levelGrid) levelGrid.hidden = state.mode !== 'grid';
        if (cardsGrid) cardsGrid.hidden = state.mode !== 'cards';
        modeButtons.forEach(function (btn) {
            const active = btn.getAttribute('data-org-mode') === state.mode;
            btn.classList.toggle('is-on', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        hydratePayload(parsed, state.mode === 'chart');
    } catch (e) {
        statusText.textContent = 'Unable to parse initial organization data.';
    }
})();
</script>
@endpush
