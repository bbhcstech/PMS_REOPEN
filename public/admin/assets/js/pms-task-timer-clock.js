(function () {
    'use strict';
    var clocks = Array.from(document.querySelectorAll('[data-live-timer-end]')).map(function (element) {
        return {
            element: element,
            serverTime: Number(element.dataset.serverNow),
            loadedAt: performance.now(),
            format: new Intl.DateTimeFormat('en-US', {
                timeZone: element.dataset.timeZone,
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            })
        };
    });
    if (!clocks.length) return;
    function update() {
        clocks.forEach(function (clock) {
            var current = new Date(clock.serverTime + performance.now() - clock.loadedAt);
            clock.element.textContent = clock.format.format(current);
            clock.element.dateTime = current.toISOString();
        });
    }
    update();
    setInterval(update, 1000);
    document.addEventListener('visibilitychange', update);
    document.addEventListener('shown.bs.modal', update);
})();
