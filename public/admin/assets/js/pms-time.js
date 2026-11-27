(function () {
    'use strict';
    const config = window.PMSClockConfig || {};
    const timeZone = config.timeZone || 'Asia/Kolkata';
    const loadedAt = performance.now();
    const serverNow = Number(config.serverNow) || Date.now();
    const now = () => new Date(serverNow + performance.now() - loadedAt);
    const wallParts = (value, tz = timeZone) => {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: tz,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hourCycle: 'h23',
        }).formatToParts(value);
        const get = type => Number(parts.find(item => item.type === type)?.value || 0);
        return {
            year: get('year'),
            month: get('month'),
            day: get('day'),
            hour: get('hour') % 24,
            minute: get('minute'),
            second: get('second'),
        };
    };
    const zonedTimestamp = (year, month, day, hour = 0, minute = 0, second = 0, ms = 0, tz = timeZone) => {
        const targetUtc = Date.UTC(year, month - 1, day, hour, minute, second, ms);
        const toWallUtc = p => Date.UTC(p.year, p.month - 1, p.day, p.hour, p.minute, p.second, ms);
        let guess = targetUtc - (toWallUtc(wallParts(new Date(targetUtc), tz)) - targetUtc);
        guess = targetUtc - (toWallUtc(wallParts(new Date(guess), tz)) - guess);
        return guess;
    };
    // SQL timestamps have no offset. Interpret wall-clock timestamps in the active app timezone.
    // ISO API timestamps already contain Z/an offset and must retain their instant.
    function parse(value) {
        if (value instanceof Date) return new Date(value.getTime());
        if (typeof value === 'string') {
            const match = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{1,3}))?)?)?$/.exec(value);
            if (match) {
                const ms = match[7] ? Number(match[7].padEnd(3, '0')) : 0;
                return new Date(zonedTimestamp(
                    Number(match[1]),
                    Number(match[2]),
                    Number(match[3]),
                    Number(match[4] || 0),
                    Number(match[5] || 0),
                    Number(match[6] || 0),
                    ms
                ));
            }
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
        const parts = wallParts(current, timeZone);
        const nextMidnight = zonedTimestamp(parts.year, parts.month, parts.day + 1, 0, 0, 3, 0, timeZone);
        const remaining = nextMidnight - current.getTime();
        return Number.isFinite(remaining) && remaining > 0 ? remaining : 60000;
    }
    window.PMSTime = Object.freeze({ timeZone, now, parse, format, dateKey, inputTime, untilMidnight });
})();
