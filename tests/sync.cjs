const {readFileSync}=require('node:fs');
const vm=require('node:vm');
const assert=require('node:assert/strict');
(async()=>{
 const calls=[],events=[];let changes={page:'a',wp_template:'a'},fail=false;
 const document={hidden:false,addEventListener(){},dispatchEvent:e=>events.push(e)};
 const window={mbfeConfig:{changesUrl:'/changes',changes:{},restNonce:'test'},etch:{navigation:{async listPostsAsync(type){calls.push(type);if(fail)throw Error('offline');},async listTemplatesAsync(){calls.push('templates');}}},addEventListener(){}};
 vm.runInNewContext(readFileSync('assets/sync.js','utf8'),{window,document,setInterval(){},CustomEvent:class{constructor(type,options){this.type=type;this.detail=options.detail;}},fetch:async()=>({ok:true,json:async()=>changes})});
 await window.mbfeSync.check();assert.deepEqual(calls,['page','templates']);
 await window.mbfeSync.check();assert.equal(calls.length,2,'unchanged tokens do not refresh');
 changes.page='b';fail=true;await window.mbfeSync.check();fail=false;await window.mbfeSync.check();assert.equal(calls.length,4,'failed refresh retried');
 changes.schema='a';await window.mbfeSync.check();assert.ok(events.length >= 4 && events.every(e=>e.detail.includes('Reload Etch')), 'all changes offer an Etch reload');
 document.hidden=true;changes.page='c';await window.mbfeSync.check();assert.equal(calls.length,4,'hidden tabs do not poll');
 document.hidden=false;await window.mbfeSync.check();assert.equal(calls.length,5);
 delete window.etch; changes.schema='b'; await window.mbfeSync.check(); assert.ok(events.at(-1).detail.includes('Reload Etch'), 'warning works without Etch API');
 assert.ok(events.every(e=>!e.detail.includes('hard refresh')), 'notices do not require a cache-bypassing refresh');
 console.log('PASS: changed lists, deduplication, retry, schema notice, hidden-tab pause.');
})();
