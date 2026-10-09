const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../../resources/views/superadmin/subscriptions/index.blade.php'), 'utf8');
const renderer = view.slice(view.indexOf('    function renderCompanyFeatureOverrides('), view.indexOf('    const overrideCompanySelect', view.indexOf('    function renderCompanyFeatureOverrides(')));
const checkbox = { setAttribute() {} }, base = {}, status = {};
const row = {
    getAttribute: name => name === 'data-moduleid' ? '12' : 'projects',
    querySelector: selector => selector === '.company-override-input' ? checkbox : selector === '.base-plan-status-cell' ? base : status,
};
const context = {
    window: { companyOverridesMap: { 1: { name: 'Tenant', plan: 'DIAMOND', plan_features: [], overrides: {} } } },
    document: { getElementById: () => null, querySelectorAll: () => [row] },
};
vm.runInNewContext(renderer + '\nthis.render = renderCompanyFeatureOverrides;', context);
context.render('1');
assert.equal(checkbox.checked, true);
assert.ok(status.innerHTML.includes('Active · Plan Default'));
context.window.companyOverridesMap[1].plan = 'FREE';
context.render('1');
assert.equal(checkbox.checked, false);
assert.ok(status.innerHTML.includes('Inactive · Plan Default'));
context.window.companyOverridesMap[1].overrides[12] = 1;
context.render('1');
assert.equal(checkbox.checked, true);
assert.ok(status.innerHTML.includes('Active · Super Admin Granted'));
context.window.companyOverridesMap[1].overrides[12] = 0;
context.render('1');
assert.equal(checkbox.checked, false);
assert.ok(status.innerHTML.includes('Inactive · Super Admin Revoked'));
assert.ok(view.includes("{{ $mod->is_active ? 'Active' : 'Inactive' }}"), 'Stored module status must be visible');
console.log('PASS: module visibility status and company Active/Inactive labels for defaults, grants and revocations.');
