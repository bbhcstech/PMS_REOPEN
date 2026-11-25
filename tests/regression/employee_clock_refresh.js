import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
const source = fs.readFileSync('resources/views/employee-dashboard.blade.php', 'utf8');
const start = source.indexOf('const updateClockWidgets = () => {');
const end = source.indexOf('updateClockWidgets();', start);
const nodes = new Map();
const oldClock = {textContent: ''};
nodes.set('employeeIstClock', oldClock);
const context = {
    document: {getElementById: id => nodes.get(id), querySelectorAll: () => []},
    Date,
    window: { PMSTime: { now: () => new Date('2026-10-14T10:30:00+05:30') } },
    formatLocalTime: () => '05:57:49 PM',
    formatLocalDate: () => 'Saturday, 10 Oct 2026',
};
vm.createContext(context);
vm.runInContext(source.slice(start, end) + '\n globalThis.tick = updateClockWidgets;', context);
context.tick();
assert.equal(oldClock.textContent, '05:57:49 PM');
const newClock = {textContent: '12:27:49 PM'};
nodes.set('employeeIstClock', newClock);
context.tick();
assert.equal(newClock.textContent, '05:57:49 PM');
const timer = { dataset: { clockIn: '2026-10-14T09:15:00+05:30' }, textContent: '' };
nodes.set('employeeWorkTimer', timer);
context.tick();
assert.equal(timer.textContent, '01:15:00', 'Work timer did not use the saved shift start');
context.window.PMSTime.now = () => new Date('2026-10-14T10:30:01+05:30');
context.tick();
assert.equal(timer.textContent, '01:15:01', 'Active work timer did not advance');
timer.dataset.fixedDuration = '08:30:00';
context.tick();
assert.equal(timer.textContent, '08:30:00', 'Clocked-out duration did not stay fixed');
for (const id of ['employeeIstClock', 'employeeClockZoneLabel', 'employeeLiveTime', 'employeeLiveDate']) {
    assert.match(source, new RegExp('id="' + id + '"[^>]*data-live-preserve'));
}
console.log('PASS: live refresh preserves client clocks and newly inserted clocks keep ticking.');
