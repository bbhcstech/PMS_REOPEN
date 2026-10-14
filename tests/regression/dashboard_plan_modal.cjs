const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const assert = require('node:assert/strict');
const view = fs.readFileSync(path.join(__dirname, '../../resources/views/superadmin/dashboard.blade.php'), 'utf8');
const start = view.indexOf('      // Plan Change Modal handlers');
const source = view.slice(start, view.indexOf('      if (closePlanBtn && planModal)', start));
const handlers = {}, choices = [1, 2, 3, 4].map(id => {
    const card = {};
    return { value: String(id), checked: false, disabled: false, closest: () => card };
});
const elements = {
    planChangeModal: { classList: { add() {} }, addEventListener: (name, fn) => { handlers[name] = fn; }, querySelector: () => choices.find(input => input.checked && !input.disabled) },
    closePlanModalBtn: {}, confirmPlanChangeBtn: {},
    modalPlanCompanyId: {}, modalPlanBillingCycle: {},
    assignPlanForm: { addEventListener: (name, fn) => { handlers[name] = fn; } },
};
const trigger = { attrs: { 'data-company-id': '42', 'data-current-plan-id': '3', 'data-allowed-plan-ids': '[3,4]', 'data-billing-cycle': 'yearly' }, getAttribute(name) { return this.attrs[name]; } };
const document = { getElementById: id => elements[id], querySelectorAll: () => choices, addEventListener: (name, fn) => { handlers[name] = fn; } };
vm.runInNewContext(source, { document });
const open = () => handlers.click({ target: { closest: () => trigger }, preventDefault() {} });
open();
assert.deepEqual(choices.map(input => input.disabled), [true, true, false, false]);
assert.deepEqual(choices.map(input => input.checked), [false, false, true, false]);
assert.equal(elements.modalPlanCompanyId.value, '42');
assert.equal(elements.modalPlanBillingCycle.value, 'yearly');
assert.equal(elements.confirmPlanChangeBtn.disabled, false);
choices.forEach(input => { input.checked = input.value === '4'; });
handlers.change({ target: { disabled: false, matches: () => true } });
let prevented = false;
handlers.submit({ preventDefault: () => { prevented = true; } });
assert.equal(prevented, false, 'Eligible upgrade must submit the form');
assert.equal(elements.confirmPlanChangeBtn.disabled, true, 'Duplicate submissions must be blocked');
choices.forEach(input => { input.checked = input.value === '1'; });
handlers.submit({ preventDefault: () => { prevented = true; } });
assert.equal(prevented, true, 'Disabled lower tiers must not submit');
trigger.attrs['data-company-id'] = '99'; trigger.attrs['data-current-plan-id'] = '4'; trigger.attrs['data-allowed-plan-ids'] = '[4]';
open();
assert.deepEqual(choices.map(input => input.disabled), [true, true, true, false]);
assert.equal(elements.modalPlanCompanyId.value, '99');
assert.equal(elements.confirmPlanChangeBtn.textContent, 'Confirm Change');
trigger.attrs['data-allowed-plan-ids'] = '[]'; open();
assert.ok(choices.every(input => !input.checked && input.disabled));
assert.equal(elements.confirmPlanChangeBtn.disabled, true);
console.log('PASS: Dashboard current/higher tiers, disabled lower tiers, billing cycle, selected company and guarded native submission.');
