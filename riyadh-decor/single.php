<?php get_header(); ?>
<main id="content">
    <?php while (have_posts()): the_post(); $service = rd_service_for_post(get_the_ID()); ?>
        <?php rd_page_top(get_the_title(), '', 'guides', 'الأدلة والمقالات'); ?>
        <article class="rd-shell rd-section rd-prose">
            <?php if (has_post_thumbnail()): ?><div class="rd-article-image"><?php the_post_thumbnail('large'); ?></div><?php elseif ($service && isset(rd_services()[$service])): ?><div class="rd-article-image"><?php rd_image(rd_services()[$service]['image']); ?></div><?php endif; ?>
            <?php the_content(); ?>
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
