<?php
add_action('wp_ajax_handle_like_action', 'handle_like_ajax');               // ログインユーザー向け
add_action('wp_ajax_nopriv_handle_like_action', 'handle_like_ajax'); // 未ログインユーザー向け

function handle_like_ajax()
{
    // 1. CSRF対策（nonce検証）
    // 第1引数：wp_create_nonce('like_nonce') と 一致、第2引数：POST/GETで送られてくるキー（nonce）
    // check_ajax_referer('like_nonce', 'nonce'); // セキュリティトークン検証
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'like_nonce')) {
        wp_send_json_error('Nonce検証に失敗しました。');
    }

    // 2. POSTパラメータの検証
    // フロントエンドから送られてきた post_id（投稿ID）を取得。intval() を使って整数化し、セキュリティを確保。
    // $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $unique_id = isset($_POST['unique_id']) ? sanitize_text_field($_POST['unique_id']) : '';
    // セッション変数 $_SESSION['unique_id'] からユーザー識別用のIDを取得（未ログインでも使えるように工夫されている可能性が高い）。
    $user_id = $_SESSION['unique_id'] ?? '';

    // post_id や user_id が無効（ゼロや空）の場合は処理を中断し、Ajax に JSON 形式でエラーを返す。
    if (empty($unique_id) || empty($user_id)) {
        wp_send_json_error('不正なリクエストです。');
    }

    // 3. いいねの状態を切り替える
    // すでに「いいね」されていれば削除（取り消し）。まだ「いいね」されていなければ追加。
    if (isGood($user_id, $unique_id)) {
        // isGood(), deleteGood(), insertGood() はそれぞれ独自に定義された関数（通常は DB を操作して状態確認・登録・削除を行う）。
        deleteGood($user_id, $unique_id);
    } else {
        insertGood($user_id, $unique_id);
    }

    // 4. 最新のカウントを返却
    // 現在の投稿IDに対する「いいね」の数を取得。
    wp_send_json_success([
        // それを JSON 形式で返す
        'count' => count(getGood($unique_id)),
    ]);
}
