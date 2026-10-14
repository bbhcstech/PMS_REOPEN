const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const inputs = [8, 9, 7].map(value => ({ value: String(value), validity: { valid: true } }));
const output = { textContent: '' }, handlers = {}, opened = [];
const form = { querySelectorAll: () => inputs, querySelector: () => output };
const section = { dataset: { behaviourOpen: '1', behaviourEmployee: '3' } };
const tab = {}, modal = {};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-appraisal-ratings.js', 'utf8'), {
    document: {
        querySelectorAll: () => [form],
        querySelector: selector => selector === '[data-behaviour-section]' ? section : tab,
        getElementById: id => id === 'behaviourRating-3' ? modal : null,
        addEventListener: (name, callback) => { handlers[name] = callback; },
    },
    window: { bootstrap: {
        Tab: { getOrCreateInstance: element => ({ show: () => { assert.equal(element, tab); opened.push('tab'); } }) },
        Modal: { getOrCreateInstance: element => ({ show: () => { assert.equal(element, modal); opened.push('modal'); } }) },
    } },
});
assert.equal(output.textContent, '80.00 / 100');
assert.deepEqual(opened, ['tab', 'modal']);
const change = () => handlers.input({ target: { matches: () => true, closest: () => form } });
inputs[0].value = ''; change(); assert.match(output.textContent, /Enter all three/);
inputs[0].value = '11'; change(); assert.match(output.textContent, /Enter all three/);
inputs[0].value = '8.1'; inputs[1].value = '9.2'; inputs[2].value = '7.3'; change();
assert.equal(output.textContent, '82.00 / 100');
inputs[0].validity.valid = false; change(); assert.match(output.textContent, /Enter all three/);
console.log('PASS: manual behaviour score preview, incomplete/out-of-range/invalid inputs, decimals and restoring the affected tab/modal after validation.');
