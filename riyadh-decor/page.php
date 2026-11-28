<?php
get_header();
while (have_posts()) :
    the_post();
    $services = rd_services();
    $slug = get_post_meta(get_the_ID(), '_rd_specialty', true);
    $kind = get_post_meta(get_the_ID(), '_rd_kind', true);
    $section = get_post_field('post_name', get_the_ID());
    $service = $services[$slug] ?? null;
    $spec = rd_service_specs()[$slug] ?? null;
?>
<main id="content">
<?php if ($service && $kind === 'gallery-item'): ?>
    <?php rd_page_top(get_the_title(), 'أفكار تصميمية توضيحية تساعدك على مقارنة الخامات والإضاءة والتفاصيل.', 'gallery', 'المعرض'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_actions($slug, true); ?>
        <?php if($spec): ?><aside class="rd-gallery-context"><h2><?php echo esc_html($spec['headline']); ?></h2><p><?php echo esc_html($spec['types']); ?>. الصور نماذج توضيحية وليست إثباتًا لتنفيذ مشروع بعينه.</p><a class="rd-button rd-button-line" href="<?php echo esc_url(rd_url('services/'.$slug)); ?>">راجع الخامات ومراحل التجهيز</a></aside><?php endif; ?>
        <?php rd_image_gallery($service['portfolio'], $slug); ?>
        <?php if (strlen(trim(get_the_content()))): ?><div class="rd-prose"><?php the_content(); ?></div><?php endif; ?>
        <?php rd_editorial_links($slug); ?>
        <?php rd_related_services($slug, 'gallery'); ?>
        <?php rd_end_cta('أعجبتك إحدى الأفكار؟', $slug); ?>
    </div>
<?php elseif ($service): ?>
    <?php rd_page_top(get_the_title(), $service['intro'], 'services', 'الخدمات'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_actions($slug); ?>
        <aside class="rd-service-value rd-service-banner"><div><span class="rd-gallery-kicker">ديكورات الرياض · حسب المقاس</span><h2><?php echo esc_html($spec['headline'] ?? $service['short']); ?></h2><p><?php echo esc_html($spec['types'] ?? $service['intro']); ?></p></div><a class="rd-button rd-button-solid" href="<?php echo esc_url(add_query_arg('service', $slug, rd_url('estimate'))); ?>">جهّز المقاسات</a></aside>
        <nav class="rd-detail-jump" aria-label="أقسام الخدمة"><a href="#service-options">خيارات التشطيب</a><a href="#service-specifications">الخامات والتنفيذ</a><a href="#service-designs">صور التصاميم</a><a href="#service-guide">دليل الاختيار</a><a href="#service-faq">الأسئلة الشائعة</a></nav>
        <div class="rd-service-layout rd-detail-layout" id="service-options">
            <div>
                <h2 class="rd-detail-heading"><span aria-hidden="true">01</span> خيارات التصميم والتشطيب</h2>
                <ul><?php foreach ($service['choices'] as $choice): ?><li><?php echo esc_html($choice); ?></li><?php endforeach; ?></ul>
                <h2 class="rd-detail-heading"><span aria-hidden="true">02</span> ماذا تجهّز قبل طلب العرض؟</h2>
                <ul><?php foreach ($service['checks'] as $check): ?><li><?php echo esc_html($check); ?></li><?php endforeach; ?></ul>
                <h2 class="rd-detail-heading"><span aria-hidden="true">03</span> عوامل تحديد التكلفة</h2>
                <p><?php echo esc_html($service['price']); ?></p>
            </div>
            <div class="rd-feature-image">
                <?php if (has_post_thumbnail()) { the_post_thumbnail('large', ['loading' => 'eager']); } else { rd_image($service['image'], ['loading' => 'eager', 'alt' => $service['alt']]); } ?>
                <p class="rd-image-note">صورة توضيحية للخامة أو التصميم؛ التنفيذ حسب المقاسات والعينات المعتمدة.</p>
            </div>
        </div>
        <?php if (strlen(trim(get_the_content()))): ?><div class="rd-prose"><?php the_content(); ?></div><?php endif; ?>
        <?php if($spec): ?><section id="service-specifications" class="rd-service-specifications">
            <?php rd_heading('الخامات ومواصفات الاختيار','تعتمد المواصفات النهائية على معاينة الموقع وعينات المنتجات الفعلية.'); ?>
            <div class="rd-spec-grid"><article><h3>أنواع التصاميم</h3><p><?php echo esc_html($spec['types']); ?></p></article><article><h3>المواد والمواصفات</h3><p><?php echo esc_html($spec['materials']); ?></p></article></div>
            <h2>مراحل التجهيز والتنفيذ</h2><ol class="rd-spec-steps"><?php foreach($spec['steps'] as $i=>$step): ?><li><strong><?php echo esc_html(['المعاينة','اعتماد التفاصيل','التركيب والمراجعة'][$i]); ?></strong><p><?php echo esc_html($step); ?></p></li><?php endforeach; ?></ol>
        </section><?php endif; ?>
        <?php if (!empty($service['portfolio'])): ?>
            <section id="service-designs" class="rd-service-portfolio" aria-label="<?php echo esc_attr($service['gallery_title']); ?>">
                <?php rd_heading($service['gallery_title'], 'تصفّح النماذج وافتح أي صورة لمعاينة تفاصيلها.'); ?>
                <?php rd_image_gallery($service['portfolio'], $slug); ?>
            </section>
        <?php endif; ?>
        <?php if ($advice = rd_service_advice()[$slug] ?? null): ?>
        <section class="rd-service-advice" id="service-guide" aria-label="دليل اختيار الخدمة وتجهيزها">
            <?php rd_heading('دليل اختيار الخدمة وتجهيزها', 'نقاط تساعدك على اتخاذ قرار واضح قبل اعتماد عرض السعر.'); ?>
            <div class="rd-advice-grid">
                <?php foreach (['المساحات المناسبة','كيف تختار التصميم؟','ما الذي تراجعه قبل التنفيذ؟','العناية بعد التركيب'] as $i => $label): ?>
                    <article><span class="rd-gallery-kicker"><?php echo esc_html(number_format_i18n($i+1)); ?></span><h3><?php echo esc_html($label); ?></h3><p><?php echo esc_html($advice[$i]); ?></p></article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        <h2 id="service-faq">أسئلة شائعة</h2>
        <?php $editorial = rd_editorial()[$slug] ?? null; rd_faq($service['faq'] + ($editorial ? [$editorial[4] => $editorial[5], 'ما الذي يؤثر في تكلفة هذه الخدمة؟' => $service['price']] : []) + (rd_extra_faq()[$slug] ?? []) + ($spec['faq'] ?? [])); ?>
        <?php rd_editorial_links($slug); ?>
        <?php $project_guides=array_filter(rd_library_content(),static fn($item)=>in_array($item['parent'],['project-guides','solutions'],true) && in_array($slug,$item['services'],true)); if($project_guides): ?><section class="rd-project-references"><?php rd_heading('أدلة تساعدك في تجهيز هذه الخدمة','مراجعة الخامات والتجهيز والاستلام قبل اعتماد العمل.',rd_url('solutions'),'حلول الديكور'); rd_space_cards(array_slice($project_guides,0,3,true)); ?></section><?php endif; ?>
        <?php rd_related_services($slug); ?>
        <?php rd_end_cta('ابدأ بطلب تقييم لخدمتك', $slug); ?>
    </div>
<?php elseif (in_array($section,['property-types','project-guides','solutions'],true) || isset(rd_library_content()[$section])): ?>
    <?php $library=rd_library_content(); $entry=$library[$section]??null; rd_page_top(get_the_title(),$entry?$entry['intro']:($section==='solutions'?'حلول عملية للخصوصية والتخزين والألوان والتشطيبات والأعمال الخارجية في الرياض.':($section==='project-guides'?'أدلة عملية للتجهيز والمقارنة والاستلام والعناية بالتشطيبات في الرياض.':'أفكار وتجهيز العقارات السكنية والتجارية في الرياض ضمن الخدمات المعتمدة.')),$entry?$entry['parent']:'spaces',$entry?rd_sections()[$entry['parent']]:'الديكور حسب المساحة'); ?>
    <div class="rd-shell rd-section rd-library-content">
      <?php if(!$entry): ?>
        <?php if($section==='solutions'): ?><div class="rd-solution-banner"><div><h2>ابدأ من احتياجك، لا من اسم الخامة</h2><p>حدد المساحة والمشكلة التي تريد حلها ثم قارن الخدمات والتصاميم.</p><a class="rd-button rd-button-solid" href="<?php echo esc_url(rd_url('service-finder')); ?>">اختيار الخدمة</a></div><?php rd_image('majlis',['alt'=>'']); ?></div><section id="solution-catalog"><?php rd_heading('حلول حسب احتياجك'); rd_space_cards(rd_solution_catalog()); ?></section><div class="rd-related rd-space-resources"><a href="<?php echo esc_url(rd_url('material-library')); ?>#material-comparisons">مقارنات الخامات</a><a href="<?php echo esc_url(rd_url('project-guides')); ?>">أدلة تجهيز المشاريع</a></div>
        <?php elseif($section==='project-guides'): ?><div class="rd-solution-banner"><div><h2>من المقاسات إلى الاستلام</h2><p>جهز المعلومات والعينات ونطاق العمل قبل اعتماد عرض السعر.</p><a class="rd-button rd-button-solid" href="<?php echo esc_url(rd_url('estimate')); ?>">حاسبة المقاسات</a></div><?php rd_image('floor',['alt'=>'']); ?></div><section id="project-guide-catalog"><?php rd_heading('أدلة تجهيز وتنفيذ المشاريع'); rd_space_cards(rd_project_catalog()); ?></section><section id="contract-guides"><?php rd_heading('قبل التعاقد'); rd_space_cards(array_intersect_key($library,array_flip(['contractor-selection','quotation-review']))); ?></section>
        <?php else: rd_heading('اختر نوع العقار'); rd_space_cards(array_filter($library,static fn($item)=>$item['parent']===$section)); endif; ?>
      <?php else: ?>
        <section class="rd-space-feature rd-space-banner-v25"><?php rd_image($entry['image'],['loading'=>'eager']); ?><div><span class="rd-gallery-kicker">دليل الاختيار</span><h2>ما الذي تراجعه قبل الاعتماد؟</h2><ul><?php foreach($entry['points'] as $point): ?><li><?php echo esc_html($point); ?></li><?php endforeach; ?></ul><a class="rd-button rd-button-solid" href="<?php echo esc_url(rd_url('estimate')); ?>">جهّز طلب التقدير</a></div></section>
        <?php if(in_array($entry['parent'],['material-library','design-styles'],true)||str_starts_with($section,'compare-')): ?><div class="rd-related rd-space-resources"><a href="<?php echo esc_url(rd_url('design-styles')); ?>">موسوعة التصميم</a><a href="<?php echo esc_url(rd_url('material-library')); ?>#material-comparisons">مقارنات الخامات</a><a href="<?php echo esc_url(rd_url('color-palette')); ?>">اختيار الألوان</a></div><?php endif; ?>
        <?php if($entry['parent']==='property-types'): ?><div class="rd-related rd-space-resources"><a href="<?php echo esc_url(rd_url('spaces')); ?>">اختيار المساحة</a><a href="<?php echo esc_url(rd_url('material-library')); ?>">مكتبة الخامات</a><a href="<?php echo esc_url(rd_url('project-guides')); ?>">أدلة تجهيز المشروع</a></div><?php endif; ?>
        <?php if(in_array($entry['parent'],['solutions','project-guides'],true)): ?><div class="rd-related rd-space-resources"><a href="<?php echo esc_url(rd_url('project-guides')); ?>">أدلة تجهيز المشاريع</a><a href="<?php echo esc_url(rd_url('quotation-review')); ?>">مراجعة عروض الأسعار</a><a href="<?php echo esc_url(rd_url('estimate')); ?>">تجهيز المقاسات</a></div><?php endif; ?>
        <?php rd_heading('الخدمات والتصاميم المرتبطة'); ?>
        <div class="rd-space-services"><?php foreach($entry['services'] as $key): $item=$services[$key]??null;if(!$item)continue; ?><article><?php rd_image($item['image'],['alt'=>'']); ?><div><h3><?php echo esc_html($item['short']); ?></h3><p><?php echo esc_html($item['intro']); ?></p><div class="rd-related"><a href="<?php echo esc_url(rd_url('services/'.$key)); ?>">تفاصيل الخدمة</a><a href="<?php echo esc_url(rd_url('gallery/'.$key)); ?>">معرض الصور</a></div></div></article><?php endforeach; ?></div>
        <?php rd_heading('أدلة مرتبطة'); rd_space_cards(array_slice(array_filter($library,static fn($item,$key)=>$item['parent']===$entry['parent'] && $key!==$section,ARRAY_FILTER_USE_BOTH),0,3,true)); ?>
      <?php endif; rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'spaces' || isset(rd_space_content()[$section])): ?>
    <?php $space_catalog=rd_space_content(); $space=$space_catalog[$section]??null; rd_page_top(get_the_title(), $space ? $space['intro'] : 'استكشف الديكور حسب مساحة المنزل، ثم قارن أنماط التصميم والخامات وجهّز مشروعك.', $space?'spaces':'', $space?'الديكور حسب المساحة':''); ?>
    <div class="rd-shell rd-section rd-space-content">
        <?php if (!$space): ?>
            <?php rd_heading('اختر المساحة التي تريد تطويرها'); rd_space_cards(rd_room_catalog()); ?>
            <?php rd_heading('أدلة التصميم والخامات'); rd_space_cards(array_diff_key($space_catalog,rd_room_catalog())); ?><div class="rd-related"><a href="<?php echo esc_url(rd_url('property-types')); ?>">الديكور حسب نوع العقار</a></div>
        <?php else: ?>
            <section class="rd-space-feature rd-space-banner-v25"><?php rd_image($space['image'],['loading'=>'eager']); ?><div><span class="rd-gallery-kicker">دليل ديكورات الرياض</span><h2>خطط للتصميم قبل التنفيذ</h2><ul><?php foreach($space['points'] as $point): ?><li><?php echo esc_html($point); ?></li><?php endforeach; ?></ul><a class="rd-button rd-button-solid" href="<?php echo esc_url(rd_url('estimate')); ?>">جهّز مقاسات المشروع</a></div></section>
            <?php if($section==='design-styles'): ?><section class="rd-encyclopedia"><h2>أنماط التصميم الستة</h2><?php rd_space_cards(array_filter(rd_library_content(),static fn($item)=>$item['parent']==='design-styles')); ?></section><?php elseif($section==='material-library'): ?><section class="rd-encyclopedia" id="material-guides"><h2>أدلة الخامات الاثنتا عشرة</h2><?php rd_space_cards(rd_material_catalog()); ?></section><section class="rd-encyclopedia" id="material-comparisons"><h2>ثماني مقارنات تساعدك على الاختيار</h2><?php rd_space_cards(rd_comparison_catalog()); ?></section><?php endif; ?>
            <div class="rd-related rd-space-resources"><a href="<?php echo esc_url(rd_url('material-library')); ?>">مكتبة الخامات</a><a href="<?php echo esc_url(rd_url('solutions')); ?>">حلول الديكور</a><a href="<?php echo esc_url(rd_url('project-guides')); ?>">أدلة تجهيز المشروع</a></div>
            <?php rd_heading('خدمات تناسب هذه الفكرة', 'راجع تفاصيل كل خدمة وصور الخامات قبل اختيار نطاق العمل.'); ?>
            <div class="rd-space-services"><?php foreach($space['services'] as $key): if(!isset($services[$key]))continue; $item=$services[$key]; ?><article><?php rd_image($item['image'],['alt'=>'']); ?><div><h3><?php echo esc_html($item['short']); ?></h3><p><?php echo esc_html($item['intro']); ?></p><div class="rd-related"><a href="<?php echo esc_url(rd_url('services/'.$key)); ?>">تفاصيل الخدمة</a><a href="<?php echo esc_url(rd_url('gallery/'.$key)); ?>">معرض الصور</a></div></div></article><?php endforeach; ?></div>
            <div class="rd-related"><a href="<?php echo esc_url(rd_url('spaces')); ?>">جميع المساحات والأدلة</a><a href="<?php echo esc_url(rd_url('materials-colors')); ?>">اختيار الخامات والألوان</a><a href="<?php echo esc_url(rd_url('project-planning')); ?>">تجهيز المشروع</a></div>
        <?php endif; ?>
        <?php rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'services'): ?>
    <?php rd_page_top('الخدمات', 'خدمات الديكور والتشطيبات الداخلية والخارجية في الرياض.'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_cards($services); ?>
    </div>
<?php elseif ($section === 'gallery'): ?>
    <?php rd_page_top('معرض التصاميم', 'اختر الخدمة لتصفح صورها ومعاينة تفاصيلها.'); ?>
    <div class="rd-shell rd-section">
        <?php rd_service_cards($services, 'gallery'); ?>
        <?php rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'guides'): ?>
    <?php rd_page_top('الأدلة والمقالات', 'اقرأ حسب نوع مشروعك، من تجهيز السطح واختيار الخامة إلى مراجعة التركيب.'); ?>
    <div class="rd-shell rd-section">
        <section class="rd-blog-feature"><?php rd_image('majlis',['alt'=>'']); ?><div><h2>أفكار عملية قبل بدء مشروعك</h2><p>أدلة متخصصة تساعدك في اختيار التشطيبات وتنسيق المساحات.</p><a class="rd-button rd-button-green" href="<?php echo esc_url(rd_url('materials-colors')); ?>">دليل الخامات والألوان</a></div></section>
        <section class="rd-knowledge-topics"><h2>تصفح مركز المعرفة حسب الموضوع</h2><div class="rd-knowledge-grid"><?php foreach(rd_knowledge_topics() as $key=>$label): $term=get_term_by('slug',$key,'category'); if(!$term)continue; ?><a href="<?php echo esc_url(get_category_link($term)); ?>"><strong><?php echo esc_html($label); ?></strong><span><?php echo esc_html(number_format_i18n($term->count)); ?> مقالات ←</span></a><?php endforeach; ?></div></section>
        <details class="rd-knowledge-specialties"><summary>تصفح حسب تخصص الخدمة</summary><div class="rd-pills"><?php foreach ($services as $key => $item): ?>
            <?php $category_url = rd_category_link($key, false); ?>
            <?php if ($category_url): ?>
                <a href="<?php echo esc_url($category_url); ?>"><?php echo esc_html($item['short']); ?></a>
            <?php endif; ?>
        <?php endforeach; ?></div></details>
        <div class="rd-related"><a href="<?php echo esc_url(rd_url('materials-colors')); ?>">دليل الخامات والألوان</a><a href="<?php echo esc_url(rd_url('project-planning')); ?>">دليل تجهيز المشروع</a></div>
        <?php get_search_form(); ?>
        <?php $number = max(1, absint($_GET['guide_page'] ?? 1)); $query = rd_guides(6, '', $number); ?>
        <?php if ($query->have_posts()): ?>
            <div class="rd-cards" data-guide-list><?php rd_guide_cards($query); ?></div>
        <?php else: ?>
            <p>ما فيه مقالات في هذه الصفحة.</p><a href="<?php echo esc_url(rd_url('guides')); ?>">ارجع إلى أول صفحة</a>
        <?php endif; ?>
        <div class="rd-load" data-guide-pagination>
            <?php if ($number > 1): ?><a class="rd-button rd-button-line" href="<?php echo esc_url(add_query_arg('guide_page', $number - 1, rd_url('guides'))); ?>">الصفحة السابقة</a><?php endif; ?>
            <?php if ($number < $query->max_num_pages): ?><a class="rd-button rd-button-green" data-guide-more href="<?php echo esc_url(add_query_arg('guide_page', $number + 1, rd_url('guides'))); ?>">عرض المزيد من المقالات</a><?php endif; ?>
        </div>
        <p class="screen-reader-text" data-guide-status aria-live="polite"></p>
    </div>
