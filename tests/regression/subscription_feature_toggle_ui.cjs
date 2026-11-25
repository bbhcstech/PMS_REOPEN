const fs = require('fs'), vm = require('vm'), assert = require('node:assert/strict');
const view = fs.readFileSync('resources/views/superadmin/subscriptions/index.blade.php', 'utf8');
const source = view.slice(view.indexOf('    // 4. Feature Matrix AJAX Toggle'), view.indexOf('    // 5. Drawer Handling'));
const toggle = { checked: false, getAttribute: name => name === 'data-planid' ? '2' : '12', addEventListener(name, fn) { this[name] = fn; } };
const toasts = [], requests = [];
let fail = true;
const context = {
    document: { querySelectorAll: () => [toggle] },
    showToast: message => toasts.push(message), overrideCompanySelect: { value: '' },
    fetch: async (url, options) => { requests.push(options); return { ok: !fail, json: async () => fail ? { success: false } : { success: true, states: { 12: 0 } } }; }
};
vm.runInNewContext(source, context);
(async () => {
    toggle.change();
    assert.equal(toggle.disabled, true);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(toggle.checked, true, 'Failed save must restore previous state');
    assert.equal(toggle.disabled, false);
    assert.match(toasts[0], /could not be saved/);
    fail = false; toggle.checked = false; toggle.change();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(toggle.checked, false, 'Saved off state must remain off');
    assert.equal(toggle.disabled, false);
    assert.deepEqual(JSON.parse(requests[1].body), { plan_id: '2', module_id: '12', enabled: 0 });
    assert.equal(requests[1].method, 'POST');
    assert.ok(requests[1].headers['X-CSRF-TOKEN']);
    console.log('PASS: matrix save failures roll back, explicit off payload persists, pending toggles lock and server states reconcile.');
})().catch(error => { console.error(error); process.exitCode = 1; });
