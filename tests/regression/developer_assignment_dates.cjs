const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');const handlers={},start={value:'2026-10-12'},due={value:'2026-10-11',setCustomValidity(text){this.error=text;}};let blocked=0,reported=0;
const form={id:'assignWorkForm',querySelector:selector=>selector.includes('start_date')?start:due,reportValidity(){reported++;}};
vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-assignment-dates.js','utf8'),{Date,document:{addEventListener:(name,fn)=>handlers[name]=fn}});
handlers.input({target:{name:'start_date',closest:()=>form}});assert.equal(due.min,start.value);assert.ok(due.error);
handlers.submit({target:form,preventDefault(){blocked++;},stopImmediatePropagation(){}});assert.equal(blocked,1);assert.equal(reported,1);
due.value='2026-10-12';handlers.change({target:{name:'due_date',closest:()=>form}});assert.equal(due.error,'');
start.value='2026-10-13';handlers.input({target:{name:'start_date',closest:()=>form}});assert.ok(due.error);due.value='2026-10-14';handlers.change({target:{name:'due_date',closest:()=>form}});assert.equal(due.error,'');
console.log('PASS: date picker minimum, start-date changes, invalid submission and same/later-day deadlines.');
