@php $taskComments = $task->comments ?? collect(); @endphp
<div class="tcm" id="taskComments">
    {{-- Comment form --}}
    <section class="tcm-card tcm-compose" aria-labelledby="tcmComposeTitle">
        <header class="tcm-head">
            <span class="tcm-head-icon is-primary" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/></svg>
            </span>
            <div>
                <h6 id="tcmComposeTitle">Leave a Comment</h6>
                <p>Share questions or updates with everyone on this task.</p>
            </div>
        </header>
        <form action="{{ route('task-comments.store', $task->id) }}" method="POST" class="tcm-form">
            @csrf
            <label for="tcmComment" class="tcm-sr">Comment</label>
            <textarea id="tcmComment" name="comment" rows="3" placeholder="Write your thoughts, questions, or updates on this task..." required></textarea>
            <div class="tcm-form-foot">
                <small>Visible to everyone who can open this task.</small>
                <button type="submit" class="tcm-send">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg>
                    <span>Post Comment</span>
                </button>
            </div>
        </form>
    </section>

    {{-- Discussion history --}}
    <section class="tcm-card" aria-labelledby="tcmHistoryTitle">
        <header class="tcm-head">
            <span class="tcm-head-icon is-success" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v4l-4-4H9a2 2 0 0 1-2-2v-1"/><path d="M15 4H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2v4l4-4h4a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/></svg>
            </span>
            <div>
                <h6 id="tcmHistoryTitle">Discussion History</h6>
                <p>{{ $taskComments->count() ? $taskComments->count() . ' ' . \Illuminate\Support\Str::plural('comment', $taskComments->count()) : 'No comments yet' }}</p>
            </div>
        </header>

        @if($taskComments->count())
            <ol class="tcm-list">
                @foreach($taskComments as $comment)
                    @php $authorName = $comment->user->name ?? 'Unknown'; @endphp
                    <li class="tcm-item">
                        <span class="tcm-avatar" aria-hidden="true">{{ strtoupper(mb_substr(trim($authorName) ?: 'U', 0, 1)) }}</span>
                        <div class="tcm-body">
                            <div class="tcm-meta">
                                <strong>{{ $authorName }}</strong>
                                @if($comment->created_at)
                                    <time datetime="{{ $comment->created_at->toIso8601String() }}" title="{{ $comment->created_at->format('d M Y, h:i A') }}">{{ $comment->created_at->diffForHumans() }}</time>
                                @endif
                            </div>
                            <p class="tcm-text">{{ $comment->comment }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <div class="tcm-empty">
                <span class="tcm-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M8 11h.01M12 11h.01M16 11h.01"/></svg>
                </span>
                <strong>No comments posted yet</strong>
                <span>Be the first to start the discussion on this task.</span>
                <button type="button" class="tcm-empty-action" onclick="document.getElementById('tcmComment')?.focus()">Write a comment</button>
            </div>
        @endif
    </section>
</div>

<style>
    #taskComments {
        --tcm-surface: #ffffff;
        --tcm-soft: #f6f7fc;
        --tcm-ink: #10142c;
        --tcm-muted: #545d82;
        --tcm-faint: #848cb0;
        --tcm-border: rgba(16, 20, 44, .09);
        --tcm-primary: #2f6bff;
        --tcm-primary-soft: rgba(47, 107, 255, .1);
        --tcm-success: #10b981;
        --tcm-success-soft: rgba(16, 185, 129, .12);
        --tcm-focus: rgba(47, 107, 255, .2);
        --tcm-shadow: 0 1px 3px rgba(15, 23, 42, .05), 0 1px 2px rgba(15, 23, 42, .03);
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 8px 0 4px;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark, body.dark-mode) #taskComments {
        --tcm-surface: #0f1530;
        --tcm-soft: #141b3d;
        --tcm-ink: #eef1fb;
        --tcm-muted: #9aa3c7;
        --tcm-faint: #6b739a;
        --tcm-border: rgba(238, 241, 251, .1);
        --tcm-primary-soft: rgba(47, 107, 255, .18);
        --tcm-success: #34d399;
        --tcm-success-soft: rgba(52, 211, 153, .14);
        --tcm-focus: rgba(47, 107, 255, .4);
        --tcm-shadow: 0 2px 8px rgba(0, 0, 0, .25);
    }
    /* The dark theme forces every SVG to white; these icons take their colour from their own badge. */
    html #taskComments svg { color: inherit !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) #taskComments svg { color: inherit !important; }
    #taskComments .tcm-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    #taskComments .tcm-card { border: 1px solid var(--tcm-border); border-radius: 16px; background: var(--tcm-surface) !important; box-shadow: var(--tcm-shadow); padding: 18px 20px; }
    #taskComments .tcm-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    #taskComments .tcm-head-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: 10px; display: grid; place-items: center; }
    #taskComments .tcm-head-icon svg { width: 18px; height: 18px; }
    #taskComments .tcm-head-icon.is-primary { background: var(--tcm-primary-soft); color: var(--tcm-primary); }
    #taskComments .tcm-head-icon.is-success { background: var(--tcm-success-soft); color: var(--tcm-success); }
    #taskComments .tcm-head h6 { margin: 0; font-size: .95rem; font-weight: 700; color: var(--tcm-ink) !important; -webkit-text-fill-color: var(--tcm-ink) !important; }
    #taskComments .tcm-head p { margin: 2px 0 0; font-size: .8rem; color: var(--tcm-muted) !important; -webkit-text-fill-color: var(--tcm-muted) !important; }

    #taskComments .tcm-form textarea { display: block; width: 100%; min-height: 96px; padding: 12px 14px; border: 1px solid var(--tcm-border) !important; border-radius: 12px !important; background: var(--tcm-soft) !important; color: var(--tcm-ink) !important; -webkit-text-fill-color: var(--tcm-ink) !important; font: inherit; font-size: .9rem; line-height: 1.55; resize: vertical; outline: none; box-shadow: none !important; transition: border-color .15s, box-shadow .15s, background .15s; }
    #taskComments .tcm-form textarea::placeholder { color: var(--tcm-faint) !important; -webkit-text-fill-color: var(--tcm-faint) !important; opacity: 1; }
    #taskComments .tcm-form textarea:focus { border-color: var(--tcm-primary) !important; background: var(--tcm-surface) !important; box-shadow: 0 0 0 4px var(--tcm-focus) !important; }
    #taskComments .tcm-form-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 12px; flex-wrap: wrap; }
    #taskComments .tcm-form-foot small { font-size: .76rem; color: var(--tcm-faint) !important; -webkit-text-fill-color: var(--tcm-faint) !important; }
    #taskComments .tcm-send { display: inline-flex !important; align-items: center; gap: 8px; height: 40px; padding: 0 18px !important; border: 0 !important; border-radius: 999px !important; background: linear-gradient(135deg, #2f6bff, #8b5cf6) !important; color: #fff !important; -webkit-text-fill-color: #fff !important; font: inherit; font-size: .86rem; font-weight: 600; cursor: pointer; width: auto !important; min-height: 0 !important; box-shadow: 0 6px 16px rgba(47, 107, 255, .28) !important; transition: transform .15s, filter .15s; }
    #taskComments .tcm-send svg { width: 16px; height: 16px; color: #fff; }
    #taskComments .tcm-send span { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #taskComments .tcm-send:hover { transform: translateY(-1px); filter: brightness(1.05); }
    #taskComments .tcm-send:focus-visible, #taskComments .tcm-empty-action:focus-visible { outline: 3px solid var(--tcm-focus); outline-offset: 2px; }

    #taskComments .tcm-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
    #taskComments .tcm-item { display: flex; gap: 12px; padding: 12px 14px; border: 1px solid var(--tcm-border); border-radius: 14px; background: var(--tcm-soft); }
    #taskComments .tcm-avatar { width: 34px; height: 34px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #2f6bff, #10b981); color: #fff !important; -webkit-text-fill-color: #fff !important; font-weight: 700; font-size: .82rem; }
    #taskComments .tcm-body { min-width: 0; flex: 1; }
    #taskComments .tcm-meta { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    #taskComments .tcm-meta strong { font-size: .88rem; color: var(--tcm-ink) !important; -webkit-text-fill-color: var(--tcm-ink) !important; }
    #taskComments .tcm-meta time { font-size: .75rem; color: var(--tcm-faint) !important; -webkit-text-fill-color: var(--tcm-faint) !important; white-space: nowrap; }
    #taskComments .tcm-text { margin: 4px 0 0; font-size: .9rem; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; color: var(--tcm-muted) !important; -webkit-text-fill-color: var(--tcm-muted) !important; }

    #taskComments .tcm-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; padding: 28px 16px; border: 1px dashed var(--tcm-border); border-radius: 14px; background: var(--tcm-soft); }
    #taskComments .tcm-empty-icon { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; background: var(--tcm-primary-soft); color: var(--tcm-primary); margin-bottom: 6px; }
    #taskComments .tcm-empty-icon svg { width: 26px; height: 26px; }
    #taskComments .tcm-empty strong { font-size: .95rem; color: var(--tcm-ink) !important; -webkit-text-fill-color: var(--tcm-ink) !important; }
    #taskComments .tcm-empty > span:not(.tcm-empty-icon) { font-size: .84rem; color: var(--tcm-muted) !important; -webkit-text-fill-color: var(--tcm-muted) !important; }
    #taskComments .tcm-empty-action { margin-top: 8px; padding: 7px 16px !important; border: 1px solid var(--tcm-primary) !important; border-radius: 999px !important; background: transparent !important; color: var(--tcm-primary) !important; -webkit-text-fill-color: var(--tcm-primary) !important; font: inherit; font-size: .8rem; font-weight: 600; cursor: pointer; width: auto !important; min-height: 0 !important; box-shadow: none !important; }
    #taskComments .tcm-empty-action:hover { background: var(--tcm-primary-soft) !important; }

    @media (max-width: 575.98px) {
        #taskComments .tcm-card { padding: 16px; }
        #taskComments .tcm-form-foot small { display: none; }
        #taskComments .tcm-send { width: 100% !important; justify-content: center; }
    }
</style>
