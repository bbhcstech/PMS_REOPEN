(() => {
    if (window.PmsLiveRecords) return;
    const region = document.querySelector('[data-live-records]');
    if (!region) return;
    let busy = false, stopped = false, timer, failures = 0;
    const protect = 'input, select, textarea, button, script, style, link, canvas, iframe, [contenteditable], [data-live-chat], [data-live-preserve], .modal, .modal-backdrop, .drawer-overlay, .offcanvas, .select2-container, .dataTables_wrapper, .pms-table-tools, [role="tablist"]';
    function key(node) {
        if (node.nodeType !== 1) return null;
        if (node.id) return node.tagName + '#' + node.id;
        for (const attr of ['data-live-key', 'data-record-id', 'data-employee-id', 'data-company-id', 'data-task-id']) {
            if (node.hasAttribute(attr)) return node.tagName + ':' + attr + ':' + node.getAttribute(attr);
        }
        if (node.tagName === 'TR') {
            const link = node.querySelector('a[href]:not([href="#"]):not([href^="javascript:"])');
            if (link) return 'TR:' + link.getAttribute('href');
        }
        return null;
    }
    function sync(current, fresh) {
        if (current.nodeType !== fresh.nodeType) return;
        if (current.nodeType === 3) { if (current.nodeValue !== fresh.nodeValue) current.nodeValue = fresh.nodeValue; return; }
        if (current.nodeType !== 1 || current.tagName !== fresh.tagName || current.matches(protect)) return;
        if (current.tagName === 'FORM' && !current.querySelector('table')) return;
        // Runtime classes/ARIA control open panels, selected tabs and widgets.
        // Update only server data and links, preserving their live UI state.
        for (const attr of fresh.attributes) {
            if (['href', 'src', 'title', 'alt'].includes(attr.name) || attr.name.startsWith('data-')) {
                if (current.getAttribute(attr.name) !== attr.value) current.setAttribute(attr.name, attr.value);
            }
        }
        // Status badges are server-owned rather than interactive state.
        if (current.matches('.badge, .status-badge, .badge-status')) current.className = fresh.className;
        const old = Array.from(current.childNodes);
        const keyed = new Map(old.map(node => [key(node), node]).filter(([id]) => id));
        const used = new Set();
        let anchor = current.firstChild;
        for (const next of fresh.childNodes) {
            if (next.nodeType === 1 && next.matches('script, style, link')) continue;
            const id = key(next);
            let match = id ? keyed.get(id) : old.find(node => !used.has(node) && !key(node) && node.nodeType === next.nodeType && (node.nodeType !== 1 || node.tagName === next.tagName));
            if (match && !used.has(match)) {
                used.add(match);
                if (match !== anchor) current.insertBefore(match, anchor);
                sync(match, next);
                anchor = match.nextSibling;
            } else {
                match = next.cloneNode(true);
                if (match.nodeType === 1) match.querySelectorAll('script').forEach(script => script.remove());
                current.insertBefore(match, anchor);
            }
        }
        for (const node of old) {
            if (!used.has(node) && !(node.nodeType === 1 && (node.matches(protect) || node.contains(document.activeElement)))) node.remove();
        }
    }
    function syncDataTables(fresh) {
        const jq = window.jQuery;
        if (!jq?.fn?.dataTable) return;
        region.querySelectorAll('table[id]').forEach(table => {
            if (!jq.fn.dataTable.isDataTable(table)) return;
            const replacement = fresh.querySelector(`#${CSS.escape(table.id)}`);
            if (!replacement?.tBodies[0]) return;
            const api = jq(table).DataTable();
            const selected = new Set(Array.from(table.querySelectorAll('input[type="checkbox"]:checked')).map(input => input.value));
            const rows = Array.from(replacement.tBodies[0].rows).filter(row => !row.querySelector('td[colspan]')).map(row => row.cloneNode(true));
            api.clear().rows.add(rows).draw(false);
            table.querySelectorAll('input[type="checkbox"]').forEach(input => { input.checked = selected.has(input.value); });
            // Align the fetched DOM with the client-generated widget wrapper so
            // the general reconciler preserves search, order and pagination.
            const wrapper = table.closest('.dataTables_wrapper');
            if (wrapper) (replacement.closest('.table-responsive') || replacement).replaceWith(wrapper.cloneNode(true));
        });
    }
    async function refresh() {
        if (busy || stopped) return;
        if (document.hidden || document.querySelector('.modal.show, .offcanvas.show, .drawer-overlay.active, .plan-modal-backdrop.open')) { timer = setTimeout(refresh, 5000); return; }
        busy = true;
        const url = location.href;
        try {
            const response = await fetch(url, { headers: { Accept: 'text/html', 'X-PMS-Live-Records': '1' }, cache: 'no-store', signal: AbortSignal.timeout(20000) });
            if (response.redirected || [401, 403, 419].includes(response.status)) { stopped = true; return; }
            if (!response.ok) throw new Error('Record refresh unavailable');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const fresh = page.querySelector('[data-live-records]');
            if (!fresh || fresh.dataset.liveIdentity !== region.dataset.liveIdentity) { stopped = true; return; }
            if (location.href !== url || !region.isConnected) return;
            const focused = document.activeElement;
            const selection = focused && typeof focused.selectionStart === 'number'
                ? [focused.selectionStart, focused.selectionEnd] : null;
            syncDataTables(fresh);
            sync(region, fresh);
            if (focused?.isConnected && document.activeElement !== focused) {
                focused.focus({ preventScroll: true });
                if (selection) focused.setSelectionRange(...selection);
            }
            document.dispatchEvent(new CustomEvent('pms:records-updated'));
            failures = 0;
        } catch (_) { failures++; }
        finally { busy = false; if (!stopped) timer = setTimeout(refresh, Math.min(30000, 5000 * Math.pow(2, failures))); }
    }
    window.PmsLiveRecords = { sync, refresh, key };
    document.addEventListener('visibilitychange', () => { clearTimeout(timer); if (!document.hidden) refresh(); });
    window.addEventListener('online', () => { clearTimeout(timer); refresh(); });
    window.addEventListener('pagehide', () => { stopped = true; clearTimeout(timer); });
    timer = setTimeout(refresh, 5000);
})();
