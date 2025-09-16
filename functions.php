<?php
// ↓ ここから追記
// rel="prev"とrel=“next"表示の削除
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

// WordPressバージョン表示の削除
remove_action('wp_head', 'wp_generator');

// 絵文字表示のための記述削除（絵文字を使用しないとき）
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// アイキャッチ画像の有効化
add_theme_support('post-thumbnails');

// 投稿固定ページを旧式のエディターに戻す
add_filter('gutenberg_can_edit_post_type', '__return_false'); //Gutenbergプラグイン用
add_filter('use_block_editor_for_post', '__return_false'); //WordPressブロックエディター用

add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');        // 既に入っていれば重複OK
    add_image_size('rect', 400, 300, true);      // ← 必要な実寸に調整（トリミング true）
});

//従来のウィジェットエディターに戻す
function example_theme_support()
{
    remove_theme_support('widgets-block-editor');
}
add_action('after_setup_theme', 'example_theme_support');

// ウィジェット追加
if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => 'ウィジェット１',
        'id' => 'widget01',
        'before_widget' => '<div class=”widget”>',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));
}

// 自動成型削除
function my_tiny_mce_before_init($init_array)
{
    global $allowedposttags;
    $init_array['valid_elements'] = '*[*]';
    $init_array['extended_valid_elements'] = '*[*]';
    $init_array['valid_children'] = '+a[' . implode('|', array_keys($allowedposttags)) . ']';
    // $init_array['indent'] = true;
    if (is_page()) {
        $init_array['wpautop'] = false;
        $init_array['force_p_newlines'] = false;
    }
    return $init_array;
}
add_filter('tiny_mce_before_init', 'my_tiny_mce_before_init');

