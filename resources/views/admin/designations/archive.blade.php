@extends('admin.layout.app')

@section('title', 'Archived Designations')

@section('content')
<style>
    :root {
        --primary-blue: #059669;
        --primary-teal: #10b981;
        --primary-green: #34d399;
        --bg-light: #f8fafc;
        --glass-border: rgba(16, 185, 129, 0.15);
        --card-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
            0px 8px 40px rgba(16, 185, 129, 0.04),
            0px 20px 60px rgba(5, 150, 105, 0.06);
        --card-shadow-hover: 0px 20px 50px rgba(16, 185, 129, 0.1),
            0px 30px 80px rgba(5, 150, 105, 0.15);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spring-transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* ===== MAIN CONTAINER ===== */
    .archive-container {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
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
        background: radial-gradient(circle, rgba(52, 211, 153, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    .orb-3 {
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(5, 150, 105, 0.08) 0%, transparent 70%);
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
    .archive-header {
        background: rgba(255, 255, 255, 0.95);
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

    .archive-header:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(16, 185, 129, 0.25);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-title h1 {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0a2e1f, #059669, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 0.25rem;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
        margin: 0;
    }

    .badge-premium {
        background: linear-gradient(135deg, #34d399, #059669);
        color: white;
        padding: 0.35rem 1.2rem;
        border-radius: 40px;
        font-size: 0.68rem;
        font-weight: 800;
        margin-left: 0.75rem;
        display: inline-block;
        letter-spacing: 0.06em;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
    }

    .back-button {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        padding: 0.7rem 1.6rem;
        border-radius: 40px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 0.9rem;
        transition: var(--spring-transition);
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.35);
    }

    .back-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px -4px rgba(5, 150, 105, 0.45);
        color: white;
    }

    /* ===== STATS CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card,
    .archive-container .stat-card,
    .archive-container .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 1.5rem;
        transition: var(--spring-transition);
        border: 1px solid rgba(16, 185, 129, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        animation: cardStagger 0.6s ease forwards;
        opacity: 0;
        position: relative;
        overflow: hidden;
        color: #0a2e1f !important;
    }

    .archive-container .stat-card:first-of-type *,
    .archive-container .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .archive-container .stat-card h3,
    .archive-container .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .archive-container .stat-card h6,
    .archive-container .stat-card span,
    .archive-container .stat-card:first-of-type span,
    .archive-container .stat-card:first-of-type h6 {
        color: #4b5563 !important;
        -webkit-text-fill-color: #4b5563 !important;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #34d399, #059669);
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
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
    }

    .stat-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.total {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.levels {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.top {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.month {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .stat-info {
        flex: 1;
        min-width: 0;
    }

    .stat-info h6 {
        font-size: 0.7rem;
        color: #64748b;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.06em;
    }

    .stat-info h3 {
        font-size: 2.1rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== ALERTS ===== */
    .alert-premium {
        border-radius: 20px;
        border-left: 6px solid;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
    }

    .alert-success {
        background: rgba(220, 252, 231, 0.95);
        border-left-color: var(--primary-green);
        color: #065f46;
    }

    .alert-error {
        background: rgba(254, 226, 226, 0.95);
        border-left-color: #ef4444;
        color: #991b1b;
    }

    .alert-premium i {
        font-size: 1.25rem;
    }

    /* ===== TABLE CARD ===== */
    .table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--glass-border);
        transition: var(--spring-transition);
    }

    .table-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .card-header {
        background: transparent;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .card-header h5 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1.15rem;
    }

    .card-header h5 i {
        color: var(--primary-teal);
    }

    .header-subtitle {
        color: #64748b;
        font-size: 0.85rem;
        margin: 0.25rem 0 0;
        font-weight: 500;
    }

    .total-badge {
        background: linear-gradient(145deg, #ecfdf5, #d1fae5);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
        padding: 0.45rem 1.3rem;
        border-radius: 40px;
        font-size: 0.78rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        letter-spacing: 0.02em;
    }

    .total-badge i {
        font-size: 0.85rem;
        color: #059669;
    }

    /* ===== SEARCH BAR ===== */
    .archive-search-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 1rem 2rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #fafefb, #f0fdf4);
    }

    .search-input {
        position: relative;
        flex: 1 1 280px;
    }

    .search-input i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #059669;
        font-size: 0.9rem;
    }

    .search-input input {
        width: 100%;
        min-height: 44px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 40px;
        background: #fff;
        padding: 0.6rem 1rem 0.6rem 44px;
        font-weight: 600;
        font-size: 0.9rem;
        outline: none;
        transition: var(--transition-smooth);
        color: #0a2e1f;
    }

    .search-input input::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .search-input input:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
    }

    .per-page-select {
        min-height: 44px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 40px;
        background: #fff;
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        font-size: 0.9rem;
        outline: none;
        width: 100px;
        color: #0a2e1f;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .per-page-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
    }

    .search-button,
    .reset-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 44px;
        padding: 0.6rem 1.6rem;
        border: 0;
        border-radius: 40px;
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        transition: var(--spring-transition);
        cursor: pointer;
    }

    .search-button {
        background: linear-gradient(145deg, #34d399, #059669);
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.25);
    }

    .reset-button {
        background: #e6f3ec;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
        box-shadow: none;
    }

    .search-button:hover {
        transform: translateY(-2px);
        color: #fff;
        box-shadow: 0 8px 25px rgba(5, 150, 105, 0.35);
    }

    .reset-button:hover {
        transform: translateY(-2px);
        background: #d1fae5;
        color: #059669;
    }

    /* ===== TABLE ===== */
    .table-responsive {
        overflow-x: auto;
        padding: 0 1.5rem 1.5rem;
    }

    .archive-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.6rem;
        min-width: 1050px;
    }

    .archive-table thead th {
        background: transparent;
        color: #0a2e1f;
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        padding: 0.75rem 1rem;
        border-bottom: 2px solid rgba(16, 185, 129, 0.15);
        letter-spacing: 0.08em;
        white-space: nowrap;
    }

    .archive-table thead th i {
        margin-right: 6px;
        color: #059669;
    }

    .archive-table tbody tr {
        background: white;
        border-radius: 16px;
        transition: var(--transition-smooth);
        animation: rowFade 0.4s ease forwards;
        opacity: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    @keyframes rowFade {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .archive-table tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.08);
    }

    .archive-table td {
        padding: 1rem 1rem;
        color: #1e293b;
        font-size: 0.875rem;
        vertical-align: middle;
        border-top: 1px solid transparent;
        border-bottom: 1px solid transparent;
    }

    .archive-table tbody tr:first-child td {
        border-top: none;
    }

    .archive-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ===== BADGES ===== */
    .code-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.35rem 1rem;
        border-radius: 40px;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid rgba(5, 150, 105, 0.2);
        font-size: 0.75rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .code-badge i {
        color: #059669;
        font-size: 0.7rem;
    }

    .deleted-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.3rem 0.9rem;
        border-radius: 40px;
        background: #f8fafc;
        color: #4b5563;
        border: 1px solid #e2e8f0;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .deleted-badge i {
        color: #94a3b8;
    }

    /* Level Badges & High-Contrast Level Pills */
    .level-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.35rem 1rem;
        border-radius: 40px;
        font-size: 0.78rem;
        font-weight: 750;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.25s ease;
    }

    .level-badge i {
        font-size: 0.75rem;
    }

    .level-badge.l0 {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0) !important;
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
        border: 1px solid rgba(51, 65, 85, 0.2) !important;
    }
    .level-badge.l0 i { color: #475569 !important; -webkit-text-fill-color: #475569 !important; }

    .level-badge.l1 {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5) !important;
        color: #065f46 !important;
        -webkit-text-fill-color: #065f46 !important;
        border: 1px solid rgba(5, 150, 105, 0.25) !important;
    }
    .level-badge.l1 i { color: #059669 !important; -webkit-text-fill-color: #059669 !important; }

    .level-badge.l2 {
        background: linear-gradient(135deg, #f0fdf4, #bbf7d0) !important;
        color: #15803d !important;
        -webkit-text-fill-color: #15803d !important;
        border: 1px solid rgba(22, 163, 74, 0.25) !important;
    }
    .level-badge.l2 i { color: #16a34a !important; -webkit-text-fill-color: #16a34a !important; }

    .level-badge.l3 {
        background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        color: #1e40af !important;
        -webkit-text-fill-color: #1e40af !important;
        border: 1px solid rgba(37, 99, 235, 0.25) !important;
    }
    .level-badge.l3 i { color: #2563eb !important; -webkit-text-fill-color: #2563eb !important; }

    .level-badge.l4 {
        background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
        color: #92400e !important;
        -webkit-text-fill-color: #92400e !important;
        border: 1px solid rgba(217, 119, 6, 0.25) !important;
    }
    .level-badge.l4 i { color: #d97706 !important; -webkit-text-fill-color: #d97706 !important; }

    .level-badge.l5 {
        background: linear-gradient(135deg, #fff7ed, #ffedd5) !important;
        color: #c2410c !important;
        -webkit-text-fill-color: #c2410c !important;
        border: 1px solid rgba(234, 88, 12, 0.25) !important;
    }
    .level-badge.l5 i { color: #ea580c !important; -webkit-text-fill-color: #ea580c !important; }

    .level-badge.l6 {
        background: linear-gradient(135deg, #fef2f2, #fee2e2) !important;
        color: #b91c1c !important;
        -webkit-text-fill-color: #b91c1c !important;
        border: 1px solid rgba(220, 38, 38, 0.25) !important;
    }
    .level-badge.l6 i { color: #dc2626 !important; -webkit-text-fill-color: #dc2626 !important; }

    .level-badge.default {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
        border: 1px solid rgba(100, 116, 139, 0.2) !important;
    }

    .parent-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.3rem 1rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
        background: #e0f2fe;
        color: #0369a1;
    }

    .parent-badge i {
        font-size: 0.65rem;
    }

    .parent-badge.top-level {
        background: #fef3c7;
        color: #d97706;
    }

    .parent-badge.top-level i {
        color: #d97706;
    }

    /* ===== DESIGNATION CELL ===== */
    .designation-cell {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .designation-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .designation-name {
        font-weight: 700;
        color: #0a2e1f;
        font-size: 0.95rem;
    }

    .designation-meta {
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* ===== ACTION BUTTONS ===== */
    .action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: var(--spring-transition);
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .action-btn:hover {
        transform: translateY(-2px) scale(1.08);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-restore {
        background: linear-gradient(145deg, #34d399, #059669);
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        border: 1px solid rgba(16, 185, 129, 0.2);
        animation: float 4s ease-in-out infinite;
        box-shadow: 0 10px 25px -8px rgba(16, 185, 129, 0.25);
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    .empty-icon i {
        font-size: 2.2rem;
        color: #059669;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .empty-icon i {
        font-size: 2.5rem;
        color: white;
    }

    .empty-state h5 {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 1.5rem;
    }

    /* ===== PAGINATION ===== */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.9rem;
        flex-wrap: wrap;
    }

    .pagination-container .pagination {
        margin: 0;
    }

    .pagination-container .page-link {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        padding: 0.4rem 0.9rem;
        transition: var(--transition-smooth);
    }

    .pagination-container .page-link:hover {
        background: var(--primary-teal);
        color: white;
        border-color: var(--primary-teal);
    }

    .pagination-container .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        border-color: var(--primary-teal);
        color: white;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .archive-container {
            padding: 1.5rem 1.25rem;
        }
        .archive-header {
            padding: 1.5rem;
        }
        .header-title h1 {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 768px) {
        .archive-container {
            padding: 1rem;
        }
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 0.9rem;
        }
        .archive-header,
        .card-header,
        .archive-search-bar,
        .pagination-container {
            flex-direction: column;
            align-items: stretch;
        }
        .search-input,
        .search-button,
        .reset-button,
        .back-button,
        .per-page-select {
            width: 100%;
        }
        .archive-search-bar {
            padding: 1rem;
        }
        .card-header {
            padding: 1.25rem;
        }
        .table-responsive {
            padding: 0 0.75rem 0.75rem;
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
        .badge-premium {
            font-size: 0.55rem;
            padding: 0.2rem 0.8rem;
        }
        .archive-table td,
        .archive-table th {
            padding: 0.6rem 0.7rem;
            font-size: 0.75rem;
        }
        .designation-avatar {
            width: 38px;
            height: 38px;
            font-size: 0.9rem;
        }
        .designation-name {
            font-size: 0.8rem;
        }
        .level-badge {
            font-size: 0.6rem;
            padding: 0.2rem 0.7rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .archive-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .archive-header,
    html[data-pms-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .table-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .archive-search-bar {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .archive-table tbody tr {
        background: #102119;
    }

    html[data-pms-theme="dark"] .archive-table tbody tr:hover {
        background: #183026;
    }

    html[data-pms-theme="dark"] .card-header,
    html[data-pms-theme="dark"] .archive-table thead th,
    html[data-pms-theme="dark"] .pagination-container {
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .card-header h5,
    html[data-pms-theme="dark"] .designation-name,
    html[data-pms-theme="dark"] .stat-info h3,
    html[data-pms-theme="dark"] .empty-state h5 {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .header-title p,
    html[data-pms-theme="dark"] .header-subtitle,
    html[data-pms-theme="dark"] .designation-meta,
    html[data-pms-theme="dark"] .stat-info h6,
    html[data-pms-theme="dark"] .archive-table thead th {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .search-input input,
    html[data-pms-theme="dark"] .per-page-select {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .search-input input::placeholder {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .search-input input:focus,
    html[data-pms-theme="dark"] .per-page-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    html[data-pms-theme="dark"] .code-badge {
        background: #183026;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .deleted-badge {
        background: #183026;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .parent-badge {
        background: #183026;
        color: #7af0b5;
    }

    html[data-pms-theme="dark"] .parent-badge.top-level {
        background: #2d1f0a;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .designation-avatar {
        background: linear-gradient(135deg, #0f744c, #10b981);
    }

    html[data-pms-theme="dark"] .stat-icon.total {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: #94a3b8;
    }

    html[data-pms-theme="dark"] .stat-icon.levels {
        background: linear-gradient(135deg, #064e3b, #047857);
        color: #34d399;
    }

    html[data-pms-theme="dark"] .stat-icon.top {
        background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
        color: #93bbfc;
    }

    html[data-pms-theme="dark"] .stat-icon.month {
        background: linear-gradient(135deg, #78350f, #d97706);
        color: #fcd34d;
    }

    html[data-pms-theme="dark"] .pagination-container .page-link {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .pagination-container .page-link:hover {
        background: #34d399;
        color: #07130d;
        border-color: #34d399;
    }

    /* Dark mode level badges - keep white text */
    html[data-pms-theme="dark"] .level-badge {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .level-badge.l0 { background: #1e293b; }
    html[data-pms-theme="dark"] .level-badge.l1 { background: #0f744c; }
    html[data-pms-theme="dark"] .level-badge.l2 { background: #10b981; }
    html[data-pms-theme="dark"] .level-badge.l3 { background: #3b82f6; }
    html[data-pms-theme="dark"] .level-badge.l4 { background: #f59e0b; }
    html[data-pms-theme="dark"] .level-badge.l5 { background: #f97316; }
    html[data-pms-theme="dark"] .level-badge.l6 { background: #ef4444; }
    html[data-pms-theme="dark"] .level-badge.default { background: #94a3b8; }
</style>

<div class="archive-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        <!-- ===== HEADER ===== -->
        <div class="archive-header">
            <div class="header-title">
                <h1>
                    <i class="fas fa-archive me-2"></i>Archived Designations
                    <span class="badge-premium"><i class="fas fa-history me-1"></i>ARCHIVE</span>
                </h1>
                <p><i class="fas fa-info-circle me-1" style="color: var(--primary-teal);"></i>Restore archived designation records whenever they need to return to the active list.</p>
            </div>
            <a href="{{ route('designations.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>Back to Active Designations
            </a>
        </div>

        <!-- ===== ALERTS ===== -->
        @if(session('success'))
            <div class="alert-premium alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-premium alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- ===== STATS ===== -->
        @php
            $archivedCollection = $designations->getCollection();
            $totalArchived = method_exists($designations, 'total') ? $designations->total() : $designations->count();
            $levelCount = $archivedCollection->pluck('level')->filter(fn ($level) => $level !== null)->unique()->count();
            $topLevelCount = $archivedCollection->whereNull('parent_id')->count();
            $monthlyArchived = $archivedCollection->filter(fn ($designation) => $designation->archived_at && $designation->archived_at->isCurrentMonth())->count();
        @endphp

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total"><i class="fas fa-archive"></i></div>
                <div class="stat-info">
                    <h6>Total Archived</h6>
                    <h3>{{ $totalArchived }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon levels"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <h6>Levels</h6>
                    <h3>{{ $levelCount }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon top"><i class="fas fa-crown"></i></div>
                <div class="stat-info">
                    <h6>Top Level</h6>
                    <h3>{{ $topLevelCount }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon month"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <h6>This Month</h6>
                    <h3>{{ $monthlyArchived }}</h3>
                </div>
            </div>
        </div>

        <!-- ===== TABLE CARD ===== -->
        <div class="table-card">
            <div class="card-header">
                <div>
                    <h5><i class="fas fa-history me-2"></i>Archived Designation Records</h5>
                    <p class="header-subtitle">Search archived roles and restore them to the active designation list.</p>
                </div>
                <span class="total-badge"><i class="fas fa-database"></i>Total: {{ $totalArchived }}</span>
            </div>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('designations.archive') }}" class="archive-search-bar">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search archived designations...">
                </div>
                <select name="per_page" class="per-page-select">
                    @foreach([10, 20, 30, 40, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>Search
                </button>
                @if(request()->hasAny(['search', 'per_page']))
                    <a href="{{ route('designations.archive') }}" class="reset-button">
                        <i class="fas fa-rotate-left"></i>Reset
                    </a>
                @endif
            </form>

            <!-- Table -->
            <div class="table-responsive">
                @if($designations->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-archive"></i></div>
                        <h5>No Archived Designations Found</h5>
                        <p>There are no designations in the archive at the moment.</p>
                        <a href="{{ route('designations.index') }}" class="back-button" style="display: inline-flex;">
                            <i class="fas fa-arrow-left"></i>Back to Active Designations
                        </a>
                    </div>
                @else
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i>#</th>
                                <th><i class="fas fa-qrcode"></i>Code</th>
                                <th><i class="fas fa-briefcase"></i>Designation Details</th>
                                <th><i class="fas fa-chart-line"></i>Level</th>
                                <th><i class="fas fa-link"></i>Parent</th>
                                <th><i class="fas fa-calendar-alt"></i>Archived On</th>
                                <th class="text-center"><i class="fas fa-cog"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $index => $designation)
                                <tr>
                                    <td>{{ $designations->firstItem() + $index }}</td>
                                    <td>
                                        <span class="code-badge">
                                            <i class="fas fa-qrcode"></i>{{ $designation->unique_code ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="designation-cell">
                                            <div class="designation-avatar"><i class="fas fa-briefcase"></i></div>
                                            <div>
                                                <div class="designation-name">{{ $designation->name ?? '-' }}</div>
                                                <div class="designation-meta">ID: {{ str_pad($designation->id, 3, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($designation->level !== null)
                                            <span class="level-badge l{{ $designation->level }}">
                                                <i class="fas fa-chart-line"></i>L{{ $designation->level }}
                                            </span>
                                        @else
                                            <span class="level-badge default"><i class="fas fa-minus"></i> Not Set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($designation->parent?->name)
                                            <span class="parent-badge">
                                                <i class="fas fa-link"></i>{{ $designation->parent->name }}
                                            </span>
                                        @else
                                            <span class="parent-badge top-level">
                                                <i class="fas fa-crown"></i>Top-level
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="deleted-badge">
                                            <i class="far fa-calendar-alt"></i>
                                            {{ $designation->archived_at ? $designation->archived_at->format('d M Y h:i A') : 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <form action="{{ route('designations.restore', $designation->id) }}" method="POST" onsubmit="return confirm('Restore this designation to the active list?');">
                                                @csrf
                                                <button type="submit" class="action-btn btn-restore" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($designations->hasPages())
                        <div class="pagination-container">
                            <div>
                                <i class="fas fa-chart-simple me-1" style="color: var(--primary-teal);"></i>
                                Showing {{ $designations->firstItem() ?? 0 }} to {{ $designations->lastItem() ?? 0 }} of {{ $designations->total() }}
                            </div>
                            <div>{{ $designations->links() }}</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
