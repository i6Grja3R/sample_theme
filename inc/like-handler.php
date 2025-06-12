<?php
/*
Template Name: lile-handler
固定ページ: AJAXリクエスト処理
*/
?>
<?php
add_action('wp_ajax_nopriv_toggle_like', 'handle_like_toggle');
add_action('wp_ajax_toggle_like', 'handle_like_toggle');

function handle_like_toggle()
{
    check_ajax_referer('like_nonce', 'nonce');

    $user_id   = $_COOKIE['guest_user_id'] ?? '';
    $unique_id = $_POST['unique_id'] ?? '';

    if (!$user_id || !$unique_id) {
        wp_send_json_error(['message' => '不正なデータです']);
    }

    if (isGood($user_id, $unique_id)) {
        deleteGood($user_id, $unique_id);
        $liked = false;
    } else {
        insertGood($user_id, $unique_id);
        $liked = true;
    }

    // このコードは関係なさそう
    wp_send_json_success([
        'liked'  => $liked,
        'count'  => count(getGood($unique_id))
    ]);
}
