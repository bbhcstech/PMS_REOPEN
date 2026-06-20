<style>
    .org-page { min-height: 100vh; padding: 24px; background: #f6f8fb; color: #1f2937; }
    .org-hero, .org-profile-hero, .org-admin-strip, .org-filter, .org-card, .org-panel, .org-editor-panel, .org-stats > div, .org-department, .org-empty {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }
    .org-alert { border-radius: 8px; font-weight: 800; }
    .org-hero, .org-profile-hero { display: flex; justify-content: space-between; align-items: center; gap: 18px; padding: 22px; margin-bottom: 16px; }
    .org-hero-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .org-add-employee-btn { box-shadow: 0 12px 24px rgba(15, 118, 110, .18); }
    .org-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: #0f766e; font-size: .76rem; font-weight: 900; text-transform: uppercase; }
    .org-hero h1, .org-profile-hero h1 { margin: 8px 0 6px; color: #111827; font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 950; }
    .org-hero p, .org-profile-hero p, .org-card p, .org-department p, .org-muted { margin: 0; color: #64748b; font-weight: 700; }
    .org-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 16px; }
    .org-stats > div { padding: 18px; border-left: 4px solid #0f766e; }
    .org-stats span { display: block; color: #64748b; font-size: .75rem; font-weight: 900; text-transform: uppercase; }
    .org-stats strong { display: block; color: #111827; font-size: 2rem; line-height: 1.1; margin-top: 5px; }
    .org-filter { padding: 14px; margin-bottom: 16px; }
    .org-filter form { display: grid; grid-template-columns: minmax(220px, 1fr) 190px 190px auto auto; gap: 10px; align-items: center; }
    .form-control, .form-select { min-height: 42px; border-radius: 8px; border: 1px solid #d9e2ec; font-weight: 700; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; border-radius: 8px; font-weight: 900; }
    .btn-primary { background: #0f766e; border-color: #0f766e; color: #fff; }
    .btn-light { background: #eef2f7; border: 1px solid #d9e2ec; color: #334155; }
    .org-admin-strip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px;
        margin-bottom: 16px;
        border-left: 4px solid #0f766e;
    }
    .org-admin-strip span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0f766e;
        font-size: .76rem;
        font-weight: 950;
        text-transform: uppercase;
    }
    .org-admin-strip strong {
        display: block;
        margin-top: 4px;
        color: #111827;
        font-size: 1rem;
        font-weight: 900;
    }
    .org-admin-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
    .org-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    .org-card { padding: 16px; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
    .org-card-top, .org-profile-main { display: flex; align-items: center; gap: 13px; min-width: 0; }
    .org-avatar, .org-profile-avatar { flex: 0 0 auto; display: grid; place-items: center; overflow: hidden; background: linear-gradient(135deg, #0f766e, #2563eb); color: #fff; text-decoration: none; font-weight: 950; }
    .org-avatar { width: 58px; height: 58px; border-radius: 8px; font-size: 1.4rem; }
    .org-profile-avatar { width: 86px; height: 86px; border-radius: 8px; font-size: 2.2rem; }
    .org-avatar img, .org-profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .org-card h2 { margin: 0 0 3px; color: #111827; font-size: 1.08rem; font-weight: 950; overflow-wrap: anywhere; }
    .org-tags, .org-card-links, .org-skill-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .org-tags span, .org-skill-list span { display: inline-flex; align-items: center; gap: 6px; padding: 7px 9px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: .78rem; font-weight: 900; }
    .org-card-facts, .org-detail-list { display: grid; grid-template-columns: 110px 1fr; gap: 8px 12px; margin: 0; }
    .org-card-facts dt, .org-detail-list dt { color: #64748b; font-size: .78rem; font-weight: 950; text-transform: uppercase; }
    .org-card-facts dd, .org-detail-list dd { margin: 0; color: #1f2937; font-weight: 800; overflow-wrap: anywhere; }
    .org-card-links { margin-top: auto; }
    .org-card-links a, .org-contact-list a { color: #0f766e; text-decoration: none; font-weight: 900; }
    .org-card-links a { display: inline-flex; align-items: center; gap: 7px; padding: 8px 10px; border-radius: 8px; background: #f1f5f9; }
    .org-card-links .org-card-edit-link { background: #0f766e; color: #fff; }
    .org-card-about { color: #475569; font-weight: 750; line-height: 1.55; }
    .org-social-strip { display: flex; flex-wrap: wrap; gap: 8px; }
    .org-social-strip a { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 8px; background: #eef6ff; color: #1d4ed8; text-decoration: none; }
    .org-pagination { margin-top: 16px; }
    .org-structure { margin-top: 22px; }
    .org-section-title { margin-bottom: 12px; }
    .org-section-title span { color: #0f766e; font-size: .76rem; font-weight: 950; text-transform: uppercase; }
    .org-section-title h2 { margin: 4px 0 0; color: #111827; font-size: 1.35rem; font-weight: 950; }
    .org-department-grid, .org-profile-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .org-department { padding: 16px; display: grid; grid-template-columns: 1fr auto; gap: 12px; }
    .org-department h3 { margin: 0 0 3px; font-size: 1.05rem; font-weight: 950; color: #111827; }
    .org-department > strong { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 8px; background: #111827; color: #fff; }
    .org-mini-list { grid-column: 1 / -1; display: grid; gap: 8px; }
    .org-mini-list a { display: grid; grid-template-columns: 34px 1fr; gap: 9px; align-items: center; text-decoration: none; color: inherit; padding: 8px; border-radius: 8px; background: #f8fafc; }
    .org-mini-list span { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 8px; background: #dbeafe; color: #1d4ed8; font-weight: 950; }
    .org-mini-list b, .org-mini-list small { display: block; overflow-wrap: anywhere; }
    .org-mini-list small { color: #64748b; font-weight: 800; }
    .org-empty { grid-column: 1 / -1; text-align: center; padding: 40px 16px; color: #64748b; }
    .org-empty i { color: #0f766e; font-size: 2rem; margin-bottom: 10px; }
    .org-profile-grid { margin-bottom: 14px; }
    .org-panel { padding: 18px; }
    .org-panel h2 { margin: 0 0 13px; color: #111827; font-size: 1.15rem; font-weight: 950; }
    .org-status { display: inline-flex; padding: 6px 10px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: .78rem; font-weight: 950; }
    .org-contact-list { display: grid; gap: 10px; }
    .org-contact-list a, .org-contact-list span { display: inline-flex; align-items: center; gap: 9px; overflow-wrap: anywhere; font-weight: 850; color: #334155; }
    .org-about { color: #334155; font-weight: 750; white-space: pre-wrap; }
    .org-profile-links { display: flex; flex-wrap: wrap; gap: 10px; }
    .org-profile-links a { display: inline-flex; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 8px; background: #f1f5f9; color: #0f766e; text-decoration: none; font-weight: 900; }
    .org-editor-panel { padding: 20px; margin-top: 16px; border-top: 4px solid #0f766e; }
    .org-editor-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
    .org-editor-head h2 { margin: 6px 0 6px; color: #111827; font-size: 1.3rem; font-weight: 950; }
    .org-editor-head p { margin: 0; color: #64748b; font-weight: 750; }
    .org-source-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
    .org-source-summary div { padding: 12px; border-radius: 8px; background: #f8fafc; border: 1px solid #e5e7eb; min-width: 0; }
    .org-source-summary span { display: block; color: #64748b; font-size: .72rem; font-weight: 950; text-transform: uppercase; }
    .org-source-summary strong { display: block; margin-top: 4px; color: #111827; font-weight: 950; overflow-wrap: anywhere; }
    .org-editor-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .org-editor-form label { display: block; margin-bottom: 6px; color: #475569; font-size: .76rem; font-weight: 950; text-transform: uppercase; }
    .org-editor-form textarea.form-control { min-height: 96px; }
    .org-form-full, .org-editor-actions { grid-column: 1 / -1; }
    .org-file-note { display: block; margin-top: 6px; color: #64748b; font-weight: 800; }
    .org-file-note a { color: #0f766e; text-decoration: none; font-weight: 950; }
    @media (max-width: 1199px) {
        .org-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .org-filter form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .org-page { padding: 12px; }
        .org-hero, .org-profile-hero, .org-admin-strip { align-items: stretch; flex-direction: column; }
        .org-stats, .org-grid, .org-department-grid, .org-profile-grid, .org-filter form, .org-source-summary, .org-editor-form { grid-template-columns: 1fr; }
        .org-hero .btn, .org-profile-hero .btn, .org-filter .btn, .org-hero-actions .btn, .org-admin-actions .btn, .org-editor-actions .btn { width: 100%; }
        .org-card-facts, .org-detail-list { grid-template-columns: 1fr; }
        .org-profile-main { align-items: flex-start; }
    }
</style>
