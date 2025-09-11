<?php

/**
 * bbs_common.php
 * 共通ヘルパー群（WordPress 前提）
 * - 匿名UUID (Cookie) の安全取得
 * - アップロード許可MIME
 * - 上限値（ファイル数/サイズ/画像寸法）
 * - 一時保存先 (/uploads/tmp)・最終保存先 (/uploads/attach) の作成
 * - 絶対パス→uploads基準の相対パス変換
 * - 文字列トリム（半角/全角/制御空白）
 * - スタンプ白リスト（1..8）
 * - レート制限 (transient)
 * - Nonce 検証ユーティリティ
 */

// [001] 直接アクセス防止（テーマ/プラグイン経由の読み込みのみ許可）
if (!defined('ABSPATH')) {
    exit;
}

// [010] セキュアな匿名UUID(v4)を Cookie ベースで取得
if (!function_exists('get_guest_uuid')) {
    function get_guest_uuid(): string
    {
        // [011] 既存Cookieを読み取り、存在しない/不正な場合は再発行
        $raw     = $_COOKIE['user_id'] ?? '';
        $user_id = sanitize_text_field($raw);

        // [012] UUID v4 の厳密パターン（xxxxxxxx-xxxx-4xxx-[8|9|a|b]xxx-xxxxxxxxxxxx）
        $uuid_v4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        // [013] 不一致なら新規発行して安全属性付きで Cookie セット
        if (!preg_match($uuid_v4, $user_id)) {
            $user_id = wp_generate_uuid4();
            @setcookie('user_id', $user_id, [
                'expires'  => time() + (10 * YEAR_IN_SECONDS), // [014] 長期識別（用途に応じて短縮可）
                'path'     => COOKIEPATH,                      // [015] WP 定数
                'domain'   => COOKIE_DOMAIN,                   // [016] WP 定数
                'secure'   => is_ssl(),                        // [017] HTTPS のみ送信
                'httponly' => true,                            // [018] JS から参照不可に
                'samesite' => 'Lax',                           // [019] CSRF 低減
            ]);
            // [020] 同一リクエスト内で参照可能にするため、$_COOKIE を上書き
            $_COOKIE['user_id'] = $user_id;
        }
        // [021] 権限判定には使わず、「識別」にのみ使用すること
        return $user_id;
    }
}

// [030] アップロード許可（実MIME → 許可拡張子配列）
if (!function_exists('bbs_allowed_upload_map')) {
    function bbs_allowed_upload_map(): array
    {
        return [
            'image/jpeg'      => ['jpg', 'jpeg'], // [031] JPG
            'image/png'       => ['png'],         // [032] PNG
            // 'image/gif'    => ['gif'],         // [033] GIFを使う場合はコメント解除
            'application/pdf' => ['pdf'],         // [034] PDF
            'video/mp4'       => ['mp4'],         // [035] MP4
        ];
    }
}

// [040] 上限定数（必要に応じてテーマ/プラグイン側で define しても良い）
if (!defined('BBS_MAX_FILES'))    define('BBS_MAX_FILES', 4);             // [041] 最大4ファイル
if (!defined('BBS_MAX_PER_FILE')) define('BBS_MAX_PER_FILE', 5 * 1024 * 1024); // [042] 1ファイル5MB
if (!defined('BBS_MAX_TOTAL'))    define('BBS_MAX_TOTAL',   20 * 1024 * 1024); // [043] 合計20MB
if (!defined('BBS_IMG_MAX_W'))    define('BBS_IMG_MAX_W',   6000);        // [044] 最大幅(px)
if (!defined('BBS_IMG_MAX_H'))    define('BBS_IMG_MAX_H',   6000);        // [045] 最大高(px)

// [050] 一時保存先 (/wp-content/uploads/tmp/) の絶対パスを返す（無ければ作成）
if (!function_exists('bbs_tmp_dir')) {
    function bbs_tmp_dir(): string
    {
        $uploads = wp_upload_dir();                                   // [051] WP の uploads 情報
        $dir     = trailingslashit($uploads['basedir']) . 'tmp/';     // [052] 物理パス
        if (!file_exists($dir)) wp_mkdir_p($dir);                     // [053] 無ければ作成
        return $dir;                                                  // [054] 絶対パス
    }
}

// [060] 最終保存先 (/wp-content/uploads/attach/) の絶対パス（無ければ作成）
if (!function_exists('bbs_attach_dir')) {
    function bbs_attach_dir(): string
    {
        $uploads = wp_upload_dir();                                      // [061] WP の uploads 情報
        $dir     = trailingslashit($uploads['basedir']) . 'attach/';     // [062] 旧来の attach 配下
        if (!file_exists($dir)) wp_mkdir_p($dir);                        // [063] 無ければ作成
        return $dir;                                                     // [064] 絶対パス
    }
}

// [070] 絶対パス → uploads 基準の相対パス（DB保存用などに）
if (!function_exists('bbs_to_uploads_relative')) {
    function bbs_to_uploads_relative(string $abs): string
    {
        $uploads = wp_upload_dir();                                           // [071] ベースディレクトリ
        $base    = wp_normalize_path(trailingslashit($uploads['basedir']));   // [072] 正規化
        $absN    = wp_normalize_path($abs);                                   // [073] 入力も正規化
        if (strpos($absN, $base) === 0) {                                     // [074] uploads配下なら
            return ltrim(substr($absN, strlen($base)), '/');                  // [075] 先頭スラッシュ除去
        }
        return $absN;                                                         // [076] 相対にできない場合はそのまま返す
    }
}

// [080] 文字列の前後トリム（半角/全角/制御空白を安全に削除）
if (!function_exists('bbs_trim')) {
    function bbs_trim(string $s): string
    {
        // [081] ^ と $ で先頭末尾を指定、\h(水平空白) \v(垂直空白) 全角空白(\x{3000}) を削除
        return preg_replace('/^[\\h\\v\\x{3000}]+|[\\h\\v\\x{3000}]+$/u', '', $s);
    }
}

// [090] スタンプ白リスト（UIに合わせ 1..8）
if (!function_exists('bbs_allowed_stamps')) {
    function bbs_allowed_stamps(): array
    {
        return [1, 2, 3, 4, 5, 6, 7, 8]; // [091] 必要に応じて増減
    }
}

// [100] レート制限ガード（共通推奨版）
if (!function_exists('bbs_rate_guard')) {
    /**
     * bbs_rate_guard
     * @param string $user_id  匿名UUID
     * @param int    $limit    許可回数（デフォルト20）
     * @param int    $window   窓の長さ（秒、デフォルト600=10分）
     */
    function bbs_rate_guard(string $user_id, int $limit = 20, int $window = 600): void
    {
        // [101] IP + user_id でキーを構成（匿名でもある程度の粒度で抑制）
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'bbs_rate_' . md5($ip . '|' . $user_id);
        // [102] 現在カウントを取得（未設定なら 0）
        $cnt = (int) get_transient($key);
        // [103] 閾値を超過していれば即エラー応答
        if ($cnt >= $limit) {
            wp_send_json_error(['errors' => ['短時間に送信が多すぎます。時間をおいて再度お試しください。']]);
        }
        // [104] カウント +1 し、ウィンドウを更新
        set_transient($key, $cnt + 1, $window);
    }
}

// [110] Nonce 検証（POST に 'nonce' がある前提）
if (!function_exists('bbs_require_nonce')) {
    function bbs_require_nonce(string $action): void
    {
        // [111] Nonce 未指定 or 検証失敗なら即エラー
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $action)) {
            wp_send_json_error(['errors' => ['不正なリクエストです（CSRF検出）。']]);
        }
    }
}
