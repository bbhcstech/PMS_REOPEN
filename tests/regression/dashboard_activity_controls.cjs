const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const handlers={},controls={activityTableSearch:{value:''},activityEntriesPerPageSelect:{value:'10'},activityShowingText:{},activityPaginationControls:{innerHTML:'',appendChild(){}}};
const rows=Array.from({length:12},(_,i)=>{const values=['2026-10-10',i===11?'Instagram':'Amazon, "India"','Company updated','127.0.0.1','Success'];return {textContent:values.join(' '),style:{},querySelectorAll:()=>values.map(textContent=>({textContent}))};});
let blob,anchor,clicks=0,revoked=0;
const document={readyState:'loading',getElementById:id=>controls[id],querySelector:()=>null,querySelectorAll:()=>rows,addEventListener:(type,fn)=>handlers[type]=fn,createElement:()=>anchor={style:{},setAttribute(k,v){this[k]=v;},click(){clicks++;}},body:{appendChild(){},removeChild(){}}};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-dashboard-activity.js','utf8'),{document,Blob,Date,alert(){throw Error('Unexpected empty export');},URL:{createObjectURL:b=>(blob=b,'blob:test'),revokeObjectURL:()=>revoked++},setTimeout:fn=>fn()});
(async()=>{
handlers.DOMContentLoaded();assert.equal(rows[11].style.display,'none');
controls.activityTableSearch.value='instagram';handlers.input({target:{id:'activityTableSearch'}});assert.equal(rows[11].style.display,'');assert.ok(rows.slice(0,11).every(r=>r.style.display==='none'));
const exportClick=()=>handlers.click({target:{closest:()=>true},preventDefault(){}});
exportClick();assert.ok((await blob.text()).includes('Instagram'));assert.ok(!(await blob.text()).includes('Amazon'));
controls.activityTableSearch.value='amazon';handlers.input({target:{id:'activityTableSearch'}});exportClick();let csv=await blob.text();assert.equal(csv.split('\r\n').length,12,'Export all matching rows across pages');assert.ok(csv.includes('"Amazon, ""India"""'));assert.equal(clicks,2);assert.equal(revoked,2);
controls.activityTableSearch={value:'127.0.0.1'};handlers['pms:records-updated']();assert.ok(controls.activityShowingText.innerHTML.includes('12 entries'));
controls.activityEntriesPerPageSelect.value='20';handlers.change({target:{id:'activityEntriesPerPageSelect',value:'20'}});assert.ok(rows.every(r=>r.style.display===''));
console.log('PASS: live search, hidden rows, filtered CSV across pages, CSV escaping, refresh and page size.');
})().catch(e=>{console.error(e);process.exitCode=1;});
