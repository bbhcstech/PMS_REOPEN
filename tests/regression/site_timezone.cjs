const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict');
process.env.TZ = 'America/Los_Angeles';
let elapsed = 100;
const window = {PMSClockConfig: {timeZone: 'Asia/Kolkata', serverNow: Date.parse('2026-10-14T18:29:59Z')}};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-time.js', 'utf8'), {window, Date, Intl, performance: {now: () => elapsed}});
const clock = window.PMSTime;
assert.equal(clock.timeZone, 'Asia/Kolkata');
assert.equal(clock.dateKey(), '2026-10-14');
assert.equal(clock.inputTime(), '23:59');
assert.equal(clock.untilMidnight(), 4000);
elapsed += 2000;
assert.equal(clock.dateKey(), '2026-10-15');
assert.equal(clock.inputTime(), '00:00');
assert.equal(clock.parse('2026-10-14 05:48:00').toISOString(), '2026-10-14T00:18:00.000Z');
assert.equal(clock.parse('2026-10-14T05:48:00Z').toISOString(), '2026-10-14T05:48:00.000Z');
assert.equal(clock.parse('2026-10-14T05:48:00+05:30').toISOString(), '2026-10-14T00:18:00.000Z');
assert.equal(clock.dateKey('2026-10-14'), '2026-10-14');
assert.equal(clock.inputTime('2026-10-14T05:48:00Z'), '11:18');
assert.equal(clock.format('invalid'), '');
for (const name of ['resources/views/employee-dashboard.blade.php', 'resources/views/dashboard-hr.blade.php']) {
    const source = fs.readFileSync(name, 'utf8');
    const start = source.indexOf('const formatLocalTime =');
    const end = source.indexOf('const updateClockWidgets =', start);
    const context = {window, Intl, Date};
    vm.createContext(context);
    vm.runInContext(source.slice(start, end) + '\nthis.formatted = formatLocalTime(new Date("2026-10-14T18:30:01Z")); this.day = formatLocalDate(new Date("2026-10-14T18:30:01Z"));', Object.assign(context, {employeeTimeZone: clock.timeZone}));
    assert(context.formatted.includes('12:00:01'), name);
    assert(context.day.includes('15 Oct 2026'), name);
    assert(!source.includes('Intl.DateTimeFormat().resolvedOptions().timeZone'), 'Dashboard still follows browser timezone');
}

// Verify UTC timezone (e.g. local .env APP_TIMEZONE=UTC) between 00:00 and 05:30 IST never produces negative untilMidnight().
const utcWin = {PMSClockConfig: {timeZone: 'UTC', serverNow: Date.parse('2026-11-26T19:49:40Z')}};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-time.js', 'utf8'), {window: utcWin, Date, Intl, performance: {now: () => 100}});
assert.equal(utcWin.PMSTime.timeZone, 'UTC');
assert.equal(utcWin.PMSTime.dateKey(), '2026-11-26');
assert.equal(utcWin.PMSTime.parse('2026-11-26').toISOString(), '2026-11-26T00:00:00.000Z');
assert.equal(utcWin.PMSTime.untilMidnight(), 15023000);

// Verify employee dashboard scheduleMidnightRefresh never enters a reload loop on negative or unchanged dateKey.
const empSource = fs.readFileSync('resources/views/employee-dashboard.blade.php', 'utf8');
const schedStart = empSource.indexOf('const loadedDateKey =');
const schedEnd = empSource.indexOf("if (typeof ApexCharts === 'undefined')", schedStart);
let reloadCalls = 0;
const timers = [];
const guardCtx = {
    window: {
        PMSTime: {
            now: () => new Date('2026-11-26T19:49:40Z'),
            dateKey: () => '2026-11-26',
            untilMidnight: () => -4777000,
        },
        location: { reload: () => { reloadCalls++; } },
    },
    setTimeout: (fn, delay) => timers.push({ type: 'timeout', fn, delay }),
    setInterval: (fn, delay) => timers.push({ type: 'interval', fn, delay }),
    Number,
};
vm.createContext(guardCtx);
vm.runInContext(empSource.slice(schedStart, schedEnd), guardCtx);
assert.ok(timers[0].delay >= 60000, 'Negative untilMidnight must not schedule a 1s reload timer');
timers[0].fn();
assert.equal(reloadCalls, 0, 'Same calendar date must never reload the page');

console.log('PASS: IST formatting in a US browser, SQL/ISO offsets, midnight dates, server clock, dashboard time/date, UTC rollover guard and timesheet inputs.');
