<?php
/**
 * bbs_quest_submit.php
 * 一時ファイル保存 + トランジェント方式（submit フック専用）
 * - フロント → 本API（一次受付）→ /uploads/tmp/ に保存（本文・メタは transient）
 * - confirm 側は、返却する draft_id を使って最終確定（DB保存 & /tmp→/attach 移動）
 */

 // [001] 直接アクセス防止
if (!defined('ABSPATH')) { exit; }

// [010] 共通ヘルパーを読み込み（パスは環境に合わせて変更）
require_once __DIR__ . '/bbs_common.php';

// --------------------------------------
// bbs_quest_submit(): 質問投稿の「一時ファイル保存＋トランジェント方式」
// ------------------------------------------------------
// ④ 一時ファイルの処理とトランジェントの安全化
// ・MIME検証: finfo_file()
// ・拡張子検証: pathinfo() + ホワイトリスト
// ・ファイル本体は /uploads/tmp に保存、トランジェントには「ファイル名のみ」を保存
// ------------------------------------------------------
if (!function_exists('bbs_quest_submit')) {              // 多重定義防止
function bbs_quest_submit()                        // AJAX ハンドラ
{
// 1-1) CSRF 検証（submit 用の nonce）
if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bbs_quest_submit')) {
    wp_send_json_error(['errors' => ['不正なリクエストです（CSRF検出）。']]);
}

    // --- 匿名UUID（v4厳密） ---
    // [022] 匿名UUID を取得（Cookie v4 厳密）
    // Cookie から匿名UUID
    // 1-2) 匿名UUID & レート制限（重い処理の前に）
    $user_id = get_guest_uuid();                      // 以降の識別・レート制限に使用（権限制御には使わない）

    // 匿名UUIDを取得した直後あたりに置く（重い処理の前）
    if (function_exists('bbs_rate_guard')) {
    // 第2引数: 許可回数, 第3引数: 期間（秒）
    bbs_rate_guard($user_id, 20, 10 * MINUTE_IN_SECONDS); // 10分で20回まで
    }

    // -------------------------------------------------------------------------
    // 2) 入力の取得・サニタイズ・上限調整
    // -------------------------------------------------------------------------
    $unique_id = sanitize_text_field($_POST['unique_id'] ?? ''); // 親ID等
    $name      = sanitize_text_field($_POST['name']      ?? '匿名'); // 表示名
    $title     = sanitize_text_field($_POST['title']     ?? '');  // タイトル
    $text      = sanitize_textarea_field($_POST['text']  ?? ''); // 本文は textarea 用サニタイズ
    $stamp     = (int)($_POST['stamp'] ?? 0);                   // スタンプは整数

    // 2-1) 長さ上限（DoS対策/DB保護）
    $title = mb_substr($title, 0, 200);                            // タイトルの最大長
    $name  = mb_substr($name,  0, 50);                             // 名前の最大長
    $text  = preg_replace("/\r\n?/", "\n", mb_substr($text, 0, 5000)); // 改行正規化

    // --- 必須チェック（空白のみも弾く） -----------------------------------
    $errors = [];                                     // エラー配列
    if (bbs_trim($title) === '') { $errors[] = '・質問タイトルをご記入ください。'; } // 空白のみ禁止
    if (bbs_trim($text)  === '') { $errors[] = '・質問文をご記入ください。'; }      // 同上
    $allowed_stamps = bbs_allowed_stamps();           // スタンプ白リスト
    if ($stamp === 0 || !in_array($stamp, $allowed_stamps, true)) {
        $errors[] = '・スタンプを選択してください。';                           // 不正値を拒否
    }
    if ($errors) {                                    // テキスト不備なら
        wp_send_json_error(['errors' => $errors]);    // ここで終了（ファイル処理に入らない）
    }

    // -------------------------------------------------------------------------
    // 3) 添付の検証 & /uploads/tmp へ退避
    // -------------------------------------------------------------------------
    // --- ファイル上限とMIMEマップ -----------------------------------------
    // 一時保存ディレクトリの絶対パス（なければ作成）
    $tmp_dir   = bbs_tmp_dir();   
    // realpath ガード用の基底実パス  
    $base_tmp  = realpath($tmp_dir);                                 
    // MIME 判定器（finfo）を初期化（拡張子偽装対策）
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    // 許可MIME→拡張子のホワイトリスト（共通ヘルパー）例: ['image/png'=>['png'], 'video/mp4'=>['mp4']]
    $allowed = bbs_allowed_upload_map();                         // ← この変数名をループでも使う（$allowed_map と混同しない）MIME→拡張子 白リスト

    $total    = 0;                                              // 合計サイズ
    $saved    = [];                                              // 成功した tmp 側の basename を格納（あとで confirm へ渡す）

    // --- 添付ファイルの検証＆tmpへ move_uploaded_file ---------------------
    // 添付配列の存在と配列性をまず検証
    if (isset($_FILES['attach']) && is_array($_FILES['attach']['tmp_name'])) {   // 添付が配列で届いているか
        $files = $_FILES['attach'];                                              // 扱いやすく変数へ
        $count = count($files['tmp_name']);                                      // スロット数                        
        // …(3) 添付ファイルの検証＆tmpへ move_uploaded_file（ここが既に実装済みの foreach）…
        // foreach ($_FILES['attach']['tmp_name'] as $i => $tmp_name) { …検証… $filenames[$i] = basename($dest_path); }

        // 上限超過を防ぐ（サーバー側（PHP）でも「ファイル数の上限チェック」は必須）
        // 3-1) 枚数上限（まず最初に弾く負荷対策）
    if ($count > BBS_MAX_FILES) {
        wp_send_json_error(['errors' => ['・添付できるファイルは最大 ' . BBS_MAX_FILES . ' 件です。']]);
    }

    // 3-2) /uploads/tmp の検証（存在/実パス）
    if ($base_tmp === false) {                                               // 実パスが取れない場合
        wp_send_json_error(['errors' => ['・一時ディレクトリの検証に失敗しました。']]);
    }

// スロット整合のため、先に $saved を空文字で初期化
    for ($i = 0; $i < $count; $i++) {
        $saved[$i] = '';                 // 未選択/検証落ちでも配列の形を保つ
        $tmp_name = $files['tmp_name'][$i] ?? '';
                // PHP標準のアップロードエラー判定
                $err = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;

                        // 3-3-1) 未選択は空で継続（UIのスロット整合を維持）
                // スロットにファイルが選ばれていない（tmp_name が空）」場合でもエラーにせず、配列の体裁を保ったまま処理を先に進めるためのガード
                if ($err === UPLOAD_ERR_NO_FILE || $tmp_name === '') {
                    continue;
                }

                // 3-3-2) PHP標準エラー
                // エラーコード別にユーザー向け簡易メッセージ
                if ($err !== UPLOAD_ERR_OK) {
                    $map = [
                        UPLOAD_ERR_INI_SIZE   => '・サーバーの上限を超えました（upload_max_filesize）。',
                        UPLOAD_ERR_FORM_SIZE  => '・フォームの上限を超えました（MAX_FILE_SIZE）。',
                        UPLOAD_ERR_PARTIAL    => '・アップロードが途中で中断されました。',
                        UPLOAD_ERR_NO_TMP_DIR => '・一時フォルダが見つかりません（サーバー設定）。',
                        UPLOAD_ERR_CANT_WRITE => '・ディスク書き込みに失敗しました。',
                        UPLOAD_ERR_EXTENSION  => '・拡張によりブロックされました。',
                    ];
                    wp_send_json_error(['errors' => [$map[$err] ?? '・アップロードに失敗しました。']]);
                }

                // 本当に HTTP 経由でアップロードされた一時ファイルか
                // is_uploaded_file関数は、セキュリティのために指定したファイルがアップロードされたファイルかどうかを確認する。
                if (!is_uploaded_file($tmp_name)) {
                    wp_send_json_error(['errors' => ['・不正なアップロードが検出されました。']]);
                }

                // 個別サイズ→合計サイズ
                $size = (int) ($files['size'][$i] ?? 0);
                if ($size <= 0 || $size > BBS_MAX_PER_FILE) {
                    wp_send_json_error(['errors' => ['・ファイルサイズが大きすぎます（1枚 ' . (BBS_MAX_PER_FILE/1024/1024) . 'MB まで）。']]);
                }

                // 合計サイズ制限（アプリ側で明示的にチェックしないとサーバ設定が大きい環境ではいくらでも通る、DoS/ストレージ保護、クライアント側制限は信用できない）
                $total += $size;
        if ($total > BBS_MAX_TOTAL) {
            // $errors[] = '・添付の合計サイズが大きすぎます（最大 ' . (BBS_MAX_TOTAL/1024/1024) . 'MB）。';
            // break; // これ以上は処理しない
            wp_send_json_error(['errors' => ['・添付の合計サイズが大きすぎます（最大 ' . (BBS_MAX_TOTAL/1024/1024) . 'MB）。']]);
        }

        // 3-3-5) 実MIME と拡張子の整合性チェック
                // 実MIME（例: "image/jpeg; charset=binary" → "image/jpeg" へ正規化）
$mime = strtolower(trim(strtok($finfo->file($tmp_name) ?: '', ';')));
                // 元ファイル名をサニタイズして拡張子を抽出（自己申告の拡張子は信用しないが正規化に使う）
                // 元名から拡張子を正規化
                $orig = sanitize_file_name($files['name'][$i] ?? '');
$ext  = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($orig, PATHINFO_EXTENSION)));  // 二重拡張子などを抑止（英数字のみ）

                // 実MIMEを finfo で判定
                // 実ファイルの MIME を finfo で判定（"image/jpeg; charset=binary" → "image/jpeg" へ正規化）
                // 追加ガード：MIMEまたは拡張子が空なら即NG
                if (!isset($allowed[$mime]) || !in_array($ext, $allowed[$mime], true)) {
    // $errors[] = '・ファイル形式を判定できませんでした。';
    // continue;
    wp_send_json_error(['errors' => ['・許可されていないファイル形式です（拡張子と内容の不一致）。']]);
}

                // 画像は中身も検査（壊れた画像/偽装画像対策）
                // PHP 8+ : str_starts_with($detected_mime, 'image/')
                // 3-3-6) 画像は寸法も検査（巨大画像/破損防止）
                if (strpos($mime, 'image/') === 0) {
                    $img = @getimagesize($tmp_name); // アップロードされる画像のファイルのピクセルサイズを取得
                    if ($img === false || ($img[0] ?? 0) > BBS_IMG_MAX_W || ($img[1] ?? 0) > BBS_IMG_MAX_H) { // ピクセル上限（DoS的な超巨大画像を防ぐ）
                        // $errors[] = '・画像ファイルが壊れています。';
                        // continue;
                        wp_send_json_error(['errors' => ['・画像が壊れているか、サイズが大きすぎます。']]);
                    }
                }

                // 3-3-7) 一時保存先パス生成（UUID）
                // 予測困難なファイル名（UUID）を生成（user_idベースは避ける）
                $uuid = wp_generate_uuid4();                   // 予測困難な一時ファイル名
$name = "{$uuid}.{$ext}";                         // 例: a1b2c3...png（パス区切りは含めない）
$dst  = trailingslashit($tmp_dir) . $name;        // 連結して最終パスを作る

// 3-3-8) realpath ガード（/uploads/tmp/ 配下強制）
//    - $base_tmp は事前に $base_tmp = realpath($tmp_dir) で取得済みを想定
$real = realpath(dirname($dst));               // まだファイルが無いので親ディレクトリで検証
if ($real === false || strpos($real, $base_tmp) !== 0) {
    wp_send_json_error(['errors' => ['・保存先の検証に失敗しました。']]);
}

// 3-3-9) tmp に移動（失敗時は即終了）
                // 一時領域（tmp）に移動（失敗チェック）
                // クライアントからのリクエストでアップロードされたファイルの保存場所を変更する
                // 指定したファイルがアップロードされたか確認し、 アップロードされていた場合、そのファイルを新しい位置に移動
                if (!@move_uploaded_file($tmp_name, $dst)) {
                    wp_send_json_error(['errors' => ['・サーバーにファイルを保存できませんでした。']]);
                }

                // パーミッションを緩めに（実行権不要）
                @chmod($dest_path, 0644);

                // 3-3-10) 後工程用に basename のみ保存
                // 後工程に渡すため「ファイル名のみ」を保持（本体は tmp にある）
                // スロット順で保持（フロントへは basename のみ渡す）
                $saved[i] = basename($dst);
            } // end for
        } // 添付あり

        // -------------------------------------------------------------------------
    // 4) トランジェントへ（本文＋ファイル名のみ／10分）
    // -------------------------------------------------------------------------
    // 複数タブ対策も兼ねて下書きIDを付ける（user_id と組み合わせて一意）
    $draft_id = wp_generate_uuid4();                           // 複数タブ対応したいなら user_id に draft_id を足す
    $transient_key = "bbs_quest_{$user_id}_{$draft_id}";                          // user_id と組み合わせて衝突回避

    $payload = [                                                                   // confirm 側が読むペイロード
        'unique_id' => $unique_id,                                                 // 親ID（任意）
        'name'      => $name,                                                      // 名前
        'title'     => $title,                                                     // タイトル
        'text'      => $text,                                                      // 本文
        'stamp'     => $stamp,                                                     // スタンプ
        'attach'    => $saved,                                                     // tmp に置いた basename の配列
        'time'      => time(),                                                     // 生成時刻
    ];

    // 4-1) JSONサイズ上限（例: 64KB）
    $json = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // JSON化
    if ($json === false || strlen($json) > 65535) {                                 // エンコード失敗 or データ肥大
        // 作った tmp を掃除（肥大化時は全捨て）
        foreach ($saved as $fn) { if ($fn) { @unlink(trailingslashit($tmp_dir) . $fn); } }
        wp_send_json_error(['errors' => ['入力が大きすぎます。内容を短くしてください。']]); // 終了
    }

    // 4-2) 保存（有効期限: 10分）
    if (!set_transient($transient_key, $payload, 10 * MINUTE_IN_SECONDS)) {        // 保存失敗
        foreach ($saved as $fn) { if ($fn) { @unlink(trailingslashit($tmp_dir) . $fn); } } // 後始末
        wp_send_json_error(['errors' => ['一時データの保存に失敗しました。']]);            // 終了
    }

    // -------------------------------------------------------------------------
    // 5) 成功レスポンス（confirm は draft_id を使う）
    // -------------------------------------------------------------------------
    wp_send_json_success([
        'message'  => '下書きを保存しました。確認へ進めます。',                      // 表示用メッセージ
        'draft_id' => $draft_id,                                                    // confirm 側が必要
        // 'preview'  => $payload,                                                  // デバッグ用途（通常は返さない）
    ]);
} // function bbs_quest_submit
} // if !function_exists

