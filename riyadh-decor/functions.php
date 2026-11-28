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
    wp_enqueue_style('rd-fonts', rd_asset('fonts.css'), [], $version);
    wp_enqueue_style('riyadh-decor', get_stylesheet_uri(), ['rd-fonts'], $version);
    wp_enqueue_script('riyadh-decor', rd_asset('site.js'), [], $version, ['in_footer' => true, 'strategy' => 'defer']);
});

add_action('customize_register', function ($customize) {
    $customize->add_section('rd_business', ['title' => 'بيانات ديكورات الرياض', 'priority' => 36]);
    foreach ([
        'rd_phone' => ['رقم الجوال للعرض والاتصال', '0505455984'],
        'rd_whatsapp' => ['رقم واتساب بالصيغة الدولية', '966505455984'],
        'rd_location' => ['منطقة الخدمة', 'الرياض، السعودية'],
        'rd_hero_title' => ['عنوان الواجهة الرئيسية', 'تشطيبات تليق بتفاصيل بيتك'],
        'rd_hero_text' => ['وصف الواجهة الرئيسية', 'ديكورات حسب المقاس، وأعمال تشطيب وترميم للمنازل والفلل في الرياض.'],
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
    static $cache = [], $index = null;
    $path=trim($path,'/');
    if ($index === null) {
        $index=[];
        foreach(get_pages(['post_status'=>'publish','number'=>0]) as $page) {
            $index[get_page_uri($page)]=$page;
        }
    }
    if (!isset($cache[$path])) {
        $page=$index[$path]??null;
        $cache[$path]=$page?get_permalink($page):home_url('/'.$path.'/');
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

function rd_category_link($slug, $fallback = true) {
    $term = get_term_by('slug', $slug, 'category');
    return $term && $term->count > 0 ? get_category_link($term->term_id) : ($fallback ? rd_url('guides') : '');
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
        return 'ديكورات الرياض للديكور والتشطيبات الداخلية والخارجية. تصفح الخدمات ونماذج التصاميم واطلب عرض سعر.';
    }
    $current = get_queried_object();
    if ($current instanceof WP_Post) {
        $services = rd_services();
        $specialty = get_post_meta($current->ID, '_rd_specialty', true);
        if (isset($services[$specialty])) {
            $spec = rd_service_specs()[$specialty] ?? null;
            if(get_post_meta($current->ID,'_rd_kind',true)==='gallery-item') return 'معرض '.$services[$specialty]['short'].' في الرياض: صور توضيحية ومراجع تصميم مرتبطة بطلب السعر.';
            return $services[$specialty]['intro'] . ($spec ? ' '.$spec['types'].'، في الرياض.' : ' في الرياض، تصفح التصاميم وجهّز طلب عرض السعر.');
        }
        $space = rd_space_content()[$current->post_name] ?? rd_library_content()[$current->post_name] ?? null;
        if ($current->post_name === 'solutions') return 'حلول الديكور والتشطيبات في الرياض: الخصوصية والتخزين وتنسيق الألوان والأحواش ومقارنة عروض الأسعار.';
        if ($current->post_name === 'project-guides') return 'اثنا عشر دليلًا لتجهيز مشاريع الديكور في الرياض، من رفع المقاسات والميزانية والتعاقد إلى استلام التشطيبات والعناية بها.';
        if ($current->post_name === 'property-types') return 'أفكار ديكور وتجهيز العقارات السكنية والتجارية في الرياض حسب المساحة والخامات ونطاق العمل.';
        if ($current->post_name === 'design-styles') return 'موسوعة ستة أنماط للديكور في الرياض: المودرن والكلاسيك والنيو كلاسيك والمعاصر والبسيط والعربي المعاصر.';
        if ($current->post_name === 'material-library') return 'مكتبة اثنتي عشرة خامة وثماني مقارنات للتشطيبات والديكور في الرياض، مع أدلة الاختيار والتركيب والعناية.';
        if ($space) return $space['intro'] . ' دليل ديكورات الرياض.';
        if ($current->post_name === 'spaces') return 'أفكار ديكور لعشر مساحات داخلية وخارجية في الرياض، من المجالس وغرف النوم إلى المكاتب والممرات والأسطح.';
        $description = wp_trim_words(wp_strip_all_tags(get_the_excerpt($current)), 28, '…');
        if ($description !== '') {
            return $description;
        }
        $descriptions = [
            'services' => 'خدمات الديكور والتشطيبات في الرياض، من المرايا وديكورات الجدران إلى الأرضيات والأسقف والمظلات.',
            'gallery' => 'معرض صور لخدمات الديكور والتشطيبات الداخلية والخارجية في الرياض.',
            'guides' => 'أدلة ديكورات الرياض لاختيار الخامات والألوان وتجهيز المقاسات ومراجعة جودة التشطيب.',
            'contact' => 'اتصل بديكورات الرياض أو جهّز طلب عرض سعر وأرسله عبر واتساب.',
            'about' => 'تعرف على مجالات ديكورات الرياض للديكور الداخلي والخارجي والتشطيبات للمنازل والفلل.',
            'project-planning' => 'دليل تجهيز مشروع الديكور: المقاسات والصور والخامات ونطاق العمل ومراجعة التسليم.',
            'materials-colors' => 'دليل عملي لاختيار خامات الديكور والألوان ومقارنة العينات حسب الإضاءة واستخدام المساحة.',
            'privacy-policy' => 'كيفية تجهيز بيانات طلب عرض السعر ومشاركتها عبر واتساب.',
            'faq'=>'إجابات عن خدمات الديكور والتشطيبات وطلبات الأسعار والخامات في الرياض.', 'estimate'=>'جهز مقاسات مشروع الديكور في الرياض واطلب تقدير تكلفة حسب الخامة ونطاق العمل.', 'service-finder'=>'اختر خدمة الديكور المناسبة لمساحتك في الرياض وتصفح التصاميم.'
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
        $current = get_queried_object();
        $specialty = $current instanceof WP_Post ? get_post_meta($current->ID, '_rd_specialty', true) : '';
        if (is_singular('post') && $current instanceof WP_Post) $specialty = rd_service_for_post($current->ID);
        $service = rd_services()[$specialty] ?? null;
        $editorial_page = $current instanceof WP_Post ? (rd_library_content()[$current->post_name] ?? rd_space_content()[$current->post_name] ?? null) : null;
        $image = $service ? rd_asset(rd_media_filename($service['image'])) : ($editorial_page ? rd_asset(rd_media_filename($editorial_page['image'])) : rd_asset('majlis.webp'));
        $canonical = is_front_page() ? home_url('/') : ($current instanceof WP_Post ? get_permalink($current) : '');
        printf("<meta property=\"og:image\" content=\"%s\">\n", esc_url($image));
        printf("<meta property=\"og:image:alt\" content=\"%s\">\n", esc_attr($service['alt'] ?? 'ديكورات الرياض'));
        echo '<meta property="og:site_name" content="ديكورات الرياض">' . "\n";
        if ($canonical) printf("<meta property=\"og:url\" content=\"%s\">\n", esc_url($canonical));
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        printf("<meta name=\"twitter:image\" content=\"%s\">\n", esc_url($image));
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

function rd_editorial_links($slug) {
    $query = rd_guides(3, $slug);
    if (!$query->have_posts()) return;
    echo '<section class="rd-editorial-links" aria-label="أدلة التخصص">';
    rd_heading('اقرأ قبل اختيار التصميم', 'دليل متخصص يساعدك على مقارنة الخيارات وتجهيز طلبك.');
    echo '<div class="rd-cards">'; rd_guide_cards($query); echo '</div></section>';
}

add_action('wp_head', function(){ if(!is_page()) return; $id=get_queried_object_id(); if(get_post_meta($id,'_rd_kind',true)!=='service') return; $key=get_post_meta($id,'_rd_specialty',true); $s=rd_services()[$key]??null; if(!$s)return; $data=['@context'=>'https://schema.org','@type'=>'Service','name'=>$s['name'],'description'=>$s['intro'],'url'=>get_permalink($id),'areaServed'=>['@type'=>'City','name'=>'الرياض'],'provider'=>['@id'=>home_url('/#business')]]; echo '<script type="application/ld+json">'.wp_json_encode($data,JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE).'</script>'; });

function rd_space_cards($items){ ?> <div class="rd-space-grid"><?php foreach($items as $key=>$item): ?><a class="rd-space-card" href="<?php echo esc_url(rd_url($key)); ?>"><?php rd_image($item['image'],['alt'=>'']); ?><div><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['intro']); ?></p><span class="rd-text-link">استكشف الأفكار ←</span></div></a><?php endforeach; ?></div><?php }
