<?php

/**
 * bbs_common.php
 * 共有ヘルパー（WordPress前提）
 *
 * - 匿名UUID（Cookieベース／UUID v4 厳密検証 & 安全属性）
 * - アップロード許可MIMEマップ
 * - サイズ・寸法などの上限定数
 * - 保存先ディレクトリ作成（/wp-content/uploads/bbs/YYYY/MM/）
 * - 絶対パス → uploads基準 相対パス変換
 * - 文字列トリム（半角/全角/制御空白）
 * - スタンプ許可リスト
 * - レート制限（IP + user_id, transientベース）
 */

// 直接アクセス防止（テーマ/プラグインの読み込み経由のみ）
if (!defined('ABSPATH')) {
    exit;
}

/* ===========================================================
 * 1) 匿名UUID（Cookie user_id）
 * =========================================================== */
if (!function_exists('get_guest_uuid')) {                                  // 多重定義防止
    /**
     * セキュアな匿名ユーザーUUID(v4)を取得。
     * - 未設定/不正なら新規発行し、Secure/HttpOnly/SameSite 属性付きでCookie保存
     * - このIDは「識別」にのみ使用（権限判定には絶対に使わない）
     */
    function get_guest_uuid(): string
    {
        // 既存の user_id Cookie を流用（キーを変えない方が互換的）
        $raw     = $_COOKIE['user_id'] ?? '';                               // 既存Cookie（未設定なら空）
        $user_id = sanitize_text_field($raw);                                // サニタイズ

        // UUID v4 厳密：xxxxxxxx-xxxx-4xxx-[8|9|a|b]xxx-xxxxxxxxxxxx
        $uuid_v4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        // 無効なら新規発行
        if (!preg_match($uuid_v4, $user_id)) {                              // 無効/未設定なら
            $user_id = wp_generate_uuid4();                                 // 新規発行
            @setcookie('user_id', $user_id, [                                // 安全属性付きで保存（PHP 7.3+）
                'expires'  => time() + (10 * YEAR_IN_SECONDS),              // 10年（用途に応じて調整可）
                'path'     => COOKIEPATH,                                   // WP定数
                'domain'   => COOKIE_DOMAIN,                                // WP定数
                'secure'   => is_ssl(),                                     // HTTPSのみ送信
                'httponly' => true,                                         // JSから読めない
                'samesite' => 'Lax',                                        // CSRF軽減
            ]);
            // 同一リクエスト内で参照可能に
            $_COOKIE['user_id'] = $user_id;                                 // 同一リクエスト内でも参照可能に
        }
        return $user_id;                                                    // 以降の識別子として利用（権限制御には使わない）
    }
}

/* ===========================================================
 * 2) アップロード許可（MIME → 許可拡張子の配列）
 * =========================================================== */
if (!function_exists('bbs_allowed_upload_map')) {                           // 多重定義防止
    function bbs_allowed_upload_map(): array                                // MIME→許可拡張子の白リスト
    {
        return [
            'image/jpeg'       => ['jpg', 'jpeg'],
            'image/png'        => ['png'],
            // 'image/gif'     => ['gif'], // 使う場合のみ有効化
            'application/pdf'  => ['pdf'],
            'video/mp4'        => ['mp4'],
        ];
    }
}

/* ===========================================================
 * 3) 上限定数（必要に応じて調整）
 * =========================================================== */
if (!defined('BBS_MAX_FILES'))     define('BBS_MAX_FILES', 4);             // 最大4ファイル
if (!defined('BBS_MAX_PER_FILE'))  define('BBS_MAX_PER_FILE', 5 * 1024 * 1024); // 1枚5MB
if (!defined('BBS_MAX_TOTAL'))     define('BBS_MAX_TOTAL',   20 * 1024 * 1024); // 合計20MB
if (!defined('BBS_IMG_MAX_W'))     define('BBS_IMG_MAX_W',   6000);        // 画像最大幅
if (!defined('BBS_IMG_MAX_H'))     define('BBS_IMG_MAX_H',   6000);        // 画像最大高

/* ===========================================================
 * 4) 保存先ディレクトリ（/uploads/bbs/YYYY/MM/）
 * =========================================================== */
