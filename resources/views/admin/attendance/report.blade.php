@extends('admin.layout.app')

@section('title', 'Attendance Report')

@section('content')
<style>
    /* ===== PREMIUM ATTENDANCE REPORT STYLES ===== */
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

    .report-container {
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

    /* ===== HEADER ===== */
    .report-header {
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

    .report-header:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-title h1 {
        font-size: 2rem;
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
        font-size: 0.95rem;
        font-weight: 500;
        margin: 0;
    }

    .header-title p i {
        color: var(--primary-teal);
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
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .filter-group label i {
        color: var(--primary-teal);
        font-size: 0.75rem;
    }

    .filter-group select {
        padding: 0.65rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0f172a;
        background: white;
        transition: var(--transition-smooth);
        outline: none;
        min-height: 46px;
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        padding-right: 2.5rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
    }

    .filter-group select:focus {
        border-color: var(--primary-teal);
        box-shadow: 0 0 0 4px rgba(14, 165, 164, 0.12);
    }

    .filter-group select option {
        padding: 0.5rem;
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
        padding: 0.65rem 1.75rem;
        border-radius: 40px;
        border: none;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--spring-transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 46px;
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.2);
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(14, 165, 164, 0.3);
    }

    /* ===== SUMMARY CARD ===== */
    .summary-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .summary-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .summary-header h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1rem;
    }

    .summary-header h6 i {
        color: var(--primary-teal);
        margin-right: 0.5rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 1rem;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        transition: var(--transition-smooth);
    }

    .summary-item:hover {
        transform: translateY(-2px);
        border-color: var(--primary-teal);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .summary-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .summary-icon.present { background: #d1fae5; }
    .summary-icon.late { background: #fef3c7; }
    .summary-icon.absent { background: #fee2e2; }
    .summary-icon.leave { background: #cffafe; }
    .summary-icon.halfday { background: #ede9fe; }
    .summary-icon.holiday { background: #ffedd5; }
    .summary-icon.dayoff { background: #dbeafe; }

    .summary-info {
        flex: 1;
    }

    .summary-info .label {
        font-size: 0.65rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .summary-info .value {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    /* ===== EXPORT BUTTONS ===== */
    .export-section {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .btn-export-excel {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.5rem;
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: var(--spring-transition);
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.25);
    }

    .btn-export-excel:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(34, 197, 94, 0.35);
        color: white;
    }

    .btn-export-pdf {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.5rem;
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: var(--spring-transition);
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.25);
    }

    .btn-export-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.35);
        color: white;
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
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .table-header h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1rem;
    }

    .table-header h6 i {
        color: var(--primary-teal);
        margin-right: 0.5rem;
    }

    .table-header .employee-name {
        font-weight: 700;
        color: var(--primary-teal);
    }

    .table-responsive {
        overflow-x: auto;
        padding: 1rem 1.5rem 1.5rem;
    }

    .attendance-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 2px;
        min-width: 700px;
    }

    .attendance-grid th {
        padding: 0.6rem 0.4rem;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #475569;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid #e2e8f0;
    }

    .attendance-grid th .day-number {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f172a;
        display: block;
    }

    .attendance-grid th .day-name {
        font-size: 0.6rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .attendance-grid td {
        padding: 0.6rem 0.4rem;
        text-align: center;
        font-size: 1.2rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        transition: var(--transition-smooth);
        min-width: 40px;
    }

    .attendance-grid td:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        z-index: 2;
        position: relative;
    }

    .attendance-grid td .status-tooltip {
        display: none;
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #0f172a;
        color: white;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.6rem;
        font-weight: 600;
        white-space: nowrap;
        margin-bottom: 4px;
    }

    .attendance-grid td:hover .status-tooltip {
        display: block;
    }

    .attendance-grid td.present { background: #d1fae5; border-color: #86efac; }
    .attendance-grid td.absent { background: #fee2e2; border-color: #fca5a5; }
    .attendance-grid td.late { background: #fef3c7; border-color: #fcd34d; }
    .attendance-grid td.half_day { background: #ede9fe; border-color: #c4b5fd; }
    .attendance-grid td.leave { background: #cffafe; border-color: #67e8f9; }
    .attendance-grid td.holiday { background: #ffedd5; border-color: #fdba74; }
    .attendance-grid td.dayoff { background: #dbeafe; border-color: #93c5fd; }
    .attendance-grid td.default { background: #f8fafc; border-color: #e2e8f0; }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-state .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h5 {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #94a3b8;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .report-container {
            padding: 1.5rem 1.25rem;
        }
        .report-header {
            padding: 1.5rem;
        }
        .header-title h1 {
            font-size: 1.6rem;
        }
        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .summary-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .report-container {
            padding: 1rem;
        }
        .report-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .filter-grid {
            grid-template-columns: 1fr;
        }
        .filter-actions {
            width: 100%;
        }
        .btn-filter {
            width: 100%;
            justify-content: center;
        }
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .summary-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .export-section {
            flex-direction: column;
            width: 100%;
        }
        .btn-export-excel,
        .btn-export-pdf {
            justify-content: center;
            width: 100%;
        }
        .table-responsive {
            padding: 0.5rem 0.75rem 0.75rem;
        }
        .attendance-grid th .day-number {
            font-size: 0.75rem;
        }
        .attendance-grid th .day-name {
            font-size: 0.5rem;
        }
        .attendance-grid td {
            font-size: 1rem;
            padding: 0.4rem 0.2rem;
            min-width: 30px;
        }
    }

    @media (max-width: 480px) {
        .header-title h1 {
            font-size: 1.3rem;
        }
        .header-title p {
            font-size: 0.8rem;
        }
        .summary-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        .summary-item {
            padding: 0.4rem 0.6rem;
        }
        .summary-info .value {
            font-size: 0.95rem;
        }
        .attendance-grid td {
            font-size: 0.85rem;
            padding: 0.3rem 0.15rem;
            min-width: 24px;
        }
        .attendance-grid th {
            font-size: 0.55rem;
            padding: 0.3rem 0.15rem;
        }
        .attendance-grid th .day-number {
            font-size: 0.65rem;
        }
        .filter-group select {
            font-size: 0.8rem;
            min-height: 40px;
            padding: 0.5rem 0.8rem;
        }
        .btn-filter {
            font-size: 0.8rem;
            min-height: 40px;
            padding: 0.5rem 1.25rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .report-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .report-header,
    html[data-pms-theme="dark"] .filter-card,
    html[data-pms-theme="dark"] .summary-card,
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

    html[data-pms-theme="dark"] .filter-group label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-group select {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    }

    html[data-pms-theme="dark"] .filter-group select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    html[data-pms-theme="dark"] .filter-group select option {
        background: #183026;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .summary-item {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .summary-info .value {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .summary-info .label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .summary-header h6 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .table-header h6 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .table-header .employee-name {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .attendance-grid th {
        background: #183026;
        color: #8ba198;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .attendance-grid th .day-number {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .attendance-grid th .day-name {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .attendance-grid td {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .attendance-grid td.present { background: #064e3b; border-color: #065f46; }
    html[data-pms-theme="dark"] .attendance-grid td.absent { background: #450a0a; border-color: #7f1d1d; }
    html[data-pms-theme="dark"] .attendance-grid td.late { background: #451a03; border-color: #78350f; }
    html[data-pms-theme="dark"] .attendance-grid td.half_day { background: #2e1065; border-color: #4c1d95; }
    html[data-pms-theme="dark"] .attendance-grid td.leave { background: #164e63; border-color: #155e75; }
    html[data-pms-theme="dark"] .attendance-grid td.holiday { background: #431407; border-color: #7c2d12; }
    html[data-pms-theme="dark"] .attendance-grid td.dayoff { background: #172554; border-color: #1e3a8a; }
    html[data-pms-theme="dark"] .attendance-grid td.default { background: #102119; border-color: rgba(122, 240, 181, 0.08); }

    html[data-pms-theme="dark"] .empty-state h5 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .empty-state p {
        color: #8ba198;
    }
</style>

<div class="report-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        <!-- ===== HEADER ===== -->
        <div class="report-header">
            <div class="header-title">
                <h1>
                    <i class="fas fa-file-alt me-2"></i>Attendance Report
                </h1>
                <p><i class="fas fa-info-circle me-1"></i>View and analyze employee attendance records</p>
            </div>
        </div>

        <!-- ===== FILTER CARD ===== -->
        <div class="filter-card">
            <form method="GET" class="filter-grid">
                <div class="filter-group">
                    <label for="user_id"><i class="fas fa-user"></i> Employee</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">-- Select Employee --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="month"><i class="fas fa-calendar-alt"></i> Month</label>
                    <select name="month" id="month" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="year"><i class="fas fa-calendar"></i> Year</label>
                    <select name="year" id="year" class="form-select">
                        @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        @if($selectedUser)
            <!-- ===== SUMMARY CARD ===== -->
            <div class="summary-card">
                <div class="summary-header">
                    <h6><i class="fas fa-chart-pie"></i> Summary for <span style="color: var(--primary-teal);">{{ $selectedUser->name }}</span></h6>
                    <span style="font-size: 0.75rem; color: #94a3b8;">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::createFromDate(null, $month)->format('F Y') }}
                    </span>
                </div>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-icon present">✔️</div>
                        <div class="summary-info">
                            <div class="label">Present</div>
                            <div class="value">{{ $summary['present'] }}</div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon late">⚠️</div>
                        <div class="summary-info">
                            <div class="label">Late</div>
                            <div class="value">{{ $summary['late'] }}</div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon absent">❌</div>
                        <div class="summary-info">
                            <div class="label">Absent</div>
                            <div class="value">{{ $summary['absent'] }}</div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon leave">🛫</div>
                        <div class="summary-info">
                            <div class="label">Leave</div>
                            <div class="value">{{ $summary['leave'] }}</div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon halfday">⏳</div>
                        <div class="summary-info">
                            <div class="label">Half Day</div>
                            <div class="value">{{ $summary['half_day'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon holiday">⭐</div>
                        <div class="summary-info">
                            <div class="label">Holiday</div>
                            <div class="value">{{ $summary['holiday'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== EXPORT BUTTONS ===== -->
            <div class="export-section">
                <form method="GET" action="{{ route('attendance.export.excel') }}" class="d-inline">
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="btn-export-excel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </form>

                <form method="GET" action="{{ route('attendance.export.pdf') }}" class="d-inline">
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="btn-export-pdf">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </form>
            </div>

            <!-- ===== TABLE CARD ===== -->
            <div class="table-card">
                <div class="table-header">
                    <h6>
                        <i class="fas fa-calendar-check"></i>
                        Attendance Grid
                        <span class="employee-name"> — {{ $selectedUser->name }}</span>
                    </h6>
                    <span style="font-size: 0.75rem; color: #94a3b8;">
                        <i class="fas fa-legend me-1"></i>
                        ✔️ Present · ⚠️ Late · ❌ Absent · 🛫 Leave · ⏳ Half Day · ⭐ Holiday
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="attendance-grid">
                        <thead>
                            <tr>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $date = \Carbon\Carbon::create($year, $month, $i);
                                    @endphp
                                    <th>
                                        <span class="day-number">{{ $i }}</span>
                                        <span class="day-name">{{ $date->format('D') }}</span>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $record = $attendances[$selectedUser->id][$i] ?? null;
                                        $status = $record->status ?? null;
                                        $symbol = match($status) {
                                            'present' => '✔️',
                                            'absent' => '❌',
                                            'holiday' => '⭐',
                                            'late' => '⚠️',
                                            'half_day' => '⏳',
                                            'leave' => '🛫',
                                            'dayoff' => '📅',
                                            default => '—'
                                        };
                                        $class = $status ? str_replace('_', '-', $status) : 'default';
                                    @endphp
                                    <td class="{{ $class }}">
                                        {{ $symbol }}
                                        @if($status)
                                            <span class="status-tooltip">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- ===== EMPTY STATE ===== -->
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <h5>Please Select an Employee</h5>
                    <p>Choose an employee from the dropdown above to view their attendance report.</p>
                </div>
            </div>
        @endif    </div>
</div>
@endsection
