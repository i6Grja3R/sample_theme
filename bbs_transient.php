<?php

/**
 * ゲスト専用：一時領域（PHP tmp）→ 検証 → move_uploaded_file() で最終保存
 *            → DBには “相対パスのみ” を保存（WordPress前提）
 *
 * ポイント
 * - ログイン判定を一切しない（常に Cookie の UUID を匿名識別子として使用）
 * - CSRF検証（nonce）
 * - 入力のサニタイズ＆必須チェック（空白のみNG・スタンプ白リスト）
 * - MIME実体（finfo）× 拡張子ホワイトリスト、画像寸法チェック
 * - 1ファイル/合計サイズの上限
 * - 最終保存先：/wp-content/uploads/bbs/YYYY/MM/（無ければ作成）
 * - DBへは uploads 基準の “相対パス” のみ保存
 * - 途中失敗時は保存済みファイルをクリーンアップ（孤児を残さない）
 * - レート制限（IP + user_uuid）
 *
 * 依存：WordPress（$wpdb, wp_upload_dir() 等）
 * 呼び方：/wp-admin/admin-ajax.php へ action=bbs_quest_submit で POST
 * 
 * bbs_guest_upload_final.php
 * submit → （PHP tmp）検証 → move_uploaded_file() で最終保存 → DBは相対パスのみ保存
 * その後の confirm では DB の is_confirmed を 1 に更新するだけ（ファイル移動・再INSERTなし）
 *
 * 依存: WordPress ($wpdb, wp_upload_dir, など)
 */

/**
 * bbs_quest_submit.php
 * 「PHP tmp で受ける → サーバーで検証 → move_uploaded_file() で最終保存」
 * → DB には uploads 基準の相対パスのみ保存（is_confirmed = 0）
 */

if (!defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/transient_common.php';

// フロントへ submit 用の nonce を配布（任意：既存のJSハンドル名に合わせて変更）
add_action('wp_enqueue_scripts', function () {
    // あなたのフロントJSのハンドル名に合わせて変更してください
    $handle = 'bbs-js-handle';
    if (!wp_script_is($handle, 'enqueued')) return;
    wp_localize_script('bbs-js-handle', 'bbs_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_submit'),
    ]);
});

/* ===========================================================
 * 4) 本体：bbs_quest_submit（AJAXハンドラ）
 * =========================================================== */
