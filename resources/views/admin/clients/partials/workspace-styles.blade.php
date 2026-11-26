<style>
/* =========================================================
   PMS Client Section — Light & Dark Workspace Design System
   Scoped under #pms-client-workspace (+ Select2/modal overlays)
   ========================================================= */

#pms-client-workspace {
    padding: 0 0 2.25rem !important;
    color: #0F172A;
}

/* Top Header Bar */
#pms-client-workspace .pms-cl-topbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.35rem;
}

#pms-client-workspace .pms-cl-heading-group {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-width: 0;
}

#pms-client-workspace .pms-cl-title-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #EFF6FF;
    color: #2563EB;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    border: 1px solid rgba(37, 99, 235, 0.14);
}

#pms-client-workspace .pms-cl-title {
    margin: 0;
    font-size: 1.45rem;
    font-weight: 700;
    line-height: 1.2;
    color: #0F172A;
    letter-spacing: -0.015em;
}

#pms-client-workspace .pms-cl-subtitle {
    margin: 0.2rem 0 0;
    font-size: 0.85rem;
    color: #64748B;
}

#pms-client-workspace .pms-cl-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
}

/* Primary & Secondary Action Buttons (10px radius) */
#pms-client-workspace .pms-cl-btn-primary,
#pms-client-workspace .pms-cl-btn-secondary,
#pms-client-workspace .pms-cl-btn-success,
#pms-client-workspace .pms-cl-btn-warning,
#pms-client-workspace .pms-cl-btn-danger,
#pms-client-workspace .pms-cl-btn-outline-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    border-radius: 10px !important;
    font-size: 0.86rem;
    font-weight: 600;
    padding: 0.56rem 1.05rem;
    line-height: 1.25;
    text-decoration: none;
    transition: all 0.18s ease;
    cursor: pointer;
}

#pms-client-workspace .pms-cl-btn-primary {
    background: #2563EB !important;
    border: 1px solid #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.22);
}

#pms-client-workspace .pms-cl-btn-primary:hover,
#pms-client-workspace .pms-cl-btn-primary:focus {
    background: #1D4ED8 !important;
    border-color: #1D4ED8 !important;
    color: #FFFFFF !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);
}

#pms-client-workspace .pms-cl-btn-secondary {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    color: #1E293B !important;
    -webkit-text-fill-color: #1E293B !important;
}

#pms-client-workspace .pms-cl-btn-secondary:hover,
#pms-client-workspace .pms-cl-btn-secondary:focus {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
    -webkit-text-fill-color: #0F172A !important;
}

#pms-client-workspace .pms-cl-btn-outline-primary {
    background: rgba(37, 99, 235, 0.06) !important;
    border: 1px solid rgba(37, 99, 235, 0.35) !important;
    color: #2563EB !important;
    -webkit-text-fill-color: #2563EB !important;
}

#pms-client-workspace .pms-cl-btn-outline-primary:hover {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-client-workspace .pms-cl-btn-success {
    background: #10B981 !important;
    border: 1px solid #10B981 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.22);
}

#pms-client-workspace .pms-cl-btn-success:hover {
    background: #059669 !important;
    border-color: #059669 !important;
    color: #FFFFFF !important;
    transform: translateY(-1px);
}

#pms-client-workspace .pms-cl-btn-warning {
    background: #F59E0B !important;
    border: 1px solid #F59E0B !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-client-workspace .pms-cl-btn-warning:hover {
    background: #D97706 !important;
    border-color: #D97706 !important;
}

#pms-client-workspace .pms-cl-btn-danger {
    background: rgba(239, 68, 68, 0.08) !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    color: #DC2626 !important;
    -webkit-text-fill-color: #DC2626 !important;
}

#pms-client-workspace .pms-cl-btn-danger:hover:not(:disabled) {
    background: #DC2626 !important;
    border-color: #DC2626 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-client-workspace .pms-cl-btn-danger:disabled,
#pms-client-workspace button:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none !important;
}

/* Segmented View Switcher (Table View / Account Verification Pending) */
#pms-client-workspace .pms-cl-switcher {
    display: inline-flex;
    align-items: center;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 3px;
    gap: 3px;
}

#pms-client-workspace .pms-cl-switch-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.44rem 0.85rem;
    border-radius: 8px !important;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    background: transparent;
    border: 0;
    text-decoration: none;
    transition: all 0.16s ease;
    white-space: nowrap;
}

#pms-client-workspace .pms-cl-switch-btn:hover {
    background: #F1F5F9;
    color: #0F172A !important;
    -webkit-text-fill-color: #0F172A !important;
}

#pms-client-workspace .pms-cl-switch-btn.is-active {
    background: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
}