function custom_print_scripts()
{
    if (!is_admin()) {
        //デフォルトjquery削除
        wp_deregister_script('jquery');

        //GoogleCDNから読み込む
        wp_enqueue_script('jquery-js', '//ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
        wp_enqueue_script('archive-js', get_template_directory_uri() . '/js/archive.js');
    }
}
add_action('wp_print_scripts', 'custom_print_scripts');

//ヘッダーにCDNを読み込ませる
function fontawesome_enqueue()
{
    wp_enqueue_script('fontawesome_script', 'https://kit.fontawesome.com/82bcc49272.js');
}
add_action('wp_enqueue_scripts', 'fontawesome_enqueue');

function register_stylesheet()
{
    wp_register_style('reset', get_template_directory_uri() . '/css/destyle.css');
    wp_register_style('style', get_template_directory_uri() . '/css/style.css');
    wp_register_style('base', 'https://use.fontawesome.com/releases/v6.2.0/css/all.css');
}
function add_stylesheet()
{
    register_stylesheet();
    wp_enqueue_style('reset', '', array(), '1.0', false);
    wp_enqueue_style('style', '', array(), '1.0', false);
    wp_enqueue_style('base', '', array(), '1.0', false);
}
add_action('wp_enqueue_scripts', 'add_stylesheet');

/*---- Google Icon ----*/
function add_google_icons()
{
    wp_register_style(
        'googleFonts',
        'https://fonts.googleapis.com/icon?family=Material+Icons'
    );
    wp_enqueue_style('googleFonts');
}
add_action('wp_enqueue_scripts', 'add_google_icons');

//PHPをウィジェット追加
function widget_text_exec_php($widget_text)
{
    if (strpos($widget_text, '<' . '?') !== false) {
        ob_start();
        eval('?>' . $widget_text);
        $widget_text = ob_get_contents();
        ob_end_clean();
    }
    return $widget_text;
}
add_filter('widget_text', 'widget_text_exec_php', 99);

// カスタマイズコメントフォーム
if (!function_exists('custom_comment_form')) {
    function custom_comment_form($args)
    {
        // 「コメントを残す」を削除
        $args['title_reply'] = '';
        //コメント欄の前に表示する文字列の削除　※デフォルトではコメント
        $args['comment_field'] = '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>';
        //「admin としてログイン中。ログアウトしますか ? * が付いている欄は必須項目です」を削除
        $args['logged_in_as'] = '';
        // 「メールアドレスが公開されることはありません」を削除
        $args['comment_notes_before'] = '';
        return $args;
    }
}

add_filter('comment_form_defaults', 'custom_comment_form');
// カスタマイズコメントフォームフィールド
if (!function_exists('custom_comment_form_fields')) {
    function custom_comment_form_fields($arg)
    {
        // コメントからウェブサイトとEmailを削除
        $arg['url'] = '';
        $arg['email'] = '';
        return $arg;
    }
}
add_filter('comment_form_default_fields', 'custom_comment_form_fields');

// ----------------------------------------------------------------------------
// コメント欄の名前が未入力の場合の投稿者名
// ----------------------------------------------------------------------------
function default_author_name($author, $comment_ID, $comment)
{
    if ($author == __('Anonymous')) {
        $author = '名無しさん';
    }

    return $author;
}
add_filter('get_comment_author', 'default_author_name', 10, 3);

// 検索ワードファイルパス
define('SEARCH_WORDS_FILE_PATH', __DIR__ . '/test.csv');


/* 投稿と固定ページ一覧にスラッグの列を追加 */
function add_posts_columns_slug($columns)
{
    $columns['slug'] = 'スラッグ';
    echo '';
    return $columns;
}
add_filter('manage_posts_columns', 'add_posts_columns_slug');
add_filter('manage_pages_columns', 'add_posts_columns_slug');

/* スラッグを表示 */
function custom_posts_columns_slug($column_name, $post_id)
{
    if ($column_name == 'slug') {
        $post = get_post($post_id);
        $slug = $post->post_name;
        echo esc_attr($slug);
    }
}
add_action('manage_posts_custom_column', 'custom_posts_columns_slug', 10, 2);
add_action('manage_pages_custom_column', 'custom_posts_columns_slug', 10, 2);


//アイキャッチを有効化
add_theme_support('post-thumbnails');

//画像サイズ追加
add_image_size('rect', 640, 400, true);

//画像URLからIDを取得
function get_attachment_id_by_url($url)
{
    global $wpdb;
    $sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s";
    preg_match('/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches);
    $post_name = $matches[1];
    return (int)$wpdb->get_var($wpdb->prepare($sql, $post_name));
}

//画像をサムネイルで出力
/** 
 * 安全なサムネイル取得ユーティリティ
 * - 1) アイキャッチ
 * - 2) ブロックの core/image の添付ID
 * - 3) 本文の <img> (DOM解析)
 * - 4) フォールバック
 *
 * @param int|\WP_Post|null $post 投稿ID or Post or null(=current)
 * @param string $fallback_url 見つからないときの代替画像URL（テーマ内 no-img 等）
 * @param string|array $size 画像サイズ（'thumbnail','medium','large','full' など or 配列 [w,h]）
 * @param string $class 出力<img>に付けるクラス
 * @return string HTML（<img …>）
 */
function bbs_first_image_html_or_fallback($post = null, string $fallback_url = '', $size = 'thumbnail', string $class = 'archive-thumbnail'): string
{
    $post = get_post($post);
    if (!$post) return $fallback_url ? sprintf('<img src="%s" alt="" class="%s" />', esc_url($fallback_url), esc_attr($class)) : '';

    // 1) アイキャッチがあれば最優先
    if (has_post_thumbnail($post)) {
        return get_the_post_thumbnail($post, $size, ['class' => $class]);
    }

    // 2) ブロックエディタの core/image から attachment ID を拾う
    if (function_exists('has_blocks') && has_blocks($post)) {
        $blocks = parse_blocks($post->post_content);
        foreach ($blocks as $b) {
            if (($b['blockName'] ?? '') === 'core/image') {
                // ブロック属性に id が入る（添付ID）
                $id = isset($b['attrs']['id']) ? (int)$b['attrs']['id'] : 0;
                if ($id > 0) {
                    $html = wp_get_attachment_image($id, $size, false, ['class' => $class]);
                    if ($html) return $html;
                }
                // id が無い場合は URL を試す
                $url = $b['attrs']['url'] ?? '';
                if ($url) {
                    $att_id = attachment_url_to_postid($url);
                    if ($att_id) {
                        $html = wp_get_attachment_image($att_id, $size, false, ['class' => $class]);
                        if ($html) return $html;
                    }
                    // 添付ID化できなくてもURL直書きで表示
                    return sprintf('<img src="%s" alt="" class="%s" />', esc_url($url), esc_attr($class));
                }
            }
        }
    }

    // 3) 本文を DOM で解析して <img> の src を拾う（先頭1枚）
    $content = get_post_field('post_content', $post);
    if ($content) {
        // 実体参照などで壊れないように
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $content . '</body></html>';
        $dom = new DOMDocument();
        // DOMの警告を抑制（壊れたHTMLでも進める）
        libxml_use_internal_errors(true);
        if ($dom->loadHTML($html)) {
            $imgs = $dom->getElementsByTagName('img');
            if ($imgs->length > 0) {
                // 同一ホスト優先で探す
                $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
                $first_url = '';
                foreach ($imgs as $img) {
                    $src = $img->getAttribute('src');
                    if (!$src) continue;
                    $host = wp_parse_url($src, PHP_URL_HOST);
                    if (!$first_url) $first_url = $src; // 第1候補（何でも）
                    if ($site_host && $host === $site_host) { // 同一ホストが見つかったらそれを採用
                        $first_url = $src;
                        break;
                    }
                }
                if ($first_url) {
                    // 添付IDに変換できるならする
                    $att_id = attachment_url_to_postid($first_url);
                    if ($att_id) {
                        $html = wp_get_attachment_image($att_id, $size, false, ['class' => $class]);
                        if ($html) return $html;
                    }
                    // できなければURL直指定
                    return sprintf('<img src="%s" alt="" class="%s" />', esc_url($first_url), esc_attr($class));
                }
            }
        }
        libxml_clear_errors();
    }

    // 4) 何も無いときはフォールバック
    if ($fallback_url) {
        return sprintf('<img src="%s" alt="" class="%s" />', esc_url($fallback_url), esc_attr($class));
    }
    return '';
}

add_filter('use_block_editor_for_post', '__return_false');
add_theme_support('post-thumbnails');

// アクセス数をカウントする
function set_post_views_days()
{
    $postID = get_the_ID();
    $key = 'pv_count_week';
    set_post_views($postID, $key);
    $key = 'pv_count_3day';
    set_post_views($postID, $key);
}
function set_post_views($postID, $key)
{
    $sum_count = (int) get_post_meta($postID, $key, true);
    update_post_meta($postID, $key, $sum_count + 1);
}

//ボットの判別
function isBot()
{
    $bot_list = array(
        'Googlebot',
        'Yahoo! Slurp',
        'Mediapartners-Google',
        'msnbot',
        'bingbot',
        'MJ12bot',
        'Ezooms',
        'pirst; MSIE 8.0;',
        'Google Web Preview',
        'ia_archiver',
        'Sogou web spider',
        'Googlebot-Mobile',
        'AhrefsBot',
        'YandexBot',
        'Purebot',
        'Baiduspider',
        'UnwindFetchor',
        'TweetmemeBot',
        'MetaURI',
        'PaperLiBot',
        'Showyoubot',
        'JS-Kit',
        'PostRank',
        'Crowsnest',
        'PycURL',
        'bitlybot',
        'Hatena',
        'facebookexternalhit',
        'NINJA bot',
        'YahooCacheSystem',
        'NHN Corp.',
        'Steeler',
        'DoCoMo',
    );
    $is_bot = false;
    foreach ($bot_list as $bot) {
        if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
            $is_bot = true;
            break;
        }
    }
    return $is_bot;
}

function getPostViewsBase($postID, $count_key)
{
    $count = get_post_meta($postID, $count_key, true);
    if ('' == $count) {
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');

        return '0 View';
    }

    return $count . ' Views';
}
function getPostViewsWeek($postID)
{
    return getPostViewsBase($postID, 'pv_count_week');
}
function getPostViews3day($postID)
{
    return getPostViewsBase($postID, 'pv_count_3 day');
}

// 7日毎にアクセスをリセットして人気カテゴリーを表示
//クリック数をカウントする
function category_views_week()
{
    global $cat;
    $categoryID = $cat;
    $key = 'category_count_week';
    category_views($categoryID, $key);
}

function category_views($categoryID, $key)
{
    $sum_count = (int) get_term_meta($categoryID, $key, true);
    update_term_meta($categoryID, $key, $sum_count + 1);
}

function display_maintenance()
{
    echo <<<maintenance
<hr>
ただいまメンテナンス中です。<br>
しばらく時間をおいてアクセスしてください。
<hr>
maintenance;

    ini_set("display_errors", "On");
}

add_action("phpmailer_init", "send_smtp_email");
function send_smtp_email($phpmailer)
{
    $phpmailer->isSMTP();
    $phpmailer->Host       = "mail.last.cfbx.jp";
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 587;
    $phpmailer->SMTPSecure = "tls";
    $phpmailer->Username   = "test@last.cfbx.jp";
    $phpmailer->Password   = "takuya7530";
    $phpmailer->From       = "test@last.cfbx.jp";
    $phpmailer->FromName   = "test";
}

function setToken()
{
    global $_SESSION;
    if (!isset($_SESSION['csrf_token'])) {
        // bin2hexはphp7.0,openssl_random_pseudo_bytesはphp5.3
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    // スクリプト自体の実行を終了
    return;
}
function getToken()
{
    global $_SESSION;
    $token = null;
    if (isset($_SESSION['csrf_token'])) {
        $token = $_SESSION['csrf_token'];
    }

    return $token;
}

function enquiry_sample()
{
    header('Content-type: application/json; charset=UTF-8');
    $result = [];
    $result['name'] = $_POST['name'];
    $result['title'] = $_POST['title'];
    $result['text'] = $_POST['text'];
    $result['stamp'] = $_POST['stamp'];
    echo json_encode($result);
    exit;
}
add_action('wp_ajax_enquiry_sample', 'enquiry_sample');
add_action('wp_ajax_nopriv_enquiry_sample', 'enquiry_sample');

/* 質問掲示板について */
class MAX_LENGTH
{
    public const NAME = 50;
    public const TITLE = 50;
    public const TEXT = 500;
}

class MIN_LENGTH
{
    public const NAME = 0;
    public const TITLE = 5;
    public const TEXT = 1;
}

// --------------------------------------
// ③ 投稿内容のサニタイズ不足（XSS防止）
// strip_tags()/htmlspecialchars の代わりに WordPress 純正の sanitize_text_field() を使い、
// タグを完全に禁止して安全なプレーンテキストを取得します。
function Chk_StrMode($str)
{
    // sanitize_text_field: タグ除去・エンティティ化・前後空白トリムなどを一度に実行
    return sanitize_text_field($str);
}

// 例：functions.php やプラグインメインファイル

// submit 用 nonce 配布
add_action('wp_enqueue_scripts', function () {
    if (!wp_script_is('bbs-js-handle', 'enqueued')) return; // そのJSを読み込む画面だけ
    wp_localize_script('bbs-js-handle', 'bbs_submit_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_submit'),
    ]);
});

