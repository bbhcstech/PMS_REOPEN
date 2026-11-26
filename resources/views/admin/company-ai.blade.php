@extends($assistantLayout ?? 'admin.layout.app')
@section('title', $assistantName)
@section('page_title', $assistantName)
@section('content')
@php
    $assistantSuggestions = $suggestions ?? [];
    $assistantLogo = asset('logos/bitroxia_logo.png');
    $supportsLocalAnswers = $supportsLocalAnswers ?? false;
    $canAsk = $configured || $supportsLocalAnswers;
    $assistantIntro = $assistantDescription ?? 'Ask anything about your workspace — holidays, office timings, attendance, leave, tasks, events, your team or how to do something. Answers come from live company records you are allowed to see.';
@endphp
@include('partials.ai-assistant-mark-styles')
<main id="company-ai-page" data-live-preserve class="{{ ($assistantLayout ?? '') === 'layouts.superadmin' ? 'company-ai-platform' : '' }}">
    <section class="cai-shell" aria-labelledby="assistant-display-name">
        <header class="cai-head">
            <div class="cai-orb pms-ai-assistant-mark" aria-hidden="true">
                <img src="{{ $assistantLogo }}" alt="" width="44" height="44">
            </div>
            <div class="cai-head-text">
                <h1 id="assistant-display-name">{{ $assistantName }}</h1>
                <p><span class="cai-live-dot" aria-hidden="true"></span>{{ $canAsk ? 'Answers from your workspace' : 'Waiting for AI service setup' }}</p>
            </div>
            <button type="button" class="cai-ghost" id="company-ai-reset" title="Start a new conversation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                <span>New chat</span>
            </button>
        </header>

        @unless($configured)
            <div class="cai-notice" role="status">
                {{ $supportsLocalAnswers ? 'Common questions work directly from workspace records. Broader questions need the AI service.' : 'A platform administrator needs to configure the AI service before this assistant can answer questions.' }}
            </div>
        @endunless

        <div class="cai-stream" id="company-ai-conversation" aria-live="polite">
            <div class="cai-welcome" id="company-ai-welcome">
                <div class="cai-orb pms-ai-assistant-mark is-large" aria-hidden="true">
                    <img src="{{ $assistantLogo }}" alt="" width="64" height="64">
                </div>
                <h2>Hello{{ auth()->user()?->name ? ', ' . strtok(auth()->user()->name, ' ') : '' }}! How can I help today?</h2>
                <p>{{ $assistantIntro }}</p>
                @if($assistantSuggestions)
                    <div class="cai-chips" role="list">
                        @foreach($assistantSuggestions as $suggestion)
                            <button type="button" class="cai-chip" role="listitem" data-question="{{ $suggestion }}" @disabled(!$canAsk)>{{ $suggestion }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <form id="company-ai-form" class="cai-composer" autocomplete="off">
            @csrf
            <label for="company-ai-question" class="cai-sr">Your question</label>
            <div class="cai-inputbox">
                <textarea id="company-ai-question" rows="1" maxlength="1000" required placeholder="Ask about holidays, leave, attendance, tasks, policies…" @disabled(!$canAsk)></textarea>
                <button id="company-ai-send" type="submit" class="cai-send" title="Send (Enter)" aria-label="Send question" @disabled(!$canAsk)>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </div>
            <div class="cai-foot">
                <small>Enter to send · Shift + Enter for a new line. Answers use records you can access and never change them.</small>
                <small id="company-ai-count">0 / 1000</small>
            </div>
        </form>
    </section>
</main>
<style>
    #company-ai-page {
        --cai-primary: #2F6BFF;
        --cai-primary-hover: #1E4FCC;
        --cai-violet: #8B5CF6;
        --cai-cyan: #22D3EE;
        --cai-bg: #F6F7FC;
        --cai-surface: #FFFFFF;
        --cai-surface-2: #F3F5FC;
        --cai-surface-3: #E9ECF9;
        --cai-ink: #10142C;
        --cai-muted: #545D82;
        --cai-faint: #848CB0;
        --cai-border: rgba(16, 20, 44, .09);
        --cai-border-strong: rgba(16, 20, 44, .16);
        --cai-focus: rgba(47, 107, 255, .3);
        --cai-success: #10B981;
        --cai-warn-bg: #FEF3C7;
        --cai-warn-ink: #92400E;
        --cai-shadow: 0 12px 24px -4px rgba(15, 23, 42, .08), 0 4px 8px -2px rgba(15, 23, 42, .03);
        --cai-gradient: linear-gradient(135deg, #2F6BFF 0%, #8B5CF6 100%);
        display: block;
        width: 100%;
        min-width: 0;
        padding: 20px 12px 28px;
        box-sizing: border-box;
        color: var(--cai-ink);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body.dark, body.dark-mode) #company-ai-page {
        --cai-primary-hover: #4F83FF;
        --cai-bg: #070B1A;
        --cai-surface: #0F1530;
        --cai-surface-2: #141B3D;
        --cai-surface-3: #1A2247;
        --cai-ink: #EEF1FB;
        --cai-muted: #9AA3C7;
        --cai-faint: #6B739A;
        --cai-border: rgba(238, 241, 251, .09);
        --cai-border-strong: rgba(238, 241, 251, .18);
        --cai-focus: rgba(47, 107, 255, .5);
        --cai-success: #34D399;
        --cai-warn-bg: rgba(245, 158, 11, .14);
        --cai-warn-ink: #FCD34D;
        --cai-shadow: 0 16px 36px -6px rgba(0, 0, 0, .5);
    }
    #company-ai-page *, #company-ai-page *::before, #company-ai-page *::after { box-sizing: border-box; }
    #company-ai-page .cai-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

    #company-ai-page .cai-shell { max-width: 1040px; margin: 0 auto; height: max(600px, calc(100vh - 190px)); display: flex; flex-direction: column; border: 1px solid var(--cai-border); border-radius: 24px; background: var(--cai-surface); box-shadow: var(--cai-shadow); overflow: hidden; position: relative; }
    #company-ai-page .cai-shell::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 220px; background: radial-gradient(600px 200px at 15% -40px, rgba(47, 107, 255, .14), transparent 70%), radial-gradient(500px 200px at 90% -60px, rgba(139, 92, 246, .14), transparent 70%); pointer-events: none; }

    #company-ai-page .cai-head { position: relative; display: flex; align-items: center; gap: 14px; padding: 18px 22px; border-bottom: 1px solid var(--cai-border); }
    #company-ai-page .cai-orb { width: 44px; height: 44px; flex-shrink: 0; box-shadow: 0 0 0 2px rgb(96 135 233 / .14), 0 5px 12px rgb(47 107 255 / .20); }
    #company-ai-page .cai-orb.is-large { width: 64px; height: 64px; }
    #company-ai-page .cai-orb.is-small { width: 32px; height: 32px; box-shadow: none; }
    #company-ai-page .cai-head-text { min-width: 0; flex: 1; }
    #company-ai-page .cai-head h1 { margin: 0; font-size: 1.08rem; font-weight: 700; line-height: 1.3; color: var(--cai-ink) !important; -webkit-text-fill-color: var(--cai-ink) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    #company-ai-page .cai-head p { margin: 3px 0 0; display: flex; align-items: center; gap: 6px; font-size: .8rem; color: var(--cai-muted) !important; -webkit-text-fill-color: var(--cai-muted) !important; }
    #company-ai-page .cai-live-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--cai-success); box-shadow: 0 0 0 3px rgba(16, 185, 129, .18); }
    #company-ai-page .cai-ghost { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border: 1px solid var(--cai-border-strong) !important; border-radius: 999px !important; background: var(--cai-surface) !important; color: var(--cai-ink) !important; -webkit-text-fill-color: var(--cai-ink) !important; font: inherit; font-size: .82rem; font-weight: 600; cursor: pointer; width: auto !important; min-height: 0 !important; box-shadow: none !important; }
    #company-ai-page .cai-ghost svg { width: 15px; height: 15px; }
    #company-ai-page .cai-ghost:hover { border-color: var(--cai-primary) !important; color: var(--cai-primary) !important; -webkit-text-fill-color: var(--cai-primary) !important; }
    #company-ai-page .cai-notice { margin: 14px 22px 0; padding: 12px 14px; border-radius: 12px; background: var(--cai-warn-bg); color: var(--cai-warn-ink) !important; -webkit-text-fill-color: var(--cai-warn-ink) !important; font-size: .86rem; line-height: 1.5; }

    #company-ai-page .cai-stream { position: relative; flex: 1; overflow-y: auto; padding: 22px; display: flex; flex-direction: column; gap: 16px; scroll-behavior: smooth; overscroll-behavior: contain; }
    #company-ai-page .cai-welcome { margin: auto; max-width: 640px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 12px 0; }
    #company-ai-page .cai-welcome h2 { margin: 8px 0 0; font-size: 1.45rem; font-weight: 800; line-height: 1.3; background: var(--cai-gradient); -webkit-background-clip: text; background-clip: text; color: transparent !important; -webkit-text-fill-color: transparent !important; }
    #company-ai-page .cai-welcome p { margin: 0; font-size: .92rem; line-height: 1.65; color: var(--cai-muted) !important; -webkit-text-fill-color: var(--cai-muted) !important; }
    #company-ai-page .cai-chips { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; width: 100%; margin-top: 14px; }
    #company-ai-page .cai-chip { display: block; width: 100% !important; min-height: 0 !important; padding: 12px 14px !important; border: 1px solid var(--cai-border) !important; border-radius: 14px !important; background: var(--cai-surface-2) !important; color: var(--cai-ink) !important; -webkit-text-fill-color: var(--cai-ink) !important; font: inherit; font-size: .86rem; font-weight: 500; line-height: 1.4; text-align: left; cursor: pointer; box-shadow: none !important; transition: border-color .15s, transform .15s, background .15s; }
    #company-ai-page .cai-chip:hover:not(:disabled) { border-color: var(--cai-primary) !important; background: var(--cai-surface) !important; transform: translateY(-1px); }
    #company-ai-page .cai-chip:disabled { opacity: .55; cursor: not-allowed; }

    #company-ai-page .cai-row { display: flex; gap: 10px; align-items: flex-start; max-width: 100%; animation: caiIn .22s ease; }
    #company-ai-page .cai-row.is-user { justify-content: flex-end; }
    #company-ai-page .cai-bubble { max-width: min(760px, 86%); padding: 12px 16px; border-radius: 18px; font-size: .92rem; line-height: 1.6; overflow-wrap: anywhere; }
    #company-ai-page .cai-row.is-user .cai-bubble { background: var(--cai-gradient); color: #fff !important; -webkit-text-fill-color: #fff !important; border-bottom-right-radius: 6px; white-space: pre-wrap; }
    #company-ai-page .cai-row.is-assistant .cai-bubble { background: var(--cai-surface-2); border: 1px solid var(--cai-border); color: var(--cai-ink) !important; -webkit-text-fill-color: var(--cai-ink) !important; border-top-left-radius: 6px; }
    #company-ai-page .cai-row.is-error .cai-bubble { background: var(--cai-warn-bg); border-color: transparent; color: var(--cai-warn-ink) !important; -webkit-text-fill-color: var(--cai-warn-ink) !important; }
    #company-ai-page .cai-retry { margin-top: 8px; }
    #company-ai-page .cai-row.is-error .cai-chips { justify-content: flex-start; margin-top: 10px; }
    #company-ai-page .cai-bubble :is(p, ul, ol) { margin: 0; color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #company-ai-page .cai-bubble :is(p, ul, ol) + :is(p, ul, ol) { margin-top: 8px; }
    #company-ai-page .cai-bubble :is(ul, ol) { padding-left: 20px; }
    #company-ai-page .cai-bubble li { margin: 2px 0; color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #company-ai-page .cai-bubble strong { font-weight: 700; color: inherit !important; -webkit-text-fill-color: currentColor !important; }
    #company-ai-page .cai-bubble a { color: var(--cai-primary) !important; -webkit-text-fill-color: var(--cai-primary) !important; font-weight: 600; text-decoration: underline; text-underline-offset: 2px; }
    #company-ai-page .cai-sources { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--cai-border-strong); font-size: .74rem; color: var(--cai-muted) !important; -webkit-text-fill-color: var(--cai-muted) !important; }
    #company-ai-page .cai-source { padding: 2px 9px; border-radius: 999px; background: var(--cai-surface); border: 1px solid var(--cai-border); color: var(--cai-muted) !important; -webkit-text-fill-color: var(--cai-muted) !important; font-weight: 600; }
    #company-ai-page .cai-typing { display: inline-flex; gap: 5px; align-items: center; padding: 16px 18px; }
    #company-ai-page .cai-typing span { width: 7px; height: 7px; border-radius: 50%; background: var(--cai-primary); opacity: .35; animation: caiDot 1.1s infinite ease-in-out; }
    #company-ai-page .cai-typing span:nth-child(2) { animation-delay: .15s; background: #6366F1; }
    #company-ai-page .cai-typing span:nth-child(3) { animation-delay: .3s; background: var(--cai-violet); }
    @keyframes caiDot { 0%, 80%, 100% { opacity: .3; transform: translateY(0); } 40% { opacity: 1; transform: translateY(-4px); } }
    @keyframes caiIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    #company-ai-page .cai-composer { position: relative; padding: 14px 22px 16px; border-top: 1px solid var(--cai-border); background: var(--cai-surface); }
    #company-ai-page .cai-inputbox { display: flex; align-items: flex-end; gap: 10px; padding: 8px 8px 8px 16px; border: 1px solid var(--cai-border-strong); border-radius: 18px; background: var(--cai-surface-2); transition: border-color .15s, box-shadow .15s; }
    #company-ai-page .cai-inputbox:focus-within { border-color: var(--cai-primary); box-shadow: 0 0 0 4px var(--cai-focus); }
    #company-ai-page #company-ai-question { flex: 1; min-height: 24px; max-height: 160px; margin: 0; padding: 8px 0 !important; border: 0 !important; outline: 0 !important; box-shadow: none !important; background: transparent !important; color: var(--cai-ink) !important; -webkit-text-fill-color: var(--cai-ink) !important; font: inherit; font-size: .93rem; line-height: 1.5; resize: none; overflow-y: auto; }
    #company-ai-page #company-ai-question::placeholder { color: var(--cai-faint) !important; -webkit-text-fill-color: var(--cai-faint) !important; opacity: 1; }
    #company-ai-page .cai-send { width: 42px !important; height: 42px !important; min-width: 42px; min-height: 0 !important; flex-shrink: 0; padding: 0 !important; border: 0 !important; border-radius: 50% !important; display: grid !important; place-items: center; background: var(--cai-gradient) !important; color: #fff !important; cursor: pointer; box-shadow: 0 8px 18px rgba(47, 107, 255, .3) !important; transition: transform .15s, opacity .15s; }
    #company-ai-page .cai-send svg { width: 19px; height: 19px; color: #fff; stroke: #fff; }
    #company-ai-page .cai-send:hover:not(:disabled) { transform: translateY(-1px); }
    #company-ai-page :is(.cai-send, .cai-ghost, .cai-chip):focus-visible { outline: 3px solid var(--cai-focus); outline-offset: 2px; }
    #company-ai-page :is(.cai-send, #company-ai-question):disabled { opacity: .55; cursor: not-allowed; }
    #company-ai-page .cai-foot { display: flex; justify-content: space-between; gap: 12px; margin-top: 8px; padding: 0 4px; }
    #company-ai-page .cai-foot small { font-size: .74rem; line-height: 1.5; color: var(--cai-faint) !important; -webkit-text-fill-color: var(--cai-faint) !important; }
    #company-ai-page .cai-foot small:last-child { flex-shrink: 0; }

    @media (max-width: 767.98px) {
        #company-ai-page { padding: 12px 0 18px; }
        #company-ai-page .cai-shell { height: max(540px, calc(100vh - 150px)); border-radius: 18px; }
        #company-ai-page .cai-head { padding: 14px 16px; }
        #company-ai-page .cai-ghost span { display: none; }
        #company-ai-page .cai-ghost { width: 38px !important; padding: 0 !important; justify-content: center; }
        #company-ai-page .cai-stream { padding: 16px; }
        #company-ai-page .cai-chips { grid-template-columns: 1fr; }
        #company-ai-page .cai-bubble { max-width: 92%; }
        #company-ai-page .cai-composer { padding: 12px 14px 14px; }
        #company-ai-page .cai-foot small:first-child { display: none; }
        #company-ai-page .cai-welcome h2 { font-size: 1.2rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        #company-ai-page .cai-row, #company-ai-page .cai-typing span { animation: none; }
    }
