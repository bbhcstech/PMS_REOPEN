<div class="attendance-details-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        {{-- ===== EMPLOYEE & SHIFT INFO ===== --}}
        <div class="info-grid">
            {{-- Employee Information --}}
            <div class="info-card">
                <div class="card-header-custom">
                    <h6><i class="fas fa-user-circle"></i> Employee Information</h6>
                </div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user"></i> Name</div>
                        <div class="info-value">{{ $attendance->user->name ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-briefcase"></i> Designation</div>
                        <div class="info-value">{{ $attendance->user->employeeDetail->designation->name ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Date</div>
                        <div class="info-value">{{ optional($attendanceDate)->format('d M Y (l)') ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-id-badge"></i> Employee ID</div>
                        <div class="info-value">{{ $attendance->user->employee_id ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- Shift Information --}}
            <div class="info-card">
                <div class="card-header-custom">
                    <h6><i class="fas fa-clock"></i> Shift Information</h6>
                </div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Location</div>
                        <div class="info-value">{{ $attendance->location ?? 'Office' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-building"></i> Department</div>
                        <div class="info-value">{{ $attendance->user->employeeDetail->department->dpt_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user-tag"></i> Reporting To</div>
                        <div class="info-value">{{ $attendance->user->employeeDetail->reporting_to ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-check"></i> Work Type</div>
                        <div class="info-value">{{ ucfirst($attendance->work_from_type ?? 'Office') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ATTENDANCE SUMMARY ===== --}}
        <div class="summary-card">
            <div class="summary-header">
                <h6><i class="fas fa-chart-bar"></i> Attendance Summary</h6>
                @if(auth()->user()->role === 'admin')
                <div class="dropdown">
                    <button class="btn-action-dropdown" type="button" id="summaryActions" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="summaryActions">
                        <li>
                            <a class="dropdown-item edit-attendance-summary"
                               href="javascript:void(0);"
                               data-attendance-id="{{ $attendance->id }}"
                               data-user-id="{{ $attendance->user_id }}"
                               data-date="{{ optional($attendanceDate)->format('Y-m-d') }}">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger delete-attendance-summary"
                               href="javascript:void(0);"
                               data-attendance-id="{{ $attendance->id }}">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-icon clock-in"><i class="fas fa-sign-in-alt"></i></div>
                    <div class="summary-info">
                        <div class="summary-label">Clock In</div>
                        <div class="summary-value">
                            @if(!empty($startTime))
                                {{ $startTime->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon clock-out"><i class="fas fa-sign-out-alt"></i></div>
                    <div class="summary-info">
                        <div class="summary-label">Clock Out</div>
                        <div class="summary-value">
                            @if($notClockedOut)
                                <span class="text-warning">Did not clock out</span>
                            @elseif(!empty($endTime))
                                {{ $endTime->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon duration"><i class="fas fa-hourglass-half"></i></div>
                    <div class="summary-info">
                        <div class="summary-label">Total Duration</div>
                        <div class="summary-value">{{ $totalTimeFormatted ?? '00:00:00' }}</div>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon status"><i class="fas fa-flag"></i></div>
                    <div class="summary-info">
                        <div class="summary-label">Status</div>
                        <div class="summary-value">
                            @if($attendance->late == 'yes')
                                <span class="status-badge late"><i class="fas fa-clock"></i> Late</span>
                            @elseif($attendance->half_day == 'yes')
                                <span class="status-badge halfday"><i class="fas fa-star-half-alt"></i> Half Day</span>
                            @else
                                <span class="status-badge present"><i class="fas fa-check-circle"></i> Present</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ACTIVITY LOG ===== --}}
        <div class="activity-card">
            <div class="activity-header">
                <h6><i class="fas fa-list-ul"></i> Activity Log</h6>
                <span class="activity-count">{{ count($attendanceActivity) }} activities</span>
            </div>

            <div class="activity-timeline">
                @forelse($attendanceActivity as $activity)
                    @php
                        $inDt = $activity->in_dt ?? null;
                        $outDt = $activity->out_dt ?? null;
                        $outForDuration = $activity->out_for_duration ?? null;
                        $durationSeconds = $activity->duration_seconds;
                        $location = $activity->location ?? ($activity->raw->location ?? 'Office');
                        $isClockOut = $outDt ? true : false;
                    @endphp

                    <div class="activity-item {{ $isClockOut ? 'clock-out' : 'clock-in' }}">
                        <div class="activity-icon">
                            <i class="fas {{ $isClockOut ? 'fa-sign-out-alt' : 'fa-sign-in-alt' }}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-head">
                                <span class="activity-type">
                                    <strong>{{ $isClockOut ? 'Clock Out' : 'Clock In' }}</strong>
                                </span>
                                <span class="activity-time">
                                    <i class="far fa-clock"></i>
                                    @if($inDt)
                                        {{ $inDt->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                    @if($outDt)
                                        - {{ $outDt->timezone($companyTimezone ?? config('app.timezone'))->format('h:i A') }}
                                    @endif
                                </span>
                            </div>
                            <div class="activity-details">
                                <span class="activity-location">
                                    <i class="fas fa-map-marker-alt"></i> {{ $location }}
                                </span>
                                @if($durationSeconds !== null && $inDt && $outForDuration)
                                    <span class="activity-duration">
                                        <i class="fas fa-hourglass-half"></i>
                                        {{ $activity->duration_human }} ({{ gmdate('H:i:s', max(0, $durationSeconds)) }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="activity-status">
                            @if($isClockOut)
                                <span class="status-dot completed"></span>
                            @else
                                <span class="status-dot active"></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="activity-empty">
                        <i class="fas fa-inbox"></i>
                        <p>No activity found for this date.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ===== EDIT MODAL ===== --}}
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom" id="editAttendanceBody">
                <div class="loading-state">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Loading attendance form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM ATTENDANCE DETAILS STYLES ===== */
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

    .attendance-details-container {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        position: relative;
        overflow: hidden;
    }

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

    /* ===== INFO GRID ===== */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .info-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    .card-header-custom {
        padding: 0.9rem 1.5rem;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-header-custom h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-header-custom h6 i {
        color: var(--primary-teal);
        font-size: 1rem;
    }

    .card-body-custom {
        padding: 1.25rem 1.5rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .info-label i {
        color: var(--primary-teal);
        font-size: 0.75rem;
        width: 18px;
    }

    .info-value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0f172a;
        text-align: right;
    }

    /* ===== SUMMARY CARD ===== */
    .summary-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .summary-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    .summary-header {
        padding: 0.9rem 1.5rem;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-header h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .summary-header h6 i {
        color: var(--primary-teal);
        font-size: 1rem;
    }

    .btn-action-dropdown {
        background: transparent;
        border: none;
        color: #64748b;
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition-smooth);
    }

    .btn-action-dropdown:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .btn-action-dropdown i {
        font-size: 1.1rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
        padding: 1.25rem 1.5rem;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.8rem;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        transition: var(--transition-smooth);
    }

    .summary-item:hover {
        border-color: var(--primary-teal);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14, 165, 164, 0.08);
    }

    .summary-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .summary-icon.clock-in {
        background: #d1fae5;
        color: #065f46;
    }

    .summary-icon.clock-out {
        background: #dbeafe;
        color: #1e40af;
    }

    .summary-icon.duration {
        background: #ede9fe;
        color: #5b21b6;
    }

    .summary-icon.status {
        background: #fef3c7;
        color: #92400e;
    }

    .summary-info {
        flex: 1;
        min-width: 0;
    }

    .summary-label {
        font-size: 0.6rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .summary-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 0.1rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.8rem;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-badge.present {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.late {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.halfday {
        background: #ede9fe;
        color: #5b21b6;
    }

    .status-badge i {
        font-size: 0.6rem;
    }

    .text-warning {
        color: #d97706 !important;
    }

    /* ===== ACTIVITY CARD ===== */
    .activity-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
        overflow: hidden;
    }

    .activity-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    .activity-header {
        padding: 0.9rem 1.5rem;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .activity-header h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .activity-header h6 i {
        color: var(--primary-teal);
        font-size: 1rem;
    }

    .activity-count {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        background: #e2e8f0;
        padding: 0.2rem 0.8rem;
        border-radius: 30px;
    }

    .activity-timeline {
        padding: 1.25rem 1.5rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-timeline::-webkit-scrollbar {
        width: 4px;
    }

    .activity-timeline::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .activity-timeline::-webkit-scrollbar-thumb {
        background: var(--primary-teal);
        border-radius: 10px;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 0.8rem 0.8rem;
        margin-bottom: 0.5rem;
        border-radius: 14px;
        background: #f8fafc;
        border-left: 4px solid #94a3b8;
        transition: var(--transition-smooth);
    }

    .activity-item:hover {
        background: #f1f5f9;
        transform: translateX(4px);
    }

    .activity-item.clock-in {
        border-left-color: #22c55e;
    }

    .activity-item.clock-out {
        border-left-color: #3b82f6;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
        font-size: 0.8rem;
    }

    .activity-item.clock-in .activity-icon {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .activity-item.clock-out .activity-icon {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin-bottom: 0.25rem;
    }

    .activity-type {
        font-size: 0.85rem;
        color: #0f172a;
    }

    .activity-type strong {
        font-weight: 700;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .activity-time i {
        font-size: 0.7rem;
    }

    .activity-details {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .activity-location {
        font-size: 0.7rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .activity-location i {
        font-size: 0.65rem;
        color: var(--primary-teal);
    }

    .activity-duration {
        font-size: 0.7rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        background: #e2e8f0;
        padding: 0.1rem 0.6rem;
        border-radius: 30px;
    }

    .activity-duration i {
        font-size: 0.65rem;
        color: #8b5cf6;
    }

    .activity-status {
        flex-shrink: 0;
        padding-top: 0.2rem;
    }

    .status-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .status-dot.completed {
        background: #22c55e;
        border: 2px solid #86efac;
    }

    .status-dot.active {
        background: #f59e0b;
        border: 2px solid #fcd34d;
        animation: pulse-dot 1.5s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(0.85); }
    }

    .activity-empty {
        text-align: center;
        padding: 2rem 0;
        color: #94a3b8;
    }

    .activity-empty i {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        color: #cbd5e1;
    }

    .activity-empty p {
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
    }

    /* ===== MODAL ===== */
    .modal-content {
        border-radius: 24px;
        border: 1px solid var(--glass-border);
        box-shadow: 0px 20px 60px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
    }

    .modal-header-custom {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        border: none;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header-custom .modal-title {
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-header-custom .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-header-custom .btn-close:hover {
        opacity: 1;
    }

    .modal-body-custom {
        padding: 1.5rem;
        max-height: 600px;
        overflow-y: auto;
        background: #fafefa;
    }

    .modal-body-custom::-webkit-scrollbar {
        width: 4px;
    }

    .modal-body-custom::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .modal-body-custom::-webkit-scrollbar-thumb {
        background: var(--primary-teal);
        border-radius: 10px;
    }

    .loading-state {
        text-align: center;
        padding: 2rem 0;
    }

    .loading-state .spinner-border {
        width: 2.5rem;
        height: 2.5rem;
        color: var(--primary-teal);
    }

    .loading-state p {
        margin-top: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* ===== DROPDOWN ===== */
    .dropdown-menu {
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        padding: 0.5rem;
        box-shadow: var(--card-shadow);
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #0f172a;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dropdown-item i {
        font-size: 0.9rem;
        color: #64748b;
    }

    .dropdown-item:hover {
        background: #f1f5f9;
        color: var(--primary-teal);
    }

    .dropdown-item.text-danger i {
        color: #ef4444;
    }

    .dropdown-item.text-danger:hover {
        background: #fee2e2;
        color: #991b1b;
    }

    .dropdown-item.text-danger:hover i {
        color: #991b1b;
    }

    .dropdown-divider {
        border-color: #e2e8f0;
        margin: 0.3rem 0;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .attendance-details-container {
            padding: 1.5rem 1.25rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .attendance-details-container {
            padding: 1rem;
        }

        .summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .summary-item {
            padding: 0.5rem 0.6rem;
        }

        .summary-value {
            font-size: 0.8rem;
        }

        .summary-icon {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .activity-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .activity-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.3rem;
        }

        .activity-item {
            padding: 0.6rem 0.6rem;
        }

        .info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.1rem;
        }

        .info-value {
            text-align: left;
        }

        .card-header-custom,
        .summary-header,
        .activity-header {
            padding: 0.7rem 1rem;
        }

        .card-body-custom {
            padding: 1rem;
        }

        .activity-timeline {
            padding: 1rem;
        }

        .modal-body-custom {
            padding: 1rem;
            max-height: 400px;
        }
    }

    @media (max-width: 480px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .summary-item {
            padding: 0.4rem 0.6rem;
        }

        .summary-icon {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }

        .summary-value {
            font-size: 0.75rem;
        }

        .info-label {
            font-size: 0.7rem;
        }

        .info-value {
            font-size: 0.75rem;
        }

        .activity-type {
            font-size: 0.75rem;
        }

        .activity-time {
            font-size: 0.65rem;
        }

        .activity-location,
        .activity-duration {
            font-size: 0.6rem;
        }

        .status-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.6rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-details-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .info-card,
    html[data-pms-theme="dark"] .summary-card,
    html[data-pms-theme="dark"] .activity-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .card-header-custom,
    html[data-pms-theme="dark"] .summary-header,
    html[data-pms-theme="dark"] .activity-header {
        background: linear-gradient(135deg, #183026, #102119);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .card-header-custom h6,
    html[data-pms-theme="dark"] .summary-header h6,
    html[data-pms-theme="dark"] .activity-header h6 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .info-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .info-value {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .info-row {
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .summary-item {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .summary-value {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .summary-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .summary-icon.clock-in {
        background: #064e3b;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .summary-icon.clock-out {
        background: #1e3a8a;
        color: #93bbfc;
    }

    html[data-pms-theme="dark"] .summary-icon.duration {
        background: #2e1065;
        color: #a78bfa;
    }

    html[data-pms-theme="dark"] .summary-icon.status {
        background: #451a03;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .status-badge.present {
        background: #064e3b;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .status-badge.late {
        background: #451a03;
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .status-badge.halfday {
        background: #2e1065;
        color: #a78bfa;
    }

    html[data-pms-theme="dark"] .activity-item {
        background: #183026;
    }

    html[data-pms-theme="dark"] .activity-item:hover {
        background: #102119;
    }

    html[data-pms-theme="dark"] .activity-type {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .activity-time {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .activity-location {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .activity-duration {
        background: #102119;
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .activity-count {
        background: #102119;
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .btn-action-dropdown {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .btn-action-dropdown:hover {
        background: #102119;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .dropdown-menu {
        background: rgba(16, 33, 25, 0.98);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .dropdown-item {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .dropdown-item i {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .dropdown-item:hover {
        background: #183026;
        color: #34d399;
    }

    html[data-pms-theme="dark"] .dropdown-divider {
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .modal-content {
        background: rgba(16, 33, 25, 0.98);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .modal-header-custom {
        background: linear-gradient(135deg, #0a5a3a, #0f744c);
    }

    html[data-pms-theme="dark"] .modal-body-custom {
        background: #102119;
    }

    html[data-pms-theme="dark"] .loading-state p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .activity-empty {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .activity-empty i {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .text-warning {
        color: #fbbf24 !important;
    }

    html[data-pms-theme="dark"] .activity-timeline::-webkit-scrollbar-track {
        background: #102119;
    }

    html[data-pms-theme="dark"] .activity-timeline::-webkit-scrollbar-thumb {
        background: #34d399;
    }

    html[data-pms-theme="dark"] .modal-body-custom::-webkit-scrollbar-track {
        background: #102119;
    }

    html[data-pms-theme="dark"] .modal-body-custom::-webkit-scrollbar-thumb {
        background: #34d399;
    }

    /* Z-INDEX FIXES */
    .modal-backdrop.show:nth-of-type(2) {
        z-index: 1155;
    }

    #editAttendanceModal {
        z-index: 1160 !important;
    }

    #attendanceDetailsModal {
        z-index: 1155 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dropdowns
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
            new bootstrap.Dropdown(el);
        });
    });

    // ===== EDIT ATTENDANCE =====
    $(document).off('click.attendanceSummaryEdit', '.edit-attendance-summary')
        .on('click.attendanceSummaryEdit', '.edit-attendance-summary', function(e) {
        e.preventDefault();

        var attendanceId = $(this).data('attendance-id');
        var userId = $(this).data('user-id');
        var date = $(this).data('date');

        var url = "{{ url('attendance/edit') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        // Remove existing modal if any
        $('#editAttendanceModal').remove();

        var editModalHtml = `
            <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" style="z-index: 2000;">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header-custom">
                            <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Attendance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body-custom" id="editAttendanceBody">
                            <div class="loading-state">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p>Loading attendance form...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(editModalHtml);

        var editModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
        editModal.show();

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#editAttendanceBody').html(response);
            },
            error: function() {
                $('#editAttendanceBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading attendance form. Please try again.
                    </div>
                `);
            }
        });
    });

    // ===== DELETE ATTENDANCE =====
    $(document).off('click.attendanceSummaryDelete', '.delete-attendance-summary')
        .on('click.attendanceSummaryDelete', '.delete-attendance-summary', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this attendance record?')) return;

        var attendanceId = $(this).data('attendance-id');

        $.ajax({
            url: "{{ url('attendance') }}/" + attendanceId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Show success message
                var alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Attendance record deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('.attendance-details-container .content-wrapper').prepend(alertHtml);

                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function() {
                alert('Error deleting attendance record. Please try again.');
            }
        });
    });
</script>
