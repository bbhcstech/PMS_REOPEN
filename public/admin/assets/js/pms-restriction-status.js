(function () {
    'use strict';
    const url = document.body.dataset.restrictionUrl;
    const initial = document.body.dataset.restrictionStatus;
    if (!url) return;
    let timer, busy = false, stopped = false;
    async function check() {
        clearTimeout(timer);
        if (stopped || busy) return;
        if (document.hidden) { timer = setTimeout(check, 2000); return; }
        busy = true;
        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store', signal: AbortSignal.timeout(10000)
            });
            if (response.redirected || [401, 403, 404, 419].includes(response.status)) { stopped = true; return; }
            if (!response.ok) return;
            const data = await response.json();
            if (['active', 'trial'].includes(data.status) || (data.status !== initial && ['expired', 'suspended'].includes(data.status))) {
                stopped = true;
                // The server chooses the correct restricted screen or Dashboard and flashes the restored-access notification.
                window.location.replace(url);
            }
        } catch (_) {
            // Retry temporary network failures without changing the restriction screen.
        } finally {
            busy = false;
            if (!stopped) timer = setTimeout(check, 2000);
        }
    }
    document.addEventListener('visibilitychange', () => { if (!document.hidden) check(); });
    window.addEventListener('pagehide', () => { stopped = true; clearTimeout(timer); });
    check();
})();
