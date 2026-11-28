<?php get_header(); ?>
<main id="content">
    <?php while (have_posts()): the_post(); $service = rd_service_for_post(get_the_ID()); ?>
        <?php rd_page_top(get_the_title(), '', 'guides', 'الأدلة والمقالات'); ?>
        <article class="rd-shell rd-section rd-prose">
            <?php if (has_post_thumbnail()): ?><div class="rd-article-image"><?php the_post_thumbnail('large'); ?></div><?php elseif ($service && isset(rd_services()[$service])): ?><div class="rd-article-image"><?php rd_image(rd_services()[$service]['image']); ?></div><?php endif; ?>
            <div class="rd-article-topics"><?php foreach(get_the_category() as $term): if(!isset(rd_knowledge_topics()[$term->slug]))continue; ?><a href="<?php echo esc_url(get_category_link($term)); ?>"><?php echo esc_html($term->name); ?></a><?php endforeach; ?></div>
            <?php the_content(); ?>
            <?php $guide=get_post_meta(get_the_ID(),'_rd_knowledge_guide',true); if($guide && isset(rd_sections()[$guide])): ?><aside class="rd-article-guide"><h2>دليل مرتبط بهذا الموضوع</h2><a href="<?php echo esc_url(rd_url($guide)); ?>"><?php echo esc_html(rd_sections()[$guide]); ?> ←</a></aside><?php endif; ?>
            <?php if ($service): ?><aside class="rd-blog-feature"><?php rd_image(rd_services()[$service]['image'],['alt'=>'']); ?><div><h2>حوّل الفكرة إلى طلب واضح</h2><p>تصفح التصاميم ثم شارك المقاسات والخامة التي تناسب مشروعك.</p><a class="rd-button rd-button-green" href="<?php echo esc_url(rd_url('gallery/'.$service)); ?>">استكشف المعرض</a></div></aside><?php endif; ?>
            <?php if ($service): ?>
                <div class="rd-related">
                    <a href="<?php echo esc_url(rd_url('services/' . $service)); ?>">تفاصيل الخدمة</a>
                    <a href="<?php echo esc_url(rd_url('gallery/' . $service)); ?>">معرض الأفكار</a>
                    <a href="<?php echo esc_url(rd_category_link($service)); ?>">مقالات التخصص</a>
                </div>
            <?php endif; ?>
            <?php rd_end_cta('تحتاج تقييمًا لمساحتك؟', $service); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
