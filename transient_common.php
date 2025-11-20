<?php

/**
 * transient_common.php
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
            'video/mp4'        => ['mp4'],   // 動画は極力 mp4 に絞るのが現実的
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

// ★ 動画・PDF 用の個別上限（未定義ならデフォルト値を入れる）
if (!defined('BBS_MAX_PER_FILE_VIDEO')) define('BBS_MAX_PER_FILE_VIDEO', 5 * 1024 * 1024); // 動画も 5MB に揃える
if (!defined('BBS_MAX_PER_FILE_PDF'))   define('BBS_MAX_PER_FILE_PDF',   5 * 1024 * 1024); // PDF も 5MB（お好みで）

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

// transient_common.php（または functions.php）に追加
if (!function_exists('bbs_first_image_from_content')) {
    /**
     * HTML本文から「最初の <img>」の情報を安全に抽出する。
     * 返り値: ['id'=>int|null,'src'=>string|null,'html'=>string|null]
     */
    function bbs_first_image_from_content(string $html): array
    {
        // ブロックエディタ/ショートコードの乱れを避けるためWPのパーサを利用
        // 添付IDが付いた画像ならそれを優先 / 直リンクのみでも拾える
        $id = null;
        $src = null;
        $imgHtml = null;

        // 1) ギャラリーや img ショートコード → 添付IDを優先抽出
        $shortcodes = [];
        preg_match_all('/\[(?:gallery|caption|audio|video|img)[^\]]*\]/i', $html, $shortcodes);
        if (!empty($shortcodes[0])) {
            // gallery ids="1,2" 等の最初のIDを拾う
            if (preg_match('/ids\s*=\s*"(\d+(?:\s*,\s*\d+)*)"/i', $shortcodes[0][0], $m)) {
                $firstId = (int)trim(explode(',', $m[1])[0]);
                if ($firstId > 0) {
                    $id  = $firstId;
                    $imgHtml = wp_get_attachment_image($id, 'medium', false, ['class' => 'archive-thumbnail']);
                    if ($imgHtml) {
                        $src = wp_get_attachment_image_url($id, 'medium');
                        return ['id' => $id, 'src' => $src, 'html' => $imgHtml];
                    }
                }
            }
        }

        // 2) 投稿本文内の <img> を DOM で解析（正規表現でのスクレイピングは避ける）
        if (function_exists('wp_kses')) {
            // 許可タグだけに狭めた簡易DOM化用HTMLを用意
            $safe = wp_kses($html, ['img' => ['src' => [], 'srcset' => [], 'sizes' => [], 'alt' => [], 'class' => [], 'id' => [], 'width' => [], 'height' => []]]);
        } else {
            $safe = $html;
        }

        // 最初の <img ...> を拾う
        if (preg_match('/<img\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $safe, $m)) {
            $src = esc_url_raw($m[1]);
            // data-id="123" のような形があれば拾う
            if (preg_match('/\bdata-attachment-id=["\'](\d+)["\']/', $m[0], $mm)) {
                $id = (int)$mm[1];
            }
            // 直接IDがなくても OK
            $imgHtml = wp_kses($m[0], ['img' => ['src' => [], 'srcset' => [], 'sizes' => [], 'alt' => [], 'class' => [], 'id' => [], 'width' => [], 'height' => []]]);
            return ['id' => $id, 'src' => $src, 'html' => $imgHtml];
        }

        return ['id' => null, 'src' => null, 'html' => null];
    }
}

if (!function_exists('bbs_first_image_html_or_fallback')) {
    /**
     * 最初の画像の <img> HTML を返す。見つからない場合は任意の代替画像を返す。
     */
    function bbs_first_image_html_or_fallback(string $html, string $fallback_url, string $size = 'medium'): string
    {
        $r = bbs_first_image_from_content($html);
        if ($r['id']) {
            $img = wp_get_attachment_image($r['id'], $size, false, ['class' => 'archive-thumbnail']);
            if ($img) return $img;
        }
        if ($r['src']) {
            $esc = esc_url($r['src']);
            return '<img class="archive-thumbnail" src="' . $esc . '" alt="" />';
        }
        return '<img class="archive-thumbnail" src="' . esc_url($fallback_url) . '" alt="No image" />';
    }
}