if (!function_exists('bbs_quest_submit')) {
    function bbs_quest_submit()
    {
        global $wpdb;                                                            // DB操作用（使わなければ削除可）
        $errors = [];                                                            // エラー蓄積

        /* --- CSRF 検証 ---------------------------------------------------- */
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bbs_quest_submit')) {
            wp_send_json_error(['errors' => ['不正なリクエストです（CSRF検出）。']]);
        }

        /* --- 匿名ユーザーUUID（ログイン判定なし） ------------------------ */
        $user_id = get_guest_uuid();                                             // 以降の識別・レート制限用（権限制御には使用しない）

        /* --- レート制限（IP + user_id、例：10分で20回まで） --------------- */
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';                              // 取得不可時はダミー
        $key = 'bbs_rate_' . md5($ip . '|' . $user_id);                     // 一意キー
        $cnt = (int) get_transient($key);                                   // 現在カウント
        if ($cnt >= 20) {                                                        // 閾値を超えたら拒否
            wp_send_json_error(['errors' => ['短時間に送信が多すぎます。時間をおいて再度お試しください。']]);
        }
        set_transient($rate_key, $cnt + 1, 10 * MINUTE_IN_SECONDS);              // 10分維持

        /* --- 入力取得・サニタイズ ---------------------------------------- */
        $unique_id = sanitize_text_field($_POST['unique_id'] ?? '');             // 親スレ等のID
        $name      = sanitize_text_field($_POST['name']      ?? '匿名');         // 表示名
        $title     = sanitize_text_field($_POST['title']     ?? '');             // タイトル
        $text      = sanitize_textarea_field($_POST['text']  ?? '');             // 本文（改行を保持）
        $stamp     = (int)($_POST['stamp'] ?? 0);                                // スタンプ（整数）

        /* --- 長さ上限＆改行正規化 ---------------------------------------- */
        $title = mb_substr($title, 0, 200);                                      // タイトル上限
        $name  = mb_substr($name,  0, 50);                                       // 名前上限
        $text  = mb_substr($text,  0, 5000);                                     // 本文上限
        $text  = preg_replace("/\r\n?/", "\n", mb_substr($text, 0, 5000));       // 改行正規化（\r\n, \r→\n）

        /* --- 必須チェック（空白のみもNG） -------------------------------- */
        // 文字列を受け取り、前後の半角/全角空白・水平/垂直空白を削る
        $trim = fn(string $s): string => preg_replace(
            '/^[\h\v\x{3000}]+|[\h\v\x{3000}]+$/u',
            '',
            $s
        );
        // 必須チェック（空白のみもNG）
        if ($trim($title) === '') $errors[] = '・質問タイトルをご記入ください。';
        if ($trim($text)  === '') $errors[] = '・質問文をご記入ください。';

        // フロントは 1〜8 を出している想定（必要に応じて調整）
        $allowed_stamps = function_exists('bbs_allowed_stamps') ? bbs_allowed_stamps() : [1, 2, 3, 4, 5, 6, 7, 8]; // 仕様に合わせて調整
        if ($stamp === 0 || !in_array($stamp, $allowed_stamps, true)) {
            $errors[] = '・スタンプを選択してください。';
        }
        if (!empty($errors)) {                                                   // テキスト不備ならファイル処理に入らない
            wp_send_json_error(['errors' => $errors]);
        }

        /* --- アップロード検証準備 ---------------------------------------- */
        $allowed       = bbs_allowed_upload_map();                               // MIME→拡張子ホワイトリスト
        $max_files     = BBS_MAX_FILES;                                          // 枚数上限
        $max_per_file  = BBS_MAX_PER_FILE;                                       // 個別上限
        $max_total     = BBS_MAX_TOTAL;                                          // 合計上限
        $img_w_max     = BBS_IMG_MAX_W;                                          // 画像最大幅
        $img_h_max     = BBS_IMG_MAX_H;                                          // 画像最大高

        $final_dir     = bbs_make_final_dir();                                   // 最終保存先（存在しなければ作成）
        $finfo         = new finfo(FILEINFO_MIME_TYPE);                          // 実MIME検出
        $total_size    = 0;                                                      // 合計サイズ
        $saved_abs     = [];                                                     // 保存済み絶対パス（失敗時の掃除用）
        $to_db         = [];                                                     // DB保存用（相対パス）

        /* --- 添付ループ --------------------------------------------------- */
        if (isset($_FILES['attach']) && is_array($_FILES['attach']['tmp_name'])) {

            // ファイル数上限チェック
            if (count($_FILES['attach']['tmp_name']) > $max_files) {
                $errors[] = '・添付できるファイル数は最大 ' . $max_files . ' 件です。';
            } else {
                foreach ($_FILES['attach']['tmp_name'] as $i => $tmp) {

                    /* 0) PHP標準エラーコードの確認 ------------------------- */
                    $err = $_FILES['attach']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

                    /* 1) 未選択スロットはスキップ ------------------------- */
                    if ($err === UPLOAD_ERR_NO_FILE || $tmp === '') continue;

                    /* 2) エラー種別ごとのメッセージ ----------------------- */
                    if ($err !== UPLOAD_ERR_OK) {
                        $map = [
                            UPLOAD_ERR_INI_SIZE   => '・サーバーの上限を超えました（upload_max_filesize）。',
                            UPLOAD_ERR_FORM_SIZE  => '・フォームの上限を超えました（MAX_FILE_SIZE）。',
                            UPLOAD_ERR_PARTIAL    => '・アップロードが途中で中断されました。',
                            UPLOAD_ERR_NO_TMP_DIR => '・一時フォルダが見つかりません（サーバー設定）。',
                            UPLOAD_ERR_CANT_WRITE => '・ディスク書き込みに失敗しました。',
                            UPLOAD_ERR_EXTENSION  => '・拡張によりブロックされました。',
                        ];
                        $errors[] = $map[$err] ?? '・アップロードに失敗しました。';
                        continue;
                    }

                    /* 3) 本当にHTTP経由の一時ファイルか ------------------- */
                    if (!is_uploaded_file($tmp)) {
                        $errors[] = '・不正なアップロードが検出されました。';
                        continue;
                    }

                    /* 4) サイズ（個別/合計） ------------------------------- */
                    $size = (int) ($_FILES['attach']['size'][$i] ?? 0);
                    if ($size <= 0 || $size > $max_per_file) {
                        $errors[] = '・ファイルサイズが大きすぎます（1ファイル最大 5MB）。';
                        continue;
                    }
                    $total_size += $size;
                    if ($total_size > $max_total) {
                        $errors[] = '・添付ファイルの合計サイズが大きすぎます（最大 20MB）。';
                        break;                                                   // 以降を止める
                    }

                    /* 5) 拡張子の正規化 ----------------------------------- */
                    $original = sanitize_file_name($_FILES['attach']['name'][$i] ?? '');
                    $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                    $ext      = preg_replace('/[^a-z0-9]/', '', $ext);          // 多重拡張子対策（英数字のみ）

                    /* 6) 実MIMEの検出と照合 -------------------------------- */
                    $detected = strtolower(trim(strtok($finfo->file($tmp) ?: '', ';'))); // "type; charset=..." → "type"
                    if (!isset($allowed[$detected]) || !in_array($ext, $allowed[$detected], true)) {
                        $errors[] = '・ファイル形式が許可されていません（拡張子とファイル内容が一致しません）。';
                        continue;
                    }

                    /* 7) 画像は中身チェック（壊れ/偽装/巨大寸法） ---------- */
                    if (strpos($detected, 'image/') === 0) {
                        $img = @getimagesize($tmp);
                        if ($img === false || ($img[0] ?? 0) > $img_w_max || ($img[1] ?? 0) > $img_h_max) {
                            $errors[] = '・画像ファイルが壊れているか、サイズが大きすぎます。';
                            continue;
                        }
                    }

                    /* 8) 予測困難なファイル名（UUID） ---------------------- */
                    $uuid      = wp_generate_uuid4();                           // 例: 550e8400-e29b-41d4-a716-446655440000
                    $safe_name = "{$uuid}_{$i}.{$ext}";                         // 例: 550e...-000_0.jpg

                    /* 9) 最終保存パスの組み立て ----------------------------- */
                    $dest      = path_join($final_dir, $safe_name);             // .../uploads/bbs/YYYY/MM/550e...jpg

                    /* 10) 保存先ディレクトリのガード ------------------------ */
                    $base = realpath($final_dir);
                    $real = realpath(dirname($dest));
                    if ($base === false || $real === false || strpos($real, $base) !== 0) {
                        $errors[] = '・保存先の検証に失敗しました。';
                        continue;
                    }

                    /* 11) PHP一時→最終保存へ移動 --------------------------- */
                    if (!@move_uploaded_file($tmp, $dest)) {               // 失敗時は以降に進まない
                        $errors[] = '・サーバーにファイルを保存できませんでした。';
                        continue;
                    }

                    /* 12) パーミッション調整 ------------------------------- */
                    @chmod($dest, 0644);                                        // 実行権不要

                    /* 13) DB保存用に相対パスへ ------------------------------ */
                    $saved_abs[] = $dest;                                       // 失敗時掃除のため絶対パスも保持
                    $to_db[]     = bbs_to_uploads_relative($dest);              // uploads基準の相対パスとして保存
                }
            }
        }

        /* --- エラーがあれば保存済みファイルを掃除して終了 ------------------ */
        if (!empty($errors)) {
            foreach ($saved_abs as $abs) {
                @unlink($abs);
            }                      // 途中まで保存したファイルを削除
            wp_send_json_error(['errors' => $errors]);
        }

        /* --- 6) DB保存（相対パスだけ） ----------------------------------- */
        $table = $wpdb->prefix . 'sortable';                                     // ← 実際のテーブル名に合わせて変更
        $now   = current_time('mysql', true);                                     // UTC（true）
        $files_json = wp_json_encode($to_db, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ok = $wpdb->insert(                                                     // プレースホルダで型安全に
            $table,
            [
                'unique_id'  => $unique_id,                                      // 親ID等
                'name'       => $name,
                'title'      => $title,
                'text'       => $text,
                'stamp'      => $stamp,
                'user_id'    => $user_id,                                        // 常に UUID（文字列）
                'files'      => $files_json,                                     // 相対パスのJSON配列
                'created_at' => $now,                                            // カラムがある場合
                'is_confirmed' => 0,                                              // ← ここ重要：submit時点では未確定
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',                          // 文字列/整数の型宣言
                '%d'
            ]
        );

        if (!$ok) {                                                              // 失敗時は保存済みファイルを掃除
            foreach ($saved_abs as $abs) {
                @unlink($abs);
            }
            wp_send_json_error(['errors' => ['DBへの保存に失敗しました。']]);
        }

        /* --- 成功レスポンス ----------------------------------------------- */
        $insert_id = (int) $wpdb->insert_id;                                     // 自動採番ID（あれば）
        wp_send_json_success([
            'message'   => '投稿を保存しました。',
            'id'        => $insert_id,
            'files'     => $to_db,                                               // 相対パス配列
            'user_uuid' => $user_id,                                             // 返すとクライアント側のデバッグに便利（不要なら外す）
        ]);
    }
}