// -----------------------------------------------------------------------------
// 2) Ajax フック登録（未ログインでもOK）
// -----------------------------------------------------------------------------
add_action('wp_ajax_bbs_quest_submit',        'bbs_quest_submit');   // ログイン時
add_action('wp_ajax_nopriv_bbs_quest_submit', 'bbs_quest_submit');   // 未ログイン時




1) 一時領域 /wp-content/uploads/tmp/ を全面アクセス禁止

配置場所：wp-content/uploads/tmp/.htaccess
# tmp配下を完全遮断
Require all denied

2) アップロード配下で PHP 実行を禁止（画像/PDF等の配信は可）

配置場所：wp-content/uploads/.htaccess
# .php / .phtml / .phar を実行させない
<FilesMatch "\.(php|phtml|phar)$">
    Require all denied
</FilesMatch>

# ディレクトリ一覧の無効化（任意）
Options -Indexes

--- ここから bbs_quest_confirm （サーバーサイド）のコード -----------

<?php
/**
 * bbs_quest_confirm.php（サーバーサイド：トランジェント版の confirm 用）
 * - mode=show   : transient を読み出してプレビュー返却（DBやファイル移動なし）
 * - mode=commit : DB INSERT（is_confirmed=1）→ /uploads/tmp → /uploads/attach へ移動 → files/attach列 UPDATE → transient 削除
 */

