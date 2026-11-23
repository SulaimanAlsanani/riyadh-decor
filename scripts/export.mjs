import { mkdir, rm, writeFile, copyFile, readdir, readFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { load } from 'cheerio';

const [localAddress, publicAddress] = process.argv.slice(2);
const origin = new URL(localAddress);
const target = new URL(publicAddress);
const base = target.pathname.replace(/\/+$/, '');
const output = resolve('dist');
const allowedAssets = new Set(['.css', '.js', '.svg', '.gif', '.png', '.webp', '.jpg', '.jpeg', '.woff', '.woff2', '.mp4']);
const assets = new Set();
const articles = [];

function absolute(path) {
    return new URL(base + path, target.origin).href;
}

function relative(from, outputPath) {
    const fromParts = from.split('/').filter(Boolean);
    const outputParts = outputPath.split('/').filter(Boolean);
    while (fromParts.length && fromParts[0] === outputParts[0]) {
        fromParts.shift();
        outputParts.shift();
    }
    const path = '../'.repeat(fromParts.length) + outputParts.join('/');
    return (path || '.') + (outputPath.endsWith('/') ? '/' : '');
}

async function read(path) {
    const response = await fetch(new URL(path, origin), { signal: AbortSignal.timeout(25000) });
    if (!response.ok) throw new Error(`${response.status} ${path}`);
    return response;
}

async function save(path, body) {
    const file = resolve(output, path.replace(/^\//, ''));
    if (!file.startsWith(output + '/')) throw new Error(`Invalid output path: ${path}`);
    await mkdir(dirname(file), { recursive: true });
    await writeFile(file, body);
}

function filePath(path) {
    return path.endsWith('/') ? path + 'index.html' : path;
}

function destination(url) {
    if (url.pathname === '/guides/' && url.searchParams.has('guide_page')) {
        const number = Number(url.searchParams.get('guide_page'));
        return number > 1 ? `/guides/page/${number}/` : '/guides/';
    }
    if (url.pathname.startsWith('/wp-includes/') || url.pathname.startsWith('/wp-content/')) {
        if (allowedAssets.has(url.pathname.slice(url.pathname.lastIndexOf('.')))) {
            assets.add(url.pathname);
            return url.pathname;
        }
        return '';
    }
    return url.pathname;
}

function rewrite(raw, current, forMetadata = false) {
    if (!raw || /^(#|data:|mailto:|tel:|javascript:)/i.test(raw)) return raw;
    const url = new URL(raw, origin);
    if (url.origin !== origin.origin) return raw;
    const path = destination(url);
    if (!path) return raw;
    const query = url.pathname === '/guides/' && url.searchParams.has('guide_page') ? '' : url.search;
    return (forMetadata ? absolute(path) : relative(current, path)) + query + url.hash;
}

await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });

const sitemap = load(await (await read('/wp-sitemap.xml')).text(), { xml: true });
const maps = sitemap('sitemap > loc').toArray().map((e) => new URL(sitemap(e).text()).pathname);
const routes = new Map([['/', '/']]);
const articlePaths = new Set();
for (const map of maps.filter((path) => /wp-sitemap-(posts|taxonomies)-/.test(path))) {
    const submap = load(await (await read(map)).text(), { xml: true });
    for (const location of submap('url > loc').toArray()) {
        const path = new URL(submap(location).text()).pathname;
        routes.set(path, path);
        if (map.includes('posts-post-')) articlePaths.add(path);
    }
}

const guidesPages = Math.ceil(articlePaths.size / 6);
for (let p = 2; p <= guidesPages; p++) routes.set(`/guides/page/${p}/`, `/guides/?guide_page=${p}`);
routes.set('/search/', '/?s=riyadh-decor-internal-placeholder');
for (const required of ['/services/', '/services/mirrors/', '/gallery/', '/contact/']) {
    if (!routes.has(required)) throw new Error(`The WordPress sitemap is missing ${required}`);
}

for (const [path, source] of routes) {
    const $ = load(await (await read(source)).text());
    if ($('#content h1').length !== 1) throw new Error(`Invalid page: ${source}`);

    $('link[rel="https://api.w.org/"],link[rel="wlwmanifest"],link[rel="EditURI"],link[rel="shortlink"],link[rel="pingback"],link[rel="alternate"][type],link[rel="dns-prefetch"],a[href*="/feed/"],script#wp-emoji-settings,script#wp-emoji-settings + script,script#wp-emoji-loader,style#wp-emoji-styles-inline-css').remove();
    $('link[rel="alternate"][href*="/wp-json/"],script[src*="s.w.org"]').remove();

    if (articlePaths.has(path) || path.startsWith('/services/') && path !== '/services/') {
        const title = $('#content h1').first().text().trim();
        const excerpt = $('meta[name="description"]').attr('content') || $('#content p').first().text().trim();
        articles.push({ path, title, excerpt, text: $('#content').text().replace(/\s+/g, ' ').trim() });
    }

    if (path === '/search/') {
        $('#content h1').text('البحث في الموقع');
        $('#content .rd-crumb [aria-current]').text('البحث');
        $('#content .rd-section > p').remove();
        $('#rd-search-field').val('');
        $('#content .rd-section').append('<div id="rd-search-results" aria-live="polite"></div>');
        $('head').append('<meta name="robots" content="noindex,follow">');
        $('title').text('البحث في الموقع | ديكورات الرياض');
    }
    if (path.startsWith('/guides/page/')) {
        $('link[rel="canonical"]').remove();
        $('head').append($('<link rel="canonical">').attr('href', absolute(path)));
    }

    $('[href],[src],[action],[poster]').each((_, element) => {
        for (const attr of ['href','src','action','poster']) {
            const value = $(element).attr(attr);
            if (!value) continue;
            const mustBeAbsolute = element.name === 'link' && $(element).attr('rel') === 'canonical';
            $(element).attr(attr, rewrite(value, path, mustBeAbsolute));
        }
    });
    $('form.rd-search').attr('action', relative(path, '/search/'));
    $('[srcset]').each((_, element) => {
        const value = $(element).attr('srcset');
        $(element).attr('srcset', value.split(',').map((item) => {
            const [sourceUrl, descriptor] = item.trim().split(/\s+/, 2);
            return [rewrite(sourceUrl, path), descriptor].filter(Boolean).join(' ');
        }).join(', '));
    });
    $('meta[property^="og:"]').each((_, element) => {
        const value = $(element).attr('content');
        if (value?.startsWith(origin.origin)) $(element).attr('content', rewrite(value, path, true));
    });
    $('script[type="application/ld+json"]').each((_, element) => {
        const schema = JSON.parse($(element).text());
        const clean = JSON.stringify(schema, (_key, value) =>
            typeof value === 'string' ? value.replaceAll(origin.origin, target.origin + base) : value);
        $(element).text(clean.replaceAll('<', '\\u003c'));
    });

    if (path === '/search/') {
        $('body').append($('<script defer></script>').attr('src', relative(path, '/site-search.js')));
    }
    await save(filePath(path), $.html());
}

const themeAssets = '/wp-content/themes/riyadh-decor/assets/';
const themeFolder = resolve('riyadh-decor/assets');
await mkdir(resolve(output, '.' + themeAssets), { recursive: true });
for (const name of await readdir(themeFolder)) {
    await copyFile(join(themeFolder, name), resolve(output, '.' + themeAssets, name));
}
for (const path of assets) {
    if (path.startsWith(themeAssets)) continue;
    const response = await read(path);
    await save(path, Buffer.from(await response.arrayBuffer()));
}

const searchScript = await readFile('scripts/search.js');
await save('/site-search.js', searchScript);
await save('/search-index.json', JSON.stringify(articles));
await save('/.nojekyll', '');
await save('/robots.txt', `User-agent: *\nAllow: /\nSitemap: ${absolute('/sitemap.xml')}\n`);
function xml(value) { return value.replace(/&/g, '&amp;').replace(/</g, '&lt;'); }
await save('/sitemap.xml', '<?xml version="1.0" encoding="UTF-8"?>\n' +
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' +
    [...routes.keys()].filter((path) => path !== '/search/' && !path.startsWith('/guides/page/'))
        .map((path) => '<url><loc>' + xml(absolute(path)) + '</loc></url>').join('') + '</urlset>');

const missing = load(await (await read('/')).text());
missing('meta[name="description"],meta[property^="og:"],link[rel="canonical"]').remove();
missing('title').text('الصفحة غير موجودة | ديكورات الرياض');
missing('head').append('<meta name="robots" content="noindex,follow">');
missing('head').html(`<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,follow"><title>الصفحة غير موجودة | ديكورات الرياض</title><link rel="stylesheet" href="${absolute('/wp-content/themes/riyadh-decor/style.css')}">`);
missing('body').html(`<main class="rd-shell rd-section"><h1>الصفحة غير موجودة</h1><p><a href="${absolute('/')}">العودة إلى الرئيسية</a></p></main>`);
await save('/404.html', missing.html());
console.log(`Exported ${routes.size} routes, ${articles.length} searchable entries and ${assets.size} referenced assets.`);
