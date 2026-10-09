const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const listeners = {};
const card = {dataset:{counts:JSON.stringify({FREE:0,GOLD:2,PLATINUM:1,DIAMOND:0})}};
const canvas = {getContext(){return {canvas:this};}};
let instance, created=0;
const document={readyState:'complete',getElementById(id){return id==='barChart'?canvas:card;},addEventListener(name,fn){listeners[name]=fn;}};
function Chart(ctx,config){created++;this.canvas=ctx.canvas;this.data=config.data;this.update=()=>{this.updated=true;};instance=this;}
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-dashboard-plan-distribution.js','utf8'),{document,Chart});
assert.deepEqual(Array.from(instance.data.labels),['FREE','GOLD','PLATINUM','DIAMOND']);
assert.deepEqual(Array.from(instance.data.datasets[0].data),[0,2,1,0]);
card.dataset.counts=JSON.stringify({FREE:1,GOLD:0,PLATINUM:0,DIAMOND:3});
listeners['pms:records-updated']();
assert.equal(created,1);assert.equal(instance.updated,true);
assert.deepEqual(Array.from(instance.data.datasets[0].data),[1,0,0,3]);
console.log('PASS: actual tier counts including zero, fixed tier ordering and live chart updates without recreation.');
