<?php
get_header();
$services = rd_services();
?>
<main id="content">
    <section class="rd-hero" aria-labelledby="rd-hero-title">
        <?php rd_image('majlis', ['alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'class' => 'rd-hero-image']); ?>
        <div class="rd-shell rd-hero-content">
            <p class="rd-kicker">ديكورات وتشطيبات · الرياض</p>
            <h1 id="rd-hero-title"><?php echo esc_html(get_theme_mod('rd_hero_title', 'تشطيبات تليق بتفاصيل بيتك')); ?></h1>
            <p><?php echo esc_html(get_theme_mod('rd_hero_text', 'ديكورات حسب المقاس، وأعمال تشطيب وترميم للمنازل والفلل في الرياض.')); ?></p>
            <div class="rd-actions">
                <a class="rd-button rd-button-solid" href="#request">اطلب عرض سعر</a>
                <a class="rd-button rd-button-light" href="<?php echo esc_url(rd_url('gallery')); ?>">شاهد المعرض</a>
            </div>
        </div>
        <div class="rd-hero-caption">صورة توضيحية لتصميم مجلس بلمسات خشبية</div>
    </section>

    <div class="rd-jump"><div class="rd-shell">
        <a href="#services">الخدمات</a>
        <a href="#design-gallery">معرض التصاميم</a>
        <a href="#steps">خطوات تجهيز المشروع</a>
        <a href="#request">طلب عرض سعر</a>
    </div></div>

    <section class="rd-shell rd-home-intro" aria-label="مجالات الديكور والتشطيب">
        <div><span class="rd-gallery-kicker">مساحات بتفاصيل متناسقة</span><h2>من الفكرة إلى تفاصيل المكان</h2><p>استكشف خيارات الديكور الداخلي والتشطيب والأعمال الخارجية بحسب احتياجك.</p></div>
        <div class="rd-home-intro-links">
            <?php foreach (rd_service_groups() as $group => [$label, $slugs]): ?>
                <a href="<?php echo esc_url(add_query_arg('group', $group, rd_url('services'))); ?>"><?php echo esc_html($label); ?> <span aria-hidden="true">←</span></a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="rd-shell rd-section" id="services">
        <?php rd_heading('خدمات الديكور والتشطيبات', 'تصفّح الخدمات حسب نوع العمل، أو ابحث مباشرة عن الخدمة التي تحتاجها.'); ?>
        <?php rd_service_cards($services); ?>
    </section>

    <section class="rd-ideas rd-section" id="design-gallery" aria-labelledby="rd-gallery-title">
        <div class="rd-shell">
            <div class="rd-section-head">
                <div><p class="rd-gallery-kicker">إلهام لمساحتك</p><h2 id="rd-gallery-title">تصفّح أفكار الديكور والتشطيبات</h2><p>نماذج تصاميم من معرض الصور، اختر منها ما يناسب ذوقك ومساحتك.</p></div>
                <a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('gallery')); ?>">جميع التصاميم</a>
            </div>
            <div class="rd-featured-gallery rd-editorial-gallery">
                <?php foreach (['bedrooms', 'tv-walls', 'wood-partitions', 'flooring', 'suspended-ceilings', 'shades-and-screens'] as $slug):
                    if (!isset($services[$slug])) continue;
                    $service = $services[$slug];
                    $pictures = array_values(array_diff($service['portfolio'] ?? [], [$service['image']]));
                    $picture = $pictures[0] ?? $service['image'];
                    ?>
                    <a class="rd-gallery-tile" href="<?php echo esc_url(rd_url('gallery/' . $slug)); ?>">
                        <?php rd_image($picture, ['alt' => '']); ?>
                        <span><strong><?php echo esc_html($service['short']); ?></strong><small>استكشف التصاميم <span aria-hidden="true">←</span></small></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="rd-shell rd-home-banner" aria-labelledby="rd-home-banner-title">
        <?php rd_image($services['tv-walls']['image'], ['alt' => '']); ?>
        <div class="rd-home-banner-content"><span class="rd-kicker">تصاميم حسب المساحة</span><h2 id="rd-home-banner-title">تفاصيل صغيرة تغيّر مظهر المكان</h2><p>تصفّح خيارات الخلفيات والخامات، ثم شاركنا المقاسات والأفكار التي تناسب مشروعك.</p><a class="rd-button rd-button-solid" href="#request">ابدأ بطلب عرض سعر</a></div>
    </section>
    <section class="rd-shell rd-section" id="steps">
        <?php rd_heading('من فكرة إلى مشروع جاهز', 'المقاسات وحالة السطح والخامات توضح المطلوب قبل الاتفاق.', rd_url('project-planning'), 'دليل تجهيز المشروع'); ?>
        <ol class="rd-steps">
            <li><h3>شارك تفاصيل المساحة</h3><p>أرسل نوع العمل والحي وصور المكان والمقاسات الأولية.</p></li>
            <li><h3>راجع المقاسات والخامات</h3><p>تُراجع حالة الأسطح وتُقارن العينات وتفاصيل التشطيب.</p></li>
            <li><h3>اعتمد نطاق التنفيذ</h3><p>وضّح بنود العرض، وما يدخل في التجهيز والتركيب، والمدة المتوقعة.</p></li>
            <li><h3>راجع التشطيب عند التسليم</h3><p>عاين الأعمال حسب المواصفات المتفق عليها وسجّل الملاحظات.</p></li>
        </ol>
    </section>

    <section class="rd-shell rd-section rd-request-section rd-home-request" id="request">
        <div>
            <h2>خلّنا نراجع تفاصيل مشروعك</h2>
            <p>اذكر المقاسات وحالة السطح والخامة اللي تفضّلها. تقدر ترفق صور المكان في واتساب بعد فتح المحادثة.</p>
            <?php rd_faq([
                'كيف يتحدد عرض السعر؟' => 'يُراجع المقاس الفعلي وحالة الموقع والخامات ونطاق التجهيز والتوريد والتركيب لكل طلب.',
                'متى أقدر أعتمد التشطيب؟' => 'بعد مقارنة عينات الخامات والتشطيبات في الإضاءة الفعلية للمكان.',
            ]); ?>
        </div>
        <?php rd_request_form(); ?>
    </section>
</main>
<?php get_footer(); ?>
