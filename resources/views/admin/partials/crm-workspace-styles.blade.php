<style>
/* =========================================================
   PMS CRM (Lead Contacts & Deals) — Light & Dark System
   ========================================================= */

#pms-crm-workspace {
    padding: 0 0 2.25rem !important;
    color: #0F172A;
}

/* Top Header */
#pms-crm-workspace .pms-crm-topbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.35rem;
}

#pms-crm-workspace .pms-crm-heading-group {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-width: 0;
}

#pms-crm-workspace .pms-crm-title-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #EFF6FF;
    color: #2563EB;
    border: 1px solid rgba(37, 99, 235, 0.16);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

#pms-crm-workspace .pms-crm-title {
    margin: 0;
    font-size: 1.45rem;
    font-weight: 700;
    line-height: 1.2;
    color: #0F172A;
    letter-spacing: -0.015em;
}

#pms-crm-workspace .pms-crm-subtitle {
    margin: 0.2rem 0 0;
    font-size: 0.85rem;
    color: #64748B;
}

#pms-crm-workspace .pms-crm-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.6rem;
}

/* Unified Workspace Buttons (10px radius) */
#pms-crm-workspace .pms-crm-btn-primary,
#pms-crm-workspace .pms-crm-btn-secondary,
#pms-crm-workspace .pms-crm-btn-outline-primary,
#pms-crm-workspace .pms-crm-btn-success,
#pms-crm-workspace .pms-crm-btn-warning,
#pms-crm-workspace .pms-crm-btn-info,
#pms-crm-workspace .pms-crm-btn-danger,
#pms-crm-workspace .btn-submit-emerald,
#pms-crm-workspace .btn-outline-emerald,
#pms-crm-workspace .btn-cancel-modern,
#pms-crm-workspace .btn-back-pill,
#pms-crm-workspace .btn-action-primary,
#pms-crm-workspace .btn-action-warning,
#pms-crm-workspace .btn-action-success,
#pms-crm-workspace .btn-action-info,
#pms-crm-workspace .btn-action-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    border-radius: 10px !important;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.56rem 1.05rem;
    line-height: 1.25;
    text-decoration: none;
    transition: all 0.18s ease;
    cursor: pointer;
}

#pms-crm-workspace .pms-crm-btn-primary,
#pms-crm-workspace .btn-submit-emerald,
#pms-crm-workspace .btn-action-primary {
    background: #2563EB !important;
    border: 1px solid #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.22);
}

#pms-crm-workspace .pms-crm-btn-primary:hover,
#pms-crm-workspace .btn-submit-emerald:hover,
#pms-crm-workspace .btn-action-primary:hover {
    background: #1D4ED8 !important;
    border-color: #1D4ED8 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);
}

#pms-crm-workspace .pms-crm-btn-secondary,
#pms-crm-workspace .btn-cancel-modern,
#pms-crm-workspace .btn-back-pill,
#pms-crm-workspace .btn-action-outline {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    color: #1E293B !important;
    -webkit-text-fill-color: #1E293B !important;
}

#pms-crm-workspace .pms-crm-btn-secondary:hover,
#pms-crm-workspace .btn-cancel-modern:hover,
#pms-crm-workspace .btn-back-pill:hover,
#pms-crm-workspace .btn-action-outline:hover {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #0F172A !important;
    -webkit-text-fill-color: #0F172A !important;
}

#pms-crm-workspace .pms-crm-btn-outline-primary,
#pms-crm-workspace .btn-outline-emerald {
    background: rgba(37, 99, 235, 0.06) !important;
    border: 1px solid rgba(37, 99, 235, 0.35) !important;
    color: #2563EB !important;
    -webkit-text-fill-color: #2563EB !important;
}

#pms-crm-workspace .pms-crm-btn-outline-primary:hover,
#pms-crm-workspace .btn-outline-emerald:hover {
    background: #2563EB !important;
    border-color: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-crm-workspace .pms-crm-btn-success,
#pms-crm-workspace .btn-action-success {
    background: #10B981 !important;
    border: 1px solid #10B981 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-crm-workspace .pms-crm-btn-success:hover,
#pms-crm-workspace .btn-action-success:hover {
    background: #059669 !important;
    border-color: #059669 !important;
}

