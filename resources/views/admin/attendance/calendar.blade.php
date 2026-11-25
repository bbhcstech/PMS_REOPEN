{{-- resources/views/admin/attendance/calendar.blade.php --}}
@php
    $authUser = Auth::user();
    $canManage = $canManage ?? (Auth::check() && in_array(strtolower((string)($authUser->role ?? '')), ['admin', 'superadmin', 'administrator', 'hr', 'manager'], true));
    $currentSelectedUserId = $selectedUserId ?? ($users->first()?->id ?? $authUser->id);
    $selectedEmployee = ($currentSelectedUserId === 'all')
        ? ($users->first() ?? $authUser)
        : ($users->firstWhere('id', $currentSelectedUserId) ?? $users->first() ?? $authUser);
    $todayStr = now()->format('Y-m-d');
@endphp

<style>
/* ==========================================================================
   ATTENDANCE CALENDAR VIEW - LIGHT & DARK THEME DESIGN SYSTEM
   ========================================================================== */

.cal-view-wrapper {
    margin-bottom: 2rem;
    animation: fadeInCal 0.35s ease;
}

@keyframes fadeInCal {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --------------------------------------------------------------------------
   1. CONTROLS CARD
   -------------------------------------------------------------------------- */
.cal-controls-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.35rem 1.75rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    transition: all 0.25s ease;
}

.cal-controls-card:hover {
    border-color: rgba(47, 107, 255, 0.25);
    box-shadow: 0 8px 30px rgba(47, 107, 255, 0.06);
}

.cal-emp-picker-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1 1 360px;
    min-width: 280px;
    position: relative;
    z-index: 5;
}

.cal-emp-label {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    -webkit-text-fill-color: #0f172a;
    display: flex;
    align-items: center;
    white-space: nowrap;
    margin: 0;
}

.cal-emp-select-wrap {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.cal-emp-select {
    width: 100%;
    min-height: 44px;
    padding: 0.65rem 1rem 0.65rem 2.6rem;
    border-radius: 14px;
    border: 1.5px solid #cbd5e1;
    font-weight: 600;
    font-size: 0.94rem;
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    background: #ffffff !important;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cal-emp-select:focus {
    border-color: #2F6BFF !important;
    background: #ffffff !important;
    box-shadow: 0 0 0 3.5px rgba(47, 107, 255, 0.18);
    outline: none;
}

.cal-emp-select option {
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    background: #ffffff !important;
    padding: 8px 12px;
}

.cal-emp-icon {
    position: absolute;
    left: 13px;
    color: #2F6BFF;
    font-size: 1.15rem;
    pointer-events: none;
    z-index: 2;
}

.cal-nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    border: 1.5px solid #cbd5e1;
    background: #f8fafc;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.cal-nav-btn:hover {
    background: #2F6BFF;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    border-color: #2F6BFF;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.28);
}

.cal-nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Month / Year Navigator */
.cal-date-nav-wrap {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.cal-month-select,
.cal-year-select {
    min-height: 40px;
    padding: 0.55rem 0.95rem;
    border-radius: 12px;
    border: 1.5px solid #cbd5e1;
    font-weight: 600;
    font-size: 0.92rem;
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    background: #f8fafc !important;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cal-month-select:focus,
.cal-year-select:focus {
    border-color: #2F6BFF !important;
    outline: none;
    background: #ffffff !important;
    box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.15);
}

.cal-month-select option,
.cal-year-select option {
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    background: #ffffff !important;
}

.cal-today-btn {
    min-height: 40px;
    padding: 0.55rem 1.15rem;
    border-radius: 12px;
    border: 1.5px solid rgba(47, 107, 255, 0.25);
    background: rgba(47, 107, 255, 0.08);
    color: #2F6BFF !important;
    -webkit-text-fill-color: #2F6BFF !important;
    font-weight: 700;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cal-today-btn:hover {
    background: #2F6BFF;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    border-color: #2F6BFF;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.25);
}

.cal-quick-mark-btn {
    min-height: 40px;
    padding: 0.55rem 1.3rem;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #2F6BFF, #1E4FCC);
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-weight: 700;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.32);
    transition: all 0.25s ease;
}

.cal-quick-mark-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(47, 107, 255, 0.42);
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

/* --------------------------------------------------------------------------
   2. SPOTLIGHT EMPLOYEE CARD & KPI CHIPS
   -------------------------------------------------------------------------- */
.cal-spotlight-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.cal-spotlight-card:hover {
    border-color: rgba(47, 107, 255, 0.2);
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.06);
}

.cal-spotlight-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    min-width: 280px;
}

.cal-spotlight-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

.cal-spotlight-avatar {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ffffff;
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.18);
    background: #f1f5f9;
}

.cal-spotlight-status-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #10B981;
    border: 2.5px solid #ffffff;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
}

.cal-spotlight-info h4 {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0f172a;
    -webkit-text-fill-color: #0f172a;
    margin: 0 0 5px 0;
    letter-spacing: -0.01em;
}

.cal-spotlight-badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-bottom: 5px;
}

.cal-badge-pill {
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.22rem 0.7rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.cal-badge-designation {
    background: #eef2ff;
    color: #2F6BFF !important;
    -webkit-text-fill-color: #2F6BFF !important;
    border: 1px solid rgba(47, 107, 255, 0.25);
}

.cal-badge-department {
    background: #f5f3ff;
    color: #8B5CF6 !important;
    -webkit-text-fill-color: #8B5CF6 !important;
    border: 1px solid rgba(139, 92, 246, 0.25);
}

.cal-spotlight-meta {
    font-size: 0.84rem;
    font-weight: 600;
    color: #475569;
    -webkit-text-fill-color: #475569;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 3px;
}

.cal-spotlight-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.cal-spotlight-meta i {
    color: #2F6BFF;
}

/* KPI Chips */
.cal-kpi-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(80px, 1fr));
    gap: 0.65rem;
    flex: 1 1 520px;
}