/* Cards */
#pms-client-workspace .pms-cl-card,
#pms-client-workspace .card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 16px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    color: #0F172A;
}

#pms-client-workspace .card-header {
    background: transparent !important;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top-left-radius: 16px !important;
    border-top-right-radius: 16px !important;
}

#pms-client-workspace .card-footer {
    background: #F8FAFC !important;
    border-top: 1px solid #E2E8F0 !important;
    border-bottom-left-radius: 16px !important;
    border-bottom-right-radius: 16px !important;
}

/* Uppercase Micro-caps Labels */
#pms-client-workspace .pms-cl-label,
#pms-client-workspace .form-label,
#pms-client-workspace form label:not(.form-check-label) {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    margin-bottom: 0.4rem;
}

#pms-client-workspace .form-check-label {
    font-size: 0.88rem;
    font-weight: 500;
    color: #1E293B;
}

/* Form Controls */
#pms-client-workspace .form-control,
#pms-client-workspace .form-select,
#pms-client-workspace .form-control-custom {
    border-radius: 10px !important;
    border: 1px solid #CBD5E1;
    background-color: #FFFFFF;
    color: #0F172A;
    font-size: 0.88rem;
    min-height: 40px;
    transition: border-color 0.16s ease, box-shadow 0.16s ease;
}

#pms-client-workspace textarea.form-control,
#pms-client-workspace textarea.form-control-custom {
    min-height: 82px;
}

#pms-client-workspace .form-control:focus,
#pms-client-workspace .form-select:focus,
#pms-client-workspace .form-control-custom:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

#pms-client-workspace .input-group > .form-select:first-child,
#pms-client-workspace .input-group > .form-control:first-child {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

#pms-client-workspace .input-group > .form-control:last-child,
#pms-client-workspace .input-group > .btn:last-child {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

#pms-client-workspace .input-group > .btn {
    border-radius: 10px !important;
}

#pms-client-workspace .input-group > .btn + .btn {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

/* Table System */
#pms-client-workspace .pms-cl-table-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

#pms-client-workspace .pms-cl-table {
    margin-bottom: 0;
    border-color: #E2E8F0 !important;
    color: #1E293B;
}

#pms-client-workspace .pms-cl-table thead th {
    background: #F8FAFC !important;
    color: #475569 !important;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top: 0 !important;
    padding: 0.85rem 0.9rem;
    white-space: nowrap;
}

#pms-client-workspace .pms-cl-table tbody td {
    padding: 0.85rem 0.9rem;
    vertical-align: middle;
    border-color: #E2E8F0 !important;
    font-size: 0.875rem;
}

#pms-client-workspace .pms-cl-table tbody tr:hover {
    background-color: #F8FAFC !important;
}

#pms-client-workspace .pms-cl-client-link {
    font-weight: 600;
    color: #0F172A;
    text-decoration: none;
}

#pms-client-workspace .pms-cl-client-link:hover {
    color: #2563EB;
}

/* Avatar Chip in Table */
#pms-client-workspace .pms-cl-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    object-fit: cover;
    background: #EFF6FF;
    color: #2563EB;
    font-weight: 700;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(37, 99, 235, 0.16);
}

/* Status Pills */
#pms-client-workspace .pms-cl-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    border-radius: 8px;
    font-size: 0.76rem;
    font-weight: 600;
}

#pms-client-workspace .pms-cl-status.is-active {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}

#pms-client-workspace .pms-cl-status.is-inactive {
    background: rgba(100, 116, 139, 0.14);
    color: #475569;
}

#pms-client-workspace .pms-cl-status.is-pending {
    background: rgba(245, 158, 11, 0.14);
    color: #D97706;
}

/* Dropdown Menus & Action Triggers */
#pms-client-workspace .pms-cl-action-trigger {
    width: 34px;
    height: 34px;
    padding: 0;
    border-radius: 9px !important;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#pms-client-workspace .pms-cl-action-trigger:hover {
    background: #EFF6FF;
    border-color: #BFDBFE;
    color: #2563EB;
}

#pms-client-workspace .dropdown-menu {
    z-index: 1065 !important;
    border-radius: 12px !important;
    border: 1px solid #E2E8F0 !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12) !important;
    padding: 0.4rem !important;
    background: #FFFFFF !important;
}

#pms-client-workspace .dropdown-item {
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    padding: 0.48rem 0.75rem;
    color: #1E293B;
}

#pms-client-workspace .dropdown-item:hover,
#pms-client-workspace .dropdown-item:focus {
    background: #EFF6FF;
    color: #2563EB;
}

#pms-client-workspace .dropdown-item.text-danger:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #DC2626 !important;
}

/* DataTables Wrappers & Empty State */
#pms-client-workspace .dt-buttons {
    display: none !important;
}

