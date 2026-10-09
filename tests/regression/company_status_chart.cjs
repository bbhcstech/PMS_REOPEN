const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const handlers={},statuses=['active','trial','suspended','expired','pending','inactive'];
const counters=statuses.map(companyStatus=>({dataset:{companyStatus}}));const extras=['pending','inactive'].map(statusExtra=>({dataset:{statusExtra}}));
const card={dataset:{statusCounts:JSON.stringify({active:1,trial:0,suspended:1,expired:1,pending:0,inactive:0})},querySelectorAll:selector=>selector==='[data-company-status]'?counters:extras};let chart,updates=0;
function Chart(ctx,config){chart=this;this.data=config.data;this.update=()=>updates++;}
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-company-status.js','utf8'),{Chart,document:{readyState:'loading',getElementById:id=>id==='companyStatusCard'?card:{getContext:()=>({})},addEventListener:(type,fn)=>handlers[type]=fn}});
handlers.DOMContentLoaded();assert.deepEqual(Array.from(chart.data.datasets[0].data),[1,0,1,1,0,0]);assert.ok(extras.every(n=>n.hidden));
card.dataset.statusCounts=JSON.stringify({active:0,trial:1,suspended:0,expired:2,pending:1,inactive:1});handlers['pms:records-updated']();assert.deepEqual(Array.from(chart.data.datasets[0].data),[0,1,0,2,1,1]);assert.deepEqual(counters.map(n=>n.textContent),[0,1,0,2,1,1]);assert.ok(extras.every(n=>!n.hidden));assert.equal(chart.data.datasets[0].backgroundColor[3],'#f43f5e');
card.dataset.statusCounts='{}';handlers['pms:records-updated']();assert.ok(chart.data.datasets[0].data.every(n=>n===0));assert.ok(counters.every(n=>n.textContent===0));assert.equal(updates,2);
console.log('PASS: database status payload, refreshed donut and counts, pending/inactive visibility, colors and empty data.');
