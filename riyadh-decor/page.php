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
        <?php rd_image_gallery($service['portfolio'], $slug); ?>
        <?php if (strlen(trim(get_the_content()))): ?><div class="rd-prose"><?php the_content(); ?></div><?php endif; ?>
        <div class="rd-related"><a href="<?php echo esc_url(rd_url('services/' . $slug)); ?>">تفاصيل الخدمة</a><a href="<?php echo esc_url(rd_category_link($slug)); ?>">أدلة التخصص</a></div>
        <?php rd_end_cta('أعجبتك إحدى الأفكار؟', $slug); ?>
    </div>
<?php elseif ($service): ?>
    <?php rd_page_top(get_the_title(), $service['intro'], 'services', 'الخدمات'); ?>
    <div class="rd-shell rd-section">
        <div class="rd-service-layout">
            <div>
                <h2>خيارات التصميم والتشطيب</h2>
                <?php if ($kind === 'paint-variant'):
                    $paint_variants = [
                        'interior-painting' => 'تبدأ الدهانات الداخلية باختيار تشطيب ملائم للغرفة ومراجعة الإضاءة وأماكن حماية الأثاث.',
                        'exterior-painting' => 'تتطلب أعمال الواجهات معاينة ارتفاع السطح وحالته وتأثره بالطقس قبل تحديد نظام الطلاء.',
                        'repainting' => 'في التجديد، تُفحص الطبقات السابقة والرطوبة والتقشر لتحديد ما يحتاج إزالة أو معالجة.',
                    ];
                    ?><p><?php echo esc_html($paint_variants[$section] ?? ''); ?></p><?php endif; ?>
                <ul><?php foreach ($service['choices'] as $choice): ?><li><?php echo esc_html($choice); ?></li><?php endforeach; ?></ul>
                <h2>ماذا تجهّز قبل طلب العرض؟</h2>
                <ul><?php foreach ($service['checks'] as $check): ?><li><?php echo esc_html($check); ?></li><?php endforeach; ?></ul>
                <h2>عوامل تحديد التكلفة</h2>
                <p><?php echo esc_html($service['price']); ?></p>
            </div>
            <div class="rd-feature-image">
                <?php if (has_post_thumbnail()) { the_post_thumbnail('large', ['loading' => 'eager']); } else { rd_image($service['image'], ['loading' => 'eager', 'alt' => $service['alt']]); } ?>
                <div class="rd-related">
                    <a href="<?php echo esc_url(rd_url('gallery/' . $slug)); ?>">استعرض معرض الخامات</a>
                    <a href="<?php echo esc_url(rd_category_link($slug)); ?>">اقرأ أدلة التخصص</a>
                </div>
            </div>
        </div>
        <?php if (strlen(trim(get_the_content()))): ?><div class="rd-prose"><?php the_content(); ?></div><?php endif; ?>
        <h2>أسئلة شائعة</h2>
        <?php rd_faq($service['faq']); ?>
        <?php rd_end_cta('ابدأ بطلب تقييم لخدمتك', $slug); ?>
    </div>
