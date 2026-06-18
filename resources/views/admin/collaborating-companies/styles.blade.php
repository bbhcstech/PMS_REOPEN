<style>
    .partner-page { min-height: 100vh; padding: 28px; background: linear-gradient(135deg, #eef7ff, #f8fffb); }
    .partner-hero, .partner-filter, .partner-card, .partner-detail-card, .partner-form-card, .partner-stats > div, .partner-empty {
        border: 1px solid rgba(15, 116, 76, .12);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 18px 44px rgba(15, 23, 42, .08);
        border-radius: 18px;
    }
    .partner-hero { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 24px; margin-bottom: 18px; }
    .partner-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: #0f744c; font-weight: 900; text-transform: uppercase; font-size: .76rem; }
    .partner-hero h1 { margin: 8px 0 6px; font-weight: 950; color: #102119; }
    .partner-hero p, .partner-card p, .partner-muted { margin: 0; color: #647067; font-weight: 700; }
    .partner-actions, .partner-card-actions, .partner-form-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .partner-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .partner-stats > div { padding: 18px; }
    .partner-stats span { color: #647067; font-weight: 900; text-transform: uppercase; font-size: .76rem; }
    .partner-stats strong { display: block; font-size: 2rem; color: #0f744c; }
    .partner-filter { padding: 16px; margin-bottom: 18px; }
    .partner-filter form { display: grid; grid-template-columns: minmax(220px, 1fr) 180px auto auto; gap: 10px; align-items: center; }
    .partner-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .partner-card { padding: 18px; display: flex; flex-direction: column; gap: 14px; min-width: 0; }
    .partner-card-head { display: grid; grid-template-columns: 48px 1fr auto; gap: 12px; align-items: center; min-width: 0; }
    .partner-avatar { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; background: linear-gradient(135deg, #0f744c, #35c985); color: #fff; font-weight: 950; font-size: 1.3rem; }
    .partner-card h2 { margin: 0 0 2px; font-size: 1.1rem; font-weight: 950; color: #102119; overflow-wrap: anywhere; }
    .partner-status { display: inline-flex; padding: 6px 10px; border-radius: 999px; color: #fff; font-size: .72rem; font-weight: 950; white-space: nowrap; }
    .partner-status.active { background: #10b981; }
    .partner-status.inactive { background: #64748b; }
    .partner-meta { display: flex; flex-wrap: wrap; gap: 8px; }
    .partner-meta span { display: inline-flex; align-items: center; gap: 6px; padding: 7px 9px; border-radius: 999px; background: #eef8f2; color: #0f744c; font-size: .78rem; font-weight: 900; }
    .partner-description { min-height: 68px; }
    .partner-socials, .partner-social-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .partner-socials a, .partner-social-list a { display: inline-flex; align-items: center; gap: 7px; min-width: 36px; min-height: 36px; justify-content: center; border-radius: 10px; background: #eef8f2; color: #0f744c; text-decoration: none; font-weight: 900; }
    .partner-card-actions { margin-top: auto; }
    .partner-card-actions form { display: inline-flex; }
    .partner-empty { grid-column: 1 / -1; padding: 44px 20px; text-align: center; color: #647067; }
    .partner-empty i { font-size: 2.4rem; color: #0f744c; margin-bottom: 12px; }
    .partner-pagination { margin-top: 18px; }
    .partner-form-card, .partner-detail-card { padding: 22px; margin-bottom: 16px; }
    .partner-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .partner-form-grid .full { grid-column: 1 / -1; }
    .partner-form-grid label { display: block; margin-bottom: 6px; color: #647067; font-size: .76rem; font-weight: 950; text-transform: uppercase; }
    .form-control, .form-select { min-height: 44px; border-radius: 12px; border: 1px solid #dbe7e1; font-weight: 700; }
    textarea.form-control { min-height: 110px; }
    .partner-form-actions { margin-top: 18px; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; min-height: 40px; font-weight: 900; }
    .btn-primary { background: linear-gradient(135deg, #0f744c, #35c985); border: 0; color: #fff; }
    .btn-light { background: #eef8f2; border: 1px solid rgba(15,116,76,.16); color: #0f744c; }
    .btn-danger { background: #ef4444; border: 0; color: #fff; }
    .partner-detail-grid { display: grid; grid-template-columns: 1.25fr .75fr; gap: 16px; }
    .partner-detail-card h2 { font-size: 1.15rem; font-weight: 950; margin-bottom: 12px; }
    .partner-detail-card p { white-space: pre-wrap; color: #344139; font-weight: 700; }
    .partner-detail-card dl { display: grid; grid-template-columns: 140px 1fr; gap: 10px; margin: 0; }
    .partner-detail-card dt { color: #647067; font-weight: 950; }
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
</style>
