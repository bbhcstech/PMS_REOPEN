@extends('admin.layout.app')

@section('title', 'Employee Attendance Dashboard')

@section('content')
<style>
    :root {
        --primary-blue: #1e3a8a;
        --primary-teal: #0ea5a4;
        --primary-green: #22c55e;
        --bg-light: #f8fafc;
        --glass-border: rgba(255, 255, 255, 0.7);
        --card-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
            0px 8px 40px rgba(0, 0, 0, 0.04),
            0px 20px 60px rgba(30, 58, 138, 0.06);
        --card-shadow-hover: 0px 20px 50px rgba(0, 0, 0, 0.08),
            0px 30px 80px rgba(30, 58, 138, 0.12);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spring-transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* ===== MAIN CONTAINER ===== */
    .attendance-container {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
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
        background: radial-gradient(circle, rgba(30, 58, 138, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(14, 165, 164, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    .orb-3 {
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, transparent 70%);
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

    /* ===== HEADER CARD ===== */
    .header-card {
        background: rgba(255, 255, 255, 0.92);
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
        border-color: rgba(14, 165, 164, 0.2);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-title h1 {
        font-size: 2.28rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal), var(--primary-green));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 0.25rem;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 1.12rem;
        font-weight: 500;
        margin: 0;
    }

    .header-title p i {
        color: var(--primary-teal);
    }

    .btn-add {
        background: linear-gradient(135deg, var(--primary-teal), var(--primary-green));
        color: white;
        padding: 0.7rem 1.6rem;
        border-radius: 40px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 600;
        font-size: 1.12rem;
        transition: var(--spring-transition);
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.2);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(14, 165, 164, 0.35);
        color: white;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
    }

    .btn-archive {
        background: #f0f9f4;
        color: #0f744c;
        padding: 0.7rem 1.35rem;
        border-radius: 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 1rem;
        transition: var(--spring-transition);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-archive:hover {
        background: #e6f3ec;
        color: #0f744c;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
    }

    .archive-count-badge {
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #ef4444;
        color: #ffffff;
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

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 1.5rem;
        transition: var(--spring-transition);
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        animation: cardStagger 0.6s ease forwards;
        opacity: 0;
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-teal), var(--primary-green));
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
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.15);
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

    .stat-icon.total {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
    }

    .stat-icon.month {
        background: linear-gradient(135deg, #fef3c7, #fed7aa);
        color: #c2410c;
    }

    .stat-icon.year {
        background: linear-gradient(135deg, #ede9fe, #ddd6fe);
        color: #6d28d9;
    }

    .stat-icon.days {
        background: linear-gradient(135deg, #ffe4e6, #fecdd3);
        color: #be123c;
    }

    .stat-info {
        flex: 1;
        min-width: 0;
    }

    .stat-info h6 {
        font-size: 0.94rem;
        color: #475569;
        margin-bottom: 0.28rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .stat-info h3 {
        font-size: 2.22rem;
        font-weight: 800;
        color: #0f172a;
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
        color: #0f172a;
        margin: 0;
        font-size: 1.24rem;
    }

    .filter-header h6 i {
        color: var(--primary-teal);
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
        color: #64748b;
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
        color: #0f172a;
        background: white;
        transition: var(--transition-smooth);
        outline: none;
        min-height: 44px;
        width: 100%;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: var(--primary-teal);
        box-shadow: 0 0 0 4px rgba(14, 165, 164, 0.12);
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-filter {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        color: white;
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
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.2);
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(14, 165, 164, 0.3);
    }

    .btn-reset {
        background: linear-gradient(135deg, #64748b, #475569);
        color: white;
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
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.2);
    }

    .btn-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
        color: white;
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

    .nav-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.5rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 1.05rem;
        color: #475569;
        background: #f1f5f9;
        text-decoration: none;
        transition: var(--spring-transition);
        border: 2px solid transparent;
    }

    .nav-tab-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-2px);
    }

    .nav-tab-btn.active {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.25);
    }

    .nav-tab-btn i {
        font-size: 1rem;
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
        color: #0f172a;
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }

    .legend-title i {
        color: var(--primary-teal);
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
        cursor: default;
    }

    .legend-item:hover {
        transform: translateY(-2px);
        border-color: var(--primary-teal);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .legend-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        font-size: 13px;
        color: white;
        flex-shrink: 0;
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
        color: #0f172a;
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
        color: #0f172a;
        margin: 0;
        font-size: 1.25rem;
    }

    .table-header h6 i {
        color: var(--primary-teal);
        margin-right: 0.5rem;
    }

    .attendance-table-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .selected-count-badge {
        min-height: 36px;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 1rem;
        font-weight: 800;
        border: 1px solid #e2e8f0;
    }

    .btn-clear-selection,
    .btn-export-menu {
        min-height: 38px;
        border: 0;
        border-radius: 999px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 1rem;
        font-weight: 800;
        transition: var(--spring-transition);
    }

    .btn-clear-selection {
        background: #e2e8f0;
        color: #475569;
    }

    .btn-clear-selection:hover {
        background: #cbd5e1;
        color: #0f172a;
        transform: translateY(-2px);
    }

    .btn-export-menu {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 16px;
        padding: 0.65rem 1.25rem;
        box-shadow: none;
    }

    .btn-export-menu:hover,
    .btn-export-menu:focus {
        background: #e6f3ec;
        color: #0f744c;
        border-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
    }

    .attendance-export-dropdown {
        position: relative;
        z-index: 60;
    }

    .attendance-table-actions .dropdown-menu {
        min-width: 230px;
        margin-top: 10px !important;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.18);
        border-radius: 16px;
        padding: 10px;
        box-shadow: 0 24px 48px -14px rgba(10, 46, 31, 0.28);
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
        color: #374151;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .attendance-table-actions .dropdown-item i {
        width: 20px;
        font-size: 1rem;
    }

    .attendance-table-actions .dropdown-item:hover {
        background: #ecfdf5;
        color: #059669;
    }

    .attendance-table-actions .dropdown-divider {
        margin: 8px 0;
        border-color: rgba(16, 185, 129, 0.16);
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
        color: #64748b;
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
        color: var(--primary-teal);
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
        color: #1e293b;
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
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        color: white;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .employee-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 1.12rem;
    }

    .employee-dept {
        font-size: 0.98rem;
        color: #94a3b8;
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
        color: white;
        transition: var(--transition-smooth);
        cursor: default;
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
    .status-leave { background: #06b6d4; }

    /* ===== FOOTER ===== */
    .footer-note {
        margin-top: 1.5rem;
        text-align: center;
        padding: 1rem;
    }

    .footer-note p {
        color: #94a3b8;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .footer-note p i {
        color: var(--primary-teal);
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
        border-top-color: var(--primary-teal);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-text {
        margin-top: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
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
    html[data-pms-theme="dark"] .attendance-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .filter-card,
    html[data-pms-theme="dark"] .tabs-card,
    html[data-pms-theme="dark"] .legend-card,
    html[data-pms-theme="dark"] .table-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .header-title h1 {
        background: linear-gradient(135deg, #60a5fa, #34d399, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
    }

    html[data-pms-theme="dark"] .header-title p {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .stat-info h6,
    html[data-pms-theme="dark"] .filter-header h6,
    html[data-pms-theme="dark"] .table-header h6,
    html[data-pms-theme="dark"] .legend-title {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .stat-info h3 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-group label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-group select,
    html[data-pms-theme="dark"] .filter-group input {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-group select:focus,
    html[data-pms-theme="dark"] .filter-group input:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    html[data-pms-theme="dark"] .filter-group select option {
        background: #183026;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-tab-btn {
        background: #183026;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .nav-tab-btn:hover {
        background: #102119;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-tab-btn.active {
        background: linear-gradient(135deg, #0f744c, #10b981);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .legend-item {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .legend-text {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .attendance-table tbody tr {
        background: #102119;
    }

    html[data-pms-theme="dark"] .attendance-table tbody tr:hover {
        background: #183026;
    }

    html[data-pms-theme="dark"] .attendance-table thead th {
        color: #8ba198;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .attendance-table td {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .employee-name {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .employee-dept {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .table-header {
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .footer-note p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .stat-icon.total {
        background: linear-gradient(135deg, #312e81, #4f46e5);
        color: #c7d2fe;
    }

    html[data-pms-theme="dark"] .stat-icon.month {
        background: linear-gradient(135deg, #78350f, #d97706);
        color: #fcd34d;
    }

    html[data-pms-theme="dark"] .stat-icon.year {
        background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
        color: #93bbfc;
    }

    html[data-pms-theme="dark"] .stat-icon.days {
        background: linear-gradient(135deg, #831843, #be185d);
        color: #fbcfe8;
    }

    html[data-pms-theme="dark"] .selected-count-badge,
    html[data-pms-theme="dark"] .btn-clear-selection {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-export-menu {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.2);
    }

    html[data-pms-theme="dark"] .btn-export-menu:hover,
    html[data-pms-theme="dark"] .btn-export-menu:focus {
        background: #224130;
        color: #ffffff;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-menu {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, 0.55);
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-item {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-item:hover {
        background: #183026;
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .attendance-table-actions .dropdown-divider {
        border-color: rgba(122, 240, 181, 0.15);
    }
</style>

<div class="attendance-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        @php $user = Auth::user(); @endphp

        <!-- ===== HEADER ===== -->
        <div class="header-card">
            <div class="header-title">
                <h1>
                    <i class="fas fa-calendar-check me-2"></i>Employee Attendance Dashboard
                </h1>
                <p><i class="fas fa-info-circle me-1"></i>Monitor and manage employee attendance records</p>
            </div>

            @if($user->role == 'admin')
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
                    <h6>{{ $user->role === 'admin' ? 'Employees Tracked' : 'Employee Tracked' }}</h6>
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
                            @if($user->role === 'admin')
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
                    @if($user->role == 'admin')
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
                @if($user->role === 'admin')
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
                <span class="legend-item">
                    <span class="legend-icon present"><i class="fas fa-check"></i></span>
                    <span class="legend-text">Present</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon absent"><i class="fas fa-times"></i></span>
                    <span class="legend-text">Absent</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon late"><i class="fas fa-clock"></i></span>
                    <span class="legend-text">Late</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon halfday"><i class="fas fa-star-half-alt"></i></span>
                    <span class="legend-text">Half Day</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon holiday"><i class="fas fa-star"></i></span>
                    <span class="legend-text">Holiday</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon dayoff"><i class="fas fa-calendar"></i></span>
                    <span class="legend-text">Day Off</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon leave"><i class="fas fa-plane-departure"></i></span>
                    <span class="legend-text">On Leave</span>
                </span>
                <span class="legend-item">
                    <span class="legend-icon wfh"><i class="fas fa-laptop-house"></i></span>
                    <span class="legend-text">Work From Home</span>
                </span>
            </div>
        </div>

        <!-- ===== TABLE CARD ===== -->
        <div class="table-card">
            <div class="table-header">
                <h6><i class="fas fa-table"></i>Attendance Summary</h6>
                <div class="attendance-table-actions">
                    <span class="selected-count-badge" id="attendanceSelectedCount">
                        <i class="fas fa-check-square"></i>0 selected
                    </span>
                    <button type="button" class="btn-clear-selection" id="attendanceClearSelection">
                        <i class="fas fa-times"></i>Clear
                    </button>
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
            <div class="table-responsive">
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

        const exportRows = function(idx, data, node) {
            const selectedRows = $('#attendanceTable .attendance-row-checkbox:checked');
            if (selectedRows.length > 0) {
                return $(node).find('.attendance-row-checkbox').is(':checked');
            }

            return true;
        };

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
                        rows: exportRows,
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
                        rows: exportRows,
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
                        rows: exportRows,
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
                        rows: exportRows,
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
                        rows: exportRows,
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
        updateAttendanceSelection();
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
                updateAttendanceSelection();
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

    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        applyAttendanceFilter(true);
    });

    filterInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function() {
                applyAttendanceFilter(false);
            }, 250);
        });
    });

    function updateAttendanceSelection() {
        const rowCheckboxes = document.querySelectorAll('#attendanceTable .attendance-row-checkbox');
        const checkedRows = document.querySelectorAll('#attendanceTable .attendance-row-checkbox:checked');
        const selectAll = document.getElementById('attendanceSelectAll');
        const selectedCount = document.getElementById('attendanceSelectedCount');

        if (selectedCount) {
            selectedCount.innerHTML = `<i class="fas fa-check-square"></i>${checkedRows.length} selected`;
        }

        if (selectAll) {
            selectAll.checked = rowCheckboxes.length > 0 && checkedRows.length === rowCheckboxes.length;
            selectAll.indeterminate = checkedRows.length > 0 && checkedRows.length < rowCheckboxes.length;
        }

        rowCheckboxes.forEach(function(checkbox) {
            checkbox.closest('tr')?.classList.toggle('attendance-row-selected', checkbox.checked);
        });
    }

    document.addEventListener('change', function(event) {
        if (event.target && event.target.id === 'attendanceSelectAll') {
            document.querySelectorAll('#attendanceTable .attendance-row-checkbox').forEach(function(checkbox) {
                checkbox.checked = event.target.checked;
            });
            updateAttendanceSelection();
        }

        if (event.target && event.target.classList.contains('attendance-row-checkbox')) {
            updateAttendanceSelection();
        }
    });

    const clearSelectionButton = document.getElementById('attendanceClearSelection');
    if (clearSelectionButton) {
        clearSelectionButton.addEventListener('click', function() {
            document.querySelectorAll('#attendanceTable .attendance-row-checkbox, #attendanceSelectAll').forEach(function(checkbox) {
                checkbox.checked = false;
                checkbox.indeterminate = false;
            });
            updateAttendanceSelection();
        });
    }

    // Notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 1rem 1.5rem;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
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
});
</script>
@endpush