/* ===========================================================
 * 5) Ajaxフック登録
 * =========================================================== */
add_action('wp_ajax_bbs_quest_submit',        'bbs_quest_submit');           // ログインユーザー
add_action('wp_ajax_nopriv_bbs_quest_submit', 'bbs_quest_submit');           // 未ログインユーザー
?>

<!--  ここから bbs_quest_confirm （サーバーサイド）のコード -->

<?php
/**
 * bbs_quest_confirm.php
 *
 * 目的:
 *  - submit で保存したレコード（is_confirmed = 0）を
 *    1) プレビュー表示 (mode=show) する
 *    2) 最終確定 (mode=commit) して is_confirmed=1 に更新する
 *
 * ポリシー:
 *  - ファイル移動や再INSERTは一切しない（submit 側で完結）
 *  - CSRF（専用nonce）、匿名UUID（Cookie, v4厳密）、レート制限（IP+UUID）
 *  - 自分のレコードのみ操作可能（id + user_id で限定）
 */

// 直アクセス防止
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/transient_common.php';

// フロントへ submit 用の nonce を配布（任意：既存のJSハンドル名に合わせて変更）
add_action('wp_enqueue_scripts', function () {
    // あなたのフロントJSのハンドル名に合わせて変更してください
    $handle = 'bbs-js-handle';
    if (!wp_script_is($handle, 'enqueued')) return;
    wp_localize_script($handle, 'bbs_confirm_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_confirm'),
    ]);
});

