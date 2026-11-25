const fs = require('fs');
const vm = require('vm');
const assert = require('assert/strict');

const view = fs.readFileSync('resources/views/admin/employees/create.blade.php', 'utf8');
assert.match(view, /id="employee-create-page" data-live-preserve/);

const region = {};
const window = { addEventListener() {} };
const document = {
    currentScript: { dataset: {} },
    querySelector: () => region,
    addEventListener() {}
};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-live-records.js', 'utf8'), {
    window, document, setInterval() {}, setTimeout() {}, clearInterval() {}, clearTimeout() {}
});

// The next GET has no flashed errors or old input. It must not replace the
// validation response, its layout, typed fields, or selected upload files.
const current = {
    nodeType: 1,
    tagName: 'DIV',
    errors: ['The employee must be at least 18 years old on the joining date.'],
    fields: { name: 'Second employee', dob: '2009-10-10' },
    files: ['profile.png'],
    matches: selector => selector.includes('[data-live-preserve]')
};
const before = JSON.stringify(current);
window.PmsLiveRecords.sync(current, { nodeType: 1, tagName: 'DIV' });
assert.equal(JSON.stringify(current), before);
console.log('PASS: live refresh preserves employee validation errors, fields, and uploads.');
