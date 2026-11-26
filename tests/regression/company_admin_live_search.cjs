const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(path.join(__dirname, '../../public/admin/assets/js/pms-company-admin-search.js'), 'utf8');
const handlers = {}, pending = [], timers = new Map(), notices = [];
const input = { value: '', addEventListener: (name, fn) => { handlers[name] = fn; } };
const form = { action: 'https://pms.test/company-admins', addEventListener: (name, fn) => { handlers[name] = fn; } };
const body = { replaceChildren: (...nodes) => { body.nodes = nodes; } };
const pagination = { replaceChildren: (...nodes) => { pagination.nodes = nodes; } };
const table = { removeAttribute() {} };
const results = {
    attrs: {}, setAttribute(name, value) { this.attrs[name] = value; }, removeAttribute(name) { delete this.attrs[name]; },
    querySelector: selector => selector.endsWith('tbody') ? body : selector === '#adminTable' ? table : pagination,
};
let lastUrl;
const exportLink = { href: 'https://pms.test/company-admins/export?admin_status=active' };
const document = {
    activeElement: input,
    getElementById: id => ({ filterForm: form, adminSearchInput: input, companyAdminResults: results })[id],
    querySelectorAll: () => [exportLink], dispatchEvent() {},
};
const context = {
    document, location: { href: 'https://pms.test/company-admins?page=3' },
    window: { PMSToast: { show: message => notices.push(message) } },
    history: { state: null, replaceState: (_, __, url) => { lastUrl = url; } },
    URL, URLSearchParams, AbortController, CustomEvent: class {},
    FormData: class { *[Symbol.iterator]() { yield ['admin_search', input.value]; yield ['company_id', '4']; yield ['admin_status', 'active']; yield ['admin_per_page', '20']; } },
    DOMParser: class { parseFromString(text) { return { getElementById: () => ({ querySelector: selector => ({ childNodes: [selector + ':' + text] }) }) }; } },
    setTimeout: fn => { const id = timers.size + 1; timers.set(id, fn); return id; },
    clearTimeout: id => timers.delete(id),
    fetch: (url, options) => new Promise(resolve => pending.push({ url, options, resolve })),
};
vm.runInNewContext(source, context);
const flush = () => { const callbacks = [...timers.values()]; timers.clear(); callbacks.forEach(fn => fn()); };
const settle = () => new Promise(resolve => setImmediate(resolve));
const type = value => { input.value = value; handlers.input({ isComposing: false }); };
async function respond(index, text, ok = true) { pending[index].resolve({ ok, redirected: false, text: async () => text }); await settle(); }
(async () => {
    type('a'); type('am'); type('amazon');
    assert.equal(pending.length, 0, 'Typing must be debounced');
    flush();
    assert.equal(pending.length, 1, 'Typing must request results without Enter');
    assert.equal(pending[0].url.searchParams.get('admin_search'), 'amazon');
    for (const [name, value] of [['company_id', '4'], ['admin_status', 'active'], ['admin_per_page', '20']]) assert.equal(pending[0].url.searchParams.get(name), value);
    assert.equal(pending[0].url.searchParams.has('page'), false, 'New search must reset pagination');
    type('new'); flush();
    assert.ok(pending[0].options.signal.aborted, 'Earlier requests must be cancelled');
    await respond(1, 'new results');
    await respond(0, 'stale results');
    assert.ok(body.nodes[0].includes('new results'), 'Stale responses must not overwrite current results');
    assert.ok(pagination.nodes[0].includes('new results'), 'Pagination must update with results');
    assert.equal(document.activeElement, input);
    assert.equal(input.value, 'new');
    assert.equal(lastUrl.searchParams.get('admin_search'), 'new');
    assert.equal(new URL(exportLink.href).searchParams.get('admin_search'), 'new');
    type(''); flush(); await respond(2, 'all results');
    assert.ok(body.nodes[0].includes('all results'), 'Clearing search must restore results');
    type('failed'); flush(); await respond(3, '', false);
    assert.ok(body.nodes[0].includes('all results'), 'Failed requests must preserve the current table');
    assert.equal(notices.length, 1);
    assert.equal(results.attrs['aria-busy'], undefined);
    let prevented = false;
    handlers.submit({ preventDefault: () => { prevented = true; } });
    assert.ok(prevented, 'Enter must not reload the page');
    await respond(4, 'manual results');
    console.log('PASS: type-to-search, debounce, active filters, pagination, stale responses, clearing, focus, export and failure recovery.');
})().catch(error => { console.error(error); process.exitCode = 1; });
