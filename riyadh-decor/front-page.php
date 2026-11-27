<?php
get_header();
$services = rd_services();
?>
<main id="content">
    <section class="rd-hero" aria-labelledby="rd-hero-title">
        <?php rd_image('majlis', ['alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'class' => 'rd-hero-image']); ?>
        <div class="rd-shell rd-hero-content">
            <p class="rd-kicker">ديكورات داخلية في الرياض</p>
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
        <a href="<?php echo esc_url(rd_url('gallery')); ?>">معرض التصاميم</a>
        <a href="#steps">خطوات تجهيز المشروع</a>
    </div></div>

    <section class="rd-shell rd-section" id="services">
        <?php rd_heading('الخدمات'); ?>
        <?php rd_service_cards($services); ?>
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

    <section class="rd-shell rd-section rd-request-section" id="request">
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
