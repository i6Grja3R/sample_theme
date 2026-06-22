<?php
/*
 固定ページ お問い合わせ
 お問い合わせフォーム 入力画面
*/
// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

echo '<link rel="stylesheet" href="' . esc_url(get_stylesheet_directory_uri() . '/css/style.css') . '?v=' . filemtime(get_stylesheet_directory() . '/css/style.css') . '">';

// 不正アクセスチェック
if (!$noindexaccess) {
    header("HTTP/1.0 404 Not Found");
    exit();
}
// エラーがあったらトースト表示
if (!empty($error_mes)) {
    echo '<div id="contact-toast" class="contact-toast">';
    echo '入力内容を確認してください';
    echo '</div>';
}

// 文字化けや一部XSS回避を安定化
$subject_value = isset($subject)
    ? htmlspecialchars($subject, ENT_QUOTES, 'UTF-8')
    : '';

$namae_value = isset($namae)
    ? htmlspecialchars($namae, ENT_QUOTES, 'UTF-8')
    : '';

$email_value = isset($email)
    ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
    : '';

$message_value = isset($message)
    ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
    : '';

echo '<div class="contact-wrap">';

// ★この行を追加して、元のピンクのヘッダーデザインを復活させます！
echo '<div class="contact-title">お絵描き民 お問い合わせ</div>';

//送信時のデータ形式を指定する
echo '<form class="contact-form" name="toiawase" method="post" action="">';

echo '<div class="contact-table">';

echo '<div class="contact-row">';
echo '<div class="contact-label">件名 <span>必須</span></div>';
echo '<div class="contact-field"><input type="text" name="subject" maxlength="100" value="' . $subject_value . '" required></div>';
echo '</div>';

echo '<div class="contact-row">';
echo '<div class="contact-label">お名前 <span>必須</span></div>';
echo '<div class="contact-field"><input type="text" name="namae" maxlength="60" value="' . $namae_value . '" required></div>';
echo '</div>';

echo '<div class="contact-row">';
echo '<div class="contact-label">メールアドレス <span>必須</span></div>';
echo '<div class="contact-field"><input type="email" name="email" maxlength="200" value="' . $email_value . '" required></div>';
echo '</div>';

echo '<div class="contact-row message-row">';
echo '<div class="contact-label">メッセージ <span>必須</span></div>';
echo '<div class="contact-field"><textarea name="message" required>' . $message_value . '</textarea></div>';
echo '</div>';

echo '</div>'; // contact-table 終了

echo '<div class="contact-bottom">';
echo '<div class="cf-turnstile" data-sitekey="0x4AAAAAADOYss38IPjlnyK9"></div>';
echo '<input type="submit" value="内容確認画面へ">';
echo '</div>';

// nonce追加
echo wp_nonce_field('contact_confirm', 'contact_nonce', true, false);

echo '<input type="hidden" name="action" value="confirm">';
echo '<input type="hidden" name="js_enabled" value="0" id="js_enabled">';

// honeypot
echo '<div style="position:absolute;left:-9999px;">';
echo '<input type="text" name="website" tabindex="-1" autocomplete="off">';
echo '</div>';

echo '</form>';
// var_dump($post);
echo '</div>'; // contact-wrap
?>
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // JS必須化用 hidden を 1 にする
        const jsEnabled = document.getElementById('js_enabled');
        if (jsEnabled) {
            jsEnabled.value = '1';
        }

        // エラーがある場合だけトーストを表示
        const toast = document.getElementById('contact-toast');
        if (toast) {
            toast.classList.add('show');

            setTimeout(function() {
                toast.classList.remove('show');
            }, 3000);
        }

    });
</script>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>