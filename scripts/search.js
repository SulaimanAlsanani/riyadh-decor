'use strict';

const results = document.querySelector('#rd-search-results');
const input = document.querySelector('#rd-search-field');
const scriptBase = new URL('.', document.currentScript.src);
const searchIndex = new URL('search-index.json', scriptBase);
const query = new URLSearchParams(window.location.search).get('s')?.trim() || '';
input.value = query;

function normalize(value) {
    return value.toLowerCase().replace(/[\u064b-\u065f\u0670\u0640]/g, '')
        .replace(/[أإآ]/g, 'ا').replace(/ى/g, 'ي').replace(/ة/g,'ه');
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
        entries.sort((a,b)=>{const score=item=>words.reduce((sum,w)=>sum+(normalize(item.title).includes(w)?3:0),0);return score(b)-score(a);});
        const overview = document.createElement('p');
        overview.textContent = entries.length ? `النتائج (${entries.length})` : 'ما وجدنا نتائج مطابقة.';
        const filters=document.createElement('div');filters.className='rd-search-filters';const types=[['all','الكل'],['service','الخدمات'],['guide','الأدلة'],['article','المقالات']];const list = document.createElement('ul');
        for (const entry of entries) {
            const item = document.createElement('li');
            const link = document.createElement('a');
            const result = new URL(entry.path.replace(/^\//, ''), scriptBase);
            if (location.pathname.endsWith('/index.html')) result.pathname += 'index.html';
            link.href = result.href;
            link.textContent = entry.title;
            item.dataset.kind=entry.kind||'guide';const category=document.createElement('small');category.textContent=types.find(x=>x[0]===item.dataset.kind)?.[1]||'الأدلة';item.append(category,link);
            if (entry.excerpt) {
                const excerpt = document.createElement('p');
                excerpt.textContent = entry.excerpt;
                item.append(excerpt);
            }
            list.append(item);
        }
        for(const [kind,label] of types){const button=document.createElement('button');button.type='button';button.textContent=label;button.setAttribute('aria-pressed',String(kind==='all'));button.addEventListener('click',()=>{for(const other of filters.children)other.setAttribute('aria-pressed',String(other===button));let count=0;for(const item of list.children){item.hidden=kind!=='all'&&item.dataset.kind!==kind;if(!item.hidden)count++;}overview.textContent='النتائج ('+count+')';});filters.append(button);}results.replaceChildren(overview,filters,list);
    } catch {
        results.textContent = 'تعذّر تحميل نتائج البحث. أعد المحاولة.';
    }
}

search();