<?php elseif ($section === 'material-compare' || $section === 'favorites'): ?>
    <?php rd_page_top(get_the_title(),$section==='favorites'?'احفظ التصاميم في هذا المتصفح واختر ما تريد إرساله مع طلبك.':'قارن الخامات بحسب الاستخدام والتجهيز والعناية؛ لا تغني المقارنة عن مواصفات المنتج الفعلي.'); ?>
    <div class="rd-shell rd-section rd-tool-panel">
    <?php if($section==='favorites'): ?><div data-favorites-list data-contact="<?php echo esc_url(rd_url('contact')); ?>"><p role="status" data-favorites-status>التصاميم المحفوظة تظهر هنا عند تفعيل JavaScript.</p><div data-favorites-items></div><button type="button" class="rd-button rd-button-solid" data-favorites-quote hidden>اطلب سعر التصاميم المختارة</button></div><a href="<?php echo esc_url(rd_url('gallery')); ?>">تصفح المعرض</a>
    <?php else: ?><div data-compare-tool><div class="rd-form-row"><?php foreach(['a'=>'الخامة الأولى','b'=>'الخامة الثانية'] as $side=>$label): ?><label><?php echo esc_html($label); ?><select data-compare="<?php echo esc_attr($side); ?>"><?php foreach(rd_material_catalog() as $key=>$item): ?><option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($item['title']); ?></option><?php endforeach; ?></select></label><?php endforeach; ?></div><div class="rd-compare-grid" data-compare-output aria-live="polite"></div><div hidden><?php foreach(rd_material_catalog() as $key=>$item): ?><template data-material="<?php echo esc_attr($key); ?>"><article><h2><?php echo esc_html($item['title']); ?></h2><p><?php echo esc_html($item['intro']); ?></p><ul><?php foreach($item['points'] as $point): ?><li><?php echo esc_html($point); ?></li><?php endforeach; ?></ul><a href="<?php echo esc_url(rd_url($key)); ?>">الدليل الكامل</a></article></template><?php endforeach; ?></div></div><a href="<?php echo esc_url(rd_url('material-library')); ?>#material-comparisons">المقارنات التفصيلية</a><?php endif; ?>
    </div>
