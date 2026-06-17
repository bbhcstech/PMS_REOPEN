<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leave Report</title>
    <style>
        body {
            margin: 0;
            background: #f8fafc;
            color: #172033;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .report-page {
            max-width: 1120px;
            margin: 24px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 28px;
            background: #ecfdf5;
            border-bottom: 1px solid #d1fae5;
        }

        h1 {
            margin: 0;
            color: #065f46;
            font-size: 26px;
        }

        .report-header p {
            margin: 6px 0 0;
            color: #475569;
            font-weight: 600;
        }

        .print-button {
            align-self: center;
            border: 0;
            border-radius: 10px;
            background: #059669;
            color: #ffffff;
            padding: 11px 18px;
            font-weight: 700;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
        }

        .empty {
            padding: 48px;
            text-align: center;
            color: #64748b;
            font-weight: 700;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .report-page {
                margin: 0;
                max-width: none;
                border: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="report-page">
        <header class="report-header">
            <div>
                <h1>Leave Report</h1>
                <p>Generated {{ now()->format('d M Y h:i A') }} | {{ $leaves->count() }} record(s)</p>
            </div>
            <button class="print-button" onclick="window.print()">Print</button>
        </header>

        @if($leaves->isEmpty())
            <div class="empty">No leave records found for this report.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Days</th>
                        <th>Paid Days</th>
                        <th>Unpaid Days</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                        <tr>
                            <td>{{ $leave->user?->name ?? 'N/A' }}</td>
                            <td>{{ $leave->type_label }}</td>
                            <td>{{ optional($leave->start_date)->format('Y-m-d') }}</td>
                            <td>{{ optional($leave->end_date)->format('Y-m-d') }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td>{{ $leave->paid_days ?? 0 }}</td>
                            <td>{{ $leave->unpaid_days ?? 0 }}</td>
                            <td>{{ ucfirst($leave->status) }}</td>
                            <td>{{ $leave->is_unpaid ? 'Unpaid' : 'Paid' }}</td>
                            <td>{{ $leave->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
