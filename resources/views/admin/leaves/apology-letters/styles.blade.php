<style>
    .leave-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #f0f9f4, #f7fbff); color: #102119; }
    .leave-breadcrumb, .leave-hero, .filter-panel, .table-card { border: 1px solid rgba(16,185,129,.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); }
    .leave-breadcrumb { display: inline-flex; gap: 8px; align-items: center; padding: 12px 18px; border-radius: 14px; color: #0f744c; font-weight: 900; margin-bottom: 22px; }
    .leave-hero { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 28px; border-radius: 24px; margin-bottom: 20px; }
    .leave-hero-main { display: flex; gap: 16px; align-items: center; }
    .leave-hero-icon { width: 58px; height: 58px; border-radius: 18px; display: grid; place-items: center; background: #d1fae5; color: #047857; font-size: 24px; }
    .leave-hero h1 { margin: 0 0 6px; font-size: 34px; font-weight: 900; }
    .leave-hero p, .table-head p { margin: 0; color: #667085; font-weight: 650; }
    .leave-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .archive-count-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #ef4444; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 900; }
    .filter-panel, .table-card { border-radius: 22px; padding: 22px; margin-bottom: 20px; }
    .filter-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 16px; align-items: end; }
    label { color: #667085; text-transform: uppercase; font-size: .76rem; font-weight: 900; margin-bottom: 6px; }
    .form-control { min-height: 44px; border-radius: 12px; border: 1px solid #dbe7e1; font-weight: 650; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; min-height: 40px; font-weight: 900; border: 0; }
    .btn-primary { background: linear-gradient(145deg, #34d399, #059669); color: #fff; }
    .btn-light, .btn-secondary { background: #f0f9f4; color: #0f744c; border: 1px solid rgba(16,185,129,.18); }
    .table-head { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 16px; align-items: center; }
    .table-head h2 { margin: 0 0 4px; font-weight: 900; }
    .checkbox-col { width: 44px; text-align: center; }
    .leave-table th { color: #667085; font-size: .78rem; text-transform: uppercase; }
    .leave-table td { vertical-align: middle; font-weight: 650; }
    .leave-table small { display: block; color: #667085; }
    .status-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: .75rem; font-weight: 900; color: #fff; }
    .status-badge.pending { background: #f59e0b; }
    .status-badge.approved { background: #10b981; }
    .status-badge.archived { background: #64748b; }
    .empty-state { color: #667085; }
    .empty-state i { font-size: 34px; color: #10b981; margin-bottom: 10px; }
    .pagination-wrap { margin-top: 16px; }
    @media (max-width: 992px) { .leave-page { padding: 18px; } .leave-hero { flex-direction: column; align-items: flex-start; } .filter-grid { grid-template-columns: 1fr; } }
</style>
