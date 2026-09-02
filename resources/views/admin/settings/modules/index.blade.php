@extends('admin.layout.app')

@section('title', 'Module Management')

@php
/**
 * Resolves any icon value stored in DB to a valid Boxicons CSS class.
 *
 * DB values can be:
 *   1. Full bx class:   "bx bx-home-smile"  → use as-is
 *   2. Bare name:       "folder-kanban"      → try bx bx-{name}, fallback to mapping
 *   3. Heroicon prefix: "heroicon-o-cube"    → map to nearest bx equivalent
 */
function resolveModuleIcon(?string $icon): string {
    if (empty($icon)) {
        return 'bx bx-cube';
    }

    // Already a full bx class
    if (str_starts_with($icon, 'bx ')) {
        return $icon;
    }

    // Heroicon prefix → map to Boxicons
    if (str_starts_with($icon, 'heroicon-')) {
        $heroMap = [
            'heroicon-o-cube'            => 'bx bx-cube',
            'heroicon-o-user'            => 'bx bx-user',
            'heroicon-o-users'           => 'bx bx-group',
            'heroicon-o-cog'             => 'bx bx-cog',
            'heroicon-o-chart-bar'       => 'bx bx-bar-chart-alt-2',
            'heroicon-o-document-text'   => 'bx bx-file-blank',
            'heroicon-o-folder'          => 'bx bx-folder',
            'heroicon-o-calendar'        => 'bx bx-calendar',
            'heroicon-o-clock'           => 'bx bx-time',
            'heroicon-o-shield-check'    => 'bx bx-shield-quarter',
            'heroicon-o-lock-closed'     => 'bx bx-lock-alt',
            'heroicon-o-tag'             => 'bx bx-tag',
            'heroicon-o-pencil'          => 'bx bx-pencil',
            'heroicon-o-trash'           => 'bx bx-trash',
            'heroicon-o-bell'            => 'bx bx-bell',
            'heroicon-o-check-circle'    => 'bx bx-check-circle',
            'heroicon-o-exclamation'     => 'bx bx-error',
            'heroicon-o-view-grid'       => 'bx bx-grid-alt',
        ];
        return $heroMap[$icon] ?? 'bx bx-cube';
    }

    // Bare name (Lucide / other) → map to nearest Boxicons class
    $bareMap = [
        'folder-kanban'  => 'bx bx-task',
        'users'          => 'bx bx-group',
        'calendar-check' => 'bx bx-calendar-check',
        'calendar-days'  => 'bx bx-calendar',
        'life-buoy'      => 'bx bx-help-circle',
        'handshake'      => 'bx bx-handshake',
        'file-signature' => 'bx bx-file-blank',
        'bar-chart'      => 'bx bx-bar-chart-alt-2',
        'bar-chart-2'    => 'bx bx-bar-chart-alt-2',
        'layout'         => 'bx bx-layout',
        'grid'           => 'bx bx-grid-alt',
        'box'            => 'bx bx-box',
        'cube'           => 'bx bx-cube',
        'user'           => 'bx bx-user',
        'settings'       => 'bx bx-cog',
        'shield'         => 'bx bx-shield-quarter',
        'lock'           => 'bx bx-lock-alt',
        'bell'           => 'bx bx-bell',
        'home'           => 'bx bx-home-smile',
        'folder'         => 'bx bx-folder',
        'calendar'       => 'bx bx-calendar',
        'clock'          => 'bx bx-time',
        'tag'            => 'bx bx-tag',
        'clipboard'      => 'bx bx-clipboard',
        'wallet'         => 'bx bx-wallet',
        'sitemap'        => 'bx bx-sitemap',
        'award'          => 'bx bx-trophy',
        'activity'       => 'bx bx-pulse',
        'log'            => 'bx bx-list-ul',
        'key'            => 'bx bx-key',
        'briefcase'      => 'bx bx-briefcase',
        'link'           => 'bx bx-link',
        'check'          => 'bx bx-check-circle',
        'message'        => 'bx bx-chat',
        'dollar-sign'    => 'bx bx-dollar-circle',
        'trending-up'    => 'bx bx-trending-up',
        'book'           => 'bx bx-book',
        'map'            => 'bx bx-map',
        'phone'          => 'bx bx-phone',
        'mail'           => 'bx bx-envelope',
        'globe'          => 'bx bx-globe',
        'package'        => 'bx bx-package',
        'layers'         => 'bx bx-layer',
        'toggle-right'   => 'bx bx-toggle-right',
        'percent'        => 'bx bx-percent',
    ];

    if (isset($bareMap[$icon])) {
        return $bareMap[$icon];
    }

    // Last resort: try bx bx-{icon} directly
    return 'bx bx-' . $icon;
}
@endphp

