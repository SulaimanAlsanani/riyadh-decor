import { readdir, readFile, stat } from 'node:fs/promises';
import { join } from 'node:path';
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
if (checked < 200 || errors.length) {
    console.error([...new Set(errors)].slice(0, 35).join('\n'));
    process.exit(1);
}
console.log(`Verified ${checked} links and assets under a GitHub Pages project prefix.`);
