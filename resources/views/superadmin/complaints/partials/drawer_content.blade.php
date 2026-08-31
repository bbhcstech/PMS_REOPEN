<div style="display: flex; flex-direction: column; gap: 20px;">

  <!-- Quick Info & Action Controls -->
  <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
      <div>
        <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Company</div>
        <div style="font-size: 15px; font-weight: 800; color: #0f172a;">{{ $ticket->company?->name ?? 'Unknown Company' }}</div>
        <div style="font-size: 12px; color: #475569;">Raised by: <strong>{{ $ticket->raised_by_name }}</strong> ({{ $ticket->raised_by_email }})</div>
      </div>
      <div>
        <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-weight: 800; font-size: 11px;">
          {{ $ticket->category }}
        </span>
        <span class="badge-priority prio-{{ $ticket->priority }}" style="margin-left: 4px; padding: 6px 12px;">
          {{ $ticket->priority }}
        </span>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
      <!-- Update Status Form -->
      <div>
        <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Update Status</label>
        <form method="POST" action="{{ route('superadmin.complaints.status', $ticket->id) }}" onsubmit="submitDrawerForm(event, this)">
          @csrf
          @method('PATCH')
          <select name="status" class="form-control-custom w-100" onchange="this.form.submit()" style="font-weight: 700; font-size: 12px;">
            <option value="OPEN" {{ $ticket->status === 'OPEN' ? 'selected' : '' }}>OPEN</option>
            <option value="IN PROGRESS" {{ $ticket->status === 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
            <option value="WAITING FOR COMPANY" {{ $ticket->status === 'WAITING FOR COMPANY' ? 'selected' : '' }}>WAITING FOR COMPANY</option>
            <option value="RESOLVED" {{ $ticket->status === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
            <option value="CLOSED" {{ $ticket->status === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
            <option value="REOPENED" {{ $ticket->status === 'REOPENED' ? 'selected' : '' }}>REOPENED</option>
          </select>
        </form>
      </div>

      <!-- Assign Ticket Form -->
      <div>
        <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Assigned To</label>
        <form method="POST" action="{{ route('superadmin.complaints.assign', $ticket->id) }}" onsubmit="submitDrawerForm(event, this)">
          @csrf
          @method('PATCH')
          <select name="super_admin_id" class="form-control-custom w-100" onchange="this.form.submit()" style="font-weight: 700; font-size: 12px;">
            <option value="unassigned" {{ !$ticket->assigned_super_admin_id ? 'selected' : '' }}>Unassigned</option>
            @foreach($superAdmins as $admin)
              <option value="{{ $admin->id }}" {{ $ticket->assigned_super_admin_id == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
            @endforeach
          </select>
        </form>
      </div>
    </div>
  </div>

  <!-- Ticket Meta Info -->
  <div style="font-size: 12px; color: #64748b; display: flex; gap: 16px; flex-wrap: wrap; background: #ffffff; border: 1px solid #f1f5f9; padding: 10px 14px; border-radius: 8px;">
    <div><strong>Created:</strong> {{ $ticket->created_at?->format('d M Y, h:i A') }}</div>
    <div><strong>Last Activity:</strong> {{ $ticket->last_reply_at ? $ticket->last_reply_at->diffForHumans() : $ticket->updated_at?->diffForHumans() }}</div>
    @if($ticket->related_module)
      <div><strong>Module:</strong> {{ $ticket->related_module }}</div>
    @endif
  </div>

  <!-- Original Description -->
  <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
    <div style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px;">Initial Complaint Description</div>
    <div style="font-size: 13.5px; color: #0f172a; line-height: 1.6; white-space: pre-wrap;">{{ $ticket->description }}</div>
  </div>

  <!-- Conversation Timeline Feed -->
  <div>
    <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
      <i class="bx bx-conversation" style="color: var(--emerald-main); font-size: 18px;"></i> Conversation Timeline ({{ $ticket->conversations->count() }} messages)
    </div>

    <div class="timeline-feed">
      @foreach($ticket->conversations as $conv)
        <div class="timeline-item {{ $conv->sender_type }}">
          <div class="timeline-bubble">
            <div class="timeline-meta">
              {{ $conv->sender_name }} • {{ $conv->created_at?->format('d M, h:i A') }} ({{ $conv->sender_type === 'super_admin' ? 'Super Admin' : 'Company Admin' }})
            </div>
            <div>{{ $conv->message }}</div>

            @if($conv->attachments->count() > 0)
              <div style="margin-top: 10px; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 6px;">
                <div style="font-size: 11px; font-weight: 700; margin-bottom: 4px;">Attachments:</div>
                @foreach($conv->attachments as $att)
                  <a href="{{ asset($att->file_path) }}" target="_blank" style="font-size: 11px; text-decoration: underline; color: #2563eb; display: block;">
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

  <!-- Activity History Log Stream -->
  @if($ticket->activities->count() > 0)
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
      <div style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 8px;">Activity Trail</div>
      <div style="display: flex; flex-direction: column; gap: 6px;">
        @foreach($ticket->activities->take(6) as $act)
          <div style="font-size: 11.5px; color: #64748b; display: flex; justify-content: space-between;">
            <span><strong>{{ $act->actor_name }}</strong>: {{ $act->description }}</span>
            <span style="white-space: nowrap; margin-left: 8px;">{{ $act->created_at?->format('H:i') }}</span>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <!-- Reply Box Form -->
  <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; margin-top: 8px;">
    <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">
      <i class="bx bx-reply"></i> Write Response to Company Admin
    </div>
    <form method="POST" action="{{ route('superadmin.complaints.respond', $ticket->id) }}" enctype="multipart/form-data" onsubmit="submitDrawerForm(event, this)">
      @csrf
      <div style="margin-bottom: 10px;">
        <textarea name="message" rows="3" class="form-control-custom w-100" placeholder="Write response to company admin..." required></textarea>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
          <label style="font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer;">
            <i class="bx bx-paperclip"></i> Attach File
            <input type="file" name="attachments[]" multiple style="display: none;" onchange="document.getElementById('fileCountNotice').innerText = this.files.length + ' file(s) selected';" />
          </label>
          <span id="fileCountNotice" style="font-size: 11px; color: #059669; font-weight: 700; margin-left: 6px;"></span>
        </div>

        <button type="submit" class="btn-primary-emerald">
          <i class="bx bx-send"></i> Send Response
        </button>
      </div>
    </form>
  </div>

</div>

<script>
function submitDrawerForm(e, form) {
  e.preventDefault();
  const formData = new FormData(form);
  
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
      openComplaintDrawer({{ $ticket->id }});
    } else {
      alert(data.message || 'Operation failed.');
    }
  })
  .catch(err => {
    alert('An error occurred.');
  });
}
</script>
