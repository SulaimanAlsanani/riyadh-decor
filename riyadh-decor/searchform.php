<form class="rd-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <label for="rd-search-field" class="screen-reader-text">ابحث في الموقع</label>
    <input type="search" id="rd-search-field" name="s" placeholder="ابحث عن خدمة أو دليل" value="<?php echo esc_attr(get_search_query(false)); ?>" required>
    <button class="rd-button rd-button-green" type="submit">بحث</button>
</form>
