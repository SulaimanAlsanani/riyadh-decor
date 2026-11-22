import { cp, mkdir, readFile, readdir, rm, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { load } from 'cheerio';

const source = 'dist';
const output = 'web-preview';
await rm(output, { recursive: true, force: true });
await mkdir(output);
await cp(source, output, { recursive: true });

function explicitIndex(raw) {
    if (/^(?:[a-z][a-z0-9+.-]*:|\/\/|#)/i.test(raw)) return raw;
    return raw.replace(/^(.*\/)([?#].*)?$/, (_url, path, suffix = '') => path + 'index.html' + suffix);
}

async function visit(directory) {
    for (const entry of await readdir(directory, { withFileTypes: true })) {
        const filename = join(directory, entry.name);
        if (entry.isDirectory()) await visit(filename);
        if (!entry.isFile() || !entry.name.endsWith('.html')) continue;
        const $ = load(await readFile(filename, 'utf8'));
        $('[href],[action]').each((_index, element) => {
            for (const key of ['href', 'action']) {
                const value = $(element).attr(key);
                if (value) $(element).attr(key, explicitIndex(value));
            }
        });
        $('head meta[name="robots"]').remove();
        $('head').append('<meta name="robots" content="noindex,follow">');
        await writeFile(filename, $.html());
    }
}

await visit(output);
