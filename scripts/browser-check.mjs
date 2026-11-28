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
const report={pages:[],interactions:[],errors:[]};
let browser;
try {
 browser=await chromium.launch({headless:true});
 const page=await browser.newPage({viewport:{width:1440,height:900}});
 page.on('pageerror',e=>report.errors.push(e.message));
 page.on('response',r=>{if(r.url().startsWith(base)&&r.status()>=400)report.errors.push(r.status()+' '+r.url());});
 async function verify(path, width, height, capture=false){
   await page.setViewportSize({width,height});
   await page.goto(base+path,{waitUntil:'load'});
   await page.evaluate(async()=>{for(const i of document.images)i.loading='eager';await document.fonts.ready;});
   await page.waitForFunction(()=>Array.from(document.images).filter(i=>i.getAttribute('src')).every(i=>i.complete),{},{timeout:20000});
   const result=await page.evaluate(()=>({images:[...document.images].filter(i=>i.getAttribute('src')).length,broken:[...document.images].filter(i=>i.getAttribute('src')&&!i.naturalWidth).map(i=>i.src),overflow:document.documentElement.scrollWidth>innerWidth+2,h1:document.querySelectorAll('h1').length,loadMs:Math.round(performance.getEntriesByType('navigation')[0]?.loadEventEnd||0)}));
   report.pages.push({path,width,...result});
   assert.equal(result.h1,1,path);
   assert.equal(result.broken.length,0,path+' broken images');
   assert.equal(result.overflow,false,path+' horizontal overflow');
   if(capture) await page.screenshot({path:'qa-report/'+width+'-'+(path.replace(/[^a-z0-9]/gi,'-')||'home')+'.png',fullPage:false});
 }
 for(const width of [390,768,1440]){
   for(const path of ['', 'services/', 'gallery/', 'services/mirrors/', 'gallery/mirrors/', 'contact/']) await verify(path,width,900,true);
 }
 for(const section of ['services','gallery']){
   for(const entry of await readdir(join(root,section),{withFileTypes:true})) if(entry.isDirectory()) await verify(section+'/'+entry.name+'/',1440,900);
 }
 await page.goto(base+'services/?group=decor');
 await page.locator('[data-service-filters]').waitFor({state:'visible'});
 assert.equal(await page.locator('[data-service-group="decor"]').getAttribute('aria-pressed'),'true');
 await page.locator('[data-service-group="all"]').click();
 await page.locator('[data-service-search]').fill('مرايا');
 assert.ok(await page.locator('[data-service-card]:visible').count()>0);
 await page.locator('[data-service-search]').fill('zzzznotfound');
 assert.equal(await page.locator('[data-service-card]:visible').count(),0);
 await page.locator('[data-service-reset]').click();
 assert.ok(await page.locator('[data-service-card]:visible').count()>=16);
 report.interactions.push('category filters, search and reset');
 await page.setViewportSize({width:390,height:844});
 await page.goto(base);
 await page.locator('.rd-menu-toggle').click();
 await page.locator('.rd-service-nav summary').click();
 assert.ok(await page.locator('.rd-mega a:visible').count()>=16);
 report.interactions.push('mobile menu and mega menu');
 await page.goto(base+'gallery/mirrors/');
 await page.locator('[data-gallery-image]').first().click();
 await page.locator('dialog[open]').waitFor();
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
 await form.locator('[name=details]').fill('طلب اختبار للمقاسات');
 assert.equal(await form.locator('select option:checked').getAttribute('data-service-key'),'mirrors');
 await form.locator('button[type=submit]').click();
 const link=await form.locator('[data-request-send]').getAttribute('href');
 const wa=new URL(link);
 assert.equal(wa.hostname,'wa.me');
 assert.ok(wa.searchParams.get('text').includes('طلب اختبار للمقاسات'));
 report.interactions.push('prefilled quote and WhatsApp message, not sent');
 assert.deepEqual(report.errors,[]);
} catch(e){report.errors.push(String(e.stack||e));process.exitCode=1;}
finally {
 await writeFile('qa-report/report.json',JSON.stringify(report,null,2));
 console.log(JSON.stringify({pages:report.pages.length,interactions:report.interactions,errors:report.errors}));
 await browser?.close();await new Promise(resolve=>server.close(resolve));
}
