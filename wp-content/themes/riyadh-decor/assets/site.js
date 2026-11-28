'use strict';

const smallScreen = window.matchMedia('(max-width: 780px)');
const menuToggle = document.querySelector('.rd-menu-toggle');
const navigation = document.querySelector('#rd-navigation');
const serviceNav = document.querySelector('.rd-service-nav');

if (menuToggle && navigation) {
    const setMenu = (open) => {
        menuToggle.hidden = !smallScreen.matches;
        navigation.hidden = smallScreen.matches && !open;
        menuToggle.setAttribute('aria-expanded', String(smallScreen.matches && open));
        if (!open && serviceNav) serviceNav.open = false;
    };

    setMenu(false);
    navigation.removeAttribute("data-initial-menu");
    smallScreen.addEventListener('change', () => setMenu(false));
    menuToggle.addEventListener('click', () => setMenu(navigation.hidden));
    navigation.addEventListener('click', (event) => {
        if (smallScreen.matches && event.target.closest('a[href]')) setMenu(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || navigation.hidden) return;
        if (serviceNav?.open) {
            serviceNav.open = false;
            serviceNav.querySelector('summary').focus();
        } else if (smallScreen.matches) {
            setMenu(false);
            menuToggle.focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (serviceNav?.open && !serviceNav.contains(event.target)) serviceNav.open = false;
    });
}

function normalizeServiceSearch(text) {
    return text.trim().toLocaleLowerCase('ar')
        .replace(/[\u064B-\u065F\u0670\u0640]/g, '')
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ى/g, 'ي')
        .replace(/ة/g, 'ه');
}

for (const list of document.querySelectorAll('[data-service-list]')) {
    const filters = list.querySelector('[data-service-filters]');
    if (!filters) continue;

    const search = filters.querySelector('[data-service-search]');
    const groups = [...filters.querySelectorAll('[data-service-group]')];
    const status = filters.querySelector('[data-service-status]');
    const empty = list.querySelector('[data-service-empty]');
    const clear = filters.querySelector('[data-service-clear]');
    const cards = [...list.querySelectorAll('[data-service-card]')].map((card) => ({
        element: card,
        group: card.dataset.serviceCard,
        text: normalizeServiceSearch(card.dataset.serviceName + ' ' + card.querySelector('.rd-card-body').textContent),
    }));
    const requestedGroup = new URLSearchParams(location.search).get('group');
    let activeGroup = groups.some(button => button.dataset.serviceGroup === requestedGroup) ? requestedGroup : 'all';
    for (const button of groups) button.setAttribute('aria-pressed', String(button.dataset.serviceGroup === activeGroup));

    const update = () => {
        const words = normalizeServiceSearch(search.value).split(/\s+/).filter(Boolean);
        let count = 0;
        for (const { element, group, text } of cards) {
            const matchesGroup = activeGroup === 'all' || group === activeGroup;
            const visible = matchesGroup && words.every((word) => text.includes(word));
            element.hidden = !visible;
            if (visible) count++;
        }
        empty.hidden = count > 0;
        if (clear) clear.hidden = !search.value.trim() && activeGroup === 'all';
        status.textContent = count ? 'عدد النتائج: ' + count.toLocaleString('ar-SA') : 'لا توجد نتائج مطابقة.';
    };

    for (const button of groups) {
        button.addEventListener('click', () => {
            activeGroup = button.dataset.serviceGroup;
            for (const item of groups) item.setAttribute('aria-pressed', String(item === button));
            update();
        });
    }
    search.addEventListener('input', update);
    clear?.addEventListener('click', () => { search.value = ''; groups[0].click(); search.focus(); });
    empty.querySelector('[data-service-reset]').addEventListener('click', () => {
        search.value = '';
        groups[0].click();
        groups[0].focus();
    });
    update();
    filters.hidden = false;
}

