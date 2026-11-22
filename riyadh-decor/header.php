<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="rd-skip" href="#content">انتقل إلى المحتوى</a>
<header class="rd-header">
    <div class="rd-shell rd-header-inner">
        <?php if (has_custom_logo()): ?>
            <div class="rd-brand"><?php the_custom_logo(); ?><span>ديكورات الرياض</span></div>
        <?php else: ?>
            <a class="rd-brand" href="<?php echo esc_url(rd_url()); ?>">
                <img src="<?php echo esc_url(rd_asset('logo.png')); ?>" width="70" height="70" alt="شعار ديكورات الرياض">
                <span>ديكورات الرياض<small>دهانات وتشطيبات</small></span>
            </a>
        <?php endif; ?>
        <button class="rd-menu-toggle" type="button" aria-expanded="false" aria-controls="rd-navigation" hidden>القائمة</button>
        <nav class="rd-navigation" id="rd-navigation" aria-label="القائمة الرئيسية">
            <a href="<?php echo esc_url(rd_url()); ?>">الرئيسية</a>
            <details class="rd-service-nav">
                <summary>الخدمات</summary>
                <div class="rd-nav-tray">
                    <a href="<?php echo esc_url(rd_url('services')); ?>"><strong>جميع الخدمات</strong></a>
                    <?php foreach (rd_services() as $slug => $service): ?>
                        <a href="<?php echo esc_url(rd_url('services/' . $slug)); ?>"><?php echo esc_html($service['short']); ?></a>
                    <?php endforeach; ?>
                </div>
            </details>
            <a href="<?php echo esc_url(rd_url('gallery')); ?>">المعرض</a>
            <a href="<?php echo esc_url(rd_url('guides')); ?>">الأدلة والمقالات</a>
            <a href="<?php echo esc_url(rd_url('about')); ?>">من نحن</a>
            <a class="rd-mobile-contact" href="<?php echo esc_url(rd_url('contact')); ?>">تواصل معنا</a>
        </nav>
        <a class="rd-button rd-button-solid rd-header-cta" href="<?php echo esc_url(rd_url('contact')); ?>">اطلب عرض سعر</a>
    </div>
</header>
