<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (is_front_page()): ?>
    <link rel="preload" as="image" href="<?php echo esc_url(rd_asset('majlis-sm.webp')); ?>" media="(max-width: 768px)" fetchpriority="high">
    <link rel="preload" as="image" href="<?php echo esc_url(rd_asset('majlis.webp')); ?>" media="(min-width: 769px)" fetchpriority="high">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="rd-skip" href="#content">انتقل إلى المحتوى</a>
<header class="rd-header rd-polished-header rd-header-v13 rd-header-v17">
    <div class="rd-topbar"><div class="rd-shell"><span>الديكور والتشطيبات الداخلية والخارجية · الرياض</span><div><a href="<?php echo esc_url(rd_tel()); ?>"><bdi><?php echo esc_html(rd_phone()); ?></bdi></a><a href="<?php echo esc_url(rd_wa()); ?>" target="_blank" rel="noopener">واتساب</a></div></div></div>
    <div class="rd-shell rd-header-inner">
        <?php if (has_custom_logo()): ?>
            <div class="rd-brand"><?php the_custom_logo(); ?><span>ديكورات الرياض</span></div>
        <?php else: ?>
            <a class="rd-brand" href="<?php echo esc_url(rd_url()); ?>">
                <img src="<?php echo esc_url(rd_asset('logo.png')); ?>" width="70" height="70" alt="شعار ديكورات الرياض">
                <span>ديكورات الرياض<small>تصميم وتشطيب داخلي</small></span>
            </a>
        <?php endif; ?>
        <button class="rd-menu-toggle" type="button" aria-expanded="false" aria-controls="rd-navigation"><?php rd_icon('menu'); ?> القائمة</button>
        <nav class="rd-navigation" id="rd-navigation" data-initial-menu aria-label="القائمة الرئيسية">
            <a href="<?php echo esc_url(rd_url()); ?>">الرئيسية</a>
            <details class="rd-service-nav">
                <summary>الخدمات</summary>
                <div class="rd-nav-tray rd-mega">
                    <?php $nav_services = rd_services(); $nav_seen = []; ?>
                    <?php foreach (rd_service_groups() as [$label, $slugs]): ?>
                        <div class="rd-mega-group"><strong><?php echo esc_html($label); ?></strong>
                        <?php foreach ($slugs as $slug): if (!isset($nav_services[$slug]) || isset($nav_seen[$slug])) continue; $nav_seen[$slug] = true; ?>
                            <a href="<?php echo esc_url(rd_url('services/' . $slug)); ?>"><?php echo esc_html($nav_services[$slug]['short']); ?></a>
                        <?php endforeach; ?></div>
                    <?php endforeach; ?>
                    <?php if ($remaining = array_diff_key($nav_services, $nav_seen)): ?>
                        <div class="rd-mega-group"><strong>خدمات أخرى</strong><?php foreach ($remaining as $slug => $service): ?><a href="<?php echo esc_url(rd_url('services/' . $slug)); ?>"><?php echo esc_html($service['short']); ?></a><?php endforeach; ?></div>
                    <?php endif; ?>
                    <div class="rd-mega-bottom"><a href="<?php echo esc_url(rd_url('services')); ?>">جميع الخدمات ←</a><a href="<?php echo esc_url(rd_url('gallery')); ?>">معرض التصاميم ←</a><a href="<?php echo esc_url(rd_url('service-finder')); ?>">اختيار الخدمة ←</a></div>
                </div>
            </details>
            <a href="<?php echo esc_url(rd_url('gallery')); ?>">المعرض</a>
            <a href="<?php echo esc_url(rd_url('about')); ?>">من نحن</a>
            <a class="rd-nav-guide" href="<?php echo esc_url(rd_url('guides')); ?>">الأدلة</a>
            <a class="rd-nav-search" href="<?php echo esc_url(rd_url('services')); ?>#content"><?php rd_icon('zoom'); ?><span>استكشف الخدمات</span></a>
            <a class="rd-mobile-contact" href="<?php echo esc_url(rd_url('contact')); ?>">تواصل معنا</a>
        </nav>
        <a class="rd-button rd-button-solid rd-header-cta" href="<?php echo esc_url(rd_url('contact')); ?>">اطلب عرض سعر</a>
    </div>
</header>
