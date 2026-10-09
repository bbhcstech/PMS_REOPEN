const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(path.join(__dirname, '../../public/admin/assets/js/pms-company-edit-phone.js'), 'utf8');
const handlers = {}, input = {
    value: '9876543210', selectionStart: 10,
    addEventListener: (type, fn) => { handlers[type] = fn; },
    setSelectionRange(start) { this.selectionStart = start; },
    setCustomValidity(message) { this.error = message; },
    checkValidity() { return !this.error; }, reportValidity() {},
};
const select = { value: '+91', selectedOptions: [{ dataset: { minDigits: '10', maxDigits: '10' } }], addEventListener: (_, fn) => { handlers.country = fn; } };
const hidden = {}, hint = {}, form = { addEventListener: (_, fn) => { handlers.submit = fn; } };
const document = { activeElement: input, getElementById: id => ({ company_phone_edit_display: input, company_edit_country_code: select, company_phone_edit: hidden, companyEditForm: form, company_phone_edit_hint: hint })[id] };
vm.runInNewContext(source, { document });
assert.equal(hidden.value, '+91 9876543210');
assert.equal(input.maxLength, 10);
let prevented = false;
handlers.beforeinput({ data: 'text', preventDefault: () => { prevented = true; } });
assert.ok(prevented, 'Letters must be blocked before insertion');
input.value = '98ab76क54 3210'; handlers.input();
assert.equal(input.value, '9876543210', 'Pasted and Unicode letters must be removed');
input.value = '9876543210123'; handlers.input();
assert.equal(input.value, '9876543210', 'Typing must respect the selected maximum');
input.value = '12345'; handlers.input();
prevented = false; handlers.submit({ preventDefault: () => { prevented = true; } });
assert.ok(prevented && input.error, 'Too-short numbers must block submission');
select.value = '+44'; select.selectedOptions[0].dataset = { minDigits: '10', maxDigits: '11' }; handlers.country();
assert.equal(input.maxLength, 11);
input.value = '20123456789'; handlers.input();
assert.equal(hidden.value, '+44 20123456789');
assert.equal(input.error, '');
select.value = '+33'; select.selectedOptions[0].dataset = { minDigits: '9', maxDigits: '9' }; handlers.country();
assert.equal(input.maxLength, 9);
assert.equal(input.value.length, 9);
input.value = ''; handlers.input();
assert.equal(hidden.value, '');
assert.equal(input.error, '', 'An optional empty phone must remain valid');
console.log('PASS: digits only, typing/paste sanitization, per-country limits, short-number rejection, country changes and optional values.');
