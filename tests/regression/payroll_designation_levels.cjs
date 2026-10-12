const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const events = {};
const field = {replaceChildren(option) { this.option = option; }};
const designation = {
    selectedOptions: [{dataset: {designationLevel: 'L0'}}],
    closest() { return {querySelector() {return field; }}; },
    matches() { return true; }
};
const context = {
    Option: function (text, value) { this.text = text; this.value = value; },
    document: {
        readyState: 'complete',
        querySelectorAll() {return [designation]; },
        addEventListener(name, handler) {events[name] = handler; }
    }
};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-payroll-designations.js', 'utf8'), context);
assert.equal(field.option.value, 'L0');
designation.selectedOptions[0].dataset.designationLevel = 'L6';
events.change({target: designation});
assert.equal(field.option.value, 'L6');
designation.selectedOptions = [];
events.change({target: designation});
assert.equal(field.option.value, '');
assert.equal(field.option.text, 'Select a designation first');
console.log('PASS: designation selection synchronizes L0/L6 and clears the level when no real designation is selected.');
