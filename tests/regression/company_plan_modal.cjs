const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const view = fs.readFileSync(path.join(__dirname, '../../resources/views/superadmin/companies/index.blade.php'), 'utf8');
const source = view.slice(view.indexOf('    // 6. Plan Change Modal'), view.indexOf('    // 7. Company Detail Drawer'));
const nodes = {}, listeners = {}, cards = [];
function classes() { const values = new Set(); return { add: value => values.add(value), remove: value => values.delete(value), toggle: (value, enabled) => enabled ? values.add(value) : values.delete(value), contains: value => values.has(value) }; }
for (let id = 1; id <= 4; id++) {
    const radio = { value: String(id), checked: false, disabled: false, addEventListener: (_, fn) => { radio.change = fn; } };
    cards.push({ radio, classList: classes(), querySelector: () => radio });
}
const modal = {
    classList: classes(),
    querySelectorAll: selector => selector === '.plan-card-option' ? cards : cards.map(card => card.radio),
    querySelector: selector => cards.map(card => card.radio).find(radio => radio.checked && (!selector.includes(':not(:disabled)') || !radio.disabled)),
};
nodes.planChangeModal = modal;
nodes.closePlanModalBtn = { addEventListener() {} };
nodes.confirmPlanChangeBtn = {};
nodes.planChangeCompanyId = {};
nodes.planChangeBillingCycle = {};
nodes.companyPlanChangeForm = { addEventListener: (name, fn) => { listeners[name] = fn; } };
const trigger = { attrs: { 'data-allowed-planids': '[3,4]', 'data-current-planid': '3', 'data-company-id': '42', 'data-billing-cycle': 'yearly' }, getAttribute(name) { return this.attrs[name]; }, addEventListener: (_, fn) => { trigger.open = fn; } };
vm.runInNewContext(source, { document: { getElementById: id => nodes[id], querySelectorAll: () => [trigger] } });
trigger.open.call(trigger, { preventDefault() {} });
assert.deepEqual(cards.map(card => card.radio.disabled), [true, true, false, false], 'Lower plans must be disabled');
assert.ok(cards[2].radio.checked && cards[2].classList.contains('selected'), 'Current plan must be selected');
assert.equal(nodes.planChangeCompanyId.value, '42');
assert.equal(nodes.planChangeBillingCycle.value, 'yearly');
assert.equal(nodes.confirmPlanChangeBtn.disabled, false);
cards.forEach(card => { card.radio.checked = card.radio.value === '4'; });
cards[3].radio.change.call(cards[3].radio);
assert.ok(cards[3].classList.contains('selected'), 'Higher plan must be selectable');
let prevented = false;
listeners.submit({ preventDefault: () => { prevented = true; } });
assert.equal(prevented, false, 'Valid upgrade must submit');
assert.equal(nodes.confirmPlanChangeBtn.disabled, true, 'Prevent duplicate submission');
cards.forEach(card => { card.radio.checked = card.radio.value === '1'; });
listeners.submit({ preventDefault: () => { prevented = true; } });
assert.ok(prevented, 'A disabled lower plan must not submit');
trigger.attrs['data-allowed-planids'] = '[4]';
trigger.attrs['data-current-planid'] = '4';
trigger.attrs['data-company-id'] = '99';
trigger.open.call(trigger, { preventDefault() {} });
assert.deepEqual(cards.map(card => card.radio.disabled), [true, true, true, false]);
assert.equal(nodes.planChangeCompanyId.value, '99', 'Reopening must target the new company');
console.log('PASS: current/higher tier selection, lower-tier restriction, selected company, billing cycle and real form submission.');
