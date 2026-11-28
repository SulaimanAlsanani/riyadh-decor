<?php
get_header();
$services = rd_services();
?>
<main id="content" class="rd-luxury-home rd-home-v13 rd-home-v14 rd-home-v17 rd-home-v20">
    <section class="rd-hero" aria-labelledby="rd-hero-title">
        <?php rd_image('majlis', ['alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'class' => 'rd-hero-image']); ?>
        <div class="rd-shell rd-hero-content"><div class="rd-hero-copy">
            <p class="rd-kicker">ديكورات الرياض · تفاصيل حسب المقاس</p>
            <h1 id="rd-hero-title"><?php echo esc_html(get_theme_mod('rd_hero_title', 'تشطيبات تليق بتفاصيل بيتك')); ?></h1>
            <p><?php echo esc_html(get_theme_mod('rd_hero_text', 'ديكورات حسب المقاس، وأعمال تشطيب وترميم للمنازل والفلل في الرياض.')); ?></p>
            <div class="rd-actions">
                <a class="rd-button rd-button-solid" href="#quick-quote">اطلب عرض سعر</a>
                <a class="rd-button rd-button-light" href="<?php echo esc_url(rd_url('gallery')); ?>">استكشف التصاميم</a>
            </div>
        </div>
        </div>
        <div class="rd-hero-proof"><strong><?php echo esc_html(number_format_i18n(count($services))); ?></strong><span>خدمة للديكور والتشطيب</span></div>
        <div class="rd-hero-caption">صورة توضيحية لتصميم مجلس بلمسات خشبية</div>
    </section>

    <div class="rd-jump"><div class="rd-shell">
        <a href="#services">الخدمات</a>
        <a href="#design-gallery">معرض التصاميم</a>
        <a href="#spaces">حسب المساحة</a><a href="#steps">خطوات المشروع</a>
        <a href="#request">طلب عرض سعر</a>
    </div></div>

    <section class="rd-shell rd-home-intro" aria-label="مجالات الديكور والتشطيب">
        <div><span class="rd-gallery-kicker">مساحات بتفاصيل متناسقة</span><h2>من الفكرة إلى تفاصيل المكان</h2><p>استكشف خيارات الديكور الداخلي والتشطيب والأعمال الخارجية بحسب احتياجك.</p></div>
        <div class="rd-home-intro-links rd-visual-categories">
            <?php foreach (rd_service_groups() as $group => [$label, $slugs]): ?>
                <?php $cover = ['decor'=>'bedrooms','wood-glass'=>'wood-partitions','finishing'=>'flooring','outdoor'=>'shades-and-screens'][$group] ?? $slugs[0]; ?>
                <a href="<?php echo esc_url(add_query_arg('group', $group, rd_url('services'))); ?>">
                    <?php rd_image($services[$cover]['image'], ['alt'=>'']); ?>
                    <span class="rd-category-copy"><strong><?php echo esc_html($label); ?></strong><small><?php echo esc_html(number_format_i18n(count(array_intersect($slugs,array_keys($services))))); ?> خدمات <span aria-hidden="true">←</span></small></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section id="quick-quote" class="rd-shell rd-quick-quote" aria-labelledby="rd-quick-title">
<div><span class="rd-gallery-kicker">ابدأ من هنا</span><h2 id="rd-quick-title">ما الخدمة التي تحتاجها؟</h2><p>اختر الخدمة وانتقل مباشرة إلى طلب عرض السعر.</p></div>
<form action="<?php echo esc_url(rd_url('contact')); ?>" method="get" class="rd-quick-form" data-quick-quote>
<label for="rd-quick-service">نوع الخدمة</label><select id="rd-quick-service" name="service" required><option value="">اختر من الخدمات</option><?php foreach ($services as $slug => $service): ?><option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($service['short']); ?></option><?php endforeach; ?></select><button type="submit" class="rd-button rd-button-solid">متابعة طلب السعر</button>
</form></section>
    <section class="rd-shell rd-section" id="services">
        <?php rd_heading('خدمات مختارة للديكور والتشطيبات', 'ست خدمات مختارة للبدء؛ جميع الخدمات متاحة في الدليل الكامل.', rd_url('services'), 'جميع الخدمات'); ?>
        <?php rd_service_cards(array_intersect_key($services,array_flip(['mirrors','bedrooms','tv-walls','wood-partitions','flooring','painting']))); ?>
    </section>

    <section class="rd-shell rd-home-banner rd-split-banner" aria-labelledby="rd-home-banner-title">
        <?php rd_image($services['tv-walls']['image'], ['alt' => '']); ?>
        <div class="rd-home-banner-content"><span class="rd-kicker">أفكار للصالات والمجالس</span><h2 id="rd-home-banner-title">خلفية تلفزيون بتفاصيل تناسب مساحتك</h2><p>تصفّح خيارات الخلفيات والخامات، ثم شاركنا المقاسات والأفكار التي تناسب مشروعك.</p><a class="rd-button rd-button-solid" href="<?php echo esc_url(add_query_arg('service','tv-walls',rd_url('contact'))); ?>">ابدأ بطلب عرض سعر</a> <a class="rd-button rd-button-light" href="<?php echo esc_url(rd_url('gallery/tv-walls')); ?>">استكشف الخلفيات</a></div>
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

    <section class="rd-shell rd-section rd-home-spaces" id="spaces">
        <?php rd_heading('ديكور لكل مساحة في منزلك','اختر المكان الذي ترغب في تطويره واستكشف الخدمات والتصاميم المناسبة.',rd_url('spaces'),'جميع المساحات والأدلة'); rd_space_cards(array_slice(rd_space_content(),0,6,true)); ?>
        <div class="rd-related"><?php foreach(array_slice(rd_space_content(),6,null,true) as $key=>$item): ?><a href="<?php echo esc_url(rd_url($key)); ?>"><?php echo esc_html($item['title']); ?></a><?php endforeach; ?></div>
    </section>
    <section class="rd-shell rd-room-banner" aria-labelledby="rd-room-title">
        <div class="rd-room-photos"><?php rd_image($services['bedrooms']['image'], ['alt'=>'']); ?><?php rd_image($services['mirrors']['image'], ['alt'=>'']); ?></div>
        <div class="rd-room-copy"><span class="rd-gallery-kicker">غرف النوم · المرايا والخلفيات</span><h2 id="rd-room-title">غرفة تعكس ذوقك، بتفاصيل حسب المقاس</h2><p>استكشف خلفيات السرير والمرايا حسب المقاس، واختر الخامات والتفاصيل التي تناسب مساحتك.</p><div class="rd-actions"><a class="rd-button rd-button-green" href="<?php echo esc_url(rd_url('gallery/bedrooms')); ?>">خلفيات السرير</a><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('gallery/mirrors')); ?>">المرايا</a></div></div>
    </section>
    <section class="rd-shell rd-section rd-property-promo">
        <?php rd_heading('ديكور يناسب نوع عقارك','خطط لتجهيز أو تجديد الفيلا والشقة وفق الاستخدام الفعلي.',rd_url('property-types'),'جميع أدلة العقارات'); rd_space_cards(array_filter(rd_library_content(),static fn($item)=>$item['parent']==='property-types')); ?>
    </section>
    <section class="rd-shell rd-section rd-material-stories" aria-labelledby="rd-material-title">
        <div class="rd-section-head"><div><span class="rd-gallery-kicker">تفاصيل معمارية</span><h2 id="rd-material-title">خامات وتفاصيل تُكمل هوية المكان</h2></div></div>
        <div class="rd-material-grid">
        <?php foreach (['wood-partitions'=>'دفء الخشب وتوازن المساحات','flooring'=>'أرضيات تكمل هوية المكان','shades-and-screens'=>'راحة المساحات الخارجية'] as $slug => $headline): ?>
            <article class="rd-material-story">
                <?php rd_image($services[$slug]['image'], ['alt'=>'']); ?>
                <div><span class="rd-gallery-kicker"><?php echo esc_html($services[$slug]['short']); ?></span><h3><?php echo esc_html($headline); ?></h3><a class="rd-text-link" href="<?php echo esc_url(rd_url('material-library')); ?>">استكشف مكتبة الخامات <span aria-hidden="true">←</span></a></div>
            </article>
        <?php endforeach; ?>
        </div>
    </section>
    <section class="rd-shell rd-section rd-home-steps" id="steps">
        <?php rd_heading('من فكرة إلى مشروع جاهز', 'المقاسات وحالة السطح والخامات توضح المطلوب قبل الاتفاق.', rd_url('project-planning'), 'دليل تجهيز المشروع'); ?>
        <ol class="rd-steps">
            <li><h3>شارك تفاصيل المساحة</h3><p>أرسل نوع العمل والحي وصور المكان والمقاسات الأولية.</p></li>
            <li><h3>راجع المقاسات والخامات</h3><p>تُراجع حالة الأسطح وتُقارن العينات وتفاصيل التشطيب.</p></li>
            <li><h3>اعتمد نطاق التنفيذ</h3><p>وضّح بنود العرض، وما يدخل في التجهيز والتركيب، والمدة المتوقعة.</p></li>
            <li><h3>راجع التشطيب عند التسليم</h3><p>عاين الأعمال حسب المواصفات المتفق عليها وسجّل الملاحظات.</p></li>
        </ol><div class="rd-actions rd-planning-links"><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('solutions')); ?>">حلول الديكور حسب احتياجك</a><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('project-guides')); ?>">أدلة تجهيز المشاريع</a><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('service-finder')); ?>">ساعدني في اختيار الخدمة</a><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('estimate')); ?>">جهّز المقاسات</a><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('faq')); ?>">الأسئلة الشائعة</a></div>
    </section>

    <section class="rd-shell rd-section rd-request-section rd-home-request" id="request">
        <div>
            <span class="rd-gallery-kicker">خطوتك الأولى</span><h2>خلّنا نراجع تفاصيل مشروعك</h2>
            <p>اذكر المقاسات وحالة السطح والخامة اللي تفضّلها. تقدر ترفق صور المكان في واتساب بعد فتح المحادثة.</p>
            <?php rd_faq([
                'كيف يتحدد عرض السعر؟' => 'يُراجع المقاس الفعلي وحالة الموقع والخامات ونطاق التجهيز والتوريد والتركيب لكل طلب.',
                'متى أقدر أعتمد التشطيب؟' => 'بعد مقارنة عينات الخامات والتشطيبات في الإضاءة الفعلية للمكان.',
            ]); ?>
        </div>
        <?php rd_request_form(); ?>
    </section>
<a class="rd-mobile-quote rd-button rd-button-solid" data-mobile-quote href="#quick-quote" hidden>اطلب عرض سعر <span aria-hidden="true">←</span></a>
</main>
<?php get_footer(); ?>
