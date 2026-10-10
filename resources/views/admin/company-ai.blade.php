@extends($assistantLayout ?? 'admin.layout.app')
@section('title', $assistantName)
@section('page_title', $assistantName)
@section('content')
<main id="company-ai-page" data-live-preserve class="container-fluid py-4">
    <div class="card p-4 company-ai-card mx-auto" style="max-width: 900px;">
        <h3 id="assistant-display-name">{{ $assistantName }}</h3>
        <p class="text-muted">{{ $assistantDescription ?? 'Ask about your company events, departments, assigned work, attendance or leave. Answers use current records you are allowed to access.' }}</p>
        @unless($configured)
            <div class="alert alert-info">Your assistant is ready. A platform administrator needs to configure the AI service before it can answer questions.</div>
        @endunless
        <div id="company-ai-conversation" aria-live="polite" class="mb-3">
            <div class="company-ai-message">Hello! I’m {{ $assistantName }}. How can I help with your workspace today?</div>
        </div>
        <form id="company-ai-form">
            @csrf
            <label for="company-ai-question" class="form-label">Your question</label>
            <textarea id="company-ai-question" class="form-control mb-3" rows="3" maxlength="1000" required placeholder="What company events are coming up?" @disabled(!$configured)></textarea>
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <small class="text-muted">Questions and relevant company records are processed by the AI service. Answers do not change records.</small>
                <button id="company-ai-send" class="btn btn-primary" @disabled(!$configured)>Ask assistant</button>
            </div>
        </form>
    </div>
</main>
<style>
    #company-ai-page .company-ai-message { padding: 16px; margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 12px; background: #f8fafc; color: #1e293b; white-space: pre-wrap; overflow-wrap: anywhere; }
    #company-ai-page .company-ai-message strong { display: block; margin-bottom: 8px; }
    #company-ai-page .company-ai-sources { font-size: 13px; margin-top: 12px; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #company-ai-page .company-ai-message { background: #141b3d; border-color: #334155; color: #e2e8f0; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) #company-ai-page .company-ai-message :is(strong, span, summary) { color: #e2e8f0 !important; -webkit-text-fill-color: #e2e8f0 !important; }
</style>
@endsection
@push($scriptStack ?? 'js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('company-ai-form');
    const input = document.getElementById('company-ai-question');
    const button = document.getElementById('company-ai-send');
    const conversation = document.getElementById('company-ai-conversation');
    function message(title, text, sources = []) {
        const box = document.createElement('div'); box.className = 'company-ai-message';
        const heading = document.createElement('strong'); heading.textContent = title;
        const body = document.createElement('span'); body.textContent = text;
        box.append(heading, body);
        if (sources.length) {
            const details = document.createElement('details'); details.className = 'company-ai-sources';
            const summary = document.createElement('summary'); summary.textContent = 'Company records used'; details.append(summary);
            sources.forEach(source => {
                const item = document.createElement('div');
                item.textContent = source.type + ': ' + Object.entries(source.facts).filter(([, value]) => value).map(([key, value]) => key.replaceAll('_', ' ') + ': ' + value).join(' · ');
                details.append(item);
            });
            box.append(details);
        }
        conversation.append(box);
        // Keep only this tab's recent display; no shared browser or server chat cache.
        while (conversation.children.length > 20) conversation.firstElementChild.remove();
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const question = input.value.trim(); if (!question || button.disabled) return;
        button.disabled = true; button.textContent = 'Thinking…'; input.disabled = true;
        message('You', question);
        try {
            const response = await fetch(@json($askUrl), {
                method: 'POST', cache: 'no-store', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Company-Workspace': @json($workspaceKey), 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: JSON.stringify({ question }), signal: AbortSignal.timeout(40000)
            });
            if (response.status === 401 || response.status === 419) { location.replace(@json(route('login'))); return; }
            if (response.status === 409) { location.reload(); return; }
            const data = await response.json();
            if (!response.ok) throw new Error(response.status === 429 ? 'Please wait a minute before asking another question.' : (data.message || 'Unable to answer. Please try again.'));
            const assistantName = data.assistant_name || @json($assistantName);
            document.getElementById('assistant-display-name').textContent = assistantName;
            message(assistantName, data.answer, data.sources || []);
            input.value = '';
        } catch (error) {
            message('Assistant', error.name === 'TimeoutError' ? 'The response took too long. Please try again.' : error.message);
        } finally {
            button.disabled = false; button.textContent = 'Ask assistant'; input.disabled = false;
        }
    });
});
</script>
@endpush
