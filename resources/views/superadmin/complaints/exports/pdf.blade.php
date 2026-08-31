<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Complaints Export Report</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 11px;
      color: #1e293b;
      margin: 0;
      padding: 15px;
    }
    .header {
      border-bottom: 2px solid #0f744c;
      padding-bottom: 12px;
      margin-bottom: 15px;
    }
    .header table {
      width: 100%;
      border-collapse: collapse;
    }
    .header .title {
      font-size: 18px;
      font-weight: bold;
      color: #0f744c;
    }
    .header .subtitle {
      font-size: 11px;
      color: #64748b;
      margin-top: 4px;
    }
    .header .meta {
      text-align: right;
      font-size: 10px;
      color: #475569;
    }
    .filters-bar {
      background-color: #f8fafc;
      border: 1px solid #cbd5e1;
      padding: 8px 12px;
      margin-bottom: 15px;
      border-radius: 4px;
      font-size: 10px;
    }
    .pdf-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      margin-top: 5px;
    }
    .pdf-table th {
      background-color: #0f744c;
      color: #ffffff;
      padding: 7px 8px;
      font-weight: bold;
      text-align: left;
      text-transform: uppercase;
      font-size: 9.5px;
      border: 1px solid #073a26;
    }
    .pdf-table td {
      padding: 7px 8px;
      border: 1px solid #cbd5e1;
      vertical-align: top;
    }
    .pdf-table tr:nth-child(even) td {
      background-color: #f8fafc;
    }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 8.5px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .badge-open { background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd; }
    .badge-in_progress { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .badge-waiting { background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
    .badge-resolved { background: #ecfdf5; color: #047857; border: 1px solid #6ee7b7; }
    .badge-closed { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .badge-critical { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }
    .badge-high { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
    .badge-medium { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-low { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .footer {
      margin-top: 20px;
      font-size: 9px;
      color: #94a3b8;
      text-align: center;
      border-top: 1px solid #e2e8f0;
      padding-top: 8px;
    }
  </style>
</head>
<body>

  <div class="header">
    <table>
      <tr>
        <td>
          <div class="title">Complaints &amp; Support Center</div>
          <div class="subtitle">Super Admin Enterprise Support Ticket Export</div>
        </td>
        <td class="meta">
          <div><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</div>
          <div><strong>Total Records:</strong> {{ count($tickets) }}</div>
        </td>
      </tr>
    </table>
  </div>

  @if(!empty($activeFilters))
  <div class="filters-bar">
    <strong>Applied Filters:</strong> {{ implode(' | ', $activeFilters) }}
  </div>
  @endif

  <table class="pdf-table">
    <thead>
      <tr>
        <th style="width: 10%;">Ticket ID</th>
        <th style="width: 14%;">Company</th>
        <th style="width: 15%;">Raised By</th>
        <th style="width: 22%;">Subject</th>
        <th style="width: 9%;">Category</th>
        <th style="width: 8%;">Priority</th>
        <th style="width: 11%;">Status</th>
        <th style="width: 11%;">Created At</th>
      </tr>
    </thead>
    <tbody>
      @forelse($tickets as $ticket)
        @php
          $statusKey = strtolower(str_replace(' ', '_', $ticket->status));
          $prioKey = strtolower($ticket->priority);
        @endphp
        <tr>
          <td><strong>#{{ $ticket->ticket_id }}</strong></td>
          <td>
            <strong>{{ $ticket->company?->name ?? 'N/A' }}</strong><br>
            <span style="font-size: 8.5px; color: #64748b;">{{ $ticket->company?->company_code ?? '' }}</span>
          </td>
          <td>
            {{ $ticket->raised_by_name }}<br>
            <span style="font-size: 8.5px; color: #64748b;">{{ $ticket->raised_by_email }}</span>
          </td>
          <td>{{ $ticket->subject }}</td>
          <td>{{ $ticket->category }}</td>
          <td>
            <span class="badge badge-{{ $prioKey }}">{{ $ticket->priority }}</span>
          </td>
          <td>
            <span class="badge badge-{{ $statusKey }}">{{ $ticket->status }}</span>
          </td>
          <td>{{ $ticket->created_at?->format('d M Y, h:i A') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">
            No tickets found for export.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="footer">
    Report generated automatically from Super Admin Command Center &bull; {{ config('app.name', 'BBHPMS') }}
  </div>

</body>
</html>
