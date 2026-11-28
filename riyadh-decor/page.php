<?php
get_header();
while (have_posts()) :
    the_post();
    $services = rd_services();
    $slug = get_post_meta(get_the_ID(), '_rd_specialty', true);
    $kind = get_post_meta(get_the_ID(), '_rd_kind', true);
    $section = get_post_field('post_name', get_the_ID());
    $service = $services[$slug] ?? null;
?>
<main id="content">
<?php if ($service && $kind === 'gallery-item'): ?>
    <?php rd_page_top(get_the_title(), 'أفكار تصميمية توضيحية تساعدك على مقارنة الخامات والإضاءة والتفاصيل.', 'gallery', 'المعرض'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_actions($slug, true); ?>
        <?php rd_image_gallery($service['portfolio'], $slug); ?>
        <?php if (strlen(trim(get_the_content()))): ?><div class="rd-prose"><?php the_content(); ?></div><?php endif; ?>
        <?php rd_related_services($slug, 'gallery'); ?>
        <?php rd_end_cta('أعجبتك إحدى الأفكار؟', $slug); ?>
    </div>
<?php elseif ($service): ?>
    <?php rd_page_top(get_the_title(), $service['intro'], 'services', 'الخدمات'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_actions($slug); ?>
        <div class="rd-service-layout rd-detail-layout">
            <div>
                <h2 class="rd-detail-heading"><span aria-hidden="true">01</span> خيارات التصميم والتشطيب</h2>
                <ul><?php foreach ($service['choices'] as $choice): ?><li><?php echo esc_html($choice); ?></li><?php endforeach; ?></ul>
                <h2 class="rd-detail-heading"><span aria-hidden="true">02</span> ماذا تجهّز قبل طلب العرض؟</h2>
                <ul><?php foreach ($service['checks'] as $check): ?><li><?php echo esc_html($check); ?></li><?php endforeach; ?></ul>
                <h2 class="rd-detail-heading"><span aria-hidden="true">03</span> عوامل تحديد التكلفة</h2>
                <p><?php echo esc_html($service['price']); ?></p>
            </div>
            <div class="rd-feature-image">
                <?php if (has_post_thumbnail()) { the_post_thumbnail('large', ['loading' => 'eager']); } else { rd_image($service['image'], ['loading' => 'eager', 'alt' => $service['alt']]); } ?>
                <p class="rd-image-note">صورة توضيحية للخامة أو التصميم؛ التنفيذ حسب المقاسات والعينات المعتمدة.</p>
            </div>
        </div>
        <?php if (strlen(trim(get_the_content()))): ?><div class="rd-prose"><?php the_content(); ?></div><?php endif; ?>
        <?php if (!empty($service['portfolio'])): ?>
            <section class="rd-service-portfolio" aria-label="<?php echo esc_attr($service['gallery_title']); ?>">
                <?php rd_heading($service['gallery_title'], 'تصفّح النماذج وافتح أي صورة لمعاينة تفاصيلها.'); ?>
                <?php rd_image_gallery($service['portfolio'], $slug); ?>
            </section>
        <?php endif; ?>
        <h2>أسئلة شائعة</h2>
        <?php rd_faq($service['faq']); ?>
        <?php rd_end_cta('ابدأ بطلب تقييم لخدمتك', $slug); ?>
        <?php rd_related_services($slug); ?>
    </div>
<?php elseif ($section === 'services'): ?>
    <?php rd_page_top('الخدمات', 'خدمات الديكور والتشطيبات الداخلية والخارجية في الرياض.'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_cards($services); ?>
    </div>
<?php elseif ($section === 'gallery'): ?>
    <?php rd_page_top('معرض التصاميم', 'اختر الخدمة لتصفح صورها ومعاينة تفاصيلها.'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_cards($services, 'gallery'); ?>
        <?php rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'guides'): ?>
    <?php rd_page_top('الأدلة والمقالات', 'اقرأ حسب نوع مشروعك، من تجهيز السطح واختيار الخامة إلى مراجعة التركيب.'); ?>
    <div class="rd-shell rd-section">
        <div class="rd-pills"><?php foreach ($services as $key => $item): ?>
            <?php $category_url = rd_category_link($key, false); ?>
            <?php if ($category_url): ?>
                <a href="<?php echo esc_url($category_url); ?>"><?php echo esc_html($item['short']); ?></a>
            <?php endif; ?>
        <?php endforeach; ?></div>
        <?php get_search_form(); ?>
        <?php $number = max(1, absint($_GET['guide_page'] ?? 1)); $query = rd_guides(6, '', $number); ?>
        <?php if ($query->have_posts()): ?>
            <div class="rd-cards" data-guide-list><?php rd_guide_cards($query); ?></div>
        <?php else: ?>
            <p>ما فيه مقالات في هذه الصفحة.</p><a href="<?php echo esc_url(rd_url('guides')); ?>">ارجع إلى أول صفحة</a>
        <?php endif; ?>
        <div class="rd-load" data-guide-pagination>
            <?php if ($number > 1): ?><a class="rd-button rd-button-line" href="<?php echo esc_url(add_query_arg('guide_page', $number - 1, rd_url('guides'))); ?>">الصفحة السابقة</a><?php endif; ?>
            <?php if ($number < $query->max_num_pages): ?><a class="rd-button rd-button-green" data-guide-more href="<?php echo esc_url(add_query_arg('guide_page', $number + 1, rd_url('guides'))); ?>">عرض المزيد من المقالات</a><?php endif; ?>
        </div>
        <p class="screen-reader-text" data-guide-status aria-live="polite"></p>
    </div>
<?php elseif ($section === 'contact'): ?>
    <?php rd_page_top('تواصل مع ديكورات الرياض', 'صف لنا موقع المشروع والمساحة والخامات المطلوبة لتبدأ من طلب واضح.'); ?>
    <section class="rd-shell rd-section rd-request-section rd-contact-layout">
        <div class="rd-contact-info"><h2>اختر طريقة التواصل</h2><p>نطاق الخدمة: <?php echo esc_html(get_theme_mod('rd_location', 'الرياض، السعودية')); ?></p>
            <a class="rd-contact-method" href="<?php echo esc_url(rd_tel()); ?>"><strong>اتصال مباشر</strong><bdi><?php echo esc_html(rd_phone()); ?></bdi></a>
            <a class="rd-contact-method" href="<?php echo esc_url(rd_wa()); ?>" target="_blank" rel="noopener"><strong>محادثة واتساب</strong><span>أرسل صور المساحة والمقاسات ←</span></a>
            <div class="rd-contact-checklist"><h3>جهّز هذه المعلومات قبل الطلب</h3><ul><li>نوع الخدمة والحي.</li><li>المقاسات التقريبية وصور الموقع.</li><li>الخامات أو التصميم المطلوب.</li></ul></div>
            <p>النموذج يجهّز رسالة واتساب؛ راجعها قبل إرسالها.</p>
        </div>
        <?php rd_request_form(sanitize_key(wp_unslash($_GET['service'] ?? ''))); ?>
    </section>
<?php else: ?>
    <?php rd_page_top(get_the_title()); ?>
    <div class="rd-shell rd-section rd-prose">
        <?php if ($section === 'about'): ?>
            <h2>خيارات تشطيب للمنازل والفلل</h2>
            <p>يقدم ديكورات الرياض خدمات الديكور والتشطيبات الداخلية والخارجية للمنازل والفلل. نراجع خيارات الخامات والتقسيمات بحسب المساحة ونطاق المشروع.</p>
            <h2>ابدأ من وصف المساحة</h2><p>اذكر مكان المشروع وما الذي تريد تغييره. صور الموقع والمقاسات تساعد في تحديد نطاق العمل ومناقشة الخامات والموعد.</p>
            <h2>عن صور المعرض</h2><p>يحتوي الموقع على صور توضيحية للأفكار والتشطيبات. تطبّق الخامات والتصاميم حسب قياس المكان والعينات المعتمدة.</p>
        <?php elseif ($section === 'project-planning'): ?>
            <h2>قبل المعاينة</h2><p>صوّر الأسطح في إضاءة واضحة، وقس عرض الجدران وارتفاعها، وحدد فتحات الكهرباء والمكيفات والأثاث القائم.</p>
            <h2>قبل اعتماد العرض</h2><p>اطلب تحديد المساحات وطريقة التجهيز والخامات الداخلة في العرض والمدة المتوقعة وطريقة مراجعة التنفيذ.</p>
            <h2>قبل الاستلام</h2><p>راجع العينات المتفق عليها والمظهر في الإضاءة الفعلية. اختبر المفاتيح والأبواب عندما يكون العمل متصلًا بها، وسجّل ملاحظاتك.</p>
        <?php elseif ($section === 'privacy-policy'): ?>
            <h2>بيانات الطلب</h2><p>يجهّز النموذج نص الرسالة داخل المتصفح. تنتقل البيانات إلى واتساب عند اختيار فتح المحادثة، وتُرسل عندما تضغط زر الإرسال في واتساب.</p>
            <h2>صور ومعلومات المشروع</h2><p>أرسل فقط الصور والمعلومات التي تحتاج لمشاركتها لمناقشة العرض. تخضع المحادثة لاستخدام واتساب وإعداداته.</p>
            <h2>سؤال عن البيانات</h2><p>تواصل على <a href="<?php echo esc_url(rd_tel()); ?>"><bdi><?php echo esc_html(rd_phone()); ?></bdi></a> للاستفسارات المتعلقة ببيانات طلبك.</p>
        <?php endif; ?>
        <?php the_content(); ?>
        <?php if ($section !== 'privacy-policy'): rd_end_cta(); endif; ?>
    </div>
<?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>
