(() => {
    if (window.PmsComplaintLive) return;
    const states = new Set();
    const known = new WeakSet();
    const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

    function visible(feed) {
        const drawer = feed.closest('#drawerOverlay');
        return !document.hidden && (!drawer || drawer.classList.contains('active'));
    }
    function append(feed, html) {
        const template = document.createElement('template');
        template.innerHTML = html;
        for (const node of template.content.children) {
            const id = node.dataset.messageId;
            if (id && !feed.querySelector(`[data-message-id="${id}"]`)) feed.appendChild(node.cloneNode(true));
        }
    }
    async function poll(state) {
        clearTimeout(state.timer);
        if (!state.feed.isConnected || state.stopped) { states.delete(state); return; }
        if (state.busy) { state.again = true; return; }
        if (!visible(state.feed)) { state.timer = setTimeout(() => poll(state), 2000); return; }
        state.busy = true;
        let delay = 2000;
        try {
            const url = new URL(state.feed.dataset.liveFeed, location.href);
            url.searchParams.set('after_id', state.last);
            const response = await fetch(url, { headers, cache: 'no-store', signal: AbortSignal.timeout(15000) });
            if ([401, 403, 404, 419].includes(response.status) || response.redirected) { state.stopped = true; return; }
            if (!response.ok) throw new Error('Conversation unavailable');
            const data = await response.json();
            if (!state.feed.isConnected || !data.success) return;
            const scroll = state.feed.closest('.drawer-body') || document.scrollingElement;
            const nearBottom = scroll.scrollHeight - scroll.scrollTop - scroll.clientHeight < 100;
            append(state.feed, data.html);
            state.last = Math.max(state.last, Number(data.last_id));
            state.feed.dataset.lastId = state.last;
            const root = state.feed.closest('[data-live-chat]');
            root.querySelectorAll('[data-live-message-count]').forEach(el => { el.textContent = data.count; });
            if (nearBottom) scroll.scrollTop = scroll.scrollHeight;
            if (data.has_more) delay = 0;
            state.failures = 0;
        } catch (_) {
            state.failures = (state.failures || 0) + 1;
            delay = Math.min(30000, 2000 * Math.pow(2, state.failures));
        } finally {
            state.busy = false;
            if (!state.stopped) state.timer = setTimeout(() => poll(state), state.again ? 0 : delay);
            state.again = false;
        }
    }
    function scan() {
        document.querySelectorAll('[data-live-feed]').forEach(feed => {
            if (known.has(feed)) return;
            known.add(feed);
            const state = { feed, last: Number(feed.dataset.lastId || 0), busy: false };
            states.add(state); poll(state);
        });
    }
    document.addEventListener('submit', async event => {
        const form = event.target.closest('form[data-live-reply]');
        if (!form) return;
        event.preventDefault(); event.stopImmediatePropagation();
        if (form.dataset.sending === '1') return;
        const button = form.querySelector('[type="submit"]');
        const root = form.closest('[data-live-chat]');
        form.dataset.sending = '1';
        if (button) button.disabled = true;
        root.querySelector('[data-live-error]')?.remove();
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Message could not be sent.');
            form.reset();
            for (const state of states) if (state.feed.closest('[data-live-chat]') === root) poll(state);
        } catch (error) {
            const message = document.createElement('div');
            message.className = 'alert alert-danger'; message.dataset.liveError = '1'; message.setAttribute('role', 'alert');
            message.textContent = error.message || 'Message could not be sent. Your draft is preserved.';
            form.prepend(message);
        } finally {
            delete form.dataset.sending;
            if (button) button.disabled = false;
        }
    }, true);
    window.PmsComplaintLive = { scan, append };
    new MutationObserver(scan).observe(document.documentElement, { childList: true, subtree: true });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) for (const state of states) poll(state); });
    window.addEventListener('pagehide', () => { for (const state of states) { state.stopped = true; clearTimeout(state.timer); } });
    scan();
})();