// confirm 用 nonce 配布（必要な画面だけ）
add_action('wp_enqueue_scripts', function () {
    if (!wp_script_is('bbs-js-handle', 'enqueued')) return;
    wp_localize_script('bbs-js-handle', 'bbs_confirm_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bbs_quest_confirm'),
    ]);
});

/* 回答タイトルとスタンプ画像なし（回答掲示板) */
function bbs_answer_submit()
{
    // AFTER（submit / confirm 共通）
    $user_id = get_secure_guest_user_id();

    // 親質問の unique_id を POST データから取得し、サニタイズ（安全な文字列に変換）
    $unique_id = sanitize_text_field($_POST['unique_id'] ?? '');

    // 回答本文と名前を POST データから取得（独自の文字列整形関数でサニタイズ）
    $text = sanitize_text_field($_POST['text'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '匿名');

    $error = []; // エラーメッセージ格納用の配列

    // --- エラーチェックは別関数で想定（例：NGワードやURL含有チェック） ---
    // Chk_ngword($name, '・NGワードがあります', $error); など

    // 後でトランジェントに保存する全体データ構造を初期化
    $answer_data = [
        'user_id'   => $user_id,       // 投稿者を識別するための ID
        'unique_id' => $unique_id,     // 親質問のID（親質問の識別子）
        'name'      => $name,          // 投稿者名
        'text'      => $text,          // 回答本文
        'attach'    => [],             // 添付ファイル情報（ファイル名のみ格納）
        'timestamp' => time()          // 投稿時刻
    ];

    // ファイル添付がある場合の処理
    if (!empty($_FILES['attach']['tmp_name'])) {
        $upload_dir = wp_upload_dir();                      // WordPress のアップロードディレクトリ情報を取得
        $basedir = wp_normalize_path($upload['basedir']);        // OS差のあるパス記法を統一

        // ファイルシステムのパス結合は path_join()、末尾は trailingslashit() で正規化
        $attach_dir = trailingslashit(path_join($basedir, 'attach'));

        // ディレクトリが無ければ作成（WordPress推奨のAPI）
        if (! is_dir($tmp_dir)) {
            // 失敗時はエラーログ & クライアントにエラーを返すなどの処理を入れる
            if (! wp_mkdir_p($tmp_dir)) {
                error_log('[BBS] tmpディレクトリの作成に失敗: ' . $tmp_dir);
                wp_send_json_error(['error' => '一時保存領域の作成に失敗しました。権限をご確認ください。']);
            }
            // ついでに .htaccess を置いて実行ファイルブロック（任意・強く推奨）
            $ht = "<FilesMatch \"\\.(php|php5|php7|phps|phtml|pl|py|jsp|asp|aspx|sh|cgi)$\">\nDeny from all\n</FilesMatch>\nOptions -Indexes\n";
            @file_put_contents(trailingslashit($tmp_dir) . '.htaccess', $ht);
        }

        // 各添付ファイルをループ処理
        foreach ($_FILES['attach']['tmp_name'] as $i => $tmp_name) {
            // ファイルが正しくアップロードされ、5MB以下であることを確認
            if (is_uploaded_file($tmp_name) && $_FILES['attach']['size'][$i] <= 5 * 1024 * 1024) {
                $original_name = sanitize_file_name($_FILES['attach']['name'][$i]);
                $mime = mime_content_type($tmp_name);
                $ext = pathinfo($original_name, PATHINFO_EXTENSION);

                // MIMEと拡張子チェック（例：画像、PDF、動画のみ許可）
                // 許可する MIME タイプ（ホワイトリスト）
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'video/mp4'];
                if (!in_array($mime, $allowed_types)) {
                    $error[] = '・許可されていないファイル形式です';
                    continue;
                }

                // 保存用ファイル名を user_id ベースで生成（衝突防止）
                $filename = $user_id . "_{$i}." . $ext;
                $file_path = $tmp_dir . $filename;

                // ファイルを tmp ディレクトリに移動（move_uploaded_file = 安全な移動）
                move_uploaded_file($tmp_name, $file_path);

                // 添付情報（トランジェント）に保存するためにファイル名を保存（本体は tmp に保存済みなのでファイル本体は保存しない）
                $answer_data['attach'][$i] = $filename;
            }
        }
    }

    $result = [];
    $result['error'] = $error;
    // エラーがなければトランジェントに保存（有効期間：10分）
    if (empty($error)) {
        set_transient('bbs_answer_' . $user_id, $answer_data, 10 * MINUTE_IN_SECONDS);
        $result['name'] = $name;
        $result['text'] = $text;
    } else {
        delete_transient('bbs_answer_' . $user_id); // エラーがある場合は念のためトランジェント削除（セキュリティ的にも良い）
    }
    wp_send_json($result);
    exit;
}
add_action('wp_ajax_bbs_answer_submit', 'bbs_answer_submit');
add_action('wp_ajax_nopriv_bbs_answer_submit', 'bbs_answer_submit');

