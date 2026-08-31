<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --org-navy-dark: #0f172a;
        --org-navy-main: #1e293b;
        --org-slate-muted: #64748b;
        --org-slate-light: #f8fafc;
        --org-emerald-main: #0f744c;
        --org-emerald-strong: #094c32;
        --org-emerald-light: #e4f3eb;
        --org-emerald-border: #a7f3d0;
        --org-blue-main: #2563eb;
        --org-blue-light: #eff6ff;
        --org-amber-main: #d97706;
        --org-amber-light: #fffbeb;
        --org-rose-main: #e11d48;
        --org-rose-light: #fff1f2;
        --org-card-bg: #ffffff;
        --org-border: #e2e8f0;
        --org-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05);
        --org-shadow-md: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        --org-shadow-lg: 0 20px 35px -10px rgba(15, 23, 42, 0.1);
        --org-radius-md: 10px;
        --org-radius-lg: 16px;
        --org-radius-xl: 22px;
    }

    .org-page {
        min-height: 100vh;
        padding: 24px 32px;
        background: #f1f5f9;
        color: var(--org-navy-dark);
        font-family: "Plus Jakarta Sans", "Inter", system-ui, -apple-system, sans-serif;
        -webkit-font-smoothing: antialiased;
    }

    /* BREADCRUMB & HEADER */
    .org-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--org-slate-muted);
        margin-bottom: 16px;
    }

    .org-breadcrumb a {
        color: var(--org-slate-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .org-breadcrumb a:hover {
        color: var(--org-emerald-main);
    }

    .org-header-card {
        background: var(--org-card-bg);
        border: 1px solid var(--org-border);
        border-radius: var(--org-radius-xl);
        padding: 24px 28px;
        box-shadow: var(--org-shadow-md);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .org-header-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, var(--org-emerald-main), var(--org-emerald-strong));
    }

    .org-header-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .org-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #e4f3eb 0%, #c6ebd7 100%);
        color: var(--org-emerald-main);
        display: grid;
        place-items: center;
        font-size: 1.5rem;
        box-shadow: inset 0 0 0 1px rgba(15, 116, 76, 0.15);
    }

    .org-header-text h1 {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
        letter-spacing: -0.02em;
    }

    .org-header-text p {
        margin: 4px 0 0 0;
        font-size: 0.9rem;
        color: var(--org-slate-muted) !important;
        -webkit-text-fill-color: var(--org-slate-muted) !important;
        font-weight: 500;
    }

    .org-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* BUTTONS */
    .org-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        white-space: nowrap;
    }

    .org-btn-primary {
        background: linear-gradient(135deg, #0f744c 0%, #094c32 100%) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(15, 116, 76, 0.25);
    }

    .org-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(15, 116, 76, 0.35);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .org-btn-secondary {
        background: #ffffff !important;
        border-color: var(--org-border) !important;
        color: var(--org-navy-main) !important;
        -webkit-text-fill-color: var(--org-navy-main) !important;
        box-shadow: var(--org-shadow-sm);
    }

    .org-btn-secondary:hover {
        background: var(--org-slate-light) !important;
        border-color: #cbd5e1 !important;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
    }

    .org-btn-outline {
        background: transparent !important;
        border-color: #cbd5e1 !important;
        color: var(--org-slate-muted) !important;
        -webkit-text-fill-color: var(--org-slate-muted) !important;
    }

    .org-btn-outline:hover {
        background: #f8fafc !important;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
    }

    .org-btn-icon {
        width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 8px;
    }

    /* STATS CARDS GRID */
    .org-stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .org-stat-card {
        background: var(--org-card-bg);
        border: 1px solid var(--org-border);
        border-radius: var(--org-radius-lg);
        padding: 18px 20px;
        box-shadow: var(--org-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }

    .org-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--org-shadow-md);
    }

    .org-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .org-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
    }

    .org-stat-icon.total { background: #eff6ff; color: #2563eb; }
    .org-stat-icon.active { background: #e4f3eb; color: #0f744c; }
    .org-stat-icon.inactive { background: #f1f5f9; color: #64748b; }
    .org-stat-icon.departments { background: #f5f3ff; color: #7c3aed; }
    .org-stat-icon.managers { background: #fff7ed; color: #ea580c; }
    .org-stat-icon.onleave { background: #fffbebf0; color: #d97706; }

    .org-stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .org-stat-label {
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--org-slate-muted) !important;
        -webkit-text-fill-color: var(--org-slate-muted) !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 4px;
    }

    /* TOOLBAR & FILTERS */
    .org-toolbar-card {
        background: var(--org-card-bg);
        border: 1px solid var(--org-border);
        border-radius: var(--org-radius-xl);
        padding: 16px 20px;
        box-shadow: var(--org-shadow-sm);
        margin-bottom: 24px;
    }

    .org-toolbar-form {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .org-search-wrap {
        position: relative;
        flex: 1 1 280px;
        min-width: 240px;
    }

    .org-search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #0f744c;
        font-size: 1rem;
        pointer-events: none;
    }

    .org-input {
        width: 100%;
        min-height: 44px;
        padding: 0 16px 0 44px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #ffffff;
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .org-input:focus, .org-select:focus {
        border-color: var(--org-emerald-main) !important;
        box-shadow: 0 0 0 3px rgba(15, 116, 76, 0.15) !important;
    }

    .org-select {
        min-height: 44px;
        padding: 0 36px 0 18px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%252364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") no-repeat right 14px center / 16px;
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--org-navy-main) !important;
        -webkit-text-fill-color: var(--org-navy-main) !important;
        appearance: none;
        outline: none;
        cursor: pointer;
    }

    .org-btn-primary {
        border-radius: 999px !important;
        padding: 0 22px !important;
    }

    .org-view-toggle {
        display: inline-flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        margin-left: auto;
        gap: 4px;
    }

    .org-view-btn {
        padding: 7px 16px;
        border-radius: 10px;
        border: 0;
        background: transparent;
        color: var(--org-slate-muted);
        font-size: 0.86rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .org-view-btn i {
        font-size: 0.9rem;
    }

    .org-view-btn.active {
        background: #ffffff !important;
        color: var(--org-emerald-main) !important;
        -webkit-text-fill-color: var(--org-emerald-main) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08) !important;
    }

    /* FORM GROUPS & CLEAN INPUT LABELS WITH AMPLE LEFT PADDING & OFFSET */
    .org-form-group {
        margin-bottom: 18px !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 6px !important;
        position: relative !important;
    }

    .org-form-label {
        display: block !important;
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 6px !important;
        margin-top: 0 !important;
        margin-left: 4px !important;
        line-height: 1.3 !important;
        position: static !important;
        transform: none !important;
    }

    .org-form-control {
        width: 100% !important;
        min-height: 46px !important;
        padding: 12px 18px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        font-size: 0.9rem !important;
        font-weight: 500 !important;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
        outline: none !important;
        box-shadow: none !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .org-form-control:focus {
        border-color: var(--org-emerald-main) !important;
        box-shadow: 0 0 0 3px rgba(15, 116, 76, 0.15) !important;
    }

    /* SHOW / PROFILE PAGE BRANDED EXECUTIVE STYLES WITH EXPLICIT WHITE CHIP TEXT */
    .org-profile-banner {
        background: linear-gradient(135deg, #094c32 0%, #0f744c 65%, #146c47 100%) !important;
        border-radius: var(--org-radius-xl);
        padding: 32px 36px;
        color: #ffffff !important;
        box-shadow: 0 20px 45px -10px rgba(9, 76, 50, 0.3) !important;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }

    .org-profile-banner::after {
        content: "";
        position: absolute;
        right: -60px;
        bottom: -60px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }

    .org-banner-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        position: relative;
        z-index: 2;
    }

    .org-profile-user-group {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .org-banner-avatar {
        width: 96px !important;
        height: 96px !important;
        border-radius: 22px !important;
        object-fit: cover !important;
        border: 4px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25) !important;
        background: linear-gradient(135deg, #0f744c, #094c32) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: grid !important;
        place-items: center !important;
        font-size: 2.2rem !important;
        font-weight: 800 !important;
        flex-shrink: 0 !important;
    }

    .org-banner-info h1,
    .org-profile-banner h1,
    .org-profile-banner .org-banner-info h1 {
        margin: 0 0 8px 0 !important;
        font-size: 2.1rem !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        letter-spacing: -0.02em !important;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
    }

    .org-banner-chips {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .org-banner-chip {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 6px 16px !important;
        border-radius: 999px !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        backdrop-filter: blur(8px) !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }

    .org-banner-chip i {
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
        font-size: 0.9rem !important;
    }

    .org-banner-chip > span,
    .org-banner-chip .chip-label {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    .org-banner-btn-primary {
        background: #ffffff !important;
        color: #094c32 !important;
        -webkit-text-fill-color: #094c32 !important;
        border: 1px solid #ffffff !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18) !important;
    }

    .org-banner-btn-primary i {
        color: #094c32 !important;
        -webkit-text-fill-color: #094c32 !important;
    }

    .org-banner-btn-primary:hover {
        background: #f8fafc !important;
        color: #047857 !important;
        -webkit-text-fill-color: #047857 !important;
        transform: translateY(-2px) !important;
    }

    .org-banner-btn-secondary {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
        font-weight: 700 !important;
        backdrop-filter: blur(6px) !important;
    }

    .org-banner-btn-secondary i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .org-banner-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: translateY(-2px) !important;
    }

    .org-banner-btn-outline {
        background: transparent !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.45) !important;
        font-weight: 700 !important;
    }

    .org-banner-btn-outline i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .org-banner-btn-outline:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: translateY(-2px) !important;
    }

    /* PROFILE LAYOUT GRID & ENTERPRISE PROFILE DATA TABLES */
    .org-profile-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
    }

    .org-side-card, .org-main-card {
        background: var(--org-card-bg);
        border: 1px solid var(--org-border);
        border-radius: var(--org-radius-lg);
        padding: 24px;
        box-shadow: var(--org-shadow-sm);
        margin-bottom: 24px;
    }

    .org-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
        margin: 0 0 18px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .org-card-title i {
        color: var(--org-emerald-main);
    }

    /* PREMIUM STRUCTURED PROFILE DATA TABLES */
    .org-profile-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
    }

    .org-profile-data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .org-profile-data-table td {
        padding: 14px 18px;
        font-size: 0.88rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .org-profile-data-table tr:last-child td {
        border-bottom: none;
    }

    .org-data-label {
        width: 22%;
        background: #f8fafc;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        font-weight: 700;
        font-size: 0.82rem;
        white-space: nowrap;
        border-right: 1px solid #f1f5f9;
    }

    .org-data-value {
        width: 28%;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-weight: 600;
    }

    .org-social-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
    }

    .org-social-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: var(--org-navy-dark) !important;
        -webkit-text-fill-color: var(--org-navy-dark) !important;
        font-size: 0.84rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .org-social-pill:hover {
        background: #ffffff;
        border-color: var(--org-emerald-main);
        color: var(--org-emerald-main) !important;
        -webkit-text-fill-color: var(--org-emerald-main) !important;
        box-shadow: 0 4px 12px rgba(15, 116, 76, 0.1);
    }

    /* DIRECTORY TABLE COMPONENTS WITH DISTINCT ROW & COLUMN SEPARATION */
    .org-table-container {
        background: var(--org-card-bg);
        border: 1px solid #cbd5e1 !important;
        border-radius: var(--org-radius-lg);
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.06) !important;
        overflow-x: auto !important;
        margin-bottom: 24px;
        -webkit-overflow-scrolling: touch;
    }

    .org-table {
        width: 100% !important;
        min-width: 1280px !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        font-family: "Plus Jakarta Sans", "Inter", -apple-system, BlinkMacSystemFont, sans-serif !important;
    }

    .org-table th {
        background: #f1f5f9 !important;
        padding: 13px 16px !important;
        font-size: 0.72rem !important;
        font-weight: 800 !important;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        border-bottom: 2px solid #cbd5e1 !important;
        border-right: 1px solid #e2e8f0 !important;
        text-align: left;
        white-space: nowrap !important;
    }

    .org-table th:last-child {
        border-right: none !important;
    }

    .org-table td {
        padding: 13px 16px !important;
        font-size: 0.86rem !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        -webkit-text-fill-color: #1e293b !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-right: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        background: #ffffff;
        transition: background 0.15s ease;
        white-space: nowrap !important;
    }

    .org-table td:last-child {
        border-right: none !important;
    }

    .org-table tbody tr:nth-child(even) td {
        background: #f8fafc !important;
    }

    .org-table tbody tr:hover td {
        background: #eff6ff !important;
    }

    .org-emp-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .org-emp-avatar {
        width: 42px !important;
        height: 42px !important;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: linear-gradient(135deg, #0f744c, #094c32);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: grid;
        place-items: center;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    }

    .org-emp-name {
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        text-decoration: none;
        display: block;
        transition: color 0.2s ease;
    }

    .org-emp-name:hover {
        color: var(--org-emerald-main) !important;
        -webkit-text-fill-color: var(--org-emerald-main) !important;
    }

    .org-emp-role {
        font-size: 0.76rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        margin-top: 1px;
    }

    .org-id-badge {
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace !important;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        background: #f1f5f9 !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        display: inline-block;
        letter-spacing: 0.02em;
    }

    .org-dept-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        color: #0369a1 !important;
        -webkit-text-fill-color: #0369a1 !important;
        background: #f0f9ff !important;
        padding: 4px 12px !important;
        border-radius: 999px !important;
        border: 1px solid #bae6fd !important;
    }

    .org-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 999px !important;
        text-transform: capitalize;
    }

    .org-status-pill.active { background: #ecfdf5 !important; color: #047857 !important; -webkit-text-fill-color: #047857 !important; border: 1px solid #a7f3d0 !important; }
    .org-status-pill.inactive { background: #f1f5f9 !important; color: #475569 !important; -webkit-text-fill-color: #475569 !important; border: 1px solid #cbd5e1 !important; }
    .org-status-pill.on_leave { background: #fffbeb !important; color: #b45309 !important; -webkit-text-fill-color: #b45309 !important; border: 1px solid #fde68a !important; }
    .org-status-pill.suspended { background: #fff1f2 !important; color: #be123c !important; -webkit-text-fill-color: #be123c !important; border: 1px solid #fecdd3 !important; }

    .org-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* GRID CARDS VIEW */
    .org-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
        gap: 24px;
        margin-bottom: 28px;
    }

    .org-grid-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    .org-grid-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px -4px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    .org-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .org-grid-card .org-emp-cell {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .org-grid-card .org-emp-avatar {
        width: 52px !important;
        height: 52px !important;
        border-radius: 14px !important;
        object-fit: cover !important;
        flex-shrink: 0 !important;
        background: linear-gradient(135deg, #0f744c, #094c32) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: grid !important;
        place-items: center !important;
        font-weight: 800 !important;
        font-size: 1.2rem !important;
        box-shadow: 0 4px 12px rgba(15, 116, 76, 0.15) !important;
    }

    .org-grid-card .org-emp-name {
        font-family: "Plus Jakarta Sans", "Inter", sans-serif !important;
        font-weight: 700 !important;
        font-size: 1.02rem !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        text-decoration: none !important;
        display: block !important;
        line-height: 1.25 !important;
        transition: color 0.2s ease !important;
    }

    .org-grid-card .org-emp-name:hover {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
    }

    .org-grid-card .org-emp-role {
        font-size: 0.84rem !important;
        font-weight: 500 !important;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        margin-top: 2px !important;
        margin-bottom: 6px !important;
    }

    .org-card-meta {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px 0;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 16px;
    }

    .org-card-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.88rem;
    }

    .org-meta-label {
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        font-weight: 500 !important;
        font-size: 0.88rem !important;
    }

    .org-meta-val {
        font-weight: 700 !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
    }

    .org-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
    }

    .org-email-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #7c3aed !important;
        -webkit-text-fill-color: #7c3aed !important;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .org-email-link i {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        font-size: 1rem;
    }

    .org-email-link:hover {
        color: #6d28d9 !important;
        -webkit-text-fill-color: #6d28d9 !important;
    }

    .org-card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .org-action-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.88rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .org-action-icon-btn:hover {
        background: #f8fafc;
        border-color: #0f744c;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(15, 116, 76, 0.15);
    }

    /* ORGANIZATION STRUCTURE TREE VIEW */
    .org-tree-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        margin-bottom: 28px;
    }

    .org-tree-dept-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .org-tree-dept-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 12px;
    }

    .org-tree-dept-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .org-tree-dept-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #e4f3eb 0%, #c6ebd7 100%);
        color: #0f744c;
        display: grid;
        place-items: center;
        font-size: 1.25rem;
        font-weight: 700;
        box-shadow: inset 0 0 0 1px rgba(15, 116, 76, 0.15);
    }

    .org-tree-dept-name {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-family: "Plus Jakarta Sans", sans-serif;
    }

    .org-tree-dept-parent {
        font-size: 0.82rem;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        font-weight: 500;
        margin-top: 2px;
        display: inline-block;
    }

    .org-tree-count-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 16px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        font-size: 0.82rem;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }

    .org-tree-member-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
    }

    .org-tree-member-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .org-tree-member-card:hover {
        background: #ffffff;
        border-color: #a7f3d0;
        box-shadow: 0 4px 16px rgba(15, 116, 76, 0.1);
        transform: translateY(-2px);
    }

    .org-tree-member-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .org-tree-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .org-tree-avatar-initials {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f744c, #094c32);
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: grid;
        place-items: center;
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .org-tree-member-name {
        font-weight: 700 !important;
        font-size: 0.92rem !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        text-decoration: none;
        display: block;
        transition: color 0.2s ease;
    }

    .org-tree-member-name:hover {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
    }

    .org-tree-member-role {
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        margin-top: 2px;
    }

    .org-tree-member-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
        flex-wrap: wrap;
    }

    .org-tree-manager-chip {
        font-size: 0.74rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 1px 7px;
        border-radius: 6px;
    }

    /* SLIDE-OVER EMPLOYEE DRAWER & BACKDROP */
    .org-drawer-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.55) !important;
        backdrop-filter: blur(4px) !important;
        z-index: 1050 !important;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .org-drawer-backdrop.active {
        display: block !important;
        opacity: 1 !important;
    }

    .org-drawer {
        position: fixed !important;
        top: 0 !important;
        right: -480px !important;
        width: 460px !important;
        max-width: 90vw !important;
        height: 100vh !important;
        background: #ffffff !important;
        box-shadow: -10px 0 35px rgba(15, 23, 42, 0.2) !important;
        z-index: 1055 !important;
        display: flex !important;
        flex-direction: column !important;
        transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        overflow: hidden !important;
    }

    .org-drawer.active {
        right: 0 !important;
    }

    .org-drawer-header {
        background: linear-gradient(135deg, #094c32 0%, #0f744c 100%) !important;
        padding: 24px 24px 20px 24px !important;
        color: #ffffff !important;
        position: relative !important;
    }

    .org-drawer-close {
        position: absolute !important;
        top: 16px !important;
        right: 16px !important;
        width: 34px !important;
        height: 34px !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: grid !important;
        place-items: center !important;
        cursor: pointer !important;
        font-size: 0.95rem !important;
        transition: background 0.2s ease !important;
    }

    .org-drawer-close:hover {
        background: rgba(255, 255, 255, 0.35) !important;
    }

    .org-drawer-profile {
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
    }

    .org-drawer-avatar {
        width: 64px !important;
        height: 64px !important;
        border-radius: 16px !important;
        object-fit: cover !important;
        border: 3px solid rgba(255, 255, 255, 0.4) !important;
        background: linear-gradient(135deg, #0f744c, #094c32) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: grid !important;
        place-items: center !important;
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        flex-shrink: 0 !important;
    }

    .org-drawer-tabs {
        display: flex !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 12px 0 12px !important;
        gap: 6px !important;
    }

    .org-drawer-tab {
        padding: 10px 14px !important;
        font-size: 0.84rem !important;
        font-weight: 700 !important;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        border-radius: 6px 6px 0 0 !important;
    }

    .org-drawer-tab:hover {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
    }

    .org-drawer-tab.active {
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        border-bottom-color: #0f744c !important;
        background: #ffffff !important;
    }

    .org-drawer-body {
        flex: 1 !important;
        padding: 20px 24px !important;
        overflow-y: auto !important;
        background: #f8fafc !important;
    }

    .org-drawer-section {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 18px !important;
        margin-bottom: 16px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    }

    .org-drawer-section-title {
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        color: #0f744c !important;
        -webkit-text-fill-color: #0f744c !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 12px !important;
        padding-bottom: 8px !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .org-info-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }

    .org-info-card {
        background: #f8fafc !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 8px !important;
        padding: 10px 12px !important;
    }

    .org-info-card span {
        display: block !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        text-transform: uppercase !important;
        margin-bottom: 4px !important;
    }

    .org-info-card strong {
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        word-break: break-all !important;
    }

    /* RESPONSIVE BREAKPOINTS */
    @media (max-width: 1400px) {
        .org-stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .org-page { padding: 16px; }
        .org-header-card { flex-direction: column; align-items: flex-start; }
        .org-header-actions { width: 100%; justify-content: flex-start; }
        .org-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .org-toolbar-form { flex-direction: column; align-items: stretch; }
        .org-view-toggle { margin-left: 0; width: 100%; justify-content: center; }
        .org-profile-layout { grid-template-columns: 1fr; }
        .org-banner-content { flex-direction: column; align-items: flex-start; }

        .org-profile-data-table, 
        .org-profile-data-table tbody, 
        .org-profile-data-table tr, 
        .org-profile-data-table td {
            display: block;
            width: 100% !important;
        }
        .org-data-label {
            border-right: none;
            background: #f1f5f9;
            padding-top: 10px;
            padding-bottom: 4px;
        }
        .org-data-value {
            padding-top: 4px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
    }

    @media (max-width: 576px) {
        .org-stats-grid { grid-template-columns: 1fr; }
        .org-table { display: block; overflow-x: auto; }
        .org-profile-user-group { flex-direction: column; align-items: flex-start; }
    }
</style>
