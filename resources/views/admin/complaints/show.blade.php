@extends('admin.layout.app')

@section('content')
<style>
/* Complaint Conversation History Styling for Light & Dark Theme Modes */
.chat-msg-bubble {
    max-width: 80%;
    padding: 0.875rem 1rem;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
}

.chat-msg-self {
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #0f172a;
}
.chat-msg-self .chat-msg-meta {
    color: #475569;
    font-size: 0.75rem;
    font-weight: 700;
}
.chat-msg-self .chat-msg-body {
    color: #0f172a;
}

:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .chat-msg-self {
    background-color: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: #f8fafc !important;
}
:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .chat-msg-self .chat-msg-meta {
    color: #cbd5e1 !important;
}
:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .chat-msg-self .chat-msg-body {
    color: #f8fafc !important;
}

.chat-msg-other {
    background-color: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #064e3b;
}
.chat-msg-other .chat-msg-meta {
    color: #047857;
    font-size: 0.75rem;
    font-weight: 700;
}
.chat-msg-other .chat-msg-body {
    color: #064e3b;
}

:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .chat-msg-other {
    background-color: rgba(16, 185, 129, 0.15) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: #34d399 !important;
}
:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .chat-msg-other .chat-msg-meta {
    color: #6ee7b7 !important;
}
:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .chat-msg-other .chat-msg-body {
    color: #34d399 !important;
}

:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-theme="dark"], body[data-bs-theme="dark"], body.dark, [data-bs-theme="dark"]) .complaint-desc-body {
    color: #f8fafc !important;
}
</style>
<div class="container-fluid px-4 py-4" style="max-width: 960px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.company-complaints.index') }}" class="btn btn-sm btn-light border fw-bold" style="border-radius: 8px;">
      <i class="bx bx-left-arrow-alt"></i> Back to Complaints List
    </a>

    @if(in_array($ticket->status, ['RESOLVED', 'CLOSED']))
      <form method="POST" action="{{ route('admin.company-complaints.reopen', $ticket->id) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" style="border-radius: 8px;">
          <i class="bx bx-refresh"></i> Reopen Ticket
        </button>
      </form>
    @endif
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 10px;">
      <i class="bx bx-check-circle me-1 align-middle"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <!-- Ticket Header Card -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
          <span class="fs-4 fw-bolder text-emerald" style="color: #10b981;">#{{ $ticket->ticket_id }}</span>
          <h4 class="fw-bold text-dark mb-1 mt-1">{{ $ticket->subject }}</h4>
          <div class="text-muted fs-7">
            Category: <strong>{{ $ticket->category }}</strong> | Raised By: <strong>{{ $ticket->raised_by_name }}</strong> on {{ $ticket->created_at?->format('d M Y, h:i A') }}
          </div>
        </div>

        <div class="text-end">
          @php
            $statusBadge = match($ticket->status) {
              'OPEN' => 'bg-primary-subtle text-primary border border-primary-subtle',
              'IN PROGRESS' => 'bg-warning-subtle text-warning border border-warning-subtle',
              'WAITING FOR COMPANY' => 'bg-purple-subtle text-purple border border-purple-subtle',
              'RESOLVED' => 'bg-success-subtle text-success border border-success-subtle',
              'CLOSED' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
              'REOPENED' => 'bg-danger-subtle text-danger border border-danger-subtle',
              default => 'bg-light text-dark'
            };
          @endphp
          <span class="badge {{ $statusBadge }} px-3 py-2 fs-7 fw-bold mb-1 d-inline-block">
            {{ $ticket->status }}
          </span>
          <div>
            <span class="badge bg-secondary fs-8 fw-bold">Priority: {{ $ticket->priority }}</span>
          </div>
        </div>
      </div>

      <!-- Ticket Original Description -->
      <div class="p-3 rounded-3 bg-light border">
        <div class="fs-8 fw-bold text-uppercase text-muted mb-1">Issue Description</div>
        <div class="fs-6 complaint-desc-body" style="white-space: pre-wrap; line-height: 1.6;">{{ $ticket->description }}</div>
      </div>
    </div>
  </div>

  <!-- Timeline Feed -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-header bg-white p-3 border-bottom">
      <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-conversation me-1 text-primary"></i> Conversation History ({{ $ticket->conversations->count() }} messages)</h6>
    </div>

    <div class="card-body p-4">
      <div class="d-flex flex-column gap-3">
        @foreach($ticket->conversations as $conv)
          @php $isSelf = $conv->sender_type === 'company_admin'; @endphp
          <div class="d-flex {{ $isSelf ? 'justify-content-start' : 'justify-content-end' }}">
            <div class="chat-msg-bubble {{ $isSelf ? 'chat-msg-self' : 'chat-msg-other' }}">
              <div class="chat-msg-meta mb-1">
                {{ $conv->sender_name }} • {{ $conv->created_at?->format('d M, h:i A') }} ({{ $isSelf ? 'You' : 'Super Admin' }})
              </div>
              <div class="fs-6 chat-msg-body" style="white-space: pre-wrap; line-height: 1.5;">{{ $conv->message }}</div>

              @if($conv->attachments->count() > 0)
                <div class="mt-2 pt-2 border-top">
                  <div class="fs-8 fw-bold mb-1">Attachments:</div>
                  @foreach($conv->attachments as $att)
                    <a href="{{ asset($att->file_path) }}" target="_blank" class="fs-7 text-primary d-block text-decoration-none">
                      <i class="bx bx-paperclip"></i> {{ $att->original_name }} ({{ round($att->file_size / 1024, 1) }} KB)
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Send Reply Card -->
  @if(!in_array($ticket->status, ['CLOSED']))
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
      <div class="card-body p-4">
        <h6 class="fw-bold mb-3 text-dark"><i class="bx bx-reply me-1 text-success"></i> Reply to Super Admin</h6>
        <form method="POST" action="{{ route('admin.company-complaints.reply', $ticket->id) }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <textarea name="message" rows="4" class="form-control" placeholder="Write your response or upload additional info..." required style="border-radius: 8px;"></textarea>
          </div>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <input type="file" name="attachments[]" multiple class="form-control form-control-sm" style="max-width: 300px; border-radius: 6px;" />
            </div>

            <button type="submit" class="btn btn-emerald px-4 fw-bold text-white shadow-sm" style="background: #10b981; border: none; border-radius: 8px;">
              <i class="bx bx-send me-1"></i> Send Reply
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif

</div>
@endsection
