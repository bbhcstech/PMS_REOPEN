const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict');
function element(value = '') {
    const classes = new Set();
    return { value, textContent: '', hidden: false, dataset: {}, listeners: {},
        classList: { add: name => classes.add(name), remove: name => classes.delete(name), contains: name => classes.has(name) },
        addEventListener(name, fn) { this.listeners[name] = fn; } };
}
const input = element('9876543210'), country = element('+91'), error = element(), hidden = element(), employee = element('7');
let messages = ['mobile', 'email'].map(field => ({ field, remove() { messages = messages.filter(item => item !== this); } }));
const summary = element(); summary.querySelector = () => messages[0] || null;
const elements = { mobile_only_digits: input, mobile_country_code: country, 'mobile-error': error, mobile_with_code: hidden, employee_id_val: employee };
const calls = [], window = {};
let valid = true;
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-employee-mobile-check.js', 'utf8'), {
    window, AbortController,
    document: { getElementById: id => elements[id], querySelectorAll: selector => selector === '[data-validation-summary]' ? [summary] : messages.filter(item => item.field === 'mobile' || item.field === 'mobile_country_code') },
    fetch(url, options) { return new Promise((resolve, reject) => calls.push({ url, options, resolve, reject })); }
});
window.PMSEmployeeMobileCheck.init({ url: '/employees/check-mobile', csrf: 'csrf-token', validate: () => valid });
const response = (exists, status = 200) => ({ ok: status === 200, status, json: async () => ({ exists }) });
(async () => {
    const first = input.listeners.blur();
    assert.equal(JSON.parse(calls[0].options.body).employee_id, '7');
    input.value = '9123456789'; input.listeners.input();
    assert.equal(calls[0].options.signal.aborted, true);
    assert.equal(hidden.value, '+919123456789');
    assert.deepEqual(messages.map(message => message.field), ['email']);
    assert.equal(summary.hidden, false, 'Unrelated errors must stay visible');
    calls[0].resolve(response(true)); await first;
    assert.equal(input.classList.contains('is-invalid'), false, 'Old duplicate response invalidated edited number');
    const second = input.listeners.blur(); calls[1].resolve(response(false)); await second;
    assert.equal(error.textContent, '');
    const duplicate = input.listeners.blur(); calls[2].resolve(response(true)); await duplicate;
    assert.equal(input.classList.contains('is-invalid'), true, 'Current duplicate not detected');
    input.value = '9234567890'; input.listeners.input();
    assert.equal(input.classList.contains('is-invalid'), false);
    assert.equal(error.textContent, '');
    const oldCountry = input.listeners.blur(); country.value = '+44'; country.listeners.change();
    calls[3].resolve(response(true)); await oldCountry;
    assert.equal(input.classList.contains('is-invalid'), false, 'Stale country-code result applied');
    assert.equal(hidden.value, '+449234567890');
    const failure = input.listeners.blur(); calls[4].reject(new Error('network down')); await failure;
    assert.equal(input.classList.contains('is-invalid'), false, 'Failed lookup flagged a duplicate');
    messages = [{ field: 'mobile', remove() { messages = []; } }];
    input.value = '9345678901'; input.listeners.input(); assert.equal(summary.hidden, true, 'Empty banner remained visible');
    valid = false; await input.listeners.blur(); assert.equal(calls.length, 5, 'Invalid local number sent availability request');
    for (const file of ['create', 'edit']) {
        const blade = fs.readFileSync(`resources/views/admin/employees/${file}.blade.php`, 'utf8');
        assert.match(blade, /PMSEmployeeMobileCheck.init/);
        assert.match(blade, /data-validation-summary/);
        assert.doesNotMatch(blade, /let mobileCheckAjax/);
        assert.doesNotMatch(blade, /slice\(0, 10\)/);
    }
    console.log('PASS: stale/aborted duplicate responses, corrected numbers and country codes, current duplicates, preserved other errors, empty banner cleanup, self-exclusion payload, network recovery and country-specific digit limits.');
})().catch(error => { console.error(error); process.exitCode = 1; });