<?php elseif ($section === 'service-finder' || $section === 'estimate'): ?>
    <?php rd_page_top(get_the_title(), $section === 'estimate' ? 'احسب المساحة التقريبية وجهّز بيانات العرض؛ لا يتم احتساب سعر دون مواصفات معتمدة.' : 'اختر المساحة التي تريد تطويرها لتظهر لك الخدمات المرتبطة بها.'); ?>
    <div class="rd-shell rd-section">
        <?php if ($section === 'service-finder'): ?>
        <section class="rd-tool-panel" data-finder>
            <h2>أين تريد تنفيذ الديكور؟</h2>
            <label>المساحة<select data-finder-space><option value="all">جميع المساحات</option><option value="living">المجلس والصالة</option><option value="bedroom">غرفة النوم</option><option value="outdoor">الحوش والسطح</option><option value="renovation">التشطيب والترميم</option></select></label>
            <label>الاحتياج<select data-finder-need><option value="all">جميع الاحتياجات</option><?php foreach(rd_solution_catalog() as $key=>$item): ?><option value="<?php echo esc_attr(implode(' ',$item['services'])); ?>"><?php echo esc_html($item['title']); ?></option><?php endforeach; ?></select></label><p data-finder-status role="status"></p>
            <div class="rd-finder-grid">
            <?php $spaces = ['living'=>['mirrors','tv-walls','wood-partitions','carved-wood','foam-trim','flooring','chipboard-decor','wallpaper','suspended-ceilings','painting','tempered-glass'], 'bedroom'=>['mirrors','bedrooms','wardrobes','flooring','wallpaper','painting','foam-trim'], 'outdoor'=>['rooftop-annexes','shades-and-screens','tempered-glass'], 'renovation'=>['building-finishing','suspended-ceilings','flooring','painting','tempered-glass']]; foreach ($services as $key=>$item): $tags=[]; foreach($spaces as $space=>$keys) if(in_array($key,$keys,true)) $tags[]=$space; ?>
                <article data-finder-key="<?php echo esc_attr($key); ?>" data-finder-card="<?php echo esc_attr(implode(' ',$tags)); ?>"><?php rd_image($item['image'], ['alt'=>'']); ?><div><h3><?php echo esc_html($item['short']); ?></h3><div class="rd-related"><a href="<?php echo esc_url(rd_url('services/'.$key)); ?>">التفاصيل</a><a href="<?php echo esc_url(rd_url('gallery/'.$key)); ?>">الصور</a></div></div></article>
            <?php endforeach; ?>
            </div>
        </section>
        <?php else: ?>
        <form class="rd-form rd-tool-panel" data-estimate action="<?php echo esc_url(rd_url('contact')); ?>" method="get">
            <h2>بيانات المشروع الأولية</h2><label>الخدمة<select name="service" required><option value="">اختر الخدمة</option><?php foreach ($services as $key=>$item): ?><option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($item['short']); ?></option><?php endforeach; ?></select></label>
            <div class="rd-form-row"><label>الطول بالمتر<input name="length" type="number" min=".1" max="1000" step=".1" required></label><label>العرض أو الارتفاع بالمتر<input name="width" type="number" min=".1" max="1000" step=".1" required></label></div>
            <label>عدد المساحات المتماثلة<input name="quantity" type="number" min="1" max="100" step="1" value="1" required></label>
            <label>هدر تقديري اختياري (%)<input name="waste" type="number" min="0" max="50" step="1" value="0"></label><div class="rd-estimate-result" role="status" data-estimate-result>أدخل المقاسات لمعرفة المساحة التقريبية.</div>
            <p>المساحة الهندسية ليست كمية التوريد أو سعر التنفيذ؛ تختلف وحدة التسعير والهدر والتجهيز حسب الخدمة.</p>
            <button class="rd-button rd-button-solid" type="submit">متابعة طلب التقدير</button>
        </form>
        <?php endif; ?>
    </div>
