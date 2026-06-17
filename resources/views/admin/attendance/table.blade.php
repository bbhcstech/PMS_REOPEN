{{-- resources/views/admin/attendance/table.blade.php --}}
@php $authUser = Auth::user(); @endphp

<div class="table-wrapper position-relative" style="margin-top:12px;">
  <div class="table-responsive">
    <table id="attendanceTable" class="table table-bordered align-middle text-center attendance-table">
      <thead class="table-light">
        <tr>
          <th class="no-export" style="background-color:#f0f0f0; width:54px;">
            <input type="checkbox" id="attendanceSelectAll" class="attendance-checkbox" aria-label="Select all attendance rows">
          </th>
          <th style="background-color:#f0f0f0; min-width:240px;">Employee</th>
          @for($i=1;$i<=$daysInMonth;$i++)
            @php $date = \Carbon\Carbon::createFromDate($year,$month,$i); @endphp
            <th style="font-size:16px; width:56px;">{{ $i }}<br><small>{{ $date->format('D') }}</small></th>
          @endfor
          <th style="background-color:#f0f0f0; min-width:140px;">Total Hours</th>
          <th class="no-export" style="background-color:#f0f0f0; min-width:260px;">Actions</th>
        </tr>
      </thead>

      <tbody>
        @foreach($users as $user)
          <tr>
            <td class="no-export">
              <input type="checkbox" class="attendance-checkbox attendance-row-checkbox" value="{{ $user->id }}" aria-label="Select {{ $user->name }}">
            </td>
            <td class="text-start" style="background-color:#f9f9f9; white-space:nowrap;">
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
                @if(auth()->user()->role === 'admin')
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

{{-- Modal: kept here so clicks can open it --}}
<div class="modal fade attendance-details-modal" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content attendance-details-modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Attendance Details</h5>
        <button type="button" class="btn-close" data-attendance-modal-close aria-label="Close"></button>
      </div>
      <div id="attendanceDetailsBody" class="modal-body attendance-details-modal-body">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-attendance-modal-close>Close</button>
      </div>
    </div>
  </div>
</div>

