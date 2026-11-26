<style>
    /* Clean SaaS Workspace Overrides for Connected Employee Views (Create / Edit / HR List / Archive) */
    .ambient-bg,
    .ambient-bg-archive,
    .employee-dashboard::before {
        display: none !important;
    }

    .employee-dashboard,
    #archivedEmployeesPage.employee-dashboard,
    #employee-create-page.container-fluid,
    #pms-emp-edit-page.container-fluid {
        padding: 0 0 2rem !important;
        margin: 0 !important;
        min-height: 0 !important;
        background: transparent !important;
        font-family: 'Inter', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }

    /* Compact Breadcrumb Wrapper */
    .employee-dashboard .breadcrumb-wrapper {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 10px 16px !important;
        margin-bottom: 16px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        backdrop-filter: none !important;
    }

    .employee-dashboard .breadcrumb-item a {
        color: #64748b !important;
    }

    .employee-dashboard .breadcrumb-item.active {
        color: #0f172a !important;
        font-weight: 600 !important;
    }

    .employee-dashboard .breadcrumb-item + .breadcrumb-item::before {
        color: #94a3b8 !important;
    }

    /* Unified Clean White Header Cards */
    #employee-create-page .page-header-enterprise,
    #pms-emp-edit-page .page-header-enterprise,
    .employee-dashboard .header-section,
    #archivedEmployeesPage .archive-hero {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 20px 24px !important;
        margin-bottom: 18px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 8px 20px rgba(15, 23, 42, 0.03) !important;
        color: #0f172a !important;
        backdrop-filter: none !important;
    }

    #employee-create-page .page-header-enterprise::before,
    #pms-emp-edit-page .page-header-enterprise::before,
    .employee-dashboard .header-section::before,
    #archivedEmployeesPage .archive-hero::before {
        display: none !important;
    }

    #employee-create-page .header-title h1,
    #pms-emp-edit-page .header-title h1,
    .employee-dashboard .title-wrapper h1,
    #archivedEmployeesPage .archive-hero h1 {
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-size: 1.35rem !important;
        font-weight: 700 !important;
        letter-spacing: -0.02em !important;
        margin: 0 !important;
    }

    #employee-create-page .header-title h1 i,
    #pms-emp-edit-page .header-title h1 i {
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
        margin-right: 8px !important;
    }

    #employee-create-page .header-title p,
    #pms-emp-edit-page .header-title p,
    .employee-dashboard .subtitle,
    #archivedEmployeesPage .archive-hero p {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        margin: 4px 0 0 !important;
    }

    .employee-dashboard .icon-circle,
    #archivedEmployeesPage .archive-hero-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        font-size: 1.25rem !important;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22) !important;
    }

    .employee-dashboard .icon-circle i,
    #archivedEmployeesPage .archive-hero-icon i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Unified Header & Action Buttons */
    #employee-create-page .btn-header-enterprise,
    #pms-emp-edit-page .btn-header-enterprise,
    #archivedEmployeesPage .archive-btn-light {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        border-radius: 10px !important;
        height: 38px !important;
        padding: 0 16px !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    #employee-create-page .btn-header-enterprise:hover,
    #pms-emp-edit-page .btn-header-enterprise:hover,
    #archivedEmployeesPage .archive-btn-light:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }

    #employee-create-page .card-premium,
    #pms-emp-edit-page .card-premium {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    #employee-create-page .card-header-premium,
    #pms-emp-edit-page .card-header-premium {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #0f172a !important;
        padding: 18px 24px !important;
    }

    #employee-create-page .card-header-premium h5,
    #pms-emp-edit-page .card-header-premium h5 {
        color: #0f172a !important;
        font-weight: 700 !important;
    }

    #employee-create-page .card-header-premium i,
    #pms-emp-edit-page .card-header-premium i {
        color: #2563eb !important;
    }

    #employee-create-page .form-control-premium,
    #employee-create-page .form-select-premium,
    #pms-emp-edit-page .form-control-premium,
    #pms-emp-edit-page .form-select-premium {
        border-radius: 10px !important;
        border-color: #e2e8f0 !important;
        background-color: #ffffff !important;
    }

    #employee-create-page .form-control-premium:focus,
    #employee-create-page .form-select-premium:focus,
    #pms-emp-edit-page .form-control-premium:focus,
    #pms-emp-edit-page .form-select-premium:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    #employee-create-page .btn-submit-premium,
    #pms-emp-edit-page .btn-submit-premium {
        background: #2563eb !important;
        border-color: #2563eb !important;
        border-radius: 10px !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22) !important;
    }

    /* Dark Mode Support */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .employee-dashboard .breadcrumb-wrapper,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .page-header-enterprise,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .page-header-enterprise,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .employee-dashboard .header-section,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #archivedEmployeesPage .archive-hero,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .card-premium,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .card-premium,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .card-header-premium,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .card-header-premium {
        background: #0f172a !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .header-title h1,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .header-title h1,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .employee-dashboard .title-wrapper h1,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #archivedEmployeesPage .archive-hero h1,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .card-header-premium h5,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .card-header-premium h5,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .employee-dashboard .breadcrumb-item.active {
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .btn-header-enterprise,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .btn-header-enterprise,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #archivedEmployeesPage .archive-btn-light,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .form-control-premium,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .form-select-premium,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .form-control-premium,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .form-select-premium {
        background: #1e293b !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
        color: #e2e8f0 !important;
        -webkit-text-fill-color: #e2e8f0 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #employee-create-page .header-title p,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #pms-emp-edit-page .header-title p,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .employee-dashboard .subtitle,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) #archivedEmployeesPage .archive-hero p {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }
</style>

