@extends('admin.layout.app')

@section('title', 'Role & Permission')

@push('styles')
<style>
    .role-permission-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .role-permission-shell {
        position: relative;
        max-width: 1400px;
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

    .btn-back-settings {
        background-color: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #0f744c !important;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 40px;
        padding: 0.65rem 1.4rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-back-settings:hover {
        background-color: #e6f3ec;
        color: #059669 !important;
        border-color: rgba(16, 185, 129, 0.4);
        transform: translateY(-2px);
    }

    .btn-back-settings:hover .back-arrow-icon {
        transform: translateX(-4px);
    }

    .back-arrow-icon {
        transition: transform 0.25s ease;
        display: inline-block;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card,
    .role-permission-page .stat-card,
    .role-permission-page .stat-card:first-of-type {
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

    .role-permission-page .stat-card:first-of-type *,
    .role-permission-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .role-permission-page .stat-card h3,
    .role-permission-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .role-permission-page .stat-card h6,
    .role-permission-page .stat-card span,
    .role-permission-page .stat-card:first-of-type span,
    .role-permission-page .stat-card:first-of-type h6 {
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

    .stat-icon.roleactive,
    .role-permission-page .stat-card:first-of-type .stat-icon.roleactive {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.totroles,
    .role-permission-page .stat-card .stat-icon.totroles {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.totmodules,
    .role-permission-page .stat-card .stat-icon.totmodules {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.actions,
    .role-permission-page .stat-card .stat-icon.actions {
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
        font-size: 1.25rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== ROLE SELECT CARD ===== */
    .role-select-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
    }

    .input-group-custom {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background-color: #fafefb;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .input-group-custom:focus-within {
        border-color: #34d399;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
        transform: translateY(-1px);
    }

    .input-group-custom .input-group-text {
        background-color: transparent;
        border: none;
        color: #059669;
        padding-left: 18px;
        padding-right: 12px;
        font-size: 1.1rem;
    }

    .input-group-custom .form-select {
        border: none;
        background-color: transparent;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0a2e1f;
        padding-right: 18px;
        height: 50px;
    }

    .input-group-custom .form-select:focus {
        box-shadow: none;
        background-color: transparent;
    }

    /* ===== MATRIX TABLE CARD ===== */
    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 1.5rem 2.25rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: transparent;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .card-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .card-header-avatar {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .matrix-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .matrix-table th {
        background: linear-gradient(90deg, #ecfdf5, #f0fdf4);
        color: #065f46;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.2);
    }

    .matrix-table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        transition: background-color 0.2s ease;
    }

    .matrix-table tr:hover td {
        background-color: rgba(240, 253, 244, 0.6);
    }

    .module-title-box {
        display: flex;
        flex-direction: column;
    }

    .module-title-box strong {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0a2e1f;
    }

    .module-title-box small {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Custom Checkbox */
    .perm-checkbox {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 2px solid rgba(16, 185, 129, 0.35);
        accent-color: #059669;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .perm-checkbox:hover {
        transform: scale(1.15);
        border-color: #34d399;
    }

    .btn-save-address {
        height: 50px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0 32px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }

    .btn-save-address:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(5, 150, 105, 0.45);
        color: white !important;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .role-permission-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="role-permission-page">
    <div class="role-permission-shell">
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
                <span>Role & Permission Matrix</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="header-title">
                        <h1>Role & Permission Management</h1>
                        <p>Configure access control levels, granular action permissions, and module capabilities for system roles.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="btn-back-settings">
                    <i class="fas fa-arrow-left me-1 back-arrow-icon"></i> Back to Settings
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

            <!-- Executive Summary Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon roleactive">
                        <i class="fas fa-user-gear"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Active Role</h6>
                        <h3>{{ ucfirst($role) }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon totroles">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <div class="stat-info">
                        <h6>System Roles</h6>
                        <h3>{{ count($roles) }} Configured</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon totmodules">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Total Modules</h6>
                        <h3>{{ count($modules) }} Modules</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon actions">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Action Types</h6>
                        <h3>{{ count($permissions) }} Permissions</h3>
                    </div>
                </div>
            </div>

            <!-- Select Role Card -->
            <div class="role-select-card">
                <form method="GET" action="{{ route('admin.role-permissions.index') }}">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5 col-lg-4">
                            <label class="form-label fw-bold text-dark mb-1.5" style="font-size: 0.9rem;"><i class="fas fa-sliders me-1.5" style="color: #059669;"></i>Select Role to Modify Permissions</label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select name="role" class="form-select" onchange="this.form.submit()">
                                    @foreach($roles as $option)
                                        <option value="{{ $option }}" @selected($role === $option)>{{ ucfirst($option) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7 col-lg-8">
                            <small class="text-muted d-block mt-md-4"><i class="fas fa-info-circle me-1" style="color: #059669;"></i>Changing the selected role will dynamically reload module access control capabilities below.</small>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Permissions Matrix Table Card -->
            <form method="POST" action="{{ route('admin.role-permissions.update') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">

                <div class="address-card-elevated">
                    <div class="card-header-custom">
                        <div class="card-header-left">
                            <div class="card-header-avatar shadow-sm">
                                <i class="fas fa-table-cells"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Module Access Control Matrix</h5>
                                <small class="text-muted">Grant or revoke granular action privileges for <strong style="color: #059669;">{{ ucfirst($role) }}</strong></small>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 240px;">Module Name</th>
                                    @foreach($permissions as $permission)
                                        <th class="text-center">{{ ucfirst($permission) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $module)
                                    @php $saved = $savedPermissions->get($module->id); @endphp
                                    <tr>
                                        <td>
                                            <div class="module-title-box">
                                                <strong>{{ $module->name }}</strong>
                                                @if($module->parent)<small><i class="fas fa-angle-right me-1" style="color: #059669;"></i>{{ $module->parent->name }}</small>@endif
                                            </div>
                                        </td>
                                        @foreach($permissions as $permission)
                                            <td class="text-center">
                                                <input type="checkbox" class="perm-checkbox" name="permissions[{{ $module->id }}][]" value="{{ $permission }}" @checked($role === 'admin' || (bool) optional($saved)->{'can_' . $permission})>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: rgba(248, 250, 252, 0.6);">
                        <small class="text-muted"><i class="fas fa-shield-halved me-1" style="color: #059669;"></i>Changes will immediately affect users assigned to the <strong>{{ ucfirst($role) }}</strong> role.</small>
                        <button type="submit" class="btn-save-address">
                            <i class="fas fa-save me-1.5"></i> Save Permissions
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