.cal-kpi-chip {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 0.7rem 0.5rem;
    text-align: center;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.cal-kpi-chip:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

.cal-kpi-chip.kpi-present { border-color: #86efac; background: #ecfdf5; }
.cal-kpi-chip.kpi-absent { border-color: #fca5a5; background: #fef2f2; }
.cal-kpi-chip.kpi-late { border-color: #fcd34d; background: #fffbeb; }
.cal-kpi-chip.kpi-halfday { border-color: #c4b5fd; background: #f5f3ff; }
.cal-kpi-chip.kpi-leave { border-color: #f9a8d4; background: #fdf2f8; }
.cal-kpi-chip.kpi-hours { border-color: #93c5fd; background: #eff6ff; }
.cal-kpi-chip.kpi-rate { border-color: #67e8f9; background: #f0fdfa; }

.cal-kpi-value {
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 3px;
}

.kpi-present .cal-kpi-value { color: #059669; }
.kpi-absent .cal-kpi-value { color: #dc2626; }
.kpi-late .cal-kpi-value { color: #d97706; }
.kpi-halfday .cal-kpi-value { color: #7c3aed; }
.kpi-leave .cal-kpi-value { color: #db2777; }
.kpi-hours .cal-kpi-value { color: #2563eb; }
.kpi-rate .cal-kpi-value { color: #0d9488; }

.cal-kpi-label {
    font-size: 0.74rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* --------------------------------------------------------------------------
   3. CALENDAR BOARD GRID
   -------------------------------------------------------------------------- */
.cal-board-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.cal-weekdays-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 8px;
}

.cal-weekday-col {
    padding: 0.75rem 0.5rem;
    text-align: center;
    font-size: 0.88rem;
    font-weight: 800;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: #f1f5f9;
    border-radius: 12px;
}

.cal-weekday-col.is-weekend {
    color: #4338ca;
    background: #eef2ff;
}

.cal-days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}

/* Individual Calendar Day Cell */
.cal-day-cell {
    min-height: 126px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 0.65rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    transition: all 0.22s ease;
    cursor: pointer;
    overflow: hidden;
}

.cal-day-cell:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(47, 107, 255, 0.12);
    border-color: #2F6BFF !important;
    z-index: 5;
}

.cal-day-cell.is-other-month {
    background: #f8fafc;
    opacity: 0.4;
    cursor: default;
    border-style: dashed;
}

.cal-day-cell.is-other-month:hover {
    transform: none;
    box-shadow: none;
    border-color: #e2e8f0 !important;
}

.cal-day-cell.is-today {
    border: 2px solid #2F6BFF !important;
    background: #f0f7ff;
    box-shadow: 0 4px 16px rgba(47, 107, 255, 0.16);
}

.cal-day-cell.is-weekend-cell {
    background: #fafbff;
}

/* Cell Header */
.cal-day-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.cal-day-num {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    -webkit-text-fill-color: #0f172a;
    line-height: 1;
}

.is-today .cal-day-num {
    color: #2F6BFF;
    -webkit-text-fill-color: #2F6BFF;
}

.cal-today-pill {
    font-size: 0.66rem;
    font-weight: 800;
    text-transform: uppercase;
    background: #2F6BFF;
    color: #ffffff;
    padding: 0.15rem 0.5rem;
    border-radius: 20px;
    letter-spacing: 0.04em;
}

.cal-weekend-pill {
    font-size: 0.66rem;
    font-weight: 700;
    color: #475569;
    background: #e2e8f0;
    padding: 0.15rem 0.5rem;
    border-radius: 20px;
}

/* Status Cards Inside Day Cells */
.cal-status-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
}

.cal-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.32rem 0.55rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1.2;
    width: 100%;
    box-sizing: border-box;
}

/* Status Variants (Light Theme) */
.cal-status-pill.status-present {
    background: #dcfce7;
    color: #15803d !important;
    -webkit-text-fill-color: #15803d !important;
    border: 1px solid #86efac;
}
.cal-day-cell.has-present { border-color: rgba(16, 185, 129, 0.4); }

.cal-status-pill.status-late {
    background: #fef3c7;
    color: #b45309 !important;
    -webkit-text-fill-color: #b45309 !important;
    border: 1px solid #fcd34d;
}
.cal-day-cell.has-late { border-color: rgba(245, 158, 11, 0.4); }

.cal-status-pill.status-halfday {
    background: #ede9fe;
    color: #6d28d9 !important;
    -webkit-text-fill-color: #6d28d9 !important;
    border: 1px solid #c4b5fd;
}
.cal-day-cell.has-halfday { border-color: rgba(139, 92, 246, 0.4); }

.cal-status-pill.status-absent {
    background: #fee2e2;
    color: #b91c1c !important;
    -webkit-text-fill-color: #b91c1c !important;
    border: 1px solid #fca5a5;
}
.cal-day-cell.has-absent { border-color: rgba(239, 68, 68, 0.35); }

.cal-status-pill.status-holiday {
    background: #dbeafe;
    color: #1d4ed8 !important;
    -webkit-text-fill-color: #1d4ed8 !important;
    border: 1px solid #93c5fd;
}
.cal-day-cell.has-holiday { border-color: rgba(59, 130, 246, 0.4); }

.cal-status-pill.status-leave {
    background: #fce7f3;
    color: #be185d !important;
    -webkit-text-fill-color: #be185d !important;
    border: 1px solid #f9a8d4;
}
.cal-day-cell.has-leave { border-color: rgba(236, 72, 153, 0.4); }

.cal-status-pill.status-wfh {
    background: #cffafe;
    color: #0e7490 !important;
    -webkit-text-fill-color: #0e7490 !important;
    border: 1px solid #67e8f9;
}

.cal-status-pill.status-dayoff {
    background: #f1f5f9;
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    border: 1px solid #cbd5e1;
}

.cal-status-pill.status-upcoming {
    background: #f8fafc;
    color: #94a3b8 !important;
    -webkit-text-fill-color: #94a3b8 !important;
    border: 1px solid #e2e8f0;
}

/* Timing & Hours in Cells */
.cal-timings {
    font-size: 0.73rem;
    font-weight: 600;
    color: #475569;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 8px;
    margin-top: 3px;
}

.cal-timing-in {
    color: #047857;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.cal-timing-out {
    color: #b91c1c;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.cal-hours-tag {
    font-size: 0.72rem;
    font-weight: 800;
    color: #1d4ed8;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 1px 6px;
    display: inline-flex;
    align-items: center;
    gap: 2px;
}

/* Hover Action Button Overlay */
.cal-cell-actions {
    position: absolute;
    top: 6px;
    right: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0;
    transform: translateY(-4px);
    transition: all 0.2s ease;
    pointer-events: none;
    z-index: 8;
}

.cal-day-cell:hover .cal-cell-actions {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}

.cal-action-btn {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}

.cal-action-btn.btn-view {
    background: #2F6BFF;
    color: #ffffff;
}

.cal-action-btn.btn-edit {
    background: #10B981;
    color: #ffffff;
}

.cal-action-btn:hover {
    transform: scale(1.12);
}

/* Calendar Legend */
.cal-legend-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e2e8f0;
}

.cal-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.84rem;
    font-weight: 600;
    color: #475569;
}

.cal-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.cal-legend-dot.present { background: #10B981; }
.cal-legend-dot.late { background: #F59E0B; }
.cal-legend-dot.halfday { background: #8B5CF6; }
.cal-legend-dot.absent { background: #EF4444; }
.cal-legend-dot.holiday { background: #3B82F6; }
.cal-legend-dot.leave { background: #EC4899; }
.cal-legend-dot.wfh { background: #06B6D4; }
.cal-legend-dot.dayoff { background: #64748B; }

/* Responsive adjustments */
@media (max-width: 992px) {
    .cal-kpi-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    .cal-day-cell {
        min-height: 110px;
        padding: 0.5rem;
    }
}

@media (max-width: 768px) {
    .cal-board-card {
        padding: 0.75rem;
        overflow-x: auto;
    }
    .cal-weekdays-header,
    .cal-days-grid {
        min-width: 680px;
    }
    .cal-controls-card {
        flex-direction: column;
        align-items: stretch;
    }
    .cal-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* ==========================================================================
   DARK MODE COMPREHENSIVE OVERRIDES
   ========================================================================== */
html[data-pms-theme="dark"] .cal-controls-card,
html[data-theme="dark"] .cal-controls-card,
html[data-bs-theme="dark"] .cal-controls-card,
[data-pms-theme="dark"] .cal-controls-card,
[data-theme="dark"] .cal-controls-card,
[data-bs-theme="dark"] .cal-controls-card,
body.dark-mode .cal-controls-card,
.dark-mode .cal-controls-card,
html[data-pms-theme="dark"] .cal-spotlight-card,
html[data-theme="dark"] .cal-spotlight-card,
html[data-bs-theme="dark"] .cal-spotlight-card,
[data-pms-theme="dark"] .cal-spotlight-card,
[data-theme="dark"] .cal-spotlight-card,
[data-bs-theme="dark"] .cal-spotlight-card,
body.dark-mode .cal-spotlight-card,
.dark-mode .cal-spotlight-card,
html[data-pms-theme="dark"] .cal-board-card,
html[data-theme="dark"] .cal-board-card,
html[data-bs-theme="dark"] .cal-board-card,
[data-pms-theme="dark"] .cal-board-card,
[data-theme="dark"] .cal-board-card,
[data-bs-theme="dark"] .cal-board-card,
body.dark-mode .cal-board-card,
.dark-mode .cal-board-card {
    background: #0F1530 !important;
    border-color: rgba(238, 241, 251, 0.12) !important;
    color: #CBD5E1 !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
}

html[data-pms-theme="dark"] .cal-emp-label,
html[data-theme="dark"] .cal-emp-label,
html[data-bs-theme="dark"] .cal-emp-label,
[data-pms-theme="dark"] .cal-emp-label,
[data-theme="dark"] .cal-emp-label,
[data-bs-theme="dark"] .cal-emp-label,
body.dark-mode .cal-emp-label,
.dark-mode .cal-emp-label {
    color: #F8FAFC !important;
    -webkit-text-fill-color: #F8FAFC !important;
}

html[data-pms-theme="dark"] .cal-emp-select,
html[data-theme="dark"] .cal-emp-select,
html[data-bs-theme="dark"] .cal-emp-select,
[data-pms-theme="dark"] .cal-emp-select,
[data-theme="dark"] .cal-emp-select,
[data-bs-theme="dark"] .cal-emp-select,
body.dark-mode .cal-emp-select,
.dark-mode .cal-emp-select,
html[data-pms-theme="dark"] .cal-month-select,
html[data-theme="dark"] .cal-month-select,
html[data-bs-theme="dark"] .cal-month-select,
body.dark-mode .cal-month-select,
html[data-pms-theme="dark"] .cal-year-select,
html[data-theme="dark"] .cal-year-select,
html[data-bs-theme="dark"] .cal-year-select,
body.dark-mode .cal-year-select {
    background: #141B3D !important;
    border-color: rgba(238, 241, 251, 0.18) !important;
    color: #EEF1FB !important;
    -webkit-text-fill-color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .cal-emp-select option,
html[data-theme="dark"] .cal-emp-select option,
html[data-bs-theme="dark"] .cal-emp-select option,
[data-pms-theme="dark"] .cal-emp-select option,
[data-theme="dark"] .cal-emp-select option,
[data-bs-theme="dark"] .cal-emp-select option,
body.dark-mode .cal-emp-select option,
.dark-mode .cal-emp-select option,
html[data-pms-theme="dark"] .cal-month-select option,
html[data-theme="dark"] .cal-month-select option,
body.dark-mode .cal-month-select option,
html[data-pms-theme="dark"] .cal-year-select option,
html[data-theme="dark"] .cal-year-select option,
body.dark-mode .cal-year-select option {
    background: #141B3D !important;
    color: #EEF1FB !important;
    -webkit-text-fill-color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .cal-nav-btn,
html[data-theme="dark"] .cal-nav-btn,
html[data-bs-theme="dark"] .cal-nav-btn,
body.dark-mode .cal-nav-btn {
    background: #141B3D !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
    color: #CBD5E1 !important;
}

html[data-pms-theme="dark"] .cal-today-btn,
html[data-theme="dark"] .cal-today-btn,
html[data-bs-theme="dark"] .cal-today-btn,
body.dark-mode .cal-today-btn {
    background: rgba(47, 107, 255, 0.22) !important;
    border-color: rgba(47, 107, 255, 0.45) !important;
    color: #93C5FD !important;
    -webkit-text-fill-color: #93C5FD !important;
}

html[data-pms-theme="dark"] .cal-spotlight-info h4,
html[data-theme="dark"] .cal-spotlight-info h4,
html[data-bs-theme="dark"] .cal-spotlight-info h4,
body.dark-mode .cal-spotlight-info h4 {
    color: #F8FAFC !important;
    -webkit-text-fill-color: #F8FAFC !important;
}

html[data-pms-theme="dark"] .cal-badge-designation,
html[data-theme="dark"] .cal-badge-designation,
body.dark-mode .cal-badge-designation {
    background: rgba(47, 107, 255, 0.22) !important;
    color: #93C5FD !important;
    -webkit-text-fill-color: #93C5FD !important;
    border-color: rgba(47, 107, 255, 0.4) !important;
}

html[data-pms-theme="dark"] .cal-badge-department,
html[data-theme="dark"] .cal-badge-department,
body.dark-mode .cal-badge-department {
    background: rgba(139, 92, 246, 0.22) !important;
    color: #C4B5FD !important;
    -webkit-text-fill-color: #C4B5FD !important;
    border-color: rgba(139, 92, 246, 0.4) !important;
}

html[data-pms-theme="dark"] .cal-spotlight-meta,
html[data-theme="dark"] .cal-spotlight-meta,
body.dark-mode .cal-spotlight-meta {
    color: #94A3B8 !important;
    -webkit-text-fill-color: #94A3B8 !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip,
html[data-theme="dark"] .cal-kpi-chip,
body.dark-mode .cal-kpi-chip {
    background: #141A38 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-present,
html[data-theme="dark"] .cal-kpi-chip.kpi-present,
body.dark-mode .cal-kpi-chip.kpi-present {
    border-color: rgba(16, 185, 129, 0.35) !important;
    background: rgba(16, 185, 129, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-present .cal-kpi-value,
html[data-theme="dark"] .kpi-present .cal-kpi-value,
body.dark-mode .kpi-present .cal-kpi-value {
    color: #34D399 !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-absent,
html[data-theme="dark"] .cal-kpi-chip.kpi-absent,
body.dark-mode .cal-kpi-chip.kpi-absent {
    border-color: rgba(239, 68, 68, 0.35) !important;
    background: rgba(239, 68, 68, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-absent .cal-kpi-value,
html[data-theme="dark"] .kpi-absent .cal-kpi-value,
body.dark-mode .kpi-absent .cal-kpi-value {
    color: #F87171 !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-late,
html[data-theme="dark"] .cal-kpi-chip.kpi-late,
body.dark-mode .cal-kpi-chip.kpi-late {
    border-color: rgba(245, 158, 11, 0.35) !important;
    background: rgba(245, 158, 11, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-late .cal-kpi-value,
html[data-theme="dark"] .kpi-late .cal-kpi-value,
body.dark-mode .kpi-late .cal-kpi-value {
    color: #FBBF24 !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-halfday,
html[data-theme="dark"] .cal-kpi-chip.kpi-halfday,
body.dark-mode .cal-kpi-chip.kpi-halfday {
    border-color: rgba(139, 92, 246, 0.35) !important;
    background: rgba(139, 92, 246, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-halfday .cal-kpi-value,
html[data-theme="dark"] .kpi-halfday .cal-kpi-value,
body.dark-mode .kpi-halfday .cal-kpi-value {
    color: #A78BFA !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-leave,
html[data-theme="dark"] .cal-kpi-chip.kpi-leave,
body.dark-mode .cal-kpi-chip.kpi-leave {
    border-color: rgba(236, 72, 153, 0.35) !important;
    background: rgba(236, 72, 153, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-leave .cal-kpi-value,
html[data-theme="dark"] .kpi-leave .cal-kpi-value,
body.dark-mode .kpi-leave .cal-kpi-value {
    color: #F472B6 !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-hours,
html[data-theme="dark"] .cal-kpi-chip.kpi-hours,
body.dark-mode .cal-kpi-chip.kpi-hours {
    border-color: rgba(59, 130, 246, 0.35) !important;
    background: rgba(59, 130, 246, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-hours .cal-kpi-value,
html[data-theme="dark"] .kpi-hours .cal-kpi-value,
body.dark-mode .kpi-hours .cal-kpi-value {
    color: #60A5FA !important;
}

html[data-pms-theme="dark"] .cal-kpi-chip.kpi-rate,
html[data-theme="dark"] .cal-kpi-chip.kpi-rate,
body.dark-mode .cal-kpi-chip.kpi-rate {
    border-color: rgba(20, 184, 166, 0.35) !important;
    background: rgba(20, 184, 166, 0.15) !important;
}
html[data-pms-theme="dark"] .kpi-rate .cal-kpi-value,
html[data-theme="dark"] .kpi-rate .cal-kpi-value,
body.dark-mode .kpi-rate .cal-kpi-value {
    color: #2DD4BF !important;
}

html[data-pms-theme="dark"] .cal-kpi-label,
html[data-theme="dark"] .cal-kpi-label,
body.dark-mode .cal-kpi-label {
    color: #94A3B8 !important;
}

html[data-pms-theme="dark"] .cal-weekday-col,
html[data-theme="dark"] .cal-weekday-col,
body.dark-mode .cal-weekday-col {
    background: #141B3D !important;
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
}

html[data-pms-theme="dark"] .cal-weekday-col.is-weekend,
html[data-theme="dark"] .cal-weekday-col.is-weekend,
body.dark-mode .cal-weekday-col.is-weekend {
    background: rgba(99, 102, 241, 0.2) !important;
    color: #A5B4FC !important;
    -webkit-text-fill-color: #A5B4FC !important;
}

html[data-pms-theme="dark"] .cal-day-cell,
html[data-theme="dark"] .cal-day-cell,
body.dark-mode .cal-day-cell {
    background: #101633 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

html[data-pms-theme="dark"] .cal-day-cell:hover,
html[data-theme="dark"] .cal-day-cell:hover,
body.dark-mode .cal-day-cell:hover {
    border-color: #3b82f6 !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6) !important;
}

html[data-pms-theme="dark"] .cal-day-cell.is-other-month,
html[data-theme="dark"] .cal-day-cell.is-other-month,
body.dark-mode .cal-day-cell.is-other-month {
    background: #070B1A !important;
    opacity: 0.28 !important;
    border-color: rgba(255, 255, 255, 0.04) !important;
}

html[data-pms-theme="dark"] .cal-day-cell.is-weekend-cell,
html[data-theme="dark"] .cal-day-cell.is-weekend-cell,
body.dark-mode .cal-day-cell.is-weekend-cell {
    background: #0B1028 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

html[data-pms-theme="dark"] .cal-day-cell.is-today,
html[data-theme="dark"] .cal-day-cell.is-today,
body.dark-mode .cal-day-cell.is-today {
    border: 2px solid #3b82f6 !important;
    background: rgba(59, 130, 246, 0.15) !important;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3) !important;
}

html[data-pms-theme="dark"] .cal-day-num,
html[data-theme="dark"] .cal-day-num,
body.dark-mode .cal-day-num {
    color: #F8FAFC !important;
    -webkit-text-fill-color: #F8FAFC !important;
}

html[data-pms-theme="dark"] .is-today .cal-day-num,
html[data-theme="dark"] .is-today .cal-day-num,
body.dark-mode .is-today .cal-day-num {
    color: #60A5FA !important;
    -webkit-text-fill-color: #60A5FA !important;
}

html[data-pms-theme="dark"] .cal-weekend-pill,
html[data-theme="dark"] .cal-weekend-pill,
body.dark-mode .cal-weekend-pill {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
}

/* Status Badges in Dark Mode */
html[data-pms-theme="dark"] .cal-status-pill.status-present,
html[data-theme="dark"] .cal-status-pill.status-present,
body.dark-mode .cal-status-pill.status-present {
    background: rgba(16, 185, 129, 0.22) !important;
    color: #34D399 !important;
    -webkit-text-fill-color: #34D399 !important;
    border-color: rgba(16, 185, 129, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-late,
html[data-theme="dark"] .cal-status-pill.status-late,
body.dark-mode .cal-status-pill.status-late {
    background: rgba(245, 158, 11, 0.22) !important;
    color: #FBBF24 !important;
    -webkit-text-fill-color: #FBBF24 !important;
    border-color: rgba(245, 158, 11, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-halfday,
html[data-theme="dark"] .cal-status-pill.status-halfday,
body.dark-mode .cal-status-pill.status-halfday {
    background: rgba(139, 92, 246, 0.22) !important;
    color: #A78BFA !important;
    -webkit-text-fill-color: #A78BFA !important;
    border-color: rgba(139, 92, 246, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-absent,
html[data-theme="dark"] .cal-status-pill.status-absent,
body.dark-mode .cal-status-pill.status-absent {
    background: rgba(239, 68, 68, 0.22) !important;
    color: #F87171 !important;
    -webkit-text-fill-color: #F87171 !important;
    border-color: rgba(239, 68, 68, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-holiday,
html[data-theme="dark"] .cal-status-pill.status-holiday,
body.dark-mode .cal-status-pill.status-holiday {
    background: rgba(59, 130, 246, 0.22) !important;
    color: #60A5FA !important;
    -webkit-text-fill-color: #60A5FA !important;
    border-color: rgba(59, 130, 246, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-leave,
html[data-theme="dark"] .cal-status-pill.status-leave,
body.dark-mode .cal-status-pill.status-leave {
    background: rgba(236, 72, 153, 0.22) !important;
    color: #F472B6 !important;
    -webkit-text-fill-color: #F472B6 !important;
    border-color: rgba(236, 72, 153, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-wfh,
html[data-theme="dark"] .cal-status-pill.status-wfh,
body.dark-mode .cal-status-pill.status-wfh {
    background: rgba(6, 182, 212, 0.22) !important;
    color: #38BDF8 !important;
    -webkit-text-fill-color: #38BDF8 !important;
    border-color: rgba(6, 182, 212, 0.45) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-dayoff,
html[data-theme="dark"] .cal-status-pill.status-dayoff,
body.dark-mode .cal-status-pill.status-dayoff {
    background: rgba(148, 163, 184, 0.16) !important;
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
    border-color: rgba(148, 163, 184, 0.3) !important;
}

html[data-pms-theme="dark"] .cal-status-pill.status-upcoming,
html[data-theme="dark"] .cal-status-pill.status-upcoming,
body.dark-mode .cal-status-pill.status-upcoming {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #64748B !important;
    -webkit-text-fill-color: #64748B !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

html[data-pms-theme="dark"] .cal-timings,
html[data-theme="dark"] .cal-timings,
body.dark-mode .cal-timings {
    color: #94A3B8 !important;
}

html[data-pms-theme="dark"] .cal-timing-in,
html[data-theme="dark"] .cal-timing-in,
body.dark-mode .cal-timing-in {
    color: #34D399 !important;
}

html[data-pms-theme="dark"] .cal-timing-out,
html[data-theme="dark"] .cal-timing-out,
body.dark-mode .cal-timing-out {
    color: #F87171 !important;
}

html[data-pms-theme="dark"] .cal-hours-tag,
html[data-theme="dark"] .cal-hours-tag,
body.dark-mode .cal-hours-tag {
    color: #93C5FD !important;
    background: rgba(59, 130, 246, 0.22) !important;
    border-color: rgba(59, 130, 246, 0.4) !important;
}

html[data-pms-theme="dark"] .cal-legend-bar,
html[data-theme="dark"] .cal-legend-bar,
body.dark-mode .cal-legend-bar {
    border-color: rgba(255, 255, 255, 0.1) !important;
}

html[data-pms-theme="dark"] .cal-legend-item,
html[data-theme="dark"] .cal-legend-item,
body.dark-mode .cal-legend-item {
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
}

/* Attendance Details Modal Styles (Light & Dark Theme) */
.attendance-details-modal-content {
    border-radius: 20px !important;
    overflow: hidden !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15) !important;
}
html[data-pms-theme="dark"] .attendance-details-modal-content,
html[data-theme="dark"] .attendance-details-modal-content,
html[data-bs-theme="dark"] .attendance-details-modal-content,
body.dark-mode .attendance-details-modal-content,
.dark-mode .attendance-details-modal-content {
    background: #0F1530 !important;
    border-color: rgba(238, 241, 251, 0.14) !important;
    color: #CBD5E1 !important;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6) !important;
}
html[data-pms-theme="dark"] .attendance-details-modal-content .modal-header,
html[data-theme="dark"] .attendance-details-modal-content .modal-header,
body.dark-mode .attendance-details-modal-content .modal-header {
    background: #141B3D !important;
    border-bottom: 1px solid rgba(238, 241, 251, 0.1) !important;
}
html[data-pms-theme="dark"] .attendance-details-modal-content .modal-footer,
html[data-theme="dark"] .attendance-details-modal-content .modal-footer,
body.dark-mode .attendance-details-modal-content .modal-footer {
    background: #141B3D !important;
    border-top: 1px solid rgba(238, 241, 251, 0.1) !important;
}
html[data-pms-theme="dark"] .attendance-details-modal-content .table,
html[data-theme="dark"] .attendance-details-modal-content .table,
body.dark-mode .attendance-details-modal-content .table {
    color: #CBD5E1 !important;
}
html[data-pms-theme="dark"] .attendance-details-modal-content .table th,
html[data-pms-theme="dark"] .attendance-details-modal-content .table td,
html[data-theme="dark"] .attendance-details-modal-content .table th,
html[data-theme="dark"] .attendance-details-modal-content .table td {
    background: transparent !important;
    color: #CBD5E1 !important;
    border-color: rgba(238, 241, 251, 0.08) !important;
}
html[data-pms-theme="dark"] .attendance-details-modal-content .form-control,
html[data-pms-theme="dark"] .attendance-details-modal-content .form-select,
html[data-theme="dark"] .attendance-details-modal-content .form-control,
html[data-theme="dark"] .attendance-details-modal-content .form-select,
body.dark-mode .attendance-details-modal-content .form-control,
body.dark-mode .attendance-details-modal-content .form-select {
    background: #141B3D !important;
    border-color: rgba(238, 241, 251, 0.18) !important;
    color: #EEF1FB !important;
}
</style>

<div class="cal-view-wrapper" id="pmsCalendarContainer">

    <!-- 1. CONTROLS BAR: Employee Dropdown & Month/Year Navigation -->
    <div class="cal-controls-card">
        <div class="cal-emp-picker-wrap">
            <label class="cal-emp-label" for="calEmployeeSelect">
                <i class="fas fa-user-circle text-primary me-2"></i>Employee:
            </label>
            <div class="cal-emp-select-wrap">
                <i class="fas fa-id-badge cal-emp-icon"></i>
                <select id="calEmployeeSelect" class="cal-emp-select" {{ !$canManage ? 'disabled' : '' }}>
                    @if($canManage)
                        <option value="all" {{ ($currentSelectedUserId === 'all') ? 'selected' : '' }}>
                            👥 All Employees (Grid View)
                        </option>
                    @endif
                    @foreach($users as $emp)
                        <option value="{{ $emp->id }}" {{ ($currentSelectedUserId != 'all' && $emp->id == $currentSelectedUserId) ? 'selected' : '' }}
                                data-avatar="{{ $emp->profile_image ? asset($emp->profile_image) : asset('images/default-avatar.png') }}"
                                data-designation="{{ $emp->employeeDetail?->designation?->name ?? ($emp->designation ?? 'Employee') }}"
                                data-department="{{ $emp->employeeDetail?->department?->dpt_name ?? 'N/A' }}"
                                data-empid="{{ $emp->employeeDetail?->employee_id ?? ('EMP-' . str_pad($emp->id, 4, '0', STR_PAD_LEFT)) }}"
                                data-email="{{ $emp->email }}">
                            {{ $emp->name }} ({{ $emp->employeeDetail?->designation?->name ?? ($emp->designation ?? 'Employee') }})
                        </option>
                    @endforeach
                </select>

                @if($canManage && $users->count() > 1)
                    <button type="button" class="cal-nav-btn" id="calPrevEmpBtn" title="Previous Employee">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="cal-nav-btn" id="calNextEmpBtn" title="Next Employee">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                @endif
            </div>
        </div>

        <div class="cal-date-nav-wrap">
            <button type="button" class="cal-nav-btn" id="calPrevMonthBtn" title="Previous Month">
                <i class="fas fa-chevron-left"></i>
            </button>

            <select id="calMonthSelect" class="cal-month-select">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                    </option>
                @endfor
            </select>

            <select id="calYearSelect" class="cal-year-select">
                @for($y = date('Y') - 3; $y <= date('Y') + 2; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>

            <button type="button" class="cal-nav-btn" id="calNextMonthBtn" title="Next Month">
                <i class="fas fa-chevron-right"></i>
            </button>

            <button type="button" class="cal-today-btn" id="calTodayBtn">
                <i class="fas fa-calendar-day"></i> Today
            </button>

            @if($canManage)
                <button type="button" class="cal-quick-mark-btn" id="calQuickMarkBtn">
                    <i class="fas fa-plus-circle"></i> Mark Attendance
                </button>
            @endif
        </div>
    </div>

    <!-- 2. SELECTED EMPLOYEE SPOTLIGHT & MONTHLY KPI CHIPS -->
    <div class="cal-spotlight-card" id="calSpotlightCard">
        <div class="cal-spotlight-left">
            <div class="cal-spotlight-avatar-wrap">
                <img src="{{ $selectedEmployee->profile_image ? asset($selectedEmployee->profile_image) : asset('images/default-avatar.png') }}"
                     alt="{{ $selectedEmployee->name }}"
                     id="calSpotlightAvatar"
                     class="cal-spotlight-avatar"
                     onerror="this.onerror=null; this.src='{{ asset('admin/assets/img/avatars/1.png') }}';">
                <div class="cal-spotlight-status-dot"></div>
            </div>
            <div class="cal-spotlight-info">
                <h4 id="calSpotlightName">{{ $selectedEmployee->name }}</h4>
                <div class="cal-spotlight-badges">
                    <span class="cal-badge-pill cal-badge-designation" id="calSpotlightDesignation">
                        <i class="fas fa-briefcase"></i> {{ $selectedEmployee->employeeDetail?->designation?->name ?? ($selectedEmployee->designation ?? 'Employee') }}
                    </span>
                    <span class="cal-badge-pill cal-badge-department" id="calSpotlightDepartment">
                        <i class="fas fa-building"></i> {{ $selectedEmployee->employeeDetail?->department?->dpt_name ?? 'N/A' }}
                    </span>
                </div>
                <div class="cal-spotlight-meta">
                    <span id="calSpotlightEmpId"><i class="fas fa-id-badge"></i> {{ $selectedEmployee->employeeDetail?->employee_id ?? ('EMP-' . str_pad($selectedEmployee->id, 4, '0', STR_PAD_LEFT)) }}</span>
                    <span id="calSpotlightEmail"><i class="fas fa-envelope"></i> {{ $selectedEmployee->email }}</span>
                </div>
            </div>
        </div>

        <div class="cal-kpi-grid" id="calKpiGrid">
            <div class="cal-kpi-chip kpi-present">
                <div class="cal-kpi-value" id="kpiPresentCount">0</div>
                <div class="cal-kpi-label">Present</div>
            </div>
            <div class="cal-kpi-chip kpi-absent">
                <div class="cal-kpi-value" id="kpiAbsentCount">0</div>
                <div class="cal-kpi-label">Absent</div>
            </div>
            <div class="cal-kpi-chip kpi-late">
                <div class="cal-kpi-value" id="kpiLateCount">0</div>
                <div class="cal-kpi-label">Late</div>
            </div>
            <div class="cal-kpi-chip kpi-halfday">
                <div class="cal-kpi-value" id="kpiHalfDayCount">0</div>
                <div class="cal-kpi-label">Half Day</div>
            </div>
            <div class="cal-kpi-chip kpi-leave">
                <div class="cal-kpi-value" id="kpiLeaveCount">0</div>
                <div class="cal-kpi-label">Holiday / Leave</div>
            </div>
            <div class="cal-kpi-chip kpi-hours">
                <div class="cal-kpi-value" id="kpiTotalHours">0h</div>
                <div class="cal-kpi-label">Total Hours</div>
            </div>
            <div class="cal-kpi-chip kpi-rate">
                <div class="cal-kpi-value" id="kpiAttendanceRate">0%</div>
                <div class="cal-kpi-label">Attendance</div>
            </div>
        </div>
    </div>

    <!-- 3. CALENDAR BOARD GRID -->
    <div class="cal-board-card">
        <!-- Weekdays Header -->
        <div class="cal-weekdays-header">
            <div class="cal-weekday-col is-weekend">Sun</div>
            <div class="cal-weekday-col">Mon</div>
            <div class="cal-weekday-col">Tue</div>
            <div class="cal-weekday-col">Wed</div>
            <div class="cal-weekday-col">Thu</div>
            <div class="cal-weekday-col">Fri</div>
            <div class="cal-weekday-col is-weekend">Sat</div>
        </div>

        <!-- 35/42 Days Grid (Rendered by JS engine) -->
        <div class="cal-days-grid" id="calDaysGrid">
            <!-- Populated via JavaScript dynamically -->
        </div>

        <!-- Status Legend -->
        <div class="cal-legend-bar">
            <span class="cal-legend-item"><span class="cal-legend-dot present"></span> Present</span>
            <span class="cal-legend-item"><span class="cal-legend-dot late"></span> Late</span>
            <span class="cal-legend-item"><span class="cal-legend-dot halfday"></span> Half Day</span>
            <span class="cal-legend-item"><span class="cal-legend-dot absent"></span> Absent</span>
            <span class="cal-legend-item"><span class="cal-legend-dot holiday"></span> Holiday</span>
            <span class="cal-legend-item"><span class="cal-legend-dot leave"></span> Leave</span>
            <span class="cal-legend-item"><span class="cal-legend-dot wfh"></span> Saturday (WFH)</span>
            <span class="cal-legend-item"><span class="cal-legend-dot dayoff"></span> Sunday / Day Off</span>
        </div>
    </div>

</div>

{{-- Attendance Details / Edit Modal (included if not already defined) --}}
@if(!isset($hasAttendanceModalDefined))
    @php $hasAttendanceModalDefined = true; @endphp
    <div class="modal fade attendance-details-modal" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content attendance-details-modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Attendance Details</h5>
            <button type="button" class="btn-close btn-close-white" data-attendance-modal-close data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="attendanceDetailsBody" class="modal-body attendance-details-modal-body">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">Loading attendance data...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-attendance-modal-close data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
@endif

{{-- Pass Data into Client-Side Engine for Instant 0ms Employee Switching --}}
<script>
window.PMS_CAL_DATA = {
    users: {!! json_encode($users->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'avatar' => $u->profile_image ? asset($u->profile_image) : asset('images/default-avatar.png'),
            'designation' => $u->employeeDetail?->designation?->name ?? ($u->designation ?? 'Employee'),
            'department' => $u->employeeDetail?->department?->dpt_name ?? 'N/A',
            'employee_id' => $u->employeeDetail?->employee_id ?? ('EMP-' . str_pad($u->id, 4, '0', STR_PAD_LEFT)),
        ];
    })->values()) !!},
    attendanceMap: {!! json_encode($attendanceMap) !!},
    selectedUserId: @json($currentSelectedUserId),
    month: {{ $month }},
    year: {{ $year }},
    daysInMonth: {{ $daysInMonth }},
    canManage: {{ $canManage ? 'true' : 'false' }},
    todayStr: @json($todayStr),
    editUrl: @json(url('attendance/edit')),
    detailsUrl: @json(url('attendance/details')),
    filterUrl: @json(route('attendance.filter')),
    indexUrl: @json($calendarActionUrl ?? (request()->routeIs('hr.dashboard') ? route('hr.dashboard') : route('attendance.index'))),
    gridUrl: @json(route('attendance.index', ['view' => 'grid']))
};

(function() {
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function pad2(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function formatTime12h(timeStr) {
        if (!timeStr) return '';
        if (timeStr.includes(' ')) {
            timeStr = timeStr.split(' ')[1];
        }
        const parts = timeStr.split(':');
        if (parts.length < 2) return timeStr;
        let h = parseInt(parts[0], 10);
        const m = parts[1];
        if (isNaN(h)) return timeStr;
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        return `${h}:${m} ${ampm}`;
    }

    function formatDuration(totalSeconds, durationStr) {
        if (totalSeconds && totalSeconds > 0) {
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            return `${h}h ${pad2(m)}m`;
        }
        if (durationStr && typeof durationStr === 'string' && durationStr.includes(':')) {
            const parts = durationStr.split(':');
            const h = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            if (!isNaN(h) && !isNaN(m)) {
                return `${h}h ${pad2(m)}m`;
            }
        }
        return '';
    }

    function renderCalendar(userId) {
        const data = window.PMS_CAL_DATA;
        if (!data || !data.users || data.users.length === 0) return;

        if (userId === 'all') {
            if (window.pmsSetViewMode) {
                window.pmsSetViewMode('grid');
            }
            return;
        }

        userId = parseInt(userId, 10);
        const user = data.users.find(u => u.id === userId) || data.users[0];
        if (!user) return;

        data.selectedUserId = user.id;

        // 1. Update Spotlight Card
        const avatarEl = document.getElementById('calSpotlightAvatar');
        const nameEl = document.getElementById('calSpotlightName');
        const desigEl = document.getElementById('calSpotlightDesignation');
        const deptEl = document.getElementById('calSpotlightDepartment');
        const empIdEl = document.getElementById('calSpotlightEmpId');
        const emailEl = document.getElementById('calSpotlightEmail');

        if (avatarEl) {
            avatarEl.src = user.avatar;
            avatarEl.alt = user.name;
        }
        if (nameEl) nameEl.textContent = user.name;
        if (desigEl) desigEl.innerHTML = '<i class="fas fa-briefcase"></i> ' + escapeHtml(user.designation);
        if (deptEl) deptEl.innerHTML = '<i class="fas fa-building"></i> ' + escapeHtml(user.department);
        if (empIdEl) empIdEl.innerHTML = '<i class="fas fa-id-badge"></i> ' + escapeHtml(user.employee_id);
        if (emailEl) emailEl.innerHTML = '<i class="fas fa-envelope"></i> ' + escapeHtml(user.email);

        // Sync dropdown
        const empSelect = document.getElementById('calEmployeeSelect');
        if (empSelect && empSelect.value != user.id) {
            empSelect.value = user.id;
        }

        // 2. Fetch User Attendance Map
        const userMap = (data.attendanceMap && data.attendanceMap[user.id]) || {};

        // 3. Compute KPI Statistics
        let presentCount = 0;
        let absentCount = 0;
        let lateCount = 0;
        let halfDayCount = 0;
        let leaveHolidayCount = 0;
        let totalSecondsWorked = 0;
        let pastWorkingDays = 0;

        const year = data.year;
        const month = data.month;
        const daysInMonth = data.daysInMonth;
        const todayStr = data.todayStr;

        for (let d = 1; d <= daysInMonth; d++) {
            const dateKey = `${year}-${pad2(month)}-${pad2(d)}`;
            const dateObj = new Date(year, month - 1, d);
            const dayOfWeek = dateObj.getDay(); // 0: Sun, 6: Sat
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
            const isPastOrToday = dateKey <= todayStr;
            const att = userMap[dateKey];
            const status = (att && att.status) ? String(att.status).toLowerCase() : '';

            if (isPastOrToday && !isWeekend) {
                pastWorkingDays++;
            }

            if (status === 'present') {
                presentCount++;
            } else if (status === 'late') {
                presentCount++;
                lateCount++;
            } else if (status === 'half_day' || status === 'halfday') {
                halfDayCount++;
            } else if (status === 'leave' || status === 'holiday') {
                leaveHolidayCount++;
            } else if (status === 'absent' && isPastOrToday && !isWeekend) {
                absentCount++;
            }

            if (att && att.total_seconds) {
                totalSecondsWorked += parseInt(att.total_seconds, 10);
            } else if (att && att.clock_in && att.clock_out) {
                try {
                    const tIn = new Date(`${dateKey}T${att.clock_in}`);
                    const tOut = new Date(`${dateKey}T${att.clock_out}`);
                    const diffSec = Math.max(0, Math.floor((tOut - tIn) / 1000));
                    if (!isNaN(diffSec) && diffSec > 0) {
                        totalSecondsWorked += diffSec;
                    }
                } catch(e) {}
            }
        }

        // Update KPI Chips
        const kpiPresent = document.getElementById('kpiPresentCount');
        const kpiAbsent = document.getElementById('kpiAbsentCount');
        const kpiLate = document.getElementById('kpiLateCount');
        const kpiHalfDay = document.getElementById('kpiHalfDayCount');
        const kpiLeave = document.getElementById('kpiLeaveCount');
        const kpiHours = document.getElementById('kpiTotalHours');
        const kpiRate = document.getElementById('kpiAttendanceRate');

        if (kpiPresent) kpiPresent.textContent = presentCount;
        if (kpiAbsent) kpiAbsent.textContent = absentCount;
        if (kpiLate) kpiLate.textContent = lateCount;
        if (kpiHalfDay) kpiHalfDay.textContent = halfDayCount;
        if (kpiLeave) kpiLeave.textContent = leaveHolidayCount;

        const totalHoursFormatted = Math.floor(totalSecondsWorked / 3600) + 'h ' + Math.floor((totalSecondsWorked % 3600) / 60) + 'm';
        if (kpiHours) kpiHours.textContent = totalHoursFormatted;

        const effectiveWorkedDays = presentCount + (halfDayCount * 0.5);
        const attendanceRatePct = pastWorkingDays > 0 ? Math.min(100, Math.round((effectiveWorkedDays / pastWorkingDays) * 100)) : 100;
        if (kpiRate) kpiRate.textContent = `${attendanceRatePct}%`;

        // 4. Render Calendar Grid Days
        const grid = document.getElementById('calDaysGrid');
        if (!grid) return;

        const firstDayOfMonth = new Date(year, month - 1, 1);
        const startingDay = firstDayOfMonth.getDay(); // 0: Sun, 1: Mon, ...
        const prevMonthLastDate = new Date(year, month - 1, 0).getDate();

        let gridHtml = '';

        // Leading padding days from previous month
        for (let i = 0; i < startingDay; i++) {
            const prevDayNum = prevMonthLastDate - startingDay + 1 + i;
            gridHtml += `
                <div class="cal-day-cell is-other-month">
                    <div class="cal-day-header">
                        <span class="cal-day-num">${prevDayNum}</span>
                    </div>
                </div>
            `;
        }

        // Days of current month
        for (let d = 1; d <= daysInMonth; d++) {
            const dateKey = `${year}-${pad2(month)}-${pad2(d)}`;
            const dateObj = new Date(year, month - 1, d);
            const dayOfWeek = dateObj.getDay();
            const isSunday = (dayOfWeek === 0);
            const isSaturday = (dayOfWeek === 6);
            const isWeekend = isSunday || isSaturday;
            const isToday = (dateKey === todayStr);
            const isFuture = (dateKey > todayStr);

            const att = userMap[dateKey];
            const rawStatus = (att && att.status) ? String(att.status).toLowerCase() : '';
            const attId = (att && att.id) ? att.id : '';

            let statusPillHtml = '';
            let timingHtml = '';
            let cellExtraClass = '';

            if (isToday) cellExtraClass += ' is-today';
            if (isWeekend) cellExtraClass += ' is-weekend-cell';

            if (att && att.status) {
                if (rawStatus === 'present') {
                    cellExtraClass += ' has-present';
                    statusPillHtml = `<span class="cal-status-pill status-present"><i class="fas fa-check-circle"></i> Present</span>`;
                } else if (rawStatus === 'late') {
                    cellExtraClass += ' has-late';
                    statusPillHtml = `<span class="cal-status-pill status-late"><i class="fas fa-clock"></i> Late</span>`;
                } else if (rawStatus === 'half_day' || rawStatus === 'halfday') {
                    cellExtraClass += ' has-halfday';
                    statusPillHtml = `<span class="cal-status-pill status-halfday"><i class="fas fa-star-half-alt"></i> Half Day</span>`;
                } else if (rawStatus === 'absent') {
                    cellExtraClass += ' has-absent';
                    statusPillHtml = `<span class="cal-status-pill status-absent"><i class="fas fa-times-circle"></i> Absent</span>`;
                } else if (rawStatus === 'holiday') {
                    cellExtraClass += ' has-holiday';
                    statusPillHtml = `<span class="cal-status-pill status-holiday"><i class="fas fa-calendar-star"></i> Holiday</span>`;
                } else if (rawStatus === 'leave') {
                    cellExtraClass += ' has-leave';
                    statusPillHtml = `<span class="cal-status-pill status-leave"><i class="fas fa-plane-departure"></i> Leave</span>`;
                } else {
                    statusPillHtml = `<span class="cal-status-pill status-upcoming">${escapeHtml(att.status)}</span>`;
                }

                // Clock times & Duration
                if (att.clock_in || att.clock_out) {
                    const inTime = formatTime12h(att.clock_in);
                    const outTime = formatTime12h(att.clock_out);
                    const durationStr = formatDuration(att.total_seconds, att.total_duration);

                    timingHtml = `
                        <div class="cal-timings">
                            ${inTime ? `<span class="cal-timing-in"><i class="fas fa-sign-in-alt"></i> ${inTime}</span>` : ''}
                            ${outTime ? `<span class="cal-timing-out"><i class="fas fa-sign-out-alt"></i> ${outTime}</span>` : ''}
                            ${durationStr ? `<span class="cal-hours-tag"><i class="fas fa-hourglass-half"></i> ${durationStr}</span>` : ''}
                        </div>
                    `;
                }
            } else {
                // No attendance record for this day
                if (isSunday) {
                    statusPillHtml = `<span class="cal-status-pill status-dayoff"><i class="fas fa-bed"></i> Sunday</span>`;
                } else if (isSaturday) {
                    statusPillHtml = `<span class="cal-status-pill status-wfh"><i class="fas fa-laptop-house"></i> Sat (WFH)</span>`;
                } else if (isFuture) {
                    statusPillHtml = `<span class="cal-status-pill status-upcoming"><i class="fas fa-calendar-minus"></i> Upcoming</span>`;
                } else {
                    statusPillHtml = `<span class="cal-status-pill status-absent"><i class="fas fa-times-circle"></i> Absent</span>`;
                }
            }

            // Cell action button
            let actionsHtml = '';
            if (attId) {
                actionsHtml = `
                    <div class="cal-cell-actions">
                        <button type="button" class="cal-action-btn btn-view view-attendance" title="View Details"
                                data-attendance-id="${attId}" data-user-id="${user.id}" data-date="${dateKey}">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${data.canManage ? `
                        <button type="button" class="cal-action-btn btn-edit edit-attendance" title="Edit Attendance"
                                data-attendance-id="${attId}" data-user-id="${user.id}" data-date="${dateKey}">
                            <i class="fas fa-pen"></i>
                        </button>
                        ` : ''}
                    </div>
                `;
            } else if (data.canManage && !isFuture) {
                actionsHtml = `
                    <div class="cal-cell-actions">
                        <button type="button" class="cal-action-btn btn-edit edit-attendance" title="Mark Attendance"
                                data-attendance-id="" data-user-id="${user.id}" data-date="${dateKey}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                `;
            }

            gridHtml += `
                <div class="cal-day-cell${cellExtraClass}"
                     data-date="${dateKey}"
                     data-user-id="${user.id}"
                     data-attendance-id="${attId}"
                     data-has-record="${attId ? '1' : '0'}"
                     data-is-future="${isFuture ? '1' : '0'}">
                    <div class="cal-day-header">
                        <span class="cal-day-num">${d}</span>
                        ${isToday ? '<span class="cal-today-pill">Today</span>' : (isSunday ? '<span class="cal-weekend-pill">Sun</span>' : (isSaturday ? '<span class="cal-weekend-pill" style="color:#0891b2; background:rgba(6,182,212,0.12);">Sat</span>' : ''))}
                    </div>
                    <div class="cal-status-body">
                        ${statusPillHtml}
                        ${timingHtml}
                    </div>
                    ${actionsHtml}
                </div>
            `;
        }

        // Trailing padding to complete grid
        const totalRendered = startingDay + daysInMonth;
        const totalGridCells = totalRendered > 35 ? 42 : 35;
        const trailingDays = totalGridCells - totalRendered;
        for (let nextDay = 1; nextDay <= trailingDays; nextDay++) {
            gridHtml += `
                <div class="cal-day-cell is-other-month">
                    <div class="cal-day-header">
                        <span class="cal-day-num">${nextDay}</span>
                    </div>
                </div>
            `;
        }

        grid.innerHTML = gridHtml;

        // Sync URL with user_id without full reload
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('user_id', user.id);
            window.history.replaceState({}, '', url.toString());
        }
    }

    // Attach listeners on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        const data = window.PMS_CAL_DATA;
        if (!data) return;

        // 1. Initial Render
        if (data.selectedUserId === 'all') {
            if (window.pmsSetViewMode) {
                window.pmsSetViewMode('grid', false);
            }
        } else {
            renderCalendar(data.selectedUserId);
        }

        // 2. Employee Dropdown Change
        const empSelect = document.getElementById('calEmployeeSelect');
        if (empSelect) {
            empSelect.addEventListener('change', function() {
                const val = this.value;
                if (val === 'all') {
                    if (window.pmsSetViewMode) {
                        window.pmsSetViewMode('grid');
                        const mainEmpSelect = document.getElementById('user_id');
                        if (mainEmpSelect) {
                            if (typeof $ !== 'undefined' && $.fn.select2) {
                                $(mainEmpSelect).val('').trigger('change.select2');
                            } else {
                                mainEmpSelect.value = '';
                            }
                        }
                    } else {
                        window.location.href = `${data.gridUrl}&month=${data.month}&year=${data.year}&user_id=all`;
                    }
                } else {
                    if (window.pmsSetViewMode) {
                        window.pmsSetViewMode('calendar', false);
                    }
                    renderCalendar(val);
                }
            });
        }

        // 3. Previous / Next Employee Buttons
        const prevEmpBtn = document.getElementById('calPrevEmpBtn');
        const nextEmpBtn = document.getElementById('calNextEmpBtn');

        if (prevEmpBtn && empSelect) {
            prevEmpBtn.addEventListener('click', function() {
                if (empSelect.selectedIndex > 0) {
                    empSelect.selectedIndex--;
                    const val = empSelect.value;
                    if (val === 'all') {
                        if (window.pmsSetViewMode) {
                            window.pmsSetViewMode('grid');
                        } else {
                            window.location.href = `${data.gridUrl}&month=${data.month}&year=${data.year}&user_id=all`;
                        }
                    } else {
                        if (window.pmsSetViewMode) window.pmsSetViewMode('calendar', false);
                        renderCalendar(val);
                    }
                }
            });
        }

        if (nextEmpBtn && empSelect) {
            nextEmpBtn.addEventListener('click', function() {
                if (empSelect.selectedIndex < empSelect.options.length - 1) {
                    empSelect.selectedIndex++;
                    const val = empSelect.value;
                    if (val === 'all') {
                        if (window.pmsSetViewMode) {
                            window.pmsSetViewMode('grid');
                        } else {
                            window.location.href = `${data.gridUrl}&month=${data.month}&year=${data.year}&user_id=all`;
                        }
                    } else {
                        if (window.pmsSetViewMode) window.pmsSetViewMode('calendar', false);
                        renderCalendar(val);
                    }
                }
            });
        }

        // 4. Month & Year Navigation
        function navigateDate(newMonth, newYear) {
            const activeUserId = (empSelect ? empSelect.value : data.selectedUserId) || data.selectedUserId;
            if (activeUserId === 'all') {
                window.location.href = `${data.gridUrl}&month=${newMonth}&year=${newYear}&user_id=all`;
                return;
            }
            let targetUrl = `${data.indexUrl}?month=${newMonth}&year=${newYear}`;
            if (activeUserId) {
                targetUrl += `&user_id=${activeUserId}`;
            }
            window.location.href = targetUrl;
        }

        const monthSelect = document.getElementById('calMonthSelect');
        const yearSelect = document.getElementById('calYearSelect');

        if (monthSelect) {
            monthSelect.addEventListener('change', function() {
                navigateDate(this.value, yearSelect ? yearSelect.value : data.year);
            });
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', function() {
                navigateDate(monthSelect ? monthSelect.value : data.month, this.value);
            });
        }

        const prevMonthBtn = document.getElementById('calPrevMonthBtn');
        const nextMonthBtn = document.getElementById('calNextMonthBtn');

        if (prevMonthBtn) {
            prevMonthBtn.addEventListener('click', function() {
                let m = parseInt(data.month, 10) - 1;
                let y = parseInt(data.year, 10);
                if (m < 1) { m = 12; y--; }
                navigateDate(m, y);
            });
        }

        if (nextMonthBtn) {
            nextMonthBtn.addEventListener('click', function() {
                let m = parseInt(data.month, 10) + 1;
                let y = parseInt(data.year, 10);
                if (m > 12) { m = 1; y++; }
                navigateDate(m, y);
            });
        }

        const todayBtn = document.getElementById('calTodayBtn');
        if (todayBtn) {
            todayBtn.addEventListener('click', function() {
                const now = new Date();
                const activeUserId = (empSelect ? empSelect.value : data.selectedUserId) || data.selectedUserId;
                if (activeUserId === 'all') {
                    window.location.href = `${data.gridUrl}&month=${now.getMonth() + 1}&year=${now.getFullYear()}&user_id=all`;
                    return;
                }
                let targetUrl = `${data.indexUrl}?month=${now.getMonth() + 1}&year=${now.getFullYear()}`;
                if (activeUserId) {
                    targetUrl += `&user_id=${activeUserId}`;
                }
                window.location.href = targetUrl;
            });
        }

        // Modal helper for Calendar view
        function openAttModal(title, url) {
            if (typeof $ === 'undefined') return;
            const modalEl = document.getElementById('attendanceDetailsModal');
            if (!modalEl) return;
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            const modal = (window.pmsGetAttendanceModal && typeof window.pmsGetAttendanceModal === 'function')
                ? window.pmsGetAttendanceModal()
                : bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true, keyboard: true });

            $('#attendanceDetailsModal .modal-title').text(title);
            if (window.pmsShowAttendanceLoading && typeof window.pmsShowAttendanceLoading === 'function') {
                window.pmsShowAttendanceLoading('Loading attendance data…');
            } else {
                $('#attendanceDetailsBody').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 text-muted">Loading attendance data…</p></div>'
                );
            }
            modal.show();
            $.get(url)
                .done(function(html) { $('#attendanceDetailsBody').html(html); })
                .fail(function() { $('#attendanceDetailsBody').html('<div class="alert alert-danger m-4">Error loading data. Please try again.</div>'); });
        }

        // 5. Quick Mark Attendance Button
        const quickMarkBtn = document.getElementById('calQuickMarkBtn');
        if (quickMarkBtn) {
            quickMarkBtn.addEventListener('click', function() {
                let activeUserId = (empSelect ? empSelect.value : data.selectedUserId) || data.selectedUserId;
                if (activeUserId === 'all' && data.users && data.users.length > 0) {
                    activeUserId = data.users[0].id;
                }
                const today = data.todayStr;
                const editUrl = `${data.editUrl}?user_id=${activeUserId}&date=${today}`;
                openAttModal('Mark Attendance', editUrl);
            });
        }

        // 6. Day Cell Click Handlers (Delegated)
        const grid = document.getElementById('calDaysGrid');
        if (grid) {
            grid.addEventListener('click', function(e) {
                if (e.target.closest('.cal-action-btn')) {
                    return;
                }

                const cell = e.target.closest('.cal-day-cell:not(.is-other-month)');
                if (!cell) return;

                const hasRecord = cell.getAttribute('data-has-record') === '1';
                const isFuture = cell.getAttribute('data-is-future') === '1';
                const attId = cell.getAttribute('data-attendance-id') || '';
                const userId = cell.getAttribute('data-user-id');
                const date = cell.getAttribute('data-date');

                if (hasRecord) {
                    const url = `${data.detailsUrl}?attendance_id=${attId}&user_id=${userId}&date=${date}`;
                    openAttModal('Attendance Details', url);
                } else if (data.canManage && !isFuture) {
                    const url = `${data.editUrl}?attendance_id=&user_id=${userId}&date=${date}`;
                    openAttModal('Mark Attendance', url);
                }
            });
        }
    // Modal form submission & close handling
    if (typeof $ !== 'undefined') {
        $(document).off('submit.pmsCalAttForm', '#attendanceDetailsModal .attendance-form')
            .on('submit.pmsCalAttForm', '#attendanceDetailsModal .attendance-form', function(e) {
                e.preventDefault();
                var form = this;
                var submitBtn = $(form).find('[type="submit"]').first();
                var orig = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');
                $.ajax({
                    url: form.action,
                    type: form.method || 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(resp) {
                        var modalEl = document.getElementById('attendanceDetailsModal');
                        if (modalEl) {
                            var m = bootstrap.Modal.getInstance(modalEl);
                            if (m) m.hide();
                        }
                        window.location.reload();
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to save. Please try again.';
                        $(form).prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">' + msg +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(orig);
                    }
                });
            });

        $(document).off('click.pmsCalClose', '[data-attendance-modal-close]')
            .on('click.pmsCalClose', '[data-attendance-modal-close]', function(e) {
                e.preventDefault();
                var modalEl = document.getElementById('attendanceDetailsModal');
                if (modalEl) {
                    var m = bootstrap.Modal.getInstance(modalEl);
                    if (m) m.hide();
                }
            });
    }

    // Expose renderCalendar globally so AJAX reloads can invoke it
    window.pmsRenderAttendanceCalendar = renderCalendar;
})();
</script>
