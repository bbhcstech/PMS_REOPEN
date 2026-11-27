<style>
    /* =========================================================================
       EMPLOYEE DIRECTORY UI/UX — CLEAN ENTERPRISE SAAS DESIGN
       Strictly scoped to #employeeDirectoryPage (and body:has(#employeeDirectoryPage))
       so shared layouts and other views (such as archived employees) remain untouched.
       ========================================================================= */

    :root {
        --emp-bg: #f8fafc;
        --emp-surface: #ffffff;
        --emp-surface-subtle: #f8fafc;
        --emp-surface-pill: #f1f5f9;
        --emp-border: #e2e8f0;
        --emp-border-subtle: #f1f5f9;
        --emp-text: #0f172a;
        --emp-text-body: #334155;
        --emp-text-muted: #64748b;
        --emp-text-faint: #94a3b8;
        --emp-primary: #2563eb;
        --emp-primary-hover: #1d4ed8;
        --emp-primary-soft: #eff6ff;
        --emp-primary-border: #dbeafe;
        --emp-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --emp-shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.04);

        --emp-designation-color: #64748b;
        --emp-role-color: #0f172a;
        --emp-reports-color: #64748b;
        --emp-count-color: #64748b;
        --emp-footer-text: #475569;
        --emp-footer-select-bg: #ffffff;
        --emp-footer-select-color: #0f172a;
        --emp-footer-select-border: #e2e8f0;
    }

    html:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark),
    body:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) {
        --emp-bg: #070b1a;
        --emp-surface: #0f1530;
        --emp-surface-subtle: #141b3d;
        --emp-surface-pill: #1a2247;
        --emp-border: rgba(148, 163, 184, 0.18);
        --emp-border-subtle: rgba(148, 163, 184, 0.12);
        --emp-text: #f8fafc;
        --emp-text-body: #e2e8f0;
        --emp-text-muted: #94a3b8;
        --emp-text-faint: #64748b;
        --emp-primary: #3b82f6;
        --emp-primary-hover: #60a5fa;
        --emp-primary-soft: rgba(59, 130, 246, 0.16);
        --emp-primary-border: rgba(96, 165, 250, 0.28);
        --emp-shadow-xs: 0 4px 12px rgba(0, 0, 0, 0.25);
        --emp-shadow-sm: 0 8px 24px rgba(0, 0, 0, 0.32);

        --emp-designation-color: #cbd5e1 !important;
        --emp-role-color: #f8fafc !important;
        --emp-reports-color: #94a3b8 !important;
        --emp-count-color: #cbd5e1 !important;
        --emp-footer-text: #cbd5e1 !important;
        --emp-footer-select-bg: #141b3d !important;
        --emp-footer-select-color: #eef1fb !important;
        --emp-footer-select-border: rgba(96, 165, 250, 0.3) !important;
    }

    /* ===== PAGE SHELL & TOP UTILITY BAR (Back on left, Breadcrumb on right) ===== */
    body:has(#employeeDirectoryPage),
    body:has(#employeeDirectoryPage) .layout-page,
    body:has(#employeeDirectoryPage) .content-wrapper {
        background: var(--emp-bg) !important;
        background-image: none !important;
    }

    body:has(#employeeDirectoryPage) .content-wrapper > .container-xxl {
        padding-top: 14px !important;
        padding-bottom: 32px !important;
    }

    body:has(#employeeDirectoryPage) .pms-page-back-navigation {
        width: auto !important;
        display: inline-flex !important;
        float: left !important;
        clear: none !important;
        padding: 0 !important;
        margin: 0 0 14px 0 !important;
        position: relative !important;
        z-index: 5 !important;
    }

    body:has(#employeeDirectoryPage) .pms-page-back-navigation .pms-page-back-link {
        min-height: 34px !important;
        padding: 6px 14px !important;
        border-radius: 999px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        gap: 6px !important;
        box-shadow: var(--emp-shadow-xs) !important;
        transition: background 0.16s ease, border-color 0.16s ease, color 0.16s ease !important;
    }

    body:has(#employeeDirectoryPage) .pms-page-back-navigation .pms-page-back-link svg {
        width: 14px !important;
        height: 14px !important;
        color: var(--emp-text-muted) !important;
    }

    body:has(#employeeDirectoryPage) .pms-page-back-navigation .pms-page-back-link:hover {
        background: var(--emp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
    }

    #employeeDirectoryPage.employee-dashboard {
        background: transparent !important;
        background-image: none !important;
        padding: 0 !important;
        min-height: auto !important;
        font-family: 'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
        color: var(--emp-text) !important;
        overflow: visible !important;
    }

    #employeeDirectoryPage.employee-dashboard::before {
        display: none !important;
        content: none !important;
    }

    #employeeDirectoryPage .emp-breadcrumb-row {
        display: flex !important;
        justify-content: flex-end !important;
        align-items: center !important;
        min-height: 34px !important;
        margin-bottom: 14px !important;
    }

    #employeeDirectoryPage .breadcrumb-wrapper {
        width: fit-content !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 6px 14px !important;
        min-height: 34px !important;
        display: inline-flex !important;
        align-items: center !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 10px !important;
        box-shadow: var(--emp-shadow-xs) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    #employeeDirectoryPage .breadcrumb {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        gap: 2px !important;
    }

    #employeeDirectoryPage .breadcrumb-item {
        display: inline-flex !important;
        align-items: center !important;
        font-size: 0.78rem !important;
        font-weight: 500 !important;
        line-height: 1.2 !important;
    }

    #employeeDirectoryPage .breadcrumb-item a {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-weight: 500 !important;
        text-decoration: none !important;
    }

    #employeeDirectoryPage .breadcrumb-item a i {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.82rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .breadcrumb-item a:hover {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
    }

    #employeeDirectoryPage .breadcrumb-item.active {
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        font-weight: 600 !important;
    }

    #employeeDirectoryPage .breadcrumb-item.active i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 0.78rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .breadcrumb-item + .breadcrumb-item::before {
        content: "›" !important;
        color: var(--emp-text-faint) !important;
        padding: 0 6px !important;
        font-size: 0.95rem !important;
        line-height: 1 !important;
    }

    /* ===== HEADER CARD (Employee Management) ===== */
    #employeeDirectoryPage .header-section {
        clear: both !important;
        position: relative !important;
        z-index: 1050 !important;
        overflow: visible !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 14px !important;
        padding: 18px 22px !important;
        margin-bottom: 18px !important;
        box-shadow: var(--emp-shadow-xs) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    #employeeDirectoryPage .header-section::before {
        display: none !important;
        content: none !important;
    }

    #employeeDirectoryPage .header-content {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
        overflow: visible !important;
    }

    #employeeDirectoryPage .title-wrapper {
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
        min-width: 0 !important;
    }

    #employeeDirectoryPage .icon-circle {
        width: 46px !important;
        height: 46px !important;
        border-radius: 12px !important;
        background: var(--emp-primary-soft) !important;
        border: 1px solid var(--emp-primary-border) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: none !important;
        animation: none !important;
    }

    #employeeDirectoryPage .icon-circle i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 1.15rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .title-wrapper h1 {
        font-size: 1.32rem !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        letter-spacing: -0.02em !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .subtitle {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.82rem !important;
        font-weight: 400 !important;
        margin: 3px 0 0 !important;
        line-height: 1.4 !important;
    }

    #employeeDirectoryPage .action-buttons {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        margin-left: auto !important;
        overflow: visible !important;
        position: relative !important;
        z-index: 1055 !important;
    }

    #employeeDirectoryPage .header-section .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 38px !important;
        min-height: 38px !important;
        padding: 0 15px !important;
        border-radius: 10px !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        box-shadow: var(--emp-shadow-xs) !important;
        animation: none !important;
        transition: background 0.16s ease, border-color 0.16s ease, color 0.16s ease, transform 0.16s ease !important;
    }

    #employeeDirectoryPage .quick-actions,
    #employeeDirectoryPage .quick-actions .dropdown {
        display: inline-flex !important;
        position: relative !important;
        z-index: 1060 !important;
        overflow: visible !important;
    }

    #employeeDirectoryPage .quick-actions .dropdown-toggle {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        min-width: auto !important;
        border-radius: 10px !important;
        padding: 0 14px !important;
    }

    #employeeDirectoryPage .quick-actions .dropdown-toggle:hover {
        background: var(--emp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px) !important;
    }

    #employeeDirectoryPage .quick-actions .dropdown-toggle i {
        color: #eab308 !important;
        -webkit-text-fill-color: #eab308 !important;
        font-size: 0.85rem !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .quick-actions .dropdown-toggle::after {
        border-top-color: var(--emp-text-muted) !important;
        margin-left: 4px !important;
    }

    #employeeDirectoryPage .quick-actions .dropdown-menu {
        position: absolute !important;
        top: calc(100% + 6px) !important;
        bottom: auto !important;
        left: 0 !important;
        right: auto !important;
        min-width: 210px !important;
        transform: none !important;
        z-index: 999999 !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 12px !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12) !important;
        padding: 6px !important;
    }

    #employeeDirectoryPage .dropdown-item {
        border-radius: 8px !important;
        padding: 8px 12px !important;
        font-size: 0.84rem !important;
        font-weight: 500 !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
    }

    #employeeDirectoryPage .dropdown-item:hover {
        background: var(--emp-surface-pill) !important;
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
    }

    #employeeDirectoryPage .btn-archive-employees {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        min-width: auto !important;
    }

    #employeeDirectoryPage .btn-archive-employees i {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.85rem !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .btn-archive-employees span,
    #employeeDirectoryPage .btn-archive-employees .btn-archive-text {
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        font-weight: 600 !important;
    }

    #employeeDirectoryPage .btn-archive-employees:hover {
        background: var(--emp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px) !important;
    }

    #employeeDirectoryPage a.btn.btn-add {
        background: var(--emp-primary) !important;
        border: 1px solid var(--emp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.22) !important;
        min-width: auto !important;
    }

    #employeeDirectoryPage a.btn.btn-add:hover {
        background: var(--emp-primary-hover) !important;
        border-color: var(--emp-primary-hover) !important;
        transform: translateY(-1px) !important;
    }

    #employeeDirectoryPage a.btn.btn-add i,
    #employeeDirectoryPage a.btn.btn-add span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage button.btn.btn-invite {
        background: var(--emp-surface) !important;
        border: 1px solid #bfdbfe !important;
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        min-width: auto !important;
    }

    #employeeDirectoryPage button.btn.btn-invite i,
    #employeeDirectoryPage button.btn.btn-invite span {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage button.btn.btn-invite:hover {
        background: var(--emp-primary-soft) !important;
        border-color: #93c5fd !important;
        transform: translateY(-1px) !important;
    }

    /* ===== STATS CARDS (4 Equal Cards, Icon on Right, Subtitle at Bottom) ===== */
    #employeeDirectoryPage .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 16px !important;
        margin-bottom: 18px !important;
        position: relative !important;
        z-index: 1 !important;
    }

    #employeeDirectoryPage .stat-card {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-top: 1px solid var(--emp-border) !important;
        border-radius: 14px !important;
        padding: 18px 20px !important;
        min-height: 112px !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        box-shadow: var(--emp-shadow-xs) !important;
        position: relative !important;
        overflow: hidden !important;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease !important;
    }

    #employeeDirectoryPage .stat-card::before,
    #employeeDirectoryPage .stat-card .stat-glow {
        display: none !important;
        content: none !important;
    }

    #employeeDirectoryPage .stat-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: var(--emp-shadow-sm) !important;
        border-color: #cbd5e1 !important;
    }

    #employeeDirectoryPage .stat-content {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    #employeeDirectoryPage .stat-label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        margin-bottom: 6px !important;
        line-height: 1.2 !important;
    }

    #employeeDirectoryPage .stat-value {
        font-size: 1.75rem !important;
        font-weight: 700 !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        line-height: 1.1 !important;
        margin-bottom: 8px !important;
        letter-spacing: -0.02em !important;
    }

    #employeeDirectoryPage .stat-meta {
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        color: var(--emp-text-faint) !important;
        -webkit-text-fill-color: var(--emp-text-faint) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        line-height: 1.2 !important;
    }

    #employeeDirectoryPage .stat-meta-active,
    #employeeDirectoryPage .stat-meta-active i {
        color: #10b981 !important;
        -webkit-text-fill-color: #10b981 !important;
        font-weight: 600 !important;
        filter: none !important;
    }

    #employeeDirectoryPage .stat-icon {
        width: 42px !important;
        height: 42px !important;
        border-radius: 11px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .stat-icon i {
        font-size: 1.08rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .stat-card.total .stat-icon {
        background: #eff6ff !important;
        border: 1px solid #dbeafe !important;
    }
    #employeeDirectoryPage .stat-card.total .stat-icon i {
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
    }

    #employeeDirectoryPage .stat-card.active .stat-icon {
        background: #ecfdf5 !important;
        border: 1px solid #d1fae5 !important;
    }
    #employeeDirectoryPage .stat-card.active .stat-icon i {
        color: #10b981 !important;
        -webkit-text-fill-color: #10b981 !important;
    }

    #employeeDirectoryPage .stat-card.inactive .stat-icon {
        background: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
    }
    #employeeDirectoryPage .stat-card.inactive .stat-icon i {
        color: #ef4444 !important;
        -webkit-text-fill-color: #ef4444 !important;
    }

    #employeeDirectoryPage .stat-card.notice .stat-icon {
        background: #fefce8 !important;
        border: 1px solid #fef08a !important;
    }
    #employeeDirectoryPage .stat-card.notice .stat-icon i {
        color: #ca8a04 !important;
        -webkit-text-fill-color: #ca8a04 !important;
    }

    /* ===== COMPANY SWITCHER (Super Admin Workspace Filter) ===== */
    #employeeDirectoryPage .company-switcher-grid {
        margin-bottom: 18px !important;
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)) !important;
        gap: 12px !important;
    }

    #employeeDirectoryPage .company-switch-card {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 12px 14px !important;
        border-radius: 14px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        box-shadow: var(--emp-shadow-xs) !important;
        text-decoration: none !important;
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease !important;
    }

    #employeeDirectoryPage .company-switch-card:hover {
        border-color: #93c5fd !important;
        transform: translateY(-1px) !important;
    }

    #employeeDirectoryPage .company-switch-card.active {
        background: var(--emp-primary-soft) !important;
        border-color: var(--emp-primary) !important;
    }

    #employeeDirectoryPage .company-switch-icon,
    #employeeDirectoryPage .employee-company-logo {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 10px !important;
        background: var(--emp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
        overflow: hidden !important;
        flex-shrink: 0 !important;
    }

    #employeeDirectoryPage .company-switch-icon {
        width: 40px !important;
        height: 40px !important;
    }

    #employeeDirectoryPage .company-switch-icon *,
    #employeeDirectoryPage .company-switch-icon i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        filter: none !important;
    }

    #employeeDirectoryPage .company-switch-icon img,
    #employeeDirectoryPage .employee-company-logo img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    #employeeDirectoryPage .company-switch-meta {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
    }

    #employeeDirectoryPage .company-switch-meta strong {
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        font-size: 0.86rem !important;
        font-weight: 600 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    #employeeDirectoryPage .company-switch-meta small {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.74rem !important;
    }

    #employeeDirectoryPage .company-module-rail {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 10px 14px !important;
        margin-bottom: 18px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 12px !important;
    }

    #employeeDirectoryPage .company-module-rail span {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: var(--emp-text-muted) !important;
    }

    #employeeDirectoryPage .company-module-rail a {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 6px 12px !important;
        border-radius: 8px !important;
        background: var(--emp-primary-soft) !important;
        color: var(--emp-primary) !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        text-decoration: none !important;
    }

    /* ===== FILTER & EXPORT CARD ===== */
    #employeeDirectoryPage .filter-export-card {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 14px !important;
        margin-bottom: 18px !important;
        box-shadow: var(--emp-shadow-xs) !important;
        overflow: visible !important;
        backdrop-filter: none !important;
    }

    #employeeDirectoryPage .filter-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 14px !important;
        padding: 16px 22px !important;
        background: var(--emp-surface) !important;
        border-bottom: 1px solid var(--emp-border-subtle) !important;
        border-radius: 14px 14px 0 0 !important;
    }

    #employeeDirectoryPage .header-left {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }

    #employeeDirectoryPage .header-icon-wrapper {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: var(--emp-primary-soft) !important;
        border: 1px solid var(--emp-primary-border) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .header-icon-wrapper i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 0.95rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .header-text h3 {
        font-size: 0.98rem !important;
        font-weight: 700 !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        margin: 0 !important;
        line-height: 1.25 !important;
        letter-spacing: -0.01em !important;
    }

    #employeeDirectoryPage .header-text p {
        font-size: 0.78rem !important;
        font-weight: 400 !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        margin: 2px 0 0 !important;
        line-height: 1.3 !important;
    }

    #employeeDirectoryPage .header-right {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    #employeeDirectoryPage .selected-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 34px !important;
        padding: 0 12px !important;
        background: var(--emp-surface-pill) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 8px !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .btn-export-all {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 36px !important;
        min-height: 36px !important;
        padding: 0 15px !important;
        border-radius: 10px !important;
        background: var(--emp-primary) !important;
        border: 1px solid var(--emp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2) !important;
    }

    #employeeDirectoryPage .btn-export-all i,
    #employeeDirectoryPage .btn-export-all span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .btn-export-all:hover {
        background: var(--emp-primary-hover) !important;
        border-color: var(--emp-primary-hover) !important;
    }

    #employeeDirectoryPage .filter-header .btn-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 2px !important;
        height: 36px !important;
        padding: 2px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 10px !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .filter-header .btn-group .btn {
        width: 32px !important;
        height: 30px !important;
        min-width: 32px !important;
        min-height: 30px !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 7px !important;
        background: transparent !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .filter-header .btn-group .btn i {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.88rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .filter-header .btn-group .btn:hover:not(:disabled) {
        background: var(--emp-surface-pill) !important;
    }

    #employeeDirectoryPage .filter-header .btn-group .btn:hover:not(:disabled) i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
    }

    #employeeDirectoryPage .filter-header .btn-group .btn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
    }

    #employeeDirectoryPage .filter-body {
        padding: 18px 22px 20px !important;
        background: var(--emp-surface) !important;
        border-radius: 0 0 14px 14px !important;
    }

    #employeeDirectoryPage .employee-filter-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) auto !important;
        gap: 14px !important;
        align-items: end !important;
    }

    #employeeDirectoryPage .employee-filter-field {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
        min-height: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .employee-filter-field .filter-label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        margin: 0 0 7px 0 !important;
        line-height: 1.2 !important;
    }

    #employeeDirectoryPage .employee-filter-field .filter-label i {
        width: auto !important;
        height: auto !important;
        background: transparent !important;
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 0.84rem !important;
        margin: 0 !important;
        filter: none !important;
    }

    #employeeDirectoryPage .employee-filter-field .form-select {
        width: 100% !important;
        height: 40px !important;
        min-height: 40px !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 10px !important;
        background-color: var(--emp-surface) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        font-size: 0.84rem !important;
        font-weight: 500 !important;
        padding: 0 36px 0 12px !important;
        box-shadow: none !important;
        transition: border-color 0.16s ease, box-shadow 0.16s ease !important;
    }

    #employeeDirectoryPage .employee-filter-field .form-select:focus {
        border-color: var(--emp-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none !important;
    }

    #employeeDirectoryPage .emp-search-select-wrap {
        position: relative !important;
        width: 100% !important;
    }

    #employeeDirectoryPage .emp-search-select-wrap .form-select {
        background-image: none !important;
        padding-right: 36px !important;
    }

    #employeeDirectoryPage .emp-search-select-icon {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: var(--emp-text-faint) !important;
        -webkit-text-fill-color: var(--emp-text-faint) !important;
        font-size: 0.84rem !important;
        pointer-events: none !important;
        filter: none !important;
    }

    #employeeDirectoryPage .employee-filter-actions {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        min-height: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 40px !important;
        min-height: 40px !important;
        border-radius: 10px !important;
        font-size: 0.83rem !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn-apply {
        padding: 0 18px !important;
        background: var(--emp-primary) !important;
        border: 1px solid var(--emp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2) !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn-apply i,
    #employeeDirectoryPage .employee-filter-actions .btn-apply span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn-apply:hover {
        background: var(--emp-primary-hover) !important;
        border-color: var(--emp-primary-hover) !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn-reset {
        padding: 0 16px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        text-decoration: none !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn-reset i,
    #employeeDirectoryPage .employee-filter-actions .btn-reset span {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .employee-filter-actions .btn-reset:hover {
        background: var(--emp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--emp-text) !important;
    }

    /* ===== EMPLOYEE LIST CARD ===== */
    #employeeDirectoryPage .table-card {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 14px !important;
        box-shadow: var(--emp-shadow-xs) !important;
        overflow: hidden !important;
        backdrop-filter: none !important;
    }

    #employeeDirectoryPage .table-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 14px !important;
        padding: 16px 22px !important;
        background: var(--emp-surface) !important;
        border-bottom: 1px solid var(--emp-border-subtle) !important;
    }

    #employeeDirectoryPage .table-title {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }

    #employeeDirectoryPage .title-icon-wrapper {
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        background: var(--emp-primary-soft) !important;
        border: 1px solid var(--emp-primary-border) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .title-icon-wrapper i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 0.92rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .title-text {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    #employeeDirectoryPage .title-text h3 {
        font-size: 0.98rem !important;
        font-weight: 700 !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        margin: 0 !important;
        letter-spacing: -0.01em !important;
    }

    #employeeDirectoryPage .employee-count {
        display: inline-flex !important;
        align-items: center !important;
        padding: 3px 10px !important;
        border-radius: 999px !important;
        background: var(--emp-surface-pill) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
    }

    #employeeDirectoryPage .table-actions {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 36px !important;
        min-height: 36px !important;
        padding: 0 14px !important;
        border-radius: 10px !important;
        background: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
        -webkit-text-fill-color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        box-shadow: none !important;
        opacity: 1 !important;
        transition: background 0.16s ease, border-color 0.16s ease !important;
    }

    #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete.is-awaiting-selection {
        opacity: 0.88 !important;
    }

    #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete i,
    #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete span {
        color: #dc2626 !important;
        -webkit-text-fill-color: #dc2626 !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete:hover {
        background: #fee2e2 !important;
        border-color: #fca5a5 !important;
        opacity: 1 !important;
    }

    /* ===== EMPTY STATE (Matches Reference Screenshot) ===== */
    #employeeDirectoryPage .empty-state {
        padding: 72px 24px !important;
        text-align: center !important;
        background: var(--emp-surface) !important;
    }

    #employeeDirectoryPage .empty-state-icon {
        width: 58px !important;
        height: 58px !important;
        margin: 0 auto 16px !important;
        border-radius: 14px !important;
        background: var(--emp-surface-subtle) !important;
        border: 1px solid var(--emp-border) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #employeeDirectoryPage .empty-state-icon i {
        color: var(--emp-text-faint) !important;
        -webkit-text-fill-color: var(--emp-text-faint) !important;
        font-size: 1.45rem !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .empty-state h5 {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        margin: 0 0 8px !important;
        letter-spacing: -0.01em !important;
    }

    #employeeDirectoryPage .empty-state p {
        font-size: 0.83rem !important;
        font-weight: 400 !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        max-width: 380px !important;
        margin: 0 auto 22px !important;
        line-height: 1.55 !important;
    }

    #employeeDirectoryPage .empty-state-actions {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    #employeeDirectoryPage .empty-state-actions .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 7px !important;
        height: 38px !important;
        min-height: 38px !important;
        padding: 0 16px !important;
        border-radius: 10px !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        box-shadow: var(--emp-shadow-xs) !important;
    }

    #employeeDirectoryPage .btn-empty-primary {
        background: var(--emp-primary) !important;
        border: 1px solid var(--emp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #employeeDirectoryPage .btn-empty-primary i,
    #employeeDirectoryPage .btn-empty-primary span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .btn-empty-primary:hover {
        background: var(--emp-primary-hover) !important;
        border-color: var(--emp-primary-hover) !important;
    }

    #employeeDirectoryPage .btn-empty-secondary {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
    }

    #employeeDirectoryPage .btn-empty-secondary i,
    #employeeDirectoryPage .btn-empty-secondary span {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        filter: none !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .btn-empty-secondary:hover {
        background: var(--emp-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--emp-text) !important;
    }

    /* ===== POPULATED EMPLOYEE TABLE (Cohesive Clean SaaS Table) ===== */
    #employeeDirectoryPage .table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        background: var(--emp-surface) !important;
    }

    #employeeDirectoryPage #employeeTable.employee-list-table {
        width: 100% !important;
        min-width: 1080px !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin-bottom: 0 !important;
        table-layout: auto !important;
    }

    #employeeDirectoryPage #employeeTable thead th {
        background: var(--emp-surface-subtle) !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        padding: 13px 16px !important;
        border-top: 0 !important;
        border-bottom: 1px solid var(--emp-border) !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }

    #employeeDirectoryPage #employeeTable tbody tr {
        background: var(--emp-surface) !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        height: auto !important;
        transition: background 0.14s ease !important;
    }

    #employeeDirectoryPage #employeeTable tbody tr:hover {
        background: var(--emp-surface-subtle) !important;
    }

    #employeeDirectoryPage #employeeTable tbody tr.selected {
        background: var(--emp-primary-soft) !important;
    }

    #employeeDirectoryPage #employeeTable tbody td {
        padding: 13px 16px !important;
        border: 0 !important;
        border-bottom: 1px solid var(--emp-border-subtle) !important;
        background: transparent !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        vertical-align: middle !important;
        border-radius: 0 !important;
        font-weight: 500 !important;
    }

    #employeeDirectoryPage #employeeTable .form-check {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        min-height: 24px !important;
    }

    #employeeDirectoryPage #employeeTable input.form-check-input[type="checkbox"] {
        appearance: none !important;
        -webkit-appearance: none !important;
        width: 17px !important;
        height: 17px !important;
        min-width: 17px !important;
        margin: 0 !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 5px !important;
        background-color: var(--emp-surface) !important;
        cursor: pointer !important;
    }

    #employeeDirectoryPage #employeeTable input.form-check-input[type="checkbox"]:checked,
    #employeeDirectoryPage #employeeTable input.form-check-input[type="checkbox"]:indeterminate {
        background-color: var(--emp-primary) !important;
        border-color: var(--emp-primary) !important;
        background-position: center !important;
        background-size: 12px 12px !important;
        background-repeat: no-repeat !important;
    }

    #employeeDirectoryPage #employeeTable input.form-check-input[type="checkbox"]:checked {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m3 8 3 3 7-7'/%3E%3C/svg%3E") !important;
    }

    #employeeDirectoryPage #employeeTable input.form-check-input[type="checkbox"]:indeterminate {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath stroke='white' stroke-linecap='round' stroke-width='2.5' d='M4 8h8'/%3E%3C/svg%3E") !important;
    }

    #employeeDirectoryPage #employeeTable input.form-check-input[type="checkbox"]:disabled {
        opacity: 0.45 !important;
        cursor: not-allowed !important;
    }

    #employeeDirectoryPage .employee-id-badge {
        display: inline-block !important;
        padding: 4px 10px !important;
        background: var(--emp-surface-pill) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 7px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
    }

    #employeeDirectoryPage .employee-info {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-width: 0 !important;
    }

    #employeeDirectoryPage .profile-image,
    #employeeDirectoryPage .profile-placeholder {
        width: 40px !important;
        height: 40px !important;
        border-radius: 10px !important;
        flex-shrink: 0 !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .profile-image {
        object-fit: cover !important;
        border: 1px solid var(--emp-border) !important;
    }

    #employeeDirectoryPage .profile-placeholder {
        background: var(--emp-primary-soft) !important;
        border: 1px solid var(--emp-primary-border) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #employeeDirectoryPage .profile-initials {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
    }

    #employeeDirectoryPage .employee-details {
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    #employeeDirectoryPage .employee-name {
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        color: var(--emp-text) !important;
        -webkit-text-fill-color: var(--emp-text) !important;
        margin: 0 0 2px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    #employeeDirectoryPage .employee-designation {
        display: block !important;
        font-size: 0.77rem !important;
        color: var(--emp-designation-color) !important;
        margin-bottom: 5px !important;
        font-weight: 500 !important;
    }

    #employeeDirectoryPage .employee-badges {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 5px !important;
    }

    #employeeDirectoryPage .employee-badges .badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        padding: 3px 9px !important;
        border-radius: 999px !important;
        font-size: 0.68rem !important;
        font-weight: 600 !important;
    }

    #employeeDirectoryPage .employee-badges .badge.bg-primary {
        background: var(--emp-primary-soft) !important;
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
    }

    #employeeDirectoryPage .employee-badges .badge.bg-primary i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        filter: none !important;
    }

    #employeeDirectoryPage .employee-badges .badge.bg-info {
        background: #ecfeff !important;
        color: #0891b2 !important;
        -webkit-text-fill-color: #0891b2 !important;
    }

    #employeeDirectoryPage .employee-badges .badge.bg-warning {
        background: #fefce8 !important;
        color: #b45309 !important;
        -webkit-text-fill-color: #b45309 !important;
    }

    #employeeDirectoryPage .employee-company-chip {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        max-width: 185px !important;
        min-width: 0 !important;
    }

    #employeeDirectoryPage .employee-company-logo {
        width: 28px !important;
        height: 28px !important;
        border-radius: 8px !important;
        font-size: 0.68rem !important;
    }

    #employeeDirectoryPage .employee-company-name {
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    #employeeDirectoryPage .email-wrapper {
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        min-width: 0 !important;
    }

    #employeeDirectoryPage .email-icon {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        font-size: 0.82rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .email-link {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.83rem !important;
        text-decoration: none !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    #employeeDirectoryPage .email-link:hover {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
        text-decoration: underline !important;
    }

    #employeeDirectoryPage .role-info {
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
        min-width: 0 !important;
    }

    #employeeDirectoryPage .role-name {
        font-size: 0.84rem !important;
        font-weight: 600 !important;
    }

    #employeeDirectoryPage .reports-to {
        font-size: 0.76rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    #employeeDirectoryPage .status-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 4px 11px !important;
        border-radius: 999px !important;
        font-size: 0.76rem !important;
        font-weight: 600 !important;
    }

    #employeeDirectoryPage .status-dot {
        width: 7px !important;
        height: 7px !important;
        border-radius: 50% !important;
        margin: 0 !important;
    }

    #employeeDirectoryPage .status-active {
        background: #ecfdf5 !important;
        border: 1px solid #d1fae5 !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }
    #employeeDirectoryPage .status-active .status-text {
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }
    #employeeDirectoryPage .status-active .status-dot {
        background: #10b981 !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .status-inactive {
        background: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
        -webkit-text-fill-color: #dc2626 !important;
    }
    #employeeDirectoryPage .status-inactive .status-text {
        color: #dc2626 !important;
        -webkit-text-fill-color: #dc2626 !important;
    }
    #employeeDirectoryPage .status-inactive .status-dot {
        background: #ef4444 !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .status-unknown {
        background: var(--emp-surface-pill) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
    }

    #employeeDirectoryPage .btn-action-dots {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        min-height: 34px !important;
        padding: 0 !important;
        border-radius: 9px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        box-shadow: none !important;
    }

    #employeeDirectoryPage .btn-action-dots i {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
        font-size: 0.9rem !important;
        filter: none !important;
    }

    #employeeDirectoryPage .btn-action-dots:hover {
        background: var(--emp-surface-pill) !important;
        border-color: #cbd5e1 !important;
    }

    #employeeDirectoryPage .btn-action-dots:hover i {
        color: var(--emp-primary) !important;
        -webkit-text-fill-color: var(--emp-primary) !important;
    }

    #employeeDirectoryPage #employeeTable .dropdown-menu {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 12px !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12) !important;
        padding: 6px !important;
    }

    /* ===== TABLE FOOTER & PAGINATION ===== */
    #employeeDirectoryPage .table-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 14px !important;
        padding: 14px 22px !important;
        background: var(--emp-surface-subtle) !important;
        border-top: 1px solid var(--emp-border-subtle) !important;
    }

    #employeeDirectoryPage .show-entries {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 0.82rem !important;
    }

    #employeeDirectoryPage .show-entries select {
        height: 34px !important;
        min-height: 34px !important;
        padding: 0 28px 0 10px !important;
        border-radius: 8px !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        width: auto !important;
    }

    #employeeDirectoryPage .pagination-info {
        font-size: 0.82rem !important;
    }

    #employeeDirectoryPage .pagination {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    #employeeDirectoryPage .page-btn {
        min-width: 34px !important;
        height: 34px !important;
        padding: 0 10px !important;
        border-radius: 8px !important;
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        -webkit-text-fill-color: var(--emp-text-body) !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
    }

    #employeeDirectoryPage .page-btn:hover:not(.disabled):not(.active) {
        background: var(--emp-surface-pill) !important;
        border-color: #cbd5e1 !important;
    }

    #employeeDirectoryPage .page-btn.active {
        background: var(--emp-primary) !important;
        border-color: var(--emp-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #employeeDirectoryPage .page-btn.disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }

    /* ===== INVITE & RESTRICTED MODALS ===== */
    #inviteModal,
    #blockedDeleteModal,
    #employeeDeleteModal,
    #employeeInactiveModal,
    #quickResetPasswordModal {
        z-index: 10055 !important;
    }

    #inviteModal .modal-dialog,
    #blockedDeleteModal .modal-dialog,
    #employeeDeleteModal .modal-dialog,
    #employeeInactiveModal .modal-dialog,
    #quickResetPasswordModal .modal-dialog {
        z-index: 10056 !important;
    }

    #inviteModal .modal-content {
        background: var(--emp-surface) !important;
        border: 1px solid var(--emp-border) !important;
        border-radius: 16px !important;
        color: var(--emp-text) !important;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22) !important;
    }

    #inviteModal .modal-header {
        border-bottom: 1px solid var(--emp-border) !important;
        padding: 1.25rem 1.5rem !important;
    }

    #inviteModal .modal-title {
        color: var(--emp-text) !important;
        font-weight: 700 !important;
    }

    #inviteModal .modal-body {
        padding: 1.25rem 1.5rem !important;
    }

    #inviteModal .modal-footer {
        border-top: 1px solid var(--emp-border) !important;
        padding: 1rem 1.5rem !important;
    }

    #inviteModal .tab-content {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 1rem 0 0 !important;
    }

    #inviteModal .alert-info.bg-light,
    #inviteModal .alert-light {
        background: var(--emp-surface-subtle) !important;
        border: 1px solid var(--emp-border) !important;
        color: var(--emp-text-body) !important;
        margin-bottom: 1rem !important;
    }

    #inviteModal .form-label {
        color: var(--emp-text) !important;
    }

    #inviteModal .form-label .text-muted {
        color: var(--emp-text-muted) !important;
    }

    #inviteModal .input-group-text {
        background: var(--emp-surface-subtle) !important;
        border-color: var(--emp-border) !important;
        color: var(--emp-text-muted) !important;
    }

    #inviteModal .form-control {
        background: var(--emp-surface) !important;
        border-color: var(--emp-border) !important;
        color: var(--emp-text) !important;
    }

    #inviteModal .form-control::placeholder {
        color: var(--emp-text-muted) !important;
        opacity: 0.75 !important;
    }

    #inviteModal .input-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
    }

    #inviteModal .input-group > .input-group-text {
        flex: 0 0 auto !important;
        width: auto !important;
    }

    #inviteModal .input-group > .form-control {
        flex: 1 1 auto !important;
        width: 1% !important;
        min-width: 0 !important;
    }

    #inviteModal .btn-primary,
    #inviteModal .btn-primary * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #inviteModal .modal-content {
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #inviteModal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #quickResetPasswordModal .btn-close {
        filter: invert(1) grayscale(1) brightness(1.6);
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #inviteModal .nav-tabs {
        background: var(--emp-surface-subtle) !important;
        border-color: var(--emp-border) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #inviteModal .nav-tabs .nav-link:not(.active) {
        color: var(--emp-text-muted) !important;
        -webkit-text-fill-color: var(--emp-text-muted) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #inviteModal .nav-tabs .nav-link.active {
        background: var(--emp-primary-soft) !important;
        color: #93c5fd !important;
        -webkit-text-fill-color: #93c5fd !important;
    }

    /* ===== DARK MODE POLISH ===== */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .stat-card.total .stat-icon {
        background: rgba(59, 130, 246, 0.16) !important;
        border-color: rgba(96, 165, 250, 0.28) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .stat-card.active .stat-icon {
        background: rgba(16, 185, 129, 0.16) !important;
        border-color: rgba(52, 211, 153, 0.28) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .stat-card.inactive .stat-icon {
        background: rgba(239, 68, 68, 0.16) !important;
        border-color: rgba(248, 113, 113, 0.28) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .stat-card.notice .stat-icon {
        background: rgba(245, 158, 11, 0.16) !important;
        border-color: rgba(251, 191, 36, 0.28) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete {
        background: rgba(239, 68, 68, 0.16) !important;
        border-color: rgba(248, 113, 113, 0.35) !important;
        color: #fca5a5 !important;
        -webkit-text-fill-color: #fca5a5 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete i,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage #btn-bulk-delete.btn-bulk-delete span {
        color: #fca5a5 !important;
        -webkit-text-fill-color: #fca5a5 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage button.btn.btn-invite {
        border-color: rgba(96, 165, 250, 0.35) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .status-active {
        background: rgba(16, 185, 129, 0.18) !important;
        border-color: rgba(52, 211, 153, 0.35) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .status-active .status-text {
        color: #6ee7b7 !important;
        -webkit-text-fill-color: #6ee7b7 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .status-inactive {
        background: rgba(239, 68, 68, 0.18) !important;
        border-color: rgba(248, 113, 113, 0.35) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage .status-inactive .status-text {
        color: #fca5a5 !important;
        -webkit-text-fill-color: #fca5a5 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body[data-pms-theme="dark"], body.dark-mode) #employeeDirectoryPage #employeeTable tbody tr.table-warning td {
        background: rgba(245, 158, 11, 0.1) !important;
    }

    button.status-badge.js-employee-status-toggle {
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    }

    button.status-badge.js-employee-status-toggle:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    button.status-badge.js-employee-status-toggle:focus-visible {
        outline: 2px solid #4f46e5;
        outline-offset: 2px;
    }

    button.status-badge.js-employee-status-toggle:disabled {
        opacity: 0.65;
        cursor: wait;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */
    @media (max-width: 1199.98px) {
        #employeeDirectoryPage .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        #employeeDirectoryPage .employee-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 991.98px) {
        #employeeDirectoryPage .header-content {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        #employeeDirectoryPage .action-buttons {
            width: 100% !important;
            margin-left: 0 !important;
            justify-content: flex-start !important;
        }
    }

    @media (max-width: 767.98px) {
        body:has(#employeeDirectoryPage) .content-wrapper > .container-xxl {
            padding-top: 10px !important;
            padding-inline: 12px !important;
        }

        #employeeDirectoryPage .table-card > .table-responsive {
            padding: 12px !important;
        }

        #employeeDirectoryPage #employeeTable.employee-list-table,
        #employeeDirectoryPage #employeeTable.employee-list-table tbody {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }

        #employeeDirectoryPage #employeeTable thead {
            display: none !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 10px;
            width: 100% !important;
            margin: 0 0 12px !important;
            padding: 12px !important;
            border: 1px solid var(--emp-border) !important;
            border-radius: 12px !important;
            background: var(--emp-surface) !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr > td {
            display: block !important;
            grid-column: 1 / -1;
            width: auto !important;
            min-width: 0 !important;
            max-width: 100% !important;
            padding: 9px 0 !important;
            text-align: left !important;
            border-bottom: 1px solid var(--dir-border) !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr > td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 3px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--emp-text-muted);
        }

        #employeeDirectoryPage #employeeTable tbody tr > td:first-child:not([colspan]) {
            grid-column: 1;
            grid-row: 1;
            padding: 0 0 8px !important;
            display: flex !important;
            align-items: center !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr > td:last-child:not([colspan]) {
            grid-column: 2;
            grid-row: 1;
            padding: 0 0 8px !important;
            text-align: right !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr > td:nth-child(3) { grid-row: 2; }

        #employeeDirectoryPage #employeeTable tbody tr > td:nth-child(2) {
            grid-area: 3 / 1;
            padding-inline-end: 8px !important;
            border-inline-end: 1px solid var(--dir-border) !important;
        }

        #employeeDirectoryPage #employeeTable tbody tr > td:nth-child(4) { grid-area: 3 / 2; }

        #employeeDirectoryPage #employeeTable tbody tr > td:nth-child(7) {
            padding-bottom: 0 !important;
            border-bottom: 0 !important;
        }

        #employeeDirectoryPage #employeeTable td:first-child::before,
        #employeeDirectoryPage #employeeTable td:nth-child(3)::before,
        #employeeDirectoryPage #employeeTable td:last-child::before {
            display: none !important;
        }
    }

    @media (max-width: 639.98px) {
        body:has(#employeeDirectoryPage) .pms-page-back-navigation {
            float: none !important;
            margin-bottom: 10px !important;
        }

        #employeeDirectoryPage .emp-breadcrumb-row {
            justify-content: flex-start !important;
            margin-bottom: 12px !important;
        }

        #employeeDirectoryPage .stats-grid,
        #employeeDirectoryPage .employee-filter-grid {
            grid-template-columns: 1fr !important;
        }

        #employeeDirectoryPage .action-buttons {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        #employeeDirectoryPage .quick-actions,
        #employeeDirectoryPage .quick-actions .dropdown,
        #employeeDirectoryPage .quick-actions .dropdown-toggle,
        #employeeDirectoryPage .btn-archive-employees,
        #employeeDirectoryPage a.btn.btn-add,
        #employeeDirectoryPage button.btn.btn-invite {
            width: 100% !important;
        }

        #employeeDirectoryPage .employee-filter-actions {
            flex-direction: row !important;
            width: 100% !important;
        }

        #employeeDirectoryPage .employee-filter-actions .btn {
            flex: 1 1 0 !important;
        }

        #employeeDirectoryPage .header-right,
        #employeeDirectoryPage .btn-export-all {
            width: 100% !important;
        }

        #employeeDirectoryPage .filter-header .btn-group {
            width: 100% !important;
            justify-content: space-between !important;
        }

        #employeeDirectoryPage .filter-header .btn-group .btn {
            flex: 1 1 auto !important;
        }
    }
</style>
@include('admin.employees.partials.workspace-overrides')
