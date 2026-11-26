
@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')
@include('admin.settings.partials.workforce-navigation')
<div id="pms-designation-page" class="designation-page" data-live-preserve data-live-records-off>
    <!-- Breadcrumb -->
    <div class="breadcrumb" data-live-key="designation-breadcrumb">
        <i class="fas fa-building"></i> Admin / Settings / Designations
    </div>

    <!-- Header Card -->
    <div class="header-card" data-live-key="designation-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div>
                <h1>Designation Management</h1>
                <p>Manage employee roles, levels and designation records.</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ \App\Support\WorkforceSettings::route('designations.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cloud-download-alt"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button type="button" class="dropdown-item" onclick="exportTo('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item" onclick="printTable()"><i class="fas fa-print text-info"></i> Print</button></li>
                </ul>
            </div>
            <a href="{{ \App\Support\WorkforceSettings::route('designations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" data-live-key="designation-success">
        <i class="fas fa-check-circle"></i>
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" data-live-key="designation-error">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>{{ str_contains(strtolower((string) session('error')), 'archive') ? 'Archive Failed:' : 'Error!' }}</strong> {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Error!</strong> {{ $errors->first() }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
        $levelsCount = $levelsCount ?? $designations->groupBy('level')->count();
        $topLevelCount = $topLevelCount ?? $designations->where('level', '<=', 2)->count();
        $recentCount = $recentCount ?? $designations->where('updated_at', '>=', now()->subDays(7))->count();
    @endphp

    @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'administrator'], true))
        <section class="card p-3 mb-4 level-settings-card" aria-labelledby="designation-level-settings" data-live-key="designation-level-settings">
            <div class="level-settings-head">
                <div class="level-settings-icon" aria-hidden="true">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="level-settings-copy">
                    <h3 id="designation-level-settings">Designation level settings</h3>
                    <p>Companies start with levels L0–L6. When an admin adds a designation at the maximum level, the next level becomes available automatically for this company. You can also change the limit here.</p>
                </div>
            </div>
            <form method="POST" action="{{ \App\Support\WorkforceSettings::route('designations.level-settings') }}" class="level-settings-form">
                @csrf
                @method('PUT')
                <div class="level-settings-control-group">
                    <label for="maximum_level" class="form-label">Maximum designation level</label>
                    <div class="level-settings-input-row">
                        <input id="maximum_level" name="maximum_level" type="number" class="form-control" min="7" step="1" required value="{{ old('maximum_level', \App\Services\DesignationLevels::count()) }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Save level limit
                        </button>
                    </div>
                    <div class="form-text">Total number of levels, starting at L0. For example, 8 allows L0–L7.</div>
                    @error('maximum_level')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </form>
        </section>
    @endif
    @if(\App\Models\Designation::where('level', '>', \App\Services\DesignationLevels::maximum())->exists())
        <div class="alert alert-warning" data-live-key="designation-level-warning">Some existing designations exceed this company's level limit. Edit their levels or ask your administrator to raise the limit. New designations must follow the configured limit.</div>
    @endif

    <!-- Stats Cards -->
    <div class="stats" data-live-key="designation-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h3>{{ $designations->total() }}</h3>
                <span>Total Designations</span>
                <p class="stat-sub">Available organization roles</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h3>{{ $levelsCount }}</h3>
                <span>Active Levels</span>
                <p class="stat-sub">Levels currently configured</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div>
                <h3>{{ $topLevelCount }}</h3>
                <span>Top Level (1-2)</span>
                <p class="stat-sub">Leadership designations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h3>{{ $recentCount }}</h3>
                <span>Recently Updated</span>
                <p class="stat-sub">Changed during last 7 days</p>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-card" data-live-key="designation-list">
        <div class="table-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h3>Designation List</h3>
                    <span class="muted">{{ $designations->total() }} designations available</span>
                </div>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="designationSearch" placeholder="Search designations..." />
            </div>
            <div id="designation-export-tools" class="designation-export-tools" data-live-preserve></div>
        </div>

        <!-- Level Filters Bar -->
        <div class="level-filters-bar">
            <span class="filter-label"><i class="fas fa-filter"></i> Filter by Level:</span>
            @for($i = 0; $i <= \App\Services\DesignationLevels::maximum(); $i++)
                <button class="level-filter-btn" data-level="{{ $i }}">L{{ $i }}</button>
            @endfor
            <button class="level-filter-btn reset-btn" onclick="resetFilters()"><i class="fas fa-undo-alt"></i> All</button>
        </div>

        <div class="table-wrapper">
            <table id="designationTable" class="designation-list-table" data-pms-toolbar-host="designation-export-tools">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="table-select-all">
                            </div>
                        </th>
                        <th><i class="fas fa-qrcode"></i> Code</th>
                        <th><i class="fas fa-user-tag"></i> Designation Name</th>
                        <th><i class="fas fa-chart-line"></i> Level</th>
                        <th><i class="fas fa-user-plus"></i> Added By</th>
                        <th><i class="fas fa-calendar-alt"></i> Last Updated</th>
                        <th class="text-end"><i class="fas fa-cog"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $designation)
                    <tr class="designation-row" data-level="{{ $designation->level }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input select-item" type="checkbox" value="{{ $designation->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="designation-info">
                                <div class="mini-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <strong class="designation-code">{{ $designation->unique_code ?? 'N/A' }}</strong>
                                    <span class="muted">ID: {{ str_pad($designation->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="designation-info">
                                <div class="mini-icon">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                                <div>
                                    <strong class="designation-name">{{ $designation->name ?? '-' }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($designation->level !== null)
                                <span class="level l{{ min((int) $designation->level, 6) }}">
                                    <i class="fas fa-chevron-up"></i> L{{ $designation->level }}
                                </span>
                            @else
                                <span class="level level-default"><i class="fas fa-question"></i> Not Set</span>
                            @endif
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="avatar-circle">
                                    <span>{{ substr($designation->addedBy?->name ?? 'S', 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="user-name">{{ $designation->addedBy?->name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $designation->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="update-date">{{ $designation->updated_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $designation->updated_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="action-btn" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ \App\Support\WorkforceSettings::route('designations.show', $designation->id) }}">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ \App\Support\WorkforceSettings::route('designations.edit', $designation->id) }}">
                                            <i class="fas fa-pen"></i> Edit Designation
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ \App\Support\WorkforceSettings::route('designations.archive.action', $designation->id) }}" method="POST" class="d-inline delete-form" data-designation-name="{{ $designation->name }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning">
                                                <i class="fas fa-box-archive"></i> Archive Designation
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-briefcase fa-4x"></i>
                                <h5>No Designations Found</h5>
                                <p>Get started by creating your first designation</p>
                                <a href="{{ \App\Support\WorkforceSettings::route('designations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Designation
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Show Entries -->
        @if($designations->count() > 0)
        <div class="footer">
            <div class="footer-left">
                <div class="show-entries">
                    <span>Show</span>
                    <select id="showEntries">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <div class="pagination-info">
                <i class="fas fa-chart-line"></i>
                Showing {{ $designations->firstItem() ?? 0 }} to {{ $designations->lastItem() ?? 0 }} of {{ $designations->total() }} designations
            </div>

            <div>
                <nav aria-label="Designation pagination">
                    <ul class="pagination">
                        @if ($designations->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-btn" href="{{ $designations->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        @php
                            $current = $designations->currentPage();
                            $last = $designations->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item"><a class="page-btn" href="{{ $designations->url(1) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">1</a></li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-dots">...</span></li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-btn {{ $i == $current ? 'active' : '' }}" href="{{ $designations->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-dots">...</span></li>
                            @endif
                            <li class="page-item"><a class="page-btn" href="{{ $designations->url($last) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">{{ $last }}</a></li>
                        @endif

                        @if ($designations->hasMorePages())
                            <li class="page-item">
                                <a class="page-btn" href="{{ $designations->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        @endif
    </div>

    <!-- Level Legend -->
    <div class="legend-card" data-live-key="designation-legend">
        <div class="legend-content">
            <span class="legend-title"><i class="fas fa-palette"></i> Level Legend:</span>
            @forelse(\App\Services\DesignationLevels::legend() as $level => $names)
                <span class="level l{{ min((int) $level, 6) }}" data-live-key="designation-level-{{ $level }}"><i class="fas fa-layer-group"></i> L{{ $level }} ({{ $names }})</span>
            @empty
                <span class="text-muted">No designation levels configured.</span>
            @endforelse
        </div>
    </div>
</div>

<style>
    /* ===== DESIGNATION PAGE DESIGN SYSTEM (LIGHT & DARK, SCOPED BY #pms-designation-page) ===== */
    #pms-designation-page,
    #pms-designation-page *:not(i):not(.fa):not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands) {
        box-sizing: border-box;
    }

    #pms-designation-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 15px;
        line-height: 1.55;
        padding: 28px 32px;
        min-height: 100vh;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 55%, #F8FAFC 100%);
        color: #0F172A;
        position: relative;
    }

    #pms-designation-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 0% 0%, rgba(47, 107, 255, 0.04) 0%, transparent 48%),
                    radial-gradient(circle at 100% 100%, rgba(79, 131, 255, 0.04) 0%, transparent 48%);
        pointer-events: none;
    }

    /* Breadcrumb */
    #pms-designation-page .breadcrumb {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        padding: 10px 18px;
        border-radius: 14px;
        border: 1px solid rgba(47, 107, 255, 0.16);
        margin-bottom: 22px;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        font-weight: 650;
        font-size: 0.9rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }

    #pms-designation-page .breadcrumb i {
        margin: 0;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
    }

    /* Header Card */
    #pms-designation-page .header-card {
        position: relative;
        z-index: 50;
        overflow: visible !important;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(8px);
        border-radius: 24px !important;
        padding: 24px 28px !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        box-shadow: 0 14px 34px -12px rgba(47, 107, 255, 0.12) !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        margin-bottom: 24px;
        transition: box-shadow 0.25s ease, border-color 0.25s ease;
    }

    #pms-designation-page .header-card:hover {
        box-shadow: 0 18px 40px -14px rgba(47, 107, 255, 0.18) !important;
        border-color: rgba(47, 107, 255, 0.24) !important;
    }

    #pms-designation-page .header-left {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    #pms-designation-page .header-icon {
        width: 64px;
        height: 64px;
        flex-shrink: 0;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: #ffffff !important;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        box-shadow: 0 12px 24px -8px rgba(47, 107, 255, 0.4);
        transition: transform 0.25s ease;
    }

    #pms-designation-page .header-card:hover .header-icon {
        transform: scale(1.03);
    }

    #pms-designation-page .header-card .header-icon i,
    #pms-designation-page .header-card .header-icon i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: transparent !important;
        filter: none !important;
        opacity: 1 !important;
    }

    #pms-designation-page .header-card h1 {
        font-size: clamp(1.55rem, 2.2vw, 2rem);
        font-weight: 800;
        line-height: 1.2;
        margin: 0 0 4px 0;
        background: linear-gradient(135deg, #0F172A, #1E4FCC);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    #pms-designation-page .header-card p {
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        font-size: 0.96rem;
        font-weight: 500;
        margin: 0;
    }

    /* Header Action Group (override Bootstrap .btn-group corner-merging & global pms-refresh gray/green rules) */
    #pms-designation-page .header-card .btn-group {
        position: relative;
        z-index: 60;
        overflow: visible !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
        box-shadow: none !important;
    }

    #pms-designation-page .header-card .btn-group > .btn,
    #pms-designation-page .header-card .btn-group > .dropdown > .btn {
        margin-left: 0 !important;
        border-radius: 14px !important;
    }

    #pms-designation-page .btn {
        min-height: 44px;
        padding: 10px 18px !important;
        border-radius: 14px !important;
        font-weight: 650 !important;
        font-size: 0.92rem !important;
        line-height: 1.25 !important;
        cursor: pointer;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease !important;
        text-decoration: none;
        white-space: nowrap;
    }

    #pms-designation-page .btn i {
        font-size: 0.96rem !important;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .btn-light {
        background: #ffffff !important;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        border: 1px solid rgba(47, 107, 255, 0.25) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04) !important;
    }

    #pms-designation-page .btn-light:hover,
    #pms-designation-page .btn-light:focus-visible {
        background: #EEF2FF !important;
        color: #1D4ED8 !important;
        -webkit-text-fill-color: #1D4ED8 !important;
        border-color: rgba(47, 107, 255, 0.45) !important;
        transform: translateY(-1px);
        box-shadow: 0 8px 18px -8px rgba(47, 107, 255, 0.28) !important;
    }

    #pms-designation-page .btn-primary {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        border: 1px solid transparent !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 8px 20px -6px rgba(47, 107, 255, 0.42) !important;
    }

    #pms-designation-page .btn-primary *,
    #pms-designation-page .btn-primary i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #pms-designation-page .btn-primary:hover,
    #pms-designation-page .btn-primary:focus-visible {
        background: linear-gradient(145deg, #3B73FF, #1E4FCC) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 12px 26px -8px rgba(47, 107, 255, 0.5) !important;
    }

    #pms-designation-page .archive-count-badge {
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border-radius: 999px;
        background: #F59E0B !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 0.76rem;
        font-weight: 800;
        line-height: 1;
    }

    /* Dropdown Menu */
    #pms-designation-page .dropdown-menu {
        z-index: 9999 !important;
        min-width: 225px;
        margin-top: 8px !important;
        padding: 8px !important;
        border-radius: 16px !important;
        background: #ffffff !important;
        border: 1px solid rgba(47, 107, 255, 0.18) !important;
        box-shadow: 0 20px 44px -12px rgba(15, 23, 42, 0.22) !important;
    }

    #pms-designation-page .header-card .dropdown-menu.show {
        display: block;
        visibility: visible;
        opacity: 1;
    }

    #pms-designation-page .dropdown-item {
        padding: 10px 14px !important;
        color: #1E293B !important;
        -webkit-text-fill-color: #1E293B !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        border-radius: 10px !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        text-decoration: none;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        width: 100%;
        min-height: 40px;
        white-space: nowrap;
        transition: background-color 0.16s ease, color 0.16s ease !important;
    }

    #pms-designation-page .dropdown-item i {
        width: 18px;
        text-align: center;
        font-size: 0.95rem;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .dropdown-item i.text-primary { color: #2F6BFF !important; -webkit-text-fill-color: #2F6BFF !important; }
    #pms-designation-page .dropdown-item i.text-success { color: #16A34A !important; -webkit-text-fill-color: #16A34A !important; }
    #pms-designation-page .dropdown-item i.text-danger { color: #DC2626 !important; -webkit-text-fill-color: #DC2626 !important; }
    #pms-designation-page .dropdown-item i.text-info { color: #0284C7 !important; -webkit-text-fill-color: #0284C7 !important; }

    #pms-designation-page .dropdown-item:hover {
        background: #EEF2FF !important;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        transform: none !important;
    }

    #pms-designation-page .dropdown-item.text-warning,
    #pms-designation-page .dropdown-item.text-warning i {
        color: #D97706 !important;
        -webkit-text-fill-color: #D97706 !important;
    }

    #pms-designation-page .dropdown-item.text-warning:hover {
        background: #FFFBEB !important;
        color: #B45309 !important;
        -webkit-text-fill-color: #B45309 !important;
    }

    /* Designation Level Settings Card */
    #pms-designation-page .level-settings-card {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.98) !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        border-radius: 22px !important;
        padding: 22px 26px !important;
        margin-bottom: 24px !important;
        box-shadow: 0 10px 28px -12px rgba(47, 107, 255, 0.1) !important;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    #pms-designation-page .level-settings-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    #pms-designation-page .level-settings-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.2);
        color: #2F6BFF !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    #pms-designation-page .level-settings-icon i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .level-settings-copy h3 {
        margin: 0 0 4px 0 !important;
        font-size: 1.08rem !important;
        font-weight: 750 !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    #pms-designation-page .level-settings-copy p {
        margin: 0 !important;
        font-size: 0.9rem !important;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        line-height: 1.5;
    }

    #pms-designation-page .level-settings-form {
        padding-left: 58px;
    }

    #pms-designation-page .level-settings-control-group .form-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1E293B !important;
        -webkit-text-fill-color: #1E293B !important;
        margin-bottom: 6px;
        display: block;
    }

    #pms-designation-page .level-settings-input-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    #pms-designation-page .level-settings-input-row input[type="number"] {
        width: 190px;
        max-width: 100%;
        height: 44px;
        padding: 0 14px;
        border-radius: 12px !important;
        border: 1px solid rgba(47, 107, 255, 0.26) !important;
        background: #F8FAFC !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        font-weight: 650;
        font-size: 0.95rem;
    }

    #pms-designation-page .level-settings-input-row input[type="number"]:focus {
        outline: none;
        background: #ffffff !important;
        border-color: #2F6BFF !important;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.14) !important;
    }

    #pms-designation-page .level-settings-control-group .form-text {
        margin-top: 6px;
        font-size: 0.82rem;
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    /* Stats Cards (explicitly neutralize pms-refresh.css :first-of-type & white .stat-icon i overrides) */
    #pms-designation-page .stats {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 26px;
    }

    #pms-designation-page .stats .stat-card,
    #pms-designation-page .stats .stat-card:first-of-type {
        background: #ffffff !important;
        padding: 22px 24px !important;
        border-radius: 22px !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        box-shadow: 0 10px 28px -12px rgba(47, 107, 255, 0.1) !important;
        color: #0F172A !important;
        display: flex !important;
        gap: 16px !important;
        align-items: center !important;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
        position: relative;
        overflow: hidden;
        min-height: 118px;
    }

    #pms-designation-page .stats .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4F83FF, #2F6BFF);
        transform: scaleX(0);
        transition: transform 0.25s ease;
    }

    #pms-designation-page .stats .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px -14px rgba(47, 107, 255, 0.18) !important;
        border-color: rgba(47, 107, 255, 0.28) !important;
    }

    #pms-designation-page .stats .stat-card:hover::after {
        transform: scaleX(1);
    }

    #pms-designation-page .stats .stat-card .stat-icon,
    #pms-designation-page .stats .stat-card:first-of-type .stat-icon {
        width: 56px !important;
        height: 56px !important;
        min-width: 56px;
        border-radius: 16px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.35rem !important;
        flex-shrink: 0;
        box-shadow: none !important;
    }

    #pms-designation-page .stats .stat-card .stat-icon i,
    #pms-designation-page .stats .stat-card:first-of-type .stat-icon i {
        background: transparent !important;
        background-color: transparent !important;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: 1.35rem !important;
        line-height: 1 !important;
        filter: none !important;
        opacity: 1 !important;
    }

    #pms-designation-page .stats .stat-card:nth-child(1) .stat-icon {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.22) !important;
        color: #2F6BFF !important;
    }

    #pms-designation-page .stats .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(145deg, #E0F2FE, #BAE6FD) !important;
        border: 1px solid rgba(2, 132, 199, 0.22) !important;
        color: #0284C7 !important;
    }

    #pms-designation-page .stats .stat-card:nth-child(3) .stat-icon {
        background: linear-gradient(145deg, #FEF3C7, #FDE68A) !important;
        border: 1px solid rgba(217, 119, 6, 0.24) !important;
        color: #D97706 !important;
    }

    #pms-designation-page .stats .stat-card:nth-child(4) .stat-icon {
        background: linear-gradient(145deg, #EDE9FE, #DDD6FE) !important;
        border: 1px solid rgba(109, 40, 217, 0.22) !important;
        color: #6D28D9 !important;
    }

    #pms-designation-page .stats .stat-card h3,
    #pms-designation-page .stats .stat-card:first-of-type h3 {
        font-size: 2rem !important;
        font-weight: 800 !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        margin: 0 0 4px 0 !important;
        line-height: 1.1 !important;
    }

    #pms-designation-page .stats .stat-card span,
    #pms-designation-page .stats .stat-card:first-of-type span {
        display: block;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        font-size: 0.78rem !important;
        font-weight: 750 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    #pms-designation-page .stats .stat-card .stat-sub,
    #pms-designation-page .stats .stat-card:first-of-type .stat-sub {
        font-size: 0.8rem !important;
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
        margin: 4px 0 0 0 !important;
        font-weight: 500;
    }

    /* Alerts */
    #pms-designation-page .alert {
        border-radius: 16px !important;
        padding: 14px 18px !important;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #pms-designation-page .alert i {
        font-size: 1.2rem;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .alert-success {
        background: #ECFDF5 !important;
        color: #065F46 !important;
        border: 1px solid #A7F3D0 !important;
        border-left: 4px solid #10B981 !important;
    }
    #pms-designation-page .alert-success i { color: #059669 !important; -webkit-text-fill-color: #059669 !important; }

    #pms-designation-page .alert-danger {
        background: #FEF2F2 !important;
        color: #991B1B !important;
        border: 1px solid #FECACA !important;
        border-left: 4px solid #EF4444 !important;
    }
    #pms-designation-page .alert-danger i { color: #DC2626 !important; -webkit-text-fill-color: #DC2626 !important; }

    /* Table Card */
    #pms-designation-page .table-card {
        position: relative;
        z-index: 2;
        background: #ffffff !important;
        border-radius: 24px !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        overflow: visible !important;
        box-shadow: 0 12px 32px -12px rgba(15, 23, 42, 0.08) !important;
    }

    #pms-designation-page .table-header {
        padding: 20px 24px;
        background: linear-gradient(180deg, #ffffff, #F8FAFC);
        border-radius: 24px 24px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgba(47, 107, 255, 0.1);
    }

    #pms-designation-page .table-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    #pms-designation-page .table-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.2);
        color: #2F6BFF !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    #pms-designation-page .table-title-icon i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .table-title h3 {
        font-size: 1.25rem !important;
        font-weight: 750 !important;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        background: transparent !important;
        margin: 0 !important;
    }

    #pms-designation-page .table-title .muted {
        display: block;
        margin-top: 2px;
        font-size: 0.85rem !important;
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
        font-weight: 500;
    }

    /* Search Box */
    #pms-designation-page .search-box {
        position: relative;
        flex: 0 1 320px;
        min-width: 220px;
    }

    #pms-designation-page .search-box input {
        width: 100%;
        height: 44px;
        padding: 0 16px 0 42px;
        border-radius: 14px !important;
        border: 1px solid rgba(47, 107, 255, 0.22) !important;
        outline: none;
        font-weight: 500;
        font-size: 0.94rem;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        background: #ffffff !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #pms-designation-page .search-box input::placeholder {
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    #pms-designation-page .search-box input:focus {
        border-color: #2F6BFF !important;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.14) !important;
    }

    #pms-designation-page .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        pointer-events: none;
        background: transparent !important;
        filter: none !important;
    }

    /* Shared Export Tools Host */
    #pms-designation-page .designation-export-tools {
        flex: 1 1 100%;
        min-width: 0;
    }

    #pms-designation-page .designation-export-tools .pms-table-tools {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    #pms-designation-page .designation-export-tools .pms-table-export__toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 42px;
        padding: 8px 16px;
        border: 1px solid #2F6BFF;
        border-radius: 12px;
        background: #2F6BFF;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }

    #pms-designation-page .designation-export-tools .pms-table-export__toggle span,
    #pms-designation-page .table-card .designation-export-tools .pms-table-export button.pms-table-export__toggle,
    #pms-designation-page .table-card .designation-export-tools .pms-table-export button.pms-table-export__toggle * {
        background: transparent !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #pms-designation-page .table-card .designation-export-tools .pms-table-export button.pms-table-export__toggle {
        background: #2F6BFF !important;
    }

    #pms-designation-page .designation-export-tools .pms-table-export__toggle:hover {
        background: #2455D8 !important;
        border-color: #2455D8 !important;
    }

    #pms-designation-page .designation-export-tools .pms-table-export__toggle:focus-visible {
        outline: 3px solid #7DA5FF;
        outline-offset: 2px;
    }

    /* Hide duplicate DataTables button strip since Export dropdown & shared toolbar handle exports */
    #pms-designation-page .dataTables_wrapper .dt-buttons {
        display: none !important;
    }

    /* Level Filters Bar */
    #pms-designation-page .level-filters-bar {
        padding: 14px 24px;
        background: #F8FAFC;
        border-bottom: 1px solid rgba(47, 107, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    #pms-designation-page .filter-label {
        font-weight: 750;
        color: #1E293B !important;
        -webkit-text-fill-color: #1E293B !important;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 4px;
    }

    #pms-designation-page .filter-label i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .level-filter-btn {
        background: #ffffff !important;
        border: 1px solid rgba(47, 107, 255, 0.22) !important;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        padding: 6px 15px !important;
        border-radius: 999px !important;
        font-size: 0.86rem !important;
        font-weight: 650 !important;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.18s ease;
    }

    #pms-designation-page .level-filter-btn i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .level-filter-btn:hover {
        background: #EEF2FF !important;
        border-color: #60A5FA !important;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
    }

    #pms-designation-page .level-filter-btn.active {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3) !important;
    }

    /* Table Wrapper & List Table */
    #pms-designation-page .table-wrapper {
        overflow-x: auto;
        padding: 10px 22px 18px;
    }

    #pms-designation-page .designation-list-table {
        width: 100%;
        min-width: 900px;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    #pms-designation-page .designation-list-table thead th {
        text-align: left;
        padding: 14px 16px;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        font-size: 0.78rem !important;
        font-weight: 750 !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: transparent !important;
        border-bottom: 1.5px solid rgba(47, 107, 255, 0.12) !important;
        white-space: nowrap;
    }

    #pms-designation-page .designation-list-table thead th i {
        margin-right: 6px;
        font-size: 0.82rem;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .designation-list-table tbody tr.designation-row {
        cursor: pointer;
    }

    #pms-designation-page .designation-list-table tbody td {
        background: #ffffff !important;
        padding: 15px 16px;
        border-top: 1px solid rgba(47, 107, 255, 0.1) !important;
        border-bottom: 1px solid rgba(47, 107, 255, 0.1) !important;
        font-size: 0.94rem;
        font-weight: 500;
        color: #0F172A;
        vertical-align: middle;
        transition: background-color 0.18s ease, border-color 0.18s ease;
    }

    #pms-designation-page .designation-list-table tbody td:first-child {
        border-left: 1px solid rgba(47, 107, 255, 0.1) !important;
        border-radius: 16px 0 0 16px;
    }

    #pms-designation-page .designation-list-table tbody td:last-child {
        border-right: 1px solid rgba(47, 107, 255, 0.1) !important;
        border-radius: 0 16px 16px 0;
    }

    #pms-designation-page .designation-list-table tbody tr:hover td {
        background: #F8FAFF !important;
        border-color: rgba(47, 107, 255, 0.24) !important;
    }

    /* Row Cell Elements & Icons */
    #pms-designation-page .designation-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #pms-designation-page .mini-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.18);
        color: #2F6BFF !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    #pms-designation-page .mini-icon i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
        line-height: 1;
    }

    #pms-designation-page .designation-row:hover .mini-icon {
        transform: scale(1.05);
    }

    #pms-designation-page .designation-code {
        font-weight: 750;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        font-size: 0.96rem;
    }

    #pms-designation-page .designation-name,
    #pms-designation-page .user-name,
    #pms-designation-page .update-date {
        font-weight: 650;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        font-size: 0.96rem;
    }

    #pms-designation-page .muted,
    #pms-designation-page .designation-list-table small,
    #pms-designation-page .designation-list-table .text-muted {
        display: block;
        margin-top: 2px;
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
        font-size: 0.8rem !important;
        font-weight: 500;
    }

    #pms-designation-page .user-info {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    #pms-designation-page .avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 750;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        font-size: 0.92rem;
        flex-shrink: 0;
    }

    /* Level Badges (L0–L6 + L7+ fallback) */
    #pms-designation-page .level {
        background: #EEF2FF;
        color: #1E40AF;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 750;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #pms-designation-page .level i {
        font-size: 0.76rem;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .l0 { background: linear-gradient(135deg, #F1F5F9, #E2E8F0) !important; color: #334155 !important; -webkit-text-fill-color: #334155 !important; border: 1px solid rgba(51, 65, 85, 0.2) !important; }
    #pms-designation-page .l1 { background: linear-gradient(135deg, #EEF2FF, #E0E7FF) !important; color: #1E40AF !important; -webkit-text-fill-color: #1E40AF !important; border: 1px solid rgba(47, 107, 255, 0.28) !important; }
    #pms-designation-page .l2 { background: linear-gradient(135deg, #ECFDF5, #D1FAE5) !important; color: #065F46 !important; -webkit-text-fill-color: #065F46 !important; border: 1px solid rgba(16, 185, 129, 0.28) !important; }
    #pms-designation-page .l3 { background: linear-gradient(135deg, #E0F2FE, #BAE6FD) !important; color: #075985 !important; -webkit-text-fill-color: #075985 !important; border: 1px solid rgba(2, 132, 199, 0.28) !important; }
    #pms-designation-page .l4 { background: linear-gradient(135deg, #FFFBEB, #FEF3C7) !important; color: #92400E !important; -webkit-text-fill-color: #92400E !important; border: 1px solid rgba(217, 119, 6, 0.28) !important; }
    #pms-designation-page .l5 { background: linear-gradient(135deg, #FFF7ED, #FFEDD5) !important; color: #9A3412 !important; -webkit-text-fill-color: #9A3412 !important; border: 1px solid rgba(234, 88, 12, 0.28) !important; }
    #pms-designation-page .l6 { background: linear-gradient(135deg, #FEF2F2, #FEE2E2) !important; color: #991B1B !important; -webkit-text-fill-color: #991B1B !important; border: 1px solid rgba(220, 38, 38, 0.28) !important; }
    #pms-designation-page .level-default { background: linear-gradient(135deg, #F8FAFC, #F1F5F9) !important; color: #475569 !important; -webkit-text-fill-color: #475569 !important; border: 1px solid rgba(100, 116, 139, 0.24) !important; }

    /* Row Action Button */
    #pms-designation-page .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px !important;
        border: 1px solid rgba(47, 107, 255, 0.16) !important;
        background: #F8FAFC !important;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        cursor: pointer;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }

    #pms-designation-page .action-btn i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .action-btn:hover,
    #pms-designation-page .action-btn[aria-expanded="true"] {
        background: #EEF2FF !important;
        border-color: rgba(47, 107, 255, 0.35) !important;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        transform: scale(1.04);
    }

    /* Checkboxes */
    #pms-designation-page .form-check {
        display: flex;
        align-items: center;
        margin: 0;
        min-height: 0;
    }

    #pms-designation-page .form-check-input {
        width: 18px;
        height: 18px;
        border: 2px solid #C7D2FE;
        border-radius: 5px;
        cursor: pointer;
    }

    #pms-designation-page .form-check-input:checked {
        background-color: #2F6BFF;
        border-color: #2F6BFF;
    }

    /* Footer & Pagination */
    #pms-designation-page .footer {
        padding: 18px 24px;
        background: #F8FAFC;
        border-radius: 0 0 24px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        color: #475569 !important;
        font-size: 0.92rem;
        font-weight: 500;
        border-top: 1px solid rgba(47, 107, 255, 0.1);
    }

    #pms-designation-page .show-entries {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    #pms-designation-page .show-entries select {
        background: #ffffff !important;
        border: 1px solid rgba(47, 107, 255, 0.22) !important;
        color: #0F172A !important;
        border-radius: 10px;
        padding: 6px 10px;
        width: 74px;
        font-weight: 600;
        cursor: pointer;
    }

    #pms-designation-page .pagination-info {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    #pms-designation-page .pagination-info i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .pagination {
        display: flex;
        gap: 6px;
        align-items: center;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    #pms-designation-page .page-btn {
        min-width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #ffffff !important;
        border: 1px solid rgba(47, 107, 255, 0.2) !important;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        font-size: 0.88rem;
        font-weight: 650;
        text-decoration: none;
        transition: all 0.18s ease;
    }

    #pms-designation-page .page-btn i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        background: transparent !important;
        filter: none !important;
    }

    #pms-designation-page .page-btn:hover:not(.disabled) {
        background: #EEF2FF !important;
        border-color: #60A5FA !important;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
    }

    #pms-designation-page .page-btn.active {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        border-color: transparent !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3);
    }

    #pms-designation-page .page-btn.disabled {
        background: #F1F5F9 !important;
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        cursor: not-allowed;
    }

    #pms-designation-page .page-dots {
        color: #94A3B8;
        padding: 0 4px;
    }

    /* Legend Card */
    #pms-designation-page .legend-card {
        position: relative;
        z-index: 1;
        margin-top: 24px;
        background: rgba(255, 255, 255, 0.98) !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        border-radius: 22px !important;
        padding: 20px 26px !important;
        box-shadow: 0 10px 28px -12px rgba(47, 107, 255, 0.08) !important;
    }

    #pms-designation-page .legend-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    #pms-designation-page .legend-title {
        font-weight: 750;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        font-size: 0.94rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-right: 4px;
    }

    #pms-designation-page .legend-title i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        filter: none !important;
        font-size: 1.05rem;
    }

    #pms-designation-page .legend-content .level {
        padding: 7px 14px !important;
        font-size: 0.84rem !important;
    }

    /* Empty State */
    #pms-designation-page .empty-state {
        padding: 56px 20px;
        text-align: center;
    }

    #pms-designation-page .empty-state i {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        background: transparent !important;
        filter: none !important;
        margin-bottom: 16px;
    }

    #pms-designation-page .empty-state h5 {
        color: #1E4FCC !important;
        font-weight: 750;
        font-size: 1.25rem;
        margin-bottom: 8px;
    }

    #pms-designation-page .empty-state p {
        color: #64748B !important;
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        #pms-designation-page .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        #pms-designation-page {
            padding: 20px;
        }
        #pms-designation-page .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 768px) {
        #pms-designation-page .stats {
            grid-template-columns: 1fr;
        }
        #pms-designation-page .level-settings-form {
            padding-left: 0;
        }
        #pms-designation-page .table-header {
            flex-direction: column;
            align-items: stretch;
        }
        #pms-designation-page .search-box {
            flex: 1 1 auto;
            max-width: none;
        }
        #pms-designation-page .footer {
            flex-direction: column;
            text-align: center;
        }
        #pms-designation-page .pagination {
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        #pms-designation-page {
            padding: 16px;
        }
        #pms-designation-page .header-card {
            padding: 20px !important;
        }
        #pms-designation-page .header-icon {
            width: 54px;
            height: 54px;
            font-size: 22px;
        }
        #pms-designation-page .header-card .dropdown-menu {
            right: auto !important;
            left: 0 !important;
            max-width: calc(100vw - 48px);
        }
    }

    @media print {
        #pms-designation-page .btn-group,
        #pms-designation-page .search-box,
        #pms-designation-page .level-filters-bar,
        #pms-designation-page .legend-card,
        #pms-designation-page .action-btn,
        #pms-designation-page .dropdown {
            display: none !important;
        }
        #pms-designation-page {
            background: #ffffff !important;
            padding: 0 !important;
        }
        #pms-designation-page .table-card {
            box-shadow: none !important;
            border: 1px solid #dddddd !important;
        }
    }

    /* ===== DARK MODE SYSTEM (ALL SELECTORS COVERED UNDER #pms-designation-page) ===== */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page {
        background: #070B1A !important;
        color: #EEF1FB !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .breadcrumb {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.24) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .breadcrumb i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.header-card, .level-settings-card, .stats .stat-card, .stats .stat-card:first-of-type, .table-card, .legend-card) {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.22) !important;
        box-shadow: 0 14px 36px rgba(0, 0, 0, 0.4) !important;
        color: #EEF1FB !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .header-card h1 {
        background: linear-gradient(135deg, #ffffff 0%, #60A5FA 100%) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.header-card p, .level-settings-copy p, .level-settings-control-group .form-text, .table-title .muted) {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.level-settings-copy h3, .level-settings-control-group .form-label, .table-title h3, .legend-title, .stats .stat-card h3, .stats .stat-card:first-of-type h3, .designation-name, .user-name, .update-date) {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .btn-light {
        background: rgba(47, 107, 255, 0.14) !important;
        border: 1px solid rgba(96, 165, 250, 0.35) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .btn-light:hover {
        background: rgba(79, 131, 255, 0.28) !important;
        border-color: rgba(96, 165, 250, 0.6) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .dropdown-menu {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.28) !important;
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, 0.65) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .dropdown-item {
        color: #E2E8F0 !important;
        -webkit-text-fill-color: #E2E8F0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .dropdown-item:hover {
        background: rgba(47, 107, 255, 0.22) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .dropdown-item.text-warning,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .dropdown-item.text-warning i {
        color: #FBBF24 !important;
        -webkit-text-fill-color: #FBBF24 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .dropdown-item.text-warning:hover {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #FDE68A !important;
        -webkit-text-fill-color: #FDE68A !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .level-settings-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .table-title-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .mini-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .avatar-circle {
        background: rgba(79, 131, 255, 0.2) !important;
        border-color: rgba(96, 165, 250, 0.4) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.level-settings-icon i, .table-title-icon i, .mini-icon i) {
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .stats .stat-card span {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .stats .stat-card .stat-sub {
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .stats .stat-card:nth-child(1) .stat-icon {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.34), rgba(47, 107, 255, 0.52)) !important;
        border-color: rgba(96, 165, 250, 0.6) !important;
        color: #DBEAFE !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .stats .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.32), rgba(2, 132, 199, 0.5)) !important;
        border-color: rgba(56, 189, 248, 0.6) !important;
        color: #E0F2FE !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .stats .stat-card:nth-child(3) .stat-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.32), rgba(217, 119, 6, 0.52)) !important;
        border-color: rgba(251, 191, 36, 0.6) !important;
        color: #FEF3C7 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .stats .stat-card:nth-child(4) .stat-icon {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.34), rgba(109, 40, 217, 0.52)) !important;
        border-color: rgba(167, 139, 250, 0.6) !important;
        color: #EDE9FE !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.table-header, .level-filters-bar, .footer) {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.16) !important;
        color: #CBD5E1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.level-settings-input-row input[type="number"], .search-box input, .show-entries select) {
        background: #141B3D !important;
        border: 1px solid rgba(96, 165, 250, 0.35) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .search-box input::placeholder {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.search-box i, .filter-label i, .designation-list-table thead th i, .pagination-info i, .legend-title i, .designation-code) {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.filter-label, .designation-list-table thead th) {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .level-filter-btn {
        background: #0F1530 !important;
        border-color: rgba(96, 165, 250, 0.3) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .level-filter-btn:hover {
        background: rgba(47, 107, 255, 0.24) !important;
        border-color: rgba(96, 165, 250, 0.55) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .level-filter-btn.active {
        background: linear-gradient(135deg, #4F83FF 0%, #2F6BFF 100%) !important;
        border-color: transparent !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .designation-list-table tbody td {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.14) !important;
        color: #E2E8F0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .designation-list-table tbody tr:hover td {
        background: #1B2552 !important;
        border-color: rgba(96, 165, 250, 0.34) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page :is(.muted, .designation-list-table small, .designation-list-table .text-muted) {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l0 { background: rgba(148, 163, 184, 0.2) !important; color: #E2E8F0 !important; -webkit-text-fill-color: #E2E8F0 !important; border-color: rgba(148, 163, 184, 0.4) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l1 { background: rgba(47, 107, 255, 0.24) !important; color: #BFDBFE !important; -webkit-text-fill-color: #BFDBFE !important; border-color: rgba(96, 165, 250, 0.45) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l2 { background: rgba(16, 185, 129, 0.22) !important; color: #6EE7B7 !important; -webkit-text-fill-color: #6EE7B7 !important; border-color: rgba(52, 211, 153, 0.42) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l3 { background: rgba(56, 189, 248, 0.22) !important; color: #7DD3FC !important; -webkit-text-fill-color: #7DD3FC !important; border-color: rgba(56, 189, 248, 0.42) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l4 { background: rgba(245, 158, 11, 0.22) !important; color: #FCD34D !important; -webkit-text-fill-color: #FCD34D !important; border-color: rgba(251, 191, 36, 0.42) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l5 { background: rgba(249, 115, 22, 0.22) !important; color: #FDBA74 !important; -webkit-text-fill-color: #FDBA74 !important; border-color: rgba(251, 146, 60, 0.42) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .l6 { background: rgba(239, 68, 68, 0.22) !important; color: #FCA5A5 !important; -webkit-text-fill-color: #FCA5A5 !important; border-color: rgba(248, 113, 113, 0.42) !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .level-default { background: rgba(148, 163, 184, 0.16) !important; color: #CBD5E1 !important; -webkit-text-fill-color: #CBD5E1 !important; border-color: rgba(148, 163, 184, 0.35) !important; }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .action-btn {
        background: rgba(47, 107, 255, 0.14) !important;
        border-color: rgba(96, 165, 250, 0.35) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .action-btn:hover,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .action-btn[aria-expanded="true"] {
        background: rgba(79, 131, 255, 0.28) !important;
        border-color: rgba(96, 165, 250, 0.6) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .page-btn {
        background: #0F1530 !important;
        border-color: rgba(96, 165, 250, 0.32) !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .page-btn:hover:not(.disabled) {
        background: rgba(47, 107, 255, 0.26) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .page-btn.active {
        background: linear-gradient(135deg, #4F83FF 0%, #2F6BFF 100%) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .empty-state i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .empty-state h5 {
        color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark-mode, [data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark) #pms-designation-page .empty-state p {
        color: #94A3B8 !important;
    }

    /* Solid floating archive feedback toast */
    .designation-toast-alert {
        position: fixed !important;
        top: 76px !important;
        right: 20px !important;
        z-index: 2147483647 !important;
        width: min(420px, calc(100vw - 24px)) !important;
        padding: 0 !important;
        pointer-events: auto !important;
    }

    .designation-toast-alert > .alert {
        background: #ffffff !important;
        background-color: #ffffff !important;
        border: 1px solid rgba(15, 23, 42, 0.12) !important;
        border-radius: 18px !important;
        box-shadow: 0 22px 50px rgba(15, 23, 42, 0.22) !important;
        padding: 18px 20px !important;
        margin: 0 !important;
        opacity: 1 !important;
        color: #1e293b !important;
    }

    .designation-toast-alert > .alert.alert-warning { border-left: 4px solid #f59e0b !important; color: #78350f !important; }
    .designation-toast-alert > .alert.alert-danger { border-left: 4px solid #ef4444 !important; color: #7f1d1d !important; }
    .designation-toast-alert > .alert.alert-success { border-left: 4px solid #10b981 !important; color: #065f46 !important; }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) .designation-toast-alert > .alert {
        background: #0F1530 !important;
        background-color: #0F1530 !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        box-shadow: 0 24px 56px rgba(0, 0, 0, 0.55) !important;
        color: #f1f5f9 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) .designation-toast-alert .text-muted {
        color: #cbd5e1 !important;
    }
</style>

@push('js')
<script>
$(document).ready(function() {
    var table = null;

    @if($designations->count() > 0)
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#designationTable') && $('#designationTable tbody tr.designation-row').length > 0) {
        table = $('#designationTable').DataTable({
            dom: 'lBfrtip',
            paging: false,
            searching: true,
            info: false,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: [0, 6] }
            ],
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: 'Copy',
                    title: 'Designation List',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Designation List',
                    filename: 'designation-list',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Designation List',
                    filename: 'designation-list',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    title: 'Designation List',
                    filename: 'designation-list',
                    pageSize: 'A4',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: 'Designation List',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search designations...",
                zeroRecords: "No matching records found",
            },
            initComplete: function() {
                $('.dataTables_filter').hide();
            }
        });
    }
    @endif

    // Custom search
    $('#designationSearch').on('keyup', function() {
        if (table) {
            table.search(this.value).draw();
        } else {
            var searchTerm = this.value.toLowerCase();
            $('.designation-row').each(function() {
                var rowText = $(this).text().toLowerCase();
                $(this).toggle(!searchTerm || rowText.indexOf(searchTerm) > -1);
            });
        }
        updateVisibleRowsForLevelFilter();
    });

    // Level filter functionality
    function updateVisibleRowsForLevelFilter() {
        var activeLevelBtn = $('.level-filter-btn.active');
        if (activeLevelBtn.length && activeLevelBtn.data('level') !== undefined && !activeLevelBtn.hasClass('reset-btn')) {
            var level = activeLevelBtn.data('level');
            var searchTerm = $('#designationSearch').val().toLowerCase();

            $('.designation-row').each(function() {
                var rowLevel = $(this).data('level');
                var rowText = $(this).text().toLowerCase();
                var levelMatch = (rowLevel == level);
                var searchMatch = !searchTerm || rowText.indexOf(searchTerm) > -1;

                if (levelMatch && searchMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    $('.level-filter-btn').on('click', function() {
        if ($(this).hasClass('reset-btn')) {
            resetFilters();
            return;
        }

        $('.level-filter-btn').removeClass('active');
        $(this).addClass('active');
        var level = $(this).data('level');
        var searchTerm = $('#designationSearch').val().toLowerCase();

        $('.designation-row').each(function() {
            var rowLevel = $(this).data('level');
            var rowText = $(this).text().toLowerCase();
            var levelMatch = (rowLevel == level);
            var searchMatch = !searchTerm || rowText.indexOf(searchTerm) > -1;

            if (levelMatch && searchMatch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Reset filters
    window.resetFilters = function() {
        $('.level-filter-btn').removeClass('active');
        $('#designationSearch').val('');
        $('.designation-row').show();
        if (table) {
            table.search('').draw();
        }
    };

    // Show Entries dropdown
    $('#showEntries').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = window.location.href;
        var url = new URL(currentUrl);
        url.searchParams.set('per_page', perPage);
        window.location.href = url.toString();
    });

    // Keep table selection available for the shared selection/export toolbar.
    $(document).on('change', '.select-item', function() {
        var total = $('.select-item').length;
        var checked = $('.select-item:checked').length;
        $('#table-select-all').prop('checked', total > 0 && total === checked)
            .prop('indeterminate', checked > 0 && checked < total);
    });

    $('#table-select-all').on('change', function() {
        $('.select-item').prop('checked', $(this).is(':checked'));
    });

    // Export functions
    window.exportTo = function(format) {
        var buttonSelectors = {
            copy: '.buttons-copy',
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };
        var selector = buttonSelectors[format];

        if (!selector || !table.button(selector).node()) {
            if (typeof toastr !== 'undefined') {
                toastr.error('The selected export option is currently unavailable.');
            } else {
                alert('The selected export option is currently unavailable.');
            }
            return;
        }

        table.button(selector).trigger();

        if (typeof toastr !== 'undefined' && format !== 'copy' && format !== 'print') {
            toastr.success(format.toUpperCase() + ' export started.');
        }
    };

    window.printTable = function() {
        exportTo('print');
    };

    // Row click for view details
    $(document).on('click', '#designationTable tbody tr.designation-row', function(e) {
        if (!$(e.target).closest('input, a, button, .dropdown, .form-check, .action-btn, .dropdown-menu, .btn, .level').length) {
            var designationId = $(this).find('.select-item').val();
            if (designationId) {
                window.location.href = "{{ \App\Support\WorkforceSettings::route('designations.show', '') }}/" + designationId;
            }
        }
    });

    // Floating toast notification matching parent_departments style
    function showArchiveToast(type, contentNodeOrText) {
        $('.designation-toast-alert').remove();

        var icons = {
            success: 'fas fa-check-circle text-success',
            danger: 'fas fa-times-circle text-danger',
            warning: 'fas fa-exclamation-triangle text-warning',
            info: 'fas fa-info-circle text-info'
        };
        var colors = {
            success: 'alert-success',
            danger: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        };

        var wrapper = document.createElement('div');
        wrapper.className = 'designation-toast-alert';

        var alertBox = document.createElement('div');
        alertBox.className = 'alert ' + (colors[type] || 'alert-warning') + ' fade show d-flex align-items-start gap-3';
        alertBox.setAttribute('role', 'alert');

        var icon = document.createElement('i');
        icon.className = (icons[type] || 'fas fa-info-circle') + ' fs-4';

        var body = document.createElement('div');
        body.className = 'flex-grow-1';
        if (typeof contentNodeOrText === 'string') {
            body.textContent = contentNodeOrText;
        } else {
            body.appendChild(contentNodeOrText);
        }

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'alert');

        alertBox.append(icon, body, closeBtn);
        wrapper.appendChild(alertBox);
        document.body.appendChild(wrapper);

        setTimeout(function () {
            if (window.jQuery && $(alertBox).alert) {
                try { $(alertBox).alert('close'); } catch (_) {}
            }
            setTimeout(function () { wrapper.remove(); }, 300);
        }, 7000);
    }

    function handleDesignationArchiveError(message) {
        var box = document.createElement('div');
        var lower = String(message || '').toLowerCase();
        var isEmployeeBlocked = lower.includes('tagged') || lower.includes('assigned') || lower.includes('employee');

        var titleWrap = document.createElement('div');
        titleWrap.className = 'mb-2';
        var title = document.createElement('strong');
        title.textContent = 'Cannot Archive Designation';
        titleWrap.appendChild(title);
        box.appendChild(titleWrap);

        var msgDiv = document.createElement('div');
        msgDiv.textContent = message || 'Unable to archive designation.';
        box.appendChild(msgDiv);

        if (isEmployeeBlocked) {
            var hint = document.createElement('div');
            hint.className = 'mt-2 small text-muted';
            var hintIcon = document.createElement('i');
            hintIcon.className = 'fas fa-info-circle me-1';
            hint.append(hintIcon, document.createTextNode('Reassign employees to another designation before archiving.'));
            box.appendChild(hint);
        }

        showArchiveToast(isEmployeeBlocked ? 'warning' : 'danger', box);
    }

    // Individual archive via AJAX so errors render immediately without page reload
    $(document).on('submit', 'form.delete-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var designationName = form.data('designation-name') || 'this designation';

        if (!confirm('Archive designation "' + designationName + '"?\n\nIt will move to Archived Designations and can be restored later.')) {
            return;
        }

        var toggleBtn = form.closest('.dropdown').find('[data-bs-toggle="dropdown"]')[0];
        if (toggleBtn && window.bootstrap && bootstrap.Dropdown) {
            try { bootstrap.Dropdown.getOrCreateInstance(toggleBtn).hide(); } catch (_) {}
        }

        var btn = form.find('button[type="submit"]');
        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Archiving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response && response.status === 'success') {
                    var row = form.closest('tr');
                    row.fadeOut(350, function() {
                        if (table) {
                            table.row(row).remove().draw(false);
                        } else {
                            $(this).remove();
                        }
                        var totalEl = $('.designation-page .stat-card:first h3');
                        var currentTotal = parseInt(totalEl.text(), 10);
                        if (!isNaN(currentTotal) && currentTotal > 0) {
                            totalEl.text(currentTotal - 1);
                        }
                        var badgeEl = $('.designation-page .archive-count-badge');
                        if (badgeEl.length) {
                            var archCount = parseInt(badgeEl.text(), 10);
                            if (!isNaN(archCount)) badgeEl.text(archCount + 1);
                        }
                        showArchiveToast('success', response.message || 'Designation archived successfully.');
                        if ($('.designation-row').length === 0) {
                            setTimeout(function() { location.reload(); }, 600);
                        }
                    });
                } else {
                    handleDesignationArchiveError(response && response.message ? response.message : 'Cannot archive designation.');
                }
            },
            error: function(xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Unable to archive designation.';
                handleDesignationArchiveError(message);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    @if(session('error'))
        handleDesignationArchiveError(@json(session('error')));
    @endif

    // Toastr options
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }
});
</script>
@endpush

@endsection
