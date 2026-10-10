const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('resources/views/admin/settings/role-permissions/index.blade.php', 'utf8');
const start = source.indexOf("    const form = document.getElementById('rolePermissionsForm');");
const end = source.indexOf('    // Sync state', start);
assert(start > 0 && end > start);
let handler;
let calls = 0;
let fail = false;
const button = { innerHTML: 'Save Permissions', disabled: false };
const toasts = [];
const boxes = Array.from({length: 170}, (_, i) => Array.from({length: 7}, (_, j) => ({ dataset: {moduleId: String(i + 1)}, value: ['view','create','edit','delete','approve','export','assign'][j], checked: true }))).flat();
boxes[0].checked = false;
const form = {action: '/settings/role-permissions', addEventListener: (_, fn) => handler = fn, querySelector: selector => selector.includes('button') ? button : {value: selector.includes('_token') ? 'csrf-token' : 'hr'}};
vm.runInNewContext(source.slice(start, end), {
    document: {getElementById: () => form}, actionCheckboxes: boxes,
    window: {showToast: (message,type) => toasts.push({message,type})},
    fetch: async (url, options) => {
        calls++;
        assert.equal(url, form.action);
        assert.equal(options.headers['Content-Type'], 'application/json');
        assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf-token');
        const data = JSON.parse(options.body);
        assert.equal(data.role, 'hr');
        assert.equal(Object.values(data.permissions).flat().length, 1189);
        assert(!data.permissions[1].includes('view'));
        return {ok: !fail, json: async () => ({success: !fail, message: fail ? 'Save failed' : 'Saved HR'})};
    },
});
(async () => {
    let prevented = 0;
    const event = {preventDefault: () => prevented++};
    const first = handler(event);
    await handler(event);
    await first;
    assert.equal(calls, 1);
    assert.equal(prevented, 2);
    assert.deepEqual(toasts, [{message: 'Saved HR', type: 'success'}]);
    assert.equal(button.disabled, false);
    fail = true;
    await handler(event);
    assert.deepEqual(toasts[1], {message: 'Save failed', type: 'error'});
    assert.equal(button.disabled, false);
    assert.equal(button.innerHTML, 'Save Permissions');
    console.log('PASS: HR JSON submission, no default navigation, complete large payload, success/error toasts and duplicate-submit protection.');
})().catch(error => { console.error(error); process.exitCode = 1; });