#pms-client-workspace .dataTables_wrapper .dataTables_length label,
#pms-client-workspace .dataTables_wrapper .dataTables_filter label,
#pms-client-workspace .dataTables_wrapper .dataTables_info {
    font-size: 0.84rem;
    color: #64748B;
    font-weight: 500;
}

#pms-client-workspace .dataTables_wrapper .dataTables_length select,
#pms-client-workspace .dataTables_wrapper .dataTables_filter input {
    border-radius: 9px !important;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #0F172A;
    padding: 0.32rem 0.65rem;
    font-size: 0.84rem;
}

#pms-client-workspace .dataTables_wrapper .dataTables_paginate .pagination {
    gap: 0.25rem;
    margin-top: 0.5rem;
}

#pms-client-workspace .dataTables_wrapper .page-link {
    border-radius: 8px !important;
    border: 1px solid #E2E8F0;
    color: #334155;
    background: #FFFFFF;
    font-size: 0.82rem;
    font-weight: 600;
}

#pms-client-workspace .dataTables_wrapper .page-item.active .page-link {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
}

#pms-client-workspace td.dataTables_empty,
#pms-client-workspace .pms-cl-empty {
    text-align: center !important;
    padding: 2.75rem 1.25rem !important;
    color: #64748B;
}

#pms-client-workspace .pms-cl-empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: #EFF6FF;
    color: #2563EB;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 0.85rem;
}

/* Client Show Hero + Segmented Tabs */
#pms-client-workspace .client-hero-card {
    background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%) !important;
    border: 1px solid #E2E8F0 !important;
}

#pms-client-workspace .pms-cl-info-tile {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 0.8rem 0.95rem;
}

#pms-client-workspace .pms-cl-info-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748B;
    margin-bottom: 0.25rem;
}

#pms-client-workspace .pms-cl-info-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #0F172A;
    word-break: break-word;
}

#pms-client-workspace .segmented-tabs-wrapper {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    padding: 4px !important;
    display: inline-flex !important;
    gap: 4px;
}

#pms-client-workspace .segmented-tabs-wrapper .nav-link {
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    border-radius: 9px !important;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.52rem 1.1rem;
    border: 0;
}

#pms-client-workspace .segmented-tabs-wrapper .nav-link.active {
    background: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.24) !important;
}

/* Wizard Stepper Alignment with #2563EB Brand */
#pms-client-workspace .step-header-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #EFF6FF;
    color: #2563EB;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#pms-client-workspace .stepper-progress-bar {
    background: linear-gradient(90deg, #10B981, #2563EB) !important;
}

#pms-client-workspace .step-item.active .step-circle {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
    box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.2) !important;
}

#pms-client-workspace .step-item.active .step-title {
    color: #2563EB !important;
}

#pms-client-workspace .card-header h5,
#pms-client-workspace .pms-cl-card-title {
    color: #0F172A !important;
}

