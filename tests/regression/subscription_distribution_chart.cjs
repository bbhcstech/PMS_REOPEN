const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const handlers={},center={},items=Array.from({length:4},()=>({dot:{style:{}},lastElementChild:{},querySelector(){return this.dot;}}));
const card={dataset:{distribution:JSON.stringify({FREE:2,GOLD:1,PLATINUM:0,DIAMOND:0})},querySelector:()=>center,querySelectorAll:()=>items};let chart,updates=0;
function Chart(ctx,config){chart=this;this.data=config.data;this.update=()=>updates++;}
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-subscription-distribution.js','utf8'),{Chart,document:{readyState:'loading',addEventListener:(type,fn)=>handlers[type]=fn,getElementById:id=>id==='subscriptionDistributionCard'?card:{getContext:()=>({})}}});
handlers.DOMContentLoaded();assert.equal(center.textContent,3);assert.equal(items[0].lastElementChild.textContent,'67% (2)');assert.equal(items[0].dot.style.background,'#64748b');assert.equal(items[3].dot.style.background,'#7c3aed');
card.dataset.distribution=JSON.stringify({FREE:0,GOLD:0,PLATINUM:0,DIAMOND:3});handlers['pms:records-updated']();assert.deepEqual(Array.from(chart.data.datasets[0].data),[0,0,0,3]);assert.equal(items[3].lastElementChild.textContent,'100% (3)');assert.equal(chart.data.datasets[0].backgroundColor[3],items[3].dot.style.background);
card.dataset.distribution='{}';handlers['pms:records-updated']();assert.equal(center.textContent,0);assert.ok(items.every(item=>item.lastElementChild.textContent==='0% (0)'));assert.equal(updates,2);
console.log('PASS: real distribution, refreshed chart/legend/total, fixed FREE/DIAMOND colors and zero-company state.');