</style>
@endsection
@push($scriptStack ?? 'js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('company-ai-form');
    const input = document.getElementById('company-ai-question');
    const button = document.getElementById('company-ai-send');
    const conversation = document.getElementById('company-ai-conversation');
    const welcome = document.getElementById('company-ai-welcome');
    const counter = document.getElementById('company-ai-count');
    const assistantAvatar = document.querySelector('#company-ai-page .cai-head .cai-orb');
    let busy = false;

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }
    function messageAvatar() {
        const avatar = assistantAvatar.cloneNode(true);
        avatar.classList.add('is-small');
        return avatar;
    }
    // Inline formatting: **bold** and in-app paths (/leaves/create) become links. Built with DOM nodes only.
    function inline(target, text) {
        const pattern = /(\*\*[^*]+\*\*)|((?:^|(?<=[\s(]))\/[A-Za-z0-9\-_/]+[A-Za-z0-9\-_])/g;
        let last = 0, match;
        while ((match = pattern.exec(text))) {
            if (match.index > last) target.append(text.slice(last, match.index));
            if (match[1]) target.append(el('strong', '', match[1].slice(2, -2)));
            else { const link = el('a', '', match[2]); link.href = match[2]; target.append(link); }
            last = match.index + match[0].length;
        }
        if (last < text.length) target.append(text.slice(last));
    }
    function formatted(text) {
        const wrap = document.createDocumentFragment();
        let list = null, listType = null, paragraph = null;
        String(text || '').split(/\r?\n/).forEach(raw => {
            const line = raw.trim();
            const bullet = line.match(/^[-*•]\s+(.*)$/);
            const numbered = line.match(/^\d+[.)]\s+(.*)$/);
            if (bullet || numbered) {
                const type = bullet ? 'ul' : 'ol';
                if (!list || listType !== type) { list = el(type); listType = type; wrap.append(list); }
                const item = el('li'); inline(item, (bullet || numbered)[1]); list.append(item);
                paragraph = null;
                return;
            }
            list = null; listType = null;
            if (!line) { paragraph = null; return; }
            if (!paragraph) { paragraph = el('p'); wrap.append(paragraph); } else { paragraph.append(document.createElement('br')); }
            inline(paragraph, line);
        });
        return wrap;
    }
    function scrollDown() { conversation.scrollTop = conversation.scrollHeight; }
    function add(kind, text, sources) {
        if (welcome) welcome.remove();
        const row = el('div', 'cai-row is-' + kind);
        if (kind !== 'user') row.append(messageAvatar());
        const bubble = el('div', 'cai-bubble');
        if (kind === 'user') bubble.textContent = text; else bubble.append(formatted(text));
        if (sources && sources.length) {
            const meta = el('div', 'cai-sources');
            meta.append('Based on');
            const seen = new Set();
            sources.forEach(source => {
                const label = source.label || String(source.type || 'record').replace(/[_-]+/g, ' ').replace(/^./, c => c.toUpperCase());
                if (seen.has(label)) return; seen.add(label);
                meta.append(el('span', 'cai-source', label));
            });
            bubble.append(meta);
        }
        row.append(bubble);
        conversation.append(row);
        // Keep only this tab's recent display; no shared browser or server chat cache.
        while (conversation.querySelectorAll('.cai-row').length > 40) conversation.querySelector('.cai-row').remove();
        scrollDown();
        return row;
    }
    function typing() {
        const row = el('div', 'cai-row is-assistant');
        row.append(messageAvatar());
        const bubble = el('div', 'cai-bubble cai-typing'); bubble.setAttribute('aria-label', 'Assistant is typing');
        bubble.append(el('span'), el('span'), el('span'));
        row.append(bubble);
        conversation.append(row); scrollDown();
        return row;
    }
    function resize() {
        input.style.height = 'auto';
        input.style.height = Math.min(160, input.scrollHeight) + 'px';
        counter.textContent = input.value.length + ' / 1000';
    }
    async function ask(question, retryRow = null) {
        if (!question || busy || button.disabled) return;
        busy = true; button.disabled = true; input.disabled = true;
        if (retryRow) retryRow.remove();
        else add('user', question);
        input.value = ''; resize();
        const pending = typing();
        const controller = new AbortController();
        const deadline = setTimeout(() => controller.abort(), 45000);
        let retryable = true;
        let knownQuestions = [];
        try {
            const response = await fetch(@json($askUrl), {
                method: 'POST', cache: 'no-store', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Company-Workspace': @json($workspaceKey), 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: JSON.stringify({ question }), signal: controller.signal
            });
            if (response.status === 401 || response.status === 419) { location.replace(@json(route('login'))); return; }
            if (response.status === 409) { location.reload(); return; }
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                retryable = response.status >= 500 && data.retryable !== false;
                knownQuestions = Array.isArray(data.suggestions) ? data.suggestions.filter(question => typeof question === 'string') : [];
                throw new Error(response.status === 429 ? 'Please wait a minute before asking another question.' : (data.message || 'Unable to answer. Please try again.'));
            }
            if (typeof data.answer !== 'string') throw new Error('The assistant did not return an answer.');
            if (data.assistant_name) document.getElementById('assistant-display-name').textContent = data.assistant_name;
            pending.remove();
            add('assistant', data.answer, data.sources || []);
        } catch (error) {
            pending.remove();
            const message = controller.signal.aborted ? 'The response took too long.' :
                (error instanceof TypeError ? 'The connection was interrupted while sending your question.' : error.message);
            const row = add('error', message);
            if (retryable) {
                const retry = el('button', 'cai-ghost cai-retry', 'Retry question');
                retry.type = 'button';
                retry.addEventListener('click', () => ask(question, row));
                row.querySelector('.cai-bubble').append(retry);
            }
            if (knownQuestions.length) {
                const options = el('div', 'cai-chips');
                knownQuestions.forEach(question => {
                    const chip = el('button', 'cai-chip', question);
                    chip.type = 'button';
                    chip.dataset.question = question;
                    options.append(chip);
                });
                row.querySelector('.cai-bubble').append(options);
            }
            scrollDown();
        } finally {
            clearTimeout(deadline);
            busy = false; button.disabled = false; input.disabled = false; input.focus();
        }
    }
    form.addEventListener('submit', event => { event.preventDefault(); ask(input.value.trim()); });
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); ask(input.value.trim()); }
    });
    input.addEventListener('input', resize);
    conversation.addEventListener('click', event => {
        const chip = event.target.closest('.cai-chip[data-question]');
        if (chip && !chip.disabled) ask(chip.dataset.question);
    });
    document.getElementById('company-ai-reset').addEventListener('click', () => location.reload());
    resize();
});
</script>
@endpush
