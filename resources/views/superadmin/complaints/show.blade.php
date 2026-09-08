@extends('layouts.superadmin')

@section('title', 'Ticket Details #' . $ticket->ticket_id)

@section('content')
<div style="padding: 10px 0 40px; max-width: 900px; margin: 0 auto;">
  <div style="margin-bottom: 16px;">
    <a href="{{ route('superadmin.complaints.index') }}" class="btn btn-sm btn-light" style="border: 1px solid #cbd5e1; font-weight: 700; border-radius: 8px;">
      <i class="bx bx-left-arrow-alt"></i> Back to Complaints Dashboard
    </a>
  </div>

  <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
    <div style="border-bottom: 1px solid #e2e8f0; pb: 16px; margin-bottom: 20px;">
      <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">#{{ $ticket->ticket_id }} — {{ $ticket->subject }}</h2>
      <div style="font-size: 13px; color: #64748b;">Company: <strong>{{ $ticket->company?->name }}</strong> | Raised By: {{ $ticket->raised_by_name }}</div>
    </div>

    @include('superadmin.complaints.partials.drawer_content', ['ticket' => $ticket, 'superAdmins' => $superAdmins])
  </div>
</div>
@endsection
