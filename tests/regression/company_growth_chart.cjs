const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const handlers={},series={};for(const [freq,length]of Object.entries({daily:30,weekly:12,monthly:12}))series[freq]={labels:Array(length).fill(freq),total:Array(length).fill(3),active:Array(length).fill(2)};
const buttons=Object.keys(series).map(freq=>({dataset:{freq},classList:{toggle(name,value){this.active=value;}},setAttribute(){}}));
const card={dataset:{growth:JSON.stringify(series)},querySelectorAll:()=>buttons};let chart,updates=0;
const document={readyState:'loading',getElementById:id=>id==='companyGrowthCard'?card:{getContext:()=>({})},addEventListener:(name,fn)=>handlers[name]=fn};
function Chart(ctx,config){chart=this;this.data=config.data;this.update=()=>updates++;}
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-company-growth.js','utf8'),{document,Chart});
handlers.DOMContentLoaded();assert.equal(chart.data.labels[0],'monthly');
for(const freq of ['daily','weekly','monthly']){handlers.click({target:{closest:()=>buttons.find(b=>b.dataset.freq===freq)},preventDefault(){}});assert.equal(chart.data.labels[0],freq);assert.equal(buttons.filter(b=>b.classList.active).length,1);}
series.monthly.total[11]=4;card.dataset.growth=JSON.stringify(series);handlers['pms:records-updated']();assert.equal(chart.data.datasets[0].data[11],4);assert.equal(chart.data.labels[0],'monthly');assert.equal(updates,5);
console.log('PASS: all three filters update real datasets, active button and refreshed data while retaining frequency.');
