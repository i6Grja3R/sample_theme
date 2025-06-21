<?php
/*
いいねAJAXリクエスト処理
*/
?>
<?php
add_action('wp_ajax_nopriv_toggle_like', 'handle_like_toggle');
add_action('wp_ajax_toggle_like', 'handle_like_toggle');

function handle_like_toggle()
{
    check_ajax_referer('like_nonce', 'nonce');

    $user_id   = $_COOKIE['user_id'] ?? '';
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

// AJAXハンドラ登録
add_action('wp_ajax_handle_like_action', 'handle_like_ajax');
add_action('wp_ajax_nopriv_handle_like_action', 'handle_like_ajax');

// トグル対応の AJAX 処理
function handle_like_ajax()
{
    error_log('handle_like_ajax called');
    error_log('POST: ' . print_r($_POST, true));
    error_log('COOKIE: ' . print_r($_COOKIE, true));
    // $_POST['unique_id']：対象となる投稿の一意ID（掲示板の質問IDなど）
    // $_COOKIE['user_id']：Cookieに保存された、ユーザー識別用のID
    // この2つが存在しない場合はエラーとして処理終了。
    if (
        !isset($_POST['unique_id']) || !isset($_COOKIE['user_id']) ||
        !wp_verify_nonce($_POST['nonce'] ?? '', 'like_nonce')
    ) {
        wp_send_json_error(['message' => '不正なリクエストです。']);
    }

    // 入力データを**無害化（サニタイズ）**して、XSSやSQLインジェクションを防ぎます。
    $unique_id = sanitize_text_field($_POST['unique_id']);
    $user_id = sanitize_text_field($_COOKIE['user_id']);

    // isGood($user_id, $unique_id)： 指定されたユーザーが既にその投稿に「いいね」しているかどうかを判定
    if (isGood($user_id, $unique_id)) {
        deleteGood($user_id, $unique_id); // 「いいね済み」の場合 → 取り消し（削除）
        $liked = false;
    } else {
        insertGood($user_id, $unique_id); // まだ「いいね」してない場合 → 登録（追加）
        $liked = true;
    }

    // 該当の $unique_id に対する全ユーザーの「いいね」数を取得
    // getGood() は、指定された unique_id に紐づくレコードを配列で返す関数
    $count = count(getGood($unique_id));
    // フロント（JavaScript）に対して成功ステータスと一緒に、最新の「いいね」数を返します。
    // JavaScript 側では data.count で受け取り、画面を更新できます。
    wp_send_json_success(['count' => $count, 'liked' => $liked]);
}
