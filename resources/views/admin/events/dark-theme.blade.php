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
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .events-section :is(.btn-action-icon, .kpi-icon-box) .bx {
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
</style>
