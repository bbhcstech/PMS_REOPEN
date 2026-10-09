const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.join(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/superadmin/companies/show.blade.php'), 'utf8');
const source = fs.readFileSync(path.join(root, 'public/admin/assets/js/pms-live-records.js'), 'utf8');
class Element {
    constructor(className, key, children = []) {
        this.nodeType = 1;
        this.tagName = 'DIV';
        this.className = className;
        this.attrs = key ? { 'data-live-key': key } : {};
        this.childNodes = [];
        children.forEach(child => this.insertBefore(child, null));
    }
    get attributes() { return Object.entries(this.attrs).map(([name, value]) => ({ name, value })); }
    get firstChild() { return this.childNodes[0] || null; }
    get nextSibling() { return this.parentNode?.childNodes[this.parentNode.childNodes.indexOf(this) + 1] || null; }
    getAttribute(name) { return this.attrs[name] ?? null; }
    hasAttribute(name) { return name in this.attrs; }
    setAttribute(name, value) { this.attrs[name] = value; }
    matches(selectors) { return selectors.split(',').some(s => s.trim() === 'script' && this.tagName === 'SCRIPT'); }
    querySelector() { return null; }
    querySelectorAll() { return []; }
    contains(node) { return this === node || this.childNodes.some(child => child.contains(node)); }
    insertBefore(node, anchor) {
        node.remove();
        const index = anchor ? this.childNodes.indexOf(anchor) : this.childNodes.length;
        this.childNodes.splice(index, 0, node);
        node.parentNode = this;
    }
    remove() {
        if (this.parentNode) this.parentNode.childNodes.splice(this.parentNode.childNodes.indexOf(this), 1);
        this.parentNode = null;
    }
    cloneNode() { return new Element(this.className, this.attrs['data-live-key'], this.childNodes.map(child => child.cloneNode())); }
}

const context = { document: { activeElement: null }, protect: 'script' };
vm.runInNewContext(source.slice(source.indexOf('    function key('), source.indexOf('    function syncDataTables(')) + '\nthis.sync = sync;', context);
function section(className) {
    const match = view.match(new RegExp('<div class="' + className + '"[^>]*data-live-key="([^"]+)"'));
    assert.ok(match, className + ' must have a stable refresh identity');
    const node = new Element(className, match[1]);
    if (className === 'metrics-summary-grid') {
        for (let i = 0; i < 5; i++) node.insertBefore(new Element('metric-summary-card'), null);
    }
    return node;
}
const stableClasses = ['top-nav-bar', 'company-header-card', 'metrics-summary-grid', 'quick-actions-bar', 'nav-tabs-wrapper'];
function workspace(alerts = []) {
    return new Element('animate-card', 'workspace', [
        section('top-nav-bar'), ...alerts.map(section),
        ...stableClasses.slice(1).map(section),
    ]);
}
// Reproduce the original section mismatch when the edit-success message disappears.
const original = workspace(['flash-alert-success']);
const fresh = workspace();
for (const node of [...original.childNodes, ...fresh.childNodes]) node.attrs = {};
context.sync(original, fresh);
assert.equal(original.childNodes[1].className, 'flash-alert-success', 'Fixture must reproduce the header being matched to an alert without identities');
for (const alerts of [[], ['flash-alert-success'], ['flash-alert-error'], ['flash-alert-success', 'flash-alert-error']]) {
    const current = workspace(alerts);
    const stable = new Map(current.childNodes.filter(node => stableClasses.includes(node.className)).map(node => [node.className, node]));
    const metricCards = [...stable.get('metrics-summary-grid').childNodes];
    for (const nextAlerts of [[], ['flash-alert-success'], [], ['flash-alert-error'], [], ['flash-alert-success', 'flash-alert-error'], []]) {
        context.sync(current, workspace(nextAlerts));
        for (const [name, node] of stable) {
            assert.equal(current.childNodes.find(item => item.className === name), node, name + ' was replaced or misplaced after refresh');
        }
        assert.deepEqual(stable.get('metrics-summary-grid').childNodes, metricCards, 'Metric cards must remain intact');
        assert.equal(current.childNodes.length, stableClasses.length + nextAlerts.length, 'Refresh introduced duplicated sections');
        assert.deepEqual(current.childNodes.filter(node => stableClasses.includes(node.className)).map(node => node.className), stableClasses);
    }
}
console.log('PASS: edit-success/error transitions preserve workspace section identities, order, five metric cards and prevent duplicates.');