// すでにあれば再定義しない
if (!function_exists('bbs_rate_guard')) {
    /**
     * IP + user_id でレート制限（$window 秒で最大 $limit 回）
     * 超過時は wp_send_json_error() で即終了。
     */
    function bbs_rate_guard(string $user_id, int $limit = 20, int $window = 600): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';          // 取得不可時はダミー
        $key = 'bbs_rate_' . md5($ip . '|' . $user_id);      // 一意キー
        $cnt = (int) get_transient($key);                    // 現在カウント
        if ($cnt >= $limit) {                                // 閾値を超えたら拒否
            wp_send_json_error(['errors' => ['短時間に送信が多すぎます。時間をおいて再度お試しください。']]);
        }
        set_transient($key, $cnt + 1, $window);              // 10分維持
    }
}

// 種類別の1ファイル上限（バイト）
if (!defined('BBS_MAX_PER_FILE_IMAGE')) define('BBS_MAX_PER_FILE_IMAGE', 5  * 1024 * 1024); // 画像 5MB
if (!defined('BBS_MAX_PER_FILE_VIDEO')) define('BBS_MAX_PER_FILE_VIDEO', 20 * 1024 * 1024); // 動画 20MB
if (!defined('BBS_MAX_PER_FILE_PDF'))   define('BBS_MAX_PER_FILE_PDF',   10 * 1024 * 1024); // PDF 10MB

// 合計サイズ（動画を許すならやや大きめ推奨）
if (!defined('BBS_MAX_TOTAL')) define('BBS_MAX_TOTAL', 50 * 1024 * 1024); // 例: 50MB

// 既存の BBS_MAX_PER_FILE があれば “未知MIMEのフォールバック” に使う
if (!defined('BBS_MAX_PER_FILE')) define('BBS_MAX_PER_FILE', 5 * 1024 * 1024); // 従来の一律上限（保険）

// --- ① 一時ディレクトリを“非公開”で初期化 ---
// ===== 1) 一時ディレクトリの“非公開”初期化 =====
if (!defined('BBS_TMP_SUBDIR')) {
    define('BBS_TMP_SUBDIR', 'tmp'); // /uploads/tmp
}

if (!function_exists('bbs_tmp_dir')) {
    function bbs_tmp_dir(): string
    {
        $up  = wp_upload_dir();
        $dir = trailingslashit($up['basedir']) . BBS_TMP_SUBDIR;
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }
}

if (!function_exists('bbs_tmp_bootstrap')) {
    function bbs_tmp_bootstrap(): void
    {
        $dir = bbs_tmp_dir();

        // 1) index.html（空ファイル）を置く
        $index = trailingslashit($dir) . 'index.html';
        if (!file_exists($index)) {
            @file_put_contents($index, "<!doctype html><title></title>");
        }

        // 2) .htaccess：直リンク禁止（LiteSpeed/Apache）
        $ht = trailingslashit($dir) . '.htaccess';
        if (!file_exists($ht)) {
            $rules = <<<HT
# BBS tmp: deny direct access
Options -Indexes
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
  Deny from all
</IfModule>

# 念のためキャッシュ禁止（互換）
<IfModule mod_headers.c>
  Header set Cache-Control "no-store, no-cache, must-revalidate"
  Header set Pragma "no-cache"
  Header set Expires "0"
</IfModule>
HT;
            @file_put_contents($ht, $rules);
        }
    }
}

// WordPress 起動ごとに存在を担保（軽い処理）
add_action('init', 'bbs_tmp_bootstrap');

// ===============================
// ② 一時ファイルの安全配信エンドポイント（直リンク禁止）
//    /wp-admin/admin-ajax.php?action=bbs_tmp_get&draft_id=...&file=...&nonce=...
// ===============================
add_action('wp_ajax_bbs_tmp_get', 'bbs_tmp_get');          // ログイン時
add_action('wp_ajax_nopriv_bbs_tmp_get', 'bbs_tmp_get');   // 未ログイン時も許可するなら

