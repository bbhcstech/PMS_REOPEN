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
for (const id of ['employeeIstClock', 'employeeClockZoneLabel', 'employeeLiveTime', 'employeeLiveDate']) {
    assert.match(source, new RegExp('id="' + id + '"[^>]*data-live-preserve'));
}
console.log('PASS: live refresh preserves client clocks and newly inserted clocks keep ticking.');
