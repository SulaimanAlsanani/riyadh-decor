<?php
defined('ABSPATH') || exit;

function rd_media_filename($key) {
    return str_ends_with($key, '.jpg') ? $key : $key . '.webp';
}

function rd_image($key, $attributes = []) {
    $media = rd_media();
    $photo = rd_gallery_media()[$key] ?? null;
    if (!isset($media[$key]) && !$photo) {
        return;
    }
    $attributes += ['alt' => $photo[0] ?? $media[$key], 'loading' => 'lazy', 'decoding' => 'async'];
    $dimensions = ['majlis' => [1440, 960], 'painting-service' => [864, 576]];
    [$width, $height] = $photo ? [$photo[1], $photo[2]] : ($dimensions[$key] ?? [504, 504]);
    printf('<img src="%s" width="%d" height="%d"', esc_url(rd_asset(rd_media_filename($key))), $width, $height);
    if ($key === 'majlis') {
        printf(' srcset="%s 768w, %s 1440w" sizes="%s"', esc_url(rd_asset('majlis-sm.webp')), esc_url(rd_asset('majlis.webp')), esc_attr($attributes['sizes'] ?? '(max-width: 740px) 95vw, 50vw'));
    }
    foreach (['alt','loading','decoding','fetchpriority','class'] as $attribute) {
        if (isset($attributes[$attribute])) {
            printf(' %s="%s"', $attribute, esc_attr($attributes[$attribute]));
        }
    }
    echo '>';
}

function rd_breadcrumb($label, $parent = '', $parent_label = '') {
    ?>
    <nav class="rd-crumb" aria-label="مسار الصفحة">
        <a href="<?php echo esc_url(rd_url()); ?>">الرئيسية</a>
        <?php if ($parent): ?>
            <span aria-hidden="true">/</span><a href="<?php echo esc_url(rd_url($parent)); ?>"><?php echo esc_html($parent_label); ?></a>
        <?php endif; ?>
        <span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html($label); ?></span>
    </nav>
    <?php
}

function rd_page_top($title, $intro = '', $parent = '', $parent_label = '') {
    ?>
    <section class="rd-page-top">
        <div class="rd-shell">
            <?php rd_breadcrumb($title, $parent, $parent_label); ?>
            <h1><?php echo esc_html($title); ?></h1>
            <?php if ($intro): ?><p><?php echo esc_html($intro); ?></p><?php endif; ?>
        </div>
    </section>
    <?php
}

function rd_heading($title, $intro = '', $link = '', $label = '') {
    ?>
    <div class="rd-section-head">
        <div>
            <h2><?php echo esc_html($title); ?></h2>
            <?php if ($intro): ?><p><?php echo esc_html($intro); ?></p><?php endif; ?>
        </div>
        <?php if ($link): ?><a class="rd-button rd-button-line" href="<?php echo esc_url($link); ?>"><?php echo esc_html($label); ?></a><?php endif; ?>
    </div>
    <?php
}

function rd_service_cards($services, $section = 'services') {
    $is_gallery = $section === 'gallery';
    $link_label = $is_gallery ? 'عرض الصور' : 'تفاصيل الخدمة';
    $group_labels = [];
    $service_groups = [];
    foreach (rd_service_groups() as $group => [$label, $slugs]) {
        $matching = array_intersect($slugs, array_keys($services));
        if (!$matching) continue;
        $group_labels[$group] = $label;
        foreach ($matching as $slug) {
            $service_groups[$slug] = $group;
        }
    }
    $grid_id = wp_unique_id('rd-service-grid-');
    $total = count($services);
    ?>
    <div data-service-list>
    <?php if ($total > 1): ?>
        <div class="rd-service-filters" data-service-filters hidden>
            <label class="rd-service-search">
                <span><?php echo $is_gallery ? 'ابحث في المعرض' : 'ابحث عن خدمة'; ?></span>
                <input type="search" data-service-search aria-controls="<?php echo esc_attr($grid_id); ?>" placeholder="مثل باركيه أو مرايا">
            </label>
            <button class="rd-filter-clear" type="button" data-service-clear hidden>مسح البحث والفلاتر</button>
            <div class="rd-service-groups" role="group" aria-label="تصفية حسب نوع العمل">
                <?php foreach (['all' => 'الكل'] + $group_labels as $group => $label): ?>
                    <button type="button" data-service-group="<?php echo esc_attr($group); ?>" aria-pressed="<?php echo $group === 'all' ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($grid_id); ?>"><?php echo esc_html($label); ?></button>
                <?php endforeach; ?>
            </div>
            <p class="rd-service-count" role="status" data-service-status><?php echo esc_html(sprintf('عدد النتائج: %s', number_format_i18n($total))); ?></p>
        </div>
    <?php endif; ?>
    <div id="<?php echo esc_attr($grid_id); ?>" class="rd-cards rd-service-card-grid<?php echo $total === 1 ? ' rd-service-card-single' : ''; ?>">
        <?php foreach ($services as $slug => $service): ?>
            <?php $url = rd_url($section . '/' . $slug); ?>
            <article class="rd-card" data-service-card="<?php echo esc_attr($service_groups[$slug] ?? ''); ?>" data-service-name="<?php echo esc_attr($service['name'] . ' ' . $service['short']); ?>">
                <a class="rd-card-media" tabindex="-1" aria-hidden="true" href="<?php echo esc_url($url); ?>"><?php rd_image($service['image'], ['alt' => '']); ?></a>
                <div class="rd-card-body">
                    <?php if (isset($service_groups[$slug], $group_labels[$service_groups[$slug]])): ?>
                        <span class="rd-category"><?php echo esc_html($group_labels[$service_groups[$slug]]); ?></span>
                    <?php endif; ?>
                    <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($service['card_title'] ?? $service['name']); ?></a></h3>
                    <p><?php echo esc_html($service['intro']); ?></p>
                    <div class="rd-card-actions">
                        <a class="rd-text-link" href="<?php echo esc_url($url); ?>"><?php echo esc_html($link_label); ?> <span aria-hidden="true">←</span><span class="screen-reader-text">: <?php echo esc_html($service['short']); ?></span></a>
                        <?php if (!$is_gallery): ?><a class="rd-card-quote" href="<?php echo esc_url(add_query_arg('service', $slug, rd_url('contact'))); ?>">طلب سعر<span class="screen-reader-text">: <?php echo esc_html($service['short']); ?></span></a><?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($total > 1): ?>
        <div class="rd-service-empty" data-service-empty hidden>
            <p>لا توجد نتائج مطابقة.</p>
            <button class="rd-button rd-button-line" type="button" data-service-reset>عرض الكل</button>
        </div>
    <?php endif; ?>
    </div>
    <?php
}

