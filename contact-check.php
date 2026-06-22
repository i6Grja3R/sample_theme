<?php
/*
 固定ページ お問い合わせ
 お問い合わせフォーム 文字列チェック
*/
// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

// 不正アクセスチェック
if (!$noindexaccess) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

/* 危険文字列置換ファンクション */
function contact_chk_str_mode($str)
{

    // タグを除去
    $str = strip_tags($str);
    // 空白を除去
    $str = mb_ereg_replace("^(　){0,}", "", $str);
    $str = mb_ereg_replace("(　){0,}$", "", $str);
    $str = trim($str);
    // 特殊文字を HTML エンティティに変換する
    $str = htmlspecialchars($str);

    return $str;
}
/* 未入力チェックファンクション */
function contact_chk_input_mode($str, $mes)
{
    $errmes = "";
    if ($str == "") {
        $errmes .= "{$mes}<br>\n";
    }
    return $errmes;
}

/* メールアドレスチェックファンクション 2017.9.1現在 参考サイト：http://wepicks.net/phpsample-preg-mail/ */
function contact_check_email_address($sMailaddress)
{
    if (preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $sMailaddress)) {
        list($username, $domain) = explode('@', $sMailaddress);
        if (!checkdnsrr($domain, 'MX')) {
            return false;
        }
        return true;
    }
    return false;
}

#----------------------------------------------------------------------------------
# データの受け取りと危険文字列置換  ※Chk_StrMode(文字列);
#----------------------------------------------------------------------------------
$params = array();

// 必要な項目だけ受け取る
$params['subject'] = isset($_POST['subject'])
    ? contact_chk_str_mode($_POST['subject'])
    : '';
$params['namae'] = isset($_POST['namae'])
    ? contact_chk_str_mode($_POST['namae'])
    : '';

$params['email'] = isset($_POST['email'])
    ? contact_chk_str_mode($_POST['email'])
    : '';

$params['message'] = isset($_POST['message'])
    ? contact_chk_str_mode($_POST['message'])
    : '';

$params['action'] = isset($_POST['action'])
    ? contact_chk_str_mode($_POST['action'])
    : '';
$params['website'] = isset($_POST['website'])
    ? contact_chk_str_mode($_POST['website'])
    : '';

$params['js_enabled'] = isset($_POST['js_enabled'])
    ? contact_chk_str_mode($_POST['js_enabled'])
    : '';

// 変数に個別代入
$subject = $params['subject'];
$namae   = $params['namae'];
$email   = $params['email'];
$message = $params['message'];
$action  = $params['action'];
$website = $params['website'];
$js_enabled = $params['js_enabled'];

// honeypot対策
if (!empty($website)) {

    error_log('Honeypot detected: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

    wp_die('不正な送信を検出しました');
}

// JavaScript必須化
if ($action === 'confirm' && $js_enabled !== '1') {

    error_log('JS disabled bot: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

    wp_die('JavaScriptを有効にしてください');
}

// Turnstile認証は「入力画面 → 確認画面」の confirm 時だけ行う
if ($action === 'confirm') {

    $turnstile_secret = '0x4AAAAAADOYsmq2AB-irYDHGZyuF4pDp8w';
    $turnstile_response = $_POST['cf-turnstile-response'] ?? '';

    if (empty($turnstile_response)) {
        $error_mes .= "・Turnstile認証を行ってください。<br />\n";
    } else {
        $verify_response = wp_remote_post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            array(
                'timeout' => 10,
                'body' => array(
                    'secret'   => $turnstile_secret,
                    'response' => $turnstile_response,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ),
            )
        );

        if (is_wp_error($verify_response)) {
            $error_mes .= "・Turnstile認証に失敗しました。<br />\n";
        } else {
            $body = json_decode(wp_remote_retrieve_body($verify_response), true);

            if (empty($body['success'])) {
                error_log(print_r($body, true));
                $error_mes .= "・Turnstile認証に失敗しました。<br />\n";
            }
        }
    }
}

#----------------------------------------------------------------------------------
# エラーチェック   ※Chk_InputMode(文字列,モード,エラーメッセージ);
#----------------------------------------------------------------------------------
$error_mes .= contact_chk_input_mode($subject, "・件名をご記入ください。<br />\n");

$error_mes .= contact_chk_input_mode($namae, "・お名前をご記入ください。<br />\n");

$error_mes .= contact_chk_input_mode($email, "・メールアドレスをご記入ください。<br />\n");

// メールアドレスチェック
if ($email) {
    if (contact_check_email_address($email) != true) {
        $error_mes .= "・メールアドレスの形式に誤りがあります。<br />\n";
    }
}

// URL数制限
$url_count = preg_match_all('/https?:\/\//i', $message);

if ($url_count >= 2) {
    $error_mes .= "・URLを複数含むお問い合わせは送信できません。<br />\n";
}

$error_mes .= contact_chk_input_mode($message, "・お問い合わせ内容をご記入ください。<br />\n");
