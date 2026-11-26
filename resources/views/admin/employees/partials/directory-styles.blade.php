<style>
    /* ===== BITROXIA THEME TOKENS FOR EMPLOYEE DIRECTORY ===== */
    :root {
        --emp-designation-color: #64748B;
        --emp-role-color: #1F2937;
        --emp-reports-color: #64748B;
        --emp-count-color: #6b4e9e;
        --emp-footer-text: #4b5563;
        --emp-footer-select-bg: #ffffff;
        --emp-footer-select-color: #1f2937;
        --emp-footer-select-border: #e0d7ff;
    }

    html[data-pms-theme="dark"],
    html[data-bs-theme="dark"],
    html[data-theme="dark"],
    html.dark,
    body[data-pms-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark,
    [data-pms-theme="dark"] {
        --emp-designation-color: #CBD5E1 !important;
        --emp-role-color: #FFFFFF !important;
        --emp-reports-color: #94A3B8 !important;
        --emp-count-color: #C7D2FE !important;
        --emp-footer-text: #CBD5E1 !important;
        --emp-footer-select-bg: #141B3D !important;
        --emp-footer-select-color: #EEF1FB !important;
        --emp-footer-select-border: rgba(79, 131, 255, 0.35) !important;
    }

    /* ===== LIGHT PURPLE THEME - SOFT, ANIMATED & EYE-FRIENDLY ===== */

    /* Main Background - Light Purple Gradient */
    .employee-dashboard {
        padding: 30px 35px;
        background: linear-gradient(145deg, #f9f5ff 0%, #f3ebff 50%, #f1e7ff 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        position: relative;
        overflow-x: hidden;
    }

    /* Soft Purple Overlay Effect */
    .employee-dashboard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 10% 20%, rgba(170, 140, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 90% 70%, rgba(150, 120, 250, 0.08) 0%, transparent 40%),
                    radial-gradient(circle at 30% 80%, rgba(180, 150, 255, 0.06) 0%, transparent 45%);
        pointer-events: none;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes slideDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0px); }
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }

    .animate-slideDown { animation: slideDown 0.6s ease forwards; }
    .animate-slideUp { animation: slideUp 0.6s ease forwards; }
    .animate-fadeIn { animation: fadeIn 0.8s ease forwards; }
    .animate-float { animation: float 4s ease-in-out infinite; }
    .animate-pulse { animation: pulse 2s infinite; }
    .animate-shake { animation: shake 0.5s ease forwards; }

    /* Breadcrumb - Light Glassmorphism */
    .breadcrumb-wrapper {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(139, 92, 246, 0.15);
        border-radius: 16px;
        padding: 16px 24px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.05);
    }

    .breadcrumb {
        display: flex;
        flex-wrap: wrap;
        padding: 0;
        margin-bottom: 0;
        list-style: none;
        background: transparent;
    }

    .breadcrumb-item {
        font-size: 0.95rem;
        font-weight: 500;
    }

    .breadcrumb-item a {
        color: #6d28d9;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #5b21b6;
    }

    .breadcrumb-item.active {
        color: #4c1d95;
        font-weight: 600;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #a78bfa;
        font-size: 1.2rem;
        line-height: 1;
        margin: 0 10px;
    }

    /* Header Section - Light Glassmorphism */
    .header-section {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(139, 92, 246, 0.2);
        border-radius: 20px;
        padding: 25px 30px;
        margin-bottom: 35px;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.08);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .title-wrapper {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .icon-circle {
        width: 65px;
        height: 65px;
        background: linear-gradient(145deg, #c4b5fd, #a78bfa);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 10px 20px rgba(167, 139, 250, 0.25);
    }

    .title-wrapper h1 {
        font-size: 1.9rem;
        font-weight: 700;
        color: #2d1b4e;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .subtitle {
        color: #6b4e9e;
        font-size: 0.95rem;
        margin-top: 5px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        border: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-outline-light {
        background: white;
        border: 1px solid #e0d7ff;
        color: #5b4b7a;
    }

    .btn-outline-light:hover {
        background: #f5f0ff;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(167, 139, 250, 0.15);
    }

    .btn-add {
        background: linear-gradient(145deg, #60A5FA, #10b981);
        color: white;
        box-shadow: 0 8px 18px rgba(47, 107, 255, 0.2);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(47, 107, 255, 0.3);
    }

    .btn-invite {
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        color: white;
        box-shadow: 0 8px 18px rgba(167, 139, 250, 0.25);
    }

    .btn-invite:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(167, 139, 250, 0.35);
    }

    /* Stats Grid - PERFECT 5 COLUMN ALIGNMENT */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 22px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        padding: 22px 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.06);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(to bottom, #c084fc, #a78bfa);
    }

    .stat-card.total::before { background: linear-gradient(to bottom, #818cf8, #6366f1); }
    .stat-card.active::before { background: linear-gradient(to bottom, #60A5FA, #10b981); }
    .stat-card.inactive::before { background: linear-gradient(to bottom, #f87171, #ef4444); }
    .stat-card.notice::before { background: linear-gradient(to bottom, #fbbf24, #f59e0b); }
    .stat-card.interns::before { background: linear-gradient(to bottom, #60a5fa, #3b82f6); }

    .stat-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 15px 35px rgba(139, 92, 246, 0.12);
        border-color: rgba(167, 139, 250, 0.4);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .stat-card.total .stat-icon {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .stat-card.active .stat-icon {
        background: #E0E7FF;
        color: #2F6BFF;
    }

    .stat-card.inactive .stat-icon {
        background: #fee2e2;
        color: #b91c1c;
    }

    .stat-card.notice .stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-card.interns .stat-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .stat-glow {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        filter: blur(30px);
        opacity: 0.1;
        pointer-events: none;
    }

    .stat-card.total .stat-glow { background: #6366f1; }
    .stat-card.active .stat-glow { background: #10b981; }
    .stat-card.inactive .stat-glow { background: #ef4444; }
    .stat-card.notice .stat-glow { background: #f59e0b; }
    .stat-card.interns .stat-glow { background: #3b82f6; }

    /* Filter & Export Card - PERFECT ALIGNMENT */
    .filter-export-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.06);
        transition: all 0.3s ease;
    }

    .filter-export-card:hover {
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .filter-header {
        padding: 18px 25px;
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-icon-wrapper {
        width: 44px;
        height: 44px;
        background: #ede9fe;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.2rem;
    }

    .header-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d1b4e;
        margin: 0;
    }

    .header-text p {
        font-size: 0.8rem;
        color: #6b4e9e;
        margin: 2px 0 0;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .selected-badge {
        padding: 6px 14px;
        background: #ede9fe;
        color: #6d28d9;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .btn-export-all {
        background: linear-gradient(145deg, #c084fc, #a78bfa);
        color: white;
        padding: 8px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        border: none;
        box-shadow: 0 4px 12px rgba(167, 139, 250, 0.2);
    }

    .btn-export-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(167, 139, 250, 0.3);
    }

    .btn-group {
        display: flex;
        gap: 5px;
        background: white;
        padding: 5px;
        border-radius: 12px;
        border: 1px solid rgba(167, 139, 250, 0.2);
    }

    .btn-group .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: #6b7280;
    }

    .btn-group .btn:hover:not(:disabled) {
        background: #f5f0ff;
        color: #8b5cf6;
        transform: scale(1.1);
    }

    .btn-group .btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .filter-body {
        padding: 25px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        align-items: flex-end;
    }

    .filter-item {
        display: flex;
        flex-direction: column;
    }

    .filter-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }

    .filter-select {
        width: 100%;
        height: 44px;
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 10px;
        padding: 0 16px;
        font-size: 0.9rem;
        color: #1f2937;
        transition: all 0.2s ease;
    }

    .filter-select:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
        outline: none;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-apply {
        background: linear-gradient(145deg, #8b5cf6, #7c3aed);
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        flex: 1;
        border: none;
    }

    .btn-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
    }

    .btn-reset {
        background: white;
        color: #6b7280;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        border: 1px solid #e0d7ff;
        flex: 1;
        text-decoration: none;
        text-align: center;
    }

    .btn-reset:hover {
        background: #f9fafb;
        color: #1f2937;
        border-color: #a78bfa;
    }

    /* Table Card - PERFECT ALIGNMENT */
    .table-card {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.2);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.06);
        transition: all 0.3s ease;
    }

    .table-card:hover {
        box-shadow: 0 12px 35px rgba(139, 92, 246, 0.1);
        border-color: rgba(167, 139, 250, 0.3);
    }

    .table-header {
        padding: 18px 25px;
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .title-icon-wrapper {
        width: 44px;
        height: 44px;
        background: #f3e8ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9b5de5;
        font-size: 1.2rem;
    }

    .title-text h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d1b4e;
        margin: 0;
    }

    .employee-count {
        font-size: 0.8rem;
        color: var(--emp-count-color, #6b4e9e);
    }

    .table-actions {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .btn-bulk-delete {
        background: #fee2e2;
        color: #b91c1c;
        padding: 8px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        border: 1px solid #fecaca;
    }

    .btn-bulk-delete:hover:not(:disabled) {
        background: #fecaca;
        color: #991b1b;
        transform: translateY(-2px);
    }

    .btn-bulk-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Invite modal: solid panel (the page showed through) and an email field that stays on one row. */
    #inviteModal .modal-content {
        background: #ffffff !important;
        opacity: 1 !important;
        backdrop-filter: none !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        border-radius: 18px !important;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25) !important;
    }
    #inviteModal .input-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
    }
    #inviteModal .btn-primary,
    #inviteModal .btn-primary * {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
    #inviteModal .input-group > .input-group-text {
        flex: 0 0 auto !important;
        width: auto !important;
    }
    #inviteModal .input-group > .form-control {
        flex: 1 1 auto !important;
        width: 1% !important;
        min-width: 0 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #inviteModal .modal-content,
    html body.dark-mode #inviteModal .modal-content {
        background: #0F1530 !important;
        border-color: rgba(96, 165, 250, 0.22) !important;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55) !important;
    }

    /* Archive Inactive with nothing selected: looks inactive but still explains itself when tapped. */
    .employee-dashboard .btn-bulk-delete.is-awaiting-selection { opacity: 0.6; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .employee-dashboard .btn-bulk-delete.is-awaiting-selection {
        opacity: 0.65 !important;
        background: rgba(239, 68, 68, 0.18) !important;
        border-color: rgba(248, 113, 113, 0.3) !important;
    }

    /* Mobile cards: the row checkbox must be clearly visible and large enough to tap. */
    @media (max-width: 768px) {
        .employee-dashboard #employeeTable tbody tr > td[data-pms-selection-column] {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            width: auto !important;
            max-width: none !important;
            min-width: 0 !important;
            min-height: 36px;
            padding: 4px 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        .employee-dashboard #employeeTable tbody tr > td[data-pms-selection-column] .form-check {
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
            display: flex !important;
            align-items: center !important;
        }
        html body .employee-dashboard #employeeTable tbody tr > td[data-pms-selection-column] input.employee-checkbox {
            float: none !important;
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            margin: 0 !important;
            border: 2px solid #64748B !important;
            border-radius: 6px !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) body .employee-dashboard #employeeTable tbody tr > td[data-pms-selection-column] input.employee-checkbox:not(:checked) {
            background-color: #0F1530 !important;
            border-color: #93C5FD !important;
        }
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .employee-list-table {
        border-collapse: collapse;
        table-layout: fixed;
    }

    .employee-list-table th,
    .employee-list-table td {
        box-sizing: border-box;
    }

    .table thead th {
        background: #faf7ff;
        color: #5b4b7a;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 16px;
        border-bottom: 2px solid rgba(167, 139, 250, 0.2);
        text-align: left;
        white-space: nowrap;
    }

    .employee-list-table thead th,
    .employee-list-table tbody td {
        padding: 15px 16px;
    }

    .employee-list-table tbody td {
        border-bottom: 1px solid rgba(167, 139, 250, 0.1);
        color: #374151;
        vertical-align: middle;
        line-height: 1.35;
    }

    .employee-list-table tbody tr {
        height: 82px;
    }

    .employee-list-table thead th:first-child,
    .employee-list-table tbody td:first-child {
        padding-left: 18px;
        padding-right: 10px;
        text-align: center;
    }

    .employee-list-table thead th:last-child,
    .employee-list-table tbody td:last-child {
        padding-left: 10px;
        padding-right: 18px;
        text-align: center;
    }

    .employee-list-table .form-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        margin: 0;
    }

    .employee-list-table .dropdown {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: #fcf9ff;
    }

    .table tbody tr.selected {
        background: #f5f0ff;
        border-left: 3px solid #8b5cf6;
    }

    .table-warning {
        background-color: #fffbeb !important;
    }

    .table-warning:hover {
        background-color: #fef3c7 !important;
    }

    /* Employee ID Badge */
    .employee-id-badge {
        display: inline-block;
        padding: 6px 12px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b5563;
    }

    /* Employee Info */
    .employee-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .profile-image {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);
    }

    .profile-placeholder {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #ede9fe;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 1.1rem;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);
    }

    .employee-details {
        flex: 1;
        min-width: 0;
    }

    .employee-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-designation {
        font-size: 0.82rem;
        color: var(--emp-designation-color, #64748b);
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .company-switcher-grid {
        margin-top: 18px;
    }

    .company-switch-card {
        align-items: center;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(47, 107, 255, 0.12);
        border-radius: 18px;
        box-shadow: 0 14px 34px rgba(47, 107, 255, 0.08);
        color: #070B1A;
        display: flex;
        gap: 12px;
        min-height: 86px;
        padding: 14px;
        text-decoration: none;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .company-switch-card:hover {
        border-color: rgba(47, 107, 255, 0.28);
        box-shadow: 0 20px 42px rgba(47, 107, 255, 0.14);
        color: #070B1A;
        transform: translateY(-2px);
    }

    .company-switch-card.active {
        background: linear-gradient(135deg, #EEF2FF, #ffffff);
        border-color: rgba(47, 107, 255, 0.42);
        box-shadow: 0 22px 46px rgba(47, 107, 255, 0.16);
    }

    .company-switch-icon,
    .employee-company-logo {
        align-items: center;
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC) !important;
        border-radius: 14px;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: inline-flex;
        flex: 0 0 auto;
        font-weight: 900;
        justify-content: center;
        overflow: hidden;
    }

    .company-switch-card .company-switch-icon,
    .company-switch-card .company-switch-icon *,
    .company-switch-card .company-switch-icon i,
    .company-switch-icon i,
    .company-switch-icon svg {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        fill: #ffffff !important;
    }

    .company-switch-icon {
        height: 48px;
        width: 48px;
    }

    .company-switch-icon img,
    .employee-company-logo img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .company-switch-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .company-switch-meta strong {
        color: #070B1A;
        font-size: .9rem;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .company-switch-meta small {
        color: #52645a;
        font-size: .76rem;
        font-weight: 700;
        margin-top: 4px;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark,
        body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode)
        #employeeTable .employee-company-chip > .employee-company-name {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    .employee-company-chip {
        align-items: center;
        display: inline-flex;
        gap: 8px;
        max-width: 180px;
        min-width: 0;
    }

    .employee-company-chip > span:last-child {
        color: #070B1A;
        font-size: .82rem;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-company-logo {
        height: 32px;
        width: 32px;
        font-size: .72rem;
    }

    .company-module-rail {
        align-items: center;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(47, 107, 255, 0.12);
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(47, 107, 255, 0.08);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 14px;
    }

    .company-module-rail span {
        color: #52645a;
        font-size: .82rem;
        font-weight: 900;
        margin-right: 4px;
        text-transform: uppercase;
    }

    .company-module-rail a {
        align-items: center;
        background: #f4faf6;
        border: 1px solid rgba(47, 107, 255, 0.12);
        border-radius: 12px;
        color: #2F6BFF;
        display: inline-flex;
        font-size: .84rem;
        font-weight: 900;
        gap: 7px;
        min-height: 38px;
        padding: 8px 12px;
        text-decoration: none;
    }

    .company-module-rail a:hover {
        background: #EEF2FF;
        color: #2F6BFF;
    }

    .badge.bg-primary {
        background: #ede9fe !important;
        color: #6d28d9;
    }

    .badge.bg-info {
        background: #dbeafe !important;
        color: #2563eb;
    }

    .badge.bg-warning {
        background: #fef3c7 !important;
        color: #b45309;
    }

    /* Email */
    .email-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .email-icon {
        color: #8b5cf6;
        font-size: 0.9rem;
    }

    .email-link {
        color: #6b7280;
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-decoration: none;
        font-size: 0.9rem;
        text-overflow: ellipsis;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .email-link:hover {
        color: #6d28d9;
        text-decoration: underline;
    }

    /* Role Info */
    .role-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .role-name {
        font-weight: 600;
        color: var(--emp-role-color, #1f2937);
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .reports-to {
        font-size: 0.8rem;
        color: var(--emp-reports-color, #64748b);
        display: flex;
        align-items: center;
        gap: 5px;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .reports-to i {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        font-style: normal !important;
        display: inline-block !important;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-active {
        background: #E0E7FF;
        color: #065f46;
        border: 1px solid #C7D2FE;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .status-unknown {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-active .status-dot {
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(47, 107, 255, 0.2);
    }

    .status-inactive .status-dot {
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }

    /* Action Dropdown */
    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: scale(1.1);
    }

    .dropdown-menu {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 14px;
        padding: 8px;
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.1);
    }

    .dropdown-item {
        padding: 10px 16px;
        color: #374151;
        font-size: 0.9rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 10px;
    }

    .dropdown-item:hover {
        background: #f5f0ff;
        color: #6d28d9;
    }

    .dropdown-divider {
        border-top: 1px solid #f0e7ff;
        margin: 8px 0;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state i {
        color: #d4c2ff;
    }

    .empty-state h5 {
        color: #6b4e9e;
        font-weight: 600;
    }

    .empty-state p {
        color: #9ca3af;
    }

    /* Table Footer - PERFECT ALIGNMENT */
    .table-footer {
        padding: 18px 25px;
        background: #faf7ff;
        border-top: 1px solid rgba(167, 139, 250, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .footer-left, .footer-center, .footer-right {
        display: flex;
        align-items: center;
    }

    .show-entries {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .show-entries select {
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 8px;
        color: #1f2937;
        padding: 6px 12px;
        width: 70px;
    }

    .show-entries select:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }

    .pagination-info {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .pagination {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .page-btn {
        min-width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: white;
        border: 1px solid #e0d7ff;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .page-btn:hover:not(.disabled) {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #6d28d9;
        transform: scale(1.05);
    }

    .page-btn.active {
        background: #8b5cf6;
        border-color: #8b5cf6;
        color: white;
    }

    .page-btn.disabled {
        background: #f3f4f6;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }

    .page-dots {
        color: #9ca3af;
        padding: 0 5px;
    }

    /* Modal Styles */
    .modal-content {
        background: white;
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 20px;
    }

    .modal-header {
        background: #faf7ff;
        border-bottom: 1px solid rgba(167, 139, 250, 0.2);
        padding: 20px 25px;
    }

    .modal-header.bg-warning {
        background: #fffbeb !important;
    }

    .modal-title {
        color: #2d1b4e;
        font-weight: 600;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        border-top: 1px solid rgba(167, 139, 250, 0.2);
        padding: 20px 25px;
    }

    .form-control, .input-group-text {
        background: white;
        border: 1px solid #e0d7ff;
        color: #1f2937;
    }

    .form-control:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }

    .input-group-text {
        background: #f5f0ff;
        color: #8b5cf6;
    }

    .nav-tabs {
        border-bottom: 1px solid #e0d7ff;
    }

    .nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        padding: 12px 20px;
    }

    .nav-tabs .nav-link.active {
        background: transparent;
        color: #6d28d9;
        border-bottom: 2px solid #8b5cf6;
    }

    .list-group-item {
        background: white;
        border: 1px solid #e0d7ff;
        color: #374151;
    }

    /* Form Check */
    .form-check-input {
        background: white;
        border: 2px solid #d4c2ff;
    }

    .form-check-input:checked {
        background-color: #8b5cf6;
        border-color: #8b5cf6;
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        border-color: #8b5cf6;
    }

    .form-check-input:disabled {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }

    /* Select2 Customization */
    .select2-container--default .select2-selection--single {
        background: white;
        border: 1px solid #e0d7ff;
        border-radius: 10px;
        height: 44px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1f2937;
        line-height: 44px;
        padding-left: 16px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .select2-dropdown {
        background: white;
        border: 1px solid #e0d7ff;
    }

    .select2-results__option {
        color: #1f2937;
    }

    .select2-results__option--highlighted {
        background: #f5f0ff !important;
        color: #6d28d9 !important;
    }

    /* Responsive - PERFECT ALIGNMENT */
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .employee-dashboard {
            padding: 25px 30px;
        }
    }

    @media (max-width: 992px) {
        .employee-dashboard {
            padding: 20px 25px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .employee-dashboard {
            padding: 15px 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table thead {
            display: none;
        }

        .employee-list-table tbody tr {
            display: block;
            height: auto;
            margin-bottom: 14px;
            border: 1px solid rgba(167, 139, 250, 0.2);
            border-radius: 16px;
            padding: 14px 16px;
        }

        .employee-list-table tbody td {
            display: block;
            padding: 9px 0 9px 45%;
            border: none;
            position: relative;
        }

        .employee-list-table tbody td:before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 40%;
            font-weight: 600;
            color: #5b4b7a;
        }

        .table-footer {
            flex-direction: column;
            align-items: center;
        }

        .footer-left, .footer-center, .footer-right {
            width: 100%;
            justify-content: center;
        }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f5f0ff;
    }

    ::-webkit-scrollbar-thumb {
        background: #d4c2ff;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #b79aff;
    }

    /* Final employee action polish */
    .employee-dashboard .action-buttons {
        align-items: center !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        justify-content: flex-end !important;
        margin-left: auto !important;
        overflow: visible !important;
    }

    .employee-dashboard {
        --pms-archive-btn-bg: #ffffff;
        --pms-archive-btn-color: #2F6BFF;
        --pms-archive-btn-border: rgba(47, 107, 255, 0.18);
        --pms-archive-btn-icon: #2F6BFF;
    }

    .employee-dashboard .header-section {
        position: relative !important;
        z-index: 1050 !important;
        overflow: visible !important;
    }

    .employee-dashboard .header-content,
    .employee-dashboard .action-buttons {
        position: relative !important;
        z-index: 1050 !important;
        overflow: visible !important;
    }

    .employee-dashboard .quick-actions,
    .employee-dashboard .quick-actions .dropdown {
        display: inline-flex !important;
        position: relative !important;
        top: auto !important;
        z-index: 1060 !important;
        overflow: visible !important;
    }

    .employee-dashboard .quick-actions .dropdown-menu {
        position: absolute !important;
        top: 100% !important;
        bottom: auto !important;
        left: 0 !important;
        right: auto !important;
        margin-top: 0.5rem !important;
        min-width: 220px !important;
        transform: none !important;
        z-index: 999999 !important;
    }

    .employee-dashboard .stats-grid {
        position: relative !important;
        z-index: 1 !important;
    }

    .employee-dashboard .stat-card {
        position: relative !important;
        z-index: 1 !important;
    }

    .employee-dashboard a.btn.btn-add,
    .employee-dashboard button.btn.btn-invite {
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC) !important;
        border-color: #2F6BFF !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard a.btn.btn-add i,
    .employee-dashboard a.btn.btn-add span,
    .employee-dashboard button.btn.btn-invite i,
    .employee-dashboard button.btn.btn-invite span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 1 !important;
    }

    .employee-dashboard .btn-archive-employees {
        border: 1px solid var(--pms-archive-btn-border, rgba(47, 107, 255, 0.18)) !important;
        background: var(--pms-archive-btn-bg, #ffffff) !important;
        color: var(--pms-archive-btn-color, #2F6BFF) !important;
        -webkit-text-fill-color: var(--pms-archive-btn-color, #2F6BFF) !important;
        box-shadow: 0 10px 20px rgba(22, 39, 30, 0.07) !important;
    }

    .employee-dashboard .btn-archive-employees i {
        color: var(--pms-archive-btn-icon, #2F6BFF) !important;
        -webkit-text-fill-color: var(--pms-archive-btn-icon, #2F6BFF) !important;
        font-weight: 900 !important;
    }

    .employee-dashboard .btn-archive-employees span,
    .employee-dashboard .btn-archive-text {
        color: var(--pms-archive-btn-color, #2F6BFF) !important;
        -webkit-text-fill-color: var(--pms-archive-btn-color, #2F6BFF) !important;
        font-weight: 900 !important;
    }

    .employee-dashboard .btn-archive-employees:hover {
        border-color: #2F6BFF !important;
        background: #edf8f2 !important;
        transform: translateY(-2px) !important;
    }

    .employee-dashboard .btn-archive-employees:hover span,
    .employee-dashboard .btn-archive-employees:hover .btn-archive-text {
        color: var(--pms-archive-btn-color, #2F6BFF) !important;
        -webkit-text-fill-color: var(--pms-archive-btn-color, #2F6BFF) !important;
    }

    .employee-dashboard .quick-actions .dropdown-toggle {
        align-items: center !important;
        display: inline-flex !important;
        justify-content: center !important;
        min-height: 42px !important;
        min-width: 150px !important;
        padding: 0.54rem 0.9rem !important;
        font-size: 0.84rem !important;
        border-radius: 999px !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .btn-archive-employees,
    .employee-dashboard a.btn.btn-add,
    .employee-dashboard button.btn.btn-invite {
        align-items: center !important;
        display: inline-flex !important;
        justify-content: center !important;
        min-height: 42px !important;
        white-space: nowrap !important;
    }

    @media (max-width: 991.98px) {
        .employee-dashboard .action-buttons {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            justify-content: stretch !important;
            margin-left: 0 !important;
            width: 100% !important;
        }

        .employee-dashboard .quick-actions,
        .employee-dashboard .quick-actions .dropdown,
        .employee-dashboard .quick-actions .dropdown-toggle,
        .employee-dashboard .btn-archive-employees,
        .employee-dashboard a.btn.btn-add,
        .employee-dashboard button.btn.btn-invite {
            min-width: 0 !important;
            width: 100% !important;
        }
    }

    @media (max-width: 575.98px) {
        .employee-dashboard .action-buttons {
            grid-template-columns: 1fr !important;
        }
    }

    .employee-dashboard #bulkDeleteTrigger {
        min-height: 32px !important;
        padding: 0.42rem 0.65rem !important;
        font-size: 0.82rem !important;
        border-radius: 10px !important;
    }

    /* Filter Employees card polish */
    .employee-dashboard .employee-filter-polish {
        border: 1px solid rgba(47, 107, 255, 0.12) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 248, 0.96)) !important;
        box-shadow: 0 18px 42px rgba(22, 39, 30, 0.09) !important;
        overflow: visible !important;
    }

    .employee-dashboard .employee-filter-polish .filter-header {
        border-bottom: 1px solid rgba(47, 107, 255, 0.1) !important;
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.09), rgba(255, 255, 255, 0.82)) !important;
    }

    .employee-dashboard .employee-filter-polish .header-icon-wrapper {
        width: 48px !important;
        height: 48px !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC) !important;
        color: #ffffff !important;
        box-shadow: 0 12px 22px rgba(47, 107, 255, 0.22) !important;
    }

    .employee-dashboard .employee-filter-polish .header-icon-wrapper i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 1.08rem !important;
    }

    .employee-dashboard .employee-filter-polish .header-text h3 {
        color: #0a1f16 !important;
        -webkit-text-fill-color: #0a1f16 !important;
        font-size: 1.18rem !important;
        font-weight: 900 !important;
        letter-spacing: 0 !important;
        margin-bottom: 0.16rem !important;
    }

    .employee-dashboard .employee-filter-polish .header-text p {
        color: #53645b !important;
        -webkit-text-fill-color: #53645b !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
    }

    .employee-dashboard .employee-filter-polish .header-right {
        align-items: stretch !important;
    }

    .employee-dashboard .employee-filter-polish .selected-badge {
        min-height: 38px !important;
        align-items: center !important;
        border: 1px solid rgba(47, 107, 255, 0.16) !important;
        background: #edf8f2 !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        font-weight: 900 !important;
        padding: 0.48rem 0.78rem !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .employee-filter-polish .btn-export-all,
    .employee-dashboard .employee-filter-polish .btn-apply {
        min-height: 42px !important;
        border: 1px solid #2F6BFF !important;
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 900 !important;
        padding: 0.58rem 1rem !important;
        box-shadow: 0 12px 22px rgba(47, 107, 255, 0.16) !important;
    }

    .employee-dashboard .employee-filter-polish .btn-export-all *,
    .employee-dashboard .employee-filter-polish .btn-apply * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .employee-filter-polish .btn-group .btn {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        padding: 0 !important;
        border-radius: 12px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        background: #ffffff !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        box-shadow: 0 8px 16px rgba(22, 39, 30, 0.06) !important;
    }

    .employee-dashboard .employee-filter-polish .btn-group .btn:disabled {
        opacity: 0.65 !important;
        color: #7f8d85 !important;
        -webkit-text-fill-color: #7f8d85 !important;
    }

    .employee-dashboard .employee-filter-polish .filter-body {
        background: rgba(255, 255, 255, 0.78) !important;
    }

    .employee-dashboard .employee-filter-polish .filter-grid {
        align-items: end !important;
    }

    .employee-dashboard .employee-filter-polish .filter-item {
        min-width: 0 !important;
    }

    .employee-dashboard .employee-filter-polish .filter-label {
        display: flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        color: #13241b !important;
        -webkit-text-fill-color: #13241b !important;
        font-size: 0.86rem !important;
        font-weight: 900 !important;
        margin-bottom: 0.5rem !important;
    }

    .employee-dashboard .employee-filter-polish .filter-label i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    .employee-dashboard .employee-filter-polish .form-select,
    .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single {
        min-height: 46px !important;
        border: 1px solid rgba(47, 107, 255, 0.18) !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        color: #111c16 !important;
        -webkit-text-fill-color: #111c16 !important;
        box-shadow: 0 8px 18px rgba(22, 39, 30, 0.05) !important;
    }

    .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111c16 !important;
        -webkit-text-fill-color: #111c16 !important;
        font-weight: 800 !important;
        line-height: 44px !important;
        padding-left: 14px !important;
        padding-right: 34px !important;
    }

    .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 8px !important;
    }

    .employee-dashboard .employee-filter-polish .btn-reset {
        min-height: 42px !important;
        border: 1px solid rgba(47, 107, 255, 0.16) !important;
        background: #ffffff !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        font-weight: 900 !important;
        padding: 0.58rem 0.95rem !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish {
        border-color: rgba(79, 131, 255, 0.22) !important;
        background: #0F1530 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .filter-header {
        border-color: rgba(79, 131, 255, 0.18) !important;
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.14), rgba(15, 21, 48, 0.96)) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .header-text h3,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .filter-label {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .header-text p {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .filter-body {
        background: rgba(15, 21, 48, 0.82) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .form-select,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single {
        border-color: rgba(79, 131, 255, 0.2) !important;
        background: #141B3D !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    @media (max-width: 640px) {
        .employee-dashboard .employee-filter-polish .header-right,
        .employee-dashboard .employee-filter-polish .btn-export-all,
        .employee-dashboard .employee-filter-polish .btn-apply,
        .employee-dashboard .employee-filter-polish .btn-reset {
            width: 100% !important;
        }

        .employee-dashboard .employee-filter-polish .btn-group {
            width: 100% !important;
            justify-content: space-between !important;
        }

        .employee-dashboard .employee-filter-polish .btn-group .btn {
            flex: 1 1 40px !important;
        }
    }

    /* Exact filter body section polish */
    .employee-dashboard .employee-filter-body {
        border-top: 1px solid rgba(47, 107, 255, 0.08) !important;
        background:
            linear-gradient(135deg, rgba(47, 107, 255, 0.035), rgba(255, 255, 255, 0.86)),
            #ffffff !important;
    }

    .employee-dashboard .employee-filter-grid {
        align-items: stretch !important;
    }

    .employee-dashboard .employee-filter-field {
        display: flex !important;
        min-width: 0 !important;
        min-height: 112px !important;
        flex-direction: column !important;
        justify-content: flex-end !important;
        border: 1px solid rgba(47, 107, 255, 0.12) !important;
        border-radius: 18px !important;
        background: rgba(255, 255, 255, 0.92) !important;
        padding: 0.9rem !important;
        box-shadow: 0 10px 24px rgba(22, 39, 30, 0.055) !important;
    }

    .employee-dashboard .employee-filter-field .filter-label {
        display: inline-flex !important;
        width: 100% !important;
        align-items: center !important;
        gap: 0.48rem !important;
        color: #0a1f16 !important;
        -webkit-text-fill-color: #0a1f16 !important;
        font-size: 0.9rem !important;
        font-weight: 950 !important;
        line-height: 1.2 !important;
        margin: 0 0 0.68rem !important;
        white-space: normal !important;
    }

    .employee-dashboard .employee-filter-field .filter-label i {
        display: inline-flex !important;
        width: 28px !important;
        height: 28px !important;
        flex: 0 0 28px !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 10px !important;
        background: #e7f5ee !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        font-size: 0.82rem !important;
        margin-right: 0 !important;
    }

    .employee-dashboard .employee-filter-field .form-select,
    .employee-dashboard .employee-filter-field .select2-container {
        width: 100% !important;
        max-width: 100% !important;
    }

    .employee-dashboard .employee-filter-field .form-select,
    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single {
        height: 48px !important;
        min-height: 48px !important;
        border: 1px solid rgba(47, 107, 255, 0.18) !important;
        border-radius: 14px !important;
        background: #f9fcfa !important;
        color: #08150f !important;
        -webkit-text-fill-color: #08150f !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 8px 18px rgba(22, 39, 30, 0.045) !important;
    }

    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        overflow: hidden !important;
        color: #08150f !important;
        -webkit-text-fill-color: #08150f !important;
        font-size: 0.92rem !important;
        font-weight: 850 !important;
        line-height: 46px !important;
        padding-left: 14px !important;
        padding-right: 38px !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #637268 !important;
        -webkit-text-fill-color: #637268 !important;
    }

    .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 9px !important;
    }

    .employee-dashboard .employee-filter-field:focus-within {
        border-color: rgba(47, 107, 255, 0.34) !important;
        box-shadow: 0 14px 30px rgba(47, 107, 255, 0.1) !important;
    }

    .employee-dashboard .employee-filter-actions {
        min-height: 112px !important;
        align-self: stretch !important;
        border: 1px solid rgba(47, 107, 255, 0.12) !important;
        border-radius: 18px !important;
        background: linear-gradient(135deg, #f3faf6, #ffffff) !important;
        padding: 0.9rem !important;
        box-shadow: 0 10px 24px rgba(22, 39, 30, 0.055) !important;
    }

    .employee-dashboard .employee-filter-actions .btn {
        min-height: 48px !important;
        flex: 1 1 auto !important;
        border-radius: 14px !important;
        font-size: 0.9rem !important;
        font-weight: 950 !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
    }

    .employee-dashboard .employee-filter-actions .btn-apply {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .employee-filter-actions .btn-apply i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .employee-filter-actions .btn-reset i {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-body {
        border-top-color: rgba(79, 131, 255, 0.14) !important;
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.06), rgba(15, 21, 48, 0.92)) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-actions {
        border-color: rgba(79, 131, 255, 0.16) !important;
        background: #0F1530 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .filter-label {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .filter-label i {
        background: rgba(47, 107, 255, 0.16) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .form-select,
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single {
        border-color: rgba(79, 131, 255, 0.2) !important;
        background: #141B3D !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Header Action Buttons (Quick Actions & Archived) in Dark Mode */
    html[data-pms-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle,
    html[data-bs-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle,
    html[data-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle,
    html[data-pms-theme="dark"] .employee-dashboard .btn-outline-light,
    html[data-bs-theme="dark"] .employee-dashboard .btn-outline-light,
    html[data-theme="dark"] .employee-dashboard .btn-outline-light {
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
        background: #141B3D !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle *,
    html[data-bs-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle *,
    html[data-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle i,
    html[data-bs-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle i,
    html[data-theme="dark"] .employee-dashboard .quick-actions .dropdown-toggle i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .dropdown-menu,
    html[data-bs-theme="dark"] .employee-dashboard .dropdown-menu,
    html[data-theme="dark"] .employee-dashboard .dropdown-menu {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .dropdown-item,
    html[data-bs-theme="dark"] .employee-dashboard .dropdown-item,
    html[data-theme="dark"] .employee-dashboard .dropdown-item {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .dropdown-item:hover,
    html[data-bs-theme="dark"] .employee-dashboard .dropdown-item:hover,
    html[data-theme="dark"] .employee-dashboard .dropdown-item:hover {
        background: rgba(47, 107, 255, 0.15) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .dropdown-divider,
    html[data-bs-theme="dark"] .employee-dashboard .dropdown-divider,
    html[data-theme="dark"] .employee-dashboard .dropdown-divider {
        border-top: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard,
    html[data-bs-theme="dark"] .employee-dashboard,
    html[data-theme="dark"] .employee-dashboard,
    html.dark .employee-dashboard,
    body[data-pms-theme="dark"] .employee-dashboard,
    body[data-bs-theme="dark"] .employee-dashboard,
    body.dark .employee-dashboard,
    [data-pms-theme="dark"] .employee-dashboard,
    [data-bs-theme="dark"] .employee-dashboard,
    .dark .employee-dashboard {
        --pms-archive-btn-bg: #141B3D !important;
        --pms-archive-btn-color: #ffffff !important;
        --pms-archive-btn-border: rgba(79, 131, 255, 0.32) !important;
        --pms-archive-btn-icon: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees,
    html.dark .employee-dashboard .btn-archive-employees,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees,
    body.dark .employee-dashboard .btn-archive-employees,
    html[data-pms-theme="dark"] .employee-dashboard a.btn-archive-employees,
    html[data-bs-theme="dark"] .employee-dashboard a.btn-archive-employees,
    html[data-theme="dark"] .employee-dashboard a.btn-archive-employees,
    html[data-pms-theme="dark"] a.btn-archive-employees,
    html[data-bs-theme="dark"] a.btn-archive-employees,
    html[data-theme="dark"] a.btn-archive-employees {
        border: 1px solid rgba(79, 131, 255, 0.32) !important;
        background: #141B3D !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees *,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees *,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees *,
    html.dark .employee-dashboard .btn-archive-employees *,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees *,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees *,
    body.dark .employee-dashboard .btn-archive-employees *,
    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees span,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees span,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees span,
    html.dark .employee-dashboard .btn-archive-employees span,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees span,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees span,
    body.dark .employee-dashboard .btn-archive-employees span,
    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-text,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-text,
    html[data-theme="dark"] .employee-dashboard .btn-archive-text,
    html.dark .employee-dashboard .btn-archive-text,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-text,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-text,
    body.dark .employee-dashboard .btn-archive-text,
    html[data-pms-theme="dark"] .btn-archive-text,
    html[data-bs-theme="dark"] .btn-archive-text,
    html[data-theme="dark"] .btn-archive-text,
    html[data-pms-theme="dark"] a.btn-archive-employees span,
    html[data-bs-theme="dark"] a.btn-archive-employees span,
    html[data-theme="dark"] a.btn-archive-employees span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees i,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees i,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees i,
    html.dark .employee-dashboard .btn-archive-employees i,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees i,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees i,
    body.dark .employee-dashboard .btn-archive-employees i,
    html[data-pms-theme="dark"] a.btn-archive-employees i,
    html[data-bs-theme="dark"] a.btn-archive-employees i,
    html[data-theme="dark"] a.btn-archive-employees i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees:hover,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees:hover,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees:hover,
    html.dark .employee-dashboard .btn-archive-employees:hover,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees:hover,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees:hover,
    body.dark .employee-dashboard .btn-archive-employees:hover,
    html[data-pms-theme="dark"] a.btn-archive-employees:hover,
    html[data-bs-theme="dark"] a.btn-archive-employees:hover,
    html[data-theme="dark"] a.btn-archive-employees:hover {
        border-color: rgba(79, 131, 255, 0.5) !important;
        background: rgba(79, 131, 255, 0.22) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: translateY(-2px) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees:hover span,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees:hover span,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees:hover span,
    html.dark .employee-dashboard .btn-archive-employees:hover span,
    body[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees:hover span,
    body[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees:hover span,
    body.dark .employee-dashboard .btn-archive-employees:hover span,
    html[data-pms-theme="dark"] .employee-dashboard .btn-archive-employees:hover .btn-archive-text,
    html[data-bs-theme="dark"] .employee-dashboard .btn-archive-employees:hover .btn-archive-text,
    html[data-theme="dark"] .employee-dashboard .btn-archive-employees:hover .btn-archive-text,
    html[data-pms-theme="dark"] a.btn-archive-employees:hover span,
    html[data-bs-theme="dark"] a.btn-archive-employees:hover span,
    html[data-theme="dark"] a.btn-archive-employees:hover span,
    html[data-pms-theme="dark"] .btn-archive-employees:hover .btn-archive-text,
    html[data-bs-theme="dark"] .btn-archive-employees:hover .btn-archive-text,
    html[data-theme="dark"] .btn-archive-employees:hover .btn-archive-text {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Selected Badge in Dark Mode */
    html[data-pms-theme="dark"] .employee-dashboard .employee-filter-polish .selected-badge,
    html[data-bs-theme="dark"] .employee-dashboard .employee-filter-polish .selected-badge,
    html[data-theme="dark"] .employee-dashboard .employee-filter-polish .selected-badge,
    html[data-pms-theme="dark"] .employee-dashboard .selected-badge,
    html[data-bs-theme="dark"] .employee-dashboard .selected-badge,
    html[data-theme="dark"] .employee-dashboard .selected-badge,
    html[data-pms-theme="dark"] #export-selected-count,
    html[data-bs-theme="dark"] #export-selected-count,
    html[data-theme="dark"] #export-selected-count {
        background: rgba(47, 107, 255, 0.16) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        font-weight: 750 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }

    @media (max-width: 640px) {
        .employee-dashboard .employee-filter-field,
        .employee-dashboard .employee-filter-actions {
            min-height: auto !important;
            padding: 0.82rem !important;
        }

        .employee-dashboard .employee-filter-actions .btn {
            width: 100% !important;
        }
    }

    /* Final employee index card polish */
    .employee-dashboard {
        background: linear-gradient(145deg, #f7fbf9 0%, #eef7f2 52%, #fafdfb 100%) !important;
    }

    .employee-dashboard .breadcrumb-wrapper,
    .employee-dashboard .header-section,
    .employee-dashboard .stat-card,
    .employee-dashboard .filter-export-card,
    .employee-dashboard .table-card,
    .employee-dashboard .employee-filter-polish {
        border: 1px solid rgba(47, 107, 255, 0.12) !important;
        box-shadow: 0 18px 44px rgba(22, 39, 30, 0.08) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .employee-dashboard .header-section {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(241, 250, 245, 0.94)) !important;
        overflow: visible !important;
    }

    .employee-dashboard .icon-circle,
    .employee-dashboard .stat-icon {
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(47, 107, 255, 0.2) !important;
    }

    .employee-dashboard .icon-circle i,
    .employee-dashboard .stat-icon i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .employee-dashboard .header-section h1,
    .employee-dashboard .stat-value,
    .employee-dashboard .table-card h3,
    .employee-dashboard .employee-name,
    .employee-dashboard .table strong {
        color: #070B1A !important;
        -webkit-text-fill-color: #070B1A !important;
        letter-spacing: 0 !important;
    }

    .employee-dashboard .subtitle,
    .employee-dashboard .stat-label,
    .employee-dashboard .employee-email,
    .employee-dashboard .table td {
        color: #52645a !important;
        -webkit-text-fill-color: #52645a !important;
    }

    .employee-dashboard .stat-card {
        border-radius: 20px !important;
        background: linear-gradient(135deg, #ffffff, #f8fcfa) !important;
        overflow: hidden;
    }

    .employee-dashboard .stat-card:hover,
    .employee-dashboard .table-card:hover,
    .employee-dashboard .employee-filter-polish:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 52px rgba(22, 39, 30, 0.11) !important;
    }

    .employee-dashboard .table-card {
        border-radius: 24px !important;
        background: rgba(255, 255, 255, 0.98) !important;
        overflow: hidden;
    }

    .employee-dashboard .table thead th {
        background: #f4faf6 !important;
        color: #070B1A !important;
        -webkit-text-fill-color: #070B1A !important;
        border-bottom: 1px solid rgba(47, 107, 255, 0.12) !important;
        letter-spacing: 0 !important;
    }

    .employee-dashboard .table tbody tr {
        transition: background 0.18s ease, transform 0.18s ease;
    }

    .employee-dashboard .table tbody tr:hover {
        background: #f5fbf7 !important;
    }

    .employee-dashboard .employee-list-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }

    .employee-dashboard .employee-list-table tbody tr {
        height: auto !important;
        margin: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .employee-dashboard .employee-list-table thead th {
        padding: 14px 16px !important;
        vertical-align: middle !important;
    }

    .employee-dashboard .employee-list-table tbody td {
        padding: 10px 16px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid rgba(47, 107, 255, 0.08) !important;
    }

    .employee-dashboard .employee-list-table tbody td:first-child,
    .employee-dashboard .employee-list-table tbody td:last-child {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    .employee-dashboard .employee-list-table .employee-info {
        min-height: 52px !important;
    }

    .employee-dashboard .employee-avatar,
    .employee-dashboard .avatar,
    .employee-dashboard img.rounded-circle {
        border: 2px solid #ffffff !important;
        box-shadow: 0 8px 16px rgba(22, 39, 30, 0.12) !important;
    }

    .employee-dashboard .btn,
    .employee-dashboard .action-btn {
        border-radius: 12px !important;
        font-weight: 850 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard,
    html[data-bs-theme="dark"] .employee-dashboard,
    html[data-theme="dark"] .employee-dashboard {
        background: linear-gradient(145deg, #070B1A, #0F1530) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .header-section,
    html[data-bs-theme="dark"] .employee-dashboard .header-section,
    html[data-theme="dark"] .employee-dashboard .header-section,
    html[data-pms-theme="dark"] .employee-dashboard .stat-card,
    html[data-bs-theme="dark"] .employee-dashboard .stat-card,
    html[data-theme="dark"] .employee-dashboard .stat-card,
    html[data-pms-theme="dark"] .employee-dashboard .table-card,
    html[data-bs-theme="dark"] .employee-dashboard .table-card,
    html[data-theme="dark"] .employee-dashboard .table-card {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.18) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .header-section h1,
    html[data-bs-theme="dark"] .employee-dashboard .header-section h1,
    html[data-theme="dark"] .employee-dashboard .header-section h1,
    html[data-pms-theme="dark"] .employee-dashboard .stat-value,
    html[data-bs-theme="dark"] .employee-dashboard .stat-value,
    html[data-theme="dark"] .employee-dashboard .stat-value,
    html[data-pms-theme="dark"] .employee-dashboard .table-card h3,
    html[data-bs-theme="dark"] .employee-dashboard .table-card h3,
    html[data-theme="dark"] .employee-dashboard .table-card h3,
    html[data-pms-theme="dark"] .employee-dashboard .table strong,
    html[data-bs-theme="dark"] .employee-dashboard .table strong,
    html[data-theme="dark"] .employee-dashboard .table strong {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .subtitle,
    html[data-bs-theme="dark"] .employee-dashboard .subtitle,
    html[data-theme="dark"] .employee-dashboard .subtitle,
    html[data-pms-theme="dark"] .employee-dashboard .table td,
    html[data-bs-theme="dark"] .employee-dashboard .table td,
    html[data-theme="dark"] .employee-dashboard .table td {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    /* Stat card labels - crisp light mint for high contrast and perfect readability in dark mode */
    html[data-pms-theme="dark"] .employee-dashboard .stat-card .stat-label,
    html[data-bs-theme="dark"] .employee-dashboard .stat-card .stat-label,
    html[data-theme="dark"] .employee-dashboard .stat-card .stat-label,
    html[data-pms-theme="dark"] .employee-dashboard .stat-label,
    html[data-bs-theme="dark"] .employee-dashboard .stat-label,
    html[data-theme="dark"] .employee-dashboard .stat-label {
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
        font-weight: 800 !important;
        letter-spacing: 0.5px !important;
        opacity: 0.95 !important;
    }

    /* Company Switcher Cards in Dark Mode */
    html[data-pms-theme="dark"] .company-switch-card,
    html[data-bs-theme="dark"] .company-switch-card,
    html[data-theme="dark"] .company-switch-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.25) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .company-switch-card:hover,
    html[data-bs-theme="dark"] .company-switch-card:hover,
    html[data-theme="dark"] .company-switch-card:hover {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.38) !important;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.4) !important;
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    html[data-pms-theme="dark"] .company-switch-card.active,
    html[data-bs-theme="dark"] .company-switch-card.active,
    html[data-theme="dark"] .company-switch-card.active {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.25) 0%, rgba(79, 131, 255, 0.15) 100%) !important;
        border: 1px solid rgba(79, 131, 255, 0.45) !important;
        box-shadow: 0 0 24px rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .company-switch-meta strong,
    html[data-bs-theme="dark"] .company-switch-meta strong,
    html[data-theme="dark"] .company-switch-meta strong {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
    }

    html[data-pms-theme="dark"] .company-switch-meta small,
    html[data-bs-theme="dark"] .company-switch-meta small,
    html[data-theme="dark"] .company-switch-meta small {
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
        font-weight: 600 !important;
    }

    html[data-pms-theme="dark"] .company-switch-icon,
    html[data-bs-theme="dark"] .company-switch-icon,
    html[data-theme="dark"] .company-switch-icon,
    html.dark .company-switch-icon,
    body[data-pms-theme="dark"] .company-switch-icon,
    body[data-bs-theme="dark"] .company-switch-icon,
    body.dark .company-switch-icon,
    html[data-pms-theme="dark"] .company-switch-card .company-switch-icon,
    html[data-bs-theme="dark"] .company-switch-card .company-switch-icon,
    html[data-theme="dark"] .company-switch-card .company-switch-icon,
    html.dark .company-switch-card .company-switch-icon,
    body[data-pms-theme="dark"] .company-switch-card .company-switch-icon,
    body[data-bs-theme="dark"] .company-switch-card .company-switch-icon,
    body.dark .company-switch-card .company-switch-icon,
    html[data-pms-theme="dark"] .company-switch-card .company-switch-icon *,
    html[data-bs-theme="dark"] .company-switch-card .company-switch-icon *,
    html[data-theme="dark"] .company-switch-card .company-switch-icon *,
    html.dark .company-switch-card .company-switch-icon *,
    body[data-pms-theme="dark"] .company-switch-card .company-switch-icon *,
    body[data-bs-theme="dark"] .company-switch-card .company-switch-icon *,
    body.dark .company-switch-card .company-switch-icon *,
    html[data-pms-theme="dark"] .company-switch-card .company-switch-icon i,
    html[data-bs-theme="dark"] .company-switch-card .company-switch-icon i,
    html[data-theme="dark"] .company-switch-card .company-switch-icon i,
    html[data-pms-theme="dark"] .company-switch-card .company-switch-icon span,
    html[data-bs-theme="dark"] .company-switch-card .company-switch-icon span,
    html[data-theme="dark"] .company-switch-card .company-switch-icon span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        fill: #ffffff !important;
    }

    html[data-pms-theme="dark"] .company-module-rail,
    html[data-bs-theme="dark"] .company-module-rail,
    html[data-theme="dark"] .company-module-rail {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.18) !important;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] .company-module-rail span,
    html[data-bs-theme="dark"] .company-module-rail span,
    html[data-theme="dark"] .company-module-rail span {
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
    }

    html[data-pms-theme="dark"] .company-module-rail a,
    html[data-bs-theme="dark"] .company-module-rail a,
    html[data-theme="dark"] .company-module-rail a {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.22) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .company-module-rail a:hover,
    html[data-bs-theme="dark"] .company-module-rail a:hover,
    html[data-theme="dark"] .company-module-rail a:hover {
        background: rgba(79, 131, 255, 0.22) !important;
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
    }

    /* ===== Employee list row gap: same distance for every row ===== */
    @media (min-width: 769px) {
        .employee-dashboard .employee-list-table {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            width: max-content !important;
            min-width: 1200px !important;
        }

        .employee-dashboard .employee-list-table thead tr,
        .employee-dashboard .employee-list-table tbody tr {
            display: table-row !important;
        }

        .employee-dashboard .employee-list-table thead th {
            padding: 13px 16px !important;
            border-bottom: 1px solid rgba(47, 107, 255, 0.1) !important;
        }

        .employee-dashboard .employee-list-table tbody td {
            height: 72px !important;
            padding: 10px 16px !important;
            background: #ffffff !important;
            border-top: 1px solid rgba(47, 107, 255, 0.06) !important;
            border-bottom: 1px solid rgba(47, 107, 255, 0.06) !important;
            vertical-align: middle !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(2),
        .employee-dashboard .employee-list-table tbody td:nth-child(2) {
            width: 140px !important;
            min-width: 140px !important;
            max-width: 140px !important;
            padding-left: 14px !important;
            padding-right: 14px !important;
            text-align: left !important;
        }

        .employee-dashboard .employee-list-table .employee-id-badge {
            max-width: 100% !important;
            overflow: hidden !important;
            text-overflow: clip !important;
            white-space: normal !important;
            overflow-wrap: anywhere !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(3),
        .employee-dashboard .employee-list-table tbody td:nth-child(3) {
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
            padding-right: 10px !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(4),
        .employee-dashboard .employee-list-table tbody td:nth-child(4) {
            width: 190px !important;
            min-width: 190px !important;
            max-width: 190px !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .employee-dashboard .employee-list-table thead th:nth-child(5),
        .employee-dashboard .employee-list-table tbody td:nth-child(5) {
            width: 210px !important;
            min-width: 210px !important;
            max-width: 210px !important;
            padding-left: 10px !important;
        }

        .employee-dashboard .employee-list-table tbody td:first-child {
            border-left: 1px solid rgba(47, 107, 255, 0.06) !important;
            border-radius: 14px 0 0 14px !important;
            padding-left: 18px !important;
            padding-right: 10px !important;
        }

        .employee-dashboard .employee-list-table tbody td:last-child {
            border-right: 1px solid rgba(47, 107, 255, 0.06) !important;
            border-radius: 0 14px 14px 0 !important;
            padding-left: 10px !important;
            padding-right: 18px !important;
        }

        .employee-dashboard .employee-list-table .employee-info {
            min-height: 48px !important;
        }
    }

    :root {
        --emp-count-color: #6b4e9e;
        --emp-footer-text: #4b5563;
        --emp-footer-select-bg: #ffffff;
        --emp-footer-select-color: #1f2937;
        --emp-footer-select-border: #e0d7ff;
    }

    html[data-pms-theme="dark"],
    html[data-bs-theme="dark"],
    html[data-theme="dark"],
    html.dark,
    body[data-pms-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark,
    [data-pms-theme="dark"],
    [data-bs-theme="dark"],
    .dark {
        --emp-count-color: #C7D2FE;
        --emp-footer-text: #CBD5E1;
        --emp-footer-select-bg: #141B3D;
        --emp-footer-select-color: #EEF1FB;
        --emp-footer-select-border: rgba(79, 131, 255, 0.35);
    }

    /* ===== EMPLOYEE LIST TABLE & CONTROLS - DARK MODE ENHANCEMENTS ===== */
    /* 0. Employee Count Subtitle ("5 employees") */
    html[data-pms-theme="dark"] .employee-dashboard .employee-count,
    html[data-bs-theme="dark"] .employee-dashboard .employee-count,
    html[data-theme="dark"] .employee-dashboard .employee-count,
    html.dark .employee-dashboard .employee-count,
    body[data-pms-theme="dark"] .employee-dashboard .employee-count,
    body[data-bs-theme="dark"] .employee-dashboard .employee-count,
    body.dark .employee-dashboard .employee-count,
    [data-pms-theme="dark"] .employee-dashboard .employee-count,
    [data-bs-theme="dark"] .employee-dashboard .employee-count,
    [data-theme="dark"] .employee-dashboard .employee-count,
    .dark .employee-dashboard .employee-count {
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
        font-weight: 600 !important;
    }

    /* 0b. Archive Inactive Button in Dark Mode */
    html[data-pms-theme="dark"] .employee-dashboard .btn-bulk-delete,
    html[data-bs-theme="dark"] .employee-dashboard .btn-bulk-delete,
    html[data-theme="dark"] .employee-dashboard .btn-bulk-delete,
    html.dark .employee-dashboard .btn-bulk-delete,
    body[data-pms-theme="dark"] .employee-dashboard .btn-bulk-delete,
    body[data-bs-theme="dark"] .employee-dashboard .btn-bulk-delete,
    body.dark .employee-dashboard .btn-bulk-delete {
        background: rgba(239, 68, 68, 0.22) !important;
        border: 1px solid rgba(248, 113, 113, 0.45) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
    }
    html[data-pms-theme="dark"] .employee-dashboard .btn-bulk-delete:hover:not(:disabled),
    html[data-bs-theme="dark"] .employee-dashboard .btn-bulk-delete:hover:not(:disabled),
    html[data-theme="dark"] .employee-dashboard .btn-bulk-delete:hover:not(:disabled) {
        background: rgba(239, 68, 68, 0.35) !important;
        border-color: rgba(248, 113, 113, 0.65) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    html[data-pms-theme="dark"] .employee-dashboard .btn-bulk-delete:disabled,
    html[data-bs-theme="dark"] .employee-dashboard .btn-bulk-delete:disabled,
    html[data-theme="dark"] .employee-dashboard .btn-bulk-delete:disabled {
        opacity: 0.65 !important;
        background: rgba(239, 68, 68, 0.18) !important;
        border-color: rgba(248, 113, 113, 0.3) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* 1. Header Selected Badges */
    html[data-pms-theme="dark"] .employee-dashboard #bulk-selected-count,
    html[data-bs-theme="dark"] .employee-dashboard #bulk-selected-count,
    html[data-theme="dark"] .employee-dashboard #bulk-selected-count,
    html.dark .employee-dashboard #bulk-selected-count,
    body[data-pms-theme="dark"] .employee-dashboard #bulk-selected-count,
    body[data-bs-theme="dark"] .employee-dashboard #bulk-selected-count,
    body.dark .employee-dashboard #bulk-selected-count,
    html[data-pms-theme="dark"] .employee-dashboard .table-header .selected-badge,
    html[data-bs-theme="dark"] .employee-dashboard .table-header .selected-badge,
    html[data-theme="dark"] .employee-dashboard .table-header .selected-badge,
    html[data-pms-theme="dark"] .employee-dashboard .selected-badge,
    html[data-bs-theme="dark"] .employee-dashboard .selected-badge,
    html[data-theme="dark"] .employee-dashboard .selected-badge {
        background: rgba(47, 107, 255, 0.2) !important;
        border: 1px solid rgba(79, 131, 255, 0.45) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }

    /* 2. Table Tools Bar ("0 rows selected" & "Export" dropdown) */
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-tools,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-tools,
    html[data-theme="dark"] .employee-dashboard .pms-table-tools,
    html.dark .employee-dashboard .pms-table-tools,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-tools,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-tools,
    body.dark .employee-dashboard .pms-table-tools {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.18) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-tools__status,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-tools__status,
    html[data-theme="dark"] .employee-dashboard .pms-table-tools__status,
    html.dark .employee-dashboard .pms-table-tools__status,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-tools__status,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-tools__status,
    body.dark .employee-dashboard .pms-table-tools__status,
    [data-pms-theme="dark"] .employee-dashboard .pms-table-tools__status,
    [data-bs-theme="dark"] .employee-dashboard .pms-table-tools__status,
    [data-theme="dark"] .employee-dashboard .pms-table-tools__status,
    .dark .employee-dashboard .pms-table-tools__status {
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
        font-weight: 800 !important;
        letter-spacing: 0.3px !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__toggle,
    html.dark .employee-dashboard .pms-table-export__toggle,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle,
    body.dark .employee-dashboard .pms-table-export__toggle,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle span,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle span,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__toggle span,
    html.dark .employee-dashboard .pms-table-export__toggle span,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle span,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle span,
    body.dark .employee-dashboard .pms-table-export__toggle span,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    html[data-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    html.dark .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    body.dark .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    html[data-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    html.dark .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    body.dark .employee-dashboard .pms-table-export.is-open .pms-table-export__toggle span,
    html[data-pms-theme="dark"] .pms-table-export__toggle,
    html[data-pms-theme="dark"] .pms-table-export__toggle span,
    html[data-pms-theme="dark"] .pms-table-export.is-open .pms-table-export__toggle,
    html[data-pms-theme="dark"] .pms-table-export.is-open .pms-table-export__toggle span {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle svg,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle svg,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__toggle svg,
    html.dark .employee-dashboard .pms-table-export__toggle svg,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle svg,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle svg,
    body.dark .employee-dashboard .pms-table-export__toggle svg {
        color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover,
    html.dark .employee-dashboard .pms-table-export__toggle:hover,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover,
    body.dark .employee-dashboard .pms-table-export__toggle:hover,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover span,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover span,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover span,
    html.dark .employee-dashboard .pms-table-export__toggle:hover span,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover span,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__toggle:hover span,
    body.dark .employee-dashboard .pms-table-export__toggle:hover span,
    html[data-pms-theme="dark"] .pms-table-export__toggle:hover,
    html[data-pms-theme="dark"] .pms-table-export__toggle:hover span {
        background: rgba(79, 131, 255, 0.22) !important;
        border-color: rgba(79, 131, 255, 0.6) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__menu,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__menu,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__menu,
    html.dark .employee-dashboard .pms-table-export__menu,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__menu,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__menu,
    body.dark .employee-dashboard .pms-table-export__menu {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option,
    html.dark .employee-dashboard .pms-table-export__option,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option,
    body.dark .employee-dashboard .pms-table-export__option,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option span,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option span,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option span,
    html.dark .employee-dashboard .pms-table-export__option span,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option span,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option span,
    body.dark .employee-dashboard .pms-table-export__option span,
    html[data-pms-theme="dark"] .pms-table-export__option,
    html[data-pms-theme="dark"] .pms-table-export__option span,
    html[data-pms-theme="dark"] [data-export-scope="all"],
    html[data-pms-theme="dark"] [data-export-scope="all"] span,
    html[data-pms-theme="dark"] [data-export-scope="selected"],
    html[data-pms-theme="dark"] [data-export-scope="selected"] span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:disabled,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:disabled,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option:disabled,
    html.dark .employee-dashboard .pms-table-export__option:disabled,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:disabled,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:disabled,
    body.dark .employee-dashboard .pms-table-export__option:disabled,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:disabled span,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:disabled span,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option:disabled span,
    html.dark .employee-dashboard .pms-table-export__option:disabled span,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:disabled span,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:disabled span,
    body.dark .employee-dashboard .pms-table-export__option:disabled span,
    html[data-pms-theme="dark"] .pms-table-export__option:disabled,
    html[data-pms-theme="dark"] .pms-table-export__option:disabled span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 0.9 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option svg,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option svg,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option svg,
    html.dark .employee-dashboard .pms-table-export__option svg,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option svg,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option svg,
    body.dark .employee-dashboard .pms-table-export__option svg,
    html[data-pms-theme="dark"] .pms-table-export__option svg {
        color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:hover,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:hover,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option:hover,
    html.dark .employee-dashboard .pms-table-export__option:hover,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:hover,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:hover,
    body.dark .employee-dashboard .pms-table-export__option:hover,
    html[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:hover span,
    html[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:hover span,
    html[data-theme="dark"] .employee-dashboard .pms-table-export__option:hover span,
    html.dark .employee-dashboard .pms-table-export__option:hover span,
    body[data-pms-theme="dark"] .employee-dashboard .pms-table-export__option:hover span,
    body[data-bs-theme="dark"] .employee-dashboard .pms-table-export__option:hover span,
    body.dark .employee-dashboard .pms-table-export__option:hover span,
    html[data-pms-theme="dark"] .pms-table-export__option:hover,
    html[data-pms-theme="dark"] .pms-table-export__option:hover span {
        background: rgba(79, 131, 255, 0.15) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* 3. EMP ID Badges */
    html[data-pms-theme="dark"] .employee-dashboard .employee-id-badge,
    html[data-bs-theme="dark"] .employee-dashboard .employee-id-badge,
    html[data-theme="dark"] .employee-dashboard .employee-id-badge,
    html.dark .employee-dashboard .employee-id-badge,
    body[data-pms-theme="dark"] .employee-dashboard .employee-id-badge,
    body[data-bs-theme="dark"] .employee-dashboard .employee-id-badge,
    body.dark .employee-dashboard .employee-id-badge,
    html[data-pms-theme="dark"] .employee-dashboard .employee-list-table .employee-id-badge,
    html[data-bs-theme="dark"] .employee-dashboard .employee-list-table .employee-id-badge,
    html[data-theme="dark"] .employee-dashboard .employee-list-table .employee-id-badge {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25) !important;
        letter-spacing: 0.5px !important;
    }

    /* 4. Company Column Text & Logo */
    html[data-pms-theme="dark"] .employee-dashboard .employee-company-chip > span:last-child,
    html[data-bs-theme="dark"] .employee-dashboard .employee-company-chip > span:last-child,
    html[data-theme="dark"] .employee-dashboard .employee-company-chip > span:last-child,
    html.dark .employee-dashboard .employee-company-chip > span:last-child,
    body[data-pms-theme="dark"] .employee-dashboard .employee-company-chip > span:last-child,
    body[data-bs-theme="dark"] .employee-dashboard .employee-company-chip > span:last-child,
    body.dark .employee-dashboard .employee-company-chip > span:last-child,
    html[data-pms-theme="dark"] .employee-dashboard .employee-company-chip span,
    html[data-bs-theme="dark"] .employee-dashboard .employee-company-chip span,
    html[data-theme="dark"] .employee-dashboard .employee-company-chip span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 750 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .employee-company-logo,
    html[data-bs-theme="dark"] .employee-dashboard .employee-company-logo,
    html[data-theme="dark"] .employee-dashboard .employee-company-logo,
    html.dark .employee-dashboard .employee-company-logo,
    body[data-pms-theme="dark"] .employee-dashboard .employee-company-logo,
    body[data-bs-theme="dark"] .employee-dashboard .employee-company-logo,
    body.dark .employee-dashboard .employee-company-logo {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 900 !important;
    }

    /* 5. Designation Under Employee Name - HIGH CONTRAST & CRISP */
    html[data-pms-theme="dark"] .employee-dashboard .employee-designation,
    html[data-bs-theme="dark"] .employee-dashboard .employee-designation,
    html[data-theme="dark"] .employee-dashboard .employee-designation,
    html.dark .employee-dashboard .employee-designation,
    body[data-pms-theme="dark"] .employee-dashboard .employee-designation,
    body[data-bs-theme="dark"] .employee-dashboard .employee-designation,
    body.dark .employee-dashboard .employee-designation,
    html[data-pms-theme="dark"] .employee-designation,
    html[data-bs-theme="dark"] .employee-designation,
    html[data-theme="dark"] .employee-designation,
    html.dark .employee-designation,
    body[data-pms-theme="dark"] .employee-designation,
    body[data-bs-theme="dark"] .employee-designation,
    body.dark .employee-designation,
    html[data-pms-theme="dark"] td .employee-designation,
    html[data-bs-theme="dark"] td .employee-designation,
    html[data-theme="dark"] td .employee-designation,
    html.dark td .employee-designation,
    html[data-pms-theme="dark"] .employee-details span.employee-designation,
    html[data-bs-theme="dark"] .employee-details span.employee-designation,
    html[data-theme="dark"] .employee-details span.employee-designation,
    html.dark .employee-details span.employee-designation {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        font-weight: 500 !important;
        opacity: 1 !important;
    }

    /* 6. Role & Reporting Column - CLEAR & LEGIBLE */
    html[data-pms-theme="dark"] .employee-dashboard .role-name,
    html[data-bs-theme="dark"] .employee-dashboard .role-name,
    html[data-theme="dark"] .employee-dashboard .role-name,
    html.dark .employee-dashboard .role-name,
    body[data-pms-theme="dark"] .employee-dashboard .role-name,
    body[data-bs-theme="dark"] .employee-dashboard .role-name,
    body.dark .employee-dashboard .role-name,
    html[data-pms-theme="dark"] .role-name,
    html[data-bs-theme="dark"] .role-name,
    html[data-theme="dark"] .role-name,
    html.dark .role-name,
    html[data-pms-theme="dark"] td .role-name,
    html[data-bs-theme="dark"] td .role-name,
    html[data-theme="dark"] td .role-name,
    html[data-pms-theme="dark"] .employee-dashboard .role-info,
    html[data-bs-theme="dark"] .employee-dashboard .role-info,
    html[data-theme="dark"] .employee-dashboard .role-info,
    html.dark .employee-dashboard .role-info,
    body[data-pms-theme="dark"] .employee-dashboard .role-info,
    body[data-bs-theme="dark"] .employee-dashboard .role-info,
    body.dark .employee-dashboard .role-info {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.92rem !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .reports-to,
    html[data-bs-theme="dark"] .employee-dashboard .reports-to,
    html[data-theme="dark"] .employee-dashboard .reports-to,
    html.dark .employee-dashboard .reports-to,
    body[data-pms-theme="dark"] .employee-dashboard .reports-to,
    body[data-bs-theme="dark"] .employee-dashboard .reports-to,
    body.dark .employee-dashboard .reports-to,
    html[data-pms-theme="dark"] .reports-to,
    html[data-bs-theme="dark"] .reports-to,
    html[data-theme="dark"] .reports-to,
    html.dark .reports-to,
    html[data-pms-theme="dark"] td .reports-to,
    html[data-bs-theme="dark"] td .reports-to,
    html[data-theme="dark"] td .reports-to,
    html[data-pms-theme="dark"] .employee-dashboard .reports-to *:not(i),
    html[data-bs-theme="dark"] .employee-dashboard .reports-to *:not(i),
    html[data-theme="dark"] .employee-dashboard .reports-to *:not(i),
    html.dark .employee-dashboard .reports-to *:not(i),
    body[data-pms-theme="dark"] .employee-dashboard .reports-to *:not(i),
    body[data-bs-theme="dark"] .employee-dashboard .reports-to *:not(i),
    body.dark .employee-dashboard .reports-to *:not(i) {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        font-weight: 500 !important;
        font-size: 0.82rem !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .reports-to i,
    html[data-bs-theme="dark"] .employee-dashboard .reports-to i,
    html[data-theme="dark"] .employee-dashboard .reports-to i,
    html.dark .employee-dashboard .reports-to i,
    body[data-pms-theme="dark"] .employee-dashboard .reports-to i,
    body[data-bs-theme="dark"] .employee-dashboard .reports-to i,
    body.dark .employee-dashboard .reports-to i,
    html[data-pms-theme="dark"] .reports-to i,
    html[data-bs-theme="dark"] .reports-to i,
    html[data-theme="dark"] .reports-to i,
    html.dark .reports-to i,
    html[data-pms-theme="dark"] td .reports-to i,
    html[data-bs-theme="dark"] td .reports-to i,
    html[data-theme="dark"] td .reports-to i {
        color: #22D3EE !important;
        -webkit-text-fill-color: #22D3EE !important;
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        font-style: normal !important;
        display: inline-block !important;
    }

    /* 7. Status Column Badges */
    html[data-pms-theme="dark"] .employee-dashboard .status-badge,
    html[data-bs-theme="dark"] .employee-dashboard .status-badge,
    html[data-theme="dark"] .employee-dashboard .status-badge,
    html.dark .employee-dashboard .status-badge,
    body[data-pms-theme="dark"] .employee-dashboard .status-badge,
    body[data-bs-theme="dark"] .employee-dashboard .status-badge,
    body.dark .employee-dashboard .status-badge,
    html[data-pms-theme="dark"] .status-badge,
    html[data-bs-theme="dark"] .status-badge,
    html[data-theme="dark"] .status-badge,
    html.dark .status-badge,
    body[data-pms-theme="dark"] .status-badge,
    body[data-bs-theme="dark"] .status-badge,
    body.dark .status-badge,
    html[data-pms-theme="dark"] .status-badge .status-text,
    html[data-bs-theme="dark"] .status-badge .status-text,
    html[data-theme="dark"] .status-badge .status-text,
    html.dark .status-badge .status-text,
    body[data-pms-theme="dark"] .status-badge .status-text,
    body[data-bs-theme="dark"] .status-badge .status-text,
    body.dark .status-badge .status-text {
        border-radius: 999px !important;
        padding: 5px 14px !important;
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .status-unknown,
    html[data-bs-theme="dark"] .employee-dashboard .status-unknown,
    html[data-theme="dark"] .employee-dashboard .status-unknown,
    html.dark .employee-dashboard .status-unknown,
    body[data-pms-theme="dark"] .employee-dashboard .status-unknown,
    body[data-bs-theme="dark"] .employee-dashboard .status-unknown,
    body.dark .employee-dashboard .status-unknown,
    html[data-pms-theme="dark"] .status-unknown,
    html[data-bs-theme="dark"] .status-unknown,
    html[data-theme="dark"] .status-unknown,
    html.dark .status-unknown,
    body[data-pms-theme="dark"] .status-unknown,
    body[data-bs-theme="dark"] .status-unknown,
    body.dark .status-unknown,
    html[data-pms-theme="dark"] .status-unknown .status-text,
    html[data-bs-theme="dark"] .status-unknown .status-text,
    html[data-theme="dark"] .status-unknown .status-text,
    html.dark .status-unknown .status-text,
    body[data-pms-theme="dark"] .status-unknown .status-text,
    body[data-bs-theme="dark"] .status-unknown .status-text,
    body.dark .status-unknown .status-text {
        background: rgba(148, 163, 184, 0.18) !important;
        border: 1px solid rgba(203, 213, 225, 0.45) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .status-active,
    html[data-bs-theme="dark"] .employee-dashboard .status-active,
    html[data-theme="dark"] .employee-dashboard .status-active,
    html.dark .employee-dashboard .status-active,
    body[data-pms-theme="dark"] .employee-dashboard .status-active,
    body[data-bs-theme="dark"] .employee-dashboard .status-active,
    body.dark .employee-dashboard .status-active,
    html[data-pms-theme="dark"] .status-active,
    html[data-bs-theme="dark"] .status-active,
    html[data-theme="dark"] .status-active,
    html.dark .status-active,
    body[data-pms-theme="dark"] .status-active,
    body[data-bs-theme="dark"] .status-active,
    body.dark .status-active,
    html[data-pms-theme="dark"] .status-active .status-text,
    html[data-bs-theme="dark"] .status-active .status-text,
    html[data-theme="dark"] .status-active .status-text,
    html.dark .status-active .status-text,
    body[data-pms-theme="dark"] .status-active .status-text,
    body[data-bs-theme="dark"] .status-active .status-text,
    body.dark .status-active .status-text {
        background: rgba(47, 107, 255, 0.2) !important;
        border: 1px solid rgba(79, 131, 255, 0.5) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .status-active .status-dot,
    html[data-bs-theme="dark"] .employee-dashboard .status-active .status-dot,
    html[data-theme="dark"] .employee-dashboard .status-active .status-dot,
    html.dark .employee-dashboard .status-active .status-dot,
    body[data-pms-theme="dark"] .employee-dashboard .status-active .status-dot,
    body[data-bs-theme="dark"] .employee-dashboard .status-active .status-dot,
    body.dark .employee-dashboard .status-active .status-dot,
    html[data-pms-theme="dark"] .status-active .status-dot,
    html[data-bs-theme="dark"] .status-active .status-dot,
    html[data-theme="dark"] .status-active .status-dot,
    html.dark .status-active .status-dot,
    body[data-pms-theme="dark"] .status-active .status-dot,
    body[data-bs-theme="dark"] .status-active .status-dot,
    body.dark .status-active .status-dot {
        background: #60A5FA !important;
        box-shadow: 0 0 0 2px rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .status-inactive,
    html[data-bs-theme="dark"] .employee-dashboard .status-inactive,
    html[data-theme="dark"] .employee-dashboard .status-inactive,
    html.dark .employee-dashboard .status-inactive,
    body[data-pms-theme="dark"] .employee-dashboard .status-inactive,
    body[data-bs-theme="dark"] .employee-dashboard .status-inactive,
    body.dark .employee-dashboard .status-inactive,
    html[data-pms-theme="dark"] .status-inactive,
    html[data-bs-theme="dark"] .status-inactive,
    html[data-theme="dark"] .status-inactive,
    html.dark .status-inactive,
    body[data-pms-theme="dark"] .status-inactive,
    body[data-bs-theme="dark"] .status-inactive,
    body.dark .status-inactive,
    html[data-pms-theme="dark"] .status-inactive .status-text,
    html[data-bs-theme="dark"] .status-inactive .status-text,
    html[data-theme="dark"] .status-inactive .status-text,
    html.dark .status-inactive .status-text,
    body[data-pms-theme="dark"] .status-inactive .status-text,
    body[data-bs-theme="dark"] .status-inactive .status-text,
    body.dark .status-inactive .status-text {
        background: rgba(239, 68, 68, 0.2) !important;
        border: 1px solid rgba(248, 113, 113, 0.5) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .status-inactive .status-dot,
    html[data-bs-theme="dark"] .employee-dashboard .status-inactive .status-dot,
    html[data-theme="dark"] .employee-dashboard .status-inactive .status-dot,
    html.dark .employee-dashboard .status-inactive .status-dot,
    body[data-pms-theme="dark"] .employee-dashboard .status-inactive .status-dot,
    body[data-bs-theme="dark"] .employee-dashboard .status-inactive .status-dot,
    body.dark .employee-dashboard .status-inactive .status-dot,
    html[data-pms-theme="dark"] .status-inactive .status-dot,
    html[data-bs-theme="dark"] .status-inactive .status-dot,
    html[data-theme="dark"] .status-inactive .status-dot,
    html.dark .status-inactive .status-dot,
    body[data-pms-theme="dark"] .status-inactive .status-dot,
    body[data-bs-theme="dark"] .status-inactive .status-dot,
    body.dark .status-inactive .status-dot {
        background: #f87171 !important;
        box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.35) !important;
    }

    /* 8. Table Footer & Pagination Controls in Dark Mode */
    html[data-pms-theme="dark"] .employee-dashboard .table-footer,
    html[data-bs-theme="dark"] .employee-dashboard .table-footer,
    html[data-theme="dark"] .employee-dashboard .table-footer,
    html.dark .employee-dashboard .table-footer,
    body[data-pms-theme="dark"] .employee-dashboard .table-footer,
    body[data-bs-theme="dark"] .employee-dashboard .table-footer,
    body.dark .employee-dashboard .table-footer,
    [data-pms-theme="dark"] .employee-dashboard .table-footer,
    [data-bs-theme="dark"] .employee-dashboard .table-footer,
    [data-theme="dark"] .employee-dashboard .table-footer,
    .dark .employee-dashboard .table-footer {
        background: #141B3D !important;
        border-top: 1px solid rgba(79, 131, 255, 0.18) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .show-entries,
    html[data-bs-theme="dark"] .employee-dashboard .show-entries,
    html[data-theme="dark"] .employee-dashboard .show-entries,
    html.dark .employee-dashboard .show-entries,
    body[data-pms-theme="dark"] .employee-dashboard .show-entries,
    body[data-bs-theme="dark"] .employee-dashboard .show-entries,
    body.dark .employee-dashboard .show-entries,
    [data-pms-theme="dark"] .employee-dashboard .show-entries,
    [data-bs-theme="dark"] .employee-dashboard .show-entries,
    [data-theme="dark"] .employee-dashboard .show-entries,
    .dark .employee-dashboard .show-entries,
    html[data-pms-theme="dark"] .employee-dashboard .show-entries span,
    html[data-bs-theme="dark"] .employee-dashboard .show-entries span,
    html[data-theme="dark"] .employee-dashboard .show-entries span,
    html.dark .employee-dashboard .show-entries span,
    body[data-pms-theme="dark"] .employee-dashboard .show-entries span,
    body[data-bs-theme="dark"] .employee-dashboard .show-entries span,
    body.dark .employee-dashboard .show-entries span,
    [data-pms-theme="dark"] .employee-dashboard .show-entries span,
    [data-bs-theme="dark"] .employee-dashboard .show-entries span,
    [data-theme="dark"] .employee-dashboard .show-entries span,
    .dark .employee-dashboard .show-entries span {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        font-weight: 600 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .show-entries select,
    html[data-bs-theme="dark"] .employee-dashboard .show-entries select,
    html[data-theme="dark"] .employee-dashboard .show-entries select,
    html.dark .employee-dashboard .show-entries select,
    body[data-pms-theme="dark"] .employee-dashboard .show-entries select,
    body[data-bs-theme="dark"] .employee-dashboard .show-entries select,
    body.dark .employee-dashboard .show-entries select,
    html[data-pms-theme="dark"] #showEntries,
    html[data-bs-theme="dark"] #showEntries,
    html[data-theme="dark"] #showEntries,
    html.dark #showEntries,
    body[data-pms-theme="dark"] #showEntries,
    body[data-bs-theme="dark"] #showEntries,
    body.dark #showEntries,
    [data-pms-theme="dark"] #showEntries,
    [data-bs-theme="dark"] #showEntries,
    [data-theme="dark"] #showEntries,
    .dark #showEntries {
        background-color: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 10px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] #showEntries option,
    html[data-bs-theme="dark"] #showEntries option,
    html[data-theme="dark"] #showEntries option,
    html.dark #showEntries option,
    body[data-pms-theme="dark"] #showEntries option,
    body[data-bs-theme="dark"] #showEntries option,
    body.dark #showEntries option,
    [data-pms-theme="dark"] #showEntries option,
    [data-bs-theme="dark"] #showEntries option,
    [data-theme="dark"] #showEntries option,
    .dark #showEntries option {
        background-color: #0F1530 !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .pagination-info,
    html[data-bs-theme="dark"] .employee-dashboard .pagination-info,
    html[data-theme="dark"] .employee-dashboard .pagination-info,
    html.dark .employee-dashboard .pagination-info,
    body[data-pms-theme="dark"] .employee-dashboard .pagination-info,
    body[data-bs-theme="dark"] .employee-dashboard .pagination-info,
    body.dark .employee-dashboard .pagination-info,
    [data-pms-theme="dark"] .employee-dashboard .pagination-info,
    [data-bs-theme="dark"] .employee-dashboard .pagination-info,
    [data-theme="dark"] .employee-dashboard .pagination-info,
    .dark .employee-dashboard .pagination-info {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
        letter-spacing: 0.2px !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .page-btn,
    html[data-bs-theme="dark"] .employee-dashboard .page-btn,
    html[data-theme="dark"] .employee-dashboard .page-btn,
    html.dark .employee-dashboard .page-btn,
    body[data-pms-theme="dark"] .employee-dashboard .page-btn,
    body[data-bs-theme="dark"] .employee-dashboard .page-btn,
    body.dark .employee-dashboard .page-btn,
    [data-pms-theme="dark"] .employee-dashboard .page-btn,
    [data-bs-theme="dark"] .employee-dashboard .page-btn,
    [data-theme="dark"] .employee-dashboard .page-btn,
    .dark .employee-dashboard .page-btn {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .page-btn:hover:not(.disabled),
    html[data-bs-theme="dark"] .employee-dashboard .page-btn:hover:not(.disabled),
    html[data-theme="dark"] .employee-dashboard .page-btn:hover:not(.disabled),
    html.dark .employee-dashboard .page-btn:hover:not(.disabled) {
        background: rgba(79, 131, 255, 0.25) !important;
        border-color: rgba(79, 131, 255, 0.6) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .page-btn.active,
    html[data-bs-theme="dark"] .employee-dashboard .page-btn.active,
    html[data-theme="dark"] .employee-dashboard .page-btn.active,
    html.dark .employee-dashboard .page-btn.active {
        background: linear-gradient(135deg, #4F83FF, #2F6BFF) !important;
        border-color: #2F6BFF !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .page-btn.disabled,
    html[data-bs-theme="dark"] .employee-dashboard .page-btn.disabled,
    html[data-theme="dark"] .employee-dashboard .page-btn.disabled,
    html.dark .employee-dashboard .page-btn.disabled {
        background: rgba(15, 21, 48, 0.5) !important;
        border-color: rgba(79, 131, 255, 0.12) !important;
        color: rgba(203, 213, 225, 0.4) !important;
        -webkit-text-fill-color: rgba(203, 213, 225, 0.4) !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
    }

    html[data-pms-theme="dark"] .employee-dashboard .page-dots,
    html[data-bs-theme="dark"] .employee-dashboard .page-dots,
    html[data-theme="dark"] .employee-dashboard .page-dots,
    html.dark .employee-dashboard .page-dots {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    /* ===== EMPLOYEE TABLE ACTION BUTTONS (THREE DOTS) & DROPDOWN MENU ===== */
    .btn-icon {
        width: 36px !important;
        height: 36px !important;
        padding: 0 !important;
        border-radius: 10px !important;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
    }
    .btn-icon:hover {
        background: #e2e8f0;
        color: #1e293b;
        border-color: #94a3b8;
    }

    html[data-pms-theme="dark"] .btn-icon,
    html[data-bs-theme="dark"] .btn-icon,
    html[data-theme="dark"] .btn-icon,
    html.dark .btn-icon,
    body[data-pms-theme="dark"] .btn-icon,
    body[data-bs-theme="dark"] .btn-icon,
    body.dark-mode .btn-icon,
    .dark-mode .btn-icon,
    .dark .btn-icon,
    html[data-pms-theme="dark"] td .btn-icon,
    html[data-bs-theme="dark"] td .btn-icon,
    html[data-theme="dark"] td .btn-icon,
    html.dark td .btn-icon,
    .dark-mode td .btn-icon,
    .dark td .btn-icon {
        background: rgba(30, 41, 59, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        color: #f1f5f9 !important;
        -webkit-text-fill-color: #f1f5f9 !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .btn-icon:hover,
    html[data-bs-theme="dark"] .btn-icon:hover,
    html[data-theme="dark"] .btn-icon:hover,
    html.dark .btn-icon:hover,
    body[data-pms-theme="dark"] .btn-icon:hover,
    body[data-bs-theme="dark"] .btn-icon:hover,
    body.dark-mode .btn-icon:hover,
    .dark-mode .btn-icon:hover,
    .dark .btn-icon:hover,
    html[data-pms-theme="dark"] td .btn-icon:hover,
    html[data-bs-theme="dark"] td .btn-icon:hover,
    html[data-theme="dark"] td .btn-icon:hover,
    html.dark td .btn-icon:hover,
    .dark-mode td .btn-icon:hover,
    .dark td .btn-icon:hover {
        background: rgba(59, 130, 246, 0.25) !important;
        border-color: rgba(96, 165, 250, 0.6) !important;
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
        transform: translateY(-1px) !important;
    }

    html[data-pms-theme="dark"] .btn-icon i,
    html[data-bs-theme="dark"] .btn-icon i,
    html[data-theme="dark"] .btn-icon i,
    html.dark .btn-icon i,
    body[data-pms-theme="dark"] .btn-icon i,
    body[data-bs-theme="dark"] .btn-icon i,
    body.dark-mode .btn-icon i,
    .dark-mode .btn-icon i,
    .dark .btn-icon i,
    html[data-pms-theme="dark"] td .btn-icon i,
    html[data-bs-theme="dark"] td .btn-icon i,
    html[data-theme="dark"] td .btn-icon i,
    html.dark td .btn-icon i,
    .dark-mode td .btn-icon i,
    .dark td .btn-icon i {
        color: #f1f5f9 !important;
        -webkit-text-fill-color: #f1f5f9 !important;
    }

    html[data-pms-theme="dark"] .btn-icon:hover i,
    html[data-bs-theme="dark"] .btn-icon:hover i,
    html[data-theme="dark"] .btn-icon:hover i,
    html.dark .btn-icon:hover i,
    body[data-pms-theme="dark"] .btn-icon:hover i,
    body[data-bs-theme="dark"] .btn-icon:hover i,
    body.dark-mode .btn-icon:hover i,
    .dark-mode .btn-icon:hover i,
    .dark .btn-icon:hover i {
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
    }

    /* Dropdown menu dark mode overrides */
    html[data-pms-theme="dark"] .employee-list-table .dropdown-menu,
    html[data-bs-theme="dark"] .employee-list-table .dropdown-menu,
    html[data-theme="dark"] .employee-list-table .dropdown-menu,
    html.dark .employee-list-table .dropdown-menu,
    body[data-pms-theme="dark"] .employee-list-table .dropdown-menu,
    body[data-bs-theme="dark"] .employee-list-table .dropdown-menu,
    body.dark-mode .employee-list-table .dropdown-menu,
    .dark-mode .employee-list-table .dropdown-menu,
    .dark .employee-list-table .dropdown-menu {
        background: #0f172a !important;
        border: 1px solid #334155 !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.6) !important;
        border-radius: 12px !important;
        padding: 6px !important;
    }

    html[data-pms-theme="dark"] .employee-list-table .dropdown-item,
    html[data-bs-theme="dark"] .employee-list-table .dropdown-item,
    html[data-theme="dark"] .employee-list-table .dropdown-item,
    html.dark .employee-list-table .dropdown-item,
    body[data-pms-theme="dark"] .employee-list-table .dropdown-item,
    body[data-bs-theme="dark"] .employee-list-table .dropdown-item,
    body.dark-mode .employee-list-table .dropdown-item,
    .dark-mode .employee-list-table .dropdown-item,
    .dark .employee-list-table .dropdown-item {
        color: #e2e8f0 !important;
        -webkit-text-fill-color: #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 8px 14px !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
    }

    html[data-pms-theme="dark"] .employee-list-table .dropdown-item:hover,
    html[data-bs-theme="dark"] .employee-list-table .dropdown-item:hover,
    html[data-theme="dark"] .employee-list-table .dropdown-item:hover,
    html.dark .employee-list-table .dropdown-item:hover,
    body[data-pms-theme="dark"] .employee-list-table .dropdown-item:hover,
    body[data-bs-theme="dark"] .employee-list-table .dropdown-item:hover,
    body.dark-mode .employee-list-table .dropdown-item:hover,
    .dark-mode .employee-list-table .dropdown-item:hover,
    .dark .employee-list-table .dropdown-item:hover {
        background: rgba(59, 130, 246, 0.18) !important;
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
    }

    /* Checkbox & Avatar styling */
    html[data-pms-theme="dark"] .employee-list-table .form-check-input,
    html[data-bs-theme="dark"] .employee-list-table .form-check-input,
    html[data-theme="dark"] .employee-list-table .form-check-input,
    html.dark .employee-list-table .form-check-input,
    body[data-pms-theme="dark"] .employee-list-table .form-check-input,
    body[data-bs-theme="dark"] .employee-list-table .form-check-input,
    body.dark-mode .employee-list-table .form-check-input,
    .dark-mode .employee-list-table .form-check-input,
    .dark .employee-list-table .form-check-input {
        background-color: #1e293b !important;
        border: 1.5px solid #475569 !important;
        border-radius: 6px !important;
        width: 18px !important;
        height: 18px !important;
        cursor: pointer !important;
    }

    html[data-pms-theme="dark"] .employee-list-table .form-check-input:checked,
    html[data-bs-theme="dark"] .employee-list-table .form-check-input:checked,
    html[data-theme="dark"] .employee-list-table .form-check-input:checked,
    html.dark .employee-list-table .form-check-input:checked,
    body[data-pms-theme="dark"] .employee-list-table .form-check-input:checked,
    body[data-bs-theme="dark"] .employee-list-table .form-check-input:checked,
    body.dark-mode .employee-list-table .form-check-input:checked,
    .dark-mode .employee-list-table .form-check-input:checked,
    .dark .employee-list-table .form-check-input:checked {
        background-color: #3b82f6 !important;
        border-color: #60a5fa !important;
    }

    html[data-pms-theme="dark"] .profile-placeholder,
    html[data-bs-theme="dark"] .profile-placeholder,
    html[data-theme="dark"] .profile-placeholder,
    html.dark .profile-placeholder,
    body[data-pms-theme="dark"] .profile-placeholder,
    body[data-bs-theme="dark"] .profile-placeholder,
    body.dark-mode .profile-placeholder,
    .dark-mode .profile-placeholder,
    .dark .profile-placeholder {
        background: linear-gradient(135deg, rgba(79, 131, 255, 0.25), rgba(47, 107, 255, 0.4)) !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3) !important;
        font-weight: 700 !important;
    }

    .profile-initials {
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }

    /* ===== EMP ID BADGE OVERFLOW FIX ===== */
    .employee-id-badge,
    .employee-dashboard .employee-list-table .employee-id-badge {
        display: inline-block !important;
        padding: 6px 10px !important;
        width: auto !important;
        max-width: 100% !important;
        min-width: 0 !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
        overflow: hidden !important;
        vertical-align: middle;
        line-height: 1.45;
        box-sizing: border-box !important;
        border-radius: 8px !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
    }

    .employee-dashboard .employee-list-table thead th:nth-child(2),
    .employee-dashboard .employee-list-table tbody td:nth-child(2) {
        width: 170px !important;
        min-width: 170px !important;
    }

    /* ===== ACTION DOTS BUTTON VISIBILITY ===== */
    .btn-action-dots {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        padding: 0 !important;
        border-radius: 10px !important;
        background: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35) !important;
    }
    .btn-action-dots:hover {
        background: #2563eb !important;
        border-color: #60a5fa !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: translateY(-1px) !important;
    }
    .btn-action-dots i {
        font-size: 1.05rem !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: inline-block !important;
        line-height: 1 !important;
    }
    /* Keep list controls sized consistently without changing selection behavior. */
    .employee-dashboard .employee-list-table input.form-check-input[type="checkbox"] {
        appearance: none !important;
        -webkit-appearance: none !important;
        display: inline-block !important;
        width: 18px !important;
        height: 18px !important;
        min-width: 18px !important;
        padding: 0 !important;
        margin: 0 !important;
        vertical-align: middle !important;
        border: 2px solid #94a3b8 !important;
        border-radius: 4px !important;
        background-color: #ffffff !important;
        background-image: none !important;
        cursor: pointer;
    }
    .employee-dashboard .employee-list-table input.form-check-input[type="checkbox"]:checked,
    .employee-dashboard .employee-list-table input.form-check-input[type="checkbox"]:indeterminate {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m3 8 3 3 7-7'/%3E%3C/svg%3E") !important;
        background-position: center !important;
        background-size: 14px 14px !important;
        background-repeat: no-repeat !important;
    }
    .employee-dashboard .employee-list-table input.form-check-input[type="checkbox"]:indeterminate {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath stroke='white' stroke-linecap='round' stroke-width='2.5' d='M4 8h8'/%3E%3C/svg%3E") !important;
    }
    .employee-dashboard .employee-list-table input.form-check-input[type="checkbox"]:focus-visible,
    .employee-dashboard .employee-list-table .btn-action-dots:focus-visible {
        outline: 2px solid #2563eb !important;
        outline-offset: 3px !important;
    }
    .employee-dashboard .employee-list-table input.form-check-input[type="checkbox"]:disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }
    .employee-dashboard .employee-list-table .btn-action-dots > i,
    .employee-dashboard .employee-list-table .btn-action-dots > i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .employee-dashboard .stat-card.total .stat-icon > i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .employee-dashboard .stat-card.total .stat-icon > i::before {
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
    }
</style>

<style>
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .employee-dashboard .stat-card .stat-icon > i,
    :is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) .employee-dashboard .stat-card .stat-icon > i::before {
        background: transparent !important;
        background-image: none !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        box-shadow: none !important;
    }
</style>

<style>
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(.dark-mode):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .employee-dashboard .employee-list-table .btn-action-dots {
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #334155 !important;
        box-shadow: 0 2px 4px rgba(15, 23, 42, 0.06) !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(.dark-mode):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .employee-dashboard .employee-list-table .btn-action-dots:hover {
        background: #e2e8f0 !important;
        border-color: #94a3b8 !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(.dark-mode):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .employee-dashboard .employee-list-table .btn-action-dots > i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark):not(.dark-mode):not(:has(body.dark, body.dark-mode, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"])) .employee-dashboard .employee-list-table .btn-action-dots > i::before {
        color: #334155 !important;
        -webkit-text-fill-color: #334155 !important;
    }
    /* Mobile employee cards override desktop column sizing and utility widths. */
    @media (max-width: 768px) {
        .employee-dashboard { padding: 12px !important; }
        .employee-dashboard .table-card > .table-responsive { padding: 12px; }
        .employee-dashboard #employeeTable,
        .employee-dashboard #employeeTable tbody {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .employee-dashboard #employeeTable thead { display: none !important; }
        .employee-dashboard #employeeTable tbody tr {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0 12px;
            width: 100% !important;
            height: auto !important;
            margin: 0 0 12px !important;
            padding: 12px !important;
            border-radius: 14px;
        }
        .employee-dashboard #employeeTable tbody tr > td {
            display: block !important;
            grid-column: 1 / -1;
            width: auto !important;
            min-width: 0 !important;
            max-width: 100% !important;
            height: auto !important;
            padding: 9px 0 !important;
            text-align: left !important;
            white-space: normal !important;
            overflow-wrap: anywhere;
            border: 0 !important;
        }
        .employee-dashboard #employeeTable tbody tr > td::before {
            position: static !important;
            display: block;
            width: auto !important;
            margin-bottom: 4px;
            font-size: 0.7rem;
            color: var(--emp-designation-color, #64748b) !important;
            -webkit-text-fill-color: var(--emp-designation-color, #64748b) !important;
        }
        .employee-dashboard #employeeTable tbody tr > td:first-child:not([colspan]) {
            grid-column: 1;
            grid-row: 1;
            padding: 0 !important;
        }
        .employee-dashboard #employeeTable tbody tr > td:last-child:not([colspan]) {
            grid-column: 2;
            grid-row: 1;
            padding: 0 !important;
            text-align: right !important;
        }
        .employee-dashboard #employeeTable td:first-child::before,
        .employee-dashboard #employeeTable td:last-child::before { display: none; }
        .employee-dashboard #employeeTable .employee-info { width: 100%; min-width: 0; }
        .employee-dashboard #employeeTable .profile-image,
        .employee-dashboard #employeeTable .profile-placeholder { flex-shrink: 0; }
        .employee-dashboard #employeeTable .employee-name,
        .employee-dashboard #employeeTable .employee-designation,
        .employee-dashboard #employeeTable .email-link {
            white-space: normal !important;
            overflow-wrap: anywhere;
        }
        .employee-dashboard #employeeTable .profile-initials {
            color: #2F6BFF !important;
            -webkit-text-fill-color: #2F6BFF !important;
        }
    }

    /* Employee ID and status contrast must override shared table text colors. */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode) .employee-dashboard #employeeTable .employee-id-badge {
        background: #141b3d !important;
        border-color: #415686 !important;
        color: #e2edff !important;
        -webkit-text-fill-color: #e2edff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode) .employee-dashboard #employeeTable .status-badge {
        color: #eef1fb !important;
        -webkit-text-fill-color: #eef1fb !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode) .employee-dashboard #employeeTable .status-badge .status-text {
        color: #eef1fb !important;
        -webkit-text-fill-color: #eef1fb !important;
        background: transparent !important;
        border: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode) .employee-dashboard #employeeTable .status-active .status-text {
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode) .employee-dashboard #employeeTable .status-inactive .status-text {
        color: #fecdd3 !important;
        -webkit-text-fill-color: #fecdd3 !important;
    }
</style>
@include('admin.employees.partials.workspace-overrides')
