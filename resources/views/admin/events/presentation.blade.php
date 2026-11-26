<style>
    :is(#events-page, .events-section.modal) {
        --ev-surface: #fff;
        --ev-soft: #f7f6fc;
        --ev-border: #e5e1ee;
        --ev-control: #d7d1e5;
        --ev-ink: #211934;
        --ev-muted: #625970;
        --ev-accent: #5840ad;
        --ev-accent-soft: #eee9fb;
        --ev-primary: #5940b0;
        --ev-primary-hover: #493395;
        --ev-green: #166349;
        --ev-green-soft: #e7f5ed;
        --ev-amber: #85530c;
        --ev-amber-soft: #fff3db;
        --ev-red: #a12c46;
        --ev-red-soft: #fcecf1;
        --ev-neutral: #4e5879;
        --ev-neutral-soft: #edf0f8;
        --ev-shadow: 0 2px 6px rgb(38 26 63 / 4%), 0 12px 30px rgb(38 26 63 / 6%);
        --ev-gap: clamp(14px, 1.6vw, 22px);
    }

    :is([data-pms-theme="dark"], [data-theme="dark"], [data-bs-theme="dark"], .dark, .dark-mode) :is(#events-page, .events-section.modal) {
        --ev-surface: #171b2f;
        --ev-soft: #20243b;
        --ev-border: #323651;
        --ev-control: #464968;
        --ev-ink: #f4efff;
        --ev-muted: #b9b4cd;
        --ev-accent: #cbbaff;
        --ev-accent-soft: #332b57;
        --ev-primary: #6d50c2;
        --ev-primary-hover: #5e42b0;
        --ev-green: #a0e4c6;
        --ev-green-soft: #1d3837;
        --ev-amber: #f9d48f;
        --ev-amber-soft: #3e332d;
        --ev-red: #ffadbf;
        --ev-red-soft: #472a3d;
        --ev-neutral: #c7cfe8;
        --ev-neutral-soft: #2b334c;
        --ev-shadow: 0 10px 28px rgb(0 0 0 / 17%);
    }

    /* Use the bundled glyphs for Events, including when the icon font CDN is unavailable. */
    :is(#events-page, .events-section.modal) .bx {
        width: 1em;
        height: 1em;
        background: currentColor !important;
        -webkit-mask-image: var(--svg) !important;
        mask-image: var(--svg) !important;
        mask-position: center;
        mask-repeat: no-repeat;
        mask-size: contain;
    }

    :is(#events-page, .events-section.modal) .bx::before { content: none !important; }

    #events-page .events-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: var(--ev-gap);
        padding: clamp(18px, 2.6vw, 34px);
        border: 1px solid #523d79;
        border-radius: 20px;
        background:
            radial-gradient(ellipse at 97% 120%, transparent 0 17%, rgb(238 209 255 / 17%) 17.15% 17.3%, transparent 17.5% 22%, rgb(238 209 255 / 12%) 22.15% 22.3%, transparent 22.5%),
            linear-gradient(112deg, #231c3c 10%, #372653 65%, #5b3a72);
        box-shadow: 0 10px 23px rgb(43 27 75 / 19%);
    }

    #events-page :is(.events-heading, .events-gallery-heading) {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    #events-page :is(.events-heading-icon, .kpi-icon-box, .events-cover-icon, .events-empty-icon, .events-organizer-avatar) {
        display: grid !important;
        flex: 0 0 auto;
        place-items: center;
        line-height: 1;
    }

    #events-page :is(.events-heading-icon, .events-cover-icon) {
        width: clamp(46px, 5vw, 62px);
        aspect-ratio: 1;
        border: 1px solid rgb(247 233 255 / 27%);
        border-radius: 16px;
        background: rgb(216 188 250 / 15%);
        color: #fff !important;
        font-size: clamp(22px, 2.6vw, 32px);
    }

    #events-page .events-hero h1 {
        margin: 0;
        color: #fff !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(1.4rem, 3.1vw, 2.2rem) !important;
        font-weight: 750 !important;
        letter-spacing: -.035em;
        line-height: 1.1;
    }

    #events-page .events-hero p {
        margin: 6px 0 0;
        color: #e5d8f3 !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .9rem;
        font-weight: 450 !important;
    }

    #events-page :is(.events-create-button, .events-primary-button, .event-photos-button, .filter-btn-nowrap) {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px !important;
        padding: 7px 12px !important;
        border-radius: 10px !important;
        box-shadow: none !important;
        font-size: .8125rem !important;
        font-weight: 650 !important;
        white-space: nowrap;
    }

    #events-page .events-create-button {
        flex: 0 0 auto;
        border: 1px solid #f6eeff !important;
        background: #f6eeff !important;
        color: #3e285c !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #events-page .events-create-button:hover { background: #e9d9fa !important; }

    #events-page :is(.events-heading-icon, .events-cover-icon, .kpi-icon-box, .events-empty-icon, .events-meta-icon, .events-create-button, .events-primary-button, .event-photos-button, .filter-btn-nowrap, .filter-btn-group .btn, .btn-action-icon) > .bx {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: inherit;
        filter: none !important;
    }

    #events-page :is(.events-create-button, .events-primary-button, .event-photos-button, .filter-btn-nowrap) > .bx { font-size: 1rem; }

    #events-page .events-kpis {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: var(--ev-gap);
        margin: 0 0 var(--ev-gap);
    }

    #events-page :is(.kpi-card, .events-filter-card, .event-card, .gallery-card, .events-empty, #eventCalendar) {
        min-width: 0;
        border: 1px solid var(--ev-border) !important;
        border-radius: 16px !important;
        background: var(--ev-surface) !important;
        box-shadow: var(--ev-shadow) !important;
    }

    #events-page .kpi-card {
        --ev-kpi-color: var(--ev-accent);
        --ev-kpi-soft: var(--ev-accent-soft);
        display: grid;
        align-content: center;
        align-items: center;
        gap: 7px;
        padding: clamp(11px, 1.55vw, 22px);
        transform: none;
    }

    #events-page .event-kpi-upcoming { --ev-kpi-color: var(--ev-green); --ev-kpi-soft: var(--ev-green-soft); }
    #events-page .event-kpi-today { --ev-kpi-color: var(--ev-amber); --ev-kpi-soft: var(--ev-amber-soft); }
    #events-page .event-kpi-past { --ev-kpi-color: var(--ev-neutral); --ev-kpi-soft: var(--ev-neutral-soft); }

    #events-page .kpi-card dt {
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .8125rem;
        font-weight: 650;
    }

    #events-page .kpi-card dd {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 5px;
        margin: 0;
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(1.35rem, 2.9vw, 2.15rem);
        font-weight: 750;
        font-variant-numeric: tabular-nums;
        line-height: 1.1;
        overflow-wrap: anywhere;
    }

    #events-page .kpi-icon-box {
        width: clamp(32px, 3.8vw, 46px) !important;
        height: clamp(32px, 3.8vw, 46px) !important;
        border-radius: 11px !important;
        background: var(--ev-kpi-soft) !important;
        color: var(--ev-kpi-color) !important;
        font-size: clamp(17px, 1.9vw, 23px) !important;
        box-shadow: none !important;
    }

    #events-page .events-filter-card { container-type: inline-size; }

    #events-page .events-filter-card .card-body { padding: clamp(12px, 1.7vw, 21px) !important; }

    #events-page #filterForm {
        margin: 0;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    #events-page .events-filter-wrapper {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        align-items: start;
    }

    #events-page :is(.filter-search-box, .filter-select-box) { min-width: 0; }
    #events-page .filter-search-box { grid-column: 1 / -1; }

    #events-page .events-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    #events-page .filter-search-box .input-group {
        flex-wrap: nowrap;
        border: 1px solid var(--ev-control);
        border-radius: 10px;
        background: var(--ev-soft);
    }

    #events-page #filterForm :is(.form-control, .form-select, .input-group-text) {
        min-height: 40px !important;
        background-color: var(--ev-soft) !important;
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-size: .8125rem !important;
    }

    #events-page #filterForm .input-group :is(.form-control, .input-group-text) {
        border: 0 !important;
        border-radius: 10px !important;
        background: transparent !important;
    }

    #events-page #filterForm .input-group-text {
        flex: 0 0 40px !important;
        width: 40px !important;
        justify-content: center;
        padding: 0 !important;
    }

    #events-page #filterForm .input-group .bx { color: var(--ev-accent) !important; font-size: 1.05rem; }
    #events-page #filterForm .form-control {
        flex: 1 1 auto !important;
        width: 0 !important;
        min-width: 0;
        padding-left: 0 !important;
        text-align: start !important;
    }
    #events-page #filterForm .form-control::placeholder { color: var(--ev-muted) !important; -webkit-text-fill-color: currentColor !important; }
    #events-page #filterForm .form-select {
        border: 1px solid var(--ev-control) !important;
        border-radius: 10px !important;
        background-image: linear-gradient(45deg, transparent 50%, var(--ev-muted) 50%), linear-gradient(135deg, var(--ev-muted) 50%, transparent 50%) !important;
        background-position: right 15px center, right 10px center !important;
        background-size: 5px 5px !important;
        background-repeat: no-repeat !important;
    }

    #events-page :is(.events-primary-button, .filter-btn-nowrap.btn-primary) {
        border-color: var(--ev-primary) !important;
        background: var(--ev-primary) !important;
        color: #fff !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #events-page :is(.events-primary-button, .filter-btn-nowrap.btn-primary):hover { background: var(--ev-primary-hover) !important; }

    #events-page .filter-btn-nowrap.btn-outline-secondary {
        border: 1px solid var(--ev-control) !important;
        background: var(--ev-surface) !important;
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #events-page .filter-btn-group {
        display: inline-flex !important;
        max-width: 100%;
        margin: 12px 0 0;
        padding: 3px;
        gap: 3px;
        border: 1px solid var(--ev-border);
        border-radius: 11px;
        background: var(--ev-soft);
    }

    #events-page .filter-btn-group .btn {
        justify-content: center;
        width: auto !important;
        min-height: 38px !important;
        margin: 0 !important;
        padding: 6px clamp(8px, 1vw, 14px) !important;
        border: 1px solid transparent !important;
        border-radius: 8px !important;
        background: transparent !important;
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-size: .8125rem !important;
        font-weight: 650 !important;
    }

    #events-page .filter-btn-group .btn:is(.active, :hover) { background: var(--ev-accent-soft) !important; color: var(--ev-accent) !important; }
    #events-page .filter-btn-group .btn.active { border-color: var(--ev-control) !important; }

    #events-page .events-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
        gap: var(--ev-gap);
    }

    #events-page .events-card-item { min-width: 0; }

    #events-page :is(.event-card, .gallery-card):hover {
        border-color: var(--ev-control) !important;
        transform: translateY(-2px);
    }

    #events-page :is(.event-banner-img, .event-banner-placeholder) { height: 150px; }
    #events-page .event-banner-img { display: block; object-fit: cover; }

    #events-page .event-banner-placeholder {
        background:
            radial-gradient(circle at 50% 50%, transparent 0 53px, rgb(241 220 255 / 21%) 54px 55px, transparent 56px 74px, rgb(241 220 255 / 12%) 75px 76px, transparent 77px),
            linear-gradient(132deg, #42315e, #674986 60%, #815383);
    }

    #events-page .event-card-content { min-width: 0; padding: clamp(14px, 1.65vw, 21px) !important; }

    #events-page .events-card-tags {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
        min-width: 0;
    }

    #events-page .events-card-tags .badge {
        max-width: 100%;
        padding: 4px 9px !important;
        border: 1px solid transparent !important;
        border-radius: 999px !important;
        box-shadow: none !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .715rem !important;
        font-weight: 650 !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #events-page .events-card-tags .badge-event-type { background: var(--ev-accent-soft) !important; color: var(--ev-accent) !important; }
    #events-page .events-card-tags .badge-status-published { background: var(--ev-green-soft) !important; color: var(--ev-green) !important; }
    #events-page .events-card-tags .badge-status-draft { background: var(--ev-amber-soft) !important; color: var(--ev-amber) !important; }
    #events-page .events-card-tags .badge-status-cancelled { background: var(--ev-red-soft) !important; color: var(--ev-red) !important; }
    #events-page .events-card-tags .badge-status-completed { background: var(--ev-neutral-soft) !important; color: var(--ev-neutral) !important; }

    #events-page :is(.events-card-title, .events-empty h2, .events-empty h3, .events-gallery-heading h2) {
        margin: 0 0 10px;
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: 1.12rem !important;
        font-weight: 750 !important;
        line-height: 1.25;
        letter-spacing: -.016em;
    }

    #events-page .events-card-title {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        overflow-wrap: anywhere;
    }

    #events-page :is(.events-card-meta, .events-organizer, .events-gallery-date, .events-rsvp-label) {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .8125rem;
        font-weight: 600;
    }

    #events-page .events-card-meta { min-height: 26px; margin: 0 0 4px; }
    #events-page .events-card-location { margin-bottom: 10px; }
    #events-page .events-card-time::before { content: '·'; margin: 0 4px; }
    #events-page .events-card-meta a { color: var(--ev-accent) !important; -webkit-text-fill-color: currentColor !important; }
    #events-page .events-card-meta a:hover { text-decoration: underline !important; }

    #events-page .events-meta-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        width: 24px;
        height: 24px;
        border-radius: 7px;
        background: var(--ev-accent-soft);
        color: var(--ev-accent) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .95rem;
    }

    #events-page :is(.events-card-description, .events-empty p, .events-gallery-heading p) {
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .85rem;
        font-weight: 450 !important;
        line-height: 1.5;
    }

    #events-page .events-card-description { margin-bottom: 12px; overflow-wrap: anywhere; }

    #events-page .event-photos-button {
        min-height: 32px !important;
        width: auto !important;
        border: 1px solid var(--ev-border) !important;
        background: var(--ev-accent-soft) !important;
        color: var(--ev-accent) !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #events-page .event-photos-button:hover { border-color: var(--ev-accent) !important; }

    #events-page .rsvp-box { padding: 10px; border: 1px solid var(--ev-border) !important; border-radius: 12px; background: var(--ev-soft) !important; box-shadow: none; }
    #events-page .rsvp-count-badge { background: var(--ev-green-soft) !important; color: var(--ev-green) !important; -webkit-text-fill-color: currentColor !important; }
    #events-page .rsvp-count-badge .bx { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #events-page .rsvp-segmented-bar { border: 1px solid var(--ev-border); border-radius: 9px; background: var(--ev-surface) !important; }
    #events-page .rsvp-segment-btn { width: auto !important; min-width: 0; min-height: 33px; padding: 5px !important; border-radius: 7px !important; color: var(--ev-muted) !important; }
    #events-page .rsvp-segment-btn:is(.active-going, .active-maybe, .active-not_going) { color: #fff !important; -webkit-text-fill-color: #fff !important; box-shadow: none !important; }
    #events-page .rsvp-segment-btn.active-maybe { background: #9b580d !important; }

    #events-page .events-card-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid var(--ev-border);
    }

    #events-page .events-organizer { flex: 1 1 92px; color: var(--ev-ink) !important; }
    #events-page .events-gallery-organizer { display: block; overflow-wrap: anywhere; }
    #events-page .events-organizer-avatar { width: 30px; height: 30px; border-radius: 9px; background: var(--ev-accent-soft); color: var(--ev-accent) !important; -webkit-text-fill-color: currentColor !important; font-size: .72rem; font-weight: 750; }
    #events-page .events-card-actions { display: flex; gap: 5px; margin-left: auto; }

    #events-page .events-card-actions .btn-action-icon {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        min-height: 34px !important;
        padding: 0 !important;
        border: 1px solid var(--ev-border) !important;
        border-radius: 9px !important;
        background: var(--ev-soft) !important;
        color: var(--ev-accent) !important;
        box-shadow: none !important;
        font-size: 1.03rem;
    }

    #events-page .events-card-actions .text-success { color: var(--ev-green) !important; }
    #events-page .events-card-actions .text-danger { color: var(--ev-red) !important; }
    #events-page .events-card-actions .btn-action-icon:hover { border-color: currentColor !important; transform: none; }

    #events-page .events-empty { padding: clamp(25px, 5vw, 62px) 16px; text-align: center; }
    #events-page .events-empty-icon { width: 46px; height: 46px; border-radius: 13px; background: var(--ev-accent-soft); color: var(--ev-accent) !important; font-size: 1.35rem; }
    #events-page .events-empty .events-empty-icon { margin: 0 auto 12px; }
    #events-page .events-empty p { margin-bottom: 14px; }
    #events-page .events-gallery-heading { margin-bottom: var(--ev-gap); }
    #events-page .events-gallery-heading :is(h2, p) { margin: 0; }

    #events-page .gallery-preview-grid { grid-auto-rows: minmax(0, 1fr); grid-template-columns: repeat(2, minmax(0, 1fr)); min-height: 150px; background: var(--ev-soft); }
    #events-page .gallery-preview-grid.single-image { grid-template-columns: minmax(0, 1fr); }
    #events-page .gallery-preview-grid.gallery-has-three .gallery-preview-img:nth-child(3) { grid-column: 1 / -1; }
    #events-page .gallery-preview-grid .event-banner-placeholder { height: 100%; }
    #events-page .gallery-overlay-count { color: #fff !important; -webkit-text-fill-color: currentColor !important; }
    #events-page .gallery-overlay-count .bx { color: inherit !important; -webkit-text-fill-color: currentColor !important; }

    #events-page #eventCalendar {
        --fc-border-color: var(--ev-border);
        --fc-page-bg-color: var(--ev-surface);
        --fc-neutral-bg-color: var(--ev-soft);
        --fc-today-bg-color: rgba(88, 64, 173, 0.06);
        padding: clamp(12px, 2vw, 24px);
    }
    :is([data-pms-theme="dark"], [data-theme="dark"], [data-bs-theme="dark"], .dark, .dark-mode) #events-page #eventCalendar {
        --fc-today-bg-color: rgba(109, 80, 194, 0.16);
    }
    #events-page #eventCalendar .fc-toolbar-title {
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(1.05rem, 1.8vw, 1.35rem) !important;
        font-weight: 750 !important;
        letter-spacing: -0.02em;
    }
    #events-page #eventCalendar .fc-button {
        background: var(--ev-soft) !important;
        border: 1px solid var(--ev-control) !important;
        border-radius: 9px !important;
        padding: 7px 14px !important;
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: currentColor !important;
        box-shadow: none !important;
        font-size: .8125rem !important;
        font-weight: 650 !important;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }
    #events-page #eventCalendar .fc-button:not(:disabled):hover {
        background: var(--ev-accent-soft) !important;
        border-color: var(--ev-accent) !important;
        color: var(--ev-accent) !important;
    }
    #events-page #eventCalendar .fc-button:not(:disabled):is(.fc-button-active, :active) {
        background: var(--ev-primary) !important;
        border-color: var(--ev-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    #events-page #eventCalendar .fc-icon { color: inherit !important; -webkit-text-fill-color: currentColor !important; }

    /* Grid headers, day cells, and today indicator */
    #events-page #eventCalendar .fc-scrollgrid {
        border-radius: 12px !important;
        overflow: hidden;
        border-color: var(--ev-border) !important;
    }
    #events-page #eventCalendar .fc-col-header-cell {
        background: var(--ev-soft) !important;
        border-color: var(--ev-border) !important;
    }
    #events-page #eventCalendar .fc-col-header-cell-cushion {
        padding: 10px 6px !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: var(--ev-muted) !important;
        text-decoration: none !important;
    }
    #events-page #eventCalendar .fc-daygrid-day-number {
        padding: 7px 9px !important;
        font-size: .84rem !important;
        font-weight: 650 !important;
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: var(--ev-ink) !important;
        text-decoration: none !important;
    }
    #events-page #eventCalendar .fc-daygrid-day-other .fc-daygrid-day-number {
        color: var(--ev-muted) !important;
        -webkit-text-fill-color: var(--ev-muted) !important;
        opacity: .55;
    }
    #events-page #eventCalendar .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        height: 26px;
        margin: 5px 6px 2px;
        padding: 0 7px !important;
        border-radius: 999px;
        background: var(--ev-primary);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    #events-page #eventCalendar td.fc-daygrid-day {
        transition: background-color .15s ease;
    }
    #events-page #eventCalendar td.fc-daygrid-day:hover {
        background: rgba(88, 64, 173, 0.025);
    }

    /* Solid calendar event pills/cards: force crisp white text in both light and dark mode */
    #events-page #eventCalendar :is(.fc-daygrid-block-event, .fc-h-event, .fc-timegrid-event) {
        border: none !important;
        border-radius: 9px !important;
        margin: 2px 4px 4px !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
        transition: transform .16s ease, box-shadow .16s ease;
    }
    #events-page #eventCalendar :is(.fc-daygrid-block-event, .fc-h-event, .fc-timegrid-event):hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
    }
    #events-page #eventCalendar :is(.fc-daygrid-block-event, .fc-h-event, .fc-timegrid-event):not(.event-calendar-has-banner) {
        padding: 3px 7px !important;
    }
    #events-page #eventCalendar :is(
        .fc-daygrid-block-event,
        .fc-daygrid-block-event *,
        .fc-h-event,
        .fc-h-event *,
        .fc-timegrid-event,
        .fc-timegrid-event *,
        .event-calendar-has-banner,
        .event-calendar-has-banner *
    ) {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    #events-page #eventCalendar .fc-daygrid-dot-event {
        border-radius: 7px !important;
        padding: 3px 6px !important;
        margin: 1px 4px !important;
    }
    #events-page #eventCalendar .fc-daygrid-dot-event,
    #events-page #eventCalendar .fc-daygrid-dot-event :is(.fc-event-title, .fc-event-time) {
        color: var(--ev-ink) !important;
        -webkit-text-fill-color: var(--ev-ink) !important;
    }
    #events-page #eventCalendar .fc-daygrid-more-link {
        color: var(--ev-accent) !important;
        -webkit-text-fill-color: var(--ev-accent) !important;
        font-weight: 700;
        font-size: .78rem;
    }
    #events-page #eventCalendar .fc-popover {
        border-radius: 12px !important;
        border: 1px solid var(--ev-border) !important;
        background: var(--ev-surface) !important;
        box-shadow: var(--ev-shadow) !important;
    }
    #events-page #eventCalendar .fc-popover-header {
        background: var(--ev-soft) !important;
        color: var(--ev-ink) !important;
        border-bottom: 1px solid var(--ev-border) !important;
        font-weight: 700;
    }

    .events-section.modal :is(.modal-content, .event-meta-card) { border-radius: 14px !important; border: 1px solid var(--ev-border) !important; }
    .events-section.modal .modal-header.text-white { background: linear-gradient(112deg, #302345, #53366f) !important; }
    .events-section.modal .form-section-title { border-color: var(--ev-accent) !important; background: var(--ev-accent-soft) !important; color: var(--ev-accent) !important; -webkit-text-fill-color: currentColor !important; }

    #events-page :is(button, a, select, input):focus-visible { outline: 2px solid var(--ev-accent) !important; outline-offset: 2px; }
    #events-page .events-hero button:focus-visible { outline-color: #e1ccff !important; }

    @container (min-width: 760px) {
        #events-page .events-filter-wrapper { grid-template-columns: minmax(0, 1.4fr) repeat(3, minmax(0, 1fr)) auto; }
        #events-page .filter-search-box { grid-column: auto; }
    }

    @media (min-width: 940px) {
        #events-page .events-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        #events-page .kpi-card { min-height: 110px; }
    }

    @media (max-width: 480px) {
        #events-page .events-create-button { width: 100% !important; }
        #events-page .filter-btn-group { width: 100% !important; }
        #events-page :is(.filter-select-box:has([name="time_filter"]), .events-filter-actions) { grid-column: 1 / -1; }
        #events-page .events-filter-actions .filter-btn-nowrap { flex: 1 1 0 !important; width: auto !important; }
    }

    @media (prefers-reduced-motion: reduce) {
        #events-page :is(.event-card, .gallery-card, .gallery-preview-img, .btn-action-icon) { transition: none; transform: none; }
        #events-page :is(.event-card, .gallery-card):hover, #events-page .gallery-card:hover .gallery-preview-img { transform: none; }
    }
</style>