#pms-crm-workspace .pms-crm-btn-warning,
#pms-crm-workspace .btn-action-warning {
    background: #F59E0B !important;
    border: 1px solid #F59E0B !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-crm-workspace .pms-crm-btn-warning:hover,
#pms-crm-workspace .btn-action-warning:hover {
    background: #D97706 !important;
    border-color: #D97706 !important;
}

#pms-crm-workspace .pms-crm-btn-info,
#pms-crm-workspace .btn-action-info {
    background: #0EA5E9 !important;
    border: 1px solid #0EA5E9 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

#pms-crm-workspace .pms-crm-btn-info:hover,
#pms-crm-workspace .btn-action-info:hover {
    background: #0284C7 !important;
    border-color: #0284C7 !important;
}

#pms-crm-workspace .pms-crm-btn-danger {
    background: rgba(239, 68, 68, 0.08) !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    color: #DC2626 !important;
    -webkit-text-fill-color: #DC2626 !important;
}

#pms-crm-workspace .pms-crm-btn-danger:hover {
    background: #DC2626 !important;
    border-color: #DC2626 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

/* Segmented View Switcher */
#pms-crm-workspace .pms-crm-switcher {
    display: inline-flex;
    align-items: center;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 3px;
    gap: 3px;
}

#pms-crm-workspace .pms-crm-switch-btn {
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
    text-decoration: none;
    transition: all 0.16s ease;
    white-space: nowrap;
}

#pms-crm-workspace .pms-crm-switch-btn:hover {
    background: #F1F5F9;
    color: #0F172A !important;
    -webkit-text-fill-color: #0F172A !important;
}

#pms-crm-workspace .pms-crm-switch-btn.is-active {
    background: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
}

/* Cards & Surfaces */
#pms-crm-workspace .crm-kpi-card,
#pms-crm-workspace .top-bar,
#pms-crm-workspace .premium-table-card,
#pms-crm-workspace .form-card-modern,
#pms-crm-workspace .lead-header-card,
#pms-crm-workspace .card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 16px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
}

#pms-crm-workspace .crm-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.07) !important;
}

#pms-crm-workspace .crm-kpi-icon.text-success,
#pms-crm-workspace .crm-kpi-icon.bg-success {
    background-color: rgba(16, 185, 129, 0.14) !important;
    color: #10B981 !important;
}

#pms-crm-workspace .card-header,
#pms-crm-workspace .form-card-header-modern {
    background: transparent !important;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top-left-radius: 16px !important;
    border-top-right-radius: 16px !important;
}

#pms-crm-workspace .form-card-header-modern .pms-crm-sec-icon {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: #EFF6FF;
    color: #2563EB;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

#pms-crm-workspace .pms-crm-entity-link {
    color: #0F172A;
    font-weight: 700;
    text-decoration: none;
    font-size: 0.9rem;
}

#pms-crm-workspace .pms-crm-entity-link:hover {
    color: #2563EB;
}

#pms-crm-workspace .pms-crm-subtext {
    color: #475569;
    font-size: 0.8rem;
}

#pms-crm-workspace .pms-crm-cell-strong {
    color: #1E293B;
    font-weight: 600;
    font-size: 0.875rem;
}

/* Labels & Inputs */
#pms-crm-workspace .form-label-modern,
#pms-crm-workspace .pms-crm-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    margin-bottom: 0.4rem;
    display: block;
}

#pms-crm-workspace .form-control,
#pms-crm-workspace .form-select,
#pms-crm-workspace .form-control-modern,
#pms-crm-workspace .form-select-modern {
    border-radius: 10px !important;
    border: 1px solid #CBD5E1;
    background-color: #FFFFFF;
    color: #0F172A;
    font-size: 0.875rem;
}

#pms-crm-workspace .form-control:focus,
#pms-crm-workspace .form-select:focus,
#pms-crm-workspace .form-control-modern:focus,
#pms-crm-workspace .form-select-modern:focus {
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

#pms-crm-workspace .input-group > .form-select:first-child {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

#pms-crm-workspace .input-group > .form-control:last-child {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