if (!defined('ABSPATH')) { exit; }                           // 直接アクセス防止

require_once __DIR__ . '/bbs_common.php';                    // 共通ヘルパー
// =============================================================================
// 1) confirm 本体
// =============================================================================
if (!function_exists('bbs_quest_confirm')) {                 // 多重定義防止
    /**
     * AJAX: bbs_quest_confirm
     *  - POST 'nonce' : bbs_quest_confirm 用 Nonce
     *  - POST 'mode'  : 'show' or 'commit'
     *  - POST 'draft_id' : submit 側が返したドラフトUUID（トランジェントキーに使用）
     */
    function bbs_quest_confirm()
    {
        global $wpdb;

        // 1) CSRF: 確認用 nonce を要求（フロントは bbs_quest_confirm で発行したものを送る）
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bbs_quest_confirm')) {
            wp_send_json_error(['errors' => ['不正なリクエストです（CSRF検出）。']]);
        }

        // AFTER（共通ヘルパーへ統一）
        // 2) 匿名UUID（Cookie）: submit と同等の厳密性で取得
        // ゲストUUID（v4厳密）
        $user_id = get_guest_uuid();

        // --- レート制限（10分/20回） --------------------------------------
        bbs_rate_guard($user_id, 20, 10 * MINUTE_IN_SECONDS);

        // --- パラメータ ----------------------------------------------------
        $mode     = sanitize_text_field($_POST['mode'] ?? 'show');
        $draft_id = sanitize_text_field($_POST['draft_id'] ?? '');
        // draft_id は UUID v4 を想定（submit 側で発行）
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $draft_id)) {
            wp_send_json_error(['errors' => ['ドラフトIDが不正です。']]);
        }

        // --- トランジェント読み出し ----------------------------------------
        $key = "bbs_quest_{$user_id}_{$draft_id}"; // submit 側と同じ命名
        $payload = get_transient($key);
        if (!is_array($payload)) {
            wp_send_json_error(['errors' => ['ドラフトが見つかりません（期限切れの可能性）。']]);
        }

        // payload 例:
        // [
        //   'unique_id' => (string),  // 親スレ等（任意）
        //   'name'      => (string),
        //   'title'     => (string),
        //   'text'      => (string),
        //   'stamp'     => (int),
        //   'attach'    => (array of tmp file names), // tmp に置いた basename のみ
        //   'time'      => (int) 保存時刻
        // ]

        // --- user_id取得・入力サニタイズ・tmpディレクトリ準備… --------------------------------------------------
        // 5) 下書きの内容を再サニタイズ＆必須チェック（submit と同等）
        $unique_id = sanitize_text_field($payload['unique_id'] ?? ''); // 親ID等
        $name      = sanitize_text_field($payload['name']      ?? '匿名');
        $title     = sanitize_text_field($payload['title']     ?? '');
        $text      = sanitize_textarea_field($payload['text']  ?? ''); // 本文は textarea 用サニタイズ
        $stamp     = (int) ($payload['stamp'] ?? 0);                   // スタンプは整数
        $files     = is_array($payload['attach'] ?? null) ? $payload['attach'] : [];

        // --- 長さ制限 & 改行正規化（submit と同じ） ------------------------------------
        $title = mb_substr($title, 0, 200);
        $name  = mb_substr($name,  0, 50);
        $text  = preg_replace("/\r\n?/", "\n", mb_substr($text, 0, 5000)); // 改行正規化

        // 必須（空白のみもNG） & stamp ホワイトリスト
        $err = [];
        if (bbs_trim($title) === '') $err[] = '・質問タイトルをご記入ください。';
        if (bbs_trim($text)  === '') $err[] = '・質問文をご記入ください。';
        if ($stamp === 0 || !in_array($stamp, bbs_allowed_stamps(), true)) {
            $err[] = '・スタンプを選択してください。';                          // 不正値
        }
        if ($err) {
            wp_send_json_error(['errors' => $err]); // テキスト不備ならここで終了（ファイル処理に入らない）
        }

        // --- 分岐: プレビュー（DB更新・移動なし） --------------------------
        if ($mode === 'show') {
            wp_send_json_success([
                'data' => compact('unique_id', 'name', 'title', 'text', 'stamp', 'files', 'draft_id'),
            ]);
        }

        // --- 分岐: 確定（DB INSERT → tmp→attach 移動 → attach列 UPDATE → トランジェント削除）
        // --- confirm 側では「アップロード処理」は行わないため、submit 側のような finfo による実MIME判定・ファイルサイズ/枚数の一次検証は“必須ではありません --------------------------------------------------
        // 入口の submit で厳密に弾けている前提なら、confirm はパス固定化・保存先ガード・拡張子再チェックができていれば十分です。
        if ($mode === 'commit') {
            // 1) まず DB に「本文等 + is_confirmed=1」で INSERT
            $table = $wpdb->prefix . 'sortable';
            $now   = current_time('mysql', true);

            // 相対パス列（JSON）を一旦は空で入れる（後でUPDATE）
            $ok = $wpdb->insert(                                                // プリペアド指定付きで INSERT を実行
                $table,                                                         // 対象テーブル
                [                                                               // 挿入するカラム => 値 の配列
                    'unique_id'    => $unique_id,
                    'name'    => $name,                                         // 投稿者名
                    'title'   => $title,                                        // タイトル
                    'text'    => $text,                                         // 本文
                    'stamp'   => $stamp,                                        // スタンプ（整数）
                    'user_id' => $user_id,                                      // 匿名UUID（またはユーザーID）
                    'files'        => wp_json_encode([], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'created_at'   => $now,
                    'is_confirmed' => 1,
                ],
                ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d']                  // 各値の型（文字列/整数）のプレースホルダ
            );
            if (!$ok) {                                                         // 失敗時
                wp_send_json_error(['errors' => ['投稿に失敗しました']]);        // JSONでエラーレスポンス
            }

            $insert_id = (int) $wpdb->insert_id;                                 // 直近INSERTの自動採番IDを取得（intに明示キャスト）

            // 2) /uploads/tmp → /uploads/attach へ移動（安全性を再確認）
            $tmp_dir     = bbs_tmp_dir();
            $attach_dir  = bbs_attach_dir();

            /* ▼▼ 追加：移動元(tmp)の実パスを 1 回だけ取得してガードに使う ▼▼ */
            $base_attach = realpath($attach_dir);  // すでにある既存チェック
            if ($base_attach === false) {             // tmp ディレクトリ自体が異常
                // 異常ならロールバック（INSERTした行を削除）
                $wpdb->delete($table, ['id' => $insert_id], ['%d']);
                wp_send_json_error(['errors' => ['保存先ディレクトリの検証に失敗しました。']]);
            }

            // confirm 側では実MIMEは再判定しないが、拡張子ホワイトリストと存在チェックは行う
            $ext_whitelist = array_unique(array_merge(...array_values(bbs_allowed_upload_map())));

            $saved_abs = [];                                                    // ここまで保存できた実ファイルの絶対パス（失敗時にロールバック）
            $final_rel = [];     // DBの files(JSON) 用
            $filenames = [];                                                     // 画面仕様（attach1/2/3/usericon 等）向けのファイル名保持（例）

            // submit 側でトランジェントに入れておいた「tmpファイル名配列」
            foreach ((array)$files as $i => $tmp_filename) {                     // 各添付を順に処理
                $tmp_filename = (string) $tmp_filename;                    // 念のため文字列化
                if ($tmp_filename === '') {                                // 空スロットはスキップ
                    $filenames[$i] = '';
                    continue;
                }

                // tmp 側絶対パス（basename 固定化）
                $src_path = trailingslashit($tmp_dir) . basename($tmp_filename); // クライアント由来のパス要素を排除（basenameのみを連結）
                $src_real = realpath($src_path);                                       // tmp 実体が無ければスキップ
                if ($src_real === false || !is_file($src_real)) {
                    $filenames[$i] = '';
                    continue;
                }

                // 拡張子を正規化（英数字のみ）
                $ext = strtolower(pathinfo($src_real, PATHINFO_EXTENSION));       // 実ファイルの拡張子を取得（小文字化）
                $ext = preg_replace('/[^a-z0-9]/', '', $ext);                // 英数字以外を除去（“php.png”のような偽装対策の一助）
                if (!in_array($ext, $ext_whitelist, true)) {                 // 許可拡張子でなければ破棄
                    @unlink($src_real);                                           // tmpファイルを削除
                    $filenames[$i] = '';
                    continue;
                }

                // 3) 旧仕様の suffix（0,1,2 → attach1～3 / 3 → usericon）
                //    表示や互換用に、人間が読める安定名を維持
                $suffix = ($i == 3) ? 'usericon' : ($i + 1);                    // 3番目だけ usericon、それ以外は 1..3

                // 4) 公開名の prefix を決定
                //    1) unique_id を最優先で使う（submit 時点で確定させておくのが理想）
                $prefix = $unique_id;

                //    2) 万一 unique_id が空/不正なら UUID へフォールバック（insert_id は列挙可能なので避ける）
                if ($prefix === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $prefix)) {
                    $prefix = wp_generate_uuid4();
                }

                // 1) まずは規則名（unique_id_添付番号.拡張子）で作る
                $final_filename = "{$prefix}_{$suffix}.{$ext}";               // 例) 550e..._attach1.jpg
                $dst_path       = trailingslashit($attach_dir) . $final_filename;

                // 2) attach 側ディレクトリの realpath ガード（シンボリックリンク/脱出対策）
                $real_dst_dir = realpath(dirname($dst_path));                   // まだファイルが無いので dirname() で検証
                if ($real_dst_dir === false || strpos($real_dst_dir, $base_attach) !== 0) {   // 想定外のパス（ディレクトリトラバーサル等）を拒否
                    @unlink($src_real);                                        // tmp を掃除
                    $filenames[$i] = '';
                    continue;
                }

                // 7)（任意）書き込み可否チェック（環境によってはあると親切）
                if (!is_dir($real_dst_dir) || !is_writable($real_dst_dir)) {
                    @unlink($src_real);
                    $filenames[$i] = '';
                    continue;
                }

                // 8) 重複対策：同名が既に存在するなら、末尾のみ UUID に差し替えて衝突回避
                // 同名衝突回避：すでに存在していたら UUID ベースに差し替え
                if (file_exists($dst_path)) {                                     // 同名ファイルがあれば
                    $final_filename = wp_generate_uuid4() . "_{$suffix}.{$ext}"; // 例) d1a7..._attach1.jpg
                    $dst_path       = trailingslashit($attach_dir) . $final_filename; // UUIDベースの別名に

                    // 差し替え後も念のためディレクトリを再検証（ディレクトリは同じだが保険）
                    $real_dst_dir = realpath(dirname($dst_path));
                    if ($real_dst_dir === false || strpos($real_dst_dir, $base_attach) !== 0) {
                        @unlink($src_real);
                        $filenames[$i] = '';
                        continue;
                    }
                }

                // 6) attach 側ディレクトリの realpath ガード（シンボリックリンク/脱出対策）
                $real_dst_dir = realpath(dirname($dst_path));                   // まだファイルが無いので dirname() で検証
                if ($real_dst_dir === false || strpos($real_dst_dir, $base_attach) !== 0) {   // 想定外のパス（ディレクトリトラバーサル等）を拒否
                    // 想定外ディレクトリなら tmp 側を掃除してスキップ
                    @unlink($src_real);                                           // tmp を削除
                    $filenames[$i] = '';
                    continue;
                }

                // 8) 重複対策：同名が既に存在するなら、末尾のみ UUID に差し替えて衝突回避
                if (file_exists($dst_path)) {                                     // 同名ファイルがあれば
                    $final_filename = wp_generate_uuid4() . "_{$suffix}.{$ext}"; // prefix を一時的にランダム化（ランダム名を発行）
                    $dst_path = trailingslashit($attach_dir) . $final_filename; // UUIDベースの別名に
                    // 差し替え後も念のためディレクトリを再検証（ディレクトリは同じだが保険）
                    $real_dst_dir = realpath(dirname($dst_path));
                    if ($real_dst_dir === false || strpos($real_dst_dir, $base_attach) !== 0) {
                        @unlink($src_real);
                        $filenames[$i] = '';
                        continue;
                    }
                }

                // 9) tmp → attach へ最終移動（rename フォールバック付き）
                if (!@rename($src_real, $dst_path)) {                           // rename が失敗する環境（跨ぎ等）向けフォールバック
                    if (!@copy($src_real, $dst_path) || !@unlink($src_real)) {  // copy 成功 + tmp 削除まで完了しなければ失敗扱い
                        if (file_exists($dst_path)) @unlink($dst_path);         // 途中生成物の掃除
                        // これまでの生成物を掃除
                        foreach ($saved_abs as $abs) {
                            @unlink($abs);
                        }
                        // DBロールバック
                        $wpdb->delete($table, ['id' => $insert_id], ['%d']);
                        // ここで、これまで保存できた $saved_abs を全削除する“全体ロールバック”も可能
                        wp_send_json_error(['errors' => ['ファイルの保存に失敗しました。']]); // エラー応答
                    }
                }

                // ※ ここも `$dst_path` ではなく `$dst` が正しい（元コードのタイポ）
                @chmod($dst_path, 0644);                                          // 実行権不要のパーミッションに調整（web配信前提）
                $saved_abs[]   = $dst_path;                                        // 後で失敗時に削除できるよう保存リストへ追加
                // DBに入れるのは “相対パスのみ” が原則
                $final_rel[]      = bbs_to_uploads_relative($dst_path);              // /wp-content/uploads からの相対パスへ変換して保持
                $filenames[$i] = basename($dst_path);          // 旧互換列用
            }

            // 3) files(JSON) と attach列を UPDATE
            $wpdb->update(                                                   // 先ほど挿入した行の files カラムを更新
                $table,
                [
                    'files' => wp_json_encode($final_rel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    // 旧カラムがある前提の場合のみセット（なければ削除）
                    'attach1' => $filenames[0] ?? '',
                    'attach2' => $filenames[1] ?? '',
                    'attach3' => $filenames[2] ?? '',
                    'usericon' => $filenames[3] ?? '',
                ],
                ['id'    => $insert_id],                                     // WHERE 条件（該当行）
                ['%s', '%s', '%s', '%s', '%s'],                                                      // 値の型
                ['%d']                                                       // WHERE の型
            );

            // 4) トランジェント削除（確定後は不要）
            delete_transient($key);                                    // submit 側で保存した下書きデータを削除（再送信防止・掃除）

            // 5) 成功レスポンス
            $row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $insert_id),
                ARRAY_A
            );
            wp_send_json_success([                                               // JSONで成功応答
                'message'   => '投稿が完了しました',                               // メッセージ
                'id'        => $insert_id,                                       // 新規レコードのID
                'data'      => $row,
                'files_rel' => $final_rel,
            ]);
        }

        // ここまで来たら不正
        wp_send_json_error(['errors' => ['不正なモードです。']]);
    }
}

// Ajax フック（未ログインでも実行可能）
add_action('wp_ajax_bbs_quest_confirm', 'bbs_quest_confirm');
add_action('wp_ajax_nopriv_bbs_quest_confirm', 'bbs_quest_confirm');
?>