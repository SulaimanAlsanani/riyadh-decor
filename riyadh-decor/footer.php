<footer class="rd-footer rd-polished-footer rd-footer-v17">
    <div class="rd-shell rd-footer-lead rd-footer-v13"><div><h2>ابدأ بتفاصيل مساحتك</h2><p>تصفح التصاميم أو شاركنا متطلبات مشروعك في الرياض.</p></div><a class="rd-button rd-button-solid" href="<?php echo esc_url(rd_url('contact')); ?>">اطلب عرض سعر</a><a class="rd-button rd-button-light" href="<?php echo esc_url(rd_url('gallery')); ?>">استكشف المعرض</a></div>
    <div class="rd-shell rd-footer-grid">
        <div>
            <a class="rd-footer-brand" href="<?php echo esc_url(rd_url()); ?>"><img src="<?php echo esc_url(rd_logo_url()); ?>" alt="" width="68" height="68" loading="lazy" decoding="async"><strong>ديكورات الرياض</strong></a>
            <p>ديكور وتشطيبات داخلية وخارجية للمنازل والفلل في الرياض.</p>
        </div>
        <div>
            <h2>روابط سريعة</h2><a href="<?php echo esc_url(rd_url('project-guides')); ?>">أدلة تجهيز وتنفيذ المشاريع</a><a href="<?php echo esc_url(rd_url('property-types')); ?>">ديكور الفلل والشقق</a><a href="<?php echo esc_url(rd_url('spaces')); ?>">الديكور حسب المساحة</a><a href="<?php echo esc_url(rd_url('design-styles')); ?>">أنماط التصميم</a><a href="<?php echo esc_url(rd_url('material-library')); ?>">مكتبة الخامات</a>
            <a href="<?php echo esc_url(rd_url('services')); ?>">جميع الخدمات</a>
            <a href="<?php echo esc_url(rd_url('gallery')); ?>">معرض الأفكار</a>
            <a href="<?php echo esc_url(rd_url('project-planning')); ?>">تجهيز المشروع</a><a href="<?php echo esc_url(rd_url('faq')); ?>">الأسئلة الشائعة</a><a href="<?php echo esc_url(rd_url('guides')); ?>">الأدلة والمقالات</a>
        </div>
        <div>
            <h2>أدوات تجهيز المشروع</h2><a href="<?php echo esc_url(rd_url('service-finder')); ?>">اختيار الخدمة</a><a href="<?php echo esc_url(rd_url('estimate')); ?>">تجهيز تقدير التكلفة</a><a href="<?php echo esc_url(rd_url('materials-colors')); ?>">الخامات والألوان</a><h2>خدمات مختارة</h2>
            <?php foreach (['mirrors','bedrooms','tv-walls','flooring','painting'] as $key): if (!isset(rd_services()[$key])) continue; ?>
                <a href="<?php echo esc_url(rd_url('services/' . $key)); ?>"><?php echo esc_html(rd_services()[$key]['short']); ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h2>تواصل معنا</h2>
            <a href="<?php echo esc_url(rd_tel()); ?>"><bdi><?php echo esc_html(rd_phone()); ?></bdi></a>
            <a href="<?php echo esc_url(rd_wa()); ?>" rel="noopener" target="_blank">محادثة واتساب</a>
            <p><?php echo esc_html(get_theme_mod('rd_location', 'الرياض، السعودية')); ?></p>

        </div>
    </div>
    <div class="rd-shell rd-footer-bottom">
        <span>© <?php echo esc_html(wp_date('Y')); ?> ديكورات الرياض</span>
        <a href="<?php echo esc_url(rd_url('privacy-policy')); ?>">سياسة الخصوصية</a>
    </div>
</footer>
<div class="rd-float" aria-label="خيارات التواصل المباشر">
    <a href="<?php echo esc_url(rd_tel()); ?>"><?php rd_icon('phone'); ?> اتصال</a>
    <a href="<?php echo esc_url(rd_wa()); ?>" rel="noopener" target="_blank"><?php rd_icon('message'); ?> واتساب</a>
</div>
<?php wp_footer(); ?>
</body>
</html>
