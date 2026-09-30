<style>
    .partner-page { min-height: 100vh; padding: 28px; background: linear-gradient(135deg, #eef7ff, #F8FAFC); }
    .partner-hero, .partner-filter, .partner-card, .partner-detail-card, .partner-form-card, .partner-stats > div, .partner-empty {
        border: 1px solid rgba(47, 107, 255, .12);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
        border-radius: 18px;
    }
    .partner-hero { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 24px; margin-bottom: 18px; }
    .partner-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: #2F6BFF; font-weight: 900; text-transform: uppercase; font-size: .76rem; }
    .partner-hero h1 { margin: 8px 0 6px; font-weight: 950; color: #0F1530; }
    .partner-hero p, .partner-card p, .partner-muted { margin: 0; color: #64748B; font-weight: 700; }
    .partner-actions, .partner-card-actions, .partner-form-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .partner-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .partner-stats > div { padding: 18px; }
    .partner-stats span { color: #64748B; font-weight: 900; text-transform: uppercase; font-size: .76rem; }
    .partner-stats strong { display: block; font-size: 2rem; color: #2F6BFF; }
    .partner-filter { padding: 16px; margin-bottom: 18px; }
    .partner-filter form { display: grid; grid-template-columns: minmax(220px, 1fr) 180px auto auto; gap: 10px; align-items: center; }
    .partner-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .partner-card { padding: 18px; display: flex; flex-direction: column; gap: 14px; min-width: 0; }
    .partner-card-image { display: grid; place-items: center; overflow: hidden; width: 100%; aspect-ratio: 16 / 9; border-radius: 14px; background: linear-gradient(135deg, #2F6BFF, #4F83FF); color: #fff; text-decoration: none; }
    .partner-card-image img, .partner-show-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .partner-card-image span { font-size: 3rem; font-weight: 950; }
    .partner-card-head { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: start; min-width: 0; }
    .partner-avatar { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; background: linear-gradient(135deg, #2F6BFF, #4F83FF); color: #fff; font-weight: 950; font-size: 1.3rem; }
    .partner-card h2 { margin: 0 0 2px; font-size: 1.1rem; font-weight: 950; color: #0F1530; overflow-wrap: anywhere; }
    .partner-status { display: inline-flex; padding: 6px 10px; border-radius: 999px; color: #fff; font-size: .72rem; font-weight: 950; white-space: nowrap; }
    .partner-status.active { background: #10b981; }
    .partner-status.inactive { background: #64748b; }
    .partner-meta { display: flex; flex-wrap: wrap; gap: 8px; }
    .partner-meta span { display: inline-flex; align-items: center; gap: 6px; padding: 7px 9px; border-radius: 999px; background: #EEF2FF; color: #2F6BFF; font-size: .78rem; font-weight: 900; }
    .partner-contact-links { display: grid; gap: 7px; }
    .partner-contact-links a, .partner-text-link { color: #2F6BFF; font-weight: 900; text-decoration: none; overflow-wrap: anywhere; }
    .partner-contact-links a { display: inline-flex; align-items: center; gap: 8px; }
    .partner-description { min-height: 68px; }
    .partner-socials, .partner-social-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .partner-socials a, .partner-social-list a { display: inline-flex; align-items: center; gap: 7px; min-width: 36px; min-height: 36px; justify-content: center; border-radius: 10px; background: #EEF2FF; color: #2F6BFF; text-decoration: none; font-weight: 900; }
    .partner-card-actions { margin-top: auto; }
    .partner-card-actions form { display: inline-flex; }
    .partner-empty { grid-column: 1 / -1; padding: 44px 20px; text-align: center; color: #64748B; }
    .partner-empty i { font-size: 2.4rem; color: #2F6BFF; margin-bottom: 12px; }
    .partner-pagination { margin-top: 18px; }
    .partner-form-card, .partner-detail-card { padding: 22px; margin-bottom: 16px; }
    .partner-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .partner-form-grid .full { grid-column: 1 / -1; }
    .partner-form-grid label { display: block; margin-bottom: 6px; color: #64748B; font-size: .76rem; font-weight: 950; text-transform: uppercase; }
    .form-control, .form-select { min-height: 44px; border-radius: 12px; border: 1px solid #E2E8F0; font-weight: 700; }
    textarea.form-control { min-height: 110px; }
    .partner-form-actions { margin-top: 18px; }
    .partner-form-preview { width: min(280px, 100%); aspect-ratio: 16 / 9; object-fit: cover; border-radius: 14px; border: 1px solid #E2E8F0; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; min-height: 40px; font-weight: 900; }
    .btn-primary { background: linear-gradient(135deg, #2F6BFF, #4F83FF); border: 0; color: #fff; }
    .btn-light { background: #EEF2FF; border: 1px solid rgba(47, 107, 255, 0.16); color: #2F6BFF; }
    .btn-danger { background: #ef4444; border: 0; color: #fff; }
    .partner-detail-grid { display: grid; grid-template-columns: 1.25fr .75fr; gap: 16px; }
    .partner-show-image { overflow: hidden; border-radius: 18px; height: clamp(190px, 32vw, 380px); margin-bottom: 16px; box-shadow: 0 18px 44px rgba(15, 23, 42, .08); }
    .partner-detail-card h2 { font-size: 1.15rem; font-weight: 950; margin-bottom: 12px; }
    .partner-detail-card p { white-space: pre-wrap; color: #334155; font-weight: 700; }
    .partner-detail-card dl { display: grid; grid-template-columns: 140px 1fr; gap: 10px; margin: 0; }
    .partner-detail-card dt { color: #64748B; font-weight: 950; }
    .partner-detail-card dd { margin: 0; font-weight: 750; }
    @media (max-width: 1199px) { .partner-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .partner-page { padding: 12px; }
        .partner-hero, .partner-filter form, .partner-detail-grid, .partner-form-grid, .partner-stats, .partner-grid { grid-template-columns: 1fr; flex-direction: column; align-items: stretch; }
        .partner-filter form { display: grid; }
        .partner-actions .btn, .partner-filter .btn, .partner-card-actions .btn, .partner-card-actions form, .partner-form-actions .btn { width: 100%; }
        .partner-card-head { grid-template-columns: 44px 1fr; }
        .partner-status { grid-column: 1 / -1; justify-content: center; }
        .partner-detail-card dl { grid-template-columns: 1fr; }
    }

    /* Dark Theme Support */
    html[data-pms-theme="dark"] .partner-page,
    html[data-bs-theme="dark"] .partner-page,
    html[data-theme="dark"] .partner-page,
    html.dark .partner-page {
        background: #070B1A !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .partner-hero,
    html[data-pms-theme="dark"] .partner-filter,
    html[data-pms-theme="dark"] .partner-card,
    html[data-pms-theme="dark"] .partner-detail-card,
    html[data-pms-theme="dark"] .partner-form-card,
    html[data-pms-theme="dark"] .partner-stats > div,
    html[data-pms-theme="dark"] .partner-empty,
    html[data-bs-theme="dark"] .partner-hero,
    html[data-bs-theme="dark"] .partner-filter,
    html[data-bs-theme="dark"] .partner-card,
    html[data-bs-theme="dark"] .partner-detail-card,
    html[data-bs-theme="dark"] .partner-form-card,
    html[data-bs-theme="dark"] .partner-stats > div,
    html[data-bs-theme="dark"] .partner-empty {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .partner-hero h1,
    html[data-pms-theme="dark"] .partner-card h2,
    html[data-pms-theme="dark"] .partner-detail-card h2,
    html[data-pms-theme="dark"] .partner-empty h2,
    html[data-bs-theme="dark"] .partner-hero h1,
    html[data-bs-theme="dark"] .partner-card h2,
    html[data-bs-theme="dark"] .partner-detail-card h2,
    html[data-bs-theme="dark"] .partner-empty h2 {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .partner-eyebrow,
    html[data-bs-theme="dark"] .partner-eyebrow {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .partner-hero p,
    html[data-pms-theme="dark"] .partner-card p,
    html[data-pms-theme="dark"] .partner-muted,
    html[data-pms-theme="dark"] .partner-empty p,
    html[data-pms-theme="dark"] .partner-stats span,
    html[data-bs-theme="dark"] .partner-hero p,
    html[data-bs-theme="dark"] .partner-card p,
    html[data-bs-theme="dark"] .partner-muted,
    html[data-bs-theme="dark"] .partner-empty p,
    html[data-bs-theme="dark"] .partner-stats span {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .partner-stats strong,
    html[data-bs-theme="dark"] .partner-stats strong {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .form-control,
    html[data-pms-theme="dark"] .form-select,
    html[data-bs-theme="dark"] .form-control,
    html[data-bs-theme="dark"] .form-select {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .form-control::placeholder,
    html[data-bs-theme="dark"] .form-control::placeholder {
        color: #64748B !important;
    }

    html[data-pms-theme="dark"] .btn-light,
    html[data-bs-theme="dark"] .btn-light {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .btn-light:hover,
    html[data-bs-theme="dark"] .btn-light:hover {
        background: #1C2652 !important;
        border-color: rgba(96, 165, 250, 0.4) !important;
        color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] .partner-meta span,
    html[data-pms-theme="dark"] .partner-socials a,
    html[data-pms-theme="dark"] .partner-social-list a,
    html[data-bs-theme="dark"] .partner-meta span,
    html[data-bs-theme="dark"] .partner-socials a,
    html[data-bs-theme="dark"] .partner-social-list a {
        background: #141B3D !important;
        color: #60A5FA !important;
        border: 1px solid rgba(238, 241, 251, 0.08) !important;
    }

    html[data-pms-theme="dark"] .partner-contact-links a,
    html[data-pms-theme="dark"] .partner-text-link,
    html[data-bs-theme="dark"] .partner-contact-links a,
    html[data-bs-theme="dark"] .partner-text-link {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .partner-empty i,
    html[data-bs-theme="dark"] .partner-empty i {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .partner-detail-card p,
    html[data-bs-theme="dark"] .partner-detail-card p {
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .partner-detail-card dt,
    html[data-bs-theme="dark"] .partner-detail-card dt {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .partner-detail-card dd,
    html[data-bs-theme="dark"] .partner-detail-card dd {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .partner-form-grid label,
    html[data-bs-theme="dark"] .partner-form-grid label {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .partner-form-preview,
    html[data-bs-theme="dark"] .partner-form-preview {
        border-color: rgba(238, 241, 251, 0.16) !important;
    }
</style>
