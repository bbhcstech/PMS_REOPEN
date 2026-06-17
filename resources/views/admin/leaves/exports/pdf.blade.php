<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 12px; }
        h1 { margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d8dee6; padding: 7px; text-align: left; }
        th { background: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>
    <h1>Leave Report</h1>
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
            </tr>
        </thead>
        <tbody>
            @foreach($leaves as $leave)
                <tr>
                    <td>{{ $leave->user?->name }}</td>
                    <td>{{ $leave->type_label }}</td>
                    <td>{{ optional($leave->start_date)->format('Y-m-d') }}</td>
                    <td>{{ optional($leave->end_date)->format('Y-m-d') }}</td>
                    <td>{{ $leave->total_days }}</td>
                    <td>{{ $leave->paid_days ?? 0 }}</td>
                    <td>{{ $leave->unpaid_days ?? 0 }}</td>
                    <td>{{ ucfirst($leave->status) }}</td>
                    <td>{{ $leave->is_unpaid ? 'Unpaid' : 'Paid' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
