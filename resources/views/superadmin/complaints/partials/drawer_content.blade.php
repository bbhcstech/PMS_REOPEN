@php
  $statusKey = str_replace(' ', '_', $ticket->status);
  $prioKey = strtoupper($ticket->priority);
@endphp

<style>
  /* ==========================================================================
     DRAWER CONTENT — LUXURY DUAL THEME (LIGHT & DARK)
     ========================================================================== */
  .drawer-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
    font-family: inherit;
  }

  /* ---- HERO TICKET INFO & CONTROLS ---- */
  .drawer-hero-card {
    background: var(--cmp-bg-subtle, #f8fafc);
    border: 1px solid var(--cmp-border, #e2e8f0);
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
  }

  .drawer-company-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .company-identity {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .company-avatar-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e40af, #2F6BFF);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(47, 107, 255, 0.25);
  }

  .company-name-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--cmp-text-main, #0f172a);
    line-height: 1.2;
    margin-bottom: 3px;
  }

  .company-sub-meta {
    font-size: 12px;
    color: var(--cmp-text-muted, #64748b);
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
  }

  .quick-controls-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    border-top: 1px solid var(--cmp-border, #e2e8f0);
    padding-top: 14px;
  }

  @media (max-width: 520px) {
    .quick-controls-grid {
      grid-template-columns: 1fr;
    }
  }

  .control-field-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--cmp-text-muted, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .select-styled {
    width: 100%;
    background: var(--cmp-bg-input, #ffffff);
    border: 1.5px solid var(--cmp-border-strong, #cbd5e1);
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--cmp-text-main, #0f172a);
    outline: none;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .select-styled:focus {
    border-color: #2F6BFF;
    box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.2);
  }

  /* ---- METADATA CHIPS BAR ---- */
  .meta-chips-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    background: var(--cmp-bg-card, #ffffff);
    border: 1px solid var(--cmp-border, #e2e8f0);
    padding: 10px 16px;
    border-radius: 12px;
    font-size: 12px;
    color: var(--cmp-text-muted, #64748b);
    align-items: center;
  }

  .meta-chip-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .meta-chip-item strong {
    color: var(--cmp-text-main, #0f172a);
  }

  .meta-chip-divider {
    color: var(--cmp-border, #cbd5e1);
  }

  /* ---- ORIGINAL DESCRIPTION PANEL ---- */
  .description-card {
    background: var(--cmp-bg-card, #ffffff);
    border: 1px solid var(--cmp-border, #e2e8f0);
    border-radius: 14px;
    padding: 18px 20px;
    position: relative;
  }

  .card-section-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--cmp-text-muted, #64748b);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .card-section-label i {
    font-size: 14px;
    color: #2F6BFF;
  }

  .description-body {
    font-size: 13.5px;
    color: var(--cmp-text-main, #0f172a);
    line-height: 1.65;
    white-space: pre-wrap;
    background: var(--cmp-bg-subtle, #f8fafc);
    padding: 14px 16px;
    border-radius: 10px;
    border-left: 3px solid #2F6BFF;
  }

  /* ---- CONVERSATION CHAT FEED ---- */
  .timeline-chat-feed {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .chat-message-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  .chat-message-row.super-admin-msg {
    flex-direction: row-reverse;
  }

  .chat-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 12px;
    flex-shrink: 0;
  }

  .chat-avatar.client-avatar {
    background: #e2e8f0;
    color: #334155;
  }

  .chat-avatar.admin-avatar {
    background: linear-gradient(135deg, #1e40af, #2F6BFF);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(47, 107, 255, 0.3);
  }

  html[data-pms-theme="dark"] .chat-avatar.client-avatar,
  html[data-theme="dark"] .chat-avatar.client-avatar,
  html[data-bs-theme="dark"] .chat-avatar.client-avatar {
    background: #1e293b;
    color: #cbd5e1;
  }

  .chat-bubble-card {
    max-width: 82%;
    padding: 14px 18px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.55;
    position: relative;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
  }

  .chat-message-row.client-msg .chat-bubble-card {
    background: var(--cmp-bg-subtle, #f1f5f9);
    color: var(--cmp-text-main, #0f172a);
    border: 1px solid var(--cmp-border, #e2e8f0);
    border-top-left-radius: 4px;
  }

  .chat-message-row.super-admin-msg .chat-bubble-card {
    background: rgba(47, 107, 255, 0.12);
    color: var(--cmp-text-main, #0f172a);
    border: 1px solid rgba(47, 107, 255, 0.3);
    border-top-right-radius: 4px;
  }

  html[data-pms-theme="dark"] .chat-message-row.super-admin-msg .chat-bubble-card,
  html[data-theme="dark"] .chat-message-row.super-admin-msg .chat-bubble-card,
  html[data-bs-theme="dark"] .chat-message-row.super-admin-msg .chat-bubble-card {
    background: rgba(30, 64, 175, 0.28);
    color: #EEF1FB;
    border-color: rgba(96, 165, 250, 0.35);
  }

  .chat-meta-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    font-size: 11px;
    font-weight: 700;
    color: var(--cmp-text-muted, #64748b);
    margin-bottom: 6px;
  }

  .chat-author-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 800;
    color: var(--cmp-text-main, #0f172a);
  }

  .chat-badge-role {
    font-size: 10px;
    padding: 1px 7px;
    border-radius: 12px;
    font-weight: 800;
    text-transform: uppercase;
  }

  .role-admin {
    background: #2F6BFF;
    color: #ffffff;
  }

  .role-company {
    background: var(--cmp-border-strong, #cbd5e1);
    color: var(--cmp-text-body, #334155);
  }

  /* ---- ATTACHMENT CARD CAROUSEL / GRID ---- */
  .attachment-pills-wrap {
    margin-top: 12px;
    border-top: 1px solid rgba(125, 125, 125, 0.15);
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .attachment-file-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    background: var(--cmp-bg-card, #ffffff);
    border: 1px solid var(--cmp-border, #e2e8f0);
    padding: 8px 12px;
    border-radius: 8px;
    text-decoration: none;
    color: var(--cmp-text-main, #0f172a);
    transition: all 0.2s ease;
  }

  .attachment-file-card:hover {
    border-color: #2F6BFF;
    background: rgba(47, 107, 255, 0.05);
    transform: translateY(-1px);
  }

  .att-left-info {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
  }

  .att-icon-box {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: rgba(47, 107, 255, 0.12);
    color: #2F6BFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
  }

  .att-name-text {
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 260px;
  }

  .att-size-text {
    font-size: 10.5px;
    color: var(--cmp-text-muted, #64748b);
  }

  /* Image Thumbnail Preview */
  .att-image-thumb {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    border: 1px solid var(--cmp-border, #e2e8f0);
    margin-top: 6px;
    display: block;
    object-fit: cover;
    cursor: pointer;
    transition: opacity 0.2s ease;
  }

  .att-image-thumb:hover {
    opacity: 0.9;
  }

  /* ---- VERTICAL ACTIVITY TIMELINE ---- */
  .activity-trail-box {
    background: var(--cmp-bg-card, #ffffff);
    border: 1px solid var(--cmp-border, #e2e8f0);
    border-radius: 14px;
    padding: 18px 20px;
  }

  .vertical-timeline {
    position: relative;
    padding-left: 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-top: 12px;
  }

  .vertical-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 6px;
    bottom: 6px;
    width: 2px;
    background: var(--cmp-border, #e2e8f0);
  }

  .v-timeline-node {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    font-size: 12px;
  }

  .v-timeline-dot {
    position: absolute;
    left: -24px;
    top: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--cmp-bg-card, #ffffff);
    border: 2px solid #2F6BFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #2F6BFF;
  }

  .v-timeline-content {
    flex: 1;
    color: var(--cmp-text-body, #334155);
    line-height: 1.4;
  }

  .v-timeline-actor {
    font-weight: 800;
    color: var(--cmp-text-main, #0f172a);
  }

  .v-timeline-time {
    white-space: nowrap;
    font-size: 11px;
    color: var(--cmp-text-muted, #64748b);
    font-weight: 600;
  }

  /* ---- UPGRADED REPLY COMPOSER ---- */
  .reply-composer-card {
    background: var(--cmp-bg-card, #ffffff);
    border: 1.5px solid var(--cmp-border, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.03);
    margin-top: 6px;
  }

  .reply-header-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .reply-title {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--cmp-text-main, #0f172a);
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .reply-textarea-wrap {
    position: relative;
    margin-bottom: 12px;
  }

  .reply-textarea {
    width: 100%;
    background: var(--cmp-bg-input, #ffffff);
    border: 1.5px solid var(--cmp-border-strong, #cbd5e1);
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 13.5px;
    font-family: inherit;
    color: var(--cmp-text-main, #0f172a);
    font-weight: 500;
    line-height: 1.6;
    outline: none;
    transition: all 0.2s ease;
    resize: vertical;
    min-height: 100px;
  }

  .reply-textarea:focus {
    border-color: #2F6BFF;
    box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.2);
  }

  .reply-textarea::placeholder {
    color: var(--cmp-text-muted, #94a3b8);
    font-weight: 500;
  }

  .reply-bottom-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }

  .btn-attach-styled {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--cmp-text-muted, #64748b);
    cursor: pointer;
    padding: 7px 12px;
    border-radius: 8px;
    background: var(--cmp-bg-subtle, #f8fafc);
    border: 1px solid var(--cmp-border, #e2e8f0);
    transition: all 0.2s ease;
  }

  .btn-attach-styled:hover {
    color: #2F6BFF;
    border-color: #2F6BFF;
    background: rgba(47, 107, 255, 0.05);
  }

  .file-selection-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
  }

  .file-selection-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(47, 107, 255, 0.1);
    color: #2F6BFF;
    border: 1px solid rgba(47, 107, 255, 0.25);
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 700;
  }

  .btn-send-response {
    background: linear-gradient(135deg, #1e40af 0%, #2F6BFF 100%);
    color: #ffffff;
    padding: 9px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.25);
  }

  .btn-send-response:hover {
    box-shadow: 0 6px 20px rgba(47, 107, 255, 0.4);
    transform: translateY(-1px);
    color: #ffffff;
  }

  .btn-send-response:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }
</style>

<div class="drawer-wrapper" data-live-chat>

  <!-- ==================== 1. HERO TICKET INFO & QUICK ACTIONS ==================== -->
  <div class="drawer-hero-card">
    <div class="drawer-company-header">
      <div class="company-identity">
        <div class="company-avatar-box">
          {{ strtoupper(substr($ticket->company?->name ?? 'C', 0, 1)) }}
        </div>
        <div>
          <div class="company-name-title">{{ $ticket->company?->name ?? 'Unknown Company' }}</div>
          <div class="company-sub-meta">
            <span><i class="bx bx-user" style="color: #2F6BFF;"></i> {{ $ticket->raised_by_name }}</span>
            <span>•</span>
            <span><i class="bx bx-envelope"></i> {{ $ticket->raised_by_email }}</span>
          </div>
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 6px;">
        <span class="badge" style="background: rgba(47, 107, 255, 0.12); color: #2F6BFF; border: 1px solid rgba(47, 107, 255, 0.25); padding: 6px 12px; border-radius: 6px; font-weight: 800; font-size: 11px;">
          {{ $ticket->category }}
        </span>
        <span class="badge-priority prio-{{ $prioKey }}" style="padding: 6px 12px;">
          {{ $prioKey }}
        </span>
      </div>
    </div>

    <!-- Quick Status & Assign Form Controls -->
    <div class="quick-controls-grid">
      <!-- Status Changer -->
      <div>
        <label class="control-field-label">
          <i class="bx bx-check-circle" style="color: #2F6BFF;"></i> Ticket Status
        </label>
        <form method="POST" action="{{ route('superadmin.complaints.status', $ticket->id) }}" onsubmit="submitDrawerForm(event, this)">
          @csrf
          <select name="status" class="select-styled" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
            <option value="OPEN" {{ $ticket->status === 'OPEN' ? 'selected' : '' }}>🟢 OPEN</option>
            <option value="IN PROGRESS" {{ $ticket->status === 'IN PROGRESS' ? 'selected' : '' }}>🟡 IN PROGRESS</option>
            <option value="WAITING FOR COMPANY" {{ $ticket->status === 'WAITING FOR COMPANY' ? 'selected' : '' }}>🟣 WAITING FOR COMPANY</option>
            <option value="RESOLVED" {{ $ticket->status === 'RESOLVED' ? 'selected' : '' }}>🔵 RESOLVED</option>
            <option value="CLOSED" {{ $ticket->status === 'CLOSED' ? 'selected' : '' }}>⚪ CLOSED</option>
            <option value="REOPENED" {{ $ticket->status === 'REOPENED' ? 'selected' : '' }}>🔴 REOPENED</option>
          </select>
        </form>
      </div>

      <!-- Assignee Changer -->
      <div>
        <label class="control-field-label">
          <i class="bx bx-user-pin" style="color: #8B5CF6;"></i> Assigned Super Admin
        </label>
        <form method="POST" action="{{ route('superadmin.complaints.assign', $ticket->id) }}" onsubmit="submitDrawerForm(event, this)">
          @csrf
          <select name="super_admin_id" class="select-styled" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
            <option value="unassigned" {{ !$ticket->assigned_super_admin_id ? 'selected' : '' }}>👤 Unassigned</option>
            @foreach($superAdmins as $admin)
              <option value="{{ $admin->id }}" {{ $ticket->assigned_super_admin_id == $admin->id ? 'selected' : '' }}>
                🛡️ {{ $admin->name }}
              </option>
            @endforeach
          </select>
        </form>
      </div>
    </div>
  </div>

  <!-- ==================== 2. METADATA CHIPS BAR ==================== -->
  <div class="meta-chips-bar">
    <div class="meta-chip-item">
      <i class="bx bx-calendar"></i>
      <span>Created: <strong>{{ $ticket->created_at?->format('d M Y, h:i A') }}</strong></span>
    </div>
    <span class="meta-chip-divider">•</span>
    <div class="meta-chip-item">
      <i class="bx bx-time"></i>
      <span>Updated: <strong>{{ $ticket->last_reply_at ? $ticket->last_reply_at->diffForHumans() : $ticket->updated_at?->diffForHumans() }}</strong></span>
    </div>
    @if($ticket->related_module)
      <span class="meta-chip-divider">•</span>
      <div class="meta-chip-item">
        <i class="bx bx-cube-alt"></i>
        <span>Module: <strong>{{ $ticket->related_module }}</strong></span>
      </div>
    @endif
  </div>

  <!-- ==================== 3. INITIAL REPORTED DESCRIPTION ==================== -->
  <div class="description-card">
    <div class="card-section-label">
      <i class="bx bx-file-blank"></i> Initial Problem Description
    </div>
    <div class="description-body">{{ $ticket->description }}</div>
  </div>

  <!-- ==================== 4. CONVERSATION TIMELINE FEED ==================== -->
  <div>
    <div style="font-size: 13.5px; font-weight: 800; color: var(--cmp-text-main, #0f172a); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
      <i class="bx bx-conversation" style="color: #2F6BFF; font-size: 18px;"></i>
      <span>Discussion Feed (<span data-live-message-count>{{ $ticket->conversations->count() }}</span> messages)</span>
    </div>

    <div class="timeline-chat-feed" data-live-feed="{{ route('superadmin.complaints.messages', $ticket->id) }}" data-last-id="{{ $ticket->conversations->max('id') ?? 0 }}">
      @include('superadmin.complaints.partials.messages', ['messages' => $ticket->conversations])
    </div>
  </div>

  <!-- ==================== 5. ACTIVITY TRAIL (VERTICAL TIMELINE) ==================== -->
  @if($ticket->activities->count() > 0)
    <div class="activity-trail-box">
      <div class="card-section-label">
        <i class="bx bx-history"></i> Audit Trail &amp; Ticket Timeline
      </div>

      <div class="vertical-timeline">
        @foreach($ticket->activities->take(8) as $act)
          @php
            $descLower = strtolower($act->description);
            $icon = 'bx-radio-circle';
            if (str_contains($descLower, 'response') || str_contains($descLower, 'reply')) {
                $icon = 'bx-message-square-detail';
            } elseif (str_contains($descLower, 'status') || str_contains($descLower, 'closed') || str_contains($descLower, 'resolved')) {
                $icon = 'bx-check';
            } elseif (str_contains($descLower, 'assigned')) {
                $icon = 'bx-user';
            }
          @endphp
          <div class="v-timeline-node">
            <div class="v-timeline-dot">
              <i class="bx {{ $icon }}"></i>
            </div>
            <div class="v-timeline-content">
              <span class="v-timeline-actor">{{ $act->actor_name }}</span>: {{ $act->description }}
            </div>
            <div class="v-timeline-time">{{ $act->created_at?->format('H:i') }}</div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <!-- ==================== 6. UPGRADED REPLY COMPOSER ==================== -->
  <div class="reply-composer-card">
    <div class="reply-header-info">
      <div class="reply-title">
        <i class="bx bx-reply" style="color: #2F6BFF; font-size: 18px;"></i>
        <span>Write Response to Company Admin</span>
      </div>
      <div style="font-size: 11px; color: var(--cmp-text-muted, #64748b);">
        <i class="bx bx-bell"></i> Instant in-app &amp; email notice
      </div>
    </div>

    <form method="POST" action="{{ route('superadmin.complaints.respond', $ticket->id) }}" enctype="multipart/form-data" data-live-reply>
      @csrf
      <div class="reply-textarea-wrap">
        <textarea name="message" rows="3" class="reply-textarea" placeholder="Type your response to the company admin here..." required></textarea>
      </div>

      <!-- Selected files chip preview -->
      <div id="drawerSelectedFilesList" class="file-selection-list"></div>

      <div class="reply-bottom-bar">
        <div>
          <label class="btn-attach-styled">
            <i class="bx bx-paperclip" style="font-size: 16px;"></i> Attach Documents / Screenshots
            <input type="file" name="attachments[]" id="drawerAttachmentInput" multiple style="display: none;" onchange="handleFileSelection(this)" />
          </label>
        </div>

        <button type="submit" class="btn-send-response" id="btnSubmitDrawerResponse">
          <i class="bx bx-send"></i> Send Response
        </button>
      </div>
    </form>
  </div>

</div>

<script>
function handleFileSelection(input) {
  const container = document.getElementById('drawerSelectedFilesList');
  if (!container) return;
  container.innerHTML = '';

  if (input.files && input.files.length > 0) {
    for (let i = 0; i < input.files.length; i++) {
      const file = input.files[i];
      const sizeKb = Math.round(file.size / 1024);
      const chip = document.createElement('span');
      chip.className = 'file-selection-chip';
      chip.innerHTML = `<i class="bx bx-file"></i> ${file.name} (${sizeKb} KB)`;
      container.appendChild(chip);
    }
  }
}

function submitDrawerForm(e, form) {
  e.preventDefault();
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<i class="bx bx-loader-alt bx-spin"></i> Processing...`;
  }
  
  fetch(form.action, {
    method: form.method || 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (typeof openComplaintDrawer === 'function') {
        openComplaintDrawer({{ $ticket->id }});
      } else {
        window.location.reload();
      }
    } else {
      alert(data.message || 'Operation failed.');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
      }
    }
  })
  .catch(err => {
    alert('An error occurred. Please try again.');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHtml;
    }
  });
}
</script>