/**
 * 問題キーワードチェック
 */
function Chk_ngword($str, $mes, array &$error)
{
    $ng_words = ['死ね', 'アホ', '殺す', 'バカ'];
    foreach ($ng_words as $ng) {
        if (mb_strpos($str, $ng) !== false) {
            $error[] = $mes;
            break;
        }
    }
}

/**
 * 未入力チェック
 */
function Chk_InputMode($str, $mes, array &$error)
{
    if (trim($str) === '') {
        $error[] = $mes;
    }
}

/**
 * URL混入チェック
 */
function CheckUrl($str, $mes, array &$error)
{
    // URLっぽい文字（:や//）が入っていればエラー
    if (preg_match('/https?:\/\/|www\./i', $str)) {
        $error[] = $mes;
    }
}

/* 回答タイトルとスタンプ画像なし（回答掲示板) */
function bbs_answer_confirm()
{
    // UUID形式かを必ず検証する
    function is_valid_uuid($uuid)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }

    // UUIDであることを検証する
    $user_id = $_COOKIE['user_id'] ?? '';
    if (!is_valid_uuid($user_id)) {
        wp_send_json_error(['message' => '無効なユーザー識別子です']);
        exit;
    }

    // UUIDの発行を自サーバで制御
    if (!isset($_COOKIE['user_id'])) {
        $user_id = wp_generate_uuid4();
        // setcookie('user_id', $user_id, ...);
        $_COOKIE['user_id'] = $user_id;
    }

    // 投稿データを一時保存していたトランジェントキーを作成
    $transient_key = 'bbs_answer_' . $user_id;
    $answer_data = get_transient($transient_key);

    // トランジェントからデータが取得できない場合（期限切れや未投稿）
    if (!$answer_data) {
        wp_send_json(['error' => '投稿データが見つかりません']);
    }

    global $wpdb;

    // 親質問（親投稿）の ID を取得（unique_id → id）
    $sql = "SELECT id FROM {$wpdb->prefix}sortable WHERE unique_id = %s";
    $query = $wpdb->prepare($sql, $answer_data['unique_id']);
    $parent_id = $wpdb->get_var($query);
    if (!$parent_id) {
        wp_send_json(['error' => '親質問が見つかりません']);
    }

    // 回答を sortable テーブルに登録
    $wpdb->insert(
        "{$wpdb->prefix}sortable",
        [
            'parent_id' => $parent_id,                  // 親ID
            'text'      => $answer_data['text'],        // 回答本文
            'name'      => $answer_data['name'],        // 回答者名
            'ip'        => $_SERVER['REMOTE_ADDR'],     // IPアドレス（任意）
            'user_id'   => $user_id                     // 投稿者識別用
        ],
        ['%d', '%s', '%s', '%s', '%s']                  // プレースホルダ（SQL注入防止）
    );

    // 新しく挿入された投稿のIDを取得
    $new_post_id = $wpdb->insert_id;

    // 添付ファイル処理：一時保存（/tmp）→ 正式保存（/attach）
    if (!empty($answer_data['attach'])) {
        $upload_dir = wp_upload_dir();
        $src_dir = $upload_dir['basedir'] . '/tmp/';      // 一時保存ディレクトリ
        $dst_dir = $upload_dir['basedir'] . '/attach/';   // 本保存ディレクトリ

        // 保存先ディレクトリが存在しない場合は作成
        if (!file_exists($dst_dir)) wp_mkdir_p($dst_dir);

        // 新しく投稿されたデータの unique_id を取得（rename用に使う）
        $sql = "SELECT unique_id FROM {$wpdb->prefix}sortable WHERE id = %d";
        $query = $wpdb->prepare($sql, $new_post_id);
        $new_unique_id = $wpdb->get_var($query);

        $filenames = []; // DB更新用のファイル名を記録

        // 添付ファイルごとに処理
        foreach ($answer_data['attach'] as $i => $filename) {
            $src_path = $src_dir . $filename;
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            // ファイル名をユニークにリネーム（例：abcde_1.png, abcde_usericon.jpg）
            $target_name = "{$new_unique_id}_" . ($i == 3 ? 'usericon' : ($i + 1)) . ".$ext";
            $dst_path = $dst_dir . $target_name;

            // 一時ファイルが存在すれば移動（rename = move）
            if (file_exists($src_path)) {
                rename($src_path, $dst_path); // move and rename
                $filenames[$i] = $target_name;
            }
        }

        // 添付ファイル情報を DB に更新（最大3つ + usericon）
        $sql = "UPDATE {$wpdb->prefix}sortable SET attach1=%s, attach2=%s, attach3=%s, usericon=%s WHERE id=%d";
        $query = $wpdb->prepare(
            $sql,
            $filenames[0] ?? '',
            $filenames[1] ?? '',
            $filenames[2] ?? '',
            $filenames[3] ?? '',
            $new_post_id
        );
        $wpdb->query($query);
    }

    // 投稿完了後はトランジェントを削除（セキュリティ＆クリーンアップ）
    delete_transient($transient_key);

    // 成功レスポンスを返す
    wp_send_json(['error' => '回答が投稿されました']);
    exit;
}
add_action('wp_ajax_bbs_answer_confirm', 'bbs_answer_confirm');
add_action('wp_ajax_nopriv_bbs_answer_confirm', 'bbs_answer_confirm');