if (!function_exists('bbs_make_final_dir')) {                               // 多重定義防止
    function bbs_make_final_dir(): string                                    // 保存先の絶対パスを返す
    {
        $uploads = wp_upload_dir();                                         // ['basedir' => 物理パス, 'baseurl' => URL, ...]
        $year    = date_i18n('Y');                                          // 例: 2025
        $month   = date_i18n('m');                                          // 例: 09
        $base    = trailingslashit($uploads['basedir']);                    // .../wp-content/uploads/
        $dir     = $base . "bbs/{$year}/{$month}/";                         // .../uploads/bbs/YYYY/MM/
        if (!file_exists($dir)) {                                           // 無ければ作成
            wp_mkdir_p($dir);                                               // 親も含め安全に作成
        }
        return $dir;                                                        // 絶対パス
    }
}

/* ===========================================================
 * 5) 絶対パス → uploads基準の相対パス（DB保存用）
 * =========================================================== */
if (!function_exists('bbs_to_uploads_relative')) {                           // 多重定義防止
    function bbs_to_uploads_relative(string $abs): string                     // 例: .../uploads/bbs/2025/09/x.jpg → bbs/2025/09/x.jpg
    {
        $uploads = wp_upload_dir();                                          // アップロード基底
        $base    = wp_normalize_path(trailingslashit($uploads['basedir']));  // 正規化 + 末尾スラッシュ
        $absN    = wp_normalize_path($abs);                                  // 入力も正規化

        if (strpos($absN, $base) === 0) {                                    // uploads配下なら先頭一致
            return ltrim(substr($absN, strlen($base)), '/');                 // uploads以降（先頭/は念のため除去）
        }
        return $absN;                                                        // フォールバック：相対化できなければ絶対パスのまま
    }
}

/* ===========================================================
 * 6) 文字列トリム（半角/全角/制御空白）
 * =========================================================== */
if (!function_exists('bbs_trim')) {                    // 同名関数の二重定義を避ける
    function bbs_trim(string $s): string               // 文字列を受け取り、文字列を返す
    {
        // 前後の水平(\h)/垂直(\v)空白 と 全角空白(U+3000) を正規表現で除去する
        // - \h : 半角スペースやタブなどの「水平方向」の空白
        // - \v : 垂直タブ・改行などの「垂直方向」の空白
        // - \x{3000} : 全角スペース（日本語入力でよく混ざる）
        // - 先頭(^)と末尾($)側をそれぞれ削る2パターンを OR(|) で指定
        // - 'u' フラグで正規表現を UTF-8 として扱う（全角文字を誤認しない）
        return preg_replace('/^[\h\v\x{3000}]+|[\h\v\x{3000}]+$/u', '', $s);
    }
}

/* ===========================================================
 * 7) スタンプ白リスト
 * =========================================================== */
// bbs_common.php
if (!function_exists('bbs_allowed_stamps')) {
    function bbs_allowed_stamps(): array
    {
        // ← 1..8 に拡張
        return [1, 2, 3, 4, 5, 6, 7, 8];
    }
}

/* ===========================================================
 * 8) レート制限（IP + user_id）
 *    - 既定: 10分で20回（LIMIT=20, WINDOW=600sec）
 *    - オーバー時は wp_send_json_error() で即終了（共通挙動）
 * =========================================================== */
if (!function_exists('bbs_rate_guard')) {
    function bbs_rate_guard(string $user_id, int $limit = 20, int $window = 600): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'bbs_rate_' . md5($ip . '|' . $user_id);
        $cnt = (int) get_transient($key);
        if ($cnt >= $limit) {
            wp_send_json_error(['errors' => ['短時間に送信が多すぎます。時間をおいて再度お試しください。']]);
        }
        set_transient($key, $cnt + 1, $window);
    }
}

/* ===========================================================
 * 9) Nonce 検証ユーティリティ（POST: nonce）
 * =========================================================== */
if (!function_exists('bbs_require_nonce')) {
    function bbs_require_nonce(string $action): void
    {
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $action)) {
            wp_send_json_error(['errors' => ['不正なリクエストです（CSRF検出）。']]);
        }
    }
}
