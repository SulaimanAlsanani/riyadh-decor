<?php
get_header();
$service = is_category() ? get_queried_object()->slug : '';
$title = is_category() ? single_cat_title('', false) : (is_search() ? 'نتائج البحث عن: ' . get_search_query(false) : 'الأدلة والمقالات');
?>
<main id="content">
    <?php rd_page_top($title, '', 'guides', 'الأدلة والمقالات'); ?>
    <section class="rd-shell rd-section">
        <?php if ($service && isset(rd_services()[$service])): ?>
            <div class="rd-related"><a href="<?php echo esc_url(rd_url('services/' . $service)); ?>">تفاصيل الخدمة</a><a href="<?php echo esc_url(rd_url('gallery/' . $service)); ?>">معرض الصور</a></div>
        <?php endif; ?>
        <?php if (have_posts()): ?>
            <div class="rd-cards"><?php rd_guide_cards($wp_query); ?></div>
            <?php the_posts_pagination(['prev_text' => 'السابق', 'next_text' => 'التالي']); ?>
        <?php else: ?>
            <p>ما وجدنا نتائج مطابقة.</p><?php get_search_form(); ?>
        <?php endif; ?>
    </section>
</main>
<?php get_footer(); ?>
