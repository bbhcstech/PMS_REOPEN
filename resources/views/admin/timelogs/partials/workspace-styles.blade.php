<style>
    /* Clean SaaS Workspace Design System for Timesheet & Connected Pages */
    .pms-ts-page {
        padding: 0 0 2rem !important;
        margin: 0 !important;
        min-height: 0 !important;
        background: transparent !important;
        font-family: 'Inter', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }

    .pms-ts-topbar {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        margin-bottom: 18px !important;
    }

    .pms-ts-title-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .pms-ts-title-icon {
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

    .pms-ts-title {
        margin: 0 !important;
        font-size: 1.35rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        letter-spacing: -0.02em !important;
        line-height: 1.25 !important;
    }

    .pms-ts-page .breadcrumb {
        margin: 2px 0 0 !important;
        padding: 0 !important;
        background: transparent !important;
        font-size: 0.78rem !important;
    }

    .pms-ts-page .breadcrumb-item a,
    .pms-ts-page .breadcrumb-item + .breadcrumb-item::before {
        color: #64748b !important;
    }

    .pms-ts-page .breadcrumb-item.active {
        color: #0f172a !important;
        font-weight: 600 !important;
    }

    .pms-ts-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .pms-ts-btn-primary {
        background: #2563eb !important;
        border: 1px solid #2563eb !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        height: 38px !important;
        padding: 0 18px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22) !important;
        text-decoration: none !important;
    }

    .pms-ts-btn-primary:hover {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .pms-ts-btn-primary i,
    .pms-ts-btn-primary * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .pms-ts-btn-secondary {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #0f172a !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        height: 38px !important;
        padding: 0 16px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        text-decoration: none !important;
    }

    .pms-ts-btn-secondary:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    .pms-ts-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    .pms-ts-card-header {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 16px 22px !important;
    }

    .pms-ts-label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #334155 !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .pms-ts-page .form-control,
    .pms-ts-page .form-select {
        min-height: 40px !important;
        border-color: #e2e8f0 !important;
        border-radius: 10px !important;
        font-size: 0.85rem !important;
        color: #0f172a !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
    }

    .pms-ts-page .input-group .input-group-text {
        background: #ffffff !important;
        border-color: #e2e8f0 !important;
        color: #64748b !important;
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
    }

    .pms-ts-page .input-group .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .pms-ts-page .form-control:focus,
    .pms-ts-page .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    .pms-ts-switcher-bar {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
    }

    .pms-ts-switcher {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        padding: 4px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .pms-ts-switcher .pms-ts-tab {
        height: 34px !important;
        padding: 0 14px !important;
        border-radius: 8px !important;
        border: none !important;
        background: transparent !important;
        color: #475569 !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        text-decoration: none !important;
        transition: all 0.16s ease !important;
    }

    .pms-ts-switcher .pms-ts-tab:hover {
        background: #f8fafc !important;
        color: #0f172a !important;
    }

    .pms-ts-switcher .pms-ts-tab.active {
        background: #2563eb !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25) !important;
    }

    .pms-ts-switcher .pms-ts-tab.active i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .pms-ts-page .table thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 12px 14px !important;
    }

    .pms-ts-page .table tbody td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 14px !important;
    }

    .pms-ts-user-box {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 14px 16px !important;
    }

    .pms-ts-detail-tile {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 14px 16px !important;
        height: 100% !important;
    }

    .pms-ts-empty {
        padding: 52px 24px !important;
        text-align: center !important;
    }

    .pms-ts-empty-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background: #eff6ff !important;
        border: 1px solid #dbeafe !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.35rem !important;
        margin: 0 auto 12px !important;
    }

    /* Dark Mode Support */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-card-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-switcher {
        background: #0f172a !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-title,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-page .breadcrumb-item.active,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-card h5,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-card .text-dark {
        color: #f8fafc !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-label {
        color: #cbd5e1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-btn-secondary,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-page .form-control,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-page .form-select,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-page .input-group .input-group-text,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-user-box,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-detail-tile,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-page .table thead th,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-page .list-group-item {
        background-color: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        color: #e2e8f0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-switcher .pms-ts-tab:not(.active) {
        color: #cbd5e1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-switcher .pms-ts-tab:not(.active):hover {
        background: #1e293b !important;
        color: #f8fafc !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-title-icon,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, [data-bs-theme="dark"]) .pms-ts-empty-icon {
        background: rgba(37, 99, 235, 0.16) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: #60a5fa !important;
    }
</style>
