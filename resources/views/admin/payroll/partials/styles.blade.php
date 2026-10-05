<style>
    /* ==========================================================================
       PAYROLL MANAGEMENT SYSTEM — DESIGN SYSTEM
       Derived from resources/ui.html & Sneat Admin Workspace
       ========================================================================== */
    :root {
        --pr-bg: #f8fafc;
        --pr-surface: #ffffff;
        --pr-primary: #2563eb;
        --pr-primary-dark: #1d4ed8;
        --pr-success: #10b981;
        --pr-warning: #f59e0b;
        --pr-danger: #ef4444;
        --pr-info: #06b6d4;
        --pr-purple: #8b5cf6;
        --pr-text: #0f172a;
        --pr-muted: #64748b;
        --pr-border: #e2e8f0;
        --pr-radius: 12px;
        --pr-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
        --pr-shadow-lg: 0 10px 30px rgba(15, 23, 42, .08);
    }

    /* Dark Mode Theme Support */
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) {
        --pr-bg: #0b1026;
        --pr-surface: #141b3d;
        --pr-text: #eef1fb;
        --pr-muted: #9aa3c7;
        --pr-border: rgba(238, 241, 251, 0.1);
        --pr-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
    }

    /* Container & Cards */
    .payroll-container {
        padding: 0.5rem 0 2rem;
    }

    .pr-card {
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        border-radius: var(--pr-radius);
        box-shadow: var(--pr-shadow);
        margin-bottom: 22px;
        overflow: hidden;
    }

    .pr-card-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--pr-border);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pr-card-head h2 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
        color: var(--pr-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pr-card-head .spacer {
        flex: 1;
    }

    .pr-card-body {
        padding: 20px;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }

    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }

    .kpi {
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        border-radius: var(--pr-radius);
        padding: 16px 18px;
        box-shadow: var(--pr-shadow);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    }

    .kpi::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: var(--pr-primary);
    }

    .kpi.green::before { background: var(--pr-success); }
    .kpi.amber::before { background: var(--pr-warning); }
    .kpi.cyan::before { background: var(--pr-info); }
    .kpi.red::before { background: var(--pr-danger); }
    .kpi.purple::before { background: var(--pr-purple); }

    .kpi .label {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--pr-muted);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 6px;
    }

    .kpi .value {
        font-size: 24px;
        font-weight: 800;
        color: var(--pr-text);
        letter-spacing: -.02em;
        line-height: 1.2;
    }

    .kpi .sub {
        font-size: 11.5px;
        color: var(--pr-muted);
        margin-top: 4px;
    }

    /* Sub-nav Tab Bar matching ui.html */
    .payroll-tabs {
        display: flex;
        gap: 6px;
        padding: 6px 8px;
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        border-radius: var(--pr-radius);
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .payroll-tab-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--pr-muted);
        border-radius: 8px;
        text-decoration: none !important;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .payroll-tab-link:hover {
        color: var(--pr-primary);
        background: rgba(37, 99, 235, 0.08);
    }

    .payroll-tab-link.active {
        color: #ffffff !important;
        background: var(--pr-primary);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }

    /* Filters Bar */
    .pr-filters {
        display: flex;
        gap: 12px;
        padding: 14px 18px;
        background: rgba(15, 23, 42, 0.02);
        border-bottom: 1px solid var(--pr-border);
        align-items: flex-end;
        flex-wrap: wrap;
    }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) .pr-filters {
        background: rgba(255, 255, 255, 0.02);
    }

    .pr-filters .field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 140px;
        flex: 1;
    }

    .pr-filters .field.search-field {
        flex: 2;
        min-width: 200px;
    }

    .pr-filters label {
        font-size: 11px;
        font-weight: 700;
        color: var(--pr-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .pr-filters select,
    .pr-filters input {
        width: 100%;
        padding: 7px 12px;
        border: 1px solid var(--pr-border);
        border-radius: 8px;
        background: var(--pr-surface);
        color: var(--pr-text);
        font-size: 13px;
    }

    /* Tables */
    .table-wrap {
        overflow-x: auto;
    }

    .pr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }

    .pr-table th {
        background: rgba(15, 23, 42, 0.03);
        color: var(--pr-muted);
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 12px 16px;
        border-bottom: 1px solid var(--pr-border);
        white-space: nowrap;
    }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) .pr-table th {
        background: rgba(255, 255, 255, 0.03);
    }

    .pr-table td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--pr-border);
        color: var(--pr-text);
        vertical-align: middle;
    }

    .pr-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.03);
    }

    .pr-table .num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }

    /* Status Pills */
    .pr-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .pr-pill.draft { background: #fef3c7; color: #92400e; }
    .pr-pill.calculated { background: #dbeafe; color: #1e40af; }
    .pr-pill.reviewed { background: #ede9fe; color: #5b21b6; }
    .pr-pill.approved { background: #d1fae5; color: #065f46; }
    .pr-pill.finalized { background: #e2e8f0; color: #334155; }
    .pr-pill.active { background: #d1fae5; color: #065f46; }
    .pr-pill.inactive { background: #fee2e2; color: #991b1b; }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) .pr-pill.finalized {
        background: #334155; color: #e2e8f0;
    }

    /* Cell Employee */
    .cell-employee {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cell-employee .avatar-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #8b5cf6);
        color: #fff;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 11.5px;
        flex-shrink: 0;
    }

    .cell-employee .name {
        font-weight: 600;
        color: var(--pr-text);
        line-height: 1.2;
    }

    .cell-employee .meta {
        font-size: 11px;
        color: var(--pr-muted);
        margin-top: 2px;
    }

    /* Buttons */
    .pr-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--pr-border);
        background: var(--pr-surface);
        color: var(--pr-text);
        text-decoration: none !important;
        transition: all 0.15s ease;
    }

    .pr-btn:hover {
        background: rgba(15, 23, 42, 0.05);
        color: var(--pr-text);
    }

    .pr-btn-primary {
        background: var(--pr-primary) !important;
        border-color: var(--pr-primary) !important;
        color: #fff !important;
    }
    .pr-btn-primary:hover {
        background: var(--pr-primary-dark) !important;
    }

    .pr-btn-success {
        background: var(--pr-success) !important;
        border-color: var(--pr-success) !important;
        color: #fff !important;
    }

    .pr-btn-warn {
        background: var(--pr-warning) !important;
        border-color: var(--pr-warning) !important;
        color: #fff !important;
    }

    .pr-btn-sm {
        padding: 4px 9px;
        font-size: 11.5px;
    }

    /* Breakdown Bar */
    .breakdown {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .bd-row {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12.5px;
    }

    .bd-row .name {
        width: 90px;
        font-weight: 600;
        color: var(--pr-text);
    }

    .bd-bar {
        flex: 1;
        height: 10px;
        background: rgba(15, 23, 42, 0.08);
        border-radius: 99px;
        overflow: hidden;
    }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) .bd-bar {
        background: rgba(255, 255, 255, 0.08);
    }

    .bd-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.4s ease;
    }

    .bd-row .count {
        width: 45px;
        text-align: right;
        font-weight: 700;
        color: var(--pr-muted);
    }

    /* Processing Steps */
    .steps {
        display: flex;
        gap: 10px;
        padding-bottom: 18px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--pr-border);
        flex-wrap: wrap;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--pr-muted);
        font-weight: 600;
    }

    .step .num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.08);
        color: var(--pr-muted);
        display: grid;
        place-items: center;
        font-size: 11.5px;
        font-weight: 700;
    }

    .step.active {
        color: var(--pr-primary);
    }
    .step.active .num {
        background: var(--pr-primary);
        color: #fff;
    }

    .step.done {
        color: var(--pr-success);
    }
    .step.done .num {
        background: var(--pr-success);
        color: #fff;
    }

    /* Preview Grids & Info Boxes */
    .preview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 840px) {
        .preview-grid { grid-template-columns: 1fr; }
    }

    .info-box {
        background: rgba(15, 23, 42, 0.015);
        border: 1px solid var(--pr-border);
        border-radius: 10px;
        padding: 16px;
    }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) .info-box {
        background: rgba(255, 255, 255, 0.02);
    }

    .info-box h4 {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--pr-muted);
        margin-bottom: 12px;
        font-weight: 800;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 12.5px;
        border-bottom: 1px dashed var(--pr-border);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .k {
        color: var(--pr-muted);
    }

    .info-row .v {
        font-weight: 600;
        color: var(--pr-text);
    }

    .summary-box {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(139, 92, 246, 0.08));
        border: 1px solid rgba(37, 99, 235, 0.2);
        border-radius: 10px;
        padding: 18px;
        margin-top: 18px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .summary-box .item .lbl {
        font-size: 11px;
        color: var(--pr-muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
    }

    .summary-box .item .val {
        font-size: 22px;
        font-weight: 800;
        color: var(--pr-primary);
        margin-top: 4px;
    }

    /* Payslip Document */
    .payslip-doc {
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        border-radius: 12px;
        padding: 32px;
        max-width: 860px;
        margin: 0 auto;
        box-shadow: var(--pr-shadow-lg);
    }

    .payslip-head {
        text-align: center;
        border-bottom: 2px solid var(--pr-primary);
        padding-bottom: 18px;
        margin-bottom: 22px;
    }

    .payslip-head .logo-badge {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        margin: 0 auto 10px;
        background: linear-gradient(135deg, #2563eb, #8b5cf6);
        display: grid;
        place-items: center;
        color: #fff;
        font-weight: 800;
        font-size: 22px;
    }

    .payslip-head h1 {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--pr-text);
        margin: 0;
    }

    .payslip-head p {
        font-size: 12px;
        color: var(--pr-muted);
        margin-top: 3px;
    }

    .payslip-head .pay-month {
        display: inline-block;
        background: var(--pr-primary);
        color: #fff;
        padding: 5px 16px;
        border-radius: 99px;
        font-size: 11.5px;
        font-weight: 700;
        margin-top: 12px;
        letter-spacing: .03em;
    }

    .payslip-doc table.ps {
        width: 100%;
        font-size: 12.5px;
        border-collapse: collapse;
        margin-bottom: 16px;
    }

    .payslip-doc table.ps th {
        background: rgba(15, 23, 42, 0.04);
        padding: 8px 12px;
        border: 1px solid var(--pr-border);
        font-size: 11.5px;
        font-weight: 700;
        color: var(--pr-text);
    }

    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) .payslip-doc table.ps th {
        background: rgba(255, 255, 255, 0.04);
    }

    .payslip-doc table.ps td {
        padding: 8px 12px;
        border: 1px solid var(--pr-border);
        color: var(--pr-text);
    }

    .final-strip {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(139, 92, 246, 0.08));
        border: 1px solid rgba(37, 99, 235, 0.2);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .final-strip .box .lbl {
        font-size: 11px;
        color: var(--pr-muted);
        text-transform: uppercase;
        font-weight: 700;
    }

    .final-strip .box .val {
        font-size: 22px;
        font-weight: 800;
        color: var(--pr-primary);
        margin-top: 4px;
    }

    .footer-note {
        text-align: center;
        font-size: 11px;
        color: var(--pr-muted);
        margin-top: 20px;
        padding-top: 14px;
        border-top: 1px dashed var(--pr-border);
    }
</style>
