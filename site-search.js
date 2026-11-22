'use strict';

const results = document.querySelector('#rd-search-results');
const input = document.querySelector('#rd-search-field');
const searchIndex = new URL('search-index.json', document.currentScript.src);
const query = new URLSearchParams(window.location.search).get('s')?.trim() || '';
input.value = query;

function normalize(value) {
    return value.toLowerCase().replace(/[\u064b-\u065f\u0670\u0640]/g, '')
        .replace(/[أإآ]/g, 'ا').replace(/ى/g, 'ي');
}

async function search() {
    if (!query) {
        results.textContent = 'اكتب كلمة للبحث في الخدمات والأدلة.';
        return;
    }
    results.textContent = 'جارٍ البحث…';
    try {
        const response = await fetch(searchIndex);
        if (!response.ok) throw new Error();
        const words = normalize(query).split(/\s+/);
        const entries = (await response.json()).filter((item) => {
            const content = normalize(item.title + ' ' + item.text);
            return words.every((word) => content.includes(word));
        });
        const overview = document.createElement('p');
        overview.textContent = entries.length ? `النتائج (${entries.length})` : 'ما وجدنا نتائج مطابقة.';
        const list = document.createElement('ul');
        for (const entry of entries) {
            const item = document.createElement('li');
            const link = document.createElement('a');
            const result = new URL('..' + entry.path, window.location.href);
            if (location.pathname.endsWith('/index.html')) result.pathname += 'index.html';
            link.href = result.href;
            link.textContent = entry.title;
            item.append(link);
            if (entry.excerpt) {
                const excerpt = document.createElement('p');
                excerpt.textContent = entry.excerpt;
                item.append(excerpt);
            }
            list.append(item);
        }
        results.replaceChildren(overview, list);
    } catch {
        results.textContent = 'تعذّر تحميل نتائج البحث. أعد المحاولة.';
    }
}

search();
