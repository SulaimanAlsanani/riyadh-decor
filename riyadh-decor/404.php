<?php get_header(); ?>
<main id="content">
    <?php rd_page_top('هذه الصفحة غير موجودة', 'يمكنك الرجوع إلى الخدمات أو البحث عن دليل.'); ?>
    <div class="rd-shell rd-section">
        <?php get_search_form(); ?>
        <a class="rd-button rd-button-green" href="<?php echo esc_url(rd_url('services')); ?>">جميع الخدمات</a>
    </div>
</main>
<?php get_footer(); ?>
