const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.join(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/admin/tasks/index.blade.php'), 'utf8');
const source = fs.readFileSync(path.join(root, 'public/admin/assets/js/pms-live-records.js'), 'utf8');

// Exercise task-list updates with the real live refresh reconciler.
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

function section(className, children=[]) {
 const match=view.match(new RegExp('<div class="'+className+'"[^>]*data-live-key="([^"]+)"'));
 assert.ok(match,className+' needs a stable refresh identity');
 return new Element(className,match[1],children);
}
function page(alerts=[],rows=1,project=false) {
 const header=section('table-header');
 const wrapper=section('table-wrapper',Array.from({length:rows},(_,i)=>new Element('task-row','task-'+i)));
 const list=section('table-card',[header,wrapper]);
 return new Element('tasks-page',null,[
  ...(project?[new Element('project-header','tasks-project-header')]:[section('breadcrumb'),section('header-card')]),
  ...alerts.map(name=>section(name)),section('filter-panel'),section('toolbar'),list,section('status-bar')
 ]);
}
const broken=page(['alert alert-success']);
const fresh=page();
for(const node of [...broken.childNodes,...fresh.childNodes])node.attrs={};
const oldList=broken.childNodes.find(n=>n.className==='table-card');
context.sync(broken,fresh);
assert.equal(oldList.childNodes.length,0,'Fixture must reproduce the original table card losing its contents');
assert.equal(broken.childNodes.find(n=>n.className==='toolbar').childNodes.length,2,'Without keys the table is rendered inside the toolbar');
for(const project of [false,true])for(const alerts of [[],['alert alert-success'],['alert alert-danger'],['alert alert-success','alert alert-danger']]) {
 const current=page(alerts,0,project);
 const list=current.childNodes.find(n=>n.className==='table-card');
 const header=list.childNodes[0],wrapper=list.childNodes[1];
 const summary=current.childNodes.find(n=>n.className==='status-bar');
 const toolbar=current.childNodes.find(n=>n.className==='toolbar');
 for(const [nextAlerts,rows] of [[['alert alert-success'],1],[[],1],[['alert alert-success'],2],[[],2],[['alert alert-danger'],2],[[],0]]) {
  context.sync(current,page(nextAlerts,rows,project));
  assert.equal(current.childNodes.find(n=>n.className==='table-card'),list,'Table card replaced after adding task');
  assert.equal(list.childNodes[0],header,'Header moved or replaced');
  assert.equal(list.childNodes[1],wrapper,'Table wrapper moved or replaced');
  assert.equal(wrapper.childNodes.length,rows,'Task rows did not update');
  assert.equal(current.childNodes.find(n=>n.className==='toolbar'),toolbar,'Toolbar matched into the table');
  assert.equal(current.childNodes.find(n=>n.className==='status-bar'),summary,'Summary cards replaced');
  assert.equal(current.childNodes.filter(n=>n.className.startsWith('alert ')).length,nextAlerts.length);
 }
}
console.log('PASS: standalone/project task lists preserve table, header, toolbar and summary across task additions and repeated success/error refreshes.');