/* =========================================================
   DARK MODE OVERRIDES
   ========================================================= */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace {
    color: #E2E8F0;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-title-icon,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .step-header-icon,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-empty-icon {
    background: rgba(37, 99, 235, 0.16) !important;
    border-color: rgba(96, 165, 250, 0.25) !important;
    color: #60A5FA !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-title,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .card-header h5,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-card-title,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-info-value,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-client-link,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace h4,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace h5,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace h6 {
    color: #F1F5F9 !important;
    -webkit-text-fill-color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-subtitle,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .text-muted,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-info-label {
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .text-dark,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .text-secondary,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-check-label {
    color: #E2E8F0 !important;
    -webkit-text-fill-color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-label,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-label,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace form label:not(.form-check-label) {
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .modal-content {
    background: #0F172A !important;
    border: 1px solid rgba(148, 163, 184, 0.2) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35) !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .client-hero-card {
    background: linear-gradient(135deg, #0F172A 0%, #111827 100%) !important;
    border-color: rgba(148, 163, 184, 0.22) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .card-header,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .modal-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .card-footer,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .modal-footer {
    background: rgba(15, 23, 42, 0.85) !important;
    border-top: 1px solid rgba(148, 163, 184, 0.18) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .bg-white,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .bg-light,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-info-tile {
    background-color: #1E293B !important;
    border-color: rgba(148, 163, 184, 0.2) !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .border,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .border-bottom,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .border-top {
    border-color: rgba(148, 163, 184, 0.18) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-btn-secondary,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .btn-outline-secondary {
    background: #1E293B !important;
    border-color: #334155 !important;
    color: #E2E8F0 !important;
    -webkit-text-fill-color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-btn-secondary:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .btn-outline-secondary:hover {
    background: #283548 !important;
    border-color: #475569 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-switcher,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .segmented-tabs-wrapper {
    background: #0F172A !important;
    border-color: #334155 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-switch-btn,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .segmented-tabs-wrapper .nav-link {
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-switch-btn:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .segmented-tabs-wrapper .nav-link:hover {
    background: #1E293B !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-switch-btn.is-active,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .segmented-tabs-wrapper .nav-link.active {
    background: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-control,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-select,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-control-custom,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .dataTables_length select,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .dataTables_filter input {
    background-color: #1E293B !important;
    border-color: #334155 !important;
    color: #F1F5F9 !important;
    -webkit-text-fill-color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-control::placeholder,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-control-custom::placeholder {
    color: #64748B !important;
    -webkit-text-fill-color: #64748B !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .form-select option {
    background-color: #0F172A;
    color: #F1F5F9;
}

/* Dark Mode Tables */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .table,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-table {
    --bs-table-bg: #0F172A;
    --bs-table-color: #E2E8F0;
    --bs-table-striped-bg: rgba(30, 41, 59, 0.55);
    --bs-table-striped-color: #E2E8F0;
    --bs-table-hover-bg: rgba(37, 99, 235, 0.12);
    --bs-table-hover-color: #F8FAFC;
    --bs-table-border-color: rgba(148, 163, 184, 0.16);
    border-color: rgba(148, 163, 184, 0.16) !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .table thead th,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .table-light th {
    background: #1E293B !important;
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
    border-color: rgba(148, 163, 184, 0.2) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .table tbody td {
    background-color: transparent !important;
    color: #E2E8F0 !important;
    border-color: rgba(148, 163, 184, 0.16) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .table-striped > tbody > tr:nth-of-type(odd) > * {
    background-color: rgba(30, 41, 59, 0.45) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .table-hover > tbody > tr:hover > * {
    background-color: rgba(37, 99, 235, 0.12) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-status.is-active {
    background: rgba(16, 185, 129, 0.16);
    color: #34D399 !important;
    -webkit-text-fill-color: #34D399 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-status.is-inactive {
    background: rgba(148, 163, 184, 0.16);
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-status.is-pending {
    background: rgba(245, 158, 11, 0.18);
    color: #FBBF24 !important;
    -webkit-text-fill-color: #FBBF24 !important;
}

/* Dark Mode Dropdowns & Action Triggers */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-action-trigger {
    background: #1E293B !important;
    border-color: #334155 !important;
    color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .pms-cl-action-trigger:hover {
    background: rgba(37, 99, 235, 0.18) !important;
    border-color: rgba(96, 165, 250, 0.4) !important;
    color: #60A5FA !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dropdown-menu {
    background: #1E293B !important;
    border-color: #334155 !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.48) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dropdown-item {
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dropdown-item:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dropdown-item:focus {
    background: rgba(37, 99, 235, 0.2) !important;
    color: #93C5FD !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dropdown-divider {
    border-color: rgba(148, 163, 184, 0.2) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .dataTables_length label,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .dataTables_filter label,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .dataTables_info {
    color: #94A3B8 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .page-link {
    background: #1E293B !important;
    border-color: #334155 !important;
    color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .dataTables_wrapper .page-item.disabled .page-link {
    background: #0F172A !important;
    color: #475569 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Dark Mode Select2 / Daterangepicker Global Overlays when inside Client pages */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .select2-container--default .select2-selection--single,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .select2-container--default .select2-selection--multiple {
    background-color: #1E293B !important;
    border-color: #334155 !important;
    color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #F1F5F9 !important;
    -webkit-text-fill-color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .select2-dropdown,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker {
    background-color: #0F172A !important;
    border-color: #334155 !important;
    color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker .calendar-table {
    background-color: #0F172A !important;
    border-color: #334155 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker td.off,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker td.off.in-range,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker td.off.start-date,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker td.off.end-date {
    background-color: #0B1120 !important;
    color: #475569 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker td.available:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker th.available:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker .ranges li:hover {
    background-color: #1E293B !important;
    color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker .ranges li.active,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) .daterangepicker td.active {
    background-color: #2563EB !important;
    color: #FFFFFF !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .stepper-progress {
    background: #334155 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .step-item.active .step-circle {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
    box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.25) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-client-workspace .step-item.active .step-title {
    color: #60A5FA !important;
}

@media (max-width: 767.98px) {
    #pms-client-workspace .pms-cl-topbar {
        align-items: flex-start;
    }
    #pms-client-workspace .pms-cl-actions {
        width: 100%;
    }
    #pms-client-workspace .pms-cl-switcher {
        width: 100%;
        justify-content: stretch;
    }
    #pms-client-workspace .pms-cl-switch-btn {
        flex: 1 1 0;
        justify-content: center;
    }
}
</style>

