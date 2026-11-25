(function () {
    'use strict';
    const config = window.PMSClockConfig || {};
    const timeZone = config.timeZone || 'Asia/Kolkata';
    const loadedAt = performance.now();
    const serverNow = Number(config.serverNow) || Date.now();
    const now = () => new Date(serverNow + performance.now() - loadedAt);
    // SQL timestamps have no offset. This deployment stores wall times in IST.
    // ISO API timestamps already contain Z/an offset and must retain their instant.
    function parse(value) {
        if (value instanceof Date) return new Date(value.getTime());
        if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?)?$/.test(value)) {
            value = value.replace(' ', 'T');
            if (value.length === 10) value += 'T00:00:00';
            value += '+05:30';
        }
        return new Date(value);
    }
    function format(value, options = {}) {
        const date = parse(value);
        if (Number.isNaN(date.getTime())) return '';
        return new Intl.DateTimeFormat('en-IN', { ...options, timeZone }).format(date);
    }
    function dateKey(value = now()) {
        const parts = new Intl.DateTimeFormat('en-CA', {timeZone, year: 'numeric', month: '2-digit', day: '2-digit'}).formatToParts(parse(value));
        const part = type => parts.find(item => item.type === type).value;
        return `${part('year')}-${part('month')}-${part('day')}`;
    }
    function inputTime(value = now()) {
        return format(value, {hour: '2-digit', minute: '2-digit', hourCycle: 'h23'});
    }
    function untilMidnight() {
        const current = now();
        const today = parse(dateKey(current));
        return today.getTime() + 86400000 + 3000 - current.getTime();
    }
    window.PMSTime = Object.freeze({ timeZone, now, parse, format, dateKey, inputTime, untilMidnight });
})();