// ─────────────────────────────────────────────
// 必要ならここで共通関数を require してください。
// require_once __DIR__ . '/bbs_common.php';
// 本ファイル単体でも動くように、未定義ならヘルパーを定義します。
// ─────────────────────────────────────────────
if (!function_exists('get_guest_uuid')) {
    function get_guest_uuid(): string
    {
        $raw     = $_COOKIE['user_id'] ?? '';
        $user_id = sanitize_text_field($raw);

        // UUID v4 厳密: xxxxxxxx-xxxx-4xxx-[8|9|a|b]xxx-xxxxxxxxxxxx
        $uuid_v4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        if (!preg_match($uuid_v4, $user_id)) {
            $user_id = wp_generate_uuid4();
            @setcookie('user_id', $user_id, [
                'expires'  => time() + (10 * YEAR_IN_SECONDS),
                'path'     => COOKIEPATH,
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            // 同一リクエスト内で参照できるように上書き
            $_COOKIE['user_id'] = $user_id;
        }
        return $user_id;
    }
}

// フロントへ confirm 用の nonce を配布（任意）
add_action('wp_enqueue_scripts', function () {
    if (!wp_script_is('bbs-js-handle', 'enqueued')) return;
    wp_localize_script('bbs-js-handle', 'bbs_confirm_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_confirm'),
    ]);
});