/* Action Circle & Dropdowns */
#pms-crm-workspace .btn-action-circle {
    width: 34px;
    height: 34px;
    border-radius: 9px !important;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#pms-crm-workspace .btn-action-circle:hover {
    background: #EFF6FF;
    border-color: #BFDBFE;
    color: #2563EB;
}

#pms-crm-workspace .dropdown-menu,
#pms-crm-workspace .dropdown-menu-premium {
    z-index: 1065 !important;
    border-radius: 12px !important;
    border: 1px solid #E2E8F0 !important;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12) !important;
    padding: 0.4rem !important;
    background: #FFFFFF !important;
}

#pms-crm-workspace .dropdown-item {
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 500;
    padding: 0.48rem 0.78rem;
    color: #1E293B;
}

#pms-crm-workspace .dropdown-item:hover {
    background: #EFF6FF;
    color: #2563EB;
}

#pms-crm-workspace .dropdown-item.text-danger:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #DC2626 !important;
}

/* Empty State Icon Box */
#pms-crm-workspace .pms-crm-empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: #EFF6FF;
    color: #2563EB;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    margin: 0 auto 0.85rem;
}

/* Unified Nav Tabs (Deal Show & Lead Show) */
#pms-crm-workspace .pms-crm-tabs,
#pms-crm-workspace .lead-nav-tabs {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    padding: 4px !important;
    display: inline-flex !important;
    flex-wrap: wrap;
    gap: 4px;
}

#pms-crm-workspace .pms-crm-tabs .nav-link,
#pms-crm-workspace .lead-nav-tabs .nav-link {
    border: 0 !important;
    border-radius: 9px !important;
    padding: 0.5rem 1rem !important;
    font-size: 0.86rem !important;
    font-weight: 600 !important;
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    background: transparent !important;
}

#pms-crm-workspace .pms-crm-tabs .nav-link:hover:not(.active),
#pms-crm-workspace .lead-nav-tabs .nav-link:hover:not(.active) {
    background: #F1F5F9 !important;
    color: #0F172A !important;
    -webkit-text-fill-color: #0F172A !important;
}

#pms-crm-workspace .pms-crm-tabs .nav-link.active,
#pms-crm-workspace .lead-nav-tabs .nav-link.active,
#pms-crm-workspace .lead-nav-tabs button.nav-link.active {
    background: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25) !important;
}

#pms-crm-workspace .pms-crm-tabs .nav-link.active *,
#pms-crm-workspace .lead-nav-tabs .nav-link.active * {
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

/* Slide-over Filter Footer */
#pms-crm-workspace .filter-footer {
    background: #F8FAFC;
    border-top: 1px solid #E2E8F0;
}

