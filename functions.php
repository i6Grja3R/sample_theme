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
function catch_that_image()
{
    global $post;
    $first_img = '';
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    $first_img_src = $matches[1][0];
    $attachment_id = get_attachment_id_by_url($first_img_src);
    $first_img = wp_get_attachment_image($attachment_id, 'rect', false, array('class' => 'archive-thumbnail'));
    if (empty($first_img)) {
        $first_img = '<img class="attachment_post_thumbnail" src="' . get_stylesheet_directory_uri() . '/assets/img/common/no-img.jpg" alt="No image" />';
    }
    return $first_img;
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

function bbs_quest_submit()
{
    session_start();
    $text = $_POST['text'];
    $name = $_POST['name'];
    $title = $_POST['title'];
    $stamp = $_POST['stamp'];
    $name = Chk_StrMode($name);
    $title = Chk_StrMode($title);
    $text = Chk_StrMode($text);
    Chk_ngword($name, '・NGワードが入力されています。', $error);
    Chk_ngword($title, '・NGワードが入力されています。', $error);
    Chk_ngword($text, '・NGワードが入力されています。', $error);
    if ($name == "") {
        $name = "匿名";
    } // 追加
    Chk_InputMode($title, '・質問タイトルをご記入ください。', $error);
    Chk_InputMode($text, '・質問文をご記入ください。', $error);
    Chk_InputMode($stamp, '・スタンプを選択してください。', $error);
    CheckUrl($name, '・お名前にＵＲＬは記入できません。', $error); // 追加
    CheckUrl($title, '・質問タイトルにＵＲＬは記入できません。', $error); // 追加
    CheckUrl($text, '・質問文にＵＲＬは記入できません。', $error); // 追加
    $result = [];
    if (empty($error)) {
        $result['error'] = '';
        $result['name'] = $name;
        $result['title'] = $title;
        $result['text'] = $text;
        $_SESSION['name'] = $name;
        $_SESSION['title'] = $title;
        $_SESSION['text'] = $text;
        $_SESSION['stamp'] = $stamp;
        $_SESSION['attach'] = $_FILES['attach'];
        foreach ($_FILES['attach']['tmp_name'] as $i => $tmp_name) {
            if (!empty($tmp_name)) {
                $_SESSION['attach']['data'][$i] = file_get_contents($tmp_name);
            }
        }
    } else {
        $result['error'] = $error;
        $_SESSION['name'] = '';
        $_SESSION['title'] = '';
        $_SESSION['text'] = '';
        $_SESSION['stamp'] = '';
        $_SESSION['attach'] = null;
    }
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode($result);
    exit;
}
add_action('wp_ajax_bbs_quest_submit', 'bbs_quest_submit');
add_action('wp_ajax_nopriv_bbs_quest_submit', 'bbs_quest_submit');

/* 回答タイトルとスタンプ画像なし（回答掲示板) */
function bbs_answer_submit()
{
    // Cookie から user_id を取得。存在しなければ UUID を生成して Cookie に保存（識別用）
    $user_id = $_COOKIE['user_id'] ?? wp_generate_uuid4();
    // user_id を 10年有効な Cookie に保存（次回以降の識別のため）
    setcookie('user_id', $user_id, time() + (10 * YEAR_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
    // サーバー側ですぐ使えるよう $_COOKIE にもセット
    $_COOKIE['user_id'] = $user_id;

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
        $tmp_dir = $upload_dir['basedir'] . '/tmp/';        // 一時保存用の tmp ディレクトリパス

        // tmp ディレクトリが存在しない場合は作成
        if (!file_exists($tmp_dir)) {
            wp_mkdir_p($tmp_dir);
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

function Chk_ngword($str, $mes, &$error)
{
    // NGワードリスト配列の定義
    $ng_words = ['死ね', 'アホ', '殺す', 'バカ'];
    foreach ($ng_words as $ngWordsVal) {
        // 対象文字列にキーワードが含まれるか
        if (false !== mb_strpos($str, $ngWordsVal)) {
            $error[] = $mes;
        }
    }
}
/* $str = 名前、タイトル、質問文 */
function Chk_StrMode($str)
{
    // タグを除去
    $str = strip_tags($str);
    // 連続する空白をひとつにする
    $str = preg_replace('/[\x20\xC2\xA0]++/u', "\x20", $str);
    // 連続する改行をひとつにする
    $str = preg_replace("/(\x20*[\r\n]\x20*)++/", "\n", $str);
    // 前後の空白を除去
    $str = mb_ereg_replace('^(　){0,}', '', $str);
    $str = mb_ereg_replace('(　){0,}$', '', $str);
    $str = trim($str);
    // 特殊文字を HTML エンティティに変換する
    $str = htmlspecialchars($str);

    return $str;
}
/* 未入力チェックファンクション */
function Chk_InputMode($str, $mes, &$error)
{
    if ('' == $str) {
        $error[] = $mes;
    }
}

/* 以下追加 */
function CheckUrl($checkurl, $mes, &$error)
{
    if (preg_match("/[\.,:;]/u", $checkurl)) {
        $error[] = $mes;
    }
}

function bbs_quest_confirm()
{
    // 新しいセッションを開始、あるいは既存のセッションを再開する
    session_start();
    // 何もせず終わる処理
    if (empty($_SESSION['text']) || empty($_SESSION['title']) || empty($_SESSION['stamp'])) {
        exit;
    }
    // $wpdbでSQLを実行
    global $wpdb;
    // どのようなデータをどのテーブルに登録するか
    $sql = "INSERT INTO {$wpdb->prefix}sortable(text,name,title,stamp,ip) VALUES(%s,%s,%s,%d,%s)";
    // セッション変数に登録
    $text = $_SESSION['text'];
    $name = $_SESSION['name'];
    $title = $_SESSION['title'];
    $stamp = $_SESSION['stamp'];
    // ipアドレスを取得する
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = $wpdb->prepare($sql, $text, $name, $title, $stamp, $ip);
    // プリペアードステートメントを用意してから、下記のようにresultsで値を取得
    $query_result = $wpdb->query($query);
    // カラム名 unique_id の質問UUID を一度そのデータを読み込んで取得する
    $sql = "SELECT unique_id FROM {$wpdb->prefix}sortable WHERE id = %d";
    $query = $wpdb->prepare($sql, $wpdb->insert_id);
    $rows = $wpdb->get_results($query);
    $unique_id = $rows[0]->unique_id;
    // アップロードディレクトリ（パス名）を取得する
    $upload_dir = wp_upload_dir();
    // 『filenames』を記述して配列名を記述し、それに『[]』を代入すればそれは配列として扱われます
    $filenames = [];
    foreach ($_SESSION['attach']['tmp_name'] as $i => $tmp_name) {
        if (empty($tmp_name)) {
            $filenames[$i] = '';
        } else {
            $type = explode('/', $_SESSION['attach']['type'][$i]);
            $ext = $type[1];
            if (3 == $i) { // 比較した時に3＋1以上なら
                $n = 'usericon';
            } else {
                $n = $i + 1;
            }
            $filenames[$i] = "{$unique_id}_{$n}.{$ext}";
            $attach_path = $upload_dir['basedir'] . '/attach/' . $filenames[$i];
            // 文字列をファイルに書き込む、文字列データを書き込むファイル名を指定
            file_put_contents($attach_path, $_SESSION['attach']['data'][$i]);
        }
    }
    $result = [];
    // 条件式が成り立った場合処理を実行
    if (false === $query_result) {
        $result['error'] = '登録できませんでした';
        // 条件式が成り立たなければ処理を実行
    } else { // どのテーブルの何をどう更新するか
        $sql = "UPDATE {$wpdb->prefix}sortable SET attach1=%s,attach2=%s,attach3=%s,usericon=%s WHERE id=%d";
        $query = $wpdb->prepare($sql, $filenames[0], $filenames[1], $filenames[2], $filenames[3], $wpdb->insert_id);
        $wpdb->query($query);
        $result['error'] = '';
    }
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode($result);
    exit;
}
add_action('wp_ajax_bbs_quest_confirm', 'bbs_quest_confirm');
add_action('wp_ajax_nopriv_bbs_quest_confirm', 'bbs_quest_confirm');

/* 回答タイトルとスタンプ画像なし（回答掲示板) */
function bbs_answer_confirm()
{
    // Cookie から user_id を取得（存在しない場合はエラー）
    $user_id = $_COOKIE['user_id'] ?? null;
    if (!$user_id) {
        wp_send_json(['error' => 'ユーザーIDが見つかりません']);
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