@section('content')
<style>
    :root {
        --mm-emerald: #0f744c;
        --mm-emerald-dark: #0b5a3a;
        --mm-emerald-light: rgba(15, 116, 76, 0.08);
        --mm-emerald-border: rgba(15, 116, 76, 0.2);
        --mm-slate-bg: #f8fafc;
        --mm-slate-border: #e2e8f0;
        --mm-slate-heading: #0f172a;
        --mm-slate-body: #334155;
        --mm-slate-muted: #64748b;
    }

    /* Page Entrance Animations */
    @keyframes mmFadeSlideUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .mm-animate-fade {
        animation: mmFadeSlideUp 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .mm-stagger-1 { animation-delay: 0.05s; }
    .mm-stagger-2 { animation-delay: 0.1s; }
    .mm-stagger-3 { animation-delay: 0.15s; }

    /* Button Enhancements */
    .btn-mm-primary {
        background: linear-gradient(135deg, #0f744c 0%, #0d6542 100%);
        color: #ffffff !important;
        border: none;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.55rem 1.25rem;
        box-shadow: 0 4px 12px rgba(15, 116, 76, 0.2);
        transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .btn-mm-primary:hover {
        background: linear-gradient(135deg, #0b5a3a 0%, #08492f 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 116, 76, 0.3);
        color: #ffffff !important;
    }
    .btn-mm-primary:active {
        transform: translateY(0) scale(0.98);
    }

    /* Stat Cards Hover Lift */
    .mm-stat-card {
        border-radius: 12px;
        border: 1px solid var(--mm-slate-border);
        background: #ffffff;
        transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .mm-stat-card:hover {
        transform: translateY(-3px);
        border-color: rgba(15, 116, 76, 0.3);
        box-shadow: 0 12px 24px -6px rgba(15, 116, 76, 0.1), 0 4px 12px rgba(0, 0, 0, 0.02);
    }
    .mm-stat-card:hover .mm-icon-wrapper {
        transform: scale(1.08);
    }
    .mm-icon-wrapper {
        transition: transform 0.3s ease;
    }

    /* Table & Card Redesign */
    .mm-card-main {
        border-radius: 12px;
        border: 1px solid var(--mm-slate-border);
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.03);
    }

    .mm-table-container {
        border-radius: 0 0 12px 12px;
    }

    .mm-table thead th {
        background-color: #f8fafc !important;
        color: var(--mm-slate-muted) !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        padding: 0.85rem 1.25rem !important;
        border-bottom: 1px solid var(--mm-slate-border) !important;
    }

    .mm-table tbody tr {
        transition: background-color 0.2s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .mm-table tbody tr:hover {
        background-color: rgba(15, 116, 76, 0.02) !important;
    }

    .mm-table tbody td {
        padding: 1rem 1.25rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    /* Custom Input Focus Ring */
    .mm-search-input:focus {
        border-color: var(--mm-emerald) !important;
        box-shadow: 0 0 0 3px rgba(15, 116, 76, 0.15) !important;
    }

    /* Status Switch Styling */
    .mm-switch .form-check-input {
        width: 2.5em;
        height: 1.35em;
        cursor: pointer;
    }
    .mm-switch .form-check-input:checked {
        background-color: var(--mm-emerald);
        border-color: var(--mm-emerald);
    }

    /* Action Buttons */
    .btn-icon-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .btn-icon-action:hover {
        transform: translateY(-1px);
    }

    /* Toast Notification Animation */
    .mm-toast-alert {
        border-left: 4px solid var(--mm-emerald);
        border-radius: 8px;
        background: #f0fdf4;
        animation: mmFadeSlideUp 0.35s cubic-bezier(0.22, 1, 0.36, 1);
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Action -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 mm-animate-fade">
        <div>
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('settings.company') }}" class="text-muted text-decoration-none">Settings</a></li>
                    <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">Module Management</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Module Management</h3>
            <p class="text-muted mb-0 small">Configure application modules, navigation routes, hierarchy, and access control.</p>
        </div>

        <button type="button" class="btn btn-mm-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModuleModal">
            <i class="bx bx-plus fs-5"></i>
            <span>Add New Module</span>
        </button>
    </div>

    <!-- Toast Success Notification -->
    @if(session('success'))
        <div class="alert alert-dismissible fade show mm-toast-alert p-3 mb-4 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <i class="bx bx-check fs-5"></i>
                </div>
                <div>
                    <strong class="text-success d-block small">Success</strong>
                    <span class="text-dark small">{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show p-3 mb-4 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                    <i class="bx bx-error-circle fs-5"></i>
                </div>
                <div>
                    <strong class="text-danger d-block small">Validation Error</strong>
                    <span class="text-dark small">{{ $errors->first() }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary Statistic Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Modules -->
        <div class="col-sm-6 col-lg-3 mm-animate-fade mm-stagger-1">
            <div class="card mm-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block fs-tiny text-uppercase tracking-wider">Total Modules</span>
                        <h3 class="fw-extrabold text-dark mb-0 mt-1">{{ $modules->count() }}</h3>
                        <small class="text-success d-inline-flex align-items-center gap-1 mt-1 fs-tiny">
                            <i class="bx bx-check-circle"></i> System registered
                        </small>
                    </div>
                    <div class="mm-icon-wrapper rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: rgba(15, 116, 76, 0.1); color: var(--mm-emerald);">
                        <i class="bx bx-grid-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Modules -->
        <div class="col-sm-6 col-lg-3 mm-animate-fade mm-stagger-2">
            <div class="card mm-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block fs-tiny text-uppercase tracking-wider">Active Modules</span>
                        <h3 class="fw-extrabold text-success mb-0 mt-1">{{ $modules->where('is_active', true)->count() }}</h3>
                        <small class="text-muted d-inline-flex align-items-center gap-1 mt-1 fs-tiny">
                            Operational now
                        </small>
                    </div>
                    <div class="mm-icon-wrapper rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="bx bx-toggle-right fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Core Modules -->
        <div class="col-sm-6 col-lg-3 mm-animate-fade mm-stagger-3">
            <div class="card mm-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block fs-tiny text-uppercase tracking-wider">Core Modules</span>
                        <h3 class="fw-extrabold text-info mb-0 mt-1">{{ $modules->where('is_core', true)->count() }}</h3>
                        <small class="text-info d-inline-flex align-items-center gap-1 mt-1 fs-tiny">
                            <i class="bx bx-shield-alt-2"></i> System locked
                        </small>
                    </div>
                    <div class="mm-icon-wrapper rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: rgba(3, 195, 236, 0.12); color: #03c3ec;">
                        <i class="bx bx-shield-quarter fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Modules -->
        <div class="col-sm-6 col-lg-3 mm-animate-fade mm-stagger-3">
            <div class="card mm-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold d-block fs-tiny text-uppercase tracking-wider">Parent Modules</span>
                        <h3 class="fw-extrabold text-warning mb-0 mt-1">{{ $parentModules->count() }}</h3>
                        <small class="text-muted d-inline-flex align-items-center gap-1 mt-1 fs-tiny">
                            Nav categories
                        </small>
                    </div>
                    <div class="mm-icon-wrapper rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: rgba(255, 171, 0, 0.12); color: #ffab00;">
                        <i class="bx bx-sitemap fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section Card -->
    <div class="card mm-card-main border-0 background-white mm-animate-fade">
        <!-- Section Header -->
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(15, 116, 76, 0.08); color: var(--mm-emerald);">
                    <i class="bx bx-layer fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Application Modules</h5>
                    <small class="text-muted">Manage all system feature modules and routes</small>
                </div>
            </div>

            <!-- Enhanced Search Box with Shortcut Indicator -->
            <div class="position-relative" style="min-width: 280px; max-width: 340px;">
                <div class="input-group input-group-merge">
                    <span class="input-group-text bg-light border-end-0 pe-2"><i class="bx bx-search text-muted fs-5"></i></span>
                    <input type="text" id="moduleSearchInput" class="form-control bg-light border-start-0 mm-search-input py-2" placeholder="Search module name or route...">
                </div>
                <div class="position-absolute end-0 top-50 translate-middle-y me-2 d-none d-sm-block pe-none">
                    <kbd class="bg-white border text-muted px-2 py-0.5 rounded font-monospace fs-tiny shadow-2xs">⌘ K</kbd>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-responsive mm-table-container">
            <table class="table mm-table align-middle mb-0" id="modulesTable">
                <thead>
                    <tr>
                        <th>MODULE DETAILS</th>
                        <th>ROUTE & PREFIX</th>
                        <th>PARENT MODULE</th>
                        <th class="text-center">SORT ORDER</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $module)
                        <tr class="module-row">
                            <!-- MODULE DETAILS -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px; min-width: 40px; background: #f8fafc; color: var(--mm-emerald);">
                                        <i class="{{ resolveModuleIcon($module->icon) }} fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                            <span>{{ $module->name }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <span class="badge bg-light text-secondary border font-monospace fs-tiny text-lowercase px-2 py-0.5">{{ $module->slug }}</span>
                                            @if($module->is_core)
                                                <span class="badge bg-label-info fs-tiny d-inline-flex align-items-center gap-1" title="System Core Module">
                                                    <i class="bx bx-shield-alt-2"></i> Core
                                                </span>
                                            @else
                                                <span class="badge bg-label-secondary fs-tiny">Custom</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- ROUTE & PREFIX -->
                            <td>
                                @if($module->route_name)
                                    <div class="d-inline-flex align-items-center gap-1 text-dark font-monospace small bg-light border px-2 py-1 rounded">
                                        <i class="bx bx-link text-primary fs-6"></i>
                                        <span>{{ $module->route_name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-minus-circle fs-6"></i> No Route
                                    </span>
                                @endif

                                @if($module->route_prefix)
                                    <div class="mt-1">
                                        <small class="text-muted font-monospace"><i class="bx bx-folder me-1 text-warning"></i>Prefix: <code>{{ $module->route_prefix }}</code></small>
                                    </div>
                                @endif
                            </td>

                            <!-- PARENT MODULE -->
                            <td>
                                @if($module->parent)
                                    <span class="badge bg-label-warning fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-git-repo-forked"></i> {{ $module->parent->name }}
                                    </span>
                                @else
                                    <span class="text-muted small d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-minus"></i> Main Module
                                    </span>
                                @endif
                            </td>

                            <!-- SORT ORDER -->
                            <td class="text-center">
                                <span class="badge bg-light text-dark border font-monospace fw-bold px-2.5 py-1">#{{ $module->sort_order }}</span>
                            </td>

                            <!-- STATUS & TOGGLE -->
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.modules.update', $module) }}" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $module->name }}">
                                    <input type="hidden" name="slug" value="{{ $module->slug }}">
                                    <input type="hidden" name="icon" value="{{ $module->icon }}">
                                    <input type="hidden" name="route_name" value="{{ $module->route_name }}">
                                    <input type="hidden" name="route_prefix" value="{{ $module->route_prefix }}">
                                    <input type="hidden" name="parent_id" value="{{ $module->parent_id }}">
                                    <input type="hidden" name="sort_order" value="{{ $module->sort_order }}">
                                    <input type="hidden" name="description" value="{{ $module->description }}">
                                    
                                    <div class="form-check form-switch mm-switch d-flex justify-content-center mb-1">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                            @checked($module->is_active) onchange="this.form.submit()" 
                                            title="Click to toggle {{ $module->name }} status">
                                    </div>
                                </form>
                                @if($module->is_active)
                                    <span class="badge bg-label-success fs-tiny">Active</span>
                                @else
                                    <span class="badge bg-label-secondary fs-tiny">Inactive</span>
                                @endif
                            </td>

                            <!-- ACTIONS -->
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-icon-action btn-label-primary" 
                                        data-bs-toggle="modal" data-bs-target="#editModuleModal{{ $module->id }}" 
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit module">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- Lock Action / Delete Button -->
                                    @if($module->is_core)
                                        <button type="button" class="btn btn-icon-action btn-label-secondary disabled" 
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Lock module (System Core)" disabled>
                                            <i class="bx bx-lock-alt"></i>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" class="d-inline" 
                                            onsubmit="return confirm('Are you sure you want to delete module &quot;{{ $module->name }}&quot;?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon-action btn-label-danger" 
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Delete module">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-grid-alt fs-1 mb-2"></i>
                                    <p class="mb-0 fs-6">No modules found matching search.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== CREATE MODULE MODAL ==================== -->
<div class="modal fade" id="createModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="background: var(--mm-emerald); width: 36px; height: 36px;">
                        <i class="bx bx-plus fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Add New Module</h5>
                        <small class="text-muted">Register a new feature module in the system</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('admin.modules.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Module Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Attendance Management" required>
                        </div>

                        <!-- Slug -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Module Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="auto-generated if empty (e.g. attendance)">
                        </div>

                        <!-- Icon -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Boxicons Class</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-cube"></i></span>
                                <input type="text" name="icon" class="form-control" placeholder="bx bx-calendar-check" value="bx bx-cube">
                            </div>
                            <small class="text-muted fs-tiny mt-1">Example: <code>bx bx-task</code>, <code>bx bx-user</code>, <code>bx bx-folder</code></small>
                        </div>

                        <!-- Parent Module -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Parent Module</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Main Top-Level Module)</option>
                                @foreach($parentModules as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Route Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Route Name</label>
                            <input type="text" name="route_name" class="form-control" placeholder="e.g. attendances.index">
                        </div>

                        <!-- Route Prefix -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Route Prefix</label>
                            <input type="text" name="route_prefix" class="form-control" placeholder="e.g. attendances">
                        </div>

                        <!-- Sort Order -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        <!-- Active Toggle -->
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mm-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="createActiveSwitch">
                                <label class="form-check-label fw-semibold text-dark" for="createActiveSwitch">Enable Module Immediately</label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary mb-1">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of feature capabilities..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-mm-primary px-4">
                        <i class="bx bx-check me-1"></i> Create Module
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== EDIT MODULE MODALS ==================== -->
@foreach($modules as $module)
<div class="modal fade" id="editModuleModal{{ $module->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center text-white" style="background: var(--mm-emerald); width: 38px; height: 38px;">
                        <i class="{{ resolveModuleIcon($module->icon) }} fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Edit Module: {{ $module->name }}</h5>
                        <small class="text-muted">Update configuration for <code>{{ $module->slug }}</code></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('admin.modules.update', $module) }}">
                @csrf
                @method('PUT')
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Module Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $module->name }}" required>
                        </div>

                        <!-- Slug -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Module Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ $module->slug }}" @if($module->is_core) readonly @endif>
                            @if($module->is_core)
                                <small class="text-muted fs-tiny mt-1 d-block"><i class="bx bx-lock-alt me-1 text-info"></i>Core module slug is locked</small>
                            @endif
                        </div>

                        <!-- Icon -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Boxicons Class</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="{{ resolveModuleIcon($module->icon) }}"></i></span>
                                <input type="text" name="icon" class="form-control" value="{{ $module->icon }}">
                            </div>
                        </div>

                        <!-- Parent Module -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Parent Module</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Main Top-Level Module)</option>
                                @foreach($parentModules->where('id', '!=', $module->id) as $parent)
                                    <option value="{{ $parent->id }}" @selected($module->parent_id === $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Route Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Route Name</label>
                            <input type="text" name="route_name" class="form-control" value="{{ $module->route_name }}">
                        </div>

                        <!-- Route Prefix -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Route Prefix</label>
                            <input type="text" name="route_prefix" class="form-control" value="{{ $module->route_prefix }}">
                        </div>

                        <!-- Sort Order -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ $module->sort_order }}">
                        </div>

                        <!-- Active Toggle -->
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mm-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($module->is_active) id="editActiveSwitch{{ $module->id }}">
                                <label class="form-check-label fw-semibold text-dark" for="editActiveSwitch{{ $module->id }}">Module Active Status</label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary mb-1">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $module->description }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-mm-primary px-4"><i class="bx bx-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Client Side Table Search & Hotkey JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('moduleSearchInput');
    const table = document.getElementById('modulesTable');

    if (searchInput && table) {
        // Filter functionality
        searchInput.addEventListener('keyup', function () {
            const query = searchInput.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr.module-row');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });

        // Keyboard Shortcut: Cmd/Ctrl + K to focus search
        document.addEventListener('keydown', function (e) {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
    }

    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endsection
