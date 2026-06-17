<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Attendance Report</title>
    <style>
        /* ===== PREMIUM PDF REPORT STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #0f172a;
            background: #ffffff;
            padding: 30px 35px;
            line-height: 1.5;
        }

        /* ===== HEADER ===== */
        .report-header {
            text-align: center;
            padding-bottom: 18px;
            margin-bottom: 20px;
            border-bottom: 3px solid #0ea5a4;
            position: relative;
        }

        .report-header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #0ea5a4, #22c55e);
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #1e3a8a, #0ea5a4, #22c55e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .report-meta {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .report-meta .divider {
            color: #cbd5e1;
        }

        .report-meta strong {
            color: #0f172a;
            font-weight: 700;
        }

        .meta-badge {
            display: inline-block;
            padding: 2px 12px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        /* ===== SUMMARY STATS ===== */
        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 18px;
            padding: 10px 15px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10.5px;
            color: #475569;
        }

        .stat-item .stat-value {
            font-weight: 800;
            color: #0f172a;
            font-size: 13px;
        }

        .stat-item .stat-label {
            color: #64748b;
            font-weight: 500;
        }

        .stat-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .stat-dot.present { background: #22c55e; }
        .stat-dot.absent { background: #ef4444; }
        .stat-dot.late { background: #f59e0b; }
        .stat-dot.halfday { background: #8b5cf6; }
        .stat-dot.leave { background: #06b6d4; }
        .stat-dot.holiday { background: #e67e22; }
        .stat-dot.dayoff { background: #3b82f6; }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 10.5px;
        }

        th {
            padding: 10px 10px;
            border: 1px solid #d1d5db;
            text-align: left;
            vertical-align: middle;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            font-weight: 700;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b;
        }

        td {
            padding: 9px 10px;
            border: 1px solid #d1d5db;
            text-align: left;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #94a3b8;
            font-weight: 400;
        }

        .small-text {
            font-size: 9.5px;
            color: #64748b;
        }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: 0.2px;
        }

        .status-badge.present {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge.late {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.half_day {
            background: #ede9fe;
            color: #5b21b6;
        }

        .status-badge.leave {
            background: #cffafe;
            color: #0e7490;
        }

        .status-badge.holiday {
            background: #ffedd5;
            color: #9a3412;
        }

        .status-badge.dayoff {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ===== EXTRA NOTE ===== */
        .extra-note {
            font-size: 9px;
            color: #64748b;
            font-weight: 400;
            margin-left: 4px;
        }

        .extra-note i {
            font-style: normal;
            background: #f1f5f9;
            padding: 0 6px;
            border-radius: 4px;
        }

        /* ===== TOTALS ROW ===== */
        .totals-row td {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            font-weight: 800;
            font-size: 11px;
            color: #0f172a;
            border-top: 2px solid #0ea5a4;
        }

        .totals-row td:first-child {
            border-top-left-radius: 0;
        }

        .totals-row td:last-child {
            border-top-right-radius: 0;
        }

        .totals-label {
            text-transform: uppercase;
            font-size: 9.5px;
            color: #475569;
            letter-spacing: 0.3px;
        }

        .total-hours {
            font-size: 13px;
            color: #0ea5a4;
        }

        /* ===== FOOTER ===== */
        .report-footer {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 9px;
            color: #94a3b8;
        }

        .report-footer .footer-note {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .report-footer .footer-note i {
            font-style: normal;
            display: inline-block;
            width: 14px;
            height: 14px;
            background: #d1fae5;
            border-radius: 50%;
            text-align: center;
            line-height: 14px;
            color: #065f46;
            font-weight: 700;
            font-size: 8px;
        }

        .page-number {
            font-weight: 600;
            color: #64748b;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .empty-state .empty-icon {
            font-size: 32px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .empty-state h4 {
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        .empty-state p {
            font-size: 10.5px;
            margin-top: 4px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 600px) {
            body {
                padding: 15px 12px;
                font-size: 10px;
            }

            .company-name {
                font-size: 17px;
            }

            .report-title {
                font-size: 13px;
            }

            .report-meta {
                font-size: 9px;
                flex-direction: column;
                gap: 4px;
            }

            .summary-stats {
                flex-wrap: wrap;
                gap: 8px 15px;
                padding: 8px 12px;
            }

            .stat-item {
                font-size: 9px;
            }

            .stat-item .stat-value {
                font-size: 11px;
            }

            th, td {
                padding: 6px 6px;
                font-size: 9px;
            }

            th {
                font-size: 8px;
            }

            .status-badge {
                font-size: 7.5px;
                padding: 1px 7px;
            }

            .totals-row td {
                font-size: 9.5px;
            }

            .total-hours {
                font-size: 11px;
            }

            .report-footer {
                flex-direction: column;
                text-align: center;
                font-size: 8px;
            }
        }

        @media print {
            body {
                padding: 20px 25px;
                background: white;
            }

            .report-header {
                border-bottom-color: #0ea5a4;
            }

            .summary-stats {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }

            th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .status-badge {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .meta-badge {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .totals-row td {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .stat-dot {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- ===== HEADER ===== -->
    <div class="report-header">
        <div class="company-name">Attendance Report</div>
        <div class="report-title">Employee Attendance Summary</div>
        <div class="report-meta">
            <span><strong>{{ $user->name ?? ($selectedUser->name ?? 'Unknown User') }}</strong></span>
            <span class="divider">|</span>
            <span>{{ \Carbon\Carbon::createFromDate(null, $month ?? now()->month)->format('F Y') }}</span>
            <span class="divider">|</span>
            <span>Generated: {{ $generated_at ?? now()->format('d M Y, h:i A') }}</span>
            <span class="meta-badge"><i style="font-style:normal;">●</i> Official Report</span>
        </div>
    </div>

    <!-- ===== SUMMARY STATS ===== -->
    @php
        $rows = $matrix ?? ($attendances ?? []);
        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $halfdayCount = 0;
        $leaveCount = 0;
        $holidayCount = 0;
        $dayoffCount = 0;
        $totalDays = 0;

        foreach ($rows as $att) {
            $status = $att->status ?? 'N/A';
            $totalDays++;
            switch ($status) {
                case 'present': $presentCount++; break;
                case 'absent': $absentCount++; break;
                case 'late': $lateCount++; break;
                case 'half_day': $halfdayCount++; break;
                case 'leave': $leaveCount++; break;
                case 'holiday': $holidayCount++; break;
                case 'dayoff': $dayoffCount++; break;
            }
        }
    @endphp

    @if($totalDays > 0)
    <div class="summary-stats">
        <span class="stat-item">
            <span class="stat-dot present"></span>
            <span class="stat-label">Present:</span>
            <span class="stat-value">{{ $presentCount }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-dot absent"></span>
            <span class="stat-label">Absent:</span>
            <span class="stat-value">{{ $absentCount }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-dot late"></span>
            <span class="stat-label">Late:</span>
            <span class="stat-value">{{ $lateCount }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-dot halfday"></span>
            <span class="stat-label">Half Day:</span>
            <span class="stat-value">{{ $halfdayCount }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-dot leave"></span>
            <span class="stat-label">Leave:</span>
            <span class="stat-value">{{ $leaveCount }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-dot holiday"></span>
            <span class="stat-label">Holiday:</span>
            <span class="stat-value">{{ $holidayCount }}</span>
        </span>
        <span class="stat-item">
            <span class="stat-dot dayoff"></span>
            <span class="stat-label">Day Off:</span>
            <span class="stat-value">{{ $dayoffCount }}</span>
        </span>
    </div>
    @endif

    <!-- ===== TABLE ===== -->
    <table>
        <thead>
            <tr>
                <th style="width:14%;">Date</th>
                <th style="width:18%;">Status</th>
                <th style="width:20%;">Clock In</th>
                <th style="width:20%;">Clock Out</th>
                <th style="width:28%;">Duration</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSeconds = 0;
                $curYear = $year ?? now()->year;
                $curMonth = $month ?? now()->month;
                $hasRecords = false;
            @endphp

            @forelse($rows as $dayKey => $attRaw)
                @php
                    $att = $attRaw;
                    $hasRecords = true;

                    // Build date string
                    if (is_numeric($dayKey) && empty($att->date)) {
                        $dateStr = sprintf('%04d-%02d-%02d', $curYear, $curMonth, (int)$dayKey);
                    } elseif (!empty($att->date)) {
                        $dateStr = $att->date;
                    } else {
                        $dateStr = is_string($dayKey) ? $dayKey : null;
                    }

                    try {
                        $readable = $dateStr ? \Carbon\Carbon::parse($dateStr)->format('d M Y') : '-';
                    } catch (\Exception $e) {
                        $readable = $dateStr ?? '-';
                    }

                    $status = $att->status ?? 'N/A';
                    $statusClass = strtolower(str_replace(' ', '_', $status));
                    $extra = $att->title ?? $att->leave_type ?? $att->note ?? null;

                    $clockInRaw = trim((string) ($att->clock_in ?? $att->clockIn ?? $att->in_time ?? ''));
                    $clockOutRaw = trim((string) ($att->clock_out ?? $att->clockOut ?? $att->out_time ?? ''));

                    $durationHuman = $att->duration_human ?? null;
                    $rowSeconds = 0;

                    // Calculate duration
                    if ($clockInRaw !== '' && $clockOutRaw !== '') {
                        try {
                            $ci = \Carbon\Carbon::parse("{$dateStr} {$clockInRaw}");
                            $co = \Carbon\Carbon::parse("{$dateStr} {$clockOutRaw}");
                            if ($co->lt($ci)) {
                                $co->addDay();
                            }
                            $rowSeconds = $co->diffInSeconds($ci);
                        } catch (\Throwable $e) {
                            // Fallback parsing
                            $ciTs = @strtotime("{$dateStr} {$clockInRaw}");
                            $coTs = @strtotime("{$dateStr} {$clockOutRaw}");
                            if ($ciTs === false) $ciTs = @strtotime($clockInRaw);
                            if ($coTs === false) $coTs = @strtotime($clockOutRaw);
                            if ($ciTs !== false && $coTs !== false) {
                                if ($coTs < $ciTs) $coTs += 86400;
                                $rowSeconds = max(0, (int)($coTs - $ciTs));
                            } else {
                                // Parse as HH:MM:SS
                                $ciParts = preg_split('/\D+/', $clockInRaw, -1, PREG_SPLIT_NO_EMPTY);
                                $coParts = preg_split('/\D+/', $clockOutRaw, -1, PREG_SPLIT_NO_EMPTY);
                                if (count($ciParts) >= 2 && count($coParts) >= 2) {
                                    $ciSeconds = ((int)$ciParts[0]) * 3600 + ((int)$ciParts[1]) * 60 + ((int)($ciParts[2] ?? 0));
                                    $coSeconds = ((int)$coParts[0]) * 3600 + ((int)$coParts[1]) * 60 + ((int)($coParts[2] ?? 0));
                                    if ($coSeconds < $ciSeconds) $coSeconds += 86400;
                                    $rowSeconds = max(0, $coSeconds - $ciSeconds);
                                }
                            }
                        }
                        $rowSeconds = max(0, (int) $rowSeconds);
                        $h = intdiv($rowSeconds, 3600);
                        $m = intdiv($rowSeconds % 3600, 60);
                        $s = $rowSeconds % 60;
                        $durationHuman = sprintf('%02d:%02d:%02d', $h, $m, $s);
                        if ($rowSeconds > 0) $totalSeconds += $rowSeconds;
                    } else {
                        $durationHuman = $durationHuman ?? '--';
                    }
                @endphp

                <tr>
                    <td>{{ $readable }}</td>
                    <td>
                        <span class="status-badge {{ $statusClass }}">
                            {{ ucfirst($status) }}
                        </span>
                        @if($extra)
                            <span class="extra-note"><i>{{ $extra }}</i></span>
                        @endif
                    </td>
                    <td class="small-text">{{ $clockInRaw !== '' ? $clockInRaw : '--' }}</td>
                    <td class="small-text">{{ $clockOutRaw !== '' ? $clockOutRaw : '--' }}</td>
                    <td class="text-right">{{ $durationHuman }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center empty-state">
                        <div class="empty-icon">📋</div>
                        <h4>No Attendance Records</h4>
                        <p>No attendance records found for the selected period.</p>
                    </td>
                </tr>
            @endforelse

            @php
                $totalH = intdiv($totalSeconds, 3600);
                $totalM = intdiv($totalSeconds % 3600, 60);
                $totalS = $totalSeconds % 60;
                $totalHuman = sprintf('%02d:%02d:%02d', $totalH, $totalM, $totalS);
            @endphp

            @if($hasRecords)
            <tr class="totals-row">
                <td colspan="4" class="text-right">
                    <span class="totals-label">Total Hours Worked</span>
                </td>
                <td class="text-right">
                    <span class="total-hours">{{ $totalHuman ?? '--' }}</span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- ===== FOOTER ===== -->
    <div class="report-footer">
        <div class="footer-note">
            <i>i</i>
            <span>Duration calculated from Clock In/Out. Overnight sessions handled automatically.</span>
        </div>
        <div class="page-number">
            Page 1 of 1
        </div>
    </div>

</body>
</html>
