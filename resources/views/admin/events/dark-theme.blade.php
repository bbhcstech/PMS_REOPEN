<style>
    /* Keep these overrides within Events, including its detached Bootstrap modals. */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section {
        --event-surface: var(--surface, #0f1530);
        --event-surface-soft: var(--surface-secondary, #141b3d);
        --event-border: var(--border-strong, rgba(238, 241, 251, 0.16));
        --event-text: var(--text-primary, #eef1fb);
        --event-muted: var(--text-secondary, #9aa3c7);
        color: var(--event-text);
        color-scheme: dark;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.event-card, .kpi-card, .events-filter-card, .gallery-card, #eventCalendar, .modal-content) {
        background: var(--event-surface) !important;
        color: var(--event-text) !important;
        border-color: var(--event-border) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.event-meta-card, .event-photo-grid-item, .photo-dropzone, .lightbox-img-wrapper, .lightbox-photo-meta, .bg-light, #lightboxCounter) {
        background: var(--event-surface-soft) !important;
        color: var(--event-text) !important;
        border-color: var(--event-border) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .photo-dropzone:is(:hover, .dragover) {
        background: rgba(47, 107, 255, 0.16) !important;
        border-color: #7da4ff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.text-dark, .text-body) {
        color: var(--event-text) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.text-muted, .text-secondary) {
        color: var(--event-muted) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.border-top, .border-bottom, .modal-header, .modal-footer) {
        border-color: var(--event-border) !important;
    }

    /* Preserve the action meaning while making icons readable on dark surfaces. */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .text-primary {
        color: #8aafff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .text-success {
        color: #63dba7 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .text-warning {
        color: #fcd878 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .text-danger {
        color: #ff929d !important;
    }
    /* KPI Stat Icon Boxes in Dark Mode */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.5rem !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-primary {
        background: rgba(47, 107, 255, 0.22) !important;
        border: 1px solid rgba(79, 131, 255, 0.45) !important;
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.25) !important;
        color: #60A5FA !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-primary .bx,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-primary i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-success {
        background: rgba(16, 185, 129, 0.22) !important;
        border: 1px solid rgba(52, 211, 153, 0.45) !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25) !important;
        color: #34D399 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-success .bx,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-success i {
        color: #34D399 !important;
        -webkit-text-fill-color: #34D399 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-warning {
        background: rgba(245, 158, 11, 0.22) !important;
        border: 1px solid rgba(251, 191, 36, 0.45) !important;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25) !important;
        color: #FBBF24 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-warning .bx,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-warning i {
        color: #FBBF24 !important;
        -webkit-text-fill-color: #FBBF24 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-secondary {
        background: rgba(148, 163, 184, 0.2) !important;
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
        box-shadow: 0 4px 14px rgba(148, 163, 184, 0.2) !important;
        color: #CBD5E1 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-secondary .bx,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-secondary i {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-danger {
        background: rgba(239, 68, 68, 0.22) !important;
        border: 1px solid rgba(248, 113, 113, 0.45) !important;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25) !important;
        color: #F87171 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-danger .bx,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .kpi-icon-box.bg-label-danger i {
        color: #F87171 !important;
        -webkit-text-fill-color: #F87171 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .btn-action-icon .bx {
        color: inherit !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section .btn-action-icon:is(:hover, :focus-visible) {
        background: var(--event-surface-soft) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.btn-primary, .btn-success, .btn-danger, .btn-close-premium-white, .photo-action-btn, .gallery-overlay-count, .event-banner-placeholder) .bx {
        color: #fff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.btn-close-premium, .lightbox-nav-btn) {
        background: var(--event-surface-soft) !important;
        color: var(--event-text) !important;
        border-color: var(--event-border) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.btn-close-premium, .lightbox-nav-btn) .bx {
        color: inherit !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.btn-close-premium, .lightbox-nav-btn):hover {
        background: #2f6bff !important;
        color: #fff !important;
    }

    /* FullCalendar uses these variables for month, week, day, and list views. */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section #eventCalendar {
        --fc-border-color: var(--event-border);
        --fc-page-bg-color: var(--event-surface);
        --fc-neutral-bg-color: var(--event-surface-soft);
        --fc-neutral-text-color: var(--event-muted);
        --fc-list-event-hover-bg-color: var(--event-surface-soft);
        --fc-today-bg-color: rgba(47, 107, 255, 0.14);
        --fc-button-bg-color: #2f6bff;
        --fc-button-border-color: #2f6bff;
        --fc-button-text-color: #fff;
        --fc-button-hover-bg-color: #2455d6;
        --fc-button-hover-border-color: #2455d6;
        --fc-button-active-bg-color: #1e4fcc;
        --fc-button-active-border-color: #1e4fcc;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section #eventCalendar :is(.fc-toolbar-title, .fc-col-header-cell-cushion, .fc-daygrid-day-number, .fc-list-day-text, .fc-list-day-side-text) {
        color: var(--event-text) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section #eventCalendar :is(.fc-button, .fc-button .fc-icon, .fc-daygrid-block-event, .fc-daygrid-block-event .fc-event-main) {
        color: #fff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section #eventCalendar .fc-button-primary:not(:disabled):is(:active, .fc-button-active) {
        background: #1e4fcc !important;
        border-color: #8aafff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section #eventCalendar .fc-daygrid-dot-event {
        color: var(--event-text) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.event-description-panel, .event-memories-panel, .event-memories-empty, .event-venue-icon) {
        background: var(--event-surface-soft) !important;
        border-color: var(--event-border) !important;
        color: var(--event-text) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #eventDetailsModal :is(h4, h5, h6, p, span, strong, i, button) {
        -webkit-text-fill-color: currentColor !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #eventDetailsModal .rsvp-stat-going {
        background: rgba(16, 185, 129, 0.14) !important;
        border-color: rgba(52, 211, 153, 0.3) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #eventDetailsModal .rsvp-stat-maybe {
        background: rgba(245, 158, 11, 0.14) !important;
        border-color: rgba(251, 191, 36, 0.3) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #eventDetailsModal .rsvp-stat-not_going {
        background: rgba(239, 68, 68, 0.14) !important;
        border-color: rgba(248, 113, 113, 0.3) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #eventDetailsModal .bg-success-subtle {
        background: rgba(16, 185, 129, 0.18) !important;
    }
    .events-section .event-memories-panel > .d-flex {
        flex-wrap: wrap;
        gap: 12px;
    }
</style>
