<?php
defined('ABSPATH') || exit;

function rd_image($key, $attributes = []) {
    $media = rd_media();
    if (!isset($media[$key])) {
        return;
    }
    $attributes += ['alt' => $media[$key], 'loading' => 'lazy', 'decoding' => 'async'];
    $width = $key === 'majlis' ? 1440 : 504;
    $height = $key === 'majlis' ? 960 : 504;
    printf('<img src="%s" width="%d" height="%d"', esc_url(rd_asset($key . '.webp')), $width, $height);
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

function rd_service_cards($services) {
    ?>
    <div class="rd-cards">
        <?php foreach ($services as $slug => $service): ?>
            <article class="rd-card">
                <a class="rd-card-media" tabindex="-1" aria-hidden="true" href="<?php echo esc_url(rd_url('services/' . $slug)); ?>"><?php rd_image($service['image'], ['alt' => '']); ?></a>
                <div class="rd-card-body">
                    <h3><a href="<?php echo esc_url(rd_url('services/' . $slug)); ?>"><?php echo esc_html($service['name']); ?></a></h3>
                    <p><?php echo esc_html($service['intro']); ?></p>
                    <a class="rd-text-link" href="<?php echo esc_url(rd_url('services/' . $slug)); ?>">تفاصيل الخدمة<span class="screen-reader-text">: <?php echo esc_html($service['short']); ?></span></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}

function rd_image_gallery($keys, $specialty = '') {
    $service = rd_services()[$specialty] ?? null;
    $media = rd_media();
    ?>
    <div class="rd-photo-grid" data-gallery>
        <?php foreach ($keys as $key): ?>
            <figure class="rd-photo">
                <a href="<?php echo esc_url(rd_asset($key . '.webp')); ?>" data-gallery-image data-caption="<?php echo esc_attr($media[$key]); ?>"><?php rd_image($key); ?></a>
                <figcaption>
                    <div><?php echo esc_html($media[$key]); ?></div>
                    <a class="rd-text-link" href="<?php echo esc_url(rd_wa('السلام عليكم، عندي استفسار عن تصميم: ' . $media[$key] . ($service ? ' — ' . $service['name'] : ''))); ?>" rel="noopener" target="_blank">استفسر عن الفكرة</a>
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
    <form class="rd-form" method="post" data-request data-whatsapp="<?php echo esc_url(rd_wa()); ?>">
        <fieldset data-request-fields disabled>
        <div class="rd-form-row">
            <label>الاسم<input required name="customer" autocomplete="name" maxlength="90" placeholder="اسمك"></label>
            <label>الحي أو المنطقة<input required name="area" autocomplete="address-level3" maxlength="100" placeholder="مثلاً: حي الياسمين"></label>
        </div>
        <label>نوع الخدمة
            <select required name="service">
                <option value="">اختر الخدمة</option>
                <?php foreach (rd_services() as $slug => $service): ?>
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
        <a class="rd-button rd-button-solid" href="<?php echo esc_url($url); ?>">جهّز طلبك</a>
    </aside>
    <?php
}