<?php elseif ($section === 'faq'): ?>
    <?php rd_page_top('الأسئلة الشائعة', 'إجابات عن تجهيز الطلب والخامات ونطاق العمل في الرياض.'); ?>
    <div class="rd-shell rd-section rd-prose">
        <?php rd_faq(['كيف أطلب عرض سعر؟'=>'اختر الخدمة وأرسل الحي والمقاسات والصور عبر نموذج التواصل.','هل الصور في الموقع أعمال منفذة؟'=>'الصور نماذج توضيحية للأفكار والخامات؛ لا تُعرض باعتبارها إثباتاً لمشروع منفذ.','كيف يتحدد السعر؟'=>'حسب المقاسات والخامة وحالة الموقع ونطاق التوريد والتركيب.','هل يمكن اختيار لون خاص؟'=>'تُراجع العينات المتاحة وتُقارن في إضاءة الموقع.','كيف أعرف الخدمة المناسبة؟'=>'استخدم دليل اختيار الخدمة حسب المساحة.','هل يشمل العرض جميع التجهيزات؟'=>'تُحدد البنود المشمولة والمستثناة قبل الاتفاق.','هل يمكن تحديد موعد التنفيذ؟'=>'تُناقش المدة بعد تحديد النطاق والخامات وحالة الموقع.','كيف أراجع التشطيب؟'=>'قارن التنفيذ بالعينة والمواصفات المتفق عليها وسجل الملاحظات.']); ?>
        <div class="rd-related"><a href="<?php echo esc_url(rd_url('estimate')); ?>">جهّز تقدير التكلفة</a><a href="<?php echo esc_url(rd_url('service-finder')); ?>">اختر الخدمة</a></div><?php rd_end_cta(); ?>
    </div>
