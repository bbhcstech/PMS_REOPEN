@extends('admin.layout.app')

@section('title', 'Archived Sub Departments')

@section('content')
<style>
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .sub-archive-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .sub-archive-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
    }

    .sub-archive-shell {
        position: relative;
        max-width: 1600px;
        margin: 0 auto;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .archive-header,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-card,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .table-card,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .search-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(47, 107, 255, 0.15);
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08);
        backdrop-filter: blur(20px);
    }

    .archive-header {
        border-radius: 28px;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .header-title h1 {
        background: linear-gradient(135deg, #0F172A, #2F6BFF, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .header-title h1 {
        margin: 0 0 0.35rem;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .header-title p {
        color: #64748b;
    }

    .header-title p {
        margin: 0;
        font-weight: 500;
    }

    .header-title p i {
        color: #2F6BFF;
    }

    .badge-premium {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-left: 0.65rem;
        padding: 0.35rem 1.1rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #60A5FA, #2F6BFF);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        vertical-align: middle;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.2);
    }

    .back-button,
    .search-button,
    .reset-button {
        border: 0;
        border-radius: 999px;
        min-height: 44px;
        padding: 0.7rem 1.6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: all 0.25s ease;
    }

    .back-button,
    .search-button {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: #fff;
        box-shadow: 0 6px 20px -4px rgba(47, 107, 255, 0.35);
    }

    .back-button:hover,
    .search-button:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 10px 28px -4px rgba(47, 107, 255, 0.45);
    }

    .reset-button {
        background: #EEF2FF;
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, 0.2);
    }

    .reset-button:hover {
        color: #2F6BFF;
        background: #E0E7FF;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.1rem;
        margin-bottom: 1.5rem;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-card,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .sub-archive-page .stat-card,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .sub-archive-page .stat-card:first-of-type {
        background: #ffffff !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08) !important;
    }

    .stat-card {
        border-radius: 24px;
        padding: 1.35rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(47, 107, 255, 0.15) !important;
        border-color: rgba(47, 107, 255, 0.25) !important;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        flex-shrink: 0;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.total,
    .stat-icon.total {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.total i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.total .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.total [class*="fa-"],
    .stat-icon.total i,
    .stat-icon.total .fa,
    .stat-icon.total [class*="fa-"] {
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
        background: transparent !important;
        display: inline-block !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.parent,
    .stat-icon.parent {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.parent i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.parent .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.parent [class*="fa-"],
    .stat-icon.parent i,
    .stat-icon.parent .fa,
    .stat-icon.parent [class*="fa-"] {
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
        background: transparent !important;
        display: inline-block !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.employee,
    .stat-icon.employee {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.employee i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.employee .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.employee [class*="fa-"],
    .stat-icon.employee i,
    .stat-icon.employee .fa,
    .stat-icon.employee [class*="fa-"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        display: inline-block !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.month,
    .stat-icon.month {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.month i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.month .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-icon.month [class*="fa-"],
    .stat-icon.month i,
    .stat-icon.month .fa,
    .stat-icon.month [class*="fa-"] {
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
        background: transparent !important;
        display: inline-block !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-info h6 {
        color: #64748b;
    }

    .stat-info h6 {
        margin: 0 0 0.25rem;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .stat-info h3 {
        color: #0F172A;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 900;
    }

    .alert-premium {
        border-radius: 18px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.2rem;
        border-left: 5px solid;
    }

    .alert-success {
        background: rgba(220, 252, 231, 0.95);
        border-left-color: #22c55e;
        color: #065f46;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .search-card {
        background: linear-gradient(135deg, #fafefb, #f0fdf4);
    }

    .search-card {
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .archive-search-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .search-field {
        flex: 1 1 320px;
        position: relative;
    }

    .search-field i {
        position: absolute;
        top: 50%;
        left: 1.2rem;
        transform: translateY(-50%);
        color: #2F6BFF;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .search-field input {
        color: #0F172A;
        background: white;
    }

    .search-field input {
        width: 100%;
        min-height: 48px;
        border-radius: 999px;
        border: 1px solid rgba(47, 107, 255, 0.2);
        padding: 0.75rem 1rem 0.75rem 2.9rem;
        outline: none;
        font-weight: 600;
        transition: all 0.25s ease;
    }

    .search-field input:focus {
        border-color: #60A5FA;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.15);
    }

    .table-card {
        border-radius: 28px;
        overflow: hidden;
    }

    .card-header {
        padding: 1.35rem 1.8rem;
        border-bottom: 1px solid rgba(47, 107, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        background: transparent;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .card-header h5 {
        color: #0F172A;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 800;
    }

    .card-header h5 i {
        color: #2F6BFF;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .total-badge {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, 0.2);
    }

    .total-badge {
        border-radius: 999px;
        padding: 0.45rem 1.2rem;
        font-weight: 800;
        font-size: 0.78rem;
    }

    .table-responsive {
        padding: 1.25rem 1.8rem 1.8rem;
        overflow-x: auto;
    }

    .archive-table {
        width: 100%;
        min-width: 1000px;
        border-collapse: separate;
        border-spacing: 0 0.65rem;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .archive-table th {
        color: #0F172A;
        border-bottom: 2px solid rgba(47, 107, 255, 0.15);
    }

    .archive-table th {
        padding: 0.75rem 1rem;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .archive-table td {
        background: #fff;
        color: #1e293b;
    }

    .archive-table td {
        padding: 1rem;
        vertical-align: middle;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .archive-table tbody tr td:first-child {
        border-radius: 16px 0 0 16px;
    }

    .archive-table tbody tr td:last-child {
        border-radius: 0 16px 16px 0;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .record-title {
        color: #0F172A;
    }

    .record-title {
        font-weight: 800;
    }

    .record-sub {
        color: #94a3b8;
        font-size: 0.8rem;
        margin-top: 0.2rem;
    }

    .pill {
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 750;
    }

    .pill.parent { background: #e0f2fe; color: #0369a1; border: 1px solid rgba(3, 105, 161, 0.2); }
    .pill.employee { background: #f0fdf4; color: #15803d; border: 1px solid rgba(21, 128, 61, 0.2); }
    .pill.archived { background: #f8fafc; color: #4b5563; border: 1px solid #e2e8f0; }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.45rem;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 12px;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px) scale(1.06);
        color: #fff;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .btn-view { background: linear-gradient(145deg, #38bdf8, #0284c7); }
    .btn-restore { background: linear-gradient(145deg, #4F83FF, #2F6BFF); }

    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 24px;
        margin: 0 auto 1.25rem;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        color: #2F6BFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        border: 1px solid rgba(47, 107, 255, 0.2);
        box-shadow: 0 10px 25px -8px rgba(47, 107, 255, 0.25);
        animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    .pagination-container {
        padding: 1rem 1.8rem;
        border-top: 1px solid rgba(47, 107, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .sub-archive-page {
            padding: 1rem;
        }

        .archive-header {
            padding: 1.25rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ===== DARK MODE OVERRIDES ===== */
    html[data-pms-theme="dark"] .sub-archive-page,
    html[data-bs-theme="dark"] .sub-archive-page,
    html[data-theme="dark"] .sub-archive-page,
    html.dark .sub-archive-page,
    body.dark-mode .sub-archive-page {
        background: transparent !important;
        color: #ffffff;
    }

    /* Archive Header in Dark Mode */
    html[data-pms-theme="dark"] .sub-archive-page .archive-header,
    html[data-bs-theme="dark"] .sub-archive-page .archive-header,
    html[data-theme="dark"] .sub-archive-page .archive-header,
    html.dark .sub-archive-page .archive-header,
    body.dark-mode .sub-archive-page .archive-header {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.15) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .header-title h1,
    html[data-bs-theme="dark"] .sub-archive-page .header-title h1,
    html[data-theme="dark"] .sub-archive-page .header-title h1,
    html.dark .sub-archive-page .header-title h1 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: none !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .header-title p,
    html[data-bs-theme="dark"] .sub-archive-page .header-title p,
    html[data-theme="dark"] .sub-archive-page .header-title p,
    html.dark .sub-archive-page .header-title p {
        color: #94A3B8 !important;
    }

    /* Stat Cards in Dark Mode */
    html[data-pms-theme="dark"] .sub-archive-page .stat-card,
    html[data-pms-theme="dark"] .sub-archive-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .sub-archive-page .stat-card,
    html[data-bs-theme="dark"] .sub-archive-page .stat-card:first-of-type,
    html[data-theme="dark"] .sub-archive-page .stat-card,
    html[data-theme="dark"] .sub-archive-page .stat-card:first-of-type,
    html.dark .sub-archive-page .stat-card,
    html.dark .sub-archive-page .stat-card:first-of-type,
    body.dark-mode .sub-archive-page .stat-card,
    body.dark-mode .sub-archive-page .stat-card:first-of-type {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.15) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .stat-info h6,
    html[data-bs-theme="dark"] .sub-archive-page .stat-info h6,
    html[data-theme="dark"] .sub-archive-page .stat-info h6,
    html.dark .sub-archive-page .stat-info h6 {
        color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .stat-info h3,
    html[data-bs-theme="dark"] .sub-archive-page .stat-info h3,
    html[data-theme="dark"] .sub-archive-page .stat-info h3,
    html.dark .sub-archive-page .stat-info h3 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Stat Icons in Dark Mode */
    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.total,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.total,
    html[data-theme="dark"] .sub-archive-page .stat-icon.total,
    html.dark .sub-archive-page .stat-icon.total {
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.3) 0%, rgba(217, 119, 6, 0.5) 100%) !important;
        border: 1.5px solid rgba(251, 191, 36, 0.65) !important;
        color: #fde68a !important;
        -webkit-text-fill-color: #fde68a !important;
    }
    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.total *,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.total *,
    html[data-theme="dark"] .sub-archive-page .stat-icon.total *,
    html.dark .sub-archive-page .stat-icon.total * {
        color: #fde68a !important;
        -webkit-text-fill-color: #fde68a !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.parent,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.parent,
    html[data-theme="dark"] .sub-archive-page .stat-icon.parent,
    html.dark .sub-archive-page .stat-icon.parent {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.3) 0%, rgba(2, 132, 199, 0.5) 100%) !important;
        border: 1.5px solid rgba(56, 189, 248, 0.65) !important;
        color: #bae6fd !important;
        -webkit-text-fill-color: #bae6fd !important;
    }
    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.parent *,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.parent *,
    html[data-theme="dark"] .sub-archive-page .stat-icon.parent *,
    html.dark .sub-archive-page .stat-icon.parent * {
        color: #bae6fd !important;
        -webkit-text-fill-color: #bae6fd !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.employee,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.employee,
    html[data-theme="dark"] .sub-archive-page .stat-icon.employee,
    html.dark .sub-archive-page .stat-icon.employee {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.3) 0%, rgba(47, 107, 255, 0.5) 100%) !important;
        border: 1.5px solid rgba(79, 131, 255, 0.65) !important;
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
    }
    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.employee *,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.employee *,
    html[data-theme="dark"] .sub-archive-page .stat-icon.employee *,
    html.dark .sub-archive-page .stat-icon.employee * {
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.month,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.month,
    html[data-theme="dark"] .sub-archive-page .stat-icon.month,
    html.dark .sub-archive-page .stat-icon.month {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.3) 0%, rgba(79, 70, 229, 0.5) 100%) !important;
        border: 1.5px solid rgba(129, 140, 248, 0.65) !important;
        color: #c7d2fe !important;
        -webkit-text-fill-color: #c7d2fe !important;
    }
    html[data-pms-theme="dark"] .sub-archive-page .stat-icon.month *,
    html[data-bs-theme="dark"] .sub-archive-page .stat-icon.month *,
    html[data-theme="dark"] .sub-archive-page .stat-icon.month *,
    html.dark .sub-archive-page .stat-icon.month * {
        color: #c7d2fe !important;
        -webkit-text-fill-color: #c7d2fe !important;
    }

    /* Search Card in Dark Mode */
    html[data-pms-theme="dark"] .sub-archive-page .search-card,
    html[data-bs-theme="dark"] .sub-archive-page .search-card,
    html[data-theme="dark"] .sub-archive-page .search-card,
    html.dark .sub-archive-page .search-card,
    body.dark-mode .sub-archive-page .search-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .search-field input,
    html[data-bs-theme="dark"] .sub-archive-page .search-field input,
    html[data-theme="dark"] .sub-archive-page .search-field input,
    html.dark .sub-archive-page .search-field input {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .search-field input::placeholder,
    html[data-bs-theme="dark"] .sub-archive-page .search-field input::placeholder {
        color: #94A3B8 !important;
    }

    /* Table Card in Dark Mode */
    html[data-pms-theme="dark"] .sub-archive-page .table-card,
    html[data-bs-theme="dark"] .sub-archive-page .table-card,
    html[data-theme="dark"] .sub-archive-page .table-card,
    html.dark .sub-archive-page .table-card,
    body.dark-mode .sub-archive-page .table-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .card-header,
    html[data-bs-theme="dark"] .sub-archive-page .card-header {
        border-bottom: 1px solid rgba(79, 131, 255, 0.12) !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .card-header h5,
    html[data-bs-theme="dark"] .sub-archive-page .card-header h5 {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .total-badge,
    html[data-bs-theme="dark"] .sub-archive-page .total-badge {
        background: #141B3D !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .archive-table th,
    html[data-bs-theme="dark"] .sub-archive-page .archive-table th {
        color: #CBD5E1 !important;
        border-bottom: 2px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .archive-table td,
    html[data-bs-theme="dark"] .sub-archive-page .archive-table td {
        background: #141B3D !important;
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .record-title,
    html[data-bs-theme="dark"] .sub-archive-page .record-title {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .record-sub,
    html[data-bs-theme="dark"] .sub-archive-page .record-sub {
        color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .sub-archive-page .pill.archived,
    html[data-bs-theme="dark"] .sub-archive-page .pill.archived {
        background: #0F1530 !important;
        color: #cbd5e1 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
    }
    /* Light-theme contrast for the filled archive controls only. */
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .sub-archive-page :is(.back-button, .search-button, .badge-premium, .action-btn.btn-view, .action-btn.btn-restore),
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .sub-archive-page :is(.back-button, .search-button, .badge-premium, .action-btn.btn-view, .action-btn.btn-restore) *,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .sub-archive-page :is(.back-button, .search-button, .badge-premium, .action-btn.btn-view, .action-btn.btn-restore) i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .sub-archive-page .archive-header .badge-premium {
        width: auto !important;
        height: auto !important;
        min-height: 0 !important;
        padding: 0.35rem 1.1rem !important;
        border-radius: 999px !important;
        line-height: 1.5 !important;
    }
    /* Preserve readable glyphs and values on the pale table badges in dark theme. */
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .sub-archive-page .archive-table .pill.parent {
        --archive-pill-foreground: #0369a1;
    }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .sub-archive-page .archive-table .pill.employee {
        --archive-pill-foreground: #15803d;
    }
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .sub-archive-page .archive-table .pill:is(.parent, .employee),
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .sub-archive-page .archive-table .pill:is(.parent, .employee) > i,
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .sub-archive-page .archive-table .pill:is(.parent, .employee) > i::before {
        color: var(--archive-pill-foreground) !important;
        -webkit-text-fill-color: var(--archive-pill-foreground) !important;
    }
</style>

<div class="sub-archive-page">
    <div class="sub-archive-shell">
        <div class="archive-header">
            <div class="header-title">
                <h1>
                    <i class="fas fa-archive me-2"></i>Archived Sub Departments
                    <span class="badge-premium"><i class="fas fa-history"></i> ARCHIVE</span>
                </h1>
                <p><i class="fas fa-info-circle me-1"></i>Restore archived sub department records whenever they need to return to the active list.</p>
            </div>
            <a href="{{ route('departments.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>Back to Active Sub Departments
            </a>
        </div>

        @if (session('success'))
            <div class="alert-premium alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @php
            $archivedCollection = $departments->getCollection();
            $totalArchived = method_exists($departments, 'total') ? $departments->total() : $departments->count();
            $parentCount = $archivedCollection->pluck('parent_dpt_id')->filter()->unique()->count();
            $employeeCount = $archivedCollection->sum('employee_details_count');
            $monthlyArchived = $archivedCollection->filter(fn ($department) => $department->archived_at && $department->archived_at->isCurrentMonth())->count();
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
                <div class="stat-icon parent"><i class="fas fa-building"></i></div>
                <div class="stat-info">
                    <h6>Parent Groups</h6>
                    <h3>{{ $parentCount }}</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon employee"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h6>Employees</h6>
                    <h3>{{ $employeeCount }}</h3>
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

        <div class="search-card">
            <form method="GET" action="{{ route('departments.archive') }}" class="archive-search-bar">
                <div class="search-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search archived sub departments...">
                </div>
                <button type="submit" class="search-button">
                    <i class="fas fa-filter"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('departments.archive') }}" class="reset-button">
                        <i class="fas fa-times"></i>Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Archived Sub Department Records</h5>
                <span class="total-badge"><i class="fas fa-database me-1"></i>Total: {{ $totalArchived }}</span>
            </div>

            <div class="table-responsive">
                @if($departments->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-archive"></i></div>
                        <h5 class="fw-bold">No Archived Sub Departments Found</h5>
                        <p class="text-muted">There are no sub departments in the archive at the moment.</p>
                        <a href="{{ route('departments.index') }}" class="back-button mt-3">
                            <i class="fas fa-arrow-left"></i>Back to Active Sub Departments
                        </a>
                    </div>
                @else
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sub Department</th>
                                <th>Code</th>
                                <th>Parent Department</th>
                                <th>Employees</th>
                                <th>Archived On</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $department)
                                <tr>
                                    <td>{{ $departments->firstItem() + $index }}</td>
                                    <td>
                                        <div class="record-title">{{ $department->dpt_name }}</div>
                                        <div class="record-sub">Sub department record</div>
                                    </td>
                                    <td>
                                        <span class="pill archived"><i class="fas fa-code"></i>{{ $department->dpt_code ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="pill parent"><i class="fas fa-building"></i>{{ $department->parent?->dpt_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="pill employee"><i class="fas fa-users"></i>{{ $department->employee_details_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="pill archived">
                                            <i class="far fa-calendar-alt"></i>
                                            {{ $department->archived_at ? $department->archived_at->format('d M Y h:i A') : 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('departments.show', $department->id) }}" class="action-btn btn-view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('departments.restore', $department->id) }}" method="POST" onsubmit="return confirm('Restore this sub department to the active list?');">
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

                    @if($departments->hasPages())
                        <div class="pagination-container">
                            <div class="text-muted">
                                Showing {{ $departments->firstItem() ?? 0 }} to {{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }}
                            </div>
                            <div>{{ $departments->links() }}</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
