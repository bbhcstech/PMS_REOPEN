const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict'), path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/admin/assets/js/pms-task-timer-clock.js'), 'utf8');
const epoch = Date.parse('2026-10-14T18:29:59Z');
const element = {dataset: {serverNow: String(epoch), timeZone: 'Asia/Kolkata'}};
let monotonic = 100;
let tick;
const events = {};
vm.runInNewContext(source, {
    document: {querySelectorAll: () => [element], addEventListener: (name, handler) => events[name] = handler},
    performance: {now: () => monotonic}, Intl, Date,
    setInterval: (handler, interval) => {assert.equal(interval, 1000); tick = handler;},
});
assert.ok(element.textContent.includes('11:59:59'));
monotonic += 2000;
tick();
assert.ok(element.textContent.includes('12:00:01'));
assert.ok(element.textContent.includes('AM'));
assert.equal(element.dateTime, '2026-10-14T18:30:01.000Z');
monotonic += 59000;
events['shown.bs.modal']();
assert.ok(element.textContent.includes('12:01:00'));
events.visibilitychange();
assert.equal(element.dateTime, '2026-10-14T18:31:00.000Z');
console.log('PASS: live server-based end time, configured timezone, second precision, midnight rollover and modal reopening.');
