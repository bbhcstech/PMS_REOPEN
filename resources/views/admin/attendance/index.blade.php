@extends('admin.layout.app')

@section('title', 'Employee Attendance Dashboard')

@section('content')
<style>
    :root {
        --primary-blue: #2F6BFF;
        --primary-hover: #1E4FCC;
        --primary-purple: #8B5CF6;
        --primary-cyan: #22D3EE;
        --primary-accent: #22D3EE;
        --primary-green: #10B981;
        --bg-light: #f8fafc;
        --glass-border: rgba(47, 107, 255, 0.12);
        --card-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
            0px 8px 40px rgba(0, 0, 0, 0.04),
            0px 20px 60px rgba(47, 107, 255, 0.06);
        --card-shadow-hover: 0px 20px 50px rgba(0, 0, 0, 0.08),
            0px 30px 80px rgba(47, 107, 255, 0.12);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spring-transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* ===== MAIN CONTAINER ===== */
    .attendance-container {
        background: linear-gradient(135deg, #f6f8fe 0%, #f0f4ff 50%, #f5f3ff 100%);
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        position: relative;
        overflow: hidden;
    }

    /* Ambient Orbs */
    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(47, 107, 255, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    .orb-3 {
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(34, 211, 238, 0.08) 0%, transparent 70%);
        animation: orbFloat 18s ease-in-out infinite;
        transform: translate(-50%, -50%);
    }

    @keyframes orbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -30px) scale(1.05); }
        66% { transform: translate(-30px, 40px) scale(0.95); }
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    /* Breadcrumbs */
    .breadcrumb-custom,
    .breadcrumb-custom span {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
    }
    .breadcrumb-custom a {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    /* ===== HEADER CARD ===== */
    .header-card {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideDown 0.6s ease;
        transition: var(--spring-transition);
    }

    .header-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(47, 107, 255, 0.25);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-title h1 {
        font-size: 2.28rem;
        font-weight: 800;
        background: linear-gradient(135deg, #2F6BFF, #8B5CF6, #22D3EE);
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: transparent;
        margin-bottom: 0.25rem;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        font-size: 1.12rem;
        font-weight: 500;
        margin: 0;
    }

    .header-title p i {
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
    }

    .btn-add {
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC) !important;
        color: white !important;
        -webkit-text-fill-color: white !important;
        padding: 0.7rem 1.6rem;
        border-radius: 40px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 600;
        font-size: 1.05rem;
        transition: var(--spring-transition);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(47, 107, 255, 0.25);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(47, 107, 255, 0.35);
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
    }

    .btn-archive {
        background: #EEF2FF !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        padding: 0.7rem 1.35rem;
        border-radius: 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 1rem;
        transition: var(--spring-transition);
        border: 1px solid rgba(47, 107, 255, 0.25) !important;
    }

    .btn-archive *,
    .btn-archive i,
    .btn-archive svg,
    .btn-archive [class*="fa"],
    .btn-archive span {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .btn-archive:hover {
        background: #E0E7FF !important;
        color: #1E4FCC !important;
        -webkit-text-fill-color: #1E4FCC !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(47, 107, 255, 0.35);
    }

    .archive-count-badge {
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #ef4444 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.45rem;
        font-size: 0.84rem;
        font-weight: 800;
    }

    /* ===== STATS CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card,
    .attendance-container .stat-card,
    .attendance-container .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 1.5rem;
        transition: var(--spring-transition);
        border: 1px solid var(--glass-border) !important;
        box-shadow: var(--card-shadow) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        animation: cardStagger 0.6s ease forwards;
        opacity: 0;
        position: relative;
        overflow: hidden;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
    }

    .stat-card h3,
    .stat-info h3,
    .attendance-container .stat-card h3,
    .attendance-container .stat-card:first-of-type h3 {
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
    }

    .stat-card h6,
    .stat-info h6,
    .attendance-container .stat-card h6,
    .attendance-container .stat-card span,
    .attendance-container .stat-card:first-of-type span,
    .attendance-container .stat-card:first-of-type h6 {
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-blue), var(--primary-purple));
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.2s; }

    @keyframes cardStagger {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: var(--card-shadow-hover) !important;
        border-color: rgba(47, 107, 255, 0.25) !important;
    }

    .stat-icon {
        width: 62px;
        height: 62px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.65rem;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
    }

    .stat-icon.total,
    .attendance-container .stat-card .stat-icon.total,
    .attendance-container .stat-card:first-of-type .stat-icon.total {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
        color: #1d4ed8 !important;
        -webkit-text-fill-color: #1d4ed8 !important;
    }

    .stat-icon.month,
    .attendance-container .stat-card .stat-icon.month,
    .attendance-container .stat-card:first-of-type .stat-icon.month {
        background: linear-gradient(135deg, #fef3c7, #fed7aa) !important;
        color: #c2410c !important;
        -webkit-text-fill-color: #c2410c !important;
    }

    .stat-icon.year,
    .attendance-container .stat-card .stat-icon.year,
    .attendance-container .stat-card:first-of-type .stat-icon.year {
        background: linear-gradient(135deg, #ede9fe, #ddd6fe) !important;
        color: #6d28d9 !important;
        -webkit-text-fill-color: #6d28d9 !important;
    }

    .stat-icon.days,
    .attendance-container .stat-card .stat-icon.days,
    .attendance-container .stat-card:first-of-type .stat-icon.days {
        background: linear-gradient(135deg, #ffe4e6, #fecdd3) !important;
        color: #be123c !important;
        -webkit-text-fill-color: #be123c !important;
    }

    .stat-icon i {
        color: inherit !important;
        -webkit-text-fill-color: inherit !important;
    }

    .stat-info {
        flex: 1;
        min-width: 0;
    }

    .stat-info h6 {
        font-size: 0.94rem;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        margin-bottom: 0.28rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .stat-info h3 {
        font-size: 2.22rem;
        font-weight: 800;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        margin: 0;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    /* ===== FILTER CARD ===== */
    .filter-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .filter-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .filter-header h6 {
        font-weight: 700;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        margin: 0;
        font-size: 1.24rem;
    }

    .filter-header h6 i {
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
        margin-right: 0.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .filter-group label {
        font-size: 0.92rem;
        font-weight: 700;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .filter-group select,
    .filter-group input {
        padding: 0.6rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1.08rem;
        font-weight: 500;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        background: white !important;
        transition: var(--transition-smooth);
        outline: none;
        min-height: 44px;
        width: 100%;
    }

    .filter-group select option {
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        background: white !important;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(47, 107, 255, 0.15);
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-filter {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-hover)) !important;
        color: white !important;
        -webkit-text-fill-color: white !important;
        padding: 0.6rem 1.5rem;
        border-radius: 40px;
        border: none;
        font-weight: 700;
        font-size: 1.05rem;
        cursor: pointer;
        transition: var(--spring-transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 44px;
        box-shadow: 0 4px 15px rgba(47, 107, 255, 0.25);
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(47, 107, 255, 0.35);
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    .btn-reset {
        background: linear-gradient(135deg, #64748b, #475569) !important;
        color: white !important;
        -webkit-text-fill-color: white !important;
        padding: 0.6rem 1.5rem;
        border-radius: 40px;
        border: 1.5px solid #cbd5e1 !important;
        font-weight: 700;
        font-size: 1.05rem;
        cursor: pointer;
        transition: var(--spring-transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 44px;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .btn-reset:hover,
    .attendance-container .btn-reset:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        border-color: #94a3b8 !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    /* ===== NAV TABS ===== */
    .tabs-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 0.75rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .tabs-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .tabs-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
    }

    .nav-tab-btn,
    .attendance-container .nav-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.5rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 1.05rem;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        background: #f1f5f9;
        text-decoration: none;
        transition: var(--spring-transition);
        border: 2px solid #e2e8f0 !important;
    }

    .nav-tab-btn:hover {
        background: #e2e8f0;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        transform: translateY(-2px);
    }

    .nav-tab-btn.active {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-hover)) !important;
        color: white !important;
        -webkit-text-fill-color: white !important;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(47, 107, 255, 0.25);
    }

    .nav-tab-btn i {
        font-size: 1rem;
        color: inherit !important;
        -webkit-text-fill-color: inherit !important;
    }

    /* ===== LEGEND CARD ===== */
    .legend-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .legend-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .legend-title {
        font-weight: 700;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }

    .legend-title i {
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
        margin-right: 0.5rem;
    }

    .legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.9rem;
        background: #f8fafc;
        border-radius: 30px;
        border: 1px solid #e2e8f0;
        transition: var(--transition-smooth);
        cursor: pointer;
        user-select: none;
    }

    .legend-item:hover {
        transform: translateY(-2px);
        border-color: var(--primary-blue);
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.15);
    }

    .legend-item.active {
        border-color: var(--primary-blue) !important;
        background: rgba(47, 107, 255, 0.08) !important;
        box-shadow: 0 4px 16px rgba(47, 107, 255, 0.2) !important;
        transform: translateY(-2px);
    }

    .legend-item.active .legend-text {
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
        font-weight: 700;
    }

    /* Hide rows not matching active legend filter */
    .attendance-table tbody tr.legend-filtered-out {
        display: none !important;
    }
    .attendance-table tbody tr.legend-filtered-in {
        opacity: 1 !important;
    }

    html[data-pms-theme="dark"] .legend-item {
        background: #0F1530 !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
    html[data-pms-theme="dark"] .legend-item .legend-text {
        color: #E2E8F0 !important;
        -webkit-text-fill-color: #E2E8F0 !important;
    }
    html[data-pms-theme="dark"] .legend-item.active {
        background: rgba(47, 107, 255, 0.3) !important;
        border-color: #3b82f6 !important;
    }

    .legend-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        font-size: 13px;
        color: white !important;
        -webkit-text-fill-color: white !important;
        flex-shrink: 0;
    }

    .legend-icon i {
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    .legend-icon.present { background: var(--primary-green); }
    .legend-icon.absent { background: #ef4444; }
    .legend-icon.late { background: #f59e0b; }
    .legend-icon.halfday { background: #8b5cf6; }
    .legend-icon.holiday { background: #e67e22; }
    .legend-icon.dayoff { background: #3b82f6; }
    .legend-icon.leave { background: #06b6d4; }
    .legend-icon.wfh { background: #14b8a6; }

    .legend-text {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
    }

    /* ===== TABLE CARD ===== */
    .table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .table-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .table-header h6 {
        font-weight: 700;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        margin: 0;
        font-size: 1.25rem;
    }

    .table-header h6 i {
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
        margin-right: 0.5rem;
    }

    .attendance-table-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .btn-export-menu {
        min-height: 38px;
        border-radius: 16px;
        padding: 0.65rem 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 1rem;
        font-weight: 800;
        transition: var(--spring-transition);
        background: #F8FAFC !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        border: 1px solid rgba(47, 107, 255, 0.2);
        box-shadow: none;
    }

    .btn-export-menu:hover,
    .btn-export-menu:focus {
        background: #EEF2FF !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        border-color: #60A5FA;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(47, 107, 255, 0.25);
    }

    .attendance-export-dropdown {
        position: relative;
        z-index: 60;
    }

    .attendance-table-actions .dropdown-menu {
        min-width: 230px;
        margin-top: 10px !important;
        background: #ffffff !important;
        border: 1px solid rgba(47, 107, 255, 0.18);
        border-radius: 16px;
        padding: 10px;
        box-shadow: 0 24px 48px -14px rgba(15, 23, 42, 0.28);
        z-index: 9999 !important;
    }

    .attendance-table-actions .dropdown-menu.show {
        display: block;
        visibility: visible;
        opacity: 1;
    }

    .attendance-table-actions .dropdown-item {
        width: 100%;
        min-height: 44px;
        border-radius: 12px;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #374151 !important;
        -webkit-text-fill-color: #374151 !important;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .attendance-table-actions .dropdown-item i {
        width: 20px;
        font-size: 1rem;
        color: inherit !important;
        -webkit-text-fill-color: inherit !important;
    }

    .attendance-table-actions .dropdown-item:hover {
        background: #EEF2FF !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    .attendance-table-actions .dropdown-divider {
        margin: 8px 0;
        border-color: rgba(47, 107, 255, 0.16);
    }

    .attendance-page-export-buttons {
        position: absolute;
        left: -9999px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
        padding: 1rem 1.5rem 1.5rem;
    }

    .attendance-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.4rem;
        min-width: 900px;
    }

    .attendance-table thead th {
        padding: 0.75rem 0.8rem;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        font-weight: 700;
        font-size: 0.96rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        background: transparent;
    }

    .attendance-table thead th i {
        margin-right: 4px;
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
    }

    .attendance-table tbody tr {
        background: white;
        border-radius: 14px;
        transition: var(--transition-smooth);
        animation: rowFade 0.4s ease forwards;
        opacity: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .attendance-table tbody tr:hover {
        background: #f8fafc;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    }

    @keyframes rowFade {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .attendance-table td {
        padding: 0.7rem 0.8rem;
        color: #1e293b !important;
        -webkit-text-fill-color: #1e293b !important;
        font-size: 1.08rem;
        vertical-align: middle;
        border-top: 1px solid transparent;
        border-bottom: 1px solid transparent;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-purple));
        color: white !important;
        -webkit-text-fill-color: white !important;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .employee-name {
        font-weight: 700;
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-size: 1.12rem;
    }

    .employee-dept {
        font-size: 0.98rem;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        font-weight: 500;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        font-size: 16px;
        color: white !important;
        -webkit-text-fill-color: white !important;
        transition: var(--transition-smooth);
        cursor: default;
    }

    .status-badge i {
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    .status-badge:hover {
        transform: scale(1.1);
    }

    .status-present { background: var(--primary-green); }
    .status-absent { background: #ef4444; }
    .status-late { background: #f59e0b; }
    .status-halfday { background: #8b5cf6; }
    .status-holiday { background: #e67e22; }
    .status-dayoff { background: #3b82f6; }

    /* ===== FOOTER ===== */
    .footer-note {
        margin-top: 1.5rem;
        text-align: center;
        padding: 1rem;
    }

    .footer-note p {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .footer-note p i {
        color: var(--primary-blue) !important;
        -webkit-text-fill-color: var(--primary-blue) !important;
        margin-right: 0.5rem;
    }

    /* ===== LOADING ===== */
    .loading-overlay {
        text-align: center;
        padding: 3rem 2rem;
    }

    .loading-spinner {
        display: inline-block;
        width: 48px;
        height: 48px;
        border: 4px solid #e2e8f0;
        border-top-color: var(--primary-blue);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-text {
        margin-top: 0.75rem;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        font-weight: 500;
    }

    /* Select2 High-Contrast Styling */
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single {
        background-color: #ffffff !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 12px !important;
        min-height: 44px !important;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered,
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-size: 1.05rem !important;
        font-weight: 600 !important;
        line-height: 40px !important;
    }
    .select2-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        z-index: 9999 !important;
    }
    .select2-results__option {
        color: #0f172a !important;
        -webkit-text-fill-color: #0f172a !important;
        font-size: 1rem !important;
    }

    /* Select2 Dark Mode */
    html[data-pms-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
    html[data-pms-theme="dark"] .select2-container--default .select2-selection--single,
    [data-pms-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
    [data-pms-theme="dark"] .select2-container--default .select2-selection--single,
    .dark-mode .select2-container--bootstrap-5 .select2-selection,
    .dark-mode .select2-container--default .select2-selection--single {
        background-color: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered,
    html[data-pms-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered,
    [data-pms-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered,
    [data-pms-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered,
    .dark-mode .select2-container--bootstrap-5 .select2-selection__rendered,
    .dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .select2-dropdown,
    [data-pms-theme="dark"] .select2-dropdown,
    .dark-mode .select2-dropdown {
        background-color: #0F1530 !important;
        border: 1px solid rgba(238, 241, 251, 0.14) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.6) !important;
    }
    html[data-pms-theme="dark"] .select2-search__field,
    [data-pms-theme="dark"] .select2-search__field,
    .dark-mode .select2-search__field {
        background-color: #141B3D !important;
        border: 1px solid rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .select2-results__option,
    [data-pms-theme="dark"] .select2-results__option,
    .dark-mode .select2-results__option {
        background-color: transparent !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .select2-results__option--highlighted,
    [data-pms-theme="dark"] .select2-results__option--highlighted,
    .dark-mode .select2-results__option--highlighted {
        background-color: #2F6BFF !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    html[data-pms-theme="dark"] .select2-results__option[aria-selected="true"],
    [data-pms-theme="dark"] .select2-results__option[aria-selected="true"],
    .dark-mode .select2-results__option[aria-selected="true"] {
        background-color: #1A2247 !important;
        color: #22D3EE !important;
        -webkit-text-fill-color: #22D3EE !important;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .attendance-container {
            padding: 1.5rem 1.25rem;
        }
        .header-card {
            padding: 1.5rem;
        }
        .header-title h1 {
            font-size: 1.6rem;
        }
        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .attendance-container {
            padding: 1rem;
        }
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 0.9rem;
        }
        .header-card,
        .filter-card,
        .tabs-card,
        .legend-card {
            flex-direction: column;
            align-items: stretch;
            padding: 1.25rem;
        }
        .header-title {
            text-align: center;
        }
        .filter-grid {
            grid-template-columns: 1fr;
        }
        .filter-actions {
            flex-direction: column;
            width: 100%;
        }
        .btn-filter,
        .btn-reset {
            width: 100%;
            justify-content: center;
        }
        .tabs-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
        .nav-tab-btn {
            justify-content: center;
        }
        .legend-items {
            justify-content: center;
        }
        .table-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .table-responsive {
            padding: 0 0.75rem 0.75rem;
        }
        .attendance-table td,
        .attendance-table th {
            padding: 0.5rem 0.6rem;
            font-size: 0.88rem;
        }
        .employee-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }
        .employee-name {
            font-size: 0.95rem;
        }
        .status-badge {
            width: 24px;
            height: 24px;
            font-size: 12px;
        }
        .stat-card {
            padding: 1.25rem;
        }
        .stat-info h3 {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 576px) {
        .header-title h1 {
            font-size: 1.3rem;
        }
        .header-title p {
            font-size: 0.95rem;
        }
        .btn-add {
            width: 100%;
            justify-content: center;
        }
        .legend-item {
            padding: 0.25rem 0.7rem;
        }
        .legend-text {
            font-size: 0.82rem;
        }
        .attendance-table-actions .dropdown-menu {
            right: auto !important;
            left: 0 !important;
            max-width: calc(100vw - 48px);
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-container,
    html[data-theme="dark"] .attendance-container,
    html[data-bs-theme="dark"] .attendance-container,
    [data-pms-theme="dark"] .attendance-container,
    [data-theme="dark"] .attendance-container,
    body.dark-mode .attendance-container {
        background: #070B1A !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-theme="dark"] .header-card,
    html[data-bs-theme="dark"] .header-card,
    [data-pms-theme="dark"] .header-card,
    [data-theme="dark"] .header-card,
    body.dark-mode .header-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-theme="dark"] .stat-card,
    html[data-bs-theme="dark"] .stat-card,
    [data-pms-theme="dark"] .stat-card,
    [data-theme="dark"] .stat-card,
    body.dark-mode .stat-card,
    html[data-pms-theme="dark"] .attendance-container .stat-card,
    html[data-theme="dark"] .attendance-container .stat-card,
    html[data-bs-theme="dark"] .attendance-container .stat-card,
    [data-pms-theme="dark"] .attendance-container .stat-card,
    [data-theme="dark"] .attendance-container .stat-card,
    body.dark-mode .attendance-container .stat-card,
    html[data-pms-theme="dark"] .filter-card,
    html[data-theme="dark"] .filter-card,
    html[data-bs-theme="dark"] .filter-card,
    [data-pms-theme="dark"] .filter-card,
    [data-theme="dark"] .filter-card,
    body.dark-mode .filter-card,
    html[data-pms-theme="dark"] .tabs-card,
    html[data-theme="dark"] .tabs-card,
    html[data-bs-theme="dark"] .tabs-card,
    [data-pms-theme="dark"] .tabs-card,
    [data-theme="dark"] .tabs-card,
    body.dark-mode .tabs-card,
    html[data-pms-theme="dark"] .legend-card,
    html[data-theme="dark"] .legend-card,
    html[data-bs-theme="dark"] .legend-card,
    [data-pms-theme="dark"] .legend-card,
    [data-theme="dark"] .legend-card,
    body.dark-mode .legend-card,
    html[data-pms-theme="dark"] .table-card,
    html[data-theme="dark"] .table-card,
    html[data-bs-theme="dark"] .table-card,
    [data-pms-theme="dark"] .table-card,
    [data-theme="dark"] .table-card,
    body.dark-mode .table-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        color: #CBD5E1 !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .header-title h1,
    html[data-theme="dark"] .header-title h1,
    html[data-bs-theme="dark"] .header-title h1,
    [data-pms-theme="dark"] .header-title h1,
    [data-theme="dark"] .header-title h1,
    body.dark-mode .header-title h1 {
        background: linear-gradient(135deg, #60a5fa, #38bdf8, #2F6BFF) !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: transparent !important;
    }

    html[data-pms-theme="dark"] .header-title p,
    html[data-theme="dark"] .header-title p,
    html[data-bs-theme="dark"] .header-title p,
    [data-pms-theme="dark"] .header-title p,
    [data-theme="dark"] .header-title p,
    body.dark-mode .header-title p {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .stat-info h6,
    html[data-theme="dark"] .stat-info h6,
    html[data-bs-theme="dark"] .stat-info h6,
    [data-pms-theme="dark"] .stat-info h6,
    [data-theme="dark"] .stat-info h6,
    body.dark-mode .stat-info h6,
    html[data-pms-theme="dark"] .filter-header h6,
    html[data-theme="dark"] .filter-header h6,
    html[data-bs-theme="dark"] .filter-header h6,
    [data-pms-theme="dark"] .filter-header h6,
    [data-theme="dark"] .filter-header h6,
    body.dark-mode .filter-header h6,
    html[data-pms-theme="dark"] .table-header h6,
    html[data-theme="dark"] .table-header h6,
    html[data-bs-theme="dark"] .table-header h6,
    [data-pms-theme="dark"] .table-header h6,
    [data-theme="dark"] .table-header h6,
    body.dark-mode .table-header h6,
    html[data-pms-theme="dark"] .legend-title,
    html[data-theme="dark"] .legend-title,
    html[data-bs-theme="dark"] .legend-title,
    [data-pms-theme="dark"] .legend-title,
    [data-theme="dark"] .legend-title,
    body.dark-mode .legend-title,
    html[data-pms-theme="dark"] .attendance-container .stat-card h6,
    html[data-theme="dark"] .attendance-container .stat-card h6,
    html[data-bs-theme="dark"] .attendance-container .stat-card h6,
    [data-pms-theme="dark"] .attendance-container .stat-card h6,
    [data-theme="dark"] .attendance-container .stat-card h6,
    body.dark-mode .attendance-container .stat-card h6 {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .stat-info h3,
    html[data-theme="dark"] .stat-info h3,
    html[data-bs-theme="dark"] .stat-info h3,
    [data-pms-theme="dark"] .stat-info h3,
    [data-theme="dark"] .stat-info h3,
    body.dark-mode .stat-info h3,
    html[data-pms-theme="dark"] .attendance-container .stat-card h3,
    html[data-theme="dark"] .attendance-container .stat-card h3,
    html[data-bs-theme="dark"] .attendance-container .stat-card h3,
    [data-pms-theme="dark"] .attendance-container .stat-card h3,
    [data-theme="dark"] .attendance-container .stat-card h3,
    body.dark-mode .attendance-container .stat-card h3 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .filter-group label,
    html[data-theme="dark"] .filter-group label,
    html[data-bs-theme="dark"] .filter-group label,
    [data-pms-theme="dark"] .filter-group label,
    [data-theme="dark"] .filter-group label,
    body.dark-mode .filter-group label {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .filter-group select,
    html[data-theme="dark"] .filter-group select,
    html[data-bs-theme="dark"] .filter-group select,
    [data-pms-theme="dark"] .filter-group select,
    [data-theme="dark"] .filter-group select,
    body.dark-mode .filter-group select,
    html[data-pms-theme="dark"] .filter-group input,
    html[data-theme="dark"] .filter-group input,
    html[data-bs-theme="dark"] .filter-group input,
    [data-pms-theme="dark"] .filter-group input,
    [data-theme="dark"] .filter-group input,
    body.dark-mode .filter-group input {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .filter-group select option,
    html[data-theme="dark"] .filter-group select option,
    html[data-bs-theme="dark"] .filter-group select option,
    [data-pms-theme="dark"] .filter-group select option,
    [data-theme="dark"] .filter-group select option,
    body.dark-mode .filter-group select option {
        background: #141B3D !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .nav-tab-btn,
    html[data-theme="dark"] .nav-tab-btn,
    html[data-bs-theme="dark"] .nav-tab-btn,
    [data-pms-theme="dark"] .nav-tab-btn,
    [data-theme="dark"] .nav-tab-btn,
    body.dark-mode .nav-tab-btn {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        border-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .nav-tab-btn:hover,
    html[data-theme="dark"] .nav-tab-btn:hover,
    html[data-bs-theme="dark"] .nav-tab-btn:hover,
    [data-pms-theme="dark"] .nav-tab-btn:hover,
    [data-theme="dark"] .nav-tab-btn:hover,
    body.dark-mode .nav-tab-btn:hover {
        background: #1A2247 !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .nav-tab-btn.active,
    html[data-theme="dark"] .nav-tab-btn.active,
    html[data-bs-theme="dark"] .nav-tab-btn.active,
    [data-pms-theme="dark"] .nav-tab-btn.active,
    [data-theme="dark"] .nav-tab-btn.active,
    body.dark-mode .nav-tab-btn.active {
        background: #2F6BFF !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border-color: #2F6BFF !important;
    }

    html[data-pms-theme="dark"] .legend-item,
    html[data-theme="dark"] .legend-item,
    html[data-bs-theme="dark"] .legend-item,
    [data-pms-theme="dark"] .legend-item,
    [data-theme="dark"] .legend-item,
    body.dark-mode .legend-item {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .legend-text,
    html[data-theme="dark"] .legend-text,
    html[data-bs-theme="dark"] .legend-text,
    [data-pms-theme="dark"] .legend-text,
    [data-theme="dark"] .legend-text,
    body.dark-mode .legend-text {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .attendance-table tbody tr,
    html[data-theme="dark"] .attendance-table tbody tr,
    html[data-bs-theme="dark"] .attendance-table tbody tr,
    [data-pms-theme="dark"] .attendance-table tbody tr,
    [data-theme="dark"] .attendance-table tbody tr,
    body.dark-mode .attendance-table tbody tr {
        background: #141B3D !important;
    }

    html[data-pms-theme="dark"] .attendance-table tbody tr:hover,
    html[data-theme="dark"] .attendance-table tbody tr:hover,
    html[data-bs-theme="dark"] .attendance-table tbody tr:hover,
    [data-pms-theme="dark"] .attendance-table tbody tr:hover,
    [data-theme="dark"] .attendance-table tbody tr:hover,
    body.dark-mode .attendance-table tbody tr:hover {
        background: #1A2247 !important;
    }

    html[data-pms-theme="dark"] .attendance-table thead th,
    html[data-theme="dark"] .attendance-table thead th,
    html[data-bs-theme="dark"] .attendance-table thead th,
    [data-pms-theme="dark"] .attendance-table thead th,
    [data-theme="dark"] .attendance-table thead th,
    body.dark-mode .attendance-table thead th {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
        border-color: rgba(238, 241, 251, 0.08) !important;
    }

    html[data-pms-theme="dark"] .attendance-table td,
    html[data-theme="dark"] .attendance-table td,
    html[data-bs-theme="dark"] .attendance-table td,
    [data-pms-theme="dark"] .attendance-table td,
    [data-theme="dark"] .attendance-table td,
    body.dark-mode .attendance-table td {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .employee-name,
    html[data-theme="dark"] .employee-name,
    html[data-bs-theme="dark"] .employee-name,
    [data-pms-theme="dark"] .employee-name,
    [data-theme="dark"] .employee-name,
    body.dark-mode .employee-name {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .employee-dept,
    html[data-theme="dark"] .employee-dept,
    html[data-bs-theme="dark"] .employee-dept,
    [data-pms-theme="dark"] .employee-dept,
    [data-theme="dark"] .employee-dept,
    body.dark-mode .employee-dept {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .footer-note p,
    html[data-theme="dark"] .footer-note p,
    html[data-bs-theme="dark"] .footer-note p,
    [data-pms-theme="dark"] .footer-note p,
    [data-theme="dark"] .footer-note p,
    body.dark-mode .footer-note p {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .stat-icon.total,
    html[data-theme="dark"] .stat-icon.total,
    html[data-bs-theme="dark"] .stat-icon.total,
    [data-pms-theme="dark"] .stat-icon.total,
    [data-theme="dark"] .stat-icon.total,
    body.dark-mode .stat-icon.total,
    html[data-pms-theme="dark"] .attendance-container .stat-card:first-of-type .stat-icon.total,
    html[data-theme="dark"] .attendance-container .stat-card:first-of-type .stat-icon.total,
    html[data-bs-theme="dark"] .attendance-container .stat-card:first-of-type .stat-icon.total,
    [data-pms-theme="dark"] .attendance-container .stat-card:first-of-type .stat-icon.total,
    [data-theme="dark"] .attendance-container .stat-card:first-of-type .stat-icon.total,
    body.dark-mode .attendance-container .stat-card:first-of-type .stat-icon.total {
        background: rgba(47, 107, 255, 0.2) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
    }

    html[data-pms-theme="dark"] .stat-icon.month,
    html[data-theme="dark"] .stat-icon.month,
    html[data-bs-theme="dark"] .stat-icon.month,
    [data-pms-theme="dark"] .stat-icon.month,
    [data-theme="dark"] .stat-icon.month,
    body.dark-mode .stat-icon.month {
        background: rgba(245, 158, 11, 0.2) !important;
        color: #FCD34D !important;
        -webkit-text-fill-color: #FCD34D !important;
    }

    html[data-pms-theme="dark"] .stat-icon.year,
    html[data-theme="dark"] .stat-icon.year,
    html[data-bs-theme="dark"] .stat-icon.year,
    [data-pms-theme="dark"] .stat-icon.year,
    [data-theme="dark"] .stat-icon.year,
    body.dark-mode .stat-icon.year {
        background: rgba(139, 92, 246, 0.2) !important;
        color: #C4B5FD !important;
        -webkit-text-fill-color: #C4B5FD !important;
    }

    html[data-pms-theme="dark"] .stat-icon.days,
    html[data-theme="dark"] .stat-icon.days,
    html[data-bs-theme="dark"] .stat-icon.days,
    [data-pms-theme="dark"] .stat-icon.days,
    [data-theme="dark"] .stat-icon.days,
    body.dark-mode .stat-icon.days {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #FCA5A5 !important;
        -webkit-text-fill-color: #FCA5A5 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .btn-export-menu,
    html[data-theme="dark"] .btn-export-menu,
    html[data-bs-theme="dark"] .btn-export-menu,
    [data-pms-theme="dark"] .btn-export-menu,
    [data-theme="dark"] .btn-export-menu,
    body.dark-mode .btn-export-menu {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
    }

    html[data-pms-theme="dark"] .btn-export-menu:hover,
    html[data-theme="dark"] .btn-export-menu:hover,
    html[data-bs-theme="dark"] .btn-export-menu:hover,
    [data-pms-theme="dark"] .btn-export-menu:hover,
    [data-theme="dark"] .btn-export-menu:hover,
    body.dark-mode .btn-export-menu:hover,
    html[data-pms-theme="dark"] .btn-export-menu:focus,
    html[data-theme="dark"] .btn-export-menu:focus,
    html[data-bs-theme="dark"] .btn-export-menu:focus,
    [data-pms-theme="dark"] .btn-export-menu:focus,
    [data-theme="dark"] .btn-export-menu:focus,
    body.dark-mode .btn-export-menu:focus {
        background: #1A2247 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border-color: #2F6BFF !important;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-menu,
    html[data-theme="dark"] .attendance-table-actions .dropdown-menu,
    html[data-bs-theme="dark"] .attendance-table-actions .dropdown-menu,
    [data-pms-theme="dark"] .attendance-table-actions .dropdown-menu,
    [data-theme="dark"] .attendance-table-actions .dropdown-menu,
    body.dark-mode .attendance-table-actions .dropdown-menu {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, 0.7) !important;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-item,
    html[data-theme="dark"] .attendance-table-actions .dropdown-item,
    html[data-bs-theme="dark"] .attendance-table-actions .dropdown-item,
    [data-pms-theme="dark"] .attendance-table-actions .dropdown-item,
    [data-theme="dark"] .attendance-table-actions .dropdown-item,
    body.dark-mode .attendance-table-actions .dropdown-item {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-item:hover,
    html[data-theme="dark"] .attendance-table-actions .dropdown-item:hover,
    html[data-bs-theme="dark"] .attendance-table-actions .dropdown-item:hover,
    [data-pms-theme="dark"] .attendance-table-actions .dropdown-item:hover,
    [data-theme="dark"] .attendance-table-actions .dropdown-item:hover,
    body.dark-mode .attendance-table-actions .dropdown-item:hover {
        background: #141B3D !important;
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-divider,
    html[data-theme="dark"] .attendance-table-actions .dropdown-divider,
    html[data-bs-theme="dark"] .attendance-table-actions .dropdown-divider,
    [data-pms-theme="dark"] .attendance-table-actions .dropdown-divider,
    [data-theme="dark"] .attendance-table-actions .dropdown-divider,
    body.dark-mode .attendance-table-actions .dropdown-divider {
        border-color: rgba(238, 241, 251, 0.08) !important;
    }

    /* Archived Button in Dark Mode */
    html[data-pms-theme="dark"] .btn-archive,
    html[data-theme="dark"] .btn-archive,
    html[data-bs-theme="dark"] .btn-archive,
    body[data-pms-theme="dark"] .btn-archive,
    body[data-theme="dark"] .btn-archive,
    body[data-bs-theme="dark"] .btn-archive,
    [data-pms-theme="dark"] .btn-archive,
    [data-theme="dark"] .btn-archive,
    [data-bs-theme="dark"] .btn-archive,
    .dark-mode .btn-archive,
    html[data-pms-theme="dark"] .attendance-container .btn-archive,
    html[data-theme="dark"] .attendance-container .btn-archive,
    html[data-bs-theme="dark"] .attendance-container .btn-archive,
    body[data-pms-theme="dark"] .attendance-container .btn-archive,
    body[data-theme="dark"] .attendance-container .btn-archive,
    body[data-bs-theme="dark"] .attendance-container .btn-archive,
    [data-pms-theme="dark"] .attendance-container .btn-archive,
    [data-theme="dark"] .attendance-container .btn-archive,
    [data-bs-theme="dark"] .attendance-container .btn-archive,
    .dark-mode .attendance-container .btn-archive {
        background: #141B3D !important;
        border: 1px solid rgba(47, 107, 255, 0.4) !important;
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .btn-archive *,
    html[data-pms-theme="dark"] .btn-archive i,
    html[data-pms-theme="dark"] .btn-archive svg,
    html[data-pms-theme="dark"] .btn-archive [class*="fa"],
    html[data-pms-theme="dark"] .btn-archive span,
    html[data-theme="dark"] .btn-archive i,
    html[data-theme="dark"] .btn-archive svg,
    html[data-theme="dark"] .btn-archive [class*="fa"],
    html[data-bs-theme="dark"] .btn-archive i,
    body[data-pms-theme="dark"] .btn-archive i,
    body[data-theme="dark"] .btn-archive i,
    [data-pms-theme="dark"] .btn-archive i,
    [data-theme="dark"] .btn-archive i,
    .dark-mode .btn-archive i,
    html[data-pms-theme="dark"] .attendance-container .btn-archive i,
    html[data-theme="dark"] .attendance-container .btn-archive i,
    html[data-bs-theme="dark"] .attendance-container .btn-archive i,
    body[data-pms-theme="dark"] .attendance-container .btn-archive i,
    body[data-theme="dark"] .attendance-container .btn-archive i,
    [data-pms-theme="dark"] .attendance-container .btn-archive i,
    [data-theme="dark"] .attendance-container .btn-archive i,
    .dark-mode .attendance-container .btn-archive i {
        color: #93C5FD !important;
        -webkit-text-fill-color: #93C5FD !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .btn-archive:hover,
    html[data-theme="dark"] .btn-archive:hover,
    html[data-bs-theme="dark"] .btn-archive:hover,
    body[data-pms-theme="dark"] .btn-archive:hover,
    body[data-theme="dark"] .btn-archive:hover,
    body[data-bs-theme="dark"] .btn-archive:hover,
    [data-pms-theme="dark"] .btn-archive:hover,
    [data-theme="dark"] .btn-archive:hover,
    [data-bs-theme="dark"] .btn-archive:hover,
    .dark-mode .btn-archive:hover,
    html[data-pms-theme="dark"] .attendance-container .btn-archive:hover,
    html[data-theme="dark"] .attendance-container .btn-archive:hover,
    html[data-bs-theme="dark"] .attendance-container .btn-archive:hover,
    body[data-pms-theme="dark"] .attendance-container .btn-archive:hover,
    body[data-theme="dark"] .attendance-container .btn-archive:hover,
    body[data-bs-theme="dark"] .attendance-container .btn-archive:hover,
    [data-pms-theme="dark"] .attendance-container .btn-archive:hover,
    [data-theme="dark"] .attendance-container .btn-archive:hover,
    [data-bs-theme="dark"] .attendance-container .btn-archive:hover,
    .dark-mode .attendance-container .btn-archive:hover {
        background: #1A2247 !important;
        border-color: #2F6BFF !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: translateY(-2px);
    }

    /* Toast / Notification Pop-up styling */
    .pms-toast-popup,
    .alert.position-fixed {
        position: fixed !important;
        top: 95px !important;
        right: 24px !important;
        z-index: 999999 !important;
        min-width: 320px !important;
        max-width: 480px !important;
        background-color: #0f172a !important;
        background: #0f172a !important;
        opacity: 1 !important;
        color: #ffffff !important;
        border: 1px solid #3b82f6 !important;
        border-left: 5px solid #3b82f6 !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45), 0 4px 12px rgba(0, 0, 0, 0.25) !important;
        border-radius: 12px !important;
        padding: 0.9rem 3.5rem 0.9rem 1.25rem !important;
        font-size: 0.925rem !important;
        font-weight: 500 !important;
        line-height: 1.5 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    .pms-toast-popup *,
    .alert.position-fixed * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .pms-toast-popup strong,
    .alert.position-fixed strong {
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
        font-weight: 700 !important;
    }
    .pms-toast-popup .btn-close,
    .alert.position-fixed .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%) !important;
        opacity: 0.8 !important;
        position: absolute !important;
        top: 50% !important;
        right: 14px !important;
        transform: translateY(-50%) !important;
        padding: 0.5rem !important;
    }
    .pms-toast-popup .btn-close:hover,
    .alert.position-fixed .btn-close:hover {
        opacity: 1 !important;
    }
</style>

<div class="attendance-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        @php
            $user = Auth::user();
            $canManage = in_array(strtolower((string)($user->role ?? '')), ['admin', 'superadmin', 'administrator', 'hr'], true);
        @endphp

        <!-- ===== BREADCRUMB ===== -->
        <div class="breadcrumb-custom mb-3" style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: #64748b;">
            <i class="fas fa-building" style="color: #2F6BFF;"></i>
            <a href="{{ route('admin.settings.index') }}" style="color: #2F6BFF; text-decoration: none;">Admin</a>
            <span>/</span>
            <a href="{{ route('admin.settings.index') }}" style="color: #2F6BFF; text-decoration: none;">Settings</a>
            <span>/</span>
            <a href="{{ route('attendance.settings') }}" style="color: #2F6BFF; text-decoration: none;">Attendance Settings</a>
            <span>/</span>
            <span>Attendance Log</span>
        </div>

        <!-- ===== HEADER ===== -->
        <div class="header-card">
            <div class="header-title">
                <h1>
                    <i class="fas fa-calendar-check me-2"></i>Employee Attendance Dashboard
                </h1>
                <p><i class="fas fa-info-circle me-1"></i>Monitor and manage employee attendance records</p>
                <a href="{{ route('attendance.index', ['month' => $month, 'year' => $year]) }}" class="btn-filter">Calendar view</a>
            </div>

            @if($canManage)
                <div class="header-actions">
                    <a href="{{ route('attendance.archive') }}" class="btn-archive">
                        <i class="fas fa-box-archive"></i> Archived
                        @if(($archivedCount ?? 0) > 0)
                            <span class="archive-count-badge">{{ $archivedCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('attendance.create') }}" class="btn-add">
                        <i class="fas fa-plus-circle"></i> Add Attendance
                    </a>
                </div>
            @endif
        </div>

        <!-- ===== STATS ===== -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total"><i class="fas fa-user-group"></i></div>
                <div class="stat-info">
                    <h6>{{ $canManage ? 'Employees Tracked' : 'Employee Tracked' }}</h6>
                    <h3 id="attendanceTotalEmployees">{{ count($users) }}</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon month"><i class="fas fa-calendar-days"></i></div>
                <div class="stat-info">
                    <h6>Attendance Month</h6>
                    <h3 id="attendanceCurrentMonth">{{ \Carbon\Carbon::createFromDate(null, $month)->format('F') }}</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon year"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h6>Report Year</h6>
                    <h3 id="attendanceSelectedYear">{{ $year }}</h3>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon days"><i class="fas fa-business-time"></i></div>
                <div class="stat-info">
                    <h6>Calendar Days</h6>
                    <h3 id="attendanceDaysInMonth">{{ $daysInMonth }}</h3>
                </div>
            </div>
        </div>

        <!-- ===== FILTER CARD ===== -->
        <div class="filter-card">
            <div class="filter-header">
                <h6><i class="fas fa-filter"></i>Filter Attendance Records</h6>
            </div>

            <form id="attendanceFilter">
                <div class="filter-grid">
                    {{-- Employee --}}
                    <div class="filter-group">
                        <label for="user_id"><i class="fas fa-user me-1"></i>Employee</label>
                        <select name="user_id" id="user_id" class="form-select">
                            @if($canManage)
                                <option value="">All Employees</option>
                                @foreach($users as $detail)
                                    <option value="{{ $detail->id }}" {{ request('user_id') == $detail->id ? 'selected' : '' }}>
                                        {{ $detail->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            @else
                                <option value="{{ $user->id }}" selected>{{ $user->name }}</option>
                            @endif
                        </select>
                    </div>

                    {{-- Department --}}
                    @if($canManage)
                    <div class="filter-group">
                        <label for="department_id"><i class="fas fa-building me-1"></i>Department</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $detp)
                                <option value="{{ $detp->id }}" {{ request('department_id') == $detp->id ? 'selected' : '' }}>
                                    {{ $detp->dpt_name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Designation --}}
                    <div class="filter-group">
                        <label for="designation_id"><i class="fas fa-user-tag me-1"></i>Designation</label>
                        <select name="designation_id" id="designation_id" class="form-select">
                            <option value="">All Designations</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Month --}}
                    <div class="filter-group">
                        <label for="month"><i class="fas fa-calendar-alt me-1"></i>Month</label>
                        <select name="month" id="month" class="form-select">
                            <option value="">All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Year --}}
                    <div class="filter-group">
                        <label for="year"><i class="fas fa-calendar me-1"></i>Year</label>
                        <select name="year" id="year" class="form-select">
                            <option value="">All Years</option>
                            @foreach(range(date('Y') - 2, date('Y') + 2) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== NAV TABS ===== -->
        <div class="tabs-card">
            <div class="tabs-wrapper">
                <a href="{{ route('attendance.index') }}" class="nav-tab-btn active">
                    <i class="fas fa-list-ul"></i> Summary
                </a>
                @if($canManage)
                    <a href="{{ route('attendance.byMember') }}" class="nav-tab-btn">
                        <i class="fas fa-user"></i> By Member
                    </a>
                    <a href="{{ route('attendance.byHour') }}" class="nav-tab-btn">
                        <i class="fas fa-clock"></i> By Hour
                    </a>
                    <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="nav-tab-btn">
                        <i class="fas fa-map-marker-alt"></i> Location View
                    </a>
                @endif
            </div>
        </div>

        <!-- ===== LEGEND ===== -->
        <div class="legend-card">
            <div class="legend-title"><i class="fas fa-key"></i>Attendance Status Legend</div>
            <div class="legend-items">
                <span class="legend-item" data-filter="present" title="Click to filter: Present only">
                    <span class="legend-icon present"><i class="fas fa-check"></i></span>
                    <span class="legend-text">Present</span>
                </span>
                <span class="legend-item" data-filter="absent" title="Click to filter: Absent only">
                    <span class="legend-icon absent"><i class="fas fa-times"></i></span>
                    <span class="legend-text">Absent</span>
                </span>
                <span class="legend-item" data-filter="late" title="Click to filter: Late only">
                    <span class="legend-icon late"><i class="fas fa-clock"></i></span>
                    <span class="legend-text">Late</span>
                </span>
                <span class="legend-item" data-filter="halfday" title="Click to filter: Half Day only">
                    <span class="legend-icon halfday"><i class="fas fa-star-half-alt"></i></span>
                    <span class="legend-text">Half Day</span>
                </span>
                <span class="legend-item" data-filter="holiday" title="Click to filter: Holiday only">
                    <span class="legend-icon holiday"><i class="fas fa-star"></i></span>
                    <span class="legend-text">Holiday</span>
                </span>
                <span class="legend-item" data-filter="dayoff" title="Click to filter: Day Off only">
                    <span class="legend-icon dayoff"><i class="fas fa-calendar"></i></span>
                    <span class="legend-text">Day Off</span>
                </span>
                <span class="legend-item" data-filter="leave" title="Click to filter: On Leave only">
                    <span class="legend-icon leave"><i class="fas fa-plane-departure"></i></span>
                    <span class="legend-text">On Leave</span>
                </span>
                <span class="legend-item" data-filter="wfh" title="Click to filter: Work From Home only">
                    <span class="legend-icon wfh"><i class="fas fa-laptop-house"></i></span>
                    <span class="legend-text">Work From Home</span>
                </span>
                <span class="legend-item legend-reset" id="legendResetBtn" title="Show all employees" style="display:none;border-color:rgba(239,68,68,0.35);background:rgba(239,68,68,0.06);">
                    <span class="legend-icon" style="background:#ef4444;"><i class="fas fa-times"></i></span>
                    <span class="legend-text" style="color:#ef4444!important;-webkit-text-fill-color:#ef4444!important;">Clear Filter</span>
                </span>
            </div>
        </div>

        <!-- ===== TABLE CARD ===== -->
        <div class="table-card">
            <div class="table-header">
                <h6><i class="fas fa-table"></i>Attendance Summary</h6>
                <div class="attendance-table-actions">
                    <div class="dropdown attendance-export-dropdown">
                        <button class="btn-export-menu dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cloud-download-alt"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button type="button" class="dropdown-item" onclick="exportTo('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                            <li><button type="button" class="dropdown-item" onclick="exportTo('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                            <li><button type="button" class="dropdown-item" onclick="exportTo('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                            <li><button type="button" class="dropdown-item" onclick="exportTo('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button type="button" class="dropdown-item" onclick="exportTo('print')"><i class="fas fa-print text-info"></i> Print</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="attendance-table">
                @include('admin.attendance.table', [
                    'users' => $users,
                    'attendanceMap' => $attendanceMap,
                    'daysInMonth' => $daysInMonth,
                    'month' => $month,
                    'year' => $year
                ])
            </div>
        </div>

        <!-- ===== FOOTER ===== -->
        <div class="footer-note">
            <p>
                <i class="fas fa-exclamation-circle"></i>
                Data is updated in real-time. Last updated: {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>
</div>

{{-- Attendance Details Modal --}}
<div class="modal fade attendance-details-modal" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content attendance-details-modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Attendance Details</h5>
        <button type="button" class="btn-close" data-attendance-modal-close aria-label="Close"></button>
      </div>
      <div id="attendanceDetailsBody" class="modal-body attendance-details-modal-body">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-attendance-modal-close>Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.form-select').select2({
            width: '100%',
            theme: 'bootstrap-5'
        });
    }

    // DataTable initialization
    function initDataTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

        $('.attendance-page-export-buttons').remove();

        if ($.fn.DataTable.isDataTable('#attendanceTable')) {
            $('#attendanceTable').DataTable().destroy();
        }

        const exportColumnIndexes = [];
        const utilityColumnIndexes = [];
        $('#attendanceTable thead th').each(function(index) {
            if (!$(this).hasClass('no-export')) {
                exportColumnIndexes.push(index);
            } else {
                utilityColumnIndexes.push(index);
            }
        });

        const exportFormat = {
            body: function(data, row, column, node) {
                const cell = $(node);
                const titledIcon = cell.find('[title]').first();

                if (titledIcon.length && titledIcon.attr('title')) {
                    return titledIcon.attr('title');
                }

                return cell.text().replace(/\s+/g, ' ').trim();
            }
        };

        const table = $('#attendanceTable').DataTable({
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>Brt<"row"<"col-md-6"i><"col-md-6"p>>',
            responsive: true,
            scrollX: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            columnDefs: [
                { orderable: false, searchable: false, targets: utilityColumnIndexes }
            ],
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: 'Copy',
                    title: 'Attendance Summary',
                    exportOptions: {
                        columns: exportColumnIndexes,
                        modifier: { search: 'applied' },
                        stripHtml: true,
                        format: exportFormat
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Attendance Summary',
                    filename: 'attendance-summary',
                    exportOptions: {
                        columns: exportColumnIndexes,
                        modifier: { search: 'applied' },
                        stripHtml: true,
                        format: exportFormat
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Attendance Summary',
                    filename: 'attendance-summary',
                    exportOptions: {
                        columns: exportColumnIndexes,
                        modifier: { search: 'applied' },
                        stripHtml: true,
                        format: exportFormat
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    title: 'Attendance Summary',
                    filename: 'attendance-summary',
                    pageSize: 'A4',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: exportColumnIndexes,
                        modifier: { search: 'applied' },
                        stripHtml: true,
                        format: exportFormat
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: 'Attendance Summary',
                    exportOptions: {
                        columns: exportColumnIndexes,
                        modifier: { search: 'applied' },
                        stripHtml: true,
                        format: exportFormat
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            }
        });

        table.buttons().container().addClass('attendance-page-export-buttons').appendTo('.table-card');
    }

    initDataTable();

    // AJAX filter handler
    const filterForm = document.getElementById('attendanceFilter');
    const filterInputs = filterForm ? filterForm.querySelectorAll('select, input') : [];
    let filterTimer = null;
    let activeFilterRequest = null;

    function updateAttendanceStats(meta) {
        if (!meta) {
            return;
        }

        const statMap = {
            attendanceTotalEmployees: meta.totalEmployees,
            attendanceCurrentMonth: meta.monthName,
            attendanceSelectedYear: meta.year,
            attendanceDaysInMonth: meta.daysInMonth
        };

        Object.entries(statMap).forEach(function([id, value]) {
            const element = document.getElementById(id);
            if (element && value !== undefined && value !== null) {
                element.textContent = value;
            }
        });
    }

    function applyAttendanceFilter(showSuccess = false) {
        const target = document.getElementById('attendance-table');
        if (!filterForm || !target) {
            return;
        }

        if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#attendanceTable')) {
            $('#attendanceTable').DataTable().destroy();
        }
        $('.attendance-page-export-buttons').remove();

        target.innerHTML = `
            <div class="loading-overlay">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading attendance data...</div>
            </div>
        `;

        const data = new URLSearchParams(new FormData(filterForm)).toString();
        const requestUrl = "{{ route('attendance.filter') }}?" + data;

        if (activeFilterRequest) {
            activeFilterRequest.abort();
        }

        activeFilterRequest = new AbortController();

        fetch(requestUrl, {
            method: 'GET',
            signal: activeFilterRequest.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Filter request failed');
            }
            return response.json();
        })
        .then(json => {
            if (json.html) {
                target.innerHTML = json.html;
                updateAttendanceStats(json.meta);
                initDataTable();
                window.history.replaceState({}, '', "{{ route('attendance.index') }}" + (data ? '?' + data : ''));

                if (showSuccess) {
                    showNotification('Filters applied successfully!', 'success');
                }
            } else {
                target.innerHTML = '<p class="text-center py-5 text-muted">No data returned</p>';
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') {
                return;
            }
            console.error('Filter error:', err);
            target.innerHTML = '<p class="text-center py-5 text-danger">Something went wrong. Please try again.</p>';
        })
        .finally(() => {
            activeFilterRequest = null;
        });
    }

    if (typeof $ !== 'undefined') {
        $('#attendanceFilter').on('submit', function (e) {
            e.preventDefault();
            applyAttendanceFilter(true);
        });

        $('#attendanceFilter').on('change', 'select, input', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function() {
                applyAttendanceFilter(false);
            }, 250);
        });

        $('#attendanceFilter select').on('select2:select select2:clear', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function() {
                applyAttendanceFilter(false);
            }, 250);
        });
    } else {
        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                applyAttendanceFilter(true);
            });
        }

        filterInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function() {
                    applyAttendanceFilter(false);
                }, 250);
            });
        });
    }

    // Notification function
    function showNotification(message, type = 'info') {
        document.querySelectorAll('.pms-toast-popup').forEach(el => el.remove());

        const isDark = document.documentElement.getAttribute('data-pms-theme') === 'dark' ||
                       document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
                       document.body.classList.contains('dark-mode') ||
                       document.body.classList.contains('dark');

        const bg = isDark ? '#0f172a' : '#1e293b';
        const borderColor = type === 'success' ? '#10b981' : (type === 'danger' ? '#ef4444' : (type === 'warning' ? '#f59e0b' : '#3b82f6'));
        const strongColor = type === 'success' ? '#34d399' : (type === 'danger' ? '#f87171' : (type === 'warning' ? '#fbbf24' : '#60a5fa'));

        const notification = document.createElement('div');
        notification.className = `pms-toast-popup alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            position: fixed !important;
            top: 95px !important;
            right: 24px !important;
            z-index: 999999 !important;
            min-width: 320px !important;
            max-width: 480px !important;
            background-color: ${bg} !important;
            background: ${bg} !important;
            opacity: 1 !important;
            color: #ffffff !important;
            border: 1px solid ${borderColor} !important;
            border-left: 5px solid ${borderColor} !important;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45), 0 4px 12px rgba(0, 0, 0, 0.25) !important;
            border-radius: 12px !important;
            padding: 0.9rem 3.25rem 0.9rem 1.25rem !important;
            font-size: 0.925rem !important;
            font-weight: 500 !important;
            line-height: 1.5 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="position: absolute !important; right: 14px !important; top: 50% !important; transform: translateY(-50%) !important; opacity: 0.8; filter: invert(1) grayscale(100%) brightness(200%);"></button>
        `;

        notification.querySelectorAll('strong').forEach(el => {
            el.style.cssText = `color: ${strongColor} !important; -webkit-text-fill-color: ${strongColor} !important; font-weight: 700 !important;`;
        });

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 3500);
    }

    // Export functions (preserved)
    window.exportPdf = function() {
        const form = document.getElementById('attendanceFilter');
        const params = new URLSearchParams(new FormData(form));
        showNotification('Preparing PDF export...', 'info');
        window.location.href = '{{ route("attendance.export.pdf") }}?' + params.toString();
    };

    window.exportExcel = function() {
        const form = document.getElementById('attendanceFilter');
        const params = new URLSearchParams(new FormData(form));
        showNotification('Preparing Excel export...', 'info');
        window.location.href = '{{ route("attendance.export.excel") }}?' + params.toString();
    };

    window.exportTo = function(format) {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined' || !$.fn.DataTable.isDataTable('#attendanceTable')) {
            showNotification('The attendance export table is not ready yet.', 'warning');
            return;
        }

        const table = $('#attendanceTable').DataTable();
        const buttonSelectors = {
            copy: '.buttons-copy',
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };
        const selector = buttonSelectors[format];

        if (!selector || !table.button(selector).node()) {
            showNotification('The selected export option is currently unavailable.', 'danger');
            return;
        }

        table.button(selector).trigger();

        if (format !== 'copy' && format !== 'print') {
            showNotification(format.toUpperCase() + ' export started.', 'success');
        }
    };

    /* ===== LEGEND CLICK FILTER ===== */
    (function() {
        let activeFilter = null;

        function applyLegendFilter(status) {
            const table = document.querySelector('#attendanceTable tbody');
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            rows.forEach(function(row) {
                row.classList.remove('legend-filtered-in', 'legend-filtered-out');
                if (status) {
                    const hasStatus = row.querySelector('.attendance-status-icon.' + status);
                    if (hasStatus) {
                        row.classList.add('legend-filtered-in');
                    } else {
                        row.classList.add('legend-filtered-out');
                    }
                }
            });
        }

        function clearLegendFilter() {
            activeFilter = null;
            applyLegendFilter(null);
            document.querySelectorAll('.legend-item').forEach(function(el) {
                el.classList.remove('active');
            });
            const resetBtn = document.getElementById('legendResetBtn');
            if (resetBtn) resetBtn.style.display = 'none';
        }

        // Attach to legend items — also re-attach after AJAX reload
        function attachLegendListeners() {
            document.querySelectorAll('.legend-item[data-filter]').forEach(function(item) {
                item.addEventListener('click', function() {
                    const status = this.getAttribute('data-filter');

                    if (activeFilter === status) {
                        // Toggle off
                        clearLegendFilter();
                        showNotification('Filter cleared — showing all employees.', 'info');
                        return;
                    }

                    // Activate this filter
                    activeFilter = status;
                    document.querySelectorAll('.legend-item').forEach(function(el) {
                        el.classList.remove('active');
                    });
                    this.classList.add('active');

                    applyLegendFilter(status);

                    const resetBtn = document.getElementById('legendResetBtn');
                    if (resetBtn) resetBtn.style.display = 'inline-flex';

                    const label = this.querySelector('.legend-text')?.textContent || status;
                    const visibleCount = document.querySelectorAll('#attendanceTable tbody tr.legend-filtered-in').length;
                    showNotification(
                        '<i class="fas fa-filter me-1"></i> Showing <strong>' + visibleCount + '</strong> employee(s) with <strong>' + label + '</strong> status.',
                        'info'
                    );
                });
            });

            const resetBtn = document.getElementById('legendResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    clearLegendFilter();
                    showNotification('Filter cleared — showing all employees.', 'info');
                });
            }
        }

        // Initial attach
        attachLegendListeners();

        // Re-attach after AJAX table reload (applyAttendanceFilter replaces innerHTML)
        const originalInitDataTable = window.initDataTable || null;
        const tableContainer = document.getElementById('attendance-table');
        if (tableContainer) {
            const observer = new MutationObserver(function() {
                // Re-apply filter if one was active
                if (activeFilter) {
                    setTimeout(function() {
                        applyLegendFilter(activeFilter);
                    }, 400);
                }
            });
            observer.observe(tableContainer, { childList: true, subtree: false });
        }
    })();

    /* ===== ATTENDANCE MODAL HANDLERS (persistent – survive AJAX reloads) ===== */
    if (typeof $ !== 'undefined') {

        function getAttendanceModal() {
            var modalEl = document.getElementById('attendanceDetailsModal');
            if (!modalEl) return null;
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            return bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: true, keyboard: true, focus: true
            });
        }

        function showAttendanceLoading(message) {
            $('#attendanceDetailsBody').html(
                '<div class="text-center py-5">' +
                    '<div class="spinner-border text-primary" role="status">' +
                        '<span class="visually-hidden">Loading...</span>' +
                    '</div>' +
                    '<p class="mt-3 mb-0 text-muted">' + message + '</p>' +
                '</div>'
            );
        }

        function cleanModalState(force) {
            if (force || $('.modal.show').length === 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        }

        function closeAttendanceModal() {
            var modalEl = document.getElementById('attendanceDetailsModal');
            if (!modalEl) { cleanModalState(true); return; }
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            modalEl.classList.remove('show');
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            modalEl.style.display = 'none';
            $('#attendanceDetailsBody').html('');
            cleanModalState(true);
        }

        function escapeHtml(v) {
            return String(v ?? '').replace(/[&<>"']/g, function(c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
            });
        }

        function parsePayload(encoded) {
            var binary = atob(encoded);
            var bytes = new Uint8Array(binary.length);
            for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
            return window.TextDecoder
                ? JSON.parse(new TextDecoder('utf-8').decode(bytes))
                : JSON.parse(decodeURIComponent(escape(binary)));
        }

        function renderMonthDetails(payload, editMode) {
            var rows = (payload.records || []).map(function(r) {
                var editBtn = editMode
                    ? '<button type="button" class="btn btn-sm btn-outline-primary edit-attendance" ' +
                      'data-attendance-id="' + escapeHtml(r.attendance_id || '') + '" ' +
                      'data-user-id="' + escapeHtml(payload.user_id) + '" ' +
                      'data-date="' + escapeHtml(r.date) + '">' +
                      '<i class="fas fa-pen me-1"></i>Edit</button>'
                    : '';
                var totalCellHtml = escapeHtml(r.total);
                if (r.is_open && r.clock_in && r.clock_in !== '-') {
                    totalCellHtml = '<span class="live-index-work-timer fw-bold text-success" data-clock-in="' + escapeHtml(r.clock_in_raw || '') + '" data-date="' + escapeHtml(r.date || '') + '" data-seconds="' + escapeHtml(r.total_seconds || 0) + '">' + escapeHtml(r.total) + '</span>' +
                        ' <span class="badge bg-success-subtle text-success small ms-1"><i class="fas fa-spinner fa-spin me-1"></i>Working</span>';
                }

                return '<tr>' +
                    '<td><strong>' + escapeHtml(r.day) + '</strong><div class="small text-muted">' + escapeHtml(r.date) + '</div></td>' +
                    '<td>' + escapeHtml(r.status) + '</td>' +
                    '<td>' + escapeHtml(r.clock_in) + '</td>' +
                    '<td>' + escapeHtml(r.clock_out) + '</td>' +
                    '<td>' + totalCellHtml + '</td>' +
                    '<td>' + escapeHtml(r.note || '-') + '</td>' +
                    (editMode ? '<td class="text-center">' + editBtn + '</td>' : '') +
                    '</tr>';
            }).join('');

            $('#attendanceDetailsModal .modal-title').text(editMode ? 'Edit Monthly Attendance' : 'Monthly Attendance Details');
            $('#attendanceDetailsBody').html(
                '<div class="attendance-month-summary">' +
                    '<div class="attendance-month-profile">' +
                        '<img src="' + escapeHtml(payload.photo) + '" alt="' + escapeHtml(payload.name) + '" onerror="this.onerror=null; this.src=\'/images/default-avatar.png\';">' +
                        '<div>' +
                            '<h5>' + escapeHtml(payload.name) + '</h5>' +
                            '<p>' + escapeHtml(payload.designation) + ' &nbsp;|&nbsp; ' + escapeHtml(payload.month_name) +
                            ' &nbsp;|&nbsp; Total: ' + escapeHtml(payload.total_hours) +
                            ' &nbsp;|&nbsp; Present: ' + escapeHtml(payload.present_count) + '/' + escapeHtml(payload.days_in_month) + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="table-responsive">' +
                        '<table class="attendance-month-table">' +
                            '<thead><tr>' +
                                '<th>Date</th><th>Status</th><th>Clock In</th><th>Clock Out</th><th>Total</th><th>Note</th>' +
                                (editMode ? '<th class="text-center">Action</th>' : '') +
                            '</tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>'
            );

            if (window.indexAttendanceTimerInterval) {
                clearInterval(window.indexAttendanceTimerInterval);
                window.indexAttendanceTimerInterval = null;
            }

            var updateIndexOpenShifts = function() {
                var now = new Date();
                $('#attendanceDetailsModal .live-index-work-timer').each(function() {
                    var el = $(this);
                    var rawDate = el.attr('data-date');
                    var rawTime = el.attr('data-clock-in');
                    if (!rawDate || !rawTime) return;
                    var parts = rawTime.split(':').map(Number);
                    var dp = rawDate.split('-').map(Number);
                    if (parts.length >= 2 && dp.length === 3) {
                        var start = new Date(dp[0], dp[1] - 1, dp[2], parts[0], parts[1], parts[2] || 0);
                        var diffSec = Math.max(0, Math.floor((now - start) / 1000));
                        var h = Math.floor(diffSec / 3600);
                        var m = Math.floor((diffSec % 3600) / 60);
                        var s = diffSec % 60;
                        var fmt = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                        el.text(fmt);
                    }
                });
            };

            window.indexAttendanceTimerInterval = setInterval(updateIndexOpenShifts, 1000);
            $('#attendanceDetailsModal').off('hidden.bs.modal.indexTimer').on('hidden.bs.modal.indexTimer', function() {
                if (window.indexAttendanceTimerInterval) {
                    clearInterval(window.indexAttendanceTimerInterval);
                    window.indexAttendanceTimerInterval = null;
                }
            });

            var modal = getAttendanceModal();
            if (modal) modal.show();
        }

        // — View month button
        $(document).off('click.pmsMonthView', '.js-month-view')
            .on('click.pmsMonthView', '.js-month-view', function () {
            renderMonthDetails(parsePayload(this.dataset.payload), false);
        });

        // — Edit month button
        $(document).off('click.pmsMonthEdit', '.js-month-edit')
            .on('click.pmsMonthEdit', '.js-month-edit', function () {
            renderMonthDetails(parsePayload(this.dataset.payload), true);
        });

        // — Archive month button
        $(document).off('click.pmsMonthArchive', '.js-month-archive')
            .on('click.pmsMonthArchive', '.js-month-archive', function () {
            var btn = $(this);
            var orig = btn.html();
            var payload = parsePayload(this.dataset.payload);
            if (!confirm('Archive attendance for ' + payload.name + ' – ' + payload.month_name + '?\nRecords will move to archive and can be restored later.')) return;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i><span>Archiving…</span>');
            $.ajax({
                url: "{{ route('attendance.month.archive') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", user_id: payload.user_id, month: payload.month, year: payload.year },
                success: function(r) { alert(r.message || 'Archived successfully.'); window.location.reload(); },
                error:   function(xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Archive failed.'); },
                complete: function() { btn.prop('disabled', false).html(orig); }
            });
        });

        // — View single attendance (status-icon click)
        $(document).off('click.pmsAttView', '.view-attendance')
            .on('click.pmsAttView', '.view-attendance', function (e) {
            e.preventDefault();
            var url = "{{ url('attendance/details') }}?attendance_id=" + $(this).data('attendance-id') +
                      "&user_id=" + $(this).data('user-id') + "&date=" + $(this).data('date');
            var modal = getAttendanceModal();
            if (!modal) return;
            $('#attendanceDetailsModal .modal-title').text('Attendance Details');
            showAttendanceLoading('Loading attendance details…');
            modal.show();
            $.get(url)
                .done(function(html) { $('#attendanceDetailsBody').html(html); })
                .fail(function()     { $('#attendanceDetailsBody').html('<div class="alert alert-danger m-4">Error loading attendance details.</div>'); });
        });

        // — Edit single attendance (absent cell click)
        $(document).off('click.pmsAttEdit', '.edit-attendance')
            .on('click.pmsAttEdit', '.edit-attendance', function (e) {
            e.preventDefault();
            var url = "{{ url('attendance/edit') }}?attendance_id=" + $(this).data('attendance-id') +
                      "&user_id=" + $(this).data('user-id') + "&date=" + $(this).data('date');
            var modal = getAttendanceModal();
            if (!modal) return;
            $('#attendanceDetailsModal .modal-title').text('Edit Attendance');
            showAttendanceLoading('Loading attendance form…');
            modal.show();
            $.get(url)
                .done(function(html) { $('#attendanceDetailsBody').html(html); })
                .fail(function()     { $('#attendanceDetailsBody').html('<div class="alert alert-danger m-4">Error loading form.</div>'); });
        });

        // — Inline form submit inside modal
        $(document).off('submit.pmsAttForm', '#attendanceDetailsModal .attendance-form')
            .on('submit.pmsAttForm', '#attendanceDetailsModal .attendance-form', function (e) {
            e.preventDefault();
            var form = this;
            var submitBtn = $(form).find('[type="submit"]').first();
            var orig = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');
            $.ajax({
                url: form.action, type: form.method || 'POST',
                data: new FormData(form), processData: false, contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function() { closeAttendanceModal(); window.location.reload(); },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to save. Please try again.';
                    $(form).prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">' + msg +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                },
                complete: function() { submitBtn.prop('disabled', false).html(orig); cleanModalState(); }
            });
        });

        // — Modal close handlers
        $(document).off('click.pmsModalClose', '[data-attendance-modal-close]')
            .on('click.pmsModalClose', '[data-attendance-modal-close]', function (e) {
            e.preventDefault(); closeAttendanceModal();
        });

        $(document).off('hidden.bs.modal.pmsAtt', '#attendanceDetailsModal')
            .on('hidden.bs.modal.pmsAtt', '#attendanceDetailsModal', function () {
            $('#attendanceDetailsBody').html(''); cleanModalState(true);
        });

        $(document).off('keydown.pmsModalEsc')
            .on('keydown.pmsModalEsc', function (e) {
            if (e.key === 'Escape' && $('#attendanceDetailsModal').hasClass('show')) closeAttendanceModal();
        });

    } // end if jQuery

});
</script>
@endpush
