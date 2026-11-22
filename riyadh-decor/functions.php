<?php
defined('ABSPATH') || exit;

require_once __DIR__ . '/inc/catalog.php';
require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/components.php';

add_action('after_setup_theme', function () {
    load_theme_textdomain('riyadh-decor');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 289, 'width' => 289, 'flex-width' => true, 'flex-height' => true]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
});

add_action('wp_enqueue_scripts', function () {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('riyadh-decor', get_stylesheet_uri(), [], $version);
    wp_enqueue_script('riyadh-decor', rd_asset('site.js'), [], $version, true);
});

add_action('customize_register', function ($customize) {
    $customize->add_section('rd_business', ['title' => 'بيانات ديكورات الرياض', 'priority' => 36]);
    foreach ([
        'rd_phone' => ['رقم الجوال للعرض والاتصال', '0505455984'],
        'rd_whatsapp' => ['رقم واتساب بالصيغة الدولية', '966505455984'],
        'rd_location' => ['منطقة الخدمة', 'الرياض، السعودية'],
        'rd_hero_title' => ['عنوان الواجهة الرئيسية', 'تفصيل عادي ومعتّقة'],
        'rd_hero_text' => ['وصف الواجهة الرئيسية', 'تفصيل مرايا عادية ومعتّقة للجدران والمداخل، بألواح طولية أو تقسيمات هندسية تناسب مساحتك.'],
    ] as $key => [$label, $default]) {
        $customize->add_setting($key, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $customize->add_control($key, [
            'label' => $label, 'section' => 'rd_business', 'type' => 'text',
        ]);
    }
});

function rd_asset($name) {
    return get_theme_file_uri('assets/' . $name);
}

function rd_url($path = '') {
    if (!$path) {
        return home_url('/');
    }
    static $cache = [];
    if (!isset($cache[$path])) {
        $page = get_page_by_path(trim($path, '/'));
        $cache[$path] = $page && $page->post_status === 'publish' ? get_permalink($page) : home_url('/' . trim($path, '/') . '/');
    }
    return $cache[$path];
}

function rd_logo_url() {
    return wp_get_attachment_image_url((int) get_theme_mod('custom_logo'), 'medium') ?: rd_asset('logo.png');
}

function rd_phone() {
    return get_theme_mod('rd_phone', '0505455984');
}

function rd_tel() {
    $digits = preg_replace('/\D/', '', rd_phone());
    if (strpos($digits, '0') === 0) {
        $digits = '966' . substr($digits, 1);
    }
    return 'tel:+' . ($digits ?: '966505455984');
}

function rd_wa($message = '') {
    $digits = preg_replace('/\D/', '', get_theme_mod('rd_whatsapp', '966505455984'));
    if (strpos($digits, '0') === 0) {
        $digits = '966' . substr($digits, 1);
    }
    return 'https://wa.me/' . ($digits ?: '966505455984') . ($message ? '?text=' . rawurlencode($message) : '');
}

function rd_category_link($slug) {
    $term = get_term_by('slug', $slug, 'category');
    return $term ? get_category_link($term->term_id) : rd_url('guides');
}

function rd_guides($limit = 6, $slug = '', $page = 1) {
    $query = [
        'post_type' => 'post', 'posts_per_page' => $limit,
        'post_status' => 'publish', 'ignore_sticky_posts' => true, 'paged' => $page,
        'orderby' => ['date' => 'DESC', 'ID' => 'DESC'],
    ];
    if ($slug) {
        $query['category_name'] = $slug;
    }
    return new WP_Query($query);
}

function rd_service_for_post($post_id) {
    $services = rd_services();
    foreach (get_the_category($post_id) as $term) {
        if (isset($services[$term->slug])) {
            return $term->slug;
        }
    }
    return '';
}

function rd_description() {
    if (is_front_page()) {
        return 'ديكورات الرياض للدهانات والجبس بورد وديكورات الشاشات والكسوات والأرضيات. تصفّح الخدمات والأفكار وجهّز طلب مشروعك.';
    }
    $current = get_queried_object();
    if ($current instanceof WP_Post) {
        $services = rd_services();
        $specialty = get_post_meta($current->ID, '_rd_specialty', true);
        if (isset($services[$specialty])) {
            return $services[$specialty]['intro'];
        }
        $description = wp_trim_words(wp_strip_all_tags(get_the_excerpt($current)), 28, '…');
        if ($description !== '') {
            return $description;
        }
        $descriptions = [
            'services' => 'خدمات الدهانات والتشطيب والديكور في الرياض، مع خيارات الخامات والمعارض والأدلة حسب التخصص.',
            'gallery' => 'أفكار مصوّرة لدهانات وديكورات الشاشات والمداخل والأسقف والأرضيات وخلفيات الأسرة.',
            'guides' => 'أدلة ديكورات الرياض لاختيار الخامات والألوان وتجهيز المقاسات ومراجعة جودة التشطيب.',
            'contact' => 'اتصل بديكورات الرياض أو جهّز طلب عرض سعر وأرسله عبر واتساب.',
        ];
        return $descriptions[$current->post_name] ?? '';
    }
    return wp_strip_all_tags(get_bloginfo('description'));
}

add_action('wp_head', function () {
    if (is_search() || is_404()) {
        return;
    }
    if (!defined('WPSEO_VERSION') && !defined('RANK_MATH_VERSION')) {
        $description = rd_description();
        if ($description) {
            printf("<meta name=\"description\" content=\"%s\">\n", esc_attr($description));
            printf("<meta property=\"og:description\" content=\"%s\">\n", esc_attr($description));
        }
        printf("<meta property=\"og:title\" content=\"%s\">\n", esc_attr(wp_get_document_title()));
        printf("<meta property=\"og:image\" content=\"%s\">\n", esc_url(rd_asset('majlis.webp')));
        printf("<meta property=\"og:type\" content=\"%s\">\n", is_singular('post') ? 'article' : 'website');
        echo "<meta property=\"og:locale\" content=\"ar_SA\">\n";
    }
    if (is_front_page()) {
        $data = [
            '@context' => 'https://schema.org', '@type' => 'HomeAndConstructionBusiness',
            '@id' => home_url('/#business'), 'name' => 'ديكورات الرياض', 'url' => home_url('/'),
            'image' => rd_asset('majlis.webp'), 'logo' => rd_logo_url(),
            'telephone' => substr(rd_tel(), 4), 'areaServed' => ['@type' => 'City', 'name' => 'الرياض'],
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }
}, 5);