<?php elseif ($section === 'services'): ?>
    <?php rd_page_top('خدمات الدهانات والديكور', 'تعرّف على الخامات وخيارات التشطيب، واستعرض الأدلة والصور المرتبطة بكل تخصص.'); ?>
    <div class="rd-shell rd-section">
        <div class="rd-related">
            <?php foreach (['interior-painting' => 'دهانات داخلية', 'exterior-painting' => 'دهانات خارجية', 'repainting' => 'تجديد الدهانات'] as $key => $label): ?>
                <a href="<?php echo esc_url(rd_url('services/' . $key)); ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </div>
        <?php rd_service_cards($services); ?>
        <?php rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'gallery'): ?>
    <?php rd_page_top('معرض أفكار الديكور', 'استعرض أمثلة تصميمية للخامات والألوان. افتح أي صورة أو اختر معرضًا لتخصص محدد.'); ?>
    <div class="rd-shell rd-section">
        <?php rd_image_gallery(array_keys(rd_media())); ?>
        <h2>معارض حسب التخصص</h2>
        <div class="rd-pills"><?php foreach ($services as $key => $item): ?>
            <a href="<?php echo esc_url(rd_url('gallery/' . $key)); ?>"><?php echo esc_html($item['short']); ?></a>
        <?php endforeach; ?></div>
        <?php rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'guides'): ?>
    <?php rd_page_top('أدلة الدهانات والديكور', 'اقرأ حسب نوع مشروعك، من تجهيز السطح واختيار الخامة إلى مراجعة التركيب.'); ?>
    <div class="rd-shell rd-section">
        <div class="rd-pills"><?php foreach ($services as $key => $item): ?>
            <a href="<?php echo esc_url(rd_category_link($key)); ?>"><?php echo esc_html($item['short']); ?></a>
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
    <section class="rd-shell rd-section rd-request-section">
        <div><h2>اتصال وواتساب</h2><p><?php echo esc_html(get_theme_mod('rd_location', 'الرياض، السعودية')); ?></p>
            <div class="rd-actions">
                <a class="rd-button rd-button-line" href="<?php echo esc_url(rd_tel()); ?>"><bdi><?php echo esc_html(rd_phone()); ?></bdi></a>
                <a class="rd-button rd-button-green" href="<?php echo esc_url(rd_wa()); ?>" target="_blank" rel="noopener">محادثة واتساب</a>
            </div>
            <p>شارك في المحادثة صور الأسطح ومقاساتها وأفضل وقت للتواصل.</p>
        </div>
        <?php rd_request_form(sanitize_key(wp_unslash($_GET['service'] ?? ''))); ?>
    </section>
<?php else: ?>
    <?php rd_page_top(get_the_title()); ?>
    <div class="rd-shell rd-section rd-prose">
        <?php if ($section === 'about'): ?>
            <h2>خيارات تشطيب للمنازل والفلل</h2>
            <p>ديكورات الرياض لخدمات الدهانات والجبس بورد وديكورات الشاشات والكسوات والأرضيات. نقدّم خيارات تُراجع بحسب المقاسات وحالة الموقع والنتيجة المطلوبة.</p>
            <h2>ابدأ من وصف المساحة</h2><p>اذكر مكان المشروع وما الذي تريد تغييره. صور الموقع والمقاسات تساعد في تحديد نطاق العمل ومناقشة الخامات والموعد.</p>
            <h2>عن صور المعرض</h2><p>يحتوي الموقع على صور توضيحية للأفكار والتشطيبات. تطبّق الخامات والتصاميم حسب قياس المكان والعينات المعتمدة.</p>
        <?php elseif ($section === 'project-planning'): ?>
            <h2>قبل المعاينة</h2><p>صوّر الأسطح في إضاءة واضحة، وقس عرض الجدران وارتفاعها، وحدد فتحات الكهرباء والمكيفات والأثاث القائم.</p>
            <h2>قبل اعتماد العرض</h2><p>اطلب تحديد المساحات وطريقة التجهيز والخامات الداخلة في العرض والمدة المتوقعة وطريقة مراجعة التنفيذ.</p>
            <h2>قبل الاستلام</h2><p>راجع العينات المتفق عليها والمظهر في الإضاءة الفعلية. اختبر المفاتيح والأبواب عندما يكون العمل متصلًا بها، وسجّل ملاحظاتك.</p>
        <?php elseif ($section === 'paint-colors'): ?>
            <h2>ابدأ من العناصر الثابتة</h2><p>قارن اللون مع البلاط والخشب والأقمشة التي ستبقى في المكان. تساعد الخلفية القريبة في ملاحظة درجة الدفء.</p>
            <h2>جرّب في إضاءة مختلفة</h2><p>ضع عينة على جدارين متعاكسين وراجعها صباحًا ومساءً. اختبر المظهر بجانب الإنارة الجدارية وبعيدًا عنها.</p>
            <h2>سجّل مواصفات العينة</h2><p>دوّن رقم اللون ونوع التشطيب وموضع التطبيق قبل شراء الدهان أو طلب التنفيذ.</p>
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
