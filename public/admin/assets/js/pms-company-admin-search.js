(() => {
    const form = document.getElementById('filterForm');
    const input = document.getElementById('adminSearchInput');
    const results = document.getElementById('companyAdminResults');
    if (!form || !input || !results) return;

    let timer, request, version = 0;
    async function filter() {
        const currentVersion = ++version;
        request?.abort();
        request = new AbortController();
        const url = new URL(form.action, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        url.searchParams.delete('page');
        results.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, {
                headers: { Accept: 'text/html' },
                signal: request.signal,
                cache: 'no-store',
            });
            if (!response.ok || response.redirected) throw new Error('Search unavailable');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (currentVersion !== version) return;
            const fresh = page.getElementById('companyAdminResults');
            const body = fresh?.querySelector('#adminTable tbody');
            const pagination = fresh?.querySelector('.table-pagination-bar');
            if (!body || !pagination) throw new Error('Search results unavailable');
            results.querySelector('#adminTable tbody').replaceChildren(...body.childNodes);
            results.querySelector('.table-pagination-bar').replaceChildren(...pagination.childNodes);
            results.querySelector('#adminTable').removeAttribute('data-sort-dir');
            history.replaceState(history.state, '', url);
            // Keep export links aligned with the search shown in the table.
            document.querySelectorAll('a[href]').forEach(link => {
                const exportUrl = new URL(link.href, location.href);
                if (exportUrl.origin !== url.origin || !exportUrl.pathname.endsWith('/export')) return;
                exportUrl.searchParams.set('admin_search', input.value);
                link.href = exportUrl.href;
            });
            document.dispatchEvent(new CustomEvent('pms:records-updated'));
        } catch (error) {
            if (error.name !== 'AbortError' && currentVersion === version) {
                window.PMSToast?.show('Search could not be updated. Please try typing again.', 'error');
            }
        } finally {
            if (currentVersion === version) results.removeAttribute('aria-busy');
        }
    }
    input.addEventListener('input', event => {
        clearTimeout(timer);
        ++version;
        request?.abort();
        results.removeAttribute('aria-busy');
        if (!event.isComposing) timer = setTimeout(filter, 300);
    });
    input.addEventListener('compositionend', () => {
        clearTimeout(timer);
        timer = setTimeout(filter, 300);
    });
    form.addEventListener('submit', event => {
        event.preventDefault();
        clearTimeout(timer);
        filter();
    });
})();