/* =========================================================
   DARK MODE OVERRIDES (All Theme Attribute Variants)
   ========================================================= */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace {
    color: #E2E8F0;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-title-icon,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-card-header-modern .pms-crm-sec-icon,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-empty-icon {
    background: rgba(37, 99, 235, 0.16) !important;
    border-color: rgba(96, 165, 250, 0.26) !important;
    color: #60A5FA !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-title,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .page-title-text,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .crm-kpi-value,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-entity-link,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-cell-strong,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .lead-header-title,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .info-value,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace h3:not(.text-success):not(.text-primary):not(.text-info):not(.text-warning):not(.text-danger),
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace h4,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace h5,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace h6 {
    color: #F1F5F9 !important;
    -webkit-text-fill-color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-subtitle,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .crm-kpi-title,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-subtext,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .info-label,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .text-muted {
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .text-dark,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .text-secondary,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-check-label {
    color: #E2E8F0 !important;
    -webkit-text-fill-color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-label-modern,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-label,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-label {
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .crm-kpi-card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .top-bar,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .premium-table-card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-card-modern,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .lead-header-card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .filter-sidebar,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .modal-content {
    background: #0F172A !important;
    border: 1px solid rgba(148, 163, 184, 0.2) !important;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35) !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .card-header,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-card-header-modern,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .filter-header,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .modal-header:not(.bg-danger) {
    background: #0F172A !important;
    border-bottom: 1px solid rgba(148, 163, 184, 0.18) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .filter-footer,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .modal-footer,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .premium-table-footer {
    background: #1E293B !important;
    border-top: 1px solid rgba(148, 163, 184, 0.18) !important;
    color: #94A3B8 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .bg-white,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .bg-light,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .info-field-card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .info-icon-box {
    background-color: #1E293B !important;
    border-color: rgba(148, 163, 184, 0.2) !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-btn-secondary,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-cancel-modern,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-back-pill,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-action-outline,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-outline-secondary {
    background: #1E293B !important;
    border-color: #334155 !important;
    color: #E2E8F0 !important;
    -webkit-text-fill-color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-btn-secondary:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-cancel-modern:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-back-pill:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-action-outline:hover {
    background: #283548 !important;
    border-color: #475569 !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-switcher,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-tabs,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .lead-nav-tabs {
    background: #0F172A !important;
    border-color: #334155 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-switch-btn,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-tabs .nav-link:not(.active),
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .lead-nav-tabs .nav-link:not(.active) {
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-switch-btn:hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-tabs .nav-link:not(.active):hover,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .lead-nav-tabs .nav-link:not(.active):hover {
    background: #1E293B !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-switch-btn.is-active,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .pms-crm-tabs .nav-link.active,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .lead-nav-tabs .nav-link.active {
    background: #2563EB !important;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
}

/* Dark Inputs & Selects across index topbar, filter sidebar, forms & modals */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-control,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-select,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-control-modern,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-select-modern {
    background-color: #1E293B !important;
    border-color: #334155 !important;
    color: #F1F5F9 !important;
    -webkit-text-fill-color: #F1F5F9 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-select option,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .form-select-modern option {
    background-color: #0F172A;
    color: #F1F5F9;
}

/* Dark Mode Tables */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .table,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .premium-table {
    --bs-table-bg: #0F172A;
    --bs-table-color: #E2E8F0;
    --bs-table-hover-bg: rgba(37, 99, 235, 0.1);
    --bs-table-hover-color: #F8FAFC;
    border-color: rgba(148, 163, 184, 0.16) !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .premium-table thead tr,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .table thead th,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .table thead.bg-light th {
    background-color: #1E293B !important;
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
    border-color: rgba(148, 163, 184, 0.18) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .table tbody td,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .premium-table td {
    background-color: transparent !important;
    color: #E2E8F0 !important;
    border-color: rgba(148, 163, 184, 0.14) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .table tbody tr:hover > * {
    background-color: rgba(37, 99, 235, 0.1) !important;
}

/* Dark Mode Dropdowns & Action Trigger */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-action-circle {
    background: #1E293B !important;
    border-color: #334155 !important;
    color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-action-circle:hover {
    background: rgba(37, 99, 235, 0.2) !important;
    border-color: rgba(96, 165, 250, 0.4) !important;
    color: #60A5FA !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .dropdown-menu,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .dropdown-menu-premium {
    background: #1E293B !important;
    border-color: #334155 !important;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.48) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .dropdown-item {
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .dropdown-item:hover {
    background: rgba(37, 99, 235, 0.2) !important;
    color: #93C5FD !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .dropdown-divider {
    border-color: rgba(148, 163, 184, 0.2) !important;
}

/* Dark Mode Kanban Board */
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .kanban-col {
    background: #0F172A !important;
    border-color: rgba(148, 163, 184, 0.2) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .kanban-col-header {
    background: #1E293B !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .kanban-card,
:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .deal-card {
    background: #1E293B !important;
    border-color: #334155 !important;
    color: #E2E8F0 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .kanban-card .border-top {
    border-color: rgba(148, 163, 184, 0.18) !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .badge-pill-source {
    background-color: #1E293B !important;
    border-color: #334155 !important;
    color: #CBD5E1 !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .alert-info {
    background-color: rgba(14, 165, 233, 0.14) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
    color: #E0F2FE !important;
}

:is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark-mode, [data-bs-theme="dark"]) #pms-crm-workspace .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

@media (max-width: 767.98px) {
    #pms-crm-workspace .pms-crm-topbar {
        align-items: flex-start;
    }
    #pms-crm-workspace .pms-crm-actions {
        width: 100%;
    }
    #pms-crm-workspace .pms-crm-switcher {
        width: 100%;
    }
    #pms-crm-workspace .pms-crm-switch-btn {
        flex: 1 1 0;
        justify-content: center;
    }
}
</style>

