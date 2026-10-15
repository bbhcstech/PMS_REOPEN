const fs = require('fs'), vm = require('vm'), assert = require('node:assert/strict');
const fields = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].map(day => ({
    dataset: { weeklyDay: day }, value: 'office',
    addEventListener(name, fn) { this[name] = fn; },
    setCustomValidity(message) { this.error = message; }
}));
const summary = { children: [], set textContent(value) { this.children = []; }, appendChild(node) { this.children.push(node); } };
const preset = { addEventListener(name, fn) { this[name] = fn; } };
const section = { querySelectorAll: () => fields, querySelector: selector => selector === '[data-weekly-summary]' ? summary : preset };
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-weekly-schedule.js', 'utf8'), {
    document: { querySelectorAll: () => [section], createElement: () => ({}) }
});
preset.click();
assert.equal(fields[5].value, 'wfh');
assert.equal(fields[6].value, 'weekend');
assert.deepEqual(summary.children.map(node => node.textContent), ['5 Office days', '1 WFH day', '1 Weekend day']);
assert.equal(fields[0].error, '');
fields.forEach(field => field.value = 'weekend'); fields[0].change();
assert.match(fields[0].error, /at least one/);
fields[6].value = 'wfh'; fields[6].change();
assert.equal(fields[0].error, '');
assert.deepEqual(summary.children.map(node => node.textContent), ['0 Office days', '1 WFH day', '6 Weekend days']);
console.log('PASS: weekly preset, live totals, custom weekend/WFH days and all-weekend validation.');