function rd_image_gallery($keys, $specialty = '') {
    $service = rd_services()[$specialty] ?? null;
    $media = rd_media() + array_map(static fn($photo) => $photo[0], rd_gallery_media());
    ?>
    <p class="rd-gallery-count"><?php echo esc_html(number_format_i18n(count($keys))); ?> صورة · اضغط على أي صورة لتكبيرها</p>
    <div class="rd-photo-grid" data-gallery>
        <?php foreach ($keys as $key): ?>
            <figure class="rd-photo" id="<?php echo esc_attr('design-' . sanitize_title($key)); ?>">
                <a href="<?php echo esc_url(rd_asset(rd_media_filename($key))); ?>" data-gallery-image data-caption="<?php echo esc_attr($media[$key]); ?>"><?php rd_image($key); ?><span class="rd-photo-zoom" aria-hidden="true"><?php rd_icon('zoom'); ?></span></a>
                <figcaption>
                    <div class="rd-photo-caption"><?php echo esc_html($media[$key]); ?></div>
                    <div class="rd-photo-actions">
                    <a class="rd-text-link" href="<?php echo esc_url(rd_wa('السلام عليكم، عندي استفسار عن تصميم: ' . $media[$key] . ($service ? ' — ' . $service['name'] : '') . ' (' . $key . ')')); ?>" rel="noopener" target="_blank">استفسر عن الفكرة <span aria-hidden="true">←</span></a>
                    <button type="button" class="rd-share-design" data-share-design hidden aria-label="<?php echo esc_attr('مشاركة ' . $media[$key]); ?>"><?php rd_icon('share'); ?><span>مشاركة</span></button>
                    </div><span class="rd-share-status" role="status"></span>
                </figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
    <?php
}

function rd_guide_cards($query) {
    while ($query->have_posts()) {
        $query->the_post();
        $service = rd_services()[rd_service_for_post(get_the_ID())] ?? null;
        ?>
        <article class="rd-card rd-article-card">
            <a class="rd-card-media" tabindex="-1" aria-hidden="true" href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large', ['loading' => 'lazy', 'alt' => '']); } else { rd_image($service['image'] ?? 'majlis', ['alt' => '']); } ?>
            </a>
            <div class="rd-card-body">
                <?php if ($service): ?><span class="rd-category"><?php echo esc_html($service['short']); ?></span><?php endif; ?>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
            </div>
        </article>
        <?php
    }
    wp_reset_postdata();
}

function rd_faq($items) {
    ?>
    <div class="rd-faq">
        <?php foreach ($items as $question => $answer): ?>
            <details><summary><?php echo esc_html($question); ?></summary><p><?php echo esc_html($answer); ?></p></details>
        <?php endforeach; ?>
    </div>
    <?php
}

