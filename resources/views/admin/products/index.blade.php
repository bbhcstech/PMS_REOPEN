@extends('admin.layout.app')

@section('title', 'Products & Projects Directory')

@section('content')
<style>
    .project-card-header {
        border-bottom: 1px solid #f1f5f9;
    }
    .avatar-group .avatar {
        transition: transform 0.2s ease;
        margin-left: -8px;
    }
    .avatar-group .avatar:first-child {
        margin-left: 0;
    }
    .avatar-group .avatar:hover {
        transform: translateY(-2px);
        z-index: 5;
    }
    .cost-badge {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        letter-spacing: -0.01em;
    }
    .btn-primary,
    .btn-primary *,
    .btn-primary i,
    .btn-primary i[class^="bx"],
    .btn-primary i[class*=" bx"] {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 1 !important;
    }

    /* Actions Column Dropdown Button & Three-Dot Visibility Fix */
    .table td .btn-icon,
    .table td .btn-icon.btn-sm,
    .table td button.btn-icon {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        min-height: 34px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        flex-shrink: 0 !important;
        transition: all 0.2s ease !important;
    }

    .table td .btn-icon i,
    .table td .btn-icon i.bx,
    .table td .btn-icon i.bx-dots-vertical-rounded {
        font-size: 1.25rem !important;
        width: 1.25rem !important;
        height: 1.25rem !important;
        min-width: 1.25rem !important;
        min-height: 1.25rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        background-color: transparent !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Light Mode Actions Button */
    .table td .btn-icon.btn-light {
        background-color: #f1f5f9 !important;
        border: 1px solid rgba(148, 163, 184, 0.3) !important;
        color: #334155 !important;
    }
    .table td .btn-icon.btn-light:hover {
        background-color: #e2e8f0 !important;
        border-color: #2F6BFF !important;
        color: #2F6BFF !important;
    }

    /* Dark Mode Actions Button & Three-Dot Prominence */
    html[data-pms-theme="dark"] .table td .btn-icon,
    html[data-bs-theme="dark"] .table td .btn-icon,
    html[data-theme="dark"] .table td .btn-icon,
    html.dark .table td .btn-icon,
    body[data-pms-theme="dark"] .table td .btn-icon,
    [data-pms-theme="dark"] .table td .btn-icon,
    .dark .table td .btn-icon {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #EEF1FB !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] .table td .btn-icon:hover,
    html[data-bs-theme="dark"] .table td .btn-icon:hover,
    html[data-theme="dark"] .table td .btn-icon:hover,
    html.dark .table td .btn-icon:hover,
    body[data-pms-theme="dark"] .table td .btn-icon:hover,
    [data-pms-theme="dark"] .table td .btn-icon:hover,
    .dark .table td .btn-icon:hover {
        background: #1A2247 !important;
        border-color: #2F6BFF !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.35) !important;
        transform: translateY(-1px) !important;
    }

    html[data-pms-theme="dark"] .table td .btn-icon i,
    html[data-pms-theme="dark"] .table td .btn-icon i.bx,
    html[data-pms-theme="dark"] .table td .btn-icon i.bx-dots-vertical-rounded,
    html[data-bs-theme="dark"] .table td .btn-icon i,
    html[data-theme="dark"] .table td .btn-icon i,
    html.dark .table td .btn-icon i,
    body[data-pms-theme="dark"] .table td .btn-icon i,
    [data-pms-theme="dark"] .table td .btn-icon i,
    .dark .table td .btn-icon i {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
        background-color: transparent !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .table td .btn-icon:hover i,
    html[data-bs-theme="dark"] .table td .btn-icon:hover i,
    html[data-theme="dark"] .table td .btn-icon:hover i,
    html.dark .table td .btn-icon:hover i,
    body[data-pms-theme="dark"] .table td .btn-icon:hover i,
    [data-pms-theme="dark"] .table td .btn-icon:hover i,
    .dark .table td .btn-icon:hover i {
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        background-color: transparent !important;
    }
    .project-selection-tools .pms-table-tools {
        margin: 0;
        border-radius: 0;
        border: 0;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    }
    #homeProjectTools .pms-table-tools__status::before { content: 'Home projects · '; }
    #clientProjectTools .pms-table-tools__status::before { content: 'Client projects · '; }
    .table .project-actions-icon {
        width: 20px;
        height: 20px;
        fill: currentColor;
        background: transparent !important;
        pointer-events: none;
    }

    /* Clean SaaS Workspace Redesign (Scoped to #pms-products-page) */
    #pms-products-page.pms-products-page {
        padding: 0 0 2rem !important;
        margin: 0 !important;
        min-height: 0 !important;
        background: transparent !important;
        font-family: 'Inter', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }

    #pms-products-page .pms-prod-topbar {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        margin-bottom: 18px !important;
    }

    #pms-products-page .pms-prod-title-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    #pms-products-page .pms-prod-title-icon {
        width: 42px !important;
        height: 42px !important;
        border-radius: 12px !important;
        background: #eff6ff !important;
        border: 1px solid #dbeafe !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.25rem !important;
        flex-shrink: 0 !important;
    }

    #pms-products-page .pms-prod-title {
        margin: 0 !important;
        font-size: 1.35rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        letter-spacing: -0.02em !important;
        line-height: 1.25 !important;
    }

    #pms-products-page .breadcrumb {
        margin: 2px 0 0 !important;
        padding: 0 !important;
        background: transparent !important;
        font-size: 0.78rem !important;
    }

    #pms-products-page .breadcrumb-item a,
    #pms-products-page .breadcrumb-item + .breadcrumb-item::before {
        color: #64748b !important;
    }

    #pms-products-page .breadcrumb-item.active {
        color: #0f172a !important;
        font-weight: 600 !important;
    }

    #pms-products-page .pms-prod-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    #pms-products-page .pms-btn-deals {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #0f172a !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        height: 38px !important;
        padding: 0 16px !important;
        border-radius: 999px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    #pms-products-page .pms-btn-deals:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    #pms-products-page .pms-btn-add {
        background: #2563eb !important;
        border: 1px solid #2563eb !important;
        color: #ffffff !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        height: 38px !important;
        padding: 0 18px !important;
        border-radius: 999px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.24) !important;
    }

    #pms-products-page .pms-btn-add:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }

    /* Quick Stats Cards */
    #pms-products-page .pms-stat-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        transition: border-color 0.16s ease, box-shadow 0.16s ease !important;
    }

    #pms-products-page .pms-stat-card:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06) !important;
    }

    #pms-products-page .pms-stat-label {
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #64748b !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    #pms-products-page .pms-stat-value {
        font-size: 1.55rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.02em !important;
        line-height: 1.15 !important;
        margin-bottom: 4px !important;
    }

    #pms-products-page .pms-stat-sub {
        font-size: 0.76rem !important;
        color: #64748b !important;
    }

    #pms-products-page .pms-stat-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        font-size: 1.25rem !important;
    }

    #pms-products-page .pms-stat-icon--blue {
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    #pms-products-page .pms-stat-icon--amber {
        background: #fef9c3 !important;
        color: #ca8a04 !important;
    }

    #pms-products-page .pms-stat-icon i,
    #pms-products-page .pms-prod-title-icon i,
    #pms-products-page .pms-section-icon i,
    #pms-products-page .pms-table-empty-icon i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    /* Filter Bar Card */
    #pms-products-page .pms-filter-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    #pms-products-page .pms-filter-card .input-group-text {
        background: #ffffff !important;
        border-color: #e2e8f0 !important;
        color: #94a3b8 !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
    }

    #pms-products-page .pms-filter-card .form-control,
    #pms-products-page .pms-filter-card .form-select {
        height: 40px !important;
        border-color: #e2e8f0 !important;
        font-size: 0.85rem !important;
        color: #0f172a !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
    }

    #pms-products-page .pms-filter-card .form-control {
        border-top-right-radius: 10px !important;
        border-bottom-right-radius: 10px !important;
    }

    #pms-products-page .pms-filter-card .form-select {
        border-radius: 10px !important;
    }

    #pms-products-page .pms-filter-card .form-control:focus,
    #pms-products-page .pms-filter-card .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    #pms-products-page .pms-btn-filter {
        height: 40px !important;
        border-radius: 10px !important;
        background: #2563eb !important;
        border: 1px solid #2563eb !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2) !important;
    }

    #pms-products-page .pms-btn-filter:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }

    #pms-products-page .pms-btn-reset {
        height: 40px !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Directory Table Cards */
    #pms-products-page .pms-directory-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
        overflow: hidden !important;
    }

    #pms-products-page .project-card-header {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 16px 22px !important;
    }

    #pms-products-page .pms-section-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: #eff6ff !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.15rem !important;
    }

    #pms-products-page .pms-count-pill {
        background: #eff6ff !important;
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
        border: 1px solid #dbeafe !important;
        border-radius: 999px !important;
        padding: 6px 14px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
    }

    #pms-products-page .pms-count-pill--green {
        background: #ecfdf5 !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
    }

    #pms-products-page .project-selection-tools .pms-table-tools {
        background: #f8fafc !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 10px 22px !important;
    }

    #pms-products-page .table thead th {
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 12px 14px !important;
    }

    #pms-products-page .table tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 14px !important;
    }

    /* Empty State inside Table */
    #pms-products-page .pms-table-empty {
        padding: 52px 24px !important;
        text-align: center !important;
    }

    #pms-products-page .pms-table-empty-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        color: #94a3b8 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.35rem !important;
        margin: 0 auto 12px !important;
    }

    #pms-products-page .pms-table-empty h6 {
        margin: 0 0 6px !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    #pms-products-page .pms-table-empty p {
        margin: 0 auto 16px !important;
        max-width: 340px !important;
        font-size: 0.8rem !important;
        color: #64748b !important;
        line-height: 1.45 !important;
    }

    #pms-products-page .pms-empty-cta {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        background: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #dbeafe !important;
        border-radius: 10px !important;
        padding: 8px 16px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all 0.16s ease !important;
    }

    #pms-products-page .pms-empty-cta:hover {
        background: #dbeafe !important;
        color: #1d4ed8 !important;
    }

    /* Dark Mode Overrides */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-filter-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-directory-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .project-card-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .table,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .table tbody tr,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .table tbody td {
        background: #0f172a !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .table thead th {
        background: #1e293b !important;
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .table-hover > tbody > tr:hover > * {
        background-color: rgba(37, 99, 235, 0.08) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-prod-title,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .breadcrumb-item.active,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-value,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-sub strong,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .project-card-header h5,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-empty h6 {
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-label,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-sub,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-empty p,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .text-muted {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-btn-deals,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-btn-reset,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-filter-card .input-group-text,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-filter-card .form-control,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-filter-card .form-select,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .project-selection-tools .pms-table-tools,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-empty-icon {
        background: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        color: #e2e8f0 !important;
        -webkit-text-fill-color: #e2e8f0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-tools .btn,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-tools button,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-tools .dropdown-toggle {
        background: #0f172a !important;
        border-color: rgba(148, 163, 184, 0.28) !important;
        color: #e2e8f0 !important;
        -webkit-text-fill-color: #e2e8f0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-table-tools .dropdown-toggle span {
        background: transparent !important;
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-prod-title-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-icon--blue,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-section-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-empty-cta,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-count-pill:not(.pms-count-pill--green) {
        background: rgba(37, 99, 235, 0.18) !important;
        border-color: rgba(96, 165, 250, 0.34) !important;
        color: #93c5fd !important;
        -webkit-text-fill-color: #93c5fd !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-stat-icon--amber {
        background: rgba(245, 158, 11, 0.16) !important;
        border: 1px solid rgba(251, 191, 36, 0.3) !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .pms-count-pill--green {
        background: rgba(16, 185, 129, 0.16) !important;
        border-color: rgba(52, 211, 153, 0.32) !important;
        color: #34d399 !important;
        -webkit-text-fill-color: #34d399 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .dropdown-menu {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .dropdown-item {
        color: #e2e8f0 !important;
        -webkit-text-fill-color: #e2e8f0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.2) !important;
        color: #93c5fd !important;
        -webkit-text-fill-color: #93c5fd !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .text-dark {
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode, [data-bs-theme="dark"]) #pms-products-page .bg-light {
        background-color: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        color: #cbd5e1 !important;
        -webkit-text-fill-color: #cbd5e1 !important;
    }
</style>

<div id="pms-products-page" class="pms-products-page">
    <!-- Top Header & Breadcrumbs -->
    <div class="pms-prod-topbar">
        <div class="pms-prod-title-wrap">
            <div class="pms-prod-title-icon" aria-hidden="true">
                <i class="bx bx-cube"></i>
            </div>
            <div>
                <h3 class="pms-prod-title">Products &amp; Projects</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Projects</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="pms-prod-actions">
            @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
                <a href="{{ route('admin.deals.index') }}" class="btn pms-btn-deals">
                    <i class="bx bx-target-lock"></i> View Deals
                </a>
                <a href="{{ route('projects.create') }}" class="btn btn-primary pms-btn-add">
                    <i class="bx bx-plus-circle"></i> Add Project
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Projects -->
        <div class="col-xl-3 col-sm-6">
            <div class="card pms-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="pms-stat-label">Total Products / Projects</span>
                        <h4 class="pms-stat-value">{{ $stats['total_projects'] }}</h4>
                        <small class="pms-stat-sub">{{ $stats['in_progress_count'] }} in progress • {{ $stats['completed_count'] }} completed</small>
                    </div>
                    <div class="pms-stat-icon pms-stat-icon--blue">
                        <i class="bx bx-cube"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Home Projects Count -->
        <div class="col-xl-3 col-sm-6">
            <div class="card pms-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="pms-stat-label">🏠 Home Projects (In-House)</span>
                        <h4 class="pms-stat-value">{{ $stats['home_projects_count'] }}</h4>
                        <small class="pms-stat-sub">Total Cost: <strong class="text-dark">${{ number_format($stats['home_cost'], 2) }}</strong></small>
                    </div>
                    <div class="pms-stat-icon pms-stat-icon--blue">
                        <i class="bx bx-home-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Projects Count -->
        <div class="col-xl-3 col-sm-6">
            <div class="card pms-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="pms-stat-label">💼 Client Projects (Client)</span>
                        <h4 class="pms-stat-value">{{ $stats['client_projects_count'] }}</h4>
                        <small class="pms-stat-sub">Total Cost: <strong class="text-dark">${{ number_format($stats['client_cost'], 2) }}</strong></small>
                    </div>
                    <div class="pms-stat-icon pms-stat-icon--blue">
                        <i class="bx bx-buildings"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Deal Value / Budget -->
        <div class="col-xl-3 col-sm-6">
            <div class="card pms-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="pms-stat-label">💰 Total Pipeline &amp; Deal Value</span>
                        @php
                            // 10 lakh (1,000,000) and above reads as M / B / T; the exact amount stays in the tooltip.
                            $pipelineValue = (float) ($stats['total_deal_cost'] ?? 0);
                            $pipelineAbs = abs($pipelineValue);
                            $pipelineLabel = number_format($pipelineValue, 2);
                            foreach ([1e12 => 'T', 1e9 => 'B', 1e6 => 'M'] as $pipelineUnit => $pipelineSuffix) {
                                if ($pipelineAbs >= $pipelineUnit) {
                                    $pipelineLabel = rtrim(rtrim(number_format($pipelineValue / $pipelineUnit, 2), '0'), '.') . $pipelineSuffix;
                                    break;
                                }
                            }
                        @endphp
                        <h4 class="pms-stat-value" title="${{ number_format($pipelineValue, 2) }}">${{ $pipelineLabel }}</h4>
                        <small class="text-success fw-medium"><i class="bx bx-trending-up me-1"></i>Combined Project Valuation</small>
                    </div>
                    <div class="pms-stat-icon pms-stat-icon--amber">
                        <i class="bx bx-dollar-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section & Navigation Tabs -->
    <div class="card pms-filter-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3 align-items-center">
                <div class="col-lg-5 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by project name, code, client or description..." value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="in progress" {{ ($status ?? '') === 'in progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="not started" {{ ($status ?? '') === 'not started' ? 'selected' : '' }}>Not Started</option>
                        <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="on hold" {{ ($status ?? '') === 'on hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="canceled" {{ ($status ?? '') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($priority ?? '') === 'all' ? 'selected' : '' }}>All Priorities</option>
                        <option value="high" {{ ($priority ?? '') === 'high' ? 'selected' : '' }}>High Priority</option>
                        <option value="medium" {{ ($priority ?? '') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                        <option value="low" {{ ($priority ?? '') === 'low' ? 'selected' : '' }}>Low Priority</option>
                        <option value="urgent" {{ ($priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary pms-btn-filter px-3 flex-grow-1">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                    @if(!empty($search) || ($status ?? 'all') !== 'all' || ($priority ?? 'all') !== 'all')
                        <a href="{{ route('products.index') }}" class="btn pms-btn-reset px-3" title="Reset Filters">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TABLE 1: HOME PROJECTS (IN-HOUSE / INTERNAL PRODUCTS)                 -->
    <!-- ===================================================================== -->
    <div class="card pms-directory-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between project-card-header">
            <div class="d-flex align-items-center gap-3">
                <span class="pms-section-icon">
                    <i class="bx bx-home-alt"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Home Projects</h5>
                    <small class="text-muted">In-house products and internal company projects</small>
                </div>
            </div>
            <span class="badge pms-count-pill">
                {{ count($homeProjects) }} Home Project(s)
            </span>
        </div>
        <div id="homeProjectTools" class="project-selection-tools" aria-label="Home project selection and export"></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-pms-toolbar-host="homeProjectTools">
                <thead class="bg-light">
                    <tr class="text-secondary text-uppercase fw-semibold" style="font-size: 0.76rem;">
                        <th data-pms-selection-column class="pms-table-selection-cell" scope="col"><label class="pms-table-select"><input type="checkbox" class="pms-table-select__input" aria-label="Select all home projects"></label></th>
                        <th class="ps-4">Project / Product</th>
                        <th>Assigned Team</th>
                        <th>Progress</th>
                        <th>Timeline</th>
                        <th>Cost / Value</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeProjects as $project)
                        @php
                            $progPercent = max(0, min(100, (int) ($project->completion_percent ?? 0)));
                            $statusClass = match(strtolower(trim((string) $project->status))) {
                                'completed' => 'bg-success-subtle text-success border-success-subtle',
                                'in progress', 'in_progress' => 'bg-primary-subtle text-primary border-primary-subtle',
                                'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
                                'on hold', 'on_hold' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                'canceled', 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                default => 'bg-light text-dark border-secondary-subtle'
                            };
                            $priorityClass = match(strtolower(trim((string) $project->priority))) {
                                'high', 'urgent' => 'text-danger bg-danger-subtle',
                                'medium' => 'text-warning bg-warning-subtle',
                                default => 'text-secondary bg-light'
                            };
                        @endphp
                        <tr>
                            <!-- Project / Product Name -->
                            <td data-pms-selection-column class="pms-table-selection-cell"><label class="pms-table-select"><input type="checkbox" class="pms-table-select__input" aria-label="Select {{ $project->name }} for export"></label></td>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bx bx-cube-alt fs-3"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.show', $project->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $project->name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.72rem;">{{ $project->project_code ?: ('PRJ-' . $project->id) }}</span>
                                            @if($project->priority)
                                                <span class="badge rounded-pill px-2 {{ $priorityClass }}" style="font-size: 0.68rem;">{{ ucfirst($project->priority) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Assigned Team -->
                            <td>
                                @if($project->users && $project->users->count() > 0)
                                    <div class="avatar-group d-flex align-items-center">
                                        @foreach($project->users->take(4) as $member)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" 
                                                  title="{{ $member->name }} ({{ $member->employeeDetail?->designation?->name ?: 'Member' }})"
                                                  style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </span>
                                        @endforeach
                                        @if($project->users->count() > 4)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-light text-muted d-inline-flex align-items-center justify-content-center small fw-bold" style="width: 32px; height: 32px; font-size: 0.72rem;">
                                                +{{ $project->users->count() - 4 }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $project->users->count() }} member(s)</small>
                                @else
                                    <span class="text-muted small fst-italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Progress -->
                            <td style="min-width: 150px;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-dark">{{ $progPercent }}%</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $project->tasks_count ?? 0 }} task(s)</small>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-info rounded-pill" role="progressbar" style="width: {{ $progPercent }}%" aria-valuenow="{{ $progPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>

                            <!-- Timeline -->
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <i class="bx bx-calendar-event text-info me-1"></i>
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'Not set' }}
                                </div>
                                <div class="small text-muted">
                                    to {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : ($project->without_deadline ? 'No deadline' : 'Ongoing') }}
                                </div>
                            </td>

                            <!-- Cost / Value (Fetched from Deals / Budget) -->
                            <td>
                                <div class="fw-bold text-dark fs-6 cost-badge">
                                    {{ $project->currency_symbol ?? '$' }}{{ number_format($project->calculated_cost, 2) }}
                                </div>
                                @if($project->cost_source === 'deal')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.68rem;" title="Fetched automatically from Deals section">
                                        <i class="bx bx-check-circle me-1"></i>Deal: {{ Str::limit($project->deal_reference, 16) }}
                                    </span>
                                @elseif($project->cost_source === 'budget')
                                    <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 0.68rem;">
                                        Budget Allocated
                                    </span>
                                @elseif($project->cost_source === 'expenses')
                                    <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 0.68rem;">
                                        Expenses Sum
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-2" style="font-size: 0.68rem;">
                                        Unspecified
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge {{ $statusClass }} border rounded-pill px-3 py-1 fw-semibold text-capitalize">
                                    {{ str_replace('_', ' ', $project->status ?: 'In Progress') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for {{ $project->name }}">
                                        <svg class="project-actions-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.show', $project->id) }}">
                                                <i class="bx bx-show text-primary me-2"></i> View Project Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('tasks.index', ['project_id' => $project->id]) }}">
                                                <i class="bx bx-check-square text-info me-2"></i> Manage Tasks
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.edit', $project->id) }}">
                                                <i class="bx bx-edit text-warning me-2"></i> Edit Project
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="pms-table-empty">
                                <div class="pms-table-empty-icon" aria-hidden="true">
                                    <i class="bx bx-home-alt"></i>
                                </div>
                                <h6>No Home Projects found.</h6>
                                <p>Get started by creating your first in-house product or feature branch.</p>
                                @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
                                    <a href="{{ route('projects.create') }}" class="pms-empty-cta">
                                        <i class="bx bx-plus"></i> Create In-House Project
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TABLE 2: CLIENT PROJECTS (CLIENT COMMISSIONED PRODUCTS)               -->
    <!-- ===================================================================== -->
    <div class="card pms-directory-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between project-card-header">
            <div class="d-flex align-items-center gap-3">
                <span class="pms-section-icon">
                    <i class="bx bx-buildings"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Client Projects</h5>
                    <small class="text-muted">Client-commissioned products, commercial builds &amp; client contracts</small>
                </div>
            </div>
            <span class="badge pms-count-pill pms-count-pill--green">
                {{ count($clientProjects) }} Client Project(s)
            </span>
        </div>
        <div id="clientProjectTools" class="project-selection-tools" aria-label="Client project selection and export"></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" data-pms-toolbar-host="clientProjectTools">
                <thead class="bg-light">
                    <tr class="text-secondary text-uppercase fw-semibold" style="font-size: 0.76rem;">
                        <th data-pms-selection-column class="pms-table-selection-cell" scope="col"><label class="pms-table-select"><input type="checkbox" class="pms-table-select__input" aria-label="Select all client projects"></label></th>
                        <th class="ps-4">Project / Product</th>
                        <th>Client Details</th>
                        <th>Assigned Team</th>
                        <th>Progress</th>
                        <th>Timeline</th>
                        <th>Deal Cost / Value</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientProjects as $project)
                        @php
                            $progPercent = max(0, min(100, (int) ($project->completion_percent ?? 0)));
                            $statusClass = match(strtolower(trim((string) $project->status))) {
                                'completed' => 'bg-success-subtle text-success border-success-subtle',
                                'in progress', 'in_progress' => 'bg-primary-subtle text-primary border-primary-subtle',
                                'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
                                'on hold', 'on_hold' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                'canceled', 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                default => 'bg-light text-dark border-secondary-subtle'
                            };
                            $priorityClass = match(strtolower(trim((string) $project->priority))) {
                                'high', 'urgent' => 'text-danger bg-danger-subtle',
                                'medium' => 'text-warning bg-warning-subtle',
                                default => 'text-secondary bg-light'
                            };
                        @endphp
                        <tr>
                            <!-- Project / Product Name -->
                            <td data-pms-selection-column class="pms-table-selection-cell"><label class="pms-table-select"><input type="checkbox" class="pms-table-select__input" aria-label="Select {{ $project->name }} for export"></label></td>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bx bx-briefcase-alt fs-3"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.show', $project->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $project->name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.72rem;">{{ $project->project_code ?: ('PRJ-' . $project->id) }}</span>
                                            @if($project->priority)
                                                <span class="badge rounded-pill px-2 {{ $priorityClass }}" style="font-size: 0.68rem;">{{ ucfirst($project->priority) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Client Details -->
                            <td>
                                @if($project->client)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($project->client->name ?? 'C', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $project->client->name }}</div>
                                            <small class="text-muted">{{ $project->client->company_name ?: ($project->client->email ?: 'Client') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">No Client Profile</span>
                                @endif
                            </td>

                            <!-- Assigned Team -->
                            <td>
                                @if($project->users && $project->users->count() > 0)
                                    <div class="avatar-group d-flex align-items-center">
                                        @foreach($project->users->take(4) as $member)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" 
                                                  title="{{ $member->name }} ({{ $member->employeeDetail?->designation?->name ?: 'Member' }})"
                                                  style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($member->name, 0, 2)) }}
                                            </span>
                                        @endforeach
                                        @if($project->users->count() > 4)
                                            <span class="avatar avatar-sm rounded-circle border border-2 border-white bg-light text-muted d-inline-flex align-items-center justify-content-center small fw-bold" style="width: 32px; height: 32px; font-size: 0.72rem;">
                                                +{{ $project->users->count() - 4 }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $project->users->count() }} member(s)</small>
                                @else
                                    <span class="text-muted small fst-italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Progress -->
                            <td style="min-width: 140px;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small fw-bold text-dark">{{ $progPercent }}%</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $project->tasks_count ?? 0 }} task(s)</small>
                                </div>
                                <div class="progress rounded-pill" style="height: 6px;">
                                    <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $progPercent }}%" aria-valuenow="{{ $progPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>

                            <!-- Timeline -->
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <i class="bx bx-calendar-event text-success me-1"></i>
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : 'Not set' }}
                                </div>
                                <div class="small text-muted">
                                    to {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : ($project->without_deadline ? 'No deadline' : 'Ongoing') }}
                                </div>
                            </td>

                            <!-- Cost / Deal Value (Fetched from Deals Section) -->
                            <td>
                                <div class="fw-bold text-dark fs-6 cost-badge">
                                    {{ $project->currency_symbol ?? '$' }}{{ number_format($project->calculated_cost, 2) }}
                                </div>
                                @if($project->cost_source === 'deal')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.68rem;" title="Fetched automatically from Deals section">
                                        <i class="bx bx-check-circle me-1"></i>From Deal: {{ Str::limit($project->deal_reference, 16) }}
                                    </span>
                                @elseif($project->cost_source === 'budget')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2" style="font-size: 0.68rem;">
                                        Budget Allocated
                                    </span>
                                @elseif($project->cost_source === 'expenses')
                                    <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 0.68rem;">
                                        Expenses Sum
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted rounded-pill px-2" style="font-size: 0.68rem;">
                                        Pending Deal Value
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge {{ $statusClass }} border rounded-pill px-3 py-1 fw-semibold text-capitalize">
                                    {{ str_replace('_', ' ', $project->status ?: 'In Progress') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for {{ $project->name }}">
                                        <svg class="project-actions-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.show', $project->id) }}">
                                                <i class="bx bx-show text-primary me-2"></i> View Project Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('tasks.index', ['project_id' => $project->id]) }}">
                                                <i class="bx bx-check-square text-info me-2"></i> Manage Tasks
                                            </a>
                                        </li>
                                        @if($project->client)
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('clients.show', $project->client_id) }}">
                                                    <i class="bx bx-user text-success me-2"></i> View Client Profile
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.edit', $project->id) }}">
                                                <i class="bx bx-edit text-warning me-2"></i> Edit Project
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="pms-table-empty">
                                <div class="pms-table-empty-icon" aria-hidden="true">
                                    <i class="bx bx-buildings"></i>
                                </div>
                                <h6>No Client Projects found.</h6>
                                <p>Connect your sales pipeline or import active enterprise client contracts.</p>
                                @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
                                    <a href="{{ route('projects.create') }}" class="pms-empty-cta">
                                        <i class="bx bx-plus"></i> Create Client Project
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
