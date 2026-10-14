@foreach($messages as $conv)
        @php
          $isAdmin = ($conv->sender_type === 'super_admin');
          $initials = strtoupper(substr($conv->sender_name ?? ($isAdmin ? 'SA' : 'CA'), 0, 2));
        @endphp
        <div data-message-id="{{ $conv->id }}" class="chat-message-row {{ $isAdmin ? 'super-admin-msg' : 'client-msg' }}">
          <!-- Sender Avatar -->
          <div class="chat-avatar {{ $isAdmin ? 'admin-avatar' : 'client-avatar' }}" title="{{ $conv->sender_name }}">
            {{ $initials }}
          </div>

          <!-- Bubble Content -->
          <div class="chat-bubble-card">
            <div class="chat-meta-header">
              <span class="chat-author-tag">
                {{ $conv->sender_name }}
                <span class="chat-badge-role {{ $isAdmin ? 'role-admin' : 'role-company' }}">
                  {{ $isAdmin ? 'Super Admin' : 'Company Admin' }}
                </span>
              </span>
              <span>{{ $conv->created_at?->format('d M, h:i A') }}</span>
            </div>

            <!-- Message Body -->
            <div style="white-space: pre-wrap; word-break: break-word;">{{ $conv->message }}</div>

            <!-- Attachments inside message bubble -->
            @if($conv->attachments->count() > 0)
              <div class="attachment-pills-wrap">
                <div style="font-size: 11px; font-weight: 800; color: var(--cmp-text-muted, #64748b); text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="bx bx-paperclip"></i> Attached Files ({{ $conv->attachments->count() }}):
                </div>

                @foreach($conv->attachments as $att)
                  @php
                    $ext = strtolower(pathinfo($att->file_path, PATHINFO_EXTENSION));
                    $isImg = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']);
                    $fileUrl = route('superadmin.complaints.attachment', [$ticket->id, $att->id]);
                  @endphp

                  <!-- Attachment Pill -->
                  <a href="{{ $fileUrl }}" target="_blank" class="attachment-file-card">
                    <div class="att-left-info">
                      <div class="att-icon-box">
                        @if($isImg)
                          <i class="bx bx-image"></i>
                        @elseif($ext === 'pdf')
                          <i class="bx bxs-file-pdf" style="color: #ef4444;"></i>
                        @else
                          <i class="bx bx-file"></i>
                        @endif
                      </div>
                      <div style="min-width: 0;">
                        <div class="att-name-text">{{ $att->original_name }}</div>
                        <div class="att-size-text">{{ round($att->file_size / 1024, 1) }} KB • Click to download</div>
                      </div>
                    </div>
                    <i class="bx bx-download" style="color: #2F6BFF; font-size: 16px;"></i>
                  </a>

                  <!-- Image Preview Thumbnail if image -->
                  @if($isImg)
                    <a href="{{ $fileUrl }}" target="_blank">
                      <img src="{{ $fileUrl }}" alt="{{ $att->original_name }}" class="att-image-thumb" />
                    </a>
                  @endif
                @endforeach
              </div>
            @endif
          </div>
        </div>
@endforeach
