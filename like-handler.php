<?php
add_action('wp_ajax_handle_like_action', 'handle_like_ajax');               // ログインユーザー向け
add_action('wp_ajax_nopriv_handle_like_action', 'handle_like_ajax'); // 未ログインユーザー向け

function handle_like_ajax()
{
    // 1. CSRF対策（nonce検証）
    check_ajax_referer('like_nonce'); // セキュリティトークン検証

    // 2. POSTパラメータの検証
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = $_SESSION['unique_id'] ?? '';

    if (!$post_id || !$user_id) {
        wp_send_json_error('不正なリクエストです。');
    }

    // 3. いいねの状態を切り替える
    if (isGood($user_id, $post_id)) {
        deleteGood($user_id, $post_id);
    } else {
        insertGood($user_id, $post_id);
    }

    // 4. 最新のカウントを返却
    wp_send_json_success([
        'count' => count(getGood($post_id)),
    ]);
}
