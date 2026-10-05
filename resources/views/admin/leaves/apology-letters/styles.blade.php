<style>
    .leave-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #F8FAFC, #f7fbff); color: #0F1530; }
    .leave-breadcrumb, .leave-hero, .filter-panel, .table-card { border: 1px solid rgba(47,107,255,.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); }
    .leave-breadcrumb { display: inline-flex; gap: 8px; align-items: center; padding: 12px 18px; border-radius: 14px; color: #2F6BFF; font-weight: 900; margin-bottom: 22px; }
    .leave-hero { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 28px; border-radius: 24px; margin-bottom: 20px; }
    .leave-hero-main { display: flex; gap: 16px; align-items: center; }
    .leave-hero-icon { width: 58px; height: 58px; border-radius: 18px; display: grid; place-items: center; background: #E0E7FF; color: #2F6BFF; font-size: 24px; }
    .leave-hero h1 { margin: 0 0 6px; font-size: 34px; font-weight: 900; }
    .leave-hero p, .table-head p { margin: 0; color: #667085; font-weight: 650; }
    .leave-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .archive-count-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #ef4444; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 900; }
    .filter-panel, .table-card { border-radius: 22px; padding: 22px; margin-bottom: 20px; }
    .filter-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 16px; align-items: end; }
    label { color: #667085; text-transform: uppercase; font-size: .76rem; font-weight: 900; margin-bottom: 6px; }
    .form-control { min-height: 44px; border-radius: 12px; border: 1px solid #E2E8F0; font-weight: 650; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; min-height: 40px; font-weight: 900; border: 0; white-space: nowrap !important; flex-shrink: 0 !important; }
    .btn-primary { background: linear-gradient(145deg, #4F83FF, #2F6BFF); color: #fff; }
    .btn-light, .btn-secondary { background: #F8FAFC; color: #2F6BFF; border: 1px solid rgba(47,107,255,.18); }
    .table-head { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 16px; align-items: center; }
    .table-head h2 { margin: 0 0 4px; font-weight: 900; }
    .checkbox-col { width: 44px; text-align: center; }
    .leave-table th { color: #667085; font-size: .78rem; text-transform: uppercase; }
    .leave-table td { vertical-align: middle; font-weight: 650; }
    .leave-table td.text-end { white-space: nowrap !important; text-align: right; width: 1%; }
    .leave-table .action-buttons,
    .action-buttons {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        white-space: nowrap !important;
        flex-wrap: nowrap !important;
    }
    .leave-table .action-buttons form,
    .action-buttons form {
        margin: 0 !important;
        display: inline-flex !important;
    }
    .leave-table .btn-sm {
        min-height: 36px;
        padding: 6px 14px;
        font-size: .82rem;
        border-radius: 999px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 1.2;
    }
    .status-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: .75rem; font-weight: 900; color: #fff; }
    .status-badge.pending { background: #f59e0b; }
    .status-badge.approved { background: #10b981; }
    .status-badge.archived { background: #64748b; }
    .empty-state { color: #667085; }
    .empty-state i { font-size: 34px; color: #60A5FA; margin-bottom: 10px; }
    .pagination-wrap { margin-top: 16px; }
    @media (max-width: 992px) { .leave-page { padding: 18px; } .leave-hero { flex-direction: column; align-items: flex-start; } .filter-grid { grid-template-columns: 1fr; } }
    html[data-pms-theme="dark"] .leave-page {
        background: #070B1A !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .leave-breadcrumb,
    html[data-pms-theme="dark"] .leave-hero,
    html[data-pms-theme="dark"] .filter-panel,
    html[data-pms-theme="dark"] .table-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
    }
    html[data-pms-theme="dark"] .leave-hero h1,
    html[data-pms-theme="dark"] .table-head h2 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-hero p,
    html[data-pms-theme="dark"] .table-head p,
    html[data-pms-theme="dark"] label,
    html[data-pms-theme="dark"] .leave-table th,
    html[data-pms-theme="dark"] .leave-table small {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .form-control {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-table td {
        color: #CBD5E1 !important;
        border-color: rgba(238, 241, 251, 0.06) !important;
    }
    html[data-pms-theme="dark"] .btn-light,
    html[data-pms-theme="dark"] .btn-secondary {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .btn-light:hover,
    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1C2652 !important;
        border-color: rgba(96, 165, 250, 0.4) !important;
        color: #FFFFFF !important;
    }
    html[data-pms-theme="dark"] .leave-hero-icon {
        background: rgba(47, 107, 255, 0.2) !important;
        color: #60A5FA !important;
    }
</style>