function bbs_que_list_items()
{
    global $wpdb;
    $count = $_POST['count'];
    $sql = "SELECT * FROM {$wpdb->prefix}sortable WHERE parent_id IS NULL LIMIT %d,10";
    $query = $wpdb->prepare($sql, $count);
    $rows = $wpdb->get_results($query);
    $result = [];
    $result['items'] = [];
    $upload_dir = wp_upload_dir();
    foreach ($rows as $row) {
        if (empty($row->attach1)) {
            $url = '';
            $type = '';
        } else {
            $info = pathinfo($row->attach1);
            $url = $upload_dir['baseurl'] . '/attach/' . $info['basename'];
            $ext = $info['extension'];
            switch ($ext) {
                case 'jpeg':
                case 'png':
                    $type = 'img';
                    break;
                case 'mp4':
                    $type = ''; /* ダミー */
                    break;
                case 'pdf':
                    $type = ''; /* ダミー */
                    break;
                default:
                    break;
            }
        }
        $result['items'][] = ['title' => $row->title, 'img1' => $url, 'type' => $type, 'url' => home_url('質問回答画面?' . $row->unique_id)];
    }
    echo json_encode($result);
    exit;
}
add_action('wp_ajax_bbs_que_list_items', 'bbs_que_list_items');
add_action('wp_ajax_nopriv_bbs_que_list_items', 'bbs_que_list_items');