function bbs_tmp_get()
{
    // ---- 1) パラメータ取得＆最低限のバリデーション ----
    $nonce    = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
    $draft_id = isset($_REQUEST['draft_id']) ? (string) $_REQUEST['draft_id'] : '';
    $file_in  = isset($_REQUEST['file']) ? (string) $_REQUEST['file'] : '';

    // Nonce 検証（JS から送っているものと同じ action 名に合わせる）
    // 例：wp_localize_script('bbs-confirm', 'bbs_confirm_vars', ['nonce' => wp_create_nonce('bbs_confirm_nonce')]);
    if (!wp_verify_nonce($nonce, 'bbs_confirm_nonce')) {
        status_header(403);
        exit('Invalid nonce');
    }

    // draft_id は [a-zA-Z0-9_-] 程度に制限（UUID想定ならハイフンOK）
    if ($draft_id === '' || !preg_match('/^[A-Za-z0-9_-]{6,64}$/', $draft_id)) {
        status_header(400);
        exit('Bad draft_id');
    }

    // file は basename 化し、許可文字のみに制限（拡張子はあとでMIMEでも見る）
    $file = basename($file_in);
    if ($file === '' || !preg_match('/^[A-Za-z0-9._-]{1,200}$/', $file)) {
        status_header(400);
        exit('Bad file name');
    }

    // ---- 2) この draft_id に紐づく「許可ファイル」か検証 ----
    // 保存先はあなたの実装に合わせて：
    //   - transient("bbs_tmp_files_$draft_id")
    //   - get_post_meta($draft_id, 'bbs_tmp_files', true)
    //   - オプション/カスタムテーブル等
    $files = get_transient("bbs_tmp_files_$draft_id");
    if (!is_array($files) || empty($files)) {
        // フォールバック（保存先が post_meta の場合など）
        $meta = get_post_meta($draft_id, 'bbs_tmp_files', true);
        if (is_array($meta)) $files = $meta;
    }
    if (!is_array($files) || !in_array($file, $files, true)) {
        status_header(403);
        exit('Not allowed');
    }

    // ---- 3) 実ファイルパスの決定（必ず tmp 直下へ閉じ込める）----
    $upload_dir = wp_upload_dir();
    $tmp_dir = trailingslashit($upload_dir['basedir']) . 'tmp/';
    $base = wp_normalize_path($tmp_dir);
    $path = wp_normalize_path($tmp_dir . $file);

    // パストラバーサル防止：$path が $base 配下であることを厳密チェック
    if (strpos($path, $base) !== 0 || !file_exists($path)) {
        status_header(404);
        exit('Not found');
    }

    // ---- 4) MIME 判定＆許可種別チェック ----
    // まず finfo（なければ wp_check_filetype）
    $mime = '';
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $mime = (string) finfo_file($f, $path);
            finfo_close($f);
        }
    }
    if ($mime === '') {
        $ft = wp_check_filetype($path);
        if (!empty($ft['type'])) $mime = $ft['type'];
    }

    // 許可リスト（必要に応じて拡張）
    $allowed_mime = array('image/jpeg', 'image/png', 'video/mp4', 'application/pdf');
    if (!in_array($mime, $allowed_mime, true)) {
        status_header(403);
        exit('Not allowed type');
    }

    // ---- 5) 配信ヘッダ（直リンク禁止のため PHP から出力）----
    // ここではキャッシュ短め＋nosniff を推奨
    @set_time_limit(0);
    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $mime);
    header('Cache-Control: private, max-age=60'); // 必要に応じて調整
    header('Accept-Ranges: bytes');

    $size = filesize($path);
    $range = null;

    // ---- 6) 簡易 Range 対応（動画のシークに必要）----
    if (isset($_SERVER['HTTP_RANGE'])) {
        // 例: bytes=12345- または bytes=12345-67890
        if (preg_match('/bytes=(\d+)-(\d*)/i', $_SERVER['HTTP_RANGE'], $m)) {
            $start = (int)$m[1];
            $end   = ($m[2] !== '' ? (int)$m[2] : ($size - 1));
            if ($start <= $end && $end < $size) {
                $range = array($start, $end);
            }
        }
    }

    if ($range) {
        list($start, $end) = $range;
        $length = $end - $start + 1;

        status_header(206); // Partial Content
        header("Content-Range: bytes $start-$end/$size");
        header('Content-Length: ' . $length);

        $fp = fopen($path, 'rb');
        if ($fp === false) {
            status_header(500);
            exit('Read error');
        }
        fseek($fp, $start);
        $buf = 8192;
        while (!feof($fp) && $length > 0) {
            $read = ($length > $buf) ? $buf : $length;
            $out = fread($fp, $read);
            if ($out === false) break;
            echo $out;
            $length -= strlen($out);
            // 出力バッファを適宜フラッシュ
            if (function_exists('fastcgi_finish_request') === false) {
                @ob_flush();
                flush();
            }
        }
        fclose($fp);
        exit;
    } else {
        // Range なし：全体配信
        header('Content-Length: ' . $size);
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            status_header(500);
            exit('Read error');
        }
        while (!feof($fp)) {
            $out = fread($fp, 8192);
            if ($out === false) break;
            echo $out;
            if (function_exists('fastcgi_finish_request') === false) {
                @ob_flush();
                flush();
            }
        }
        fclose($fp);
        exit;
    }
}

