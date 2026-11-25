@extends('layouts.superadmin')

@section('title', 'Ticket Details #' . $ticket->ticket_id)

@push('styles')
<style>
  .show-ticket-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
  }

  .show-ticket-header {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 16px;
    margin-bottom: 20px;
  }

  .show-ticket-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
  }

  .show-ticket-meta {
    font-size: 13px;
    color: #64748b;
  }

  html[data-pms-theme="dark"] .show-ticket-card,
  html[data-theme="dark"] .show-ticket-card,
  html[data-bs-theme="dark"] .show-ticket-card {
    background: #0F1530;
    border-color: rgba(238, 241, 251, 0.09);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
  }

  html[data-pms-theme="dark"] .show-ticket-header,
  html[data-theme="dark"] .show-ticket-header,
  html[data-bs-theme="dark"] .show-ticket-header {
    border-color: rgba(238, 241, 251, 0.09);
  }

  html[data-pms-theme="dark"] .show-ticket-title,
  html[data-theme="dark"] .show-ticket-title,
  html[data-bs-theme="dark"] .show-ticket-title {
    color: #EEF1FB;
  }

  html[data-pms-theme="dark"] .show-ticket-meta,
  html[data-theme="dark"] .show-ticket-meta,
  html[data-bs-theme="dark"] .show-ticket-meta {
    color: #9AA3C7;
  }
</style>
@endpush

@section('content')
<div style="padding: 10px 0 40px; max-width: 900px; margin: 0 auto;">
  <div style="margin-bottom: 16px;">
    <a href="{{ route('superadmin.complaints.index') }}" class="btn btn-sm btn-light" style="border: 1px solid var(--cmp-border, #cbd5e1); font-weight: 700; border-radius: 8px;">
      <i class="bx bx-left-arrow-alt"></i> Back to Complaints Dashboard
    </a>
  </div>

  <div class="show-ticket-card">
    <div class="show-ticket-header">
      <h2 class="show-ticket-title">#{{ $ticket->ticket_id }} — {{ $ticket->subject }}</h2>
      <div class="show-ticket-meta">Company: <strong style="color: var(--cmp-text-main, #0f172a);">{{ $ticket->company?->name }}</strong> | Raised By: {{ $ticket->raised_by_name }}</div>
    </div>

    @include('superadmin.complaints.partials.drawer_content', ['ticket' => $ticket, 'superAdmins' => $superAdmins])
  </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('admin/assets/js/pms-complaint-live.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-complaint-live.js')) }}" defer></script>
@endpush
