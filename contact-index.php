<?php
/*
 固定ページ お問い合わせ
 Template Name: contact-index
*/
?>
<?php
/*　↑↑↑↑↑↑↑↑↑↑　ここから上部は page.php のヘッダーのコピペ　↑↑↑↑↑↑↑↑↑↑　 */

// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

// エラーメッセージと不正アクセスフラグ(メール送信)
$error_mes = "";
$noindexaccess = true;

//define() - 名前を指定して定数を定義する
// メアドに表示する名前
define('WEBMST_NAME', 'サイト名');
// お問い合わせ用メアド
define('WEBMST_MAIL', 'xxxxx@xxx.xx');
// 送信先メールアドレス
$mailto = WEBMST_MAIL;

#--------------------------------------------------------------
# 条件分岐文のひとつswitch()
#--------------------------------------------------------------
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action):
        // エラーチェック&確認画面表示
    case "completion":
        /////////////////////////////////////////////////////////////////////////////
        //　メール送信処理と完了画面を表示,外部ファイルを読み込んで使いまわす方法

        if (
            !isset($_POST['contact_nonce']) ||
            !wp_verify_nonce($_POST['contact_nonce'], 'contact_completion')
        ) {
            wp_die('不正なアクセスです');
        }

        include('contact-check.php');

        if (!$error_mes) {
            include('contact-sendmail.php');
            include('contact-completion.php');
        } else {
            die("<p>エラーが発生しました。<br />もう一度送信しなおしてください。</p>");
        }

        break;
    case "confirm":

        /////////////////////////////////////////////////////////////////////////////
        // エラーがあれば再入力、なければ確認画面表示

        if (
            !isset($_POST['contact_nonce']) ||
            !wp_verify_nonce($_POST['contact_nonce'], 'contact_confirm')
        ) {
            wp_die('不正なアクセスです');
        }

        // IPアドレス取得
        // Cloudflare経由なら HTTP_CF_CONNECTING_IP を優先
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';

        // 制限キー作成
        $limit_key = 'contact_limit_' . md5($ip);

        // 現在回数取得
        $limit_count = (int) get_transient($limit_key);

        // 10分で3回以上なら拒否
        if ($limit_count >= 3) {
            wp_die('送信回数が多すぎます。しばらく待ってから再度お試しください。');
        }

        // 回数加算（10分保持）
        set_transient($limit_key, $limit_count + 1, 600);

        include('contact-check.php');

        if ($error_mes):
            include('contact-input.php');
        else:
            include('contact-confirm.php');
        endif;

        break;
    default:
        /////////////////////////////////////////////////////////////////////////////
        // 新規入力画面を表示

        include('contact-input.php');

endswitch;

/*　↓↓↓↓↓↓↓↓↓↓　ここから下部は page.php のフッターのコピペ　↓↓↓↓↓↓↓↓↓↓　 */
?>