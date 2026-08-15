@extends('admin.layout.app')

@section('title', 'Business Addresses & Locations')

@push('styles')
<style>
    .branches-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .branches-shell {
        position: relative;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Ambient Orbs */
    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(52, 211, 153, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    @keyframes orbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -30px) scale(1.05); }
        66% { transform: translate(-30px, 40px) scale(0.95); }
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
    }

    .breadcrumb-custom a {
        color: #059669;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-custom a:hover {
        color: #047857;
    }

    /* ===== HEADER CARD ===== */
    .branches-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-left-box {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .header-icon-badge {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.35);
        flex-shrink: 0;
    }

    .header-title h1 {
        font-size: 1.95rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0a2e1f, #059669, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin: 0 0 0.2rem 0;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
    }

    .btn-add-address {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white !important;
        padding: 0.75rem 1.6rem;
        border-radius: 40px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.35);
    }

    .btn-add-address:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(5, 150, 105, 0.45);
        color: white !important;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card,
    .branches-page .stat-card,
    .branches-page .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem;
        border: 1px solid rgba(16, 185, 129, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #0a2e1f !important;
    }

    .branches-page .stat-card:first-of-type *,
    .branches-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .branches-page .stat-card h3,
    .branches-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .branches-page .stat-card h6,
    .branches-page .stat-card span,
    .branches-page .stat-card:first-of-type span,
    .branches-page .stat-card:first-of-type h6 {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #34d399, #059669);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.total,
    .branches-page .stat-card:first-of-type .stat-icon.total {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.default,
    .branches-page .stat-card .stat-icon.default {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.countries,
    .branches-page .stat-card .stat-icon.countries {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.tax,
    .branches-page .stat-card .stat-icon.tax {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .stat-info h6 {
        font-size: 0.72rem;
        color: #64748b;
        margin: 0 0 0.2rem 0;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .stat-info h3 {
        font-size: 2rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1;
    }

    /* ===== MAIN CARD & BRANCH ITEMS ===== */
    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 1.35rem 2rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .branch-item-card {
        border: 1px solid rgba(16, 185, 129, 0.15);
        border-radius: 20px;
        padding: 1.5rem;
        background: #ffffff;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .branch-item-card:hover {
        border-color: rgba(16, 185, 129, 0.3);
        transform: translateY(-4px);
        box-shadow: 0 14px 35px -10px rgba(16, 185, 129, 0.12);
    }

    .branch-avatar-box {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .branch-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state-container {
        text-align: center;
        padding: 4.5rem 2rem;
    }

    .empty-state-icon-box {
        width: 84px;
        height: 84px;
        border-radius: 26px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        border: 1px solid rgba(16, 185, 129, 0.2);
        animation: floatOrb 4s ease-in-out infinite;
        box-shadow: 0 10px 25px -8px rgba(16, 185, 129, 0.25);
    }

    @keyframes floatOrb {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .branches-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="branches-page">
    <div class="branches-shell">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>

        <div class="content-wrapper">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-custom">
                <i class="fas fa-building"></i>
                <a href="{{ route('admin.settings.index') }}">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.settings.index') }}">Settings</a>
                <span>/</span>
                <span>Branches & Locations</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="header-title">
                        <h1>Business Addresses</h1>
                        <p>Manage your organization's official branch locations, contact details, and office addresses.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.business-address.create') }}" class="btn-add-address">
                    <i class="fas fa-plus-circle"></i> Add New Address
                </a>
            </div>

            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(220, 252, 231, 0.95); color: #065f46; border-left: 5px solid #10b981 !important;" role="alert">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(254, 226, 226, 0.95); color: #991b1b; border-left: 5px solid #ef4444 !important;" role="alert">
                    <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Stats Grid -->
            @php
                $totalBranches = $addresses ? $addresses->count() : 0;
                $defaultBranchCount = $addresses ? $addresses->where('is_default', true)->count() : 0;
                $countriesCount = $addresses ? $addresses->pluck('country')->filter()->unique()->count() : 0;
                $taxConfiguredCount = $addresses ? $addresses->whereNotNull('tax_name')->where('tax_name', '!=', '')->count() : 0;
            @endphp

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Total Branches</h6>
                        <h3>{{ $totalBranches }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon default">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Primary Default</h6>
                        <h3>{{ $defaultBranchCount }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon countries">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Active Countries</h6>
                        <h3>{{ $countriesCount }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon tax">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Tax Configured</h6>
                        <h3>{{ $taxConfiguredCount }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Address List Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-sm rounded-3 p-1 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: linear-gradient(145deg, #d1fae5, #a7f3d0); color: #059669;">
                            <i class="fas fa-map-pin fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Address List</h5>
                            <small class="text-muted">Manage your business locations and primary office addresses</small>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.settings.business-address.create') }}" class="btn btn-sm px-3 rounded-pill fw-bold" style="background: #e6f3ec; color: #0f744c; border: 1px solid rgba(16, 185, 129, 0.25);">
                        <i class="fas fa-plus me-1"></i> Add Address
                    </a>
                </div>

                <div class="p-4 p-md-5">
                    @if($addresses && $addresses->count() > 0)
                        <div class="row g-4">
                            @foreach($addresses as $index => $address)
                                @php
                                    $hasLogo = !empty($address->logo) && file_exists(public_path($address->logo));
                                @endphp
                                <div class="col-lg-6">
                                    <div class="branch-item-card {{ $address->is_default ? 'border-emerald' : '' }}">
                                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="branch-avatar-box shadow-sm">
                                                    @if($hasLogo)
                                                        <img src="{{ asset($address->logo) }}" alt="{{ $address->display_name }}" class="branch-avatar-img">
                                                    @else
                                                        <i class="fas fa-city"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <h5 class="fw-bold mb-0" style="color: #0a2e1f;">{{ $address->display_name }}</h5>
                                                        @if($address->is_default)
                                                            <span class="badge rounded-pill px-2.5 py-1 small" style="background: linear-gradient(145deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid rgba(5, 150, 105, 0.25); font-weight: 750;">
                                                                <i class="fas fa-star me-1" style="color: #059669;"></i> Default Primary
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted small mt-1 d-flex align-items-center gap-2 flex-wrap">
                                                        <span><i class="fas fa-map-marker-alt text-success me-1"></i> {{ $address->location }}</span>
                                                        <span>•</span>
                                                        <span><i class="fas fa-globe text-success me-1"></i> {{ $address->country }}</span>
                                                        @if($address->tax_name)
                                                            <span>•</span> Tax: <span class="fw-semibold text-secondary">{{ $address->tax_name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($address->email || $address->phone)
                                            <div class="d-flex align-items-center gap-3 text-muted small mb-2 flex-wrap">
                                                @if($address->email)
                                                    <span><i class="fas fa-envelope text-success me-1"></i> {{ $address->email }}</span>
                                                @endif
                                                @if($address->phone)
                                                    <span><i class="fas fa-phone-alt text-success me-1"></i> {{ $address->phone }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="p-3 rounded-3 mb-3 small" style="background: #f8fafc; color: #4b5563; border: 1px solid #e2e8f0;">
                                            <i class="fas fa-map text-muted me-1"></i> {{ $address->address }}
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
                                            <div>
                                                @if(!$address->is_default)
                                                    <form action="{{ route('admin.settings.business-address.make-default', $address->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-sm rounded-pill px-3 fw-bold" style="background: #e6f3ec; color: #0f744c; border: 1px solid rgba(16, 185, 129, 0.25);">
                                                            <i class="fas fa-check-circle me-1"></i> Set as Default
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('admin.settings.business-address.edit', $address) }}" class="btn btn-sm rounded-pill px-3 fw-bold" style="background: #fef3c7; color: #92400e; border: 1px solid rgba(217, 119, 6, 0.25);">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>

                                                @if($addresses->count() > 1)
                                                    <form action="{{ route('admin.settings.business-address.destroy', $address) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this business address?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm rounded-pill px-3 fw-bold" style="background: #fef2f2; color: #b91c1c; border: 1px solid rgba(220, 38, 38, 0.25);">
                                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Premium Empty State -->
                        <div class="empty-state-container">
                            <div class="empty-state-icon-box">
                                <i class="fas fa-city"></i>
                            </div>
                            <h4 class="fw-bold mb-2" style="color: #0a2e1f;">No Business Addresses Found</h4>
                            <p class="text-muted mb-4 max-w-md mx-auto small">Add your first business address to configure official branch locations, contact info, logo, and tax details.</p>
                            <a href="{{ route('admin.settings.business-address.create') }}" class="btn-add-address">
                                <i class="fas fa-plus-circle"></i> Add First Address
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
