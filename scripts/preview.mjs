import { cp, mkdir, readFile, readdir, rm, writeFile, stat } from 'node:fs/promises';
import { join, relative, resolve, sep } from 'node:path';
import { load } from 'cheerio';

const source = 'dist';
const output = 'web-preview';
const root = resolve(output);
const repository = process.env.GITHUB_REPOSITORY || 'SulaimanAlsanani/riyadh-decor';
const preview = new URL('https://raw.githack.com/' + repository + '/web-preview/');
const local = new URL('https://preview.invalid/');
let verified = 0;
await rm(output, { recursive: true, force: true });
await mkdir(output);
await cp(source, output, { recursive: true });

async function rewrite(raw, page) {
    if (!raw || /^(?:[a-z][a-z0-9+.-]*:|\/\/|#)/i.test(raw)) return raw;
    const url = new URL(raw, new URL(page, local));
    if (url.origin !== local.origin) return raw;
    if (url.pathname.endsWith('/')) url.pathname += 'index.html';
    const filename = resolve(root, '.' + decodeURIComponent(url.pathname));
    if (!filename.startsWith(root + sep) || !(await stat(filename)).isFile()) {
        throw new Error('Missing preview target: ' + page + ' -> ' + raw);
    }
    verified++;
    return new URL(url.pathname.slice(1) + url.search + url.hash, preview).href;
}

async function visit(directory) {
    for (const entry of await readdir(directory, { withFileTypes: true })) {
        const filename = join(directory, entry.name);
        if (entry.isDirectory()) await visit(filename);
        if (!entry.isFile() || !entry.name.endsWith('.html')) continue;
        const page = relative(root, resolve(filename)).split(sep).join('/');
        const $ = load(await readFile(filename, 'utf8'));
        for (const element of $('[href],[action],[src],[poster],[srcset]').toArray()) {
            for (const key of ['href', 'action', 'src', 'poster']) {
                const value = $(element).attr(key);
                if (value) $(element).attr(key, await rewrite(value, page));
            }
            const srcset = $(element).attr('srcset');
            if (srcset && !srcset.startsWith('data:')) {
                const items = [];
                for (const item of srcset.split(',')) {
                    const [url, descriptor] = item.trim().split(/\s+/, 2);
                    items.push([await rewrite(url, page), descriptor].filter(Boolean).join(' '));
                }
                $(element).attr('srcset', items.join(', '));
            }
        }
        $('head meta[name="robots"]').remove();
        $('head').append('<meta name="robots" content="noindex,follow">');
        await writeFile(filename, $.html());
    }
}
await visit(output);
if (verified < 200) throw new Error('Insufficient preview targets checked: ' + verified);
console.log('Verified and rewrote ' + verified + ' preview URLs.');
