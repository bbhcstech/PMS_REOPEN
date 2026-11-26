{{-- Events List + Gallery polish (styles only, on top of presentation.blade.php; uses its --ev-* tokens). --}}
<style>
    /* ---------- Cards: even rhythm, footer pinned to the bottom ---------- */
    #pms-events-page :is(.event-card, .gallery-card) {
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease !important;
    }
    #pms-events-page :is(.event-card, .gallery-card):hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 10px rgb(38 26 63 / 6%), 0 22px 44px -12px rgb(38 26 63 / 22%) !important;
        border-color: color-mix(in srgb, var(--ev-accent) 35%, var(--ev-border)) !important;
    }
    #pms-events-page .events-card-description {
        flex-grow: 0 !important;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.6;
    }
    #pms-events-page .event-card .events-card-footer,
    #pms-events-page .gallery-card .events-card-footer {
        margin-top: auto !important;
    }
    #pms-events-page .event-card .rsvp-box { margin-bottom: 14px; }
    #pms-events-page .events-card-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        letter-spacing: -.01em;
    }

    /* ---------- List cover: soft zoom + gradient fade into the card ---------- */
    #pms-events-page .events-list-cover {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }
    #pms-events-page .events-list-cover::after {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 55%;
        background: linear-gradient(to top, rgb(17 12 36 / 32%), transparent);
        pointer-events: none;
        z-index: 1;
    }
    #pms-events-page .events-list-cover .event-banner-img {
        width: 100%;
        transition: transform .5s ease;
    }
    #pms-events-page .event-card:hover .events-list-cover .event-banner-img { transform: scale(1.06); }
    #pms-events-page .event-banner-placeholder {
        background:
            radial-gradient(circle at 20% 20%, rgb(255 255 255 / 18%), transparent 45%),
            linear-gradient(135deg, #2f6bff 0%, #6d5dfc 55%, #8b5cf6 100%) !important;
    }

    /* ---------- Gallery: framed collage, readable photo chip ---------- */
    #pms-events-page .gallery-preview-grid {
        position: relative;
        overflow: hidden;
        gap: 3px !important;
        background: var(--ev-surface);
    }
    #pms-events-page .gallery-preview-grid::after {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 45%;
        background: linear-gradient(to top, rgb(17 12 36 / 45%), transparent);
        pointer-events: none;
    }
    #pms-events-page .gallery-preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease, filter .3s ease;
    }
    #pms-events-page .gallery-card:hover .gallery-preview-img { transform: scale(1.06); filter: saturate(1.1); }
    #pms-events-page .gallery-overlay-count {
        z-index: 2;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        background: rgb(17 12 36 / 62%) !important;
        border: 1px solid rgb(255 255 255 / 22%);
        box-shadow: 0 4px 14px rgb(0 0 0 / 18%);
        font-weight: 700;
        letter-spacing: .01em;
    }
    #pms-events-page .gallery-card { cursor: pointer; }
    #pms-events-page .gallery-card .events-primary-button {
        background: linear-gradient(135deg, var(--ev-primary) 0%, #8b5cf6 100%) !important;
        border: 0 !important;
        box-shadow: 0 8px 18px -8px rgb(89 64 176 / 60%) !important;
        transition: transform .18s ease, filter .18s ease !important;
    }
    #pms-events-page .gallery-card:hover .events-primary-button { filter: brightness(1.06); }
    #pms-events-page .gallery-card .events-primary-button .bx { transition: transform .2s ease; }
    #pms-events-page .gallery-card:hover .events-primary-button .bx { transform: translateX(3px); }

    /* ---------- Small touches ---------- */
    #pms-events-page .event-card .events-organizer-avatar {
        background: linear-gradient(135deg, var(--ev-accent-soft), color-mix(in srgb, var(--ev-accent) 22%, var(--ev-accent-soft))) !important;
    }
    #pms-events-page :is(.event-card, .gallery-card) :is(.btn, button):focus-visible {
        outline: 3px solid color-mix(in srgb, var(--ev-accent) 45%, transparent);
        outline-offset: 2px;
    }
    #pms-events-page .gallery-card:focus-within { border-color: var(--ev-accent) !important; }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #pms-events-page :is(.event-card, .gallery-card):hover {
        box-shadow: 0 4px 10px rgb(0 0 0 / 25%), 0 22px 44px -12px rgb(0 0 0 / 55%) !important;
    }

    @media (prefers-reduced-motion: reduce) {
        #pms-events-page :is(.event-card, .gallery-card),
        #pms-events-page :is(.event-banner-img, .gallery-preview-img, .events-primary-button, .events-primary-button .bx) { transition: none !important; }
        #pms-events-page :is(.event-card, .gallery-card):hover,
        #pms-events-page :is(.event-card, .gallery-card):hover :is(.event-banner-img, .gallery-preview-img) { transform: none; }
    }
</style>
