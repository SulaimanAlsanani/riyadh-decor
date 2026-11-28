<footer class="rd-footer">
    <div class="rd-shell rd-footer-grid">
        <div>
            <a class="rd-footer-brand" href="<?php echo esc_url(rd_url()); ?>"><img src="<?php echo esc_url(rd_logo_url()); ?>" alt="" width="68" height="68" loading="lazy" decoding="async"><strong>ديكورات الرياض</strong></a>
            <p>ديكور وتشطيبات داخلية وخارجية للمنازل والفلل في الرياض.</p>
        </div>
        <div>
            <h2>روابط سريعة</h2>
            <a href="<?php echo esc_url(rd_url('services')); ?>">جميع الخدمات</a>
            <a href="<?php echo esc_url(rd_url('gallery')); ?>">معرض الأفكار</a>
            <a href="<?php echo esc_url(rd_url('project-planning')); ?>">تجهيز المشروع</a>
        </div>
        <div>
            <h2>مجالات العمل</h2>
            <?php foreach (['mirrors','bedrooms','tv-walls','flooring','painting'] as $key): if (!isset(rd_services()[$key])) continue; ?>
                <a href="<?php echo esc_url(rd_url('services/' . $key)); ?>"><?php echo esc_html(rd_services()[$key]['short']); ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h2>تواصل معنا</h2>
            <a href="<?php echo esc_url(rd_tel()); ?>"><bdi><?php echo esc_html(rd_phone()); ?></bdi></a>
            <a href="<?php echo esc_url(rd_wa()); ?>" rel="noopener" target="_blank">محادثة واتساب</a>
            <p><?php echo esc_html(get_theme_mod('rd_location', 'الرياض، السعودية')); ?></p>
            <a class="rd-button rd-button-solid rd-footer-quote" href="<?php echo esc_url(rd_url('contact')); ?>">اطلب عرض سعر</a>
        </div>
    </div>
    <div class="rd-shell rd-footer-bottom">
        <span>© <?php echo esc_html(wp_date('Y')); ?> ديكورات الرياض</span>
        <a href="<?php echo esc_url(rd_url('privacy-policy')); ?>">سياسة الخصوصية</a>
    </div>
</footer>
<div class="rd-float" aria-label="خيارات التواصل المباشر">
    <a href="<?php echo esc_url(rd_tel()); ?>">اتصال</a>
    <a href="<?php echo esc_url(rd_wa()); ?>" rel="noopener" target="_blank">واتساب</a>
</div>
<?php wp_footer(); ?>
</body>
</html>