<?php elseif ($section === 'contact'): ?>
    <?php rd_page_top('تواصل مع ديكورات الرياض', 'صف لنا موقع المشروع والمساحة والخامات المطلوبة لتبدأ من طلب واضح.'); ?>
    <section class="rd-shell rd-section rd-request-section rd-contact-layout">
        <div class="rd-contact-info"><h2>اختر طريقة التواصل</h2><p>نطاق الخدمة: <?php echo esc_html(get_theme_mod('rd_location', 'الرياض، السعودية')); ?></p>
            <a class="rd-contact-method" href="<?php echo esc_url(rd_tel()); ?>"><strong>اتصال مباشر</strong><bdi><?php echo esc_html(rd_phone()); ?></bdi></a>
            <a class="rd-contact-method" href="<?php echo esc_url(rd_wa()); ?>" target="_blank" rel="noopener"><strong>محادثة واتساب</strong><span>أرسل صور المساحة والمقاسات ←</span></a>
            <div class="rd-contact-checklist"><h3>جهّز هذه المعلومات قبل الطلب</h3><ul><li>نوع الخدمة والحي.</li><li>المقاسات التقريبية وصور الموقع.</li><li>الخامات أو التصميم المطلوب.</li></ul></div>
            <p>النموذج يجهّز رسالة واتساب؛ راجعها قبل إرسالها.</p>
        </div>
        <?php rd_request_form(sanitize_key(wp_unslash($_GET['service'] ?? ''))); ?>
    </section>