{{-- small styles --}}
<style>
  .half-star { display:inline-block; width:18px; height:18px; }
  .leave-plane { display:inline-block; padding:2px 6px; border-radius:4px; font-size:14px; }
  .attendance-checkbox {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    cursor: pointer;
    accent-color: #0ea5a4;
  }
  #attendanceTable tbody tr.attendance-row-selected td {
    background-color: #f0f9ff !important;
  }
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
    color: #0f172a;
    font-size: 1.12rem;
    line-height: 1.2;
  }
  .attendance-employee-meta small {
    color: #64748b;
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
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 800;
    transition: all 0.2s ease;
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
    background: #2563eb;
  }
  .attendance-action-btn.edit {
    background: #f59e0b;
  }
  .attendance-action-btn.archive {
    background: #64748b;
  }
  .attendance-status-icon {
    width: 34px;
    height: 34px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1rem;
    font-weight: 800;
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.12);
  }
  .attendance-status-icon.present {
    background: #22c55e;
  }
  .attendance-status-icon.absent {
    background: #ef4444;
  }
  .attendance-status-icon.late {
    background: #f59e0b;
  }
  .attendance-status-icon.halfday {
    background: #8b5cf6;
  }
  .attendance-status-icon.holiday {
    background: #e67e22;
  }
  .attendance-status-icon.dayoff {
    background: #3b82f6;
  }
  .attendance-status-icon.leave {
    background: #06b6d4;
  }
  .attendance-status-icon.wfh {
    background: #14b8a6;
  }
  .attendance-status-icon.empty {
    background: #e2e8f0;
    color: #64748b;
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
    color: #0f172a;
    font-weight: 800;
    font-size: 1.25rem;
  }
  .attendance-month-profile p {
    margin: 0.2rem 0 0;
    color: #64748b;
    font-weight: 600;
    font-size: 1rem;
  }
  .attendance-month-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.45rem;
  }
  .attendance-month-table th {
    color: #64748b;
    font-size: 0.95rem;
    text-transform: uppercase;
    padding: 0.65rem;
  }
  .attendance-month-table td {
    background: #ffffff;
    color: #1e293b;
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
    background: #fff7ed;
    color: #c2410c;
    font-weight: 800;
    font-size: 0.95rem;
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
  html[data-pms-theme="dark"] .attendance-employee-meta strong {
    color: #ffffff;
  }
  html[data-pms-theme="dark"] .attendance-employee-meta small {
    color: #8ba198;
  }
  html[data-pms-theme="dark"] .attendance-employee-photo {
    border-color: #183026;
    background: #183026;
  }
  html[data-pms-theme="dark"] .attendance-month-summary {
    background: #07130d;
  }
  html[data-pms-theme="dark"] .attendance-month-profile,
  html[data-pms-theme="dark"] .attendance-month-table td {
    background: #102119;
    border-color: rgba(122, 240, 181, 0.15);
  }
  html[data-pms-theme="dark"] .attendance-month-profile h5,
  html[data-pms-theme="dark"] .attendance-month-table td {
    color: #ffffff;
  }
  html[data-pms-theme="dark"] .attendance-status-icon.empty {
    background: #183026;
    color: #8ba198;
  }
</style>

@push('js')
<script>
$(document).ready(function () {
    function getAttendanceModal() {
        var modalEl = document.getElementById('attendanceDetailsModal');
        if (!modalEl) return null;

        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        return bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
    }

    function showAttendanceLoading(message) {
        $('#attendanceDetailsBody').html(
            '<div class="text-center py-5 bg-white">' +
                '<div class="spinner-border text-primary" role="status">' +
                    '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<p class="mt-3 mb-0 text-muted">' + message + '</p>' +
            '</div>'
        );
    }

    function cleanModalState(force = false) {
        if (force || $('.modal.show').length === 0) {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                overflow: '',
                paddingRight: ''
            });
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    }

    function closeAttendanceModal() {
        var modalEl = document.getElementById('attendanceDetailsModal');
        if (!modalEl) {
            cleanModalState(true);
            return;
        }

        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }

        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.removeAttribute('aria-modal');
        modalEl.style.display = 'none';
        $('#attendanceDetailsBody').html('');
        $('#editAttendanceModal').remove();
        cleanModalState(true);
    }

    function escapeAttendanceHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function parseAttendancePayload(encoded) {
        var binary = atob(encoded);
        var bytes = new Uint8Array(binary.length);
        for (var i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }

        if (window.TextDecoder) {
            return JSON.parse(new TextDecoder('utf-8').decode(bytes));
        }

        return JSON.parse(decodeURIComponent(escape(binary)));
    }

    function renderEmployeeMonthDetails(payload, editMode) {
        var rows = (payload.records || []).map(function(record) {
            var editButton = '';
            if (editMode) {
                editButton = '<button type="button" class="month-edit-day-btn edit-attendance" ' +
                    'data-attendance-id="' + escapeAttendanceHtml(record.attendance_id || '') + '" ' +
                    'data-user-id="' + escapeAttendanceHtml(payload.user_id) + '" ' +
                    'data-date="' + escapeAttendanceHtml(record.date) + '">' +
                    '<i class="fas fa-pen me-1"></i>Edit</button>';
            }

            return '<tr>' +
                '<td><strong>' + escapeAttendanceHtml(record.day) + '</strong><div class="small text-muted">' + escapeAttendanceHtml(record.date) + '</div></td>' +
                '<td>' + escapeAttendanceHtml(record.status) + '</td>' +
                '<td>' + escapeAttendanceHtml(record.clock_in) + '</td>' +
                '<td>' + escapeAttendanceHtml(record.clock_out) + '</td>' +
                '<td>' + escapeAttendanceHtml(record.total) + '</td>' +
                '<td>' + escapeAttendanceHtml(record.note || '-') + '</td>' +
                (editMode ? '<td class="text-center">' + editButton + '</td>' : '') +
            '</tr>';
        }).join('');

        var heading = editMode ? 'Edit Monthly Attendance' : 'Monthly Attendance Details';
        $('#attendanceDetailsModal .modal-title').text(heading);
        $('#attendanceDetailsBody').html(
            '<div class="attendance-month-summary">' +
                '<div class="attendance-month-profile">' +
                    '<img src="' + escapeAttendanceHtml(payload.photo) + '" alt="' + escapeAttendanceHtml(payload.name) + '">' +
                    '<div>' +
                        '<h5>' + escapeAttendanceHtml(payload.name) + '</h5>' +
                        '<p>' + escapeAttendanceHtml(payload.designation) + ' | ' + escapeAttendanceHtml(payload.month_name) +
                        ' | Total: ' + escapeAttendanceHtml(payload.total_hours) +
                        ' | Present: ' + escapeAttendanceHtml(payload.present_count) + '/' + escapeAttendanceHtml(payload.days_in_month) + '</p>' +
                    '</div>' +
                '</div>' +
                '<div class="table-responsive">' +
                    '<table class="attendance-month-table">' +
                        '<thead><tr>' +
                            '<th>Date</th><th>Status</th><th>Clock In</th><th>Clock Out</th><th>Total</th><th>Note</th>' +
                            (editMode ? '<th class="text-center">Action</th>' : '') +
                        '</tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );

        var modal = getAttendanceModal();
        if (modal) {
            modal.show();
        }
    }

    $(document).off('click.attendanceMonthView', '.js-month-view')
        .on('click.attendanceMonthView', '.js-month-view', function() {
        renderEmployeeMonthDetails(parseAttendancePayload(this.dataset.payload), false);
    });

    $(document).off('click.attendanceMonthEdit', '.js-month-edit')
        .on('click.attendanceMonthEdit', '.js-month-edit', function() {
        renderEmployeeMonthDetails(parseAttendancePayload(this.dataset.payload), true);
    });

    $(document).off('click.attendanceMonthArchive', '.js-month-archive')
        .on('click.attendanceMonthArchive', '.js-month-archive', function() {
        var archiveButton = $(this);
        var originalHtml = archiveButton.html();
        var payload = parseAttendancePayload(this.dataset.payload);
        var message = 'Archive attendance records for ' + payload.name + ' in ' + payload.month_name + '?\n\nThey will move out of the active attendance table and can be restored later.';
        if (!confirm(message)) {
            return;
        }

        archiveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i><span>Archiving</span>');

        $.ajax({
            url: "{{ route('attendance.month.archive') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                user_id: payload.user_id,
                month: payload.month,
                year: payload.year
            },
            success: function(response) {
                alert(response.message || 'Monthly attendance archived successfully.');
                window.location.reload();
            },
            error: function(xhr) {
                var message = 'Unable to archive monthly attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            },
            complete: function() {
                archiveButton.prop('disabled', false).html(originalHtml);
            }
        });
    });

    $(document).off('hidden.bs.modal.attendanceDetails', '#attendanceDetailsModal')
        .on('hidden.bs.modal.attendanceDetails', '#attendanceDetailsModal', function () {
        $('#attendanceDetailsBody').html('');
        $('#editAttendanceModal').remove();
        cleanModalState(true);
    });

    $(document).off('click.attendanceModalClose', '[data-attendance-modal-close]')
        .on('click.attendanceModalClose', '[data-attendance-modal-close]', function(e) {
        e.preventDefault();
        closeAttendanceModal();
    });

    $(document).off('click.attendanceModalBackdrop', '#attendanceDetailsModal')
        .on('click.attendanceModalBackdrop', '#attendanceDetailsModal', function(e) {
        if (e.target === this) {
            closeAttendanceModal();
        }
    });

    $(document).off('keydown.attendanceModalEscape')
        .on('keydown.attendanceModalEscape', function(e) {
        if (e.key === 'Escape' && $('#attendanceDetailsModal').hasClass('show')) {
            closeAttendanceModal();
        }
    });

    $(document).off('submit.attendanceModalForm', '#attendanceDetailsModal .attendance-form')
        .on('submit.attendanceModalForm', '#attendanceDetailsModal .attendance-form', function(e) {
        e.preventDefault();

        var form = this;
        var submitButton = $(form).find('[type="submit"]').first();
        var originalHtml = submitButton.html();

        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.action,
            type: form.method || 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function() {
                closeAttendanceModal();
                window.location.reload();
            },
            error: function(xhr) {
                var message = 'Unable to save attendance. Please check the form and try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $(form).prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalHtml);
                cleanModalState();
            }
        });
    });

    // Filter AJAX submit (optional; fallback to normal GET if disabled)
    $('#attendanceFilter').on('submit', function(e) {
        // allow default GET submit, but if you want ajax un-comment below
        // e.preventDefault();
        // $.ajax({...})
    });

    // view attendance modal
    $(document).off('click.attendanceView', '.view-attendance')
        .on('click.attendanceView', '.view-attendance', function(e) {
        e.preventDefault();
        var attendanceId = $(this).data('attendance-id');
        var userId = $(this).data('user-id');
        var date = $(this).data('date');

        var url = "{{ url('attendance/details') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        var modal = getAttendanceModal();
        if (!modal) return;

        $('#editAttendanceModal').remove();
        $('#attendanceDetailsModal .modal-title').text('Attendance Details');
        showAttendanceLoading('Loading attendance details...');
        modal.show();

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#attendanceDetailsBody').html(response);
            },
            error: function(xhr) {
                $('#attendanceDetailsBody').html('<div class="alert alert-danger m-4">Error loading attendance details.</div>');
            },
            complete: function() {
                cleanModalState();
            }
        });
    });

    // edit attendance modal for admins
    $(document).off('click.attendanceEdit', '.edit-attendance')
        .on('click.attendanceEdit', '.edit-attendance', function(e) {
        e.preventDefault();
        var attendanceId = $(this).data('attendance-id');
        var userId       = $(this).data('user-id');
        var date         = $(this).data('date');

        var url = "{{ url('attendance/edit') }}?attendance_id=" + attendanceId + "&user_id=" + userId + "&date=" + date;

        var modal = getAttendanceModal();
        if (!modal) return;

        $('#editAttendanceModal').remove();
        $('#attendanceDetailsModal .modal-title').text('Edit Attendance');
        showAttendanceLoading('Loading attendance form...');
        modal.show();

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#attendanceDetailsBody').html(response);
            },
            error: function(xhr) {
                $('#attendanceDetailsBody').html('<div class="alert alert-danger m-4">Error loading attendance form.</div>');
            },
            complete: function() {
                cleanModalState();
            }
        });
    });

    // tooltips
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
