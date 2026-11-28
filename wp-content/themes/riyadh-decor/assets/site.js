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
        '<div class="rd-dialog-controls"><button class="rd-button rd-button-light" type="button" data-prev>السابق</button>' +
        '<button class="rd-button rd-button-light" type="button" data-close>إغلاق</button>' +
        '<button class="rd-button rd-button-light" type="button" data-next>التالي</button></div>';
    document.body.append(lightbox);
    const image = lightbox.querySelector('img');
    const caption = lightbox.querySelector('figcaption');
    const close = lightbox.querySelector('[data-close]');
    let items = [];
    let current = 0;
    let source;

    const show = (index) => {
        current = (index + items.length) % items.length;
        image.src = items[current].href;
        image.alt = items[current].dataset.caption;
        caption.textContent = (current + 1).toLocaleString('ar-SA') + ' / ' + items.length.toLocaleString('ar-SA') + ' — ' + items[current].dataset.caption;
    };

    for (const gallery of document.querySelectorAll('[data-gallery]')) {
        gallery.addEventListener('click', (event) => {
            const link = event.target.closest('[data-gallery-image]');
            if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            source = link;
            items = Array.from(gallery.querySelectorAll('[data-gallery-image]'));
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
