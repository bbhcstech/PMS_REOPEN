const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const handlers={},controls={userGrowthFrequency:{value:'yearly'},userGrowthYear:{value:'2026',options:[],appendChild(x){this.options.push(x);}},userGrowthMonth:{value:'2026-02'}};
const card={dataset:{userGrowth:JSON.stringify({today:'2026-10-10',records:[{date:'2025-12-01',total:2,active:1},{date:'2026-02-07',total:3,active:2},{date:'2026-02-08',total:1,active:0},{date:'2026-02-28',total:2,active:2}]})}};let chart;
function Chart(ctx,config){chart=this;this.data=config.data;this.update=()=>{};}
const document={readyState:'loading',getElementById:id=>id==='platformUserGrowthCard'?card:controls[id]||{getContext:()=>({})},addEventListener:(type,fn)=>handlers[type]=fn,createElement:()=>({})};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-platform-user-growth.js','utf8'),{document,Chart,Date});handlers.DOMContentLoaded();assert.equal(chart.data.labels.length,12);assert.equal(chart.data.datasets[0].data[0],2);assert.equal(chart.data.datasets[0].data[1],8);assert.equal(chart.data.datasets[0].data[10],null);
controls.userGrowthFrequency.value='weekly';handlers.change({target:{id:'userGrowthFrequency'}});assert.deepEqual(Array.from(chart.data.datasets[0].data),[5,6,6,8]);assert.deepEqual(Array.from(chart.data.datasets[1].data),[3,3,3,5]);assert.equal(controls.userGrowthMonth.hidden,false);
controls.userGrowthYear.value='2025';controls.userGrowthFrequency.value='yearly';handlers.change({target:{id:'userGrowthYear'}});assert.equal(chart.data.datasets[0].data[11],2);
handlers['pms:records-updated']();assert.equal(controls.userGrowthYear.value,'2025');assert.equal(chart.data.datasets[0].data[11],2);
console.log('PASS: yearly selection, selected-month weekly boundaries, active counts, baseline, future periods and refresh selection retention.');