for (const form of document.querySelectorAll('[data-request]')) {
    const review = form.querySelector('[data-request-review]');
    const send = form.querySelector('[data-request-send]');
    const submit = form.querySelector('[type="submit"]');
    const fields = form.querySelector('[data-request-fields]');
    const detailsCount = form.querySelector('[data-details-count]');
    const countDetails = () => { if (detailsCount) detailsCount.textContent = form.elements.details.value.length + ' / 1000'; };
    form.elements.details.addEventListener('input',countDetails);
    countDetails();
    const requestedService = new URLSearchParams(location.search).get('service');
    if (requestedService) {
        const option = Array.from(form.elements.service.options).find((item) => item.dataset.serviceKey === requestedService);
        if (option) option.selected = true;
    }

    form.addEventListener('input', (event) => {
        if (event.target.setCustomValidity) event.target.setCustomValidity('');
        review.hidden = true;
        send.removeAttribute('href');
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const data = new FormData(form);
        for (const key of ['customer', 'area', 'details']) {
            if (!data.get(key).trim()) {
                form.elements[key].setCustomValidity('اكتب التفاصيل المطلوبة.');
                form.elements[key].reportValidity();
                return;
            }
        }

        const lines = [
            'السلام عليكم، أود طلب عرض سعر من ديكورات الرياض.',
            'الاسم: ' + data.get('customer').trim(),
            'الحي أو المنطقة: ' + data.get('area').trim(),
            'الخدمة: ' + data.get('service'),
            ...(data.get('site_state') ? ['حالة الموقع: '+data.get('site_state')] : []),
            ...(data.get('stage') ? ['مرحلة المشروع: '+data.get('stage')] : []),
            'التفاصيل والمقاسات: ' + data.get('details').trim(),
        ];
        const message = lines.join('\n');
        const whatsapp = new URL(form.dataset.whatsapp);
        whatsapp.searchParams.set('text', message);
        form.querySelector('[data-request-message]').textContent = message;
        send.href = whatsapp.href;
        review.hidden = false;
        send.focus();
    });
    form.querySelector('[data-request-edit]').addEventListener('click', () => {
        review.hidden = true;
        send.removeAttribute('href');
        form.elements.customer.focus();
    });
    fields.disabled = false;
    submit.hidden = false;
}

if (typeof HTMLDialogElement !== 'undefined') {
    const lightbox = document.createElement('dialog');
    lightbox.className = 'rd-dialog';
    lightbox.setAttribute('aria-label', 'عارض صور المعرض');
    lightbox.innerHTML = '<figure><img alt=""><figcaption></figcaption></figure>' +
        '<div class="rd-dialog-thumbs" aria-label="صور المعرض"></div><div class="rd-dialog-controls"><button class="rd-button rd-button-light" type="button" data-prev>السابق</button>' +
        '<button class="rd-button rd-button-light" type="button" data-close>إغلاق</button>' +
        '<button class="rd-button rd-button-light" type="button" data-next>التالي</button></div>';
    document.body.append(lightbox);
    const image = lightbox.querySelector('img');
    const caption = lightbox.querySelector('figcaption');
    const close = lightbox.querySelector('[data-close]');
    const thumbs = lightbox.querySelector('.rd-dialog-thumbs');
    let items = [];
    let current = 0;
    let source;

    const show = (index) => {
        current = (index + items.length) % items.length;
        image.src = items[current].href;
        image.alt = items[current].dataset.caption;
        for (const [i,button] of Array.from(thumbs.children).entries()) button.setAttribute('aria-pressed',String(i===current));
        caption.textContent = (current + 1).toLocaleString('ar-SA') + ' / ' + items.length.toLocaleString('ar-SA') + ' — ' + items[current].dataset.caption;
    };

    for (const gallery of document.querySelectorAll('[data-gallery]')) {
        gallery.addEventListener('click', (event) => {
            const link = event.target.closest('[data-gallery-image]');
            if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            source = link;
            items = Array.from(gallery.querySelectorAll('[data-gallery-image]'));
            thumbs.replaceChildren(...items.map((item,index)=>{const b=document.createElement('button');b.type='button';b.setAttribute('aria-label',item.dataset.caption);const thumb=document.createElement('img');thumb.src=item.querySelector('img').currentSrc || item.href;thumb.alt='';b.append(thumb);b.addEventListener('click',()=>show(index));return b;}));
            show(items.indexOf(link));
            lightbox.showModal();
            close.focus();
        });
    }
    close.addEventListener('click', () => lightbox.close());
    lightbox.querySelector('[data-prev]').addEventListener('click', () => show(current - 1));
    lightbox.querySelector('[data-next]').addEventListener('click', () => show(current + 1));
    lightbox.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            show(current + (event.key === 'ArrowLeft' ? 1 : -1));
        }
    });
    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) lightbox.close();
    });
    lightbox.addEventListener('close', () => source?.focus());
}

