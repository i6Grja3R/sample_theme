<?php
require_once dirname(__DIR__, 3) . '/wp-load.php';
$key = 'category_count_week';
$args = array(
    'cat' => -1, //取得数
    'category__in' => $category_ID, //post_type
    'meta_key' => $key,
);
$reset_terms = get_terms($taxonomies, $args); //もしタクソノミーの何れかが存在しなければ WP_Error オブジェクトを返します。

if ($reset_terms) {
    foreach ($reset_terms as $reset_terms) {
        delete_post_meta($reset_terms->ID, $key);
    }
}
