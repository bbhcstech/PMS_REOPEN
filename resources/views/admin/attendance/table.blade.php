{{-- resources/views/admin/attendance/table.blade.php --}}
@php $authUser = Auth::user(); @endphp

<div class="table-wrapper position-relative" style="margin-top:12px;">
  <div class="table-responsive">
    <table id="attendanceTable" class="table table-bordered align-middle text-center attendance-table">
      <thead class="table-light">
        <tr>
          <th class="no-export" data-pms-selection-column aria-label="Select rows"></th>
          <th class="attendance-col-emp" style="min-width:240px;">Employee</th>
          @for($i=1;$i<=$daysInMonth;$i++)
            @php $date = \Carbon\Carbon::createFromDate($year,$month,$i); @endphp
            <th style="font-size:16px; width:56px;">{{ $i }}<br><small>{{ $date->format('D') }}</small></th>
          @endfor
          <th class="attendance-col-hours" style="min-width:140px;">Total Hours</th>
          <th class="no-export attendance-col-actions" style="min-width:260px;">Actions</th>
        </tr>
      </thead>

      <tbody>
        @foreach($users as $user)
          <tr>
            <td class="no-export" data-pms-selection-column></td>
            <td class="text-start attendance-cell-emp" style="white-space:nowrap;">
              <div class="attendance-employee-cell">
                <img src="{{ $user->profile_image ? asset($user->profile_image) : asset('images/default-avatar.png') }}"
                     alt="{{ $user->name }}"
                     class="attendance-employee-photo"
                     onerror="this.onerror=null; this.src='{{ asset('admin/assets/img/avatars/1.png') }}';">
                <div class="attendance-employee-meta">
                  <strong>{{ $user->name }}</strong>
                  <small>{{ $user->employeeDetail->designation->name ?? '-' }}</small>
                </div>
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
                $cellDate = \Carbon\Carbon::parse($dateKey);
                $isRealAttendanceCell = $attendance instanceof \App\Models\Attendance;
                $isAutoSaturday = !$isRealAttendanceCell && $cellDate->isSaturday();
                $isAutoSunday = !$isRealAttendanceCell && $cellDate->isSunday();
                $status = strtolower($attendance->status ?? '');
                $durationFlag = strtolower($attendance->duration ?? '');
                $statusIconClass = 'attendance-status-icon';
                $symbol = '-';
                $popupClass = '';
                $rowSeconds = 0;
                $today = \Carbon\Carbon::now()->format('Y-m-d');

                if ($isAutoSaturday) {
                  $symbol = "<span class='{$statusIconClass} wfh' data-bs-toggle='tooltip' title='Saturday - Work From Home'><i class='fas fa-laptop-house'></i></span>";
                  $popupClass = '';
                } elseif ($isAutoSunday) {
                  $symbol = "<span class='{$statusIconClass} holiday' data-bs-toggle='tooltip' title='Sunday Holiday'><i class='fas fa-star'></i></span>";
                  $popupClass = '';
                } elseif ($status === 'holiday') {
                  $occ = $attendance->occassion ?? 'Holiday';
                  $symbol = "<span class='{$statusIconClass} holiday' data-bs-toggle='tooltip' title='{$occ}'><i class='fas fa-star'></i></span>";
                  $popupClass = '';
                } elseif ($dateKey <= $today) {
                  switch ($status) {
                    case 'present':
                      $symbol = "<span class='{$statusIconClass} present' data-bs-toggle='tooltip' title='Present'><i class='fas fa-check'></i></span>";
                      $presentCount++;
                      $popupClass = 'view-attendance';
                      break;
                    case 'absent':
                      $symbol = "<span class='{$statusIconClass} absent' data-bs-toggle='tooltip' title='Absent'><i class='fas fa-times'></i></span>";
                      $popupClass = 'edit-attendance';
                      break;
                    case 'late':
                      $symbol = "<span class='{$statusIconClass} late' data-bs-toggle='tooltip' title='Late'><i class='fas fa-clock'></i></span>";
                      $presentCount++;
                      $popupClass = 'view-attendance';
                      break;
                    case 'half_day':
                      $symbol = "<span class='{$statusIconClass} halfday' data-bs-toggle='tooltip' title='Half Day'><i class='fas fa-star-half-alt'></i></span>";
                      $presentCount += 0.5;
                      $popupClass = 'view-attendance';
                      break;
                    case 'leave':
                      $lr = $attendance->reason ?? 'On Leave';
                      if ($durationFlag === 'full-day') {
                        $symbol = "<span class='{$statusIconClass} leave' data-bs-toggle='tooltip' title='{$lr}'><i class='fas fa-plane-departure'></i></span>";
                      } else {
                        $symbol = "<span class='{$statusIconClass} halfday' data-bs-toggle='tooltip' title='{$lr} (Half Day)'><i class='fas fa-star-half-alt'></i></span>";
                        $presentCount += 0.5;
                      }
                      $popupClass = '';
                      break;
                    case 'day_off':
                    case 'dayoff':
                      $symbol = "<span class='{$statusIconClass} dayoff' data-bs-toggle='tooltip' title='Day Off'><i class='fas fa-calendar'></i></span>";
                      $popupClass = '';
                      break;
                    default:
                      $symbol = "<span class='{$statusIconClass} empty' data-bs-toggle='tooltip' title='No Record'>-</span>";
                      $popupClass = '';
                  }
                } else {
                  if ($status === 'leave') {
                    if ($durationFlag === 'full-day') {
                      $symbol = "<span class='{$statusIconClass} leave' data-bs-toggle='tooltip' title='Planned Leave'><i class='fas fa-plane-departure'></i></span>";
                    } else {
                      $symbol = "<span class='{$statusIconClass} halfday' data-bs-toggle='tooltip' title='Planned Half Day'><i class='fas fa-star-half-alt'></i></span>";
                    }
                  } elseif ($status === 'holiday') {
                    $symbol = "<span class='{$statusIconClass} holiday' data-bs-toggle='tooltip' title='Holiday'><i class='fas fa-star'></i></span>";
                  } elseif ($status === 'day_off' || $status === 'dayoff') {
                    $symbol = "<span class='{$statusIconClass} dayoff' data-bs-toggle='tooltip' title='Day Off'><i class='fas fa-calendar'></i></span>";
                  } else {
                    $symbol = "<span class='{$statusIconClass} empty' data-bs-toggle='tooltip' title='Upcoming'>-</span>";
                  }
                  $popupClass = '';
                }

                if ($isRealAttendanceCell) {
                  $rowSeconds = (int) ($attendance->total_seconds ?? 0);
                }

                $totalSeconds += (int)$rowSeconds;
              @endphp

              <td class="fw-bold">
                @php $canEditAttendance = in_array(strtolower((string)(auth()->user()->role ?? '')), ['admin', 'superadmin', 'administrator', 'hr'], true); @endphp

                @if($popupClass === 'view-attendance')
                  <a href="javascript:;" class="view-attendance" data-attendance-id="{{ $attendance->id ?? '' }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}">
                    {!! $symbol !!}
                  </a>
                @elseif($popupClass === 'edit-attendance' && $canEditAttendance)
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
              $monthRecords = [];
              for ($d=1; $d<=$daysInMonth; $d++) {
                $dateKey = \Carbon\Carbon::createFromDate($year,$month,$d)->format('Y-m-d');
                $dayRecord = $attendanceMap[$user->id][$dateKey] ?? null;
                $isRealAttendance = $dayRecord instanceof \App\Models\Attendance;
                $monthCellDate = \Carbon\Carbon::parse($dateKey);
                $autoWeekendStatus = !$isRealAttendance && $monthCellDate->isSaturday()
                  ? 'Work From Home'
                  : (!$isRealAttendance && $monthCellDate->isSunday() ? 'Holiday' : null);
                $daySeconds = $isRealAttendance ? (int) ($dayRecord->total_seconds ?? 0) : 0;
                $monthRecords[] = [
                  'date' => $dateKey,
                  'day' => \Carbon\Carbon::parse($dateKey)->format('d D'),
                  'attendance_id' => $isRealAttendance ? $dayRecord->id : null,
                  'status' => $autoWeekendStatus ?? ucfirst(str_replace('_', ' ', strtolower($dayRecord->status ?? 'absent'))),
                  'clock_in' => $isRealAttendance && $dayRecord->clock_in ? \Carbon\Carbon::parse($dayRecord->clock_in)->format('h:i A') : '-',
                  'clock_out' => $isRealAttendance && $dayRecord->clock_out ? \Carbon\Carbon::parse($dayRecord->clock_out)->format('h:i A') : '-',
                  'total' => $isRealAttendance ? sprintf('%02d:%02d:%02d', intdiv($daySeconds, 3600), intdiv($daySeconds % 3600, 60), $daySeconds % 60) : '-',
                  'note' => $autoWeekendStatus ? ($monthCellDate->isSaturday() ? 'Auto Saturday WFH' : 'Auto Sunday Holiday') : ($dayRecord->occassion ?? $dayRecord->reason ?? ''),
                ];
              }
              $employeeMonthPayload = [
                'user_id' => $user->id,
                'name' => $user->name,
                'designation' => $user->employeeDetail->designation->name ?? '-',
                'photo' => $user->profile_image ? asset($user->profile_image) : asset('images/default-avatar.png'),
                'month' => (int) $month,
                'year' => (int) $year,
                'month_name' => \Carbon\Carbon::createFromDate($year, $month)->format('F Y'),
                'total_hours' => $total_human,
                'present_count' => $presentCount,
                'days_in_month' => $daysInMonth,
                'records' => $monthRecords,
              ];
              $employeeMonthPayloadEncoded = base64_encode(json_encode($employeeMonthPayload));
            @endphp

            <td class="fw-bold text-primary">
              {{ $total_human }}
              <div class="small text-muted">({{ $presentCount }} / {{ $daysInMonth }})</div>
            </td>
            <td class="no-export">
              <div class="attendance-row-actions">
                <button type="button" class="attendance-action-btn view js-month-view" data-payload="{{ $employeeMonthPayloadEncoded }}" title="View month details">
                  <i class="fas fa-eye"></i>
                  <span>View</span>
                </button>
                @if($canEditAttendance)
                  <button type="button" class="attendance-action-btn edit js-month-edit" data-payload="{{ $employeeMonthPayloadEncoded }}" title="Edit month records">
                    <i class="fas fa-pen"></i>
                    <span>Edit</span>
                  </button>
                  <button type="button" class="attendance-action-btn archive js-month-archive" data-payload="{{ $employeeMonthPayloadEncoded }}" title="Archive month records">
                    <i class="fas fa-box-archive"></i>
                    <span>Archive</span>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>



