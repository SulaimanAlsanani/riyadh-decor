import { chromium } from 'playwright';
import { createServer } from 'node:http';
import { readFile, readdir, mkdir, writeFile, stat } from 'node:fs/promises';
import { resolve, join, extname, sep } from 'node:path';
import assert from 'node:assert/strict';

const root = resolve('dist');
const prefix = '/project/';
const mime = {'.html':'text/html; charset=utf-8','.css':'text/css','.js':'application/javascript','.json':'application/json','.jpg':'image/jpeg','.jpeg':'image/jpeg','.png':'image/png','.webp':'image/webp','.svg':'image/svg+xml','.woff2':'font/woff2','.mp4':'video/mp4'};
const server = createServer(async (req,res) => {
  try {
    const url = new URL(req.url,'http://localhost');
    if (!url.pathname.startsWith(prefix)) throw Error();
    let file = resolve(root, decodeURIComponent(url.pathname.slice(prefix.length)));
    if (file !== root && !file.startsWith(root+sep)) throw Error();
    if ((await stat(file)).isDirectory()) file=join(file,'index.html');
    res.writeHead(200,{'Content-Type':mime[extname(file)] || 'application/octet-stream'});
    res.end(await readFile(file));
  } catch { res.writeHead(404);res.end('Not found'); }
});
await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));
const base='http://127.0.0.1:'+server.address().port+prefix;
await mkdir('qa-report',{recursive:true});
const report={pages:[],interactions:[],errors:[],performance:[]};
let browser;
try {
 browser=await chromium.launch({headless:true});
 const page=await browser.newPage({viewport:{width:1440,height:900}});
 // Cold-cache, throttled lab measurements separate from the eager-image visual audit.
 for(const width of [390,1440]){
   const perf=await browser.newPage({viewport:{width,height:900},deviceScaleFactor:1});
   try {
     const cdp=await perf.context().newCDPSession(perf);
     await cdp.send('Network.enable');
     await cdp.send('Network.setCacheDisabled',{cacheDisabled:true});
     await cdp.send('Network.emulateNetworkConditions',{offline:false,latency:100,downloadThroughput:200000,uploadThroughput:100000});
     await cdp.send('Emulation.setCPUThrottlingRate',{rate:4});
     await perf.addInitScript(()=>{
       window.rdPerf={lcp:null,cls:0,maxEventDuration:null,shifts:[]};
       let session=0,first=0,last=0;
       new PerformanceObserver(list=>{for(const e of list.getEntries())window.rdPerf.lcp=e.startTime;}).observe({type:'largest-contentful-paint',buffered:true});
       new PerformanceObserver(list=>{for(const e of list.getEntries()){if(e.hadRecentInput)continue;window.rdPerf.shifts.push({value:e.value,time:e.startTime,sources:e.sources.map(s=>({tag:s.node?.tagName,cls:typeof s.node?.className==='string'?s.node.className:'',previous:s.previousRect,current:s.currentRect}))});if(session&&e.startTime-last<1000&&e.startTime-first<5000)session+=e.value;else{session=e.value;first=e.startTime;}last=e.startTime;window.rdPerf.cls=Math.max(window.rdPerf.cls,session);}}).observe({type:'layout-shift',buffered:true});
       if(PerformanceObserver.supportedEntryTypes.includes('event'))new PerformanceObserver(list=>{for(const e of list.getEntries())if(e.interactionId)window.rdPerf.maxEventDuration=Math.max(window.rdPerf.maxEventDuration||0,e.duration);}).observe({type:'event',buffered:true,durationThreshold:16});
     });
     await perf.goto(base,{waitUntil:'load',timeout:60000});
     await perf.waitForTimeout(2000);
     const initial=await perf.evaluate(()=>({lcpMs:window.rdPerf.lcp,cls:window.rdPerf.cls,ttfbMs:performance.getEntriesByType('navigation')[0].responseStart,transferBytes:performance.getEntriesByType('resource').reduce((n,r)=>n+r.transferSize,0),heroSource:document.querySelector('.rd-hero-image').currentSrc,shifts:window.rdPerf.shifts,heroTiming:performance.getEntriesByName(document.querySelector('.rd-hero-image').currentSrc).map(r=>({start:r.startTime,end:r.responseEnd,bytes:r.transferSize}))}));
     if(width===390){await perf.locator('.rd-menu-toggle').click();await perf.keyboard.press('Escape');}else{await perf.locator('.rd-service-nav summary').click();await perf.keyboard.press('Escape');}
     await perf.waitForTimeout(500);
     const eventDuration=await perf.evaluate(()=>window.rdPerf.maxEventDuration);
     if(width===1440) assert.ok(initial.heroSource.endsWith('majlis-desktop-opt.webp'),'desktop optimized hero');
     report.performance.push({width,profile:'cold cache, 1.6 Mbps, 100ms latency, 4x CPU',...initial,maxSampledInteractionMs:eventDuration});
   } finally {await perf.close();}
 }
 console.log('LAB_PERFORMANCE '+JSON.stringify(report.performance));
 page.on('pageerror',e=>report.errors.push(e.message));
 page.on('response',r=>{if(r.url().startsWith(base)&&r.status()>=400)report.errors.push(r.status()+' '+r.url());});
 async function verify(path, width, height, capture=false){
   await page.setViewportSize({width,height});
   await page.goto(base+path,{waitUntil:'load'});
   await page.evaluate(async()=>{for(const i of document.images)i.loading='eager';await document.fonts.ready;});
   await page.waitForFunction(()=>Array.from(document.images).filter(i=>i.getAttribute('src')).every(i=>i.complete),{},{timeout:20000});
   const result=await page.evaluate(()=>({images:[...document.images].filter(i=>i.getAttribute('src')).length,broken:[...document.images].filter(i=>i.getAttribute('src')&&!i.naturalWidth).map(i=>i.src),overflow:document.documentElement.scrollWidth>innerWidth+2,h1:document.querySelectorAll('h1').length,loadMs:Math.round(performance.getEntriesByType('navigation')[0]?.loadEventEnd||0)}));
   report.pages.push({path,width,...result});
   if (result.broken.length || result.overflow || result.h1 !== 1) await page.screenshot({path:'qa-report/failure-'+width+'-'+(path.replace(/[^a-z0-9]/gi,'-')||'home')+'.png',fullPage:true});
   assert.equal(result.h1,1,path);
   if(/^services\/[^/]+\/$/.test(path)) assert.equal(await page.locator('[data-related-service]').count(),3,path+' complementary services');
   if(/^services\/[^/]+\/$/.test(path)) assert.equal(await page.locator('.rd-service-advice article').count(),4,path+' content guide');
   if(/^services\/[^/]+\/$/.test(path)){assert.equal(await page.locator('.rd-spec-grid article').count(),2,path+' specifications');assert.equal(await page.locator('.rd-spec-steps li').count(),3,path+' implementation stages');}
   if(/^gallery\/[^/]+\/$/.test(path))assert.ok(await page.locator('.rd-design-reference').count()>0,path+' gallery references');
   if(/^services\/[^/]+\/$/.test(path)) assert.ok(await page.locator('.rd-faq details').count()>=4,path+' expanded FAQ');
   assert.equal(result.broken.length,0,path+' broken images');
   assert.equal(result.overflow,false,path+' horizontal overflow');
   await page.evaluate(async()=>{await Promise.all([...document.images].filter(i=>i.getAttribute('src')).map(i=>i.decode()));});
   if(capture) await page.screenshot({path:'qa-report/'+width+'-'+(path.replace(/[^a-z0-9]/gi,'-')||'home')+'.png',fullPage:false});
 }
 for(const width of [390,768,1440]){
   for(const path of ['', 'services/', 'gallery/', 'services/mirrors/', 'gallery/mirrors/', 'contact/', 'guides/', 'materials-colors/', 'mirrors-guide-1/', 'about/', 'faq/', 'service-finder/', 'estimate/']) await verify(path,width,900,true);
 }
 for(const section of ['services','gallery']){
   for(const entry of await readdir(join(root,section),{withFileTypes:true})) if(entry.isDirectory()) await verify(section+'/'+entry.name+'/',1440,900);
 }
 for(const path of ["majlis-decor/","living-room-decor/","bedroom-decor/","entrance-decor/","outdoor-decor/","home-renovation/","design-styles/","material-library/","decor-coordination/","project-combinations/"]) await verify(path,390,900);
 await verify('spaces/',1440,900,true);
 await verify('material-compare/',390,900,true);await verify('favorites/',390,900,true);
 await page.goto(base+'material-compare/');assert.equal(await page.locator('[data-compare-output] article').count(),2);
 await page.locator('[data-compare=b]').selectOption({index:2});assert.equal(await page.locator('[data-compare-output] article').count(),2);
 await page.goto(base+'gallery/mirrors/');await page.locator('[data-favorite]').first().click();assert.equal(await page.locator('[data-favorite]').first().getAttribute('aria-pressed'),'true');
 await page.goto(base+'favorites/');assert.equal(await page.locator('[data-favorites-items] input:checked').count(),1);
 await page.locator('[data-favorites-quote]').click();await page.waitForURL('**/contact/?**');assert.equal(await page.locator('[name=selected_design]:checked').count(),1);
 await page.evaluate(()=>localStorage.removeItem('rd-favorites-v1'));
 report.interactions.push('material compare, local favorites and design quote transfer');
 for(const slug of ["knowledge-majlis-focal-wall","knowledge-quiet-modern","knowledge-wood-edge","knowledge-paint-test-patch","knowledge-renovation-inventory","knowledge-quantity-sheet","knowledge-mirror-cleaning","knowledge-shade-orientation"]){await verify(slug+'/',390,900,true);await verify(slug+'/',1440,900);assert.equal(await page.locator('.rd-article-guide').count(),1);}
 await page.goto(base+'guides/');
 assert.equal(await page.locator('.rd-knowledge-grid>a').count(),8);
 await page.goto(base+'search/?s='+encodeURIComponent('تنظيم طلبات التغيير أثناء التشطيب'));
 await page.locator('#rd-search-results a[href*="knowledge-change-orders"]').waitFor();
 report.interactions.push('knowledge topics and article search');
 for(const slug of ["measurement-planning","renovation-sequence","contract-scope","sample-approval","site-readiness","aftercare-planning","bedroom-solutions","tv-wall-solutions","renovation-solutions"]){await verify(slug+'/',390,900,true);await verify(slug+'/',1440,900);assert.ok(await page.locator('.rd-space-services article').count()>=3);}
 report.interactions.push('project and needs-first editorial coverage');
 for(const slug of ["dining-room-decor","hallway-decor","home-office-decor","staircase-decor","rooftop-decor","palace-decor","rest-house-decor","office-decor","retail-decor","restaurant-decor"]) {await verify(slug+'/',390,900,true);await verify(slug+'/',1440,900);assert.equal(await page.locator('.rd-space-banner-v25 img').count(),1);assert.ok(await page.locator('.rd-space-services article').count()>=3);}
 report.interactions.push('expanded room and property content');
 for(const slug of ["solutions","privacy-solutions","storage-solutions","wall-solutions","compare-ceilings","compare-wall-panels","color-palette","light-and-color","courtyard-planning","outdoor-privacy","contractor-selection","quotation-review"]) {await verify(slug+'/',390,900,true);await verify(slug+'/',1440,900);}
 await page.goto(base+'solutions/');
 assert.equal(await page.locator('#solution-catalog .rd-space-card').count(),10);
 await page.goto(base+'search/?s='+encodeURIComponent('مقارنة عروض أسعار الديكور'));
 await page.locator('#rd-search-results a[href*="quotation-review"]').waitFor();
 report.interactions.push('solutions library and expanded search');
 for(const path of ["project-guides/","lighting-planning/","wall-preparation/","budget-planning/","handover-checklist/","maintenance-guide/","small-space-planning/"]) { await verify(path,390,900,true); await verify(path,1440,900,true); }
 await page.goto(base+'project-guides/');
 assert.equal(await page.locator('#project-guide-catalog .rd-space-card').count(),12);
 assert.equal(await page.locator('#contract-guides .rd-space-card').count(),2);
 await page.goto(base+'lighting-planning/');
 assert.equal(await page.locator('.rd-space-services article').count(),3);
 report.interactions.push('project guidance library and service links');
 for(const path of ["property-types/","material-wood/","material-glass/","material-flooring/","material-paint/","material-ceiling/","material-wallcovering/","compare-flooring/","compare-walls/","compare-partitions/","style-modern/","style-classic/","style-neoclassic/","villa-decor/","apartment-decor/","villa-renovation/","apartment-renovation/"]) await verify(path,390,900);
 for(const slug of ["style-contemporary","style-minimalist","style-modern-arabic","material-parquet","material-vinyl","material-cement-board","material-foam-fiber","material-carved-wood","material-sandwich-panel","compare-mirrors","compare-paint-sheen","compare-shades"]){await verify(slug+'/',390,900,true);await verify(slug+'/',1440,900);assert.ok(await page.locator('.rd-space-services article').count()>=1);}
 await verify('material-library/',1440,900,true);
 await verify('design-styles/',1440,900,true);
 await verify('property-types/',1440,900,true);
 await page.goto(base+'property-types/');
 assert.equal(await page.locator('.rd-space-card').count(),9);
 await page.goto(base+'material-library/');
 assert.equal(await page.locator('#material-guides .rd-space-card').count(),12);
 assert.equal(await page.locator('#material-comparisons .rd-space-card').count(),8);
 await page.goto(base+'design-styles/');
 assert.equal(await page.locator('.rd-encyclopedia .rd-space-card').count(),6);
 report.interactions.push('material library and property guides');
 await page.goto(base+'spaces/');
 assert.equal(await page.locator('.rd-space-card').count(),15);
 await page.goto(base+'bedroom-decor/');
 assert.equal(await page.locator('.rd-space-services article').count(),5);
 report.interactions.push('space catalog and complementary links');
 await page.goto(base);
 assert.equal(await page.locator('.rd-split-banner img').count(),1);
 assert.equal(await page.locator('#services [data-service-card]').count(),6);
 assert.equal(await page.locator('#quick-quote').count(),1);
 assert.equal(await page.locator('.rd-home-v20').count(),1);
 assert.equal(await page.locator('.rd-discovery-grid>a').count(),3);
 assert.equal(await page.locator('.rd-home-knowledge .rd-article-card').count(),3);
 await page.locator('.rd-explore-nav summary').click();
 assert.equal(await page.locator('.rd-explore-nav .rd-explore-tray a:visible').count(),6);
 await page.keyboard.press('Escape');
 assert.equal(await page.locator('.rd-explore-nav').getAttribute('open'),null);
 report.interactions.push('grouped content navigation and home discovery');
 assert.equal(await page.locator('.rd-visual-categories a').count(),4);
 assert.equal(await page.locator('.rd-visual-categories img').count(),4);
 assert.equal(await page.locator('.rd-material-story img').count(),3);
 assert.equal(await page.locator('.rd-room-photos img').count(),2);
 await page.evaluate(async()=>{for(const img of document.images) if(img.getAttribute('src')) img.loading='eager';await document.fonts.ready;});
 await page.waitForFunction(()=>[...document.images].filter(i=>i.getAttribute('src')).every(i=>i.complete&&i.naturalWidth>0),{},{timeout:30000});
 await page.evaluate(async()=>{await Promise.all([...document.images].filter(i=>i.getAttribute('src')).map(i=>i.decode()));});
 await page.screenshot({path:'qa-report/home-full-desktop.png',fullPage:true});
 assert.equal(await page.locator('.rd-footer-lead a').count(),2);
 assert.equal(await page.locator('.rd-float .rd-icon').count(),2);
 const tokens=await page.evaluate(()=>({control:getComputedStyle(document.documentElement).getPropertyValue('--rd-control-height').trim(),radius:getComputedStyle(document.documentElement).getPropertyValue('--rd-card-radius').trim()}));
 assert.equal(tokens.control,'44px');assert.equal(tokens.radius,'14px');
 await page.goto(base);
 assert.equal(await page.locator('#rd-quick-service option').count(),17);
 await page.locator('#rd-quick-service').selectOption('mirrors');
 await Promise.all([page.waitForURL('**/contact/?service=mirrors'),page.locator('[data-quick-quote] button').click()]);
 assert.equal(await page.locator('[data-request] select[name=service] option:checked').getAttribute('data-service-key'),'mirrors');
 report.interactions.push('homepage quick quote navigation');
 await page.goto(base+'gallery/mirrors/');
 const designQuote=page.locator('.rd-photo-actions .rd-card-quote').first();
 await designQuote.click();
 await page.waitForURL('**/contact/?**');
 assert.ok((await page.locator('[data-request] [name=details]').inputValue()).includes('مرجع التصميم:'));
 assert.equal(await page.locator('[data-request] select[name=service] option:checked').getAttribute('data-service-key'),'mirrors');
 report.interactions.push('gallery design reference to quote');
 await page.goto(base+'service-finder/');
 await page.locator('[data-finder-space]').selectOption('outdoor');
 assert.equal(await page.locator('[data-finder-card]:visible').count(),3);
 await page.goto(base+'estimate/');
 await page.locator('[data-estimate] [name=service]').selectOption('flooring');
 await page.locator('[name=length]').fill('4');
 await page.locator('[name=width]').fill('3');
 await page.locator('[name=quantity]').fill('2');
 assert.ok((await page.locator('[data-estimate-result]').textContent()).includes('م²'));
 await page.locator('[data-estimate] button').click();
 await page.waitForURL('**/contact/?**');
 assert.ok((await page.locator('[data-request] [name=details]').inputValue()).includes('24.00'));
 report.interactions.push('service finder and measurement estimate transfer');
 await page.goto(base+'services/?group=decor');
 await page.locator('[data-service-filters]').waitFor({state:'visible'});
 assert.equal(await page.locator('[data-service-group="decor"]').getAttribute('aria-pressed'),'true');
 await page.locator('[data-service-group="all"]').click();
 await page.locator('[data-service-search]').fill('مرايا');
 assert.ok(await page.locator('[data-service-card]:visible').count()>0);
 await page.locator('[data-service-clear]').click();
 assert.equal(await page.locator('[data-service-search]').inputValue(),'');
 await page.locator('[data-service-search]').fill('zzzznotfound');
 assert.equal(await page.locator('[data-service-card]:visible').count(),0);
 await page.locator('[data-service-reset]').click();
 assert.ok(await page.locator('[data-service-card]:visible').count()>=16);
 assert.ok((await page.locator('.rd-card-quote').first().getAttribute('href')).includes('service='));
 report.interactions.push('category filters, search, clear, reset and contextual quote links');
 await page.setViewportSize({width:390,height:844});
 await page.goto(base);
 await page.locator('[data-mobile-quote]').waitFor({state:'visible'});
 await page.locator('[data-mobile-quote]').click();
 await page.waitForFunction(()=>document.querySelector('[data-mobile-quote]').hidden);
 await page.evaluate(()=>window.scrollTo(0,0));
 await page.locator('.rd-menu-toggle').click();
 await page.locator('.rd-service-nav summary').click();
 assert.ok(await page.locator('.rd-mega a:visible').count()>=16);
 report.interactions.push('mobile menu and mega menu');
 await page.goto(base+'gallery/mirrors/');
 assert.ok(await page.locator('.rd-photo-zoom').count()>0);
 assert.ok(await page.locator('[data-share-design]:visible').count()>0);
 await page.locator('[data-gallery-search]').fill('zzzznotfound');
 assert.equal(await page.locator('.rd-photo:visible').count(),0);
 assert.ok(await page.locator('[data-gallery-empty]').isVisible());
 await page.locator('[data-gallery-clear]').click();
 assert.ok(await page.locator('.rd-photo:visible').count()>0);
 const designId=await page.locator('.rd-photo').first().getAttribute('id');
 assert.ok(designId.startsWith('design-'));
 await page.context().grantPermissions(['clipboard-read','clipboard-write']);
 await page.evaluate(()=>Object.defineProperty(navigator,'share',{value:undefined,configurable:true}));
 await page.locator('[data-share-design]').first().click();
 await page.waitForFunction(()=>document.querySelector('.rd-share-status').textContent.includes('نسخ'));
 assert.ok((await page.evaluate(()=>navigator.clipboard.readText())).endsWith('#'+designId));
 report.interactions.push('individual design share link');
 await page.locator('[data-gallery-image]').first().click();
 await page.locator('dialog[open]').waitFor();
 assert.ok(await page.locator('.rd-dialog-thumbs button').count()>1);
 await page.locator('.rd-dialog-thumbs button').nth(1).click();
 assert.equal(await page.locator('.rd-dialog-thumbs button').nth(1).getAttribute('aria-pressed'),'true');
 await page.locator('dialog [data-next]').click();
 await page.locator('dialog [data-close]').click();
 report.interactions.push('gallery lightbox');
 await page.goto(base+'search/?s='+encodeURIComponent('مرايا'));
 await page.locator('#rd-search-results a').first().waitFor();
 assert.ok((await page.locator('#rd-search-results a').first().getAttribute('href')).startsWith(base));
 report.interactions.push('site-wide search');
 await page.goto(base+'contact/?service=mirrors');
 const form=page.locator('[data-request]');
 await form.locator('[name=customer]').fill('اختبار آلي');
 await form.locator('[name=area]').fill('الرياض');
 await form.locator('[name=site_state]').selectOption('موقع جديد');
 await form.locator('[name=stage]').selectOption('أجهز المقاسات');
 await form.locator('[name=details]').fill('طلب اختبار للمقاسات');
 assert.ok((await form.locator('[data-details-count]').textContent()).startsWith(String('طلب اختبار للمقاسات'.length)));
 assert.equal(await form.locator('select[name=service] option:checked').getAttribute('data-service-key'),'mirrors');
 await form.locator('button[type=submit]').click();
 const link=await form.locator('[data-request-send]').getAttribute('href');
 const wa=new URL(link);
 assert.equal(wa.hostname,'wa.me');
 assert.ok(wa.searchParams.get('text').includes('طلب اختبار للمقاسات'));
 assert.ok(wa.searchParams.get('text').includes('موقع جديد'));
 assert.ok(wa.searchParams.get('text').includes('أجهز المقاسات'));
 report.interactions.push('prefilled quote and WhatsApp message, not sent');
 await page.goto(base+'estimate/');
 await page.locator('[data-estimate] [name=service]').selectOption('flooring');
 await page.locator('[name=length]').fill('5');await page.locator('[name=width]').fill('4');await page.locator('[name=quantity]').fill('2');await page.locator('[name=waste]').fill('10');
 assert.ok((await page.locator('[data-estimate-result]').textContent()).includes('44.00'));
 await page.locator('[name=waste]').fill('51');assert.ok((await page.locator('[data-estimate-result]').textContent()).includes('صحيحة'));await page.locator('[name=waste]').fill('10');
 await page.locator('[data-estimate] button[type=submit]').click();await page.waitForURL('**/contact/?**');assert.ok((await page.locator('[name=details]').inputValue()).includes('44.00'));
 await page.locator('[name=customer]').fill('اختبار');await page.locator('[name=area]').fill('الرياض');await page.locator('.rd-extra-services summary').click();await page.locator('[name=extra_service]').last().check();await page.locator('[data-request] button[type=submit]').click();
 assert.ok(new URL(await page.locator('[data-request-send]').getAttribute('href')).searchParams.get('text').includes('خدمات إضافية'));
 await page.goto(base+'favorites/');await page.evaluate(()=>localStorage.setItem('rd-favorites-v1','{broken'));await page.reload();assert.ok((await page.locator('[data-favorites-status]').textContent()).includes('لم تحفظ'));await page.evaluate(()=>localStorage.removeItem('rd-favorites-v1'));
 await page.goto(base+'search/?s='+encodeURIComponent('مرايا'));await page.locator('.rd-search-filters button').last().click();assert.equal(await page.locator('#rd-search-results li:not([hidden]):not([data-kind=article])').count(),0);
 report.interactions.push('release: quantity bounds, multi-service WhatsApp, corrupted storage and search classification');
 const audit=JSON.parse(await readFile(join(root,'content-audit.json'),'utf8'));
 const visited=new Set(report.pages.map(p=>p.path+'@'+p.width));
 for(const path of audit.editorialPaths){const relative=path.replace(/^\//,'');for(const width of [390,1440])if(!visited.has(relative+'@'+width))await verify(relative,width,900,false);}
 report.interactions.push('all editorial pages checked on mobile and desktop');
 assert.deepEqual(report.errors,[]);
} catch(e){report.errors.push(String(e.stack||e));process.exitCode=1;}
finally {
 await writeFile('qa-report/report.json',JSON.stringify(report,null,2));
 console.log(JSON.stringify({pages:report.pages.length,interactions:report.interactions,errors:report.errors}));
 await browser?.close();await new Promise(resolve=>server.close(resolve));
}
