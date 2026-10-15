const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const view = fs.readFileSync('resources/views/superadmin/subscriptions/index.blade.php', 'utf8');
const renderer = view.slice(view.indexOf('    function renderCompanyFeatureOverrides('), view.indexOf('    const overrideCompanySelect', view.indexOf('    function renderCompanyFeatureOverrides(')));
const applyStart = view.indexOf('    function applyCompanyFeatureStates(');
const apply = view.slice(applyStart, view.indexOf('    if (overrideCompanySelect)', applyStart));
const checkbox = { setAttribute() {} }, base = {}, status = {}, loads = [];
const row = {
    getAttribute: () => '12',
    querySelector: selector => selector === '.company-override-input' ? checkbox : selector === '.base-plan-status-cell' ? base : status,
};
const context = {
    featureStateGeneration: 0,
    overrideCompanySelect: { value: '1' },
    window: { companyOverridesMap: { 1: { name: 'Tenant', plan: 'DIAMOND', overrides: {} } } },
    document: { getElementById: id => id === 'overrideCompanySelect' ? context.overrideCompanySelect : null,
        querySelectorAll: selector => selector === '.company-override-input' ? [checkbox] : [row] },
    escapeFeatureText: value => String(value), loadCompanyFeatureStates: id => loads.push(id),
};
vm.createContext(context);
vm.runInContext(renderer + apply, context);
context.renderCompanyFeatureOverrides('1');
assert.equal(checkbox.disabled, true, 'Do not guess Diamond features while loading');
assert.deepEqual(loads, ['1']);
const set = state => context.applyCompanyFeatureStates('1', { 12: state });
set({ effective: 0, plan: 0, override: null, blocked_by: null });
assert.equal(checkbox.checked, false);
assert.equal(checkbox.disabled, false);
assert.match(base.textContent, /Not Included/);
assert.match(status.innerHTML, /Inactive/);
set({ effective: 1, plan: 0, override: 1, blocked_by: null });
assert.equal(checkbox.checked, true);
assert.match(status.innerHTML, /Super Admin Granted/);
set({ effective: 0, plan: 1, override: 0, blocked_by: null });
assert.equal(checkbox.checked, false);
assert.match(status.innerHTML, /Super Admin Revoked/);
assert.match(base.textContent, /^Included/);
set({ effective: 0, plan: 1, override: 1, blocked_by: 'Work' });
assert.equal(checkbox.checked, false);
assert.match(status.innerHTML, /Off via Work/);
context.overrideCompanySelect.value = '2';
set({ effective: 1, plan: 1, override: null });
assert.equal(checkbox.checked, false, 'Late response for another company must be ignored');
context.overrideCompanySelect.value = '';
context.renderCompanyFeatureOverrides(null);
set({ effective: 1, plan: 1, override: null });
assert.equal(checkbox.disabled, true, 'Cleared selection must stay disabled after a late response');
assert.ok(!view.includes('$isEnabledInDb || $isDefaultEnabled'), 'Matrix must not OR saved state with tier defaults');
console.log('PASS: server-authoritative plan defaults, grants, revocations, parent blocking, load state and company response isolation.');
