<?php
get_header();
$services = rd_services();
$mirror_photos = [
    'mirror-bronze-door.jpg' => 'مرايا برونزية بألواح طولية وباب مدمج',
    'mirror-antiqued-panels.jpg' => 'مرايا معتّقة ذهبية بألواح طولية',
    'mirror-corner-panels.jpg' => 'مرايا برونزية تغطّي زاوية وأبواب خزائن',
    'mirror-living-room-grid.jpg' => 'جدار مرايا برونزية بتقسيمات مستطيلة',
    'mirror-backlit-black-frame.jpg' => 'مرآة بإطار أسود وإضاءة خلفية',
    'mirror-black-frame.jpg' => 'مرآة جدارية بتقسيمات سوداء',
    'mirror-bronze-tiles.jpg' => 'جدار بلاطات مرايا برونزية',
    'mirror-backlit-organic.jpg' => 'مرايا بأشكال عضوية مع إضاءة خلفية',
    'mirror-diagonal-wall.jpg' => 'جدار مرايا بخطوط هندسية مائلة',
    'mirror-sculpted-pieces.jpg' => 'قطع مرايا غير منتظمة بحواف منحنية',
];
?>
<main id="content">
    <section class="rd-hero" aria-labelledby="rd-hero-title">
        <?php rd_image('majlis', ['alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'class' => 'rd-hero-image']); ?>
        <div class="rd-shell rd-hero-content">
            <p class="rd-kicker">دهانات وتشطيبات في الرياض</p>
            <h1 id="rd-hero-title"><?php echo esc_html(get_theme_mod('rd_hero_title', 'تشطيبات تليق بتفاصيل بيتك')); ?></h1>
            <p><?php echo esc_html(get_theme_mod('rd_hero_text', 'دهانات وكسوات وتشطيبات داخلية وخارجية في الرياض. استكشف الخامات، جهّز مقاساتك، وتواصل معنا لتقييم مشروعك.')); ?></p>
            <div class="rd-actions">
                <a class="rd-button rd-button-solid" href="#request">اطلب عرض سعر</a>
                <a class="rd-button rd-button-light" href="<?php echo esc_url(rd_url('gallery')); ?>">شاهد المعرض</a>
            </div>
        </div>
        <div class="rd-hero-caption">صورة توضيحية لتصميم مجلس بلمسات خشبية</div>
    </section>

    <div class="rd-jump"><div class="rd-shell">
        <a href="#services">تصفح تخصصاتنا</a>
        <a href="#inspiration">أفكار للمساحات</a>
        <a href="#steps">خطوات تجهيز المشروع</a>
    </div></div>

    <section class="rd-shell rd-section" id="services">
        <?php rd_heading('خدمات الدهانات والديكور', 'اختر الخدمة واطّلع على الخامات والأفكار وما تحتاجه قبل طلب العرض.', rd_url('services'), 'جميع الخدمات'); ?>
        <?php rd_service_cards(array_slice($services, 0, 6, true)); ?>
        <div class="rd-more-links">
            <?php foreach (array_slice($services, 6, null, true) as $slug => $service): ?>
                <a href="<?php echo esc_url(rd_url('services/' . $slug)); ?>"><?php echo esc_html($service['short']); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="rd-mirror-collection" aria-labelledby="rd-mirror-title">
            <div class="rd-section-head">
                <div>
                    <h3 id="rd-mirror-title">تفصيل عادي ومعتّقة</h3>
                    <p>مرايا حسب المقاس للجدران والمداخل، بألواح طولية أو تقسيمات هندسية.</p>
                </div>
                <a class="rd-button rd-button-line" href="<?php echo esc_url(add_query_arg('service', 'mirrors', rd_url('contact'))); ?>">اطلب عرض سعر</a>
            </div>
            <div class="rd-mirror-gallery" data-gallery role="group" aria-label="صور تفصيل المرايا العادية والمعتّقة">
                <?php foreach ($mirror_photos as $filename => $caption): ?>
                    <?php $photo = rd_asset($filename); ?>
                    <a href="<?php echo esc_url($photo); ?>" data-gallery-image data-caption="<?php echo esc_attr($caption); ?>">
                        <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($caption); ?>" loading="lazy" decoding="async">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="rd-ideas rd-section" id="inspiration">
        <div class="rd-shell">
            <?php rd_heading('أفكار تناسب مساحتك', 'نماذج تصميم توضيحية لتنسيق الألوان والأسطح والإضاءة.', rd_url('gallery'), 'المعرض الكامل'); ?>
            <div class="rd-rail" data-rail>
                <div class="rd-rail-controls" hidden>
                    <button class="rd-button rd-button-line" type="button" data-rail-step="-1">السابق</button>
                    <button class="rd-button rd-button-line" type="button" data-rail-step="1">التالي</button>
                </div>
                <div class="rd-rail-track" data-rail-track tabindex="0" aria-label="صور أفكار الديكور">
                    <?php foreach (['tv-walls','painting','gypsum','tiles','flooring','bedrooms'] as $slug): $service = $services[$slug]; ?>
                        <a href="<?php echo esc_url(rd_url('gallery/' . $slug)); ?>">
                            <?php rd_image($service['image'], ['alt' => '']); ?>
                            <span><?php echo esc_html($service['short']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="rd-palette-callout">
                <div><h3>اختيار اللون يبدأ من إضاءة المكان</h3><p>قارن ملمس الخامة ولونها في الصباح والمساء قبل اعتماد العينة.</p></div>
                <div class="rd-swatches" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                <a class="rd-text-link" href="<?php echo esc_url(rd_url('paint-colors')); ?>">دليل اختيار اللون</a>
            </div>
        </div>
    </section>

    <section class="rd-shell rd-section rd-film-section" aria-labelledby="rd-film-title">
        <div class="rd-film-text">
            <h2 id="rd-film-title">شاهد أثر الخامة والإضاءة</h2>
            <p>قرّب الصورة لملاحظة ملمس الدهان، اتجاه الخشب، وتدرج الإضاءة في المجلس.</p>
            <a class="rd-text-link" href="<?php echo esc_url(rd_url('gallery/painting')); ?>">استعرض أفكار الدهانات</a>
        </div>
        <div class="rd-film" data-film>
            <video muted loop playsinline preload="none" poster="<?php echo esc_url(rd_asset('majlis-sm.webp')); ?>" aria-label="مشهد بصري توضيحي لخامات المجلس">
                <source src="<?php echo esc_url(rd_asset('majlis-motion.mp4')); ?>" type="video/mp4">
            </video>
            <button class="rd-button rd-button-light" type="button" hidden data-film-toggle>شغّل المشهد</button>
        </div>
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

    <?php $guides = rd_guides(3); if ($guides->have_posts()): ?>
        <section class="rd-ideas rd-section"><div class="rd-shell">
            <?php rd_heading('أدلة قبل اعتماد التصميم', 'قرارات تساعدك في القياس والاختيار ومراجعة التنفيذ.', rd_url('guides'), 'كل الأدلة'); ?>
            <div class="rd-cards"><?php rd_guide_cards($guides); ?></div>
        </div></section>
    <?php endif; ?>

    <section class="rd-shell rd-section rd-request-section" id="request">
        <div>
            <h2>خلّنا نراجع تفاصيل مشروعك</h2>
            <p>اذكر المقاسات وحالة السطح والخامة اللي تفضّلها. تقدر ترفق صور المكان في واتساب بعد فتح المحادثة.</p>
            <?php rd_faq([
                'كيف يتحدد عرض السعر؟' => 'يُراجع المقاس الفعلي وحالة الموقع والخامات ونطاق التجهيز والتوريد والتركيب لكل طلب.',
                'هل أقدر أطلب أكثر من خدمة؟' => 'اختر الخدمة الرئيسية وأضف بقية الأعمال المطلوبة في خانة التفاصيل.',
                'متى أقدر أعتمد اللون؟' => 'بعد مقارنة عينة على السطح في الإضاءة الفعلية مع الأثاث والأرضية.',
            ]); ?>
        </div>
        <?php rd_request_form(); ?>
    </section>
</main>
<?php get_footer(); ?>
