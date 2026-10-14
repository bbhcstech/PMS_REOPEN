const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function node() { return { children: [], focus() {}, classList: { add() {}, remove() {}, toggle() {}, contains() { return false; } }, append(...items) { this.children.push(...items); }, replaceChildren() { this.children = []; } }; }
const group = { querySelector: () => ({ textContent: 'Required field *' }), scrollIntoView() {} };
function field(name, value = '') {
    return { ...node(), name, value, selectedOptions: [], required: false, disabled: false, focused: false,
        setCustomValidity(message) { this.custom = message; }, setAttribute() {}, removeAttribute() {},
        get willValidate() { return !this.disabled; },
        checkValidity() { return !this.custom && (!this.required || (this.name.endsWith('[]') ? this.selectedOptions.length > 0 : Boolean(this.value))); },
        get validationMessage() { return this.custom || 'This field is required.'; },
        focus() { this.focused = true; }, closest(selector) { return selector === '.form-group' ? group : null; }
    };
}
const deadline = field('deadline', '2026-10-12'); const start = field('start_date', '2026-10-10');
const noDeadline = field('without_deadline'); noDeadline.checked = false;
const code = field('shortcode_manual'); const manual = { checked: false };
const members = field('employee_ids[]'); members.selectedOptions = [{}];
const departments = field('department_ids[]'); departments.selectedOptions = [{}];
const fields = [deadline, code, members, departments]; const feedback = node(); const handlers = {};
const selectorFields = { '[name="deadline"]': deadline, '[name="start_date"]': start, '[name="without_deadline"]': noDeadline,
    '[name="shortcode_manual"]': code, '#shortcode_manual_opt': manual, '[name="employee_ids[]"]': members, '[name="department_ids[]"]': departments };
const form = { elements: fields, querySelector: s => selectorFields[s], addEventListener: (name, callback) => handlers[name] = callback };
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-project-create-validation.js', 'utf8'), {
    window: {}, document: { readyState: 'complete', getElementById: id => id === 'projectForm' ? form : feedback, createElement: node }
});
function submit() { let prevented = false; handlers.submit({ preventDefault() { prevented = true; } }); return prevented; }
assert.equal(submit(), false, 'Valid project submission must proceed.');
deadline.value = ''; assert.equal(submit(), true); assert.equal(deadline.focused, true);
noDeadline.checked = true; assert.equal(submit(), false, 'No deadline project must submit.');
members.selectedOptions = []; assert.equal(submit(), true, 'Missing members must be visible validation error.');
members.selectedOptions = [{}]; departments.selectedOptions = []; assert.equal(submit(), true);
assert.ok(feedback.children[1].children.some(item => item.textContent.includes('project department')));
departments.selectedOptions = [{}]; noDeadline.checked = false; deadline.value = '2026-10-09'; assert.equal(submit(), true);
deadline.value = '2026-10-12'; manual.checked = true; assert.equal(submit(), true);
code.value = 'CUSTOM-001'; assert.equal(submit(), false);
console.log('PASS: valid project submission, no-deadline mode, missing member/department feedback, date ordering and manual shortcode validation.');
