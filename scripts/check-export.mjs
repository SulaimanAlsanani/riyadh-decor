import { readdir, readFile, stat } from 'node:fs/promises';
import { join } from 'node:path';
import { createHash } from 'node:crypto';
import { load } from 'cheerio';

const output = 'dist';
const origin = 'https://test.invalid';
const mount = '/project';
const errors = [];
let checked = 0;

async function visit(path) {
    for (const entry of await readdir(path, { withFileTypes: true })) {
        const filename = join(path, entry.name);
        if (entry.isDirectory()) await visit(filename);
        if (entry.isFile() && entry.name === 'index.html') await validate(filename);
    }
}

async function validate(filename) {
    const document = await readFile(filename, 'utf8');
    const $ = load(document);
    const location = mount + '/' + filename.slice(output.length + 1, -'index.html'.length);
    if ($('html').attr('dir') !== 'rtl' || $('h1').length !== 1) errors.push(`Invalid layout: ${filename}`);
    for (const element of $('[href],[src],[poster],[action],[srcset]').toArray()) {
        const urls = ['href', 'src', 'poster', 'action'].map((attribute) => $(element).attr(attribute));
        const srcset = $(element).attr('srcset');
        if (srcset) urls.push(...srcset.split(',').map((value) => value.trim().split(/\s+/)[0]));
        for (const value of urls.filter(Boolean)) {
            if (value.startsWith('#')) {
                if (!$('[id]').toArray().some((item) => $(item).attr('id') === value.slice(1))) {
                    errors.push(`${filename} → ${value}: missing anchor`);
                }
                continue;
            }
            const url = new URL(value, origin + location);
            if (url.origin !== origin) continue;
            if (!url.pathname.startsWith(mount + '/')) {
                errors.push(`${filename} → ${value}: escaped the project path`);
                continue;
            }
            const relative = decodeURI(url.pathname.slice(mount.length + 1));
            const local = join(output, url.pathname.endsWith('/') ? join(relative, 'index.html') : relative);
            try {
                if (!(await stat(local)).isFile()) throw new Error();
                checked++;
            } catch {
                errors.push(`${filename} → ${value}: missing ${local}`);
            }
        }
    }
}
await visit(output);
const home = load(await readFile(join(output, 'index.html'), 'utf8'));
for (const selector of ['.rd-home-intro', '.rd-home-banner img', '#design-gallery', '#request', '.rd-mega']) {
    if (!home(selector).length) errors.push('Homepage missing ' + selector);
}
if (home('#services [data-service-card]').length < 16) errors.push('Homepage service catalog incomplete');
const index = JSON.parse(await readFile(join(output,'search-index.json'),'utf8'));
if(index.filter(x=>/guide-1\/$/.test(x.path)).length < 16) errors.push('Missing specialist articles');
for(const slug of ["spaces","majlis-decor","living-room-decor","bedroom-decor","entrance-decor","outdoor-decor","home-renovation","design-styles","material-library","decor-coordination","project-combinations"]) {try {const html=load(await readFile(join(output,slug,'index.html'),'utf8'));if(!html('.rd-space-content').length)errors.push('Missing space content: '+slug);}catch{errors.push('Missing space page: '+slug);}}
for(const slug of ["property-types","material-wood","material-glass","material-flooring","material-paint","material-ceiling","material-wallcovering","compare-flooring","compare-walls","compare-partitions","style-modern","style-classic","style-neoclassic","villa-decor","apartment-decor","villa-renovation","apartment-renovation"]) {try {const html=load(await readFile(join(output,slug,'index.html'),'utf8'));if(!html('.rd-library-content').length||!html('meta[name=description]').attr('content'))errors.push('Missing library content or metadata: '+slug);}catch{errors.push('Missing library page: '+slug);}}
const heroOriginal=await readFile('riyadh-decor/assets/majlis.webp');
const heroOptimized=await readFile('riyadh-decor/assets/majlis-desktop-opt.webp');
if(heroOptimized.length>=heroOriginal.length)errors.push('Hero optimization did not reduce bytes');
if(!home('.rd-hero-image').attr('srcset')?.includes('majlis-desktop-opt.webp'))errors.push('Optimized hero not connected');
const fonts = await readFile(join(output, 'wp-content/themes/riyadh-decor/assets/fonts.css'), 'utf8');
for (const match of fonts.matchAll(/url\(([^)]+)\)/g)) {
    const file = match[1].replace(/['"]/g, '');
    if (/^(https?:|data:)/.test(file)) continue;
    try { if (!(await stat(join(output, 'wp-content/themes/riyadh-decor/assets', file))).isFile()) throw new Error(); checked++; }
    catch { errors.push('Missing font: ' + file); }
}
const originals = 'riyadh-decor/assets';
const exported = join(output, 'wp-content/themes/riyadh-decor/assets');
let preserved = 0;
for (const name of await readdir(originals)) {
    if (!/\.(jpg|jpeg|png|webp|gif|svg)$/i.test(name)) continue;
    const hash = data => createHash('sha256').update(data).digest('hex');
    try {
        if (hash(await readFile(join(originals,name))) !== hash(await readFile(join(exported,name)))) throw Error();
        preserved++;
    } catch { errors.push('Original image changed or missing: ' + name); }
}
console.log('Byte-identical original images: ' + preserved);
if (checked < 200 || errors.length) {
    console.error([...new Set(errors)].slice(0, 35).join('\n'));
    process.exit(1);
}
console.log(`Verified ${checked} links and assets under a GitHub Pages project prefix.`);
