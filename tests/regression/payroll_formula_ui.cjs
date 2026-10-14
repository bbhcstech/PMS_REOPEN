const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict');
function node(properties = {}) {
    const result = Object.assign({value: '', dataset: {}, children: [], listeners: {}, disabled: false,
        addEventListener(event, fn) { this.listeners[event] = fn; },
        async fire(event) { return this.listeners[event]?.call(this, {preventDefault() {}}); },
        append(...items) { this.children.push(...items); }, replaceChildren(...items) { this.children = items; },
        get childElementCount() { return this.children.length; },
        querySelectorAll() { return this.children.flatMap(child => child.dataset.variable ? [child] : child.querySelectorAll()); },
        focus() {}, setRangeText(text) { this.value += text; },
    }, properties);
    let value = String(result.value);
    Object.defineProperty(result, 'value', {get: () => value, set: next => value = String(next)});
    return result;
}
const ids = {};
for (const id of ['payrollFormulaData', 'formulaExpression', 'testerExpression', 'formulaEditorInputs', 'formulaTesterInputs', 'formulaEditorFeedback', 'formulaTesterFeedback',
    'formulaVariablePalette', 'formulaEnabled', 'formulaEditorTitle', 'formulaTesterTitle', 'formulaEditorModal', 'formulaTesterModal', 'addPayrollFormula', 'runFormulaTest', 'testEditorFormula']) ids[id] = node({id});
const fields = {};
for (const name of ['name', 'code', 'category', 'formula', 'description', 'effective_from', 'effective_to', '_method', 'formula_id', 'version', 'test_inputs']) fields[name] = name === 'formula' ? ids.formulaExpression : node();
const save = node();
const editor = node({action: '', querySelector(selector) { return selector.includes('button') ? save : fields[selector.match(/name="([^"]+)"/)[1]]; }, reportValidity() { return true; },
    reset() { for (const field of Object.values(fields)) field.value = ''; fields.category.value = 'earnings'; fields.effective_from.value = '2026-09-01'; }});
ids.payrollFormulaEditor = editor;
const config = {formulas: {1: {id: 1, name: 'Existing rule', code: 'CURRENT_BASIC', category: 'earnings', formula: 'BASIC * 0.1', version: 4, effective_from: '2026-09-01', is_active: true, test_inputs: {BASIC: 30000}}},
    definitions: {CURRENT_BASIC: ['earnings', ['BASIC']]}, customVariables: {deduction: ['GROSS_SALARY'], earnings: ['BASIC']}, samples: {GROSS_SALARY: 38181.82, BASIC: 30000},
    examples: [{name: 'Recovery test', code: 'RECOVERY', category: 'deduction', formula: 'ROUND(GROSS_SALARY * 0.02, 2)'}],
    old: {}, storeUrl: '/pms/payroll/formulas', editUrl: '/pms/payroll/formulas/__ID__', testUrl: '/pms/payroll/formulas/test', csrf: 'csrf'};
ids.payrollFormulaData.textContent = JSON.stringify(config);
const buttons = { '[data-test-formula]': [node({dataset: {testFormula: '1'}})], '[data-edit-formula]': [node({dataset: {editFormula: '1'}})],
    '[data-test-example]': [node({dataset: {testExample: '0'}})], '[data-use-example]': [node({dataset: {useExample: '0'}})] };
let requests = 0, failure = false, hold = false, finish, nativeSubmits = 0, lastPayload;
const response = () => ({ok: !failure, json: async () => failure ? {success: false, error: 'Division by zero.'} : {success: true, formatted: '₹763.64', result: 763.64}});
class FormDataMock { constructor() { this.entries = Object.entries(fields).map(([key, field]) => [key, field.value]); } [Symbol.iterator]() { return this.entries[Symbol.iterator](); } }
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-payroll-formulas.js', 'utf8'), {
    document: {getElementById: id => ids[id], querySelectorAll: selector => buttons[selector] || [], createElement: () => node(), addEventListener: (event, fn) => { if (event === 'DOMContentLoaded') fn(); }},
    bootstrap: {Modal: {getOrCreateInstance: () => ({show() {}})}}, FormData: FormDataMock,
    HTMLFormElement: {prototype: {submit() { nativeSubmits++; }}},
    fetch: async (url, options) => { requests++; assert.equal(url, config.testUrl); lastPayload = JSON.parse(options.body); assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf');
        if (hold) return new Promise(resolve => finish = () => resolve(response())); return response(); },
});
(async () => {
    await buttons['[data-edit-formula]'][0].fire('click');
    assert.equal(editor.action, '/pms/payroll/formulas/1'); assert.equal(fields._method.value, 'PUT'); assert.equal(fields.version.value, '4');
    assert.equal(fields.effective_from.value, '2026-09-01'); assert.equal(fields.code.readOnly, true);
    await buttons['[data-test-example]'][0].fire('click');
    assert.equal(ids.formulaTesterInputs.querySelectorAll().length, 1, 'Function names became variable inputs');
    await ids.runFormulaTest.fire('click');
    assert.equal(lastPayload.variables.GROSS_SALARY, 38181.82); assert(ids.formulaTesterFeedback.textContent.includes('₹763.64'));
    failure = true; await ids.runFormulaTest.fire('click'); assert.equal(ids.formulaTesterFeedback.textContent, 'Division by zero.');
    assert.equal(ids.runFormulaTest.disabled, false); failure = false;
    await buttons['[data-use-example]'][0].fire('click'); assert.equal(fields.category.value, 'deduction'); assert.equal(editor.action, config.storeUrl);
    assert.equal(fields.code.readOnly, false); assert.equal(ids.formulaEnabled.checked, false);
    const sampleInput = ids.formulaEditorInputs.querySelectorAll()[0]; sampleInput.value = '';
    const before = requests; await ids.testEditorFormula.fire('click'); assert.equal(requests, before); assert(ids.formulaEditorFeedback.textContent.includes('numeric value'));
    sampleInput.value = '38181.82';
    hold = true; const pending = editor.fire('submit'); await editor.fire('submit'); assert.equal(requests, before + 1, 'Duplicate submit issued two requests');
    fields.effective_from.value = '2026-10-01'; finish(); await pending;
    assert.equal(nativeSubmits, 0); assert(ids.formulaEditorFeedback.textContent.includes('settings changed'));
    assert.equal(save.disabled, false);
    fields.effective_from.value = '2026-09-01'; hold = false; await editor.fire('submit');
    assert.equal(nativeSubmits, 1); assert.equal(JSON.parse(fields.test_inputs.value).GROSS_SALARY, 38181.82);
    console.log('PASS: formula editor and examples, variable extraction, saved dates/version, subdirectory URLs, read-only testing, numeric validation, failure recovery, duplicate protection and metadata changes during save.');
})().catch(error => { console.error(error); process.exitCode = 1; });
