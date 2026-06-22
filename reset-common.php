<?php
$args = [
'posts_per_page' => -1,
'post_status' => 'publish',
'meta_key' => $key,
];
$reset_posts = get_posts($args);
if ($reset_posts) {
    foreach ($reset_posts as $reset_post) {
        update_post_meta($reset_post->ID, $key, 0);
    }
}
?>