<?php else: ?>
    <?php rd_page_top(get_the_title()); ?>
    <div class="rd-shell rd-section rd-prose">
        <?php if ($section === 'about'): ?>
            <div class="rd-about-feature"><?php rd_image('majlis', ['alt'=>'مجلس بتفاصيل خشبية','loading'=>'eager']); ?><div><span class="rd-gallery-kicker">ديكورات الرياض</span><h2>خيارات تشطيب للمنازل والفلل</h2><p>من اختيار الفكرة إلى تحديد الخامات والمقاسات.</p></div></div>
            <p>يقدم ديكورات الرياض خدمات الديكور والتشطيبات الداخلية والخارجية للمنازل والفلل. نراجع خيارات الخامات والتقسيمات بحسب المساحة ونطاق المشروع.</p>
            <h2>ابدأ من وصف المساحة</h2><p>اذكر مكان المشروع وما الذي تريد تغييره. صور الموقع والمقاسات تساعد في تحديد نطاق العمل ومناقشة الخامات والموعد.</p>
            <h2>مجالات العمل</h2><p>تجمع خدماتنا بين الديكور الداخلي والأخشاب والزجاج والتشطيبات والأعمال الخارجية. يمكنك استكشاف كل تخصص ومراجعة نماذج الخامات قبل تحديد الطلب.</p><h2>كيف تبدأ معنا؟</h2><p>حدد نوع العمل والحي والمقاسات، ثم شارك صور المساحة. تُراجع تفاصيل النطاق والخامات والموعد وفق طبيعة الطلب قبل الاتفاق.</p><div class="rd-related"><a href="<?php echo esc_url(rd_url('services')); ?>">استكشف الخدمات</a><a href="<?php echo esc_url(rd_url('gallery')); ?>">معرض التصاميم</a></div><h2>عن صور المعرض</h2><p>يحتوي الموقع على صور توضيحية للأفكار والتشطيبات. تطبّق الخامات والتصاميم حسب قياس المكان والعينات المعتمدة.</p>
        <?php elseif ($section === 'project-planning'): ?>
            <h2>قبل المعاينة</h2><p>صوّر الأسطح في إضاءة واضحة، وقس عرض الجدران وارتفاعها، وحدد فتحات الكهرباء والمكيفات والأثاث القائم.</p>
            <h2>قبل اعتماد العرض</h2><p>اطلب تحديد المساحات وطريقة التجهيز والخامات الداخلة في العرض والمدة المتوقعة وطريقة مراجعة التنفيذ.</p>
            <h2>تنسيق مراحل العمل</h2><p>حدّد الأعمال التي تسبق غيرها، ومواعيد إتاحة الموقع، ومسارات الخدمات، ومسؤولية إزالة المخلفات. راجع أي تغيير في النطاق قبل تنفيذه.</p><h2>اختيار العينات</h2><p>قارن العينات في المكان نفسه ووثّق الخامة واللون والمقاس الذي تم اعتماده.</p><div class="rd-related"><a href="<?php echo esc_url(rd_url('materials-colors')); ?>">اقرأ دليل اختيار الخامات والألوان</a></div><h2>قبل الاستلام</h2><p>راجع العينات المتفق عليها والمظهر في الإضاءة الفعلية. اختبر المفاتيح والأبواب عندما يكون العمل متصلًا بها، وسجّل ملاحظاتك.</p>
        <?php elseif ($section === 'materials-colors'): ?>
            <h2>ابدأ من وظيفة المساحة</h2><p>حدّد الاستخدام اليومي والرطوبة والإضاءة ومتطلبات التنظيف قبل مقارنة المواد. لا تعتمد على الشكل وحده.</p>
            <h2>وازن بين الألوان والخامات</h2><p>اختر لوناً أساسياً للجدران ودرجة مكملة للأرضيات والأخشاب. قارن العينات في ضوء النهار والإنارة المسائية، وتجنب اعتماد اللون من الشاشة وحدها.</p>
            <h2>قارن العينات الفعلية</h2><p>راجع ملمس السطح واللمعان والحواف والوصلات وتعليمات العناية ومواصفات المنتج. تختلف خصائص المواد باختلاف المنتج وطريقة التركيب.</p>
            <h2>اعتمد المواصفات قبل التنفيذ</h2><p>وثّق اسم الخامة ودرجتها ومقاسها وطريقة تركيبها، وراجع أثرها على الميزانية وتجهيز الموقع.</p>
            <div class="rd-related"><a href="<?php echo esc_url(rd_url('gallery')); ?>">قارن التصاميم</a><a href="<?php echo esc_url(rd_url('project-planning')); ?>">جهّز مشروعك</a></div>
        <?php elseif ($section === 'privacy-policy'): ?>
            <h2>بيانات الطلب</h2><p>يجهّز النموذج نص الرسالة داخل المتصفح. تنتقل البيانات إلى واتساب عند اختيار فتح المحادثة، وتُرسل عندما تضغط زر الإرسال في واتساب.</p>
            <h2>صور ومعلومات المشروع</h2><p>أرسل فقط الصور والمعلومات التي تحتاج لمشاركتها لمناقشة العرض. تخضع المحادثة لاستخدام واتساب وإعداداته.</p>
            <h2>سؤال عن البيانات</h2><p>تواصل على <a href="<?php echo esc_url(rd_tel()); ?>"><bdi><?php echo esc_html(rd_phone()); ?></bdi></a> للاستفسارات المتعلقة ببيانات طلبك.</p>
        <?php endif; ?>
        <?php the_content(); ?>
        <?php if ($section !== 'privacy-policy'): rd_end_cta(); endif; ?>
    </div>
<?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>