// ─────────────────────────────────────────────
// API本体: bbs_quest_confirm
//   mode = "show"  : id から内容を返す（プレビュー用）
//   mode = "commit": id を確定（is_confirmed=1）
// ─────────────────────────────────────────────
if (!function_exists('bbs_quest_confirm')) {
    function bbs_quest_confirm()
    {
        global $wpdb;

        // 1) CSRF（confirm 用の別 nonce を使う）CSRF 検証
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bbs_quest_confirm')) {
            wp_send_json_error(['errors' => ['不正なリクエストです（CSRF検出）。']]);
        }

        // 2) 匿名UUID（ログイン判定なし／v4厳密）
        $user_id = get_guest_uuid();

        // 3) レート制限（IP + user_id）10分で20回
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'bbs_rate_' . md5($ip . '|' . $user_id);
        $cnt = (int) get_transient($key);
        if ($cnt >= 20) {
            wp_send_json_error(['errors' => ['短時間に送信が多すぎます。時間をおいて再度お試しください。']]);
        }
        set_transient($key, $cnt + 1, 10 * MINUTE_IN_SECONDS);

        // 3) パラメータ
        $mode = sanitize_text_field($_POST['mode'] ?? 'show'); // 'show' or 'commit'
        $id   = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            wp_send_json_error(['errors' => ['不正なパラメータです。']]);
        }

        $table = $wpdb->prefix . 'sortable';

        // 4) レコード取得（自分のドラフトのみ扱う）
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d AND user_id = %s", $id, $user_id),
            ARRAY_A
        );
        if (!$row) {
            wp_send_json_error(['errors' => ['データが見つからないか、操作権限がありません。']]);
        }

        // 6) 分岐：プレビュー
        if ($mode === 'show') {
            // プレビュー用にそのまま返却
            wp_send_json_success(['data' => $row]);
        }

        // 7) DB保存（相対パスだけ）
        if ($mode === 'commit') {
            if ((int)$row['is_confirmed'] === 1) {
                wp_send_json_error(['errors' => ['すでに確定済みです。']]);
            }
            $ok = $wpdb->update(
                $table,
                ['is_confirmed' => 1, 'confirmed_at' => current_time('mysql', true)],
                ['id' => $id, 'user_id' => $user_id],
                ['%d', '%s'],
                ['%d', '%s']
            );

            if ($ok === false) {
                wp_send_json_error(['errors' => ['確定処理に失敗しました。']]);
            }
            // 更新後の行を返す
            $row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
                ARRAY_A
            );
            wp_send_json_success(['message' => '投稿を確定しました。', 'data' => $row]);
        }

        wp_send_json_error(['errors' => ['不正なモードです。']]);
    }
}

// Ajax フック（submit 用とは完全分離）
add_action('wp_ajax_bbs_quest_confirm',        'bbs_quest_confirm');
add_action('wp_ajax_nopriv_bbs_quest_confirm', 'bbs_quest_confirm');
?>