/* WordPressでJavaScriptファイルを読み込む方法 */
function my_scripts_method()
{
    wp_enqueue_script(
        'custom_script',
        get_template_directory_uri() . '/response.js',
    );
}
add_action('wp_enqueue_scripts', 'my_scripts_method');

// WordPress の「初期化処理（init アクション）」のタイミングで、PHP のセッションがまだ開始されていなければ、session_start() を実行する。
// ログインユーザーでなくても、セッション ID を使って「一意の識別子（unique_id）」を発行・保持できるようにするため。
// 「このユーザーはこの投稿にいいねしたか？」という判定を、$_SESSION['unique_id'] で行っているからです。
/* add_action('init', function () {
    if (!session_id()) {
        session_start();
    }
}); */

/*
add_action('init', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['unique_id'])) {
        $_SESSION['unique_id'] = bin2hex(random_bytes(16)); // ランダムな一意のID
    }
});
*/


// ゲスト識別用 UUID をCookieに保存
// cookie が正しく保存されない → user_id が欠損 → いいね機能が動かない という致命的な問題になるため、WordPress が HTTP ヘッダーを送る「直前」にフックする
// init で setcookie() を使うのはタイミング的に遅すぎるため setcookie() の効果が失われ、ブラウザに Cookie が保存されない
// add_action('init', function () {
add_action('send_headers', function () {
    if (!isset($_COOKIE['user_id'])) {
        $guest_user_id = wp_generate_uuid4();
        setcookie('user_id', $guest_user_id, time() + (10 * YEAR_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
        $_COOKIE['user_id'] = $guest_user_id; // 即時使用できるように
    }
});

// JSファイルの読み込みとAJAX用データの埋め込み
// like.js を読み込み & nonce を渡す（1か所に統一）
function like_enqueue_scripts()
{
    $handle = 'like-script';
    $script_path = get_template_directory() . '/assets/js/like.js';

    wp_enqueue_script(
        $handle,
        get_template_directory_uri() . '/assets/js/like.js',
        [],
        filemtime($script_path), // キャッシュバスティング用
        // filemtime(get_template_directory() . '/assets/js/like.js'),
        true // フッターで読み込み
    );

    wp_localize_script($handle, 'like_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('like_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'like_enqueue_scripts');

// いいね関連の処理を読み込み
// like関係の処理ファイル読み込み（重複除去）
require_once get_template_directory() . '/inc/like-functions.php';
require_once get_template_directory() . '/inc/like-handler.php';

/**
 * 添付IDを安全に画像HTMLへ。ダメならフォールバック画像を返す。
 */
function bbs_safe_attachment_img_or_fallback($attachment_id, $size = 'thumbnail', $attr = [], $fallback_url = '')
{
    $html = '';

    // 妥当性チェック：数値・正のID・存在するMIME
    if (is_numeric($attachment_id) && (int)$attachment_id > 0 && get_post_mime_type($attachment_id)) {
        $html = wp_get_attachment_image((int)$attachment_id, $size, false, $attr);
    }

    if (! $html && $fallback_url) {
        $class = isset($attr['class']) ? esc_attr($attr['class']) : 'noimage';
        $alt   = isset($attr['alt'])   ? esc_attr($attr['alt'])   : esc_attr__('No image', 'your-textdomain');
        $html  = sprintf(
            '<img src="%s" class="%s" alt="%s" />',
            esc_url($fallback_url),
            $class,
            $alt
        );
    }
    return $html;
}

// functions.php
if (!function_exists('bbs_safe_attachment_img_or_fallback')) {
    function bbs_safe_attachment_img_or_fallback($attachment_id, $size = 'thumbnail', $attrs = [], $fallback_url = '')
    {
        $attachment_id = (int)$attachment_id;
        if ($attachment_id > 0 && get_post($attachment_id) && get_post_mime_type($attachment_id)) {
            return wp_get_attachment_image($attachment_id, $size, false, $attrs);
        }
        if ($fallback_url) {
            $attr_html = '';
            foreach ((array)$attrs as $k => $v) {
                $k = esc_attr($k);
                $v = esc_attr($v);
                $attr_html .= " {$k}=\"{$v}\"";
            }
            $fallback_url = esc_url($fallback_url);
            return "<img src=\"{$fallback_url}\"{$attr_html} />";
        }
        return '';
    }
}

// 代表画像を使うならこれが必要
add_theme_support('post-thumbnails');

// あなたが使っているサイズ名 'rect' を登録する（値は用途に合わせて）
if (function_exists('add_image_size')) {
    add_image_size('rect', 400, 300, true); // 例：400x300 のハードクロップ
}
