@extends('admin.layout.app')

@section('title', 'Community - Company-wide Communication')

@push('styles')
<style>
    /* =========================================================================
     | COMMUNITY CHAT MODULE - MODERN EXECUTIVE DESIGN SYSTEM
     | ========================================================================= */
    .community-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 160px);
        min-height: 600px;
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(15, 116, 76, 0.12);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    /* HEADER BAR */
    .community-header {
        padding: 16px 24px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        z-index: 10;
    }

    .community-title-box {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .community-icon-avatar {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        flex-shrink: 0;
    }
    .community-icon-avatar i {
        color: #ffffff !important;
        font-size: 1.85rem !important;
        line-height: 1 !important;
        display: block !important;
    }

    /* PINNED BANNER */
    .pinned-banner {
        background: #fffbeb;
        border-bottom: 1px solid #fef3c7;
        padding: 12px 24px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 0.85rem;
    }
    .pinned-banner-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .btn-pinned-toggle {
        border-radius: 20px;
        font-weight: 700;
        padding: 5px 14px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .btn-pinned-toggle:hover {
        background: #fffbeb;
        border-color: #fde047;
        color: #854d0e;
    }
    .btn-pinned-badge {
        background: #fef08a;
        color: #854d0e;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 12px;
    }

    /* TIMELINE FEED */
    .chat-timeline {
        flex: 1;
        padding: 24px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 16px;
        scroll-behavior: smooth;
    }

    /* DATE SEPARATOR */
    .date-separator {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 14px 0;
        position: relative;
    }
    .date-separator::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        height: 1px;
        background: #e2e8f0;
        z-index: 1;
    }
    .date-separator-badge {
        position: relative;
        z-index: 2;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 4px 18px;
        border-radius: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    /* MESSAGE BUBBLES & LAYOUT */
    .message-wrapper {
        display: flex;
        gap: 12px;
        max-width: 72%;
        margin-bottom: 4px;
        position: relative;
    }
    .message-wrapper.message-other {
        align-self: flex-start;
        flex-direction: row;
    }
    .message-wrapper.message-self {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    /* AVATARS & INITIALS BADGE */
    .msg-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .user-avatar-initials {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f744c 0%, #094c32 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.82rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(15, 116, 76, 0.2);
        letter-spacing: 0.5px;
    }

    /* MESSAGE CARD STYLING */
    .msg-content-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 12px 18px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        position: relative;
        min-width: 200px;
        max-width: 100%;
    }
    .message-other .msg-content-card {
        border-left: 4px solid #0f744c;
        border-top-left-radius: 4px;
        background: #ffffff;
    }
    .message-self .msg-content-card {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-top-right-radius: 4px;
    }

    .msg-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }
    .msg-sender-name {
        font-weight: 700;
        font-size: 0.88rem;
        color: #0f172a;
    }
    .msg-role-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .role-admin { background: #fef3c7; color: #92400e; }
    .role-hr { background: #f3e8ff; color: #6b21a8; }
    .role-manager { background: #dbeafe; color: #1e40af; }
    .role-employee { background: #f1f5f9; color: #475569; }

    .msg-time {
        font-size: 0.72rem;
        color: #94a3b8;
        font-weight: 600;
        margin-left: auto;
    }

    .msg-body-text {
        font-size: 0.93rem;
        color: #334155;
        line-height: 1.5;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .msg-edited-tag {
        font-size: 0.72rem;
        color: #94a3b8;
        font-style: italic;
        margin-left: 6px;
    }

    /* DELETED MESSAGE STYLING */
    .msg-deleted-card {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #64748b;
        font-style: italic;
        font-size: 0.85rem;
        padding: 8px 14px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* QUOTED REPLY BLOCK */
    .reply-quote-box {
        background: rgba(15, 116, 76, 0.07);
        border-left: 3.5px solid #0f744c;
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .message-self .reply-quote-box {
        background: rgba(15, 116, 76, 0.12);
    }
    .reply-quote-box:hover {
        background: rgba(15, 116, 76, 0.18);
    }
    .reply-quote-sender {
        font-weight: 700;
        font-size: 0.78rem;
        color: #0f744c;
        margin-bottom: 2px;
    }
    .reply-quote-text {
        font-size: 0.82rem;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ATTACHMENT CARDS */
    .msg-attachment-img {
        max-width: 100%;
        max-height: 260px;
        border-radius: 12px;
        object-fit: cover;
        margin-top: 8px;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .msg-attachment-doc {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 14px;
        margin-top: 8px;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }
    .msg-attachment-doc:hover {
        border-color: #0f744c;
        background: #f0fdf4;
    }

    /* EMOJI REACTIONS BAR */
    .msg-reactions-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }
    .reaction-pill {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        padding: 3px 10px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }
    .reaction-pill.user-has-reacted {
        background: #dcfce7;
        border-color: #86efac;
        color: #0f744c;
    }
    .reaction-pill:hover {
        transform: scale(1.08);
    }

    /* MESSAGE ACTION DROPDOWN */
    .msg-action-trigger {
        position: absolute;
        top: 8px;
        right: 8px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .msg-content-card:hover .msg-action-trigger {
        opacity: 1;
    }
    .msg-action-btn {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #f1f5f9;
        border: none;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    /* COMPOSER BAR */
    .community-composer {
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        padding: 14px 24px;
    }

    .reply-preview-bar {
        background: #f0fdf4;
        border-left: 3.5px solid #0f744c;
        padding: 8px 14px;
        border-radius: 10px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .composer-form-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 28px;
        padding: 6px 16px;
        transition: all 0.2s ease;
    }
    .composer-form-box:focus-within {
        border-color: #0f744c;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(15, 116, 76, 0.12);
    }

    .composer-textarea {
        flex: 1;
        border: none;
        background: transparent;
        resize: none;
        max-height: 120px;
        padding: 8px 0;
        font-size: 0.95rem;
        outline: none !important;
        box-shadow: none !important;
    }

    .composer-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .composer-action-icon:hover {
        background: #e2e8f0;
        color: #0f744c;
    }

    .btn-send-msg {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #0f744c 0%, #094c32 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        box-shadow: 0 3px 12px rgba(15, 116, 76, 0.35);
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .btn-send-msg:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
    }

    /* EMOJI POPOVER */
    .emoji-popover {
        position: absolute;
        bottom: 75px;
        right: 80px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 18px;
        padding: 12px;
        box-shadow: 0 12px 35px rgba(0,0,0,0.16);
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
        z-index: 100;
    }
    .emoji-item {
        font-size: 1.5rem;
        cursor: pointer;
        padding: 6px;
        border-radius: 10px;
        text-align: center;
        transition: transform 0.15s ease;
    }
    .emoji-item:hover {
        transform: scale(1.3);
        background: #f1f5f9;
    }

    /* UNIVERSAL PREMIUM CLOSE BUTTONS */
    .btn-close-premium {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.25rem !important;
        font-weight: bold !important;
        cursor: pointer;
        transition: all 0.2s ease !important;
        margin-left: auto !important;
    }
    .btn-close-premium:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- MAIN COMMUNITY CHAT CONTAINER --}}
    <div class="community-container" id="communityContainer">

        {{-- 1. HEADER BAR --}}
        <div class="community-header">
            <div class="community-title-box">
                <div class="community-icon-avatar">
                    <i class="bx bx-chat"></i>
                </div>
                <div>
                    <h5 class="fw-extrabold text-dark mb-0 d-flex align-items-center gap-2">
                        Community
                        <span class="badge bg-label-success rounded-pill px-2.5 py-1" style="font-size: 11px;">Company-Wide</span>
                    </h5>
                    <span class="text-muted small fw-semibold">
                        Official communication channel • {{ number_format($totalMembers) }} {{ Str::plural('member', $totalMembers) }}
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Search Toggle --}}
                <div class="position-relative d-none d-md-block" style="width: 220px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm rounded-pill ps-4" placeholder="Search messages..." oninput="handleSearch(this.value)">
                    <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-2.5 text-muted"></i>
                </div>

                {{-- Pinned Toggle Button --}}
                <button type="button" class="btn-pinned-toggle" onclick="togglePinnedView()">
                    <i class="bx bx-pin text-warning fs-5"></i>
                    <span class="d-none d-sm-inline">Pinned</span>
                    <span class="btn-pinned-badge" id="pinnedBadgeCount">{{ count($pinnedMessages) }}</span>
                </button>

                {{-- Info Drawer Button --}}
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#communityInfoModal">
                    <i class="bx bx-info-circle"></i> <span class="d-none d-sm-inline">Info</span>
                </button>
            </div>
        </div>

        {{-- 2. PINNED MESSAGES BANNER --}}
        <div class="pinned-banner d-none" id="pinnedBanner">
            <div class="pinned-banner-header">
                <div class="d-flex align-items-center gap-2 text-warning fw-bold">
                    <i class="bx bx-pin fs-5"></i> Pinned Announcements (<span id="pinnedBannerCount">{{ count($pinnedMessages) }}</span>)
                </div>
                <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2 text-decoration-none" onclick="togglePinnedView()">
                    <i class="bx bx-x fs-4"></i>
                </button>
            </div>
            <div id="pinnedSnippetContainer">
                @if(count($pinnedMessages) > 0)
                    @foreach($pinnedMessages as $pm)
                        <div class="d-flex align-items-center justify-content-between gap-2 py-1 border-bottom" style="cursor: pointer;" onclick="scrollToMessage({{ $pm->id }})">
                            <span class="fw-bold text-dark me-1">{{ $pm->user?->name ?? 'User' }}:</span>
                            <span class="text-secondary text-truncate flex-grow-1">{{ Str::limit($pm->message, 80) }}</span>
                            <span class="badge bg-warning text-dark extra-small">Jump &rarr;</span>
                        </div>
                    @endforeach
                @else
                    <span class="text-secondary text-truncate" id="pinnedSnippet">No pinned message</span>
                @endif
            </div>
        </div>

        {{-- 3. CHAT TIMELINE FEED --}}
        <div class="chat-timeline" id="chatTimeline">
            {{-- Loaded dynamically via JavaScript --}}
            <div class="text-center py-5" id="loadingSpinner">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading Community Messages...</span>
                </div>
            </div>
        </div>

        {{-- 4. COMPOSER BAR --}}
        <div class="community-composer">
            {{-- Quoted Reply Preview Bar --}}
            <div class="reply-preview-bar d-none" id="replyPreviewBar">
                <div class="text-truncate me-2">
                    <span class="fw-bold text-success small d-block" id="replySenderName">Replying to User</span>
                    <span class="text-muted small text-truncate d-block" id="replyTextSnippet">Message snippet...</span>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 fw-bold fs-5 text-decoration-none" onclick="cancelReply()">
                    &times;
                </button>
            </div>

            {{-- Selected Attachment Preview Bar --}}
            <div class="mb-2 d-none" id="attachmentPreviewBar">
                <div class="badge bg-light text-dark border p-2 d-inline-flex align-items-center gap-2">
                    <i class="bx bx-file text-primary fs-5"></i>
                    <span class="fw-semibold small text-truncate" id="attachmentFileName" style="max-width: 200px;">file.pdf</span>
                    <button type="button" class="btn-close ms-2" style="font-size: 10px;" onclick="clearSelectedAttachment()"></button>
                </div>
            </div>

            <form id="composerForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="replyParentId" value="">
                <input type="file" id="attachmentInput" class="d-none" onchange="handleAttachmentSelected(this.files)">

                <div class="composer-form-box">
                    {{-- File Attachment Button --}}
                    <button type="button" class="composer-action-icon" onclick="document.getElementById('attachmentInput').click()" title="Attach image or document">
                        <i class="bx bx-paperclip"></i>
                    </button>

                    {{-- Emoji Button --}}
                    <button type="button" class="composer-action-icon" onclick="toggleEmojiPicker()" title="Add Emoji">
                        <i class="bx bx-smile"></i>
                    </button>

                    {{-- Main Textarea --}}
                    <textarea id="messageInput" class="composer-textarea" rows="1" placeholder="Type a message..." oninput="handleMessageInput()" onkeydown="handleKeyDown(event)"></textarea>

                    {{-- Send Button --}}
                    <button type="submit" class="btn-send-msg" id="btnSend" disabled title="Send Message">
                        <i class="bx bx-send"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- EMOJI POPOVER --}}
    <div class="emoji-popover d-none" id="emojiPopover">
        <span class="emoji-item" onclick="insertEmoji('👍')">👍</span>
        <span class="emoji-item" onclick="insertEmoji('❤️')">❤️</span>
        <span class="emoji-item" onclick="insertEmoji('😂')">😂</span>
        <span class="emoji-item" onclick="insertEmoji('👏')">👏</span>
        <span class="emoji-item" onclick="insertEmoji('🎉')">🎉</span>
        <span class="emoji-item" onclick="insertEmoji('🙏')">🙏</span>
        <span class="emoji-item" onclick="insertEmoji('🔥')">🔥</span>
        <span class="emoji-item" onclick="insertEmoji('✅')">✅</span>
        <span class="emoji-item" onclick="insertEmoji('😊')">😊</span>
        <span class="emoji-item" onclick="insertEmoji('🙌')">🙌</span>
        <span class="emoji-item" onclick="insertEmoji('💡')">💡</span>
        <span class="emoji-item" onclick="insertEmoji('🚀')">🚀</span>
    </div>

</div>

{{-- COMMUNITY INFO MODAL --}}
<div class="modal fade" id="communityInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.18);">
            <div class="modal-header py-3 px-4 bg-light border-bottom d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-chat text-success fs-4"></i> Community Info
                </h5>
                <button type="button" class="btn-close-premium" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="community-icon-avatar mx-auto mb-3" style="width: 64px; height: 64px; font-size: 2rem;">
                    <i class="bx bx-group"></i>
                </div>
                <h5 class="fw-extrabold text-dark mb-1">Company Community</h5>
                <p class="text-muted small mb-3">Official internal communication space for all company employees.</p>
                
                <div class="row g-2 text-start mt-2">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="text-muted small d-block">Total Members</span>
                            <h4 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalMembers) }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <span class="text-muted small d-block">Channel Type</span>
                            <h4 class="fw-bold text-success mb-0 mt-1">Company Group</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    /* =========================================================================
     | COMMUNITY MESSAGING CORE JAVASCRIPT
     | ========================================================================= */
    const CURRENT_USER_ID = {{ auth()->id() }};
    const CAN_MANAGE = {{ $canManage ? 'true' : 'false' }};

    let lastLoadedMessageId = 0;
    let selectedFile = null;
    let currentReplyParentId = null;
    let pollingInterval = null;
    let isUserNearBottom = true;

    document.addEventListener('DOMContentLoaded', function() {
        fetchInitialMessages();

        // Start delta polling every 3.5 seconds
        pollingInterval = setInterval(pollNewMessages, 3500);

        // Timeline Scroll Listener
        const timeline = document.getElementById('chatTimeline');
        timeline.addEventListener('scroll', function() {
            const distanceToBottom = timeline.scrollHeight - timeline.clientHeight - timeline.scrollTop;
            isUserNearBottom = (distanceToBottom < 120);
        });
    });

    /**
     * Fetch initial messages from server
     */
    function fetchInitialMessages() {
        fetch(`{{ route('community.messages') }}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderMessagesTimeline(data.messages, true);
                if (data.messages.length > 0) {
                    lastLoadedMessageId = Math.max(...data.messages.map(m => m.id));
                }
                if (data.pinned_messages) {
                    updatePinnedUI(data.pinned_messages);
                }
                scrollToBottom();
            }
        })
        .catch(err => console.error('Error loading community messages:', err));
    }

    /**
     * Poll new messages (delta polling)
     */
    function pollNewMessages() {
        if (!lastLoadedMessageId) return;

        fetch(`{{ route('community.messages') }}?after_id=${lastLoadedMessageId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.messages && data.messages.length > 0) {
                    appendNewMessages(data.messages);
                    lastLoadedMessageId = Math.max(...data.messages.map(m => m.id));
                    if (isUserNearBottom) {
                        scrollToBottom();
                    }
                }
                if (data.pinned_messages) {
                    updatePinnedUI(data.pinned_messages);
                }
            }
        })
        .catch(err => console.error('Polling error:', err));
    }

    /**
     * Update Pinned Announcements UI
     */
    function updatePinnedUI(pinnedList) {
        const badgeCount = document.getElementById('pinnedBadgeCount');
        const bannerCount = document.getElementById('pinnedBannerCount');
        const container = document.getElementById('pinnedSnippetContainer');

        if (badgeCount) badgeCount.innerText = pinnedList.length;
        if (bannerCount) bannerCount.innerText = pinnedList.length;

        if (!container) return;

        if (!pinnedList || pinnedList.length === 0) {
            container.innerHTML = `<span class="text-secondary text-truncate">No pinned message</span>`;
            return;
        }

        let html = '';
        pinnedList.forEach((pm, idx) => {
            html += `
                <div class="d-flex align-items-center justify-content-between gap-2 py-1.5 ${idx > 0 ? 'border-top pt-2 mt-1' : ''}" style="cursor: pointer;" onclick="scrollToMessage(${pm.id})">
                    <span class="fw-bold text-dark me-1">${escapeHtml(pm.sender_name)}:</span>
                    <span class="text-secondary text-truncate flex-grow-1">${escapeHtml(pm.message || '📷 Attachment')}</span>
                    <span class="badge bg-warning text-dark extra-small">Jump &rarr;</span>
                </div>`;
        });

        container.innerHTML = html;
    }

    /**
     * Render Messages Timeline with Date Grouping
     */
    function renderMessagesTimeline(messages, replace = false) {
        const timeline = document.getElementById('chatTimeline');
        if (replace) {
            timeline.innerHTML = '';
        }

        if (messages.length === 0) {
            timeline.innerHTML = `
                <div class="text-center py-5" id="emptyStateBox">
                    <div class="community-icon-avatar mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.8rem; background: #e2e8f0; color: #64748b;">
                        <i class="bx bx-chat" style="color: #64748b !important;"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Welcome to Company Community</h6>
                    <p class="text-muted small">Start the conversation with your team!</p>
                </div>`;
            return;
        }

        let currentDateGroup = null;

        messages.forEach(m => {
            if (m.date_group !== currentDateGroup) {
                currentDateGroup = m.date_group;
                timeline.appendChild(createDateSeparator(currentDateGroup));
            }
            timeline.appendChild(createMessageCardElement(m));
        });
    }

    /**
     * Append New Polled Messages
     */
    function appendNewMessages(newMessages) {
        const timeline = document.getElementById('chatTimeline');
        const emptyState = document.getElementById('emptyStateBox');
        if (emptyState) emptyState.remove();

        newMessages.forEach(m => {
            // Prevent duplicate insertion
            if (document.getElementById(`msgCard_${m.id}`)) return;

            timeline.appendChild(createMessageCardElement(m));
        });
    }

    /**
     * Create Date Separator DOM Element
     */
    function createDateSeparator(dateText) {
        const div = document.createElement('div');
        div.className = 'date-separator';
        div.innerHTML = `<span class="date-separator-badge">${escapeHtml(dateText)}</span>`;
        return div;
    }

    /**
     * Render Avatar (Image or Sleek Initials Badge)
     */
    function renderAvatarHtml(m) {
        const initials = escapeHtml(m.sender_initials || 'U');
        if (m.avatar_url) {
            return `<img src="${m.avatar_url}" class="msg-avatar" alt="${escapeHtml(m.sender_name)}" onerror="this.outerHTML='<div class=\'user-avatar-initials\'>${initials}</div>'">`;
        }
        return `<div class="user-avatar-initials">${initials}</div>`;
    }

    /**
     * Create Message Card DOM Element
     */
    function createMessageCardElement(m) {
        const div = document.createElement('div');
        div.className = `message-wrapper ${m.is_self ? 'message-self' : 'message-other'}`;
        div.id = `msgCard_${m.id}`;

        const avatarMarkup = renderAvatarHtml(m);

        if (m.is_deleted) {
            div.innerHTML = `
                ${avatarMarkup}
                <div class="msg-deleted-card">
                    <i class="bx bx-block me-1"></i> ${escapeHtml(m.deleted_text)}
                </div>`;
            return div;
        }

        // Role Badge Class
        const roleClass = 'role-' + (m.sender_role.toLowerCase() || 'employee');

        // Quoted Reply HTML
        let replyHtml = '';
        if (m.parent) {
            replyHtml = `
                <div class="reply-quote-box" onclick="scrollToMessage(${m.parent.id})">
                    <div class="reply-quote-sender">${escapeHtml(m.parent.sender_name)}</div>
                    <div class="reply-quote-text">${escapeHtml(m.parent.message)}</div>
                </div>`;
        }

        // Attachment HTML
        let attachmentHtml = '';
        if (m.attachment_url) {
            if (m.attachment_type === 'image') {
                attachmentHtml = `<img src="${m.attachment_url}" class="msg-attachment-img" onclick="window.open('${m.attachment_url}', '_blank')">`;
            } else {
                attachmentHtml = `
                    <a href="${m.attachment_url}" target="_blank" class="msg-attachment-doc">
                        <i class="bx bx-file text-success fs-4"></i>
                        <div class="text-truncate">
                            <span class="fw-bold text-dark small d-block text-truncate">${escapeHtml(m.attachment_name)}</span>
                            <span class="text-muted extra-small">${m.attachment_size_formatted || 'File'}</span>
                        </div>
                        <i class="bx bx-download text-muted ms-auto fs-5"></i>
                    </a>`;
            }
        }

        // Reactions HTML
        let reactionsHtml = '';
        if (m.reactions && m.reactions.length > 0) {
            reactionsHtml = `<div class="msg-reactions-bar">`;
            m.reactions.forEach(r => {
                reactionsHtml += `
                    <span class="reaction-pill ${r.user_reacted ? 'user-has-reacted' : ''}" onclick="toggleReaction(${m.id}, '${r.emoji}')" title="${escapeHtml(r.users.join(', '))}">
                        ${r.emoji} <span>${r.count}</span>
                    </span>`;
            });
            reactionsHtml += `</div>`;
        }

        // Dropdown Actions HTML
        let actionDropdown = `
            <div class="dropdown msg-action-trigger">
                <button type="button" class="msg-action-btn" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="min-width: 140px;">
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setReplyTarget(${m.id}, '${escapeHtml(m.sender_name)}', '${escapeHtml(m.message || 'Attachment')}')"><i class="bx bx-reply me-1.5 text-primary"></i> Reply</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="toggleReaction(${m.id}, '👍')"><i class="bx bx-like me-1.5 text-success"></i> Like 👍</a></li>
                    ${m.can_edit ? `<li><a class="dropdown-item small" href="javascript:void(0)" onclick="editMessage(${m.id}, '${escapeHtml(m.message)}')"><i class="bx bx-edit me-1.5 text-info"></i> Edit</a></li>` : ''}
                    ${CAN_MANAGE ? `<li><a class="dropdown-item small" href="javascript:void(0)" onclick="togglePinMessage(${m.id})"><i class="bx bx-pin me-1.5 text-warning"></i> ${m.is_pinned ? 'Unpin' : 'Pin'}</a></li>` : ''}
                    ${m.can_delete ? `<li><hr class="dropdown-divider my-1"></li><li><a class="dropdown-item small text-danger" href="javascript:void(0)" onclick="deleteMessage(${m.id})"><i class="bx bx-trash me-1.5"></i> Delete</a></li>` : ''}
                </ul>
            </div>`;

        div.innerHTML = `
            ${avatarMarkup}
            <div class="msg-content-card">
                ${actionDropdown}
                <div class="msg-header">
                    <span class="msg-sender-name">${escapeHtml(m.sender_name)}</span>
                    <span class="msg-role-badge ${roleClass}">${escapeHtml(m.sender_role)}</span>
                    <span class="msg-time">${m.time}</span>
                </div>
                ${replyHtml}
                ${m.message ? `<div class="msg-body-text">${escapeHtml(m.message)}${m.is_edited ? '<span class="msg-edited-tag">(Edited)</span>' : ''}</div>` : ''}
                ${attachmentHtml}
                ${reactionsHtml}
            </div>`;

        return div;
    }

    /**
     * Submit Form (Send Message)
     */
    document.getElementById('composerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const textInput = document.getElementById('messageInput');
        const text = textInput.value.trim();

        if (!text && !selectedFile) return;

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        if (text) formData.append('message', text);
        if (currentReplyParentId) formData.append('parent_id', currentReplyParentId);
        if (selectedFile) formData.append('attachment', selectedFile);

        const btnSend = document.getElementById('btnSend');
        btnSend.disabled = true;

        fetch(`{{ route('community.store') }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                textInput.value = '';
                cancelReply();
                clearSelectedAttachment();
                handleMessageInput();
                appendNewMessages([data.message]);
                lastLoadedMessageId = Math.max(lastLoadedMessageId, data.message.id);
                scrollToBottom();
            } else {
                alert(data.error || 'Failed to send message.');
            }
            btnSend.disabled = false;
        })
        .catch(err => {
            console.error('Send error:', err);
            btnSend.disabled = false;
        });
    });

    /**
     * Reply Target Handlers
     */
    function setReplyTarget(id, senderName, textSnippet) {
        currentReplyParentId = id;
        document.getElementById('replyParentId').value = id;
        document.getElementById('replySenderName').innerText = `Replying to ${senderName}`;
        document.getElementById('replyTextSnippet').innerText = textSnippet;
        document.getElementById('replyPreviewBar').classList.remove('d-none');
        document.getElementById('messageInput').focus();
    }

    function cancelReply() {
        currentReplyParentId = null;
        document.getElementById('replyParentId').value = '';
        document.getElementById('replyPreviewBar').classList.add('d-none');
    }

    /**
     * Attachment Selection Handlers
     */
    function handleAttachmentSelected(files) {
        if (!files || files.length === 0) return;
        selectedFile = files[0];
        document.getElementById('attachmentFileName').innerText = selectedFile.name;
        document.getElementById('attachmentPreviewBar').classList.remove('d-none');
        handleMessageInput();
    }

    function clearSelectedAttachment() {
        selectedFile = null;
        document.getElementById('attachmentInput').value = '';
        document.getElementById('attachmentPreviewBar').classList.add('d-none');
        handleMessageInput();
    }

    /**
     * Toggle Emoji Reaction
     */
    function toggleReaction(messageId, emoji) {
        fetch(`{{ url('community/messages') }}/${messageId}/react`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ emoji: emoji })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                pollNewMessages();
            }
        });
    }

    /**
     * Edit Own Message
     */
    function editMessage(id, currentText) {
        const newText = prompt('Edit your message:', currentText);
        if (newText === null) return;
        if (!newText.trim()) return alert('Message content cannot be empty.');

        fetch(`{{ url('community/messages') }}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message: newText.trim() })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`msgCard_${id}`);
                if (card) {
                    const newElem = createMessageCardElement(data.message);
                    card.replaceWith(newElem);
                }
            } else {
                alert(data.error || 'Failed to edit message.');
            }
        });
    }

    /**
     * Delete Message (Own or Moderation)
     */
    function deleteMessage(id) {
        if (!confirm('Are you sure you want to remove this message?')) return;

        fetch(`{{ url('community/messages') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`msgCard_${id}`);
                if (card) {
                    card.innerHTML = `
                        <div class="user-avatar-initials">U</div>
                        <div class="msg-deleted-card">
                            <i class="bx bx-block me-1"></i> ${data.is_moderated ? 'This message was removed by an administrator.' : 'This message was deleted.'}
                        </div>`;
                }
            } else {
                alert(data.error || 'Failed to delete message.');
            }
        });
    }

    /**
     * Pin / Unpin Message (Admin/Manager)
     */
    function togglePinMessage(id) {
        fetch(`{{ url('community/messages') }}/${id}/pin`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                pollNewMessages();
            } else {
                alert(data.error || 'Failed to toggle pin.');
            }
        });
    }

    /**
     * Scroll to specific message by ID
     */
    function scrollToMessage(id) {
        const elem = document.getElementById(`msgCard_${id}`);
        if (elem) {
            elem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            elem.classList.add('bg-warning-subtle');
            setTimeout(() => elem.classList.remove('bg-warning-subtle'), 1500);
        }
    }

    /**
     * Scroll Timeline to Bottom
     */
    function scrollToBottom() {
        const timeline = document.getElementById('chatTimeline');
        timeline.scrollTop = timeline.scrollHeight;
    }

    /**
     * Input handlers & Emoji Popover
     */
    function handleMessageInput() {
        const val = document.getElementById('messageInput').value.trim();
        const btnSend = document.getElementById('btnSend');
        btnSend.disabled = (!val && !selectedFile);
    }

    function handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('composerForm').dispatchEvent(new Event('submit'));
        }
    }

    function toggleEmojiPicker() {
        const popover = document.getElementById('emojiPopover');
        popover.classList.toggle('d-none');
    }

    function insertEmoji(emoji) {
        const input = document.getElementById('messageInput');
        input.value += emoji;
        handleMessageInput();
        document.getElementById('emojiPopover').classList.add('d-none');
        input.focus();
    }

    function togglePinnedView() {
        const banner = document.getElementById('pinnedBanner');
        banner.classList.toggle('d-none');
    }

    function handleSearch(query) {
        if (!query.trim()) {
            fetchInitialMessages();
            return;
        }
        fetch(`{{ route('community.messages') }}?search=${encodeURIComponent(query.trim())}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderMessagesTimeline(data.messages, true);
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endsection