{{-- small styles --}}
<style>
  .half-star { display:inline-block; width:18px; height:18px; }
  .leave-plane { display:inline-block; padding:2px 6px; border-radius:4px; font-size:14px; }
  .attendance-employee-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 240px;
  }
  .attendance-employee-photo {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #ffffff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
    flex-shrink: 0;
    background: #e2e8f0;
  }
  .attendance-employee-meta {
    display: flex;
    flex-direction: column;
    gap: 0.12rem;
    min-width: 0;
  }
  .attendance-employee-meta strong {
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    font-size: 1.12rem;
    line-height: 1.2;
  }
  .attendance-employee-meta small {
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    font-size: 0.96rem;
    line-height: 1.2;
  }
  .attendance-row-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    min-width: 250px;
  }
  .attendance-action-btn {
    min-width: 72px;
    height: 40px;
    border: 0;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.38rem;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 0.92rem;
    font-weight: 800;
    transition: all 0.2s ease;
  }
  .attendance-action-btn i,
  .attendance-action-btn span {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
  }
  .attendance-action-btn:disabled {
    cursor: not-allowed;
    opacity: 0.72;
    transform: none;
    box-shadow: none;
  }
  .attendance-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
  }
  .attendance-action-btn.view {
    background: #2563eb !important;
  }
  .attendance-action-btn.edit {
    background: #f59e0b !important;
  }
  .attendance-action-btn.archive {
    background: #64748b !important;
  }
  .attendance-status-icon {
    width: 34px;
    height: 34px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 1rem;
    font-weight: 800;
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.12);
  }
  .attendance-status-icon i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
  }
  .attendance-status-icon.present {
    background: #22c55e !important;
  }
  .attendance-status-icon.absent {
    background: #ef4444 !important;
  }
  .attendance-status-icon.late {
    background: #f59e0b !important;
  }
  .attendance-status-icon.halfday {
    background: #8b5cf6 !important;
  }
  .attendance-status-icon.holiday {
    background: #e67e22 !important;
  }
  .attendance-status-icon.dayoff {
    background: #3b82f6 !important;
  }
  .attendance-status-icon.leave {
    background: #06b6d4 !important;
  }
  .attendance-status-icon.wfh {
    background: #14b8a6 !important;
  }
  .attendance-status-icon.empty {
    background: #e2e8f0 !important;
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    box-shadow: none;
  }
  .attendance-table td a .attendance-status-icon {
    cursor: pointer;
  }
  .attendance-table td a:hover .attendance-status-icon {
    transform: translateY(-2px);
  }
  .attendance-month-summary {
    background: #f8fafc;
    padding: 1.25rem;
  }
  .attendance-month-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1rem;
    margin-bottom: 1rem;
  }
  .attendance-month-profile img {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    object-fit: cover;
  }
  .attendance-month-profile h5 {
    margin: 0;
    color: #0f172a !important;
    -webkit-text-fill-color: #0f172a !important;
    font-weight: 800;
    font-size: 1.25rem;
  }
  .attendance-month-profile p {
    margin: 0.2rem 0 0;
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    font-weight: 600;
    font-size: 1rem;
  }
  .attendance-month-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.45rem;
  }
  .attendance-month-table th {
    color: #475569 !important;
    -webkit-text-fill-color: #475569 !important;
    font-size: 0.95rem;
    text-transform: uppercase;
    padding: 0.65rem;
  }
  .attendance-month-table td {
    background: #ffffff;
    color: #1e293b !important;
    -webkit-text-fill-color: #1e293b !important;
    padding: 0.85rem;
    font-size: 1rem;
    border-top: 1px solid #eef2f7;
    border-bottom: 1px solid #eef2f7;
  }
  .attendance-month-table td:first-child {
    border-left: 1px solid #eef2f7;
    border-radius: 12px 0 0 12px;
  }
  .attendance-month-table td:last-child {
    border-right: 1px solid #eef2f7;
    border-radius: 0 12px 12px 0;
  }
  .month-edit-day-btn {
    border: 0;
    border-radius: 999px;
    padding: 0.45rem 0.9rem;
    background: #fff7ed !important;
    color: #c2410c !important;
    -webkit-text-fill-color: #c2410c !important;
    font-weight: 800;
    font-size: 0.95rem;
  .attendance-col-emp,
  .attendance-col-hours,
  .attendance-col-actions {
    background-color: #f8fafc !important;
    color: #475569 !important;
  }
  .attendance-cell-emp {
    background-color: #ffffff !important;
  }
  #attendanceDetailsModal {
    z-index: 2060 !important;
  }
  #attendanceDetailsModal,
  #attendanceDetailsModal * {
    pointer-events: auto;
  }
  #attendanceDetailsModal .modal-dialog {
    max-width: min(1180px, calc(100vw - 2rem));
  }
  #attendanceDetailsModal .attendance-details-modal-content {
    background: #ffffff !important;
    opacity: 1 !important;
    border: 0;
    border-radius: 18px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
    overflow: hidden;
  }
  #attendanceDetailsModal .attendance-details-modal-body {
    background: #ffffff;
    padding: 0;
    max-height: calc(100vh - 11rem);
    overflow-y: auto;
  }
  #attendanceDetailsModal .attendance-details-container {
    min-height: auto;
    padding: 1.25rem;
    background: #f8fafc;
    overflow: visible;
  }
  #attendanceDetailsModal .attendance-details-container .ambient-orb {
    display: none;
  }
  .modal-backdrop.show {
    opacity: 0.48 !important;
    z-index: 2050 !important;
  }

  /* Dark mode overrides for table & modal */
  html[data-pms-theme="dark"] .attendance-col-emp,
  html[data-pms-theme="dark"] .attendance-col-hours,
  html[data-pms-theme="dark"] .attendance-col-actions,
  html[data-theme="dark"] .attendance-col-emp,
  html[data-theme="dark"] .attendance-col-hours,
  html[data-theme="dark"] .attendance-col-actions,
  body.dark-mode .attendance-col-emp,
  body.dark-mode .attendance-col-hours,
  body.dark-mode .attendance-col-actions {
    background-color: #0F1530 !important;
    color: #9AA3C7 !important;
    -webkit-text-fill-color: #9AA3C7 !important;
    border-color: rgba(238, 241, 251, 0.08) !important;
  }
  html[data-pms-theme="dark"] .attendance-cell-emp,
  html[data-theme="dark"] .attendance-cell-emp,
  body.dark-mode .attendance-cell-emp {
    background-color: #0F1530 !important;
    border-color: rgba(238, 241, 251, 0.08) !important;
  }
  html[data-pms-theme="dark"] .attendance-table th,
  html[data-theme="dark"] .attendance-table th,
  html[data-bs-theme="dark"] .attendance-table th,
  [data-pms-theme="dark"] .attendance-table th,
  [data-theme="dark"] .attendance-table th,
  body.dark-mode .attendance-table th {
    background-color: #0F1530 !important;
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
    background-color: #141B3D !important;
    color: #CBD5E1 !important;
    -webkit-text-fill-color: #CBD5E1 !important;
    border-color: rgba(238, 241, 251, 0.08) !important;
  }
  html[data-pms-theme="dark"] .attendance-table tbody tr:hover td,
  html[data-theme="dark"] .attendance-table tbody tr:hover td,
  body.dark-mode .attendance-table tbody tr:hover td {
    background-color: #1A2247 !important;
  }
  html[data-pms-theme="dark"] .attendance-employee-meta strong,
  html[data-theme="dark"] .attendance-employee-meta strong,
  html[data-bs-theme="dark"] .attendance-employee-meta strong,
  [data-pms-theme="dark"] .attendance-employee-meta strong,
  [data-theme="dark"] .attendance-employee-meta strong,
  body.dark-mode .attendance-employee-meta strong {
    color: #EEF1FB !important;
    -webkit-text-fill-color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .attendance-employee-meta small,
  html[data-theme="dark"] .attendance-employee-meta small,
  html[data-bs-theme="dark"] .attendance-employee-meta small,
  [data-pms-theme="dark"] .attendance-employee-meta small,
  [data-theme="dark"] .attendance-employee-meta small,
  body.dark-mode .attendance-employee-meta small {
    color: #9AA3C7 !important;
    -webkit-text-fill-color: #9AA3C7 !important;
  }
  html[data-pms-theme="dark"] .attendance-employee-photo,
  html[data-theme="dark"] .attendance-employee-photo,
  html[data-bs-theme="dark"] .attendance-employee-photo,
  [data-pms-theme="dark"] .attendance-employee-photo,
  [data-theme="dark"] .attendance-employee-photo,
  body.dark-mode .attendance-employee-photo {
    border-color: rgba(238, 241, 251, 0.16) !important;
    background: #141B3D !important;
  }
  html[data-pms-theme="dark"] .attendance-month-summary,
  html[data-theme="dark"] .attendance-month-summary,
  html[data-bs-theme="dark"] .attendance-month-summary,
  [data-pms-theme="dark"] .attendance-month-summary,
  [data-theme="dark"] .attendance-month-summary,
  body.dark-mode .attendance-month-summary {
    background: #070B1A !important;
  }
  html[data-pms-theme="dark"] .attendance-month-profile,
  html[data-theme="dark"] .attendance-month-profile,
  html[data-bs-theme="dark"] .attendance-month-profile,
  [data-pms-theme="dark"] .attendance-month-profile,
  [data-theme="dark"] .attendance-month-profile,
  body.dark-mode .attendance-month-profile {
    background: #0F1530 !important;
    border-color: rgba(238, 241, 251, 0.1) !important;
  }
  html[data-pms-theme="dark"] .attendance-month-profile h5,
  html[data-theme="dark"] .attendance-month-profile h5,
  html[data-bs-theme="dark"] .attendance-month-profile h5,
  [data-pms-theme="dark"] .attendance-month-profile h5,
  [data-theme="dark"] .attendance-month-profile h5,
  body.dark-mode .attendance-month-profile h5 {
    color: #EEF1FB !important;
    -webkit-text-fill-color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .attendance-month-profile p,
  html[data-theme="dark"] .attendance-month-profile p,
  body.dark-mode .attendance-month-profile p {
    color: #9AA3C7 !important;
    -webkit-text-fill-color: #9AA3C7 !important;
  }
  html[data-pms-theme="dark"] .attendance-month-table th,
  html[data-theme="dark"] .attendance-month-table th,
  body.dark-mode .attendance-month-table th {
    color: #9AA3C7 !important;
    -webkit-text-fill-color: #9AA3C7 !important;
  }
  html[data-pms-theme="dark"] .attendance-month-table td,
  html[data-theme="dark"] .attendance-month-table td,
  html[data-bs-theme="dark"] .attendance-month-table td,
  [data-pms-theme="dark"] .attendance-month-table td,
  [data-theme="dark"] .attendance-month-table td,
  body.dark-mode .attendance-month-table td {
    background: #141B3D !important;
    border-color: rgba(238, 241, 251, 0.08) !important;
    color: #EEF1FB !important;
    -webkit-text-fill-color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] .attendance-status-icon.empty,
  html[data-theme="dark"] .attendance-status-icon.empty,
  html[data-bs-theme="dark"] .attendance-status-icon.empty,
  [data-pms-theme="dark"] .attendance-status-icon.empty,
  [data-theme="dark"] .attendance-status-icon.empty,
  body.dark-mode .attendance-status-icon.empty {
    background: #141B3D !important;
    color: #6B739A !important;
    -webkit-text-fill-color: #6B739A !important;
  }
  /* Dark mode modal fixes */
  html[data-pms-theme="dark"] #attendanceDetailsModal .attendance-details-modal-content,
  html[data-theme="dark"] #attendanceDetailsModal .attendance-details-modal-content,
  body.dark-mode #attendanceDetailsModal .attendance-details-modal-content {
    background: #0F1530 !important;
    border: 1px solid rgba(238, 241, 251, 0.12) !important;
    color: #CBD5E1 !important;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.7) !important;
  }
  html[data-pms-theme="dark"] #attendanceDetailsModal .attendance-details-modal-body,
  html[data-theme="dark"] #attendanceDetailsModal .attendance-details-modal-body,
  body.dark-mode #attendanceDetailsModal .attendance-details-modal-body {
    background: #0F1530 !important;
    color: #CBD5E1 !important;
  }
  html[data-pms-theme="dark"] #attendanceDetailsModal .attendance-details-container,
  html[data-theme="dark"] #attendanceDetailsModal .attendance-details-container,
  body.dark-mode #attendanceDetailsModal .attendance-details-container {
    background: #070B1A !important;
    color: #CBD5E1 !important;
  }
  html[data-pms-theme="dark"] #attendanceDetailsModal .modal-header,
  html[data-theme="dark"] #attendanceDetailsModal .modal-header,
  body.dark-mode #attendanceDetailsModal .modal-header {
    background: #141B3D !important;
    border-bottom: 1px solid rgba(238, 241, 251, 0.09) !important;
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] #attendanceDetailsModal .modal-title,
  html[data-theme="dark"] #attendanceDetailsModal .modal-title,
  body.dark-mode #attendanceDetailsModal .modal-title {
    color: #EEF1FB !important;
  }
  html[data-pms-theme="dark"] #attendanceDetailsModal .modal-footer,
  html[data-theme="dark"] #attendanceDetailsModal .modal-footer,
  body.dark-mode #attendanceDetailsModal .modal-footer {
    background: #0F1530 !important;
    border-top: 1px solid rgba(238, 241, 251, 0.09) !important;
  }
</style>