for (const container of document.querySelectorAll('[data-film]')) {
    const video = container.querySelector('video');
    const toggle = container.querySelector('[data-film-toggle]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let manuallyControlled = false;

    toggle.hidden = false;
    video.addEventListener('play', () => { toggle.textContent = 'أوقف المشهد'; });
    video.addEventListener('pause', () => { toggle.textContent = 'شغّل المشهد'; });
    toggle.addEventListener('click', () => {
        manuallyControlled = true;
        if (video.paused) video.play().catch(() => {});
        else video.pause();
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(([entry]) => {
            if (manuallyControlled || reducedMotion.matches) return;
            if (entry.isIntersecting) video.play().catch(() => {});
            else video.pause();
        }, { threshold: 0.4 });
        observer.observe(video);
    }
    reducedMotion.addEventListener('change', ({ matches }) => {
        if (matches && !manuallyControlled) video.pause();
    });
}

for (const rail of document.querySelectorAll('[data-rail]')) {
    const strip = rail.querySelector('[data-rail-track]');
    const controls = rail.querySelector('.rd-rail-controls');
    controls.hidden = false;
    controls.addEventListener('click', (event) => {
        const button = event.target.closest('[data-rail-step]');
        if (!button) return;
        const width = strip.firstElementChild.getBoundingClientRect().width;
        strip.scrollBy({
            left: -Number(button.dataset.railStep) * (width + 18),
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
        });
    });
}

const guideList = document.querySelector('[data-guide-list]');
const guidePages = document.querySelector('[data-guide-pagination]');
const guideStatus = document.querySelector('[data-guide-status]');

if (guideList && guidePages && guideStatus) {
    let loading = false;
    guidePages.addEventListener('click', async (event) => {
        const more = event.target.closest('[data-guide-more]');
        if (!more || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        if (loading) return;

        loading = true;
        more.setAttribute('aria-disabled', 'true');
        guideStatus.textContent = 'جارٍ تحميل الأدلة.';
        try {
            const response = await fetch(more.href, { credentials: 'same-origin' });
            if (!response.ok) throw new Error('Guide request failed');
            const nextPage = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextItems = Array.from(nextPage.querySelectorAll('[data-guide-list] article'));
            if (!nextItems.length) throw new Error('Guide page is empty');
            for (const item of nextItems) {
                for (const element of item.querySelectorAll('[href],[src],[srcset]')) {
                    for (const attr of ['href','src']) {
                        const value = element.getAttribute(attr);
                        if (value) element.setAttribute(attr, new URL(value, response.url).href);
                    }
                    const srcset = element.getAttribute('srcset');
                    if (srcset) element.setAttribute('srcset', srcset.split(',').map(candidate => {
                        const [url, size] = candidate.trim().split(/\s+/, 2);
                        return [new URL(url, response.url).href, size].filter(Boolean).join(' ');
                    }).join(', '));
                }
            }
            guideList.append(...nextItems);
            const nextLink = nextPage.querySelector('[data-guide-more]');
            if (nextLink) {
                more.href = new URL(nextLink.getAttribute('href'), response.url).href;
            } else {
                more.remove();
                nextItems[0].querySelector('h3 a')?.focus();
            }
            guideStatus.textContent = 'تم إضافة مقالات إلى القائمة.';
        } catch {
            window.location.assign(more.href);
        } finally {
            loading = false;
            more.removeAttribute('aria-disabled');
        }
    });
}

/* Share a stable design anchor, with a copy fallback. */
for (const button of document.querySelectorAll('[data-share-design]')) {
    button.hidden = false;
    button.addEventListener('click', async () => {
        const figure = button.closest('.rd-photo');
        const status = figure.querySelector('.rd-share-status');
        const url = new URL(location.href);
        url.hash = figure.id;
        const title = figure.querySelector('.rd-photo-caption').textContent.trim();
        try {
            if (navigator.share) {
                await navigator.share({title,url:url.href});
                status.textContent = 'تمت المشاركة.';
            } else if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(url.href);
                status.textContent = 'تم نسخ الرابط.';
            } else {
                window.prompt('انسخ رابط التصميم',url.href);
            }
        } catch (error) {
            if (error.name !== 'AbortError') window.prompt('انسخ رابط التصميم',url.href);
        }
    });
}

/* Mobile quote shortcut: visible only when the actual quote form is off-screen. */
const quoteShortcut = document.querySelector('[data-mobile-quote]');
const quoteTarget = document.querySelector('#request');
const quickTarget = document.querySelector('#quick-quote');
if (quoteShortcut && quoteTarget && 'IntersectionObserver' in window) {
 const visibility = new Map();
 const observer = new IntersectionObserver(entries=>{for(const entry of entries)visibility.set(entry.target,entry.isIntersecting);quoteShortcut.hidden=[...visibility.values()].some(Boolean);},{threshold:0.05});
 observer.observe(quoteTarget);
 if(quickTarget)observer.observe(quickTarget);
}

for(const finder of document.querySelectorAll('[data-finder]')) {const select=finder.querySelector('[data-finder-space]');const update=()=>{let count=0;for(const card of finder.querySelectorAll('[data-finder-card]')){card.hidden=select.value!=='all'&&!card.dataset.finderCard.split(' ').includes(select.value);if(!card.hidden)count++;}finder.querySelector('[data-finder-status]').textContent='الخدمات المناسبة: '+count;};select.addEventListener('change',update);update();}
for(const form of document.querySelectorAll('[data-estimate]')){const result=form.querySelector('[data-estimate-result]');const area=()=>Number(form.elements.namedItem('length').value)*Number(form.elements.namedItem('width').value)*Number(form.elements.namedItem('quantity').value);form.addEventListener('input',()=>{result.textContent=form.elements.namedItem('length').validity.valid&&form.elements.namedItem('width').validity.valid&&form.elements.namedItem('quantity').validity.valid&&area()>0?'المساحة التقريبية: '+area().toLocaleString('ar-SA',{maximumFractionDigits:2})+' م²':'أدخل مقاسات صحيحة.';});form.addEventListener('submit',e=>{e.preventDefault();const url=new URL(form.action);url.searchParams.set('service',form.elements.service.value);url.searchParams.set('dimensions',form.elements.namedItem('length').value+' × '+form.elements.namedItem('width').value+' م، العدد '+form.elements.namedItem('quantity').value+'، المساحة التقريبية '+area().toFixed(2)+' م²');location.assign(url.href);});}
for(const form of document.querySelectorAll('[data-request]')){const dimensions=new URLSearchParams(location.search).get('dimensions');if(dimensions&&!form.elements.details.value){form.elements.details.value=dimensions.slice(0,200);form.elements.details.dispatchEvent(new Event('input',{bubbles:true}));}}

/* V1.7.0: filter captions without changing the lightbox source order. */
for(const tools of document.querySelectorAll('[data-gallery-tools]')){const gallery=tools.parentElement.querySelector('[data-gallery]');if(!gallery)continue;const input=tools.querySelector('[data-gallery-search]');const update=()=>{let count=0;for(const item of gallery.querySelectorAll('.rd-photo')){item.hidden=!normalizeServiceSearch(item.querySelector('.rd-photo-caption').textContent).includes(normalizeServiceSearch(input.value));if(!item.hidden)count++;}tools.querySelector('[data-gallery-results]').textContent='الصور المطابقة: '+count;};input.addEventListener('input',update);tools.querySelector('[data-gallery-clear]').addEventListener('click',()=>{input.value='';update();input.focus();});tools.hidden=false;update();}
for(const form of document.querySelectorAll('[data-estimate]')){const service=new URLSearchParams(location.search).get('service');if(service&&Array.from(form.elements.service.options).some(o=>o.value===service))form.elements.service.value=service;}

/* V2: carry the selected gallery reference into the editable quote. */
for(const form of document.querySelectorAll('[data-request]')){const design=new URLSearchParams(location.search).get('design');if(design&&/^[a-z0-9-]+\.(jpg|webp)$/i.test(design)&&!form.elements.details.value){form.elements.details.value='مرجع التصميم: '+design;form.elements.details.dispatchEvent(new Event('input',{bubbles:true}));}}
