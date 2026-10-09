const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
async function scenario(status,initial='suspended'){
 const timers=[],redirects=[];
 const document={body:{dataset:{restrictionUrl:'/subscription/suspended',restrictionStatus:initial}},hidden:false,addEventListener(){}};
 vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-restriction-status.js','utf8'),{document,window:{location:{replace:url=>redirects.push(url)},addEventListener(){}},AbortSignal,clearTimeout(){},setTimeout:(fn,delay)=>(timers.push({fn,delay}),1),fetch:async()=>({ok:true,status:200,json:async()=>({status})})});
 await new Promise(resolve=>setImmediate(resolve));return {timers,redirects};
}
(async()=>{
 for(const status of ['active','trial'])assert.equal((await scenario(status)).redirects[0],'/subscription/suspended','Restored company must navigate through server Dashboard redirect');
 const suspended=await scenario('suspended');assert.equal(suspended.redirects.length,0);assert.equal(suspended.timers[0].delay,2000);
 assert.equal((await scenario('expired')).redirects.length,1,'Expired transition must choose expiry screen');
 assert.equal((await scenario('expired','expired')).redirects.length,0,'Expiry page must not repeatedly reload');
 console.log('PASS: automatic reactivation redirect, remaining suspension, genuine expiry transition and no expiry reload loop.');
})().catch(e=>{console.error(e);process.exitCode=1;});
