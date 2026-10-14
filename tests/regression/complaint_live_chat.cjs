const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
async function check(identity,responseIdentity,expectMessages){
 const scheduled=[],messages=[],count={},handlers={};let requests=0;
 const root={querySelectorAll:()=>[count]};
 const feed={dataset:{liveFeed:'/messages',lastId:'0'},isConnected:true,closest:selector=>selector==='[data-live-records]'?{dataset:{liveIdentity:identity}}:selector==='[data-live-chat]'?root:null,querySelector:selector=>messages.find(n=>selector.includes('"'+n.dataset.messageId+'"')),appendChild:node=>messages.push(node)};
 const document={hidden:false,documentElement:{},scrollingElement:{scrollHeight:200,scrollTop:200,clientHeight:100},querySelectorAll:()=>[feed],addEventListener:(name,fn)=>handlers[name]=fn,createElement:()=>({content:{children:[{dataset:{messageId:'1'},cloneNode(){return this;}}]},set innerHTML(value){}})};
 const window={addEventListener(){}};
 vm.runInNewContext(fs.readFileSync('public/admin/assets/js/pms-complaint-live.js','utf8'),{window,document,location:{href:'https://example.test/chat'},URL,AbortSignal,MutationObserver:class{observe(){}},clearTimeout(){},setTimeout:(fn,delay)=>(scheduled.push({fn,delay}),1),fetch:async()=>{requests++;return {status:200,ok:true,headers:{get:()=>null},json:async()=>({success:true,identity:responseIdentity,html:'message',last_id:1,count:1})};}});
 await new Promise(resolve=>setImmediate(resolve));
 assert.equal(messages.length,expectMessages);
 if(expectMessages){assert.equal(feed.dataset.lastId,1);assert.equal(scheduled[0].delay,1000);await scheduled.shift().fn();assert.equal(messages.length,1,'Repeated response must not duplicate messages');assert.equal(requests,2);assert.equal(count.textContent,1);}
 else assert.equal(scheduled.length,0,'Foreign session must stop polling');
}
(async()=>{await check(' session-hash\n','session-hash',1);await check('tenant-hash','different-company',0);console.log('PASS: whitespace identity, incoming messages without refresh, one-second polling, cursor, deduplication and foreign-session protection.');})().catch(e=>{console.error(e);process.exitCode=1;});