// ====== 一時ファイルの自動掃除（Cron登録） ======

// 1日1回のイベントを登録（重複登録を防ぐ）
function bbs_register_cleanup_cron()
{
    if (!wp_next_scheduled('bbs_tmp_cleanup_event')) {
        wp_schedule_event(time(), 'daily', 'bbs_tmp_cleanup_event');
    }
}
add_action('wp', 'bbs_register_cleanup_cron');

// 24時間前のファイルを削除
function bbs_run_tmp_cleanup()
{
    $dir = trailingslashit(BBS_TMP_DIR);  // あなたの定義済みディレクトリ
    if (!is_dir($dir)) return;

    $files = glob($dir . '*');
    if (!$files) return;

    $now = time();
    $limit = 24 * 60 * 60; // 24時間

    foreach ($files as $f) {
        if (!is_file($f)) continue;
        if ($now - filemtime($f) > $limit) {
            @unlink($f);
        }
    }
}
add_action('bbs_tmp_cleanup_event', 'bbs_run_tmp_cleanup');

// --------------------------------------------------
// 一時ファイル安全配信エンドポイント（admin-ajax.php 経由）
// --------------------------------------------------
if (!function_exists('bbs_tmp_get')) {
    function bbs_tmp_get()
    {
        // draft_id（= 一時保存中の投稿ID）
        $draft_id = isset($_GET['draft']) ? (string) $_GET['draft'] : '';
        $name     = isset($_GET['name'])  ? (string) $_GET['name']  : '';

        // 最低限のバリデーション
        if ($draft_id === '' || $name === '') {
            status_header(400);
            wp_die('Bad Request', '', ['response' => 400]);
        }

        // ノンスチェック（JS側で作ったものと同じルール）
        $nonce = isset($_GET['_nonce']) ? (string) $_GET['_nonce'] : '';
        if (!wp_verify_nonce($nonce, 'bbs_tmp_get_' . $draft_id)) {
            status_header(403);
            wp_die('Forbidden', '', ['response' => 403]);
        }

        // 一時ディレクトリのパス（あなたの既存ヘルパーに合わせてください）
        $tmp_dir = bbs_tmp_dir(); // 例：/home/xxxx/wp-content/uploads/bbstmp など

        // ディレクトリトラバーサル対策（../ を潰す）
        $name = wp_basename($name);

        $file = trailingslashit($tmp_dir) . $name;

        if (!file_exists($file) || !is_file($file)) {
            status_header(404);
            wp_die('Not Found', '', ['response' => 404]);
        }

        // MIME を安全に推測
        $mime = wp_check_filetype($file);
        $mime_type = $mime['type'] ?: 'application/octet-stream';

        // キャッシュ系ヘッダ（お好みで）
        nocache_headers();
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file));
        // inline 表示にしたいので Content-Disposition は付けない or inline に
        // header('Content-Disposition: inline; filename="' . esc_attr($name) . '"');

        // 出力
        readfile($file);
        exit;
    }
}

// ログイン・未ログイン両方から呼べるようにする
add_action('wp_ajax_bbs_tmp_get',        'bbs_tmp_get');
add_action('wp_ajax_nopriv_bbs_tmp_get', 'bbs_tmp_get');
