const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const view = fs.readFileSync('resources/views/superadmin/subscriptions/index.blade.php', 'utf8');
const start = view.indexOf('    // 5. Drawer Handling');
const source = view.slice(start, view.indexOf('    if (closeDrawerBtn && drawer)', start));
const elements = new Map();
function element(id) {
    if (!elements.has(id)) elements.set(id, {
        style: {}, attrs: {}, innerText: '', innerHTML: '',
        setAttribute(name, value) {this.attrs[name] = value; },
        classList: {add(value) {this.value = value; }}
    });
    return elements.get(id);
}
let click;
vm.runInNewContext(source, {Date, document: {
    getElementById: element,
    addEventListener(type, handler) {assert.equal(type, 'click'); click = handler; }
}});
function open(id, name, domain) {
    const attrs = {'data-id': id, 'data-name': name, 'data-domain': domain, 'data-tenant': '#TEN-'+id,
        'data-plan': 'GOLD', 'data-status': 'Active', 'data-endsat': 'Dec 31, 2026 12:00 AM'};
    const trigger = {getAttribute(key) {return attrs[key] ?? null; }};
    // A delegated listener also handles refreshed table buttons and icon clicks.
    click({target: {closest() {return trigger; }}, preventDefault() {}});
    assert.equal(element('drawerCompanyName').innerText, name);
    assert.equal(element('drawerCompanyDomain').innerText, domain);
    assert.equal(element('drawerTenantId').innerText, '#TEN-'+id);
    assert.equal(element('drawerChangePlanTrigger').attrs['data-id'], id);
    assert.equal(element('drawerManageFeaturesBtn').attrs['data-companyid'], id);
    assert.equal(element('companyDetailDrawer').classList.value, 'open');
}
open('42', 'Actual Tenant Company', 'actual.example.com');
open('99', 'Another Company With A Long Display Name', 'another.example.com');
assert.match(view, /id="companyDetailDrawer" data-live-preserve/);
assert.match(view, /#companyDetailDrawer \.drawer-panel-custom \{ max-width: 820px; overflow: hidden !important;/);
assert.match(view, /#companyDetailDrawer \.drawer-body > \* \{ flex-shrink: 0;/);
assert.match(view, /#companyDetailDrawer \.drawer-card-box \{[^}]*max-height: none; overflow: visible !important;/);
console.log('PASS: selected company identity and action targets, refreshed/icon triggers, refresh preservation, wider drawer and non-shrinking readable detail cards.');
