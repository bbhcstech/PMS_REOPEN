{{-- resources/views/admin/attendance/table.blade.php --}}
@php $authUser = Auth::user(); @endphp

{{-- ===== ATTENDANCE TABLE - PREMIUM DESIGN ===== --}}
<div class="attendance-table-wrapper">

    {{-- Export Buttons - Top Right --}}
    <div class="export-top">
        <button id="exportExcelBtnTable" class="btn-export-excel" type="button">
            <i class="fas fa-file-excel"></i> Excel
        </button>
        <button id="exportPdfBtnTable" class="btn-export-pdf" type="button">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
    </div>

    <div class="table-responsive">
        <table id="attendanceTable" class="attendance-table">
            <thead>
                <tr>
                    <th class="employee-col">
                        <i class="fas fa-user-circle"></i> Employee
                    </th>
                    @for($i=1;$i<=$daysInMonth;$i++)
                        @php $date = \Carbon\Carbon::createFromDate($year,$month,$i); @endphp
                        <th class="day-col">
                            <span class="day-number">{{ $i }}</span>
                            <span class="day-name">{{ $date->format('D') }}</span>
                        </th>
                    @endfor
                    <th class="total-col">
                        <i class="fas fa-clock"></i> Total Hours
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="employee-cell">
                            <div class="employee-avatar">
                                <span>{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div class="employee-info">
                                <div class="employee-name">{{ $user->name }}</div>
                                <div class="employee-designation">{{ $user->employeeDetail->designation->name ?? '-' }}</div>
                            </div>
                        </td>

                        @php
                            $totalSeconds = 0;
                            $presentCount = 0;
                        @endphp

                        @for($d=1;$d<=$daysInMonth;$d++)
                            @php
                                $dateKey = \Carbon\Carbon::createFromDate($year,$month,$d)->format('Y-m-d');
                                $attendance = $attendanceMap[$user->id][$dateKey] ?? null;
                                $status = strtolower($attendance->status ?? '');
                                $durationFlag = strtolower($attendance->duration ?? '');
                                $symbol = '-';
                                $popupClass = '';
                                $rowSeconds = 0;
                                $today = \Carbon\Carbon::now()->format('Y-m-d');
                                $statusClass = '';

                                if ($status === 'holiday') {
                                    $occ = $attendance->occassion ?? 'Holiday';
                                    $symbol = '<i class="fas fa-star status-icon holiday-icon" data-bs-toggle="tooltip" title="' . $occ . '"></i>';
                                    $statusClass = 'holiday';
                                    $popupClass = '';
                                } elseif ($dateKey <= $today) {
                                    switch ($status) {
                                        case 'present':
                                            $symbol = '<i class="fas fa-check status-icon present-icon" data-bs-toggle="tooltip" title="Present"></i>';
                                            $presentCount++;
                                            $statusClass = 'present';
                                            $popupClass = 'view-attendance';
                                            break;
                                        case 'absent':
                                            $symbol = '<i class="fas fa-times status-icon absent-icon" data-bs-toggle="tooltip" title="Absent"></i>';
                                            $statusClass = 'absent';
                                            $popupClass = 'edit-attendance';
                                            break;
                                        case 'late':
                                            $symbol = '<i class="fas fa-clock status-icon late-icon" data-bs-toggle="tooltip" title="Late"></i>';
                                            $presentCount++;
                                            $statusClass = 'late';
                                            $popupClass = 'view-attendance';
                                            break;
                                        case 'half_day':
                                            $symbol = '<i class="fas fa-star-half-alt status-icon halfday-icon" data-bs-toggle="tooltip" title="Half Day"></i>';
                                            $presentCount += 0.5;
                                            $statusClass = 'halfday';
                                            $popupClass = 'view-attendance';
                                            break;
                                        case 'leave':
                                            $lr = $attendance->reason ?? 'On Leave';
                                            if ($durationFlag === 'full-day') {
                                                $symbol = '<i class="fas fa-plane-departure status-icon leave-icon" data-bs-toggle="tooltip" title="' . $lr . '"></i>';
                                                $statusClass = 'leave';
                                            } else {
                                                $symbol = '<i class="fas fa-star-half-alt status-icon halfday-icon" data-bs-toggle="tooltip" title="' . $lr . ' (Half Day)"></i>';
                                                $presentCount += 0.5;
                                                $statusClass = 'halfday';
                                            }
                                            $popupClass = '';
                                            break;
                                        default:
                                            $symbol = '<span class="status-icon default-icon">—</span>';
                                            $statusClass = 'default';
                                            $popupClass = '';
                                    }
                                } else {
                                    if ($status === 'leave') {
                                        if ($durationFlag === 'full-day') {
                                            $symbol = '<i class="fas fa-plane-departure status-icon leave-icon" data-bs-toggle="tooltip" title="Planned Leave"></i>';
                                            $statusClass = 'leave';
                                        } else {
                                            $symbol = '<i class="fas fa-star-half-alt status-icon halfday-icon" data-bs-toggle="tooltip" title="Planned Half Day"></i>';
                                            $statusClass = 'halfday';
                                        }
                                    } else {
                                        $symbol = '<span class="status-icon default-icon">—</span>';
                                        $statusClass = 'default';
                                    }
                                    $popupClass = '';
                                }

                                if (!empty($attendance)) {
                                    if (isset($attendance->total_seconds) && is_numeric($attendance->total_seconds)) {
                                        $rowSeconds = (int)$attendance->total_seconds;
                                    } elseif (isset($attendance->duration_seconds) && is_numeric($attendance->duration_seconds)) {
                                        $rowSeconds = (int)$attendance->duration_seconds;
                                    } else {
                                        $ciRaw = $attendance->clock_in ?? null;
                                        $coRaw = $attendance->clock_out ?? null;
                                        $ciStr = is_null($ciRaw) ? '' : trim((string)$ciRaw);
                                        $coStr = is_null($coRaw) ? '' : trim((string)$coRaw);
                                        if ($ciStr !== '' && $coStr !== '') {
                                            try {
                                                $ci = \Carbon\Carbon::parse($dateKey . ' ' . $ciStr);
                                                $co = \Carbon\Carbon::parse($dateKey . ' ' . $coStr);
                                                if ($co->lt($ci)) $co = $co->copy()->addDay();
                                                $rowSeconds = max(0, $co->diffInSeconds($ci));
                                            } catch (\Throwable $e) {
                                                $ciParts = preg_split('/\D+/', $ciStr, -1, PREG_SPLIT_NO_EMPTY);
                                                $coParts = preg_split('/\D+/', $coStr, -1, PREG_SPLIT_NO_EMPTY);
                                                if (count($ciParts) >= 2 && count($coParts) >= 2) {
                                                    $ciSeconds = ((int)$ciParts[0])*3600 + ((int)$ciParts[1])*60 + ((int)($ciParts[2] ?? 0));
                                                    $coSeconds = ((int)$coParts[0])*3600 + ((int)$coParts[1])*60 + ((int)($coParts[2] ?? 0));
                                                    if ($coSeconds < $ciSeconds) $coSeconds += 86400;
                                                    $rowSeconds = max(0, $coSeconds - $ciSeconds);
                                                } else {
                                                    $rowSeconds = 0;
                                                }
                                            }
                                        }
                                    }
                                }

                                $totalSeconds += (int)$rowSeconds;
                            @endphp

                            <td class="status-cell {{ $statusClass }}">
                                @php $isAdmin = auth()->user()->role === 'admin'; @endphp

                                @if($popupClass === 'view-attendance')
                                    <a href="javascript:;" class="view-attendance" data-attendance-id="{{ $attendance->id ?? '' }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}">
                                        {!! $symbol !!}
                                    </a>
                                @elseif($popupClass === 'edit-attendance' && $isAdmin)
                                    <a href="javascript:;" class="edit-attendance" data-attendance-id="{{ $attendance->id ?? '' }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}">
                                        {!! $symbol !!}
                                    </a>
                                @else
                                    {!! $symbol !!}
                                @endif
                            </td>

                        @endfor

                        @php
                            $h = intdiv($totalSeconds, 3600);
                            $m = intdiv($totalSeconds % 3600, 60);
                            $s = $totalSeconds % 60;
                            $total_human = sprintf('%d:%02d:%02d', $h, $m, $s);
                        @endphp

                        <td class="total-cell">
                            <span class="total-hours">{{ $total_human }}</span>
                            <span class="total-count">({{ $presentCount }} / {{ $daysInMonth }})</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ===== MODAL ===== --}}
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-check"></i> Attendance Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="attendanceDetailsBody" class="modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== STYLES ===== --}}
<style>
    /* ===== ATTENDANCE TABLE - PREMIUM DESIGN ===== */
    .attendance-table-wrapper {
        position: relative;
        padding-top: 55px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
                    0px 8px 40px rgba(0, 0, 0, 0.04),
                    0px 20px 60px rgba(30, 58, 138, 0.06);
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .attendance-table-wrapper:hover {
        box-shadow: 0px 20px 50px rgba(0, 0, 0, 0.08),
                    0px 30px 80px rgba(30, 58, 138, 0.12);
        border-color: rgba(14, 165, 164, 0.2);
    }

    /* ===== EXPORT BUTTONS ===== */
    .export-top {
        position: absolute;
        top: 12px;
        right: 16px;
        z-index: 30;
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-export-excel,
    .btn-export-pdf {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 1rem;
        border: none;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none;
        min-height: 34px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .btn-export-excel {
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: white;
        box-shadow: 0 2px 12px rgba(34, 197, 94, 0.2);
    }

    .btn-export-excel:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.3);
        color: white;
    }

    .btn-export-pdf {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: white;
        box-shadow: 0 2px 12px rgba(239, 68, 68, 0.2);
    }

    .btn-export-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .btn-export-excel i,
    .btn-export-pdf i {
        font-size: 0.85rem;
    }

    /* ===== TABLE ===== */
    .table-responsive {
        overflow-x: auto;
        padding: 0 0.5rem 0.5rem;
    }

    .attendance-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.2rem;
        min-width: 900px;
    }

    .attendance-table thead th {
        padding: 0.7rem 0.5rem;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        color: #475569;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: center;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .attendance-table thead th i {
        margin-right: 4px;
        color: var(--primary-teal, #0ea5a4);
        font-size: 0.8rem;
    }

    .attendance-table thead th.employee-col {
        text-align: left;
        min-width: 180px;
        border-radius: 14px 0 0 0;
    }

    .attendance-table thead th.total-col {
        text-align: center;
        min-width: 140px;
        border-radius: 0 14px 0 0;
    }

    .attendance-table thead th .day-number {
        display: block;
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .attendance-table thead th .day-name {
        font-size: 0.6rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .attendance-table tbody tr {
        transition: all 0.3s ease;
    }

    .attendance-table tbody tr:hover td {
        background: #f8fafc;
    }

    .attendance-table tbody td {
        padding: 0.6rem 0.4rem;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        font-size: 0.85rem;
    }

    /* ===== EMPLOYEE CELL ===== */
    .employee-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.3rem 0.5rem;
        min-height: 50px;
        text-align: left !important;
    }

    .employee-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-blue, #1e3a8a), var(--primary-teal, #0ea5a4));
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .employee-info {
        flex: 1;
        min-width: 0;
    }

    .employee-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .employee-designation {
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 500;
        white-space: nowrap;
    }

    /* ===== STATUS CELL ===== */
    .status-cell {
        padding: 0.3rem 0.2rem !important;
        border-radius: 8px;
        transition: all 0.2s ease;
        min-width: 36px;
    }

    .status-cell a {
        text-decoration: none;
        display: inline-block;
        padding: 4px 6px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .status-cell a:hover {
        transform: scale(1.15);
        background: rgba(0, 0, 0, 0.04);
    }

    .status-icon {
        font-size: 1.1rem;
        transition: all 0.2s ease;
    }

    .status-icon.present-icon { color: #22c55e; }
    .status-icon.absent-icon { color: #ef4444; }
    .status-icon.late-icon { color: #f59e0b; }
    .status-icon.halfday-icon { color: #8b5cf6; }
    .status-icon.leave-icon { color: #06b6d4; }
    .status-icon.holiday-icon { color: #e67e22; }
    .status-icon.default-icon { color: #94a3b8; font-weight: 700; }

    .status-cell.present { background: rgba(34, 197, 94, 0.06); border-radius: 8px; }
    .status-cell.absent { background: rgba(239, 68, 68, 0.06); border-radius: 8px; }
    .status-cell.late { background: rgba(245, 158, 11, 0.06); border-radius: 8px; }
    .status-cell.halfday { background: rgba(139, 92, 246, 0.06); border-radius: 8px; }
    .status-cell.leave { background: rgba(6, 182, 212, 0.06); border-radius: 8px; }
    .status-cell.holiday { background: rgba(230, 126, 34, 0.06); border-radius: 8px; }

    /* ===== TOTAL CELL ===== */
    .total-cell {
        text-align: center !important;
        padding: 0.6rem 0.5rem !important;
        background: rgba(14, 165, 164, 0.03);
        border-radius: 8px;
        min-width: 120px;
    }

    .total-hours {
        display: block;
        font-weight: 800;
        font-size: 0.95rem;
        color: var(--primary-teal, #0ea5a4);
    }

    .total-count {
        display: block;
        font-size: 0.65rem;
        color: #94a3b8;
        font-weight: 600;
    }

    /* ===== MODAL ===== */
    .modal-content {
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0px 20px 60px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--primary-blue, #1e3a8a), var(--primary-teal, #0ea5a4));
        border: none;
        color: white;
    }

    .modal-header .modal-title {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .modal-header .modal-title i {
        margin-right: 0.5rem;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 1.5rem;
        background: #fafefa;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #fafefa;
    }

    .btn-close-modal {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1.5rem;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-close-modal:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .attendance-table-wrapper {
            padding-top: 70px;
        }

        .export-top {
            top: 8px;
            right: 10px;
            flex-wrap: wrap;
            gap: 4px;
        }

        .btn-export-excel,
        .btn-export-pdf {
            padding: 0.3rem 0.7rem;
            font-size: 0.65rem;
            min-height: 28px;
        }

        .btn-export-excel i,
        .btn-export-pdf i {
            font-size: 0.7rem;
        }

        .attendance-table thead th {
            font-size: 0.6rem;
            padding: 0.4rem 0.2rem;
        }

        .attendance-table thead th .day-number {
            font-size: 0.7rem;
        }

        .attendance-table tbody td {
            font-size: 0.75rem;
            padding: 0.4rem 0.2rem;
        }

        .employee-name {
            font-size: 0.75rem;
        }

        .employee-designation {
            font-size: 0.6rem;
        }

        .employee-avatar {
            width: 30px;
            height: 30px;
            font-size: 0.7rem;
        }

        .status-icon {
            font-size: 0.9rem;
        }

        .total-hours {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .attendance-table-wrapper {
            padding-top: 80px;
            border-radius: 16px;
        }

        .export-top {
            top: 6px;
            right: 6px;
            gap: 3px;
        }

        .btn-export-excel,
        .btn-export-pdf {
            padding: 0.25rem 0.5rem;
            font-size: 0.55rem;
            min-height: 24px;
            border-radius: 20px;
        }

        .btn-export-excel i,
        .btn-export-pdf i {
            font-size: 0.6rem;
        }

        .attendance-table thead th {
            font-size: 0.5rem;
            padding: 0.3rem 0.15rem;
        }

        .attendance-table thead th .day-number {
            font-size: 0.6rem;
        }

        .attendance-table thead th .day-name {
            font-size: 0.45rem;
        }

        .attendance-table tbody td {
            font-size: 0.65rem;
            padding: 0.3rem 0.15rem;
        }

        .employee-cell {
            padding: 0.2rem 0.3rem;
            gap: 0.4rem;
            min-height: 40px;
        }

        .employee-avatar {
            width: 24px;
            height: 24px;
            font-size: 0.6rem;
            border-radius: 8px;
        }

        .employee-name {
            font-size: 0.65rem;
        }

        .employee-designation {
            font-size: 0.5rem;
        }

        .status-icon {
            font-size: 0.75rem;
        }

        .status-cell {
            padding: 0.2rem 0.1rem !important;
            min-width: 24px;
        }

        .total-cell {
            padding: 0.3rem 0.2rem !important;
            min-width: 60px;
        }

        .total-hours {
            font-size: 0.7rem;
        }

        .total-count {
            font-size: 0.5rem;
        }

        .modal-dialog {
            margin: 0.5rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-table-wrapper {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .attendance-table thead th {
        background: linear-gradient(135deg, #183026, #102119);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .attendance-table thead th .day-number {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .attendance-table thead th .day-name {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .attendance-table tbody tr:hover td {
        background: #183026;
    }

    html[data-pms-theme="dark"] .attendance-table tbody td {
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .employee-name {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .employee-designation {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .employee-avatar {
        background: linear-gradient(135deg, #0f744c, #10b981);
    }

    html[data-pms-theme="dark"] .status-cell.present { background: rgba(34, 197, 94, 0.12); }
    html[data-pms-theme="dark"] .status-cell.absent { background: rgba(239, 68, 68, 0.12); }
    html[data-pms-theme="dark"] .status-cell.late { background: rgba(245, 158, 11, 0.12); }
    html[data-pms-theme="dark"] .status-cell.halfday { background: rgba(139, 92, 246, 0.12); }
    html[data-pms-theme="dark"] .status-cell.leave { background: rgba(6, 182, 212, 0.12); }
    html[data-pms-theme="dark"] .status-cell.holiday { background: rgba(230, 126, 34, 0.12); }

    html[data-pms-theme="dark"] .status-icon.present-icon { color: #34d399; }
    html[data-pms-theme="dark"] .status-icon.absent-icon { color: #f87171; }
    html[data-pms-theme="dark"] .status-icon.late-icon { color: #fbbf24; }
    html[data-pms-theme="dark"] .status-icon.halfday-icon { color: #a78bfa; }
    html[data-pms-theme="dark"] .status-icon.leave-icon { color: #22d3ee; }
    html[data-pms-theme="dark"] .status-icon.holiday-icon { color: #fb923c; }
    html[data-pms-theme="dark"] .status-icon.default-icon { color: #64748b; }

    html[data-pms-theme="dark"] .total-cell {
        background: rgba(52, 211, 153, 0.06);
    }

    html[data-pms-theme="dark"] .total-hours {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .total-count {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .modal-content {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .modal-header {
        background: linear-gradient(135deg, #0a5a3a, #0f744c);
    }

    html[data-pms-theme="dark"] .modal-body {
        background: #102119;
    }

    html[data-pms-theme="dark"] .modal-footer {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-close-modal {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.2);
    }

    html[data-pms-theme="dark"] .btn-close-modal:hover {
        background: #102119;
        color: #ffffff;
    }
</style>

@push('js')
<script>
$(document).ready(function () {
    // ===== DataTable Initialization =====
    if ($.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#attendanceTable')) {
            $('#attendanceTable').DataTable().destroy();
        }

        $('#attendanceTable').DataTable({
            dom: 'lrtip',
            responsive: true,
            scrollX: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search attendance...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            },
            initComplete: function() {
                // Move search to custom position if needed
            }
        });
    }

    // ===== Export Buttons =====
    $('#exportExcelBtnTable').off('click').on('click', function (e) {
        e.preventDefault();
        var qs = (function() {
            var form = document.getElementById('attendanceFilter');
            if (!form) return '';
            return new URLSearchParams(new FormData(form)).toString();
        })();
        var url = "{{ url('attendance/export/excel') }}";
        window.location.href = qs ? (url + '?' + qs) : url;
    });

    $('#exportPdfBtnTable').off('click').on('click', function (e) {
        e.preventDefault();
        var qs = (function() {
            var form = document.getElementById('attendanceFilter');
            if (!form) return '';
            return new URLSearchParams(new FormData(form)).toString();
        })();
        var url = "{{ url('attendance/export/pdf') }}";
        var full = qs ? (url + '?' + qs) : url;
        var w = window.open(full, '_blank');
        if (!w) window.location.href = full;
    });

    // ===== View Attendance Modal =====
    $(document).on('click', '.view-attendance', function(e) {
        e.preventDefault();
        var attendanceId = $(this).data('attendance-id');
        var userId = $(this).data('user-id');
        var date = $(this).data('date');

        var url = "{{ url('attendance/details') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        var modal = new bootstrap.Modal(document.getElementById('attendanceDetailsModal'));
        modal.show();

        $('#attendanceDetailsBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#attendanceDetailsBody').html(response);
            },
            error: function(xhr) {
                $('#attendanceDetailsBody').html('<div class="alert alert-danger">Error loading attendance details.</div>');
            }
        });
    });

    // ===== Edit Attendance Modal (Admin) =====
    $(document).on('click', '.edit-attendance', function(e) {
        e.preventDefault();
        var attendanceId = $(this).data('attendance-id');
        var userId = $(this).data('user-id');
        var date = $(this).data('date');

        var url = "{{ url('attendance/edit') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        var modal = new bootstrap.Modal(document.getElementById('attendanceDetailsModal'));
        modal.show();

        $('#attendanceDetailsBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#attendanceDetailsBody').html(response);
            },
            error: function(xhr) {
                $('#attendanceDetailsBody').html('<div class="alert alert-danger">Error loading attendance form.</div>');
            }
        });
    });

    // ===== Tooltips =====
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            placement: 'top'
        });
    });

    $(document).ajaxComplete(function () {
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            placement: 'top'
        });
    });

});
</script>
@endpush
