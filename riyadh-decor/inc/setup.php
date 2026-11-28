<?php
defined('ABSPATH') || exit;

function rd_insert_page($slug, $title, $parent = 0, $meta = []) {
    $path = $parent ? get_page_uri($parent) . '/' . $slug : $slug;
    $existing = get_page_by_path($path);
    if ($existing) {
        if ($meta && !get_post_meta($existing->ID, '_rd_owned', true)) {
            return new WP_Error('rd_page_exists', 'مسار صفحة مستخدم: ' . $path);
        }
        return $existing->ID;
    }
    return wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug,
        'post_title' => $title, 'post_parent' => $parent, 'comment_status' => 'closed',
        'meta_input' => ['_rd_owned' => 1] + $meta,
    ], true);
}

function rd_seed_content() {
    $sections = [];
    foreach (rd_sections() as $slug => $title) {
        $sections[$slug] = rd_insert_page($slug, $title);
        if (is_wp_error($sections[$slug])) {
            return $sections[$slug];
        }
    }
    $home = rd_insert_page('homepage', 'ديكورات الرياض');
    if (is_wp_error($home)) {
        return $home;
    }
    foreach (rd_services() as $slug => $service) {
        foreach (['services' => 'service', 'gallery' => 'gallery-item'] as $parent => $kind) {
            $title = $kind === 'service' ? $service['name'] : 'أفكار ' . $service['short'];
            $page = rd_insert_page($slug, $title, $sections[$parent], [
                '_rd_specialty' => $slug, '_rd_kind' => $kind,
            ]);
            if (is_wp_error($page)) {
                return $page;
            }
        }
        $term = term_exists($slug, 'category');
        if (!$term) {
            $term = wp_insert_term($service['short'], 'category', ['slug' => $slug, 'description' => $service['intro']]);
        }
        if (is_wp_error($term)) {
            return $term;
        }
        $editorial = rd_editorial()[$slug] ?? null;
        $guides = $service['guides'];
        if ($editorial) $guides[] = array_slice($editorial,0,4);
        foreach ($guides as $index => $guide) {
            [$title, $first, $second] = $guide;
            $article_slug = $slug . '-guide-' . ($index + 1);
            if (get_page_by_path($article_slug, OBJECT, 'post')) {
                continue;
            }
            $id = wp_insert_post([
                'post_title' => $title, 'post_name' => $article_slug, 'post_type' => 'post',
                'post_status' => 'publish', 'post_excerpt' => $first, 'comment_status' => 'closed',
                'post_content' => '<h2>قبل البدء</h2><p>' . esc_html($first) . '</p><h2>ما الذي تراجعه؟</h2><p>' . esc_html($second) . '</p>' . (isset($guide[3]) ? '<h2>قبل اعتماد التنفيذ</h2><p>' . esc_html($guide[3]) . '</p>' : ''),
                'post_category' => [(int) $term['term_id']], 'meta_input' => ['_rd_owned' => 1],
            ], true);
            if (is_wp_error($id)) {
                return $id;
            }
        }
    }

    $topic_ids = [];
    foreach(rd_knowledge_topics() as $key=>$label){
        $term=term_exists($key,'category');
        if(!$term)$term=wp_insert_term($label,'category',['slug'=>$key]);
        if(is_wp_error($term))return $term;
        $topic_ids[$key]=(int)$term['term_id'];
    }
    foreach(rd_knowledge_original_topics() as $service=>$topic){
        $post=get_page_by_path($service.'-guide-1',OBJECT,'post');
        if($post && get_post_meta($post->ID,'_rd_owned',true)){
            $assigned=wp_set_post_categories($post->ID,[$topic_ids[$topic]],true);
            if(is_wp_error($assigned))return $assigned;
        }
    }
    foreach(rd_knowledge_articles() as $slug=>$item){
        if(get_page_by_path($slug,OBJECT,'post'))continue;
        $service_term=term_exists($item['service'],'category');
        if(!$service_term||is_wp_error($service_term))return new WP_Error('rd_missing_service_category',$item['service']);
        $content='';
        foreach($item['paragraphs'] as $i=>$paragraph)$content.='<h2>'.esc_html(['ما الذي تحدده؟','ما الذي تراجعه؟','قبل الاعتماد'][$i]).'</h2><p>'.esc_html($paragraph).'</p>';
        $id=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_name'=>$slug,'post_title'=>$item['title'],'post_excerpt'=>$item['paragraphs'][0],'post_content'=>$content,'comment_status'=>'closed','post_category'=>[(int)$service_term['term_id'],$topic_ids[$item['topic']]],'meta_input'=>['_rd_owned'=>1,'_rd_knowledge_guide'=>$item['guide']]],true);
        if(is_wp_error($id))return $id;
    }

    if (get_option('show_on_front') === 'posts' && get_post_meta($home, '_rd_owned', true)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home);
    }
    if (!get_option('permalink_structure')) {
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules(false);
    }
    if (!get_option('wp_page_for_privacy_policy') || !get_post_status(get_option('wp_page_for_privacy_policy'))) {
        update_option('wp_page_for_privacy_policy', $sections['privacy-policy']);
    }
    update_option('rd_starter_imported', true);
    return true;
}

add_action('admin_menu', function () {
    add_theme_page('تهيئة ديكورات الرياض', 'تهيئة ديكورات الرياض', 'manage_options', 'rd-setup', 'rd_setup_screen');
});

function rd_setup_screen() {
    ?>
    <div class="wrap" dir="rtl">
        <h1>تهيئة ديكورات الرياض</h1>
        <?php $notice = get_transient('rd_setup_message_' . get_current_user_id()); ?>
        <?php if ($notice): ?>
            <div class="notice <?php echo get_option('rd_starter_imported') ? 'notice-success' : 'notice-warning'; ?>"><p><?php echo esc_html($notice); ?></p></div>
            <?php delete_transient('rd_setup_message_' . get_current_user_id()); ?>
        <?php endif; ?>
        <p>أضف صفحات الخدمات والمعارض والأدلة ومحتواها الأولي. يحافظ الزر على المحتوى الموجود عند إعادة الاستخدام.</p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="rd_seed">
            <?php wp_nonce_field('rd_seed_content'); ?>
            <?php submit_button('إضافة المحتوى الأولي'); ?>
        </form>
        <p>لتعديل الرقم والنص الرئيسي والشعار، افتح المظهر ← تخصيص. يمكن تحرير النصوص الإضافية من الصفحات، والصور البارزة والمقالات من محرر WordPress.</p>
    </div>
    <?php
}

add_action('admin_post_rd_seed', function () {
    if (!current_user_can('manage_options')) {
        wp_die('ليس لديك صلاحية إدارة المحتوى.', '', ['response' => 403]);
    }
    check_admin_referer('rd_seed_content');
    $result = rd_seed_content();
    set_transient('rd_setup_message_' . get_current_user_id(), is_wp_error($result) ? $result->get_error_message() : 'تم تجهيز صفحات الموقع والأدلة.', MINUTE_IN_SECONDS);
    wp_safe_redirect(admin_url('themes.php?page=rd-setup'));
    exit;
});
