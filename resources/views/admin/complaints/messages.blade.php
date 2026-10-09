@foreach($messages as $conv)
          @php $isSelf = $conv->sender_type === 'company_admin'; @endphp
          <div data-message-id="{{ $conv->id }}" class="d-flex {{ $isSelf ? 'justify-content-start' : 'justify-content-end' }}">
            <div class="chat-msg-bubble {{ $isSelf ? 'chat-msg-self' : 'chat-msg-other' }}">
              <div class="chat-msg-meta mb-1">
                {{ $conv->sender_name }} • {{ $conv->created_at?->format('d M, h:i A') }} ({{ $isSelf ? 'You' : 'Super Admin' }})
              </div>
              <div class="fs-6 chat-msg-body" style="white-space: pre-wrap; line-height: 1.5;">{{ $conv->message }}</div>

              @if($conv->attachments->count() > 0)
                <div class="mt-2 pt-2 border-top">
                  <div class="fs-8 fw-bold mb-1">Attachments:</div>
                  @foreach($conv->attachments as $att)
                    <a href="{{ route('admin.company-complaints.attachment', [$ticket->id, $att->id]) }}" target="_blank" class="fs-7 text-primary d-block text-decoration-none">
                      <i class="bx bx-paperclip"></i> {{ $att->original_name }} ({{ round($att->file_size / 1024, 1) }} KB)
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
@endforeach
