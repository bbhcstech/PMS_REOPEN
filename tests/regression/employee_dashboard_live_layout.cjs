const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.join(__dirname, '../..');
const view = fs.readFileSync(path.join(root, 'resources/views/employee-dashboard.blade.php'), 'utf8');
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


const sections = [...view.matchAll(/^            <(div|section)[^>]*data-live-key="(employee-[^"]+)"/gm)].map(match => ({tag:match[1].toUpperCase(),key:match[2],className:match[0].match(/class="([^"]+)"/)[1]}));
assert.equal(sections.length,11);
for(const key of ['employee-success','employee-error','employee-clock-display','employee-clock-attendance','employee-clock-location','employee-clock-timers'])assert.ok(view.includes('data-live-key="'+key+'"'));
function dashboard(alerts=[]){return new Element('employee-dashboard','root',[
 ...alerts.map(key=>new Element('alert',key)),
 ...sections.map(section=>{const node=new Element(section.className,section.key,[new Element('card-grid','cards-'+section.key,[new Element('card','card-'+section.key)])]);node.tagName=section.tag;return node;})
]);}
const broken=dashboard(['employee-success']),fresh=dashboard();
for(const node of [...broken.childNodes,...fresh.childNodes])node.attrs={};
context.sync(broken,fresh);
assert.ok(broken.childNodes.some((node,i)=>node.className!==fresh.childNodes[i]?.className),'Unkeyed sections must reproduce the layout failure');
const current=dashboard(['employee-success']);
const original=new Map(current.childNodes.filter(n=>n.attrs['data-live-key']!=='employee-success').map(n=>[n.attrs['data-live-key'],n]));
const cards=new Map([...original].map(([key,node])=>[key,node.firstChild.firstChild]));
for(const alerts of [[],['employee-success'],[],['employee-error'],[],['employee-success','employee-error'],[]]){
 context.sync(current,dashboard(alerts));
 assert.deepEqual(current.childNodes.map(n=>n.attrs['data-live-key']),[...alerts,...sections.map(s=>s.key)]);
 for(const [key,node]of original){assert.equal(current.childNodes.find(n=>n.attrs['data-live-key']===key),node);assert.equal(node.firstChild.firstChild,cards.get(key));}
}
function clock(state){return new Element('employee-clock','clock',[
 new Element('text-lg-end','employee-clock-display'),
 ...(state==='in'?[]:[new Element('clock-requirements','requirements')]),
 ...(state==='idle'?[]:[new Element('clock-attendance-card','employee-clock-attendance',[
 ...(state==='in'?[new Element('photo','photo')]:[]),
 new Element('clock-location-note','employee-clock-location'),
 new Element('clock-live-grid','employee-clock-timers')
 ])])
]);}
const live=clock('idle');let attendance, timers;
for(const state of ['in','out','in','out']){
 context.sync(live,clock(state));
 assert.deepEqual(live.childNodes.map(n=>n.attrs['data-live-key']),clock(state).childNodes.map(n=>n.attrs['data-live-key']));
 const card=live.childNodes.find(n=>n.attrs['data-live-key']==='employee-clock-attendance');
 attendance??=card;assert.equal(card,attendance);
 const timer=card.childNodes.find(n=>n.attrs['data-live-key']==='employee-clock-timers');
 timers??=timer;assert.equal(timer,timers);
 assert.equal(card.className,'clock-attendance-card');
}
console.log('PASS: Employee clock-in/out and repeated flash transitions preserve all 11 dashboard sections, card containers and attendance timer layout.');