function rd_request_form($selected = '') {
    ?>
    <?php $services = rd_services(); ?>
    <form class="rd-form" method="post" data-request data-whatsapp="<?php echo esc_url(rd_wa()); ?>">
        <fieldset data-request-fields disabled>
        <div class="rd-form-row">
            <label>الاسم<input required name="customer" autocomplete="name" maxlength="90" placeholder="اسمك"></label>
            <label>الحي أو المنطقة<input required name="area" autocomplete="address-level3" maxlength="100" placeholder="مثلاً: حي الياسمين"></label>
        </div>
        <label>نوع الخدمة
            <select required name="service">
                <?php if (count($services) > 1): ?><option value="">اختر الخدمة</option><?php endif; ?>
                <?php foreach ($services as $slug => $service): ?>
                    <option value="<?php echo esc_attr($service['name']); ?>" data-service-key="<?php echo esc_attr($slug); ?>" <?php selected($selected, $slug); ?>><?php echo esc_html($service['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>تفاصيل المشروع والمقاسات
            <textarea name="details" required rows="4" maxlength="1000" placeholder="اذكر المساحة، المقاسات التقريبية، وحالة السطح أو الخامة المطلوبة"></textarea>
        </label>
        </fieldset>
        <div class="rd-form-actions">
            <button class="rd-button rd-button-solid" type="submit" hidden>جهّز رسالة واتساب</button>
            <a href="<?php echo esc_url(rd_tel()); ?>">اتصال مباشر <bdi><?php echo esc_html(rd_phone()); ?></bdi></a>
            <noscript><a href="<?php echo esc_url(rd_wa()); ?>" rel="noopener" target="_blank">افتح واتساب لإرسال التفاصيل</a></noscript>
        </div>
        <div class="rd-review" hidden data-request-review>
            <p>راجع نص الطلب ثم افتح واتساب لإرساله.</p>
            <p class="rd-message" data-request-message></p>
            <a class="rd-button rd-button-green" data-request-send rel="noopener" target="_blank">فتح واتساب</a>
            <button class="rd-button rd-button-line" type="button" data-request-edit>تعديل البيانات</button>
        </div>
    </form>
    <?php
}

function rd_end_cta($title = 'تبي تعرف وش يناسب مساحتك؟', $service = '') {
    $url = $service ? add_query_arg('service', $service, rd_url('contact')) : rd_url('contact');
    ?>
    <aside class="rd-cta">
        <div><h2><?php echo esc_html($title); ?></h2><p>شارك الحي والمقاسات وصور المساحة عشان نناقش نطاق العمل.</p></div>
        <a class="rd-button rd-button-solid" href="<?php echo esc_url($url); ?>">اطلب عرض سعر</a>
    </aside>
    <?php
}

function rd_service_actions($slug, $gallery = false) {
    $service = rd_services()[$slug] ?? null;
    if (!$service) return;
    $quote = add_query_arg('service', $slug, rd_url('contact'));
    ?>
    <div class="rd-detail-actions">
        <div><strong><?php echo esc_html($service['short']); ?></strong><small><?php echo esc_html(number_format_i18n(count($service['portfolio'] ?? []))); ?> نموذج تصميم</small></div>
        <div class="rd-actions">
            <a class="rd-button rd-button-solid" href="<?php echo esc_url($quote); ?>">اطلب عرض سعر</a>
            <a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url(($gallery ? 'services/' : 'gallery/') . $slug)); ?>"><?php echo $gallery ? 'تفاصيل الخدمة' : 'معرض التصاميم'; ?></a>
        </div>
    </div>
    <?php
}

function rd_related_services($slug, $section = 'services') {
    $services = rd_services();
    $related = [];
    foreach (rd_service_groups() as [$label, $slugs]) {
        if (!in_array($slug, $slugs, true)) continue;
        foreach ($slugs as $key) {
            if ($key !== $slug && isset($services[$key])) $related[$key] = $services[$key];
        }
    }
    foreach ($services as $key => $service) {
        if ($key !== $slug && count($related) < 3) $related[$key] = $service;
    }
    $related = array_slice($related, 0, 3, true);
    if (!$related) return;
    ?>
    <section class="rd-related-section" aria-label="خدمات مرتبطة">
        <?php rd_heading('خدمات قد تناسب مشروعك', '', rd_url($section), 'عرض الكل'); ?>
        <div class="rd-related-grid">
        <?php foreach ($related as $key => $item): ?>
            <a class="rd-related-tile" href="<?php echo esc_url(rd_url($section . '/' . $key)); ?>">
                <?php rd_image($item['image'], ['alt' => '']); ?>
                <span><?php echo esc_html($item['short']); ?> <span aria-hidden="true">←</span></span>
            </a>
        <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/** Shared inline icon set; all names are controlled locally. */
function rd_icon($name) {
    $paths = [
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2A18 18 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 1.8l-1.2 1.2a14 14 0 0 0 4.7 4.7l1.2-1.2a2 2 0 0 1 1.8-.6l2.8.5a2 2 0 0 1 1.7 2z"/>',
        'message' => '<path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3H13a8.5 8.5 0 0 1 8 8z"/>',
        'zoom' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5M10.5 7v7M7 10.5h7"/>',
        'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.5 6.8-4M8.6 13.5l6.8 4"/>',
    ];
    if (!isset($paths[$name])) return;
    echo '<svg class="rd-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}
